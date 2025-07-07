<?php
/**
 * MoodifyMe - Notifications Center
 * View and manage all user notifications
 */

require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';
require_once '../includes/notification_functions.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect(APP_URL . '/pages/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'mark_read':
            $notificationId = intval($_POST['notification_id']);
            $success = markNotificationAsRead($notificationId, $userId);
            echo json_encode(['success' => $success]);
            exit;
            
        case 'mark_all_read':
            $success = markAllNotificationsAsRead($userId);
            echo json_encode(['success' => $success]);
            exit;
            
        case 'delete':
            $notificationId = intval($_POST['notification_id']);
            $success = deleteNotification($notificationId, $userId);
            echo json_encode(['success' => $success]);
            exit;
    }
}

// Get filter parameters
$filter = $_GET['filter'] ?? 'all';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Get notifications based on filter
$unreadOnly = ($filter === 'unread');
$notifications = getUserNotifications($userId, $limit, $offset, $unreadOnly);

// Get total counts
$totalUnread = getUnreadNotificationCount($userId);
$totalAll = count(getUserNotifications($userId, 1000, 0, false)); // Get all for count

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-bell"></i> Notifications</h2>
                <div class="btn-group" role="group">
                    <?php if ($totalUnread > 0): ?>
                        <button class="btn btn-outline-primary btn-sm" onclick="markAllAsRead()">
                            <i class="fas fa-check-double"></i> Mark All Read
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Filter Tabs -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter === 'all' ? 'active' : ''; ?>" 
                       href="?filter=all">
                        All (<?php echo $totalAll; ?>)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter === 'unread' ? 'active' : ''; ?>" 
                       href="?filter=unread">
                        Unread (<?php echo $totalUnread; ?>)
                    </a>
                </li>
            </ul>

            <!-- Notifications List -->
            <div class="card">
                <div class="card-body p-0">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <h5>No notifications</h5>
                            <p class="text-muted">
                                <?php echo $filter === 'unread' ? 'You have no unread notifications.' : 'You have no notifications yet.'; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="list-group-item notification-item <?php echo !$notification['is_read'] ? 'bg-light' : ''; ?>" 
                                     data-notification-id="<?php echo $notification['id']; ?>">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <!-- Notification Icon -->
                                                <div class="me-3">
                                                    <?php
                                                    $iconClass = 'fas fa-bell';
                                                    $iconColor = 'text-primary';
                                                    
                                                    switch ($notification['type']) {
                                                        case 'friend_request':
                                                            $iconClass = 'fas fa-user-plus';
                                                            $iconColor = 'text-success';
                                                            break;
                                                        case 'friend_accepted':
                                                            $iconClass = 'fas fa-user-check';
                                                            $iconColor = 'text-success';
                                                            break;
                                                        case 'message':
                                                            $iconClass = 'fas fa-envelope';
                                                            $iconColor = 'text-info';
                                                            break;
                                                        case 'system':
                                                            $iconClass = 'fas fa-cog';
                                                            $iconColor = 'text-warning';
                                                            break;
                                                    }
                                                    ?>
                                                    <i class="<?php echo $iconClass . ' ' . $iconColor; ?> fa-lg"></i>
                                                </div>
                                                
                                                <!-- User Avatar (if applicable) -->
                                                <?php if ($notification['related_user_image']): ?>
                                                    <img src="<?php echo APP_URL . '/' . $notification['related_user_image']; ?>" 
                                                         alt="User" class="rounded-circle me-2" width="32" height="32">
                                                <?php endif; ?>
                                                
                                                <!-- Notification Content -->
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                    <p class="mb-1 text-muted"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                    <small class="text-muted"><?php echo timeAgo($notification['created_at']); ?></small>
                                                </div>
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <?php if ($notification['action_url'] && $notification['action_text']): ?>
                                                <div class="mt-2">
                                                    <a href="<?php echo APP_URL . $notification['action_url']; ?>" 
                                                       class="btn btn-primary btn-sm me-2"
                                                       onclick="markAsRead(<?php echo $notification['id']; ?>)">
                                                        <?php echo htmlspecialchars($notification['action_text']); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Notification Actions -->
                                        <div class="dropdown">
                                            <button class="btn btn-link text-muted p-1" type="button" 
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <?php if (!$notification['is_read']): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="#" 
                                                           onclick="markAsRead(<?php echo $notification['id']; ?>)">
                                                            <i class="fas fa-check"></i> Mark as Read
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" 
                                                       onclick="deleteNotification(<?php echo $notification['id']; ?>)">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pagination -->
            <?php if (count($notifications) >= $limit): ?>
                <nav aria-label="Notifications pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?filter=<?php echo $filter; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                        </li>
                        <li class="page-item active">
                            <span class="page-link"><?php echo $page; ?></span>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?filter=<?php echo $filter; ?>&page=<?php echo $page + 1; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function markAsRead(notificationId) {
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=mark_read&notification_id=${notificationId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (item) {
                item.classList.remove('bg-light');
            }
            updateNotificationCount();
        }
    })
    .catch(error => console.error('Error:', error));
}

function markAllAsRead() {
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_all_read'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteNotification(notificationId) {
    if (confirm('Are you sure you want to delete this notification?')) {
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete&notification_id=${notificationId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (item) {
                    item.remove();
                }
                updateNotificationCount();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function updateNotificationCount() {
    // Update the notification count in the header
    fetch('<?php echo APP_URL; ?>/api/notification_count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notification-count');
            if (data.count > 0) {
                if (badge) {
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                }
            } else {
                if (badge) {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(error => console.error('Error updating notification count:', error));
}
</script>

<?php include '../includes/footer.php'; ?>
