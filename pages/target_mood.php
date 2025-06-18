<?php
/**
 * MoodifyMe - Target Mood Selection Page
 *
 * This page allows users to select their target mood and redirects to the mood options page
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

// Get user ID
$userId = $_SESSION['user_id'];

// Get source emotion from URL parameters
$sourceEmotion = isset($_GET['source']) ? sanitizeInput($_GET['source']) : '';
$emotionId = isset($_GET['emotion_id']) ? sanitizeInput($_GET['emotion_id']) : '';

// If source emotion is not provided, redirect to home page
if (empty($sourceEmotion)) {
    // Check if user has recent emotions
    $stmt = $conn->prepare("SELECT emotion_type FROM emotions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $sourceEmotion = $row['emotion_type'];
    } else {
        // Redirect to home page
        redirect(APP_URL);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['target_mood'])) {
    $targetMood = sanitizeInput($_POST['target_mood']);

    // Redirect to mood options page
    $redirectUrl = APP_URL . '/pages/mood_options.php?source=' . urlencode($sourceEmotion) . '&target=' . urlencode($targetMood);

    // Add emotion ID if available
    if (!empty($emotionId)) {
        $redirectUrl .= '&emotion_id=' . urlencode($emotionId);
    }

    // Redirect
    redirect($redirectUrl);
}

// Include header
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">Choose Your Target Mood</h2>

                    <div class="current-mood-info text-center mb-4">
                        <p class="lead">
                            Your current mood:
                            <span class="badge bg-primary"><?php echo ucfirst($sourceEmotion); ?></span>
                        </p>
                    </div>

                    <form method="POST" action="">
                        <div class="target-mood-selection">
                            <div class="row g-4">
                                <?php
                                $targetMoods = [
                                    'Happy', 'Calm', 'Energetic', 'Focused', 'Inspired', 'Relaxed',
                                    'Confident', 'Peaceful', 'Motivated', 'Creative', 'Optimistic', 'Grateful',
                                    'Joyful', 'Serene', 'Ambitious', 'Mindful', 'Empowered', 'Content',
                                    'Excited', 'Balanced', 'Determined', 'Refreshed', 'Uplifted', 'Centered'
                                ];

                                // Debug: Show total count
                                echo "<!-- DEBUG: Total target moods: " . count($targetMoods) . " -->";

                                foreach ($targetMoods as $mood) {
                                    $moodLower = strtolower($mood);
                                    ?>
                                    <div class="col-md-3">
                                        <div class="form-check mood-option">
                                            <input class="form-check-input visually-hidden" type="radio" name="target_mood" id="mood-<?php echo $moodLower; ?>" value="<?php echo $moodLower; ?>">
                                            <label class="form-check-label mood-card" for="mood-<?php echo $moodLower; ?>">
                                                <div class="mood-icon">
                                                    <i class="fas fa-<?php echo getEmotionIcon($mood); ?>"></i>
                                                </div>
                                                <div class="mood-name"><?php echo $mood; ?></div>
                                            </label>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>

                        <div class="text-center mt-5">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-arrow-right me-2"></i> Continue
                            </button>
                            <a href="<?php echo APP_URL; ?>" class="btn btn-outline-secondary btn-lg ms-2">
                                <i class="fas fa-redo me-2"></i> Start Over
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.target-mood-selection {
    min-height: 800px; /* Ensure enough height for all 24 options */
    overflow: visible;
}

.mood-option {
    margin-bottom: 1rem;
}

.mood-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    border-radius: 10px;
    border: 2px solid #e0e0e0;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
    width: 100%;
    height: 100%;
}

.mood-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    border-color: #007bff;
}

.form-check-input:checked + .mood-card {
    background-color: #e8f4ff;
    border-color: #007bff;
    box-shadow: 0 5px 15px rgba(0,123,255,0.3);
}

.mood-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: #007bff;
}

.mood-name {
    font-weight: 600;
    font-size: 1.2rem;
}

.current-mood-info {
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Count mood options
    const moodOptions = document.querySelectorAll('.mood-option');
    console.log('Target Mood Page - Total mood options found:', moodOptions.length);

    // List all mood options
    moodOptions.forEach((option, index) => {
        const moodName = option.querySelector('.mood-name');
        if (moodName) {
            console.log(`Mood ${index + 1}:`, moodName.textContent);
        }
    });
});
</script>

<?php
// Include footer
include '../includes/footer.php';

/**
 * Get emotion icon
 * @param string $emotion Emotion name
 * @return string Icon name
 */
function getEmotionIcon($emotion) {
    $icons = [
        'happy' => 'smile',
        'sad' => 'frown',
        'angry' => 'angry',
        'anxious' => 'tired',
        'calm' => 'peace',
        'excited' => 'grin-stars',
        'bored' => 'meh',
        'tired' => 'bed',
        'stressed' => 'grimace',
        'neutral' => 'meh-blank',
        'energetic' => 'bolt',
        'focused' => 'bullseye',
        'inspired' => 'lightbulb',
        'relaxed' => 'couch',
        'confident' => 'crown',
        'peaceful' => 'dove',
        'motivated' => 'fire',
        'creative' => 'palette',
        'optimistic' => 'sun',
        'grateful' => 'heart',
        'joyful' => 'laugh',
        'serene' => 'leaf',
        'ambitious' => 'mountain',
        'mindful' => 'brain',
        'empowered' => 'fist-raised',
        'content' => 'smile-beam',
        'balanced' => 'yin-yang',
        'determined' => 'flag',
        'refreshed' => 'seedling',
        'uplifted' => 'arrow-up',
        'centered' => 'circle-dot'
    ];

    return $icons[strtolower($emotion)] ?? 'smile';
}
?>
