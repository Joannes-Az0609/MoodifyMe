<?php
/**
 * MoodifyMe - African Meals API
 * Comprehensive API for African meal recommendations
 *
 * Endpoints:
 * GET /api/african_meals.php?action=get_by_mood&source=sad&target=happy
 * GET /api/african_meals.php?action=get_by_region&region=west_africa
 * GET /api/african_meals.php?action=get_by_country&country=nigeria
 * GET /api/african_meals.php?action=search&query=jollof
 * GET /api/african_meals.php?action=get_meal&id=123
 * GET /api/african_meals.php?action=get_popular&limit=10
 * GET /api/african_meals.php?action=get_random&limit=5
 * POST /api/african_meals.php?action=add_feedback (with JSON body)
 */

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';
require_once '../includes/recommendation_functions.php';

// Start session
session_start();

// Set response header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if user is logged in for most endpoints
$requireAuth = !in_array($_GET['action'] ?? '', ['get_random', 'search', 'get_by_region', 'get_by_country']);

if ($requireAuth && !isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated',
        'error_code' => 'AUTH_REQUIRED'
    ]);
    exit;
}

// Get user ID if authenticated
$userId = $_SESSION['user_id'] ?? null;

// Get action parameter
$action = $_GET['action'] ?? '';

// Route to appropriate handler
switch ($action) {
    case 'get_by_mood':
        handleGetByMood();
        break;
    case 'get_by_region':
        handleGetByRegion();
        break;
    case 'get_by_country':
        handleGetByCountry();
        break;
    case 'search':
        handleSearch();
        break;
    case 'get_meal':
        handleGetMeal();
        break;
    case 'get_popular':
        handleGetPopular();
        break;
    case 'get_random':
        handleGetRandom();
        break;
    case 'add_feedback':
        handleAddFeedback();
        break;
    case 'get_regions':
        handleGetRegions();
        break;
    case 'get_countries':
        handleGetCountries();
        break;
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action specified',
            'error_code' => 'INVALID_ACTION',
            'available_actions' => [
                'get_by_mood', 'get_by_region', 'get_by_country',
                'search', 'get_meal', 'get_popular', 'get_random',
                'add_feedback', 'get_regions', 'get_countries'
            ]
        ]);
        break;
}

/**
 * Get African meals by mood transition
 */
function handleGetByMood() {
    global $conn, $userId;

    $sourceEmotion = $_GET['source'] ?? '';
    $targetEmotion = $_GET['target'] ?? '';
    $limit = min(intval($_GET['limit'] ?? 10), 50); // Max 50 results
    $page = max(intval($_GET['page'] ?? 1), 1);
    $offset = ($page - 1) * $limit;

    // Validate input
    if (empty($sourceEmotion) || empty($targetEmotion)) {
        echo json_encode([
            'success' => false,
            'message' => 'Source and target emotions are required',
            'error_code' => 'MISSING_EMOTIONS'
        ]);
        return;
    }

    // Get meals with fallback logic
    $meals = getAfricanMealsByMood($sourceEmotion, $targetEmotion, $limit, $offset);

    // Add user feedback if authenticated
    if ($userId) {
        $meals = addUserFeedbackToMeals($meals, $userId);
    }

    // Log recommendation view if authenticated
    if ($userId && !empty($meals)) {
        logMoodBasedRecommendationView($userId, $sourceEmotion, $targetEmotion, $meals);
    }

    echo json_encode([
        'success' => true,
        'data' => $meals,
        'meta' => [
            'source_emotion' => $sourceEmotion,
            'target_emotion' => $targetEmotion,
            'page' => $page,
            'limit' => $limit,
            'total_results' => count($meals)
        ]
    ]);
}

/**
 * Get African meals by region
 */
function handleGetByRegion() {
    global $conn;

    $region = $_GET['region'] ?? '';
    $limit = min(intval($_GET['limit'] ?? 20), 50);
    $page = max(intval($_GET['page'] ?? 1), 1);
    $offset = ($page - 1) * $limit;

    if (empty($region)) {
        echo json_encode([
            'success' => false,
            'message' => 'Region parameter is required',
            'error_code' => 'MISSING_REGION'
        ]);
        return;
    }

    $meals = getAfricanMealsByRegion($region, $limit, $offset);

    echo json_encode([
        'success' => true,
        'data' => $meals,
        'meta' => [
            'region' => $region,
            'page' => $page,
            'limit' => $limit,
            'total_results' => count($meals)
        ]
    ]);
}

