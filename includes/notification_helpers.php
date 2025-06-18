<?php
/**
 * MoodifyMe - Notification Helper Functions
 * Send notifications via various channels
 */

/**
 * Send Telegram notification
 * @param string $message Message to send
 * @return bool Success status
 */
function sendTelegramNotification($message) {
    // Replace with your Telegram Bot Token and Chat ID
    $botToken = 'YOUR_TELEGRAM_BOT_TOKEN';
    $chatId = 'YOUR_TELEGRAM_CHAT_ID';
    
    if ($botToken === 'YOUR_TELEGRAM_BOT_TOKEN') {
        return false; // Not configured
    }
    
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}

/**
 * Send WhatsApp notification via Twilio
 * @param string $message Message to send
 * @return bool Success status
 */
function sendWhatsAppNotification($message) {
    // Replace with your Twilio credentials
    $accountSid = 'YOUR_TWILIO_ACCOUNT_SID';
    $authToken = 'YOUR_TWILIO_AUTH_TOKEN';
    $fromNumber = 'whatsapp:+14155238886'; // Twilio Sandbox number
    $toNumber = 'whatsapp:+237677069985'; // Your WhatsApp number
    
    if ($accountSid === 'YOUR_TWILIO_ACCOUNT_SID') {
        return false; // Not configured
    }
    
    $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";
    
    $data = [
        'From' => $fromNumber,
        'To' => $toNumber,
        'Body' => $message
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "{$accountSid}:{$authToken}");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 201;
}

/**
 * Send Discord notification via webhook
 * @param string $message Message to send
 * @return bool Success status
 */
function sendDiscordNotification($message) {
    // Replace with your Discord webhook URL
    $webhookUrl = 'YOUR_DISCORD_WEBHOOK_URL';
    
    if ($webhookUrl === 'YOUR_DISCORD_WEBHOOK_URL') {
        return false; // Not configured
    }
    
    $data = [
        'content' => $message,
        'username' => 'MoodifyMe Contact Form'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhookUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 204;
}

/**
 * Format contact form message for notifications
 * @param array $formData Contact form data
 * @return string Formatted message
 */
function formatContactNotification($formData) {
    $message = "🔔 New MoodifyMe Contact Form Submission\n\n";
    $message .= "👤 Name: {$formData['name']}\n";
    $message .= "📧 Email: {$formData['email']}\n";
    $message .= "📋 Subject: {$formData['subject']}\n";
    $message .= "💬 Message:\n{$formData['message']}\n\n";
    $message .= "🕒 Time: " . date('Y-m-d H:i:s') . "\n";
    $message .= "🌐 IP: {$formData['ip']}";
    
    return $message;
}

/**
 * Send all configured notifications
 * @param array $formData Contact form data
 * @return array Results of each notification method
 */
function sendAllNotifications($formData) {
    $message = formatContactNotification($formData);
    
    $results = [
        'telegram' => sendTelegramNotification($message),
        'whatsapp' => sendWhatsAppNotification($message),
        'discord' => sendDiscordNotification($message)
    ];
    
    return $results;
}
?>
