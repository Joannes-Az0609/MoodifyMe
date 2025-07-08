<?php
/**
 * MoodifyMe - Direct Messages
 * Private messaging between connected users
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
$currentUser = getUserById($currentUserId);

// Get target user ID if specified
$targetUserId = isset($_GET['user']) ? (int)$_GET['user'] : null;
$selectedConversationId = isset($_GET['conversation']) ? (int)$_GET['conversation'] : null;

// Get user's conversations
$conversations = [];
$stmt = $conn->prepare("
    SELECT 
        c.id,
        c.conversation_type,
        c.title,
        c.last_message_at,
        c.created_at,
        u.id as other_user_id,
        u.username as other_username,
        u.display_name as other_display_name,
        u.profile_picture as other_profile_image,
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
");
$stmt->bind_param("iiii", $currentUserId, $currentUserId, $currentUserId, $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $conversations[] = $row;
}

// Handle starting a new conversation with a specific user
if ($targetUserId && !$selectedConversationId) {
    // Check if conversation already exists
    $existingConversation = null;
    foreach ($conversations as $conv) {
        if ($conv['other_user_id'] == $targetUserId) {
            $existingConversation = $conv;
            break;
        }
    }
    
    if ($existingConversation) {
        $selectedConversationId = $existingConversation['id'];
    } else {
        // Check if users are connected
        $connectionStatus = getConnectionStatus($currentUserId, $targetUserId);
        if (!$connectionStatus || $connectionStatus['status'] !== 'accepted') {
            redirect(APP_URL . '/pages/social_profile.php?id=' . $targetUserId);
        }
        
        // Create new conversation
        $conn->begin_transaction();
        try {
            // Create conversation
            $stmt = $conn->prepare("INSERT INTO conversations (created_by, conversation_type) VALUES (?, 'direct')");
            $stmt->bind_param("i", $currentUserId);
            $stmt->execute();
            $conversationId = $conn->insert_id;
            
            // Add participants
            $stmt = $conn->prepare("INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?), (?, ?)");
            $stmt->bind_param("iiii", $conversationId, $currentUserId, $conversationId, $targetUserId);
            $stmt->execute();
            
            $conn->commit();
            $selectedConversationId = $conversationId;
            
            // Refresh conversations list
            header("Location: " . APP_URL . "/pages/messages.php?conversation=" . $conversationId);
            exit;
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to create conversation";
        }
    }
}

// Get selected conversation details
$selectedConversation = null;
$otherUser = null;

if ($selectedConversationId) {
    foreach ($conversations as $conv) {
        if ($conv['id'] == $selectedConversationId) {
            $selectedConversation = $conv;
            $otherUser = [
                'id' => $conv['other_user_id'],
                'username' => $conv['other_username'],
                'display_name' => $conv['other_display_name'],
                'profile_picture' => $conv['other_profile_image']
            ];
            break;
        }
    }
}

// If no conversation selected, select the first one
if (!$selectedConversationId && !empty($conversations)) {
    $selectedConversationId = $conversations[0]['id'];
    $selectedConversation = $conversations[0];
    $otherUser = [
        'id' => $conversations[0]['other_user_id'],
        'username' => $conversations[0]['other_username'],
        'display_name' => $conversations[0]['other_display_name'],
        'profile_picture' => $conversations[0]['other_profile_image']
    ];
}

// Update user online status
updateUserOnlineStatus($currentUserId);

// Include header
include '../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Conversations Sidebar -->
        <div class="col-md-4 col-lg-3">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-envelope"></i> Messages
                        </h5>
                        <a href="<?php echo APP_URL; ?>/pages/user_directory.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus"></i> New
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($conversations)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-envelope fa-2x text-muted mb-3"></i>
                            <h6>No conversations yet</h6>
                            <p class="text-muted small">Connect with other users to start messaging!</p>
                            <a href="<?php echo APP_URL; ?>/pages/user_directory.php" class="btn btn-primary btn-sm">
                                Find People
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($conversations as $conv): ?>
                                <div class="list-group-item list-group-item-action <?php echo $selectedConversationId == $conv['id'] ? 'active' : ''; ?> <?php echo $conv['unread_count'] > 0 ? 'conversation-unread' : ''; ?> p-0">
                                    <div class="d-flex align-items-center">
                                        <!-- Main conversation link -->
                                        <a href="?conversation=<?php echo $conv['id']; ?>"
                                           class="flex-grow-1 text-decoration-none text-reset p-3 d-flex align-items-center">
                                            <!-- Profile Picture -->
                                            <div class="position-relative me-3">
                                                <?php if ($conv['other_profile_image']): ?>
                                                    <img src="<?php echo APP_URL . '/' . $conv['other_profile_image']; ?>"
                                                         alt="Profile" class="rounded-circle"
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                         style="width: 50px; height: 50px; background-color: #e9ecef;">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($conv['unread_count'] > 0): ?>
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger unread-badge">
                                                        <?php echo $conv['unread_count'] > 99 ? '99+' : $conv['unread_count']; ?>
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
                                                <p class="mb-0 text-muted small text-truncate message-preview">
                                                    <?php echo $conv['last_message'] ? htmlspecialchars(substr($conv['last_message'], 0, 50)) . '...' : 'No messages yet'; ?>
                                                </p>
                                            </div>
                                        </a>

                                        <!-- Delete button -->
                                        <div class="p-2">
                                            <button type="button"
                                                    class="btn btn-outline-danger btn-sm delete-conversation-btn"
                                                    data-conversation-id="<?php echo $conv['id']; ?>"
                                                    data-conversation-name="<?php echo htmlspecialchars($conv['other_display_name'] ?: $conv['other_username']); ?>"
                                                    title="Delete conversation">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Chat Area -->
        <div class="col-md-8 col-lg-9">
            <?php if ($selectedConversation && $otherUser): ?>
                <div class="card h-100">
                    <!-- Chat Header -->
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <!-- Profile Picture -->
                                <?php if ($otherUser['profile_picture']): ?>
                                    <img src="<?php echo APP_URL . '/' . $otherUser['profile_picture']; ?>"
                                         alt="Profile" class="rounded-circle me-3"
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px; background-color: #e9ecef;">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($otherUser['display_name'] ?: $otherUser['username']); ?></h6>
                                    <small class="text-muted">@<?php echo htmlspecialchars($otherUser['username']); ?></small>
                                </div>
                            </div>
                            
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" 
                                        data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="<?php echo APP_URL; ?>/pages/social_profile.php?id=<?php echo $otherUser['id']; ?>">
                                            <i class="fas fa-user"></i> View Profile
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" onclick="blockUser(<?php echo $otherUser['id']; ?>)">
                                            <i class="fas fa-ban"></i> Block User
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Messages -->
                    <div class="card-body p-0">
                        <div id="chat-messages" class="chat-messages-container" 
                             style="height: 400px; overflow-y: auto; padding: 15px; background-color: #f8f9fa;">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading messages...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Message Input -->
                    <div class="card-footer">


                        <!-- Regular Message Form -->
                        <form id="message-form" class="d-flex gap-2">
                            <input type="hidden" id="conversation-id" value="<?php echo $selectedConversationId; ?>">
                            <div class="flex-grow-1">
                                <textarea id="message-input" class="form-control" rows="2"
                                          placeholder="Type your message... (Press Ctrl+Enter to send)"
                                          maxlength="1000"></textarea>
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addEmoji('❤️')">
                                    <i class="fas fa-heart"></i>
                                </button>

                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div class="text-center">
                            <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                            <h5>Select a conversation</h5>
                            <p class="text-muted">Choose a conversation from the sidebar to start messaging.</p>
                            <?php if (empty($conversations)): ?>
                                <a href="<?php echo APP_URL; ?>/pages/user_directory.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Start New Conversation
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Direct messaging styles */
.chat-messages-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.message-bubble {
    max-width: 70%;
    margin-bottom: 15px;
    animation: fadeInUp 0.3s ease-out;
}

