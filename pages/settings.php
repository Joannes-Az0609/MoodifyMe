<?php
/**
 * MoodifyMe - User Settings Page
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

// Initialize variables
$success = '';
$error = '';

// Get user preferences
$preferences = [];
$stmt = $conn->prepare("SELECT preference_key, preference_value FROM user_preferences WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $preferences[$row['preference_key']] = $row['preference_value'];
}

// Set default preferences if not set
$defaultPreferences = [
    'email_notifications' => 'on',
    'recommendation_frequency' => 'daily',
    'theme' => 'light',
    'privacy_mode' => 'off',
    'language' => 'en'
];

foreach ($defaultPreferences as $key => $value) {
    if (!isset($preferences[$key])) {
        $preferences[$key] = $value;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_preferences'])) {
        // Get form data
        $emailNotifications = isset($_POST['email_notifications']) ? 'on' : 'off';
        $recommendationFrequency = sanitizeInput($_POST['recommendation_frequency']);
        $theme = sanitizeInput($_POST['theme']);
        $privacyMode = isset($_POST['privacy_mode']) ? 'on' : 'off';
        $language = sanitizeInput($_POST['language']);
        
        // Update preferences
        $preferencesToUpdate = [
            'email_notifications' => $emailNotifications,
            'recommendation_frequency' => $recommendationFrequency,
            'theme' => $theme,
            'privacy_mode' => $privacyMode,
            'language' => $language
        ];
        
        $updateSuccess = true;
        
        foreach ($preferencesToUpdate as $key => $value) {
            // Check if preference exists
            $stmt = $conn->prepare("SELECT id FROM user_preferences WHERE user_id = ? AND preference_key = ?");
            $stmt->bind_param("is", $userId, $key);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing preference
                $stmt = $conn->prepare("UPDATE user_preferences SET preference_value = ?, updated_at = NOW() WHERE user_id = ? AND preference_key = ?");
                $stmt->bind_param("sis", $value, $userId, $key);
            } else {
                // Insert new preference
                $stmt = $conn->prepare("INSERT INTO user_preferences (user_id, preference_key, preference_value, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("iss", $userId, $key, $value);
            }
            
            if (!$stmt->execute()) {
                $updateSuccess = false;
                break;
            }
        }
        
        if ($updateSuccess) {
            $success = 'Settings saved successfully.';
            
            // Update preferences array
            $preferences = $preferencesToUpdate;
        } else {
            $error = 'Failed to save settings.';
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title">Settings</h2>
                    <p class="card-text">Customize your MoodifyMe experience.</p>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="nav flex-column nav-pills" id="settings-tab" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="pill" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">
                            <i class="fas fa-cog"></i> General
                        </button>
                        <button class="nav-link" id="notifications-tab" data-bs-toggle="pill" data-bs-target="#notifications" type="button" role="tab" aria-controls="notifications" aria-selected="false">
                            <i class="fas fa-bell"></i> Notifications
                        </button>
                        <button class="nav-link" id="privacy-tab" data-bs-toggle="pill" data-bs-target="#privacy" type="button" role="tab" aria-controls="privacy" aria-selected="false">
                            <i class="fas fa-user-shield"></i> Privacy
                        </button>
                        <button class="nav-link" id="appearance-tab" data-bs-toggle="pill" data-bs-target="#appearance" type="button" role="tab" aria-controls="appearance" aria-selected="false">
                            <i class="fas fa-palette"></i> Appearance
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Need Help?</h5>
                    <p class="card-text">If you have any questions about your settings, please contact our support team.</p>
                    <a href="<?php echo APP_URL; ?>/pages/contact.php" class="btn btn-outline-primary">
                        <i class="fas fa-envelope"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="tab-content" id="settings-tabContent">
                            <!-- General Settings -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                <h3>General Settings</h3>
                                
                                <div class="mb-3">
                                    <label for="language" class="form-label">Language</label>
                                    <select class="form-select" id="language" name="language">
                                        <option value="en" <?php echo $preferences['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                                        <option value="es" <?php echo $preferences['language'] === 'es' ? 'selected' : ''; ?>>Spanish</option>
                                        <option value="fr" <?php echo $preferences['language'] === 'fr' ? 'selected' : ''; ?>>French</option>
                                        <option value="de" <?php echo $preferences['language'] === 'de' ? 'selected' : ''; ?>>German</option>
                                        <option value="zh" <?php echo $preferences['language'] === 'zh' ? 'selected' : ''; ?>>Chinese</option>
                                    </select>
                                    <div class="form-text">Choose your preferred language for the application interface.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="recommendation_frequency" class="form-label">Recommendation Frequency</label>
                                    <select class="form-select" id="recommendation_frequency" name="recommendation_frequency">
                                        <option value="daily" <?php echo $preferences['recommendation_frequency'] === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                        <option value="weekly" <?php echo $preferences['recommendation_frequency'] === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                        <option value="monthly" <?php echo $preferences['recommendation_frequency'] === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                        <option value="never" <?php echo $preferences['recommendation_frequency'] === 'never' ? 'selected' : ''; ?>>Never</option>
                                    </select>
                                    <div class="form-text">How often would you like to receive new recommendations?</div>
                                </div>
                            </div>
                            
                            <!-- Notification Settings -->
                            <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                                <h3>Notification Settings</h3>
                                
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" <?php echo $preferences['email_notifications'] === 'on' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="email_notifications">Email Notifications</label>
                                    <div class="form-text">Receive email notifications about new recommendations and features.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <h5>Notification Types</h5>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notify_recommendations" name="notify_recommendations" checked disabled>
                                        <label class="form-check-label" for="notify_recommendations">New Recommendations</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notify_mood_reminders" name="notify_mood_reminders" checked disabled>
                                        <label class="form-check-label" for="notify_mood_reminders">Mood Check Reminders</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notify_system" name="notify_system" checked disabled>
                                        <label class="form-check-label" for="notify_system">System Updates</label>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Detailed notification settings will be available in a future update.
                                </div>
                            </div>
                            
                            <!-- Privacy Settings -->
                            <div class="tab-pane fade" id="privacy" role="tabpanel" aria-labelledby="privacy-tab">
                                <h3>Privacy Settings</h3>
                                
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="privacy_mode" name="privacy_mode" <?php echo $preferences['privacy_mode'] === 'on' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="privacy_mode">Enhanced Privacy Mode</label>
                                    <div class="form-text">When enabled, your mood data will be anonymized for system analytics.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <h5>Data Usage</h5>
                                    <p>MoodifyMe uses your data to provide personalized recommendations. You can control how your data is used:</p>
                                    
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="data_recommendations" name="data_recommendations" checked disabled>
                                        <label class="form-check-label" for="data_recommendations">Use my data for personalized recommendations</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="data_improvement" name="data_improvement" checked disabled>
                                        <label class="form-check-label" for="data_improvement">Use my data to improve the system</label>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Detailed privacy settings will be available in a future update.
                                </div>
                            </div>
                            
                            <!-- Appearance Settings -->
                            <div class="tab-pane fade" id="appearance" role="tabpanel" aria-labelledby="appearance-tab">
                                <h3>Appearance Settings</h3>
                                
                                <div class="mb-3">
                                    <label for="theme" class="form-label">Theme</label>
                                    <select class="form-select" id="theme" name="theme">
                                        <option value="light" <?php echo $preferences['theme'] === 'light' ? 'selected' : ''; ?>>Light</option>
                                        <option value="dark" <?php echo $preferences['theme'] === 'dark' ? 'selected' : ''; ?>>Dark</option>
                                        <option value="auto" <?php echo $preferences['theme'] === 'auto' ? 'selected' : ''; ?>>Auto (follow system)</option>
                                    </select>
                                    <div class="form-text">Choose your preferred color theme for the application.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <h5>Preview</h5>
                                    <div class="theme-preview p-3 border rounded mb-3" id="theme-preview">
                                        <h4>Theme Preview</h4>
                                        <p>This is how the application will look with your selected theme.</p>
                                        <button class="btn btn-primary">Primary Button</button>
                                        <button class="btn btn-secondary">Secondary Button</button>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> More appearance customization options will be available in a future update.
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 d-flex justify-content-between">
                            <button type="reset" class="btn btn-outline-secondary">Reset Changes</button>
                            <button type="submit" name="save_preferences" class="btn btn-primary">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced theme functionality
document.addEventListener('DOMContentLoaded', function() {
    const themeSelect = document.getElementById('theme');
    const themePreview = document.getElementById('theme-preview');

    // Get current theme from the main theme system
    function getCurrentTheme() {
        return document.documentElement.getAttribute('data-theme') || 'light';
    }

    // Set theme using the main theme system
    function setTheme(theme) {
        if (theme === 'auto') {
            // Remove saved preference to use system default
            localStorage.removeItem('moodifyme-theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-theme', systemPrefersDark ? 'dark' : 'light');
        } else {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('moodifyme-theme', theme);
        }

        // Dispatch theme change event
        window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));
    }

    function updateThemePreview() {
        const selectedTheme = themeSelect.value;

        // Remove existing theme classes
        themePreview.classList.remove('theme-light', 'theme-dark');

        // Add selected theme class
        if (selectedTheme === 'light') {
            themePreview.classList.add('theme-light');
            themePreview.style.backgroundColor = '#ffffff';
            themePreview.style.color = '#212529';
        } else if (selectedTheme === 'dark') {
            themePreview.classList.add('theme-dark');
            themePreview.style.backgroundColor = '#1a1a1a';
            themePreview.style.color = '#f8f9fa';
        } else {
            // Auto theme - use system preference
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                themePreview.classList.add('theme-dark');
                themePreview.style.backgroundColor = '#1a1a1a';
                themePreview.style.color = '#f8f9fa';
            } else {
                themePreview.classList.add('theme-light');
                themePreview.style.backgroundColor = '#ffffff';
                themePreview.style.color = '#212529';
            }
        }
    }

    // Update preview when theme changes
    themeSelect.addEventListener('change', function() {
        updateThemePreview();
        // Apply theme immediately for live preview
        setTheme(themeSelect.value);
    });

    // Initialize theme select with current value
    const savedTheme = localStorage.getItem('moodifyme-theme');
    if (savedTheme) {
        themeSelect.value = savedTheme;
    } else {
        themeSelect.value = 'auto';
    }

    // Initialize preview
    updateThemePreview();
    
    // Initial update
    updateThemePreview();
});
</script>

<?php
// Include footer
include '../includes/footer.php';
?>
