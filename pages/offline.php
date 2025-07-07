<?php
/**
 * MoodifyMe - Offline Page
 * Displayed when user is offline and tries to access unavailable content
 */

session_start();
require_once '../config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline - <?php echo APP_NAME; ?></title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#007bff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .offline-container {
            text-align: center;
            color: white;
            max-width: 500px;
            padding: 2rem;
        }
        
        .offline-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            opacity: 0.8;
            animation: pulse 2s infinite;
        }
        
        .offline-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .offline-message {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .offline-features {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 2rem 0;
            backdrop-filter: blur(10px);
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }
        
        .feature-item:last-child {
            margin-bottom: 0;
        }
        
        .feature-item i {
            margin-right: 0.75rem;
            width: 20px;
            color: #28a745;
        }
        
        .retry-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem;
        }
        
        .retry-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
            transform: translateY(-2px);
        }
        
        .connection-status {
            margin-top: 2rem;
            padding: 1rem;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
        }
        
        .status-online {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .status-offline {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @media (max-width: 768px) {
            .offline-container {
                padding: 1rem;
            }
            
            .offline-icon {
                font-size: 4rem;
            }
            
            .offline-title {
                font-size: 2rem;
            }
            
            .offline-message {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon">
            <i class="fas fa-wifi" id="wifi-icon"></i>
        </div>
        
        <h1 class="offline-title">You're Offline</h1>
        
        <p class="offline-message">
            It looks like you've lost your internet connection. Don't worry - some features of MoodifyMe are still available offline!
        </p>
        
        <div class="offline-features">
            <h5 style="margin-bottom: 1rem; font-weight: 600;">Available Offline:</h5>
            <div class="feature-item">
                <i class="fas fa-check"></i>
                <span>View previously loaded content</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check"></i>
                <span>Access cached mood tracking data</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check"></i>
                <span>Browse saved conversations</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check"></i>
                <span>Use basic app navigation</span>
            </div>
        </div>
        
        <div class="connection-status" id="connection-status">
            <i class="fas fa-circle" id="status-indicator"></i>
            <span id="status-text">Checking connection...</span>
        </div>
        
        <div style="margin-top: 2rem;">
            <a href="javascript:location.reload()" class="retry-btn">
                <i class="fas fa-redo"></i> Try Again
            </a>
            <a href="<?php echo APP_URL; ?>/pages/dashboard.php" class="retry-btn">
                <i class="fas fa-home"></i> Go Home
            </a>
        </div>
    </div>

    <script>
        // Check connection status
        function updateConnectionStatus() {
            const statusIndicator = document.getElementById('status-indicator');
            const statusText = document.getElementById('status-text');
            const connectionStatus = document.getElementById('connection-status');
            const wifiIcon = document.getElementById('wifi-icon');
            
            if (navigator.onLine) {
                statusIndicator.style.color = '#28a745';
                statusText.textContent = 'Connection restored! You can now access all features.';
                connectionStatus.className = 'connection-status status-online';
                wifiIcon.className = 'fas fa-wifi';
                
                // Auto-redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = '<?php echo APP_URL; ?>/pages/dashboard.php';
                }, 2000);
            } else {
                statusIndicator.style.color = '#dc3545';
                statusText.textContent = 'No internet connection detected.';
                connectionStatus.className = 'connection-status status-offline';
                wifiIcon.className = 'fas fa-wifi';
                wifiIcon.style.opacity = '0.5';
            }
        }
        
        // Initial check
        updateConnectionStatus();
        
        // Listen for connection changes
        window.addEventListener('online', updateConnectionStatus);
        window.addEventListener('offline', updateConnectionStatus);
        
        // Periodic check every 5 seconds
        setInterval(updateConnectionStatus, 5000);
        
        // Auto-retry every 30 seconds
        setInterval(() => {
            if (navigator.onLine) {
                fetch('<?php echo APP_URL; ?>/api/ping.php')
                    .then(response => {
                        if (response.ok) {
                            window.location.href = '<?php echo APP_URL; ?>/pages/dashboard.php';
                        }
                    })
                    .catch(() => {
                        // Still offline
                    });
            }
        }, 30000);
    </script>
</body>
</html>
