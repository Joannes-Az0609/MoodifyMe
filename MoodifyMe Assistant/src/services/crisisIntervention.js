const logger = require('../utils/logger');
const crisisResources = require('./crisisResources');

class CrisisInterventionService {
  constructor() {
    this.interventionHistory = new Map(); // Track interventions per user
  }

  async executeIntervention(crisisAssessment, userId, userMessage, conversationContext = {}) {
    try {
      // Log crisis intervention
      this.logCrisisIntervention(userId, crisisAssessment, userMessage);

      // Determine intervention level
      const interventionLevel = this.determineInterventionLevel(crisisAssessment);
      
      // Execute appropriate intervention
      const intervention = await this.createIntervention(
        interventionLevel, 
        crisisAssessment, 
        userId, 
        conversationContext
      );

      // Track intervention for follow-up
      this.trackIntervention(userId, intervention);

      return intervention;
    } catch (error) {
      logger.error('Error executing crisis intervention:', error);
      
      // Fallback to basic crisis response
      return this.createEmergencyFallbackIntervention(crisisAssessment);
    }
  }

  determineInterventionLevel(crisisAssessment) {
    const { riskLevel, severity, immediacy, crisisType } = crisisAssessment;

    // Critical interventions (immediate danger)
    if (riskLevel >= 9 || severity === 'critical' || 
        ['suicide', 'violence'].includes(crisisType)) {
      return 'CRITICAL';
    }

    // High-priority interventions
    if (riskLevel >= 7 || severity === 'high' || 
        crisisType === 'selfHarm' || immediacy === 'high') {
      return 'HIGH';
    }

    // Moderate interventions
    if (riskLevel >= 5 || severity === 'medium') {
      return 'MODERATE';
    }

    // Supportive interventions
    if (riskLevel >= 3 || severity === 'low') {
      return 'SUPPORTIVE';
    }

    return 'PREVENTIVE';
  }

  async createIntervention(level, crisisAssessment, userId, conversationContext) {
    const interventionStrategies = {
      CRITICAL: () => this.createCriticalIntervention(crisisAssessment, userId),
      HIGH: () => this.createHighPriorityIntervention(crisisAssessment, userId),
      MODERATE: () => this.createModerateIntervention(crisisAssessment, conversationContext),
      SUPPORTIVE: () => this.createSupportiveIntervention(crisisAssessment, conversationContext),
      PREVENTIVE: () => this.createPreventiveIntervention(crisisAssessment, conversationContext)
    };

    const intervention = await interventionStrategies[level]();
    
    return {
      ...intervention,
      level,
      timestamp: new Date().toISOString(),
      userId,
      crisisAssessment
    };
  }

  async createCriticalIntervention(crisisAssessment, userId) {
    const resources = await crisisResources.getEmergencyResources(userId);
    
    return {
      type: 'CRITICAL_EMERGENCY',
      priority: 'IMMEDIATE',
      message: this.formatCriticalMessage(crisisAssessment, resources),
      actions: [
        'DISPLAY_EMERGENCY_RESOURCES',
        'OFFER_IMMEDIATE_CONNECTION',
        'STAY_WITH_USER',
        'LOG_EMERGENCY_EVENT'
      ],
      resources: resources.immediate,
      followUp: {
        immediate: true,
        schedule: [
          { minutes: 5, type: 'safety_check' },
          { minutes: 15, type: 'resource_follow_up' },
          { hours: 1, type: 'crisis_follow_up' }
        ]
      },
      escalation: {
        required: true,
        contacts: resources.emergency
      }
    };
  }

