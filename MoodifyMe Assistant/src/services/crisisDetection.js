const logger = require('../utils/logger');
const geminiConfig = require('../config/gemini');

class CrisisDetectionService {
  constructor() {
    this.crisisKeywords = {
      // Immediate danger - Critical level
      suicide: [
        'kill myself', 'end my life', 'suicide', 'want to die', 'better off dead',
        'not worth living', 'end it all', 'take my own life', 'don\'t want to be here',
        'wish I was dead', 'want to disappear forever', 'planning to die',
        'goodbye forever', 'this is my last', 'won\'t be here tomorrow'
      ],
      
      // Self-harm - High level
      selfHarm: [
        'cut myself', 'hurt myself', 'self harm', 'self-harm', 'cutting',
        'burning myself', 'harm myself', 'punish myself', 'deserve pain',
        'make myself bleed', 'razor', 'blade', 'cutting tools'
      ],
      
      // Violence toward others - Critical level
      violence: [
        'hurt someone', 'kill someone', 'violent thoughts', 'want to hurt others',
        'harm others', 'make them pay', 'they deserve to die', 'planning violence',
        'get revenge', 'make them suffer', 'weapon', 'attack'
      ],
      
      // Severe depression indicators - Medium to High level
      severe: [
        'can\'t go on', 'hopeless', 'no point', 'give up', 'nothing matters',
        'no way out', 'trapped', 'can\'t escape', 'unbearable pain',
        'too much to handle', 'breaking point', 'can\'t take it anymore'
      ],
      
      // Crisis planning indicators - High level
      planning: [
        'have a plan', 'know how I\'ll do it', 'saving pills', 'wrote a note',
        'final arrangements', 'giving away', 'saying goodbye', 'last time',
        'when I\'m gone', 'after I die', 'funeral plans'
      ],
      
      // Cry for help - Medium level
      helpCry: [
        'nobody cares', 'no one understands', 'all alone', 'invisible',
        'what\'s the point', 'why bother', 'doesn\'t matter', 'waste of space',
        'burden to everyone', 'better without me', 'failed at everything'
      ]
    };

    this.contextualModifiers = {
      // Phrases that might reduce crisis severity (metaphorical usage)
      metaphorical: [
        'dying of boredom', 'killing time', 'dead tired', 'dying to know',
        'kill the mood', 'dead serious', 'dying laughing', 'kill it' // as in succeed
      ],
      
      // Phrases that increase severity (immediacy indicators)
      immediacy: [
        'right now', 'tonight', 'today', 'this moment', 'can\'t wait',
        'immediately', 'soon', 'very soon', 'any minute', 'about to'
      ],
      
      // Past tense might indicate reflection rather than current crisis
      pastTense: [
        'used to think', 'once felt', 'previously', 'in the past',
        'before', 'earlier', 'last year', 'months ago'
      ]
    };
  }

  async assessCrisisRisk(message, userId, conversationHistory = []) {
    try {
      // Multi-layer crisis assessment
      const textAnalysis = this.analyzeTextForCrisis(message);
      const contextAnalysis = this.analyzeContext(message, conversationHistory);
      const aiAnalysis = await this.getAIRiskAssessment(message, conversationHistory);
      
      // Combine all assessments
      const combinedRisk = this.combineRiskAssessments(textAnalysis, contextAnalysis, aiAnalysis);
      
      // Log crisis assessment for monitoring
      if (combinedRisk.riskLevel >= 6) {
        logger.warn(`Crisis risk detected for user ${userId}: Level ${combinedRisk.riskLevel}`, {
          userId,
          riskLevel: combinedRisk.riskLevel,
          triggers: combinedRisk.triggers,
          message: message.substring(0, 100) + '...'
        });
      }

      return combinedRisk;
    } catch (error) {
      logger.error('Error in crisis risk assessment:', error);
      
      // Fallback to basic keyword detection
      const fallbackAssessment = this.analyzeTextForCrisis(message);
      return {
        ...fallbackAssessment,
        confidence: Math.max(0.3, fallbackAssessment.confidence - 0.2), // Lower confidence due to error
        error: 'Advanced analysis unavailable, using basic detection'
      };
    }
  }

