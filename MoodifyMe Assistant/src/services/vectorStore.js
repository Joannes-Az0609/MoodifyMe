const langchainConfig = require('../config/langchain');
const pineconeConfig = require('../config/database');
const logger = require('../utils/logger');

class VectorStoreService {
  constructor() {
    this.vectorStore = null;
    this.embeddings = null;
  }

  async initialize() {
    try {
      this.vectorStore = langchainConfig.getVectorStore();
      this.embeddings = langchainConfig.getEmbeddings();
      logger.info('Vector store service initialized');
      return true;
    } catch (error) {
      logger.error('Failed to initialize vector store service:', error);
      throw error;
    }
  }

  async addDocuments(documents) {
    try {
      if (!this.vectorStore) {
        throw new Error('Vector store not initialized');
      }

      logger.info(`Adding ${documents.length} documents to vector store`);
      
      // Add documents in batches to avoid overwhelming the API
      const batchSize = 10;
      const batches = [];
      
      for (let i = 0; i < documents.length; i += batchSize) {
        batches.push(documents.slice(i, i + batchSize));
      }
      
      let totalAdded = 0;
      for (const batch of batches) {
        await this.vectorStore.addDocuments(batch);
        totalAdded += batch.length;
        logger.info(`Added batch: ${totalAdded}/${documents.length} documents`);
        
        // Small delay between batches
        await new Promise(resolve => setTimeout(resolve, 1000));
      }
      
      logger.info(`Successfully added ${totalAdded} documents to vector store`);
      return totalAdded;
    } catch (error) {
      logger.error('Error adding documents to vector store:', error);
      throw error;
    }
  }

  async similaritySearch(query, options = {}) {
    try {
      if (!this.vectorStore) {
        throw new Error('Vector store not initialized');
      }

      const {
        k = 4,
        filter = null,
        scoreThreshold = 0.7
      } = options;

      logger.info(`Performing similarity search for: "${query.substring(0, 50)}..."`);

      // Use different method based on whether filter is provided
      let results;
      if (filter && Object.keys(filter).length > 0) {
        results = await this.vectorStore.similaritySearchWithScore(query, k, filter);
      } else {
        // Use the basic similarity search without filter
        results = await this.vectorStore.similaritySearchWithScore(query, k);
      }

      // Filter by score threshold
      const filteredResults = results.filter(([doc, score]) => score >= scoreThreshold);

      logger.info(`Found ${filteredResults.length} relevant documents`);

      return filteredResults.map(([doc, score]) => ({
        document: doc,
        score,
        content: doc.pageContent,
        metadata: doc.metadata
      }));
    } catch (error) {
      logger.error('Error performing similarity search:', error);
      throw error;
    }
  }

  async searchByMood(query, mood, options = {}) {
    try {
      const moodCategories = {
        happy: ['happiness', 'joy', 'positive'],
        sad: ['sadness', 'grief', 'loss', 'meaning'],
        anxious: ['anxiety', 'worry', 'stress', 'mindfulness'],
        calm: ['peace', 'tranquility', 'meditation', 'mindfulness'],
        angry: ['anger', 'frustration', 'philosophy'],
        confused: ['clarity', 'understanding', 'philosophy'],
        lonely: ['connection', 'relationships', 'meaning'],
        motivated: ['inspiration', 'purpose', 'meaning'],
        tired: ['rest', 'energy', 'balance'],
        grateful: ['gratitude', 'appreciation', 'happiness']
      };

      const categories = moodCategories[mood] || ['general'];

      // Try with filter first, fallback to no filter if it fails
      try {
        const filter = {
          category: { $in: categories }
        };

        const searchOptions = {
          ...options,
          filter: { ...options.filter, ...filter }
        };

        return await this.similaritySearch(query, searchOptions);
      } catch (filterError) {
        logger.warn('Filter search failed, falling back to basic search:', filterError.message);
        return await this.similaritySearch(query, { ...options, filter: null });
      }
    } catch (error) {
      logger.error('Error searching by mood:', error);
      // Fallback to regular search
      return await this.similaritySearch(query, { ...options, filter: null });
    }
  }

