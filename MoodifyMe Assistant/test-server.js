const express = require('express');
const cors = require('cors');
require('dotenv').config();

const app = express();
const port = process.env.PORT || 3001;

// Basic middleware
app.use(cors());
app.use(express.json());
app.use(express.static('public'));

// Simple health check
app.get('/api/health', (req, res) => {
  res.json({
    status: 'healthy',
    timestamp: new Date().toISOString(),
    message: 'MoodifyMe Assistant is ready'
  });
});

// Simple ping endpoint
app.get('/ping', (req, res) => {
  res.json({ 
    status: 'pong', 
    timestamp: new Date().toISOString() 
  });
});

// Basic mood analysis endpoint
app.post('/api/chat/mood', (req, res) => {
  const { text } = req.body;

  // Simple mood detection based on keywords
  let mood = 'neutral';
  let confidence = 0.7;

  const lowerText = text.toLowerCase();

  // More comprehensive mood detection
  if (lowerText.includes('happy') || lowerText.includes('joy') || lowerText.includes('excited') ||
      lowerText.includes('great') || lowerText.includes('wonderful') || lowerText.includes('amazing') ||
      lowerText.includes('fantastic') || lowerText.includes('good') || lowerText.includes('cheerful')) {
    mood = 'happy';
    confidence = 0.9;
  } else if (lowerText.includes('sad') || lowerText.includes('down') || lowerText.includes('depressed') ||
             lowerText.includes('unhappy') || lowerText.includes('blue') || lowerText.includes('melancholy') ||
             lowerText.includes('feeling sad') || lowerText.includes('feel sad') || lowerText.includes('sorrow')) {
    mood = 'sad';
    confidence = 0.9;
  } else if (lowerText.includes('angry') || lowerText.includes('mad') || lowerText.includes('furious') ||
             lowerText.includes('rage') || lowerText.includes('irritated') || lowerText.includes('annoyed')) {
    mood = 'angry';
    confidence = 0.9;
  } else if (lowerText.includes('anxious') || lowerText.includes('worried') || lowerText.includes('nervous') ||
             lowerText.includes('anxiety') || lowerText.includes('fear') || lowerText.includes('scared')) {
    mood = 'anxious';
    confidence = 0.9;
  } else if (lowerText.includes('stressed') || lowerText.includes('overwhelmed') || lowerText.includes('pressure') ||
             lowerText.includes('tense') || lowerText.includes('burden')) {
    mood = 'stressed';
    confidence = 0.9;
  } else if (lowerText.includes('calm') || lowerText.includes('peaceful') || lowerText.includes('relaxed') ||
             lowerText.includes('serene') || lowerText.includes('tranquil')) {
    mood = 'calm';
    confidence = 0.9;
  }

  res.json({
    success: true,
    data: {
      mood: mood,
      confidence: confidence
    }
  });
});

// Helper function to analyze mood
function analyzeMood(text) {
  let mood = 'neutral';
  let confidence = 0.7;

  const lowerText = text.toLowerCase();

  if (lowerText.includes('happy') || lowerText.includes('joy') || lowerText.includes('excited') ||
      lowerText.includes('great') || lowerText.includes('wonderful') || lowerText.includes('amazing') ||
      lowerText.includes('fantastic') || lowerText.includes('good') || lowerText.includes('cheerful')) {
    mood = 'happy';
    confidence = 0.9;
  } else if (lowerText.includes('sad') || lowerText.includes('down') || lowerText.includes('depressed') ||
             lowerText.includes('unhappy') || lowerText.includes('blue') || lowerText.includes('melancholy') ||
             lowerText.includes('feeling sad') || lowerText.includes('feel sad') || lowerText.includes('sorrow')) {
    mood = 'sad';
    confidence = 0.9;
  } else if (lowerText.includes('angry') || lowerText.includes('mad') || lowerText.includes('furious') ||
             lowerText.includes('rage') || lowerText.includes('irritated') || lowerText.includes('annoyed')) {
    mood = 'angry';
    confidence = 0.9;
  } else if (lowerText.includes('anxious') || lowerText.includes('worried') || lowerText.includes('nervous') ||
             lowerText.includes('anxiety') || lowerText.includes('fear') || lowerText.includes('scared')) {
    mood = 'anxious';
    confidence = 0.9;
  } else if (lowerText.includes('stressed') || lowerText.includes('overwhelmed') || lowerText.includes('pressure') ||
             lowerText.includes('tense') || lowerText.includes('burden')) {
    mood = 'stressed';
    confidence = 0.9;
  } else if (lowerText.includes('calm') || lowerText.includes('peaceful') || lowerText.includes('relaxed') ||
             lowerText.includes('serene') || lowerText.includes('tranquil')) {
    mood = 'calm';
    confidence = 0.9;
  }

  return { mood, confidence };
}

// Basic chat endpoint (mock response)
app.post('/api/chat/message', (req, res) => {
  const { message, requestJoke } = req.body;

  // Analyze mood from the message
  const moodAnalysis = analyzeMood(message);

  let response = '';
  let type = 'support';

  if (requestJoke) {
    response = `Here's a little mood booster: Why did the therapist bring a ladder to work? Because they wanted to help their patients reach new heights! 😊 But seriously, "${message}" - I'm here to support you through this.`;
    type = 'joke';
  } else {
    // Generate response based on detected mood
    switch(moodAnalysis.mood) {
      case 'sad':
        response = `I understand you're feeling sad. It's completely normal to have these feelings. Remember that emotions are temporary, and you have the strength to work through this. Would you like to talk about what's making you feel this way, or would you prefer some uplifting suggestions?`;
        break;
      case 'anxious':
        response = `I hear that you're feeling anxious. Anxiety can be overwhelming, but you're taking a positive step by reaching out. Try taking a few deep breaths - in for 4 counts, hold for 4, out for 4. What specific thoughts or situations are contributing to your anxiety right now?`;
        break;
      case 'happy':
        response = `That's wonderful to hear! I'm so glad you're feeling positive. Happiness is such a beautiful emotion to experience. What's been contributing to your good mood today? I'd love to help you maintain and build on these positive feelings.`;
        break;
      case 'angry':
        response = `I can sense your anger, and that's completely valid. Anger often signals that something important to you has been affected. Let's work through this together. What's triggering these feelings, and how can we channel this energy constructively?`;
        break;
      case 'stressed':
        response = `I understand you're feeling stressed. Stress can be overwhelming, but remember that you're stronger than you think. Let's break this down - what's the main source of your stress right now? Sometimes talking through it can help lighten the load.`;
        break;
      case 'calm':
        response = `It's wonderful that you're feeling calm and peaceful. This is a great emotional state to be in. How can I help you maintain this sense of tranquility, or is there something specific you'd like to explore while you're in this centered space?`;
        break;
      default:
        response = `Thank you for sharing "${message}" with me. I'm here to provide emotional support and guidance. Your feelings are valid and important. How can I best support you today?`;
    }
  }

  res.json({
    success: true,
    data: {
      response: response,
      mood: moodAnalysis,
      type: type
    }
  });
});

// Start server
app.listen(port, () => {
  console.log(`MoodifyMe Assistant running on port ${port}`);
  console.log(`Health check: http://localhost:${port}/api/health`);
  console.log(`Web interface: http://localhost:${port}`);
});
