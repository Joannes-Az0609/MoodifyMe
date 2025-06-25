const logger = require('../utils/logger');

class CrisisResourcesService {
  constructor() {
    this.globalResources = this.initializeGlobalResources();
    this.localResources = new Map(); // Cache for location-specific resources
  }

  initializeGlobalResources() {
    return {
      // International/Global Crisis Resources
      global: {
        suicide: {
          name: "International Association for Suicide Prevention",
          website: "https://www.iasp.info/resources/Crisis_Centres/",
          description: "Global directory of crisis centers"
        }
      },

      // Country-specific resources
      countries: {
        US: {
          emergency: "911",
          suicide: {
            name: "988 Suicide & Crisis Lifeline",
            phone: "988",
            text: "Text HOME to 741741",
            chat: "https://suicidepreventionlifeline.org/chat/",
            description: "24/7 free and confidential support"
          },
          crisis: {
            name: "Crisis Text Line",
            text: "741741",
            website: "https://www.crisistextline.org/",
            description: "24/7 crisis support via text"
          },
          mentalHealth: {
            name: "National Alliance on Mental Illness (NAMI)",
            phone: "1-800-950-NAMI (6264)",
            website: "https://www.nami.org/",
            description: "Mental health support and resources"
          },
          selfHarm: {
            name: "Self-Injury Outreach & Support",
            website: "https://sioutreach.org/",
            description: "Resources for self-injury recovery"
          }
        },

        UK: {
          emergency: "999",
          suicide: {
            name: "Samaritans",
            phone: "116 123",
            email: "jo@samaritans.org",
            website: "https://www.samaritans.org/",
            description: "Free 24/7 emotional support"
          },
          crisis: {
            name: "Crisis Text Line UK",
            text: "Text SHOUT to 85258",
            website: "https://giveusashout.org/",
            description: "24/7 text support service"
          },
          mentalHealth: {
            name: "Mind",
            phone: "0300 123 3393",
            website: "https://www.mind.org.uk/",
            description: "Mental health support and information"
          }
        },

        CA: {
          emergency: "911",
          suicide: {
            name: "Talk Suicide Canada",
            phone: "1-833-456-4566",
            text: "Text 45645",
            website: "https://talksuicide.ca/",
            description: "24/7 bilingual crisis support"
          },
          crisis: {
            name: "Crisis Services Canada",
            phone: "1-833-456-4566",
            website: "https://www.crisisservicescanada.ca/",
            description: "National crisis helpline"
          }
        },

        AU: {
          emergency: "000",
          suicide: {
            name: "Lifeline Australia",
            phone: "13 11 14",
            text: "Text 0477 13 11 14",
            website: "https://www.lifeline.org.au/",
            description: "24/7 crisis support and suicide prevention"
          },
          crisis: {
            name: "Beyond Blue",
            phone: "1300 22 4636",
            website: "https://www.beyondblue.org.au/",
            description: "Mental health support"
          }
        },

        // Add more countries as needed
        DE: {
          emergency: "112",
          suicide: {
            name: "Telefonseelsorge",
            phone: "0800 111 0 111 or 0800 111 0 222",
            website: "https://www.telefonseelsorge.de/",
            description: "24/7 crisis counseling in German"
          }
        },

        FR: {
          emergency: "112",
          suicide: {
            name: "SOS Amitié",
            phone: "09 72 39 40 50",
            website: "https://www.sos-amitie.org/",
            description: "24/7 emotional support in French"
          }
        },

        JP: {
          emergency: "110",
          suicide: {
            name: "TELL Lifeline",
            phone: "03-5774-0992",
            website: "https://telljp.com/",
            description: "Crisis support in English and Japanese"
          }
        }
      },

      // Specialized resources
      specialized: {
        lgbtq: {
          name: "The Trevor Project",
          phone: "1-866-488-7386",
          text: "Text START to 678-678",
          website: "https://www.thetrevorproject.org/",
          description: "Crisis support for LGBTQ+ youth"
        },
        veterans: {
          name: "Veterans Crisis Line",
          phone: "1-800-273-8255 (Press 1)",
          text: "Text 838255",
          website: "https://www.veteranscrisisline.net/",
          description: "Crisis support for veterans"
        },
        teens: {
          name: "Teen Line",
          phone: "1-800-852-8336",
          text: "Text TEEN to 839863",
          website: "https://teenlineonline.org/",
          description: "Peer support for teenagers"
        },
        domestic: {
          name: "National Domestic Violence Hotline",
          phone: "1-800-799-7233",
          text: "Text START to 88788",
          website: "https://www.thehotline.org/",
          description: "Support for domestic violence survivors"
        }
      },

      // Online resources
      online: {
        apps: [
          {
            name: "Crisis Text Line",
            description: "Text-based crisis support",
            platforms: ["iOS", "Android", "Web"]
          },
          {
            name: "PTSD Coach",
            description: "PTSD symptom management",
            platforms: ["iOS", "Android"]
          },
          {
            name: "MindShift",
            description: "Anxiety management",
            platforms: ["iOS", "Android"]
          }
        ],
        websites: [
          {
            name: "Mental Health America",
            url: "https://www.mhanational.org/",
            description: "Mental health resources and screening tools"
          },
          {
            name: "Crisis Text Line",
            url: "https://www.crisistextline.org/",
            description: "24/7 text-based crisis support"
          }
        ]
      }
    };
  }

