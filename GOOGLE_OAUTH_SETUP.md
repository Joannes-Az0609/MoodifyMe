# Google OAuth Setup Guide for MoodifyMe

## 🚀 Overview

Google OAuth has been successfully integrated into MoodifyMe! Users can now sign up and log in using their Google accounts for a seamless authentication experience.

## 📋 Setup Instructions

### Step 1: Create Google Cloud Project

1. **Go to Google Cloud Console**
   - Visit [https://console.cloud.google.com/](https://console.cloud.google.com/)
   - Sign in with your Google account

2. **Create a New Project**
   - Click "Select a project" → "New Project"
   - Project name: `MoodifyMe OAuth`
   - Click "Create"

### Step 2: Enable Google+ API

1. **Navigate to APIs & Services**
   - In the left sidebar, click "APIs & Services" → "Library"
   - Search for "Google+ API"
   - Click on it and press "Enable"

2. **Also Enable Google OAuth2 API**
   - Search for "Google OAuth2 API"
   - Click and enable it

### Step 3: Configure OAuth Consent Screen

1. **Go to OAuth Consent Screen**
   - APIs & Services → OAuth consent screen
   - Choose "External" (for public use)
   - Click "Create"

2. **Fill Required Information**
   - App name: `MoodifyMe`
   - User support email: Your email
   - App logo: Upload MoodifyMe logo (optional)
   - App domain: `http://localhost` (for development)
   - Developer contact: Your email
   - Click "Save and Continue"

3. **Scopes**
   - Click "Add or Remove Scopes"
   - Add these scopes:
     - `openid`
     - `email`
     - `profile`
   - Click "Save and Continue"

4. **Test Users** (for development)
   - Add your email and any test user emails
   - Click "Save and Continue"

### Step 4: Create OAuth Credentials

1. **Go to Credentials**
   - APIs & Services → Credentials
   - Click "Create Credentials" → "OAuth 2.0 Client IDs"

2. **Configure OAuth Client**
   - Application type: "Web application"
   - Name: `MoodifyMe Web Client`
   
3. **Add Authorized Redirect URIs**
   - Click "Add URI"
   - Add: `http://localhost/MoodifyMe/api/google_oauth_callback.php`
   - For production, also add: `https://yourdomain.com/api/google_oauth_callback.php`

4. **Save and Get Credentials**
   - Click "Create"
   - Copy the Client ID and Client Secret

### Step 5: Update MoodifyMe Configuration

1. **Open `config.php`**
   - Replace `your_google_client_id_here` with your actual Client ID
   - Replace `your_google_client_secret_here` with your actual Client Secret

```php
// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'your_actual_client_id_here');
define('GOOGLE_CLIENT_SECRET', 'your_actual_client_secret_here');
```

### Step 6: Update Database Schema

1. **Run the Database Update**
   - Open phpMyAdmin or your MySQL client
   - Run the SQL file: `database/google_oauth_schema.sql`
   - This adds OAuth support to your users table

```sql
-- Or run this command in your MySQL client:
SOURCE /path/to/MoodifyMe/database/google_oauth_schema.sql;
```

## 🧪 Testing

### Test the Integration

1. **Visit Login Page**
   - Go to `http://localhost/MoodifyMe/pages/login.php`
   - You should see a "Continue with Google" button

2. **Test Google Sign-In**
   - Click the Google button
   - Sign in with your Google account
   - You should be redirected back to MoodifyMe dashboard

3. **Check Database**
   - Verify the user was created in the `users` table
   - Check `oauth_tokens` and `social_logins` tables for data

## 🔧 Features Included

### ✅ What's Working

- **Google Sign-Up**: New users can register with Google
- **Google Sign-In**: Existing users can login with Google
- **Account Linking**: Existing email accounts get linked to Google
- **Secure Tokens**: OAuth tokens are stored securely
- **User Data**: Profile pictures and verified emails from Google
- **Session Management**: Seamless session handling
- **Error Handling**: Comprehensive error handling and logging

### 🎨 UI Features

- **Modern Google Button**: Official Google design guidelines
- **Responsive Design**: Works on all devices
- **Smooth Animations**: Professional hover effects
- **Clean Integration**: Fits perfectly with existing design

### 🔒 Security Features

- **CSRF Protection**: State parameter prevents attacks
- **Secure Storage**: Encrypted token storage
- **Session Security**: Proper session management
- **Error Logging**: Comprehensive error tracking

## 📱 User Experience

### For New Users
1. Click "Sign up with Google"
2. Authorize MoodifyMe access
3. Automatically redirected to dashboard
4. Profile picture and email pre-filled

### For Existing Users
1. Click "Continue with Google"
2. If email matches, account gets linked
3. Future logins use Google seamlessly

## 🚀 Production Deployment

### Update for Production

1. **Update Redirect URI**
   - In Google Cloud Console, add production URL
   - Update `config.php` with production domain

2. **SSL Certificate**
   - Ensure HTTPS is enabled
   - Update `APP_URL` in config.php

3. **Environment Variables**
   - Store credentials in environment variables
   - Never commit real credentials to version control

## 🔍 Troubleshooting

### Common Issues

1. **"redirect_uri_mismatch" Error**
   - Check redirect URI in Google Console matches exactly
   - Ensure no trailing slashes

2. **"invalid_client" Error**
   - Verify Client ID and Secret are correct
   - Check if APIs are enabled

3. **"access_denied" Error**
   - User cancelled authorization
   - Check OAuth consent screen configuration

### Debug Mode

Enable debug logging by adding to `config.php`:
```php
// Enable OAuth debugging
define('OAUTH_DEBUG', true);
```

## 📊 Database Schema

### New Tables Created

- `oauth_tokens`: Stores access/refresh tokens
- `social_logins`: Logs all social login attempts

### Modified Tables

- `users`: Added Google ID, OAuth provider, avatar URL, email verification

## 🎉 Success!

Your MoodifyMe application now supports Google OAuth! Users can:
- ✅ Sign up instantly with Google
- ✅ Login seamlessly 
- ✅ Have verified email addresses
- ✅ Get profile pictures automatically
- ✅ Enjoy a modern, secure authentication experience

The integration follows Google's best practices and provides a professional, user-friendly authentication system for your mood tracking application! 🎭✨
