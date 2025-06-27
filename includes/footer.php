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

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (required for some components) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>



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

    <!-- Floating Chat Widget -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="floating-chat-widget">
        <div class="chat-toggle-btn" id="chatToggleBtn">
            <i class="fas fa-comments"></i>
            <span class="chat-notification-badge" id="chatNotificationBadge" style="display: none;">0</span>
        </div>

        <div class="chat-widget-panel" id="chatWidgetPanel" style="display: none;">
            <div class="chat-widget-header">
                <h6 class="mb-0">
                    <i class="fas fa-comments me-2"></i>Community & Support
                </h6>
                <button class="btn-close btn-close-white" id="closeChatWidget"></button>
            </div>
            <div class="chat-widget-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo APP_URL; ?>/pages/community_posts.php" class="btn btn-success btn-sm">
                        <i class="fas fa-users me-1"></i> Community Posts
                    </a>
                    <a href="<?php echo APP_URL; ?>/pages/messages.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-envelope me-1"></i> Direct Messages
                    </a>
                    <a href="<?php echo APP_URL; ?>/pages/user_directory.php" class="btn btn-info btn-sm">
                        <i class="fas fa-search me-1"></i> Find People
                    </a>
                    <hr class="my-2">
                    <a href="http://localhost:3001" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-robot me-1"></i> AI Assistant
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
    .floating-chat-widget {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1050;
    }

    .chat-toggle-btn {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #28a745, #20c997);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        transition: all 0.3s ease;
        position: relative;
    }

    .chat-toggle-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
    }

    .chat-notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
    }

    .chat-widget-panel {
        position: absolute;
        bottom: 70px;
        right: 0;
        width: 280px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        border: 1px solid #e9ecef;
        overflow: hidden;
    }

    .chat-widget-header {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 12px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .chat-widget-body {
        padding: 16px;
    }

    .btn-close-white {
        filter: invert(1);
        opacity: 0.8;
    }

    .btn-close-white:hover {
        opacity: 1;
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .floating-chat-widget {
            bottom: 15px;
            right: 15px;
        }

        .chat-toggle-btn {
            width: 50px;
            height: 50px;
            font-size: 20px;
        }

        .chat-widget-panel {
            width: 260px;
            bottom: 60px;
        }
    }
    </style>

    <script>
    // Floating Chat Widget Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const chatToggleBtn = document.getElementById('chatToggleBtn');
        const chatWidgetPanel = document.getElementById('chatWidgetPanel');
        const closeChatWidget = document.getElementById('closeChatWidget');
        const chatNotificationBadge = document.getElementById('chatNotificationBadge');

        if (!chatToggleBtn || !chatWidgetPanel || !closeChatWidget) return;

        // Toggle chat widget
        chatToggleBtn.addEventListener('click', function() {
            if (chatWidgetPanel.style.display === 'none' || chatWidgetPanel.style.display === '') {
                chatWidgetPanel.style.display = 'block';
                chatWidgetPanel.style.animation = 'slideUp 0.3s ease';
            } else {
                chatWidgetPanel.style.display = 'none';
            }
        });

        // Close chat widget
        closeChatWidget.addEventListener('click', function() {
            chatWidgetPanel.style.display = 'none';
        });

        // Close widget when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.floating-chat-widget')) {
                chatWidgetPanel.style.display = 'none';
            }
        });

        // Load notification count
        function updateNotificationCount() {
            fetch('<?php echo APP_URL; ?>/api/community_stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.unread_messages > 0) {
                        chatNotificationBadge.textContent = data.unread_messages;
                        chatNotificationBadge.style.display = 'flex';
                    } else {
                        chatNotificationBadge.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.log('Could not load notification count');
                });
        }

        // Update notifications every 30 seconds
        updateNotificationCount();
        setInterval(updateNotificationCount, 30000);
    });

    // Add slide up animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);
    </script>
    <?php endif; ?>
</body>
</html>
