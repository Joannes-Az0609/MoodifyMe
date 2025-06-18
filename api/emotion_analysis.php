<?php
/**
 * MoodifyMe - Emotion Analysis API
 * Analyzes user input (text or voice) to detect emotions
 */

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Return error response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated'
    ]);
    exit;
}

// Get user ID
$userId = $_SESSION['user_id'];

// Set response header
header('Content-Type: application/json');

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get input type
$inputType = '';
$inputData = '';



// Check if request is JSON
$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
if (strpos($contentType, 'application/json') !== false) {
    // Get JSON data
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    // Handle new voice input format
    if (isset($data['text']) && isset($data['source'])) {
        $inputType = $data['source'] === 'voice' ? 'voice' : 'text';
        $inputData = $data['text'];
    }
    // Handle existing format
    elseif (isset($data['input_type'])) {
        $inputType = $data['input_type'];

        if ($inputType === 'text' && isset($data['input_data'])) {
            $inputData = $data['input_data'];
        } elseif ($inputType === 'voice' && isset($data['input_data'])) {
            $inputData = $data['input_data'];
        }
    }
} else {
    // Get form data
    if (isset($_POST['input_type'])) {
        $inputType = $_POST['input_type'];

        if ($inputType === 'text' && isset($_POST['input_data'])) {
            $inputData = $_POST['input_data'];
        } elseif ($inputType === 'text' && isset($_POST['mood_text'])) {
            // For backward compatibility with form submissions
            $inputType = 'text';
            $inputData = $_POST['mood_text'];
        } elseif ($inputType === 'voice' && isset($_POST['audio_data'])) {
            // Voice input - audio_data contains transcribed text
            $inputData = $_POST['audio_data'];
        }
    } elseif (isset($_POST['mood_text'])) {
        // For backward compatibility with form submissions
        $inputType = 'text';
        $inputData = $_POST['mood_text'];

    }
}

// Validate input
if (empty($inputType)) {
    echo json_encode([
        'success' => false,
        'message' => 'Input type is required'
    ]);
    exit;
}

// Define the Python API URL
define('EMOTION_API_URL', 'http://localhost:5000');

// Process input based on type
$emotion = '';
$confidence = 0;
$apiResponse = null;

switch ($inputType) {
    case 'text':
    case 'voice':
        // Validate text input (voice input is transcribed to text)
        if (empty($inputData)) {
            echo json_encode([
                'success' => false,
                'message' => 'Text input is required'
            ]);
            exit;
        }

        // Try Python API first, but always fallback to enhanced keyword analysis
        $pythonApiWorked = false;

        // Call the Python API for text emotion analysis (if available)
        $apiUrl = EMOTION_API_URL . '/analyze_text';
        $postData = json_encode(['text' => $inputData]);

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Reduced timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // Quick connection timeout

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Check if Python API worked
        if (!$error && $httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && isset($result['success']) && $result['success']) {
                $emotion = $result['emotion'];
                $confidence = $result['confidence'];
                $apiResponse = $result;
                $pythonApiWorked = true;

                // Check if emotion is unknown and needs clarification
                if ($emotion === 'unknown' && isset($result['needs_clarification']) && $result['needs_clarification']) {
                    $apiResponse['needs_clarification'] = true;
                    $apiResponse['clarification_message'] = $result['message'] ?? 'Could not determine emotion with confidence';
                }
            }
        }

        // If Python API didn't work or returned unknown, use enhanced fallback
        if (!$pythonApiWorked || $emotion === 'unknown') {
            $moodDetected = fallbackTextAnalysis($inputData, $emotion, $confidence);

            if ($moodDetected) {
                // Successfully detected emotion with fallback
                $apiResponse = [
                    'method' => 'enhanced_keyword_analysis',
                    'emotion' => $emotion,
                    'confidence' => $confidence,
                    'success' => true
                ];
            } else {
                // No mood detected even with enhanced analysis
                $apiResponse = [
                    'method' => 'enhanced_keyword_analysis',
                    'needs_clarification' => true,
                    'clarification_message' => 'Could not detect a clear emotion in your text. Please try being more specific about how you feel, for example: "I feel happy" or "I am stressed".'
                ];
                $emotion = 'unknown';
                $confidence = 0;
            }
        }

        break;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid input type'
        ]);
        exit;
}

