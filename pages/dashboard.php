<?php
/**
 * MoodifyMe - User Dashboard
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

// Get user data
$userId = $_SESSION['user_id'];
$user = getUserById($userId);

// Get recent emotions
$recentEmotions = [];
$stmt = $conn->prepare("SELECT * FROM emotions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $recentEmotions[] = $row;
}

// Get recent recommendations
$recentRecommendations = [];
$stmt = $conn->prepare("
    SELECT r.*
    FROM recommendations r
    JOIN recommendation_logs rl ON r.id = rl.recommendation_id
    WHERE rl.user_id = ?
    ORDER BY rl.viewed_at DESC
    LIMIT 3
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $recentRecommendations[] = $row;
}

// Include header
include '../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Welcome, <?php echo $user['username']; ?>!</h2>
                    <p class="card-text">Track your emotional journey and get personalized recommendations to improve your mood.</p>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                        <a href="<?php echo APP_URL; ?>" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> New Mood Check
                        </a>
                        <a href="<?php echo APP_URL; ?>/pages/history.php" class="btn btn-outline-primary">
                            <i class="fas fa-history"></i> View History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Your Mood History</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($recentEmotions)): ?>
                        <p class="text-muted">You haven't recorded any moods yet. Start by checking your mood on the home page.</p>
                    <?php else: ?>
                        <div class="mood-chart">
                            <canvas id="moodChart"></canvas>
                        </div>

                        <h4 class="mt-4">Recent Mood Entries</h4>
                        <div class="list-group">
                            <?php foreach ($recentEmotions as $emotion): ?>
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">
                                            <i class="fas fa-<?php echo getEmotionIcon($emotion['emotion_type']); ?> emotion-icon"></i>
                                            <?php echo ucfirst($emotion['emotion_type']); ?>
                                        </h5>
                                        <small><?php echo formatDate($emotion['created_at']); ?></small>
                                    </div>
                                    <p class="mb-1">Confidence: <?php echo round($emotion['confidence'] * 100); ?>%</p>
                                    <small>Source: <?php echo ucfirst($emotion['source']); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="text-center mt-3">
                            <a href="<?php echo APP_URL; ?>/pages/history.php" class="btn btn-outline-primary">View All</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Quick Mood Check</h3>
                </div>
                <div class="card-body">
                    <p>How are you feeling right now?</p>

                    <form id="quick-mood-form">
                        <div class="form-group mb-3">
                            <textarea class="form-control" name="mood_text" id="quick-mood-text" rows="3"
                                placeholder="Describe how you're feeling right now..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Analyze My Mood</button>
                    </form>

                    <!-- This div will hold the emotion results -->
                    <div id="emotion-results-container" class="mt-3"></div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Recent Recommendations</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($recentRecommendations)): ?>
                        <p class="text-muted">You haven't received any recommendations yet. Check your mood to get personalized recommendations.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($recentRecommendations as $recommendation): ?>
                                <a href="<?php echo APP_URL; ?>/pages/recommendation_details.php?id=<?php echo $recommendation['id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?php echo $recommendation['title']; ?></h5>
                                        <small class="badge bg-primary"><?php echo ucfirst($recommendation['type']); ?></small>
                                    </div>
                                    <p class="mb-1"><?php echo substr($recommendation['description'], 0, 100); ?>...</p>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <div class="text-center mt-3">
                            <a href="<?php echo APP_URL; ?>/pages/recommendations.php" class="btn btn-outline-primary">View All</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-robot me-2"></i> AI Assistant
                    </h3>
                </div>
                <div class="card-body">
                    <p>Need someone to talk to? Our AI Assistant is here to help with:</p>
                    <ul>
                        <li>Jokes to brighten your day</li>
                        <li>Comforting conversations when you're down</li>
                        <li>Advice for managing different emotions</li>
                        <li>Just someone to chat with</li>
                    </ul>
                    <a href="http://localhost:3000" target="_blank" class="btn btn-primary w-100">
                        <i class="fas fa-comments me-2"></i> Chat with Assistant
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    try {
        <?php if (!empty($recentEmotions)): ?>
        // Get mood data for chart
        const moodData = <?php
            $chartData = [];
            $emotionTypes = [];
            $dates = [];

            // Get last 7 days of emotions
            $stmt = $conn->prepare("
                SELECT emotion_type, DATE(created_at) as date, AVG(confidence) as avg_confidence
                FROM emotions
                WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY emotion_type, DATE(created_at)
                ORDER BY date ASC
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                if (!in_array($row['emotion_type'], $emotionTypes)) {
                    $emotionTypes[] = $row['emotion_type'];
                }

                if (!in_array($row['date'], $dates)) {
                    $dates[] = $row['date'];
                }

                $chartData[] = [
                    'emotion' => $row['emotion_type'],
                    'date' => $row['date'],
                    'confidence' => $row['avg_confidence']
                ];
            }

            echo json_encode($chartData);
        ?>;

        // Prepare data for Chart.js
        const labels = <?php echo json_encode(array_map(function($date) {
            return date('M j', strtotime($date));
        }, $dates)); ?>;

        const datasets = [];
        const colors = {
            'happy': 'rgba(255, 193, 7, 0.5)',
            'sad': 'rgba(13, 110, 253, 0.5)',
            'angry': 'rgba(220, 53, 69, 0.5)',
            'anxious': 'rgba(108, 117, 125, 0.5)',
            'calm': 'rgba(25, 135, 84, 0.5)',
            'excited': 'rgba(255, 193, 7, 0.5)',
            'bored': 'rgba(108, 117, 125, 0.5)',
            'tired': 'rgba(108, 117, 125, 0.5)',
            'stressed': 'rgba(220, 53, 69, 0.5)',
            'neutral': 'rgba(108, 117, 125, 0.5)'
        };

        <?php foreach ($emotionTypes as $emotion): ?>
        const <?php echo $emotion; ?>Data = [];

        <?php foreach ($dates as $date): ?>
        let found = false;
        for (const item of moodData) {
            if (item.emotion === '<?php echo $emotion; ?>' && item.date === '<?php echo $date; ?>') {
                <?php echo $emotion; ?>Data.push(item.confidence);
                found = true;
                break;
            }
        }

        if (!found) {
            <?php echo $emotion; ?>Data.push(null);
        }
        <?php endforeach; ?>

        datasets.push({
            label: '<?php echo ucfirst($emotion); ?>',
            data: <?php echo $emotion; ?>Data,
            backgroundColor: colors['<?php echo $emotion; ?>'] || 'rgba(0, 0, 0, 0.5)',
            borderColor: colors['<?php echo $emotion; ?>'] || 'rgba(0, 0, 0, 0.5)',
            tension: 0.1
        });
        <?php endforeach; ?>

        // Create chart
        const ctx = document.getElementById('moodChart').getContext('2d');
        const moodChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Your Mood Over Time'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 1,
                        ticks: {
                            callback: function(value) {
                                return (value * 100) + '%';
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    } catch (error) {
        console.error('Error in chart creation:', error);
    }
});

// Helper function to get emotion icon
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
        'centered': 'circle-dot'
    };

    return icons[emotion.toLowerCase()] || 'smile';
}

// Handle quick mood form submission
document.addEventListener('DOMContentLoaded', function() {
    try {
        const quickMoodForm = document.getElementById('quick-mood-form');
        if (quickMoodForm) {
            quickMoodForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const moodText = document.getElementById('quick-mood-text').value;
                if (!moodText.trim()) {
                    alert('Please describe your mood before submitting.');
                    return;
                }

                // Show loading indicator
                const submitButton = quickMoodForm.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.innerHTML;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Analyzing...';
                submitButton.disabled = true;

                // Send data to server
                fetch('../api/emotion_analysis.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        input_type: 'text',
                        input_data: moodText
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show target mood selection
                        showDashboardEmotionResults(data.emotion, data.confidence, data.emotion_id);

                        // Reset button
                        submitButton.innerHTML = originalButtonText;
                        submitButton.disabled = false;
                    } else {
                        // Show error message
                        alert('Error analyzing mood: ' + data.message);

                        // Reset button
                        submitButton.innerHTML = originalButtonText;
                        submitButton.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while analyzing your mood. Please try again.');

                    // Reset button
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                });
            });
        }
    } catch (error) {
        console.error('Error in form submission:', error);
    }
});

// Function to show emotion results in dashboard
function showDashboardEmotionResults(emotion, confidence, emotionId) {
    try {
        const resultsContainer = document.getElementById('emotion-results-container');
        if (!resultsContainer) return;

        // Clear previous results
        resultsContainer.innerHTML = '';

        // Create detected emotion display
        const detectedEmotion = document.createElement('div');
        detectedEmotion.className = 'detected-emotion mb-3';
        detectedEmotion.innerHTML = `
            <h4>Your Current Mood</h4>
            <div class="alert alert-info">
                <i class="fas fa-${getEmotionIcon(emotion)} me-2"></i>
                <strong>${emotion.charAt(0).toUpperCase() + emotion.slice(1)}</strong>
                <small>(${Math.round(confidence * 100)}% confidence)</small>
            </div>
        `;

        // Create target emotion selection
        const targetSelection = document.createElement('div');
        targetSelection.className = 'target-emotion-selection';
        targetSelection.innerHTML = `
            <h4>What mood would you like to achieve?</h4>
            <div class="target-buttons d-flex flex-wrap gap-2 mt-3">
                <!-- Target buttons will be added here -->
            </div>
        `;

        // Add target emotion buttons
        const targetButtons = targetSelection.querySelector('.target-buttons');
        const emotions = [
            'Happy', 'Calm', 'Energetic', 'Focused', 'Inspired', 'Relaxed',
            'Confident', 'Peaceful', 'Motivated', 'Creative', 'Optimistic', 'Grateful',
            'Joyful', 'Serene', 'Ambitious', 'Mindful', 'Empowered', 'Content',
            'Excited', 'Balanced', 'Determined', 'Refreshed', 'Uplifted', 'Centered'
        ];

        emotions.forEach(targetEmotion => {
            // Create a simple button for each target emotion
            const emotionButton = document.createElement('a');
            emotionButton.href = `<?php echo APP_URL; ?>/pages/mood_options.php?source=${encodeURIComponent(emotion.toLowerCase())}&target=${encodeURIComponent(targetEmotion.toLowerCase())}&emotion_id=${encodeURIComponent(emotionId || '')}`;
            emotionButton.className = 'btn btn-primary btn-lg';
            emotionButton.style.padding = '15px';
            emotionButton.style.borderRadius = '10px';
            emotionButton.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
            emotionButton.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
            emotionButton.style.width = '100%';
            emotionButton.innerHTML = `
                <i class="fas fa-${getEmotionIcon(targetEmotion)} me-2"></i>
                ${targetEmotion}
            `;

            // Add hover effect
            emotionButton.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 8px 15px rgba(0, 0, 0, 0.1)';
            });

            emotionButton.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
            });

            // Add button to target buttons container
            targetButtons.appendChild(emotionButton);
        });

        // Create reset button
        const resetButton = document.createElement('button');
        resetButton.className = 'btn btn-outline-secondary mt-3 w-100';
        resetButton.innerHTML = '<i class="fas fa-redo me-2"></i> Try Another Mood Check';
        resetButton.addEventListener('click', function() {
            // Show the form again
            document.getElementById('quick-mood-form').style.display = 'block';
            // Clear the text area
            document.getElementById('quick-mood-text').value = '';
            // Remove the results
            resultsContainer.innerHTML = '';
        });

        // Add elements to container
        resultsContainer.appendChild(detectedEmotion);
        resultsContainer.appendChild(targetSelection);
        resultsContainer.appendChild(resetButton);

        // Hide the form
        document.getElementById('quick-mood-form').style.display = 'none';

        // Add some animation
        resultsContainer.style.animation = 'fadeIn 0.5s ease-in-out';
    } catch (error) {
        console.error('Error in showDashboardEmotionResults:', error);
    }
}
</script>

<style>
.detected-emotion {
    margin-bottom: 1.5rem;
}

.target-emotion-selection h4 {
    margin-bottom: 1rem;
}

.target-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
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

#emotion-results-container {
    animation: fadeIn 0.5s ease-in-out;
}
</style>

<?php
// Include footer
include '../includes/footer.php';

// Helper function to get emotion icon
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