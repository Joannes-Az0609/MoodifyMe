# MoodifyMe Free Hosting Deployment Guide

## 🚀 Quick Start - Railway Deployment (Recommended)

Railway is perfect for your MoodifyMe project because it supports both PHP and Node.js with databases.

### Prerequisites
- GitHub account
- Railway account (free at [railway.app](https://railway.app))
- Your project pushed to GitHub

### Step 1: Prepare Your Project

#### 1.1 Create Railway Configuration Files

Create `railway.toml` in your root directory:
```toml
[build]
builder = "nixpacks"

[deploy]
healthcheckPath = "/"
healthcheckTimeout = 100
restartPolicyType = "never"

[[services]]
name = "moodifyme-web"
source = "."
variables = { PHP_VERSION = "8.1" }

[[services]]
name = "moodifyme-ai"
source = "./MoodifyMe Assistant"
variables = { NODE_VERSION = "18" }
```

#### 1.2 Update Configuration for Production

Update `config.php`:
```php
<?php
// Use environment variables for production
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'moodifyme');

// Use Railway's provided URL or custom domain
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/MoodifyMe');

// API Keys from environment variables
define('TMDB_API_KEY', $_ENV['TMDB_API_KEY'] ?? 'a931731976a07c91bf2dc1208ed4ac3d');
define('SPOTIFY_CLIENT_ID', $_ENV['SPOTIFY_CLIENT_ID'] ?? 'your_client_id');
define('SPOTIFY_CLIENT_SECRET', $_ENV['SPOTIFY_CLIENT_SECRET'] ?? 'your_client_secret');
define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID'] ?? 'your_google_client_id');
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? 'your_google_client_secret');

// Production settings
ini_set('session.cookie_secure', 1); // HTTPS only
error_reporting(0); // Hide errors in production
ini_set('display_errors', 0);
?>
```

### Step 2: Deploy to Railway

#### 2.1 Create Railway Project
1. Go to [railway.app](https://railway.app)
2. Sign up with GitHub
3. Click "New Project"
4. Select "Deploy from GitHub repo"
5. Choose your MoodifyMe repository

#### 2.2 Add Database Service
1. In your Railway project dashboard
2. Click "New Service"
3. Select "Database" → "MySQL"
4. Railway will provide connection details

#### 2.3 Configure Environment Variables
In Railway dashboard, go to Variables tab and add:
```
DB_HOST=your_mysql_host_from_railway
DB_USER=your_mysql_user_from_railway
DB_PASS=your_mysql_password_from_railway
DB_NAME=railway
APP_URL=https://your-app-name.up.railway.app
TMDB_API_KEY=a931731976a07c91bf2dc1208ed4ac3d
SPOTIFY_CLIENT_ID=your_spotify_client_id
SPOTIFY_CLIENT_SECRET=your_spotify_client_secret
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
```

#### 2.4 Deploy AI Assistant Separately
1. Create another service in same project
2. Set source to `./MoodifyMe Assistant`
3. Add Node.js environment variables:
```
GEMINI_API_KEY=your_gemini_api_key
PINECONE_API_KEY=your_pinecone_api_key
PINECONE_ENVIRONMENT=your_pinecone_environment
PORT=3000
```

### Step 3: Database Setup

#### 3.1 Import Database Schema
1. Connect to Railway MySQL using provided credentials
2. Import your schema files:
   - `database/schema.sql`
   - `database/seed.sql`
   - `database/google_oauth_schema.sql`
   - `database/contact_messages_schema.sql`

#### 3.2 Update OAuth Redirect URLs
Update your Google OAuth settings:
- Authorized redirect URIs: `https://your-app-name.up.railway.app/api/google_oauth_callback.php`

## 🌐 Alternative Hosting Options

### Option 2: Vercel + PlanetScale

#### Vercel Setup (Frontend + API)
1. Install Vercel CLI: `npm i -g vercel`
2. Create `vercel.json`:
```json
{
  "functions": {
    "api/*.php": {
      "runtime": "vercel-php@0.6.0"
    }
  },
  "routes": [
    { "src": "/(.*)", "dest": "/$1" }
  ]
}
```

#### PlanetScale Setup (Database)
1. Sign up at [planetscale.com](https://planetscale.com)
2. Create database
3. Get connection string
4. Import schema using PlanetScale CLI

### Option 3: Heroku (Limited Free Tier)

Create `composer.json` for PHP buildpack:
```json
{
  "require": {
    "php": "^8.1"
  }
}
```

Create `Procfile`:
```
web: vendor/bin/heroku-php-apache2
```

### Option 4: Netlify + Supabase

#### Netlify (Frontend)
- Perfect for static files and client-side functionality
- Free tier: 100GB bandwidth/month

#### Supabase (Backend + Database)
- PostgreSQL database
- Built-in authentication
- Real-time subscriptions

## 🔧 Production Optimizations

### Security Enhancements
```php
// Add to config.php
if (isset($_ENV['RAILWAY_ENVIRONMENT'])) {
    // Production security headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
```

### Performance Optimizations
1. **Enable PHP OPcache**
2. **Compress assets** (CSS/JS minification)
3. **Use CDN** for static assets
4. **Database indexing** for better performance

### Environment-Specific Configuration
```php
// Detect environment
$isProduction = isset($_ENV['RAILWAY_ENVIRONMENT']) || 
                isset($_ENV['VERCEL_ENV']) || 
                $_SERVER['HTTP_HOST'] !== 'localhost';

if ($isProduction) {
    // Production settings
    error_reporting(0);
    ini_set('display_errors', 0);
    define('APP_DEBUG', false);
} else {
    // Development settings
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    define('APP_DEBUG', true);
}
```

## 📊 Cost Comparison

| Platform | Free Tier | Database | Custom Domain | Best For |
|----------|-----------|----------|---------------|----------|
| **Railway** | $5 credit/month | MySQL included | ✅ Free | Full-stack apps |
| **Vercel** | 100GB bandwidth | Need external DB | ✅ Free | Frontend + API |
| **Render** | 750 hours/month | PostgreSQL 90 days | ✅ Free | Web services |
| **Netlify** | 100GB bandwidth | Need external DB | ✅ Free | Static sites |

## 🚨 Important Notes

### API Keys Security
- Never commit API keys to GitHub
- Use environment variables
- Rotate keys regularly

### Database Considerations
- Railway: MySQL included
- Vercel: Need external database (PlanetScale recommended)
- Render: PostgreSQL for 90 days free

### SSL Certificates
- All platforms provide free SSL
- Automatic HTTPS redirect
- Update OAuth redirect URLs to HTTPS

## 🎯 Recommended Deployment Strategy

1. **Start with Railway** - easiest for your PHP + Node.js stack
2. **Use environment variables** for all sensitive data
3. **Set up monitoring** and error tracking
4. **Configure backups** for your database
5. **Test thoroughly** before going live

## 📞 Support Resources

- **Railway**: [docs.railway.app](https://docs.railway.app)
- **Vercel**: [vercel.com/docs](https://vercel.com/docs)
- **PlanetScale**: [planetscale.com/docs](https://planetscale.com/docs)
- **Community**: Stack Overflow, Discord servers

Your MoodifyMe project will be live and accessible worldwide within minutes using any of these platforms!