.message-bubble.own {
    margin-left: auto;
}

.message-bubble .message-content {
    padding: 10px 15px;
    border-radius: 18px;
    position: relative;
}

.message-bubble.own .message-content {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.message-bubble:not(.own) .message-content {
    background: white;
    border: 1px solid #dee2e6;
    color: #333;
}

.message-meta {
    font-size: 0.75rem;
    opacity: 0.7;
    margin-top: 5px;
}

.message-bubble.own .message-meta {
    text-align: right;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}





/* Delete conversation button styles */
.delete-conversation-btn {
    opacity: 0;
    transition: opacity 0.2s ease;
    border: none !important;
    background: transparent !important;
    color: #dc3545 !important;
}

.list-group-item:hover .delete-conversation-btn {
    opacity: 1;
}

.delete-conversation-btn:hover {
    background: #dc3545 !important;
    color: white !important;
    transform: scale(1.1);
}

/* Unread message indicators */
.conversation-unread {
    background-color: #f8f9fa !important;
    border-left: 4px solid #007bff !important;
}

.conversation-unread .message-preview {
    font-weight: 600 !important;
    color: #212529 !important;
}

.unread-badge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}
</style>

<script>
let currentConversationId = <?php echo $selectedConversationId ?: 'null'; ?>;
let currentUserId = <?php echo $currentUserId; ?>;
let lastMessageId = 0;
let isLoadingMessages = false;
let messagePollingInterval;
let lastMessageCheck = Math.floor(Date.now() / 1000);
let messageNotificationInterval;

