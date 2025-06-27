<?php
/**
 * MoodifyMe - Community Posts
 * Individual posts/blocks instead of chat rooms
 */

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect(APP_URL . '/pages/login.php');
}

$currentUserId = $_SESSION['user_id'];

// Handle post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $postType = $_POST['post_type'] ?? 'general';
    $moodTag = trim($_POST['mood_tag'] ?? '');
    $isAnonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    
    if (!empty($title) && !empty($content)) {
        $stmt = $conn->prepare("
            INSERT INTO community_posts (user_id, title, content, post_type, mood_tag, is_anonymous) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issssi", $currentUserId, $title, $content, $postType, $moodTag, $isAnonymous);
        
        if ($stmt->execute()) {
            $successMessage = "Your post has been shared with the community!";
        } else {
            $errorMessage = "Failed to create post. Please try again.";
        }
    } else {
        $errorMessage = "Please fill in both title and content.";
    }
}

// Get posts with pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$postsPerPage = 10;
$offset = ($page - 1) * $postsPerPage;

// Filter by post type if specified
$filterType = $_GET['filter'] ?? 'all';
$whereClause = "WHERE cp.is_active = TRUE";
$params = [];
$types = "";

if ($filterType !== 'all') {
    $whereClause .= " AND cp.post_type = ?";
    $params[] = $filterType;
    $types .= "s";
}

