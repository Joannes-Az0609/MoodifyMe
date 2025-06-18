<?php
/**
 * MoodifyMe - Recommendations Page (Modified to show Mood Options)
 *
 * This page displays options for how the user would like to attain their desired mood
 */

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';
require_once '../includes/tmdb_api.php'; // Include TMDB API functions
require_once '../includes/recommendation_functions.php'; // Include recommendation functions

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
$specificType = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';

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

// Check if a specific type is requested
if (!empty($specificType) && array_key_exists($specificType, REC_TYPES)) {
    // Show specific recommendations
    $recommendations = getRecommendationsByType($specificType, $sourceEmotion, $targetEmotion, 10);
    ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4"><?php echo REC_TYPES[$specificType]; ?> Recommendations</h2>

                        <div class="mood-transition-info text-center mb-4">
                            <p class="lead">
                                Here are <?php echo strtolower(REC_TYPES[$specificType]); ?> to help you transition from
                                <span class="badge bg-primary"><?php echo ucfirst($sourceEmotion); ?></span>
                                to
                                <span class="badge bg-success"><?php echo ucfirst($targetEmotion); ?></span>
                            </p>
                        </div>

                        <?php if (!empty($recommendations)): ?>
                            <div class="row">
                                <?php foreach ($recommendations as $recommendation): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card h-100 recommendation-card">
                                            <?php
                                            $hasValidImage = !empty($recommendation['image_url']) &&
                                                           !strpos($recommendation['image_url'], 'placeholder') &&
                                                           filter_var($recommendation['image_url'], FILTER_VALIDATE_URL);
                                            ?>

                                            <?php if ($hasValidImage): ?>
                                                <img src="<?php echo $recommendation['image_url']; ?>"
                                                     class="card-img-top"
                                                     alt="<?php echo htmlspecialchars($recommendation['title']); ?>"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="card-img-top no-image" style="display: none;"></div>
                                            <?php else: ?>
                                                <div class="card-img-top no-image"></div>
                                            <?php endif; ?>
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($recommendation['title']); ?></h5>
                                                <p class="card-text"><?php echo htmlspecialchars($recommendation['description']); ?></p>
                                                <?php if ($specificType === 'african_meals' && !empty($recommendation['content'])): ?>
                                                    <div class="recipe-preview">
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars(substr($recommendation['content'], 0, 150)); ?>...
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-footer">
                                                <?php if ($specificType === 'movies'): ?>
                                                    <a href="<?php echo APP_URL; ?>/pages/movie_genre_selection.php?source=<?php echo $sourceEmotion; ?>&target=<?php echo $targetEmotion; ?>&emotion_id=<?php echo $emotionId; ?>"
                                                       class="btn btn-danger btn-sm">
                                                        <i class="fas fa-film me-1"></i> Choose Movie Genre
                                                    </a>
                                                <?php elseif (!empty($recommendation['link'])): ?>
                                                    <a href="<?php echo $recommendation['link']; ?>"
                                                       class="btn btn-primary btn-sm"
                                                       target="_blank">
                                                        <i class="fas fa-external-link-alt me-1"></i>
                                                        <?php echo $specificType === 'african_meals' ? 'View Recipe' : 'View Details'; ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info text-center">
                                <h4>No <?php echo REC_TYPES[$specificType]; ?> Found</h4>
                                <p>We don't have any <?php echo strtolower(REC_TYPES[$specificType]); ?> recommendations for the transition from <strong><?php echo ucfirst($sourceEmotion); ?></strong> to <strong><?php echo ucfirst($targetEmotion); ?></strong> yet.</p>
                                <p>Try a different mood combination or explore other recommendation types!</p>

                                <div class="mt-4">
                                    <h5>Try These Alternatives:</h5>
                                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                                        <?php if ($specificType !== 'african_meals'): ?>
                                            <a href="<?php echo APP_URL; ?>/pages/recommendations.php?source=<?php echo urlencode($sourceEmotion); ?>&target=<?php echo urlencode($targetEmotion); ?>&emotion_id=<?php echo urlencode($emotionId); ?>&type=african_meals" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-utensils me-1"></i> African Meals
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($specificType !== 'music'): ?>
                                            <a href="<?php echo APP_URL; ?>/pages/spotify_music.php?source=<?php echo urlencode($sourceEmotion); ?>&target=<?php echo urlencode($targetEmotion); ?>&emotion_id=<?php echo urlencode($emotionId); ?>" class="btn btn-outline-success btn-sm">
                                                <i class="fas fa-music me-1"></i> Music
                                            </a>
                                        <?php endif; ?>

                                        <a href="<?php echo APP_URL; ?>/pages/movie_genre_selection.php?source=<?php echo urlencode($sourceEmotion); ?>&target=<?php echo urlencode($targetEmotion); ?>&emotion_id=<?php echo urlencode($emotionId); ?>" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-film me-1"></i> Movies
                                        </a>

                                        <a href="<?php echo APP_URL; ?>/pages/ai_chat_redirect.php?source=<?php echo urlencode($sourceEmotion); ?>&target=<?php echo urlencode($targetEmotion); ?>&emotion_id=<?php echo urlencode($emotionId); ?>" class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-robot me-1"></i> AI Support
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mt-4">
                            <a href="<?php echo APP_URL; ?>/pages/recommendations.php?source=<?php echo urlencode($sourceEmotion); ?>&target=<?php echo urlencode($targetEmotion); ?>&emotion_id=<?php echo urlencode($emotionId); ?>"
                               class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Back to Options
                            </a>
                            <a href="<?php echo APP_URL; ?>/pages/mood_options.php?source=<?php echo urlencode($sourceEmotion); ?>&target=<?php echo urlencode($targetEmotion); ?>&emotion_id=<?php echo urlencode($emotionId); ?>"
                               class="btn btn-outline-primary ms-2">
                                <i class="fas fa-list me-2"></i> View All Options
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
} else {
    // Show recommendation options
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
                                            <i class="fas fa-music me-2"></i> View Music
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Movies Option (Always show, regardless of recommendations) -->
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
                        <a href="<?php echo APP_URL; ?>/pages/mood_options.php?source=<?php echo urlencode($sourceEmotion); ?>&target=<?php echo urlencode($targetEmotion); ?>&emotion_id=<?php echo urlencode($emotionId); ?>" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i> Back to Options
                        </a>
                        <a href="<?php echo APP_URL; ?>" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-redo me-2"></i> New Mood Check
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
} // Close the else block
?>

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

