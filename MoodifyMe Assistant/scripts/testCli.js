#!/usr/bin/env node

const readline = require('readline');
const chalk = require('chalk');
const path = require('path');
require('dotenv').config({ path: path.join(__dirname, '..', '.env') });

// Import configurations and services
const geminiConfig = require('../src/config/gemini');
const pineconeConfig = require('../src/config/database');
const langchainConfig = require('../src/config/langchain');
const vectorStoreService = require('../src/services/vectorStore');
const chatService = require('../src/services/chatService');
const moodAnalyzer = require('../src/services/moodAnalyzer');
const jokeService = require('../src/services/jokeService');
const logger = require('../src/utils/logger');

class ChatbotCLI {
  constructor() {
    this.rl = null;
    this.conversationId = null;
    this.isInitialized = false;
    this.commands = {
      '/help': 'Show available commands',
      '/mood <text>': 'Analyze mood of text',
      '/joke [mood]': 'Generate a joke for specific mood',
      '/clear': 'Clear conversation memory',
      '/new': 'Start a new conversation',
      '/stats': 'Show vector store statistics',
      '/history': 'Show conversation history',
      '/quit': 'Exit the chatbot'
    };
  }

  async initialize() {
    try {
      console.log(chalk.blue('🚀 Initializing Mood RAG Chatbot CLI...\n'));

      // Initialize configurations
      console.log(chalk.yellow('📡 Initializing Gemini API...'));
      await geminiConfig.initialize();
      console.log(chalk.green('✅ Gemini API initialized'));

      console.log(chalk.yellow('🗄️  Initializing Pinecone...'));
      await pineconeConfig.initialize();
      console.log(chalk.green('✅ Pinecone initialized'));

      console.log(chalk.yellow('🔗 Initializing LangChain...'));
      await langchainConfig.initialize();
      console.log(chalk.green('✅ LangChain initialized'));

      console.log(chalk.yellow('📚 Initializing Vector Store Service...'));
      await vectorStoreService.initialize();
      console.log(chalk.green('✅ Vector Store Service initialized'));

      console.log(chalk.yellow('💬 Initializing Chat Service...'));
      await chatService.initialize();
      console.log(chalk.green('✅ Chat Service initialized\n'));

      this.isInitialized = true;
      logger.info('CLI initialized successfully');
    } catch (error) {
      console.error(chalk.red('❌ Failed to initialize CLI:'), error.message);
      logger.error('Failed to initialize CLI:', error);
      throw error;
    }
  }

  setupReadline() {
    this.rl = readline.createInterface({
      input: process.stdin,
      output: process.stdout,
      prompt: chalk.cyan('You: ')
    });

    this.rl.on('line', async (input) => {
      const trimmedInput = input.trim();
      
      if (trimmedInput === '') {
        this.rl.prompt();
        return;
      }

      await this.handleInput(trimmedInput);
      this.rl.prompt();
    });

    this.rl.on('close', () => {
      console.log(chalk.yellow('\n👋 Goodbye! Thanks for chatting!'));
      process.exit(0);
    });
  }

  async handleInput(input) {
    try {
      // Handle commands
      if (input.startsWith('/')) {
        await this.handleCommand(input);
        return;
      }

      // Regular chat message
      console.log(chalk.gray('🤔 Processing your message...\n'));
      
      const result = await chatService.processMessage(input, this.conversationId);
      this.conversationId = result.conversationId;

      // Display mood analysis
      console.log(chalk.magenta(`😊 Detected mood: ${result.mood.mood} (${(result.mood.confidence * 100).toFixed(1)}% confidence)`));
      
      // Display response
      console.log(chalk.green(`🤖 Bot: ${result.response}\n`));
      
      // Show sources if available
      if (result.sources && result.sources.length > 0) {
        console.log(chalk.gray(`📚 Sources: ${result.sources.join(', ')}\n`));
      }

    } catch (error) {
      console.error(chalk.red('❌ Error processing message:'), error.message);
      logger.error('Error processing message:', error);
    }
  }

  async handleCommand(command) {
    const [cmd, ...args] = command.split(' ');
    const argText = args.join(' ');

    switch (cmd) {
      case '/help':
        this.showHelp();
        break;

      case '/mood':
        if (!argText) {
          console.log(chalk.red('❌ Please provide text to analyze. Usage: /mood <text>'));
          return;
        }
        await this.analyzeMood(argText);
        break;

      case '/joke':
        const mood = argText || 'neutral';
        await this.generateJoke(mood);
        break;

      case '/clear':
        await this.clearConversation();
        break;

      case '/new':
        this.startNewConversation();
        break;

      case '/stats':
        await this.showStats();
        break;

      case '/history':
        this.showHistory();
        break;

      case '/quit':
        this.rl.close();
        break;

      default:
        console.log(chalk.red(`❌ Unknown command: ${cmd}`));
        console.log(chalk.yellow('Type /help to see available commands'));
    }
  }

