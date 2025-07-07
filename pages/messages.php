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
                        <!-- Voice Recording Interface (Hidden by default) -->
                        <div id="voice-recording-interface" class="voice-recording-panel mb-3" style="display: none;">
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="voice-visualizer">
                                        <div class="recording-indicator"></div>
                                    </div>
                                    <div class="recording-info">
                                        <div class="recording-status">Starting recording...</div>
                                        <div class="recording-duration">0:00</div>
                                    </div>
                                </div>
                                <div class="voice-controls d-flex gap-2">
                                    <button type="button" id="start-recording-btn" class="btn btn-success">
                                        <i class="fas fa-microphone"></i> Start
                                    </button>
                                    <button type="button" id="stop-recording-btn" class="btn btn-danger" style="display: none;">
                                        <i class="fas fa-stop"></i> Stop
                                    </button>
                                    <button type="button" id="pause-recording-btn" class="btn btn-warning" style="display: none;">
                                        <i class="fas fa-pause"></i> Pause
                                    </button>
                                    <button type="button" id="play-recording-btn" class="btn btn-info" style="display: none;">
                                        <i class="fas fa-play"></i> Play
                                    </button>
                                    <button type="button" id="send-voice-btn" class="btn btn-primary" style="display: none;">
                                        <i class="fas fa-paper-plane"></i> Send
                                    </button>
                                    <button type="button" id="cancel-voice-btn" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>
                            </div>
                        </div>

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
                                <button type="button" id="voice-message-btn" class="btn btn-outline-primary btn-sm" title="Start Recording Voice Message">
                                    <i class="fas fa-microphone"></i>
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

/* Voice message styles */
.voice-recording-panel {
    border: 2px dashed #007bff;
    border-radius: 10px;
    background: rgba(0, 123, 255, 0.05);
}

/* Voice button enhancement */
#voice-message-btn {
    position: relative;
    transition: all 0.3s ease;
}

#voice-message-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
}

#voice-message-btn:active {
    transform: scale(0.95);
}

.voice-visualizer {
    width: 40px;
    height: 40px;
    position: relative;
}

.recording-indicator {
    width: 20px;
    height: 20px;
    background: #dc3545;
    border-radius: 50%;
    margin: 10px auto;
    animation: pulse 1.5s infinite;
}

.recording-indicator.recording {
    animation: pulse 0.8s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.2); opacity: 0.7; }
    100% { transform: scale(1); opacity: 1; }
}

.recording-info {
    text-align: left;
}

.recording-status {
    font-weight: 600;
    color: #007bff;
}

.recording-duration {
    font-size: 0.9rem;
    color: #6c757d;
    font-family: monospace;
}

.voice-message-bubble {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border-radius: 20px;
    padding: 15px;
    max-width: 300px;
}

.voice-message-bubble.own {
    background: linear-gradient(135deg, #007bff, #0056b3);
}

.voice-player {
    display: flex;
    align-items: center;
    gap: 10px;
}

.voice-play-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.voice-play-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.05);
}

.voice-waveform {
    flex: 1;
    height: 30px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    position: relative;
    overflow: hidden;
}

.voice-progress {
    height: 100%;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 15px;
    transition: width 0.1s ease;
}

.voice-duration {
    font-size: 0.8rem;
    opacity: 0.9;
    min-width: 35px;
    text-align: right;
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

    // Check if this is a voice message
    if (message.message_content_type === 'voice' && message.voice_file_path) {
        const voiceId = 'voice_' + message.id;
        messageDiv.innerHTML = `
            <div class="message-content voice-message-bubble ${isOwn ? 'own' : ''}">
                <div class="voice-player">
                    <button class="voice-play-btn" onclick="toggleVoicePlayback('${voiceId}', '<?php echo APP_URL; ?>/${message.voice_file_path}')">
                        <i class="fas fa-play" id="${voiceId}_icon"></i>
                    </button>
                    <div class="voice-waveform">
                        <div class="voice-progress" id="${voiceId}_progress" style="width: 0%"></div>
                    </div>
                    <div class="voice-duration">${formatVoiceDuration(message.voice_duration)}</div>
                </div>
                <div class="message-meta">
                    ${formatMessageTime(message.created_at)}
                </div>
            </div>
        `;
    } else {
        // Regular text message
        messageDiv.innerHTML = `
            <div class="message-content">
                <div>${escapeHtml(message.content).replace(/\n/g, '<br>')}</div>
                <div class="message-meta">
                    ${formatMessageTime(message.created_at)}
                </div>
            </div>
        `;
    }

    container.appendChild(messageDiv);
}

