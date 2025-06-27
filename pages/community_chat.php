<?php
/**
 * MoodifyMe - Community Chat
 * Public chat rooms for community support
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

// Get selected room ID
$selectedRoomId = isset($_GET['room']) ? (int)$_GET['room'] : null;

// Get all available chat rooms
$chatRooms = [];
$stmt = $conn->prepare("
    SELECT cr.*, u.username as creator_name,
           (SELECT COUNT(*) FROM chat_room_participants crp 
            WHERE crp.chat_room_id = cr.id AND crp.is_active = TRUE) as participant_count
    FROM chat_rooms cr
    LEFT JOIN users u ON cr.created_by = u.id
    WHERE cr.is_active = TRUE AND cr.room_type = 'public'
    ORDER BY cr.name
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $chatRooms[] = $row;
}

// If no room selected, select the first available room
if (!$selectedRoomId && !empty($chatRooms)) {
    $selectedRoomId = $chatRooms[0]['id'];
}

// Get selected room details
$selectedRoom = null;
if ($selectedRoomId) {
    foreach ($chatRooms as $room) {
        if ($room['id'] == $selectedRoomId) {
            $selectedRoom = $room;
            break;
        }
    }
}

// Join the room if not already joined
if ($selectedRoom) {
    $stmt = $conn->prepare("
        INSERT INTO chat_room_participants (chat_room_id, user_id, last_read_at) 
        VALUES (?, ?, NOW()) 
        ON DUPLICATE KEY UPDATE is_active = TRUE, last_read_at = NOW()
    ");
    $stmt->bind_param("ii", $selectedRoomId, $currentUserId);
    $stmt->execute();
}

// Update user online status
updateUserOnlineStatus($currentUserId);

// Include header
include '../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Chat Rooms Sidebar -->
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-comments"></i> Community Rooms
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($chatRooms as $room): ?>
                            <a href="?room=<?php echo $room['id']; ?>" 
                               class="list-group-item list-group-item-action <?php echo $selectedRoomId == $room['id'] ? 'active' : ''; ?>">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($room['name']); ?></h6>
                                        <p class="mb-1 small text-muted"><?php echo htmlspecialchars($room['description']); ?></p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-secondary"><?php echo $room['participant_count']; ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> Choose a room to join the conversation
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Chat Area -->
        <div class="col-md-9">
            <?php if ($selectedRoom): ?>
                <div class="card h-100">
                    <!-- Chat Header -->
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><?php echo htmlspecialchars($selectedRoom['name']); ?></h5>
                                <small class="text-muted"><?php echo htmlspecialchars($selectedRoom['description']); ?></small>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-success" id="online-count">
                                    <i class="fas fa-users"></i> <span id="participant-count"><?php echo $selectedRoom['participant_count']; ?></span>
                                </span>
                                <button class="btn btn-outline-secondary btn-sm" onclick="toggleParticipantsList()">
                                    <i class="fas fa-users"></i> Participants
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chat Messages -->
                    <div class="card-body p-0 position-relative">
                        <div id="chat-messages" class="chat-messages-container" 
                             style="height: 400px; overflow-y: auto; padding: 15px; background-color: #f8f9fa;">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading messages...</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Participants Sidebar (Hidden by default) -->
                        <div id="participants-sidebar" class="position-absolute top-0 end-0 h-100 bg-white border-start" 
                             style="width: 250px; display: none; z-index: 10;">
                            <div class="p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Participants</h6>
                                    <button class="btn-close" onclick="toggleParticipantsList()"></button>
                                </div>
                            </div>
                            <div id="participants-list" class="p-3">
                                <div class="text-center">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chat Input -->
                    <div class="card-footer">
                        <form id="chat-form" class="d-flex gap-2">
                            <input type="hidden" id="room-id" value="<?php echo $selectedRoomId; ?>">
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
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt"></i> Be respectful and supportive. 
                                <a href="#" onclick="showCommunityGuidelines()">Community Guidelines</a>
                            </small>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div class="text-center">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <h5>Welcome to Community Chat</h5>
                            <p class="text-muted">Select a room from the sidebar to start chatting with the community.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Community Guidelines Modal -->
<div class="modal fade" id="guidelinesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Community Guidelines</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Our Community Values</h6>
                <ul>
                    <li><strong>Be Supportive:</strong> We're all here to support each other's mental wellness journey.</li>
                    <li><strong>Be Respectful:</strong> Treat everyone with kindness and respect.</li>
                    <li><strong>Be Safe:</strong> Don't share personal information like addresses or phone numbers.</li>
                    <li><strong>Be Appropriate:</strong> Keep conversations relevant and appropriate for all ages.</li>
                    <li><strong>Be Mindful:</strong> Remember that everyone is at different stages of their wellness journey.</li>
                </ul>
                
                <h6 class="mt-3">Not Allowed</h6>
                <ul>
                    <li>Harassment, bullying, or discrimination</li>
                    <li>Spam or promotional content</li>
                    <li>Sharing personal contact information</li>
                    <li>Inappropriate or offensive content</li>
                    <li>Medical advice (we're not medical professionals)</li>
                </ul>
                
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Crisis Support:</strong> If you're in crisis, please contact emergency services or a crisis hotline immediately.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
            </div>
        </div>
    </div>
</div>

<style>
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

.typing-indicator {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 10px 15px;
    background: white;
    border-radius: 18px;
    margin-bottom: 10px;
    max-width: 100px;
}

.typing-dots {
    display: flex;
    gap: 3px;
}

.typing-dots span {
    width: 6px;
    height: 6px;
    background: #6c757d;
    border-radius: 50%;
    animation: typing 1.4s infinite ease-in-out;
}

.typing-dots span:nth-child(1) { animation-delay: -0.32s; }
.typing-dots span:nth-child(2) { animation-delay: -0.16s; }

@keyframes typing {
    0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
    40% { transform: scale(1); opacity: 1; }
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

.participant-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.participant-item:last-child {
    border-bottom: none;
}

.online-indicator {
    width: 8px;
    height: 8px;
    background: #28a745;
    border-radius: 50%;
    display: inline-block;
}

.offline-indicator {
    width: 8px;
    height: 8px;
    background: #6c757d;
    border-radius: 50%;
    display: inline-block;
}
</style>

<script>
let currentRoomId = <?php echo $selectedRoomId ?: 'null'; ?>;
let currentUserId = <?php echo $currentUserId; ?>;
let lastMessageId = 0;
let isLoadingMessages = false;
let messagePollingInterval;

// Initialize chat when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (currentRoomId) {
        initializeChat();
        loadParticipants();
    }
    
    // Handle form submission
    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });
    
    // Handle Ctrl+Enter to send message
    document.getElementById('message-input').addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            sendMessage();
        }
    });
    
    // Auto-resize textarea
    document.getElementById('message-input').addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });
});

function initializeChat() {
    loadMessages();
    
    // Start polling for new messages every 3 seconds
    messagePollingInterval = setInterval(loadNewMessages, 3000);
    
    // Update online status every 30 seconds
    setInterval(updateOnlineStatus, 30000);
}

function loadMessages() {
    if (isLoadingMessages) return;

    isLoadingMessages = true;

    fetch(`<?php echo APP_URL; ?>/api/chat_messages.php?room_id=${currentRoomId}&limit=50`)
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
    if (isLoadingMessages) return;
    
    fetch(`<?php echo APP_URL; ?>/api/chat_messages.php?room_id=${currentRoomId}&after_id=${lastMessageId}`)
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
    container.innerHTML = '';
    
    if (messages.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-comments fa-2x mb-2"></i>
                <p>No messages yet. Be the first to start the conversation!</p>
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
    const isOwn = message.sender_id == currentUserId;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message-bubble ${isOwn ? 'own' : ''}`;
    
    const profileImage = message.profile_image 
        ? `<img src="<?php echo APP_URL; ?>/${message.profile_image}" alt="Profile" class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">`
        : `<div class="rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; background-color: #e9ecef;"><i class="fas fa-user text-muted"></i></div>`;
    
    messageDiv.innerHTML = `
        <div class="d-flex align-items-start ${isOwn ? 'flex-row-reverse' : ''}">
            ${!isOwn ? profileImage : ''}
            <div class="message-content">
                ${!isOwn ? `<div class="fw-bold small mb-1">${escapeHtml(message.display_name || message.username)}</div>` : ''}
                <div>${escapeHtml(message.content).replace(/\n/g, '<br>')}</div>
                <div class="message-meta">
                    ${formatMessageTime(message.created_at)}
                </div>
            </div>
            ${isOwn ? profileImage : ''}
        </div>
    `;
    
    container.appendChild(messageDiv);
}

function sendMessage() {
    const input = document.getElementById('message-input');
    const content = input.value.trim();
    
    if (!content) return;
    
    const submitBtn = document.querySelector('#chat-form button[type="submit"]');
    submitBtn.disabled = true;
    
    fetch('<?php echo APP_URL; ?>/api/send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `room_id=${currentRoomId}&content=${encodeURIComponent(content)}`
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
        submitBtn.disabled = false;
        input.focus();
    });
}

function loadParticipants() {
    fetch(`<?php echo APP_URL; ?>/api/chat_participants.php?room_id=${currentRoomId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayParticipants(data.participants);
                document.getElementById('participant-count').textContent = data.participants.length;
            }
        })
        .catch(error => {
            console.error('Error loading participants:', error);
        });
}

function displayParticipants(participants) {
    const container = document.getElementById('participants-list');
    
    if (participants.length === 0) {
        container.innerHTML = '<p class="text-muted">No participants</p>';
        return;
    }
    
    container.innerHTML = participants.map(participant => {
        const profileImage = participant.profile_image 
            ? `<img src="<?php echo APP_URL; ?>/${participant.profile_image}" alt="Profile" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">`
            : `<div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background-color: #e9ecef;"><i class="fas fa-user text-muted"></i></div>`;
        
        const onlineIndicator = participant.is_online 
            ? '<span class="online-indicator"></span>'
            : '<span class="offline-indicator"></span>';
        
        return `
            <div class="participant-item">
                ${profileImage}
                <div class="flex-grow-1">
                    <div class="fw-bold small">${escapeHtml(participant.display_name || participant.username)}</div>
                    <div class="text-muted small">@${escapeHtml(participant.username)}</div>
                </div>
                ${onlineIndicator}
            </div>
        `;
    }).join('');
}

function toggleParticipantsList() {
    const sidebar = document.getElementById('participants-sidebar');
    const isVisible = sidebar.style.display !== 'none';
    
    sidebar.style.display = isVisible ? 'none' : 'block';
    
    if (!isVisible) {
        loadParticipants();
    }
}

function addEmoji(emoji) {
    const input = document.getElementById('message-input');
    input.value += emoji;
    input.focus();
}

function showCommunityGuidelines() {
    const modal = new bootstrap.Modal(document.getElementById('guidelinesModal'));
    modal.show();
}

function updateOnlineStatus() {
    fetch('<?php echo APP_URL; ?>/api/update_online_status.php', {
        method: 'POST'
    });
}

function scrollToBottom() {
    const container = document.getElementById('chat-messages');
    container.scrollTop = container.scrollHeight;
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

// Cleanup when leaving page
window.addEventListener('beforeunload', function() {
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
});
</script>

<?php include '../includes/footer.php'; ?>
