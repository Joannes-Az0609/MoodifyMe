<?php
/**
 * MoodifyMe - Installation Script
 * Sets up the database and initial configuration
 */

// Define constants
define('INSTALL_PATH', __DIR__);
define('CONFIG_FILE', INSTALL_PATH . '/config.php');
define('SCHEMA_FILE', INSTALL_PATH . '/database/schema.sql');
define('SEED_FILE', INSTALL_PATH . '/database/seed.sql');

// Start session
session_start();

// Initialize variables
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['step']) && $_POST['step'] === '1') {
        // Step 1: Database configuration
        $dbHost = trim($_POST['db_host']);
        $dbUser = trim($_POST['db_user']);
        $dbPass = $_POST['db_pass'];
        $dbName = trim($_POST['db_name']);
        
        // Validate input
        if (empty($dbHost) || empty($dbUser) || empty($dbName)) {
            $error = 'All fields except password are required.';
        } else {
            // Test database connection
            try {
                $conn = new mysqli($dbHost, $dbUser, $dbPass);
                
                if ($conn->connect_error) {
                    throw new Exception("Connection failed: " . $conn->connect_error);
                }
                
                // Update config file
                if (file_exists(CONFIG_FILE)) {
                    $configContent = file_get_contents(CONFIG_FILE);
                    
                    // Replace database configuration
                    $configContent = preg_replace('/define\(\'DB_HOST\',\s*\'.*?\'\);/', "define('DB_HOST', '$dbHost');", $configContent);
                    $configContent = preg_replace('/define\(\'DB_USER\',\s*\'.*?\'\);/', "define('DB_USER', '$dbUser');", $configContent);
                    $configContent = preg_replace('/define\(\'DB_PASS\',\s*\'.*?\'\);/', "define('DB_PASS', '$dbPass');", $configContent);
                    $configContent = preg_replace('/define\(\'DB_NAME\',\s*\'.*?\'\);/', "define('DB_NAME', '$dbName');", $configContent);
                    
                    file_put_contents(CONFIG_FILE, $configContent);
                    
                    // Store database credentials in session for next step
                    $_SESSION['db_host'] = $dbHost;
                    $_SESSION['db_user'] = $dbUser;
                    $_SESSION['db_pass'] = $dbPass;
                    $_SESSION['db_name'] = $dbName;
                    
                    // Redirect to step 2
                    header('Location: install.php?step=2');
                    exit;
                } else {
                    $error = 'Config file not found.';
                }
            } catch (Exception $e) {
                $error = 'Database connection error: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['step']) && $_POST['step'] === '2') {
        // Step 2: Create database and tables
        if (isset($_SESSION['db_host']) && isset($_SESSION['db_user']) && isset($_SESSION['db_name'])) {
            $dbHost = $_SESSION['db_host'];
            $dbUser = $_SESSION['db_user'];
            $dbPass = $_SESSION['db_pass'];
            $dbName = $_SESSION['db_name'];
            
            try {
                // Connect to database
                $conn = new mysqli($dbHost, $dbUser, $dbPass);
                
                if ($conn->connect_error) {
                    throw new Exception("Connection failed: " . $conn->connect_error);
                }
                
                // Create database if it doesn't exist
                $conn->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Select database
                $conn->select_db($dbName);
                
                // Execute schema SQL
                if (file_exists(SCHEMA_FILE)) {
                    $schema = file_get_contents(SCHEMA_FILE);
                    $queries = explode(';', $schema);
                    
                    foreach ($queries as $query) {
                        $query = trim($query);
                        
                        if (!empty($query)) {
                            $conn->query($query);
                            
                            if ($conn->error) {
                                throw new Exception("Error executing query: " . $conn->error);
                            }
                        }
                    }
                } else {
                    throw new Exception("Schema file not found.");
                }
                
                // Execute seed SQL if requested
                if (isset($_POST['seed_data']) && $_POST['seed_data'] === '1') {
                    if (file_exists(SEED_FILE)) {
                        $seed = file_get_contents(SEED_FILE);
                        $queries = explode(';', $seed);
                        
                        foreach ($queries as $query) {
                            $query = trim($query);
                            
                            if (!empty($query)) {
                                $conn->query($query);
                                
                                if ($conn->error) {
                                    throw new Exception("Error executing seed query: " . $conn->error);
                                }
                            }
                        }
                    } else {
                        throw new Exception("Seed file not found.");
                    }
                }
                
                // Redirect to step 3
                header('Location: install.php?step=3');
                exit;
            } catch (Exception $e) {
                $error = 'Database setup error: ' . $e->getMessage();
            }
        } else {
            $error = 'Database configuration not found. Please go back to step 1.';
        }
    } elseif (isset($_POST['step']) && $_POST['step'] === '3') {
        // Step 3: Create admin user
        if (isset($_SESSION['db_host']) && isset($_SESSION['db_user']) && isset($_SESSION['db_name'])) {
            $dbHost = $_SESSION['db_host'];
            $dbUser = $_SESSION['db_user'];
            $dbPass = $_SESSION['db_pass'];
            $dbName = $_SESSION['db_name'];
            
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            
            // Validate input
            if (empty($username) || empty($email) || empty($password)) {
                $error = 'All fields are required.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters long.';
            } else {
                try {
                    // Connect to database
                    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
                    
                    if ($conn->connect_error) {
                        throw new Exception("Connection failed: " . $conn->connect_error);
                    }
                    
                    // Hash password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert admin user
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
                    $stmt->bind_param("sss", $username, $email, $hashedPassword);
                    
                    if ($stmt->execute()) {
                        // Installation complete
                        header('Location: install.php?step=4');
                        exit;
                    } else {
                        throw new Exception("Error creating admin user: " . $stmt->error);
                    }
                } catch (Exception $e) {
                    $error = 'Error creating admin user: ' . $e->getMessage();
                }
            }
        } else {
            $error = 'Database configuration not found. Please go back to step 1.';
        }
    }
}

// HTML header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoodifyMe - Installation</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f5f7fa;
            padding-top: 40px;
        }
        .install-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .install-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .install-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-bottom: 3px solid #dee2e6;
        }
        .step.active {
            border-bottom-color: #4e54c8;
            font-weight: bold;
        }
        .step.completed {
            border-bottom-color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="install-container">
            <div class="install-header">
                <h1>MoodifyMe Installation</h1>
                <p>Follow the steps below to set up your MoodifyMe application.</p>
            </div>
            
            <div class="install-steps">
                <div class="step <?php echo ($step >= 1) ? 'active' : ''; ?> <?php echo ($step > 1) ? 'completed' : ''; ?>">
                    <i class="fas fa-database"></i> Database
                </div>
                <div class="step <?php echo ($step >= 2) ? 'active' : ''; ?> <?php echo ($step > 2) ? 'completed' : ''; ?>">
                    <i class="fas fa-table"></i> Tables
                </div>
                <div class="step <?php echo ($step >= 3) ? 'active' : ''; ?> <?php echo ($step > 3) ? 'completed' : ''; ?>">
                    <i class="fas fa-user"></i> Admin
                </div>
                <div class="step <?php echo ($step >= 4) ? 'active' : ''; ?>">
                    <i class="fas fa-check-circle"></i> Complete
                </div>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($step === 1): ?>
                <!-- Step 1: Database Configuration -->
                <h2>Step 1: Database Configuration</h2>
                <p>Enter your database connection details below.</p>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="step" value="1">
                    
                    <div class="mb-3">
                        <label for="db_host" class="form-label">Database Host</label>
                        <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="db_user" class="form-label">Database Username</label>
                        <input type="text" class="form-control" id="db_user" name="db_user" value="root" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="db_pass" class="form-label">Database Password</label>
                        <input type="password" class="form-control" id="db_pass" name="db_pass">
                    </div>
                    
                    <div class="mb-3">
                        <label for="db_name" class="form-label">Database Name</label>
                        <input type="text" class="form-control" id="db_name" name="db_name" value="moodifyme" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Next</button>
                    </div>
                </form>
            <?php elseif ($step === 2): ?>
                <!-- Step 2: Create Database and Tables -->
                <h2>Step 2: Create Database and Tables</h2>
                <p>Now we'll create the database and tables needed for MoodifyMe.</p>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="step" value="2">
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="seed_data" name="seed_data" value="1" checked>
                        <label class="form-check-label" for="seed_data">Include sample data (recommended)</label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Create Database and Tables</button>
                    </div>
                </form>
            <?php elseif ($step === 3): ?>
                <!-- Step 3: Create Admin User -->
                <h2>Step 3: Create Admin User</h2>
                <p>Create your admin user account to manage MoodifyMe.</p>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="step" value="3">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8">
                        <div class="form-text">Password must be at least 8 characters long.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Create Admin User</button>
                    </div>
                </form>
            <?php elseif ($step === 4): ?>
                <!-- Step 4: Installation Complete -->
                <h2>Installation Complete!</h2>
                <div class="alert alert-success">
                    <p><i class="fas fa-check-circle"></i> MoodifyMe has been successfully installed.</p>
                </div>
                
                <p>You can now:</p>
                <ul>
                    <li>Access your MoodifyMe application at <a href="index.php">index.php</a></li>
                    <li>Log in with the admin user you created</li>
                    <li>Start exploring and customizing your application</li>
                </ul>
                
                <div class="alert alert-warning">
                    <p><i class="fas fa-exclamation-triangle"></i> For security reasons, please delete this installation file (install.php) after you've completed the setup.</p>
                </div>
                
                <div class="d-grid gap-2">
                    <a href="index.php" class="btn btn-primary">Go to MoodifyMe</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
