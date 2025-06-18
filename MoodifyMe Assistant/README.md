# Mood RAG Chatbot

A sophisticated chatbot powered by Retrieval-Augmented Generation (RAG) that improves people's mood, tells jokes based on emotional state, and maintains conversation context using LangChain, Pinecone, and Google's Gemini API.

## Features

- **Mood Analysis**: Advanced mood detection using sentiment analysis, keyword matching, and AI-powered analysis
- **RAG-Powered Responses**: Retrieves relevant information from uploaded documents to provide contextual responses
- **Mood-Based Jokes**: Generates appropriate jokes based on the user's emotional state
- **Conversation Memory**: Maintains conversation context using LangChain's memory system
- **PDF Knowledge Base**: Processes PDF documents to create a searchable knowledge base
- **CLI Testing Interface**: Command-line interface for easy testing and interaction
- **RESTful API**: Complete REST API for integration with frontend applications

## Architecture

```
├── src/
│   ├── config/          # Configuration files for APIs and services
│   ├── services/        # Core business logic
│   ├── routes/          # Express.js API routes
│   ├── middleware/      # Custom middleware
│   └── utils/           # Utility functions
├── scripts/             # Setup and testing scripts
├── pdfs/               # PDF documents for knowledge base
└── logs/               # Application logs
```

## Prerequisites

- Node.js 18.0.0 or higher
- Google Gemini API key
- Pinecone API key
- LangChain API key (optional, for enhanced features)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd mood-rag-chatbot
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Set up environment variables**
   
   The `.env` file is already configured with your API keys:
   ```env
   GEMINI_API_KEY=AIzaSyAOVI3adXRmnF4AeD_g9n1CIc9Z9UEemnE
   PINECONE_API_KEY=pcsk_4hYq2w_ME1Bafh2nNAcYjve7BYJghc6P6D7DeRiUJJmaehVhkXwXG9kB4HDiCAoiiE87c4
   LANGCHAIN_API_KEY=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
   ```

4. **Create logs directory**
   ```bash
   mkdir logs
   ```

## Setup

### 1. Initialize Vector Store

Before using the chatbot, you need to process the PDF documents and set up the vector store:

```bash
npm run setup
```

This script will:
- Process all PDF files in the `pdfs/` directory
- Create document chunks
- Generate embeddings using Gemini
- Store vectors in Pinecone
- Verify the setup

### 2. Available PDFs

The system comes with several mood-related and philosophical books:
- Kafka's Letters and Diaries
- The Art of Happiness and the Art of Being
- The Art of Happiness
- Wherever You Go There You Are
- Daily Stoic
- Man's Search for Meaning

## Usage

### CLI Testing Interface

Start the interactive CLI for testing:

```bash
npm run test-cli
```

**Available CLI Commands:**
- `/help` - Show available commands
- `/mood <text>` - Analyze mood of text
- `/joke [mood]` - Generate a joke for specific mood
- `/clear` - Clear conversation memory
- `/new` - Start a new conversation
- `/stats` - Show vector store statistics
- `/history` - Show conversation history
- `/quit` - Exit the chatbot

### REST API Server

Start the REST API server:

```bash
npm start
```

For development with auto-reload:
```bash
npm run dev
```

The server will start on `http://localhost:3001`

## API Endpoints

### Chat Endpoints

**POST /api/chat/message**
```json
{
  "message": "I'm feeling sad today",
  "conversationId": "optional-conversation-id",
  "requestJoke": false
}
```

**GET /api/chat/conversation/:id**
- Get conversation details

**GET /api/chat/conversations**
- Get all conversations

**DELETE /api/chat/conversation/:id**
- Delete a conversation

**POST /api/chat/conversation/:id/clear**
- Clear conversation memory

### Mood Analysis

**POST /api/chat/mood**
```json
{
  "text": "I'm having a great day!"
}
```

### Joke Generation

**POST /api/chat/joke**
```json
{
  "mood": "happy",
  "context": "optional context"
}
```

