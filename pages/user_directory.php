<?php
/**
 * MoodifyMe - User Directory
 * Search and discover other users to connect with
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
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$users = [];

// Search for users if query provided
if ($searchQuery) {
    $users = searchUsers($searchQuery, $currentUserId);
}

// Include header
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-users"></i> Discover People
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Find and connect with other members of the MoodifyMe community.</p>
                    
                    <!-- Search Form -->
                    <form method="GET" class="mb-4">
                        <div class="input-group">
                            <input type="text" class="form-control" name="q" 
                                   placeholder="Search by username or display name..." 
                                   value="<?php echo htmlspecialchars($searchQuery); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                    
                    <?php if ($searchQuery): ?>
                        <h5 class="mb-3">Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h5>
                        
                        <?php if (empty($users)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h5>No users found</h5>
                                <p class="text-muted">Try searching with different keywords.</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($users as $user): ?>
                                    <?php
                                    $isFollowing = isFollowing($currentUserId, $user['id']);
                                    $connectionStatus = getConnectionStatus($currentUserId, $user['id']);
                                    $onlineStatus = getUserOnlineStatus($user['id']);
                                    ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <!-- Profile Picture -->
                                                <div class="position-relative d-inline-block mb-3">
                                                    <?php if ($user['profile_image']): ?>
                                                        <img src="<?php echo APP_URL . '/' . $user['profile_image']; ?>" 
                                                             alt="Profile Picture" class="rounded-circle" 
                                                             style="width: 80px; height: 80px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 80px; height: 80px; background-color: #e9ecef;">
                                                            <i class="fas fa-user fa-2x text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Online Status -->
                                                    <span class="position-absolute" 
                                                          style="bottom: 5px; right: 5px; width: 16px; height: 16px; border-radius: 50%; border: 2px solid white;
                                                                 background-color: <?php echo $onlineStatus['status'] === 'online' ? '#28a745' : '#6c757d'; ?>;">
                                                    </span>
                                                </div>
                                                
                                                <!-- User Info -->
                                                <h6 class="card-title mb-1">
                                                    <?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?>
                                                </h6>
                                                <p class="text-muted small mb-2">@<?php echo htmlspecialchars($user['username']); ?></p>
                                                
                                                <?php if ($user['bio']): ?>
                                                    <p class="card-text small mb-3" style="max-height: 60px; overflow: hidden;">
                                                        <?php echo htmlspecialchars(substr($user['bio'], 0, 100)) . (strlen($user['bio']) > 100 ? '...' : ''); ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <!-- Social Stats -->
                                                <div class="d-flex justify-content-center gap-3 mb-3 small text-muted">
                                                    <span><strong><?php echo number_format($user['follower_count']); ?></strong> Followers</span>
                                                    <span><strong><?php echo number_format($user['following_count']); ?></strong> Following</span>
                                                </div>
                                                
                                                <!-- Action Buttons -->
                                                <div class="d-grid gap-2">
                                                    <a href="<?php echo APP_URL; ?>/pages/social_profile.php?id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye"></i> View Profile
                                                    </a>
                                                    
                                                    <div class="btn-group">
                                                        <!-- Follow Button -->
                                                        <button class="btn btn-<?php echo $isFollowing ? 'outline-primary' : 'primary'; ?> btn-sm" 
                                                                onclick="toggleFollow(<?php echo $user['id']; ?>, this)">
                                                            <i class="fas fa-<?php echo $isFollowing ? 'user-minus' : 'user-plus'; ?>"></i>
                                                            <?php echo $isFollowing ? 'Unfollow' : 'Follow'; ?>
                                                        </button>
                                                        
                                                        <!-- Connection Button -->
                                                        <?php if (!$connectionStatus): ?>
                                                            <button class="btn btn-outline-success btn-sm" 
                                                                    onclick="sendConnectionRequest(<?php echo $user['id']; ?>, this)">
                                                                <i class="fas fa-user-friends"></i>
                                                            </button>
                                                        <?php elseif ($connectionStatus['status'] === 'pending'): ?>
                                                            <button class="btn btn-outline-warning btn-sm" disabled>
                                                                <i class="fas fa-clock"></i>
                                                            </button>
                                                        <?php elseif ($connectionStatus['status'] === 'accepted'): ?>
                                                            <a href="<?php echo APP_URL; ?>/pages/messages.php?user=<?php echo $user['id']; ?>" 
                                                               class="btn btn-success btn-sm">
                                                                <i class="fas fa-comment"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Suggested Users or Welcome Message -->
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5>Find Your Community</h5>
                            <p class="text-muted">Search for other MoodifyMe members to connect with and support each other on your wellness journey.</p>
                            <div class="mt-4">
                                <h6>Tips for connecting:</h6>
                                <ul class="list-unstyled text-muted">
                                    <li><i class="fas fa-lightbulb text-warning"></i> Search by username or display name</li>
                                    <li><i class="fas fa-heart text-danger"></i> Follow users to see their updates</li>
                                    <li><i class="fas fa-user-friends text-success"></i> Send connection requests to enable direct messaging</li>
                                    <li><i class="fas fa-comments text-info"></i> Participate in community posts to meet new people</li>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFollow(userId, button) {
    const isFollowing = button.textContent.trim().includes('Unfollow');
    const action = isFollowing ? 'unfollow' : 'follow';
    
    // Disable button during request
    button.disabled = true;
    
    fetch('<?php echo APP_URL; ?>/api/social_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=${action}&user_id=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button appearance
            if (isFollowing) {
                button.className = 'btn btn-primary btn-sm';
                button.innerHTML = '<i class="fas fa-user-plus"></i> Follow';
            } else {
                button.className = 'btn btn-outline-primary btn-sm';
                button.innerHTML = '<i class="fas fa-user-minus"></i> Unfollow';
            }
        } else {
            alert(data.message || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        button.disabled = false;
    });
}

function sendConnectionRequest(userId, button) {
    // Disable button during request
    button.disabled = true;
    
    fetch('<?php echo APP_URL; ?>/api/social_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=connect&user_id=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button to show request sent
            button.className = 'btn btn-outline-warning btn-sm';
            button.innerHTML = '<i class="fas fa-clock"></i>';
            button.disabled = true;
        } else {
            alert(data.message || 'An error occurred');
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        button.disabled = false;
    });
}
</script>

<?php include '../includes/footer.php'; ?>
