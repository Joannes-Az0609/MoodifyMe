<?php
/**
 * MoodifyMe - African Meals Setup Script
 * This script sets up the African meals recommendation system
 */

// Include configuration
require_once '../config.php';
require_once '../includes/db_connect.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>MoodifyMe African Meals Setup</h2>\n";

// Check if we're connected to the database
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "<p>✓ Database connection successful</p>\n";

// Check if recommendations table exists
$result = $conn->query("SHOW TABLES LIKE 'recommendations'");
if ($result->num_rows == 0) {
    die("<p>❌ Error: recommendations table does not exist. Please run the main schema setup first.</p>");
}

echo "<p>✓ Recommendations table exists</p>\n";

// Check current African meals count
$result = $conn->query("SELECT COUNT(*) as count FROM recommendations WHERE type = 'african_meals'");
$row = $result->fetch_assoc();
$currentCount = $row['count'];

echo "<p>Current African meals in database: {$currentCount}</p>\n";

// Read and execute the African meals seed file
$seedFile = __DIR__ . '/african_meals_seed.sql';

if (!file_exists($seedFile)) {
    die("<p>❌ Error: african_meals_seed.sql file not found</p>");
}

echo "<p>✓ Found african_meals_seed.sql file</p>\n";

// Read the SQL file
$sql = file_get_contents($seedFile);

// Remove the USE database statement since we're already connected
$sql = preg_replace('/USE\s+\w+;\s*/', '', $sql);

// Split into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

$successCount = 0;
$errorCount = 0;
$errors = [];

echo "<p>Executing SQL statements...</p>\n";

foreach ($statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue; // Skip empty statements and comments
    }
    
    if ($conn->query($statement)) {
        $successCount++;
    } else {
        $errorCount++;
        $errors[] = "Error: " . $conn->error . " in statement: " . substr($statement, 0, 100) . "...";
    }
}

echo "<p>✓ Executed {$successCount} statements successfully</p>\n";

if ($errorCount > 0) {
    echo "<p>❌ {$errorCount} statements failed:</p>\n";
    foreach ($errors as $error) {
        echo "<p style='color: red;'>{$error}</p>\n";
    }
}

// Check final count
$result = $conn->query("SELECT COUNT(*) as count FROM recommendations WHERE type = 'african_meals'");
$row = $result->fetch_assoc();
$finalCount = $row['count'];

echo "<p>Final African meals count: {$finalCount}</p>\n";
echo "<p>Added: " . ($finalCount - $currentCount) . " new African meal recommendations</p>\n";

// Show sample of what was added
echo "<h3>Sample African Meals Added:</h3>\n";
$result = $conn->query("SELECT title, source_emotion, target_emotion FROM recommendations WHERE type = 'african_meals' ORDER BY created_at DESC LIMIT 5");

if ($result->num_rows > 0) {
    echo "<ul>\n";
    while ($row = $result->fetch_assoc()) {
        echo "<li>{$row['title']} ({$row['source_emotion']} → {$row['target_emotion']})</li>\n";
    }
    echo "</ul>\n";
}

// Test the API endpoints
echo "<h3>Testing API Endpoints:</h3>\n";

// Test mood-based recommendations
$testUrl = APP_URL . "/api/african_meals.php?action=get_by_mood&source=sad&target=happy&limit=3";
echo "<p>Testing: <a href='{$testUrl}' target='_blank'>{$testUrl}</a></p>\n";

// Test random meals
$testUrl2 = APP_URL . "/api/african_meals.php?action=get_random&limit=5";
echo "<p>Testing: <a href='{$testUrl2}' target='_blank'>{$testUrl2}</a></p>\n";

// Test search
$testUrl3 = APP_URL . "/api/african_meals.php?action=search&query=jollof&limit=3";
echo "<p>Testing: <a href='{$testUrl3}' target='_blank'>{$testUrl3}</a></p>\n";

echo "<h3>Next Steps:</h3>\n";
echo "<ol>\n";
echo "<li>Test the African meals recommendations by visiting the recommendations page</li>\n";
echo "<li>Try different mood combinations to see various meal suggestions</li>\n";
echo "<li>Add images to the assets/images/meals/ directory for better visual appeal</li>\n";
echo "<li>Consider adding more regional variations and dietary preferences</li>\n";
echo "</ol>\n";

echo "<p><strong>Setup Complete!</strong> Your African meals recommendation system is now ready to use.</p>\n";

// Close connection
$conn->close();
?>
