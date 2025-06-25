<?php
/**
 * Crisis Support Page
 * Provides immediate crisis resources and support
 */

// Include configuration and functions
if (isset($_ENV['RENDER']) || strpos($_SERVER['HTTP_HOST'], '.onrender.com') !== false || $_SERVER['HTTP_HOST'] !== 'localhost') {
    require_once '../config.production.php';
} else {
    require_once '../config.php';
}

require_once '../includes/functions.php';
require_once '../includes/db_connect.php';
require_once '../includes/crisis_detection.php';

// Start session
session_start();

// Initialize crisis detection
$crisisDetection = new CrisisDetection();

// Check if this is a crisis assessment request
$crisisAssessment = null;
$intervention = null;

if ($_POST && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    $userId = $_SESSION['user_id'] ?? 'anonymous';
    
    if (!empty($message)) {
        // Assess crisis risk
        $crisisAssessment = $crisisDetection->assessCrisisRisk($message, $userId);
        
        // Get intervention if needed
        if ($crisisAssessment['requiresIntervention']) {
            $intervention = $crisisDetection->getCrisisIntervention(
                $crisisAssessment, 
                $userId, 
                $message
            );
        }
    }
}

// Get crisis resources
$crisisResources = $crisisDetection->getCrisisResources();

// Include header
include '../includes/header.php';
?>

