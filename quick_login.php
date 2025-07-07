<?php
/**
 * Quick login script for testing community posts
 */

require_once 'config.php';
require_once 'includes/db_connect.php';

session_start();

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    echo "✅ Already logged in as user ID: " . $_SESSION['user_id'] . "\n";
    echo "🌐 Visit: http://localhost/MoodifyMe/pages/community_posts.php\n";
    exit;
}

// Get a test user
$result = $conn->query("SELECT id, username, email FROM users WHERE username = 'joannes_azhinwi' OR username = 'Jo' LIMIT 1");

if ($result && $row = $result->fetch_assoc()) {
    // Set session variables to log in the user
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['username'] = $row['username'];
    $_SESSION['email'] = $row['email'];
    
    echo "🎉 Successfully logged in!\n";
    echo "👤 User: " . $row['username'] . " (ID: " . $row['id'] . ")\n";
    echo "📧 Email: " . $row['email'] . "\n\n";
    
    echo "🌐 Now visit: http://localhost/MoodifyMe/pages/community_posts.php\n";
    echo "📱 Or use the Community dropdown from: http://localhost/MoodifyMe/pages/dashboard.php\n\n";
    
    echo "✨ You should now see all 14 community posts!\n";
} else {
    echo "❌ No test user found\n";
}
?>
