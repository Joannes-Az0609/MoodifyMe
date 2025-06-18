<?php
/**
 * MoodifyMe - Bridge Redirect to Mood Options
 * Temporary bridge file to redirect to mood_options.php
 */

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';

// Get parameters from URL
$source = isset($_GET['source']) ? sanitizeInput($_GET['source']) : '';
$target = isset($_GET['target']) ? sanitizeInput($_GET['target']) : '';
$emotionId = isset($_GET['emotion_id']) ? sanitizeInput($_GET['emotion_id']) : '';

// Build redirect URL
$redirectUrl = APP_URL . '/pages/mood_options.php';
$params = [];

if (!empty($source)) {
    $params['source'] = $source;
}
if (!empty($target)) {
    $params['target'] = $target;
}
if (!empty($emotionId)) {
    $params['emotion_id'] = $emotionId;
}

// Add parameters to URL if any exist
if (!empty($params)) {
    $redirectUrl .= '?' . http_build_query($params);
}

// Redirect to mood options page
redirect($redirectUrl);
?>
