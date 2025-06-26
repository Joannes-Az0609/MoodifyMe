const langchainConfig = require('../config/langchain');
const vectorStoreService = require('./vectorStore');
const moodAnalyzer = require('./moodAnalyzer');
const jokeService = require('./jokeService');
const geminiConfig = require('../config/gemini');
const logger = require('../utils/logger');
const { v4: uuidv4 } = require('uuid');

class ChatService {
  constructor() {
    this.conversations = new Map(); // In-memory storage for conversations
    this.qaChain = null;
  }

  async initialize() {
    try {
      this.qaChain = langchainConfig.getQAChain();
      logger.info('Chat service initialized');
      return true;
    } catch (error) {
      logger.error('Failed to initialize chat service:', error);
      throw error;
    }
  }

  async processMessage(message, conversationId = null, options = {}) {
    try {
      // Enhanced conversation flow - allow more natural discussions
      const isGeneralConversation = this.isGeneralConversationAllowed(message, conversationId);

      // Check if message is psychology/mood related (but allow joke requests and general conversation)
      if (!this.isPsychologyRelated(message) && !options.requestJoke && !isGeneralConversation) {
        return {
          conversationId: conversationId || uuidv4(),
          response: "I'm here to help with mood and emotional well-being, but I'm also happy to have a friendly conversation! How are you feeling today, and what's on your mind?",
          mood: { mood: 'neutral', confidence: 0.5 },
          type: 'redirect',
          sources: [],
          conversation: { id: conversationId || uuidv4(), messageCount: 0, currentMood: 'neutral' }
        };
      }

      // Create new conversation if none provided
      if (!conversationId) {
        conversationId = uuidv4();
      }

      // Get or create conversation with enhanced tracking
      let conversation = this.conversations.get(conversationId);
      if (!conversation) {
        conversation = {
          id: conversationId,
          messages: [],
          mood: 'neutral',
          moodHistory: [],
          topics: [], // Track conversation topics
          personalDetails: {}, // Remember user details for continuity
          conversationStyle: 'supportive', // Adapt conversation style
          engagementLevel: 'medium', // Track how engaged the user is
          createdAt: new Date().toISOString(),
          lastActivity: new Date().toISOString()
        };
        this.conversations.set(conversationId, conversation);
      }

      // Analyze mood and update conversation intelligence
      const moodAnalysis = await moodAnalyzer.analyzeMood(message);
      conversation.mood = moodAnalysis.mood;
      conversation.moodHistory.push(moodAnalysis);
      conversation.lastActivity = new Date().toISOString();

      // Enhanced conversation tracking
      this.updateConversationIntelligence(conversation, message, moodAnalysis);

      // Add user message to conversation
      conversation.messages.push({
        role: 'user',
        content: message,
        timestamp: new Date().toISOString(),
        mood: moodAnalysis
      });

      // Check for crisis situations first
      const crisisDetected = this.detectCrisis(message, moodAnalysis);

      // Determine response type based on crisis, options and mood
      let response;
      if (crisisDetected) {
        response = await this.generateCrisisResponse(message, moodAnalysis, conversation, crisisDetected);
      } else if (options.requestJoke || this.shouldTellJoke(message, moodAnalysis)) {
        response = await this.generateJokeResponse(message, moodAnalysis, conversation);
      } else {
        response = await this.generateTherapeuticResponse(message, moodAnalysis, conversation);
      }

      // Add assistant response to conversation
      conversation.messages.push({
        role: 'assistant',
        content: response.content,
        timestamp: new Date().toISOString(),
        type: response.type,
        sources: response.sources || [],
        mood: moodAnalysis.mood
      });

      // Enhanced conversation history management for long conversations
      const maxHistory = parseInt(process.env.MAX_CONVERSATION_HISTORY) || 20; // Increased for longer conversations
      if (conversation.messages.length > maxHistory * 2) {
        // Keep important messages and recent messages
        const importantMessages = this.extractImportantMessages(conversation.messages);
        const recentMessages = conversation.messages.slice(-maxHistory);
        conversation.messages = [...importantMessages, ...recentMessages];
      }

      logger.info(`Processed message for conversation ${conversationId}, mood: ${moodAnalysis.mood}`);

      return {
        conversationId,
        response: response.content,
        mood: moodAnalysis,
        type: response.type,
        sources: response.sources || [],
        conversation: this.getConversationSummary(conversation)
      };
    } catch (error) {
      logger.error('Error processing message:', error);

      // Determine appropriate fallback response based on error type
      let fallbackResponse;
      let errorType = 'error';

      if (error.message?.includes('quota exceeded') || error.message?.includes('Too Many Requests')) {
        // Use a direct therapeutic response without API calls
        fallbackResponse = this.getTherapeuticFallbackResponse(message, 'quota_exceeded');
        errorType = 'quota_exceeded';
      } else if (error.message?.includes('Network connection') || error.message?.includes('fetch failed')) {
        fallbackResponse = this.getTherapeuticFallbackResponse(message, 'network_error');
        errorType = 'network_error';
      } else if (error.message?.includes('model not found')) {
        fallbackResponse = this.getTherapeuticFallbackResponse(message, 'model_error');
        errorType = 'model_error';
      } else {
        fallbackResponse = this.getTherapeuticFallbackResponse(message, 'general_error');
      }

      return {
        conversationId: conversationId || uuidv4(),
        response: fallbackResponse,
        mood: { mood: conversation?.mood || 'neutral', confidence: 0.5 },
        type: errorType,
        sources: [],
        conversation: {
          id: conversationId || uuidv4(),
          messageCount: conversation?.messages?.length || 0,
          currentMood: conversation?.mood || 'neutral'
        }
      };
    }
  }

