# 🚀 MoodifyMe Render Deployment Checklist

## Pre-Deployment Checklist

### ✅ Repository Preparation
- [ ] Code pushed to GitHub
- [ ] `render.yaml` file in root directory
- [ ] `config.render.php` file created
- [ ] `build.sh` script created and executable
- [ ] `database/postgresql_schema.sql` ready for import

### ✅ Render Account Setup
- [ ] Render account created at [render.com](https://render.com)
- [ ] GitHub connected to Render
- [ ] Payment method added (for overages, free tier available)

## Deployment Steps

### 1. Create Services on Render

#### Option A: Blueprint Deployment (Recommended)
- [ ] Go to Render Dashboard
- [ ] Click "New" → "Blueprint"
- [ ] Select your GitHub repository
- [ ] Render detects `render.yaml`
- [ ] Click "Apply" to create services

#### Option B: Manual Deployment
- [ ] Create PostgreSQL database first
- [ ] Create web service
- [ ] Configure build and start commands

### 2. Configure Environment Variables

#### Required Variables:
- [ ] `RENDER=true`
- [ ] `PHP_VERSION=8.1`
- [ ] Database variables (auto-configured with Blueprint)

#### API Keys (add your actual values):
- [ ] `TMDB_API_KEY=a931731976a07c91bf2dc1208ed4ac3d`
- [ ] `SPOTIFY_CLIENT_ID=your_actual_client_id`
- [ ] `SPOTIFY_CLIENT_SECRET=your_actual_client_secret`
- [ ] `GOOGLE_CLIENT_ID=your_actual_google_client_id`
- [ ] `GOOGLE_CLIENT_SECRET=your_actual_google_client_secret`

#### Optional Variables:
- [ ] `AI_ASSISTANT_URL=https://moodifyme-bot.onrender.com`
- [ ] `SMTP_HOST=smtp.gmail.com`
- [ ] `SMTP_USERNAME=your_email@gmail.com`
- [ ] `SMTP_PASSWORD=your_app_password`

### 3. Database Setup

#### Import Schema:
- [ ] Connect to Render PostgreSQL database
- [ ] Import `database/postgresql_schema.sql`
- [ ] Verify tables created successfully
- [ ] Import any seed data if needed

#### Connection Test:
- [ ] Test database connection from web service
- [ ] Check health endpoint: `/health`

### 4. OAuth Configuration

#### Google OAuth:
- [ ] Update Google Cloud Console
- [ ] Add authorized origin: `https://your-service-name.onrender.com`
- [ ] Add redirect URI: `https://your-service-name.onrender.com/api/google_oauth_callback.php`

#### Spotify API:
- [ ] Update Spotify app settings
- [ ] Add redirect URI if needed

### 5. Testing & Verification

#### Basic Functionality:
- [ ] Visit your app URL: `https://your-service-name.onrender.com`
- [ ] Health check works: `https://your-service-name.onrender.com/health`
- [ ] Database connection successful
- [ ] User registration works
- [ ] User login works
- [ ] Google OAuth works

#### Feature Testing:
- [ ] Emotion tracking works
- [ ] Recommendations load
- [ ] Community posts work
- [ ] Direct messaging works
- [ ] File uploads work (if applicable)

## Post-Deployment

### 6. Performance & Monitoring

#### Setup Monitoring:
- [ ] Check Render service metrics
- [ ] Monitor error logs
- [ ] Set up uptime monitoring (optional)

#### Performance Optimization:
- [ ] Verify OPcache is working
- [ ] Check page load times
- [ ] Monitor database performance
- [ ] Optimize slow queries if needed

### 7. Security & Maintenance

#### Security:
- [ ] Verify HTTPS is enforced
- [ ] Check security headers
- [ ] Test session security
- [ ] Verify environment variables are secure

#### Backup & Recovery:
- [ ] Set up database backups (Render handles this)
- [ ] Document recovery procedures
- [ ] Test backup restoration (if possible)

### 8. Domain & DNS (Optional)

#### Custom Domain:
- [ ] Purchase domain (if desired)
- [ ] Configure DNS settings
- [ ] Add custom domain in Render
- [ ] Update OAuth redirect URLs
- [ ] Update APP_URL environment variable

## Troubleshooting

### Common Issues:

#### Build Failures:
- [ ] Check build logs in Render dashboard
- [ ] Verify `build.sh` has execute permissions
- [ ] Check PHP syntax errors
- [ ] Verify composer.json is valid

#### Database Issues:
- [ ] Check DATABASE_URL format
- [ ] Verify PostgreSQL schema imported correctly
- [ ] Check database connection in logs
- [ ] Test with simple query

#### Environment Variables:
- [ ] Verify all required variables are set
- [ ] Check for typos in variable names
- [ ] Ensure sensitive data is not in code

#### OAuth Issues:
- [ ] Verify redirect URLs are correct
- [ ] Check client IDs and secrets
- [ ] Test OAuth flow manually

## Success Metrics

### Deployment Success:
- [ ] ✅ App loads without errors
- [ ] ✅ Database queries work
- [ ] ✅ User authentication works
- [ ] ✅ All major features functional
- [ ] ✅ Performance is acceptable
- [ ] ✅ No security warnings

### Ready for Production:
- [ ] ✅ All tests pass
- [ ] ✅ Error monitoring in place
- [ ] ✅ Backup strategy confirmed
- [ ] ✅ Team has access to Render dashboard
- [ ] ✅ Documentation updated

## Support Resources

- **Render Documentation:** [render.com/docs](https://render.com/docs)
- **Render Community:** [community.render.com](https://community.render.com)
- **PHP on Render:** [render.com/docs/php](https://render.com/docs/php)
- **PostgreSQL on Render:** [render.com/docs/postgresql](https://render.com/docs/postgresql)

## Emergency Contacts

- **Render Support:** Available through dashboard
- **Database Issues:** Check Render PostgreSQL docs
- **Application Issues:** Check application logs in Render dashboard

---

**🎉 Congratulations!** 

Your MoodifyMe application should now be successfully deployed on Render!

**Your app URL:** `https://your-service-name.onrender.com`

Remember to:
- Monitor the application regularly
- Keep dependencies updated
- Back up important data
- Monitor costs and usage