  async createHighPriorityIntervention(crisisAssessment, userId) {
    const resources = await crisisResources.getCrisisResources(userId);
    
    return {
      type: 'HIGH_PRIORITY_CRISIS',
      priority: 'URGENT',
      message: this.formatHighPriorityMessage(crisisAssessment, resources),
      actions: [
        'DISPLAY_CRISIS_RESOURCES',
        'OFFER_PROFESSIONAL_CONNECTION',
        'PROVIDE_COPING_STRATEGIES',
        'SCHEDULE_CHECK_IN'
      ],
      resources: resources.crisis,
      followUp: {
        immediate: false,
        schedule: [
          { hours: 1, type: 'safety_check' },
          { hours: 6, type: 'resource_check' },
          { hours: 24, type: 'follow_up_assessment' }
        ]
      },
      copingStrategies: this.getCrisisSpecificCoping(crisisAssessment.crisisType)
    };
  }

  async createModerateIntervention(crisisAssessment, conversationContext) {
    const resources = await crisisResources.getSupportResources();
    
    return {
      type: 'MODERATE_SUPPORT',
      priority: 'IMPORTANT',
      message: this.formatModerateMessage(crisisAssessment, resources),
      actions: [
        'PROVIDE_SUPPORT_RESOURCES',
        'TEACH_COPING_SKILLS',
        'VALIDATE_FEELINGS',
        'OFFER_CONTINUED_SUPPORT'
      ],
      resources: resources.support,
      copingStrategies: this.getTherapeuticCoping(crisisAssessment),
      followUp: {
        schedule: [
          { hours: 4, type: 'check_in' },
          { hours: 24, type: 'progress_check' }
        ]
      }
    };
  }

  async createSupportiveIntervention(crisisAssessment, conversationContext) {
    return {
      type: 'SUPPORTIVE_GUIDANCE',
      priority: 'STANDARD',
      message: this.formatSupportiveMessage(crisisAssessment),
      actions: [
        'VALIDATE_EMOTIONS',
        'PROVIDE_PERSPECTIVE',
        'SUGGEST_RESOURCES',
        'ENCOURAGE_SELF_CARE'
      ],
      resources: await crisisResources.getWellnessResources(),
      copingStrategies: this.getPreventiveCoping(),
      followUp: {
        schedule: [
          { hours: 12, type: 'wellness_check' }
        ]
      }
    };
  }

  async createPreventiveIntervention(crisisAssessment, conversationContext) {
    return {
      type: 'PREVENTIVE_SUPPORT',
      priority: 'ROUTINE',
      message: this.formatPreventiveMessage(crisisAssessment),
      actions: [
        'ACKNOWLEDGE_FEELINGS',
        'PROVIDE_GENTLE_SUPPORT',
        'SHARE_WELLNESS_TIPS'
      ],
      resources: await crisisResources.getWellnessResources(),
      copingStrategies: this.getWellnessCoping()
    };
  }

  formatCriticalMessage(crisisAssessment, resources) {
    const { crisisType } = crisisAssessment;
    
    const messages = {
      suicide: `🚨 **IMMEDIATE CRISIS SUPPORT NEEDED** 🚨

I'm very concerned about what you've shared. Your life has value and meaning, and I want to help you get through this crisis right now.

**🆘 IMMEDIATE HELP AVAILABLE:**
• **Call 988** - National Suicide Prevention Lifeline (24/7)
• **Text HOME to 741741** - Crisis Text Line
• **Call 911** - Emergency Services

**💙 YOU ARE NOT ALONE:**
Professional crisis counselors are available RIGHT NOW to help you through this. These feelings can change with proper support.

I'm going to stay here with you. Can you please reach out to one of these resources while we talk?`,

      violence: `🚨 **IMMEDIATE PROFESSIONAL HELP NEEDED** 🚨

I'm concerned about the thoughts you're experiencing. Having thoughts about harming others requires immediate professional intervention for everyone's safety.

**🆘 IMMEDIATE ACTION NEEDED:**
• **Call 911** or go to your nearest emergency room
• **Call 988** - National Crisis Line
• **Text HOME to 741741** - Crisis Text Line

These thoughts are a sign that you need professional support right now. Please reach out immediately.`,

      selfHarm: `🚨 **CRISIS SUPPORT NEEDED** 🚨

I'm concerned about your safety and the emotional pain you're experiencing. Self-harm might feel like a way to cope, but there are safer alternatives that can help.

**🆘 IMMEDIATE RESOURCES:**
• **Call 988** - National Suicide Prevention Lifeline
• **Text HOME to 741741** - Crisis Text Line
• **Self-Injury Outreach & Support:** sioutreach.org

Your feelings are completely valid, and there are healthier ways to manage this intense pain. Professional help is available right now.`
    };

    return messages[crisisType] || messages.suicide;
  }