/**
 * Enhanced fallback text analysis using improved keyword matching
 * @param string $text The text to analyze
 * @param string &$emotion The emotion variable to set
 * @param float &$confidence The confidence variable to set
 * @return bool Whether a mood was detected
 */
function fallbackTextAnalysis($text, &$emotion, &$confidence) {
    // Comprehensive emotion keywords and phrases
    $emotionKeywords = [
        'happy' => [
            // Direct words
            'happy', 'joy', 'joyful', 'excited', 'great', 'wonderful', 'fantastic', 'good', 'positive',
            'smile', 'smiling', 'smilling', 'laugh', 'laughing', 'cheerful', 'delighted', 'pleased', 'glad', 'content', 'elated',
            'amazing', 'awesome', 'brilliant', 'excellent', 'upbeat', 'optimistic', 'thrilled',
            'ecstatic', 'overjoyed', 'blissful', 'merry', 'bright', 'sunny', 'radiant',
            // Phrases
            'feel good', 'feeling good', 'feeling great', 'feeling happy', 'feeling wonderful',
            'so happy', 'really happy', 'very happy', 'quite happy', 'pretty happy',
            'in a good mood', 'good mood', 'great mood', 'feeling positive',
            'i am smiling', 'i am smilling', 'am smiling', 'am smilling'
        ],
        'sad' => [
            // Direct words
            'sad', 'unhappy', 'depressed', 'down', 'blue', 'gloomy', 'miserable', 'cry', 'tears',
            'sorrow', 'grief', 'melancholy', 'dejected', 'heartbroken', 'disappointed', 'upset',
            'low', 'hurt', 'pain', 'ache', 'broken', 'empty', 'lonely', 'hopeless',
            // Phrases
            'feel bad', 'feeling bad', 'feeling sad', 'feeling down', 'feeling low',
            'so sad', 'really sad', 'very sad', 'quite sad', 'pretty sad',
            'feeling awful', 'feeling terrible', 'feeling miserable', 'bad mood'
        ],
        'angry' => [
            // Direct words
            'angry', 'mad', 'furious', 'rage', 'annoyed', 'irritated', 'frustrated', 'upset',
            'pissed', 'livid', 'outraged', 'infuriated', 'agitated', 'hostile', 'bitter',
            'resentful', 'indignant', 'cross', 'irate', 'steaming', 'boiling', 'fuming',
            // Phrases
            'feeling angry', 'so angry', 'really angry', 'very angry', 'quite angry',
            'pissed off', 'ticked off', 'fed up', 'had enough', 'really mad'
        ],
        'anxious' => [
            // Direct words
            'anxious', 'worried', 'nervous', 'tense', 'stress', 'stressed', 'fear', 'afraid', 'panic',
            'anxiety', 'concern', 'uneasy', 'restless', 'apprehensive', 'troubled', 'distressed',
            'overwhelmed', 'pressured', 'scared', 'frightened', 'terrified', 'paranoid', 'jittery',
            // Phrases
            'feeling anxious', 'feeling worried', 'feeling nervous', 'feeling stressed',
            'so worried', 'really worried', 'very worried', 'quite worried',
            'stressed out', 'freaking out', 'panicking', 'on edge'
        ],
        'calm' => [
            // Direct words
            'calm', 'peaceful', 'relaxed', 'serene', 'tranquil', 'content', 'quiet', 'still',
            'composed', 'centered', 'balanced', 'zen', 'mellow', 'chill', 'comfortable',
            'settled', 'stable', 'grounded', 'steady', 'cool', 'collected',
            // Phrases
            'feeling calm', 'feeling peaceful', 'feeling relaxed', 'at peace',
            'so calm', 'really calm', 'very calm', 'quite calm', 'pretty calm',
            'chilled out', 'calmed down', 'feeling zen'
        ],
        'excited' => [
            // Direct words
            'excited', 'thrilled', 'enthusiastic', 'eager', 'energetic', 'pumped', 'hyped',
            'amped', 'psyched', 'stoked', 'buzzing', 'electric', 'animated', 'spirited',
            'lively', 'vibrant', 'dynamic', 'charged', 'exhilarated',
            // Phrases
            'feeling excited', 'so excited', 'really excited', 'very excited', 'quite excited',
            'fired up', 'pumped up', 'hyped up', 'can\'t wait', 'looking forward'
        ],
        'bored' => [
            // Direct words
            'bored', 'dull', 'monotonous', 'tedious', 'uninterested', 'apathetic', 'listless',
            'uninspired', 'flat', 'stale', 'bland', 'disengaged', 'indifferent',
            // Phrases
            'feeling bored', 'so bored', 'really bored', 'very bored', 'quite bored',
            'nothing to do', 'bored out of my mind', 'bored to death', 'bored stiff'
        ],
        'tired' => [
            // Direct words
            'tired', 'exhausted', 'sleepy', 'fatigued', 'drained', 'weary', 'beat', 'spent',
            'depleted', 'lethargic', 'sluggish', 'drowsy', 'zonked', 'pooped', 'bushed',
            // Phrases
            'feeling tired', 'so tired', 'really tired', 'very tired', 'quite tired',
            'worn out', 'wiped out', 'burned out', 'dead tired', 'dog tired',
            'need sleep', 'need rest', 'can\'t keep my eyes open'
        ],
        'stressed' => [
            // Direct words
            'stressed', 'overwhelmed', 'pressured', 'burdened', 'overloaded', 'swamped',
            'stretched', 'strained', 'taxed', 'frazzled', 'harassed', 'hassled',
            // Phrases
            'feeling stressed', 'so stressed', 'really stressed', 'very stressed', 'quite stressed',
            'under pressure', 'burned out', 'stressed out', 'at my limit', 'breaking point'
        ],
        'neutral' => [
            // Direct words
            'neutral', 'okay', 'fine', 'alright', 'average', 'normal', 'regular', 'ordinary',
            'indifferent', 'unchanged', 'same', 'usual',
            // Phrases
            'so-so', 'nothing special', 'meh', 'whatever', 'not bad', 'not good',
            'feeling okay', 'feeling fine', 'feeling alright', 'feeling normal'
        ]
    ];

    $text = strtolower($text);
    $emotionScores = [];

    foreach ($emotionKeywords as $emo => $keywords) {
        $score = 0;
        $matchedKeywords = [];

        foreach ($keywords as $keyword) {
            // Use word boundaries for better matching
            $pattern = '/\b' . preg_quote($keyword, '/') . '\b/i';

            if (preg_match($pattern, $text)) {
                // Check for negation words before the keyword
                $keywordPos = strpos($text, $keyword);
                $beforeKeyword = substr($text, 0, $keywordPos);

                // Look for negation words in the 30 characters before the keyword
                $negationWords = ['not', 'don\'t', 'doesn\'t', 'can\'t', 'won\'t', 'never', 'no', 'hardly', 'barely'];
                $isNegated = false;

                foreach ($negationWords as $negation) {
                    if (strpos($beforeKeyword, $negation) !== false) {
                        // Check if negation is close to the keyword (within reasonable distance)
                        $negationPos = strrpos($beforeKeyword, $negation);
                        $distance = $keywordPos - $negationPos;
                        if ($distance <= 30) { // Within 30 characters
                            $isNegated = true;
                            break;
                        }
                    }
                }

                if (!$isNegated) {
                    // Score based on keyword specificity and length
                    $keywordScore = 1;

                    // Boost score for longer, more specific phrases
                    if (strlen($keyword) > 10) {
                        $keywordScore = 3; // Long phrases like "feeling really tired"
                    } elseif (strlen($keyword) > 6) {
                        $keywordScore = 2; // Medium phrases like "feeling tired"
                    }

                    // Boost score for phrases containing "feeling" or "feel"
                    if (strpos($keyword, 'feeling') !== false || strpos($keyword, 'feel') !== false) {
                        $keywordScore += 1;
                    }

                    $score += $keywordScore;
                    $matchedKeywords[] = $keyword;
                }
            }
        }
        $emotionScores[$emo] = $score;
    }

    // Enhanced emotion detection with context analysis
    $totalScore = array_sum($emotionScores);

    if ($totalScore > 0) {
        $maxScore = max($emotionScores);
        $emotion = array_keys($emotionScores, $maxScore)[0];



        // Calculate confidence based on score strength and context
        $textLength = str_word_count($text);

        // Base confidence calculation
        $baseConfidence = min($maxScore / 3, 1); // More generous base calculation

        // Boost confidence for multiple keyword matches
        $uniqueMatches = count(array_filter($emotionScores, function($score) { return $score > 0; }));
        if ($uniqueMatches == 1) {
            $baseConfidence += 0.2; // Boost for clear single emotion
        }

        // Boost confidence for longer, more descriptive text
        if ($textLength > 3) {
            $baseConfidence += 0.1;
        }
        if ($textLength > 6) {
            $baseConfidence += 0.1;
        }

        // Boost confidence for high scores
        if ($maxScore >= 3) {
            $baseConfidence += 0.15;
        }

        $confidence = min(max($baseConfidence, 0.65), 0.95); // Range: 65%-95%
        return true;
    }

    // If no keywords found, try sentiment analysis patterns
    // Check negative patterns FIRST to catch negated positive phrases
    $negativePatterns = ['feel bad', 'feeling awful', 'terrible day', 'not feeling good', 'not feeling well', 'don\'t feel good', 'not good', 'not well'];

    foreach ($negativePatterns as $pattern) {
        if (strpos($text, $pattern) !== false) {
            $emotion = 'sad';
            $confidence = 0.7;
            return true;
        }
    }

    // Then check positive patterns
    $positivePatterns = ['feel good', 'feeling great', 'doing well', 'going good', 'pretty good'];

    foreach ($positivePatterns as $pattern) {
        if (strpos($text, $pattern) !== false) {
            $emotion = 'happy';
            $confidence = 0.7;
            return true;
        }
    }

    // If text contains "I feel" or "I'm feeling" but no emotion keywords,
    // it's likely an emotion but we couldn't detect it
    if (preg_match('/\b(i feel|i\'m feeling|feeling|feel)\b/', $text)) {
        $emotion = 'unknown';
        $confidence = 0;
        return false; // Emotion attempt detected but couldn't classify
    }

    // No emotion indicators found at all
    $emotion = 'unknown';
    $confidence = 0;
    return false;
}

