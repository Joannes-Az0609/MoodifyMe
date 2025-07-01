# 🚀 MoodifyMe Render Deployment Guide

## Prerequisites
- GitHub account with your MoodifyMe project
- Render account (free at [render.com](https://render.com))
- Your project pushed to GitHub

## Step 1: Prepare Your Repository

1. **Ensure all files are committed to GitHub:**
   ```bash
   git add .
   git commit -m "Prepare for Render deployment"
   git push origin main
   ```

2. **Files created for Render deployment:**
   - ✅ `render.yaml` - Render service configuration
   - ✅ `config.render.php` - Production configuration for Render
   - ✅ `database/schema.postgresql.sql` - PostgreSQL schema
   - ✅ Updated `includes/db_connect.php` - Multi-database support
   - ✅ Updated `composer.json` - Build scripts

## Step 2: Deploy to Render

### Option A: Using render.yaml (Recommended)

1. **Go to [Render Dashboard](https://dashboard.render.com)**
2. **Click "New" → "Blueprint"**
3. **Connect your GitHub repository**
4. **Select your MoodifyMe repository**
5. **Render will automatically detect the `render.yaml` file**
6. **Click "Apply" to deploy**

### Option B: Manual Setup

1. **Create Web Service:**
   - Go to Render Dashboard
   - Click "New" → "Web Service"
   - Connect GitHub and select your repository
   - Configure:
     - **Name:** `moodifyme-web`
     - **Environment:** `PHP`
     - **Build Command:** `composer install --no-dev && mkdir -p uploads logs cache`
     - **Start Command:** `php -S 0.0.0.0:$PORT -t .`

2. **Create PostgreSQL Database:**
   - Click "New" → "PostgreSQL"
   - **Name:** `moodifyme-db`
   - **Plan:** Free
   - **Database Name:** `moodifyme`
   - **User:** `moodifyme_user`

## Step 3: Configure Environment Variables

In your Render web service, add these environment variables:

### Required Variables:
```
APP_ENV=production
AI_ASSISTANT_URL=https://moodifyme-bot.onrender.com
```

### Database Variables (Auto-populated if using render.yaml):
```
DB_HOST=[from database]
DB_PORT=[from database]
DB_NAME=[from database]
DB_USER=[from database]
DB_PASS=[from database]
```

### Optional API Keys:
```
TMDB_API_KEY=a931731976a07c91bf2dc1208ed4ac3d
SPOTIFY_CLIENT_ID=a0f9cf5c2f3e4bdb80bdc3213bab0035
SPOTIFY_CLIENT_SECRET=8ca23d17f6dc4324bc0823ab7ce297dd
GOOGLE_CLIENT_ID=1005843795519-95v3g07sj7rder70eb1ikavouk057rli.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
```

## Step 4: Set Up Database

1. **Connect to your PostgreSQL database:**
   - Use the connection details from Render dashboard
   - Or use Render's web-based database console

2. **Import the schema:**
   ```sql
   -- Copy and paste contents of database/schema.postgresql.sql
   -- Or upload the file through Render's database console
   ```

3. **Verify tables are created:**
   ```sql
   \dt  -- List all tables
   ```

## Step 5: Update OAuth Settings

Update your Google OAuth settings:
- **Authorized redirect URIs:** `https://your-app-name.onrender.com/api/google_oauth_callback.php`
- Replace `your-app-name` with your actual Render service name

## Step 6: Test Your Deployment

1. **Visit your app:** `https://your-app-name.onrender.com`
2. **Test key features:**
   - ✅ User registration/login
   - ✅ Emotion detection
   - ✅ Recommendations
   - ✅ AI Assistant integration
   - ✅ Database connectivity

3. **Check health endpoint:** `https://your-app-name.onrender.com/health`

## Troubleshooting

### Common Issues:

1. **Database Connection Errors:**
   - Verify environment variables are set correctly
   - Check database is running and accessible
   - Ensure PostgreSQL schema is imported

2. **Build Failures:**
   - Check build logs in Render dashboard
   - Ensure composer.json is valid
   - Verify PHP version compatibility

3. **AI Assistant Not Working:**
   - Verify `AI_ASSISTANT_URL` environment variable
   - Check CORS settings in config.render.php
   - Test assistant URL directly

### Logs and Debugging:
- **View logs:** Render Dashboard → Your Service → Logs
- **Database logs:** Render Dashboard → Your Database → Logs
- **Health check:** Visit `/health` endpoint

## Performance Optimization

### Free Tier Limitations:
- **Web Service:** 512MB RAM, sleeps after 15 minutes of inactivity
- **Database:** 1GB storage, 97 connection limit
- **Bandwidth:** 100GB/month

### Optimization Tips:
1. **Enable caching** in your PHP code
2. **Optimize database queries**
3. **Use CDN** for static assets
4. **Implement connection pooling**

## Scaling Up

When ready to scale:
1. **Upgrade to paid plans** for better performance
2. **Add Redis** for session storage and caching
3. **Use external CDN** for assets
4. **Implement load balancing** for multiple instances

## Support

- **Render Documentation:** [render.com/docs](https://render.com/docs)
- **Community:** [community.render.com](https://community.render.com)
- **Status:** [status.render.com](https://status.render.com)

---

🎉 **Your MoodifyMe app should now be live on Render!**

Visit your app at: `https://your-app-name.onrender.com`
