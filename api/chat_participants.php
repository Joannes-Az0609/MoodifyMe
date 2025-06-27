<?php
/**
 * MoodifyMe - Chat Participants API
 * Get participants for a chat room
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

// Get room ID
$roomId = (int)($_GET['room_id'] ?? 0);

// Validate room ID
if ($roomId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
    exit;
}

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

// Get participants with their online status
$stmt = $conn->prepare("
    SELECT 
        u.id,
        u.username,
        u.display_name,
        u.profile_image,
        crp.role,
        crp.joined_at,
        crp.last_read_at,
        COALESCE(uos.status, 'offline') as online_status,
        uos.last_seen,
        CASE 
            WHEN uos.status = 'online' AND uos.last_seen > DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1
            ELSE 0
        END as is_online
    FROM chat_room_participants crp
    JOIN users u ON crp.user_id = u.id
    LEFT JOIN user_online_status uos ON u.id = uos.user_id
    WHERE crp.chat_room_id = ? 
      AND crp.is_active = TRUE
      AND NOT EXISTS (
          SELECT 1 FROM user_blocks ub 
          WHERE ub.blocker_id = ? AND ub.blocked_id = u.id
      )
    ORDER BY 
        is_online DESC,
        crp.role DESC,
        u.display_name ASC,
        u.username ASC
");
$stmt->bind_param("ii", $roomId, $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

$participants = [];
$onlineCount = 0;

while ($row = $result->fetch_assoc()) {
    $participant = [
        'id' => (int)$row['id'],
        'username' => $row['username'],
        'display_name' => $row['display_name'],
        'profile_image' => $row['profile_image'],
        'role' => $row['role'],
        'joined_at' => $row['joined_at'],
        'last_read_at' => $row['last_read_at'],
        'online_status' => $row['online_status'],
        'last_seen' => $row['last_seen'],
        'is_online' => (bool)$row['is_online']
    ];
    
    if ($participant['is_online']) {
        $onlineCount++;
    }
    
    $participants[] = $participant;
}

// Update current user's online status
updateUserOnlineStatus($currentUserId);

echo json_encode([
    'success' => true,
    'participants' => $participants,
    'total_count' => count($participants),
    'online_count' => $onlineCount,
    'room_id' => $roomId
]);
?>
