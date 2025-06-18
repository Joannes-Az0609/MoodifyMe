# Chat-Tevez API Documentation

## Overview

Chat-Tevez is a psychology-focused RAG chatbot that provides mood analysis, therapeutic support, and psychology-themed humor. This API enables integration of Chat-Tevez's capabilities into your applications.

**Base URL**: `http://localhost:3000`

## Authentication

Currently, no authentication is required for API access.

## Response Format

All API responses follow this structure:

```json
{
  "success": true,
  "data": {
    // Response data
  }
}
```

Error responses:
```json
{
  "error": "Error message",
  "message": "Detailed error description"
}
```

## Endpoints

### 1. Health Check

#### GET `/ping`
Simple connectivity test.

**Response:**
```json
{
  "status": "pong",
  "timestamp": "2025-06-09T10:08:58.963Z"
}
```

#### GET `/api/health`
Detailed health information.

**Response:**
```json
{
  "status": "healthy",
  "timestamp": "2025-06-09T10:09:29.602Z",
  "uptime": 133.334278629,
  "memory": {
    "rss": 133095424,
    "heapTotal": 62799872,
    "heapUsed": 60063680,
    "external": 3483474,
    "arrayBuffers": 66702
  },
  "version": "1.0.0"
}
```

#### GET `/api/health/status`
Comprehensive system status including vector store and PDF information.

**Response:**
```json
{
  "status": "healthy",
  "timestamp": "2025-06-09T10:09:29.602Z",
  "services": {
    "vectorStore": {
      "status": "connected",
      "totalVectors": 4639,
      "dimension": 768,
      "indexFullness": 0.15
    },
    "pdfProcessor": {
      "status": "available",
      "availablePDFs": 6,
      "pdfs": ["kafka-letters.pdf", "art-of-happiness.pdf", ...]
    },
    "gemini": {
      "status": "connected"
    },
    "pinecone": {
      "status": "connected"
    }
  }
}
```

### 2. Chat Endpoints

#### POST `/api/chat/message`
Main chat endpoint for conversing with Chat-Tevez.

**Request Body:**
```json
{
  "message": "I'm feeling really anxious about my job interview",
  "conversationId": "optional-conversation-id",
  "requestJoke": false
}
```

**Parameters:**
- `message` (string, required): User's message
- `conversationId` (string, optional): Existing conversation ID for continuity
- `requestJoke` (boolean, optional): Force joke generation

**Response:**
```json
{
  "success": true,
  "data": {
    "conversationId": "11d7d6a3-0eaf-4149-92b5-8f6fbd81c53b",
    "response": "🌡️ I can sense the worry and tension you're experiencing about your interview, and that's completely normal - job interviews can trigger our fight-or-flight response. 💬 Try the 5-4-3-2-1 grounding technique: notice 5 things you can see, 4 you can touch, 3 you can hear, 2 you can smell, and 1 you can taste. This helps anchor you in the present moment. 😄 Here's something to lighten the mood: Why did the anxious person bring a ladder to the interview? Because they wanted to reach new heights of confidence! 🧠 Remember, you've prepared for this, and even if it doesn't go perfectly, each interview is valuable practice for building your skills.",
    "mood": {
      "mood": "anxious",
      "confidence": 0.95,
      "sentiment": {
        "score": -1,
        "comparative": -0.1,
        "positive": ["feeling"],
        "negative": ["anxious"]
      },
      "keywordAnalysis": {
        "mood": "anxious",
        "confidence": 1,
        "scores": {
          "happy": 0,
          "sad": 0,
          "angry": 0,
          "anxious": 1,
          "calm": 0,
          "confused": 0,
          "lonely": 0,
          "motivated": 0,
          "tired": 0,
          "grateful": 0
        }
      },
      "aiAnalysis": {
        "mood": "anxious",
        "confidence": 0.95,
        "reasoning": "The statement explicitly uses the word 'anxious,' a direct indicator of the emotional state. The context of a job interview is a common trigger for anxiety."
      },
      "timestamp": "2025-06-09T10:14:26.727Z"
    },
    "type": "rag",
    "sources": ["Daily Stoic", "Art of Happiness"],
    "quotes": [
      {
        "text": "You have power over your mind - not outside events. Realize this, and you will find strength.",
        "source": "Daily Stoic"
      }
    ],
    "conversation": {
      "id": "11d7d6a3-0eaf-4149-92b5-8f6fbd81c53b",
      "messageCount": 2,
      "currentMood": "anxious",
      "createdAt": "2025-06-09T10:14:24.683Z",
      "lastActivity": "2025-06-09T10:14:26.728Z"
    }
  }
}
```

