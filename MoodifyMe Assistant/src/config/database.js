const { Pinecone } = require('@pinecone-database/pinecone');
const logger = require('../utils/logger');
require('dotenv').config();

class PineconeConfig {
  constructor() {
    this.apiKey = process.env.PINECONE_API_KEY;
    this.indexName = process.env.PINECONE_INDEX_NAME || 'mood-chatbot-index';
    
    if (!this.apiKey) {
      throw new Error('PINECONE_API_KEY is required');
    }
    
    this.client = null;
    this.index = null;
  }

  async initialize() {
    try {
      this.client = new Pinecone({
        apiKey: this.apiKey,
      });
      
      // Check if index exists, create if it doesn't
      await this.ensureIndexExists();
      
      this.index = this.client.index(this.indexName);
      logger.info(`Pinecone initialized with index: ${this.indexName}`);
      return true;
    } catch (error) {
      logger.error('Failed to initialize Pinecone:', error);
      throw error;
    }
  }

  async ensureIndexExists() {
    try {
      const indexList = await this.client.listIndexes();
      const indexExists = indexList.indexes?.some(index => index.name === this.indexName);

      if (!indexExists) {
        logger.info(`Creating Pinecone index: ${this.indexName}`);
        await this.client.createIndex({
          name: this.indexName,
          dimension: 768, // Dimension for Google Generative AI embeddings
          metric: 'cosine',
          spec: {
            serverless: {
              cloud: 'aws',
              region: 'us-east-1'
            }
          }
        });

        // Wait for index to be ready
        await this.waitForIndexReady();
      }
    } catch (error) {
      logger.error('Error ensuring index exists:', error);
      throw error;
    }
  }

  async waitForIndexReady() {
    const maxAttempts = 30;
    let attempts = 0;
    
    while (attempts < maxAttempts) {
      try {
        const indexStats = await this.client.describeIndex(this.indexName);
        if (indexStats.status?.ready) {
          logger.info('Pinecone index is ready');
          return;
        }
        
        logger.info('Waiting for Pinecone index to be ready...');
        await new Promise(resolve => setTimeout(resolve, 2000));
        attempts++;
      } catch (error) {
        logger.error('Error checking index status:', error);
        attempts++;
      }
    }
    
    throw new Error('Timeout waiting for Pinecone index to be ready');
  }

  getIndex() {
    if (!this.index) {
      throw new Error('Pinecone index not initialized. Call initialize() first.');
    }
    return this.index;
  }

  getClient() {
    if (!this.client) {
      throw new Error('Pinecone client not initialized. Call initialize() first.');
    }
    return this.client;
  }
}

module.exports = new PineconeConfig();
