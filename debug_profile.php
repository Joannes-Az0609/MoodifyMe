<?php
/**
 * Debug Profile System
 */

require_once 'config.php';
require_once 'includes/db_connect.php';
require_once 'includes/social_functions.php';

session_start();

echo "<h2>Profile System Debug</h2>\n";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ You need to be logged in</p>\n";
    echo "<a href='" . APP_URL . "/pages/login.php'>Login</a>\n";
    exit;
}

$currentUserId = $_SESSION['user_id'];
echo "<p>✅ Current User ID: $currentUserId</p>\n";

// Test profile ID from URL
$testProfileId = isset($_GET['id']) ? (int)$_GET['id'] : $currentUserId;
echo "<p>🎯 Testing Profile ID: $testProfileId</p>\n";

// Check if users table has required columns
echo "<h3>Checking Users Table Structure...</h3>\n";
$columnsQuery = $conn->query("SHOW COLUMNS FROM users");
$userColumns = [];
while ($column = $columnsQuery->fetch_assoc()) {
    $userColumns[] = $column['Field'];
}

$requiredColumns = ['id', 'username', 'email'];
$optionalColumns = ['display_name', 'profile_image', 'bio', 'is_public', 'follower_count', 'following_count', 'connection_count'];

foreach ($requiredColumns as $col) {
    if (in_array($col, $userColumns)) {
        echo "<p style='color: green;'>✅ Required column '$col' exists</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Required column '$col' missing</p>\n";
    }
}

foreach ($optionalColumns as $col) {
    if (in_array($col, $userColumns)) {
        echo "<p style='color: green;'>✅ Optional column '$col' exists</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ Optional column '$col' missing (will use defaults)</p>\n";
    }
}

// Test getUserProfileWithStats function
echo "<h3>Testing getUserProfileWithStats Function...</h3>\n";
try {
    $user = getUserProfileWithStats($testProfileId);
    if ($user) {
        echo "<p style='color: green;'>✅ getUserProfileWithStats() successful</p>\n";
        echo "<p><strong>Username:</strong> " . ($user['username'] ?? 'N/A') . "</p>\n";
        echo "<p><strong>Display Name:</strong> " . ($user['display_name'] ?? 'N/A') . "</p>\n";
        echo "<p><strong>Is Public:</strong> " . ($user['is_public'] ?? 'N/A') . "</p>\n";
        echo "<p><strong>Follower Count:</strong> " . ($user['follower_count'] ?? 'N/A') . "</p>\n";
    } else {
        echo "<p style='color: red;'>❌ getUserProfileWithStats() returned null - User not found</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ getUserProfileWithStats() error: " . $e->getMessage() . "</p>\n";
}

// Check if social tables exist
echo "<h3>Checking Social Tables...</h3>\n";
$socialTables = ['user_follows', 'user_connections', 'user_blocks', 'user_online_status'];

foreach ($socialTables as $table) {
    $tableCheck = $conn->query("SHOW TABLES LIKE '$table'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        echo "<p style='color: green;'>✅ Table '$table' exists</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ Table '$table' missing (will create if needed)</p>\n";
    }
}

// Test direct profile access
echo "<h3>Test Profile Links:</h3>\n";
echo "<a href='" . APP_URL . "/pages/social_profile.php?id=$currentUserId' target='_blank'>View Your Own Profile</a><br>\n";

// Get a list of other users to test
$stmt = $conn->prepare("SELECT id, username FROM users WHERE id != ? LIMIT 5");
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

echo "<h4>Test Other Users:</h4>\n";
while ($otherUser = $result->fetch_assoc()) {
    echo "<a href='" . APP_URL . "/pages/social_profile.php?id=" . $otherUser['id'] . "' target='_blank'>View " . $otherUser['username'] . "'s Profile</a><br>\n";
}

// Create missing columns if needed
echo "<h3>Fix Missing Columns:</h3>\n";
if (!in_array('is_public', $userColumns)) {
    echo "<p>Adding 'is_public' column...</p>\n";
    $conn->query("ALTER TABLE users ADD COLUMN is_public BOOLEAN DEFAULT TRUE");
}

if (!in_array('follower_count', $userColumns)) {
    echo "<p>Adding 'follower_count' column...</p>\n";
    $conn->query("ALTER TABLE users ADD COLUMN follower_count INT DEFAULT 0");
}

if (!in_array('following_count', $userColumns)) {
    echo "<p>Adding 'following_count' column...</p>\n";
    $conn->query("ALTER TABLE users ADD COLUMN following_count INT DEFAULT 0");
}

if (!in_array('connection_count', $userColumns)) {
    echo "<p>Adding 'connection_count' column...</p>\n";
    $conn->query("ALTER TABLE users ADD COLUMN connection_count INT DEFAULT 0");
}

if (!in_array('display_name', $userColumns)) {
    echo "<p>Adding 'display_name' column...</p>\n";
    $conn->query("ALTER TABLE users ADD COLUMN display_name VARCHAR(100)");
    $conn->query("UPDATE users SET display_name = username WHERE display_name IS NULL");
}

if (!in_array('bio', $userColumns)) {
    echo "<p>Adding 'bio' column...</p>\n";
    $conn->query("ALTER TABLE users ADD COLUMN bio TEXT");
}

if (!in_array('profile_image', $userColumns)) {
    echo "<p>Adding 'profile_image' column...</p>\n";
    $conn->query("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255)");
}

echo "<p><a href='?id=$currentUserId'>Refresh Test</a></p>\n";
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; line-height: 1.6; }
h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
h3 { color: #555; margin-top: 20px; }
a { color: #007bff; text-decoration: none; margin: 5px 0; display: block; }
a:hover { text-decoration: underline; }
</style>
