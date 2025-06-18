const { GoogleGenerativeAIEmbeddings } = require('@langchain/google-genai');
const { PineconeStore } = require('@langchain/pinecone');
const { RecursiveCharacterTextSplitter } = require('@langchain/textsplitters');
const { ConversationSummaryBufferMemory } = require('langchain/memory');
const { ConversationalRetrievalQAChain } = require('langchain/chains');
const geminiConfig = require('./gemini');
const pineconeConfig = require('./database');
const logger = require('../utils/logger');
require('dotenv').config();

class LangChainConfig {
  constructor() {
    this.embeddings = null;
    this.vectorStore = null;
    this.textSplitter = null;
    this.memory = null;
    this.qaChain = null;
  }

  async initialize() {
    try {
      // Initialize embeddings
      this.embeddings = new GoogleGenerativeAIEmbeddings({
        apiKey: process.env.GEMINI_API_KEY,
        modelName: 'embedding-001',
      });

      // Initialize text splitter
      this.textSplitter = new RecursiveCharacterTextSplitter({
        chunkSize: parseInt(process.env.CHUNK_SIZE) || 1000,
        chunkOverlap: parseInt(process.env.CHUNK_OVERLAP) || 200,
      });

      // Initialize vector store
      const pineconeIndex = pineconeConfig.getIndex();
      this.vectorStore = await PineconeStore.fromExistingIndex(
        this.embeddings,
        { pineconeIndex }
      );

      // Initialize conversation memory
      this.memory = new ConversationSummaryBufferMemory({
        llm: geminiConfig.getChatModel(),
        maxTokenLimit: 2000,
        returnMessages: true,
        memoryKey: 'chat_history',
        inputKey: 'question',
        outputKey: 'text',
      });

      // Initialize QA chain
      this.qaChain = ConversationalRetrievalQAChain.fromLLM(
        geminiConfig.getChatModel(),
        this.vectorStore.asRetriever({
          searchType: 'similarity',
          searchKwargs: { k: 4 },
        }),
        {
          memory: this.memory,
          returnSourceDocuments: true,
          questionGeneratorChainOptions: {
            llm: geminiConfig.getChatModel(),
          },
        }
      );

      logger.info('LangChain configuration initialized successfully');
      return true;
    } catch (error) {
      logger.error('Failed to initialize LangChain:', error);
      throw error;
    }
  }

  getEmbeddings() {
    if (!this.embeddings) {
      throw new Error('Embeddings not initialized. Call initialize() first.');
    }
    return this.embeddings;
  }

  getVectorStore() {
    if (!this.vectorStore) {
      throw new Error('Vector store not initialized. Call initialize() first.');
    }
    return this.vectorStore;
  }

  getTextSplitter() {
    if (!this.textSplitter) {
      throw new Error('Text splitter not initialized. Call initialize() first.');
    }
    return this.textSplitter;
  }

  getMemory() {
    if (!this.memory) {
      throw new Error('Memory not initialized. Call initialize() first.');
    }
    return this.memory;
  }

  getQAChain() {
    if (!this.qaChain) {
      throw new Error('QA chain not initialized. Call initialize() first.');
    }
    return this.qaChain;
  }

  async clearMemory() {
    if (this.memory) {
      await this.memory.clear();
    }
  }
}

module.exports = new LangChainConfig();
