<?php
// Test community posts page access
session_start();

// Set a test session to simulate logged-in user
$_SESSION['user_id'] = 1; // Use an existing user ID
$_SESSION['username'] = 'test_user';

echo "🔍 Testing community posts page access...\n\n";

// Capture output from community_posts.php
ob_start();
try {
    include 'pages/community_posts.php';
    $output = ob_get_contents();
} catch (Exception $e) {
    echo "❌ Error including community_posts.php: " . $e->getMessage() . "\n";
    $output = '';
} finally {
    ob_end_clean();
}

// Check if posts are in the output
if (strpos($output, 'No posts yet') !== false) {
    echo "⚠️ Page shows 'No posts yet' message\n";
} elseif (strpos($output, 'post-card') !== false) {
    echo "✅ Posts are being displayed (found post-card elements)\n";
    
    // Count how many post cards
    $postCount = substr_count($output, 'post-card');
    echo "📊 Number of post cards found: $postCount\n";
} else {
    echo "❓ Unclear if posts are showing\n";
}

// Check for PHP errors in output
if (strpos($output, 'Fatal error') !== false || strpos($output, 'Warning') !== false) {
    echo "❌ PHP errors detected in output\n";
    
    // Extract error messages
    preg_match_all('/(?:Fatal error|Warning|Notice):.*/', $output, $matches);
    foreach ($matches[0] as $error) {
        echo "  Error: $error\n";
    }
} else {
    echo "✅ No PHP errors detected\n";
}

// Save a snippet of the output for inspection
file_put_contents('community_output_test.html', $output);
echo "💾 Full output saved to community_output_test.html\n";

echo "\n🎯 Test complete!\n";
?>
