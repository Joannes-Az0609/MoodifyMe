<?php
/**
 * MoodifyMe - Movie Genre Selection Page
 * Allow users to choose their preferred movie genre before showing recommendations
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

// Define all available movie genres with TMDB genre IDs
$allGenres = [
    'action' => ['id' => 28, 'name' => 'Action', 'icon' => 'fist-raised', 'color' => 'danger'],
    'adventure' => ['id' => 12, 'name' => 'Adventure', 'icon' => 'map', 'color' => 'warning'],
    'animation' => ['id' => 16, 'name' => 'Animation', 'icon' => 'palette', 'color' => 'info'],
    'comedy' => ['id' => 35, 'name' => 'Comedy', 'icon' => 'laugh', 'color' => 'success'],
    'crime' => ['id' => 80, 'name' => 'Crime', 'icon' => 'user-secret', 'color' => 'dark'],
    'documentary' => ['id' => 99, 'name' => 'Documentary', 'icon' => 'video', 'color' => 'secondary'],
    'drama' => ['id' => 18, 'name' => 'Drama', 'icon' => 'theater-masks', 'color' => 'primary'],
    'family' => ['id' => 10751, 'name' => 'Family', 'icon' => 'home', 'color' => 'success'],
    'fantasy' => ['id' => 14, 'name' => 'Fantasy', 'icon' => 'magic', 'color' => 'info'],
    'history' => ['id' => 36, 'name' => 'History', 'icon' => 'landmark', 'color' => 'warning'],
    'horror' => ['id' => 27, 'name' => 'Horror', 'icon' => 'ghost', 'color' => 'danger'],
    'music' => ['id' => 10402, 'name' => 'Music', 'icon' => 'music', 'color' => 'primary'],
    'mystery' => ['id' => 9648, 'name' => 'Mystery', 'icon' => 'search', 'color' => 'dark'],
    'romance' => ['id' => 10749, 'name' => 'Romance', 'icon' => 'heart', 'color' => 'danger'],
    'scifi' => ['id' => 878, 'name' => 'Sci-Fi', 'icon' => 'rocket', 'color' => 'info'],
    'thriller' => ['id' => 53, 'name' => 'Thriller', 'icon' => 'exclamation-triangle', 'color' => 'warning'],
    'war' => ['id' => 10752, 'name' => 'War', 'icon' => 'shield-alt', 'color' => 'secondary'],
    'western' => ['id' => 37, 'name' => 'Western', 'icon' => 'hat-cowboy', 'color' => 'warning']
];

// Define mood-based genre recommendations
$moodGenreMap = [
    // From SAD
    'sad_to_happy' => ['comedy', 'animation', 'family', 'music', 'romance'],
    'sad_to_calm' => ['documentary', 'drama', 'family', 'music'],
    'sad_to_energetic' => ['action', 'adventure', 'comedy', 'music'],
    'sad_to_focused' => ['documentary', 'drama', 'thriller', 'mystery'],
    'sad_to_relaxed' => ['comedy', 'family', 'animation', 'romance'],
    'sad_to_confident' => ['action', 'adventure', 'drama', 'thriller'],
    'sad_to_inspired' => ['drama', 'documentary', 'family', 'music'],
    'sad_to_peaceful' => ['documentary', 'family', 'animation', 'music'],

    // From ANGRY
    'angry_to_calm' => ['documentary', 'family', 'animation', 'music'],
    'angry_to_happy' => ['comedy', 'animation', 'family', 'music'],
    'angry_to_relaxed' => ['comedy', 'family', 'animation', 'romance'],
    'angry_to_peaceful' => ['documentary', 'family', 'animation', 'music'],
    'angry_to_focused' => ['documentary', 'drama', 'thriller', 'mystery'],
    'angry_to_confident' => ['action', 'adventure', 'drama', 'thriller'],

    // From ANXIOUS
    'anxious_to_calm' => ['documentary', 'family', 'animation', 'music'],
    'anxious_to_confident' => ['action', 'adventure', 'drama', 'comedy'],
    'anxious_to_relaxed' => ['comedy', 'family', 'animation', 'romance'],
    'anxious_to_peaceful' => ['documentary', 'family', 'animation', 'music'],
    'anxious_to_happy' => ['comedy', 'animation', 'family', 'music'],
    'anxious_to_focused' => ['documentary', 'drama', 'thriller'],

    // From BORED
    'bored_to_excited' => ['action', 'adventure', 'thriller', 'comedy'],
    'bored_to_energetic' => ['action', 'adventure', 'comedy', 'music'],
    'bored_to_inspired' => ['drama', 'documentary', 'fantasy', 'scifi'],
    'bored_to_happy' => ['comedy', 'animation', 'family', 'music'],
    'bored_to_focused' => ['thriller', 'mystery', 'drama', 'documentary'],
    'bored_to_motivated' => ['action', 'adventure', 'drama', 'thriller'],

    // From STRESSED
    'stressed_to_relaxed' => ['comedy', 'family', 'animation', 'romance'],
    'stressed_to_calm' => ['documentary', 'family', 'animation', 'music'],
    'stressed_to_peaceful' => ['documentary', 'family', 'animation', 'music'],
    'stressed_to_happy' => ['comedy', 'animation', 'family', 'music'],
    'stressed_to_confident' => ['action', 'adventure', 'drama', 'comedy'],

    // From TIRED
    'tired_to_energetic' => ['action', 'adventure', 'comedy', 'music'],
    'tired_to_motivated' => ['action', 'adventure', 'drama', 'thriller'],
    'tired_to_excited' => ['action', 'adventure', 'thriller', 'comedy'],
    'tired_to_happy' => ['comedy', 'animation', 'family', 'music'],
    'tired_to_focused' => ['thriller', 'mystery', 'drama', 'documentary'],

    // Default fallback
    'default' => ['comedy', 'drama', 'action', 'documentary', 'family']
];

// Get recommended genres for this mood transition
$transitionKey = strtolower($sourceEmotion) . '_to_' . strtolower($targetEmotion);
$recommendedGenreKeys = $moodGenreMap[$transitionKey] ?? $moodGenreMap['default'];

// Build the movie genres array based on recommendations
$movieGenres = [];
foreach ($recommendedGenreKeys as $genreKey) {
    if (isset($allGenres[$genreKey])) {
        $movieGenres[] = $allGenres[$genreKey];
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="page-title">
                            <i class="fas fa-film me-3"></i>
                            Choose Your Movie Genre
                        </h2>
                        <div class="title-underline"></div>
                    </div>
                    
                    <div class="mood-transition-info text-center mb-4">
                        <p class="lead">
                            These movie genres are specially recommended to help you transition from
                            <span class="badge bg-primary"><?php echo ucfirst($sourceEmotion); ?></span>
                            to
                            <span class="badge bg-success"><?php echo ucfirst($targetEmotion); ?></span>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-lightbulb me-2"></i>
                            Each genre is carefully selected based on psychological research about mood enhancement through entertainment.
                        </p>
                    </div>

                    <?php if (!empty($movieGenres)): ?>
                        <div class="row g-4">
                            <?php foreach ($movieGenres as $genre): ?>
                                <div class="col-md-3 col-sm-4 col-6">
                                    <div class="genre-card" data-genre-id="<?php echo $genre['id']; ?>" data-genre-name="<?php echo $genre['name']; ?>">
                                        <div class="genre-icon">
                                            <i class="fas fa-<?php echo $genre['icon']; ?> fa-3x text-<?php echo $genre['color']; ?>"></i>
                                        </div>
                                        <h5 class="genre-name"><?php echo $genre['name']; ?></h5>
                                        <div class="genre-overlay">
                                            <i class="fas fa-play-circle fa-2x"></i>
                                            <p>View Movies</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <h5>No specific genre recommendations found</h5>
                            <p>We don't have specific genre recommendations for this mood transition yet.
                               You can still explore all movie genres by going back to mood options.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Navigation -->
                    <div class="text-center mt-5">
                        <a href="<?php echo APP_URL; ?>/pages/mood_options.php?source=<?php echo urlencode($sourceEmotion); ?>&target=<?php echo urlencode($targetEmotion); ?>&emotion_id=<?php echo urlencode($emotionId); ?>"
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
/* Genre Card Styling */
.genre-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 30px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
    height: 180px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.genre-card:hover {
    transform: translateY(-10px) scale(1.05);
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    border-color: #667eea;
}

