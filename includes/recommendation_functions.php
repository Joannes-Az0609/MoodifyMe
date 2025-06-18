<?php
/**
 * MoodifyMe Recommendation Functions
 * 
 * Functions for retrieving and managing recommendations
 */

/**
 * Get recommendations by type
 * 
 * @param string $type Recommendation type (music, movies, african_meals)
 * @param string $sourceEmotion Source emotion
 * @param string $targetEmotion Target emotion
 * @param int $limit Maximum number of recommendations to return
 * @return array Recommendations
 */
function getRecommendationsByType($type, $sourceEmotion, $targetEmotion, $limit = 5) {
    global $conn;
    
    // Sanitize inputs
    $type = $conn->real_escape_string($type);
    $sourceEmotion = $conn->real_escape_string($sourceEmotion);
    $targetEmotion = $conn->real_escape_string($targetEmotion);
    $limit = (int)$limit;
    
    // First try to get exact match for source and target emotions
    $sql = "SELECT * FROM recommendations 
            WHERE type = '$type' 
            AND source_emotion = '$sourceEmotion' 
            AND target_emotion = '$targetEmotion'
            ORDER BY RAND() 
            LIMIT $limit";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $recommendations = [];
        while ($row = $result->fetch_assoc()) {
            $recommendations[] = $row;
        }
        return $recommendations;
    }
    
    // If no exact match, try to get recommendations for just the target emotion
    $sql = "SELECT * FROM recommendations 
            WHERE type = '$type' 
            AND target_emotion = '$targetEmotion'
            ORDER BY RAND() 
            LIMIT $limit";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $recommendations = [];
        while ($row = $result->fetch_assoc()) {
            $recommendations[] = $row;
        }
        return $recommendations;
    }
    
    // If still no results, get any recommendations of this type
    $sql = "SELECT * FROM recommendations 
            WHERE type = '$type' 
            ORDER BY RAND() 
            LIMIT $limit";
    
    $result = $conn->query($sql);
    
    $recommendations = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $recommendations[] = $row;
        }
    }
    
    return $recommendations;
}

/**
 * Get all recommendation types
 * 
 * @return array Recommendation types
 */
function getRecommendationTypes() {
    return REC_TYPES;
}

/**
 * Get recommendations for a specific emotion transition
 * 
 * @param string $sourceEmotion Source emotion
 * @param string $targetEmotion Target emotion
 * @param int $limit Maximum number of recommendations per type
 * @return array Recommendations grouped by type
 */
function getRecommendationsForEmotionTransition($sourceEmotion, $targetEmotion, $limit = 3) {
    $types = getRecommendationTypes();
    $recommendations = [];
    
    foreach ($types as $type => $label) {
        $recommendations[$type] = getRecommendationsByType($type, $sourceEmotion, $targetEmotion, $limit);
    }
    
    return $recommendations;
}