.recommendation-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.recommendation-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    border-color: #007bff;
}

.recommendation-card .card-img-top {
    height: 200px;
    object-fit: cover;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
    position: relative;
}

.recommendation-card .card-img-top.no-image {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.recommendation-card .card-img-top.no-image::before {
    content: "🍽️";
    font-size: 4rem;
    opacity: 0.8;
}

.recommendation-card .card-body {
    flex: 1;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
}

.recommendation-card .card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.75rem;
    line-height: 1.3;
}

.recommendation-card .card-text {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 0.75rem;
    flex: 1;
}

.recommendation-card .recipe-preview {
    background: #f8f9fa;
    padding: 0.75rem;
    border-radius: 6px;
    margin-top: 0.5rem;
    border-left: 3px solid #007bff;
}

.recommendation-card .recipe-preview small {
    font-size: 0.8rem;
    line-height: 1.4;
}

.recommendation-card .card-footer {
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    padding: 0.75rem 1.25rem;
    display: flex;
    justify-content: center;
    align-items: center;
}

.recommendation-card .card-footer .btn {
    font-size: 0.85rem;
    padding: 0.375rem 0.75rem;
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

/* Prevent layout shift */
.row {
    margin-left: -0.75rem;
    margin-right: -0.75rem;
}

.row > * {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
}

/* Loading state for images */
.card-img-top {
    background-color: #f8f9fa;
    transition: opacity 0.3s ease;
}

.card-img-top[src=""] {
    display: none;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .recommendation-card .card-img-top {
        height: 150px;
    }

    .recommendation-card .card-title {
        font-size: 1rem;
    }

    .recommendation-card .card-footer {
        padding: 0.5rem 1rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
