/**
 * MoodifyMe - MediaPipe Landmark-Based Emotion Detection
 * Advanced facial emotion detection using 468 facial landmarks
 */

class LandmarkEmotionDetector {
    constructor() {
        this.faceMesh = null;
        this.camera = null;
        this.isInitialized = false;
        this.isDetecting = false;
        this.currentEmotion = null;
        this.confidence = 0;
        this.emotionHistory = [];
        this.callbacks = {
            onEmotionDetected: null,
            onError: null,
            onInitialized: null
        };

        // Landmark indices for key facial features
        this.landmarkIndices = {
            // Eyes
            leftEye: [33, 7, 163, 144, 145, 153, 154, 155, 133, 173, 157, 158, 159, 160, 161, 246],
            rightEye: [362, 382, 381, 380, 374, 373, 390, 249, 263, 466, 388, 387, 386, 385, 384, 398],

            // Eyebrows
            leftEyebrow: [46, 53, 52, 51, 48, 115, 131, 134, 102, 48, 64],
            rightEyebrow: [276, 283, 282, 281, 278, 344, 360, 363, 331, 278, 294],

            // Mouth
            outerMouth: [61, 84, 17, 314, 405, 320, 307, 375, 321, 308, 324, 318],
            innerMouth: [78, 95, 88, 178, 87, 14, 317, 402, 318, 324, 308, 324],
            mouthCorners: [61, 291], // Left and right corners

            // Nose
            noseTip: [1, 2],
            noseBase: [168],

            // Face outline
            faceOval: [10, 338, 297, 332, 284, 251, 389, 356, 454, 323, 361, 288, 397, 365, 379, 378, 400, 377, 152, 148, 176, 149, 150, 136, 172, 58, 132, 93, 234, 127, 162, 21, 54, 103, 67, 109]
        };
    }

    /**
     * Initialize MediaPipe Face Mesh
     */
    async initialize() {
        try {
            console.log('Initializing MediaPipe Face Mesh...');

            // Check if MediaPipe is loaded
            if (typeof FaceMesh === 'undefined') {
                throw new Error('MediaPipe FaceMesh not loaded. Please ensure face_mesh.js is included.');
            }

            // Initialize Face Mesh
            this.faceMesh = new FaceMesh({
                locateFile: (file) => {
                    // Use CDN for all MediaPipe files to avoid 404 errors
                    const cdnBase = 'https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh@0.4.1633559619/';
                    console.log('Loading MediaPipe file from CDN:', cdnBase + file);
                    return cdnBase + file;
                }
            });

            // Configure Face Mesh
            this.faceMesh.setOptions({
                maxNumFaces: 1,
                refineLandmarks: true,
                minDetectionConfidence: 0.5,
                minTrackingConfidence: 0.5
            });

            // Set up result callback
            this.faceMesh.onResults((results) => {
                this.processResults(results);
            });

            this.isInitialized = true;
            console.log('MediaPipe Face Mesh initialized successfully');

            if (this.callbacks.onInitialized) {
                this.callbacks.onInitialized();
            }

        } catch (error) {
            console.error('Failed to initialize MediaPipe:', error);
            if (this.callbacks.onError) {
                this.callbacks.onError(error);
            }
        }
    }

    /**
     * Start camera and begin detection
     */
    async startDetection(videoElement) {
        try {
            if (!this.isInitialized) {
                await this.initialize();
            }

            console.log('Starting camera for landmark detection...');

            // Initialize camera
            this.camera = new Camera(videoElement, {
                onFrame: async () => {
                    if (this.isDetecting) {
                        await this.faceMesh.send({image: videoElement});
                    }
                },
                width: 640,
                height: 480
            });

            // Start camera
            await this.camera.start();
            this.isDetecting = true;

            console.log('Landmark detection started successfully');

        } catch (error) {
            console.error('Failed to start detection:', error);
            if (this.callbacks.onError) {
                this.callbacks.onError(error);
            }
        }
    }

    /**
     * Stop detection and camera
     */
    stopDetection() {
        this.isDetecting = false;
        if (this.camera) {
            this.camera.stop();
        }
        console.log('Landmark detection stopped');
    }

