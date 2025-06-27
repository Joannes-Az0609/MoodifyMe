<?php
/**
 * MoodifyMe - Social Profile Page
 * View and manage social profile features
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
$profileUserId = isset($_GET['id']) ? (int)$_GET['id'] : $currentUserId;
$isOwnProfile = ($currentUserId == $profileUserId);

// Get user profile data
$user = getUserProfileWithStats($profileUserId);
if (!$user) {
    redirect(APP_URL . '/pages/404.php');
}

// Check if user is blocked
if (!$isOwnProfile && (isUserBlocked($profileUserId, $currentUserId) || isUserBlocked($currentUserId, $profileUserId))) {
    redirect(APP_URL . '/pages/404.php');
}

// Check privacy settings
if (!$isOwnProfile && !$user['is_public']) {
    $connectionStatus = getConnectionStatus($currentUserId, $profileUserId);
    if (!$connectionStatus || $connectionStatus['status'] !== 'accepted') {
        redirect(APP_URL . '/pages/404.php');
    }
}

// Get social relationship status
$isFollowing = false;
$connectionStatus = null;
$onlineStatus = null;

if (!$isOwnProfile) {
    $isFollowing = isFollowing($currentUserId, $profileUserId);
    $connectionStatus = getConnectionStatus($currentUserId, $profileUserId);
    $onlineStatus = getUserOnlineStatus($profileUserId);
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $response = ['success' => false, 'message' => ''];
    
    switch ($_POST['action']) {
        case 'follow':
            if (followUser($currentUserId, $profileUserId)) {
                $response = ['success' => true, 'message' => 'User followed successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to follow user'];
            }
            break;
            
        case 'unfollow':
            if (unfollowUser($currentUserId, $profileUserId)) {
                $response = ['success' => true, 'message' => 'User unfollowed successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to unfollow user'];
            }
            break;
            
        case 'connect':
            if (sendConnectionRequest($currentUserId, $profileUserId)) {
                $response = ['success' => true, 'message' => 'Connection request sent'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to send connection request'];
            }
            break;
            
        case 'accept_connection':
            if (acceptConnectionRequest($profileUserId, $currentUserId)) {
                $response = ['success' => true, 'message' => 'Connection request accepted'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to accept connection request'];
            }
            break;
            
        case 'decline_connection':
            if (declineConnectionRequest($profileUserId, $currentUserId)) {
                $response = ['success' => true, 'message' => 'Connection request declined'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to decline connection request'];
            }
            break;
            
        case 'remove_connection':
            if (removeConnection($currentUserId, $profileUserId)) {
                $response = ['success' => true, 'message' => 'Connection removed'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to remove connection'];
            }
            break;
            
        case 'block':
            $reason = $_POST['reason'] ?? null;
            if (blockUser($currentUserId, $profileUserId, $reason)) {
                $response = ['success' => true, 'message' => 'User blocked successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to block user'];
            }
            break;
    }
    
    echo json_encode($response);
    exit;
}

// Include header
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- Profile Header -->
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <div class="profile-avatar-container position-relative">
                                <?php if ($user['profile_image']): ?>
                                    <img src="<?php echo APP_URL . '/' . $user['profile_image']; ?>" 
                                         alt="Profile Picture" class="profile-avatar rounded-circle" 
                                         style="width: 120px; height: 120px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="profile-avatar-placeholder rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 120px; height: 120px; background-color: #e9ecef;">
                                        <i class="fas fa-user fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!$isOwnProfile && $user['show_online_status'] && $onlineStatus): ?>
                                    <span class="online-status-indicator position-absolute" 
                                          style="bottom: 10px; right: 10px; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white;
                                                 background-color: <?php echo $onlineStatus['status'] === 'online' ? '#28a745' : '#6c757d'; ?>;">
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h2 class="mb-1"><?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?></h2>
                            <p class="text-muted mb-2">@<?php echo htmlspecialchars($user['username']); ?></p>
                            
                            <?php if ($user['bio']): ?>
                                <p class="mb-3"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                            <?php endif; ?>
                            
                            <div class="social-stats d-flex gap-4">
                                <div class="stat-item">
                                    <strong><?php echo number_format($user['follower_count']); ?></strong>
                                    <span class="text-muted">Followers</span>
                                </div>
                                <div class="stat-item">
                                    <strong><?php echo number_format($user['following_count']); ?></strong>
                                    <span class="text-muted">Following</span>
                                </div>
                                <div class="stat-item">
                                    <strong><?php echo number_format($user['connection_count']); ?></strong>
                                    <span class="text-muted">Connections</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <?php if ($isOwnProfile): ?>
                                <div class="d-grid gap-2">
                                    <a href="<?php echo APP_URL; ?>/pages/profile.php" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i> Edit Profile
                                    </a>
                                    <a href="<?php echo APP_URL; ?>/pages/settings.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-cog"></i> Settings
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="d-grid gap-2" id="social-actions">
                                    <!-- Follow Button -->
                                    <button class="btn <?php echo $isFollowing ? 'btn-outline-primary' : 'btn-primary'; ?>" 
                                            onclick="toggleFollow()" id="follow-btn">
                                        <i class="fas fa-<?php echo $isFollowing ? 'user-minus' : 'user-plus'; ?>"></i>
                                        <?php echo $isFollowing ? 'Unfollow' : 'Follow'; ?>
                                    </button>
                                    
                                    <!-- Connection Button -->
                                    <?php if (!$connectionStatus): ?>
                                        <button class="btn btn-outline-success" onclick="sendConnectionRequest()" id="connect-btn">
                                            <i class="fas fa-user-friends"></i> Connect
                                        </button>
                                    <?php elseif ($connectionStatus['status'] === 'pending'): ?>
                                        <?php if ($connectionStatus['requester_id'] == $currentUserId): ?>
                                            <button class="btn btn-outline-warning" disabled>
                                                <i class="fas fa-clock"></i> Request Sent
                                            </button>
                                        <?php else: ?>
                                            <div class="btn-group w-100">
                                                <button class="btn btn-success" onclick="acceptConnection()">
                                                    <i class="fas fa-check"></i> Accept
                                                </button>
                                                <button class="btn btn-outline-danger" onclick="declineConnection()">
                                                    <i class="fas fa-times"></i> Decline
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($connectionStatus['status'] === 'accepted'): ?>
                                        <div class="btn-group w-100">
                                            <a href="<?php echo APP_URL; ?>/pages/messages.php?user=<?php echo $profileUserId; ?>" 
                                               class="btn btn-success">
                                                <i class="fas fa-comment"></i> Message
                                            </a>
                                            <button class="btn btn-outline-danger" onclick="removeConnection()">
                                                <i class="fas fa-user-times"></i> Remove
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Block Button -->
                                    <button class="btn btn-outline-danger btn-sm" onclick="showBlockModal()">
                                        <i class="fas fa-ban"></i> Block User
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Profile Content Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="activity-tab" data-bs-toggle="tab" 
                                    data-bs-target="#activity" type="button" role="tab">
                                <i class="fas fa-chart-line"></i> Activity
                            </button>
                        </li>
                        <?php if ($isOwnProfile || ($connectionStatus && $connectionStatus['status'] === 'accepted')): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="mood-history-tab" data-bs-toggle="tab" 
                                        data-bs-target="#mood-history" type="button" role="tab">
                                    <i class="fas fa-heart"></i> Mood Journey
                                </button>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="profileTabsContent">
                        <div class="tab-pane fade show active" id="activity" role="tabpanel">
                            <div class="text-center py-5">
                                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                <h5>Activity Feed</h5>
                                <p class="text-muted">Recent activity and updates will appear here.</p>
                            </div>
                        </div>
                        
                        <?php if ($isOwnProfile || ($connectionStatus && $connectionStatus['status'] === 'accepted')): ?>
                            <div class="tab-pane fade" id="mood-history" role="tabpanel">
                                <div class="text-center py-5">
                                    <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                                    <h5>Mood Journey</h5>
                                    <p class="text-muted">Mood tracking history and insights will be displayed here.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Block User Modal -->
<div class="modal fade" id="blockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Block User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to block <strong><?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?></strong>?</p>
                <p class="text-muted small">This will remove any connections and prevent them from contacting you.</p>
                
                <div class="mb-3">
                    <label for="blockReason" class="form-label">Reason (optional)</label>
                    <select class="form-select" id="blockReason">
                        <option value="">Select a reason...</option>
                        <option value="spam">Spam</option>
                        <option value="harassment">Harassment</option>
                        <option value="inappropriate">Inappropriate content</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="blockUser()">Block User</button>
            </div>
        </div>
    </div>
</div>

<script>
// Social action functions
function toggleFollow() {
    const isFollowing = document.getElementById('follow-btn').textContent.trim().includes('Unfollow');
    const action = isFollowing ? 'unfollow' : 'follow';
    
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=${action}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function sendConnectionRequest() {
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=connect'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
}

function acceptConnection() {
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=accept_connection'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
}

function declineConnection() {
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=decline_connection'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
}

function removeConnection() {
    if (confirm('Are you sure you want to remove this connection?')) {
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=remove_connection'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}

function showBlockModal() {
    const modal = new bootstrap.Modal(document.getElementById('blockModal'));
    modal.show();
}

function blockUser() {
    const reason = document.getElementById('blockReason').value;
    
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=block&reason=${encodeURIComponent(reason)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?php echo APP_URL; ?>/pages/dashboard.php';
        } else {
            alert(data.message);
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
