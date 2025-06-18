<?php
/**
 * MoodifyMe - Login Page
 */

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';
require_once '../includes/google_oauth.php';

// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to dashboard
    redirect(APP_URL . '/pages/dashboard.php');
}

// Initialize variables
$email = '';
$password = '';
$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    // Validate form data
    if (empty($email)) {
        $error = 'Email is required.';
    } elseif (empty($password)) {
        $error = 'Password is required.';
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (verifyPassword($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];

                // Redirect to dashboard
                redirect(APP_URL . '/pages/dashboard.php');
            } else {
                $error = 'Invalid password.';
            }
        } else {
            $error = 'User not found.';
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-background">
        <div class="auth-pattern"></div>
    </div>

    <div class="container-fluid h-100">
        <div class="row h-100 align-items-center">
            <!-- Left Side - Branding -->
            <div class="col-lg-6 d-none d-lg-block auth-branding">
                <div class="branding-content">
                    <div class="brand-logo">
                        <img src="../assets/images/logo.png" alt="MoodifyMe" class="logo-large">
                        <h1 class="brand-title">MoodifyMe</h1>
                    </div>
                    <h2 class="brand-subtitle">Transform Your Mood with African Culture</h2>
                    <p class="brand-description">
                        Discover the perfect blend of African meals, music, and movies to elevate your emotional well-being.
                        Join thousands who have found their path to better mental health through cultural experiences.
                    </p>
                    <div class="feature-highlights">
                        <div class="feature-item">
                            <i class="fas fa-utensils feature-icon"></i>
                            <span>Authentic African Meals</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-music feature-icon"></i>
                            <span>Curated Music Playlists</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-film feature-icon"></i>
                            <span>Movie Recommendations</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="col-lg-6 col-12">
                <div class="auth-form-container">
                    <div class="auth-form-modern">
                        <div class="form-header">
                            <h1 class="form-title">Welcome Back!</h1>
                            <p class="form-subtitle">Sign in to continue your mood journey</p>
                        </div>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-modern">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="auth-form-fields">
                            <div class="form-group-modern">
                                <div class="input-wrapper">
                                    <input type="email" class="form-control-modern" id="email" name="email" value="<?php echo $email; ?>" required>
                                    <label for="email" class="floating-label">Email Address</label>
                                    <div class="input-border"></div>
                                </div>
                                <div class="form-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>

                            <div class="form-group-modern">
                                <div class="input-wrapper">
                                    <input type="password" class="form-control-modern" id="password" name="password" required>
                                    <label for="password" class="floating-label">Password</label>
                                    <div class="input-border"></div>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password-eye"></i>
                                    </button>
                                </div>
                                <div class="form-feedback">
                                    Please enter your password.
                                </div>
                            </div>

                            <div class="form-options">
                                <div class="custom-checkbox">
                                    <input type="checkbox" id="remember" name="remember">
                                    <label for="remember">
                                        <span class="checkmark"></span>
                                        Remember me
                                    </label>
                                </div>
                                <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
                            </div>

                            <button type="submit" class="btn-auth-primary">
                                <span class="btn-text">Sign In</span>
                                <i class="fas fa-arrow-right btn-icon"></i>
                            </button>
                        </form>

                        <!-- Social Login Section -->
                        <div class="social-login-section">
                            <div class="divider">
                                <span>or</span>
                            </div>

                            <a href="<?php echo getGoogleOAuthURL(); ?>" class="btn-google-signin">
                                <svg class="google-icon" viewBox="0 0 24 24" width="20" height="20">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                <span>Continue with Google</span>
                            </a>
                        </div>

                        <div class="auth-footer">
                            <p class="auth-switch">
                                Don't have an account?
                                <a href="register.php" class="auth-link">Create Account</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Authentication Styles */
.auth-container {
    min-height: 100vh;
    position: relative;
    overflow: hidden;
}

.auth-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #E55100 0%, #D32F2F 30%, #FF8F00 70%, #FFC107 100%);
    z-index: -2;
}

.auth-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image:
        radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
    background-size: 100px 100px;
    animation: patternMove 20s ease-in-out infinite;
    z-index: -1;
}

@keyframes patternMove {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(20px, 20px); }
}

/* Branding Section */
.auth-branding {
    padding: 3rem;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
}

.branding-content {
    max-width: 500px;
    text-align: center;
}

.brand-logo {
    margin-bottom: 2rem;
}

.logo-large {
    width: 80px;
    height: 80px;
    margin-bottom: 1rem;
    filter: brightness(0) invert(1);
}

