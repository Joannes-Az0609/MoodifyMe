<?php
/**
 * Crisis Detection Integration for MoodifyMe
 * Integrates with Node.js AI Assistant crisis detection system
 */

class CrisisDetection {
    private $aiAssistantUrl;
    private $timeout;
    
    public function __construct() {
        $this->aiAssistantUrl = defined('AI_ASSISTANT_URL') ? AI_ASSISTANT_URL : 'http://localhost:3000';
        $this->timeout = 10; // 10 second timeout
    }
    
    /**
     * Assess crisis risk for user input
     */
    public function assessCrisisRisk($message, $userId = null, $conversationHistory = []) {
        try {
            $endpoint = $this->aiAssistantUrl . '/api/crisis/assess';
            
            $data = [
                'message' => $message,
                'userId' => $userId,
                'conversationHistory' => $conversationHistory
            ];
            
            $response = $this->makeApiCall($endpoint, $data);
            
            if ($response && isset($response['success']) && $response['success']) {
                return $response['assessment'];
            }
            
            // Fallback to basic detection if AI service fails
            return $this->basicCrisisDetection($message);
            
        } catch (Exception $e) {
            error_log("Crisis assessment error: " . $e->getMessage());
            return $this->basicCrisisDetection($message);
        }
    }
    
    /**
     * Get crisis intervention response
     */
    public function getCrisisIntervention($crisisAssessment, $userId, $userMessage, $conversationContext = []) {
        try {
            $endpoint = $this->aiAssistantUrl . '/api/crisis/intervene';
            
            $data = [
                'crisisAssessment' => $crisisAssessment,
                'userId' => $userId,
                'userMessage' => $userMessage,
                'conversationContext' => $conversationContext
            ];
            
            $response = $this->makeApiCall($endpoint, $data);
            
            if ($response && isset($response['success']) && $response['success']) {
                return $response['intervention'];
            }
            
            // Fallback intervention
            return $this->getEmergencyIntervention($crisisAssessment);
            
        } catch (Exception $e) {
            error_log("Crisis intervention error: " . $e->getMessage());
            return $this->getEmergencyIntervention($crisisAssessment);
        }
    }
    
    /**
     * Get crisis resources
     */
    public function getCrisisResources($userId = null, $location = null, $type = 'crisis') {
        try {
            $endpoint = $this->aiAssistantUrl . '/api/crisis/resources';
            $params = [
                'userId' => $userId,
                'location' => $location,
                'type' => $type
            ];
            
            $url = $endpoint . '?' . http_build_query(array_filter($params));
            $response = $this->makeApiCall($url, null, 'GET');
            
            if ($response && isset($response['success']) && $response['success']) {
                return $response['resources'];
            }
            
            return $this->getFallbackResources();
            
        } catch (Exception $e) {
            error_log("Crisis resources error: " . $e->getMessage());
            return $this->getFallbackResources();
        }
    }
    