  shouldTellJoke(message, moodAnalysis) {
    const jokeKeywords = ['joke', 'funny', 'laugh', 'humor', 'cheer me up', 'make me smile', 'tell me a joke', 'lighten up', 'brighten my day'];
    const messageWords = message.toLowerCase();

    // Only tell jokes when explicitly requested
    const asksForJoke = jokeKeywords.some(keyword => messageWords.includes(keyword));

    return asksForJoke; // Only when explicitly requested
  }

  async generateJokeResponse(message, moodAnalysis, conversation) {
    try {
      // Use message as context without exposing internal request ID
      const context = message;
      const jokeResponse = await jokeService.generateMoodBasedResponse(moodAnalysis.mood, context);

      return {
        content: jokeResponse.response,
        type: 'joke',
        mood: moodAnalysis.mood
      };
    } catch (error) {
      logger.error('Error generating joke response:', error);
      // Fallback to simple joke
      const fallbackJoke = jokeService.getFallbackJoke(moodAnalysis.mood);
      return {
        content: `I understand you're feeling ${moodAnalysis.mood}. ${fallbackJoke.joke}`,
        type: 'joke',
        mood: moodAnalysis.mood
      };
    }
  }

  async generateRAGResponse(message, moodAnalysis, conversation) {
    try {
      // Get relevant context from vector store with reduced context size for faster retrieval
      const contextData = await vectorStoreService.getRelevantContext(
        message,
        moodAnalysis.mood,
        1200 // Reduced from 2000 for faster performance
      );

      // Create enhanced prompt with mood awareness
      const enhancedPrompt = this.createMoodAwarePrompt(
        message,
        moodAnalysis,
        contextData.context,
        conversation
      );

      // Use QA chain for response generation
      const result = await this.qaChain.call({
        question: enhancedPrompt,
        chat_history: this.formatChatHistory(conversation)
      });

      // Enhance the response with quotes if available
      let enhancedResponse = result.text;

      if (contextData.quotes && contextData.quotes.length > 0) {
        const randomQuote = contextData.quotes[Math.floor(Math.random() * contextData.quotes.length)];
        enhancedResponse += `\n\n💭 As ${randomQuote.source} reminds us: "${randomQuote.text}"`;
      }

      return {
        content: enhancedResponse,
        type: 'rag',
        sources: contextData.sources,
        quotes: contextData.quotes,
        relevantPassages: contextData.relevantPassages,
        context: contextData.context
      };
    } catch (error) {
      logger.error('Error generating RAG response:', error);
      // Fallback to enhanced response
      return await this.generateEnhancedFallbackResponse(message, moodAnalysis, conversation);
    }
  }

  createMoodAwarePrompt(message, moodAnalysis, context, conversation) {
    const moodGuidance = this.getMoodGuidance(moodAnalysis.mood);
    const previousContext = this.getEnhancedConversationContext(conversation);
    const personalContext = this.getPersonalContext(conversation);

    return `
You are Chat-Tevez, a kind and emotionally intelligent AI companion who provides therapeutic-style support and guidance. You excel at having meaningful, long conversations with HUMAN USERS about psychology, mental health, life experiences, and personal growth.

IMPORTANT DISCLAIMERS:
- You are NOT a licensed therapist or mental health professional
- You provide supportive guidance, not professional therapy or diagnosis
- For serious mental health concerns, always encourage seeking professional help
- In crisis situations, direct users to emergency services or crisis hotlines

CRITICAL: The user is a HUMAN PERSON sharing their life with you. When they say "I feel tired" or "I want to feel motivated", they are describing THEIR OWN human emotions and experiences. You are their supportive companion helping THEM.

THERAPEUTIC APPROACH & PERSONALITY:
- Function as a professional therapeutic AI using evidence-based techniques (CBT, DBT, ACT, mindfulness, validation therapy)
- Speak like a skilled therapist who builds therapeutic rapport and remembers client progress
- Use therapeutic language that is warm, non-judgmental, and professionally supportive
- Ask therapeutic questions that promote insight, self-reflection, and emotional processing
- Help identify cognitive distortions, behavioral patterns, triggers, and maladaptive coping mechanisms
- Teach evidence-based coping strategies, emotional regulation techniques, and mindfulness practices
- Provide therapeutic homework, exercises, and between-session support
- Remember and reference therapeutic insights and progress from previous sessions
- Adapt therapeutic modality to match their needs: ${conversation.conversationStyle}
- Current therapeutic engagement: ${conversation.engagementLevel}
- Provide psychoeducation, crisis intervention guidance, and professional referrals when needed

CONVERSATION CONTINUITY:
${personalContext ? `What I remember about you: ${personalContext}` : 'This is our first conversation together.'}
${previousContext ? `Recent conversation flow: ${previousContext}` : ''}

THERAPEUTIC RESPONSE STRUCTURE (4-6 sentences, professionally flowing):
🌡️ VALIDATION: Acknowledge and validate their emotional experience with therapeutic empathy
💬 EXPLORATION: Use therapeutic questioning to explore thoughts, feelings, and behavioral patterns
🤔 INTERVENTION: Provide evidence-based coping strategies, reframing, or therapeutic techniques
🧠 INTEGRATION: Connect current session to previous therapeutic work and insights
📋 HOMEWORK: Suggest therapeutic exercises or practices for between sessions when appropriate

Current user mood: ${moodAnalysis.mood} (confidence: ${moodAnalysis.confidence.toFixed(2)})
Mood guidance: ${moodGuidance}

Relevant insights from knowledge base:
${context || 'Drawing from psychological principles and life wisdom.'}

User message: "${message}"

THERAPEUTIC GOALS:
- Assess and understand their mental health needs and therapeutic goals
- Help them develop insight into thought patterns, emotions, and behaviors
- Teach evidence-based coping strategies and emotional regulation skills
- Process trauma, grief, anxiety, depression, and other mental health concerns
- Build resilience, self-awareness, and healthy relationship patterns
- Create a safe therapeutic space for healing and growth
- Track therapeutic progress and adjust treatment approach as needed
- Provide crisis intervention and professional referrals when necessary

Respond as Chat-Tevez, your therapeutic AI assistant, with professional warmth and evidence-based therapeutic expertise:`;
  }

