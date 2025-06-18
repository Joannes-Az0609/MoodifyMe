<?php
/**
 * MoodifyMe - Contact Page
 */

// Include configuration
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';
require_once '../includes/email_functions.php';

// Start session
session_start();

// Initialize variables
$name = '';
$email = '';
$subject = '';
$message = '';
$success = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Validate form data
    if (empty($name)) {
        $error = 'Name is required.';
    } elseif (empty($email)) {
        $error = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (empty($subject)) {
        $error = 'Subject is required.';
    } elseif (empty($message)) {
        $error = 'Message is required.';
    } else {
        // Send email notification
        $to = 'jazhinwi@gmail.com';
        $emailSubject = 'MoodifyMe Contact Form: ' . $subject;

        // Create email body
        $emailBody = "New contact form submission from MoodifyMe:\n\n";
        $emailBody .= "Name: " . $name . "\n";
        $emailBody .= "Email: " . $email . "\n";
        $emailBody .= "Subject: " . $subject . "\n";
        $emailBody .= "Message:\n" . $message . "\n\n";
        $emailBody .= "Submitted on: " . date('Y-m-d H:i:s') . "\n";
        $emailBody .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";

        // Email headers
        $headers = "From: noreply@moodifyme.com\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // Save to database first
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssss", $name, $email, $subject, $message, $ipAddress, $userAgent);

        $databaseSaved = $stmt->execute();

        // Try to send email (suppress errors for local development)
        $emailSent = @mail($to, $emailSubject, $emailBody, $headers);

        if ($databaseSaved) {
            if ($emailSent) {
                $success = 'Your message has been sent! We will get back to you soon.';
            } else {
                $success = 'Your message has been received! We will get back to you soon.';
            }

            // Clear form data on successful save
            $name = '';
            $email = '';
            $subject = '';
            $message = '';
        } else {
            $error = 'Sorry, there was an error saving your message. Please try again or contact us directly at jazhinwi@gmail.com';
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
                    <h1 class="card-title">Contact Us</h1>
                    <p class="lead">We'd love to hear from you! Send us a message and we'll respond as soon as possible.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>" required>
                            <div class="invalid-feedback">
                                Please enter your name.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="" <?php echo empty($subject) ? 'selected' : ''; ?>>Select a subject</option>
                                <option value="General Inquiry" <?php echo $subject === 'General Inquiry' ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="Technical Support" <?php echo $subject === 'Technical Support' ? 'selected' : ''; ?>>Technical Support</option>
                                <option value="Feature Request" <?php echo $subject === 'Feature Request' ? 'selected' : ''; ?>>Feature Request</option>
                                <option value="Bug Report" <?php echo $subject === 'Bug Report' ? 'selected' : ''; ?>>Bug Report</option>
                                <option value="Feedback" <?php echo $subject === 'Feedback' ? 'selected' : ''; ?>>Feedback</option>
                                <option value="Other" <?php echo $subject === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a subject.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required><?php echo $message; ?></textarea>
                            <div class="invalid-feedback">
                                Please enter your message.
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="privacy" name="privacy" required>
                            <label class="form-check-label" for="privacy">I agree to the <a href="<?php echo APP_URL; ?>/pages/privacy.php">Privacy Policy</a></label>
                            <div class="invalid-feedback">
                                You must agree to the privacy policy.
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h3>Contact Information</h3>
                    <p>Feel free to reach out to us using any of the following methods:</p>
                    
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <a href="mailto:jazhinwi@gmail.com">jazhinwi@gmail.com</a>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-phone text-primary me-2"></i>
                            <a href="tel:+237677069985">+237 677 069 985</a>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                            Yaounde<br>
                            Cameroon<br>
                            Central Africa
                        </li>
                    </ul>
                    
                    <h4 class="mt-4">Business Hours</h4>
                    <p>Our support team is available during the following hours:</p>
                    
                    <ul class="list-unstyled">
                        <li>Monday - Friday: 8:00 AM - 5:00 PM WAT</li>
                        <li>Saturday: 9:00 AM - 1:00 PM WAT</li>
                        <li>Sunday: Closed</li>
                    </ul>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h3>Follow Us</h3>
                    <p>Stay connected with us on social media for updates, tips, and more:</p>
                    
                    <div class="social-icons">
                        <a href="#" class="btn btn-outline-primary me-2 mb-2">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                        <a href="#" class="btn btn-outline-primary me-2 mb-2">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                        <a href="#" class="btn btn-outline-primary me-2 mb-2">
                            <i class="fab fa-instagram"></i> Instagram
                        </a>
                        <a href="#" class="btn btn-outline-primary me-2 mb-2">
                            <i class="fab fa-linkedin-in"></i> LinkedIn
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h3>FAQ</h3>
                    <p>Have a question? Check our <a href="<?php echo APP_URL; ?>/pages/faq.php">Frequently Asked Questions</a> page for quick answers to common inquiries.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
});
</script>

<?php
// Include footer
include '../includes/footer.php';
?>
