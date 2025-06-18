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
</body>
</html>