  showHelp() {
    console.log(chalk.blue('\n📖 Available Commands:'));
    console.log(chalk.blue('=' .repeat(40)));
    Object.entries(this.commands).forEach(([cmd, desc]) => {
      console.log(chalk.cyan(`${cmd.padEnd(20)} - ${desc}`));
    });
    console.log(chalk.blue('=' .repeat(40) + '\n'));
  }

  async analyzeMood(text) {
    try {
      console.log(chalk.yellow('🔍 Analyzing mood...\n'));
      const analysis = await moodAnalyzer.analyzeMood(text);
      
      console.log(chalk.magenta('😊 Mood Analysis Results:'));
      console.log(chalk.white(`   Primary mood: ${analysis.mood}`));
      console.log(chalk.white(`   Confidence: ${(analysis.confidence * 100).toFixed(1)}%`));
      console.log(chalk.white(`   Sentiment score: ${analysis.sentiment.score}`));
      
      if (analysis.aiAnalysis && analysis.aiAnalysis.reasoning) {
        console.log(chalk.white(`   AI reasoning: ${analysis.aiAnalysis.reasoning}`));
      }
      console.log();
    } catch (error) {
      console.error(chalk.red('❌ Error analyzing mood:'), error.message);
    }
  }

  async generateJoke(mood) {
    try {
      console.log(chalk.yellow(`😄 Generating a joke for mood: ${mood}...\n`));
      const joke = await jokeService.generateJoke(mood);
      
      console.log(chalk.green(`🎭 ${joke.joke}\n`));
    } catch (error) {
      console.error(chalk.red('❌ Error generating joke:'), error.message);
    }
  }

  async clearConversation() {
    if (this.conversationId) {
      try {
        await chatService.clearConversationMemory(this.conversationId);
        console.log(chalk.green('✅ Conversation memory cleared\n'));
      } catch (error) {
        console.error(chalk.red('❌ Error clearing conversation:'), error.message);
      }
    } else {
      console.log(chalk.yellow('⚠️  No active conversation to clear\n'));
    }
  }

  startNewConversation() {
    this.conversationId = null;
    console.log(chalk.green('✅ Started new conversation\n'));
  }

  async showStats() {
    try {
      console.log(chalk.yellow('📊 Fetching vector store statistics...\n'));
      const stats = await vectorStoreService.getDocumentStats();
      
      console.log(chalk.blue('📈 Vector Store Statistics:'));
      console.log(chalk.white(`   Total vectors: ${stats.totalVectors}`));
      console.log(chalk.white(`   Dimension: ${stats.dimension}`));
      console.log(chalk.white(`   Index fullness: ${(stats.indexFullness * 100).toFixed(2)}%\n`));
    } catch (error) {
      console.error(chalk.red('❌ Error fetching stats:'), error.message);
    }
  }

  showHistory() {
    if (!this.conversationId) {
      console.log(chalk.yellow('⚠️  No active conversation\n'));
      return;
    }

    const conversation = chatService.getConversation(this.conversationId);
    if (!conversation || conversation.messages.length === 0) {
      console.log(chalk.yellow('⚠️  No conversation history\n'));
      return;
    }

    console.log(chalk.blue('\n💬 Conversation History:'));
    console.log(chalk.blue('=' .repeat(50)));
    
    conversation.messages.forEach((msg, index) => {
      const timestamp = new Date(msg.timestamp).toLocaleTimeString();
      const role = msg.role === 'user' ? chalk.cyan('You') : chalk.green('Bot');
      console.log(`${role} [${timestamp}]: ${msg.content}`);
      
      if (msg.mood) {
        console.log(chalk.gray(`   (mood: ${msg.mood.mood || msg.mood})`));
      }
      console.log();
    });
    
    console.log(chalk.blue('=' .repeat(50) + '\n'));
  }

  showWelcome() {
    console.log(chalk.blue('\n🎉 Welcome to the Mood RAG Chatbot CLI!'));
    console.log(chalk.blue('=' .repeat(50)));
    console.log(chalk.white('This chatbot can:'));
    console.log(chalk.white('• Analyze your mood and provide appropriate responses'));
    console.log(chalk.white('• Tell jokes based on your emotional state'));
    console.log(chalk.white('• Use knowledge from uploaded documents to help you'));
    console.log(chalk.white('• Remember your conversation context'));
    console.log(chalk.blue('=' .repeat(50)));
    console.log(chalk.yellow('💡 Type /help to see available commands'));
    console.log(chalk.yellow('💡 Just type normally to start chatting!'));
    console.log(chalk.blue('=' .repeat(50) + '\n'));
  }

  async start() {
    try {
      await this.initialize();
      this.setupReadline();
      this.showWelcome();
      
      console.log(chalk.green('🚀 Chatbot is ready! Start typing to begin...\n'));
      this.rl.prompt();
      
    } catch (error) {
      console.error(chalk.red('❌ Failed to start CLI:'), error.message);
      process.exit(1);
    }
  }
}

// Start the CLI if this script is executed directly
if (require.main === module) {
  const cli = new ChatbotCLI();
  cli.start();
}

module.exports = ChatbotCLI;
