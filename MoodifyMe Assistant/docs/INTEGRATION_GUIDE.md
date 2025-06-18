# Chat-Tevez Integration Guide

## Overview

This guide shows how to integrate Chat-Tevez into your applications using various programming languages and frameworks.

## Prerequisites

1. Chat-Tevez server running on `http://localhost:3000`
2. API endpoints accessible
3. Network connectivity to the server

## Quick Start

### 1. Basic Chat Integration

#### JavaScript/Node.js

```javascript
const axios = require('axios');

class ChatTevezClient {
  constructor(baseURL = 'http://localhost:3000') {
    this.baseURL = baseURL;
    this.conversationId = null;
  }

  async sendMessage(message, requestJoke = false) {
    try {
      const response = await axios.post(`${this.baseURL}/api/chat/message`, {
        message,
        conversationId: this.conversationId,
        requestJoke
      });

      // Store conversation ID for continuity
      this.conversationId = response.data.data.conversationId;
      
      return response.data.data;
    } catch (error) {
      console.error('Error sending message:', error.response?.data || error.message);
      throw error;
    }
  }

  async analyzeMood(text) {
    try {
      const response = await axios.post(`${this.baseURL}/api/chat/mood`, {
        text
      });
      return response.data.data;
    } catch (error) {
      console.error('Error analyzing mood:', error.response?.data || error.message);
      throw error;
    }
  }

  async generateJoke(mood, context = '') {
    try {
      const response = await axios.post(`${this.baseURL}/api/chat/joke`, {
        mood,
        context
      });
      return response.data.data;
    } catch (error) {
      console.error('Error generating joke:', error.response?.data || error.message);
      throw error;
    }
  }

  async getConversation() {
    if (!this.conversationId) return null;
    
    try {
      const response = await axios.get(`${this.baseURL}/api/chat/conversation/${this.conversationId}`);
      return response.data.data;
    } catch (error) {
      console.error('Error getting conversation:', error.response?.data || error.message);
      throw error;
    }
  }

  async clearConversation() {
    if (!this.conversationId) return;
    
    try {
      await axios.post(`${this.baseURL}/api/chat/conversation/${this.conversationId}/clear`);
    } catch (error) {
      console.error('Error clearing conversation:', error.response?.data || error.message);
      throw error;
    }
  }

  startNewConversation() {
    this.conversationId = null;
  }
}

// Usage Example
async function example() {
  const chatbot = new ChatTevezClient();

  // Send a message
  const response = await chatbot.sendMessage("I'm feeling anxious about my presentation tomorrow");
  console.log('Bot Response:', response.response);
  console.log('Detected Mood:', response.mood.mood);
  console.log('Confidence:', response.mood.confidence);

  // Analyze mood separately
  const moodAnalysis = await chatbot.analyzeMood("I feel overwhelmed with work");
  console.log('Mood Analysis:', moodAnalysis);

  // Generate a joke
  const joke = await chatbot.generateJoke('anxious', 'work presentation');
  console.log('Joke:', joke.joke);

  // Get conversation history
  const conversation = await chatbot.getConversation();
  console.log('Conversation:', conversation);
}

example().catch(console.error);
```

#### Python

```python
import requests
import json

class ChatTevezClient:
    def __init__(self, base_url="http://localhost:3000"):
        self.base_url = base_url
        self.conversation_id = None
        self.session = requests.Session()

    def send_message(self, message, request_joke=False):
        """Send a message to Chat-Tevez"""
        url = f"{self.base_url}/api/chat/message"
        payload = {
            "message": message,
            "requestJoke": request_joke
        }
        
        if self.conversation_id:
            payload["conversationId"] = self.conversation_id

        try:
            response = self.session.post(url, json=payload)
            response.raise_for_status()
            
            data = response.json()["data"]
            self.conversation_id = data["conversationId"]
            
            return data
        except requests.exceptions.RequestException as e:
            print(f"Error sending message: {e}")
            raise

    def analyze_mood(self, text):
        """Analyze mood from text"""
        url = f"{self.base_url}/api/chat/mood"
        payload = {"text": text}

        try:
            response = self.session.post(url, json=payload)
            response.raise_for_status()
            return response.json()["data"]
        except requests.exceptions.RequestException as e:
            print(f"Error analyzing mood: {e}")
            raise

    def generate_joke(self, mood, context=""):
        """Generate a psychology-themed joke"""
        url = f"{self.base_url}/api/chat/joke"
        payload = {"mood": mood, "context": context}

        try:
            response = self.session.post(url, json=payload)
            response.raise_for_status()
            return response.json()["data"]
        except requests.exceptions.RequestException as e:
            print(f"Error generating joke: {e}")
            raise

    def get_conversation(self):
        """Get current conversation details"""
        if not self.conversation_id:
            return None

        url = f"{self.base_url}/api/chat/conversation/{self.conversation_id}"
        
        try:
            response = self.session.get(url)
            response.raise_for_status()
            return response.json()["data"]
        except requests.exceptions.RequestException as e:
            print(f"Error getting conversation: {e}")
            raise

    def clear_conversation(self):
        """Clear conversation memory"""
        if not self.conversation_id:
            return

        url = f"{self.base_url}/api/chat/conversation/{self.conversation_id}/clear"
        
        try:
            response = self.session.post(url)
            response.raise_for_status()
        except requests.exceptions.RequestException as e:
            print(f"Error clearing conversation: {e}")
            raise

    def start_new_conversation(self):
        """Start a new conversation"""
        self.conversation_id = None

# Usage Example
def main():
    chatbot = ChatTevezClient()

    # Send a message
    response = chatbot.send_message("I'm feeling stressed about my exams")
    print(f"Bot Response: {response['response']}")
    print(f"Detected Mood: {response['mood']['mood']}")
    print(f"Confidence: {response['mood']['confidence']}")

    # Analyze mood
    mood_analysis = chatbot.analyze_mood("I feel lonely and isolated")
    print(f"Mood Analysis: {mood_analysis}")

    # Generate joke
    joke = chatbot.generate_joke('sad', 'feeling isolated')
    print(f"Joke: {joke['joke']}")

if __name__ == "__main__":
    main()
```

