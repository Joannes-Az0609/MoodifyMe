<?php
/**
 * MoodifyMe - Meals Database Schema Update
 * Adds meal-specific columns to the recommendations table
 */

// Include configuration
require_once '../config.php';
require_once '../includes/db_connect.php';

// Set content type
header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n<title>Meals Schema Update</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }\n";
echo ".container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }\n";
echo ".success { color: #28a745; }\n";
echo ".error { color: #dc3545; }\n";
echo ".info { color: #17a2b8; }\n";
echo "h1 { color: #333; }\n";
echo "h2 { color: #666; margin-top: 30px; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";
echo "<div class='container'>\n";

echo "<h1>🍽️ Meals Database Schema Update</h1>\n";
echo "<p>Adding meal-specific columns to the recommendations table...</p>\n";

$success = true;
$errors = [];

// Define the columns to add for meals
$columnsToAdd = [
    [
        'name' => 'ingredients',
        'sql' => "ALTER TABLE recommendations ADD COLUMN ingredients TEXT NULL AFTER link",
        'description' => 'List of ingredients for the meal'
    ],
    [
        'name' => 'cooking_time',
        'sql' => "ALTER TABLE recommendations ADD COLUMN cooking_time VARCHAR(50) NULL AFTER ingredients",
        'description' => 'Estimated cooking time (e.g., "30 minutes")'
    ],
    [
        'name' => 'difficulty',
        'sql' => "ALTER TABLE recommendations ADD COLUMN difficulty ENUM('Easy', 'Medium', 'Hard') NULL AFTER cooking_time",
        'description' => 'Cooking difficulty level'
    ],
    [
        'name' => 'servings',
        'sql' => "ALTER TABLE recommendations ADD COLUMN servings VARCHAR(20) NULL AFTER difficulty",
        'description' => 'Number of servings (e.g., "4 people")'
    ],
    [
        'name' => 'cuisine_type',
        'sql' => "ALTER TABLE recommendations ADD COLUMN cuisine_type VARCHAR(100) NULL AFTER servings",
        'description' => 'Type of cuisine (e.g., "Italian", "Asian", "Comfort Food")'
    ],
    [
        'name' => 'dietary_tags',
        'sql' => "ALTER TABLE recommendations ADD COLUMN dietary_tags VARCHAR(255) NULL AFTER cuisine_type",
        'description' => 'Dietary information (e.g., "Vegetarian, Gluten-Free")'
    ],
    [
        'name' => 'nutrition_info',
        'sql' => "ALTER TABLE recommendations ADD COLUMN nutrition_info TEXT NULL AFTER dietary_tags",
        'description' => 'Basic nutrition information'
    ]
];

echo "<h2>Adding Meal Columns</h2>\n";

foreach ($columnsToAdd as $column) {
    echo "<h3>Adding column: {$column['name']}</h3>\n";
    echo "<p class='info'>{$column['description']}</p>\n";
    
    try {
        // Check if column already exists
        $stmt = $conn->prepare("SHOW COLUMNS FROM recommendations LIKE ?");
        $stmt->bind_param("s", $column['name']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "<p class='info'>ℹ️ Column '{$column['name']}' already exists, skipping...</p>\n";
        } else {
            // Add the column
            $conn->query($column['sql']);
            echo "<p class='success'>✅ Successfully added column: {$column['name']}</p>\n";
        }
    } catch (Exception $e) {
        $errors[] = "Failed to add column {$column['name']}: " . $e->getMessage();
        echo "<p class='error'>❌ Failed to add column {$column['name']}: " . $e->getMessage() . "</p>\n";
        $success = false;
    }
}

// Update the schema comment in the database
echo "<h2>Updating Table Comment</h2>\n";
try {
    $conn->query("ALTER TABLE recommendations COMMENT = 'Recommendations table with support for music, movies, and meals'");
    echo "<p class='success'>✅ Updated table comment</p>\n";
} catch (Exception $e) {
    echo "<p class='error'>❌ Failed to update table comment: " . $e->getMessage() . "</p>\n";
}

// Show final table structure
echo "<h2>📊 Final Table Structure</h2>\n";
try {
    $result = $conn->query("DESCRIBE recommendations");
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 10px;'>\n";
    echo "<tr style='background: #f8f9fa;'><th style='padding: 8px;'>Field</th><th style='padding: 8px;'>Type</th><th style='padding: 8px;'>Null</th><th style='padding: 8px;'>Key</th><th style='padding: 8px;'>Default</th></tr>\n";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>\n";
        echo "<td style='padding: 8px;'>{$row['Field']}</td>\n";
        echo "<td style='padding: 8px;'>{$row['Type']}</td>\n";
        echo "<td style='padding: 8px;'>{$row['Null']}</td>\n";
        echo "<td style='padding: 8px;'>{$row['Key']}</td>\n";
        echo "<td style='padding: 8px;'>{$row['Default']}</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
} catch (Exception $e) {
    echo "<p class='error'>❌ Failed to show table structure: " . $e->getMessage() . "</p>\n";
}

// Summary
echo "<h2>📋 Update Summary</h2>\n";
if ($success && empty($errors)) {
    echo "<p class='success'>✅ <strong>Schema update completed successfully!</strong></p>\n";
    echo "<p>The recommendations table now supports meal-specific data including ingredients, cooking time, difficulty, and more.</p>\n";
} else {
    echo "<p class='error'>⚠️ <strong>Schema update completed with some issues:</strong></p>\n";
    if (!empty($errors)) {
        echo "<ul>\n";
        foreach ($errors as $error) {
            echo "<li class='error'>$error</li>\n";
        }
        echo "</ul>\n";
    }
}

echo "<h3>Next Steps:</h3>\n";
echo "<ol>\n";
echo "<li>Create the meals API endpoint</li>\n";
echo "<li>Add meal UI components</li>\n";
echo "<li>Populate sample meal data</li>\n";
echo "<li>Test the meal recommendations</li>\n";
echo "</ol>\n";

echo "</div>\n</body>\n</html>\n";

$conn->close();
?>
