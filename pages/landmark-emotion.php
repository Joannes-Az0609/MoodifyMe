<?php
/**
 * MoodifyMe - Landmark-Based Emotion Detection Page
 * Advanced facial emotion detection using MediaPipe landmarks
 */

require_once '../config.php';
require_once '../includes/auth_check.php';

$pageTitle = 'Landmark Emotion Detection';
$currentPage = 'landmark-emotion.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - MoodifyMe</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .landmark-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .video-container {
            position: relative;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #landmark-video {
            width: 100%;
            height: auto;
            max-height: 480px;
            object-fit: cover;
            display: block;
        }

        #landmark-canvas {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 10;
            border-radius: 15px;
        }

        .emotion-display {
            background: linear-gradient(135deg, #E55100 0%, #D32F2F 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(229, 81, 0, 0.3);
        }

        .emotion-badge {
            display: inline-block;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            margin: 10px;
            backdrop-filter: blur(10px);
        }

        .confidence-bar {
            background: rgba(255, 255, 255, 0.3);
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 10px;
        }

        .confidence-fill {
            background: #fff;
            height: 100%;
            transition: width 0.3s ease;
            border-radius: 4px;
        }

        .controls {
            text-align: center;
            margin: 20px 0;
        }

        .btn-landmark {
            background: linear-gradient(135deg, #E55100 0%, #D32F2F 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            margin: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(229, 81, 0, 0.3);
        }

        .btn-landmark:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(229, 81, 0, 0.4);
            color: white;
        }

        .status-indicator {
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            text-align: center;
        }

        .status-loading {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-ready {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .landmark-points {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 15;
        }

        .landmark-point {
            position: absolute;
            width: 2px;
            height: 2px;
            background: #00ff00;
            border-radius: 50%;
            transform: translate(-50%, -50%);
        }

        .emotion-history {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
        }

        .emotion-history h5 {
            color: #E55100;
            margin-bottom: 15px;
        }

        .history-item {
            display: inline-block;
            background: white;
            padding: 5px 10px;
            border-radius: 15px;
            margin: 2px;
            font-size: 0.9em;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container-fluid landmark-container">
        <div class="row">
            <div class="col-12">
                <div class="text-center mb-4">
                    <h1><i class="fas fa-brain me-2"></i> Advanced Emotion Detection</h1>
                    <p class="lead">Real-time emotion analysis</p>
                </div>

                <!-- Status Indicator -->
                <div id="status-indicator" class="status-indicator status-loading">
                    <i class="fas fa-spinner fa-spin me-2"></i>
                    Initializing MediaPipe Face Mesh...
                </div>

                <!-- Video Container -->
                <div class="video-container">
                    <video id="landmark-video" autoplay muted playsinline></video>
                    <canvas id="landmark-canvas"></canvas>
                    <div id="landmark-points" class="landmark-points"></div>
                </div>

                <!-- Controls -->
                <div class="controls">
                    <button id="start-detection" class="btn btn-landmark" disabled>
                        <i class="fas fa-play me-2"></i> Start Detection
                    </button>
                    <button id="stop-detection" class="btn btn-landmark" disabled>
                        <i class="fas fa-stop me-2"></i> Stop Detection
                    </button>
                    <button id="capture-emotion" class="btn btn-landmark" disabled>
                        <i class="fas fa-camera me-2"></i> Capture Emotion
                    </button>
                    <button id="reset-history" class="btn btn-outline-secondary">
                        <i class="fas fa-refresh me-2"></i> Reset History
                    </button>
                </div>

                <!-- Current Emotion Display -->
                <div id="emotion-display" class="emotion-display" style="display: none;">
                    <h3><i class="fas fa-heart me-2"></i> Current Emotion</h3>
                    <div class="emotion-badge">
                        <i id="emotion-icon" class="fas fa-meh"></i>
                        <span id="emotion-name">Neutral</span>
                    </div>
                    <div class="confidence-bar">
                        <div id="confidence-fill" class="confidence-fill" style="width: 0%"></div>
                    </div>
                    <small id="confidence-text">0% confidence</small>
                </div>

                <!-- Emotion History -->
                <div id="emotion-history" class="emotion-history" style="display: none;">
                    <h5><i class="fas fa-history me-2"></i> Emotion History</h5>
                    <div id="history-items"></div>
                </div>

                <!-- Action Buttons -->
                <div id="action-buttons" class="text-center mt-4" style="display: none;">
                    <h4>What would you like to do with your detected emotion?</h4>
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary w-100" onclick="proceedToMoodOptions()">
                                <i class="fas fa-arrow-right me-2"></i> Continue to Recommendations
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-success w-100" onclick="tryAgain()">
                                <i class="fas fa-redo me-2"></i> Try Again
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-info w-100" onclick="viewAnalytics()">
                                <i class="fas fa-chart-line me-2"></i> View Analytics
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-warning w-100" onclick="goHome()">
                                <i class="fas fa-home me-2"></i> Back to Home
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- MediaPipe Scripts from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils@0.3.1640029074/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils@0.3.1620248257/drawing_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh@0.4.1633559619/face_mesh.js" crossorigin="anonymous"></script>

    <!-- Custom Scripts -->
    <script src="../assets/js/landmark-emotion-detector.js"></script>

    <script>
        // Global variables
        let emotionDetector = null;
        let currentEmotion = null;
        let isDetecting = false;
        let statusUpdateInterval = null;

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeLandmarkDetection();
        });

        // Handle window resize to keep landmarks centered
        window.addEventListener('resize', function() {
            if (emotionDetector && isDetecting) {
                // Force canvas resize on next frame
                setTimeout(() => {
                    const canvas = document.getElementById('landmark-canvas');
                    const video = document.getElementById('landmark-video');
                    if (canvas && video) {
                        canvas.width = video.clientWidth;
                        canvas.height = video.clientHeight;
                        canvas.style.width = video.clientWidth + 'px';
                        canvas.style.height = video.clientHeight + 'px';
                    }
                }, 100);
            }
        });

        async function initializeLandmarkDetection() {
            try {
                updateStatus('Initializing MediaPipe Face Mesh...', 'loading');

                // Debug: Check if required classes are available
                console.log('Checking dependencies...');
                console.log('LandmarkEmotionDetector available:', typeof LandmarkEmotionDetector);
                console.log('FaceMesh available:', typeof FaceMesh);
                console.log('Camera available:', typeof Camera);

                // Check if LandmarkEmotionDetector is defined
                if (typeof LandmarkEmotionDetector === 'undefined') {
                    throw new Error('LandmarkEmotionDetector class is not defined. Check if landmark-emotion-detector.js loaded correctly.');
                }

                // Create emotion detector
                emotionDetector = new LandmarkEmotionDetector();

                // Set up callbacks
                emotionDetector.setCallbacks({
                    onInitialized: () => {
                        updateStatus('MediaPipe initialized successfully!', 'ready');
                        document.getElementById('start-detection').disabled = false;
                    },
                    onEmotionDetected: (result) => {
                        updateEmotionDisplay(result);
                        addToHistory(result);
                    },
                    onError: (error) => {
                        updateStatus(`Error: ${error.message}`, 'error');
                        console.error('Landmark detection error:', error);
                    }
                });

                // Initialize
                await emotionDetector.initialize();

            } catch (error) {
                updateStatus(`Initialization failed: ${error.message}`, 'error');
                console.error('Failed to initialize landmark detection:', error);
            }
        }

        // Start detection
        document.getElementById('start-detection').addEventListener('click', async function() {
            try {
                const video = document.getElementById('landmark-video');
                await emotionDetector.startDetection(video);

                isDetecting = true;
                this.disabled = true;
                document.getElementById('stop-detection').disabled = false;
                document.getElementById('capture-emotion').disabled = false;

                updateStatus('Detection started - analyzing your emotions...', 'ready');
                document.getElementById('emotion-display').style.display = 'block';

                // Start periodic status updates
                startStatusUpdates();

            } catch (error) {
                updateStatus(`Failed to start detection: ${error.message}`, 'error');
            }
        });

        // Stop detection
        document.getElementById('stop-detection').addEventListener('click', function() {
            emotionDetector.stopDetection();

            isDetecting = false;
            document.getElementById('start-detection').disabled = false;
            this.disabled = true;
            document.getElementById('capture-emotion').disabled = true;

            // Stop status updates
            stopStatusUpdates();

            updateStatus('Detection stopped', 'ready');
        });

        // Capture current emotion
        document.getElementById('capture-emotion').addEventListener('click', function() {
            const emotion = emotionDetector.getCurrentEmotion();
            console.log('Capture button clicked. Current emotion:', emotion);

            // Lower threshold and better validation
            if (emotion.emotion && emotion.confidence > 0.15) { // Lowered from 0.3 to 0.15
                currentEmotion = emotion;
                updateStatus(`Captured emotion: ${emotion.emotion} (${Math.round(emotion.confidence * 100)}% confidence) - Redirecting...`, 'ready');
                console.log('Emotion captured successfully:', currentEmotion);

                // Stop detection and redirect immediately to mood options
                emotionDetector.stopDetection();
                stopStatusUpdates();

                // Show target mood selection
                setTimeout(function() {
                    console.log('About to call showTargetMoodSelection with emotion:', emotion);
                    showTargetMoodSelection(emotion);
                }, 1000); // Small delay to show the success message

            } else if (!emotion.emotion) {
                updateStatus('No emotion detected yet. Please wait for detection to start.', 'error');
                console.log('No emotion detected - emotion.emotion is null/undefined');
            } else {
                updateStatus(`Emotion confidence too low: ${emotion.emotion} (${Math.round(emotion.confidence * 100)}%). Try making a clearer expression.`, 'error');
                console.log('Confidence too low:', emotion.confidence);
            }
        });

        // Reset history
        document.getElementById('reset-history').addEventListener('click', function() {
            emotionDetector.resetHistory();
            document.getElementById('history-items').innerHTML = '';
            document.getElementById('emotion-history').style.display = 'none';
            updateStatus('Emotion history cleared', 'ready');
        });

        function updateStatus(message, type) {
            const indicator = document.getElementById('status-indicator');
            indicator.className = `status-indicator status-${type}`;

            let icon = 'fas fa-info-circle';
            if (type === 'loading') icon = 'fas fa-spinner fa-spin';
            else if (type === 'ready') icon = 'fas fa-check-circle';
            else if (type === 'error') icon = 'fas fa-exclamation-triangle';

            indicator.innerHTML = `<i class="${icon} me-2"></i>${message}`;
        }

        function updateEmotionDisplay(result) {
            const emotionName = document.getElementById('emotion-name');
            const emotionIcon = document.getElementById('emotion-icon');
            const confidenceFill = document.getElementById('confidence-fill');
            const confidenceText = document.getElementById('confidence-text');

            emotionName.textContent = result.emotion.charAt(0).toUpperCase() + result.emotion.slice(1);
            emotionIcon.className = `fas fa-${getEmotionIcon(result.emotion)}`;

            const confidencePercent = Math.round(result.confidence * 100);
            confidenceFill.style.width = `${confidencePercent}%`;
            confidenceText.textContent = `${confidencePercent}% confidence`;
        }

        function addToHistory(result) {
            const historyContainer = document.getElementById('emotion-history');
            const historyItems = document.getElementById('history-items');

            const item = document.createElement('span');
            item.className = 'history-item';
            item.innerHTML = `${result.emotion} (${Math.round(result.confidence * 100)}%)`;

            historyItems.appendChild(item);
            historyContainer.style.display = 'block';

            // Keep only last 20 items
            const items = historyItems.children;
            if (items.length > 20) {
                historyItems.removeChild(items[0]);
            }
        }

        function getEmotionIcon(emotion) {
            const icons = {
                'happy': 'smile',
                'sad': 'frown',
                'angry': 'angry',
                'surprised': 'surprise',
                'fear': 'tired',
                'disgust': 'grimace',
                'neutral': 'meh'
            };
            return icons[emotion] || 'meh';
        }

        // Action button functions
        function proceedToMoodOptions() {
            if (currentEmotion) {
                const params = new URLSearchParams({
                    source: currentEmotion.emotion,
                    target: 'happy', // Default target
                    confidence: currentEmotion.confidence,
                    method: 'landmark_detection'
                });
                window.location.href = `mood_options.php?${params.toString()}`;
            }
        }

        function tryAgain() {
            document.getElementById('action-buttons').style.display = 'none';
            currentEmotion = null;
            updateStatus('Ready to detect emotions again', 'ready');
        }

        function viewAnalytics() {
            window.location.href = 'dashboard.php';
        }

        function goHome() {
            window.location.href = '../index.php';
        }

        // Status update functions
        function startStatusUpdates() {
            statusUpdateInterval = setInterval(function() {
                if (emotionDetector && isDetecting) {
                    const emotion = emotionDetector.getCurrentEmotion();
                    if (emotion.emotion && emotion.confidence > 0.1) {
                        updateStatus(`Detecting: ${emotion.emotion} (${Math.round(emotion.confidence * 100)}% confidence) - Click "Capture Emotion" when ready`, 'ready');
                    } else {
                        updateStatus('Looking for facial expressions... Make sure your face is visible and well-lit', 'loading');
                    }
                }
            }, 2000); // Update every 2 seconds
        }

        function stopStatusUpdates() {
            if (statusUpdateInterval) {
                clearInterval(statusUpdateInterval);
                statusUpdateInterval = null;
            }
        }

        // Show target mood selection interface
        function showTargetMoodSelection(capturedEmotion) {
            console.log('showTargetMoodSelection called with:', capturedEmotion);

            // Hide the video and controls
            document.querySelector('.video-container').style.display = 'none';
            document.querySelector('.controls').style.display = 'none';
            document.getElementById('emotion-display').style.display = 'none';
            document.getElementById('emotion-history').style.display = 'none';

            // Create target mood selection interface
            const container = document.querySelector('.landmark-container');
            console.log('Container found:', container);
            const targetSelectionHTML = `
                <div id="target-mood-selection" class="text-center">
                    <h2><i class="fas fa-target me-2"></i> Choose Your Target Mood</h2>
                    <p class="lead mb-4">We detected you're feeling <strong>${capturedEmotion.emotion}</strong>. Where would you like to go?</p>

                    <div class="row g-3 justify-content-center">
                        <div class="col-md-3">
                            <button class="btn btn-outline-success btn-lg w-100 target-mood-btn" data-target="happy">
                                <i class="fas fa-smile fa-2x mb-2"></i><br>
                                Happy
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-info btn-lg w-100 target-mood-btn" data-target="calm">
                                <i class="fas fa-peace fa-2x mb-2"></i><br>
                                Calm
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-warning btn-lg w-100 target-mood-btn" data-target="energetic">
                                <i class="fas fa-bolt fa-2x mb-2"></i><br>
                                Energetic
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary btn-lg w-100 target-mood-btn" data-target="focused">
                                <i class="fas fa-bullseye fa-2x mb-2"></i><br>
                                Focused
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-secondary btn-lg w-100 target-mood-btn" data-target="relaxed">
                                <i class="fas fa-couch fa-2x mb-2"></i><br>
                                Relaxed
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-danger btn-lg w-100 target-mood-btn" data-target="confident">
                                <i class="fas fa-crown fa-2x mb-2"></i><br>
                                Confident
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-success btn-lg w-100 target-mood-btn" data-target="inspired">
                                <i class="fas fa-lightbulb fa-2x mb-2"></i><br>
                                Inspired
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-info btn-lg w-100 target-mood-btn" data-target="peaceful">
                                <i class="fas fa-dove fa-2x mb-2"></i><br>
                                Peaceful
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-warning btn-lg w-100 target-mood-btn" data-target="motivated">
                                <i class="fas fa-fire fa-2x mb-2"></i><br>
                                Motivated
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary btn-lg w-100 target-mood-btn" data-target="creative">
                                <i class="fas fa-palette fa-2x mb-2"></i><br>
                                Creative
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-secondary btn-lg w-100 target-mood-btn" data-target="optimistic">
                                <i class="fas fa-sun fa-2x mb-2"></i><br>
                                Optimistic
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-danger btn-lg w-100 target-mood-btn" data-target="grateful">
                                <i class="fas fa-heart fa-2x mb-2"></i><br>
                                Grateful
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-success btn-lg w-100 target-mood-btn" data-target="joyful">
                                <i class="fas fa-laugh fa-2x mb-2"></i><br>
                                Joyful
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-info btn-lg w-100 target-mood-btn" data-target="serene">
                                <i class="fas fa-leaf fa-2x mb-2"></i><br>
                                Serene
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-warning btn-lg w-100 target-mood-btn" data-target="ambitious">
                                <i class="fas fa-mountain fa-2x mb-2"></i><br>
                                Ambitious
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary btn-lg w-100 target-mood-btn" data-target="mindful">
                                <i class="fas fa-brain fa-2x mb-2"></i><br>
                                Mindful
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-secondary btn-lg w-100 target-mood-btn" data-target="empowered">
                                <i class="fas fa-fist-raised fa-2x mb-2"></i><br>
                                Empowered
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-danger btn-lg w-100 target-mood-btn" data-target="content">
                                <i class="fas fa-smile-beam fa-2x mb-2"></i><br>
                                Content
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-success btn-lg w-100 target-mood-btn" data-target="excited">
                                <i class="fas fa-grin-stars fa-2x mb-2"></i><br>
                                Excited
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-info btn-lg w-100 target-mood-btn" data-target="balanced">
                                <i class="fas fa-yin-yang fa-2x mb-2"></i><br>
                                Balanced
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-warning btn-lg w-100 target-mood-btn" data-target="determined">
                                <i class="fas fa-flag fa-2x mb-2"></i><br>
                                Determined
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary btn-lg w-100 target-mood-btn" data-target="refreshed">
                                <i class="fas fa-seedling fa-2x mb-2"></i><br>
                                Refreshed
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-secondary btn-lg w-100 target-mood-btn" data-target="uplifted">
                                <i class="fas fa-arrow-up fa-2x mb-2"></i><br>
                                Uplifted
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-danger btn-lg w-100 target-mood-btn" data-target="centered">
                                <i class="fas fa-circle-dot fa-2x mb-2"></i><br>
                                Centered
                            </button>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button class="btn btn-outline-secondary" onclick="goBackToDetection()">
                            <i class="fas fa-arrow-left me-2"></i> Back to Detection
                        </button>
                    </div>
                </div>
            `;

            container.innerHTML = targetSelectionHTML;
            console.log('Target selection HTML set. Total mood buttons should be 24');

            // Add click handlers for target mood buttons
            const moodButtons = document.querySelectorAll('.target-mood-btn');
            console.log('Found', moodButtons.length, 'mood buttons');

            moodButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const targetMood = this.getAttribute('data-target');
                    console.log('Mood button clicked:', targetMood);
                    console.log('Captured emotion:', capturedEmotion);

                    // Add visual feedback
                    this.style.backgroundColor = '#28a745';
                    this.style.color = 'white';
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i><br>Processing...';

                    // Disable all buttons to prevent double-click
                    moodButtons.forEach(b => b.disabled = true);

                    // Call the redirect function
                    setTimeout(() => {
                        proceedToMoodOptionsWithTarget(capturedEmotion, targetMood);
                    }, 500);
                });
            });
        }

        // Proceed to mood options with selected target
        function proceedToMoodOptionsWithTarget(capturedEmotion, targetMood) {
            console.log('proceedToMoodOptionsWithTarget called');
            console.log('Captured emotion:', capturedEmotion);
            console.log('Target mood:', targetMood);

            // Validate inputs
            if (!capturedEmotion || !capturedEmotion.emotion) {
                console.error('Invalid captured emotion:', capturedEmotion);
                alert('Error: No emotion data available. Please try again.');
                return;
            }

            if (!targetMood) {
                console.error('Invalid target mood:', targetMood);
                alert('Error: No target mood selected. Please try again.');
                return;
            }

            console.log('Creating form for submission...');

            // First, save the emotion data via AJAX
            const emotionData = {
                emotion_type: capturedEmotion.emotion,
                confidence: capturedEmotion.confidence,
                source: 'face',
                raw_input: 'Facial landmark detection',
                method: 'landmark_detection'
            };

            // Send AJAX request to save emotion
            fetch('../api/emotion_analysis.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    input_type: 'face',
                    input_data: emotionData,
                    detected_emotion: capturedEmotion.emotion,
                    confidence: capturedEmotion.confidence
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Successfully saved emotion, now redirect to mood options
                    const params = new URLSearchParams({
                        source: capturedEmotion.emotion,
                        target: targetMood,
                        emotion_id: data.emotion_id || '',
                        confidence: capturedEmotion.confidence,
                        method: 'landmark_detection'
                    });

                    const redirectUrl = `<?php echo APP_URL; ?>/pages/mood_options.php?${params.toString()}`;
                    console.log('Redirecting to:', redirectUrl);
                    window.location.href = redirectUrl;
                } else {
                    console.error('Failed to save emotion:', data.message);
                    // Fallback: redirect anyway with the data we have
                    const params = new URLSearchParams({
                        source: capturedEmotion.emotion,
                        target: targetMood,
                        confidence: capturedEmotion.confidence,
                        method: 'landmark_detection'
                    });

                    const redirectUrl = `<?php echo APP_URL; ?>/pages/mood_options.php?${params.toString()}`;
                    console.log('Fallback redirect to:', redirectUrl);
                    window.location.href = redirectUrl;
                }
            })
            .catch(error => {
                console.error('AJAX error:', error);
                // Fallback: redirect anyway with the data we have
                const params = new URLSearchParams({
                    source: capturedEmotion.emotion,
                    target: targetMood,
                    confidence: capturedEmotion.confidence,
                    method: 'landmark_detection'
                });

                const redirectUrl = `<?php echo APP_URL; ?>/pages/mood_options.php?${params.toString()}`;
                console.log('Error fallback redirect to:', redirectUrl);
                window.location.href = redirectUrl;
            });
        }

        // Go back to detection interface
        function goBackToDetection() {
            location.reload(); // Simple way to reset the interface
        }

        // Get emotion icon function (same as in emotion-detection.js)
        function getEmotionIcon(emotion) {
            const icons = {
                'happy': 'smile',
                'sad': 'frown',
                'angry': 'angry',
                'anxious': 'tired',
                'calm': 'peace',
                'excited': 'grin-stars',
                'bored': 'meh',
                'tired': 'bed',
                'stressed': 'grimace',
                'neutral': 'meh-blank',
                'energetic': 'bolt',
                'focused': 'bullseye',
                'inspired': 'lightbulb',
                'relaxed': 'couch',
                'confident': 'crown',
                'peaceful': 'dove',
                'motivated': 'fire',
                'creative': 'palette',
                'optimistic': 'sun',
                'grateful': 'heart',
                'joyful': 'laugh',
                'serene': 'leaf',
                'ambitious': 'mountain',
                'mindful': 'brain',
                'empowered': 'fist-raised',
                'content': 'smile-beam',
                'balanced': 'yin-yang',
                'determined': 'flag',
                'refreshed': 'seedling',
                'uplifted': 'arrow-up',
                'centered': 'circle-dot',
                'unknown': 'question-circle'
            };

            return icons[emotion.toLowerCase()] || 'smile';
        }
    </script>
</body>
</html>