  getMoodGuidance(mood) {
    const therapeuticGuidance = {
      happy: 'Use positive psychology techniques to help them savor and maintain this state. Explore what contributed to this mood.',
      sad: 'Validate their sadness, explore underlying causes, and introduce behavioral activation and cognitive restructuring techniques.',
      angry: 'Help process anger using DBT emotion regulation skills, identify triggers, and teach healthy expression techniques.',
      anxious: 'Apply CBT anxiety management: grounding techniques, thought challenging, exposure principles, and mindfulness practices.',
      calm: 'Reinforce this positive state, explore what created it, and teach techniques to return to this state during stress.',
      confused: 'Use cognitive restructuring to organize thoughts, break down complex issues, and provide psychoeducation.',
      lonely: 'Address isolation using interpersonal therapy techniques, explore relationship patterns, and suggest connection strategies.',
      motivated: 'Channel motivation therapeutically using goal-setting, behavioral planning, and sustainable habit formation.',
      tired: 'Assess for depression/burnout, introduce self-care planning, energy management, and sleep hygiene techniques.',
      grateful: 'Build on gratitude using positive psychology interventions and explore how to cultivate this mindset regularly.',
      neutral: 'Conduct therapeutic assessment, explore current concerns, and identify areas for therapeutic growth.'
    };

    return therapeuticGuidance[mood] || therapeuticGuidance.neutral;
  }

  getDetailedMoodGuidance(mood) {
    const detailedGuidance = {
      happy: 'Amplify their joy! Celebrate with them, encourage them to savor the moment, and suggest ways to maintain this positive energy.',
      sad: 'Offer deep comfort and validation. Acknowledge their pain, provide hope, and share wisdom about resilience and healing.',
      angry: 'Help them process anger healthily. Validate their feelings, offer perspective, and suggest calming techniques for emotional regulation.',
      anxious: 'Provide strong reassurance and practical coping strategies. Offer grounding techniques and perspective on their worries.',
      calm: 'Support their peaceful state. Acknowledge their tranquility and offer wisdom for maintaining emotional balance.',
      confused: 'Provide clarity and structure. Help them organize their thoughts and offer perspective to reduce confusion.',
      lonely: 'Offer connection and understanding. Remind them they\'re not alone and validate their feelings of isolation.',
      motivated: 'Channel their energy positively! Encourage their drive and help them focus their motivation constructively.',
      tired: 'Encourage rest and self-care. Validate their exhaustion and suggest gentle recovery strategies.',
      grateful: 'Build on their gratitude! Acknowledge their appreciation and help them expand these positive feelings.',
      neutral: 'Engage warmly and look for opportunities to understand their emotional state better.'
    };

    return detailedGuidance[mood] || detailedGuidance.neutral;
  }

  getConversationContext(conversation) {
    if (!conversation || !conversation.messages || conversation.messages.length === 0) {
      return null;
    }

    const recentMessages = conversation.messages.slice(-4); // Last 2 exchanges
    const context = recentMessages
      .map(msg => `${msg.role}: ${msg.content.substring(0, 100)}${msg.content.length > 100 ? '...' : ''}`)
      .join(' | ');

    const moodProgression = conversation.moodHistory
      ? conversation.moodHistory.slice(-3).map(m => m.mood).join(' → ')
      : 'No mood history';

    return `Recent conversation: ${context} | Mood progression: ${moodProgression}`;
  }

  formatChatHistory(conversation) {
    return conversation.messages
      .slice(-6) // Last 3 exchanges
      .map(msg => `${msg.role}: ${msg.content}`)
      .join('\n');
  }