    /**
     * Process MediaPipe results and detect emotion
     */
    processResults(results) {
        console.log('MediaPipe results received:', results);

        if (!results.multiFaceLandmarks || results.multiFaceLandmarks.length === 0) {
            console.log('No face landmarks detected');
            return;
        }

        console.log('Face landmarks detected:', results.multiFaceLandmarks.length, 'faces');
        console.log('Landmarks for first face:', results.multiFaceLandmarks[0].length, 'points');

        const landmarks = results.multiFaceLandmarks[0];

        // Draw landmarks on canvas for visualization
        this.drawLandmarks(landmarks);

        const emotion = this.analyzeEmotion(landmarks);
        console.log('Emotion analysis result:', emotion);

        // Add to emotion history for smoothing
        this.emotionHistory.push(emotion);
        if (this.emotionHistory.length > 10) {
            this.emotionHistory.shift();
        }

        // Get smoothed emotion
        const smoothedEmotion = this.getSmoothEmotion();

        if (smoothedEmotion.emotion !== this.currentEmotion ||
            Math.abs(smoothedEmotion.confidence - this.confidence) > 0.1) {

            this.currentEmotion = smoothedEmotion.emotion;
            this.confidence = smoothedEmotion.confidence;

            if (this.callbacks.onEmotionDetected) {
                this.callbacks.onEmotionDetected({
                    emotion: this.currentEmotion,
                    confidence: this.confidence,
                    landmarks: landmarks,
                    timestamp: Date.now()
                });
            }
        }
    }

    /**
     * Analyze emotion from facial landmarks
     */
    analyzeEmotion(landmarks) {
        const features = this.extractEmotionFeatures(landmarks);
        return this.classifyEmotion(features);
    }

    /**
     * Extract emotion-relevant features from landmarks
     */
    extractEmotionFeatures(landmarks) {
        const features = {};

        // Mouth features
        features.mouthAspectRatio = this.calculateMouthAspectRatio(landmarks);
        features.mouthCurvature = this.calculateMouthCurvature(landmarks);
        features.mouthWidth = this.calculateMouthWidth(landmarks);

        // Eye features
        features.leftEyeAspectRatio = this.calculateEyeAspectRatio(landmarks, 'left');
        features.rightEyeAspectRatio = this.calculateEyeAspectRatio(landmarks, 'right');
        features.eyeDistance = this.calculateEyeDistance(landmarks);

        // Eyebrow features
        features.leftEyebrowHeight = this.calculateEyebrowHeight(landmarks, 'left');
        features.rightEyebrowHeight = this.calculateEyebrowHeight(landmarks, 'right');
        features.eyebrowAngle = this.calculateEyebrowAngle(landmarks);

        return features;
    }

    /**
     * Calculate mouth aspect ratio (height/width)
     */
    calculateMouthAspectRatio(landmarks) {
        // Correct MediaPipe mouth landmarks
        const leftCorner = landmarks[61];   // Left mouth corner
        const rightCorner = landmarks[291]; // Right mouth corner
        const upperLip = landmarks[13];     // Upper lip center
        const lowerLip = landmarks[14];     // Lower lip center

        const width = this.calculateDistance(leftCorner, rightCorner);
        const height = this.calculateDistance(upperLip, lowerLip);

        const ratio = height / width;

        console.log('Mouth aspect ratio calculation:', {
            width: width,
            height: height,
            ratio: ratio
        });

        return ratio;
    }

    /**
     * Calculate mouth curvature (smile/frown detection)
     */
    calculateMouthCurvature(landmarks) {
        // Correct MediaPipe landmark indices for mouth
        const leftCorner = landmarks[61];   // Left mouth corner
        const rightCorner = landmarks[291]; // Right mouth corner
        const upperLip = landmarks[13];     // Upper lip center
        const lowerLip = landmarks[14];     // Lower lip center

        // Calculate mouth center Y position
        const mouthCenterY = (upperLip.y + lowerLip.y) / 2;
        const avgCornerY = (leftCorner.y + rightCorner.y) / 2;

        // In MediaPipe coordinates: smaller Y = higher on face
        // Smile: corners higher than center (smaller Y values)
        // Frown: corners lower than center (larger Y values)
        const curvature = mouthCenterY - avgCornerY;

        console.log('Mouth curvature calculation:', {
            leftCorner: leftCorner.y,
            rightCorner: rightCorner.y,
            avgCornerY: avgCornerY,
            mouthCenterY: mouthCenterY,
            curvature: curvature
        });

        return curvature; // Positive = smile, Negative = frown
    }

