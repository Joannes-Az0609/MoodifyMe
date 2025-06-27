<?php
/**
 * MoodifyMe - Send Message API
 * Send a message to a chat room or direct conversation
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
$roomId = (int)($_POST['room_id'] ?? 0);
$conversationId = (int)($_POST['conversation_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

// Validate input
if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Message content is required']);
    exit;
}

if (strlen($content) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Message is too long (max 1000 characters)']);
    exit;
}

// Determine message type and validate access
$messageType = '';
$targetId = 0;

if ($roomId > 0) {
    // Community chat message
    $messageType = 'community';
    $targetId = $roomId;
    
    // Check if room exists and is accessible
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
    
    // Check if user is a participant
    $stmt = $conn->prepare("
        SELECT 1 FROM chat_room_participants 
        WHERE chat_room_id = ? AND user_id = ? AND is_active = TRUE
    ");
    $stmt->bind_param("ii", $roomId, $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Auto-join the user to the room
        $stmt = $conn->prepare("
            INSERT INTO chat_room_participants (chat_room_id, user_id) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE is_active = TRUE
        ");
        $stmt->bind_param("ii", $roomId, $currentUserId);
        $stmt->execute();
    }
    
} elseif ($conversationId > 0) {
    // Direct message
    $messageType = 'direct';
    $targetId = $conversationId;
    
    // Check if conversation exists and user has access
    $stmt = $conn->prepare("
        SELECT c.* FROM conversations c
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
    
} else {
    echo json_encode(['success' => false, 'message' => 'Either room_id or conversation_id is required']);
    exit;
}

// Basic content filtering
$content = filterMessage($content);

// Insert the message
try {
    $stmt = $conn->prepare("
        INSERT INTO messages (sender_id, message_type, chat_room_id, conversation_id, content, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $chatRoomIdParam = $messageType === 'community' ? $roomId : null;
    $conversationIdParam = $messageType === 'direct' ? $conversationId : null;
    
    $stmt->bind_param("isiss",
        $currentUserId,
        $messageType,
        $chatRoomIdParam,
        $conversationIdParam,
        $content
    );
    
    if ($stmt->execute()) {
        $messageId = $conn->insert_id;
        
        // Update conversation last message time if it's a direct message
        if ($messageType === 'direct') {
            $stmt = $conn->prepare("
                UPDATE conversations 
                SET last_message_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $conversationId);
            $stmt->execute();
        }
        
        // Update user online status
        updateUserOnlineStatus($currentUserId);
        
        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully',
            'message_id' => $messageId
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send message']);
    }
    
} catch (Exception $e) {
    error_log("Error sending message: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}

/**
 * Basic message content filtering
 */
function filterMessage($content) {
    // Remove excessive whitespace
    $content = preg_replace('/\s+/', ' ', $content);
    
    // Basic profanity filter (you can expand this)
    $profanityWords = ['spam', 'scam']; // Add more as needed
    foreach ($profanityWords as $word) {
        $content = str_ireplace($word, str_repeat('*', strlen($word)), $content);
    }
    
    // Remove potentially harmful HTML/JS
    $content = strip_tags($content);
    $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    
    return trim($content);
}
?>
