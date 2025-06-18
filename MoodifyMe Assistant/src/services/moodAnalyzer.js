const natural = require('natural');
const Sentiment = require('sentiment');
const geminiConfig = require('../config/gemini');
const logger = require('../utils/logger');

class MoodAnalyzer {
  constructor() {
    this.sentiment = new Sentiment();
    this.tokenizer = new natural.WordTokenizer();
    
    // Mood keywords mapping
    this.moodKeywords = {
      happy: ['happy', 'joy', 'excited', 'cheerful', 'delighted', 'elated', 'glad', 'pleased'],
      sad: ['sad', 'depressed', 'down', 'melancholy', 'gloomy', 'sorrowful', 'unhappy', 'blue'],
      angry: ['angry', 'mad', 'furious', 'irritated', 'annoyed', 'frustrated', 'rage', 'upset'],
      anxious: ['anxious', 'worried', 'nervous', 'stressed', 'concerned', 'uneasy', 'tense', 'fearful'],
      calm: ['calm', 'peaceful', 'relaxed', 'serene', 'tranquil', 'composed', 'zen', 'balanced'],
      confused: ['confused', 'lost', 'uncertain', 'puzzled', 'bewildered', 'perplexed', 'unclear'],
      lonely: ['lonely', 'isolated', 'alone', 'solitary', 'abandoned', 'disconnected', 'empty'],
      motivated: ['motivated', 'inspired', 'determined', 'driven', 'ambitious', 'energetic', 'focused'],
      tired: ['tired', 'exhausted', 'weary', 'drained', 'fatigued', 'sleepy', 'worn out'],
      grateful: ['grateful', 'thankful', 'appreciative', 'blessed', 'fortunate', 'content']
    };
  }

  async analyzeMood(text) {
    try {
      // Basic sentiment analysis
      const sentimentResult = this.sentiment.analyze(text);
      
      // Keyword-based mood detection
      const keywordMood = this.detectMoodFromKeywords(text);
      
      // Advanced mood analysis using Gemini
      const aiMood = await this.analyzeWithAI(text);
      
      // Combine results
      const combinedMood = this.combineMoodAnalysis(sentimentResult, keywordMood, aiMood);
      
      logger.info(`Mood analysis completed for text: "${text.substring(0, 50)}..."`);
      return combinedMood;
    } catch (error) {
      logger.error('Error analyzing mood:', error);
      // Fallback to basic sentiment analysis
      return this.fallbackMoodAnalysis(text);
    }
  }

  detectMoodFromKeywords(text) {
    const tokens = this.tokenizer.tokenize(text.toLowerCase());
    const moodScores = {};
    
    // Initialize mood scores
    Object.keys(this.moodKeywords).forEach(mood => {
      moodScores[mood] = 0;
    });
    
    // Count keyword matches
    tokens.forEach(token => {
      Object.entries(this.moodKeywords).forEach(([mood, keywords]) => {
        if (keywords.includes(token)) {
          moodScores[mood]++;
        }
      });
    });
    
    // Find the mood with highest score
    const topMood = Object.entries(moodScores)
      .sort(([,a], [,b]) => b - a)[0];
    
    return {
      mood: topMood[0],
      confidence: topMood[1] > 0 ? Math.min(topMood[1] / tokens.length * 10, 1) : 0,
      scores: moodScores
    };
  }

  async analyzeWithAI(text) {
    try {
      const prompt = `
        You are a specialized psychological AI assistant focused on mood and emotional analysis.

        Analyze the emotional tone and mood of the following text from a psychological perspective.
        Consider psychological indicators, emotional language, and mental health context.

        Respond with a JSON object containing:
        - mood: one of [happy, sad, angry, anxious, calm, confused, lonely, motivated, tired, grateful, neutral]
        - confidence: a number between 0 and 1
        - reasoning: brief psychological explanation of the mood indicators

        Text: "${text}"

        Response (JSON only):
      `;
      
      const response = await geminiConfig.generateText(prompt);
      
      // Try to parse JSON response
      try {
        const cleanResponse = response.replace(/```json|```/g, '').trim();
        return JSON.parse(cleanResponse);
      } catch (parseError) {
        logger.warn('Could not parse AI mood analysis response as JSON');
        return { mood: 'neutral', confidence: 0.5, reasoning: 'AI analysis failed' };
      }
    } catch (error) {
      logger.error('Error in AI mood analysis:', error);
      return { mood: 'neutral', confidence: 0.5, reasoning: 'AI analysis failed' };
    }
  }

  combineMoodAnalysis(sentimentResult, keywordMood, aiMood) {
    const confidenceThreshold = parseFloat(process.env.MOOD_CONFIDENCE_THRESHOLD) || 0.6;
    
    // Determine primary mood based on highest confidence
    let primaryMood = 'neutral';
    let primaryConfidence = 0;
    
    // Check AI analysis first (usually most accurate)
    if (aiMood.confidence > confidenceThreshold) {
      primaryMood = aiMood.mood;
      primaryConfidence = aiMood.confidence;
    }
    // Then check keyword analysis
    else if (keywordMood.confidence > confidenceThreshold) {
      primaryMood = keywordMood.mood;
      primaryConfidence = keywordMood.confidence;
    }
    // Fallback to sentiment-based mood
    else {
      if (sentimentResult.score > 2) {
        primaryMood = 'happy';
        primaryConfidence = Math.min(sentimentResult.score / 5, 1);
      } else if (sentimentResult.score < -2) {
        primaryMood = 'sad';
        primaryConfidence = Math.min(Math.abs(sentimentResult.score) / 5, 1);
      } else {
        primaryMood = 'neutral';
        primaryConfidence = 0.5;
      }
    }
    
    return {
      mood: primaryMood,
      confidence: primaryConfidence,
      sentiment: {
        score: sentimentResult.score,
        comparative: sentimentResult.comparative,
        positive: sentimentResult.positive,
        negative: sentimentResult.negative
      },
      keywordAnalysis: keywordMood,
      aiAnalysis: aiMood,
      timestamp: new Date().toISOString()
    };
  }

  fallbackMoodAnalysis(text) {
    const sentimentResult = this.sentiment.analyze(text);
    
    let mood = 'neutral';
    if (sentimentResult.score > 2) mood = 'happy';
    else if (sentimentResult.score < -2) mood = 'sad';
    
    return {
      mood,
      confidence: 0.5,
      sentiment: sentimentResult,
      keywordAnalysis: { mood: 'neutral', confidence: 0 },
      aiAnalysis: { mood: 'neutral', confidence: 0 },
      timestamp: new Date().toISOString()
    };
  }

  getMoodCategory(mood) {
    const moodCategories = {
      positive: ['happy', 'calm', 'motivated', 'grateful'],
      negative: ['sad', 'angry', 'anxious', 'lonely', 'tired'],
      neutral: ['confused', 'neutral']
    };
    
    for (const [category, moods] of Object.entries(moodCategories)) {
      if (moods.includes(mood)) {
        return category;
      }
    }
    return 'neutral';
  }
}

module.exports = new MoodAnalyzer();
