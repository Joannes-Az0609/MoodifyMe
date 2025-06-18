<?php
/**
 * MoodifyMe - Voice Input Page
 * Fresh voice input implementation using Web Speech API
 */

require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect(APP_URL . '/pages/login.php');
}

$userId = $_SESSION['user_id'];

// Include header
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="card-title">🎤 Voice Mood Detection</h2>
                        <p class="text-muted">Tell us how you're feeling and we'll help you improve your mood</p>
                    </div>

                    <!-- Voice Input Interface -->
                    <div class="voice-input-container text-center">
                        <!-- Microphone Button -->
                        <div class="microphone-section mb-4">
                            <button id="voiceButton" class="btn-microphone" type="button">
                                <div class="mic-icon">
                                    <i class="fas fa-microphone"></i>
                                </div>
                                <div class="pulse-ring"></div>
                            </button>
                            <p class="mt-3 mb-0">
                                <span id="voiceStatus" class="voice-status">Click to start speaking</span>
                            </p>
                        </div>

                        <!-- Voice Transcript -->
                        <div class="transcript-section mb-4" id="transcriptSection" style="display: none;">
                            <div class="transcript-box">
                                <h5>What you said:</h5>
                                <p id="transcript" class="transcript-text"></p>
                                <button id="retryButton" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-redo me-1"></i> Try Again
                                </button>
                            </div>
                        </div>

                        <!-- Detected Emotion -->
                        <div class="emotion-result mb-4" id="emotionResult" style="display: none;">
                            <div class="emotion-box">
                                <h5>Detected Emotion:</h5>
                                <div class="detected-emotion">
                                    <span id="detectedEmotion" class="emotion-badge"></span>
                                    <div class="confidence-bar mt-2">
                                        <small class="text-muted">Confidence: <span id="confidenceLevel"></span>%</small>
                                        <div class="progress">
                                            <div id="confidenceBar" class="progress-bar bg-success" role="progressbar"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons" id="actionButtons" style="display: none;">
                            <button id="continueButton" class="btn btn-primary btn-lg me-2">
                                <i class="fas fa-arrow-right me-1"></i> Continue to Target Mood
                            </button>
                            <button id="startOverButton" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-redo me-1"></i> Start Over
                            </button>
                        </div>

                        <!-- Error Message -->
                        <div class="alert alert-danger" id="errorMessage" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span id="errorText"></span>
                        </div>

                        <!-- Browser Support Check -->
                        <div class="alert alert-warning" id="browserWarning" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Your browser doesn't support voice recognition. Please try using Chrome, Edge, or Safari.
                        </div>
                    </div>

                    <!-- Alternative Input Methods -->
                    <div class="alternative-methods mt-5 pt-4 border-top">
                        <h6 class="text-center text-muted mb-3">Or choose another input method:</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="<?php echo APP_URL; ?>" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-keyboard me-2"></i> Text Input
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="<?php echo APP_URL; ?>/pages/landmark-emotion.php" class="btn btn-outline-info w-100">
                                    <i class="fas fa-camera me-2"></i> Face Detection
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Voice Input Styles */
.voice-input-container {
    padding: 2rem 0;
}

.btn-microphone {
    position: relative;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-size: 2.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.btn-microphone:hover {
    transform: scale(1.05);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
}

.btn-microphone.listening {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    animation: pulse 1.5s infinite;
}

.btn-microphone.processing {
    background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%);
    animation: spin 2s linear infinite;
}

.pulse-ring {
    position: absolute;
    top: -10px;
    left: -10px;
    right: -10px;
    bottom: -10px;
    border: 3px solid rgba(102, 126, 234, 0.3);
    border-radius: 50%;
    animation: pulse-ring 2s infinite;
    opacity: 0;
}

