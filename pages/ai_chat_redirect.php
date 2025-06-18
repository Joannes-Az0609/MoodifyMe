<?php
/**
 * MoodifyMe - AI Chat Redirect Page
 * Redirects to MoodifyMe Assistant with context
 */

// Include configuration
require_once '../config.php';

// Get parameters
$sourceEmotion = $_GET['source'] ?? '';
$targetEmotion = $_GET['target'] ?? '';
$emotionId = $_GET['emotion_id'] ?? '';

// Create context message for the AI
$contextMessage = '';
if ($sourceEmotion && $targetEmotion) {
    $contextMessage = "I'm currently feeling {$sourceEmotion} and I want to feel {$targetEmotion}. Can you help me with this emotional transition?";
}

// Encode the context message for URL
$encodedMessage = urlencode($contextMessage);

// MoodifyMe Assistant URL
$aiAssistantUrl = "http://localhost:3001";

// If we have context, we'll pass it via JavaScript to auto-send the message
if ($contextMessage) {
    $aiAssistantUrl .= "?context=" . $encodedMessage;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connecting to AI Assistant - MoodifyMe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .redirect-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        .spinner {
            width: 3rem;
            height: 3rem;
            margin: 2rem auto;
        }
        .context-preview {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin: 1rem 0;
            font-style: italic;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="redirect-card">
        <div class="mb-4">
            <i class="fas fa-robot fa-4x text-primary mb-3"></i>
            <h2>Connecting to AI Assistant</h2>
            <p class="text-muted">Preparing your personalized chat session...</p>
        </div>

        <?php if ($contextMessage): ?>
        <div class="context-preview">
            <strong>Context:</strong><br>
            "<?php echo htmlspecialchars($contextMessage); ?>"
        </div>
        <p class="small text-muted">This context will be shared with the AI to provide better support.</p>
        <?php endif; ?>

        <div class="spinner-border text-primary spinner" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>

        <div class="mt-4">
            <p class="small text-muted">
                If you're not redirected automatically,
                <a href="<?php echo $aiAssistantUrl; ?>" id="manualLink">click here</a>
            </p>
            <div class="mt-3">
                <a href="<?php echo APP_URL; ?>/pages/recommendations.php<?php
                    echo ($sourceEmotion && $targetEmotion) ?
                        "?source=" . urlencode($sourceEmotion) . "&target=" . urlencode($targetEmotion) . "&emotion_id=" . urlencode($emotionId) :
                        "";
                ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Recommendations
                </a>
            </div>
        </div>
    </div>

    <script>
        // Simple direct redirect after 3 seconds
        setTimeout(function() {
            window.location.href = '<?php echo $aiAssistantUrl; ?>';
        }, 3000);

        // Manual link click handler
        document.getElementById('manualLink').addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '<?php echo $aiAssistantUrl; ?>';
        });
    </script>
</body>
</html>
