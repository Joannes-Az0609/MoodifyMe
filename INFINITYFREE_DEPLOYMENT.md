# 🚀 MoodifyMe InfinityFree Deployment Guide

## Why InfinityFree?
- ✅ **Completely FREE** - No hidden costs
- ✅ **PHP + MySQL included** - Pre-configured environment
- ✅ **cPanel access** - Easy file management
- ✅ **phpMyAdmin** - Database management
- ✅ **Simple setup** - Just upload files

## Step 1: Create InfinityFree Account

### 1.1 Sign Up
1. Go to [infinityfree.net](https://infinityfree.net)
2. Click "Create Account"
3. Fill in your details
4. Verify your email

### 1.2 Create Website
1. In InfinityFree control panel, click "Create Account"
2. Choose a subdomain (e.g., `moodifyme.epizy.com`)
3. Or use your own domain if you have one
4. Wait for account creation (usually instant)

## Step 2: Get Database Details

### 2.1 Access cPanel
1. Click "Control Panel" for your website
2. Login to cPanel

### 2.2 Create MySQL Database
1. In cPanel, find "MySQL Databases"
2. Create new database: `moodifyme`
3. Create database user with password
4. Add user to database with ALL PRIVILEGES
5. Note down these details:
   ```
   Database Host: sql200.epizy.com (or similar)
   Database Name: epiz_xxxxx_moodifyme
   Username: epiz_xxxxx_user
   Password: [your-password]
   ```

## Step 3: Prepare Your Files

### 3.1 Create Production Config
Create `config.infinityfree.php` with your database details:

```php
<?php
// Database Configuration (InfinityFree)
define('DB_HOST', 'sql200.epizy.com'); // Your actual host
define('DB_USER', 'epiz_xxxxx_user'); // Your actual username
define('DB_PASS', 'your_password'); // Your actual password
define('DB_NAME', 'epiz_xxxxx_moodifyme'); // Your actual database name

// Application Configuration
define('APP_NAME', 'MoodifyMe');
define('APP_URL', 'https://moodifyme.epizy.com'); // Your actual domain
define('APP_VERSION', '1.0.0');

// AI Assistant Configuration
define('AI_ASSISTANT_URL', 'https://moodifyme-bot.onrender.com');

// API Keys (same as your local config)
define('TMDB_API_KEY', 'a931731976a07c91bf2dc1208ed4ac3d');
define('SPOTIFY_CLIENT_ID', 'a0f9cf5c2f3e4bdb80bdc3213bab0035');
define('SPOTIFY_CLIENT_SECRET', '8ca23d17f6dc4324bc0823ab7ce297dd');
define('GOOGLE_CLIENT_ID', '1005843795519-95v3g07sj7rder70eb1ikavouk057rli.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');

// Security
error_reporting(0);
ini_set('display_errors', 0);
?>
```

### 3.2 Update Database Connection
Your `includes/db_connect.php` should detect InfinityFree:

```php
// Load appropriate configuration
if (strpos($_SERVER['HTTP_HOST'], '.epizy.com') !== false || 
    strpos($_SERVER['HTTP_HOST'], '.rf.gd') !== false) {
    require_once dirname(__DIR__) . '/config.infinityfree.php';
} else {
    require_once dirname(__DIR__) . '/config.php';
}
```

## Step 4: Upload Files

### 4.1 Using File Manager (Recommended)
1. In cPanel, open "File Manager"
2. Navigate to `htdocs` folder
3. Upload your entire MoodifyMe project
4. Extract if uploaded as ZIP

### 4.2 Using FTP (Alternative)
1. Get FTP details from InfinityFree control panel
2. Use FileZilla or similar FTP client
3. Upload all files to `htdocs` folder

### 4.3 File Structure Should Be:
```
htdocs/
├── api/
├── assets/
├── database/
├── includes/
├── pages/
├── config.php
├── config.infinityfree.php
├── index.php
└── ... (all other files)
```

## Step 5: Set Up Database

### 5.1 Import Database Schema
1. In cPanel, open "phpMyAdmin"
2. Select your database
3. Click "Import" tab
4. Upload `database/schema.sql`
5. Click "Go" to import

### 5.2 Or Use Migration Script
Visit: `https://yourdomain.epizy.com/database/migrate.php?migrate=1`

## Step 6: Update OAuth Settings

Update your Google OAuth settings:
- **Authorized redirect URIs:** `https://yourdomain.epizy.com/api/google_oauth_callback.php`

## Step 7: Test Your Deployment

1. **Visit your app:** `https://yourdomain.epizy.com`
2. **Test features:**
   - ✅ User registration/login
   - ✅ Database connectivity
   - ✅ AI Assistant integration
   - ✅ Recommendations

## InfinityFree Limitations

### ⚠️ **Be Aware Of:**
- **CPU limits** - 30 seconds execution time
- **File limits** - 10GB storage
- **Bandwidth** - Unlimited but fair use
- **Ads** - Small ads on free hosting
- **No HTTPS** - Only HTTP (unless you upgrade)

### 💡 **Optimization Tips:**
1. **Optimize images** - Compress before upload
2. **Minimize database queries** - Use efficient queries
3. **Cache results** - Implement simple caching
4. **Clean code** - Remove debug statements

## Troubleshooting

### Common Issues:

1. **Database Connection Errors:**
   - Double-check database credentials
   - Ensure user has ALL PRIVILEGES
   - Check if database host is correct

2. **File Upload Issues:**
   - Check file permissions (755 for folders, 644 for files)
   - Ensure htdocs is the web root
   - Clear browser cache

3. **AI Assistant CORS:**
   - InfinityFree might block some external requests
   - Test AI assistant functionality

### Performance Issues:
- **Use .htaccess** for caching
- **Optimize database** queries
- **Compress files** before upload

## Upgrading Later

When ready for better hosting:
1. **Remove ads** - Upgrade to premium
2. **Get HTTPS** - SSL certificate
3. **Better performance** - Dedicated resources
4. **Custom domain** - Professional appearance

---

🎉 **Your MoodifyMe app should now be live on InfinityFree!**

Visit your app at: `https://yourdomain.epizy.com`

**Total Cost: $0** 💰
