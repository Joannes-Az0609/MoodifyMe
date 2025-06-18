<?php
/**
 * MoodifyMe - User Profile Page
 */

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    redirect(APP_URL . '/pages/login.php');
}

// Get user data
$userId = $_SESSION['user_id'];
$user = getUserById($userId);

// Initialize variables
$success = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $username = sanitizeInput($_POST['username']);
        $email = sanitizeInput($_POST['email']);
        $bio = sanitizeInput($_POST['bio']);
        
        // Validate input
        if (empty($username)) {
            $error = 'Username is required.';
        } elseif (empty($email)) {
            $error = 'Email is required.';
        } else {
            // Check if username already exists (excluding current user)
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->bind_param("si", $username, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Username already exists.';
            } else {
                // Check if email already exists (excluding current user)
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->bind_param("si", $email, $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error = 'Email already exists.';
                } else {
                    // Update user profile
                    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, bio = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->bind_param("sssi", $username, $email, $bio, $userId);
                    
                    if ($stmt->execute()) {
                        // Update session variables
                        $_SESSION['username'] = $username;
                        $_SESSION['email'] = $email;
                        
                        // Refresh user data
                        $user = getUserById($userId);
                        
                        $success = 'Profile updated successfully.';
                    } else {
                        $error = 'Failed to update profile.';
                    }
                }
            }
        }
    } elseif (isset($_POST['change_password'])) {
        // Change password
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Validate input
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'All password fields are required.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'New password must be at least 8 characters long.';
        } else {
            // Verify current password
            if (verifyPassword($currentPassword, $user['password'])) {
                // Hash new password
                $hashedPassword = hashPassword($newPassword);
                
                // Update password
                $stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("si", $hashedPassword, $userId);
                
                if ($stmt->execute()) {
                    $success = 'Password changed successfully.';
                } else {
                    $error = 'Failed to change password.';
                }
            } else {
                $error = 'Current password is incorrect.';
            }
        }
    } elseif (isset($_POST['upload_avatar'])) {
        // Handle profile image upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 2 * 1024 * 1024; // 2MB
            
            $file = $_FILES['profile_image'];
            
            // Validate file type
            if (!in_array($file['type'], $allowedTypes)) {
                $error = 'Invalid file type. Only JPEG, PNG, and GIF images are allowed.';
            } elseif ($file['size'] > $maxFileSize) {
                $error = 'File size exceeds the maximum limit of 2MB.';
            } else {
                // Create uploads directory if it doesn't exist
                $uploadsDir = '../assets/images/avatars';
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0755, true);
                }
                
                // Generate unique filename
                $filename = $userId . '_' . time() . '_' . basename($file['name']);
                $targetPath = $uploadsDir . '/' . $filename;
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    // Update user profile image
                    $profileImage = 'assets/images/avatars/' . $filename;
                    $stmt = $conn->prepare("UPDATE users SET profile_image = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->bind_param("si", $profileImage, $userId);
                    
                    if ($stmt->execute()) {
                        // Refresh user data
                        $user = getUserById($userId);
                        
                        $success = 'Profile image uploaded successfully.';
                    } else {
                        $error = 'Failed to update profile image.';
                    }
                } else {
                    $error = 'Failed to upload image.';
                }
            }
        } else {
            $error = 'No image selected or upload error occurred.';
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title">Your Profile</h2>
                    <p class="card-text">Manage your account information and preferences.</p>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img src="<?php echo APP_URL . '/' . $user['profile_image']; ?>" alt="Profile Image" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    <?php else: ?>
                        <div class="profile-placeholder mb-3">
                            <i class="fas fa-user-circle fa-7x text-secondary"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h3><?php echo $user['username']; ?></h3>
                    <p class="text-muted">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data" class="mt-3">
                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Change Profile Picture</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                        </div>
                        <button type="submit" name="upload_avatar" class="btn btn-outline-primary">Upload Image</button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Account Statistics</h3>
                    <ul class="list-group list-group-flush">
                        <?php
                        // Get mood count
                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM emotions WHERE user_id = ?");
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        $moodCount = $stmt->get_result()->fetch_assoc()['count'];
                        
                        // Get recommendation count
                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM recommendation_logs WHERE user_id = ?");
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        $recommendationCount = $stmt->get_result()->fetch_assoc()['count'];
                        
                        // Get feedback count
                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM recommendation_feedback WHERE user_id = ?");
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        $feedbackCount = $stmt->get_result()->fetch_assoc()['count'];
                        
                        // Get most common emotion
                        $stmt = $conn->prepare("
                            SELECT emotion_type, COUNT(*) as count 
                            FROM emotions 
                            WHERE user_id = ? 
                            GROUP BY emotion_type 
                            ORDER BY count DESC 
                            LIMIT 1
                        ");
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $mostCommonEmotion = $result->num_rows > 0 ? $result->fetch_assoc()['emotion_type'] : 'N/A';
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Moods Recorded
                            <span class="badge bg-primary rounded-pill"><?php echo $moodCount; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Recommendations Received
                            <span class="badge bg-primary rounded-pill"><?php echo $recommendationCount; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Feedback Provided
                            <span class="badge bg-primary rounded-pill"><?php echo $feedbackCount; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Most Common Mood
                            <span class="badge bg-secondary"><?php echo ucfirst($mostCommonEmotion); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">Profile Information</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab" aria-controls="password" aria-selected="false">Change Password</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="profileTabsContent">
                        <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo $user['username']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="bio" class="form-label">Bio</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo $user['bio'] ?? ''; ?></textarea>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                        
                        <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                                    <div class="form-text">Password must be at least 8 characters long.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Account Actions</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="<?php echo APP_URL; ?>/pages/settings.php" class="btn btn-outline-primary w-100 mb-3">
                                <i class="fas fa-cog"></i> Notification Settings
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo APP_URL; ?>/pages/history.php" class="btn btn-outline-primary w-100 mb-3">
                                <i class="fas fa-history"></i> View Mood History
                            </a>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h4>Data Management</h4>
                        <p class="text-muted">These actions affect your account data.</p>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#exportDataModal">
                                <i class="fas fa-download"></i> Export Your Data
                            </button>
                            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                <i class="fas fa-trash-alt"></i> Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Data Modal -->
<div class="modal fade" id="exportDataModal" tabindex="-1" aria-labelledby="exportDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportDataModalLabel">Export Your Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You can download all your data in JSON format. This includes:</p>
                <ul>
                    <li>Your profile information</li>
                    <li>Your mood history</li>
                    <li>Recommendations you've received</li>
                    <li>Your feedback on recommendations</li>
                </ul>
                <p>The export process may take a few moments to complete.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="<?php echo APP_URL; ?>/api/export_data.php" class="btn btn-primary">Export Data</a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAccountModalLabel">Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <p><strong>Warning:</strong> This action cannot be undone.</p>
                </div>
                <p>Deleting your account will permanently remove:</p>
                <ul>
                    <li>Your profile information</li>
                    <li>Your mood history</li>
                    <li>All recommendations and feedback</li>
                </ul>
                <p>Are you sure you want to delete your account?</p>
                
                <form id="deleteAccountForm" method="post" action="<?php echo APP_URL; ?>/api/delete_account.php">
                    <div class="mb-3">
                        <label for="delete_confirmation" class="form-label">Type "DELETE" to confirm</label>
                        <input type="text" class="form-control" id="delete_confirmation" name="delete_confirmation" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="deleteAccountForm" class="btn btn-danger">Delete Account</button>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>