  async generateFallbackResponse(message, moodAnalysis) {
    try {
      const prompt = `
        You are Chat-Tevez, a psychology-focused AI companion helping a HUMAN USER. The human user is feeling ${moodAnalysis.mood} and said: "${message}"

        IMPORTANT: The user is a HUMAN PERSON sharing their feelings. When they say "I feel tired", they mean THEY feel tired, not you.

        Provide a comprehensive, supportive response (3-5 sentences) that includes:
        1. 🌡️ Mood acknowledgment with empathy for THEIR feelings
        2. 💬 Supportive psychological guidance and practical advice for THEM
        3. 🧠 A helpful insight or coping strategy for THEM

        Be warm, natural, and genuinely helpful. Focus on THEIR mental health and emotional well-being.
      `;

      const response = await geminiConfig.generateText(prompt);

      return {
        content: response.trim(),
        type: 'fallback',
        sources: []
      };
    } catch (error) {
      logger.error('Error generating fallback response:', error);
      return {
        content: this.getBasicFallbackResponse(moodAnalysis.mood),
        type: 'fallback',
        sources: []
      };
    }
  }

  async generateEnhancedFallbackResponse(message, moodAnalysis, conversation) {
    try {
      const previousContext = this.getConversationContext(conversation);
      const moodGuidance = this.getDetailedMoodGuidance(moodAnalysis.mood);

      const prompt = `
        You are Chat-Tevez, a kind and emotionally intelligent AI companion helping a HUMAN USER. The human user is feeling ${moodAnalysis.mood} and said: "${message}"

        IMPORTANT: The user is a HUMAN PERSON sharing their feelings. Respond to THEIR emotions and experiences, not your own.

        ${previousContext ? `Previous conversation context: ${previousContext}` : ''}

        Provide a comprehensive, supportive response (3-5 sentences) following this structure:
        🌡️ Mood Analysis: Acknowledge THEIR emotional state with empathy
        💬 Supportive Response: Provide empathetic advice, perspective, and practical psychological guidance for THEM
        🧠 Memory Hint: Offer a helpful insight or practical coping strategy for THEM

        Guidance for this mood: ${moodGuidance}

        Be warm, natural, human-like, and genuinely helpful. Focus on THEIR psychology, mental health, and emotional well-being.
      `;

      const response = await geminiConfig.generateText(prompt);

      return {
        content: response.trim(),
        type: 'enhanced_fallback',
        sources: []
      };
    } catch (error) {
      logger.error('Error generating enhanced fallback response:', error);
      return {
        content: this.getBasicFallbackResponse(moodAnalysis.mood),
        type: 'fallback',
        sources: []
      };
    }
  }

  getBasicFallbackResponse(mood) {
    const therapeuticResponses = {
      happy: "🌡️ I can sense the positive energy in your words, and I want to validate this wonderful emotional state you're experiencing. 💬 From a therapeutic perspective, happiness provides us with important data about what works well in your life. Let's explore what specifically contributed to this feeling so we can help you access it more regularly. 🧠 I'd like to work with you on developing strategies to maintain and cultivate these positive emotions as part of your ongoing emotional wellness.",

      sad: "🌡️ I want to acknowledge and validate the sadness you're experiencing - these feelings are completely legitimate and deserve our attention. 💬 Sadness often carries important information about loss, unmet needs, or significant life changes. It's therapeutically important that we don't rush to 'fix' this feeling, but rather explore what it's telling us. 🧠 Together, we can work on processing these emotions and developing healthy coping strategies that honor your experience while supporting your healing.",

      anxious: "🌡️ I can hear the anxiety in your words, and I want you to know that what you're experiencing is your nervous system's attempt to protect you from perceived threats. 💬 Let's work together using evidence-based anxiety management techniques. Try this grounding exercise: notice 5 things you can see, 4 you can touch, 3 you can hear, 2 you can smell, and 1 you can taste. 🧠 We can develop a personalized anxiety management toolkit that includes cognitive restructuring, breathing techniques, and mindfulness practices.",

      angry: "🌡️ I can sense the intensity of your anger, and I want to validate that this emotion is providing important information about your boundaries and values. 💬 Anger often signals that something meaningful to you has been threatened or violated. Let's explore what's underneath this anger - sometimes we find hurt, fear, or unmet needs. 🧠 Together, we can work on healthy anger expression techniques and develop strategies for addressing the underlying issues constructively.",

      lonely: "🌡️ I want to acknowledge the loneliness you're experiencing and validate how painful isolation can feel. 💬 Loneliness is a universal human experience that signals our fundamental need for connection and belonging. Your willingness to reach out shows tremendous courage and self-awareness. 🧠 Let's work together on understanding your relationship patterns and developing strategies for building meaningful connections while also strengthening your relationship with yourself.",

      confused: "🌡️ I can sense the uncertainty you're experiencing, and I want to normalize that confusion is often a natural part of processing complex emotions or life situations. 💬 From a therapeutic perspective, confusion can indicate that you're in a period of growth or transition. Let's work together to organize your thoughts and feelings, breaking down complex issues into manageable pieces. 🧠 We can develop clarity through structured exploration and help you build tolerance for uncertainty as you navigate this process.",

      neutral: "🌡️ I'm here to provide therapeutic support and create a safe space for you to explore whatever is on your mind today. 💬 Sometimes the most important therapeutic work happens when we simply have someone who listens without judgment and helps us process our experiences. 🧠 What would be most helpful for you to explore in our session today? I'm here to support your mental health and emotional wellbeing through evidence-based therapeutic approaches."
    };

    return therapeuticResponses[mood] || therapeuticResponses.neutral;
  }

