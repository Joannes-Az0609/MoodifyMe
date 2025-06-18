<?php
/**
 * MoodifyMe - Admin Contact Messages Panel
 * View and manage contact form submissions
 */

require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';

session_start();

// Check if user is logged in (you can add admin role check here)
if (!isset($_SESSION['user_id'])) {
    redirect(APP_URL . '/pages/login.php');
    exit;
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $messageId = intval($_POST['message_id']);
    $newStatus = sanitizeInput($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE contact_messages SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $messageId);
    $stmt->execute();
    
    $success = "Message status updated successfully.";
}

// Get filter parameters
$statusFilter = $_GET['status'] ?? 'all';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$whereClause = "";
$params = [];
$types = "";

if ($statusFilter !== 'all') {
    $whereClause = "WHERE status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM contact_messages $whereClause";
if (!empty($params)) {
    $stmt = $conn->prepare($countQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $totalResult = $stmt->get_result();
} else {
    $totalResult = $conn->query($countQuery);
}
$totalMessages = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalMessages / $limit);

// Get messages
$query = "SELECT * FROM contact_messages $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$messages = $stmt->get_result();

include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h1 class="card-title">📧 Contact Messages</h1>
                    <p class="card-text">Manage contact form submissions from users.</p>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5>Filter Messages</h5>
                    <div class="btn-group" role="group">
                        <a href="?status=all" class="btn <?php echo $statusFilter === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            All (<?php echo $totalMessages; ?>)
                        </a>
                        <a href="?status=new" class="btn <?php echo $statusFilter === 'new' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            New
                        </a>
                        <a href="?status=read" class="btn <?php echo $statusFilter === 'read' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            Read
                        </a>
                        <a href="?status=replied" class="btn <?php echo $statusFilter === 'replied' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            Replied
                        </a>
                        <a href="?status=archived" class="btn <?php echo $statusFilter === 'archived' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            Archived
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Messages -->
    <div class="row">
        <div class="col-md-12">
            <?php if ($messages->num_rows > 0): ?>
                <?php while ($message = $messages->fetch_assoc()): ?>
                    <div class="card mb-3 <?php echo $message['status'] === 'new' ? 'border-primary' : ''; ?>">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                <span class="text-muted">&lt;<?php echo htmlspecialchars($message['email']); ?>&gt;</span>
                                <span class="badge bg-<?php 
                                    echo match($message['status']) {
                                        'new' => 'primary',
                                        'read' => 'info',
                                        'replied' => 'success',
                                        'archived' => 'secondary',
                                        default => 'secondary'
                                    };
                                ?>"><?php echo ucfirst($message['status']); ?></span>
                            </div>
                            <small class="text-muted">
                                <?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?>
                            </small>
                        </div>
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted">
                                Subject: <?php echo htmlspecialchars($message['subject']); ?>
                            </h6>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        IP: <?php echo htmlspecialchars($message['ip_address']); ?>
                                    </small>
                                </div>
                                <div class="col-md-6 text-end">
                                    <!-- Status Update Form -->
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                        <select name="status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                            <option value="new" <?php echo $message['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                            <option value="read" <?php echo $message['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                            <option value="replied" <?php echo $message['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                                            <option value="archived" <?php echo $message['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-sm btn-outline-primary">Update</button>
                                    </form>
                                    
                                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo urlencode($message['subject']); ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-reply"></i> Reply
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Messages pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?status=<?php echo $statusFilter; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?status=<?php echo $statusFilter; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?status=<?php echo $statusFilter; ?>&page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center">
                        <h5>No messages found</h5>
                        <p class="text-muted">No contact form submissions match your current filter.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