  formatHighPriorityMessage(crisisAssessment, resources) {
    return `💙 **CRISIS SUPPORT AVAILABLE** 💙

I can see you're going through an incredibly difficult time, and I want you to know that what you're experiencing is real and significant. You don't have to face this alone.

**🤝 IMMEDIATE SUPPORT OPTIONS:**
• **Crisis Text Line:** Text HOME to 741741
• **National Crisis Line:** 988 (24/7 support)
• **Online Crisis Chat:** Available at suicidepreventionlifeline.org

**🧠 RIGHT NOW, YOU CAN:**
• Take 10 slow, deep breaths
• Reach out to a trusted person
• Use the grounding technique: 5 things you see, 4 you can touch, 3 you hear

These intense feelings are temporary, even though they feel overwhelming right now. Professional support can help you through this crisis.

Would you like me to help you connect with immediate support, or would you prefer to talk about what you're experiencing?`;
  }

  formatModerateMessage(crisisAssessment, resources) {
    return `💙 **SUPPORT & GUIDANCE AVAILABLE** 💙

I can sense you're experiencing significant emotional distress, and I want to acknowledge how difficult this must be for you. Your feelings are completely valid and important.

**🤝 SUPPORT RESOURCES:**
• **National Alliance on Mental Illness:** 1-800-950-NAMI
• **Crisis Text Line:** Text HOME to 741741 (if you need immediate support)
• **Psychology Today:** Find local therapists at psychologytoday.com

**🧠 COPING STRATEGIES FOR RIGHT NOW:**
• Practice the 4-7-8 breathing technique (breathe in 4, hold 7, exhale 8)
• Try progressive muscle relaxation
• Reach out to a supportive friend or family member
• Engage in a comforting, safe activity

These feelings can improve with proper support and coping strategies. You deserve care and healing.

How are you feeling right now, and what kind of support would be most helpful?`;
  }

  formatSupportiveMessage(crisisAssessment) {
    return `💙 **EMOTIONAL SUPPORT & VALIDATION** 💙

I want to acknowledge what you're going through and validate that these feelings are real and understandable. It takes courage to reach out and share what you're experiencing.

**🌟 GENTLE REMINDERS:**
• Your feelings are temporary, even when they feel overwhelming
• You have survived difficult times before
• Seeking support is a sign of strength, not weakness
• Small steps toward self-care can make a meaningful difference

**🧠 GENTLE COPING STRATEGIES:**
• Take a few moments for deep, mindful breathing
• Practice self-compassion - treat yourself as you would a good friend
• Consider what has helped you feel better in the past
• Remember that it's okay to not be okay sometimes

I'm here to listen and support you. What would feel most helpful for you right now?`;
  }

  formatPreventiveMessage(crisisAssessment) {
    return `💙 **EMOTIONAL WELLNESS SUPPORT** 💙

I want to acknowledge what you're feeling and remind you that all emotions are valid parts of the human experience. It's completely normal to have ups and downs.

**🌟 WELLNESS REMINDERS:**
• You're taking a positive step by reaching out and reflecting on your feelings
• Emotional awareness is the first step toward emotional wellness
• Small acts of self-care can have a meaningful impact
• You have inner strength and resilience

**🧠 GENTLE WELLNESS PRACTICES:**
• Take a moment to appreciate something good in your day
• Practice gratitude for small things
• Engage in an activity that brings you comfort or joy
• Connect with someone who cares about you

How are you feeling, and what would be most supportive for you right now?`;
  }