  getConversation(conversationId) {
    return this.conversations.get(conversationId);
  }

  getConversationSummary(conversation) {
    return {
      id: conversation.id,
      messageCount: conversation.messages.length,
      currentMood: conversation.mood,
      createdAt: conversation.createdAt,
      lastActivity: conversation.lastActivity
    };
  }

  getAllConversations() {
    return Array.from(this.conversations.values()).map(conv => this.getConversationSummary(conv));
  }

  deleteConversation(conversationId) {
    return this.conversations.delete(conversationId);
  }

  async clearConversationMemory(conversationId) {
    const conversation = this.conversations.get(conversationId);
    if (conversation) {
      conversation.messages = [];
      await langchainConfig.clearMemory();
      logger.info(`Cleared memory for conversation ${conversationId}`);
      return true;
    }
    return false;
  }

  isPsychologyRelated(message) {
    const psychologyKeywords = [
      // Emotions and feelings
      'feel', 'feeling', 'emotion', 'mood', 'happy', 'sad', 'angry', 'anxious', 'calm', 'stressed', 'worried', 'excited', 'depressed', 'lonely', 'grateful', 'frustrated', 'overwhelmed', 'content', 'joyful', 'fearful', 'nervous', 'relaxed', 'upset', 'disappointed', 'hopeful', 'confident', 'insecure',

      // Mental health terms
      'mental health', 'therapy', 'counseling', 'psychology', 'psychiatry', 'mindfulness', 'meditation', 'self-care', 'wellness', 'well-being', 'stress management', 'coping', 'resilience', 'healing', 'recovery', 'support', 'help', 'guidance',

      // Psychological concepts
      'thoughts', 'thinking', 'mind', 'brain', 'consciousness', 'awareness', 'perception', 'memory', 'behavior', 'habit', 'pattern', 'trigger', 'response', 'reaction', 'motivation', 'goal', 'purpose', 'meaning', 'identity', 'self-esteem', 'confidence', 'growth', 'development',

      // Relationships and social
      'relationship', 'family', 'friend', 'love', 'connection', 'communication', 'conflict', 'boundary', 'trust', 'intimacy', 'social', 'isolation', 'belonging',

      // Life experiences
      'life', 'experience', 'challenge', 'problem', 'difficulty', 'struggle', 'success', 'failure', 'change', 'transition', 'loss', 'grief', 'trauma', 'healing', 'hope', 'future', 'past', 'present', 'moment',

      // Questions about feelings/state
      'how are you', 'how do you feel', 'what should i do', 'i need help', 'can you help', 'advice', 'suggestion', 'recommend',

      // Humor and mood lifting
      'joke', 'funny', 'laugh', 'humor', 'cheer me up', 'make me smile', 'lighten up', 'brighten my day',

      // Greetings and basic interactions (allow these)
      'hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'thank you', 'thanks', 'please', 'sorry', 'excuse me', 'goodbye', 'bye'
    ];

    const lowerMessage = message.toLowerCase();

    // Allow greetings and basic interactions
    const basicInteractions = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'thank you', 'thanks', 'please', 'sorry', 'excuse me', 'goodbye', 'bye', 'how are you'];
    if (basicInteractions.some(phrase => lowerMessage.includes(phrase))) {
      return true;
    }

    // Check if message contains psychology-related keywords
    const containsPsychologyKeywords = psychologyKeywords.some(keyword =>
      lowerMessage.includes(keyword.toLowerCase())
    );

    // Check for question words that might indicate seeking help/advice
    const helpIndicators = ['how', 'what', 'why', 'when', 'where', 'can', 'should', 'would', 'could', 'help', 'advice', 'feel', 'think'];
    const containsHelpIndicators = helpIndicators.some(indicator =>
      lowerMessage.includes(indicator)
    );

