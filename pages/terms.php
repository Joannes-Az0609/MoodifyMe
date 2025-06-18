<?php
/**
 * MoodifyMe - Terms of Service Page
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
                    <h1 class="card-title">Terms of Service</h1>
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
                        <a href="#acceptance" class="list-group-item list-group-item-action">1. Acceptance of Terms</a>
                        <a href="#changes" class="list-group-item list-group-item-action">2. Changes to Terms</a>
                        <a href="#access" class="list-group-item list-group-item-action">3. Access and Use</a>
                        <a href="#accounts" class="list-group-item list-group-item-action">4. User Accounts</a>
                        <a href="#content" class="list-group-item list-group-item-action">5. User Content</a>
                        <a href="#privacy" class="list-group-item list-group-item-action">6. Privacy</a>
                        <a href="#intellectual" class="list-group-item list-group-item-action">7. Intellectual Property</a>
                        <a href="#disclaimer" class="list-group-item list-group-item-action">8. Disclaimer</a>
                        <a href="#limitation" class="list-group-item list-group-item-action">9. Limitation of Liability</a>
                        <a href="#termination" class="list-group-item list-group-item-action">10. Termination</a>
                        <a href="#governing" class="list-group-item list-group-item-action">11. Governing Law</a>
                        <a href="#contact" class="list-group-item list-group-item-action">12. Contact Information</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <section id="acceptance" class="mb-5">
                        <h2>1. Acceptance of Terms</h2>
                        <p>By accessing or using MoodifyMe ("the Service"), you agree to be bound by these Terms of Service ("Terms"). If you do not agree to these Terms, you may not access or use the Service.</p>
                        <p>These Terms constitute a legally binding agreement between you and MoodifyMe regarding your use of the Service. The Service is intended for users who are at least 13 years of age. By using the Service, you represent and warrant that you meet this requirement.</p>
                    </section>
                    
                    <section id="changes" class="mb-5">
                        <h2>2. Changes to Terms</h2>
                        <p>We reserve the right to modify these Terms at any time. If we make changes, we will provide notice by posting the updated Terms on the Service and updating the "Last updated" date. Your continued use of the Service after any such changes constitutes your acceptance of the new Terms.</p>
                        <p>We encourage you to review the Terms whenever you access or use the Service to stay informed about our terms and conditions.</p>
                    </section>
                    
                    <section id="access" class="mb-5">
                        <h2>3. Access and Use</h2>
                        <p>Subject to these Terms, we grant you a limited, non-exclusive, non-transferable, and revocable license to access and use the Service for your personal, non-commercial use.</p>
                        <p>You agree not to:</p>
                        <ul>
                            <li>Use the Service in any way that violates any applicable law or regulation</li>
                            <li>Use the Service for any harmful, fraudulent, or deceptive purpose</li>
                            <li>Attempt to gain unauthorized access to any part of the Service</li>
                            <li>Interfere with or disrupt the Service or servers or networks connected to the Service</li>
                            <li>Use any robot, spider, or other automated device to access the Service</li>
                            <li>Introduce any viruses, trojan horses, worms, or other harmful material</li>
                        </ul>
                    </section>
                    
                    <section id="accounts" class="mb-5">
                        <h2>4. User Accounts</h2>
                        <p>To access certain features of the Service, you may need to create an account. You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.</p>
                        <p>You agree to:</p>
                        <ul>
                            <li>Provide accurate and complete information when creating your account</li>
                            <li>Update your information as necessary to keep it accurate and current</li>
                            <li>Notify us immediately of any unauthorized use of your account or any other breach of security</li>
                            <li>Accept responsibility for all activities that occur under your account</li>
                        </ul>
                        <p>We reserve the right to suspend or terminate your account if any information provided is inaccurate, false, or no longer current, or if you have violated these Terms.</p>
                    </section>
                    
                    <section id="content" class="mb-5">
                        <h2>5. User Content</h2>
                        <p>The Service allows you to submit, store, and share content, including text, images, and other materials ("User Content"). You retain all rights in your User Content, but you grant us a worldwide, non-exclusive, royalty-free license to use, reproduce, modify, adapt, publish, translate, and distribute your User Content in connection with the Service.</p>
                        <p>You represent and warrant that:</p>
                        <ul>
                            <li>You own or have the necessary rights to your User Content</li>
                            <li>Your User Content does not violate the privacy rights, publicity rights, copyright, contractual rights, or any other rights of any person or entity</li>
                            <li>Your User Content does not contain any material that is defamatory, obscene, indecent, abusive, offensive, harassing, violent, hateful, inflammatory, or otherwise objectionable</li>
                        </ul>
                        <p>We reserve the right to remove any User Content that violates these Terms or that we determine is harmful, offensive, or otherwise inappropriate.</p>
                    </section>
                    
                    <section id="privacy" class="mb-5">
                        <h2>6. Privacy</h2>
                        <p>Your privacy is important to us. Our <a href="<?php echo APP_URL; ?>/pages/privacy.php">Privacy Policy</a> explains how we collect, use, and protect your personal information. By using the Service, you agree to the collection and use of information in accordance with our Privacy Policy.</p>
                    </section>
                    
                    <section id="intellectual" class="mb-5">
                        <h2>7. Intellectual Property</h2>
                        <p>The Service and its original content, features, and functionality are owned by MoodifyMe and are protected by international copyright, trademark, patent, trade secret, and other intellectual property or proprietary rights laws.</p>
                        <p>You may not copy, modify, create derivative works of, publicly display, publicly perform, republish, or transmit any of the material on our Service without prior written consent.</p>
                    </section>
                    
                    <section id="disclaimer" class="mb-5">
                        <h2>8. Disclaimer</h2>
                        <p>THE SERVICE IS PROVIDED ON AN "AS IS" AND "AS AVAILABLE" BASIS. MOODIFYME EXPRESSLY DISCLAIMS ALL WARRANTIES OF ANY KIND, WHETHER EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT.</p>
                        <p>MOODIFYME MAKES NO WARRANTY THAT:</p>
                        <ul>
                            <li>THE SERVICE WILL MEET YOUR REQUIREMENTS</li>
                            <li>THE SERVICE WILL BE UNINTERRUPTED, TIMELY, SECURE, OR ERROR-FREE</li>
                            <li>THE RESULTS THAT MAY BE OBTAINED FROM THE USE OF THE SERVICE WILL BE ACCURATE OR RELIABLE</li>
                        </ul>
                        <p>MOODIFYME IS NOT A MEDICAL OR MENTAL HEALTH SERVICE. THE SERVICE IS NOT INTENDED TO DIAGNOSE, TREAT, CURE, OR PREVENT ANY DISEASE OR CONDITION. THE SERVICE IS NOT A SUBSTITUTE FOR PROFESSIONAL MEDICAL ADVICE, DIAGNOSIS, OR TREATMENT.</p>
                    </section>
                    
                    <section id="limitation" class="mb-5">
                        <h2>9. Limitation of Liability</h2>
                        <p>IN NO EVENT SHALL MOODIFYME, ITS DIRECTORS, EMPLOYEES, PARTNERS, AGENTS, SUPPLIERS, OR AFFILIATES, BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, INCLUDING WITHOUT LIMITATION, LOSS OF PROFITS, DATA, USE, GOODWILL, OR OTHER INTANGIBLE LOSSES, RESULTING FROM:</p>
                        <ul>
                            <li>YOUR ACCESS TO OR USE OF OR INABILITY TO ACCESS OR USE THE SERVICE</li>
                            <li>ANY CONDUCT OR CONTENT OF ANY THIRD PARTY ON THE SERVICE</li>
                            <li>ANY CONTENT OBTAINED FROM THE SERVICE</li>
                            <li>UNAUTHORIZED ACCESS, USE, OR ALTERATION OF YOUR TRANSMISSIONS OR CONTENT</li>
                        </ul>
                    </section>
                    
                    <section id="termination" class="mb-5">
                        <h2>10. Termination</h2>
                        <p>We may terminate or suspend your account and access to the Service immediately, without prior notice or liability, for any reason, including without limitation if you breach these Terms.</p>
                        <p>Upon termination, your right to use the Service will immediately cease. If you wish to terminate your account, you may simply discontinue using the Service or contact us to request account deletion.</p>
                    </section>
                    
                    <section id="governing" class="mb-5">
                        <h2>11. Governing Law</h2>
                        <p>These Terms shall be governed by and construed in accordance with the laws of the United States, without regard to its conflict of law provisions.</p>
                        <p>Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights. If any provision of these Terms is held to be invalid or unenforceable by a court, the remaining provisions of these Terms will remain in effect.</p>
                    </section>
                    
                    <section id="contact" class="mb-5">
                        <h2>12. Contact Information</h2>
                        <p>If you have any questions about these Terms, please contact us at:</p>
                        <ul class="list-unstyled">
                            <li>Email: <a href="mailto:jazhinwi@gmail.com">jazhinwi@gmail.com</a></li>
                            <li>Phone: <a href="tel:+237677069985">+237 677 069 985</a></li>
                            <li>Address: Yaounde, Cameroon, Central Africa</li>
                        </ul>
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