  async getEmergencyResources(userId = null, location = null) {
    try {
      const userLocation = location || await this.detectUserLocation(userId);
      const countryCode = this.getCountryCode(userLocation);
      
      const countryResources = this.globalResources.countries[countryCode] || this.globalResources.countries.US;

      return {
        immediate: {
          emergency: {
            number: countryResources.emergency,
            description: "Emergency services (police, fire, medical)"
          },
          suicide: countryResources.suicide,
          crisis: countryResources.crisis
        },
        emergency: [
          countryResources.emergency,
          countryResources.suicide.phone,
          countryResources.crisis.text || countryResources.crisis.phone
        ],
        specialized: this.getSpecializedResources(userId)
      };
    } catch (error) {
      logger.error('Error getting emergency resources:', error);
      return this.getFallbackEmergencyResources();
    }
  }

  async getCrisisResources(userId = null, location = null) {
    try {
      const userLocation = location || await this.detectUserLocation(userId);
      const countryCode = this.getCountryCode(userLocation);
      
      const countryResources = this.globalResources.countries[countryCode] || this.globalResources.countries.US;

      return {
        crisis: {
          hotlines: [
            countryResources.suicide,
            countryResources.crisis,
            countryResources.mentalHealth
          ].filter(Boolean),
          specialized: this.getSpecializedResources(userId),
          online: this.globalResources.online
        },
        immediate: {
          actions: [
            "Call a crisis hotline",
            "Go to nearest emergency room",
            "Call a trusted friend or family member",
            "Remove means of self-harm",
            "Stay in a safe, public place"
          ]
        }
      };
    } catch (error) {
      logger.error('Error getting crisis resources:', error);
      return this.getFallbackCrisisResources();
    }
  }

  async getSupportResources(userId = null, location = null) {
    try {
      const userLocation = location || await this.detectUserLocation(userId);
      const countryCode = this.getCountryCode(userLocation);
      
      const countryResources = this.globalResources.countries[countryCode] || this.globalResources.countries.US;

      return {
        support: {
          mentalHealth: countryResources.mentalHealth,
          therapy: {
            name: "Psychology Today",
            website: "https://www.psychologytoday.com/",
            description: "Find local therapists and counselors"
          },
          support_groups: {
            name: "Support Groups Central",
            website: "https://www.supportgroupscentral.com/",
            description: "Find local and online support groups"
          }
        },
        selfCare: {
          apps: this.globalResources.online.apps,
          websites: this.globalResources.online.websites
        }
      };
    } catch (error) {
      logger.error('Error getting support resources:', error);
      return this.getFallbackSupportResources();
    }
  }

