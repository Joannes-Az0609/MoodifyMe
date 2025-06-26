<?php
/**
 * MoodifyMe - Production Configuration File
 * Environment-aware configuration for deployment
 */

// Detect environment
$isProduction = isset($_ENV['RAILWAY_ENVIRONMENT']) ||
                isset($_ENV['VERCEL_ENV']) ||
                isset($_ENV['HEROKU_APP_NAME']) ||
                isset($_ENV['RENDER']) ||
                strpos($_SERVER['HTTP_HOST'], '.onrender.com') !== false ||
                $_SERVER['HTTP_HOST'] !== 'localhost';

// Database Configuration - Use environment variables
define('DB_HOST', $_ENV['DB_HOST'] ?? $_ENV['DATABASE_URL'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? $_ENV['DATABASE_USERNAME'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? $_ENV['DATABASE_PASSWORD'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? $_ENV['DATABASE_NAME'] ?? 'moodifyme');

// Application Configuration
define('APP_NAME', 'MoodifyMe');
define('APP_VERSION', '1.0.0');

// Dynamic APP_URL based on environment
if (isset($_ENV['RAILWAY_PUBLIC_DOMAIN'])) {
    define('APP_URL', 'https://' . $_ENV['RAILWAY_PUBLIC_DOMAIN']);
} elseif (isset($_ENV['VERCEL_URL'])) {
    define('APP_URL', 'https://' . $_ENV['VERCEL_URL']);
} elseif (isset($_ENV['HEROKU_APP_NAME'])) {
    define('APP_URL', 'https://' . $_ENV['HEROKU_APP_NAME'] . '.herokuapp.com');
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

// Environment-specific settings
if ($isProduction) {
    // Production settings
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', '/tmp/php_errors.log');
    
    // Security headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // HTTPS settings
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    define('APP_DEBUG', false);
} else {
    // Development settings
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('session.cookie_secure', 0);
    define('APP_DEBUG', true);
}

// Session Configuration
ini_set('session.use_only_cookies', 1);
ini_set('session.entropy_length', 32);
ini_set('session.entropy_file', '/dev/urandom');

// Timezone
date_default_timezone_set('UTC');

// Database connection with error handling
function getDatabaseConnection() {
    try {
        // Handle different database URL formats
        if (isset($_ENV['DATABASE_URL'])) {
            $url = parse_url($_ENV['DATABASE_URL']);
            $host = $url['host'];
            $username = $url['user'];
            $password = $url['pass'];
            $database = ltrim($url['path'], '/');
            $port = $url['port'] ?? 3306;
        } else {
            $host = DB_HOST;
            $username = DB_USER;
            $password = DB_PASS;
            $database = DB_NAME;
            $port = 3306;
        }
        
        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        
        return $pdo;
    } catch (PDOException $e) {
        if (APP_DEBUG) {
            die('Database connection failed: ' . $e->getMessage());
        } else {
            error_log('Database connection failed: ' . $e->getMessage());
            die('Database connection failed. Please try again later.');
        }
    }
}

// Cache configuration
define('CACHE_ENABLED', $isProduction);
define('CACHE_TTL', 3600); // 1 hour

// Rate limiting
define('RATE_LIMIT_ENABLED', $isProduction);
define('RATE_LIMIT_REQUESTS', 100); // requests per hour
define('RATE_LIMIT_WINDOW', 3600); // 1 hour

// File upload limits
define('MAX_UPLOAD_SIZE', '10M');
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// Email configuration (if using email features)
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'localhost');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? '');
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? '');
define('SMTP_ENCRYPTION', $_ENV['SMTP_ENCRYPTION'] ?? 'tls');

// AI Assistant configuration
define('AI_ASSISTANT_URL', $_ENV['AI_ASSISTANT_URL'] ?? 'http://localhost:3000');
define('AI_ASSISTANT_ENABLED', !empty($_ENV['AI_ASSISTANT_URL']));

// Logging configuration
define('LOG_LEVEL', $isProduction ? 'ERROR' : 'DEBUG');
define('LOG_FILE', $_ENV['LOG_FILE'] ?? '/tmp/moodifyme.log');

// Feature flags
define('FEATURE_FACIAL_DETECTION', true);
define('FEATURE_VOICE_INPUT', true);
define('FEATURE_AI_CHAT', AI_ASSISTANT_ENABLED);
define('FEATURE_SOCIAL_LOGIN', true);

// Performance settings
if ($isProduction) {
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
}

// CORS settings for API endpoints
function setCorsHeaders() {
    $allowedOrigins = [
        APP_URL,
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
        'environment' => $isProduction ? 'production' : 'development'
    ]);
    exit;
}
?>