  async getDocumentStats() {
    try {
      const index = pineconeConfig.getIndex();
      const stats = await index.describeIndexStats();
      
      return {
        totalVectors: stats.totalVectorCount || 0,
        dimension: stats.dimension || 0,
        indexFullness: stats.indexFullness || 0,
        namespaces: stats.namespaces || {}
      };
    } catch (error) {
      logger.error('Error getting document stats:', error);
      return {
        totalVectors: 0,
        dimension: 0,
        indexFullness: 0,
        namespaces: {}
      };
    }
  }

  async deleteAllDocuments() {
    try {
      const index = pineconeConfig.getIndex();
      await index.deleteAll();
      logger.info('All documents deleted from vector store');
      return true;
    } catch (error) {
      logger.error('Error deleting all documents:', error);
      throw error;
    }
  }

  async searchWithMetadata(query, metadataFilter = {}, k = 4) {
    try {
      if (!this.vectorStore) {
        throw new Error('Vector store not initialized');
      }

      const results = await this.vectorStore.similaritySearchWithScore(query, k, metadataFilter);
      
      return results.map(([doc, score]) => ({
        content: doc.pageContent,
        metadata: doc.metadata,
        score,
        relevance: this.calculateRelevance(score)
      }));
    } catch (error) {
      logger.error('Error searching with metadata:', error);
      throw error;
    }
  }

  calculateRelevance(score) {
    if (score >= 0.9) return 'very_high';
    if (score >= 0.8) return 'high';
    if (score >= 0.7) return 'medium';
    if (score >= 0.6) return 'low';
    return 'very_low';
  }

  async getRelevantContext(query, mood, maxTokens = 2000) {
    try {
      const results = await this.searchByMood(query, mood, { k: 4, scoreThreshold: 0.65 }); // Reduced results for faster performance

      let context = '';
      let tokenCount = 0;
      const sources = new Set();
      const quotes = [];

      for (const result of results) {
        const content = result.content;
        const estimatedTokens = Math.ceil(content.length / 4); // Rough token estimation

        if (tokenCount + estimatedTokens <= maxTokens) {
          // Add source to set
          sources.add(result.metadata.source);

          // Extract meaningful quotes for psychology
          const quote = this.extractMeaningfulQuote(content, result.metadata.source);
          if (quote) {
            quotes.push(quote);
          }

          context += `\n\n--- From ${result.metadata.source} ---\n${content}`;
          tokenCount += estimatedTokens;
        } else {
          break;
        }
      }

      return {
        context: context.trim(),
        sources: Array.from(sources),
        quotes: quotes.slice(0, 3), // Top 3 quotes
        tokenCount,
        relevantPassages: results.length
      };
    } catch (error) {
      logger.error('Error getting relevant context:', error);
      return {
        context: '',
        sources: [],
        quotes: [],
        tokenCount: 0,
        relevantPassages: 0
      };
    }
  }

  extractMeaningfulQuote(content, source) {
    // Extract meaningful quotes from psychological texts
    const sentences = content.split(/[.!?]+/).filter(s => s.trim().length > 20);

    // Look for sentences with psychological keywords
    const psychKeywords = ['happiness', 'meaning', 'purpose', 'emotion', 'feeling', 'mind', 'heart', 'soul', 'life', 'love', 'hope', 'peace', 'joy', 'wisdom', 'strength', 'courage', 'resilience'];

    for (const sentence of sentences) {
      const lowerSentence = sentence.toLowerCase();
      if (psychKeywords.some(keyword => lowerSentence.includes(keyword)) && sentence.length < 200) {
        return {
          text: sentence.trim(),
          source: source
        };
      }
    }

    // Fallback to first meaningful sentence
    const meaningfulSentence = sentences.find(s => s.length > 30 && s.length < 200);
    return meaningfulSentence ? {
      text: meaningfulSentence.trim(),
      source: source
    } : null;
  }
}

module.exports = new VectorStoreService();
