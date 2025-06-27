<?php
/**
 * MoodifyMe - Direct Messages API
 * Get messages for a direct conversation
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
$conversationId = (int)($_GET['conversation_id'] ?? 0);
$limit = min((int)($_GET['limit'] ?? 50), 100); // Max 100 messages
$afterId = (int)($_GET['after_id'] ?? 0);

// Validate conversation ID
if ($conversationId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid conversation ID']);
    exit;
}

// Check if user has access to the conversation
$stmt = $conn->prepare("
    SELECT c.*, cp.last_read_at
    FROM conversations c
    JOIN conversation_participants cp ON c.id = cp.conversation_id
    WHERE c.id = ? AND cp.user_id = ? AND cp.is_active = TRUE
");
$stmt->bind_param("ii", $conversationId, $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Conversation not found or access denied']);
    exit;
}

$conversation = $result->fetch_assoc();

// Get the other participant's info
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.display_name, u.profile_image
    FROM conversation_participants cp
    JOIN users u ON cp.user_id = u.id
    WHERE cp.conversation_id = ? AND cp.user_id != ? AND cp.is_active = TRUE
");
$stmt->bind_param("ii", $conversationId, $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

$otherParticipant = null;
if ($result->num_rows > 0) {
    $otherParticipant = $result->fetch_assoc();
}

// Check if the other user has blocked current user
if ($otherParticipant && isUserBlocked($otherParticipant['id'], $currentUserId)) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Build query based on whether we're getting initial messages or new messages
if ($afterId > 0) {
    // Get new messages after a specific ID
    $stmt = $conn->prepare("
        SELECT m.*, u.username, u.display_name, u.profile_image
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.conversation_id = ? 
          AND m.id > ? 
          AND m.message_status != 'deleted'
        ORDER BY m.created_at ASC
        LIMIT ?
    ");
    $stmt->bind_param("iii", $conversationId, $afterId, $limit);
} else {
    // Get recent messages
    $stmt = $conn->prepare("
        SELECT m.*, u.username, u.display_name, u.profile_image
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.conversation_id = ? 
          AND m.message_status != 'deleted'
        ORDER BY m.created_at DESC
        LIMIT ?
    ");
    $stmt->bind_param("ii", $conversationId, $limit);
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

// Update user's last read timestamp for this conversation
$stmt = $conn->prepare("
    UPDATE conversation_participants 
    SET last_read_at = NOW() 
    WHERE conversation_id = ? AND user_id = ?
");
$stmt->bind_param("ii", $conversationId, $currentUserId);
$stmt->execute();

// Update user online status
updateUserOnlineStatus($currentUserId);

echo json_encode([
    'success' => true,
    'messages' => $messages,
    'conversation_info' => [
        'id' => (int)$conversation['id'],
        'type' => $conversation['conversation_type'],
        'created_at' => $conversation['created_at']
    ],
    'other_participant' => $otherParticipant
]);
?>
