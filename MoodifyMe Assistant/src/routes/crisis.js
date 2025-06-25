const express = require('express');
const router = express.Router();
const crisisDetection = require('../services/crisisDetection');
const crisisIntervention = require('../services/crisisIntervention');
const crisisResources = require('../services/crisisResources');
const logger = require('../utils/logger');

/**
 * Crisis Assessment Endpoint
 * Analyzes text for crisis indicators
 */
router.post('/assess', async (req, res) => {
  try {
    const { message, userId, conversationHistory = [] } = req.body;

    if (!message) {
      return res.status(400).json({
        error: 'Message is required for crisis assessment'
      });
    }

    // Perform crisis risk assessment
    const crisisAssessment = await crisisDetection.assessCrisisRisk(
      message,
      userId,
      conversationHistory
    );

    // Log assessment for monitoring
    if (crisisAssessment.riskLevel >= 6) {
      logger.warn('High-risk crisis assessment', {
        userId,
        riskLevel: crisisAssessment.riskLevel,
        crisisType: crisisAssessment.crisisType,
        timestamp: new Date().toISOString()
      });
    }

    res.json({
      success: true,
      assessment: {
        riskLevel: crisisAssessment.riskLevel,
        severity: crisisAssessment.severity,
        crisisType: crisisAssessment.crisisType,
        confidence: crisisAssessment.confidence,
        immediacy: crisisAssessment.immediacy,
        triggers: crisisAssessment.triggers,
        requiresIntervention: crisisAssessment.riskLevel >= 3
      },
      timestamp: new Date().toISOString()
    });

  } catch (error) {
    logger.error('Error in crisis assessment:', error);
    res.status(500).json({
      error: 'Crisis assessment failed',
      fallback: {
        riskLevel: 0,
        severity: 'unknown',
        requiresIntervention: false,
        message: 'Assessment system temporarily unavailable. If you are in crisis, please call 988 or 911.'
      }
    });
  }
});

/**
 * Crisis Intervention Endpoint
 * Provides intervention response based on assessment
 */
router.post('/intervene', async (req, res) => {
  try {
    const { 
      crisisAssessment, 
      userId, 
      userMessage, 
      conversationContext = {} 
    } = req.body;

    if (!crisisAssessment || !userId) {
      return res.status(400).json({
        error: 'Crisis assessment and user ID are required'
      });
    }

    // Execute crisis intervention
    const intervention = await crisisIntervention.executeIntervention(
      crisisAssessment,
      userId,
      userMessage,
      conversationContext
    );

    // Log intervention
    logger.info('Crisis intervention executed', {
      userId,
      interventionType: intervention.type,
      level: intervention.level,
      timestamp: intervention.timestamp
    });

    res.json({
      success: true,
      intervention: {
        type: intervention.type,
        level: intervention.level,
        message: intervention.message,
        actions: intervention.actions,
        resources: intervention.resources,
        copingStrategies: intervention.copingStrategies,
        followUp: intervention.followUp,
        priority: intervention.priority
      },
      timestamp: intervention.timestamp
    });

  } catch (error) {
    logger.error('Error in crisis intervention:', error);
    res.status(500).json({
      error: 'Crisis intervention failed',
      fallback: {
        type: 'EMERGENCY_FALLBACK',
        message: '🚨 If you are in immediate danger, please call 911 or 988 (Suicide & Crisis Lifeline) right away. Professional help is available 24/7.',
        resources: {
          emergency: '911',
          crisis: '988',
          text: '741741'
        }
      }
    });
  }
});

/**
 * Crisis Resources Endpoint
 * Provides location-appropriate crisis resources
 */
