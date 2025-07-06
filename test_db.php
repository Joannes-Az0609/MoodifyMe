<?php
// Test database connection and meal data
require_once 'config.php';
require_once 'includes/db_connect.php';

echo "<h2>Database Connection Test</h2>";

// Test connection
if ($conn->connect_error) {
    echo "<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>";
    exit;
} else {
    echo "<p style='color: green;'>Database connected successfully!</p>";
}

// Test if recommendations table exists
$result = $conn->query("SHOW TABLES LIKE 'recommendations'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>Recommendations table exists!</p>";
} else {
    echo "<p style='color: red;'>Recommendations table does not exist!</p>";
}

// Check meal data
$result = $conn->query("SELECT COUNT(*) as count FROM recommendations WHERE type = 'meals'");
$row = $result->fetch_assoc();
echo "<p>Total meals in database: <strong>" . $row['count'] . "</strong></p>";

// Check specific mood transition
$stmt = $conn->prepare("SELECT * FROM recommendations WHERE type = 'meals' AND source_emotion = 'sad' AND target_emotion = 'happy'");
$stmt->execute();
$result = $stmt->get_result();

echo "<h3>Meals for Sad → Happy transition:</h3>";
if ($result->num_rows > 0) {
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li><strong>" . htmlspecialchars($row['title']) . "</strong> - " . htmlspecialchars($row['description']) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>No meals found for sad → happy transition!</p>";
}

// Check all meal data
echo "<h3>All Meals in Database:</h3>";
$result = $conn->query("SELECT title, source_emotion, target_emotion FROM recommendations WHERE type = 'meals' ORDER BY source_emotion, target_emotion");
if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Title</th><th>Source Emotion</th><th>Target Emotion</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . htmlspecialchars($row['source_emotion']) . "</td>";
        echo "<td>" . htmlspecialchars($row['target_emotion']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No meals found in database!</p>";
}

$conn->close();
?>
