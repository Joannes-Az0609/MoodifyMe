# 🚀 MoodifyMe Vercel Deployment Guide

## Prerequisites
- GitHub account with your MoodifyMe project
- Vercel account (free at [vercel.com](https://vercel.com))
- PlanetScale account (free at [planetscale.com](https://planetscale.com))

## Step 1: Set Up PlanetScale Database (Free MySQL)

### 1.1 Create PlanetScale Account
1. Go to [planetscale.com](https://planetscale.com)
2. Sign up with GitHub (recommended)
3. Create a new database:
   - **Name:** `moodifyme`
   - **Region:** Choose closest to your users

### 1.2 Get Database Connection Details
1. In PlanetScale dashboard, go to your database
2. Click "Connect" → "Create password"
3. Select "General" connection type
4. Copy the connection details:
   ```
   Host: [your-host].us-east-2.psdb.cloud
   Username: [your-username]
   Password: [your-password]
   Database: moodifyme
   ```

### 1.3 Import Database Schema
1. Use PlanetScale CLI or web console
2. Import your `database/schema.sql` file
3. Or run the migration after deployment

## Step 2: Deploy to Vercel

### 2.1 Connect GitHub to Vercel
1. Go to [vercel.com](https://vercel.com)
2. Sign up/login with GitHub
3. Click "New Project"
4. Import your MoodifyMe repository

### 2.2 Configure Environment Variables
In Vercel project settings, add these environment variables:

#### Required Database Variables:
```
DB_HOST=[your-planetscale-host]
DB_USER=[your-planetscale-username]
DB_PASS=[your-planetscale-password]
DB_NAME=moodifyme
```

#### Application Variables:
```
APP_URL=https://your-app-name.vercel.app
AI_ASSISTANT_URL=https://moodifyme-bot.onrender.com
APP_ENV=production
VERCEL=1
```

#### Optional API Keys:
```
TMDB_API_KEY=a931731976a07c91bf2dc1208ed4ac3d
SPOTIFY_CLIENT_ID=a0f9cf5c2f3e4bdb80bdc3213bab0035
SPOTIFY_CLIENT_SECRET=8ca23d17f6dc4324bc0823ab7ce297dd
GOOGLE_CLIENT_ID=1005843795519-95v3g07sj7rder70eb1ikavouk057rli.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
```

### 2.3 Deploy
1. Click "Deploy" in Vercel
2. Wait for deployment to complete
3. Your app will be available at: `https://your-app-name.vercel.app`

## Step 3: Set Up Database

### 3.1 Run Migration
Visit: `https://your-app-name.vercel.app/database/migrate.php?migrate=1`

This will create all necessary tables in your PlanetScale database.

### 3.2 Verify Database
Check that all tables were created successfully in PlanetScale dashboard.

## Step 4: Update OAuth Settings

Update your Google OAuth settings:
- **Authorized redirect URIs:** `https://your-app-name.vercel.app/api/google_oauth_callback.php`

## Step 5: Test Your Deployment

1. **Visit your app:** `https://your-app-name.vercel.app`
2. **Test key features:**
   - ✅ User registration/login
   - ✅ Emotion detection
   - ✅ Recommendations
   - ✅ AI Assistant integration
   - ✅ Database connectivity

## Advantages of Vercel + PlanetScale

### ✅ **Vercel Benefits:**
- **Free tier:** Generous limits for small projects
- **Automatic deployments:** Push to GitHub = auto deploy
- **Global CDN:** Fast worldwide performance
- **HTTPS:** Automatic SSL certificates
- **PHP support:** Built-in PHP runtime

### ✅ **PlanetScale Benefits:**
- **Free MySQL:** 5GB storage, 1 billion reads/month
- **Serverless:** Scales automatically
- **Branching:** Database branching like Git
- **No connection limits:** Unlike traditional MySQL
- **Backup included:** Automatic backups

## Troubleshooting

### Common Issues:

1. **Database Connection Errors:**
   - Verify PlanetScale connection details
   - Check environment variables in Vercel
   - Ensure database is not sleeping

2. **Build Failures:**
   - Check Vercel build logs
   - Verify vercel.json configuration
   - Ensure PHP syntax is correct

3. **AI Assistant Not Working:**
   - Verify `AI_ASSISTANT_URL` environment variable
   - Check CORS settings in config.vercel.php
   - Test assistant URL directly

### Performance Tips:
1. **Enable caching** in your PHP code
2. **Optimize database queries**
3. **Use Vercel's edge functions** for API endpoints
4. **Implement connection pooling**

## Scaling Up

When ready to scale:
1. **Upgrade PlanetScale** for more storage/performance
2. **Use Vercel Pro** for better performance
3. **Add Redis** for session storage
4. **Implement database branching** for staging

---

🎉 **Your MoodifyMe app should now be live on Vercel with PlanetScale!**

Visit your app at: `https://your-app-name.vercel.app`
