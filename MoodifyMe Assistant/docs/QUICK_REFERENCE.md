# Chat-Tevez Quick Reference

## 🚀 Quick Start

### Start the Server
```bash
npm start
# Server runs on http://localhost:3000
```

### Test with CLI
```bash
npm run test-cli
```

## 📡 Essential API Endpoints

### 1. Send Message (Main Chat)
```bash
curl -X POST http://localhost:3000/api/chat/message \
  -H "Content-Type: application/json" \
  -d '{"message": "I feel anxious about my presentation"}'
```

**Response:**
```json
{
  "success": true,
  "data": {
    "conversationId": "uuid",
    "response": "🌡️ I can sense your anxiety... 💬 Try breathing exercises... 😄 Psychology joke... 🧠 Remember you're prepared!",
    "mood": {
      "mood": "anxious",
      "confidence": 0.95,
      "reasoning": "Direct expression of anxiety about presentation"
    },
    "type": "rag",
    "sources": ["Daily Stoic", "Art of Happiness"]
  }
}
```

### 2. Analyze Mood Only
```bash
curl -X POST http://localhost:3000/api/chat/mood \
  -H "Content-Type: application/json" \
  -d '{"text": "I feel overwhelmed with work"}'
```

### 3. Generate Psychology Joke
```bash
curl -X POST http://localhost:3000/api/chat/joke \
  -H "Content-Type: application/json" \
  -d '{"mood": "sad", "context": "feeling lonely"}'
```

### 4. Health Check
```bash
curl http://localhost:3000/ping
```

## 💻 Code Examples

### JavaScript/Node.js
```javascript
const axios = require('axios');

// Send message
const response = await axios.post('http://localhost:3000/api/chat/message', {
  message: "I'm feeling stressed",
  conversationId: "optional-id"
});

console.log(response.data.data.response);
console.log(response.data.data.mood.mood);
```

### Python
```python
import requests

response = requests.post('http://localhost:3000/api/chat/message', 
  json={"message": "I feel anxious"})

data = response.json()["data"]
print(f"Response: {data['response']}")
print(f"Mood: {data['mood']['mood']}")
```

### PHP
```php
$response = file_get_contents('http://localhost:3000/api/chat/message', 
  false, stream_context_create([
    'http' => [
      'method' => 'POST',
      'header' => 'Content-Type: application/json',
      'content' => json_encode(['message' => 'I feel sad'])
    ]
  ]));

$data = json_decode($response, true)['data'];
echo $data['response'];
```

## 🎭 Mood Categories

| Mood | Description | Example Triggers |
|------|-------------|------------------|
| **happy** | Joy, excitement | Good news, achievements |
| **sad** | Sadness, grief | Loss, disappointment |
| **angry** | Anger, frustration | Injustice, obstacles |
| **anxious** | Worry, nervousness | Uncertainty, pressure |
| **calm** | Peace, tranquility | Meditation, nature |
| **confused** | Uncertainty, bewilderment | Complex decisions |
| **lonely** | Isolation, disconnection | Social separation |
| **motivated** | Drive, determination | Goals, inspiration |
| **tired** | Exhaustion, fatigue | Overwork, stress |
| **grateful** | Appreciation, thankfulness | Kindness, blessings |
| **neutral** | Balanced, no strong emotion | Normal state |

## 🔧 Response Structure

Every chat response includes:

```json
{
  "conversationId": "uuid",
  "response": "🌡️ Mood acknowledgment 💬 Support 😄 Joke 🧠 Insight",
  "mood": {
    "mood": "detected_mood",
    "confidence": 0.95,
    "sentiment": { "score": -1, "positive": [], "negative": ["anxious"] },
    "aiAnalysis": { "reasoning": "Psychological explanation" }
  },
  "type": "rag|joke|fallback",
  "sources": ["Book1", "Book2"],
  "quotes": [{"text": "Quote", "source": "Book"}],
  "conversation": {
    "messageCount": 4,
    "currentMood": "anxious"
  }
}
```

## 🎯 Psychology Focus

Chat-Tevez **ONLY** discusses:
- ✅ Emotions and feelings
- ✅ Mental health and wellness
- ✅ Psychology and therapy concepts
- ✅ Relationships and social connections
- ✅ Personal growth and resilience
- ✅ Mindfulness and stress management

**Non-psychology topics** are redirected:
```json
{
  "response": "I'm here to focus on your emotional well-being. How are you feeling right now?"
}
```

## 🛠️ Integration Patterns

### Simple Chat Widget
```javascript
class ChatWidget {
  constructor() {
    this.conversationId = null;
  }

  async sendMessage(message) {
    const response = await fetch('/api/chat/message', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        message, 
        conversationId: this.conversationId 
      })
    });
    
    const data = await response.json();
    this.conversationId = data.data.conversationId;
    return data.data;
  }
}
```

### Mood Tracking
```javascript
async function trackMood(userId, message) {
  const moodData = await fetch('/api/chat/mood', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ text: message })
  }).then(r => r.json());

  // Store mood data
  await database.saveMoodEntry(userId, moodData.data);
  
  return moodData.data.mood;
}
```

### Batch Processing
```javascript
async function analyzeMoodBatch(messages) {
  const results = [];
  for (const msg of messages) {
    const mood = await fetch('/api/chat/mood', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ text: msg.text })
    }).then(r => r.json());
    
    results.push({ messageId: msg.id, mood: mood.data });
    await new Promise(r => setTimeout(r, 100)); // Rate limiting
  }
  return results;
}
```

## 🚨 Error Handling

```javascript
async function safeApiCall(endpoint, data) {
  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    return await response.json();
  } catch (error) {
    console.error('API Error:', error);
    return { 
      error: true, 
      message: 'Failed to connect to Chat-Tevez' 
    };
  }
}
```

## 📊 Sample Responses

### Anxiety Response
```
🌡️ I can sense the worry and tension you're experiencing about your presentation, and that's completely normal - public speaking triggers our fight-or-flight response. 💬 Try the 5-4-3-2-1 grounding technique: notice 5 things you can see, 4 you can touch, 3 you can hear, 2 you can smell, and 1 you can taste. This helps anchor you in the present moment. 😄 Here's something to lighten the mood: Why did the anxious person bring a ladder to the presentation? Because they wanted to reach new heights of confidence! 🧠 Remember, you've prepared for this, and even if it doesn't go perfectly, each presentation is valuable practice for building your skills.
```

### Depression Support
```
🌡️ I hear the heaviness in your words, and I want you to know that what you're feeling is completely valid. Depression can make everything feel overwhelming and hopeless. 💬 Remember that depression is like weather - it feels permanent when you're in it, but it does pass. Try to focus on one small thing you can do today, even if it's just taking a shower or making a cup of tea. 😄 Here's a gentle reminder: Why don't feelings ever get lost? Because they always know where the heart is! 🧠 You're stronger than you know, and reaching out shows incredible courage. Consider connecting with a mental health professional who can provide additional support.
```

## 🔗 Useful Links

- **Full API Documentation**: [docs/API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- **Integration Guide**: [docs/INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md)
- **License Agreement**: [LICENSE_AGREEMENT.md](../LICENSE_AGREEMENT.md)

## 📞 Support

**Developer**: Ngana Noa
- **Email**: noafrederic91@gmail.com
- **Phone**: +237676537278

**Note**: Support is limited to initial delivery. All deployment, integration, and maintenance is the responsibility of the license holder (Atancho Johannes A.).
