<?php
/**
 * MoodifyMe - AI Assistant Page
 * Chat interface for AI emotional support
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

// Get user ID
$userId = $_SESSION['user_id'];

// Get parameters from URL
$sourceEmotion = isset($_GET['source']) ? sanitizeInput($_GET['source']) : '';
$targetEmotion = isset($_GET['target']) ? sanitizeInput($_GET['target']) : '';
$emotionId = isset($_GET['emotion_id']) ? sanitizeInput($_GET['emotion_id']) : '';

// If source or target emotion is not provided, redirect to home page
if (empty($sourceEmotion) || empty($targetEmotion)) {
    redirect(APP_URL);
}

// Include header
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">🤖 AI Emotional Support</h2>
                    
                    <div class="mood-transition-info text-center mb-4">
                        <p class="lead">
                            AI support to help you transition from
                            <span class="badge bg-primary"><?php echo ucfirst($sourceEmotion); ?></span>
                            to
                            <span class="badge bg-success"><?php echo ucfirst($targetEmotion); ?></span>
                        </p>
                    </div>

                    <div class="text-center py-5">
                        <i class="fas fa-robot fa-5x text-info mb-4"></i>
                        <h3>AI Assistant Coming Soon!</h3>
                        <p class="lead text-muted mb-4">
                            We're developing an intelligent AI assistant that will provide personalized 
                            emotional support and guidance for your mood transitions.
                        </p>
                        
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-info-circle me-2"></i>What's Coming:</h5>
                                    <ul class="text-start mb-0">
                                        <li>Personalized emotional support conversations</li>
                                        <li>Guided meditation and breathing exercises</li>
                                        <li>Cognitive behavioral therapy techniques</li>
                                        <li>Mood tracking and insights</li>
                                        <li>24/7 emotional support availability</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-center mt-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5>In the meantime...</h5>
                                        <p class="mb-3">Try our other mood enhancement options:</p>
                                        <div class="d-grid gap-2">
                                            <a href="<?php echo APP_URL; ?>/pages/recommendations.php?source=<?php echo $sourceEmotion; ?>&target=<?php echo $targetEmotion; ?>&emotion_id=<?php echo $emotionId; ?>&type=african_meals" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-utensils me-2"></i> Try African Meals
                                            </a>
                                            <a href="<?php echo APP_URL; ?>/pages/movie_genre_selection.php?source=<?php echo $sourceEmotion; ?>&target=<?php echo $targetEmotion; ?>&emotion_id=<?php echo $emotionId; ?>"
                                            class="btn btn-danger btn-sm">
                                            <i class="fas fa-film me-2"></i> Watch Movies
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="text-center mt-5">
                        <a href="<?php echo APP_URL; ?>/pages/mood_options.php?source=<?php echo $sourceEmotion; ?>&target=<?php echo $targetEmotion; ?>&emotion_id=<?php echo $emotionId; ?>" 
                           class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-2"></i> Back to Options
                        </a>
                        <a href="<?php echo APP_URL; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-redo me-2"></i> New Mood Check
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.mood-transition-info {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.mood-transition-info .badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}

.alert ul {
    margin-bottom: 0;
}

.alert li {
    margin-bottom: 0.5rem;
}
</style>

<?php include '../includes/footer.php'; ?>
