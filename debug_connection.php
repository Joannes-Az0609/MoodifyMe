<?php
/**
 * Debug PostgreSQL Connection for Render
 * This script helps diagnose connection issues
 */

// Load configuration
if (isset($_ENV['RENDER']) || strpos($_SERVER['HTTP_HOST'], '.onrender.com') !== false) {
    require_once 'config.render.php';
} else {
    require_once 'config.php';
}

echo "<h2>MoodifyMe PostgreSQL Connection Debug</h2>\n";
echo "<pre>\n";

// Show environment info
echo "=== Environment Info ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "PDO PostgreSQL Available: " . (extension_loaded('pdo_pgsql') ? 'YES' : 'NO') . "\n";
echo "OpenSSL Version: " . (defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'Not available') . "\n";
echo "\n";

// Show configuration
echo "=== Configuration ===\n";
echo "DB_TYPE: " . (defined('DB_TYPE') ? DB_TYPE : 'Not defined') . "\n";
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "\n";
echo "DB_PORT: " . (defined('DB_PORT') ? DB_PORT : 'Not defined') . "\n";
echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'Not defined') . "\n";
echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'Not defined') . "\n";
echo "DB_PASS: " . (defined('DB_PASS') ? (strlen(DB_PASS) > 0 ? '[SET - ' . strlen(DB_PASS) . ' chars]' : '[EMPTY]') : 'Not defined') . "\n";
echo "DATABASE_URL: " . (defined('DATABASE_URL') ? '[SET - ' . strlen(DATABASE_URL) . ' chars]' : 'Not defined') . "\n";
echo "\n";

// Test different connection methods
echo "=== Connection Tests ===\n";

$connectionMethods = [];

// If DATABASE_URL is available
if (defined('DATABASE_URL')) {
    $databaseUrl = DATABASE_URL;
    $dsn = str_replace('postgres://', 'pgsql://', $databaseUrl);
    
    $connectionMethods[] = [
        'name' => 'DATABASE_URL (no SSL)',
        'dsn' => $dsn . (strpos($dsn, '?') !== false ? '&' : '?') . 'sslmode=disable',
        'user' => null,
        'pass' => null
    ];
    
    $connectionMethods[] = [
        'name' => 'DATABASE_URL (allow SSL)',
        'dsn' => $dsn . (strpos($dsn, '?') !== false ? '&' : '?') . 'sslmode=allow',
        'user' => null,
        'pass' => null
    ];
}

// Individual parameters
if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
    $port = defined('DB_PORT') ? DB_PORT : '5432';
    
    $connectionMethods[] = [
        'name' => 'Individual params (no SSL)',
        'dsn' => "pgsql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";sslmode=disable",
        'user' => DB_USER,
        'pass' => DB_PASS
    ];
    
    $connectionMethods[] = [
        'name' => 'Individual params (allow SSL)',
        'dsn' => "pgsql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";sslmode=allow",
        'user' => DB_USER,
        'pass' => DB_PASS
    ];
}

foreach ($connectionMethods as $method) {
    echo "Testing: " . $method['name'] . "\n";
    echo "DSN: " . $method['dsn'] . "\n";
    
    try {
        $conn = new PDO($method['dsn'], $method['user'], $method['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 10
        ]);
        
        // Test the connection
        $result = $conn->query("SELECT version()");
        $version = $result->fetch()['version'];
        
        echo "✅ SUCCESS! Connected to: " . substr($version, 0, 50) . "...\n";
        echo "Connection working!\n";
        
        $conn = null; // Close connection
        break; // Stop testing once we find a working method
        
    } catch (PDOException $e) {
        echo "❌ FAILED: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== End Debug ===\n";
echo "</pre>";
?>
