<?php
/**
 * MoodifyMe - Render.com Configuration File
 * Optimized configuration for Render deployment
 */

// Prevent multiple inclusions
if (defined('MOODIFYME_CONFIG_LOADED')) {
    return;
}
define('MOODIFYME_CONFIG_LOADED', true);

// Render environment detection
$isRender = isset($_ENV['RENDER']) || strpos($_SERVER['HTTP_HOST'], '.onrender.com') !== false;

// Database Configuration - Render PostgreSQL
if (!defined('DB_HOST')) {
    if (isset($_ENV['DATABASE_URL'])) {
        // Parse Render's DATABASE_URL format: postgres://user:pass@host:port/dbname
        $databaseUrl = parse_url($_ENV['DATABASE_URL']);
        define('DB_HOST', $databaseUrl['host']);
        define('DB_USER', $databaseUrl['user']);
        define('DB_PASS', $databaseUrl['pass']);
        define('DB_NAME', ltrim($databaseUrl['path'], '/'));
        define('DB_PORT', $databaseUrl['port'] ?? 5432);
        define('DB_TYPE', 'pgsql'); // PostgreSQL for Render
    } else {
        // Fallback to individual environment variables
        define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
        define('DB_USER', $_ENV['DB_USER'] ?? 'root');
        define('DB_PASS', $_ENV['DB_PASS'] ?? '');
        define('DB_NAME', $_ENV['DB_NAME'] ?? 'moodifyme');
        define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);
        define('DB_TYPE', 'mysql'); // Default to MySQL
    }
}

// Application Configuration
if (!defined('APP_NAME')) {
    define('APP_NAME', 'MoodifyMe');
    define('APP_VERSION', '1.0.0');

    // Dynamic APP_URL for Render
    if (isset($_ENV['RENDER_SERVICE_NAME'])) {
        define('APP_URL', 'https://' . $_ENV['RENDER_SERVICE_NAME'] . '.onrender.com');
    } else {
        define('APP_URL', $_ENV['APP_URL'] ?? 'https://moodifyme-web.onrender.com');
    }
}

// API Keys from environment variables
if (!defined('NLP_API_KEY')) {
    define('NLP_API_KEY', $_ENV['NLP_API_KEY'] ?? 'your_nlp_api_key');
    define('TMDB_API_KEY', $_ENV['TMDB_API_KEY'] ?? 'a931731976a07c91bf2dc1208ed4ac3d');
    define('SPOTIFY_CLIENT_ID', $_ENV['SPOTIFY_CLIENT_ID'] ?? 'a0f9cf5c2f3e4bdb80bdc3213bab0035');
    define('SPOTIFY_CLIENT_SECRET', $_ENV['SPOTIFY_CLIENT_SECRET'] ?? '8ca23d17f6dc4324bc0823ab7ce297dd');
}

// Google OAuth Configuration
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID'] ?? '1005843795519-95v3g07sj7rder70eb1ikavouk057rli.apps.googleusercontent.com');
    define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? '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');
    define('GOOGLE_REDIRECT_URI', APP_URL . '/api/google_oauth_callback.php');
}

// Recommendation Types
if (!defined('REC_TYPES')) {
    define('REC_TYPES', [
        'music' => 'Music',
        'movies' => 'Movies',
        'african_meals' => 'African Meals'
    ]);
}

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

// Production settings for Render
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php_errors.log');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// HTTPS settings
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_path', '/');
session_name('MOODIFYME_SESSION');

// Timezone
date_default_timezone_set('UTC');

// AI Assistant configuration
if (!defined('AI_ASSISTANT_URL')) {
    define('AI_ASSISTANT_URL', $_ENV['AI_ASSISTANT_URL'] ?? 'https://moodifyme-bot.onrender.com');
    define('AI_ASSISTANT_ENABLED', !empty($_ENV['AI_ASSISTANT_URL']));
}

// Feature flags
if (!defined('FEATURE_FACIAL_DETECTION')) {
    define('FEATURE_FACIAL_DETECTION', true);
    define('FEATURE_VOICE_INPUT', true);
    define('FEATURE_AI_CHAT', AI_ASSISTANT_ENABLED);
    define('FEATURE_SOCIAL_LOGIN', true);
}

// Performance settings
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', false);
    define('CACHE_ENABLED', true);
    define('CACHE_TTL', 3600);
}

// Enable OPcache if available
if (function_exists('opcache_get_status')) {
    ini_set('opcache.enable', 1);
    ini_set('opcache.memory_consumption', 128);
    ini_set('opcache.max_accelerated_files', 4000);
    ini_set('opcache.revalidate_freq', 60);
}

// Enable compression
if (!ob_get_level()) {
    ob_start('ob_gzhandler');
}

// CORS settings for API endpoints
function setCorsHeaders() {
    $allowedOrigins = [
        APP_URL,
        'https://moodifyme-bot.onrender.com',
        'https://localhost:3000'
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
        'environment' => 'render',
        'database' => defined('DB_TYPE') ? DB_TYPE : 'unknown'
    ]);
    exit;
}

// Email configuration (if using email features)
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com');
    define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
    define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? '');
    define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? '');
    define('SMTP_ENCRYPTION', $_ENV['SMTP_ENCRYPTION'] ?? 'tls');
}

// Rate limiting
if (!defined('RATE_LIMIT_ENABLED')) {
    define('RATE_LIMIT_ENABLED', true);
    define('RATE_LIMIT_REQUESTS', 100);
    define('RATE_LIMIT_WINDOW', 3600);
}

// File upload limits
if (!defined('MAX_UPLOAD_SIZE')) {
    define('MAX_UPLOAD_SIZE', '10M');
    define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
}

// Logging configuration
if (!defined('LOG_LEVEL')) {
    define('LOG_LEVEL', 'ERROR');
    define('LOG_FILE', $_ENV['LOG_FILE'] ?? '/tmp/moodifyme.log');
}
?>
