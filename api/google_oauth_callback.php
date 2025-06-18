<?php
/**
 * MoodifyMe - Google OAuth Callback Handler
 * Handles the callback from Google OAuth and processes user authentication
 */

require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';
require_once '../includes/google_oauth.php';

// Start session
session_start();

// Check for error from Google
if (isset($_GET['error'])) {
    $error = $_GET['error'];
    $errorDescription = $_GET['error_description'] ?? 'Unknown error occurred';
    
    // Log the error
    error_log("Google OAuth Error: $error - $errorDescription");
    
    // Redirect to login with error message
    $errorMsg = urlencode('Google sign-in was cancelled or failed. Please try again.');
    redirect(APP_URL . '/pages/login.php?error=' . $errorMsg);
    exit;
}

// Check for required parameters
if (!isset($_GET['code']) || !isset($_GET['state'])) {
    redirect(APP_URL . '/pages/login.php?error=' . urlencode('Invalid OAuth callback parameters'));
    exit;
}

$code = $_GET['code'];
$state = $_GET['state'];

// Verify state to prevent CSRF attacks
if (!verifyOAuthState($state)) {
    redirect(APP_URL . '/pages/login.php?error=' . urlencode('Invalid OAuth state. Please try again.'));
    exit;
}

try {
    // Exchange authorization code for access token
    $tokenData = exchangeCodeForToken($code);
    
    if (!$tokenData || !isset($tokenData['access_token'])) {
        throw new Exception('Failed to obtain access token from Google');
    }
    
    // Get user information from Google
    $googleUser = getGoogleUserInfo($tokenData['access_token']);
    
    if (!$googleUser || !isset($googleUser['id'])) {
        throw new Exception('Failed to obtain user information from Google');
    }
    
    // Validate required user data
    if (empty($googleUser['email'])) {
        throw new Exception('Google account does not have a valid email address');
    }
    
    // Create or update user in database
    $user = createOrUpdateGoogleUser($googleUser, $tokenData);
    
    if (!$user) {
        throw new Exception('Failed to create or update user account');
    }
    
    // Set user session
    setOAuthUserSession($user);
    
    // Determine redirect URL
    $redirectUrl = APP_URL . '/pages/dashboard.php';
    
    // Check if there's a return URL in session
    if (isset($_SESSION['oauth_return_url'])) {
        $redirectUrl = $_SESSION['oauth_return_url'];
        unset($_SESSION['oauth_return_url']);
    }
    
    // Success! Redirect to dashboard or return URL
    redirect($redirectUrl . '?welcome=google');
    
} catch (Exception $e) {
    // Log the error
    error_log("Google OAuth Callback Error: " . $e->getMessage());
    
    // Redirect to login with error message
    $errorMsg = urlencode('Google sign-in failed: ' . $e->getMessage());
    redirect(APP_URL . '/pages/login.php?error=' . $errorMsg);
}
?>
