const geminiConfig = require('../config/gemini');
const logger = require('../utils/logger');

class JokeService {
  constructor() {
    this.jokeTemplates = {
      happy: {
        style: 'uplifting and celebratory',
        themes: ['success', 'friendship', 'good times', 'achievements'],
        tone: 'enthusiastic and positive'
      },
      sad: {
        style: 'gentle and comforting',
        themes: ['hope', 'resilience', 'small joys', 'perspective'],
        tone: 'warm and understanding'
      },
      angry: {
        style: 'light and defusing',
        themes: ['absurdity', 'perspective', 'silly situations', 'irony'],
        tone: 'calming and humorous'
      },
      anxious: {
        style: 'reassuring and light',
        themes: ['everyday situations', 'relatable worries', 'gentle humor'],
        tone: 'soothing and comforting'
      },
      calm: {
        style: 'peaceful and zen-like',
        themes: ['nature', 'simplicity', 'mindfulness', 'balance'],
        tone: 'serene and wise'
      },
      confused: {
        style: 'clarifying and simple',
        themes: ['everyday logic', 'simple truths', 'clarity'],
        tone: 'helpful and clear'
      },
      lonely: {
        style: 'connecting and inclusive',
        themes: ['shared experiences', 'universal feelings', 'connection'],
        tone: 'warm and inclusive'
      },
      motivated: {
        style: 'energetic and inspiring',
        themes: ['achievement', 'progress', 'determination', 'success'],
        tone: 'encouraging and dynamic'
      },
      tired: {
        style: 'gentle and energizing',
        themes: ['rest', 'simple pleasures', 'easy humor'],
        tone: 'soft and revitalizing'
      },
      grateful: {
        style: 'appreciative and heartwarming',
        themes: ['blessings', 'kindness', 'good fortune', 'appreciation'],
        tone: 'thankful and positive'
      },
      neutral: {
        style: 'balanced and versatile',
        themes: ['everyday life', 'observations', 'general humor'],
        tone: 'friendly and approachable'
      }
    };
  }

  async generateJoke(mood, context = '') {
    try {
      const jokeConfig = this.jokeTemplates[mood] || this.jokeTemplates.neutral;
      
      const prompt = this.createJokePrompt(mood, jokeConfig, context);
      const joke = await geminiConfig.generateText(prompt);
      
      logger.info(`Generated joke for mood: ${mood}`);
      
      return {
        joke: joke.trim(),
        mood,
        style: jokeConfig.style,
        timestamp: new Date().toISOString()
      };
    } catch (error) {
      logger.error('Error generating joke:', error);
      return this.getFallbackJoke(mood);
    }
  }

  createJokePrompt(mood, jokeConfig, context) {
    return `
      You are a specialized psychology-focused AI assistant. Generate a ${jokeConfig.style} joke that would be appropriate for someone feeling ${mood}.

      IMPORTANT: Focus ONLY on psychology, mental health, emotions, and mood-related humor.

      Guidelines:
      - Tone: ${jokeConfig.tone}
      - Themes: ${jokeConfig.themes.join(', ')} (psychology/mood related)
      - Keep it clean and appropriate for mental health context
      - Make it relatable to emotional experiences
      - Length: 1-3 sentences maximum
      - Should help improve the person's mood through psychological humor
      - Avoid topics unrelated to psychology, emotions, or mental well-being
      ${context ? `- Context: ${context}` : ''}

      Generate only the psychology/mood-related joke, no additional text or explanation:
    `;
  }

  async generateMultipleJokes(mood, count = 3) {
    try {
      const jokes = [];
      for (let i = 0; i < count; i++) {
        const joke = await this.generateJoke(mood);
        jokes.push(joke);
        // Small delay to avoid rate limiting
        await new Promise(resolve => setTimeout(resolve, 500));
      }
      return jokes;
    } catch (error) {
      logger.error('Error generating multiple jokes:', error);
      return [this.getFallbackJoke(mood)];
    }
  }

  getFallbackJoke(mood) {
    const fallbackJokes = {
      happy: "Why did the therapist bring a ladder to work? Because they wanted to help people reach new heights of happiness! Keep climbing! 🎉",
      sad: "What did one emotion say to another? 'I feel you!' Remember, all feelings are valid and temporary. You're stronger than you know! 💙",
      angry: "Why don't anger management classes ever get cancelled? Because they always have a full house! Take a deep breath - you've got this! 😤➡️😌",
      anxious: "What's a therapist's favorite type of music? Anything that helps you find your rhythm! Focus on your breathing - you're safe right now! 🎵",
      calm: "Why did the meditation teacher refuse novocaine? She wanted to transcend dental medication! Stay in this peaceful moment! 🧘",
      confused: "Why did the psychologist bring a map to therapy? To help people find their way! It's okay to feel lost sometimes - clarity will come! 🗺️",
      lonely: "What do you call a support group for introverts? A quiet gathering of beautiful souls! Remember, you're never truly alone! 🤗",
      motivated: "Why did the life coach carry a flashlight? To help people see their potential! Keep shining your light! ✨",
      tired: "What did the therapist say to the exhausted client? 'Rest is not a reward for work completed, it's a requirement for work to continue!' Take care of yourself! 😴",
      grateful: "Why did the gratitude journal go to therapy? It wanted to appreciate itself more! Thank you for being you! 🙏",
      neutral: "What's the difference between a psychologist and a magician? One helps you find yourself, the other makes things disappear! Hope this brings a smile! 😊"
    };

    return {
      joke: fallbackJokes[mood] || fallbackJokes.neutral,
      mood,
      style: 'fallback',
      timestamp: new Date().toISOString()
    };
  }

