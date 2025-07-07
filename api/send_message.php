<?php
/**
 * MoodifyMe - Send Message API
 * Handle sending direct messages
 */

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';
require_once '../includes/social_functions.php';
require_once '../includes/notification_functions.php';

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

// Get POST parameters
$conversationId = (int)($_POST['conversation_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

// Debug logging
error_log("Send message attempt - User: $currentUserId, Conversation: $conversationId, Content length: " . strlen($content));

// Validate inputs
if ($conversationId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid conversation ID']);
    exit;
}

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Message content cannot be empty']);
    exit;
}

if (strlen($content) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Message too long (max 1000 characters)']);
    exit;
}

try {
    // Check if conversation exists and user is a participant
    $stmt = $conn->prepare("
        SELECT cp.user_id, c.conversation_type 
        FROM conversation_participants cp
        JOIN conversations c ON cp.conversation_id = c.id
        WHERE cp.conversation_id = ? AND cp.user_id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ii", $conversationId, $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'You are not a participant in this conversation']);
        exit;
    }
    
    $stmt->close();
    
    // Filter message content (basic profanity filter)
    $filteredContent = filterMessage($content);
    
    // Insert the message
    $stmt = $conn->prepare("
        INSERT INTO messages (sender_id, conversation_id, content, message_type, created_at) 
        VALUES (?, ?, ?, 'direct', NOW())
    ");
    
    if (!$stmt) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("iis", $currentUserId, $conversationId, $filteredContent);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert message: " . $stmt->error);
    }
    
    $messageId = $conn->insert_id;
    $stmt->close();
    
    // Update conversation's last message timestamp
    $stmt = $conn->prepare("UPDATE conversations SET last_message_at = NOW() WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $conversationId);
        $stmt->execute();
        $stmt->close();
    }

    // Create notifications for other participants
    $stmt = $conn->prepare("
        SELECT cp.user_id
        FROM conversation_participants cp
        WHERE cp.conversation_id = ? AND cp.user_id != ?
    ");
    if ($stmt) {
        $stmt->bind_param("ii", $conversationId, $currentUserId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($participant = $result->fetch_assoc()) {
            createMessageNotification($participant['user_id'], $currentUserId, $content);
        }
        $stmt->close();
    }
    
    // Get the inserted message with user details
    $stmt = $conn->prepare("
        SELECT m.*, u.username, u.display_name, u.profile_picture as profile_image
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.id = ?
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $messageId);
        $stmt->execute();
        $result = $stmt->get_result();
        $message = $result->fetch_assoc();
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully',
            'message_id' => $messageId
        ]);
    }
    
} catch (Exception $e) {
    error_log("Send message error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send message. Please try again.',
        'error' => $e->getMessage(),
        'debug' => [
            'conversation_id' => $conversationId,
            'user_id' => $currentUserId,
            'content_length' => strlen($content)
        ]
    ]);
}

/**
 * Basic message filtering function
 */
function filterMessage($content) {
    // Basic profanity filter - you can expand this
    $profanity = ['spam', 'scam', 'fake'];
    $filtered = $content;
    
    foreach ($profanity as $word) {
        $filtered = str_ireplace($word, str_repeat('*', strlen($word)), $filtered);
    }
    
    return $filtered;
}
?>