.btn-microphone.listening .pulse-ring {
    border-color: rgba(255, 107, 107, 0.5);
    animation: pulse-ring 1s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

@keyframes pulse-ring {
    0% {
        transform: scale(0.8);
        opacity: 1;
    }
    100% {
        transform: scale(1.4);
        opacity: 0;
    }
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.voice-status {
    font-size: 1.1rem;
    font-weight: 500;
    color: #6c757d;
}

.voice-status.listening {
    color: #dc3545;
    animation: blink 1s infinite;
}

.voice-status.processing {
    color: #ffc107;
}

@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0.5; }
}

.transcript-box, .emotion-box {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1.5rem;
    margin: 1rem 0;
}

.transcript-text {
    font-size: 1.1rem;
    font-style: italic;
    color: #495057;
    background: white;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #007bff;
    margin: 1rem 0;
}

.emotion-badge {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    font-size: 1.2rem;
    font-weight: 600;
    border-radius: 25px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    text-transform: capitalize;
}

.confidence-bar {
    max-width: 300px;
    margin: 0 auto;
}

.progress {
    height: 8px;
    border-radius: 4px;
    background-color: #e9ecef;
}

.alternative-methods {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .btn-microphone {
        width: 100px;
        height: 100px;
        font-size: 2rem;
    }
    
    .voice-input-container {
        padding: 1rem 0;
    }
}
</style>

<script>
// Voice Recognition Implementation
class VoiceEmotionDetector {
    constructor() {
        this.recognition = null;
        this.isListening = false;
        this.transcript = '';
        this.detectedEmotion = null;
        this.confidence = 0;
        
        this.initializeElements();
        this.checkBrowserSupport();
        this.setupEventListeners();
    }
    
    initializeElements() {
        this.voiceButton = document.getElementById('voiceButton');
        this.voiceStatus = document.getElementById('voiceStatus');
        this.transcriptSection = document.getElementById('transcriptSection');
        this.transcript = document.getElementById('transcript');
        this.emotionResult = document.getElementById('emotionResult');
        this.detectedEmotion = document.getElementById('detectedEmotion');
        this.confidenceLevel = document.getElementById('confidenceLevel');
        this.confidenceBar = document.getElementById('confidenceBar');
        this.actionButtons = document.getElementById('actionButtons');
        this.errorMessage = document.getElementById('errorMessage');
        this.browserWarning = document.getElementById('browserWarning');
        this.retryButton = document.getElementById('retryButton');
        this.continueButton = document.getElementById('continueButton');
        this.startOverButton = document.getElementById('startOverButton');
    }
    
    checkBrowserSupport() {
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            this.browserWarning.style.display = 'block';
            this.voiceButton.disabled = true;
            return false;
        }
        
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        this.recognition = new SpeechRecognition();
        
        this.recognition.continuous = false;
        this.recognition.interimResults = false;
        this.recognition.lang = 'en-US';
        