**POST /api/chat/jokes**
```json
{
  "mood": "sad",
  "count": 3
}
```

### Health Endpoints

**GET /api/health**
- Basic health check

**GET /api/health/status**
- Detailed system status

**GET /api/health/vector-stats**
- Vector store statistics

**GET /api/health/pdfs**
- Available PDF files

## How It Works

### 1. Mood Analysis
The system uses a multi-layered approach:
- **Sentiment Analysis**: Basic positive/negative sentiment scoring
- **Keyword Matching**: Matches against predefined mood keywords
- **AI Analysis**: Uses Gemini to provide sophisticated mood detection

### 2. RAG (Retrieval-Augmented Generation)
- Documents are chunked and embedded using Gemini's embedding model
- User queries are embedded and matched against the vector store
- Relevant context is retrieved and used to generate informed responses

### 3. Conversation Memory
- Uses LangChain's ConversationSummaryBufferMemory
- Maintains context across multiple exchanges
- Automatically summarizes older conversations to stay within token limits

### 4. Mood-Based Responses
- Responses are tailored based on detected mood
- Different conversation strategies for different emotional states
- Appropriate joke generation based on mood

## Configuration

Key configuration options in `.env`:

```env
# Server
PORT=3000
NODE_ENV=development

# Pinecone
PINECONE_INDEX_NAME=mood-chatbot-index

# Chat Configuration
MAX_CONVERSATION_HISTORY=10
CHUNK_SIZE=1000
CHUNK_OVERLAP=200
MOOD_CONFIDENCE_THRESHOLD=0.6
```

## Development

### Project Structure

- **config/**: API configurations and initialization
- **services/**: Core business logic
  - `chatService.js`: Main chat orchestration
  - `moodAnalyzer.js`: Mood detection and analysis
  - `jokeService.js`: Joke generation
  - `vectorStore.js`: Vector operations
  - `pdfProcessor.js`: PDF processing and chunking
- **routes/**: Express.js API routes
- **middleware/**: Custom middleware for error handling
- **utils/**: Utility functions and helpers

### Adding New PDFs

1. Place PDF files in the `pdfs/` directory
2. Run the setup script: `npm run setup`
3. The system will automatically process and index the new documents

### Extending Mood Categories

To add new mood categories:

1. Update `moodKeywords` in `src/services/moodAnalyzer.js`
2. Add corresponding joke templates in `src/services/jokeService.js`
3. Update mood guidance in `src/services/chatService.js`

## Troubleshooting

### Common Issues

1. **Vector store initialization fails**
   - Check Pinecone API key and permissions
   - Ensure index name is unique

2. **PDF processing errors**
   - Verify PDF files are not corrupted
   - Check file permissions

3. **Gemini API errors**
   - Verify API key is correct
   - Check rate limits

### Logs

Application logs are stored in the `logs/` directory:
- `error.log`: Error messages only
- `combined.log`: All log messages

## Documentation

### 📚 Complete Documentation Package

- **[API Documentation](docs/API_DOCUMENTATION.md)** - Complete API reference with endpoints, parameters, and sample responses
- **[Integration Guide](docs/INTEGRATION_GUIDE.md)** - Code examples for JavaScript, Python, PHP, React, and more
- **[License Agreement](LICENSE_AGREEMENT.md)** - Production license for commercial use

### 🔗 Quick Links

- **API Base URL**: `http://localhost:3000`
- **Health Check**: `GET /ping`
- **Main Chat**: `POST /api/chat/message`
- **Mood Analysis**: `POST /api/chat/mood`
- **Joke Generation**: `POST /api/chat/joke`

## License

**Production License Agreement** - See [LICENSE_AGREEMENT.md](LICENSE_AGREEMENT.md) for full commercial licensing terms.

**Developer**: Ngana Noa (noafrederic91@gmail.com, +237676537278)
**Licensed To**: Atancho Johannes A. (Full Rights Transfer)

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## Support

For issues and questions, please create an issue in the repository.
