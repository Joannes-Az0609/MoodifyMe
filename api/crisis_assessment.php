<?php
/**
 * Crisis Assessment API Endpoint
 * Provides crisis detection and intervention for the main PHP application
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

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session
session_start();

// Initialize response
$response = [
    'success' => false,
    'error' => null,
    'data' => null,
    'timestamp' => date('c')
];

try {
    // Only allow POST requests for crisis assessment
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST requests are allowed');
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required fields
    if (!isset($input['message']) || empty(trim($input['message']))) {
        throw new Exception('Message is required');
    }

    $message = trim($input['message']);
    $userId = $input['userId'] ?? ($_SESSION['user_id'] ?? 'anonymous');
    $conversationHistory = $input['conversationHistory'] ?? [];
    $action = $input['action'] ?? 'assess';

    // Initialize crisis detection
    $crisisDetection = new CrisisDetection();

    switch ($action) {
        case 'assess':
            // Perform crisis assessment
            $assessment = $crisisDetection->assessCrisisRisk($message, $userId, $conversationHistory);
            
            // Log high-risk assessments
            if ($assessment['riskLevel'] >= 6) {
                error_log("HIGH RISK CRISIS ASSESSMENT - User: $userId, Risk: {$assessment['riskLevel']}, Type: {$assessment['crisisType']}");
            }
            
            $response['success'] = true;
            $response['data'] = [
                'assessment' => $assessment,
                'requiresIntervention' => $assessment['requiresIntervention'],
                'aiAssistantAvailable' => $crisisDetection->isAiAssistantAvailable()
            ];
            break;

        case 'intervene':
            // Get crisis assessment from input
            $crisisAssessment = $input['crisisAssessment'] ?? null;
            if (!$crisisAssessment) {
                throw new Exception('Crisis assessment is required for intervention');
            }

            // Get intervention response
            $intervention = $crisisDetection->getCrisisIntervention(
                $crisisAssessment,
                $userId,
                $message,
                $input['conversationContext'] ?? []
            );

            // Log intervention
            error_log("CRISIS INTERVENTION EXECUTED - User: $userId, Level: {$intervention['level']}, Type: {$intervention['type']}");

            $response['success'] = true;
            $response['data'] = [
                'intervention' => $intervention,
                'crisisLevel' => $crisisAssessment['riskLevel']
            ];
            break;

        case 'resources':
            // Get crisis resources
            $resourceType = $input['resourceType'] ?? 'crisis';
            $location = $input['location'] ?? null;
            
            $resources = $crisisDetection->getCrisisResources($userId, $location, $resourceType);
            
            $response['success'] = true;
            $response['data'] = [
                'resources' => $resources,
                'type' => $resourceType
            ];
            break;

        case 'emergency':
            // Emergency contact - log immediately
            error_log("EMERGENCY CRISIS CONTACT - User: $userId, Message: " . substr($message, 0, 100) . "...");
            
            // Get emergency resources
            $emergencyResources = $crisisDetection->getCrisisResources($userId, null, 'emergency');
            
            $response['success'] = true;
            $response['data'] = [
                'emergency' => true,
                'message' => '🚨 Emergency support activated. Please contact emergency services immediately.',
                'resources' => $emergencyResources,
                'immediateActions' => [
                    'Call 911 if in immediate physical danger',
                    'Call 988 for suicide crisis support',
                    'Text HOME to 741741 for crisis text support',
                    'Go to nearest emergency room',
                    'Call a trusted friend or family member'
                ]
            ];
            break;

        default:
            throw new Exception('Invalid action specified');
    }

    // Save crisis interaction to database if user is logged in
    if (isset($_SESSION['user_id']) && $action === 'assess') {
        try {
            $pdo = getDatabaseConnection();
            $stmt = $pdo->prepare("
                INSERT INTO crisis_interactions (user_id, message, risk_level, crisis_type, assessment_data, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                substr($message, 0, 500), // Limit message length
                $assessment['riskLevel'],
                $assessment['crisisType'],
                json_encode($assessment)
            ]);
        } catch (Exception $e) {
            // Don't fail the response if database logging fails
            error_log("Failed to log crisis interaction: " . $e->getMessage());
        }
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
    
    // Log error
    error_log("Crisis assessment API error: " . $e->getMessage());
    
    // Provide fallback crisis resources on error
    if (strpos($e->getMessage(), 'Crisis assessment') !== false || 
        strpos($e->getMessage(), 'intervention') !== false) {
        
        $response['fallback'] = [
            'message' => '🚨 If you are in crisis, please contact emergency services immediately.',
            'resources' => [
                'emergency' => '911',
                'crisis' => '988',
                'text' => '741741'
            ],
            'note' => 'Crisis assessment system temporarily unavailable'
        ];
    }
    
    http_response_code(500);
}

// Output JSON response
echo json_encode($response, JSON_PRETTY_PRINT);

// Create crisis_interactions table if it doesn't exist
function createCrisisInteractionsTable() {
    try {
        $pdo = getDatabaseConnection();
        $sql = "
        CREATE TABLE IF NOT EXISTS crisis_interactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            message TEXT,
            risk_level INT,
            crisis_type VARCHAR(50),
            assessment_data JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_risk_level (risk_level),
            INDEX idx_created_at (created_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $pdo->exec($sql);
    } catch (Exception $e) {
        error_log("Failed to create crisis_interactions table: " . $e->getMessage());
    }
}

// Create table on first run
createCrisisInteractionsTable();
?>
