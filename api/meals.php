<?php
/**
 * MoodifyMe - Meals API
 * API for meal recommendations based on mood transitions
 *
 * Endpoints:
 * GET /api/meals.php?action=get_by_mood&source=sad&target=happy&limit=5
 * GET /api/meals.php?action=get_by_cuisine&cuisine=italian&limit=10
 * GET /api/meals.php?action=get_by_difficulty&difficulty=easy&limit=10
 * GET /api/meals.php?action=get_random&limit=5
 * GET /api/meals.php?action=search&query=pasta&limit=10
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';

// Start session
session_start();

// Set response header
header('Content-Type: application/json');

// Get action parameter
$action = $_GET['action'] ?? 'get_by_mood';

// Get user ID if logged in
$userId = $_SESSION['user_id'] ?? null;

// Route to appropriate handler
switch ($action) {
    case 'get_by_mood':
        handleGetByMood();
        break;
    case 'get_by_cuisine':
        handleGetByCuisine();
        break;
    case 'get_by_difficulty':
        handleGetByDifficulty();
        break;
    case 'get_random':
        handleGetRandom();
        break;
    case 'search':
        handleSearch();
        break;
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action specified',
            'error_code' => 'INVALID_ACTION',
            'available_actions' => [
                'get_by_mood', 'get_by_cuisine', 'get_by_difficulty', 
                'get_random', 'search'
            ]
        ]);
        break;
}

/**
 * Get meals by mood transition
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
    $meals = getMealsByMood($sourceEmotion, $targetEmotion, $limit, $offset);

    // Log recommendation views if user is authenticated
    if ($userId && !empty($meals)) {
        logMoodBasedRecommendationView($userId, $sourceEmotion, $targetEmotion, 'meals');
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
 * Get meals by cuisine type
 */
function handleGetByCuisine() {
    global $conn;

    $cuisine = $_GET['cuisine'] ?? '';
    $limit = min(intval($_GET['limit'] ?? 10), 50);
    $page = max(intval($_GET['page'] ?? 1), 1);
    $offset = ($page - 1) * $limit;

    if (empty($cuisine)) {
        echo json_encode([
            'success' => false,
            'message' => 'Cuisine type is required',
            'error_code' => 'MISSING_CUISINE'
        ]);
        return;
    }

    $meals = getMealsByCuisine($cuisine, $limit, $offset);

    echo json_encode([
        'success' => true,
        'data' => $meals,
        'meta' => [
            'cuisine' => $cuisine,
            'page' => $page,
            'limit' => $limit,
            'total_results' => count($meals)
        ]
    ]);
}

/**
 * Get meals by difficulty level
 */
function handleGetByDifficulty() {
    global $conn;

    $difficulty = $_GET['difficulty'] ?? '';
    $limit = min(intval($_GET['limit'] ?? 10), 50);
    $page = max(intval($_GET['page'] ?? 1), 1);
    $offset = ($page - 1) * $limit;

    if (empty($difficulty)) {
        echo json_encode([
            'success' => false,
            'message' => 'Difficulty level is required',
            'error_code' => 'MISSING_DIFFICULTY'
        ]);
        return;
    }

    $meals = getMealsByDifficulty($difficulty, $limit, $offset);

    echo json_encode([
        'success' => true,
        'data' => $meals,
        'meta' => [
            'difficulty' => $difficulty,
            'page' => $page,
            'limit' => $limit,
            'total_results' => count($meals)
        ]
    ]);
}

/**
 * Get random meals
 */
function handleGetRandom() {
    global $conn;

    $limit = min(intval($_GET['limit'] ?? 5), 20); // Max 20 random results

    $meals = getRandomMeals($limit);

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
 * Search meals by name or ingredients
 */
function handleSearch() {
    global $conn;

    $query = $_GET['query'] ?? '';
    $limit = min(intval($_GET['limit'] ?? 10), 50);
    $page = max(intval($_GET['page'] ?? 1), 1);
    $offset = ($page - 1) * $limit;

    if (empty($query)) {
        echo json_encode([
            'success' => false,
            'message' => 'Search query is required',
            'error_code' => 'MISSING_QUERY'
        ]);
        return;
    }

    $meals = searchMeals($query, $limit, $offset);

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
 * Get meals by mood transition
 */
function getMealsByMood($sourceEmotion, $targetEmotion, $limit, $offset) {
    global $conn;

    // First try exact match
    $stmt = $conn->prepare("
        SELECT r.*,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'like') as likes,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'dislike') as dislikes
        FROM recommendations r
        WHERE r.type = 'meals'
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
        $existingIds = array_column($meals, 'id');

        if ($existingIds) {
            // Use NOT IN with existing IDs
            $placeholders = str_repeat('?,', count($existingIds) - 1) . '?';
            $sql = "
                SELECT r.*,
                       (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'like') as likes,
                       (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'dislike') as dislikes
                FROM recommendations r
                WHERE r.type = 'meals'
                AND r.target_emotion = ?
                AND r.id NOT IN ($placeholders)
                ORDER BY RAND() LIMIT ?";

            $stmt = $conn->prepare($sql);
            $types = 's' . str_repeat('i', count($existingIds)) . 'i';
            $params = array_merge([$targetEmotion], $existingIds, [$remaining]);
            $stmt->bind_param($types, ...$params);
        } else {
            // Simple query without exclusions
            $sql = "
                SELECT r.*,
                       (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'like') as likes,
                       (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'dislike') as dislikes
                FROM recommendations r
                WHERE r.type = 'meals'
                AND r.target_emotion = ?
                ORDER BY RAND() LIMIT ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $targetEmotion, $remaining);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $meals[] = enhanceMealData($row);
        }
    }

    return $meals;
}