.brand-title {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    background: linear-gradient(45deg, #fff, rgba(255, 255, 255, 0.8));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.brand-subtitle {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    opacity: 0.9;
}

.brand-description {
    font-size: 1.1rem;
    line-height: 1.6;
    margin-bottom: 2rem;
    opacity: 0.8;
}

.feature-highlights {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.feature-item {
    display: flex;
    align-items: center;
    font-size: 1.1rem;
    font-weight: 500;
}

.feature-icon {
    width: 24px;
    margin-right: 1rem;
    color: #8D6E63;
}

/* Form Container */
.auth-form-container {
    padding: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
}

.auth-form-modern {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: var(--radius-3xl);
    padding: 3rem;
    width: 100%;
    max-width: 450px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.form-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.form-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--neutral-900);
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, #E55100, #D32F2F);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.form-subtitle {
    font-size: 1.1rem;
    color: var(--neutral-600);
    margin-bottom: 0;
}

/* Modern Form Fields */
.auth-form-fields {
    margin-bottom: 2rem;
}

.form-group-modern {
    margin-bottom: 2rem;
    position: relative;
}

.input-wrapper {
    position: relative;
}

.form-control-modern {
    width: 100%;
    padding: 1rem 1rem 1rem 0;
    font-size: 1.1rem;
    border: none;
    border-bottom: 2px solid var(--neutral-300);
    background: transparent;
    outline: none;
    transition: all 0.3s ease;
    color: var(--neutral-800);
}

.form-control-modern:focus {
    border-bottom-color: #E55100;
}

.floating-label {
    position: absolute;
    top: 1rem;
    left: 0;
    font-size: 1.1rem;
    color: var(--neutral-500);
    pointer-events: none;
    transition: all 0.3s ease;
}

.form-control-modern:focus + .floating-label,
.form-control-modern:not(:placeholder-shown) + .floating-label {
    top: -0.5rem;
    font-size: 0.9rem;
    color: #E55100;
    font-weight: 600;
}

.input-border {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(135deg, #E55100, #FF8F00);
    transition: width 0.3s ease;
}

.form-control-modern:focus ~ .input-border {
    width: 100%;
}

.password-toggle {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--neutral-500);
    cursor: pointer;
    padding: 0.5rem;
    transition: color 0.3s ease;
}

.password-toggle:hover {
    color: #E55100;
}

.form-feedback {
    font-size: 0.9rem;
    color: var(--danger-color);
    margin-top: 0.5rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.form-control-modern:invalid + .floating-label + .input-border + .password-toggle + .form-feedback,
.form-control-modern:invalid + .floating-label + .input-border + .form-feedback {
    opacity: 1;
}

/* Form Options */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.custom-checkbox {
    display: flex;
    align-items: center;
}

.custom-checkbox input[type="checkbox"] {
    display: none;
}

.custom-checkbox label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 1rem;
    color: var(--neutral-700);
}

.checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid var(--neutral-400);
    border-radius: var(--radius);
    margin-right: 0.75rem;
    position: relative;
    transition: all 0.3s ease;
}

.custom-checkbox input[type="checkbox"]:checked + label .checkmark {
    background: linear-gradient(135deg, #E55100, #FF8F00);
    border-color: #E55100;
}

.custom-checkbox input[type="checkbox"]:checked + label .checkmark::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.forgot-link {
    color: #E55100;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.forgot-link:hover {
    color: #D32F2F;
}
</style>

<style>
/* Auth Button */
.btn-auth-primary {
    width: 100%;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #E55100, #D32F2F);
    border: none;
    border-radius: var(--radius-xl);
    color: white;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-auth-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(229, 81, 0, 0.4);
    background: linear-gradient(135deg, #FF8F00, #E55100);
}

.btn-auth-primary:active {
    transform: translateY(0);
}

.btn-icon {
    transition: transform 0.3s ease;
}

.btn-auth-primary:hover .btn-icon {
    transform: translateX(5px);
}

/* Social Login Styles */
.social-login-section {
    margin: 25px 0;
}

.divider {
    position: relative;
    text-align: center;
    margin: 20px 0;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: rgba(255, 255, 255, 0.2);
}

.divider span {
    background: rgba(255, 255, 255, 0.1);
    padding: 0 15px;
    color: rgba(255, 255, 255, 0.7);
    font-size: 14px;
    position: relative;
    backdrop-filter: blur(10px);
    border-radius: 15px;
}

.btn-google-signin {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 12px 20px;
    background: #ffffff;
    color: #333333;
    border: 1px solid #dadce0;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    font-size: 16px;
    transition: all 0.3s ease;
    gap: 12px;
}

.btn-google-signin:hover {
    background: #f8f9fa;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    color: #333333;
    transform: translateY(-1px);
}

.btn-google-signin:active {
    transform: translateY(0);
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.google-icon {
    flex-shrink: 0;
}

/* Alert Styling */
.alert-modern {
    border: none;
    border-radius: var(--radius-lg);
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    background: rgba(220, 53, 69, 0.1);
    color: var(--danger-color);
    border-left: 4px solid var(--danger-color);
}

/* Auth Footer */
.auth-footer {
    text-align: center;
    padding-top: 2rem;
    border-top: 1px solid var(--neutral-200);
}

.auth-switch {
    margin: 0;
    color: var(--neutral-600);
    font-size: 1rem;
}

.auth-link {
    color: #E55100;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.auth-link:hover {
    color: #D32F2F;
}

/* Responsive Design */
@media (max-width: 991px) {
    .auth-form-container {
        padding: 1rem;
    }

    .auth-form-modern {
        padding: 2rem;
    }

    .form-title {
        font-size: 2rem;
    }
}

@media (max-width: 576px) {
    .auth-form-modern {
        padding: 1.5rem;
    }

    .form-title {
        font-size: 1.75rem;
    }

    .form-options {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
}
</style>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const eye = document.getElementById(inputId + '-eye');

    if (input.type === 'password') {
        input.type = 'text';
        eye.classList.remove('fa-eye');
        eye.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        eye.classList.remove('fa-eye-slash');
        eye.classList.add('fa-eye');
    }
}

// Form validation and animations
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.auth-form-fields');
    const inputs = document.querySelectorAll('.form-control-modern');

    // Add placeholder attribute for floating labels
    inputs.forEach(input => {
        input.setAttribute('placeholder', ' ');
    });

    // Form submission handling
    form.addEventListener('submit', function(e) {
        let isValid = true;

        inputs.forEach(input => {
            if (input.hasAttribute('required') && !input.value.trim()) {
                isValid = false;
                input.classList.add('invalid');
            } else {
                input.classList.remove('invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
        }
    });
});
</script>

<?php
// Include footer
include '../includes/footer.php';
?>
