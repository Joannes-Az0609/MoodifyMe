<?php
/**
 * MoodifyMe - Email Functions
 * Enhanced email sending with SMTP support
 */

/**
 * Send email using Gmail SMTP
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email body
 * @param string $replyTo Reply-to email
 * @param string $replyName Reply-to name
 * @return bool Success status
 */
function sendGmailSMTP($to, $subject, $body, $replyTo = '', $replyName = '') {
    // Gmail SMTP configuration
    $smtpHost = 'smtp.gmail.com';
    $smtpPort = 587;
    $smtpUsername = 'jazhinwi@gmail.com'; // Your Gmail address
    $smtpPassword = 'your_app_password_here'; // Your Gmail App Password
    
    // If app password not configured, fall back to basic mail()
    if ($smtpPassword === 'your_app_password_here') {
        return sendBasicEmail($to, $subject, $body, $replyTo);
    }
    
    // Create email headers
    $headers = "From: MoodifyMe <jazhinwi@gmail.com>\r\n";
    if (!empty($replyTo)) {
        $headers .= "Reply-To: " . (!empty($replyName) ? "$replyName <$replyTo>" : $replyTo) . "\r\n";
    }
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: MoodifyMe Contact Form\r\n";
    
    // Try to use PHPMailer if available, otherwise use basic mail()
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return sendWithPHPMailer($to, $subject, $body, $replyTo, $replyName);
    } else {
        return sendBasicEmail($to, $subject, $body, $replyTo);
    }
}

/**
 * Send email using basic PHP mail() function
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email body
 * @param string $replyTo Reply-to email
 * @return bool Success status
 */
function sendBasicEmail($to, $subject, $body, $replyTo = '') {
    $headers = "From: MoodifyMe <noreply@moodifyme.com>\r\n";
    if (!empty($replyTo)) {
        $headers .= "Reply-To: $replyTo\r\n";
    }
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    return @mail($to, $subject, $body, $headers);
}

/**
 * Send email using PHPMailer with Gmail SMTP
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email body
 * @param string $replyTo Reply-to email
 * @param string $replyName Reply-to name
 * @return bool Success status
 */
function sendWithPHPMailer($to, $subject, $body, $replyTo = '', $replyName = '') {
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jazhinwi@gmail.com';
        $mail->Password = 'your_app_password_here'; // Your Gmail App Password
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('jazhinwi@gmail.com', 'MoodifyMe');
        $mail->addAddress($to);
        
        if (!empty($replyTo)) {
            $mail->addReplyTo($replyTo, $replyName ?: '');
        }
        
        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send HTML email with template
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param array $data Template data
 * @param string $template Template name
 * @return bool Success status
 */
function sendTemplateEmail($to, $subject, $data, $template = 'contact_form') {
    $htmlBody = generateEmailTemplate($template, $data);
    $textBody = strip_tags(str_replace('<br>', "\n", $htmlBody));
    
    // Try to send HTML email
    $headers = "From: MoodifyMe <jazhinwi@gmail.com>\r\n";
    $headers .= "Reply-To: " . ($data['reply_to'] ?? 'jazhinwi@gmail.com') . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return @mail($to, $subject, $htmlBody, $headers);
}

/**
 * Generate email template
 * @param string $template Template name
 * @param array $data Template data
 * @return string HTML content
 */
function generateEmailTemplate($template, $data) {
    switch ($template) {
        case 'contact_form':
            return generateContactFormTemplate($data);
        default:
            return generateBasicTemplate($data);
    }
}

/**
 * Generate contact form email template
 * @param array $data Form data
 * @return string HTML content
 */
function generateContactFormTemplate($data) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>MoodifyMe Contact Form</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #FF6B35; color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #555; }
            .value { margin-top: 5px; }
            .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🎭 MoodifyMe Contact Form</h1>
                <p>New message received</p>
            </div>
            <div class="content">
                <div class="field">
                    <div class="label">👤 Name:</div>
                    <div class="value">' . htmlspecialchars($data['name']) . '</div>
                </div>
                <div class="field">
                    <div class="label">📧 Email:</div>
                    <div class="value">' . htmlspecialchars($data['email']) . '</div>
                </div>
                <div class="field">
                    <div class="label">📋 Subject:</div>
                    <div class="value">' . htmlspecialchars($data['subject']) . '</div>
                </div>
                <div class="field">
                    <div class="label">💬 Message:</div>
                    <div class="value">' . nl2br(htmlspecialchars($data['message'])) . '</div>
                </div>
                <div class="field">
                    <div class="label">🕒 Submitted:</div>
                    <div class="value">' . date('Y-m-d H:i:s') . '</div>
                </div>
                <div class="field">
                    <div class="label">🌐 IP Address:</div>
                    <div class="value">' . htmlspecialchars($data['ip'] ?? 'Unknown') . '</div>
                </div>
            </div>
            <div class="footer">
                <p>This message was sent via the MoodifyMe contact form</p>
                <p>Reply directly to this email to respond to the sender</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * Generate basic email template
 * @param array $data Template data
 * @return string HTML content
 */
function generateBasicTemplate($data) {
    $html = '<html><body>';
    $html .= '<h2>MoodifyMe Notification</h2>';
    foreach ($data as $key => $value) {
        $html .= '<p><strong>' . ucfirst($key) . ':</strong> ' . htmlspecialchars($value) . '</p>';
    }
    $html .= '</body></html>';
    
    return $html;
}

/**
 * Test email configuration
 * @return array Test results
 */
function testEmailConfiguration() {
    $results = [
        'basic_mail' => false,
        'smtp_available' => false,
        'phpmailer_available' => false
    ];
    
    // Test basic mail function
    $results['basic_mail'] = function_exists('mail');
    
    // Test PHPMailer availability
    $results['phpmailer_available'] = class_exists('PHPMailer\PHPMailer\PHPMailer');
    
    // Test SMTP connectivity (basic check)
    $results['smtp_available'] = @fsockopen('smtp.gmail.com', 587, $errno, $errstr, 5) !== false;
    
    return $results;
}
?>
