#!/usr/bin/env node

const path = require('path');
require('dotenv').config({ path: path.join(__dirname, '..', '.env') });

// Import configurations and services
const geminiConfig = require('../src/config/gemini');
const pineconeConfig = require('../src/config/database');
const langchainConfig = require('../src/config/langchain');
const vectorStoreService = require('../src/services/vectorStore');
const pdfProcessor = require('../src/services/pdfProcessor');
const logger = require('../src/utils/logger');

class VectorStoreSetup {
  constructor() {
    this.isInitialized = false;
  }

  async initialize() {
    try {
      console.log('🚀 Initializing Vector Store Setup...\n');

      // Initialize configurations
      console.log('📡 Initializing Gemini API...');
      await geminiConfig.initialize();
      console.log('✅ Gemini API initialized\n');

      console.log('🗄️  Initializing Pinecone...');
      await pineconeConfig.initialize();
      console.log('✅ Pinecone initialized\n');

      console.log('🔗 Initializing LangChain...');
      await langchainConfig.initialize();
      console.log('✅ LangChain initialized\n');

      console.log('📚 Initializing Vector Store Service...');
      await vectorStoreService.initialize();
      console.log('✅ Vector Store Service initialized\n');

      this.isInitialized = true;
      logger.info('Vector store setup initialized successfully');
    } catch (error) {
      logger.error('Failed to initialize vector store setup:', error);
      throw error;
    }
  }

  async checkExistingData() {
    try {
      console.log('🔍 Checking existing data in vector store...');
      const stats = await vectorStoreService.getDocumentStats();
      
      console.log(`📊 Current vector store stats:`);
      console.log(`   - Total vectors: ${stats.totalVectors}`);
      console.log(`   - Dimension: ${stats.dimension}`);
      console.log(`   - Index fullness: ${(stats.indexFullness * 100).toFixed(2)}%\n`);
      
      return stats.totalVectors > 0;
    } catch (error) {
      logger.error('Error checking existing data:', error);
      return false;
    }
  }

  async processPDFs() {
    try {
      console.log('📖 Processing PDF files...');
      
      // Get available PDFs
      const availablePDFs = await pdfProcessor.getAvailablePDFs();
      console.log(`📚 Found ${availablePDFs.length} PDF files:`);
      availablePDFs.forEach(pdf => console.log(`   - ${pdf}`));
      console.log();

      if (availablePDFs.length === 0) {
        console.log('⚠️  No PDF files found in the pdfs directory');
        return [];
      }

      // Process all PDFs
      console.log('🔄 Processing PDFs and creating document chunks...');
      const documents = await pdfProcessor.processPDFsInDirectory();
      
      console.log(`✅ Successfully processed ${documents.length} document chunks\n`);
      
      // Show breakdown by source
      const sourceBreakdown = {};
      documents.forEach(doc => {
        const source = doc.metadata.source;
        sourceBreakdown[source] = (sourceBreakdown[source] || 0) + 1;
      });
      
      console.log('📋 Document breakdown by source:');
      Object.entries(sourceBreakdown).forEach(([source, count]) => {
        console.log(`   - ${source}: ${count} chunks`);
      });
      console.log();
      
      return documents;
    } catch (error) {
      logger.error('Error processing PDFs:', error);
      throw error;
    }
  }

  async addDocumentsToVectorStore(documents) {
    try {
      console.log('🔄 Adding documents to vector store...');
      console.log('⏳ This may take several minutes depending on the number of documents...\n');
      
      const startTime = Date.now();
      const totalAdded = await vectorStoreService.addDocuments(documents);
      const endTime = Date.now();
      const duration = ((endTime - startTime) / 1000).toFixed(2);
      
      console.log(`✅ Successfully added ${totalAdded} documents to vector store`);
      console.log(`⏱️  Processing time: ${duration} seconds\n`);
      
      return totalAdded;
    } catch (error) {
      logger.error('Error adding documents to vector store:', error);
      throw error;
    }
  }

  async verifySetup() {
    try {
      console.log('🔍 Verifying vector store setup...');
      
      // Check final stats
      const stats = await vectorStoreService.getDocumentStats();
      console.log(`📊 Final vector store stats:`);
      console.log(`   - Total vectors: ${stats.totalVectors}`);
      console.log(`   - Dimension: ${stats.dimension}`);
      console.log(`   - Index fullness: ${(stats.indexFullness * 100).toFixed(2)}%\n`);
      
      // Test similarity search
      console.log('🧪 Testing similarity search...');
      const testQuery = 'happiness and joy';
      const results = await vectorStoreService.similaritySearch(testQuery, { k: 2 });
      
      console.log(`🔍 Test query: "${testQuery}"`);
      console.log(`📝 Found ${results.length} relevant documents:`);
      results.forEach((result, index) => {
        console.log(`   ${index + 1}. Source: ${result.metadata.source} (Score: ${result.score.toFixed(3)})`);
        console.log(`      Preview: ${result.content.substring(0, 100)}...\n`);
      });
      
      return true;
    } catch (error) {
      logger.error('Error verifying setup:', error);
      return false;
    }
  }

  async run() {
    try {
      console.log('🎯 Starting Vector Store Setup Process\n');
      console.log('=' .repeat(50) + '\n');
      
      // Initialize
      await this.initialize();
      
      // Check if data already exists
      const hasExistingData = await this.checkExistingData();
      
      if (hasExistingData) {
        console.log('⚠️  Vector store already contains data.');
        console.log('🔄 To rebuild, delete the existing index and run this script again.\n');
        
        // Still verify the setup works
        await this.verifySetup();
        console.log('✅ Vector store setup verification completed successfully!');
        return;
      }
      
      // Process PDFs
      const documents = await this.processPDFs();
      
      if (documents.length === 0) {
        console.log('❌ No documents to process. Please add PDF files to the pdfs directory.');
        return;
      }
      
      // Add to vector store
      await this.addDocumentsToVectorStore(documents);
      
      // Verify setup
      const verified = await this.verifySetup();
      
      if (verified) {
        console.log('🎉 Vector store setup completed successfully!');
        console.log('🚀 You can now start the chatbot server with: npm start');
      } else {
        console.log('❌ Vector store setup verification failed');
      }
      
    } catch (error) {
      console.error('❌ Vector store setup failed:', error.message);
      logger.error('Vector store setup failed:', error);
      process.exit(1);
    }
  }
}

// Run the setup if this script is executed directly
if (require.main === module) {
  const setup = new VectorStoreSetup();
  setup.run().then(() => {
    console.log('\n' + '=' .repeat(50));
    console.log('🏁 Setup process completed');
    process.exit(0);
  }).catch(error => {
    console.error('Setup failed:', error);
    process.exit(1);
  });
}

module.exports = VectorStoreSetup;
