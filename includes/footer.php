    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><?php echo APP_NAME; ?></h5>
                    <p>Your AI-powered emotion-based recommendation system designed to enhance your emotional well-being.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo APP_URL; ?>" class="text-white">Home</a></li>
                        <li><a href="<?php echo APP_URL; ?>/pages/about.php" class="text-white">About</a></li>
                        <li><a href="<?php echo APP_URL; ?>/pages/faq.php" class="text-white">FAQ</a></li>
                        <li><a href="<?php echo APP_URL; ?>/pages/contact.php" class="text-white">Contact</a></li>
                        <li><a href="<?php echo APP_URL; ?>/pages/privacy.php" class="text-white">Privacy Policy</a></li>
                        <li><a href="<?php echo APP_URL; ?>/pages/terms.php" class="text-white">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Connect With Us</h5>
                    <div class="social-icons">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
        <!-- Toasts will be dynamically added here -->
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (required for some components) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Message Notification System -->
    <script>
        // Make APP_URL available to JavaScript
        window.APP_URL = '<?php echo APP_URL; ?>';
    </script>
    <script src="<?php echo APP_URL; ?>/assets/js/message-notifications.js"></script>



    <!-- Custom JavaScript -->
    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>

    <?php
    // Add page-specific scripts based on current page
    $currentPage = basename($_SERVER['PHP_SELF']);

    if (($currentPage == 'index.php' || $currentPage == 'dashboard.php') && isset($_SESSION['user_id'])) {
        echo '<script src="' . APP_URL . '/assets/js/emotion-detection.js"></script>';

    } else if ($currentPage == 'recommendations.php') {
        echo '<script src="' . APP_URL . '/assets/js/recommendations.js"></script>';
    }
    ?>

    <!-- Floating Community Icon -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="floating-community-widget" id="floatingCommunityWidget">
        <!-- Dropup Menu -->
        <div class="community-dropup-menu" id="communityDropupMenu" style="display: none;">
            <!-- Header -->
            <div class="dropup-header">
                <i class="fas fa-users me-2"></i>Community
                <i class="fas fa-chevron-down ms-2"></i>
            </div>

            <!-- Menu Items -->
            <div class="dropup-items">
                <a href="<?php echo APP_URL; ?>/pages/chat_hub.php" class="dropup-item">
                    <i class="fas fa-home me-2"></i>Community Hub
                </a>
                <a href="<?php echo APP_URL; ?>/pages/community_posts.php" class="dropup-item">
                    <i class="fas fa-newspaper me-2"></i>Community Posts
                </a>
                <a href="<?php echo APP_URL; ?>/pages/messages.php" class="dropup-item">
                    <i class="fas fa-envelope me-2"></i>Direct Messages
                </a>
                <a href="<?php echo APP_URL; ?>/pages/connections.php" class="dropup-item">
                    <i class="fas fa-user-friends me-2"></i>My Connections
                </a>
                <a href="<?php echo APP_URL; ?>/pages/user_directory.php" class="dropup-item">
                    <i class="fas fa-search me-2"></i>Find People
                </a>
                <a href="<?php echo APP_URL; ?>/pages/social_profile.php" class="dropup-item">
                    <i class="fas fa-user me-2"></i>My Profile
                </a>
            </div>
        </div>

        <!-- Always Visible Icon -->
        <div class="community-icon-btn" id="communityIconBtn">
            <i class="fas fa-users"></i>
        </div>
    </div>

    <!-- Floating Community Widget Styles -->
    <style>
    .floating-community-widget {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 9999;
    }

    /* Always Visible Icon */
    .community-icon-btn {
        width: 75px;
        height: 75px;
        background: linear-gradient(145deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 30px;
        box-shadow:
            0 10px 30px rgba(102, 126, 234, 0.3),
            0 0 0 1px rgba(255, 255, 255, 0.1) inset,
            0 2px 10px rgba(255, 255, 255, 0.2) inset;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        animation: float 3s ease-in-out infinite;
        cursor: pointer;
        position: relative;
        z-index: 10;
        border: 2px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
    }

    .community-icon-btn:hover {
        transform: scale(1.15) rotate(5deg);
        box-shadow:
            0 15px 40px rgba(102, 126, 234, 0.5),
            0 0 0 1px rgba(255, 255, 255, 0.2) inset,
            0 2px 15px rgba(255, 255, 255, 0.3) inset;
        animation: none;
        background: linear-gradient(145deg, #5a67d8 0%, #6b46c1 50%, #ec4899 100%);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .community-icon-btn:active {
        transform: scale(1.05) rotate(2deg);
        transition: all 0.1s ease;
    }

    /* Dropup Menu */
    .community-dropup-menu {
        position: absolute;
        bottom: 80px;
        right: 0;
        width: 250px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        transform: translateY(20px) scale(0.8);
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        transform-origin: bottom right;
    }

    .community-dropup-menu.show {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    .dropup-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 15px 20px;
        font-weight: 600;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dropup-items {
        padding: 8px 0;
        max-height: 300px;
        overflow-y: auto;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* Internet Explorer 10+ */
    }

    .dropup-items::-webkit-scrollbar {
        display: none; /* WebKit */
    }

    .dropup-item {
        display: block;
        padding: 12px 20px;
        color: #333;
        text-decoration: none;
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
        font-size: 14px;
    }

    .dropup-item:hover {
        background: linear-gradient(135deg, #f7fafc, #edf2f7);
        color: #667eea;
        border-left-color: #667eea;
        text-decoration: none;
        transform: translateX(5px);
    }

    .dropup-item i {
        width: 20px;
        text-align: center;
        color: #666;
    }

    .dropup-item:hover i {
        color: #667eea;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px) rotate(0deg);
        }
        33% {
            transform: translateY(-8px) rotate(1deg);
        }
        66% {
            transform: translateY(-4px) rotate(-1deg);
        }
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0);
        }
        40% {
            transform: translateY(-15px);
        }
        60% {
            transform: translateY(-8px);
        }
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .floating-community-widget {
            bottom: 20px;
            right: 20px;
        }

        .community-icon-btn {
            width: 65px;
            height: 65px;
            font-size: 26px;
            border-radius: 18px;
        }

        .community-dropup-menu {
            width: 220px;
            bottom: 70px;
        }

        .dropup-item {
            padding: 10px 15px;
            font-size: 13px;
        }

        .dropup-header {
            padding: 12px 15px;
            font-size: 14px;
        }
    }
    </style>
    <?php endif; ?>





    <script>
    // Floating Community Widget Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const widget = document.getElementById('floatingCommunityWidget');
        const iconBtn = document.getElementById('communityIconBtn');
        const dropupMenu = document.getElementById('communityDropupMenu');

        let isMenuOpen = false;

        if (widget && iconBtn && dropupMenu) {
            // Toggle dropup menu
            function toggleDropup() {
                if (!isMenuOpen) {
                    // Show dropup menu
                    dropupMenu.style.display = 'block';
                    setTimeout(() => {
                        dropupMenu.classList.add('show');
                    }, 10);
                    isMenuOpen = true;
                } else {
                    // Hide dropup menu
                    dropupMenu.classList.remove('show');
                    setTimeout(() => {
                        dropupMenu.style.display = 'none';
                    }, 400);
                    isMenuOpen = false;
                }
            }

            // Click icon to toggle dropup
            iconBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleDropup();
            });

            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!widget.contains(e.target) && isMenuOpen) {
                    toggleDropup();
                }
            });

            // Add click effect to icon
            iconBtn.addEventListener('mousedown', function() {
                this.style.transform = 'scale(0.95)';
            });

            iconBtn.addEventListener('mouseup', function() {
                this.style.transform = 'scale(1)';
            });

            iconBtn.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });

            // Handle menu item clicks
            dropupMenu.addEventListener('click', function(e) {
                if (e.target.tagName === 'A' || e.target.closest('a')) {
                    // Close menu before navigation
                    setTimeout(() => {
                        if (isMenuOpen) {
                            toggleDropup();
                        }
                    }, 100);
                }
            });

            // Prevent menu clicks from closing the dropup
            dropupMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    });

    // Load notification bell count
    function updateNotificationBellCount() {
        const notificationBadge = document.getElementById('notification-count');
        if (!notificationBadge) return; // Only update if notification bell exists

        fetch('<?php echo APP_URL; ?>/api/notification_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.count > 0) {
                        notificationBadge.textContent = data.count > 99 ? '99+' : data.count;
                        notificationBadge.style.display = 'inline';
                    } else {
                        notificationBadge.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.log('Could not load notification bell count');
            });
    }

    // Update notifications every 30 seconds
    if (document.getElementById('notification-count')) {
        updateNotificationBellCount();
        setInterval(updateNotificationBellCount, 30000);
    }

    // Toast Notification System
    function showToast(title, message, type = 'info', duration = 5000) {
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) return;

        // Create unique ID for toast
        const toastId = 'toast-' + Date.now();

        // Determine icon and color based on type
        let iconClass = 'fas fa-info-circle';
        let bgClass = 'bg-primary';

        switch (type) {
            case 'success':
                iconClass = 'fas fa-check-circle';
                bgClass = 'bg-success';
                break;
            case 'error':
                iconClass = 'fas fa-exclamation-circle';
                bgClass = 'bg-danger';
                break;
            case 'warning':
                iconClass = 'fas fa-exclamation-triangle';
                bgClass = 'bg-warning';
                break;
            case 'friend_request':
                iconClass = 'fas fa-user-plus';
                bgClass = 'bg-success';
                break;
        }

        // Create toast HTML
        const toastHTML = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <div class="d-flex align-items-center">
                            <i class="${iconClass} me-2"></i>
                            <div>
                                <strong>${title}</strong>
                                <div class="small">${message}</div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        // Add toast to container
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);

        // Initialize and show toast
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: duration
        });

        toast.show();

        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }

    // Global function to show notification toasts
    window.showNotificationToast = function(title, message, type = 'info') {
        showToast(title, message, type, 6000);
    };

    // Function to check for new notifications and show toasts
    let lastNotificationCheck = Date.now();
    function checkForNewNotifications() {
        const notificationBadge = document.getElementById('notification-count');
        if (!notificationBadge) return; // Only check if user is logged in

        fetch('<?php echo APP_URL; ?>/api/notification_count.php?since=' + lastNotificationCheck)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.new_notifications) {
                    data.new_notifications.forEach(notification => {
                        showToast(
                            notification.title,
                            notification.message,
                            notification.type,
                            7000
                        );
                    });
                }
                lastNotificationCheck = Date.now();
            })
            .catch(error => {
                console.log('Could not check for new notifications');
            });
    }

    // Check for new notifications every 60 seconds (temporarily disabled for debugging)
    if (document.getElementById('notification-count')) {
        // setInterval(checkForNewNotifications, 60000); // Temporarily disabled
    }

    </script>

    <!-- Community Dropdown Enhancement Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure dropdown closes when clicking on dropdown items
        const dropdownItems = document.querySelectorAll('.dropdown-item');
        dropdownItems.forEach(function(item) {
            item.addEventListener('click', function() {
                // Close any open dropdowns
                const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
                openDropdowns.forEach(function(dropdown) {
                    const dropdownToggle = dropdown.previousElementSibling;
                    if (dropdownToggle && dropdownToggle.classList.contains('dropdown-toggle')) {
                        const bsDropdown = bootstrap.Dropdown.getInstance(dropdownToggle);
                        if (bsDropdown) {
                            bsDropdown.hide();
                        }
                    }
                });
            });
        });

        // Add smooth hover effects
        const communityDropdown = document.getElementById('communityDropdown');
        if (communityDropdown) {
            communityDropdown.addEventListener('shown.bs.dropdown', function() {
                const dropdownMenu = this.nextElementSibling;
                if (dropdownMenu) {
                    dropdownMenu.style.opacity = '0';
                    dropdownMenu.style.transform = 'translateY(-10px)';
                    setTimeout(function() {
                        dropdownMenu.style.transition = 'all 0.3s ease';
                        dropdownMenu.style.opacity = '1';
                        dropdownMenu.style.transform = 'translateY(0)';
                    }, 10);
                }
            });
        }
    });
    </script>

    <!-- PWA Functionality -->
    <script src="<?php echo APP_URL; ?>/assets/js/pwa.js"></script>

    <!-- Offline Status Indicator -->
    <div id="offline-indicator" style="display: none;">
        <i class="fas fa-wifi"></i> You are currently offline. Some features may be limited.
    </div>
</body>
</html>
