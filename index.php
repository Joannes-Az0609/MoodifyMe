<?php
/**
 * MoodifyMe - Comprehensive Emotion-Based Recommendation System
 * Main entry point for the application
 */

// Include configuration and functions
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db_connect.php';

// Start session
session_start();

// Check if user is logged in
$loggedIn = isset($_SESSION['user_id']);

// Include header
include 'includes/header.php';

// Main content
?>

<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 animate__animated animate__fadeInLeft">
                <h1>Transform Your <span class="text-warning">Mood</span> With MoodifyMe</h1>
                <p class="lead">Your AI-powered companion for emotional well-being through personalized recommendations.</p>

                <?php if (!$loggedIn): ?>
                    <div class="welcome-section">
                        <p class="mb-4">Discover music, movies, and conversations tailored to your mood and help you achieve your desired emotional state.</p>
                        <div class="cta-buttons d-flex gap-3">
                            <a href="<?php echo APP_URL; ?>/pages/login.php" class="btn btn-light btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i> Login
                            </a>
                            <a href="<?php echo APP_URL; ?>/pages/register.php" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-user-plus me-2"></i> Register
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-6 animate__animated animate__fadeInRight">
                <img src="assets/images/hero-illustration.svg" alt="MoodifyMe Illustration" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<?php if ($loggedIn): ?>
<div class="mood-detection-container py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 animate__animated animate__fadeInUp">
                    <div class="card-body p-5 mood-detection-section">
                        <h2 class="card-title text-center mb-4">How are you feeling today?</h2>
                        <p class="text-center text-muted mb-4">Describe your current mood to get personalized recommendations</p>

                        <!-- Mood Input Options -->
                        <div class="mood-input-options d-flex justify-content-center gap-4 mb-5">
                            <div class="input-option active" id="text-input-option">
                                <div class="icon-circle">
                                    <i class="fas fa-keyboard"></i>
                                </div>
                                <span>Text</span>
                            </div>
                            <div class="input-option" id="voice-input-option">
                                <div class="icon-circle">
                                    <i class="fas fa-microphone"></i>
                                </div>
                                <span>Voice</span>
                            </div>
                            <div class="input-option" id="face-input-option">
                                <div class="icon-circle">
                                    <i class="fas fa-face-smile"></i>
                                </div>
                                <span>Face Detection</span>
                            </div>
                        </div>

                        <!-- Text Input Form (default) -->
                        <div class="mood-input-form" id="text-input-form">
                            <form id="mood-text-form">
                                <input type="hidden" name="input_type" value="text">
                                <div class="form-group mb-4">
                                    <textarea class="form-control form-control-lg" name="mood_text" id="mood-text" rows="4"
                                        placeholder="Describe how you're feeling right now..."></textarea>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-brain me-2"></i> Analyze My Mood
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Voice Input Form (hidden by default) -->
                        <div class="mood-input-form" id="voice-input-form" style="display: none;">
                            <div class="text-center">
                                <div class="voice-detection-info mb-4">
                                    <div class="icon-circle mx-auto mb-3" style="width: 80px; height: 80px;">
                                        <i class="fas fa-microphone fa-2x"></i>
                                    </div>
                                    <h4>Voice Emotion Detection</h4>
                                    <p class="text-muted">Speak naturally and let our AI analyze your emotions from your voice</p>
                                </div>
                                <div class="d-grid">
                                    <a href="<?php echo APP_URL; ?>/pages/voice_input.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-microphone me-2"></i> Start Voice Detection
                                    </a>
                                </div>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-shield-alt me-1"></i>
                                        Your privacy is protected - voice processing happens securely
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Face Detection Form (hidden by default) -->
                        <div class="mood-input-form" id="face-input-form" style="display: none;">
                            <div class="text-center">
                                <div class="face-detection-info mb-4">
                                    <div class="icon-circle mx-auto mb-3" style="width: 80px; height: 80px;">
                                        <i class="fas fa-camera fa-2x"></i>
                                    </div>
                                    <h4>Advanced Facial Emotion Detection</h4>
                                    <p class="text-muted">Use your camera to detect emotions</p>
                                </div>
                                <div class="d-grid">
                                    <a href="<?php echo APP_URL; ?>/pages/landmark-emotion.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-video me-2"></i> Start Face Detection
                                    </a>
                                </div>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-shield-alt me-1"></i>
                                        Your privacy is protected - all processing happens locally in your browser
                                    </small>
                                </div>
                            </div>
                        </div>




                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Features Section -->
