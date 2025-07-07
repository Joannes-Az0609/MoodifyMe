<?php
require_once '../includes/db_connect.php';

// Set JSON header
header('Content-Type: application/json');

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to delete conversations.'
    ]);
    exit;
}

$currentUserId = $_SESSION['user_id'];

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Get conversation ID
$conversationId = (int)($_POST['conversation_id'] ?? 0);

if (!$conversationId) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid conversation ID.'
    ]);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Verify user is a participant in this conversation
    $stmt = $conn->prepare("
        SELECT cp.user_id 
        FROM conversation_participants cp 
        WHERE cp.conversation_id = ? AND cp.user_id = ?
    ");
    $stmt->bind_param("ii", $conversationId, $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('You are not authorized to delete this conversation.');
    }
    
    // Delete all messages in the conversation
    $stmt = $conn->prepare("DELETE FROM messages WHERE conversation_id = ?");
    $stmt->bind_param("i", $conversationId);
    $stmt->execute();
    
    // Delete conversation participants
    $stmt = $conn->prepare("DELETE FROM conversation_participants WHERE conversation_id = ?");
    $stmt->bind_param("i", $conversationId);
    $stmt->execute();
    
    // Delete the conversation itself
    $stmt = $conn->prepare("DELETE FROM conversations WHERE id = ?");
    $stmt->bind_param("i", $conversationId);
    $stmt->execute();
    
    // Check if conversation was actually deleted
    if ($stmt->affected_rows === 0) {
        throw new Exception('Conversation not found or already deleted.');
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Conversation deleted successfully.'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    error_log("Delete conversation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