    // Allow if it contains psychology keywords or seems like a help-seeking question
    return containsPsychologyKeywords || containsHelpIndicators;
  }

  // New methods for enhanced long conversations
  isGeneralConversationAllowed(message, conversationId) {
    // Allow more natural conversation flow if we have an existing conversation
    if (conversationId && this.conversations.has(conversationId)) {
      const conversation = this.conversations.get(conversationId);
      // Allow general conversation if we've had at least 2 exchanges
      return conversation.messages.length >= 4;
    }

    // Allow basic conversational responses
    const conversationalPhrases = [
      'tell me about', 'what do you think', 'how about', 'i was wondering',
      'can we talk about', 'i want to discuss', 'let me tell you',
      'you know what', 'speaking of', 'by the way', 'actually',
      'i remember', 'earlier you said', 'going back to'
    ];

    const lowerMessage = message.toLowerCase();
    return conversationalPhrases.some(phrase => lowerMessage.includes(phrase));
  }

  updateConversationIntelligence(conversation, message, moodAnalysis) {
    // Extract and remember personal details
    this.extractPersonalDetails(conversation, message);

    // Track conversation topics
    this.updateTopics(conversation, message);

    // Adjust conversation style based on user's communication
    this.adaptConversationStyle(conversation, message, moodAnalysis);

    // Update engagement level
    this.updateEngagementLevel(conversation, message);
  }

  extractPersonalDetails(conversation, message) {
    const lowerMessage = message.toLowerCase();

    // Extract names
    const namePatterns = [
      /my name is (\w+)/i,
      /i'm (\w+)/i,
      /call me (\w+)/i
    ];

    namePatterns.forEach(pattern => {
      const match = message.match(pattern);
      if (match) {
        conversation.personalDetails.name = match[1];
      }
    });

    // Extract interests/hobbies
    const interestPatterns = [
      /i love (\w+)/i,
      /i enjoy (\w+)/i,
      /i like (\w+)/i,
      /my hobby is (\w+)/i
    ];

    interestPatterns.forEach(pattern => {
      const match = message.match(pattern);
      if (match) {
        if (!conversation.personalDetails.interests) {
          conversation.personalDetails.interests = [];
        }
        conversation.personalDetails.interests.push(match[1]);
      }
    });

    // Extract life situations
    if (lowerMessage.includes('work') || lowerMessage.includes('job')) {
      conversation.personalDetails.hasWorkContext = true;
    }
    if (lowerMessage.includes('family') || lowerMessage.includes('parent')) {
      conversation.personalDetails.hasFamilyContext = true;
    }
    if (lowerMessage.includes('school') || lowerMessage.includes('student')) {
      conversation.personalDetails.hasSchoolContext = true;
    }
  }

  updateTopics(conversation, message) {
    // Simple topic extraction based on key themes
    const topics = {
      work: ['work', 'job', 'career', 'office', 'boss', 'colleague'],
      relationships: ['relationship', 'partner', 'friend', 'family', 'love'],
      health: ['health', 'exercise', 'sleep', 'tired', 'energy'],
      goals: ['goal', 'dream', 'aspiration', 'future', 'plan'],
      stress: ['stress', 'pressure', 'overwhelmed', 'busy', 'deadline']
    };

    const lowerMessage = message.toLowerCase();
    Object.keys(topics).forEach(topic => {
      if (topics[topic].some(keyword => lowerMessage.includes(keyword))) {
        if (!conversation.topics.includes(topic)) {
          conversation.topics.push(topic);
        }
      }
    });
  }

  adaptConversationStyle(conversation, message, moodAnalysis) {
    const messageLength = message.length;
    const isDetailed = messageLength > 100;
    const isEmotional = ['sad', 'anxious', 'angry', 'lonely'].includes(moodAnalysis.mood);

    if (isDetailed && isEmotional) {
      conversation.conversationStyle = 'deep_supportive';
    } else if (isDetailed) {
      conversation.conversationStyle = 'thoughtful';
    } else if (isEmotional) {
      conversation.conversationStyle = 'gentle_supportive';
    } else {
      conversation.conversationStyle = 'friendly';
    }
  }

  updateEngagementLevel(conversation, message) {
    const messageLength = message.length;
    const hasQuestions = message.includes('?');
    const isPersonal = ['i feel', 'i think', 'i want', 'i need'].some(phrase =>
      message.toLowerCase().includes(phrase)
    );

    if (messageLength > 150 || (hasQuestions && isPersonal)) {
      conversation.engagementLevel = 'high';
    } else if (messageLength > 50 || hasQuestions || isPersonal) {
      conversation.engagementLevel = 'medium';
    } else {
      conversation.engagementLevel = 'low';
    }
  }

  getEnhancedConversationContext(conversation) {
    if (!conversation || !conversation.messages || conversation.messages.length === 0) {
      return null;
    }

    // Get more context for longer conversations
    const contextLength = Math.min(8, conversation.messages.length);
    const recentMessages = conversation.messages.slice(-contextLength);

    const context = recentMessages
      .map(msg => `${msg.role}: ${msg.content.substring(0, 150)}${msg.content.length > 150 ? '...' : ''}`)
      .join(' | ');

    const moodProgression = conversation.moodHistory
      ? conversation.moodHistory.slice(-5).map(m => m.mood).join(' → ')
      : 'No mood history';

    const topicContext = conversation.topics.length > 0
      ? `Topics discussed: ${conversation.topics.join(', ')}`
      : '';

    return `${context} | Mood progression: ${moodProgression} | ${topicContext}`;
  }

  getPersonalContext(conversation) {
    if (!conversation.personalDetails || Object.keys(conversation.personalDetails).length === 0) {
      return null;
    }

    const details = [];
    if (conversation.personalDetails.name) {
      details.push(`Name: ${conversation.personalDetails.name}`);
    }
    if (conversation.personalDetails.interests && conversation.personalDetails.interests.length > 0) {
      details.push(`Interests: ${conversation.personalDetails.interests.join(', ')}`);
    }
    if (conversation.personalDetails.hasWorkContext) {
      details.push('Has work/career context');
    }
    if (conversation.personalDetails.hasFamilyContext) {
      details.push('Has family context');
    }

    return details.length > 0 ? details.join(' | ') : null;
  }

  extractImportantMessages(messages) {
    // Extract messages that contain important personal information or emotional breakthroughs
    return messages.filter(msg => {
      const content = msg.content.toLowerCase();
      const isImportant = [
        'my name is', 'i realized', 'breakthrough', 'important to me',
        'i learned', 'i discovered', 'changed my mind', 'life changing'
      ].some(phrase => content.includes(phrase));

      const isEmotionallySignificant = msg.mood &&
        ['very sad', 'very happy', 'breakthrough', 'realization'].includes(msg.mood);

      return isImportant || isEmotionallySignificant;
    }).slice(0, 5); // Keep max 5 important messages
  }

  // Crisis detection system for therapeutic safety
  detectCrisis(message, moodAnalysis) {
    const crisisKeywords = {
      suicide: ['kill myself', 'end my life', 'suicide', 'want to die', 'better off dead', 'not worth living', 'end it all', 'take my own life'],
      selfHarm: ['cut myself', 'hurt myself', 'self harm', 'self-harm', 'cutting', 'burning myself', 'harm myself'],
      violence: ['hurt someone', 'kill someone', 'violent thoughts', 'want to hurt others', 'harm others'],
      severe: ['can\'t go on', 'hopeless', 'no point', 'give up', 'nothing matters', 'no way out']
    };

    const lowerMessage = message.toLowerCase();

    for (const [type, keywords] of Object.entries(crisisKeywords)) {
      if (keywords.some(keyword => lowerMessage.includes(keyword))) {
        return { type, severity: type === 'suicide' ? 'critical' : type === 'selfHarm' ? 'high' : 'medium' };
      }
    }

    // Check mood-based crisis indicators
    if (moodAnalysis.mood === 'sad' && moodAnalysis.confidence > 0.9) {
      return { type: 'severe_depression', severity: 'medium' };
    }

    return null;
  }

  // Crisis intervention response with professional resources
  async generateCrisisResponse(message, moodAnalysis, conversation, crisisInfo) {
    const crisisResources = {
      suicide: {
        message: "🚨 **IMMEDIATE CRISIS SUPPORT NEEDED** 🚨\n\nI'm very concerned about what you've shared. Your life has value and meaning. Please reach out for immediate professional help:\n\n**IMMEDIATE RESOURCES:**\n• **National Suicide Prevention Lifeline: 988** (24/7)\n• **Crisis Text Line: Text HOME to 741741**\n• **Emergency Services: 911**\n\n**International:**\n• International Association for Suicide Prevention: https://www.iasp.info/resources/Crisis_Centres/\n\nYou don't have to face this alone. Professional counselors are available right now to help you through this crisis. Please reach out immediately.",
        urgency: "critical"
      },
      selfHarm: {
        message: "🚨 **CRISIS SUPPORT NEEDED** 🚨\n\nI'm concerned about your safety and the pain you're experiencing. Self-harm is often a way of coping with overwhelming emotions, but there are safer alternatives. Please consider immediate support:\n\n**RESOURCES:**\n• **Crisis Text Line: Text HOME to 741741**\n• **National Suicide Prevention Lifeline: 988**\n• **Self-Injury Outreach & Support: sioutreach.org**\n\nYour feelings are completely valid, and there are healthier ways to manage this intense emotional pain. Professional help is available.",
        urgency: "high"
      },
      severe_depression: {
        message: "💙 **THERAPEUTIC SUPPORT RECOMMENDED** 💙\n\nI can sense you're experiencing significant emotional distress. Depression can make everything feel overwhelming and hopeless, but these feelings can improve with proper therapeutic support.\n\n**RECOMMENDED RESOURCES:**\n• **National Alliance on Mental Illness: 1-800-950-NAMI**\n• **Psychology Today Therapist Directory: psychologytoday.com**\n• **Your primary care physician for referrals**\n\nYou deserve professional therapeutic support during this difficult time. These feelings can change with proper care.",
        urgency: "moderate"
      },
      violence: {
        message: "🚨 **IMMEDIATE PROFESSIONAL HELP NEEDED** 🚨\n\nI'm concerned about the thoughts you're experiencing. Having thoughts about harming others requires immediate professional intervention:\n\n**IMMEDIATE ACTION:**\n• **Call 911 or go to your nearest emergency room**\n• **National Crisis Line: 988**\n• **Crisis Text Line: Text HOME to 741741**\n\nThese thoughts are a sign that you need professional support right now. Please reach out immediately for everyone's safety.",
        urgency: "critical"
      }
    };

    const response = crisisResources[crisisInfo.type] || crisisResources.severe_depression;

    // Log crisis intervention for safety monitoring
    logger.error(`CRISIS INTERVENTION TRIGGERED: ${crisisInfo.type} (${crisisInfo.severity} severity) - User message: ${message.substring(0, 100)}...`);

    return response.message;
  }

  // Enhanced therapeutic response generation
  async generateTherapeuticResponse(message, moodAnalysis, conversation) {
    // This replaces the old generateRAGResponse with enhanced therapeutic focus
    return await this.generateRAGResponse(message, moodAnalysis, conversation);
  }

  // Therapeutic fallback responses that don't require API calls
  getTherapeuticFallbackResponse(message, errorType) {
    const lowerMessage = message.toLowerCase();

    // Detect emotional keywords to provide appropriate therapeutic response
    const emotionalKeywords = {
      sad: ['sad', 'depressed', 'down', 'upset', 'crying', 'tears', 'grief', 'loss'],
      anxious: ['anxious', 'worried', 'nervous', 'panic', 'stress', 'overwhelmed', 'fear'],
      angry: ['angry', 'mad', 'furious', 'rage', 'frustrated', 'irritated'],
      lonely: ['lonely', 'alone', 'isolated', 'disconnected', 'empty'],
      hopeless: ['hopeless', 'worthless', 'pointless', 'give up', 'end it all'],
      happy: ['happy', 'good', 'great', 'excited', 'joy', 'wonderful']
    };

    let detectedMood = 'neutral';
    for (const [mood, keywords] of Object.entries(emotionalKeywords)) {
      if (keywords.some(keyword => lowerMessage.includes(keyword))) {
        detectedMood = mood;
        break;
      }
    }

    const responses = {
      quota_exceeded: {
        sad: "🌡️ I can sense the sadness in your words, and I want you to know that what you're feeling is completely valid and important. 💬 While I'm experiencing high demand right now, I want to offer you some immediate support: try the 4-7-8 breathing technique (breathe in for 4, hold for 7, exhale for 8). 🧠 Your feelings matter, and I'll be ready to provide deeper therapeutic support in just a moment. Please try again in about a minute.",

        anxious: "🌡️ I can hear the anxiety in your message, and I want to validate that these feelings are real and understandable. 💬 While my systems recharge, let's use this moment therapeutically: try grounding yourself by naming 5 things you can see, 4 you can touch, 3 you can hear, 2 you can smell, and 1 you can taste. 🧠 This anxiety will pass, and I'll be here to help you process it further in just a moment.",

        angry: "🌡️ I can feel the intensity of your emotions, and anger often signals that something important to you has been threatened or violated. 💬 While I'm briefly unavailable, try this: take 10 slow, deep breaths and notice where you feel the anger in your body. 🧠 Your feelings are valid, and I'll be ready to help you explore what's underneath this anger very soon.",

        lonely: "🌡️ I hear the loneliness in your words, and I want you to know that reaching out shows tremendous courage and self-awareness. 💬 Even though I'm temporarily at capacity, please remember that you're not truly alone - you've taken the brave step of seeking connection. 🧠 I'll be back in just a moment to provide the therapeutic support and connection you deserve.",

        hopeless: "🌡️ I can sense you're in significant emotional pain right now, and I want you to know that these feelings, while overwhelming, are temporary. 💬 If you're having thoughts of self-harm, please reach out immediately to: National Suicide Prevention Lifeline (988) or Crisis Text Line (text HOME to 741741). 🧠 You matter, your life has value, and I'll be available to support you therapeutically in just a moment.",

        happy: "🌡️ I can feel the positive energy in your message, and I want to celebrate this moment with you! 💬 While I'm briefly at capacity, take a moment to savor this feeling - notice what contributed to it and how it feels in your body. 🧠 I'll be ready to help you explore and maintain these positive emotions in just a moment.",

        neutral: "🌡️ I want to acknowledge that you're reaching out for therapeutic support, and that takes courage. 💬 I'm currently experiencing high demand but will be available again very soon. In the meantime, take three deep breaths and remind yourself that seeking help is a sign of strength. 🧠 I'll be ready to provide professional therapeutic support in about a minute."
      },

      network_error: {
        sad: "🌡️ I can sense you're reaching out during a difficult time, and I want to validate your courage in seeking support. 💬 While we're having connection issues, please remember that your feelings are completely valid and important. 🧠 Try some gentle self-care while we reconnect - perhaps some deep breathing or a warm cup of tea. I'm here for you.",

        anxious: "🌡️ I can hear the anxiety in your message, and I want you to know that these feelings are understandable and manageable. 💬 While we work through this technical issue, try the 5-4-3-2-1 grounding technique to help calm your nervous system. 🧠 You've handled difficult moments before, and you can handle this one too.",

        neutral: "🌡️ I want to acknowledge that you're seeking therapeutic support, which shows self-awareness and strength. 💬 We're experiencing some technical difficulties, but your mental health journey is important. 🧠 Please try again in a moment - I'm committed to providing you with the therapeutic support you deserve."
      },

      general_error: {
        neutral: "🌡️ I want to validate that you're taking the important step of seeking therapeutic support. 💬 I'm experiencing some technical difficulties, but I want you to know that your feelings and experiences are important and valid. 🧠 Please try again in a moment, and I'll be ready to provide you with professional therapeutic guidance and support."
      }
    };

    const errorResponses = responses[errorType] || responses.general_error;
    return errorResponses[detectedMood] || errorResponses.neutral;
  }
}

module.exports = new ChatService();
