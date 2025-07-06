<?php
/**
 * MoodifyMe - Mood Options Page
 *
 * This page displays options for how the user would like to attain their desired mood
 */

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';

// Start session
session_start();

// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    redirect(APP_URL . '/pages/login.php');
}

// Get user ID
$userId = $_SESSION['user_id'];

// Get source and target emotions from URL parameters
$sourceEmotion = isset($_GET['source']) ? sanitizeInput($_GET['source']) : '';
$targetEmotion = isset($_GET['target']) ? sanitizeInput($_GET['target']) : '';
$emotionId = isset($_GET['emotion_id']) ? sanitizeInput($_GET['emotion_id']) : '';

// If source or target emotion is not provided, redirect to home page
if (empty($sourceEmotion) || empty($targetEmotion)) {
    // Check if user has recent emotions
    $stmt = $conn->prepare("SELECT emotion_type FROM emotions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $sourceEmotion = $row['emotion_type'];
        $targetEmotion = 'happy'; // Default target emotion
    } else {
        // Redirect to home page
        redirect(APP_URL);
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">How would you like to attain your desired mood?</h2>

                    <div class="mood-transition-info text-center mb-4">
                        <p class="lead">
                            We'll help you transition from
                            <span class="badge bg-primary"><?php echo ucfirst($sourceEmotion); ?></span>
                            to
                            <span class="badge bg-success"><?php echo ucfirst($targetEmotion); ?></span>
                        </p>
                    </div>

                    <div class="recommendation-options">
                        <div class="row g-4">
                            <!-- African Meals Option -->
                            <div class="col-md-6">
                                <div class="card h-100 option-card">
                                    <div class="card-body text-center p-4">
                                        <div class="option-icon mb-3">
                                            <i class="fas fa-utensils fa-3x text-primary"></i>
                                        </div>
                                        <h3 class="card-title">African Meals</h3>
                                        <p class="card-text">
                                            Discover traditional African recipes that can help enhance your mood.
                                        </p>
                                        <a href="<?php echo APP_URL; ?>/pages/recommendations.php?source=<?php echo urlencode($sourceEmotion); ?>&target=<?php echo urlencode($targetEmotion); ?>&emotion_id=<?php echo urlencode($emotionId); ?>&type=african_meals" class="btn btn-primary btn-lg mt-3 w-100">
                                            <i class="fas fa-utensils me-2"></i> View African Meals
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Music Option -->
                            <div class="col-md-6">
                                <div class="card h-100 option-card">
                                    <div class="card-body text-center p-4">
                                        <div class="option-icon mb-3">
                                            <i class="fas fa-music fa-3x text-success"></i>
                                        </div>
                                        <h3 class="card-title">Music</h3>
                                        <p class="card-text">
                                            Listen to music that can help you transition to your desired mood.
                                        </p>
                                        <a href="<?php echo APP_URL; ?>/pages/spotify_music.php?source=<?php echo urlencode($sourceEmotion); ?>&target=<?php echo urlencode($targetEmotion); ?>&emotion_id=<?php echo urlencode($emotionId); ?>" class="btn btn-success btn-lg mt-3 w-100">
                                            <i class="fab fa-spotify me-2"></i> View Music
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Movies Option -->
                            <div class="col-md-6">
                                <div class="card h-100 option-card">
                                    <div class="card-body text-center p-4">
                                        <div class="option-icon mb-3">
                                            <i class="fas fa-film fa-3x text-danger"></i>
                                        </div>
                                        <h3 class="card-title">Movies</h3>
                                        <p class="card-text">
                                            Explore movie genres that can help you achieve your target mood.
                                        </p>
                                        <a href="<?php echo APP_URL; ?>/pages/movie_genre_selection.php?source=<?php echo urlencode($sourceEmotion); ?>&target=<?php echo urlencode($targetEmotion); ?>&emotion_id=<?php echo urlencode($emotionId); ?>" class="btn btn-danger btn-lg mt-3 w-100">
                                            <i class="fas fa-film me-2"></i> Choose Movie Genre
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- AI Support Option -->
                            <div class="col-md-6">
                                <div class="card h-100 option-card">
                                    <div class="card-body text-center p-4">
                                        <div class="option-icon mb-3">
                                            <i class="fas fa-robot fa-3x text-info"></i>
                                        </div>
                                        <h3 class="card-title">AI Support</h3>
                                        <p class="card-text">
                                            Chat with our AI assistant for personalized emotional support.
                                        </p>
                                        <a href="<?php echo APP_URL; ?>/pages/ai_chat_redirect.php?source=<?php echo urlencode($sourceEmotion); ?>&target=<?php echo urlencode($targetEmotion); ?>&emotion_id=<?php echo urlencode($emotionId); ?>" class="btn btn-info btn-lg mt-3 w-100">
                                            <i class="fas fa-robot me-2"></i> Chat with AI
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-5">
                        <a href="<?php echo APP_URL; ?>/pages/recommendations.php?source=<?php echo urlencode($sourceEmotion); ?>&target=<?php echo urlencode($targetEmotion); ?>&emotion_id=<?php echo urlencode($emotionId); ?>" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i> View All Recommendations
                        </a>
                        <a href="<?php echo APP_URL; ?>" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-redo me-2"></i> New Mood Check
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.option-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 15px;
    overflow: hidden;
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.option-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
}

.option-icon {
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mood-transition-info {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.badge {
    font-size: 1rem;
    padding: 8px 15px;
}
</style>

<?php include '../includes/footer.php'; ?>
