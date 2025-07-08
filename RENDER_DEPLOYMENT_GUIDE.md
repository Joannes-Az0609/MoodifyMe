# 🚀 MoodifyMe Render Deployment Guide

## Overview
This guide will help you deploy your MoodifyMe application to Render.com, a modern cloud platform that's perfect for PHP applications with databases.

## Prerequisites
- ✅ GitHub account with your MoodifyMe code
- ✅ Render account (free at [render.com](https://render.com))
- ✅ Your project pushed to GitHub

## 🎯 Quick Deployment Steps

### Step 1: Prepare Your Repository
Make sure these files are in your repository root:
- ✅ `render.yaml` (created)
- ✅ `config.render.php` (created)
- ✅ `composer.json` (exists)

### Step 2: Create Render Services

#### Option A: Using render.yaml (Recommended)
1. Go to [render.com](https://render.com) and sign in
2. Click "New" → "Blueprint"
3. Connect your GitHub repository
4. Render will automatically detect the `render.yaml` file
5. Click "Apply" to create all services

#### Option B: Manual Setup
1. **Create Database First:**
   - Click "New" → "PostgreSQL"
   - Name: `moodifyme-db`
   - Plan: Free
   - Note the connection details

2. **Create Web Service:**
   - Click "New" → "Web Service"
   - Connect your GitHub repository
   - Name: `moodifyme-web`
   - Environment: PHP
   - Build Command: `composer install --no-dev --optimize-autoloader`
   - Start Command: `php -S 0.0.0.0:$PORT -t .`

### Step 3: Configure Environment Variables

In your Render web service dashboard, add these environment variables:

#### Required Variables:
```bash
RENDER=true
PHP_VERSION=8.1
```

#### Database Variables (auto-filled if using Blueprint):
```bash
DATABASE_URL=postgresql://user:pass@host:port/dbname
# OR individual variables:
DB_HOST=your_postgres_host
DB_USER=your_postgres_user
DB_PASS=your_postgres_password
DB_NAME=your_postgres_database
```

#### API Keys (add your actual keys):
```bash
TMDB_API_KEY=a931731976a07c91bf2dc1208ed4ac3d
SPOTIFY_CLIENT_ID=your_spotify_client_id
SPOTIFY_CLIENT_SECRET=your_spotify_client_secret
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
```

#### Optional Variables:
```bash
AI_ASSISTANT_URL=https://moodifyme-bot.onrender.com
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
```

### Step 4: Database Setup

#### Import Your Database Schema:
1. Connect to your Render PostgreSQL database using the provided connection string
2. You'll need to convert your MySQL schema to PostgreSQL format

**Option A: Use a tool like pgloader or manual conversion**
**Option B: Recreate tables manually using PostgreSQL syntax**

#### Key Schema Differences (MySQL → PostgreSQL):
```sql
-- MySQL
AUTO_INCREMENT → SERIAL
DATETIME → TIMESTAMP
TEXT → TEXT (same)
VARCHAR(255) → VARCHAR(255) (same)

-- Example conversion:
-- MySQL:
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- PostgreSQL:
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Step 5: Update OAuth Settings

Update your Google OAuth settings:
- **Authorized JavaScript origins:** `https://your-service-name.onrender.com`
- **Authorized redirect URIs:** `https://your-service-name.onrender.com/api/google_oauth_callback.php`

### Step 6: Test Your Deployment

1. **Health Check:** Visit `https://your-service-name.onrender.com/health`
2. **Main App:** Visit `https://your-service-name.onrender.com`
3. **Database Connection:** Try logging in or registering

## 🔧 Troubleshooting

### Common Issues:

#### 1. Database Connection Errors
```bash
# Check your DATABASE_URL format:
postgresql://username:password@hostname:port/database_name
```

#### 2. PHP Version Issues
```bash
# Ensure PHP_VERSION is set to 8.1 in environment variables
PHP_VERSION=8.1
```

#### 3. File Permissions
```bash
# Render automatically handles file permissions
# No action needed
```

#### 4. Session Issues
```bash
# Make sure session.cookie_secure is set correctly
# This is handled in config.render.php
```

### Debugging Steps:
1. Check Render logs in the dashboard
2. Visit `/health` endpoint to verify configuration
3. Check environment variables are set correctly
4. Verify database connection string

## 🚀 Performance Optimizations

### 1. Enable OPcache (already configured)
### 2. Use Render's CDN for static assets
### 3. Optimize database queries
### 4. Enable compression (already configured)

## 💰 Render Free Tier Limits

- **Web Services:** 750 hours/month
- **PostgreSQL:** 1GB storage, 1 month retention
- **Bandwidth:** 100GB/month
- **Build time:** 500 minutes/month

## 🔄 Continuous Deployment

Render automatically deploys when you push to your main branch:
1. Push changes to GitHub
2. Render detects changes
3. Automatic build and deploy
4. Zero-downtime deployment

## 📊 Monitoring

### Built-in Monitoring:
- Service metrics in Render dashboard
- Automatic health checks
- Log aggregation
- Performance metrics

### Custom Monitoring:
Add to your application:
```php
// Log important events
error_log("User login: " . $user_id);

// Track performance
$start_time = microtime(true);
// ... your code ...
$execution_time = microtime(true) - $start_time;
error_log("Execution time: " . $execution_time);
```

## 🔐 Security Best Practices

### Already Implemented:
- ✅ HTTPS enforced
- ✅ Security headers set
- ✅ Environment variables for secrets
- ✅ Session security configured

### Additional Recommendations:
- Regularly update dependencies
- Monitor for security vulnerabilities
- Use strong passwords for database
- Enable 2FA on Render account

## 🎉 Success!

Your MoodifyMe application should now be live on Render! 

**Next Steps:**
1. Test all functionality
2. Set up monitoring
3. Configure custom domain (optional)
4. Set up backups
5. Monitor performance and costs

## 📞 Support Resources

- **Render Docs:** [render.com/docs](https://render.com/docs)
- **Render Community:** [community.render.com](https://community.render.com)
- **PHP on Render:** [render.com/docs/php](https://render.com/docs/php)

Your MoodifyMe app will be accessible at: `https://your-service-name.onrender.com`
