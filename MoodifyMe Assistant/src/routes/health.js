const express = require('express');
const vectorStoreService = require('../services/vectorStore');
const pdfProcessor = require('../services/pdfProcessor');
const logger = require('../utils/logger');

const router = express.Router();

// Health check endpoint
router.get('/', async (req, res) => {
  try {
    const health = {
      status: 'healthy',
      timestamp: new Date().toISOString(),
      uptime: process.uptime(),
      memory: process.memoryUsage(),
      version: process.env.npm_package_version || '1.0.0'
    };

    res.json(health);
  } catch (error) {
    logger.error('Health check failed:', error);
    res.status(500).json({
      status: 'unhealthy',
      timestamp: new Date().toISOString(),
      error: error.message
    });
  }
});

// Detailed system status
router.get('/status', async (req, res) => {
  try {
    const [vectorStats, availablePDFs] = await Promise.all([
      vectorStoreService.getDocumentStats(),
      pdfProcessor.getAvailablePDFs()
    ]);

    const status = {
      status: 'healthy',
      timestamp: new Date().toISOString(),
      services: {
        vectorStore: {
          status: 'connected',
          totalVectors: vectorStats.totalVectors,
          dimension: vectorStats.dimension,
          indexFullness: vectorStats.indexFullness
        },
        pdfProcessor: {
          status: 'available',
          availablePDFs: availablePDFs.length,
          pdfs: availablePDFs
        },
        gemini: {
          status: 'connected'
        },
        pinecone: {
          status: 'connected'
        }
      },
      environment: {
        nodeVersion: process.version,
        platform: process.platform,
        arch: process.arch,
        uptime: process.uptime(),
        memory: process.memoryUsage()
      }
    };

    res.json(status);
  } catch (error) {
    logger.error('Status check failed:', error);
    res.status(500).json({
      status: 'unhealthy',
      timestamp: new Date().toISOString(),
      error: error.message
    });
  }
});

// Vector store statistics
router.get('/vector-stats', async (req, res) => {
  try {
    const stats = await vectorStoreService.getDocumentStats();
    
    res.json({
      success: true,
      data: stats
    });
  } catch (error) {
    logger.error('Error getting vector stats:', error);
    res.status(500).json({
      error: 'Failed to get vector store statistics'
    });
  }
});

// Available PDFs
router.get('/pdfs', async (req, res) => {
  try {
    const pdfs = await pdfProcessor.getAvailablePDFs();
    
    res.json({
      success: true,
      data: {
        count: pdfs.length,
        pdfs: pdfs
      }
    });
  } catch (error) {
    logger.error('Error getting available PDFs:', error);
    res.status(500).json({
      error: 'Failed to get available PDFs'
    });
  }
});

module.exports = router;
