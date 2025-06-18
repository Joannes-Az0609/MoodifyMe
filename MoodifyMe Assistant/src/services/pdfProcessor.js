const fs = require('fs').promises;
const path = require('path');
const pdf = require('pdf-parse');
const { Document } = require('@langchain/core/documents');
const langchainConfig = require('../config/langchain');
const logger = require('../utils/logger');

class PDFProcessor {
  constructor() {
    this.pdfDirectory = path.join(process.cwd(), 'pdfs');
  }

  async processPDFsInDirectory() {
    try {
      const files = await fs.readdir(this.pdfDirectory);
      const pdfFiles = files.filter(file => file.toLowerCase().endsWith('.pdf'));
      
      logger.info(`Found ${pdfFiles.length} PDF files to process`);
      
      const allDocuments = [];
      
      for (const pdfFile of pdfFiles) {
        const filePath = path.join(this.pdfDirectory, pdfFile);
        const documents = await this.processPDF(filePath);
        allDocuments.push(...documents);
        logger.info(`Processed ${pdfFile}: ${documents.length} chunks created`);
      }
      
      return allDocuments;
    } catch (error) {
      logger.error('Error processing PDFs in directory:', error);
      throw error;
    }
  }

  async processPDF(filePath) {
    try {
      const buffer = await fs.readFile(filePath);
      const data = await pdf(buffer);
      
      const fileName = path.basename(filePath, '.pdf');
      const textSplitter = langchainConfig.getTextSplitter();
      
      // Split the text into chunks
      const chunks = await textSplitter.splitText(data.text);
      
      // Create Document objects with metadata
      const documents = chunks.map((chunk, index) => {
        return new Document({
          pageContent: chunk,
          metadata: {
            source: fileName,
            chunk: index,
            totalChunks: chunks.length,
            type: 'pdf',
            category: this.categorizeContent(fileName),
          },
        });
      });
      
      return documents;
    } catch (error) {
      logger.error(`Error processing PDF ${filePath}:`, error);
      throw error;
    }
  }

  categorizeContent(fileName) {
    const lowerFileName = fileName.toLowerCase();
    
    if (lowerFileName.includes('happiness') || lowerFileName.includes('joy')) {
      return 'happiness';
    } else if (lowerFileName.includes('stoic') || lowerFileName.includes('philosophy')) {
      return 'philosophy';
    } else if (lowerFileName.includes('meaning') || lowerFileName.includes('purpose')) {
      return 'meaning';
    } else if (lowerFileName.includes('mindfulness') || lowerFileName.includes('meditation')) {
      return 'mindfulness';
    } else if (lowerFileName.includes('kafka') || lowerFileName.includes('letters')) {
      return 'literature';
    } else {
      return 'general';
    }
  }

  async extractTextFromPDF(filePath) {
    try {
      const buffer = await fs.readFile(filePath);
      const data = await pdf(buffer);
      return data.text;
    } catch (error) {
      logger.error(`Error extracting text from PDF ${filePath}:`, error);
      throw error;
    }
  }

  async getAvailablePDFs() {
    try {
      const files = await fs.readdir(this.pdfDirectory);
      return files.filter(file => file.toLowerCase().endsWith('.pdf'));
    } catch (error) {
      logger.error('Error getting available PDFs:', error);
      throw error;
    }
  }
}

module.exports = new PDFProcessor();