#### PHP

```php
<?php

class ChatTevezClient {
    private $baseUrl;
    private $conversationId;

    public function __construct($baseUrl = 'http://localhost:3000') {
        $this->baseUrl = $baseUrl;
        $this->conversationId = null;
    }

    public function sendMessage($message, $requestJoke = false) {
        $url = $this->baseUrl . '/api/chat/message';
        $data = [
            'message' => $message,
            'requestJoke' => $requestJoke
        ];

        if ($this->conversationId) {
            $data['conversationId'] = $this->conversationId;
        }

        $response = $this->makeRequest('POST', $url, $data);
        
        if ($response && isset($response['data'])) {
            $this->conversationId = $response['data']['conversationId'];
            return $response['data'];
        }

        throw new Exception('Failed to send message');
    }

    public function analyzeMood($text) {
        $url = $this->baseUrl . '/api/chat/mood';
        $data = ['text' => $text];

        $response = $this->makeRequest('POST', $url, $data);
        
        if ($response && isset($response['data'])) {
            return $response['data'];
        }

        throw new Exception('Failed to analyze mood');
    }

    public function generateJoke($mood, $context = '') {
        $url = $this->baseUrl . '/api/chat/joke';
        $data = ['mood' => $mood, 'context' => $context];

        $response = $this->makeRequest('POST', $url, $data);
        
        if ($response && isset($response['data'])) {
            return $response['data'];
        }

        throw new Exception('Failed to generate joke');
    }

    private function makeRequest($method, $url, $data = null) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: $httpCode");
        }

        return json_decode($response, true);
    }
}

// Usage Example
try {
    $chatbot = new ChatTevezClient();

    // Send message
    $response = $chatbot->sendMessage("I'm feeling overwhelmed with work");
    echo "Bot Response: " . $response['response'] . "\n";
    echo "Mood: " . $response['mood']['mood'] . "\n";

    // Analyze mood
    $moodAnalysis = $chatbot->analyzeMood("I feel happy and excited");
    echo "Mood Analysis: " . $moodAnalysis['mood'] . "\n";

    // Generate joke
    $joke = $chatbot->generateJoke('happy');
    echo "Joke: " . $joke['joke'] . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
```

### 2. Frontend Integration

#### React Component