// Voice message playback functionality
const voicePlaybacks = {};

function toggleVoicePlayback(voiceId, audioUrl) {
    const icon = document.getElementById(voiceId + '_icon');
    const progress = document.getElementById(voiceId + '_progress');

    if (!voicePlaybacks[voiceId]) {
        // Create new audio player
        voicePlaybacks[voiceId] = new Audio(audioUrl);
        const audio = voicePlaybacks[voiceId];

        audio.addEventListener('loadedmetadata', () => {
            // Audio is ready
        });

        audio.addEventListener('timeupdate', () => {
            if (audio.duration) {
                const progressPercent = (audio.currentTime / audio.duration) * 100;
                progress.style.width = progressPercent + '%';
            }
        });

        audio.addEventListener('ended', () => {
            icon.className = 'fas fa-play';
            progress.style.width = '0%';
        });

        audio.addEventListener('error', (e) => {
            console.error('Audio playback error:', e);
            icon.className = 'fas fa-play';
            alert('Could not play voice message');
        });
    }

    const audio = voicePlaybacks[voiceId];

    if (audio.paused) {
        // Stop all other voice messages
        Object.keys(voicePlaybacks).forEach(id => {
            if (id !== voiceId && !voicePlaybacks[id].paused) {
                voicePlaybacks[id].pause();
                document.getElementById(id + '_icon').className = 'fas fa-play';
            }
        });

        audio.play().then(() => {
            icon.className = 'fas fa-pause';
        }).catch(error => {
            console.error('Playback failed:', error);
            alert('Could not play voice message');
        });
    } else {
        audio.pause();
        icon.className = 'fas fa-play';
    }
}