  async getWellnessResources() {
    return {
      wellness: {
        meditation: [
          {
            name: "Headspace",
            description: "Guided meditation and mindfulness",
            type: "app"
          },
          {
            name: "Calm",
            description: "Sleep stories, meditation, relaxation",
            type: "app"
          },
          {
            name: "Insight Timer",
            description: "Free meditation app with community",
            type: "app"
          }
        ],
        selfCare: [
          {
            name: "Self-Care Check-In",
            description: "Regular self-assessment and care planning",
            type: "practice"
          },
          {
            name: "Gratitude Journaling",
            description: "Daily practice of noting positive experiences",
            type: "practice"
          },
          {
            name: "Mindful Breathing",
            description: "Simple breathing exercises for stress relief",
            type: "practice"
          }
        ],
        education: [
          {
            name: "Mental Health America",
            url: "https://www.mhanational.org/",
            description: "Mental health education and resources"
          },
          {
            name: "National Institute of Mental Health",
            url: "https://www.nimh.nih.gov/",
            description: "Research-based mental health information"
          }
        ]
      }
    };
  }

  getSpecializedResources(userId = null) {
    // In a real implementation, this would consider user demographics/preferences
    return [
      this.globalResources.specialized.lgbtq,
      this.globalResources.specialized.veterans,
      this.globalResources.specialized.teens,
      this.globalResources.specialized.domestic
    ];
  }

  async detectUserLocation(userId) {
    // In a real implementation, this would:
    // 1. Check user profile for location
    // 2. Use IP geolocation
    // 3. Ask user for location if needed
    // For now, default to US
    return { country: 'US', region: null };
  }

  getCountryCode(location) {
    if (!location || !location.country) {
      return 'US'; // Default fallback
    }
    
    // Map common country names/codes
    const countryMap = {
      'United States': 'US',
      'USA': 'US',
      'US': 'US',
      'United Kingdom': 'UK',
      'UK': 'UK',
      'Britain': 'UK',
      'Canada': 'CA',
      'Australia': 'AU',
      'Germany': 'DE',
      'France': 'FR',
      'Japan': 'JP'
    };

    return countryMap[location.country] || 'US';
  }

  getFallbackEmergencyResources() {
    return {
      immediate: {
        emergency: {
          number: "911",
          description: "Emergency services (US)"
        },
        suicide: {
          name: "988 Suicide & Crisis Lifeline",
          phone: "988",
          description: "24/7 crisis support"
        },
        crisis: {
          name: "Crisis Text Line",
          text: "741741",
          description: "Text HOME to 741741"
        }
      },
      emergency: ["911", "988", "741741"]
    };
  }

  getFallbackCrisisResources() {
    return {
      crisis: {
        hotlines: [
          {
            name: "988 Suicide & Crisis Lifeline",
            phone: "988",
            description: "24/7 crisis support"
          },
          {
            name: "Crisis Text Line",
            text: "741741",
            description: "Text HOME to 741741"
          }
        ]
      }
    };
  }

  getFallbackSupportResources() {
    return {
      support: {
        mentalHealth: {
          name: "National Alliance on Mental Illness",
          phone: "1-800-950-NAMI",
          website: "https://www.nami.org/"
        }
      }
    };
  }

  // Method to add custom resources for specific regions/organizations
  addCustomResource(region, resourceType, resource) {
    try {
      if (!this.localResources.has(region)) {
        this.localResources.set(region, {});
      }
      
      const regionResources = this.localResources.get(region);
      if (!regionResources[resourceType]) {
        regionResources[resourceType] = [];
      }
      
      regionResources[resourceType].push(resource);
      logger.info(`Added custom resource for ${region}:${resourceType}`);
    } catch (error) {
      logger.error('Error adding custom resource:', error);
    }
  }

  // Method to get formatted resource list for display
  formatResourcesForDisplay(resources, type = 'crisis') {
    try {
      const formatted = [];
      
      if (resources.immediate) {
        formatted.push({
          category: 'Immediate Help',
          items: Object.values(resources.immediate)
        });
      }
      
      if (resources.crisis && resources.crisis.hotlines) {
        formatted.push({
          category: 'Crisis Hotlines',
          items: resources.crisis.hotlines
        });
      }
      
      if (resources.support) {
        formatted.push({
          category: 'Support Resources',
          items: Object.values(resources.support)
        });
      }
      
      return formatted;
    } catch (error) {
      logger.error('Error formatting resources:', error);
      return [];
    }
  }
}

module.exports = new CrisisResourcesService();