#### GET `/api/chat/conversation/:id`
Retrieve conversation details.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "11d7d6a3-0eaf-4149-92b5-8f6fbd81c53b",
    "messages": [
      {
        "role": "user",
        "content": "I'm feeling anxious about my interview",
        "timestamp": "2025-06-09T10:14:24.683Z",
        "mood": {
          "mood": "anxious",
          "confidence": 0.95
        }
      },
      {
        "role": "assistant",
        "content": "🌡️ I can sense the worry...",
        "timestamp": "2025-06-09T10:14:26.728Z",
        "type": "rag",
        "sources": ["Daily Stoic"],
        "mood": "anxious"
      }
    ],
    "mood": "anxious",
    "moodHistory": [
      {
        "mood": "anxious",
        "confidence": 0.95,
        "timestamp": "2025-06-09T10:14:26.727Z"
      }
    ],
    "createdAt": "2025-06-09T10:14:24.683Z",
    "lastActivity": "2025-06-09T10:14:26.728Z"
  }
}
```

#### GET `/api/chat/conversations`
List all conversations.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "11d7d6a3-0eaf-4149-92b5-8f6fbd81c53b",
      "messageCount": 4,
      "currentMood": "anxious",
      "createdAt": "2025-06-09T10:14:24.683Z",
      "lastActivity": "2025-06-09T10:14:26.728Z"
    }
  ]
}
```

#### DELETE `/api/chat/conversation/:id`
Delete a conversation.

**Response:**
```json
{
  "success": true,
  "message": "Conversation deleted successfully"
}
```

#### POST `/api/chat/conversation/:id/clear`
Clear conversation memory while keeping the conversation.

**Response:**
```json
{
  "success": true,
  "message": "Conversation memory cleared successfully"
}
```

### 3. Mood Analysis

#### POST `/api/chat/mood`
Analyze mood from text without starting a conversation.

**Request Body:**
```json
{
  "text": "I feel really sad and lonely today"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "mood": "sad",
    "confidence": 0.95,
    "sentiment": {
      "score": -4,
      "comparative": -0.5714285714285714,
      "positive": [],
      "negative": ["lonely", "sad"]
    },
    "keywordAnalysis": {
      "mood": "sad",
      "confidence": 1,
      "scores": {
        "happy": 0,
        "sad": 1,
        "angry": 0,
        "anxious": 0,
        "calm": 0,
        "confused": 0,
        "lonely": 1,
        "motivated": 0,
        "tired": 0,
        "grateful": 0
      }
    },
    "aiAnalysis": {
      "mood": "sad",
      "confidence": 0.95,
      "reasoning": "The text explicitly states 'I feel really sad and lonely today.' Sadness and loneliness are primary emotions directly expressed. The use of 'really' emphasizes the intensity of the feeling."
    },
    "timestamp": "2025-06-09T10:13:36.151Z"
  }
}
```

### 4. Joke Generation

#### POST `/api/chat/joke`
Generate a single psychology-themed joke.

**Request Body:**
```json
{
  "mood": "anxious",
  "context": "job interview preparation"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "joke": "Why did the psychologist bring a ladder to the therapy session? Because they wanted to help their client reach new heights of emotional understanding!",
    "mood": "anxious",
    "style": "reassuring and light",
    "timestamp": "2025-06-09T10:14:09.065Z"
  }
}
```

#### POST `/api/chat/jokes`
Generate multiple jokes.

**Request Body:**
```json
{
  "mood": "sad",
  "count": 3
}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "joke": "Why did the therapist bring tissues to every session? Because they knew that tears are just the heart's way of speaking when words aren't enough!",
      "mood": "sad",
      "style": "gentle and comforting",
      "timestamp": "2025-06-09T10:14:09.065Z"
    },
    {
      "joke": "What did one emotion say to another? 'I feel you!' Remember, all feelings are valid and temporary.",
      "mood": "sad",
      "style": "gentle and comforting",
      "timestamp": "2025-06-09T10:14:10.123Z"
    },
    {
      "joke": "Why don't feelings ever get lost? Because they always know where the heart is!",
      "mood": "sad",
      "style": "gentle and comforting",
      "timestamp": "2025-06-09T10:14:11.456Z"
    }
  ]
}
```

## Mood Categories

Chat-Tevez recognizes 11 mood categories:

- **happy**: Joy, excitement, contentment
- **sad**: Sadness, grief, melancholy
- **angry**: Anger, frustration, irritation
- **anxious**: Anxiety, worry, nervousness
- **calm**: Peace, tranquility, relaxation
- **confused**: Uncertainty, bewilderment
- **lonely**: Isolation, disconnection
- **motivated**: Drive, determination, inspiration
- **tired**: Exhaustion, fatigue, weariness
- **grateful**: Appreciation, thankfulness
- **neutral**: Balanced, no strong emotion

## Error Codes

- **400**: Bad Request - Invalid input parameters
- **404**: Not Found - Resource doesn't exist
- **500**: Internal Server Error - Server-side error
- **503**: Service Unavailable - System still initializing

## Rate Limits

Currently no rate limits are enforced, but consider implementing them in production.

## Psychology Focus

Chat-Tevez is designed exclusively for psychology, mental health, and emotional well-being topics. Non-psychology questions are automatically redirected with supportive guidance back to emotional topics.
