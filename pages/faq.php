<?php
/**
 * MoodifyMe - Frequently Asked Questions Page
 */

// Include configuration
require_once '../config.php';
require_once '../includes/functions.php';

// Start session
session_start();

// Include header
include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-12">
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 text-primary">
                    <i class="fas fa-question-circle me-3"></i>
                    Frequently Asked Questions
                </h1>
                <p class="lead text-muted">Find quick answers to common questions about MoodifyMe</p>
            </div>

            <!-- Search FAQ -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" id="faqSearch" placeholder="Search FAQ...">
                    </div>
                </div>
            </div>

            <!-- FAQ Categories -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="btn-group flex-wrap" role="group" aria-label="FAQ Categories">
                        <button type="button" class="btn btn-outline-primary active" data-category="all">All</button>
                        <button type="button" class="btn btn-outline-primary" data-category="getting-started">Getting Started</button>
                        <button type="button" class="btn btn-outline-primary" data-category="mood-detection">Mood Detection</button>
                        <button type="button" class="btn btn-outline-primary" data-category="recommendations">Recommendations</button>
                        <button type="button" class="btn btn-outline-primary" data-category="account">Account</button>
                        <button type="button" class="btn btn-outline-primary" data-category="technical">Technical</button>
                        <button type="button" class="btn btn-outline-primary" data-category="privacy">Privacy</button>
                    </div>
                </div>
            </div>

            <!-- FAQ Accordion -->
            <div class="accordion" id="faqAccordion">
                
                <!-- Getting Started -->
                <div class="faq-item" data-category="getting-started">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq1">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                <i class="fas fa-play-circle me-2 text-primary"></i>
                                What is MoodifyMe and how does it work?
                            </button>
                        </h2>
                        <div id="collapse1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p><strong>MoodifyMe</strong> is an AI-powered platform that helps improve your emotional well-being by providing personalized recommendations based on your current mood and desired emotional state.</p>
                                <p><strong>How it works:</strong></p>
                                <ol>
                                    <li><strong>Mood Detection:</strong> Tell us how you're feeling using text, voice, or facial detection</li>
                                    <li><strong>Target Selection:</strong> Choose your desired emotional state</li>
                                    <li><strong>AI Recommendations:</strong> Get personalized music, movies, activities, and African meals</li>
                                    <li><strong>Track Progress:</strong> Monitor your emotional journey over time</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="getting-started">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                <i class="fas fa-user-plus me-2 text-primary"></i>
                                How do I get started with MoodifyMe?
                            </button>
                        </h2>
                        <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Getting started is easy:</p>
                                <ol>
                                    <li><strong>Register:</strong> Create a free account with your email</li>
                                    <li><strong>Complete Profile:</strong> Add basic information about yourself</li>
                                    <li><strong>First Mood Check:</strong> Tell us how you're feeling today</li>
                                    <li><strong>Get Recommendations:</strong> Receive your first personalized suggestions</li>
                                    <li><strong>Explore Features:</strong> Try different input methods and recommendation types</li>
                                </ol>
                                <p><a href="<?php echo APP_URL; ?>/pages/register.php" class="btn btn-primary btn-sm">Register Now</a></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mood Detection -->
                <div class="faq-item" data-category="mood-detection">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq3">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                <i class="fas fa-brain me-2 text-primary"></i>
                                What mood detection methods are available?
                            </button>
                        </h2>
                        <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>MoodifyMe offers three convenient ways to detect your mood:</p>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <i class="fas fa-keyboard fa-2x text-primary mb-2"></i>
                                                <h6>Text Input</h6>
                                                <p class="small">Type how you're feeling in natural language</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <i class="fas fa-microphone fa-2x text-primary mb-2"></i>
                                                <h6>Voice Input</h6>
                                                <p class="small">Speak your emotions naturally</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <i class="fas fa-camera fa-2x text-primary mb-2"></i>
                                                <h6>Facial Detection</h6>
                                                <p class="small">AI analyzes your facial expressions</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="mood-detection">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq4">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                                <i class="fas fa-eye me-2 text-primary"></i>
                                How accurate is the facial emotion detection?
                            </button>
                        </h2>
                        <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Our facial emotion detection uses advanced AI algorithms with high accuracy rates:</p>
                                <ul>
                                    <li><strong>85-95% accuracy</strong> for basic emotions (happy, sad, angry, surprised, neutral)</li>
                                    <li><strong>Real-time processing</strong> with instant results</li>
                                    <li><strong>Privacy-focused:</strong> Images are processed locally and not stored</li>
                                    <li><strong>Works best with:</strong> Good lighting, clear face visibility, front-facing camera</li>
                                </ul>
                                <p><em>Note: Results may vary based on lighting conditions, camera quality, and individual facial expressions.</em></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommendations -->
                <div class="faq-item" data-category="recommendations">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq5">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5">
                                <i class="fas fa-lightbulb me-2 text-primary"></i>
                                What types of recommendations do you provide?
                            </button>
                        </h2>
                        <div id="collapse5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>MoodifyMe provides diverse recommendations to help improve your mood:</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-music text-primary"></i> Music</h6>
                                        <ul>
                                            <li>Curated playlists</li>
                                            <li>Mood-based genres</li>
                                            <li>Spotify integration</li>
                                        </ul>
                                        
                                        <h6><i class="fas fa-film text-primary"></i> Movies & Shows</h6>
                                        <ul>
                                            <li>Mood-appropriate content</li>
                                            <li>Genre recommendations</li>
                                            <li>Streaming platform links</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-utensils text-primary"></i> African Meals</h6>
                                        <ul>
                                            <li>Comfort food recipes</li>
                                            <li>Mood-boosting ingredients</li>
                                            <li>Cultural cuisine therapy</li>
                                        </ul>
                                        
                                        <h6><i class="fas fa-running text-primary"></i> Activities</h6>
                                        <ul>
                                            <li>Mood-lifting exercises</li>
                                            <li>Mindfulness practices</li>
                                            <li>Social activities</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="recommendations">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq6">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6">
                                <i class="fas fa-globe-africa me-2 text-primary"></i>
                                Why do you include African meals in recommendations?
                            </button>
                        </h2>
                        <div id="collapse6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>African cuisine plays a unique role in emotional well-being:</p>
                                <ul>
                                    <li><strong>Cultural Connection:</strong> Food connects us to heritage and community</li>
                                    <li><strong>Comfort Factor:</strong> Traditional meals provide emotional comfort</li>
                                    <li><strong>Nutritional Benefits:</strong> Many African ingredients have mood-boosting properties</li>
                                    <li><strong>Social Aspect:</strong> Cooking and sharing meals builds connections</li>
                                    <li><strong>Mindful Preparation:</strong> Cooking can be therapeutic and meditative</li>
                                </ul>
                                <p>Our AI matches specific African dishes to mood transitions, helping you discover new flavors while improving your emotional state.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account -->
                <div class="faq-item" data-category="account">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq7">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7">
                                <i class="fas fa-user-cog me-2 text-primary"></i>
                                How do I manage my account settings?
                            </button>
                        </h2>
                        <div id="collapse7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>You can manage your account through the Settings page:</p>
                                <ul>
                                    <li><strong>Profile Information:</strong> Update name, email, and bio</li>
                                    <li><strong>Privacy Settings:</strong> Control data sharing and visibility</li>
                                    <li><strong>Notification Preferences:</strong> Choose how you receive updates</li>
                                    <li><strong>Recommendation Settings:</strong> Customize AI preferences</li>
                                    <li><strong>Data Export:</strong> Download your mood history</li>
                                </ul>
                                <p><a href="<?php echo APP_URL; ?>/pages/settings.php" class="btn btn-outline-primary btn-sm">Go to Settings</a></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="account">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq8">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse8">
                                <i class="fas fa-chart-line me-2 text-primary"></i>
                                Can I track my mood progress over time?
                            </button>
                        </h2>
                        <div id="collapse8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Yes! MoodifyMe provides comprehensive mood tracking:</p>
                                <ul>
                                    <li><strong>Mood History:</strong> View your emotional journey over days, weeks, and months</li>
                                    <li><strong>Visual Charts:</strong> Interactive graphs showing mood patterns</li>
                                    <li><strong>Recommendation Effectiveness:</strong> See which suggestions worked best</li>
                                    <li><strong>Progress Reports:</strong> Weekly and monthly summaries</li>
                                    <li><strong>Export Data:</strong> Download your data for personal analysis</li>
                                </ul>
                                <p><a href="<?php echo APP_URL; ?>/pages/history.php" class="btn btn-outline-primary btn-sm">View History</a></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technical -->
                <div class="faq-item" data-category="technical">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq9">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse9">
                                <i class="fas fa-mobile-alt me-2 text-primary"></i>
                                Is MoodifyMe available on mobile devices?
                            </button>
                        </h2>
                        <div id="collapse9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>MoodifyMe is fully responsive and works great on all devices:</p>
                                <ul>
                                    <li><strong>Web Browser:</strong> Access from any modern browser</li>
                                    <li><strong>Mobile Optimized:</strong> Touch-friendly interface for phones and tablets</li>
                                    <li><strong>Camera Access:</strong> Facial detection works on mobile cameras</li>
                                    <li><strong>Voice Input:</strong> Microphone support on mobile devices</li>
                                    <li><strong>Offline Features:</strong> Some features work without internet</li>
                                </ul>
                                <p><em>Native mobile apps are planned for future releases.</em></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="technical">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq10">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse10">
                                <i class="fas fa-exclamation-triangle me-2 text-primary"></i>
                                What should I do if I encounter technical issues?
                            </button>
                        </h2>
                        <div id="collapse10" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>If you experience technical problems, try these steps:</p>
                                <ol>
                                    <li><strong>Refresh the page:</strong> Simple reload often fixes temporary issues</li>
                                    <li><strong>Clear browser cache:</strong> Remove stored data that might be outdated</li>
                                    <li><strong>Check internet connection:</strong> Ensure stable connectivity</li>
                                    <li><strong>Try different browser:</strong> Test with Chrome, Firefox, or Safari</li>
                                    <li><strong>Disable extensions:</strong> Ad blockers might interfere with features</li>
                                    <li><strong>Contact support:</strong> If issues persist, reach out to our team</li>
                                </ol>
                                <div class="alert alert-info">
                                    <strong>Camera/Microphone Issues:</strong> Make sure you've granted permission for camera and microphone access in your browser settings.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Privacy -->
                <div class="faq-item" data-category="privacy">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq11">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse11">
                                <i class="fas fa-shield-alt me-2 text-primary"></i>
                                How do you protect my privacy and data?
                            </button>
                        </h2>
                        <div id="collapse11" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Your privacy is our top priority. Here's how we protect your data:</p>
                                <ul>
                                    <li><strong>Local Processing:</strong> Facial detection happens on your device, not our servers</li>
                                    <li><strong>Encrypted Storage:</strong> All data is encrypted using industry-standard methods</li>
                                    <li><strong>No Image Storage:</strong> Photos/videos are never saved or transmitted</li>
                                    <li><strong>Minimal Data Collection:</strong> We only collect what's necessary for functionality</li>
                                    <li><strong>User Control:</strong> You can delete your data anytime</li>
                                    <li><strong>GDPR Compliant:</strong> We follow international privacy standards</li>
                                </ul>
                                <p><a href="<?php echo APP_URL; ?>/pages/privacy.php" class="btn btn-outline-primary btn-sm">Read Privacy Policy</a></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="privacy">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq12">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse12">
                                <i class="fas fa-trash-alt me-2 text-primary"></i>
                                Can I delete my account and data?
                            </button>
                        </h2>
                        <div id="collapse12" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Yes, you have full control over your data:</p>
                                <ul>
                                    <li><strong>Account Deletion:</strong> Permanently delete your account from Settings</li>
                                    <li><strong>Data Export:</strong> Download all your data before deletion</li>
                                    <li><strong>Selective Deletion:</strong> Remove specific mood entries or recommendations</li>
                                    <li><strong>Immediate Effect:</strong> Deletion is processed immediately</li>
                                    <li><strong>No Recovery:</strong> Deleted data cannot be restored</li>
                                </ul>
                                <div class="alert alert-warning">
                                    <strong>Note:</strong> Account deletion is permanent and cannot be undone. Make sure to export any data you want to keep.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Contact Support -->
            <div class="card mt-5 bg-light">
                <div class="card-body text-center">
                    <h4><i class="fas fa-question-circle text-primary me-2"></i>Still Have Questions?</h4>
                    <p class="mb-3">Can't find what you're looking for? Our support team is here to help!</p>
                    <a href="<?php echo APP_URL; ?>/pages/contact.php" class="btn btn-primary">
                        <i class="fas fa-envelope me-2"></i>Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.faq-item {
    margin-bottom: 1rem;
}

