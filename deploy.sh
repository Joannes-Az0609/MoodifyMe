#!/bin/bash

# MoodifyMe Deployment Script
# This script helps prepare your project for deployment

echo "🚀 MoodifyMe Deployment Preparation"
echo "=================================="

# Check if git is initialized
if [ ! -d ".git" ]; then
    echo "❌ Git repository not found. Initializing..."
    git init
    git add .
    git commit -m "Initial commit"
    echo "✅ Git repository initialized"
fi

# Create .env.example file
echo "📝 Creating .env.example file..."
cat > .env.example << EOL
# Database Configuration
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=moodifyme

# Application URL
APP_URL=https://your-app-name.up.railway.app

# API Keys
TMDB_API_KEY=your_tmdb_api_key
SPOTIFY_CLIENT_ID=your_spotify_client_id
SPOTIFY_CLIENT_SECRET=your_spotify_client_secret
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret

# AI Assistant (Node.js service)
GEMINI_API_KEY=your_gemini_api_key
PINECONE_API_KEY=your_pinecone_api_key
PINECONE_ENVIRONMENT=your_pinecone_environment
AI_ASSISTANT_URL=https://your-ai-service.up.railway.app

# Email Configuration (optional)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
SMTP_ENCRYPTION=tls
EOL

# Create .gitignore if it doesn't exist
if [ ! -f ".gitignore" ]; then
    echo "📝 Creating .gitignore file..."
    cat > .gitignore << EOL
# Environment files
.env
.env.local
.env.production

# Logs
*.log
logs/
/tmp/

# Dependencies
node_modules/
vendor/

# IDE files
.vscode/
.idea/
*.swp
*.swo

# OS files
.DS_Store
Thumbs.db

# Cache
cache/
*.cache

# Uploads
uploads/
temp/

# Database
*.sql.backup
*.db

# API Keys (backup)
config.local.php
EOL
    echo "✅ .gitignore created"
fi

# Check for sensitive data
echo "🔍 Checking for sensitive data..."
if grep -r "your_.*_api_key\|your_.*_secret\|localhost" --include="*.php" --include="*.js" .; then
    echo "⚠️  Warning: Found potential sensitive data in files"
    echo "   Make sure to use environment variables in production"
fi

# Display deployment options
echo ""
echo "🌐 Deployment Options:"
echo "====================="
echo ""
echo "1. 🚂 Railway (Recommended)"
echo "   - Supports PHP + Node.js + MySQL"
echo "   - Free $5 credit monthly"
echo "   - Visit: https://railway.app"
echo "   - Connect your GitHub repo"
echo ""
echo "2. ⚡ Vercel + PlanetScale"
echo "   - Great for modern stack"
echo "   - Vercel: https://vercel.com"
echo "   - PlanetScale: https://planetscale.com"
echo ""
echo "3. 🎨 Render"
echo "   - 750 hours free monthly"
echo "   - Visit: https://render.com"
echo ""
echo "4. 🟣 Heroku"
echo "   - Limited free tier"
echo "   - Visit: https://heroku.com"
echo ""

# Check if Railway CLI is installed
if command -v railway &> /dev/null; then
    echo "✅ Railway CLI found"
    echo "   Run 'railway login' and 'railway up' to deploy"
else
    echo "📦 Install Railway CLI:"
    echo "   npm install -g @railway/cli"
    echo "   or visit: https://docs.railway.app/develop/cli"
fi

# Check if Vercel CLI is installed
if command -v vercel &> /dev/null; then
    echo "✅ Vercel CLI found"
    echo "   Run 'vercel' to deploy"
else
    echo "📦 Install Vercel CLI:"
    echo "   npm install -g vercel"
fi

echo ""
echo "📋 Next Steps:"
echo "=============="
echo "1. Push your code to GitHub"
echo "2. Sign up for your chosen hosting platform"
echo "3. Connect your GitHub repository"
echo "4. Set up environment variables"
echo "5. Deploy your database schema"
echo "6. Test your deployment"
echo ""
echo "📖 For detailed instructions, see DEPLOYMENT_GUIDE.md"
echo ""
echo "🎉 Good luck with your deployment!"