// Initialize chat when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (currentConversationId) {
        initializeChat();
    }

    // Handle form submission
    const form = document.getElementById('message-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            sendMessage();
        });
    }

    // Handle Ctrl+Enter to send message
    const input = document.getElementById('message-input');
    if (input) {
        input.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });

        // Auto-resize textarea
        input.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
    }

    // Handle delete conversation buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-conversation-btn')) {
            const button = e.target.closest('.delete-conversation-btn');
            const conversationId = button.dataset.conversationId;
            const conversationName = button.dataset.conversationName;

            deleteConversation(conversationId, conversationName);
        }
    });
});

function initializeChat() {
    loadMessages();

    // Start polling for new messages every 3 seconds
    messagePollingInterval = setInterval(loadNewMessages, 3000);

    // Check for new messages across all conversations every 5 seconds
    messageNotificationInterval = setInterval(checkForNewMessageNotifications, 5000);

    // Update online status every 30 seconds
    setInterval(updateOnlineStatus, 30000);

    // Request notification permission
    requestNotificationPermission();
}

function loadMessages() {
    if (isLoadingMessages || !currentConversationId) return;
    
    isLoadingMessages = true;
    
    fetch(`<?php echo APP_URL; ?>/api/direct_messages.php?conversation_id=${currentConversationId}&limit=50`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMessages(data.messages);
                if (data.messages.length > 0) {
                    lastMessageId = Math.max(...data.messages.map(m => m.id));
                }
            }
        })
        .catch(error => {
            console.error('Error loading messages:', error);
        })
        .finally(() => {
            isLoadingMessages = false;
        });
}

function loadNewMessages() {
    if (isLoadingMessages || !currentConversationId) return;
    
    fetch(`<?php echo APP_URL; ?>/api/direct_messages.php?conversation_id=${currentConversationId}&after_id=${lastMessageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.messages.length > 0) {
                appendMessages(data.messages);
                lastMessageId = Math.max(...data.messages.map(m => m.id));
            }
        })
        .catch(error => {
            console.error('Error loading new messages:', error);
        });
}

function displayMessages(messages) {
    const container = document.getElementById('chat-messages');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (messages.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-envelope fa-2x mb-2"></i>
                <p>No messages yet. Start the conversation!</p>
            </div>
        `;
        return;
    }
    
    messages.forEach(message => {
        appendMessage(message);
    });
    
    scrollToBottom();
}

function appendMessages(messages) {
    messages.forEach(message => {
        appendMessage(message);
    });
    scrollToBottom();
}

function appendMessage(message) {
    const container = document.getElementById('chat-messages');
    if (!container) return;

    const isOwn = message.sender_id == currentUserId;

    const messageDiv = document.createElement('div');
    messageDiv.className = `message-bubble ${isOwn ? 'own' : ''}`;

    // Regular text message
    messageDiv.innerHTML = `
        <div class="message-content">
            <div>${escapeHtml(message.content).replace(/\n/g, '<br>')}</div>
            <div class="message-meta">
                ${formatMessageTime(message.created_at)}
            </div>
        </div>
    `;

    container.appendChild(messageDiv);
}



function sendMessage() {
    const input = document.getElementById('message-input');
    if (!input || !currentConversationId) return;
    
    const content = input.value.trim();
    if (!content) return;
    
    const submitBtn = document.querySelector('#message-form button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;
    
    fetch('<?php echo APP_URL; ?>/api/send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `conversation_id=${currentConversationId}&content=${encodeURIComponent(content)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            input.style.height = 'auto';
            loadNewMessages(); // Load the new message immediately
        } else {
            alert(data.message || 'Failed to send message');
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Failed to send message. Please try again.');
    })
    .finally(() => {
        if (submitBtn) submitBtn.disabled = false;
        input.focus();
    });
}