    /**
     * Calculate mouth width
     */
    calculateMouthWidth(landmarks) {
        const leftCorner = landmarks[61];
        const rightCorner = landmarks[291];
        return this.calculateDistance(leftCorner, rightCorner);
    }

    /**
     * Calculate eye aspect ratio
     */
    calculateEyeAspectRatio(landmarks, eye) {
        const indices = eye === 'left' ? this.landmarkIndices.leftEye : this.landmarkIndices.rightEye;

        // Get key eye points
        const p1 = landmarks[indices[1]];
        const p2 = landmarks[indices[5]];
        const p3 = landmarks[indices[2]];
        const p4 = landmarks[indices[4]];
        const p5 = landmarks[indices[0]];
        const p6 = landmarks[indices[3]];

        // Calculate vertical distances
        const A = this.calculateDistance(p2, p4);
        const B = this.calculateDistance(p1, p5);

        // Calculate horizontal distance
        const C = this.calculateDistance(p5, p6);

        return (A + B) / (2.0 * C);
    }

    /**
     * Calculate distance between eyes
     */
    calculateEyeDistance(landmarks) {
        const leftEyeCenter = landmarks[33];
        const rightEyeCenter = landmarks[362];
        return this.calculateDistance(leftEyeCenter, rightEyeCenter);
    }

    /**
     * Calculate eyebrow height relative to eye
     */
    calculateEyebrowHeight(landmarks, side) {
        const eyebrowIndices = side === 'left' ? this.landmarkIndices.leftEyebrow : this.landmarkIndices.rightEyebrow;
        const eyeIndices = side === 'left' ? this.landmarkIndices.leftEye : this.landmarkIndices.rightEye;

        const eyebrowCenter = landmarks[eyebrowIndices[Math.floor(eyebrowIndices.length / 2)]];
        const eyeCenter = landmarks[eyeIndices[Math.floor(eyeIndices.length / 2)]];

        return eyeCenter.y - eyebrowCenter.y; // Higher value = raised eyebrow
    }

    /**
     * Calculate eyebrow angle
     */
    calculateEyebrowAngle(landmarks) {
        const leftBrow = landmarks[46];
        const rightBrow = landmarks[276];
        const centerBrow = landmarks[9];

        const leftAngle = Math.atan2(leftBrow.y - centerBrow.y, leftBrow.x - centerBrow.x);
        const rightAngle = Math.atan2(rightBrow.y - centerBrow.y, rightBrow.x - centerBrow.x);

        return Math.abs(leftAngle - rightAngle);
    }

    /**
     * Calculate Euclidean distance between two points
     */
    calculateDistance(point1, point2) {
        const dx = point1.x - point2.x;
        const dy = point1.y - point2.y;
        const dz = (point1.z || 0) - (point2.z || 0);
        return Math.sqrt(dx * dx + dy * dy + dz * dz);
    }