        return true;
    }
    
    setupEventListeners() {
        if (!this.recognition) return;
        
        this.voiceButton.addEventListener('click', () => this.toggleListening());
        this.retryButton.addEventListener('click', () => this.startOver());
        this.startOverButton.addEventListener('click', () => this.startOver());
        this.continueButton.addEventListener('click', () => this.continueToTargetMood());
        
        this.recognition.onstart = () => this.onStart();
        this.recognition.onresult = (event) => this.onResult(event);
        this.recognition.onerror = (event) => this.onError(event);
        this.recognition.onend = () => this.onEnd();
    }
    
    toggleListening() {
        if (this.isListening) {
            this.stopListening();
        } else {
            this.startListening();
        }
    }
    
    startListening() {
        this.hideError();
        this.recognition.start();
    }
    
    stopListening() {
        this.recognition.stop();
    }
    
    onStart() {
        this.isListening = true;
        this.voiceButton.classList.add('listening');
        this.voiceStatus.textContent = 'Listening... speak now';
        this.voiceStatus.classList.add('listening');
        this.hideAllSections();
    }
    
    onResult(event) {
        const result = event.results[0];
        const transcript = result[0].transcript;
        const confidence = result[0].confidence;
        
        this.transcript = transcript;
        this.showTranscript(transcript);
        this.analyzeEmotion(transcript, confidence);
    }
    
    onError(event) {
        this.showError(`Voice recognition error: ${event.error}`);
        this.resetButton();
    }
    
    onEnd() {
        this.isListening = false;
        this.resetButton();
    }
    
    showTranscript(text) {
        document.getElementById('transcript').textContent = text;
        this.transcriptSection.style.display = 'block';
    }
    
    async analyzeEmotion(text, speechConfidence) {
        this.voiceButton.classList.add('processing');
        this.voiceStatus.textContent = 'Analyzing emotion...';
        this.voiceStatus.classList.add('processing');
        
        try {
            const response = await fetch('../api/emotion_analysis.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    input_type: 'text',
                    input_data: text,
                    source: 'voice'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showEmotionResult(data.emotion, data.confidence);
            } else {
                this.showError('Failed to analyze emotion. Please try again.');
            }
        } catch (error) {
            this.showError('Network error. Please check your connection and try again.');
        }
        
        this.voiceButton.classList.remove('processing');
        this.voiceStatus.classList.remove('processing');
    }
    
    showEmotionResult(emotion, confidence) {
        this.detectedEmotion.textContent = emotion;
        this.confidenceLevel.textContent = Math.round(confidence * 100);
        this.confidenceBar.style.width = `${confidence * 100}%`;
        
        // Set confidence bar color based on confidence level
        if (confidence > 0.7) {
            this.confidenceBar.className = 'progress-bar bg-success';
        } else if (confidence > 0.5) {
            this.confidenceBar.className = 'progress-bar bg-warning';
        } else {
            this.confidenceBar.className = 'progress-bar bg-danger';
        }
        
        this.emotionResult.style.display = 'block';
        this.actionButtons.style.display = 'block';
        this.voiceStatus.textContent = 'Emotion detected successfully!';
        
        // Store emotion data for next step
        this.currentEmotion = emotion;
        this.currentConfidence = confidence;
    }
    
    continueToTargetMood() {
        // Save emotion to database and redirect to target mood selection
        this.saveEmotionAndRedirect();
    }
    
    async saveEmotionAndRedirect() {
        try {
            // Use the same emotion analysis API to save the emotion
            const response = await fetch('../api/emotion_analysis.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    input_type: 'voice',
                    input_data: this.transcript,
                    detected_emotion: this.currentEmotion,
                    confidence: this.currentConfidence
                })
            });

            const data = await response.json();

            if (data.success) {
                // Redirect to target mood selection
                window.location.href = `<?php echo APP_URL; ?>/pages/target_mood.php?source=${encodeURIComponent(this.currentEmotion)}&emotion_id=${encodeURIComponent(data.emotion_id || '')}`;
            } else {
                this.showError('Failed to save emotion. Please try again.');
            }
        } catch (error) {
            this.showError('Network error. Please try again.');
        }
    }
    
    startOver() {
        this.hideAllSections();
        this.resetButton();
        this.voiceStatus.textContent = 'Click to start speaking';
        this.transcript = '';
        this.currentEmotion = null;
        this.currentConfidence = 0;
    }
    
    resetButton() {
        this.voiceButton.classList.remove('listening', 'processing');
        this.voiceStatus.classList.remove('listening', 'processing');
    }
    
    hideAllSections() {
        this.transcriptSection.style.display = 'none';
        this.emotionResult.style.display = 'none';
        this.actionButtons.style.display = 'none';
        this.hideError();
    }
    
    showError(message) {
        document.getElementById('errorText').textContent = message;
        this.errorMessage.style.display = 'block';
    }
    
    hideError() {
        this.errorMessage.style.display = 'none';
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    new VoiceEmotionDetector();
});
</script>

<?php include '../includes/footer.php'; ?>
