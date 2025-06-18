<?php
/**
 * MoodifyMe - Authentication Check
 * Ensures user is logged in before accessing protected pages
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: " . APP_URL . "/pages/login.php");
    exit;
}

// Optional: Check if session is still valid (you can add session timeout logic here)
// For example, check if session has expired after certain time
/*
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
    // Session expired after 1 hour
    session_unset();
    session_destroy();
    header("Location: " . APP_URL . "/pages/login.php?expired=1");
    exit;
}
$_SESSION['last_activity'] = time(); // Update last activity time
*/
?>
