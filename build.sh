#!/bin/bash

# MoodifyMe Build Script for Render
echo "🚀 Starting MoodifyMe build process..."

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader
    echo "✅ PHP dependencies installed"
else
    echo "⚠️ No composer.json found, skipping PHP dependencies"
fi

# Set proper permissions
echo "🔧 Setting file permissions..."
chmod -R 755 .
chmod -R 644 *.php
chmod -R 644 pages/*.php
chmod -R 644 api/*.php
chmod -R 644 includes/*.php

# Create necessary directories
echo "📁 Creating necessary directories..."
mkdir -p logs
mkdir -p uploads
mkdir -p cache

# Set permissions for writable directories
chmod -R 777 logs
chmod -R 777 uploads
chmod -R 777 cache

echo "✅ Build process completed successfully!"
