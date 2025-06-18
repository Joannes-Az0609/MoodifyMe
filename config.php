<?php
/**
 * MoodifyMe - Configuration File
 * Contains all the configuration settings for the application
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // If your root user has a password, enter it here
define('DB_NAME', 'moodifyme');

// Application Configuration
define('APP_NAME', 'MoodifyMe');
define('APP_URL', 'http://localhost/MoodifyMe');
define('APP_VERSION', '1.0.0');

// API Keys (replace with actual keys when available)
define('NLP_API_KEY', 'your_nlp_api_key');
// define('OPENAI_API_KEY', 'your_openai_api_key_here'); // OpenAI API Key (not needed - voice input removed)
define('TMDB_API_KEY', 'a931731976a07c91bf2dc1208ed4ac3d'); // The Movie Database API Key
define('SPOTIFY_CLIENT_ID', 'a0f9cf5c2f3e4bdb80bdc3213bab0035'); // Spotify API Client ID
define('SPOTIFY_CLIENT_SECRET', '8ca23d17f6dc4324bc0823ab7ce297dd'); // Spotify API Client Secret

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', '1005843795519-95v3g07sj7rder70eb1ikavouk057rli.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', APP_URL . '/api/google_oauth_callback.php');

// Recommendation Types
define('REC_TYPES', [
    'music' => 'Music',
    'movies' => 'Movies',
    'african_meals' => 'African Meals'
]);

// Emotion Categories
define('EMOTION_CATEGORIES', [
    'happy' => 'Happy',
    'sad' => 'Sad',
    'angry' => 'Angry',
    'anxious' => 'Anxious',
    'calm' => 'Calm',
    'excited' => 'Excited',
    'bored' => 'Bored',
    'tired' => 'Tired',
    'stressed' => 'Stressed',
    'neutral' => 'Neutral'
]);

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Timezone
date_default_timezone_set('UTC');