/**
 * Get African meals by country
 */
function handleGetByCountry() {
    global $conn;

    $country = $_GET['country'] ?? '';
    $limit = min(intval($_GET['limit'] ?? 20), 50);
    $page = max(intval($_GET['page'] ?? 1), 1);
    $offset = ($page - 1) * $limit;

    if (empty($country)) {
        echo json_encode([
            'success' => false,
            'message' => 'Country parameter is required',
            'error_code' => 'MISSING_COUNTRY'
        ]);
        return;
    }

    $meals = getAfricanMealsByCountry($country, $limit, $offset);

    echo json_encode([
        'success' => true,
        'data' => $meals,
        'meta' => [
            'country' => $country,
            'page' => $page,
            'limit' => $limit,
            'total_results' => count($meals)
        ]
    ]);
}

/**
 * Search African meals
 */
function handleSearch() {
    global $conn;

    $query = $_GET['query'] ?? '';
    $limit = min(intval($_GET['limit'] ?? 20), 50);
    $page = max(intval($_GET['page'] ?? 1), 1);
    $offset = ($page - 1) * $limit;

    if (empty($query) || strlen($query) < 2) {
        echo json_encode([
            'success' => false,
            'message' => 'Search query must be at least 2 characters long',
            'error_code' => 'INVALID_QUERY'
        ]);
        return;
    }

    $meals = searchAfricanMeals($query, $limit, $offset);

    echo json_encode([
        'success' => true,
        'data' => $meals,
        'meta' => [
            'query' => $query,
            'page' => $page,
            'limit' => $limit,
            'total_results' => count($meals)
        ]
    ]);
}

/**
 * Get specific meal details
 */
function handleGetMeal() {
    global $conn, $userId;

    $mealId = intval($_GET['id'] ?? 0);

    if ($mealId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Valid meal ID is required',
            'error_code' => 'INVALID_MEAL_ID'
        ]);
        return;
    }

    $meal = getAfricanMealById($mealId);

    if (!$meal) {
        echo json_encode([
            'success' => false,
            'message' => 'Meal not found',
            'error_code' => 'MEAL_NOT_FOUND'
        ]);
        return;
    }

    // Add user feedback if authenticated
    if ($userId) {
        $meal = addUserFeedbackToMeal($meal, $userId);
    }

    echo json_encode([
        'success' => true,
        'data' => $meal
    ]);
}

/**
 * Get popular African meals
 */
function handleGetPopular() {
    global $conn, $userId;

    $limit = min(intval($_GET['limit'] ?? 10), 50);
    $timeframe = $_GET['timeframe'] ?? 'all_time'; // all_time, month, week

    $meals = getPopularAfricanMeals($limit, $timeframe);

    // Add user feedback if authenticated
    if ($userId) {
        $meals = addUserFeedbackToMeals($meals, $userId);
    }

    echo json_encode([
        'success' => true,
        'data' => $meals,
        'meta' => [
            'limit' => $limit,
            'timeframe' => $timeframe,
            'total_results' => count($meals)
        ]
    ]);
}

/**
 * Get random African meals
 */
function handleGetRandom() {
    global $conn, $userId;

    $limit = min(intval($_GET['limit'] ?? 5), 20);

    $meals = getRandomAfricanMeals($limit);

    // Add user feedback if authenticated
    if ($userId) {
        $meals = addUserFeedbackToMeals($meals, $userId);
    }

    echo json_encode([
        'success' => true,
        'data' => $meals,
        'meta' => [
            'limit' => $limit,
            'total_results' => count($meals)
        ]
    ]);
}

/**
 * Add feedback for a meal
 */
