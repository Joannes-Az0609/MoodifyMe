<?php
/**
 * MoodifyMe - History Page
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

// Get page number for pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Get total number of emotions
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM emotions WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$totalItems = $result->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Get emotions with pagination
$emotions = [];
$stmt = $conn->prepare("
    SELECT e.*, 
           (SELECT COUNT(*) FROM recommendation_logs rl WHERE rl.emotion_id = e.id) as recommendation_count
    FROM emotions e
    WHERE e.user_id = ?
    ORDER BY e.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("iii", $userId, $itemsPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $emotions[] = $row;
}

// Include header
include '../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Your Mood History</h2>
                    <p class="card-text">Track your emotional journey over time and see the recommendations you've received.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <?php if (empty($emotions)): ?>
                <div class="alert alert-info">
                    <p>You haven't recorded any moods yet. Start by checking your mood on the home page.</p>
                    <a href="<?php echo APP_URL; ?>" class="btn btn-primary mt-2">
                        <i class="fas fa-plus-circle"></i> New Mood Check
                    </a>
                </div>
            <?php else: ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Mood</th>
                                        <th>Confidence</th>
                                        <th>Source</th>
                                        <th>Recommendations</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($emotions as $emotion): ?>
                                        <tr>
                                            <td><?php echo formatDate($emotion['created_at']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo getEmotionColor($emotion['emotion_type']); ?>">
                                                    <i class="fas fa-<?php echo getEmotionIcon($emotion['emotion_type']); ?>"></i>
                                                    <?php echo ucfirst($emotion['emotion_type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo round($emotion['confidence'] * 100); ?>%</td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php if ($emotion['source'] === 'text'): ?>
                                                        <i class="fas fa-keyboard"></i>
                                                    <?php elseif ($emotion['source'] === 'voice'): ?>
                                                        <i class="fas fa-microphone"></i>
                                                    <?php elseif ($emotion['source'] === 'face'): ?>
                                                        <i class="fas fa-camera"></i>
                                                    <?php endif; ?>
                                                    <?php echo ucfirst($emotion['source']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($emotion['recommendation_count'] > 0): ?>
                                                    <span class="badge bg-success"><?php echo $emotion['recommendation_count']; ?> recommendations</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark">None</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/pages/emotion_details.php?id=<?php echo $emotion['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Emotion history pagination">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h3>Mood Statistics</h3>
                        <p>Here's a breakdown of your mood patterns over time.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Most Common Moods</h4>
                                <canvas id="moodPieChart"></canvas>
                            </div>
                            <div class="col-md-6">
                                <h4>Mood Trends</h4>
                                <canvas id="moodTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($emotions)): ?>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Get mood statistics
<?php
// Get mood distribution
$stmt = $conn->prepare("
    SELECT emotion_type, COUNT(*) as count
    FROM emotions
    WHERE user_id = ?
    GROUP BY emotion_type
    ORDER BY count DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$moodLabels = [];
$moodData = [];
$moodColors = [];

while ($row = $result->fetch_assoc()) {
    $moodLabels[] = ucfirst($row['emotion_type']);
    $moodData[] = $row['count'];
    $moodColors[] = getEmotionColorHex($row['emotion_type']);
}

// Get mood trends (last 30 days)
$stmt = $conn->prepare("
    SELECT DATE(created_at) as date, emotion_type, COUNT(*) as count
    FROM emotions
    WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at), emotion_type
    ORDER BY date ASC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$trendDates = [];
$trendData = [];

while ($row = $result->fetch_assoc()) {
    $date = date('M j', strtotime($row['date']));
    if (!in_array($date, $trendDates)) {
        $trendDates[] = $date;
    }
    
    if (!isset($trendData[$row['emotion_type']])) {
        $trendData[$row['emotion_type']] = array_fill(0, count($trendDates), 0);
    } else {
        // Extend array if needed
        while (count($trendData[$row['emotion_type']]) < count($trendDates)) {
            $trendData[$row['emotion_type']][] = 0;
        }
    }
    
    $dateIndex = array_search($date, $trendDates);
    $trendData[$row['emotion_type']][$dateIndex] = $row['count'];
}

// Prepare trend datasets
$trendDatasets = [];
foreach ($trendData as $emotion => $data) {
    $trendDatasets[] = [
        'label' => ucfirst($emotion),
        'data' => $data,
        'backgroundColor' => getEmotionColorHex($emotion),
        'borderColor' => getEmotionColorHex($emotion),
        'tension' => 0.1
    ];
}
?>

// Create pie chart
const moodPieCtx = document.getElementById('moodPieChart').getContext('2d');
const moodPieChart = new Chart(moodPieCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($moodLabels); ?>,
        datasets: [{
            data: <?php echo json_encode($moodData); ?>,
            backgroundColor: <?php echo json_encode($moodColors); ?>,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'right',
            }
        }
    }
});

// Create trend chart
const moodTrendCtx = document.getElementById('moodTrendChart').getContext('2d');
const moodTrendChart = new Chart(moodTrendCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($trendDates); ?>,
        datasets: <?php echo json_encode($trendDatasets); ?>
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});
</script>
<?php endif; ?>

<?php
// Include footer
include '../includes/footer.php';

// Helper functions
function getEmotionColor($emotion) {
    $colors = [
        'happy' => 'warning',
        'sad' => 'primary',
        'angry' => 'danger',
        'anxious' => 'secondary',
        'calm' => 'success',
        'excited' => 'warning',
        'bored' => 'secondary',
        'tired' => 'secondary',
        'stressed' => 'danger',
        'neutral' => 'secondary'
    ];
    
    return $colors[strtolower($emotion)] ?? 'secondary';
}

function getEmotionColorHex($emotion) {
    $colors = [
        'happy' => '#ffc107',
        'sad' => '#0d6efd',
        'angry' => '#dc3545',
        'anxious' => '#6c757d',
        'calm' => '#198754',
        'excited' => '#ff9800',
        'bored' => '#6c757d',
        'tired' => '#6c757d',
        'stressed' => '#dc3545',
        'neutral' => '#6c757d'
    ];
    
    return $colors[strtolower($emotion)] ?? '#6c757d';
}

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
        'neutral' => 'meh-blank'
    ];
    
    return $icons[strtolower($emotion)] ?? 'smile';
}
?>