router.get('/resources', async (req, res) => {
  try {
    const { userId, location, type = 'crisis' } = req.query;

    let resources;
    
    switch (type) {
      case 'emergency':
        resources = await crisisResources.getEmergencyResources(userId, location);
        break;
      case 'crisis':
        resources = await crisisResources.getCrisisResources(userId, location);
        break;
      case 'support':
        resources = await crisisResources.getSupportResources(userId, location);
        break;
      case 'wellness':
        resources = await crisisResources.getWellnessResources();
        break;
      default:
        resources = await crisisResources.getCrisisResources(userId, location);
    }

    res.json({
      success: true,
      resources,
      type,
      timestamp: new Date().toISOString()
    });

  } catch (error) {
    logger.error('Error getting crisis resources:', error);
    res.status(500).json({
      error: 'Failed to retrieve crisis resources',
      fallback: {
        emergency: '911',
        crisis: '988',
        text: '741741',
        message: 'If you need immediate help, please call these numbers.'
      }
    });
  }
});

/**
 * Crisis History Endpoint
 * Gets intervention history for a user (for authorized access only)
 */
router.get('/history/:userId', async (req, res) => {
  try {
    const { userId } = req.params;
    
    // In production, add proper authentication/authorization here
    // For now, this is a basic implementation
    
    const history = crisisIntervention.getUserInterventionHistory(userId);
    
    res.json({
      success: true,
      userId,
      interventionCount: history.length,
      history: history.map(intervention => ({
        timestamp: intervention.timestamp,
        type: intervention.type,
        level: intervention.level,
        followUpCompleted: intervention.followUpCompleted || false
      })),
      timestamp: new Date().toISOString()
    });

  } catch (error) {
    logger.error('Error getting crisis history:', error);
    res.status(500).json({
      error: 'Failed to retrieve crisis history'
    });
  }
});

/**
 * Crisis Health Check Endpoint
 * Checks if crisis intervention system is operational
 */
router.get('/health', async (req, res) => {
  try {
    // Test basic functionality
    const testAssessment = await crisisDetection.assessCrisisRisk(
      'I am feeling okay today',
      'test-user',
      []
    );

    const systemStatus = {
      crisisDetection: testAssessment ? 'operational' : 'degraded',
      crisisIntervention: 'operational',
      crisisResources: 'operational',
      timestamp: new Date().toISOString()
    };

    res.json({
      success: true,
      status: 'healthy',
      components: systemStatus,
      message: 'Crisis intervention system is operational'
    });

  } catch (error) {
    logger.error('Crisis system health check failed:', error);
    res.status(503).json({
      success: false,
      status: 'unhealthy',
      error: 'Crisis intervention system experiencing issues',
      fallback: 'For immediate crisis support, call 988 or 911'
    });
  }
});

/**
 * Emergency Contact Endpoint
 * For immediate crisis situations - logs and provides emergency resources
 */
router.post('/emergency', async (req, res) => {
  try {
    const { userId, location, message } = req.body;

    // Log emergency contact
    logger.error('EMERGENCY CRISIS CONTACT', {
      userId,
      location,
      message: message ? message.substring(0, 100) + '...' : 'No message',
      timestamp: new Date().toISOString(),
      urgent: true
    });

    // Get emergency resources immediately
    const emergencyResources = await crisisResources.getEmergencyResources(userId, location);

    res.json({
      success: true,
      emergency: true,
      message: '🚨 Emergency support activated. Please contact emergency services immediately.',
      resources: emergencyResources,
      immediateActions: [
        'Call 911 if in immediate physical danger',
        'Call 988 for suicide crisis support',
        'Text HOME to 741741 for crisis text support',
        'Go to nearest emergency room',
        'Call a trusted friend or family member'
      ],
      timestamp: new Date().toISOString()
    });

  } catch (error) {
    logger.error('Emergency endpoint error:', error);
    res.status(500).json({
      success: false,
      emergency: true,
      message: '🚨 System error - Please call 911 or 988 immediately for crisis support',
      fallback: {
        emergency: '911',
        crisis: '988',
        text: '741741'
      }
    });
  }
});

module.exports = router;