function addEmoji(emoji) {
    const input = document.getElementById('message-input');
    if (input) {
        input.value += emoji;
        input.focus();
    }
}

function blockUser(userId) {
    if (confirm('Are you sure you want to block this user? This will end your conversation and prevent them from contacting you.')) {
        fetch('<?php echo APP_URL; ?>/api/social_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=block&user_id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '<?php echo APP_URL; ?>/pages/messages.php';
            } else {
                alert(data.message || 'Failed to block user');
            }
        })
        .catch(error => {
            console.error('Error blocking user:', error);
            alert('Failed to block user. Please try again.');
        });
    }
}

function updateOnlineStatus() {
    fetch('<?php echo APP_URL; ?>/api/update_online_status.php', {
        method: 'POST'
    });
}

function scrollToBottom() {
    const container = document.getElementById('chat-messages');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

function formatMessageTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Request notification permission
function requestNotificationPermission() {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
}

// Check for new message notifications
function checkForNewMessageNotifications() {
    fetch(`<?php echo APP_URL; ?>/api/check_new_messages.php?last_check=${lastMessageCheck}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.new_messages && data.new_messages.length > 0) {
                data.new_messages.forEach(message => {
                    showMessageNotification(message);
                });
                lastMessageCheck = data.timestamp;

                // Update notification bell count
                if (typeof updateNotificationBellCount === 'function') {
                    updateNotificationBellCount();
                }
            }
        })
        .catch(error => {
            console.log('Could not check for new messages:', error);
        });
}

// Show message notification
function showMessageNotification(message) {
    const senderName = message.sender.display_name || message.sender.username;
    const title = `New message from ${senderName}`;
    const body = message.preview;

    // Show browser notification if permission granted
    if ('Notification' in window && Notification.permission === 'granted') {
        const notification = new Notification(title, {
            body: body,
            icon: message.sender.profile_image ?
                  `<?php echo APP_URL; ?>/${message.sender.profile_image}` :
                  '<?php echo APP_URL; ?>/assets/images/default-avatar.png',
            tag: `message-${message.conversation_id}`,
            requireInteraction: false
        });

        // Auto close after 5 seconds
        setTimeout(() => notification.close(), 5000);

        // Handle click to open conversation
        notification.onclick = function() {
            window.focus();
            window.location.href = `<?php echo APP_URL; ?>/pages/messages.php?conversation=${message.conversation_id}`;
            notification.close();
        };
    }

    // Show toast notification
    if (typeof showNotificationToast === 'function') {
        showNotificationToast(title, body, 'message');
    }

    // Play notification sound
    playNotificationSound();
}

// Play notification sound
function playNotificationSound() {
    try {
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT');
        audio.volume = 0.3;
        audio.play().catch(() => {
            // Ignore audio play errors (user interaction required)
        });
    } catch (e) {
        // Ignore audio errors
    }
}

// Delete conversation function
function deleteConversation(conversationId, conversationName) {
    // Show confirmation dialog
    if (!confirm(`Are you sure you want to delete the conversation with ${conversationName}?\n\nThis action cannot be undone and will permanently delete all messages in this conversation.`)) {
        return;
    }

    // Show loading state
    const button = document.querySelector(`[data-conversation-id="${conversationId}"]`);
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }

    // Send delete request
    fetch('<?php echo APP_URL; ?>/api/delete_conversation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `conversation_id=${conversationId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert('Conversation deleted successfully!');

            // If we're currently viewing the deleted conversation, redirect to messages page
            if (currentConversationId == conversationId) {
                window.location.href = '<?php echo APP_URL; ?>/pages/messages.php';
            } else {
                // Otherwise, just reload the page to update the conversation list
                window.location.reload();
            }
        } else {
            alert('Error: ' + data.message);

            // Restore button state
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-trash"></i>';
            }
        }
    })
    .catch(error => {
        console.error('Error deleting conversation:', error);
        alert('An error occurred while deleting the conversation. Please try again.');

        // Restore button state
        if (button) {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-trash"></i>';
        }
    });
}









// Cleanup when leaving page
window.addEventListener('beforeunload', function() {
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
    if (messageNotificationInterval) {
        clearInterval(messageNotificationInterval);
    }
});
</script>



<?php include '../includes/footer.php'; ?>
