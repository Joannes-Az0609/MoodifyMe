<?php
/**
 * MoodifyMe - Google OAuth Helper Functions
 * Handles Google OAuth authentication flow
 */

/**
 * Generate Google OAuth login URL
 * @return string Google OAuth authorization URL
 */
function getGoogleOAuthURL() {
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'scope' => 'openid email profile',
        'response_type' => 'code',
        'access_type' => 'offline',
        'prompt' => 'consent',
        'state' => generateOAuthState()
    ];
    
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

/**
 * Generate and store OAuth state for CSRF protection
 * @return string Random state string
 */
function generateOAuthState() {
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth_state'] = $state;
    return $state;
}

/**
 * Verify OAuth state to prevent CSRF attacks
 * @param string $state State parameter from OAuth callback
 * @return bool True if state is valid
 */
function verifyOAuthState($state) {
    return isset($_SESSION['oauth_state']) && 
           hash_equals($_SESSION['oauth_state'], $state);
}

/**
 * Exchange authorization code for access token
 * @param string $code Authorization code from Google
 * @return array|false Token data or false on failure
 */
function exchangeCodeForToken($code) {
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    
    $postData = [
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code',
        'code' => $code
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        return json_decode($response, true);
    }
    
    return false;
}

/**
 * Get user info from Google using access token
 * @param string $accessToken Google access token
 * @return array|false User data or false on failure
 */
function getGoogleUserInfo($accessToken) {
    $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        return json_decode($response, true);
    }
    
    return false;
}

/**
 * Create or update user from Google OAuth data
 * @param array $googleUser Google user data
 * @param array $tokenData OAuth token data
 * @return array|false User data or false on failure
 */
function createOrUpdateGoogleUser($googleUser, $tokenData) {
    global $conn;
    
    $googleId = $googleUser['id'];
    $email = $googleUser['email'];
    $name = $googleUser['name'];
    $picture = $googleUser['picture'] ?? null;
    $emailVerified = $googleUser['verified_email'] ?? false;
    
    // Check if user already exists by Google ID
    $stmt = $conn->prepare("SELECT * FROM users WHERE google_id = ?");
    $stmt->bind_param("s", $googleId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing user
        $user = $result->fetch_assoc();
        $userId = $user['id'];
        
        $stmt = $conn->prepare("
            UPDATE users 
            SET email = ?, avatar_url = ?, email_verified = ?, last_login = NOW() 
            WHERE id = ?
        ");
        $stmt->bind_param("ssii", $email, $picture, $emailVerified, $userId);
        $stmt->execute();
        
    } else {
        // Check if user exists by email
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Link existing account to Google
            $user = $result->fetch_assoc();
            $userId = $user['id'];
            
            $stmt = $conn->prepare("
                UPDATE users 
                SET google_id = ?, oauth_provider = 'google', account_type = 'google', 
                    avatar_url = ?, email_verified = ?, last_login = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("ssii", $googleId, $picture, $emailVerified, $userId);
            $stmt->execute();
            
        } else {
            // Create new user
            $username = generateUniqueUsername($name, $email);
            
            $stmt = $conn->prepare("
                INSERT INTO users (username, email, google_id, oauth_provider, account_type, 
                                 avatar_url, email_verified, created_at, last_login) 
                VALUES (?, ?, ?, 'google', 'google', ?, ?, NOW(), NOW())
            ");
            $stmt->bind_param("ssssi", $username, $email, $googleId, $picture, $emailVerified);
            $stmt->execute();
            $userId = $conn->insert_id;
        }
    }
    
    // Store/update OAuth tokens
    storeOAuthTokens($userId, 'google', $tokenData);
    
    // Log social login
    logSocialLogin($userId, 'google', $googleId);
    
    // Get updated user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Generate unique username from name and email
 * @param string $name Full name
 * @param string $email Email address
 * @return string Unique username
 */
function generateUniqueUsername($name, $email) {
    global $conn;
    
    // Start with name, fallback to email prefix
    $baseUsername = !empty($name) ? $name : explode('@', $email)[0];
    
    // Clean username (remove spaces, special chars)
    $baseUsername = preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', $baseUsername));
    $baseUsername = strtolower(substr($baseUsername, 0, 20));
    
    $username = $baseUsername;
    $counter = 1;
    
    // Check if username exists and increment if needed
    while (true) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            break;
        }
        
        $username = $baseUsername . '_' . $counter;
        $counter++;
    }
    
    return $username;
}

/**
 * Store OAuth tokens for user
 * @param int $userId User ID
 * @param string $provider OAuth provider
 * @param array $tokenData Token data
 */
function storeOAuthTokens($userId, $provider, $tokenData) {
    global $conn;
    
    $accessToken = $tokenData['access_token'];
    $refreshToken = $tokenData['refresh_token'] ?? null;
    $tokenType = $tokenData['token_type'] ?? 'Bearer';
    $expiresIn = $tokenData['expires_in'] ?? null;
    $scope = $tokenData['scope'] ?? null;
    
    $expiresAt = null;
    if ($expiresIn) {
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);
    }
    
    $stmt = $conn->prepare("
        INSERT INTO oauth_tokens (user_id, provider, access_token, refresh_token, 
                                token_type, expires_at, scope) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        access_token = VALUES(access_token),
        refresh_token = VALUES(refresh_token),
        token_type = VALUES(token_type),
        expires_at = VALUES(expires_at),
        scope = VALUES(scope),
        updated_at = NOW()
    ");
    
    $stmt->bind_param("issssss", $userId, $provider, $accessToken, $refreshToken, 
                     $tokenType, $expiresAt, $scope);
    $stmt->execute();
}

/**
 * Log social login attempt
 * @param int $userId User ID
 * @param string $provider OAuth provider
 * @param string $providerUserId Provider user ID
 */
function logSocialLogin($userId, $provider, $providerUserId) {
    global $conn;
    
    $loginIp = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $stmt = $conn->prepare("
        INSERT INTO social_logins (user_id, provider, provider_user_id, login_ip, user_agent) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $userId, $provider, $providerUserId, $loginIp, $userAgent);
    $stmt->execute();
}

/**
 * Set user session after successful OAuth login
 * @param array $user User data
 */
function setOAuthUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['account_type'] = $user['account_type'];
    $_SESSION['avatar_url'] = $user['avatar_url'];
    
    // Clear OAuth state
    unset($_SESSION['oauth_state']);
}
?>
