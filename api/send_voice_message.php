<?php
/**
 * Send Voice Message API
 * Handles voice message upload and sending
 */

session_start();

require_once '../config.php';
require_once '../includes/db_connect.php';
require_once '../includes/notification_functions.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
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
$duration = (float)($_POST['duration'] ?? 0);

// Validate inputs
if ($conversationId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid conversation ID']);
    exit;
}

if ($duration <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid voice message duration']);
    exit;
}

// Check if voice file was uploaded
if (!isset($_FILES['voice_file']) || $_FILES['voice_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No voice file uploaded or upload error']);
    exit;
}

$voiceFile = $_FILES['voice_file'];

// Validate file type
$allowedTypes = ['audio/webm', 'audio/wav', 'audio/mp3', 'audio/ogg'];
$fileType = $voiceFile['type'];

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only audio files are allowed.']);
    exit;
}

// Validate file size (max 10MB)
$maxSize = 10 * 1024 * 1024; // 10MB
if ($voiceFile['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 10MB.']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Verify user is participant in conversation
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM conversation_participants 
        WHERE conversation_id = ? AND user_id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ii", $conversationId, $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row['count'] == 0) {
        throw new Exception("User is not a participant in this conversation");
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = '../uploads/voice/' . date('Y/m');
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $fileExtension = pathinfo($voiceFile['name'], PATHINFO_EXTENSION);
    if (empty($fileExtension)) {
        // Determine extension from MIME type
        $mimeToExt = [
            'audio/webm' => 'webm',
            'audio/wav' => 'wav',
            'audio/mp3' => 'mp3',
            'audio/ogg' => 'ogg'
        ];
        $fileExtension = $mimeToExt[$fileType] ?? 'webm';
    }
    
    $fileName = 'voice_' . time() . '_' . $currentUserId . '_' . uniqid() . '.' . $fileExtension;
    $filePath = $uploadDir . '/' . $fileName;
    $relativeFilePath = 'uploads/voice/' . date('Y/m') . '/' . $fileName;
    
    // Move uploaded file
    if (!move_uploaded_file($voiceFile['tmp_name'], $filePath)) {
        throw new Exception("Failed to save voice file");
    }
    
    // Insert the message
    $stmt = $conn->prepare("
        INSERT INTO messages (sender_id, conversation_id, content, message_content_type, voice_file_path, voice_duration, voice_file_size, created_at) 
        VALUES (?, ?, ?, 'voice', ?, ?, ?, NOW())
    ");
    
    if (!$stmt) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }
    
    $content = "Voice message (" . round($duration, 1) . "s)";
    $stmt->bind_param("iissii", $currentUserId, $conversationId, $content, $relativeFilePath, $duration, $voiceFile['size']);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert message: " . $stmt->error);
    }
    
    $messageId = $conn->insert_id;
    $stmt->close();
    
    // Insert voice message metadata
    $stmt = $conn->prepare("
        INSERT INTO voice_messages (message_id, original_filename, file_path, file_size, duration, format, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    if ($stmt) {
        $format = $fileExtension;
        $stmt->bind_param("issids", $messageId, $voiceFile['name'], $relativeFilePath, $voiceFile['size'], $duration, $format);
        $stmt->execute();
        $stmt->close();
    }
    
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
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Voice message sent successfully',
            'data' => $message
        ]);
    } else {
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Voice message sent successfully',
            'message_id' => $messageId
        ]);
    }
    
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    
    // Delete uploaded file if it exists
    if (isset($filePath) && file_exists($filePath)) {
        unlink($filePath);
    }
    
    error_log("Send voice message error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send voice message. Please try again.',
        'error' => $e->getMessage()
    ]);
}
?>
