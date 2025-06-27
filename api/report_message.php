<?php
/**
 * MoodifyMe - Report Message API
 * Report inappropriate messages for moderation
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

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get parameters
$messageId = (int)($_POST['message_id'] ?? 0);
$reason = $_POST['reason'] ?? '';
$description = trim($_POST['description'] ?? '');

// Validate input
if ($messageId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid message ID']);
    exit;
}

$validReasons = ['spam', 'harassment', 'inappropriate', 'other'];
if (!in_array($reason, $validReasons)) {
    echo json_encode(['success' => false, 'message' => 'Invalid reason']);
    exit;
}

if (strlen($description) > 500) {
    echo json_encode(['success' => false, 'message' => 'Description is too long (max 500 characters)']);
    exit;
}

// Check if message exists and user has access to it
$stmt = $conn->prepare("
    SELECT m.*, 
           CASE 
               WHEN m.chat_room_id IS NOT NULL THEN 'community'
               WHEN m.conversation_id IS NOT NULL THEN 'direct'
               ELSE 'unknown'
           END as message_context
    FROM messages m
    WHERE m.id = ? AND m.message_status != 'deleted'
");
$stmt->bind_param("i", $messageId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Message not found']);
    exit;
}

$message = $result->fetch_assoc();

// Check if user can see this message
$canReport = false;

if ($message['message_context'] === 'community') {
    // Check if user is in the chat room
    $stmt = $conn->prepare("
        SELECT 1 FROM chat_room_participants 
        WHERE chat_room_id = ? AND user_id = ? AND is_active = TRUE
    ");
    $stmt->bind_param("ii", $message['chat_room_id'], $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    $canReport = $result->num_rows > 0;
    
} elseif ($message['message_context'] === 'direct') {
    // Check if user is in the conversation
    $stmt = $conn->prepare("
        SELECT 1 FROM conversation_participants 
        WHERE conversation_id = ? AND user_id = ? AND is_active = TRUE
    ");
    $stmt->bind_param("ii", $message['conversation_id'], $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    $canReport = $result->num_rows > 0;
}

if (!$canReport) {
    echo json_encode(['success' => false, 'message' => 'You cannot report this message']);
    exit;
}

// Check if user has already reported this message
$stmt = $conn->prepare("
    SELECT id FROM message_reports 
    WHERE message_id = ? AND reporter_id = ?
");
$stmt->bind_param("ii", $messageId, $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reported this message']);
    exit;
}

// Prevent users from reporting their own messages
if ($message['sender_id'] == $currentUserId) {
    echo json_encode(['success' => false, 'message' => 'You cannot report your own message']);
    exit;
}

// Insert the report
try {
    $stmt = $conn->prepare("
        INSERT INTO message_reports (message_id, reporter_id, reason, description, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("iiss", $messageId, $currentUserId, $reason, $description);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Message reported successfully. Our moderation team will review it.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit report']);
    }
    
} catch (Exception $e) {
    error_log("Error reporting message: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to submit report']);
}
?>
