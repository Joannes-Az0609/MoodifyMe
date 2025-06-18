<?php
/**
 * MoodifyMe - Spotify Music Page
 * Display music recommendations based on mood transition
 */

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';
require_once '../includes/spotify_api.php';

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

// Get Spotify music recommendations
$tracks = getSpotifyMusicRecommendations($sourceEmotion, $targetEmotion, 12);

// Include header
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">🎵 Music Recommendations</h2>

                    <div class="mood-transition-info text-center mb-4">
                        <p class="lead">
                            Music to help you transition from
                            <span class="badge bg-primary"><?php echo ucfirst($sourceEmotion); ?></span>
                            to
                            <span class="badge bg-success"><?php echo ucfirst($targetEmotion); ?></span>
                        </p>
                    </div>

                    <?php if (!empty($tracks)): ?>
                        <div class="row g-4">
                            <?php foreach ($tracks as $track): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100 track-card animate__animated animate__fadeInUp">
                                        <div class="track-image position-relative">
                                            <?php if (!empty($track['image_url'])): ?>
                                                <img src="<?php echo htmlspecialchars($track['image_url']); ?>"
                                                     class="card-img-top track-img-art shadow-lg"
                                                     alt="<?php echo htmlspecialchars($track['title']); ?>">
                                                <?php if (!empty($track['preview_url'])): ?>
                                                    <div class="play-btn-overlay">
                                                        <i class="fas fa-play-circle"></i>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div class="placeholder-image d-flex align-items-center justify-content-center"
                                                     style="height: 200px; background: linear-gradient(135deg, #E55100 0%, #FFD54F 100%);">
                                                    <i class="fas fa-music fa-3x text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="track-overlay position-absolute w-100 h-100 top-0 start-0 d-flex flex-column justify-content-end p-3">
                                                <h5 class="track-title-glass mb-1"><?php echo htmlspecialchars($track['title']); ?></h5>
                                                <p class="track-artist-glass mb-0"><i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($track['description']); ?></p>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex flex-column track-card-glass rounded-bottom-3" style="min-height: 160px;">
                                            <div class="track-info mb-2 flex-grow-1">
                                                <small class="text-muted"><?php echo htmlspecialchars($track['content']); ?></small>
                                            </div>
                                            <div class="track-actions mt-auto">
                                                <?php if (!empty($track['preview_url'])): ?>
                                                    <audio controls class="w-100 mb-2">
                                                        <source src="<?php echo htmlspecialchars($track['preview_url']); ?>" type="audio/mpeg">
                                                        Your browser does not support the audio element.
                                                    </audio>
                                                <?php endif; ?>
                                                <a href="<?php echo htmlspecialchars($track['link']); ?>"
                                                   target="_blank"
                                                   class="btn btn-spotify-glass btn-success btn-sm w-100">
                                                    <i class="fab fa-spotify me-1"></i> Listen on Spotify
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fab fa-spotify fa-3x text-muted mb-3"></i>
                            <h4>No Music Found</h4>
                            <p class="text-muted">We couldn't find music recommendations for this mood transition.</p>
                            <a href="<?php echo APP_URL; ?>/pages/mood_options.php?source=<?php echo urlencode($sourceEmotion); ?>&target=<?php echo urlencode($targetEmotion); ?>&emotion_id=<?php echo urlencode($emotionId); ?>"
                               class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i> Back to Options
                            </a>
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
.track-card {
    transition: transform 0.3s cubic-bezier(.4,2,.6,1), box-shadow 0.3s cubic-bezier(.4,2,.6,1);
    border-radius: 18px;
    overflow: hidden;
    border: none;
    box-shadow: 0 8px 32px rgba(229,81,0,0.13), 0 1.5px 8px rgba(0,0,0,0.07);
    background: rgba(255,255,255,0.75);
    position: relative;
    backdrop-filter: blur(4px);
}
.track-card:hover {
    transform: translateY(-8px) scale(1.04);
    box-shadow: 0 24px 48px rgba(229,81,0,0.22), 0 2px 12px rgba(0,0,0,0.10);
    z-index: 2;
}
.track-image {
    position: relative;
    overflow: hidden;
    border-top-left-radius: 18px;
    border-top-right-radius: 18px;
    min-height: 200px;
}
.track-img-art {
    border-radius: 16px 16px 0 0;
    box-shadow: 0 2px 12px rgba(229,81,0,0.08);
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s cubic-bezier(.4,2,.6,1), box-shadow 0.3s cubic-bezier(.4,2,.6,1);
}
.track-card:hover .track-img-art {
    transform: scale(1.06);
    box-shadow: 0 8px 32px rgba(229,81,0,0.18);
}
.play-btn-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #fff;
    font-size: 3rem;
    opacity: 0.85;
    pointer-events: none;
    text-shadow: 0 2px 12px rgba(0,0,0,0.25);
}
.track-overlay {
    pointer-events: none;
    border-top-left-radius: 18px;
    border-top-right-radius: 18px;
    background: linear-gradient(0deg, rgba(0,0,0,0.78) 60%, rgba(255,255,255,0.01) 100%);
}
.track-title-glass {
    color: #fff;
    font-family: 'Merriweather', serif;
    font-size: 1.18rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    text-shadow: 0 2px 8px rgba(0,0,0,0.18);
}
.track-artist-glass {
    color: #FFD54F;
    font-size: 1rem;
    font-weight: 500;
    text-shadow: 0 1px 6px rgba(0,0,0,0.13);
}
.track-card-glass {
    background: rgba(255,255,255,0.82);
    box-shadow: 0 1.5px 8px rgba(229,81,0,0.07);
    backdrop-filter: blur(2.5px);
}
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
.track-actions audio {
    border-radius: 5px;
    background: #f8f9fa;
}
.placeholder-image {
    border-radius: 0;
}
.btn-spotify-glass {
    background: linear-gradient(90deg, #1db954 60%, #1ed760 100%) !important;
    color: #fff !important;
    font-weight: 600;
    border: none;
    box-shadow: 0 2px 8px rgba(30,185,84,0.13);
    transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
}
.btn-spotify-glass:hover, .btn-spotify-glass:focus {
    background: linear-gradient(90deg, #1ed760 0%, #1db954 100%) !important;
    color: #fff !important;
    box-shadow: 0 4px 16px rgba(30,185,84,0.22);
    transform: translateY(-2px) scale(1.03);
}
@media (max-width: 768px) {
    .col-md-6 {
        margin-bottom: 1rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