/**
 * Get meals by cuisine type
 */
function getMealsByCuisine($cuisine, $limit, $offset) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT r.*,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'like') as likes,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'dislike') as dislikes
        FROM recommendations r
        WHERE r.type = 'meals'
        AND r.cuisine_type LIKE ?
        ORDER BY RAND()
        LIMIT ? OFFSET ?
    ");
    $cuisinePattern = "%{$cuisine}%";
    $stmt->bind_param("sii", $cuisinePattern, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $meals = [];
    while ($row = $result->fetch_assoc()) {
        $meals[] = enhanceMealData($row);
    }

    return $meals;
}

/**
 * Get meals by difficulty level
 */
function getMealsByDifficulty($difficulty, $limit, $offset) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT r.*,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'like') as likes,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'dislike') as dislikes
        FROM recommendations r
        WHERE r.type = 'meals'
        AND r.difficulty = ?
        ORDER BY RAND()
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("sii", $difficulty, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $meals = [];
    while ($row = $result->fetch_assoc()) {
        $meals[] = enhanceMealData($row);
    }

    return $meals;
}

/**
 * Get random meals
 */
function getRandomMeals($limit) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT r.*,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'like') as likes,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'dislike') as dislikes
        FROM recommendations r
        WHERE r.type = 'meals'
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
 * Search meals by name or ingredients
 */
function searchMeals($query, $limit, $offset) {
    global $conn;

    $searchTerm = "%{$query}%";
    $stmt = $conn->prepare("
        SELECT r.*,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'like') as likes,
               (SELECT COUNT(*) FROM recommendation_feedback rf WHERE rf.recommendation_id = r.id AND rf.feedback_type = 'dislike') as dislikes
        FROM recommendations r
        WHERE r.type = 'meals'
        AND (r.title LIKE ? OR r.description LIKE ? OR r.ingredients LIKE ?)
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
 * Enhance meal data with additional formatting
 */
function enhanceMealData($meal) {
    // Parse ingredients if it's a JSON string
    if (!empty($meal['ingredients']) && is_string($meal['ingredients'])) {
        $decoded = json_decode($meal['ingredients'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $meal['ingredients_list'] = $decoded;
        } else {
            // If not JSON, split by newlines or commas
            $meal['ingredients_list'] = array_filter(array_map('trim', preg_split('/[\n,]/', $meal['ingredients'])));
        }
    }

    // Parse dietary tags
    if (!empty($meal['dietary_tags'])) {
        $meal['dietary_tags_list'] = array_filter(array_map('trim', explode(',', $meal['dietary_tags'])));
    }

    // Add cooking time in minutes for sorting
    if (!empty($meal['cooking_time'])) {
        $meal['cooking_time_minutes'] = extractMinutesFromTime($meal['cooking_time']);
    }

    // Add difficulty score for sorting
    $difficultyScores = ['Easy' => 1, 'Medium' => 2, 'Hard' => 3];
    $meal['difficulty_score'] = $difficultyScores[$meal['difficulty']] ?? 2;

    return $meal;
}

/**
 * Extract minutes from cooking time string
 */
function extractMinutesFromTime($timeString) {
    if (preg_match('/(\d+)\s*(?:hours?|hrs?)/i', $timeString, $matches)) {
        $hours = intval($matches[1]);
        $minutes = $hours * 60;

        if (preg_match('/(\d+)\s*(?:minutes?|mins?)/i', $timeString, $minMatches)) {
            $minutes += intval($minMatches[1]);
        }

        return $minutes;
    } elseif (preg_match('/(\d+)\s*(?:minutes?|mins?)/i', $timeString, $matches)) {
        return intval($matches[1]);
    }

    return 30; // Default to 30 minutes if can't parse
}

/**
 * Log mood-based recommendation view
 */
function logMoodBasedRecommendationView($userId, $sourceEmotion, $targetEmotion, $type) {
    global $conn;

    try {
        // Get latest emotion record for this user
        $stmt = $conn->prepare("SELECT id FROM emotions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $emotionId = $result->fetch_assoc()['id'];

            // Log the view (we'll log the first recommendation ID we find, or 0 if none)
            $stmt = $conn->prepare("SELECT id FROM recommendations WHERE type = ? AND source_emotion = ? AND target_emotion = ? LIMIT 1");
            $stmt->bind_param("sss", $type, $sourceEmotion, $targetEmotion);
            $stmt->execute();
            $result = $stmt->get_result();

            $recommendationId = $result->num_rows > 0 ? $result->fetch_assoc()['id'] : 0;

            if ($recommendationId > 0) {
                logRecommendationView($userId, $emotionId, $recommendationId);
            }
        }
    } catch (Exception $e) {
        // Silently fail - logging is not critical
        error_log("Failed to log recommendation view: " . $e->getMessage());
    }
}
?>
