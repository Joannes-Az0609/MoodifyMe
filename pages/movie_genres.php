<?php
/**
 * MoodifyMe - Movie Genres Page
 * Display movie recommendations based on mood transition
 */

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';
require_once '../includes/tmdb_api.php';

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
$genreId = isset($_GET['genre_id']) ? intval($_GET['genre_id']) : null;
$genreName = isset($_GET['genre_name']) ? sanitizeInput($_GET['genre_name']) : '';

// If source or target emotion is not provided, redirect to home page
if (empty($sourceEmotion) || empty($targetEmotion)) {
    redirect(APP_URL);
}

// Get movie recommendations based on genre if specified
if ($genreId) {
    $movies = getTMDBMoviesByGenre($genreId, 12);
    // Add emotion data to each movie
    foreach ($movies as &$movie) {
        $movie['source_emotion'] = $sourceEmotion;
        $movie['target_emotion'] = $targetEmotion;
    }
} else {
    $movies = getTMDBMovieRecommendations($sourceEmotion, $targetEmotion, 12);
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
                            <?php echo $genreName ? $genreName . ' Movies' : 'Movie Recommendations'; ?>
                        </h2>
                        <div class="title-underline"></div>
                    </div>
                    
                    <div class="mood-transition-info text-center mb-4">
                        <p class="lead">
                            Movies to help you transition from
                            <span class="badge bg-primary"><?php echo ucfirst($sourceEmotion); ?></span>
                            to
                            <span class="badge bg-success"><?php echo ucfirst($targetEmotion); ?></span>
                        </p>
                    </div>

                    <?php if (!empty($movies)): ?>
                        <div class="row g-4">
                            <?php foreach ($movies as $movie): ?>
                                <div class="col-md-6 col-lg-4 col-xl-3">
                                    <div class="card h-100 movie-card">
                                        <div class="movie-poster-container">
                                            <img src="<?php echo htmlspecialchars($movie['image_url']); ?>"
                                                 class="movie-poster"
                                                 alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                                 loading="lazy">
                                            <div class="movie-overlay">
                                                <div class="overlay-content">
                                                    <i class="fas fa-play-circle fa-3x text-white mb-2"></i>
                                                    <p class="text-white fw-bold">View Details</p>
                                                </div>
                                            </div>
                                            <div class="movie-rating">
                                                <?php
                                                // Extract rating from content
                                                preg_match('/Rating: ([\d.]+)/', $movie['content'], $matches);
                                                $rating = isset($matches[1]) ? floatval($matches[1]) : 0;
                                                ?>
                                                <span class="rating-badge">
                                                    <i class="fas fa-star"></i> <?php echo number_format($rating, 1); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex flex-column p-3">
                                            <h5 class="movie-title mb-2"><?php echo htmlspecialchars($movie['title']); ?></h5>
                                            <p class="movie-description flex-grow-1">
                                                <?php echo htmlspecialchars(substr($movie['description'], 0, 120)) . '...'; ?>
                                            </p>
                                            <div class="movie-meta mb-3">
                                                <?php
                                                // Extract year from content
                                                preg_match('/Released: (\d{4})/', $movie['content'], $yearMatches);
                                                $year = isset($yearMatches[1]) ? $yearMatches[1] : 'N/A';
                                                ?>
                                                <span class="year-badge">
                                                    <i class="fas fa-calendar-alt me-1"></i><?php echo $year; ?>
                                                </span>
                                            </div>
                                            <a href="<?php echo htmlspecialchars($movie['link']); ?>"
                                               target="_blank"
                                               class="btn btn-movie-action mt-auto">
                                                <i class="fas fa-external-link-alt me-2"></i>
                                                <span>Watch Now</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-film fa-3x text-muted mb-3"></i>
                            <h4>No Movies Found</h4>
                            <p class="text-muted">We couldn't find movie recommendations for this mood transition.</p>
                            <a href="<?php echo APP_URL; ?>/pages/mood_options.php?source=<?php echo $sourceEmotion; ?>&target=<?php echo $targetEmotion; ?>&emotion_id=<?php echo $emotionId; ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i> Back to Options
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Navigation -->
                    <div class="text-center mt-5">
                        <?php if ($genreId): ?>
                            <a href="<?php echo APP_URL; ?>/pages/movie_genre_selection.php?source=<?php echo $sourceEmotion; ?>&target=<?php echo $targetEmotion; ?>&emotion_id=<?php echo $emotionId; ?>"
                               class="btn btn-outline-info me-2">
                                <i class="fas fa-list me-2"></i> Choose Different Genre
                            </a>
                        <?php endif; ?>
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
/* Movie Card Styling */
.movie-card {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid rgba(0,0,0,0.05);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    background: #ffffff !important;
    position: relative;
    margin-bottom: 2rem;
}

.movie-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

/* Movie Poster Container */
.movie-poster-container {
    position: relative;
    overflow: hidden;
    height: 350px;
    background: #f8f9fa;
}

.movie-poster {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.movie-card:hover .movie-poster {
    transform: scale(1.1);
}

/* Movie Overlay */
.movie-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(0,0,0,0.7), rgba(0,0,0,0.4));
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.movie-card:hover .movie-overlay {
    opacity: 1;
}

.overlay-content {
    text-align: center;
    transform: translateY(20px);
    transition: transform 0.3s ease;
}

.movie-card:hover .overlay-content {
    transform: translateY(0);
}

/* Rating Badge */
.movie-rating {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 2;
}

.rating-badge {
    background: #ff6b6b; /* Fallback */
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    color: white !important;
    padding: 8px 12px;
    border-radius: 25px;
    font-size: 0.85rem;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
    backdrop-filter: blur(10px);
    border: none;
}

/* Movie Title */
.movie-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1.3;
    margin-bottom: 8px;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Movie Description */
.movie-description {
    font-size: 0.9rem;
    color: #6c757d;
    line-height: 1.5;
    margin-bottom: 15px;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Movie Meta */
.movie-meta {
    margin-bottom: 15px;
}

.year-badge {
    background: #74b9ff; /* Fallback */
    background: linear-gradient(135deg, #74b9ff, #0984e3);
    color: white !important;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-block;
    border: none;
}

/* Action Button */
.btn-movie-action {
    background: linear-gradient(135deg, #6c5ce7, #a29bfe) !important;
    border: none !important;
    color: white !important;
    padding: 12px 20px;
    border-radius: 25px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    text-decoration: none;
}

.btn-movie-action:hover {
    background: linear-gradient(135deg, #5f3dc4, #9775fa) !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(108, 92, 231, 0.3);
    color: white !important;
    text-decoration: none;
}

.btn-movie-action:before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-movie-action:hover:before {
    left: 100%;
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

.mood-transition-info .bg-primary {
    background: linear-gradient(135deg, #ff6b6b, #ee5a24) !important;
}

.mood-transition-info .bg-success {
    background: linear-gradient(135deg, #00b894, #00a085) !important;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .movie-poster-container {
        height: 320px;
    }
}

@media (max-width: 768px) {
    .movie-poster-container {
        height: 280px;
    }

    .movie-title {
        font-size: 1rem;
    }

    .movie-description {
        font-size: 0.85rem;
    }

    .col-md-6 {
        margin-bottom: 1.5rem;
    }
}

@media (max-width: 576px) {
    .movie-poster-container {
        height: 250px;
    }

    .movie-card {
        margin-bottom: 20px;
    }
}

/* Loading Animation */
.movie-poster[loading="lazy"] {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

/* Hover Effects for Icons */
.movie-overlay i {
    transition: transform 0.3s ease;
}

.movie-card:hover .movie-overlay i {
    transform: scale(1.2);
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

/* Card Entrance Animation */
.movie-card {
    animation: fadeInUp 0.6s ease forwards;
}

.movie-card:nth-child(1) { animation-delay: 0.1s; }
.movie-card:nth-child(2) { animation-delay: 0.2s; }
.movie-card:nth-child(3) { animation-delay: 0.3s; }
.movie-card:nth-child(4) { animation-delay: 0.4s; }
.movie-card:nth-child(5) { animation-delay: 0.5s; }
.movie-card:nth-child(6) { animation-delay: 0.6s; }

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<?php include '../includes/footer.php'; ?>