  getCrisisSpecificCoping(crisisType) {
    const strategies = {
      suicide: [
        'Remove means of self-harm from immediate environment',
        'Call a trusted friend or family member',
        'Go to a public place or stay with someone',
        'Use the STOP technique: Stop, Take a breath, Observe, Proceed mindfully',
        'Create a safety plan with specific steps and contacts'
      ],
      selfHarm: [
        'Hold ice cubes or take a cold shower',
        'Draw on your skin with a red marker instead',
        'Do intense exercise like running or pushups',
        'Squeeze a stress ball or punch a pillow',
        'Call a crisis line to talk through the urge'
      ],
      severe: [
        'Practice grounding: 5 things you see, 4 you hear, 3 you touch',
        'Use deep breathing: 4 counts in, 6 counts out',
        'Challenge negative thoughts with evidence',
        'Reach out to your support network',
        'Engage in a previously enjoyable activity'
      ]
    };

    return strategies[crisisType] || strategies.severe;
  }

  getTherapeuticCoping(crisisAssessment) {
    return [
      'Practice mindful breathing for 5 minutes',
      'Write down your thoughts and feelings',
      'Use progressive muscle relaxation',
      'Call a supportive friend or family member',
      'Engage in gentle physical activity',
      'Listen to calming music or nature sounds',
      'Practice self-compassion and positive self-talk'
    ];
  }

  getPreventiveCoping() {
    return [
      'Take three deep, mindful breaths',
      'Practice gratitude by listing three good things',
      'Do a brief body scan for tension and relax',
      'Step outside for fresh air if possible',
      'Drink a glass of water mindfully',
      'Stretch or do gentle movement'
    ];
  }

  getWellnessCoping() {
    return [
      'Take a moment to appreciate the present',
      'Practice a brief loving-kindness meditation',
      'Do something creative or expressive',
      'Connect with nature, even briefly',
      'Practice gentle self-care',
      'Reflect on your personal strengths'
    ];
  }

  logCrisisIntervention(userId, crisisAssessment, userMessage) {
    const logData = {
      timestamp: new Date().toISOString(),
      userId,
      riskLevel: crisisAssessment.riskLevel,
      crisisType: crisisAssessment.crisisType,
      severity: crisisAssessment.severity,
      triggers: crisisAssessment.triggers,
      messagePreview: userMessage.substring(0, 100) + '...'
    };

    logger.warn('CRISIS INTERVENTION ACTIVATED', logData);
    
    // In production, this should also alert monitoring systems
    // and potentially notify crisis professionals
  }

  trackIntervention(userId, intervention) {
    if (!this.interventionHistory.has(userId)) {
      this.interventionHistory.set(userId, []);
    }
    
    this.interventionHistory.get(userId).push({
      timestamp: intervention.timestamp,
      type: intervention.type,
      level: intervention.level,
      followUpScheduled: intervention.followUp?.schedule || []
    });

    // Keep only last 10 interventions per user
    const userHistory = this.interventionHistory.get(userId);
    if (userHistory.length > 10) {
      this.interventionHistory.set(userId, userHistory.slice(-10));
    }
  }

  createEmergencyFallbackIntervention(crisisAssessment) {
    return {
      type: 'EMERGENCY_FALLBACK',
      priority: 'CRITICAL',
      message: `🚨 **CRISIS SUPPORT NEEDED** 🚨

I'm concerned about your safety and wellbeing. Please reach out for immediate professional help:

**IMMEDIATE RESOURCES:**
• **National Suicide Prevention Lifeline: 988**
• **Crisis Text Line: Text HOME to 741741**
• **Emergency Services: 911**

You don't have to face this alone. Professional help is available right now.`,
      actions: ['DISPLAY_EMERGENCY_RESOURCES'],
      resources: {
        hotline: '988',
        text: '741741',
        emergency: '911'
      }
    };
  }

  getUserInterventionHistory(userId) {
    return this.interventionHistory.get(userId) || [];
  }
}

module.exports = new CrisisInterventionService();
