<?php
/**
 * Save Push Notification Subscription
 * Stores user's push notification subscription for PWA
 */

session_start();
require_once '../config.php';
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['endpoint'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid subscription data']);
    exit;
}

try {
    // Create push_subscriptions table if it doesn't exist
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS push_subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            endpoint TEXT NOT NULL,
            p256dh_key TEXT,
            auth_key TEXT,
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_endpoint (user_id, endpoint(255))
        )
    ";
    
    $conn->query($createTableSQL);
    
    // Extract subscription data
    $endpoint = $input['endpoint'];
    $p256dhKey = isset($input['keys']['p256dh']) ? $input['keys']['p256dh'] : null;
    $authKey = isset($input['keys']['auth']) ? $input['keys']['auth'] : null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Check if subscription already exists
    $stmt = $conn->prepare("
        SELECT id FROM push_subscriptions 
        WHERE user_id = ? AND endpoint = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("is", $userId, $endpoint);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing subscription
        $stmt->close();
        $stmt = $conn->prepare("
            UPDATE push_subscriptions 
            SET p256dh_key = ?, auth_key = ?, user_agent = ?, updated_at = NOW()
            WHERE user_id = ? AND endpoint = ?
        ");
        
        if (!$stmt) {
            throw new Exception("Database prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("sssis", $p256dhKey, $authKey, $userAgent, $userId, $endpoint);
        
    } else {
        // Insert new subscription
        $stmt->close();
        $stmt = $conn->prepare("
            INSERT INTO push_subscriptions (user_id, endpoint, p256dh_key, auth_key, user_agent)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            throw new Exception("Database prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("issss", $userId, $endpoint, $p256dhKey, $authKey, $userAgent);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to save subscription: " . $stmt->error);
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Push subscription saved successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Save push subscription error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save push subscription',
        'error' => $e->getMessage()
    ]);
}
?>