  async generateMoodBasedResponse(mood, userMessage) {
    try {
      const jokeConfig = this.jokeTemplates[mood] || this.jokeTemplates.neutral;

      // Create variety in joke topics to avoid repetition
      const jokeTopics = [
        'therapy sessions', 'brain functions', 'emotions', 'mindfulness', 'stress management',
        'relationships', 'self-care', 'mental health', 'psychology research', 'cognitive behavior',
        'meditation', 'anxiety', 'happiness', 'memory', 'dreams', 'personality types',
        'communication', 'empathy', 'resilience', 'motivation'
      ];

      const randomTopic = jokeTopics[Math.floor(Math.random() * jokeTopics.length)];
      const timestamp = Date.now();

      const prompt = `
        You are Chat-Tevez, a psychology-focused AI companion. The user is feeling ${mood} and said: "${userMessage}"

        Create a comprehensive, supportive response (4-7 sentences) that includes:

        1. 🌡️ Mood acknowledgment with empathy
        2. 💬 Supportive psychological guidance and perspective
        3. 😄 A UNIQUE, ORIGINAL psychology-themed joke about "${randomTopic}" that's ${jokeConfig.tone}
        4. 🧠 Encouraging insight or practical tip

        CRITICAL REQUIREMENTS:
        - Generate a COMPLETELY NEW joke about "${randomTopic}" - NOT about ladders, parties, or therapy sessions with ladders
        - Make it creative, fresh, and different from typical psychology jokes
        - Use wordplay, puns, or clever observations about "${randomTopic}"
        - Request ID: ${timestamp} (use this to ensure uniqueness)

        Guidelines:
        - Tone: ${jokeConfig.tone}
        - Themes: ${jokeConfig.themes.join(', ')} (psychology-focused)
        - Be warm, natural, and genuinely helpful
        - Include practical psychological advice
        - Make the joke feel natural and uplifting, not forced
        - Focus on mental health, emotional well-being, and psychological growth

        Response as Chat-Tevez:
      `;

      const response = await geminiConfig.generateText(prompt);

      return {
        response: response.trim(),
        mood,
        timestamp: new Date().toISOString()
      };
    } catch (error) {
      logger.error('Error generating mood-based response:', error);
      return {
        response: this.getEnhancedFallbackResponse(mood, userMessage),
        mood,
        timestamp: new Date().toISOString()
      };
    }
  }

  getEnhancedFallbackResponse(mood, userMessage) {
    const responses = {
      happy: `🌡️ I can feel your positive energy radiating through your message! It's wonderful when we're in a good headspace. 💬 Happiness is like a muscle - the more we exercise it, the stronger it gets. Try to notice what specifically is making you feel good right now and hold onto that feeling. 😄 You know what they say in psychology: "A happy person is not a person in a certain set of circumstances, but rather a person with a certain set of attitudes!" 🧠 Keep spreading those good vibes!`,

      sad: `🌡️ I hear the heaviness in your words, and I want you to know that what you're feeling is completely valid. 💬 Sadness is actually a healthy emotion that helps us process difficult experiences and connect with our deeper needs. It's okay to sit with these feelings for a while. 😄 Here's something that might bring a small smile: Why did the therapist bring tissues to every session? Because they knew that tears are just the heart's way of speaking when words aren't enough! 🧠 Remember, this feeling is temporary, and you're stronger than you know.`,

      angry: `🌡️ I can sense the frustration and intensity in what you're sharing - anger often shows up when something important to us feels threatened. 💬 Anger is actually a secondary emotion that usually covers hurt, fear, or disappointment. Take a few deep breaths and try to identify what's underneath that anger. 😄 You know what's funny? Anger is like a smoke alarm - it's really loud and annoying, but it's just trying to tell you something needs attention! 🧠 Channel that energy into understanding what you really need right now.`,

      anxious: `🌡️ I can feel the worry and tension in your message, and I want you to know that anxiety is your mind's way of trying to protect you, even if it doesn't feel helpful right now. 💬 Try the 5-4-3-2-1 grounding technique: notice 5 things you can see, 4 you can touch, 3 you can hear, 2 you can smell, and 1 you can taste. 😄 Here's a gentle reminder: Anxiety is like a rocking chair - it gives you something to do but doesn't get you anywhere! 🧠 You've handled 100% of your difficult days so far, and you'll handle this one too.`,

      lonely: `🌡️ I can feel the isolation in your words, and I want you to know that feeling lonely doesn't mean you're alone - it means you're human and wired for connection. 💬 Loneliness is actually our emotional GPS telling us we need meaningful connection. Even this conversation right now is a form of connection! 😄 You know what's beautiful? Even when we feel most alone, we're all under the same sky, breathing the same air, and sharing this human experience together! 🧠 Consider reaching out to one person today, even if it's just a simple "thinking of you" message.`
    };

    return responses[mood] || responses.sad;
  }
}

module.exports = new JokeService();
