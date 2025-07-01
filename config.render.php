<?php
/**
 * MoodifyMe - Render Production Configuration
 * Configuration specifically for Render hosting platform
 */

// Detect if we're on Render
$isRender = isset($_ENV['RENDER']) || strpos($_SERVER['HTTP_HOST'], '.onrender.com') !== false;
$isProduction = $isRender || isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production';

// Database Configuration for Render (PostgreSQL)
if ($isRender) {
    define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
    define('DB_PORT', $_ENV['DB_PORT'] ?? '5432');
    define('DB_USER', $_ENV['DB_USER'] ?? 'moodifyme_user');
    define('DB_PASS', $_ENV['DB_PASS'] ?? '');
    define('DB_NAME', $_ENV['DB_NAME'] ?? 'moodifyme');
    define('DB_TYPE', 'postgresql'); // Use PostgreSQL on Render
} else {
    // Local development with MySQL
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'moodifyme');
    define('DB_TYPE', 'mysql');
}

// Application Configuration
define('APP_NAME', 'MoodifyMe');
define('APP_VERSION', '1.0.0');

// Dynamic APP_URL based on environment
if (isset($_ENV['RENDER_EXTERNAL_URL'])) {
    define('APP_URL', $_ENV['RENDER_EXTERNAL_URL']);
} elseif ($isRender) {
    define('APP_URL', 'https://' . $_SERVER['HTTP_HOST']);
} else {
    define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/MoodifyMe');
}

// API Keys from environment variables
define('NLP_API_KEY', $_ENV['NLP_API_KEY'] ?? 'your_nlp_api_key');
define('TMDB_API_KEY', $_ENV['TMDB_API_KEY'] ?? 'a931731976a07c91bf2dc1208ed4ac3d');
define('SPOTIFY_CLIENT_ID', $_ENV['SPOTIFY_CLIENT_ID'] ?? 'a0f9cf5c2f3e4bdb80bdc3213bab0035');
define('SPOTIFY_CLIENT_SECRET', $_ENV['SPOTIFY_CLIENT_SECRET'] ?? '8ca23d17f6dc4324bc0823ab7ce297dd');

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID'] ?? '1005843795519-95v3g07sj7rder70eb1ikavouk057rli.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? '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');
define('GOOGLE_REDIRECT_URI', APP_URL . '/api/google_oauth_callback.php');

// Emotion API Configuration
define('EMOTION_API_URL', $_ENV['EMOTION_API_URL'] ?? 'http://localhost:5000');

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
if ($isProduction) {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', '/tmp/php_errors.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', $isProduction ? 1 : 0);

// Timezone
date_default_timezone_set('UTC');

// AI Assistant configuration
define('AI_ASSISTANT_URL', $_ENV['AI_ASSISTANT_URL'] ?? 'https://moodifyme-bot.onrender.com');
define('AI_ASSISTANT_ENABLED', !empty(AI_ASSISTANT_URL));

// Logging configuration
define('LOG_LEVEL', $isProduction ? 'ERROR' : 'DEBUG');
define('LOG_FILE', $_ENV['LOG_FILE'] ?? '/tmp/moodifyme.log');

// Feature flags
define('FEATURE_FACIAL_DETECTION', true);
define('FEATURE_VOICE_INPUT', true);
define('FEATURE_AI_CHAT', AI_ASSISTANT_ENABLED);
define('FEATURE_SOCIAL_LOGIN', true);

// Security headers for production
if ($isProduction) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// CORS settings for API endpoints
function setCorsHeaders() {
    $allowedOrigins = [
        APP_URL,
        'https://moodifyme-bot.onrender.com',
        'https://localhost:3000',
        'https://127.0.0.1:3000'
    ];
    
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
    }
    
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
}

// Health check endpoint
if ($_SERVER['REQUEST_URI'] === '/health' || $_SERVER['REQUEST_URI'] === '/health.php') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'healthy',
        'timestamp' => date('c'),
        'version' => APP_VERSION,
        'environment' => $isProduction ? 'production' : 'development',
        'database' => DB_TYPE,
        'ai_assistant' => AI_ASSISTANT_ENABLED ? 'enabled' : 'disabled'
    ]);
    exit;
}
?>