    /**
     * Basic crisis detection fallback
     */
    private function basicCrisisDetection($message) {
        $crisisKeywords = [
            'suicide' => ['kill myself', 'end my life', 'suicide', 'want to die', 'better off dead'],
            'selfHarm' => ['cut myself', 'hurt myself', 'self harm', 'self-harm', 'cutting'],
            'violence' => ['hurt someone', 'kill someone', 'violent thoughts'],
            'severe' => ['can\'t go on', 'hopeless', 'no point', 'give up', 'nothing matters']
        ];
        
        $lowerMessage = strtolower($message);
        $riskLevel = 0;
        $crisisType = null;
        $triggers = [];
        
        foreach ($crisisKeywords as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($lowerMessage, $keyword) !== false) {
                    $triggers[] = $keyword;
                    $typeRisk = $this->getRiskLevelForType($type);
                    if ($typeRisk > $riskLevel) {
                        $riskLevel = $typeRisk;
                        $crisisType = $type;
                    }
                }
            }
        }
        
        return [
            'riskLevel' => $riskLevel,
            'severity' => $this->categorizeSeverity($riskLevel),
            'crisisType' => $crisisType,
            'confidence' => $riskLevel > 0 ? 0.7 : 0.1,
            'immediacy' => $riskLevel >= 8 ? 'high' : ($riskLevel >= 5 ? 'medium' : 'low'),
            'triggers' => $triggers,
            'requiresIntervention' => $riskLevel >= 3,
            'fallback' => true
        ];
    }
    
    private function getRiskLevelForType($type) {
        $riskLevels = [
            'suicide' => 10,
            'violence' => 10,
            'selfHarm' => 8,
            'severe' => 6
        ];
        
        return $riskLevels[$type] ?? 0;
    }
    
    private function categorizeSeverity($riskLevel) {
        if ($riskLevel >= 9) return 'critical';
        if ($riskLevel >= 7) return 'high';
        if ($riskLevel >= 5) return 'medium';
        if ($riskLevel >= 3) return 'low';
        return 'minimal';
    }
    
    /**
     * Emergency intervention fallback
     */
    private function getEmergencyIntervention($crisisAssessment) {
        $riskLevel = $crisisAssessment['riskLevel'] ?? 0;
        
        if ($riskLevel >= 9) {
            $message = "🚨 **IMMEDIATE CRISIS SUPPORT NEEDED** 🚨\n\n";
            $message .= "I'm very concerned about your safety. Please reach out for immediate professional help:\n\n";
            $message .= "**🆘 IMMEDIATE RESOURCES:**\n";
            $message .= "• **Call 988** - National Suicide Prevention Lifeline (24/7)\n";
            $message .= "• **Text HOME to 741741** - Crisis Text Line\n";
            $message .= "• **Call 911** - Emergency Services\n\n";
            $message .= "You don't have to face this alone. Professional help is available right now.";
        } else if ($riskLevel >= 6) {
            $message = "💙 **CRISIS SUPPORT AVAILABLE** 💙\n\n";
            $message .= "I can see you're going through a very difficult time. Please consider reaching out for professional support:\n\n";
            $message .= "**🤝 SUPPORT RESOURCES:**\n";
            $message .= "• **Crisis Text Line:** Text HOME to 741741\n";
            $message .= "• **National Crisis Line:** 988\n";
            $message .= "• **National Alliance on Mental Illness:** 1-800-950-NAMI\n\n";
            $message .= "These feelings can improve with proper support. You deserve care and healing.";
        } else {
            $message = "💙 **SUPPORT & VALIDATION** 💙\n\n";
            $message .= "I want to acknowledge the difficult emotions you're experiencing. Your feelings are valid and important.\n\n";
            $message .= "**🌟 GENTLE REMINDERS:**\n";
            $message .= "• These feelings are temporary, even when they feel overwhelming\n";
            $message .= "• Seeking support is a sign of strength\n";
            $message .= "• You have survived difficult times before\n\n";
            $message .= "I'm here to listen and support you. What would feel most helpful right now?";
        }
        
        return [
            'type' => 'EMERGENCY_FALLBACK',
            'level' => $riskLevel >= 9 ? 'CRITICAL' : ($riskLevel >= 6 ? 'HIGH' : 'MODERATE'),
            'message' => $message,
            'priority' => $riskLevel >= 9 ? 'IMMEDIATE' : 'URGENT',
            'resources' => $this->getFallbackResources(),
            'fallback' => true
        ];
    }
    
    /**
     * Fallback crisis resources
     */
    private function getFallbackResources() {
        return [
            'immediate' => [
                'emergency' => [
                    'number' => '911',
                    'description' => 'Emergency services'
                ],
                'suicide' => [
                    'name' => '988 Suicide & Crisis Lifeline',
                    'phone' => '988',
                    'description' => '24/7 crisis support'
                ],
                'crisis' => [
                    'name' => 'Crisis Text Line',
                    'text' => '741741',
                    'description' => 'Text HOME to 741741'
                ]
            ]
        ];
    }
    
    /**
     * Make API call to AI Assistant
     */
    private function makeApiCall($url, $data = null, $method = 'POST') {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ]);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("CURL Error: " . $error);
        }
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: " . $httpCode);
        }
        
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON Decode Error: " . json_last_error_msg());
        }
        
        return $decoded;
    }
    
    /**
     * Check if AI Assistant is available
     */
    public function isAiAssistantAvailable() {
        try {
            $endpoint = $this->aiAssistantUrl . '/api/crisis/health';
            $response = $this->makeApiCall($endpoint, null, 'GET');
            return isset($response['success']) && $response['success'];
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