<div class="crisis-support-container">
    <div class="container py-5">
        
        <!-- Emergency Alert Banner -->
        <?php if ($crisisAssessment && $crisisAssessment['riskLevel'] >= 8): ?>
        <div class="alert alert-danger crisis-emergency-alert" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <h4 class="alert-heading mb-1">🚨 IMMEDIATE CRISIS SUPPORT NEEDED</h4>
                    <p class="mb-2">If you are in immediate danger, please contact emergency services right now.</p>
                    <div class="crisis-emergency-buttons">
                        <a href="tel:911" class="btn btn-light btn-lg me-2">
                            <i class="fas fa-phone"></i> Call 911
                        </a>
                        <a href="tel:988" class="btn btn-light btn-lg me-2">
                            <i class="fas fa-phone"></i> Call 988
                        </a>
                        <a href="sms:741741?body=HOME" class="btn btn-light btn-lg">
                            <i class="fas fa-sms"></i> Text 741741
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Main Crisis Support Content -->
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 mb-4">
                    <div class="card-body p-5">
                        <h1 class="card-title text-center mb-4">
                            <i class="fas fa-heart text-danger me-2"></i>
                            Crisis Support & Resources
                        </h1>
                        
                        <p class="lead text-center mb-4">
                            You are not alone. Help is available 24/7, and your life has value and meaning.
                        </p>

                        <!-- Crisis Assessment Form -->
                        <?php if (!$crisisAssessment): ?>
                        <div class="crisis-assessment-section mb-5">
                            <h3 class="mb-3">How are you feeling right now?</h3>
                            <p class="text-muted mb-4">
                                Share what you're experiencing, and we'll provide appropriate support and resources.
                            </p>
                            
                            <form method="POST" class="crisis-assessment-form">
                                <div class="mb-3">
                                    <textarea 
                                        class="form-control form-control-lg" 
                                        name="message" 
                                        rows="4" 
                                        placeholder="Describe how you're feeling or what you're going through..."
                                        required
                                    ></textarea>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-heart me-2"></i>
                                        Get Support & Resources
                                    </button>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>

                        <!-- Crisis Intervention Response -->
                        <?php if ($intervention): ?>
                        <div class="crisis-intervention-response mb-5">
                            <div class="alert alert-info">
                                <h4 class="alert-heading">
                                    <i class="fas fa-hands-helping me-2"></i>
                                    Support Response
                                </h4>
                                <div class="crisis-message">
                                    <?= nl2br(htmlspecialchars($intervention['message'])) ?>
                                </div>
                                
                                <?php if (isset($intervention['copingStrategies'])): ?>
                                <div class="coping-strategies mt-4">
                                    <h5>🧠 Immediate Coping Strategies:</h5>
                                    <ul class="list-unstyled">
                                        <?php foreach (array_slice($intervention['copingStrategies'], 0, 3) as $strategy): ?>
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            <?= htmlspecialchars($strategy) ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-center mt-4">
                                <a href="<?= APP_URL ?>/pages/crisis_support.php" class="btn btn-outline-primary">
                                    <i class="fas fa-redo me-2"></i>
                                    Get Additional Support
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Immediate Crisis Resources -->
                        <div class="immediate-resources mb-5">
                            <h3 class="mb-4">
                                <i class="fas fa-phone text-danger me-2"></i>
                                Immediate Help Available 24/7
                            </h3>
                            
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="crisis-resource-card h-100">
                                        <div class="card border-danger">
                                            <div class="card-body text-center">
                                                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-3"></i>
                                                <h5 class="card-title">Emergency Services</h5>
                                                <p class="card-text">Immediate danger or medical emergency</p>
                                                <a href="tel:911" class="btn btn-danger btn-lg">
                                                    <i class="fas fa-phone me-2"></i>Call 911
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="crisis-resource-card h-100">
                                        <div class="card border-warning">
                                            <div class="card-body text-center">
                                                <i class="fas fa-heart fa-2x text-warning mb-3"></i>
                                                <h5 class="card-title">Suicide & Crisis Lifeline</h5>
                                                <p class="card-text">24/7 free and confidential support</p>
                                                <a href="tel:988" class="btn btn-warning btn-lg">
                                                    <i class="fas fa-phone me-2"></i>Call 988
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="crisis-resource-card h-100">
                                        <div class="card border-info">
                                            <div class="card-body text-center">
                                                <i class="fas fa-comments fa-2x text-info mb-3"></i>
                                                <h5 class="card-title">Crisis Text Line</h5>
                                                <p class="card-text">Text-based crisis support</p>
                                                <a href="sms:741741?body=HOME" class="btn btn-info btn-lg">
                                                    <i class="fas fa-sms me-2"></i>Text HOME to 741741
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Resources -->
                        <div class="additional-resources">
                            <h3 class="mb-4">
                                <i class="fas fa-hands-helping text-primary me-2"></i>
                                Additional Support Resources
                            </h3>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="fas fa-brain text-primary me-2"></i>
                                                Mental Health Support
                                            </h5>
                                            <p class="card-text">National Alliance on Mental Illness (NAMI)</p>
                                            <a href="tel:1-800-950-6264" class="btn btn-outline-primary">
                                                <i class="fas fa-phone me-2"></i>1-800-950-NAMI
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="fas fa-search text-primary me-2"></i>
                                                Find a Therapist
                                            </h5>
                                            <p class="card-text">Psychology Today therapist directory</p>
                                            <a href="https://www.psychologytoday.com/" target="_blank" class="btn btn-outline-primary">
                                                <i class="fas fa-external-link-alt me-2"></i>Find Therapist
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar with Quick Resources -->
            <div class="col-lg-4">
                <div class="card shadow border-0 mb-4">
                    <div class="card-body">
                        <h4 class="card-title">
                            <i class="fas fa-shield-alt text-success me-2"></i>
                            You Are Safe Here
                        </h4>
                        <p class="card-text">
                            This is a safe space where you can express your feelings without judgment. 
                            Your privacy is protected, and help is always available.
                        </p>
                    </div>
                </div>

                <div class="card shadow border-0 mb-4">
                    <div class="card-body">
                        <h4 class="card-title">
                            <i class="fas fa-lightbulb text-warning me-2"></i>
                            Immediate Coping
                        </h4>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Take 10 slow, deep breaths
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Call a trusted friend or family member
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Go to a safe, public place
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Remove means of self-harm
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow border-0">
                    <div class="card-body">
                        <h4 class="card-title">
                            <i class="fas fa-heart text-danger me-2"></i>
                            Remember
                        </h4>
                        <blockquote class="blockquote">
                            <p class="mb-0">
                                "You are braver than you believe, stronger than you seem, 
                                and more loved than you know."
                            </p>
                            <footer class="blockquote-footer mt-2">
                                Your life has value and meaning
                            </footer>
                        </blockquote>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.crisis-support-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
}

.crisis-emergency-alert {
    border-left: 5px solid #dc3545;
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
}

.crisis-emergency-buttons .btn {
    margin-bottom: 0.5rem;
}

.crisis-resource-card .card {
    transition: transform 0.2s ease-in-out;
}

.crisis-resource-card .card:hover {
    transform: translateY(-5px);
}

.crisis-assessment-form textarea {
    border: 2px solid #dee2e6;
    border-radius: 10px;
}

.crisis-assessment-form textarea:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.crisis-intervention-response {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    border-radius: 10px;
    padding: 1.5rem;
}

.crisis-message {
    font-size: 1.1rem;
    line-height: 1.6;
}

.coping-strategies ul li {
    background: rgba(255, 255, 255, 0.7);
    padding: 0.5rem;
    border-radius: 5px;
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .crisis-emergency-buttons {
        text-align: center;
    }
    
    .crisis-emergency-buttons .btn {
        display: block;
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>

<?php
// Include footer
include '../includes/footer.php';
?>
