/**
 * Voice Message Recorder
 * Handles voice message recording, playback, and sending
 */

class VoiceRecorder {
    constructor(options = {}) {
        this.mediaRecorder = null;
        this.audioChunks = [];
        this.isRecording = false;
        this.isPaused = false;
        this.recordingStartTime = null;
        this.recordingDuration = 0;
        this.maxDuration = options.maxDuration || 300; // 5 minutes default
        this.minDuration = options.minDuration || 1; // 1 second minimum
        
        this.onRecordingStart = options.onRecordingStart || (() => {});
        this.onRecordingStop = options.onRecordingStop || (() => {});
        this.onRecordingPause = options.onRecordingPause || (() => {});
        this.onRecordingResume = options.onRecordingResume || (() => {});
        this.onDurationUpdate = options.onDurationUpdate || (() => {});
        this.onError = options.onError || ((error) => console.error('Voice recorder error:', error));
        
        this.audioContext = null;
        this.analyser = null;
        this.dataArray = null;
        this.animationFrame = null;
    }
    
    async initialize() {
        try {
            // Request microphone permission
            const stream = await navigator.mediaDevices.getUserMedia({ 
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true
                } 
            });
            
            // Create MediaRecorder
            const options = { mimeType: 'audio/webm;codecs=opus' };
            if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                options.mimeType = 'audio/webm';
                if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                    options.mimeType = 'audio/mp4';
                    if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                        options.mimeType = '';
                    }
                }
            }
            
            this.mediaRecorder = new MediaRecorder(stream, options);
            
            // Set up audio context for visualization
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const source = this.audioContext.createMediaStreamSource(stream);
            this.analyser = this.audioContext.createAnalyser();
            this.analyser.fftSize = 256;
            source.connect(this.analyser);
            
            this.dataArray = new Uint8Array(this.analyser.frequencyBinCount);
            
            // Set up event listeners
            this.mediaRecorder.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    this.audioChunks.push(event.data);
                }
            };
            
            this.mediaRecorder.onstop = () => {
                this.onRecordingStop(this.createAudioBlob());
            };
            
            return true;
        } catch (error) {
            this.onError('Failed to initialize voice recorder: ' + error.message);
            return false;
        }
    }

    async initializeAndStartRecording() {
        // Initialize if not already done
        if (!this.mediaRecorder) {
            const initialized = await this.initialize();
            if (!initialized) {
                return false;
            }
        }

        // Start recording immediately
        return this.startRecording();
    }

    startRecording() {
        if (!this.mediaRecorder || this.isRecording) {
            return false;
        }
        
        try {
            this.audioChunks = [];
            this.recordingStartTime = Date.now();
            this.recordingDuration = 0;
            this.isRecording = true;
            this.isPaused = false;
            
            this.mediaRecorder.start(100); // Collect data every 100ms
            this.startDurationTimer();
            this.startVisualization();
            
            this.onRecordingStart();
            return true;
        } catch (error) {
            this.onError('Failed to start recording: ' + error.message);
            return false;
        }
    }
    
    stopRecording() {
        if (!this.isRecording) {
            return false;
        }
        
        try {
            this.isRecording = false;
            this.isPaused = false;
            this.mediaRecorder.stop();
            this.stopDurationTimer();
            this.stopVisualization();
            
            return true;
        } catch (error) {
            this.onError('Failed to stop recording: ' + error.message);
            return false;
        }
    }
    
    pauseRecording() {
        if (!this.isRecording || this.isPaused) {
            return false;
        }
        
        try {
            this.mediaRecorder.pause();
            this.isPaused = true;
            this.stopDurationTimer();
            this.onRecordingPause();
            return true;
        } catch (error) {
            this.onError('Failed to pause recording: ' + error.message);
            return false;
        }
    }
    
    resumeRecording() {
        if (!this.isRecording || !this.isPaused) {
            return false;
        }
        
        try {
            this.mediaRecorder.resume();
            this.isPaused = false;
            this.startDurationTimer();
            this.onRecordingResume();
            return true;
        } catch (error) {
            this.onError('Failed to resume recording: ' + error.message);
            return false;
        }
    }
    
    createAudioBlob() {
        const mimeType = this.mediaRecorder.mimeType || 'audio/webm';
        return new Blob(this.audioChunks, { type: mimeType });
    }
    
    startDurationTimer() {
        this.durationTimer = setInterval(() => {
            if (this.isRecording && !this.isPaused) {
                this.recordingDuration = (Date.now() - this.recordingStartTime) / 1000;
                this.onDurationUpdate(this.recordingDuration);
                
                // Auto-stop if max duration reached
                if (this.recordingDuration >= this.maxDuration) {
                    this.stopRecording();
                }
            }
        }, 100);
    }
    
    stopDurationTimer() {
        if (this.durationTimer) {
            clearInterval(this.durationTimer);
            this.durationTimer = null;
        }
    }
    
    startVisualization() {
        if (!this.analyser) return;
        
        const visualize = () => {
            if (!this.isRecording) return;
            
            this.analyser.getByteFrequencyData(this.dataArray);
            
            // Calculate average volume
            let sum = 0;
            for (let i = 0; i < this.dataArray.length; i++) {
                sum += this.dataArray[i];
            }
            const average = sum / this.dataArray.length;
            
            // Trigger visualization update
            this.onVisualizationUpdate && this.onVisualizationUpdate(average, this.dataArray);
            
            this.animationFrame = requestAnimationFrame(visualize);
        };
        
        visualize();
    }
    
    stopVisualization() {
        if (this.animationFrame) {
            cancelAnimationFrame(this.animationFrame);
            this.animationFrame = null;
        }
    }
    
    formatDuration(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
    }
    
    isValidDuration() {
        return this.recordingDuration >= this.minDuration && this.recordingDuration <= this.maxDuration;
    }
    
    destroy() {
        this.stopRecording();
        this.stopDurationTimer();
        this.stopVisualization();
        
        if (this.mediaRecorder && this.mediaRecorder.stream) {
            this.mediaRecorder.stream.getTracks().forEach(track => track.stop());
        }
        
        if (this.audioContext) {
            this.audioContext.close();
        }
    }
}

