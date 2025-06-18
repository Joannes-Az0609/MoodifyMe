const { GoogleGenerativeAI } = require('@google/generative-ai');
const { ChatGoogleGenerativeAI } = require('@langchain/google-genai');
const logger = require('../utils/logger');
require('dotenv').config();

class GeminiConfig {
  constructor() {
    this.apiKey = process.env.GEMINI_API_KEY;
    if (!this.apiKey) {
      throw new Error('GEMINI_API_KEY is required');
    }

    this.genAI = null;
    this.model = null;
    this.chatModel = null;

    // Rate limiting
    this.requestTimes = [];
    this.maxRequestsPerMinute = 10; // Very conservative to prevent quota issues
    this.rateLimitWindow = 60000; // 1 minute

    // Simple response cache to avoid repeated API calls
    this.responseCache = new Map();
    this.cacheMaxSize = 50;
    this.cacheExpiry = 300000; // 5 minutes
  }

  async initialize() {
    try {
      // Initialize the GoogleGenerativeAI instance
      this.genAI = new GoogleGenerativeAI(this.apiKey);

      // Initialize the Gemini model for direct API calls - using stable model
      this.model = this.genAI.getGenerativeModel({
        model: 'gemini-1.5-flash-latest',
        generationConfig: {
          temperature: 0.7,
          topK: 40,
          topP: 0.95,
          maxOutputTokens: 1024,
        }
      });

      // Initialize LangChain Gemini model for RAG
      this.chatModel = new ChatGoogleGenerativeAI({
        apiKey: this.apiKey,
        modelName: 'gemini-1.5-flash-latest',
        temperature: 0.7,
        maxOutputTokens: 1024,
      });

      logger.info('Gemini API initialized successfully');
      return true;
    } catch (error) {
      logger.error('Failed to initialize Gemini API:', error);
      throw error;
    }
  }

  getModel() {
    if (!this.model) {
      throw new Error('Gemini model not initialized. Call initialize() first.');
    }
    return this.model;
  }

  getChatModel() {
    if (!this.chatModel) {
      throw new Error('Gemini chat model not initialized. Call initialize() first.');
    }
    return this.chatModel;
  }

  async waitForRateLimit() {
    const now = Date.now();

    // Remove old requests outside the window
    this.requestTimes = this.requestTimes.filter(time => now - time < this.rateLimitWindow);

    // Check if we're at the limit
    if (this.requestTimes.length >= this.maxRequestsPerMinute) {
      const oldestRequest = this.requestTimes[0];
      const waitTime = this.rateLimitWindow - (now - oldestRequest) + 5000; // Add 5 second buffer for safety

      if (waitTime > 0) {
        logger.warn(`Rate limit reached (${this.requestTimes.length}/${this.maxRequestsPerMinute}). Waiting ${Math.ceil(waitTime/1000)}s before next request.`);
        await new Promise(resolve => setTimeout(resolve, waitTime));
        return this.waitForRateLimit(); // Recursive call to check again
      }
    }

    // Record this request
    this.requestTimes.push(now);
  }

  async generateText(prompt, options = {}) {
    try {
      // Check cache first for faster responses
      const cacheKey = this.getCacheKey(prompt);
      const cached = this.getFromCache(cacheKey);
      if (cached && !options.skipCache) {
        logger.debug('Using cached response');
        return cached;
      }

      // Wait for rate limit if necessary
      await this.waitForRateLimit();

      const model = this.getModel();
      const result = await model.generateContent(prompt);
      const response = await result.response;
      const text = response.text();

      // Cache the response
      this.setCache(cacheKey, text);

      return text;
    } catch (error) {
      logger.error('Error generating text with Gemini:', error);

      // Enhanced error handling with specific fallback messages
      if (error.status === 429) {
        const waitTime = error.errorDetails?.[2]?.retryDelay || '60s';
        throw new Error(`Gemini API quota exceeded. Please wait ${waitTime} and try again, or consider upgrading your API plan.`);
      }

      if (error.status === 404) {
        throw new Error('Gemini model not found. The API model may have been updated or deprecated.');
      }

      if (error.message?.includes('fetch failed')) {
        throw new Error('Network connection error. Please check your internet connection and try again.');
      }

      // Generic fallback
      throw new Error(`AI service temporarily unavailable: ${error.message}`);
    }
  }

  // Cache helper methods for performance optimization
  getCacheKey(prompt) {
    // Create a simple hash of the prompt for caching
    return prompt.substring(0, 100).replace(/\s+/g, ' ').trim();
  }

  getFromCache(key) {
    const cached = this.responseCache.get(key);
    if (cached && Date.now() - cached.timestamp < this.cacheExpiry) {
      return cached.response;
    }
    if (cached) {
      this.responseCache.delete(key); // Remove expired cache
    }
    return null;
  }

  setCache(key, response) {
    // Limit cache size
    if (this.responseCache.size >= this.cacheMaxSize) {
      const firstKey = this.responseCache.keys().next().value;
      this.responseCache.delete(firstKey);
    }

    this.responseCache.set(key, {
      response,
      timestamp: Date.now()
    });
  }
}

module.exports = new GeminiConfig();
