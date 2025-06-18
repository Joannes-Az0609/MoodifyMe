<?php
/**
 * MoodifyMe - Helper Functions
 * Contains utility functions used throughout the application
 */

/**
 * Sanitize user input
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Generate a secure random token
 * @param int $length Length of the token
 * @return string Random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Hash password securely
 * @param string $password Password to hash
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * @param string $password Password to verify
 * @param string $hash Hash to verify against
 * @return bool True if password matches hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if user is logged in
 * @return bool True if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect to a URL
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Get user data by ID
 * @param int $userId User ID
 * @return array|null User data or null if not found
 */
function getUserById($userId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    return $result->fetch_assoc();
}

/**
 * Log an emotion entry
 * @param int $userId User ID
 * @param string $emotionType Type of emotion
 * @param float $confidence Confidence score
 * @param string $source Source of emotion detection (text, voice, face)
 * @param string $rawInput Raw input data
 * @return int|bool ID of inserted record or false on failure
 */
function logEmotion($userId, $emotionType, $confidence, $source, $rawInput = '') {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO emotions (user_id, emotion_type, confidence, source, raw_input, created_at) 
                           VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isdss", $userId, $emotionType, $confidence, $source, $rawInput);
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    
    return false;
}

/**
 * Get recommendations based on emotion
 * @param string $emotionType Type of emotion
 * @param string $targetEmotion Target emotion to achieve
 * @param array $types Types of recommendations to get
 * @param int $limit Maximum number of recommendations
 * @return array Recommendations
 */
function getRecommendations($emotionType, $targetEmotion, $types = [], $limit = 5) {
    global $conn;
    
    $recommendations = [];
    
    // If no specific types are requested, use all types
    if (empty($types)) {
        $types = array_keys(REC_TYPES);
    }
    
    // Convert types to string for SQL IN clause
    $typesStr = "'" . implode("','", $types) . "'";
    
    $query = "SELECT * FROM recommendations 
              WHERE source_emotion = ? AND target_emotion = ? 
              AND type IN ($typesStr)
              ORDER BY RAND() 
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $emotionType, $targetEmotion, $limit);
    $stmt->execute();
    
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $recommendations[] = $row;
    }
    
    return $recommendations;
}

/**
 * Log a recommendation that was shown to user
 * @param int $userId User ID
 * @param int $emotionId Emotion ID
 * @param int $recommendationId Recommendation ID
 * @return bool Success status
 */
function logRecommendationView($userId, $emotionId, $recommendationId) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO recommendation_logs (user_id, emotion_id, recommendation_id, viewed_at) 
                           VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iii", $userId, $emotionId, $recommendationId);
    
    return $stmt->execute();
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Format string
 * @return string Formatted date
 */
function formatDate($date, $format = 'M j, Y g:i A') {
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}