function formatVoiceDuration(seconds) {
    if (!seconds) return '0:00';
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = Math.floor(seconds % 60);
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
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

// Voice Message Functionality
let voiceRecorder = null;
let audioPlayer = null;
let currentRecordingBlob = null;

// Initialize voice message functionality
function initializeVoiceMessages() {
    const voiceBtn = document.getElementById('voice-message-btn');
    const voiceInterface = document.getElementById('voice-recording-interface');
    const messageForm = document.getElementById('message-form');

    if (!voiceBtn || !voiceInterface) return;

    // Start recording immediately when voice button is clicked
    // This provides a seamless user experience - no need for separate "start recording" step
    voiceBtn.addEventListener('click', async function() {
        voiceInterface.style.display = 'block';
        messageForm.style.display = 'none';

        // Initialize recorder if not already done
        if (!voiceRecorder) {
            voiceRecorder = new VoiceRecorder({
                maxDuration: 300, // 5 minutes
                minDuration: 1,   // 1 second
                onRecordingStart: () => {
                    updateRecordingUI('recording');
                },
                onRecordingStop: (blob) => {
                    currentRecordingBlob = blob;
                    updateRecordingUI('stopped');
                },
                onDurationUpdate: (duration) => {
                    document.querySelector('.recording-duration').textContent = formatDuration(duration);
                },
                onError: (error) => {
                    alert('Voice recording error: ' + error);
                    cancelVoiceMessage();
                }
            });
        }

        // Start recording immediately
        updateRecordingUI('initializing');

        try {
            const started = await voiceRecorder.initializeAndStartRecording();
            if (!started) {
                alert('Could not access microphone. Please check permissions.');
                cancelVoiceMessage();
                return;
            }
        } catch (error) {
            alert('Failed to start recording: ' + error.message);
            cancelVoiceMessage();
        }
    });

    // Recording controls
    document.getElementById('start-recording-btn').addEventListener('click', () => {
        voiceRecorder.startRecording();
    });

    document.getElementById('stop-recording-btn').addEventListener('click', () => {
        voiceRecorder.stopRecording();
    });

    document.getElementById('pause-recording-btn').addEventListener('click', () => {
        if (voiceRecorder.isPaused) {
            voiceRecorder.resumeRecording();
        } else {
            voiceRecorder.pauseRecording();
        }
    });

    document.getElementById('play-recording-btn').addEventListener('click', () => {
        if (currentRecordingBlob) {
            if (!audioPlayer) {
                audioPlayer = new AudioPlayer();
                audioPlayer.onPlay = () => {
                    document.querySelector('#play-recording-btn i').className = 'fas fa-pause';
                };
                audioPlayer.onPause = () => {
                    document.querySelector('#play-recording-btn i').className = 'fas fa-play';
                };
                audioPlayer.onEnded = () => {
                    document.querySelector('#play-recording-btn i').className = 'fas fa-play';
                };
            }

            if (audioPlayer.isPlaying) {
                audioPlayer.pause();
            } else {
                audioPlayer.loadFromBlob(currentRecordingBlob);
                audioPlayer.play();
            }
        }
    });

    document.getElementById('send-voice-btn').addEventListener('click', () => {
        sendVoiceMessage();
    });

    document.getElementById('cancel-voice-btn').addEventListener('click', () => {
        cancelVoiceMessage();
    });
}

function updateRecordingUI(state) {
    const startBtn = document.getElementById('start-recording-btn');
    const stopBtn = document.getElementById('stop-recording-btn');
    const pauseBtn = document.getElementById('pause-recording-btn');
    const playBtn = document.getElementById('play-recording-btn');
    const sendBtn = document.getElementById('send-voice-btn');
    const status = document.querySelector('.recording-status');
    const indicator = document.querySelector('.recording-indicator');

    // Hide all buttons first
    [startBtn, stopBtn, pauseBtn, playBtn, sendBtn].forEach(btn => {
        btn.style.display = 'none';
    });

    switch (state) {
        case 'initializing':
            status.textContent = 'Starting recording...';
            indicator.classList.add('recording');
            break;
        case 'ready':
            startBtn.style.display = 'inline-block';
            status.textContent = 'Ready to record';
            indicator.classList.remove('recording');
            break;
        case 'recording':
            stopBtn.style.display = 'inline-block';
            pauseBtn.style.display = 'inline-block';
            status.textContent = 'Recording...';
            indicator.classList.add('recording');
            break;
        case 'stopped':
            playBtn.style.display = 'inline-block';
            sendBtn.style.display = 'inline-block';
            startBtn.style.display = 'inline-block';
            status.textContent = 'Recording complete';
            indicator.classList.remove('recording');
            break;
    }
}

function sendVoiceMessage() {
    if (!currentRecordingBlob || !voiceRecorder) {
        alert('No recording to send');
        return;
    }

    const conversationId = document.getElementById('conversation-id').value;
    if (!conversationId) {
        alert('No conversation selected');
        return;
    }

    const formData = new FormData();
    formData.append('voice_file', currentRecordingBlob, 'voice_message.webm');
    formData.append('conversation_id', conversationId);
    formData.append('duration', voiceRecorder.recordingDuration);

    // Show sending indicator
    document.querySelector('.recording-status').textContent = 'Sending...';
    document.getElementById('send-voice-btn').disabled = true;

    fetch('<?php echo APP_URL; ?>/api/send_voice_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add message to chat
            if (data.data) {
                appendMessage(data.data);
                scrollToBottom();
            }
            cancelVoiceMessage();

            // Refresh conversation list to update last message
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            alert('Failed to send voice message: ' + data.message);
            document.getElementById('send-voice-btn').disabled = false;
            updateRecordingUI('stopped');
        }
    })
    .catch(error => {
        console.error('Error sending voice message:', error);
        alert('Failed to send voice message. Please try again.');
        document.getElementById('send-voice-btn').disabled = false;
        updateRecordingUI('stopped');
    });
}

function cancelVoiceMessage() {
    const voiceInterface = document.getElementById('voice-recording-interface');
    const messageForm = document.getElementById('message-form');

    voiceInterface.style.display = 'none';
    messageForm.style.display = 'flex';

    // Stop recording if active
    if (voiceRecorder && voiceRecorder.isRecording) {
        voiceRecorder.stopRecording();
    }

    // Stop playback if active
    if (audioPlayer && audioPlayer.isPlaying) {
        audioPlayer.stop();
    }

    // Reset state
    currentRecordingBlob = null;
    document.querySelector('.recording-duration').textContent = '0:00';
    document.querySelector('.recording-status').textContent = 'Ready to record';
    document.getElementById('send-voice-btn').disabled = false;
}

function formatDuration(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = Math.floor(seconds % 60);
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
}

// Initialize voice messages when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeVoiceMessages();
});

// Cleanup when leaving page
window.addEventListener('beforeunload', function() {
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
    if (messageNotificationInterval) {
        clearInterval(messageNotificationInterval);
    }

    // Cleanup voice recorder
    if (voiceRecorder) {
        voiceRecorder.destroy();
    }
    if (audioPlayer) {
        audioPlayer.destroy();
    }
});
</script>

<!-- Include voice recorder script -->
<script src="<?php echo APP_URL; ?>/assets/js/voice-recorder.js"></script>

<?php include '../includes/footer.php'; ?>