// Audio playback utility
class AudioPlayer {
    constructor() {
        this.audio = null;
        this.isPlaying = false;
        this.onPlay = () => {};
        this.onPause = () => {};
        this.onEnded = () => {};
        this.onTimeUpdate = () => {};
        this.onError = () => {};
    }
    
    loadFromBlob(blob) {
        this.stop();
        const url = URL.createObjectURL(blob);
        this.audio = new Audio(url);
        this.setupEventListeners();
        return url;
    }
    
    loadFromUrl(url) {
        this.stop();
        this.audio = new Audio(url);
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        if (!this.audio) return;
        
        this.audio.addEventListener('play', () => {
            this.isPlaying = true;
            this.onPlay();
        });
        
        this.audio.addEventListener('pause', () => {
            this.isPlaying = false;
            this.onPause();
        });
        
        this.audio.addEventListener('ended', () => {
            this.isPlaying = false;
            this.onEnded();
        });
        
        this.audio.addEventListener('timeupdate', () => {
            this.onTimeUpdate(this.audio.currentTime, this.audio.duration);
        });
        
        this.audio.addEventListener('error', (e) => {
            this.onError(e);
        });
    }
    
    play() {
        if (this.audio) {
            this.audio.play().catch(this.onError);
        }
    }
    
    pause() {
        if (this.audio) {
            this.audio.pause();
        }
    }
    
    stop() {
        if (this.audio) {
            this.audio.pause();
            this.audio.currentTime = 0;
            this.isPlaying = false;
        }
    }
    
    setCurrentTime(time) {
        if (this.audio) {
            this.audio.currentTime = time;
        }
    }
    
    destroy() {
        this.stop();
        if (this.audio && this.audio.src) {
            URL.revokeObjectURL(this.audio.src);
        }
        this.audio = null;
    }
}

// Export for use in other scripts
window.VoiceRecorder = VoiceRecorder;
window.AudioPlayer = AudioPlayer;