.accordion-button {
    font-weight: 500;
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.btn-group .btn {
    margin: 2px;
}

@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin: 1px 0;
    }
}

.card.h-100 {
    transition: transform 0.2s;
}

.card.h-100:hover {
    transform: translateY(-2px);
}

#faqSearch {
    border-radius: 0.375rem 0.375rem 0.375rem 0.375rem;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.faq-item.hidden {
    display: none;
}

.no-results {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('faqSearch');
    const categoryButtons = document.querySelectorAll('[data-category]');
    const faqItems = document.querySelectorAll('.faq-item');
    const accordion = document.getElementById('faqAccordion');

    let currentCategory = 'all';

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        filterFAQs(searchTerm, currentCategory);
    });

    // Category filtering
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Update active button
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Update current category
            currentCategory = this.dataset.category;

            // Filter FAQs
            const searchTerm = searchInput.value.toLowerCase().trim();
            filterFAQs(searchTerm, currentCategory);
        });
    });

    function filterFAQs(searchTerm, category) {
        let visibleCount = 0;

        faqItems.forEach(item => {
            const itemCategory = item.dataset.category;
            const itemText = item.textContent.toLowerCase();

            // Check category match
            const categoryMatch = category === 'all' || itemCategory === category;

            // Check search term match
            const searchMatch = searchTerm === '' || itemText.includes(searchTerm);

            // Show/hide item
            if (categoryMatch && searchMatch) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });

        // Show/hide no results message
        let noResultsMsg = document.querySelector('.no-results');
        if (visibleCount === 0) {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.className = 'no-results';
                noResultsMsg.innerHTML = `
                    <i class="fas fa-search fa-3x mb-3"></i>
                    <h4>No results found</h4>
                    <p>Try adjusting your search terms or selecting a different category.</p>
                `;
                accordion.appendChild(noResultsMsg);
            }
            noResultsMsg.style.display = 'block';
        } else {
            if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        }
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Auto-expand FAQ if URL has hash
    if (window.location.hash) {
        const targetCollapse = document.querySelector(window.location.hash);
        if (targetCollapse) {
            setTimeout(() => {
                const bsCollapse = new bootstrap.Collapse(targetCollapse, {
                    show: true
                });
            }, 500);
        }
    }

    // Track FAQ interactions (optional analytics)
    document.querySelectorAll('.accordion-button').forEach(button => {
        button.addEventListener('click', function() {
            const faqTitle = this.textContent.trim();
            console.log('FAQ opened:', faqTitle);
            // You can add analytics tracking here
        });
    });
});
</script>

<?php
// Include footer
include '../includes/footer.php';
?>