  analyzeTextForCrisis(message) {
    const lowerMessage = message.toLowerCase();
    let maxRiskLevel = 0;
    let triggers = [];
    let crisisType = null;
    let confidence = 0;

    // Check for crisis keywords
    for (const [type, keywords] of Object.entries(this.crisisKeywords)) {
      const foundKeywords = keywords.filter(keyword => 
        lowerMessage.includes(keyword.toLowerCase())
      );
      
      if (foundKeywords.length > 0) {
        triggers.push(...foundKeywords);
        
        // Assign risk levels based on crisis type
        let typeRiskLevel = this.getRiskLevelForType(type);
        
        if (typeRiskLevel > maxRiskLevel) {
          maxRiskLevel = typeRiskLevel;
          crisisType = type;
        }
        
        // Increase confidence based on number of keywords found
        confidence += foundKeywords.length * 0.2;
      }
    }

    // Apply contextual modifiers
    const modifiedRisk = this.applyContextualModifiers(lowerMessage, maxRiskLevel, confidence);

    return {
      riskLevel: modifiedRisk.riskLevel,
      confidence: Math.min(1.0, modifiedRisk.confidence),
      crisisType,
      triggers: [...new Set(triggers)], // Remove duplicates
      immediacy: this.assessImmediacy(lowerMessage),
      severity: this.categorizeSeverity(modifiedRisk.riskLevel)
    };
  }

  getRiskLevelForType(type) {
    const riskLevels = {
      suicide: 10,      // Critical - immediate intervention
      violence: 10,     // Critical - immediate intervention
      planning: 9,      // Critical - has specific plan
      selfHarm: 8,      // High - serious self-injury risk
      severe: 6,        // Medium-High - severe depression
      helpCry: 4        // Medium - cry for help
    };
    
    return riskLevels[type] || 0;
  }

  applyContextualModifiers(message, riskLevel, confidence) {
    let modifiedRisk = riskLevel;
    let modifiedConfidence = confidence;

    // Check for metaphorical usage (reduces risk)
    const hasMetaphorical = this.contextualModifiers.metaphorical.some(phrase =>
      message.includes(phrase)
    );
    if (hasMetaphorical && riskLevel < 8) {
      modifiedRisk = Math.max(0, riskLevel - 3);
      modifiedConfidence *= 0.5;
    }

    // Check for immediacy indicators (increases risk)
    const hasImmediacy = this.contextualModifiers.immediacy.some(phrase =>
      message.includes(phrase)
    );
    if (hasImmediacy && riskLevel >= 6) {
      modifiedRisk = Math.min(10, riskLevel + 1);
      modifiedConfidence *= 1.3;
    }

    // Check for past tense (might reduce current risk)
    const hasPastTense = this.contextualModifiers.pastTense.some(phrase =>
      message.includes(phrase)
    );
    if (hasPastTense && riskLevel < 9) {
      modifiedRisk = Math.max(0, riskLevel - 1);
      modifiedConfidence *= 0.8;
    }

    return {
      riskLevel: modifiedRisk,
      confidence: modifiedConfidence
    };
  }

  analyzeContext(message, conversationHistory) {
    // Analyze conversation patterns for escalating crisis
    const recentMessages = conversationHistory.slice(-5);
    let escalationScore = 0;
    let patternRisk = 0;

    if (recentMessages.length > 0) {
      // Check for escalating negative mood
      const moodProgression = recentMessages
        .filter(msg => msg.mood)
        .map(msg => msg.mood.mood);
      
      const negativeProgression = this.detectNegativeMoodProgression(moodProgression);
      if (negativeProgression) {
        escalationScore += 2;
      }

      // Check for repeated crisis themes
      const crisisThemes = recentMessages.filter(msg => 
        msg.content && this.analyzeTextForCrisis(msg.content).riskLevel > 3
      );
      
      if (crisisThemes.length >= 2) {
        patternRisk += crisisThemes.length;
      }
    }

    return {
      escalationScore,
      patternRisk,
      conversationLength: conversationHistory.length,
      recentCrisisMessages: recentMessages.filter(msg => 
        msg.content && this.analyzeTextForCrisis(msg.content).riskLevel > 5
      ).length
    };
  }

