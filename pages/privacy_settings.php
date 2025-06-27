<?php
/**
 * MoodifyMe - Privacy Settings
 * Manage user privacy and safety settings
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
$user = getUserProfileWithStats($currentUserId);

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_privacy'])) {
        $isPublic = isset($_POST['is_public']) ? 1 : 0;
        $allowDirectMessages = $_POST['allow_direct_messages'] ?? 'connections';
        $showOnlineStatus = isset($_POST['show_online_status']) ? 1 : 0;
        
        // Validate input
        $validMessageSettings = ['everyone', 'connections', 'none'];
        if (!in_array($allowDirectMessages, $validMessageSettings)) {
            $allowDirectMessages = 'connections';
        }
        
        // Update privacy settings
        $stmt = $conn->prepare("
            UPDATE users 
            SET is_public = ?, allow_direct_messages = ?, show_online_status = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->bind_param("isii", $isPublic, $allowDirectMessages, $showOnlineStatus, $currentUserId);
        
        if ($stmt->execute()) {
            $success = 'Privacy settings updated successfully.';
            // Refresh user data
            $user = getUserProfileWithStats($currentUserId);
        } else {
            $error = 'Failed to update privacy settings.';
        }
    }
}

// Get blocked users
$blockedUsers = [];
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.display_name, u.profile_image, ub.created_at as blocked_at, ub.reason
    FROM user_blocks ub
    JOIN users u ON ub.blocked_id = u.id
    WHERE ub.blocker_id = ?
    ORDER BY ub.created_at DESC
");
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $blockedUsers[] = $row;
}

// Include header
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-shield-alt"></i> Privacy & Safety Settings
                    </h3>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Privacy Settings Form -->
                    <form method="POST">
                        <h5 class="mb-3">
                            <i class="fas fa-eye"></i> Profile Visibility
                        </h5>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_public" name="is_public" 
                                       <?php echo $user['is_public'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_public">
                                    <strong>Public Profile</strong>
                                </label>
                            </div>
                            <small class="text-muted">
                                When enabled, other users can find and view your profile. When disabled, only your connections can see your profile.
                            </small>
                        </div>
                        
                        <h5 class="mb-3">
                            <i class="fas fa-envelope"></i> Messaging Preferences
                        </h5>
                        
                        <div class="mb-4">
                            <label for="allow_direct_messages" class="form-label">Who can send you direct messages?</label>
                            <select class="form-select" id="allow_direct_messages" name="allow_direct_messages">
                                <option value="everyone" <?php echo $user['allow_direct_messages'] === 'everyone' ? 'selected' : ''; ?>>
                                    Everyone
                                </option>
                                <option value="connections" <?php echo $user['allow_direct_messages'] === 'connections' ? 'selected' : ''; ?>>
                                    Only my connections
                                </option>
                                <option value="none" <?php echo $user['allow_direct_messages'] === 'none' ? 'selected' : ''; ?>>
                                    No one
                                </option>
                            </select>
                            <small class="text-muted">
                                Control who can start new conversations with you. You can always message your existing connections.
                            </small>
                        </div>
                        
                        <h5 class="mb-3">
                            <i class="fas fa-circle"></i> Online Status
                        </h5>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="show_online_status" name="show_online_status" 
                                       <?php echo $user['show_online_status'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="show_online_status">
                                    <strong>Show when I'm online</strong>
                                </label>
                            </div>
                            <small class="text-muted">
                                When enabled, other users can see when you're online or when you were last active.
                            </small>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="update_privacy" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Privacy Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Blocked Users -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-ban"></i> Blocked Users
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($blockedUsers)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-shield-alt fa-2x text-muted mb-3"></i>
                            <h6>No blocked users</h6>
                            <p class="text-muted">You haven't blocked anyone yet. You can block users from their profile or messages.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($blockedUsers as $blockedUser): ?>
                                <div class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        <!-- Profile Picture -->
                                        <?php if ($blockedUser['profile_image']): ?>
                                            <img src="<?php echo APP_URL . '/' . $blockedUser['profile_image']; ?>" 
                                                 alt="Profile" class="rounded-circle me-3" 
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 50px; height: 50px; background-color: #e9ecef;">
                                                <i class="fas fa-user text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- User Info -->
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <?php echo htmlspecialchars($blockedUser['display_name'] ?: $blockedUser['username']); ?>
                                            </h6>
                                            <p class="mb-1 text-muted">@<?php echo htmlspecialchars($blockedUser['username']); ?></p>
                                            <small class="text-muted">
                                                Blocked <?php echo formatDate($blockedUser['blocked_at']); ?>
                                                <?php if ($blockedUser['reason']): ?>
                                                    • Reason: <?php echo htmlspecialchars($blockedUser['reason']); ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        
                                        <!-- Unblock Button -->
                                        <button class="btn btn-outline-success btn-sm" 
                                                onclick="unblockUser(<?php echo $blockedUser['id']; ?>, this)">
                                            <i class="fas fa-unlock"></i> Unblock
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Safety Tips -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-lightbulb"></i> Safety Tips
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Protect Your Privacy</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Don't share personal information like addresses or phone numbers</li>
                                <li><i class="fas fa-check text-success"></i> Use a display name instead of your real name if you prefer</li>
                                <li><i class="fas fa-check text-success"></i> Be cautious about sharing photos that could identify your location</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Stay Safe</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-shield-alt text-primary"></i> Report inappropriate behavior immediately</li>
                                <li><i class="fas fa-shield-alt text-primary"></i> Block users who make you uncomfortable</li>
                                <li><i class="fas fa-shield-alt text-primary"></i> Trust your instincts - if something feels wrong, it probably is</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Remember:</strong> MoodifyMe is for emotional support and wellness. If you're in crisis, please contact emergency services or a crisis hotline immediately.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function unblockUser(userId, button) {
    if (confirm('Are you sure you want to unblock this user? They will be able to contact you again.')) {
        button.disabled = true;
        
        fetch('<?php echo APP_URL; ?>/api/social_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=unblock&user_id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to unblock user');
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
</script>

<?php include '../includes/footer.php'; ?>