/**
 * Fallback to random emotion
 * @param string &$emotion The emotion variable to set
 * @param float &$confidence The confidence variable to set
 */
function fallbackRandomEmotion(&$emotion, &$confidence) {
    global $EMOTION_CATEGORIES;
    $emotions = array_keys($EMOTION_CATEGORIES);
    $emotion = $emotions[array_rand($emotions)];
    $confidence = mt_rand(70, 95) / 100;
}

// Log emotion
$emotionId = logEmotion($userId, $emotion, $confidence, $inputType, $inputData);

// Prepare response data
$responseData = [
    'success' => true,
    'emotion' => $emotion,
    'confidence' => $confidence,
    'emotion_id' => $emotionId,
    'input_type' => $inputType
];

// Add API response data if available
if ($apiResponse) {
    $responseData['api_method'] = $apiResponse['method'] ?? 'unknown';

    // Add clarification information if needed
    if (isset($apiResponse['needs_clarification']) && $apiResponse['needs_clarification']) {
        $responseData['needs_clarification'] = true;
        $responseData['clarification_message'] = $apiResponse['clarification_message'] ?? 'Please clarify your emotion';
    }

    // Add additional data based on input type
    if ($inputType === 'text' && isset($apiResponse['all_emotions'])) {
        $responseData['all_emotions'] = $apiResponse['all_emotions'];
    }
}

// Return response
echo json_encode($responseData);