    /**
     * Classify emotion based on extracted features
     */
    classifyEmotion(features) {
        // Debug: Log all features to understand the values
        console.log('Facial features:', features);

        const emotions = {
            happy: 0,
            sad: 0,
            angry: 0,
            surprised: 0,
            fear: 0,
            disgust: 0,
            neutral: 0.2 // Lower baseline for neutral
        };

        // Happy detection (smile) - Improved thresholds
        if (features.mouthCurvature > 0.008) { // More conservative threshold
            emotions.happy += Math.abs(features.mouthCurvature) * 20;
            console.log('Happy detected: mouth curvature =', features.mouthCurvature);
        }
        if (features.mouthAspectRatio > 0.025 && features.mouthAspectRatio < 0.1) {
            emotions.happy += 0.5; // Wide smile
            console.log('Happy detected: mouth aspect ratio =', features.mouthAspectRatio);
        }
        if (features.leftEyeAspectRatio < 0.3 && features.rightEyeAspectRatio < 0.3) {
            emotions.happy += 0.3; // Squinting eyes (Duchenne smile)
        }

        // Sad detection (frown) - More conservative thresholds
        if (features.mouthCurvature < -0.008) { // More conservative threshold
            emotions.sad += Math.abs(features.mouthCurvature) * 15;
            console.log('Sad detected: mouth curvature =', features.mouthCurvature);
        }
        if (features.mouthAspectRatio < 0.015) {
            emotions.sad += 0.4; // Very tight/downturned mouth
            console.log('Sad detected: tight mouth, ratio =', features.mouthAspectRatio);
        }

        // Surprised detection - More sensitive
        if (features.leftEyeAspectRatio > 0.4 || features.rightEyeAspectRatio > 0.4) {
            emotions.surprised += 0.6; // Wide eyes
            console.log('Surprised detected: eye ratios =', features.leftEyeAspectRatio, features.rightEyeAspectRatio);
        }
        if (features.mouthAspectRatio > 0.08) {
            emotions.surprised += 0.5; // Open mouth
        }
        if (features.leftEyebrowHeight > 0.03 || features.rightEyebrowHeight > 0.03) {
            emotions.surprised += 0.3; // Raised eyebrows
        }

        // Angry detection - Adjusted
        if (features.leftEyeAspectRatio < 0.25 && features.rightEyeAspectRatio < 0.25) {
            emotions.angry += 0.4; // Narrowed eyes
        }
        if (features.mouthWidth < 0.06) {
            emotions.angry += 0.3; // Tight mouth
        }
        if (features.leftEyebrowHeight < 0.01 && features.rightEyebrowHeight < 0.01) {
            emotions.angry += 0.3; // Lowered brows
        }

        // Fear detection
        if (features.leftEyeAspectRatio > 0.35 && features.rightEyeAspectRatio > 0.35) {
            emotions.fear += 0.4;
        }
        if (features.mouthAspectRatio > 0.06 && features.mouthCurvature < 0) {
            emotions.fear += 0.4;
        }

        // Disgust detection
        if (features.mouthCurvature < -0.003 && features.mouthAspectRatio < 0.03) {
            emotions.disgust += 0.4;
        }
        if (features.leftEyeAspectRatio < 0.28 && features.rightEyeAspectRatio < 0.28) {
            emotions.disgust += 0.3;
        }

        // Boost neutral for normal expressions - More robust
        if (Math.abs(features.mouthCurvature) < 0.006 &&
            features.leftEyeAspectRatio > 0.25 && features.leftEyeAspectRatio < 0.4 &&
            features.rightEyeAspectRatio > 0.25 && features.rightEyeAspectRatio < 0.4 &&
            features.mouthAspectRatio > 0.015 && features.mouthAspectRatio < 0.06) {
            emotions.neutral += 0.6;
            console.log('Neutral detected: balanced features');
        }

        // Additional neutral boost if no strong emotion indicators
        const maxEmotionScore = Math.max(emotions.happy, emotions.sad, emotions.angry, emotions.surprised);
        if (maxEmotionScore < 0.3) {
            emotions.neutral += 0.4;
            console.log('Neutral boosted: no strong emotion detected');
        }

        // Find emotion with highest score
        const maxEmotion = Object.keys(emotions).reduce((a, b) =>
            emotions[a] > emotions[b] ? a : b
        );

        const confidence = Math.min(emotions[maxEmotion], 1.0);

        console.log('Emotion scores:', emotions);
        console.log('Selected emotion:', maxEmotion, 'confidence:', confidence);

        return {
            emotion: maxEmotion,
            confidence: confidence,
            allScores: emotions
        };
    }

    /**
     * Get smoothed emotion from history
     */
    getSmoothEmotion() {
        if (this.emotionHistory.length === 0) {
            return { emotion: 'neutral', confidence: 0 };
        }

        // Count emotion occurrences
        const emotionCounts = {};
        let totalConfidence = 0;

        this.emotionHistory.forEach(result => {
            if (!emotionCounts[result.emotion]) {
                emotionCounts[result.emotion] = { count: 0, confidence: 0 };
            }
            emotionCounts[result.emotion].count++;
            emotionCounts[result.emotion].confidence += result.confidence;
            totalConfidence += result.confidence;
        });

        // Find most frequent emotion
        let maxCount = 0;
        let dominantEmotion = 'neutral';

        Object.keys(emotionCounts).forEach(emotion => {
            if (emotionCounts[emotion].count > maxCount) {
                maxCount = emotionCounts[emotion].count;
                dominantEmotion = emotion;
            }
        });

        const avgConfidence = emotionCounts[dominantEmotion].confidence / emotionCounts[dominantEmotion].count;

        return {
            emotion: dominantEmotion,
            confidence: Math.min(avgConfidence, 1.0)
        };
    }