function handleAddFeedback() {
    global $conn, $userId;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'success' => false,
            'message' => 'POST method required',
            'error_code' => 'INVALID_METHOD'
        ]);
        return;
    }

    if (!$userId) {
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required',
            'error_code' => 'AUTH_REQUIRED'
        ]);
        return;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    $mealId = intval($input['meal_id'] ?? 0);
    $feedbackType = $input['feedback_type'] ?? '';

    if ($mealId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Valid meal ID is required',
            'error_code' => 'INVALID_MEAL_ID'
        ]);
        return;
    }

    if (!in_array($feedbackType, ['like', 'dislike'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Feedback type must be "like" or "dislike"',
            'error_code' => 'INVALID_FEEDBACK_TYPE'
        ]);
        return;
    }

    // Check if meal exists
    $stmt = $conn->prepare("SELECT id FROM recommendations WHERE id = ? AND type = 'african_meals'");
    $stmt->bind_param("i", $mealId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Meal not found',
            'error_code' => 'MEAL_NOT_FOUND'
        ]);
        return;
    }

    // Add or update feedback
    $stmt = $conn->prepare("
        INSERT INTO recommendation_feedback (user_id, recommendation_id, feedback_type, created_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
        feedback_type = VALUES(feedback_type),
        created_at = NOW()
    ");
    $stmt->bind_param("iis", $userId, $mealId, $feedbackType);

    if ($stmt->execute()) {
        // Get updated feedback counts
        $feedbackCounts = getMealFeedbackCounts($mealId);

        echo json_encode([
            'success' => true,
            'message' => 'Feedback recorded successfully',
            'data' => [
                'meal_id' => $mealId,
                'user_feedback' => $feedbackType,
                'total_likes' => $feedbackCounts['likes'],
                'total_dislikes' => $feedbackCounts['dislikes']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to record feedback',
            'error_code' => 'FEEDBACK_FAILED'
        ]);
    }
}

/**
 * Get available regions
 */
function handleGetRegions() {
    $regions = [
        'west_africa' => 'West Africa',
        'east_africa' => 'East Africa',
        'north_africa' => 'North Africa',
        'south_africa' => 'Southern Africa',
        'central_africa' => 'Central Africa',
        'island_africa' => 'African Islands'
    ];

    echo json_encode([
        'success' => true,
        'data' => $regions
    ]);
}

/**
 * Get available countries
 */
function handleGetCountries() {
    $countries = getAfricanCountriesWithMeals();

    echo json_encode([
        'success' => true,
        'data' => $countries,
        'meta' => [
            'total_countries' => count($countries)
        ]
    ]);
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Get African meals by mood transition
 */
function getAfricanMealsByMood($sourceEmotion, $targetEmotion, $limit, $offset) {
    global $conn;

    // First try exact match
    $stmt = $conn->prepare("
        SELECT r.*,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'like') as likes,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'dislike') as dislikes
        FROM recommendations r
        WHERE r.type = 'african_meals'
        AND r.source_emotion = ?
        AND r.target_emotion = ?
        ORDER BY RAND()
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("ssii", $sourceEmotion, $targetEmotion, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $meals = [];
    while ($row = $result->fetch_assoc()) {
        $meals[] = enhanceMealData($row);
    }

    // If not enough results, try target emotion only
    if (count($meals) < $limit) {
        $remaining = $limit - count($meals);
        $stmt = $conn->prepare("
            SELECT r.*,
                   (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'like') as likes,
                   (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'dislike') as dislikes
            FROM recommendations r
            WHERE r.type = 'african_meals'
            AND r.target_emotion = ?
            AND r.id NOT IN (" . implode(',', array_map(function($m) { return $m['id']; }, $meals)) . ")
            ORDER BY RAND()
            LIMIT ?
        ");
        $stmt->bind_param("si", $targetEmotion, $remaining);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $meals[] = enhanceMealData($row);
        }
    }

    return $meals;
}

/**
 * Get African meals by region
 */
function getAfricanMealsByRegion($region, $limit, $offset) {
    global $conn;

    // Map regions to countries or search patterns
    $regionPatterns = [
        'west_africa' => ['Nigeria', 'Ghana', 'Senegal', 'Mali', 'Burkina Faso', 'Ivory Coast', 'Guinea', 'Sierra Leone', 'Liberia', 'Togo', 'Benin'],
        'east_africa' => ['Ethiopia', 'Kenya', 'Tanzania', 'Uganda', 'Rwanda', 'Burundi', 'Somalia', 'Eritrea'],
        'north_africa' => ['Morocco', 'Algeria', 'Tunisia', 'Libya', 'Egypt', 'Sudan'],
        'south_africa' => ['South Africa', 'Zimbabwe', 'Botswana', 'Namibia', 'Zambia', 'Malawi', 'Lesotho', 'Swaziland'],
        'central_africa' => ['Democratic Republic of Congo', 'Central African Republic', 'Chad', 'Cameroon', 'Equatorial Guinea', 'Gabon'],
        'island_africa' => ['Madagascar', 'Mauritius', 'Seychelles', 'Comoros', 'Cape Verde']
    ];

    if (!isset($regionPatterns[$region])) {
        return [];
    }

    $countries = $regionPatterns[$region];
    $placeholders = str_repeat('?,', count($countries) - 1) . '?';

    $stmt = $conn->prepare("
        SELECT r.*,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'like') as likes,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'dislike') as dislikes
        FROM recommendations r
        WHERE r.type = 'african_meals'
        AND (r.title LIKE CONCAT('%', ?, '%') OR r.description LIKE CONCAT('%', ?, '%') OR r.content LIKE CONCAT('%', ?, '%'))
        ORDER BY RAND()
        LIMIT ? OFFSET ?
    ");

    $searchTerm = implode('|', $countries);
    $stmt->bind_param("sssii", $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $meals = [];
    while ($row = $result->fetch_assoc()) {
        $meals[] = enhanceMealData($row);
    }

    return $meals;
}

/**
 * Get African meals by country
 */
function getAfricanMealsByCountry($country, $limit, $offset) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT r.*,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'like') as likes,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'dislike') as dislikes
        FROM recommendations r
        WHERE r.type = 'african_meals'
        AND (r.title LIKE CONCAT('%', ?, '%') OR r.description LIKE CONCAT('%', ?, '%') OR r.content LIKE CONCAT('%', ?, '%'))
        ORDER BY RAND()
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("sssii", $country, $country, $country, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $meals = [];
    while ($row = $result->fetch_assoc()) {
        $meals[] = enhanceMealData($row);
    }

    return $meals;
}

/**
 * Search African meals
 */
function searchAfricanMeals($query, $limit, $offset) {
    global $conn;

    $searchTerm = '%' . $query . '%';

    $stmt = $conn->prepare("
        SELECT r.*,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'like') as likes,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'dislike') as dislikes
        FROM recommendations r
        WHERE r.type = 'african_meals'
        AND (r.title LIKE ? OR r.description LIKE ? OR r.content LIKE ?)
        ORDER BY
            CASE
                WHEN r.title LIKE ? THEN 1
                WHEN r.description LIKE ? THEN 2
                ELSE 3
            END,
            RAND()
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("sssssii", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $meals = [];
    while ($row = $result->fetch_assoc()) {
        $meals[] = enhanceMealData($row);
    }

    return $meals;
}

/**
 * Get African meal by ID
 */
function getAfricanMealById($mealId) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT r.*,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'like') as likes,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'dislike') as dislikes
        FROM recommendations r
        WHERE r.id = ? AND r.type = 'african_meals'
    ");
    $stmt->bind_param("i", $mealId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return enhanceMealData($row);
    }

    return null;
}