  async getAIRiskAssessment(message, conversationHistory) {
    try {
      const recentContext = conversationHistory
        .slice(-3)
        .map(msg => `${msg.role}: ${msg.content}`)
        .join('\n');

      const prompt = `
        You are a crisis assessment AI. Analyze this message for suicide risk, self-harm, or mental health crisis indicators.
        
        Current message: "${message}"
        Recent conversation context: ${recentContext || 'No previous context'}
        
        Assess the crisis risk level on a scale of 0-10:
        - 0-2: No crisis indicators
        - 3-4: Mild distress, monitor
        - 5-6: Moderate concern, supportive intervention
        - 7-8: High risk, immediate support needed
        - 9-10: Critical risk, emergency intervention required
        
        Consider:
        - Direct statements about self-harm or suicide
        - Hopelessness and despair levels
        - Planning or method mentions
        - Immediacy indicators
        - Context and metaphorical vs literal language
        
        Respond with only a JSON object:
        {
          "riskLevel": number (0-10),
          "confidence": number (0-1),
          "reasoning": "brief explanation",
          "immediacy": "low|medium|high",
          "recommendedAction": "monitor|support|immediate_intervention"
        }
      `;

      const response = await geminiConfig.generateText(prompt);
      
      try {
        const aiAssessment = JSON.parse(response.trim());
        return {
          riskLevel: Math.min(10, Math.max(0, aiAssessment.riskLevel || 0)),
          confidence: Math.min(1, Math.max(0, aiAssessment.confidence || 0)),
          reasoning: aiAssessment.reasoning || 'AI analysis completed',
          immediacy: aiAssessment.immediacy || 'low',
          recommendedAction: aiAssessment.recommendedAction || 'monitor'
        };
      } catch (parseError) {
        logger.error('Error parsing AI crisis assessment:', parseError);
        return { riskLevel: 0, confidence: 0, error: 'AI parsing failed' };
      }
    } catch (error) {
      logger.error('Error getting AI risk assessment:', error);
      return { riskLevel: 0, confidence: 0, error: 'AI assessment unavailable' };
    }
  }

  combineRiskAssessments(textAnalysis, contextAnalysis, aiAnalysis) {
    // Weighted combination of different assessment methods
    const weights = {
      text: 0.4,      // Keyword-based analysis
      context: 0.2,   // Conversation pattern analysis
      ai: 0.4         // AI-powered assessment
    };

    // Calculate combined risk level
    let combinedRisk = (
      textAnalysis.riskLevel * weights.text +
      (contextAnalysis.patternRisk + contextAnalysis.escalationScore) * weights.context +
      (aiAnalysis.riskLevel || 0) * weights.ai
    );

    // Apply context modifiers
    if (contextAnalysis.recentCrisisMessages >= 2) {
      combinedRisk += 1; // Escalating pattern
    }

    // Calculate combined confidence
    const combinedConfidence = (
      textAnalysis.confidence * weights.text +
      (contextAnalysis.escalationScore > 0 ? 0.7 : 0.3) * weights.context +
      (aiAnalysis.confidence || 0) * weights.ai
    );

    return {
      riskLevel: Math.min(10, Math.round(combinedRisk)),
      confidence: Math.min(1.0, combinedConfidence),
      crisisType: textAnalysis.crisisType,
      triggers: textAnalysis.triggers,
      immediacy: this.determineOverallImmediacy(textAnalysis, aiAnalysis),
      severity: this.categorizeSeverity(combinedRisk),
      assessmentDetails: {
        textAnalysis,
        contextAnalysis,
        aiAnalysis
      }
    };
  }

  assessImmediacy(message) {
    const immediacyIndicators = this.contextualModifiers.immediacy;
    const hasImmediacy = immediacyIndicators.some(indicator => 
      message.includes(indicator)
    );
    
    if (hasImmediacy) return 'high';
    
    // Check for planning indicators
    const planningWords = ['plan', 'going to', 'will', 'soon', 'ready'];
    const hasPlanning = planningWords.some(word => message.includes(word));
    
    return hasPlanning ? 'medium' : 'low';
  }

  determineOverallImmediacy(textAnalysis, aiAnalysis) {
    const immediacyLevels = { low: 1, medium: 2, high: 3 };
    
    const textLevel = immediacyLevels[textAnalysis.immediacy] || 1;
    const aiLevel = immediacyLevels[aiAnalysis.immediacy] || 1;
    
    const maxLevel = Math.max(textLevel, aiLevel);
    
    return Object.keys(immediacyLevels).find(key => immediacyLevels[key] === maxLevel);
  }

  categorizeSeverity(riskLevel) {
    if (riskLevel >= 9) return 'critical';
    if (riskLevel >= 7) return 'high';
    if (riskLevel >= 5) return 'medium';
    if (riskLevel >= 3) return 'low';
    return 'minimal';
  }

  detectNegativeMoodProgression(moodProgression) {
    const negativeMoods = ['sad', 'angry', 'anxious', 'lonely', 'hopeless'];
    const negativeCount = moodProgression.filter(mood => 
      negativeMoods.includes(mood)
    ).length;
    
    return negativeCount >= Math.ceil(moodProgression.length * 0.6);
  }
}

module.exports = new CrisisDetectionService();
