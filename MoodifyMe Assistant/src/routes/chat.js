const express = require('express');
const chatService = require('../services/chatService');
const jokeService = require('../services/jokeService');
const moodAnalyzer = require('../services/moodAnalyzer');
const logger = require('../utils/logger');

const router = express.Router();

// Chat endpoint
router.post('/message', async (req, res) => {
  try {
    const { message, conversationId, requestJoke = false } = req.body;
    
    if (!message || typeof message !== 'string' || message.trim().length === 0) {
      return res.status(400).json({
        error: 'Message is required and must be a non-empty string'
      });
    }

    const result = await chatService.processMessage(
      message.trim(), 
      conversationId, 
      { requestJoke }
    );

    res.json({
      success: true,
      data: result
    });
  } catch (error) {
    logger.error('Error in chat message endpoint:', error);
    res.status(500).json({
      error: 'Internal server error',
      message: 'Failed to process message'
    });
  }
});

// Get conversation
router.get('/conversation/:id', async (req, res) => {
  try {
    const { id } = req.params;
    const conversation = chatService.getConversation(id);
    
    if (!conversation) {
      return res.status(404).json({
        error: 'Conversation not found'
      });
    }

    res.json({
      success: true,
      data: conversation
    });
  } catch (error) {
    logger.error('Error getting conversation:', error);
    res.status(500).json({
      error: 'Internal server error'
    });
  }
});

// Get all conversations
router.get('/conversations', async (req, res) => {
  try {
    const conversations = chatService.getAllConversations();
    
    res.json({
      success: true,
      data: conversations
    });
  } catch (error) {
    logger.error('Error getting conversations:', error);
    res.status(500).json({
      error: 'Internal server error'
    });
  }
});

// Delete conversation
router.delete('/conversation/:id', async (req, res) => {
  try {
    const { id } = req.params;
    const deleted = chatService.deleteConversation(id);
    
    if (!deleted) {
      return res.status(404).json({
        error: 'Conversation not found'
      });
    }

    res.json({
      success: true,
      message: 'Conversation deleted successfully'
    });
  } catch (error) {
    logger.error('Error deleting conversation:', error);
    res.status(500).json({
      error: 'Internal server error'
    });
  }
});

// Clear conversation memory
router.post('/conversation/:id/clear', async (req, res) => {
  try {
    const { id } = req.params;
    const cleared = await chatService.clearConversationMemory(id);
    
    if (!cleared) {
      return res.status(404).json({
        error: 'Conversation not found'
      });
    }

    res.json({
      success: true,
      message: 'Conversation memory cleared successfully'
    });
  } catch (error) {
    logger.error('Error clearing conversation memory:', error);
    res.status(500).json({
      error: 'Internal server error'
    });
  }
});

// Analyze mood endpoint
router.post('/mood', async (req, res) => {
  try {
    const { text } = req.body;
    
    if (!text || typeof text !== 'string' || text.trim().length === 0) {
      return res.status(400).json({
        error: 'Text is required and must be a non-empty string'
      });
    }

    const moodAnalysis = await moodAnalyzer.analyzeMood(text.trim());
    
    res.json({
      success: true,
      data: moodAnalysis
    });
  } catch (error) {
    logger.error('Error analyzing mood:', error);
    res.status(500).json({
      error: 'Internal server error'
    });
  }
});

// Generate joke endpoint
router.post('/joke', async (req, res) => {
  try {
    const { mood = 'neutral', context = '' } = req.body;
    
    const joke = await jokeService.generateJoke(mood, context);
    
    res.json({
      success: true,
      data: joke
    });
  } catch (error) {
    logger.error('Error generating joke:', error);
    res.status(500).json({
      error: 'Internal server error'
    });
  }
});

// Generate multiple jokes endpoint
router.post('/jokes', async (req, res) => {
  try {
    const { mood = 'neutral', count = 3 } = req.body;
    
    if (count > 10) {
      return res.status(400).json({
        error: 'Maximum 10 jokes can be generated at once'
      });
    }
    
    const jokes = await jokeService.generateMultipleJokes(mood, count);
    
    res.json({
      success: true,
      data: jokes
    });
  } catch (error) {
    logger.error('Error generating multiple jokes:', error);
    res.status(500).json({
      error: 'Internal server error'
    });
  }
});

module.exports = router;
