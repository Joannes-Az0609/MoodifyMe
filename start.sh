#!/bin/bash

# MoodifyMe Start Script for Render
echo "🚀 Starting MoodifyMe PHP server..."

# Set the port from environment variable or default to 10000
PORT=${PORT:-10000}

echo "🌐 Server will run on port $PORT"

# Start PHP built-in server
php -S 0.0.0.0:$PORT index.php