<div class="features-section py-5">
    <div class="container">
        <h2 class="text-center mb-5">How MoodifyMe Works</h2>
        <div class="row g-4">
            <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-brain fa-3x text-primary"></i>
                        </div>
                        <h3 class="card-title h5">Emotion Detection</h3>
                        <p class="card-text">Express your emotions through text and let our AI analyze your current mood.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-lightbulb fa-3x text-primary"></i>
                        </div>
                        <h3 class="card-title h5">Personalized Recommendations</h3>
                        <p class="card-text">Receive tailored suggestions for music, movies, and conversations to help you achieve your desired emotional state.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-chart-line fa-3x text-primary"></i>
                        </div>
                        <h3 class="card-title h5">Emotional Journey Tracking</h3>
                        <p class="card-text">Monitor your mood patterns over time and gain insights into your emotional well-being.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Testimonials Section -->
<div class="testimonials-section py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">What Our Users Say</h2>
        <div class="row">
            <div class="col-lg-4 mb-4 animate__animated animate__fadeIn" style="animation-delay: 0.2s">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar me-3">
                                <img src="assets/images/avatar-1.jpg" alt="User Avatar" class="rounded-circle" width="50" height="50">
                            </div>
                            <div>
                                <h5 class="mb-0">Amina Okafor</h5>
                                <div class="text-warning">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <p class="card-text">"MoodifyMe has been a game-changer for my mental health. The movie recommendations always match perfectly with how I want to feel!"</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4 animate__animated animate__fadeIn" style="animation-delay: 0.3s">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar me-3">
                                <img src="assets/images/avatar-2.jpg" alt="User Avatar" class="rounded-circle" width="50" height="50">
                            </div>
                            <div>
                                <h5 class="mb-0">Kwame Mensah</h5>
                                <div class="text-warning">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                            </div>
                        </div>
                        <p class="card-text">"I love how the app detects my mood from my text input. The music playlists are always spot on and help me relax after a stressful day."</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4 animate__animated animate__fadeIn" style="animation-delay: 0.4s">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar me-3">
                                <img src="assets/images/avatar-3.jpg" alt="User Avatar" class="rounded-circle" width="50" height="50">
                            </div>
                            <div>
                                <h5 class="mb-0">Zanele Dlamini</h5>
                                <div class="text-warning">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <p class="card-text">"The AI jokes always make me laugh when I'm feeling down. MoodifyMe has become an essential part of my self-care routine!"</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="cta-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h2 class="mb-3">Ready to improve your emotional well-being?</h2>
                <p class="lead mb-0">Join MoodifyMe today and discover personalized recommendations tailored to your mood.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?php echo APP_URL; ?>/pages/register.php" class="btn btn-light btn-lg">Get Started Now</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// Include footer
include 'includes/footer.php';
?>

<style>
/* Hero Section */
.hero-section {
    padding: 7rem 0;
    background: linear-gradient(135deg, rgba(229, 81, 0, 0.9) 0%, rgba(211, 47, 47, 0.9) 50%, rgba(255, 143, 0, 0.9) 100%);
    position: relative;
    overflow: hidden;
    color: white !important;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
    height: 100%;
    background-image: url('assets/images/pattern.svg');
    background-size: cover;
    opacity: 0.1;
    z-index: 0;
}

.hero-section .container {
    position: relative;
    z-index: 2;
}

.hero-section h1 {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    color: black !important;
    text-shadow: 0 2px 10px rgba(255, 255, 255, 0.3);
}

.hero-section .lead {
    font-size: 1.5rem;
    margin-bottom: 2rem;
    color: black !important;
    opacity: 0.95;
    text-shadow: 0 1px 5px rgba(255, 255, 255, 0.2);
}

.hero-section p {
    font-size: 1.1rem;
    color: black !important;
    opacity: 0.9;
    text-shadow: 0 1px 3px rgba(255, 255, 255, 0.2);
}

