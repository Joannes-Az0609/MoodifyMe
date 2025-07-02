<?php
/**
 * MoodifyMe - InfinityFree Configuration
 * Configuration for InfinityFree hosting
 * 
 * IMPORTANT: Update the database credentials below with your actual InfinityFree details
 */

// Database Configuration (InfinityFree)
define('DB_HOST', 'sql100.infinityfree.com');
define('DB_USER', 'if0_39373357');
define('DB_PASS', 'Feres12*345');
define('DB_NAME', 'if0_39373357_moodifyme');

// Application Configuration
define('APP_NAME', 'MoodifyMe');
define('APP_URL', 'http://MoodifyMe.kesug.com');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'production');

// AI Assistant Configuration
define('AI_ASSISTANT_URL', 'https://moodifyme-bot.onrender.com');

// API Keys (same as your local configuration)
define('NLP_API_KEY', 'your_nlp_api_key');
define('TMDB_API_KEY', 'a931731976a07c91bf2dc1208ed4ac3d');
define('SPOTIFY_CLIENT_ID', 'a0f9cf5c2f3e4bdb80bdc3213bab0035');
define('SPOTIFY_CLIENT_SECRET', '8ca23d17f6dc4324bc0823ab7ce297dd');

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', '1005843795519-95v3g07sj7rder70eb1ikavouk057rli.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');

// Security Configuration
define('SESSION_SECURE', false); // HTTP only on free hosting
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Lax');

// CORS Configuration for AI Assistant
$allowed_origins = [
    'https://moodifyme-bot.onrender.com',
    'http://MoodifyMe.kesug.com',
    'https://MoodifyMe.kesug.com' // If you get SSL later
];

// Set CORS headers if needed
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Error Reporting (disabled in production)
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Session Configuration (HTTP only for free hosting)
ini_set('session.cookie_secure', '0'); // No HTTPS on free tier
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

// Timezone
date_default_timezone_set('UTC');

// InfinityFree specific optimizations
ini_set('max_execution_time', 25); // InfinityFree has 30 second limit
ini_set('memory_limit', '64M'); // Conservative memory usage

// Simple caching headers for static assets
if (strpos($_SERVER['REQUEST_URI'], '/assets/') !== false) {
    header('Cache-Control: public, max-age=86400'); // 1 day cache
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
}
?>
