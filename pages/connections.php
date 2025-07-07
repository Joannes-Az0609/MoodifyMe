<?php
/**
 * MoodifyMe - Connections Management
 * Manage followers, following, and connection requests
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
$activeTab = $_GET['tab'] ?? 'followers';

// Get user's social data
$user = getUserProfileWithStats($currentUserId);

// Get followers
$followers = [];
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.display_name, u.profile_picture, u.bio, uf.created_at as followed_at
    FROM user_follows uf
    JOIN users u ON uf.follower_id = u.id
    WHERE uf.following_id = ?
    ORDER BY uf.created_at DESC
");
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $followers[] = $row;
}

// Get following
$following = [];
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.display_name, u.profile_picture, u.bio, uf.created_at as followed_at
    FROM user_follows uf
    JOIN users u ON uf.following_id = u.id
    WHERE uf.follower_id = ?
    ORDER BY uf.created_at DESC
");
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $following[] = $row;
}

// Get pending connection requests (received)
$pendingRequests = [];
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.display_name, u.profile_picture, u.bio, uc.created_at as request_date
    FROM user_connections uc
    JOIN users u ON uc.requester_id = u.id
    WHERE uc.receiver_id = ? AND uc.status = 'pending'
    ORDER BY uc.created_at DESC
");
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $pendingRequests[] = $row;
}

// Get sent connection requests
$sentRequests = [];
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.display_name, u.profile_picture, u.bio, uc.created_at as request_date
    FROM user_connections uc
    JOIN users u ON uc.receiver_id = u.id
    WHERE uc.requester_id = ? AND uc.status = 'pending'
    ORDER BY uc.created_at DESC
");
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $sentRequests[] = $row;
}

// Get accepted connections
$connections = [];
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.display_name, u.profile_picture, u.bio, uc.updated_at as connected_at
    FROM user_connections uc
    JOIN users u ON (CASE WHEN uc.requester_id = ? THEN uc.receiver_id ELSE uc.requester_id END) = u.id
    WHERE (uc.requester_id = ? OR uc.receiver_id = ?) AND uc.status = 'accepted'
    ORDER BY uc.updated_at DESC
");
$stmt->bind_param("iii", $currentUserId, $currentUserId, $currentUserId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $connections[] = $row;
}

// Include header
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-users"></i> My Connections
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Stats Overview -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-primary"><?php echo number_format($user['follower_count']); ?></h4>
                                <p class="text-muted mb-0">Followers</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-info"><?php echo number_format($user['following_count']); ?></h4>
                                <p class="text-muted mb-0">Following</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-success"><?php echo number_format($user['connection_count']); ?></h4>
                                <p class="text-muted mb-0">Connections</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-warning"><?php echo count($pendingRequests); ?></h4>
                                <p class="text-muted mb-0">Pending Requests</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation Tabs -->
                    <ul class="nav nav-tabs mb-4" id="connectionTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $activeTab === 'followers' ? 'active' : ''; ?>" 
                                    id="followers-tab" data-bs-toggle="tab" data-bs-target="#followers" type="button" role="tab">
                                <i class="fas fa-heart"></i> Followers (<?php echo count($followers); ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $activeTab === 'following' ? 'active' : ''; ?>" 
                                    id="following-tab" data-bs-toggle="tab" data-bs-target="#following" type="button" role="tab">
                                <i class="fas fa-user-plus"></i> Following (<?php echo count($following); ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $activeTab === 'connections' ? 'active' : ''; ?>" 
                                    id="connections-tab" data-bs-toggle="tab" data-bs-target="#connections" type="button" role="tab">
                                <i class="fas fa-user-friends"></i> Connections (<?php echo count($connections); ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $activeTab === 'requests' ? 'active' : ''; ?>" 
                                    id="requests-tab" data-bs-toggle="tab" data-bs-target="#requests" type="button" role="tab">
                                <i class="fas fa-clock"></i> Requests 
                                <?php if (count($pendingRequests) > 0): ?>
                                    <span class="badge bg-warning"><?php echo count($pendingRequests); ?></span>
                                <?php endif; ?>
                            </button>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content" id="connectionTabsContent">
                        <!-- Followers Tab -->
                        <div class="tab-pane fade <?php echo $activeTab === 'followers' ? 'show active' : ''; ?>" 
                             id="followers" role="tabpanel">
                            <?php if (empty($followers)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                                    <h5>No followers yet</h5>
                                    <p class="text-muted">Share your profile to gain followers!</p>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($followers as $follower): ?>
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <?php if ($follower['profile_picture']): ?>
                                                        <img src="<?php echo APP_URL . '/' . $follower['profile_picture']; ?>"
                                                             alt="Profile" class="rounded-circle mb-2"
                                                             style="width: 60px; height: 60px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                                             style="width: 60px; height: 60px; background-color: #e9ecef;">
                                                            <i class="fas fa-user fa-lg text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <h6 class="card-title mb-1">
                                                        <?php echo htmlspecialchars($follower['display_name'] ?: $follower['username']); ?>
                                                    </h6>
                                                    <p class="text-muted small mb-2">@<?php echo htmlspecialchars($follower['username']); ?></p>
                                                    <p class="text-muted small">Followed <?php echo formatDate($follower['followed_at']); ?></p>
                                                    
                                                    <div class="d-grid gap-2">
                                                        <a href="<?php echo APP_URL; ?>/pages/social_profile.php?id=<?php echo $follower['id']; ?>" 
                                                           class="btn btn-outline-primary btn-sm">View Profile</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Following Tab -->
                        <div class="tab-pane fade <?php echo $activeTab === 'following' ? 'show active' : ''; ?>" 
                             id="following" role="tabpanel">
                            <?php if (empty($following)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                                    <h5>Not following anyone yet</h5>
                                    <p class="text-muted">
                                        <a href="<?php echo APP_URL; ?>/pages/user_directory.php">Discover people</a> to follow!
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($following as $followed): ?>
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <?php if ($followed['profile_picture']): ?>
                                                        <img src="<?php echo APP_URL . '/' . $followed['profile_picture']; ?>"
                                                             alt="Profile" class="rounded-circle mb-2"
                                                             style="width: 60px; height: 60px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                                             style="width: 60px; height: 60px; background-color: #e9ecef;">
                                                            <i class="fas fa-user fa-lg text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <h6 class="card-title mb-1">
                                                        <?php echo htmlspecialchars($followed['display_name'] ?: $followed['username']); ?>
                                                    </h6>
                                                    <p class="text-muted small mb-2">@<?php echo htmlspecialchars($followed['username']); ?></p>
                                                    <p class="text-muted small">Following since <?php echo formatDate($followed['followed_at']); ?></p>
                                                    
                                                    <div class="d-grid gap-2">
                                                        <a href="<?php echo APP_URL; ?>/pages/social_profile.php?id=<?php echo $followed['id']; ?>" 
                                                           class="btn btn-outline-primary btn-sm">View Profile</a>
                                                        <button class="btn btn-outline-danger btn-sm" 
                                                                onclick="unfollowUser(<?php echo $followed['id']; ?>, this)">
                                                            <i class="fas fa-user-minus"></i> Unfollow
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Connections Tab -->
                        <div class="tab-pane fade <?php echo $activeTab === 'connections' ? 'show active' : ''; ?>" 
                             id="connections" role="tabpanel">
                            <?php if (empty($connections)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                                    <h5>No connections yet</h5>
                                    <p class="text-muted">Send connection requests to start messaging with other users!</p>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($connections as $connection): ?>
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <?php if ($connection['profile_picture']): ?>
                                                        <img src="<?php echo APP_URL . '/' . $connection['profile_picture']; ?>"
                                                             alt="Profile" class="rounded-circle mb-2"
                                                             style="width: 60px; height: 60px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                                                             style="width: 60px; height: 60px; background-color: #e9ecef;">
                                                            <i class="fas fa-user fa-lg text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <h6 class="card-title mb-1">
                                                        <?php echo htmlspecialchars($connection['display_name'] ?: $connection['username']); ?>
                                                    </h6>
                                                    <p class="text-muted small mb-2">@<?php echo htmlspecialchars($connection['username']); ?></p>
                                                    <p class="text-muted small">Connected <?php echo formatDate($connection['connected_at']); ?></p>
                                                    
                                                    <div class="d-grid gap-2">
                                                        <a href="<?php echo APP_URL; ?>/pages/messages.php?user=<?php echo $connection['id']; ?>" 
                                                           class="btn btn-success btn-sm">
                                                            <i class="fas fa-comment"></i> Message
                                                        </a>
                                                        <a href="<?php echo APP_URL; ?>/pages/social_profile.php?id=<?php echo $connection['id']; ?>" 
                                                           class="btn btn-outline-primary btn-sm">View Profile</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Requests Tab -->
                        <div class="tab-pane fade <?php echo $activeTab === 'requests' ? 'show active' : ''; ?>" 
                             id="requests" role="tabpanel">
                            <div class="row">
                                <!-- Pending Requests (Received) -->
                                <div class="col-md-6">
                                    <h5 class="mb-3">
                                        <i class="fas fa-inbox"></i> Received Requests 
                                        <?php if (count($pendingRequests) > 0): ?>
                                            <span class="badge bg-warning"><?php echo count($pendingRequests); ?></span>
                                        <?php endif; ?>
                                    </h5>
                                    
                                    <?php if (empty($pendingRequests)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">No pending requests</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($pendingRequests as $request): ?>
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($request['profile_picture']): ?>
                                                            <img src="<?php echo APP_URL . '/' . $request['profile_picture']; ?>"
                                                                 alt="Profile" class="rounded-circle me-3"
                                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                                 style="width: 50px; height: 50px; background-color: #e9ecef;">
                                                                <i class="fas fa-user text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">
                                                                <?php echo htmlspecialchars($request['display_name'] ?: $request['username']); ?>
                                                            </h6>
                                                            <p class="text-muted small mb-0">
                                                                @<?php echo htmlspecialchars($request['username']); ?> • 
                                                                <?php echo formatDate($request['request_date']); ?>
                                                            </p>
                                                        </div>
                                                        
                                                        <div class="btn-group">
                                                            <button class="btn btn-success btn-sm" 
                                                                    onclick="acceptConnection(<?php echo $request['id']; ?>, this)">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button class="btn btn-outline-danger btn-sm" 
                                                                    onclick="declineConnection(<?php echo $request['id']; ?>, this)">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Sent Requests -->
                                <div class="col-md-6">
                                    <h5 class="mb-3">
                                        <i class="fas fa-paper-plane"></i> Sent Requests 
                                        (<?php echo count($sentRequests); ?>)
                                    </h5>
                                    
                                    <?php if (empty($sentRequests)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-paper-plane fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">No sent requests</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($sentRequests as $request): ?>
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($request['profile_picture']): ?>
                                                            <img src="<?php echo APP_URL . '/' . $request['profile_picture']; ?>"
                                                                 alt="Profile" class="rounded-circle me-3"
                                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                                 style="width: 50px; height: 50px; background-color: #e9ecef;">
                                                                <i class="fas fa-user text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">
                                                                <?php echo htmlspecialchars($request['display_name'] ?: $request['username']); ?>
                                                            </h6>
                                                            <p class="text-muted small mb-0">
                                                                @<?php echo htmlspecialchars($request['username']); ?> • 
                                                                <?php echo formatDate($request['request_date']); ?>
                                                            </p>
                                                        </div>
                                                        
                                                        <span class="badge bg-warning">Pending</span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function unfollowUser(userId, button) {
    if (confirm('Are you sure you want to unfollow this user?')) {
        button.disabled = true;
        
        fetch('<?php echo APP_URL; ?>/api/social_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=unfollow&user_id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            button.disabled = false;
        });
    }
}

function acceptConnection(userId, button) {
    button.disabled = true;
    
    fetch('<?php echo APP_URL; ?>/api/social_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=accept_connection&user_id=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        button.disabled = false;
    });
}

function declineConnection(userId, button) {
    button.disabled = true;
    
    fetch('<?php echo APP_URL; ?>/api/social_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=decline_connection&user_id=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        button.disabled = false;
    });
}

// Set active tab based on URL parameter
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'followers';
    
    const tabButton = document.getElementById(activeTab + '-tab');
    if (tabButton) {
        tabButton.click();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