// Get posts - simplified query to avoid issues with missing tables
try {
    // First check if reaction/comment tables exist
    $reactionsTableExists = false;
    $commentsTableExists = false;

    $result = $conn->query("SHOW TABLES LIKE 'post_reactions'");
    if ($result && $result->num_rows > 0) {
        $reactionsTableExists = true;
    }

    $result = $conn->query("SHOW TABLES LIKE 'post_comments'");
    if ($result && $result->num_rows > 0) {
        $commentsTableExists = true;
    }

    // Build query based on available tables
    if ($reactionsTableExists && $commentsTableExists) {
        // Full query with reactions and comments
        $stmt = $conn->prepare("
            SELECT cp.*, u.username, u.profile_image,
                   (SELECT COUNT(*) FROM post_reactions pr WHERE pr.post_id = cp.id) as reaction_count,
                   (SELECT COUNT(*) FROM post_comments pc WHERE pc.post_id = cp.id AND pc.is_active = TRUE) as comment_count,
                   (SELECT COUNT(*) FROM post_reactions pr WHERE pr.post_id = cp.id AND pr.user_id = ?) as user_reacted
            FROM community_posts cp
            LEFT JOIN users u ON cp.user_id = u.id
            $whereClause
            ORDER BY cp.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $types = "i" . $types . "ii"; // user_id + filter params + limit + offset
        $params = array_merge([$currentUserId], $params, [$postsPerPage, $offset]);
    } else {
        // Simplified query without reactions/comments
        $stmt = $conn->prepare("
            SELECT cp.*, u.username, u.profile_image,
                   0 as reaction_count,
                   0 as comment_count,
                   0 as user_reacted
            FROM community_posts cp
            LEFT JOIN users u ON cp.user_id = u.id
            $whereClause
            ORDER BY cp.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $types = $types . "ii"; // filter params + limit + offset
        $params = array_merge($params, [$postsPerPage, $offset]);
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    error_log("Community posts query error: " . $e->getMessage());
    $posts = []; // Fallback to empty array
}

// Get total posts count for pagination
$countStmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM community_posts cp 
    $whereClause
");
if ($filterType !== 'all') {
    $countStmt->bind_param("s", $filterType);
}
$countStmt->execute();
$totalPosts = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalPosts / $postsPerPage);

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filter Posts
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="?filter=all" class="list-group-item list-group-item-action <?php echo $filterType === 'all' ? 'active' : ''; ?>">
                            <i class="fas fa-globe me-2"></i>All Posts
                        </a>
                        <a href="?filter=general" class="list-group-item list-group-item-action <?php echo $filterType === 'general' ? 'active' : ''; ?>">
                            <i class="fas fa-comments me-2"></i>General
                        </a>
                        <a href="?filter=support" class="list-group-item list-group-item-action <?php echo $filterType === 'support' ? 'active' : ''; ?>">
                            <i class="fas fa-hands-helping me-2"></i>Support
                        </a>
                        <a href="?filter=success" class="list-group-item list-group-item-action <?php echo $filterType === 'success' ? 'active' : ''; ?>">
                            <i class="fas fa-trophy me-2"></i>Success Stories
                        </a>
                        <a href="?filter=question" class="list-group-item list-group-item-action <?php echo $filterType === 'question' ? 'active' : ''; ?>">
                            <i class="fas fa-question-circle me-2"></i>Questions
                        </a>
                        <a href="?filter=mood_share" class="list-group-item list-group-item-action <?php echo $filterType === 'mood_share' ? 'active' : ''; ?>">
                            <i class="fas fa-heart me-2"></i>Mood Sharing
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Community Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-2">
                            <div class="text-muted small">Total Posts</div>
                            <div class="fw-bold text-primary"><?php echo $totalPosts; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-users me-2"></i>Community Posts
                </h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
                    <i class="fas fa-plus me-2"></i>Create Post
                </button>
            </div>
            
            <!-- Success/Error Messages -->
            <?php if (isset($successMessage)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo htmlspecialchars($successMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($errorMessage)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo htmlspecialchars($errorMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Posts Feed -->
            <div id="posts-container">
                <?php if (empty($posts)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No posts yet</h4>
                        <p class="text-muted">Be the first to share something with the community!</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
                            <i class="fas fa-plus me-2"></i>Create First Post
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="card mb-4 post-card" data-post-id="<?php echo $post['id']; ?>">
                            <div class="card-body">
                                <!-- Post Header -->
                                <div class="d-flex align-items-start mb-3">
                                    <div class="flex-shrink-0">
                                        <?php if ($post['is_anonymous']): ?>
                                            <div class="avatar-circle bg-secondary">
                                                <i class="fas fa-user-secret text-white"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="avatar-circle bg-primary">
                                                <?php echo strtoupper(substr($post['username'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">
                                                    <?php echo $post['is_anonymous'] ? 'Anonymous' : htmlspecialchars($post['username']); ?>
                                                    <span class="badge bg-<?php echo getPostTypeBadgeColor($post['post_type']); ?> ms-2">
                                                        <?php echo ucfirst(str_replace('_', ' ', $post['post_type'])); ?>
                                                    </span>
                                                </h6>
                                                <small class="text-muted">
                                                    <?php echo timeAgo($post['created_at']); ?>
                                                    <?php if (!empty($post['mood_tag'])): ?>
                                                        • <span class="text-info">#<?php echo htmlspecialchars($post['mood_tag']); ?></span>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Post Content -->
                                <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                                
                                <!-- Post Actions -->
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-outline-primary btn-sm reaction-btn" data-post-id="<?php echo $post['id']; ?>" data-reaction="like">
                                            <i class="fas fa-thumbs-up me-1"></i>
                                            <span class="reaction-count"><?php echo $post['reaction_count']; ?></span>
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" onclick="toggleComments(<?php echo $post['id']; ?>)">
                                            <i class="fas fa-comment me-1"></i>
                                            <?php echo $post['comment_count']; ?>
                                        </button>
                                    </div>
                                    <small class="text-muted">
                                        <?php if ($post['user_reacted'] > 0): ?>
                                            <i class="fas fa-heart text-danger me-1"></i>You reacted
                                        <?php endif; ?>
                                    </small>
                                </div>
                                
                                <!-- Comments Section (Initially Hidden) -->
                                <div class="comments-section mt-3" id="comments-<?php echo $post['id']; ?>" style="display: none;">
                                    <div class="border-top pt-3">
                                        <div class="comments-list" id="comments-list-<?php echo $post['id']; ?>">
                                            <!-- Comments will be loaded here -->
                                        </div>
                                        <div class="mt-3">
                                            <div class="input-group">
                                                <input type="text" class="form-control" placeholder="Write a comment..." id="comment-input-<?php echo $post['id']; ?>">
                                                <button class="btn btn-primary" onclick="addComment(<?php echo $post['id']; ?>)">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Posts pagination">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filterType; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Create Post Modal -->
<div class="modal fade" id="createPostModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>Create New Post
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Post Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required maxlength="255" placeholder="What's on your mind?">
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Content *</label>
                        <textarea class="form-control" id="content" name="content" rows="5" required placeholder="Share your thoughts, feelings, or experiences..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="post_type" class="form-label">Post Type</label>
                                <select class="form-select" id="post_type" name="post_type">
                                    <option value="general">General</option>
                                    <option value="support">Support Request</option>
                                    <option value="success">Success Story</option>
                                    <option value="question">Question</option>
                                    <option value="mood_share">Mood Sharing</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mood_tag" class="form-label">Mood Tag (Optional)</label>
                                <input type="text" class="form-control" id="mood_tag" name="mood_tag" maxlength="50" placeholder="e.g., anxious, happy, grateful">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_anonymous" name="is_anonymous">
                            <label class="form-check-label" for="is_anonymous">
                                Post anonymously
                                <small class="text-muted d-block">Your username won't be shown with this post</small>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_post" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Share Post
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

/* Override global card hover effects for post cards */
.post-card,
.post-card.card {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
    transition: none !important;
    transform: none !important;
}

.post-card:hover,
.post-card.card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
    transition: none !important;
    transform: none !important;
    animation: none !important;
}

.reaction-btn.active {
    background-color: var(--bs-primary);
    color: white;
}

.comments-section {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-top: 15px;
}
</style>

<script>
// Post reactions
function toggleReaction(postId, reactionType) {
    fetch('<?php echo APP_URL; ?>/api/toggle_post_reaction.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            post_id: postId,
            reaction_type: reactionType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update reaction count
            const btn = document.querySelector(`[data-post-id="${postId}"][data-reaction="${reactionType}"]`);
            const countSpan = btn.querySelector('.reaction-count');
            countSpan.textContent = data.reaction_count;

            // Toggle active state
            if (data.user_reacted) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

// Comments
function toggleComments(postId) {
    const commentsSection = document.getElementById(`comments-${postId}`);
    if (commentsSection.style.display === 'none') {
        commentsSection.style.display = 'block';
        loadComments(postId);
    } else {
        commentsSection.style.display = 'none';
    }
}

function loadComments(postId) {
    fetch(`<?php echo APP_URL; ?>/api/get_post_comments.php?post_id=${postId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const commentsList = document.getElementById(`comments-list-${postId}`);
                commentsList.innerHTML = data.comments.map(comment => `
                    <div class="comment mb-2">
                        <div class="d-flex">
                            <div class="avatar-circle bg-secondary me-2" style="width: 30px; height: 30px; font-size: 12px;">
                                ${comment.is_anonymous ? '<i class="fas fa-user-secret"></i>' : comment.username.charAt(0).toUpperCase()}
                            </div>
                            <div class="flex-grow-1">
                                <div class="bg-white rounded p-2">
                                    <small class="fw-bold">${comment.is_anonymous ? 'Anonymous' : comment.username}</small>
                                    <small class="text-muted ms-2">${comment.time_ago}</small>
                                    <div>${comment.content}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        })
        .catch(error => console.error('Error:', error));
}

function addComment(postId) {
    const input = document.getElementById(`comment-input-${postId}`);
    const content = input.value.trim();

    if (!content) return;

    fetch('<?php echo APP_URL; ?>/api/add_post_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            post_id: postId,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadComments(postId);

            // Update comment count
            const commentBtn = document.querySelector(`[onclick="toggleComments(${postId})"]`);
            const currentCount = parseInt(commentBtn.textContent.trim().split(' ').pop());
            commentBtn.innerHTML = `<i class="fas fa-comment me-1"></i>${currentCount + 1}`;
        }
    })
    .catch(error => console.error('Error:', error));
}

// Add event listeners for reaction buttons
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.reaction-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const reactionType = this.dataset.reaction;
            toggleReaction(postId, reactionType);
        });
    });
});
</script>

<?php
// Helper functions
function getPostTypeBadgeColor($type) {
    $colors = [
        'general' => 'primary',
        'support' => 'warning',
        'success' => 'success',
        'question' => 'info',
        'mood_share' => 'danger'
    ];
    return $colors[$type] ?? 'secondary';
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);

    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    if ($time < 2592000) return floor($time/86400) . 'd ago';

    return date('M j, Y', strtotime($datetime));
}

// Include footer
include '../includes/footer.php';
?>