    /**
     * Set callback functions
     */
    setCallbacks(callbacks) {
        this.callbacks = { ...this.callbacks, ...callbacks };
    }

    /**
     * Get current emotion state
     */
    getCurrentEmotion() {
        return {
            emotion: this.currentEmotion,
            confidence: this.confidence,
            isDetecting: this.isDetecting
        };
    }

    /**
     * Reset emotion history
     */
    resetHistory() {
        this.emotionHistory = [];
        this.currentEmotion = null;
        this.confidence = 0;
    }

    /**
     * Draw facial landmarks on canvas for visualization
     */
    drawLandmarks(landmarks) {
        const canvas = document.getElementById('landmark-canvas');
        const video = document.getElementById('landmark-video');

        if (!canvas || !video) {
            console.log('Canvas or video element not found');
            return;
        }

        // Get actual video dimensions and display dimensions
        const videoWidth = video.videoWidth || 640;
        const videoHeight = video.videoHeight || 480;
        const displayWidth = video.clientWidth;
        const displayHeight = video.clientHeight;

        // Set canvas size to match video display size exactly
        canvas.width = displayWidth;
        canvas.height = displayHeight;

        // Update canvas style to ensure perfect overlay
        canvas.style.width = displayWidth + 'px';
        canvas.style.height = displayHeight + 'px';
        canvas.style.position = 'absolute';
        canvas.style.top = '0';
        canvas.style.left = '0';

        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Calculate face center and bounding box
        const faceCenter = this.calculateFaceCenter(landmarks);
        const faceBounds = this.calculateFaceBounds(landmarks);

        // Calculate scaling factors to map video coordinates to display coordinates
        const scaleX = displayWidth / videoWidth;
        const scaleY = displayHeight / videoHeight;

        // Calculate face center in display coordinates
        const faceCenterX = faceCenter.x * videoWidth * scaleX;
        const faceCenterY = faceCenter.y * videoHeight * scaleY;

        // Calculate display center
        const displayCenterX = displayWidth / 2;
        const displayCenterY = displayHeight / 2;

        // Draw face centering guide
        this.drawCenteringGuide(ctx, faceCenterX, faceCenterY, displayCenterX, displayCenterY, displayWidth, displayHeight);

        // Draw all landmarks as small green dots with proper scaling
        ctx.fillStyle = '#00ff00';
        landmarks.forEach((landmark, index) => {
            // Apply proper scaling to center landmarks on the displayed video
            const x = landmark.x * videoWidth * scaleX;
            const y = landmark.y * videoHeight * scaleY;

            ctx.beginPath();
            ctx.arc(x, y, 2, 0, 2 * Math.PI); // Slightly larger dots for better visibility
            ctx.fill();
        });

        // Draw key landmarks in different colors for better visibility
        this.drawKeyLandmarks(ctx, landmarks, videoWidth, videoHeight, scaleX, scaleY);
    }

    /**
     * Calculate the center point of the detected face
     */
    calculateFaceCenter(landmarks) {
        // Use key facial landmarks to determine face center
        const noseTip = landmarks[1]; // Nose tip
        const leftCheek = landmarks[234]; // Left cheek
        const rightCheek = landmarks[454]; // Right cheek
        const chin = landmarks[18]; // Chin
        const forehead = landmarks[10]; // Forehead

        // Calculate average position
        const centerX = (noseTip.x + leftCheek.x + rightCheek.x + chin.x + forehead.x) / 5;
        const centerY = (noseTip.y + leftCheek.y + rightCheek.y + chin.y + forehead.y) / 5;

        return { x: centerX, y: centerY };
    }

