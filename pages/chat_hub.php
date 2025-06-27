<?php
/**
 * MoodifyMe - Chat Hub
 * Main hub for all messaging features
 */

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';
require_once '../includes/social_functions.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect(APP_URL . '/pages/login.php');
}

$currentUserId = $_SESSION['user_id'];
$currentUser = getUserProfileWithStats($currentUserId);

// Get user's recent conversations
$recentConversations = [];
$stmt = $conn->prepare("
    SELECT 
        c.id,
        c.conversation_type,
        c.last_message_at,
        u.id as other_user_id,
        u.username as other_username,
        u.display_name as other_display_name,
        u.profile_image as other_profile_image,
        (SELECT content FROM messages m WHERE m.conversation_id = c.id ORDER BY m.created_at DESC LIMIT 1) as last_message,
        (SELECT COUNT(*) FROM messages m 
         JOIN conversation_participants cp ON m.conversation_id = cp.conversation_id
         WHERE m.conversation_id = c.id 
           AND m.created_at > cp.last_read_at 
           AND cp.user_id = ? 
           AND m.sender_id != ?) as unread_count
    FROM conversations c
    JOIN conversation_participants cp1 ON c.id = cp1.conversation_id
    JOIN conversation_participants cp2 ON c.id = cp2.conversation_id
    JOIN users u ON cp2.user_id = u.id
    WHERE cp1.user_id = ? 
      AND cp1.is_active = TRUE
      AND cp2.user_id != ?
      AND cp2.is_active = TRUE
    ORDER BY c.last_message_at DESC
    LIMIT 5
");
$stmt->bind_param("iiii", $currentUserId, $currentUserId, $currentUserId, $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $recentConversations[] = $row;
}

// Get active chat rooms
$activeChatRooms = [];
$stmt = $conn->prepare("
    SELECT cr.*, 
           (SELECT COUNT(*) FROM chat_room_participants crp 
            WHERE crp.chat_room_id = cr.id AND crp.is_active = TRUE) as participant_count,
           (SELECT COUNT(*) FROM messages m 
            WHERE m.chat_room_id = cr.id 
              AND m.created_at > (
                  SELECT COALESCE(last_read_at, '1970-01-01') 
                  FROM chat_room_participants 
                  WHERE chat_room_id = cr.id AND user_id = ?
              )) as unread_count
    FROM chat_rooms cr
    WHERE cr.is_active = TRUE AND cr.room_type = 'public'
    ORDER BY cr.name
");
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $activeChatRooms[] = $row;
}

// Get pending connection requests
$pendingRequests = [];
$stmt = $conn->prepare("
    SELECT COUNT(*) as count
    FROM user_connections 
    WHERE receiver_id = ? AND status = 'pending'
");
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();
$pendingRequestsCount = $result->fetch_assoc()['count'];

// Update user online status
updateUserOnlineStatus($currentUserId);

// Include header
include '../includes/header.php';
?>

<div class="container mt-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="card-title mb-2">
                                <i class="fas fa-comments"></i> Welcome to Chat Hub
                            </h2>
                            <p class="card-text mb-0">
                                Connect with the MoodifyMe community through public chat rooms or private messages. 
                                Share your journey, find support, and build meaningful connections.
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="d-flex justify-content-center gap-4">
                                <div class="text-center">
                                    <h4 class="mb-1"><?php echo $currentUser['connection_count']; ?></h4>
                                    <small>Connections</small>
                                </div>
                                <div class="text-center">
                                    <h4 class="mb-1"><?php echo $currentUser['following_count']; ?></h4>
                                    <small>Following</small>
                                </div>
                                <div class="text-center">
                                    <h4 class="mb-1"><?php echo $currentUser['follower_count']; ?></h4>
                                    <small>Followers</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Community Chat</h5>
                    <p class="card-text">Join public rooms for group discussions and community support.</p>
                    <a href="<?php echo APP_URL; ?>/pages/community_chat.php" class="btn btn-primary">
                        Join Chat Rooms
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="fas fa-envelope fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Direct Messages</h5>
                    <p class="card-text">Private conversations with your connections.</p>
                    <a href="<?php echo APP_URL; ?>/pages/messages.php" class="btn btn-success">
                        View Messages
                        <?php if (array_sum(array_column($recentConversations, 'unread_count')) > 0): ?>
                            <span class="badge bg-light text-dark ms-1">
                                <?php echo array_sum(array_column($recentConversations, 'unread_count')); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="fas fa-user-friends fa-3x text-info mb-3"></i>
                    <h5 class="card-title">My Connections</h5>
                    <p class="card-text">Manage your followers, following, and connection requests.</p>
                    <a href="<?php echo APP_URL; ?>/pages/connections.php" class="btn btn-info">
                        Manage Connections
                        <?php if ($pendingRequestsCount > 0): ?>
                            <span class="badge bg-warning text-dark ms-1"><?php echo $pendingRequestsCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="fas fa-search fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Find People</h5>
                    <p class="card-text">Discover and connect with other community members.</p>
                    <a href="<?php echo APP_URL; ?>/pages/user_directory.php" class="btn btn-warning">
                        Find People
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <!-- Recent Conversations -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-envelope"></i> Recent Conversations
                        </h5>
                        <a href="<?php echo APP_URL; ?>/pages/messages.php" class="btn btn-outline-primary btn-sm">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($recentConversations)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-envelope fa-2x text-muted mb-3"></i>
                            <h6>No conversations yet</h6>
                            <p class="text-muted">Start connecting with other users to begin messaging!</p>
                            <a href="<?php echo APP_URL; ?>/pages/user_directory.php" class="btn btn-primary btn-sm">
                                Find People
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentConversations as $conv): ?>
                                <a href="<?php echo APP_URL; ?>/pages/messages.php?conversation=<?php echo $conv['id']; ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center">
                                        <!-- Profile Picture -->
                                        <div class="position-relative me-3">
                                            <?php if ($conv['other_profile_image']): ?>
                                                <img src="<?php echo APP_URL . '/' . $conv['other_profile_image']; ?>" 
                                                     alt="Profile" class="rounded-circle" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px; background-color: #e9ecef;">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($conv['unread_count'] > 0): ?>
                                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                    <?php echo $conv['unread_count'] > 9 ? '9+' : $conv['unread_count']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Conversation Info -->
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-1 text-truncate">
                                                    <?php echo htmlspecialchars($conv['other_display_name'] ?: $conv['other_username']); ?>
                                                </h6>
                                                <small class="text-muted">
                                                    <?php echo formatDate($conv['last_message_at']); ?>
                                                </small>
                                            </div>
                                            <p class="mb-0 text-muted small text-truncate">
                                                <?php echo $conv['last_message'] ? htmlspecialchars(substr($conv['last_message'], 0, 40)) . '...' : 'No messages yet'; ?>
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Active Chat Rooms -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users"></i> Community Rooms
                        </h5>
                        <a href="<?php echo APP_URL; ?>/pages/community_chat.php" class="btn btn-outline-primary btn-sm">
                            Join Chat
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php foreach ($activeChatRooms as $room): ?>
                            <a href="<?php echo APP_URL; ?>/pages/community_chat.php?room=<?php echo $room['id']; ?>" 
                               class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">
                                            <?php echo htmlspecialchars($room['name']); ?>
                                            <?php if ($room['unread_count'] > 0): ?>
                                                <span class="badge bg-primary ms-2"><?php echo $room['unread_count']; ?></span>
                                            <?php endif; ?>
                                        </h6>
                                        <p class="mb-1 small text-muted"><?php echo htmlspecialchars($room['description']); ?></p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-users"></i> <?php echo $room['participant_count']; ?>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Community Guidelines -->
    <div class="row">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-shield-alt"></i> Community Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Our Values</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-heart text-danger"></i> <strong>Be Supportive:</strong> We're here to help each other</li>
                                <li><i class="fas fa-handshake text-success"></i> <strong>Be Respectful:</strong> Treat everyone with kindness</li>
                                <li><i class="fas fa-shield-alt text-primary"></i> <strong>Be Safe:</strong> Protect your privacy and others'</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Quick Tips</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-lightbulb text-warning"></i> Use community chat for group support</li>
                                <li><i class="fas fa-user-friends text-info"></i> Connect with users for private messaging</li>
                                <li><i class="fas fa-flag text-danger"></i> Report inappropriate behavior</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.list-group-item-action:hover {
    background-color: rgba(0,0,0,0.05);
}

.badge {
    font-size: 0.75em;
}
</style>

<?php include '../includes/footer.php'; ?>
