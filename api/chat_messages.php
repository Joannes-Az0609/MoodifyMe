<?php
/**
 * MoodifyMe - Chat Messages API
 * Get messages for a chat room
 */

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';
require_once '../includes/social_functions.php';

// Start session
session_start();

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$currentUserId = $_SESSION['user_id'];

// Check if request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get parameters
$roomId = (int)($_GET['room_id'] ?? 0);
$limit = min((int)($_GET['limit'] ?? 50), 100); // Max 100 messages
$afterId = (int)($_GET['after_id'] ?? 0);

// Validate room ID
if ($roomId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
    exit;
}

// Check if user has access to the room
$stmt = $conn->prepare("
    SELECT cr.* FROM chat_rooms cr
    WHERE cr.id = ? AND cr.is_active = TRUE
");
$stmt->bind_param("i", $roomId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Room not found or access denied']);
    exit;
}

$room = $result->fetch_assoc();

// Check if user is blocked from viewing messages
$stmt = $conn->prepare("
    SELECT 1 FROM user_blocks 
    WHERE blocked_id = ? AND blocker_id IN (
        SELECT DISTINCT sender_id FROM messages WHERE chat_room_id = ?
    )
");
$stmt->bind_param("ii", $currentUserId, $roomId);
$stmt->execute();
$result = $stmt->get_result();

// Build query based on whether we're getting initial messages or new messages
if ($afterId > 0) {
    // Get new messages after a specific ID
    $stmt = $conn->prepare("
        SELECT m.*, u.username, u.display_name, u.profile_image
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.chat_room_id = ? 
          AND m.id > ? 
          AND m.message_status != 'deleted'
          AND NOT EXISTS (
              SELECT 1 FROM user_blocks ub 
              WHERE ub.blocker_id = ? AND ub.blocked_id = m.sender_id
          )
        ORDER BY m.created_at ASC
        LIMIT ?
    ");
    $stmt->bind_param("iiii", $roomId, $afterId, $currentUserId, $limit);
} else {
    // Get recent messages
    $stmt = $conn->prepare("
        SELECT m.*, u.username, u.display_name, u.profile_image
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.chat_room_id = ? 
          AND m.message_status != 'deleted'
          AND NOT EXISTS (
              SELECT 1 FROM user_blocks ub 
              WHERE ub.blocker_id = ? AND ub.blocked_id = m.sender_id
          )
        ORDER BY m.created_at DESC
        LIMIT ?
    ");
    $stmt->bind_param("iii", $roomId, $currentUserId, $limit);
}

$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'id' => (int)$row['id'],
        'sender_id' => (int)$row['sender_id'],
        'username' => $row['username'],
        'display_name' => $row['display_name'],
        'profile_image' => $row['profile_image'],
        'content' => $row['content'],
        'created_at' => $row['created_at'],
        'edited_at' => $row['edited_at']
    ];
}

// If we got recent messages, reverse the order to show oldest first
if ($afterId === 0) {
    $messages = array_reverse($messages);
}

// Update user's last read timestamp for this room
$stmt = $conn->prepare("
    UPDATE chat_room_participants 
    SET last_read_at = NOW() 
    WHERE chat_room_id = ? AND user_id = ?
");
$stmt->bind_param("ii", $roomId, $currentUserId);
$stmt->execute();

// Update user online status
updateUserOnlineStatus($currentUserId);

echo json_encode([
    'success' => true,
    'messages' => $messages,
    'room_info' => [
        'id' => (int)$room['id'],
        'name' => $room['name'],
        'description' => $room['description']
    ]
]);
?>
