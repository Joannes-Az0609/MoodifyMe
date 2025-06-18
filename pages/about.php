<?php
/**
 * MoodifyMe - About Page
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
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h1 class="card-title">About MoodifyMe</h1>
                    <p class="lead">An AI-powered platform designed to enhance your emotional well-being.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h2>Our Mission</h2>
                    <p>At MoodifyMe, we believe that emotional well-being is just as important as physical health. Our mission is to create a platform that helps people understand, manage, and improve their emotional states through personalized recommendations.</p>

                    <h2 class="mt-4">How It Works</h2>
                    <div class="row mt-3">
                        <div class="col-md-4 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-brain fa-3x text-primary mb-3"></i>
                                <h5>Emotion Detection</h5>
                                <p>Express how you feel through text, voice, or facial expressions.</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-lightbulb fa-3x text-primary mb-3"></i>
                                <h5>Smart Analysis</h5>
                                <p>Our AI analyzes your input to understand your emotional state.</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-heart fa-3x text-primary mb-3"></i>
                                <h5>Personalized Recommendations</h5>
                                <p>Receive tailored suggestions to help you achieve your desired mood.</p>
                            </div>
                        </div>
                    </div>

                    <h2 class="mt-4">Our Technology</h2>
                    <p>MoodifyMe combines several cutting-edge technologies to provide an intelligent and empathetic recommendation system:</p>
                    <ul>
                        <li><strong>Natural Language Processing (NLP)</strong> - To analyze text input and detect emotions from written expressions</li>
                        <li><strong>Voice Sentiment Analysis</strong> - To identify emotional cues in speech patterns and tone</li>
                        <li><strong>Facial Emotion Recognition with Landmarks</strong> - To detect emotions from facial expressions using advanced facial landmark detection</li>
                        <li><strong>Recommendation Algorithms</strong> - To suggest content and activities based on emotional context</li>
                    </ul>

                    <h2 class="mt-4">Our Recommendation Categories</h2>
                    <p>We focus on three key categories of recommendations to help improve your mood:</p>

                    <div class="row mt-3">
                        <div class="col-md-4 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-music fa-3x text-primary mb-3"></i>
                                <h5>Music</h5>
                                <p>Curated playlists designed to complement or transform your emotional state.</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-film fa-3x text-primary mb-3"></i>
                                <h5>Movies</h5>
                                <p>Film recommendations that resonate with your current mood or help shift it.</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="fas fa-robot fa-3x text-primary mb-3"></i>
                                <h5>AI Jokes</h5>
                                <p>AI-generated humor to lighten your mood and bring a smile to your face.</p>
                            </div>
                        </div>
                    </div>

                    <h2 class="mt-4">Future Development: AI Mental Health Assistant</h2>
                    <p>While MoodifyMe currently focuses on providing content recommendations based on your emotional state, we're working on developing a more comprehensive AI mental health assistant that can:</p>
                    <ul>
                        <li><strong>Engage in supportive conversations</strong> about your emotions</li>
                        <li><strong>Offer evidence-based coping strategies</strong> for difficult emotions</li>
                        <li><strong>Track your emotional patterns</strong> over time and provide insights</li>
                        <li><strong>Suggest personalized activities</strong> to improve your mental wellbeing</li>
                        <li><strong>Use humor appropriately</strong> to lighten your mood when needed</li>
                    </ul>
                    <p>Our goal is to create a compassionate, intelligent assistant that understands the nuances of human emotion and can provide meaningful support on your mental wellness journey.</p>

                    <h2 class="mt-4">Privacy & Ethics</h2>
                    <p>We take your privacy seriously. All emotional data is processed with strict confidentiality and is used solely to improve your experience. We do not share your personal data with third parties without your explicit consent.</p>
                    <p>Our recommendation system is designed to promote emotional well-being and is not intended to replace professional mental health services. If you're experiencing serious emotional distress, please consult a qualified healthcare professional.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h3>Why MoodifyMe?</h3>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> Multi-modal emotion detection with facial landmarks
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> Curated music recommendations for every mood
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> Thoughtfully selected movies to match your emotional state
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> AI-generated jokes to lighten your mood
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> Future AI mental health assistant capabilities
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h3>Our Team</h3>
                    <p>MoodifyMe was created by a passionate team of developers, data scientists, and UX designers who believe in the power of technology to improve emotional well-being.</p>
                    <a href="<?php echo APP_URL; ?>/pages/team.php" class="btn btn-outline-primary">
                        <i class="fas fa-users"></i> Meet the Team
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h3>Contact Us</h3>
                    <p>Have questions or feedback? We'd love to hear from you!</p>
                    <a href="<?php echo APP_URL; ?>/pages/contact.php" class="btn btn-outline-primary">
                        <i class="fas fa-envelope"></i> Get in Touch
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>
