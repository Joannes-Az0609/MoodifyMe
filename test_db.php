<?php
// Simple database connection test
echo "PostgreSQL Connection Test\n";
echo "==========================\n\n";

// Check if PostgreSQL extension is available
if (!extension_loaded('pdo_pgsql')) {
    die("❌ PDO PostgreSQL extension is not loaded!\n");
}

echo "✅ PDO PostgreSQL extension is available\n\n";

// Get environment variables
$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '5432';
$dbname = $_ENV['DB_NAME'] ?? 'moodifyme';
$user = $_ENV['DB_USER'] ?? 'moodifyme_user';
$password = $_ENV['DB_PASS'] ?? '';
$database_url = $_ENV['DATABASE_URL'] ?? '';

echo "Environment Variables:\n";
echo "DB_HOST: $host\n";
echo "DB_PORT: $port\n";
echo "DB_NAME: $dbname\n";
echo "DB_USER: $user\n";
echo "DB_PASS: " . (strlen($password) > 0 ? '[SET]' : '[EMPTY]') . "\n";
echo "DATABASE_URL: " . (strlen($database_url) > 0 ? '[SET]' : '[EMPTY]') . "\n\n";

// Test 1: Try with DATABASE_URL if available
if (!empty($database_url)) {
    echo "Test 1: Using DATABASE_URL\n";
    try {
        // Convert postgres:// to pgsql://
        $dsn = str_replace('postgres://', 'pgsql://', $database_url);
        $conn = new PDO($dsn);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $result = $conn->query("SELECT 1 as test");
        $row = $result->fetch();
        
        echo "✅ SUCCESS with DATABASE_URL!\n";
        echo "Test query result: " . $row['test'] . "\n";
        exit;
        
    } catch (PDOException $e) {
        echo "❌ Failed with DATABASE_URL: " . $e->getMessage() . "\n\n";
    }
}

// Test 2: Try with individual parameters, no SSL
echo "Test 2: Individual parameters, no SSL\n";
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=disable";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $result = $conn->query("SELECT 1 as test");
    $row = $result->fetch();
    
    echo "✅ SUCCESS with individual parameters (no SSL)!\n";
    echo "Test query result: " . $row['test'] . "\n";
    exit;
    
} catch (PDOException $e) {
    echo "❌ Failed with individual parameters (no SSL): " . $e->getMessage() . "\n\n";
}

// Test 3: Try with individual parameters, allow SSL
echo "Test 3: Individual parameters, allow SSL\n";
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=allow";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $result = $conn->query("SELECT 1 as test");
    $row = $result->fetch();
    
    echo "✅ SUCCESS with individual parameters (allow SSL)!\n";
    echo "Test query result: " . $row['test'] . "\n";
    exit;
    
} catch (PDOException $e) {
    echo "❌ Failed with individual parameters (allow SSL): " . $e->getMessage() . "\n\n";
}

echo "❌ All connection attempts failed!\n";
?>