/**
 * Get popular African meals
 */
function getPopularAfricanMeals($limit, $timeframe = 'all_time') {
    global $conn;

    $timeCondition = '';
    if ($timeframe === 'week') {
        $timeCondition = "AND rf.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
    } elseif ($timeframe === 'month') {
        $timeCondition = "AND rf.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    }

    $stmt = $conn->prepare("
        SELECT r.*,
               COUNT(CASE WHEN rf.feedback_type = 'like' THEN 1 END) as likes,
               COUNT(CASE WHEN rf.feedback_type = 'dislike' THEN 1 END) as dislikes,
               (COUNT(CASE WHEN rf.feedback_type = 'like' THEN 1 END) - COUNT(CASE WHEN rf.feedback_type = 'dislike' THEN 1 END)) as score
        FROM recommendations r
        LEFT JOIN recommendation_feedback rf ON r.id = rf.recommendation_id $timeCondition
        WHERE r.type = 'african_meals'
        GROUP BY r.id
        ORDER BY score DESC, likes DESC
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $meals = [];
    while ($row = $result->fetch_assoc()) {
        $meals[] = enhanceMealData($row);
    }

    return $meals;
}

/**
 * Get random African meals
 */
function getRandomAfricanMeals($limit) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT r.*,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'like') as likes,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'dislike') as dislikes
        FROM recommendations r
        WHERE r.type = 'african_meals'
        ORDER BY RAND()
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $meals = [];
    while ($row = $result->fetch_assoc()) {
        $meals[] = enhanceMealData($row);
    }

    return $meals;
}