    /**
     * Calculate face bounding box
     */
    calculateFaceBounds(landmarks) {
        let minX = 1, maxX = 0, minY = 1, maxY = 0;

        landmarks.forEach(landmark => {
            minX = Math.min(minX, landmark.x);
            maxX = Math.max(maxX, landmark.x);
            minY = Math.min(minY, landmark.y);
            maxY = Math.max(maxY, landmark.y);
        });

        return { minX, maxX, minY, maxY, width: maxX - minX, height: maxY - minY };
    }

    /**
     * Draw centering guide to help user position face in center
     */
    drawCenteringGuide(ctx, faceCenterX, faceCenterY, displayCenterX, displayCenterY, displayWidth, displayHeight) {
        // Draw center crosshairs
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 1;
        ctx.setLineDash([5, 5]);

        // Vertical center line
        ctx.beginPath();
        ctx.moveTo(displayCenterX, 0);
        ctx.lineTo(displayCenterX, displayHeight);
        ctx.stroke();

        // Horizontal center line
        ctx.beginPath();
        ctx.moveTo(0, displayCenterY);
        ctx.lineTo(displayWidth, displayCenterY);
        ctx.stroke();

        // Reset line dash
        ctx.setLineDash([]);

        // Draw face center indicator
        ctx.fillStyle = '#ff6b6b';
        ctx.beginPath();
        ctx.arc(faceCenterX, faceCenterY, 8, 0, 2 * Math.PI);
        ctx.fill();

        // Draw target center
        ctx.strokeStyle = '#4ecdc4';
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.arc(displayCenterX, displayCenterY, 15, 0, 2 * Math.PI);
        ctx.stroke();

        // Calculate distance from center
        const distance = Math.sqrt(
            Math.pow(faceCenterX - displayCenterX, 2) +
            Math.pow(faceCenterY - displayCenterY, 2)
        );

        // Show centering status
        ctx.fillStyle = distance < 30 ? '#4ecdc4' : '#ff6b6b';
        ctx.font = '16px Arial';
        ctx.textAlign = 'center';
        const status = distance < 30 ? 'CENTERED ✓' : 'MOVE TO CENTER';
        ctx.fillText(status, displayCenterX, displayCenterY - 30);
    }

    /**
     * Draw key facial landmarks with different colors
     */
    drawKeyLandmarks(ctx, landmarks, videoWidth, videoHeight, scaleX, scaleY) {
        // Draw mouth in red
        ctx.strokeStyle = '#ff0000';
        ctx.lineWidth = 2;
        this.drawLandmarkGroup(ctx, landmarks, this.landmarkIndices.outerMouth, videoWidth, videoHeight, scaleX, scaleY, true);

        // Draw eyes in blue
        ctx.strokeStyle = '#0000ff';
        this.drawLandmarkGroup(ctx, landmarks, this.landmarkIndices.leftEye, videoWidth, videoHeight, scaleX, scaleY, true);
        this.drawLandmarkGroup(ctx, landmarks, this.landmarkIndices.rightEye, videoWidth, videoHeight, scaleX, scaleY, true);

        // Draw eyebrows in yellow
        ctx.strokeStyle = '#ffff00';
        this.drawLandmarkGroup(ctx, landmarks, this.landmarkIndices.leftEyebrow, videoWidth, videoHeight, scaleX, scaleY, false);
        this.drawLandmarkGroup(ctx, landmarks, this.landmarkIndices.rightEyebrow, videoWidth, videoHeight, scaleX, scaleY, false);
    }

    /**
     * Draw a group of landmarks
     */
    drawLandmarkGroup(ctx, landmarks, indices, videoWidth, videoHeight, scaleX, scaleY, closed = false) {
        if (indices.length === 0) return;

        ctx.beginPath();
        const firstPoint = landmarks[indices[0]];
        // Apply proper scaling to center landmarks on the displayed video
        ctx.moveTo(firstPoint.x * videoWidth * scaleX, firstPoint.y * videoHeight * scaleY);

        for (let i = 1; i < indices.length; i++) {
            const point = landmarks[indices[i]];
            // Apply proper scaling to center landmarks on the displayed video
            ctx.lineTo(point.x * videoWidth * scaleX, point.y * videoHeight * scaleY);
        }

        if (closed) {
            ctx.closePath();
        }
        ctx.stroke();
    }
}

// Make available globally
window.LandmarkEmotionDetector = LandmarkEmotionDetector;
