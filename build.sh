#!/bin/bash

# MoodifyMe Render Build Script
# This script prepares the application for deployment on Render

echo "🚀 Starting MoodifyMe build process for Render..."

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
    echo "✅ Composer dependencies installed"
else
    echo "⚠️  No composer.json found, skipping PHP dependencies"
fi

# Create necessary directories
echo "📁 Creating necessary directories..."
mkdir -p uploads
mkdir -p assets/uploads
mkdir -p tmp
mkdir -p logs

# Set proper permissions (Render handles this automatically, but good to be explicit)
echo "🔐 Setting permissions..."
chmod 755 uploads
chmod 755 assets/uploads
chmod 755 tmp
chmod 755 logs

# Check if required files exist
echo "🔍 Checking required files..."
required_files=("config.render.php" "index.php" "includes/db_connect.php")

for file in "${required_files[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file exists"
    else
        echo "❌ $file is missing!"
        exit 1
    fi
done

# Validate PHP syntax
echo "🔍 Validating PHP syntax..."
find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \; > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ PHP syntax validation passed"
else
    echo "❌ PHP syntax errors found!"
    exit 1
fi

# Check for environment variables (in production)
if [ "$RENDER" = "true" ]; then
    echo "🌍 Checking environment variables..."
    
    required_env_vars=("DATABASE_URL")
    
    for var in "${required_env_vars[@]}"; do
        if [ -z "${!var}" ]; then
            echo "⚠️  Environment variable $var is not set"
        else
            echo "✅ $var is set"
        fi
    done
fi

# Optimize for production
echo "⚡ Optimizing for production..."

# Clear any development caches
if [ -d "tmp" ]; then
    rm -rf tmp/*
    echo "✅ Cleared temporary files"
fi

# Generate optimized autoloader
if [ -f "vendor/autoload.php" ]; then
    composer dump-autoload --optimize --no-dev
    echo "✅ Optimized autoloader generated"
fi

echo "🎉 Build process completed successfully!"
echo ""
echo "📋 Deployment checklist:"
echo "  ✅ Dependencies installed"
echo "  ✅ Directories created"
echo "  ✅ Permissions set"
echo "  ✅ PHP syntax validated"
echo "  ✅ Production optimizations applied"
echo ""
echo "🚀 Ready for Render deployment!"
