<?php
/**
 * Sync Mood Data API
 * Handles syncing mood data when user comes back online
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

try {
    // Get any pending mood data from request
    $input = json_decode(file_get_contents('php://input'), true);
    
    $syncedCount = 0;
    
    if ($input && isset($input['mood_entries']) && is_array($input['mood_entries'])) {
        // Process offline mood entries
        foreach ($input['mood_entries'] as $entry) {
            if (!isset($entry['mood_level']) || !isset($entry['timestamp'])) {
                continue;
            }
            
            // Check if entry already exists
            $stmt = $conn->prepare("
                SELECT id FROM mood_entries 
                WHERE user_id = ? AND DATE(created_at) = DATE(?) 
                LIMIT 1
            ");
            
            if ($stmt) {
                $date = date('Y-m-d H:i:s', $entry['timestamp']);
                $stmt->bind_param("is", $userId, $date);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows == 0) {
                    // Insert new mood entry
                    $stmt->close();
                    $stmt = $conn->prepare("
                        INSERT INTO mood_entries (user_id, mood_level, notes, created_at)
                        VALUES (?, ?, ?, ?)
                    ");
                    
                    if ($stmt) {
                        $notes = $entry['notes'] ?? '';
                        $stmt->bind_param("iiss", $userId, $entry['mood_level'], $notes, $date);
                        
                        if ($stmt->execute()) {
                            $syncedCount++;
                        }
                        $stmt->close();
                    }
                } else {
                    $stmt->close();
                }
            }
        }
    }
    
    // Get latest mood data for client
    $stmt = $conn->prepare("
        SELECT mood_level, notes, created_at 
        FROM mood_entries 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 30
    ");
    
    $latestEntries = [];
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $latestEntries[] = $row;
        }
        $stmt->close();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Mood data synced successfully',
        'synced_count' => $syncedCount,
        'latest_entries' => $latestEntries
    ]);
    
} catch (Exception $e) {
    error_log("Sync mood data error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to sync mood data',
        'error' => $e->getMessage()
    ]);
}
?>
