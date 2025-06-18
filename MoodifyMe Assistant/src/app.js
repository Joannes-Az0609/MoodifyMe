const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
require('dotenv').config();

// Import configurations
const geminiConfig = require('./config/gemini');
const pineconeConfig = require('./config/database');
const langchainConfig = require('./config/langchain');

// Import services
const vectorStoreService = require('./services/vectorStore');
const chatService = require('./services/chatService');

// Import routes
const chatRoutes = require('./routes/chat');
const healthRoutes = require('./routes/health');

// Import middleware
const { errorHandler, notFoundHandler } = require('./middleware/errorHandler');
const logger = require('./utils/logger');

class App {
  constructor() {
    this.app = express();
    this.port = process.env.PORT || 3000;
    this.isInitialized = false;
  }

  async initialize() {
    try {
      logger.info('Starting application initialization...');

      // Initialize configurations
      await geminiConfig.initialize();
      await pineconeConfig.initialize();
      await langchainConfig.initialize();

      // Initialize services
      await vectorStoreService.initialize();
      await chatService.initialize();

      this.isInitialized = true;
      logger.info('Application initialized successfully');
    } catch (error) {
      logger.error('Failed to initialize application:', error);
      throw error;
    }
  }

  setupMiddleware() {
    // Security middleware
    this.app.use(helmet({
      contentSecurityPolicy: false, // Disable CSP for API
      crossOriginEmbedderPolicy: false
    }));

    // CORS configuration
    this.app.use(cors({
      origin: process.env.ALLOWED_ORIGINS?.split(',') || '*',
      methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
      allowedHeaders: ['Content-Type', 'Authorization'],
      credentials: true
    }));

    // Body parsing middleware
    this.app.use(express.json({ limit: '10mb' }));
    this.app.use(express.urlencoded({ extended: true, limit: '10mb' }));

    // Serve static files for web interface
    this.app.use(express.static('public'));

    // Request logging middleware
    this.app.use((req, res, next) => {
      logger.info(`${req.method} ${req.url}`, {
        ip: req.ip,
        userAgent: req.get('User-Agent'),
        body: req.method === 'POST' ? req.body : undefined
      });
      next();
    });

    // Health check middleware (before initialization check)
    this.app.get('/ping', (req, res) => {
      res.json({ status: 'pong', timestamp: new Date().toISOString() });
    });

    // Initialization check middleware
    this.app.use((req, res, next) => {
      if (!this.isInitialized && !req.url.startsWith('/health') && req.url !== '/ping') {
        return res.status(503).json({
          error: 'Service unavailable',
          message: 'Application is still initializing'
        });
      }
      next();
    });
  }

  setupRoutes() {
    // API routes
    this.app.use('/api/chat', chatRoutes);
    this.app.use('/api/health', healthRoutes);

    // Root endpoint
    this.app.get('/', (req, res) => {
      res.json({
        name: 'Mood RAG Chatbot API',
        version: '1.0.0',
        description: 'A RAG-powered chatbot that improves mood and tells jokes',
        endpoints: {
          chat: '/api/chat',
          health: '/api/health',
          ping: '/ping'
        },
        status: this.isInitialized ? 'ready' : 'initializing'
      });
    });

    // 404 handler
    this.app.use(notFoundHandler);

    // Error handling middleware (must be last)
    this.app.use(errorHandler);
  }

  async start() {
    try {
      // Setup middleware first
      this.setupMiddleware();

      // Initialize the application
      await this.initialize();

      // Setup routes after initialization
      this.setupRoutes();

      // Start the server
      this.server = this.app.listen(this.port, () => {
        logger.info(`Server running on port ${this.port}`);
        logger.info(`Environment: ${process.env.NODE_ENV || 'development'}`);
        logger.info('API endpoints:');
        logger.info(`  - Health: http://localhost:${this.port}/api/health`);
        logger.info(`  - Chat: http://localhost:${this.port}/api/chat`);
        logger.info(`  - Ping: http://localhost:${this.port}/ping`);
      });

      // Graceful shutdown handling
      this.setupGracefulShutdown();

    } catch (error) {
      logger.error('Failed to start server:', error);
      process.exit(1);
    }
  }

  setupGracefulShutdown() {
    const shutdown = async (signal) => {
      logger.info(`Received ${signal}. Starting graceful shutdown...`);
      
      if (this.server) {
        this.server.close(() => {
          logger.info('HTTP server closed');
          process.exit(0);
        });

        // Force close after 10 seconds
        setTimeout(() => {
          logger.error('Could not close connections in time, forcefully shutting down');
          process.exit(1);
        }, 10000);
      } else {
        process.exit(0);
      }
    };

    process.on('SIGTERM', () => shutdown('SIGTERM'));
    process.on('SIGINT', () => shutdown('SIGINT'));

    // Handle uncaught exceptions
    process.on('uncaughtException', (error) => {
      logger.error('Uncaught Exception:', error);
      process.exit(1);
    });

    process.on('unhandledRejection', (reason, promise) => {
      logger.error('Unhandled Rejection at:', promise, 'reason:', reason);
      process.exit(1);
    });
  }
}

// Create and start the application
const app = new App();

if (require.main === module) {
  app.start();
}

module.exports = app;
