<?php
/**
 * MoodifyMe - Privacy Policy Page
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
                    <h1 class="card-title">Privacy Policy</h1>
                    <p class="text-muted">Last updated: <?php echo date('F d, Y'); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Table of Contents</h5>
                    <div class="list-group list-group-flush">
                        <a href="#introduction" class="list-group-item list-group-item-action">1. Introduction</a>
                        <a href="#information" class="list-group-item list-group-item-action">2. Information We Collect</a>
                        <a href="#use" class="list-group-item list-group-item-action">3. How We Use Your Information</a>
                        <a href="#sharing" class="list-group-item list-group-item-action">4. Information Sharing</a>
                        <a href="#security" class="list-group-item list-group-item-action">5. Data Security</a>
                        <a href="#retention" class="list-group-item list-group-item-action">6. Data Retention</a>
                        <a href="#rights" class="list-group-item list-group-item-action">7. Your Rights</a>
                        <a href="#children" class="list-group-item list-group-item-action">8. Children's Privacy</a>
                        <a href="#changes" class="list-group-item list-group-item-action">9. Changes to This Policy</a>
                        <a href="#contact" class="list-group-item list-group-item-action">10. Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <section id="introduction" class="mb-5">
                        <h2>1. Introduction</h2>
                        <p>MoodifyMe ("we", "our", or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our emotion-based recommendation system ("the Service").</p>
                        <p>We take your privacy seriously and have designed our Service with your privacy in mind. Please read this Privacy Policy carefully. By using the Service, you agree to the collection and use of information in accordance with this policy.</p>
                    </section>
                    
                    <section id="information" class="mb-5">
                        <h2>2. Information We Collect</h2>
                        
                        <h4>2.1 Information You Provide</h4>
                        <p>We collect information that you voluntarily provide when using our Service, including:</p>
                        <ul>
                            <li><strong>Account Information:</strong> When you create an account, we collect your username, email address, and password.</li>
                            <li><strong>Profile Information:</strong> Any additional information you choose to add to your profile, such as a bio or profile picture.</li>
                            <li><strong>Emotional Data:</strong> Information about your emotional states that you share through text, voice, or facial expressions.</li>
                            <li><strong>Feedback and Preferences:</strong> Your responses to recommendations, likes, dislikes, and preference settings.</li>
                            <li><strong>Communications:</strong> Information you provide when contacting us or participating in surveys.</li>
                        </ul>
                        
                        <h4>2.2 Information Collected Automatically</h4>
                        <p>When you use our Service, we may automatically collect certain information, including:</p>
                        <ul>
                            <li><strong>Device Information:</strong> Information about your device, such as IP address, browser type, operating system, and device identifiers.</li>
                            <li><strong>Usage Information:</strong> Information about how you use the Service, including pages visited, features used, and time spent on the Service.</li>
                            <li><strong>Cookies and Similar Technologies:</strong> We use cookies and similar technologies to collect information about your browsing activities and to remember your preferences.</li>
                        </ul>
                    </section>
                    
                    <section id="use" class="mb-5">
                        <h2>3. How We Use Your Information</h2>
                        <p>We use the information we collect for various purposes, including:</p>
                        <ul>
                            <li><strong>Providing the Service:</strong> To operate, maintain, and improve the Service, including generating personalized recommendations based on your emotional states.</li>
                            <li><strong>Personalization:</strong> To tailor the content and recommendations to your preferences and needs.</li>
                            <li><strong>Communication:</strong> To respond to your inquiries, send you important notices, and provide customer support.</li>
                            <li><strong>Research and Development:</strong> To analyze usage patterns, conduct research, and improve our algorithms and recommendation system.</li>
                            <li><strong>Security:</strong> To protect the Service, prevent fraud, and ensure the security of your account.</li>
                            <li><strong>Legal Compliance:</strong> To comply with applicable laws, regulations, and legal processes.</li>
                        </ul>
                    </section>
                    
                    <section id="sharing" class="mb-5">
                        <h2>4. Information Sharing</h2>
                        <p>We may share your information in the following circumstances:</p>
                        <ul>
                            <li><strong>With Your Consent:</strong> We may share your information when you give us explicit consent to do so.</li>
                            <li><strong>Service Providers:</strong> We may share information with third-party vendors, consultants, and other service providers who need access to such information to perform work on our behalf.</li>
                            <li><strong>Compliance with Laws:</strong> We may disclose your information if required to do so by law or in response to valid requests by public authorities.</li>
                            <li><strong>Business Transfers:</strong> If we are involved in a merger, acquisition, or sale of all or a portion of our assets, your information may be transferred as part of that transaction.</li>
                            <li><strong>Aggregated or De-identified Data:</strong> We may share aggregated or de-identified information that cannot reasonably be used to identify you.</li>
                        </ul>
                        <p>We do not sell your personal information to third parties.</p>
                    </section>
                    
                    <section id="security" class="mb-5">
                        <h2>5. Data Security</h2>
                        <p>We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.</p>
                        <p>We regularly review our security procedures and consider appropriate new security technology and methods. However, despite our efforts, no security measures are perfect or impenetrable.</p>
                    </section>
                    
                    <section id="retention" class="mb-5">
                        <h2>6. Data Retention</h2>
                        <p>We retain your personal information for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law. When determining how long to retain information, we consider:</p>
                        <ul>
                            <li>The amount, nature, and sensitivity of the information</li>
                            <li>The potential risk of harm from unauthorized use or disclosure</li>
                            <li>The purposes for which we process the information</li>
                            <li>Whether we can achieve those purposes through other means</li>
                            <li>Applicable legal requirements</li>
                        </ul>
                        <p>When we no longer need your personal information, we will securely delete or anonymize it.</p>
                    </section>
                    
                    <section id="rights" class="mb-5">
                        <h2>7. Your Rights</h2>
                        <p>Depending on your location, you may have certain rights regarding your personal information, including:</p>
                        <ul>
                            <li><strong>Access:</strong> You can request access to the personal information we hold about you.</li>
                            <li><strong>Correction:</strong> You can request that we correct inaccurate or incomplete information.</li>
                            <li><strong>Deletion:</strong> You can request that we delete your personal information in certain circumstances.</li>
                            <li><strong>Restriction:</strong> You can request that we restrict the processing of your information in certain circumstances.</li>
                            <li><strong>Data Portability:</strong> You can request a copy of your personal information in a structured, commonly used, and machine-readable format.</li>
                            <li><strong>Objection:</strong> You can object to our processing of your personal information in certain circumstances.</li>
                        </ul>
                        <p>To exercise these rights, please contact us using the information provided in the "Contact Us" section. We will respond to your request within a reasonable timeframe and in accordance with applicable laws.</p>
                    </section>
                    
                    <section id="children" class="mb-5">
                        <h2>8. Children's Privacy</h2>
                        <p>Our Service is not directed to children under the age of 13, and we do not knowingly collect personal information from children under 13. If we learn that we have collected personal information from a child under 13, we will take steps to delete such information as soon as possible.</p>
                        <p>If you are a parent or guardian and believe that your child has provided us with personal information, please contact us so that we can take appropriate action.</p>
                    </section>
                    
                    <section id="changes" class="mb-5">
                        <h2>9. Changes to This Policy</h2>
                        <p>We may update this Privacy Policy from time to time to reflect changes in our practices or for other operational, legal, or regulatory reasons. We will notify you of any material changes by posting the new Privacy Policy on this page and updating the "Last updated" date.</p>
                        <p>We encourage you to review this Privacy Policy periodically for any changes. Your continued use of the Service after any changes to this Privacy Policy constitutes your acceptance of the changes.</p>
                    </section>
                    
                    <section id="contact" class="mb-5">
                        <h2>10. Contact Us</h2>
                        <p>If you have any questions, concerns, or requests regarding this Privacy Policy or our privacy practices, please contact us at:</p>
                        <ul class="list-unstyled">
                            <li>Email: <a href="mailto:jazhinwi@gmail.com">jazhinwi@gmail.com</a></li>
                            <li>Phone: <a href="tel:+237677069985">+237 677 069 985</a></li>
                            <li>Address: Yaounde, Cameroon, Central Africa</li>
                        </ul>
                        <p>We will make every effort to resolve your concerns promptly and thoroughly.</p>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>