.genre-icon {
    margin-bottom: 15px;
    transition: transform 0.3s ease;
}

.genre-card:hover .genre-icon {
    transform: scale(1.2);
}

.genre-name {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0;
    font-size: 1.1rem;
}

/* Genre Overlay */
.genre-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9));
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    color: white;
}

.genre-card:hover .genre-overlay {
    opacity: 1;
}

.genre-overlay p {
    margin: 10px 0 0 0;
    font-weight: 600;
    font-size: 1rem;
}

/* Page Title Styling */
.page-title {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 800;
    font-size: 2.5rem;
    margin-bottom: 10px;
}

.title-underline {
    width: 80px;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    margin: 0 auto;
    border-radius: 2px;
}

/* Mood Transition Info */
.mood-transition-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.mood-transition-info .badge {
    font-size: 0.9rem;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    margin: 0 5px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .genre-card {
        height: 150px;
        padding: 20px 15px;
    }
    
    .genre-icon i {
        font-size: 2rem !important;
    }
    
    .genre-name {
        font-size: 1rem;
    }
    
    .page-title {
        font-size: 2rem;
    }
}

@media (max-width: 576px) {
    .genre-card {
        height: 130px;
        padding: 15px 10px;
    }
    
    .genre-icon i {
        font-size: 1.5rem !important;
    }
    
    .genre-name {
        font-size: 0.9rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const genreCards = document.querySelectorAll('.genre-card');
    
    genreCards.forEach(card => {
        card.addEventListener('click', function() {
            const genreId = this.getAttribute('data-genre-id');
            const genreName = this.getAttribute('data-genre-name');
            
            // Add visual feedback
            this.style.transform = 'scale(0.95)';
            this.style.opacity = '0.7';
            
            // Build URL for movie recommendations with genre
            const params = new URLSearchParams({
                source: '<?php echo urlencode($sourceEmotion); ?>',
                target: '<?php echo urlencode($targetEmotion); ?>',
                emotion_id: '<?php echo urlencode($emotionId); ?>',
                genre_id: genreId,
                genre_name: genreName
            });

            // Redirect to movie recommendations with genre filter
            setTimeout(() => {
                window.location.href = `<?php echo APP_URL; ?>/pages/movie_genres.php?${params.toString()}`;
            }, 200);
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