/**
 * Get African countries that have meals in the database
 */
function getAfricanCountriesWithMeals() {
    global $conn;

    $stmt = $conn->prepare("
        SELECT DISTINCT
            CASE
                WHEN title LIKE '%Nigerian%' OR description LIKE '%Nigeria%' OR content LIKE '%Nigeria%' THEN 'Nigeria'
                WHEN title LIKE '%Ghanaian%' OR description LIKE '%Ghana%' OR content LIKE '%Ghana%' THEN 'Ghana'
                WHEN title LIKE '%Ethiopian%' OR description LIKE '%Ethiopia%' OR content LIKE '%Ethiopia%' THEN 'Ethiopia'
                WHEN title LIKE '%Moroccan%' OR description LIKE '%Morocco%' OR content LIKE '%Morocco%' THEN 'Morocco'
                WHEN title LIKE '%Egyptian%' OR description LIKE '%Egypt%' OR content LIKE '%Egypt%' THEN 'Egypt'
                WHEN title LIKE '%Kenyan%' OR description LIKE '%Kenya%' OR content LIKE '%Kenya%' THEN 'Kenya'
                WHEN title LIKE '%South African%' OR description LIKE '%South Africa%' OR content LIKE '%South Africa%' THEN 'South Africa'
                WHEN title LIKE '%Senegalese%' OR description LIKE '%Senegal%' OR content LIKE '%Senegal%' THEN 'Senegal'
                WHEN title LIKE '%Tanzanian%' OR description LIKE '%Tanzania%' OR content LIKE '%Tanzania%' THEN 'Tanzania'
                WHEN title LIKE '%Algerian%' OR description LIKE '%Algeria%' OR content LIKE '%Algeria%' THEN 'Algeria'
                WHEN title LIKE '%Tunisian%' OR description LIKE '%Tunisia%' OR content LIKE '%Tunisia%' THEN 'Tunisia'
                WHEN title LIKE '%Libyan%' OR description LIKE '%Libya%' OR content LIKE '%Libya%' THEN 'Libya'
                WHEN title LIKE '%Ugandan%' OR description LIKE '%Uganda%' OR content LIKE '%Uganda%' THEN 'Uganda'
                WHEN title LIKE '%Rwandan%' OR description LIKE '%Rwanda%' OR content LIKE '%Rwanda%' THEN 'Rwanda'
                WHEN title LIKE '%Somali%' OR description LIKE '%Somalia%' OR content LIKE '%Somalia%' THEN 'Somalia'
                WHEN title LIKE '%Eritrean%' OR description LIKE '%Eritrea%' OR content LIKE '%Eritrea%' THEN 'Eritrea'
                WHEN title LIKE '%Zimbabwean%' OR description LIKE '%Zimbabwe%' OR content LIKE '%Zimbabwe%' THEN 'Zimbabwe'
                WHEN title LIKE '%Namibian%' OR description LIKE '%Namibia%' OR content LIKE '%Namibia%' THEN 'Namibia'
                WHEN title LIKE '%Botswana%' OR description LIKE '%Botswana%' OR content LIKE '%Botswana%' THEN 'Botswana'
                WHEN title LIKE '%Zambian%' OR description LIKE '%Zambia%' OR content LIKE '%Zambia%' THEN 'Zambia'
                WHEN title LIKE '%Malawian%' OR description LIKE '%Malawi%' OR content LIKE '%Malawi%' THEN 'Malawi'
                WHEN title LIKE '%Congolese%' OR description LIKE '%Congo%' OR content LIKE '%Congo%' THEN 'Democratic Republic of Congo'
                WHEN title LIKE '%Gabonese%' OR description LIKE '%Gabon%' OR content LIKE '%Gabon%' THEN 'Gabon'
                WHEN title LIKE '%Chadian%' OR description LIKE '%Chad%' OR content LIKE '%Chad%' THEN 'Chad'
                WHEN title LIKE '%Cameroonian%' OR description LIKE '%Cameroon%' OR content LIKE '%Cameroon%' THEN 'Cameroon'
                WHEN title LIKE '%Madagascan%' OR description LIKE '%Madagascar%' OR content LIKE '%Madagascar%' THEN 'Madagascar'
                WHEN title LIKE '%Mauritian%' OR description LIKE '%Mauritius%' OR content LIKE '%Mauritius%' THEN 'Mauritius'
                WHEN title LIKE '%Seychellois%' OR description LIKE '%Seychelles%' OR content LIKE '%Seychelles%' THEN 'Seychelles'
                WHEN title LIKE '%Comorian%' OR description LIKE '%Comoros%' OR content LIKE '%Comoros%' THEN 'Comoros'
                WHEN title LIKE '%Cape Verdean%' OR description LIKE '%Cape Verde%' OR content LIKE '%Cape Verde%' THEN 'Cape Verde'
                ELSE 'Other African'
            END as country
        FROM recommendations
        WHERE type = 'african_meals'
        HAVING country != 'Other African'
        ORDER BY country
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    $countries = [];
    while ($row = $result->fetch_assoc()) {
        $countries[] = $row['country'];
    }

    return array_unique($countries);
}

/**
 * Enhance meal data with additional information
 */
function enhanceMealData($meal) {
    // Extract country from title or description
    $meal['country'] = extractCountryFromMeal($meal);
    $meal['region'] = getRegionFromCountry($meal['country']);
    $meal['difficulty'] = estimateDifficulty($meal);
    $meal['cooking_time'] = estimateCookingTime($meal);
    $meal['dietary_tags'] = extractDietaryTags($meal);

    // Ensure image URL is absolute
    if (!empty($meal['image_url']) && !str_starts_with($meal['image_url'], 'http')) {
        $meal['image_url'] = APP_URL . '/' . ltrim($meal['image_url'], '/');
    }

    // Add fallback image if none exists
    if (empty($meal['image_url'])) {
        $meal['image_url'] = APP_URL . '/assets/images/placeholder-meal.jpg';
    }

    return $meal;
}

/**
 * Extract country from meal data
 */
function extractCountryFromMeal($meal) {
    $text = strtolower($meal['title'] . ' ' . $meal['description'] . ' ' . $meal['content']);

    $countryPatterns = [
        'nigeria' => ['nigerian', 'nigeria'],
        'ghana' => ['ghanaian', 'ghana'],
        'ethiopia' => ['ethiopian', 'ethiopia'],
        'morocco' => ['moroccan', 'morocco'],
        'egypt' => ['egyptian', 'egypt'],
        'kenya' => ['kenyan', 'kenya'],
        'south africa' => ['south african', 'south africa'],
        'senegal' => ['senegalese', 'senegal'],
        'tanzania' => ['tanzanian', 'tanzania'],
        'algeria' => ['algerian', 'algeria'],
        'tunisia' => ['tunisian', 'tunisia'],
        'libya' => ['libyan', 'libya'],
        'uganda' => ['ugandan', 'uganda'],
        'rwanda' => ['rwandan', 'rwanda'],
        'somalia' => ['somali', 'somalia'],
        'eritrea' => ['eritrean', 'eritrea'],
        'zimbabwe' => ['zimbabwean', 'zimbabwe'],
        'namibia' => ['namibian', 'namibia'],
        'botswana' => ['botswana'],
        'zambia' => ['zambian', 'zambia'],
        'malawi' => ['malawian', 'malawi'],
        'democratic republic of congo' => ['congolese', 'congo', 'drc'],
        'gabon' => ['gabonese', 'gabon'],
        'chad' => ['chadian', 'chad'],
        'cameroon' => ['cameroonian', 'cameroon'],
        'madagascar' => ['madagascan', 'madagascar'],
        'mauritius' => ['mauritian', 'mauritius'],
        'seychelles' => ['seychellois', 'seychelles'],
        'comoros' => ['comorian', 'comoros'],
        'cape verde' => ['cape verdean', 'cape verde']
    ];

    foreach ($countryPatterns as $country => $patterns) {
        foreach ($patterns as $pattern) {
            if (str_contains($text, $pattern)) {
                return ucwords($country);
            }
        }
    }

    return 'African';
}

/**
 * Get region from country
 */
function getRegionFromCountry($country) {
    $regionMap = [
        'Nigeria' => 'West Africa',
        'Ghana' => 'West Africa',
        'Senegal' => 'West Africa',
        'Mali' => 'West Africa',
        'Burkina Faso' => 'West Africa',
        'Ivory Coast' => 'West Africa',
        'Guinea' => 'West Africa',
        'Sierra Leone' => 'West Africa',
        'Liberia' => 'West Africa',
        'Togo' => 'West Africa',
        'Benin' => 'West Africa',
        'Ethiopia' => 'East Africa',
        'Kenya' => 'East Africa',
        'Tanzania' => 'East Africa',
        'Uganda' => 'East Africa',
        'Rwanda' => 'East Africa',
        'Burundi' => 'East Africa',
        'Somalia' => 'East Africa',
        'Eritrea' => 'East Africa',
        'Morocco' => 'North Africa',
        'Algeria' => 'North Africa',
        'Tunisia' => 'North Africa',
        'Libya' => 'North Africa',
        'Egypt' => 'North Africa',
        'Sudan' => 'North Africa',
        'South Africa' => 'Southern Africa',
        'Zimbabwe' => 'Southern Africa',
        'Botswana' => 'Southern Africa',
        'Namibia' => 'Southern Africa',
        'Zambia' => 'Southern Africa',
        'Malawi' => 'Southern Africa',
        'Lesotho' => 'Southern Africa',
        'Swaziland' => 'Southern Africa',
        'Democratic Republic Of Congo' => 'Central Africa',
        'Central African Republic' => 'Central Africa',
        'Chad' => 'Central Africa',
        'Cameroon' => 'Central Africa',
        'Equatorial Guinea' => 'Central Africa',
        'Gabon' => 'Central Africa',
        'Madagascar' => 'African Islands',
        'Mauritius' => 'African Islands',
        'Seychelles' => 'African Islands',
        'Comoros' => 'African Islands',
        'Cape Verde' => 'African Islands'
    ];

    return $regionMap[$country] ?? 'Africa';
}

/**
 * Estimate cooking difficulty based on meal description
 */
function estimateDifficulty($meal) {
    $text = strtolower($meal['title'] . ' ' . $meal['description'] . ' ' . $meal['content']);

    $easyKeywords = ['simple', 'easy', 'quick', 'basic', 'traditional', 'street food'];
    $mediumKeywords = ['marinated', 'slow-cooked', 'stew', 'curry', 'spiced', 'fermented'];
    $hardKeywords = ['complex', 'ceremonial', 'layered', 'stuffed', 'multiple', 'advanced'];

    $easyCount = 0;
    $mediumCount = 0;
    $hardCount = 0;

    foreach ($easyKeywords as $keyword) {
        if (str_contains($text, $keyword)) $easyCount++;
    }

    foreach ($mediumKeywords as $keyword) {
        if (str_contains($text, $keyword)) $mediumCount++;
    }

    foreach ($hardKeywords as $keyword) {
        if (str_contains($text, $keyword)) $hardCount++;
    }

    if ($hardCount > 0) return 'Hard';
    if ($mediumCount > $easyCount) return 'Medium';
    return 'Easy';
}

/**
 * Estimate cooking time based on meal description
 */
function estimateCookingTime($meal) {
    $text = strtolower($meal['title'] . ' ' . $meal['description'] . ' ' . $meal['content']);

    $quickKeywords = ['quick', 'fast', 'instant', 'grilled', 'fried', 'sautéed'];
    $mediumKeywords = ['cooked', 'steamed', 'baked', 'roasted'];
    $longKeywords = ['slow-cooked', 'stew', 'braised', 'fermented', 'marinated', 'overnight'];

    foreach ($longKeywords as $keyword) {
        if (str_contains($text, $keyword)) return '2+ hours';
    }

    foreach ($mediumKeywords as $keyword) {
        if (str_contains($text, $keyword)) return '30-60 minutes';
    }

    foreach ($quickKeywords as $keyword) {
        if (str_contains($text, $keyword)) return '15-30 minutes';
    }

    return '30-60 minutes'; // Default
}

/**
 * Extract dietary tags from meal description
 */
function extractDietaryTags($meal) {
    $text = strtolower($meal['title'] . ' ' . $meal['description'] . ' ' . $meal['content']);
    $tags = [];

    // Check for dietary restrictions
    if (str_contains($text, 'vegetarian') || str_contains($text, 'vegetables') || str_contains($text, 'beans') || str_contains($text, 'lentils')) {
        $tags[] = 'Vegetarian';
    }

    if (str_contains($text, 'vegan') || (str_contains($text, 'vegetables') && !str_contains($text, 'meat') && !str_contains($text, 'fish') && !str_contains($text, 'chicken'))) {
        $tags[] = 'Vegan';
    }

    if (str_contains($text, 'gluten-free') || str_contains($text, 'rice') || str_contains($text, 'cassava')) {
        $tags[] = 'Gluten-Free';
    }

    if (str_contains($text, 'spicy') || str_contains($text, 'hot') || str_contains($text, 'pepper') || str_contains($text, 'chili')) {
        $tags[] = 'Spicy';
    }

    if (str_contains($text, 'protein') || str_contains($text, 'meat') || str_contains($text, 'chicken') || str_contains($text, 'fish') || str_contains($text, 'beef')) {
        $tags[] = 'High Protein';
    }

    if (str_contains($text, 'soup') || str_contains($text, 'stew') || str_contains($text, 'broth')) {
        $tags[] = 'Comfort Food';
    }

    if (str_contains($text, 'street food') || str_contains($text, 'snack')) {
        $tags[] = 'Street Food';
    }

    if (str_contains($text, 'festive') || str_contains($text, 'celebration') || str_contains($text, 'ceremonial')) {
        $tags[] = 'Festive';
    }

    return $tags;
}

/**
 * Add user feedback to meals array
 */
function addUserFeedbackToMeals($meals, $userId) {
    if (empty($meals)) return $meals;

    global $conn;

    $mealIds = array_map(function($meal) { return $meal['id']; }, $meals);
    $placeholders = str_repeat('?,', count($mealIds) - 1) . '?';

    $stmt = $conn->prepare("
        SELECT recommendation_id, feedback_type
        FROM recommendation_feedback
        WHERE user_id = ? AND recommendation_id IN ($placeholders)
    ");
    $stmt->bind_param("i" . str_repeat("i", count($mealIds)), $userId, ...$mealIds);
    $stmt->execute();
    $result = $stmt->get_result();

    $userFeedback = [];
    while ($row = $result->fetch_assoc()) {
        $userFeedback[$row['recommendation_id']] = $row['feedback_type'];
    }

    // Add user feedback to each meal
    foreach ($meals as &$meal) {
        $meal['user_feedback'] = $userFeedback[$meal['id']] ?? null;
    }

    return $meals;
}

/**
 * Add user feedback to single meal
 */
function addUserFeedbackToMeal($meal, $userId) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT feedback_type
        FROM recommendation_feedback
        WHERE user_id = ? AND recommendation_id = ?
    ");
    $stmt->bind_param("ii", $userId, $meal['id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $meal['user_feedback'] = $row['feedback_type'];
    } else {
        $meal['user_feedback'] = null;
    }

    return $meal;
}

/**
 * Get meal feedback counts
 */
function getMealFeedbackCounts($mealId) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT
            COUNT(CASE WHEN feedback_type = 'like' THEN 1 END) as likes,
            COUNT(CASE WHEN feedback_type = 'dislike' THEN 1 END) as dislikes
        FROM recommendation_feedback
        WHERE recommendation_id = ?
    ");
    $stmt->bind_param("i", $mealId);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

/**
 * Log mood-based recommendation view
 */
function logMoodBasedRecommendationView($userId, $sourceEmotion, $targetEmotion, $meals) {
    global $conn;

    // Get latest emotion ID
    $stmt = $conn->prepare("SELECT id FROM emotions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $emotionId = $result->fetch_assoc()['id'];

        // Log recommendation views
        foreach ($meals as $meal) {
            logRecommendationView($userId, $emotionId, $meal['id']);
        }
    }
}

?>