.hero-section .text-warning {
    color: #D32F2F !important;
    text-shadow: 0 2px 10px rgba(255, 255, 255, 0.3);
}

.hero-section .btn-light,
.hero-section .btn-outline-light {
    background: white !important;
    color: #E55100 !important;
    font-weight: 600;
    border: 2px solid white;
    transition: all 0.3s ease;
    text-decoration: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.hero-section .btn-light:hover,
.hero-section .btn-outline-light:hover {
    background: #FFD54F !important;
    color: #E55100 !important;
    border-color: #FFD54F;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}



/* Features Section */
.feature-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background-color: rgba(229, 81, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

/* Mood Input Options */
.mood-input-options {
    display: flex;
    justify-content: center;
    gap: 2rem;
}

.input-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    transition: all 0.3s ease;
    padding: 1rem;
    border-radius: var(--radius-lg);
}

.input-option:hover {
    transform: translateY(-5px);
}

.input-option.active {
    background-color: rgba(229, 81, 0, 0.1);
}

.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}

.input-option.active .icon-circle {
    background-color: #E55100;
    color: white;
}

.input-option i {
    font-size: 1.5rem;
}

.input-option span {
    font-weight: 500;
    margin-top: 0.5rem;
}



/* Video Container */
.video-container {
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow);
}

/* Testimonials */
.testimonials-section {
    background-color: var(--neutral-100);
}

.avatar img {
    object-fit: cover;
    border: 2px solid #E55100;
}

/* CTA Section */
.cta-section {
    background: linear-gradient(135deg, #E55100 0%, #D32F2F 100%);
}

/* Emotion Results */
.emotion-results {
    animation: fadeIn 0.5s ease-in-out;
}

.emotion-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    background-color: #f8f9fa;
    margin-bottom: 1rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.emotion-badge i {
    font-size: 1.5rem;
    margin-right: 0.5rem;
}

.emotion-badge.happy, .emotion-badge.excited {
    background-color: #fff3cd;
    color: #856404;
}

.emotion-badge.sad, .emotion-badge.anxious {
    background-color: #d1ecf1;
    color: #0c5460;
}

.emotion-badge.angry, .emotion-badge.stressed {
    background-color: #f8d7da;
    color: #721c24;
}

.emotion-badge.calm, .emotion-badge.relaxed {
    background-color: #d4edda;
    color: #155724;
}

.emotion-badge.neutral, .emotion-badge.bored {
    background-color: #e2e3e5;
    color: #383d41;
}

.emotion-badge.energetic, .emotion-badge.focused, .emotion-badge.inspired {
    background-color: #cce5ff;
    color: #004085;
}

.emotion-container {
    transition: all 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Loading Overlay */
#loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    color: white;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 5px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
    margin-bottom: 20px;
}

.loading-message {
    font-size: 1.2rem;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>

<script>
// Handle switching between input methods
document.addEventListener('DOMContentLoaded', function() {
    const textOption = document.getElementById('text-input-option');
    const voiceOption = document.getElementById('voice-input-option');
    const faceOption = document.getElementById('face-input-option');
    const textForm = document.getElementById('text-input-form');
    const voiceForm = document.getElementById('voice-input-form');
    const faceForm = document.getElementById('face-input-form');

    // Function to reset all options
    function resetAllOptions() {
        textOption.classList.remove('active');
        voiceOption.classList.remove('active');
        faceOption.classList.remove('active');
        textForm.style.display = 'none';
        voiceForm.style.display = 'none';
        faceForm.style.display = 'none';
    }

    // Switch to text input
    if (textOption) {
        textOption.addEventListener('click', function() {
            resetAllOptions();
            textOption.classList.add('active');
            textForm.style.display = 'block';
        });
    }

    // Switch to voice input
    if (voiceOption) {
        voiceOption.addEventListener('click', function() {
            resetAllOptions();
            voiceOption.classList.add('active');
            voiceForm.style.display = 'block';
        });
    }

    // Switch to face detection
    if (faceOption) {
        faceOption.addEventListener('click', function() {
            resetAllOptions();
            faceOption.classList.add('active');
            faceForm.style.display = 'block';
        });
    }
});
</script>
