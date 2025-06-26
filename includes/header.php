<?php
/**
 * MoodifyMe - Header Template
 * Common header included in all pages
 */

// Check if user is logged in
$loggedIn = isset($_SESSION['user_id']);

// Get current page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Your Mental Health Assistant</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?php echo APP_URL; ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/responsive.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/theme.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/assets/css/dark-theme.css" rel="stylesheet">

    <!-- African Sunset Theme - Load Last to Override Everything -->
    <link href="<?php echo APP_URL; ?>/assets/css/african-sunset.css" rel="stylesheet">

    <!-- Inline Critical CSS for Navbar -->
    <style>
        /* Critical inline CSS to ensure navbar gets sunset colors and medium size */
        .navbar-dark.bg-primary,
        nav.navbar-dark.bg-primary,
        .navbar.navbar-dark.bg-primary {
            background: linear-gradient(135deg, #E55100 0%, #D32F2F 50%, #FF8F00 100%) !important;
            background-color: #E55100 !important;
            box-shadow: 0 4px 15px rgba(229, 81, 0, 0.3) !important;
            padding: 1rem 0 !important;
            min-height: 65px !important;
        }

        .bg-primary {
            background: linear-gradient(135deg, #E55100, #D32F2F) !important;
            background-color: #E55100 !important;
        }

        .navbar-brand {
            font-size: 1.5rem !important;
            padding: 0.5rem 0 !important;
        }

        .navbar-nav .nav-link {
            font-size: 1rem !important;
            padding: 0.75rem 1rem !important;
        }

        .navbar-brand img {
            height: 35px !important;
            margin-right: 0.5rem !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }

        /* Ensure navbar links are clickable */
        .navbar a,
        .navbar .nav-link,
        .navbar .navbar-brand {
            pointer-events: auto !important;
            cursor: pointer !important;
            z-index: 15 !important;
            position: relative !important;
        }
    </style>

    <!-- Favicon -->
    <link rel="icon" href="<?php echo APP_URL; ?>/assets/images/logo.png" type="image/svg+xml">
    <link rel="icon" href="<?php echo APP_URL; ?>/assets/images/logo.png" type="image/x-icon">
    <link rel="apple-touch-icon" href="<?php echo APP_URL; ?>/assets/images/logo.png">
    <meta name="theme-color" content="#667eea">

    <!-- Animation Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <!-- Theme Initialization Script -->
    <script>
        // Initialize theme before page loads to prevent flash
        (function() {
            const savedTheme = localStorage.getItem('moodifyme-theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            let theme = 'light';
            if (savedTheme) {
                theme = savedTheme;
            } else if (systemPrefersDark) {
                theme = 'dark';
            }

            document.documentElement.setAttribute('data-theme', theme);
        })();

        // Custom dropdown functionality
        function toggleUserDropdown(event) {
            event.preventDefault();
            event.stopPropagation();

            console.log('User dropdown clicked');

            const dropdownMenu = document.getElementById('userDropdownMenu');
            const dropdownToggle = document.getElementById('userDropdown');

            if (dropdownMenu && dropdownToggle) {
                const isVisible = dropdownMenu.style.display === 'block';

                if (isVisible) {
                    dropdownMenu.style.display = 'none';
                    dropdownToggle.setAttribute('aria-expanded', 'false');
                    console.log('Dropdown hidden');
                } else {
                    // Calculate position dynamically
                    const navbar = document.querySelector('.navbar');
                    const toggleRect = dropdownToggle.getBoundingClientRect();
                    const navbarHeight = navbar ? navbar.offsetHeight : 70;

                    // Position the dropdown
                    dropdownMenu.style.top = (navbarHeight + 5) + 'px';
                    dropdownMenu.style.right = (window.innerWidth - toggleRect.right + 10) + 'px';

                    dropdownMenu.style.display = 'block';
                    dropdownToggle.setAttribute('aria-expanded', 'true');
                    console.log('Dropdown shown at position:', {
                        top: dropdownMenu.style.top,
                        right: dropdownMenu.style.right
                    });
                }
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdownMenu = document.getElementById('userDropdownMenu');
            const dropdownToggle = document.getElementById('userDropdown');

            if (dropdownMenu && dropdownToggle) {
                const isClickInsideDropdown = dropdownToggle.contains(event.target) || dropdownMenu.contains(event.target);

                if (!isClickInsideDropdown && dropdownMenu.style.display === 'block') {
                    dropdownMenu.style.display = 'none';
                    dropdownToggle.setAttribute('aria-expanded', 'false');
                    console.log('Dropdown closed by outside click');
                }
            }
        });

        // Reposition dropdown on window resize
        window.addEventListener('resize', function() {
            const dropdownMenu = document.getElementById('userDropdownMenu');
            const dropdownToggle = document.getElementById('userDropdown');

            if (dropdownMenu && dropdownToggle && dropdownMenu.style.display === 'block') {
                const navbar = document.querySelector('.navbar');
                const toggleRect = dropdownToggle.getBoundingClientRect();
                const navbarHeight = navbar ? navbar.offsetHeight : 70;

                dropdownMenu.style.top = (navbarHeight + 5) + 'px';
                dropdownMenu.style.right = (window.innerWidth - toggleRect.right + 10) + 'px';
            }
        });
    </script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo APP_URL; ?>">
                <img src="<?php echo APP_URL; ?>/assets/images/logo.png"
                     alt="<?php echo APP_NAME; ?>"
                     class="d-inline-block navbar-logo"
                     onerror="console.log('Logo failed to load:', this.src); this.style.display='none';"
                     onload="console.log('Logo loaded successfully:', this.src);">
                <span class="fw-bold"><?php echo APP_NAME; ?></span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>">
                            Home
                        </a>
                    </li>

                    <?php if ($loggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/pages/dashboard.php">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage == 'recommendations.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/pages/recommendations.php">
                                Recommendations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage == 'history.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/pages/history.php">
                                History
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'about.php') ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/pages/about.php">
                            About
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <!-- Theme Toggle -->
                    <li class="nav-item">
                        <button class="theme-toggle nav-link" id="themeToggle" title="Toggle Dark Mode">
                            <i class="fas fa-sun sun-icon"></i>
                            <i class="fas fa-moon moon-icon"></i>
                        </button>
                    </li>

                    <?php if ($loggedIn): ?>
                        <li class="nav-item dropdown position-relative">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                               onclick="toggleUserDropdown(event)"
                               style="pointer-events: auto !important; cursor: pointer !important; z-index: 20 !important; position: relative !important;">
                                <i class="fas fa-user-circle"></i>
                                <?php echo $_SESSION['username'] ?? 'User'; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" id="userDropdownMenu" style="display: none;">
                                <li>
                                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/pages/profile.php">
                                        <i class="fas fa-user"></i> Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/pages/settings.php">
                                        <i class="fas fa-cog"></i> Settings
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo APP_URL; ?>/pages/logout.php">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage == 'login.php') ? 'active' : ''; ?>"
                               href="<?php echo APP_URL; ?>/pages/login.php"
                               style="pointer-events: auto !important; cursor: pointer !important; z-index: 20 !important; position: relative !important;">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentPage == 'register.php') ? 'active' : ''; ?>"
                               href="<?php echo APP_URL; ?>/pages/register.php"
                               style="pointer-events: auto !important; cursor: pointer !important; z-index: 20 !important; position: relative !important;">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </li>
                        <!-- Debug info -->
                        <?php if (isset($_GET['debug'])): ?>
                            <li class="nav-item">
                                <span class="nav-link text-warning">
                                    Debug: Not logged in (<?php echo $loggedIn ? 'true' : 'false'; ?>)
                                </span>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="py-4">