```jsx
import React, { useState, useEffect } from 'react';
import axios from 'axios';

const ChatTevez = () => {
  const [messages, setMessages] = useState([]);
  const [inputMessage, setInputMessage] = useState('');
  const [conversationId, setConversationId] = useState(null);
  const [isLoading, setIsLoading] = useState(false);

  const sendMessage = async () => {
    if (!inputMessage.trim()) return;

    const userMessage = {
      role: 'user',
      content: inputMessage,
      timestamp: new Date().toISOString()
    };

    setMessages(prev => [...prev, userMessage]);
    setIsLoading(true);

    try {
      const response = await axios.post('http://localhost:3000/api/chat/message', {
        message: inputMessage,
        conversationId
      });

      const data = response.data.data;
      setConversationId(data.conversationId);

      const botMessage = {
        role: 'assistant',
        content: data.response,
        mood: data.mood,
        sources: data.sources,
        timestamp: new Date().toISOString()
      };

      setMessages(prev => [...prev, botMessage]);
      setInputMessage('');
    } catch (error) {
      console.error('Error sending message:', error);
      const errorMessage = {
        role: 'assistant',
        content: 'Sorry, I encountered an error. Please try again.',
        timestamp: new Date().toISOString()
      };
      setMessages(prev => [...prev, errorMessage]);
    } finally {
      setIsLoading(false);
    }
  };

  const getMoodEmoji = (mood) => {
    const moodEmojis = {
      happy: '😊',
      sad: '😢',
      angry: '😠',
      anxious: '😰',
      calm: '😌',
      confused: '😕',
      lonely: '😔',
      motivated: '💪',
      tired: '😴',
      grateful: '🙏',
      neutral: '😐'
    };
    return moodEmojis[mood] || '😐';
  };

  return (
    <div className="chat-tevez-container">
      <div className="chat-header">
        <h2>Chat-Tevez - Your Psychology Companion</h2>
      </div>
      
      <div className="chat-messages">
        {messages.map((message, index) => (
          <div key={index} className={`message ${message.role}`}>
            <div className="message-content">
              {message.content}
            </div>
            {message.mood && (
              <div className="mood-indicator">
                {getMoodEmoji(message.mood.mood)} 
                {message.mood.mood} ({Math.round(message.mood.confidence * 100)}%)
              </div>
            )}
            {message.sources && message.sources.length > 0 && (
              <div className="sources">
                📚 Sources: {message.sources.join(', ')}
              </div>
            )}
            <div className="timestamp">
              {new Date(message.timestamp).toLocaleTimeString()}
            </div>
          </div>
        ))}
        {isLoading && (
          <div className="message assistant loading">
            <div className="typing-indicator">Chat-Tevez is typing...</div>
          </div>
        )}
      </div>

      <div className="chat-input">
        <input
          type="text"
          value={inputMessage}
          onChange={(e) => setInputMessage(e.target.value)}
          onKeyPress={(e) => e.key === 'Enter' && sendMessage()}
          placeholder="How are you feeling today?"
          disabled={isLoading}
        />
        <button onClick={sendMessage} disabled={isLoading || !inputMessage.trim()}>
          Send
        </button>
      </div>
    </div>
  );
};

export default ChatTevez;
```

### 3. Advanced Integration Patterns

#### Webhook Integration

```javascript
// Express.js webhook handler
app.post('/webhook/mood-analysis', async (req, res) => {
  const { userId, message } = req.body;
  
  try {
    // Analyze mood
    const moodResponse = await axios.post('http://localhost:3000/api/chat/mood', {
      text: message
    });
    
    const mood = moodResponse.data.data;
    
    // Store mood data
    await database.storeMoodData(userId, mood);
    
    // Trigger appropriate response based on mood
    if (mood.mood === 'sad' || mood.mood === 'anxious') {
      // Send supportive message
      const chatResponse = await axios.post('http://localhost:3000/api/chat/message', {
        message: message
      });
      
      await sendNotification(userId, chatResponse.data.data.response);
    }
    
    res.json({ success: true, mood: mood.mood });
  } catch (error) {
    console.error('Webhook error:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});
```

#### Batch Processing

```javascript
// Process multiple messages for mood analysis
async function batchMoodAnalysis(messages) {
  const results = [];
  
  for (const message of messages) {
    try {
      const response = await axios.post('http://localhost:3000/api/chat/mood', {
        text: message.text
      });
      
      results.push({
        messageId: message.id,
        mood: response.data.data,
        timestamp: new Date().toISOString()
      });
      
      // Rate limiting
      await new Promise(resolve => setTimeout(resolve, 100));
    } catch (error) {
      console.error(`Error processing message ${message.id}:`, error);
      results.push({
        messageId: message.id,
        error: error.message,
        timestamp: new Date().toISOString()
      });
    }
  }
  
  return results;
}
```

## Error Handling

Always implement proper error handling:

```javascript
async function safeApiCall(apiFunction) {
  try {
    return await apiFunction();
  } catch (error) {
    if (error.response) {
      // Server responded with error status
      console.error('API Error:', error.response.status, error.response.data);
      return { error: error.response.data.error || 'API Error' };
    } else if (error.request) {
      // Network error
      console.error('Network Error:', error.message);
      return { error: 'Network connection failed' };
    } else {
      // Other error
      console.error('Error:', error.message);
      return { error: 'Unexpected error occurred' };
    }
  }
}
```

## Best Practices

1. **Conversation Continuity**: Always store and pass conversation IDs
2. **Error Handling**: Implement robust error handling for network issues
3. **Rate Limiting**: Respect API limits in production
4. **Mood Tracking**: Store mood progression for analytics
5. **User Privacy**: Handle sensitive emotional data responsibly
6. **Fallback Responses**: Provide fallback responses when API is unavailable

## Production Considerations

1. **Environment Variables**: Use environment variables for API URLs
2. **HTTPS**: Use HTTPS in production
3. **Authentication**: Implement proper authentication if needed
4. **Monitoring**: Monitor API response times and error rates
5. **Caching**: Consider caching responses for better performance
6. **Load Balancing**: Use load balancers for high traffic
