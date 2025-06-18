<?php
/**
 * MoodifyMe - 404 Error Page
 */

// Include configuration
require_once '../config.php';

// Start session
session_start();

// Include header
include '../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2 text-center">
            <div class="error-page my-5">
                <h1 class="display-1 text-primary">404</h1>
                <h2 class="mb-4">Page Not Found</h2>
                <p class="lead">Oops! The page you're looking for doesn't exist or has been moved.</p>
                <p>Don't worry, we all feel lost sometimes. Let's help you find your way back.</p>
                
                <div class="mt-4 mb-5">
                    <a href="<?php echo APP_URL; ?>" class="btn btn-primary">
                        <i class="fas fa-home"></i> Go Home
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Go Back
                    </a>
                </div>
                
                <div class="card mt-5">
                    <div class="card-body">
                        <h3>Feeling lost?</h3>
                        <p>Here are some helpful links:</p>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><a href="<?php echo APP_URL; ?>"><i class="fas fa-home"></i> Home</a></li>
                                    <li><a href="<?php echo APP_URL; ?>/pages/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                    <li><a href="<?php echo APP_URL; ?>/pages/recommendations.php"><i class="fas fa-lightbulb"></i> Recommendations</a></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><a href="<?php echo APP_URL; ?>/pages/profile.php"><i class="fas fa-user"></i> Profile</a></li>
                                    <li><a href="<?php echo APP_URL; ?>/pages/about.php"><i class="fas fa-info-circle"></i> About</a></li>
                                    <li><a href="<?php echo APP_URL; ?>/pages/contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>
