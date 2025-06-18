class MoodifyApp {
    constructor() {
        try {
            console.log('Constructor: Starting initialization...');
            this.apiUrl = window.location.origin;
            this.conversationId = this.generateConversationId();
            this.isConnected = false;
            this.currentMood = null;
            this.messageHistory = [];
            this.isTyping = false;
            this.isListening = false;
            this.recognition = null;

            console.log('Constructor: Initializing elements...');
            this.initializeElements();
            console.log('Constructor: Setting up event listeners...');
            this.setupEventListeners();
            console.log('Constructor: Checking connection...');
            this.checkConnection();
            console.log('Constructor: Loading settings...');
            this.loadSettings();
            console.log('Constructor: Checking for context...');
            this.checkForContext();
            console.log('Constructor: Initialization complete');
        } catch (error) {
            console.error('Constructor error:', error);
            throw error;
        }
    }

    initializeElements() {
        // Main elements
        this.statusDot = document.getElementById('statusDot');
        this.statusText = document.getElementById('statusText');
        this.welcomeMessage = document.getElementById('welcomeMessage');
        this.chatMessages = document.getElementById('chatMessages');
        this.messageInput = document.getElementById('messageInput');
        this.sendBtn = document.getElementById('sendBtn');
        this.voiceBtn = document.getElementById('voiceBtn');
        this.typingIndicator = document.getElementById('typingIndicator');
        this.charCount = document.getElementById('charCount');
        this.moodDisplay = document.getElementById('moodDisplay');
        this.currentMood = document.getElementById('currentMood');
        this.loadingOverlay = document.getElementById('loadingOverlay');
        
        // Panel elements
        this.historyPanel = document.getElementById('historyPanel');
        this.settingsPanel = document.getElementById('settingsPanel');
        this.historyToggleBtn = document.getElementById('historyToggleBtn');
        this.settingsBtn = document.getElementById('settingsBtn');
        this.closeSettingsBtn = document.getElementById('closeSettingsBtn');
        this.clearHistoryBtn = document.getElementById('clearHistoryBtn');
        this.historyContent = document.getElementById('historyContent');
        
        // Settings elements
        this.themeSelect = document.getElementById('themeSelect');
        this.autoJoke = document.getElementById('autoJoke');
        this.soundEnabled = document.getElementById('soundEnabled');
    }

    setupEventListeners() {
        // Message input
        this.messageInput.addEventListener('input', () => this.handleInputChange());
        this.messageInput.addEventListener('keydown', (e) => this.handleKeyDown(e));
        
        // Buttons
        this.sendBtn.addEventListener('click', () => this.sendMessage());
        this.voiceBtn.addEventListener('click', () => this.toggleVoiceInput());
        
        // Panel toggles
        this.historyToggleBtn.addEventListener('click', () => this.toggleHistory());
        this.settingsBtn.addEventListener('click', () => this.toggleSettings());
        this.closeSettingsBtn.addEventListener('click', () => this.closeSettings());
        this.clearHistoryBtn.addEventListener('click', () => this.clearHistory());
        
        // Settings
        this.themeSelect.addEventListener('change', () => this.changeTheme());
        this.autoJoke.addEventListener('change', () => this.saveSettings());
        this.soundEnabled.addEventListener('change', () => this.saveSettings());
        
        // Auto-resize textarea
        this.messageInput.addEventListener('input', () => this.autoResizeTextarea());

        // Voice input (if supported)
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            this.setupVoiceInput();
        }
    }



    generateConversationId() {
        return 'web_' + Date.now() + '_' + Math.random().toString(36).substring(2, 11);
    }

    async checkConnection() {
        try {
            // Try different ports in case the server is running on a different port
            const ports = [3001, 3000, 8080, 5000];
            let connected = false;

            for (const port of ports) {
                try {
                    const testUrl = `http://localhost:${port}`;
                    const response = await fetch(`${testUrl}/api/health`);
                    if (response.ok) {
                        this.apiUrl = testUrl;
                        this.setConnectionStatus(true);
                        this.hideLoading();
                        connected = true;
                        break;
                    }
                } catch (portError) {
                    // Continue to next port
                    continue;
                }
            }

            if (!connected) {
                throw new Error('No server found on any port');
            }
        } catch (error) {
            console.error('Connection check failed:', error);
            this.setConnectionStatus(false);
            this.hideLoading();
            this.showConnectionError();
        }
    }

    setConnectionStatus(connected) {
        this.isConnected = connected;
        this.statusDot.classList.toggle('connected', connected);
        this.statusText.textContent = connected ? 'Connected' : 'Disconnected';
        this.sendBtn.disabled = !connected;
        this.voiceBtn.disabled = !connected;
    }

    hideLoading() {
        this.loadingOverlay.style.display = 'none';
    }

    handleInputChange() {
        const text = this.messageInput.value;
        this.charCount.textContent = text.length;
        this.sendBtn.disabled = !text.trim() || !this.isConnected;
        
        // Auto-detect mood for longer texts
        if (text.length > 20) {
            this.detectMood(text);
        }
    }

    handleKeyDown(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            this.sendMessage();
        }
    }

    autoResizeTextarea() {
        this.messageInput.style.height = 'auto';
        this.messageInput.style.height = Math.min(this.messageInput.scrollHeight, 120) + 'px';
    }

    async detectMood(text) {
        try {
            const response = await fetch(`${this.apiUrl}/api/chat/mood`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text })
            });
            
            if (response.ok) {
                const data = await response.json();
                this.displayCurrentMood(data.data.mood, data.data.confidence);
            }
        } catch (error) {
            console.error('Mood detection failed:', error);
        }
    }

    displayCurrentMood(mood, confidence) {
        if (confidence > 0.6) {
            this.currentMood.textContent = `${mood} (${Math.round(confidence * 100)}%)`;
            this.moodDisplay.style.display = 'block';
        } else {
            this.moodDisplay.style.display = 'none';
        }
    }

    async sendMessage(message = null) {
        console.log('sendMessage called with:', { message });
        const text = message || this.messageInput.value.trim();
        if (!text || !this.isConnected) {
            console.log('Aborting sendMessage:', { text, isConnected: this.isConnected });
            return;
        }

        // Clear input and hide welcome
        if (!message) {
            this.messageInput.value = '';
            this.charCount.textContent = '0';
            this.moodDisplay.style.display = 'none';
        }
        this.hideWelcome();

        // Add user message to chat
        this.addMessage('user', text);
        
        // Show typing indicator
        this.showTyping();

        try {
            const response = await fetch(`${this.apiUrl}/api/chat/message`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: text,
                    conversationId: this.conversationId
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('Received response:', data);
            this.hideTyping();

            // Add assistant response
            this.addMessage('assistant', data.data.response, {
                mood: data.data.mood,
                type: data.data.type,
                timestamp: new Date().toISOString()
            });

            // Auto-suggest joke for sad moods
            if (this.autoJoke.checked && data.data.mood.mood === 'sad' && data.data.type !== 'joke') {
                setTimeout(() => {
                    this.suggestJoke();
                }, 2000);
            }

            // Save to history
            this.saveToHistory(text, data.data.response, data.data.mood);

        } catch (error) {
            console.error('Send message failed:', error);
            this.hideTyping();
            this.addMessage('assistant', 'I apologize, but I encountered an error processing your message. Please try again.', {
                mood: { mood: 'neutral', confidence: 1 },
                type: 'error'
            });
        }
    }

    async toggleVoiceInput() {
        console.log('toggleVoiceInput called, isConnected:', this.isConnected);
        if (!this.isConnected) return;

        if (!this.isListening) {
            this.startVoiceInput();
        } else {
            this.stopVoiceInput();
        }
    }

    startVoiceInput() {
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            alert('Voice input is not supported in your browser. Please try Chrome or Edge.');
            return;
        }

        try {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.recognition = new SpeechRecognition();

            this.recognition.continuous = false;
            this.recognition.interimResults = false;
            this.recognition.lang = 'en-US';

            this.recognition.onstart = () => {
                this.isListening = true;
                this.voiceBtn.classList.add('listening');
                this.voiceBtn.title = 'Stop listening';
                console.log('Voice recognition started');
            };

            this.recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                console.log('Voice input received:', transcript);
                this.messageInput.value = transcript;
                this.handleInputChange();
            };

            this.recognition.onerror = (event) => {
                console.error('Voice recognition error:', event.error);
                this.stopVoiceInput();
                if (event.error === 'no-speech') {
                    alert('No speech detected. Please try again.');
                } else if (event.error === 'not-allowed') {
                    alert('Microphone access denied. Please allow microphone access and try again.');
                }
            };

            this.recognition.onend = () => {
                this.stopVoiceInput();
            };

            this.recognition.start();
        } catch (error) {
            console.error('Error starting voice recognition:', error);
            alert('Error starting voice input. Please try again.');
        }
    }

    stopVoiceInput() {
        if (this.recognition) {
            this.recognition.stop();
        }
        this.isListening = false;
        this.voiceBtn.classList.remove('listening');
        this.voiceBtn.title = 'Voice input';
        console.log('Voice recognition stopped');
    }



    addMessage(sender, content, metadata = null) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}`;
        
        let metaHtml = '';
        if (metadata && sender === 'assistant') {
            const moodClass = metadata.mood.mood.toLowerCase();
            const confidence = Math.round(metadata.mood.confidence * 100);
            metaHtml = `
                <div class="message-meta">
                    <span class="mood-badge ${moodClass}">
                        ${this.getMoodIcon(metadata.mood.mood)} ${metadata.mood.mood} (${confidence}%)
                    </span>
                    <span>${metadata.type}</span>
                    <span>${new Date().toLocaleTimeString()}</span>
                </div>
            `;
        }
        
        messageDiv.innerHTML = `
            <div class="message-content">
                <p>${this.formatMessage(content)}</p>
                ${metaHtml}
            </div>
        `;
        
        this.chatMessages.appendChild(messageDiv);
        this.scrollToBottom();
        
        // Play sound notification
        if (this.soundEnabled.checked && sender === 'assistant') {
            this.playNotificationSound();
        }
    }

    formatMessage(text) {
        // Basic formatting for links, line breaks, etc.
        return text
            .replace(/\n/g, '<br>')
            .replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>');
    }

    getMoodIcon(mood) {
        const icons = {
            happy: '😊',
            sad: '😢',
            angry: '😠',
            anxious: '😰',
            excited: '🤩',
            neutral: '😐',
            stressed: '😫',
            lonely: '😔',
            confident: '😎'
        };
        return icons[mood] || '😐';
    }

    hideWelcome() {
        this.welcomeMessage.style.display = 'none';
        this.chatMessages.style.display = 'block';
    }

    showTyping() {
        this.typingIndicator.style.display = 'flex';
        this.scrollToBottom();
    }

    hideTyping() {
        this.typingIndicator.style.display = 'none';
    }

    scrollToBottom() {
        setTimeout(() => {
            this.chatMessages.scrollTop = this.chatMessages.scrollHeight;
        }, 100);
    }

    playNotificationSound() {
        // Simple beep sound using Web Audio API
        try {
            if (!window.AudioContext && !window.webkitAudioContext) {
                console.log('Web Audio API not supported');
                return;
            }
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            const audioContext = new AudioContextClass();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        } catch (error) {
            console.log('Audio notification not supported');
        }
    }

    // Panel Management
    toggleHistory() {
        this.historyPanel.classList.toggle('open');
        this.settingsPanel.classList.remove('open');
        this.updateHistoryDisplay();
    }

    toggleSettings() {
        this.settingsPanel.classList.toggle('open');
        this.historyPanel.classList.remove('open');
    }

    closeSettings() {
        this.settingsPanel.classList.remove('open');
    }

    updateHistoryDisplay() {
        this.historyContent.innerHTML = '';

        if (this.messageHistory.length === 0) {
            this.historyContent.innerHTML = '<p style="color: var(--text-muted); text-align: center; margin-top: 2rem;">No conversation history yet.</p>';
            return;
        }

        this.messageHistory.slice(-10).reverse().forEach((item) => {
            const historyItem = document.createElement('div');
            historyItem.className = 'history-item';
            historyItem.style.cssText = `
                padding: 1rem;
                border: 1px solid var(--border-color);
                border-radius: var(--radius-md);
                margin-bottom: 1rem;
                cursor: pointer;
                transition: background 0.2s ease;
            `;

            historyItem.innerHTML = `
                <div style="font-weight: 500; margin-bottom: 0.5rem; color: var(--text-primary);">
                    ${item.userMessage.substring(0, 50)}${item.userMessage.length > 50 ? '...' : ''}
                </div>
                <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                    ${item.assistantResponse.substring(0, 100)}${item.assistantResponse.length > 100 ? '...' : ''}
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted); display: flex; justify-content: space-between;">
                    <span class="mood-badge ${item.mood.mood}">${this.getMoodIcon(item.mood.mood)} ${item.mood.mood}</span>
                    <span>${new Date(item.timestamp).toLocaleDateString()}</span>
                </div>
            `;

            historyItem.addEventListener('mouseenter', () => {
                historyItem.style.background = 'var(--bg-secondary)';
            });

            historyItem.addEventListener('mouseleave', () => {
                historyItem.style.background = 'transparent';
            });

            historyItem.addEventListener('click', () => {
                this.sendMessage(item.userMessage);
                this.historyPanel.classList.remove('open');
            });

            this.historyContent.appendChild(historyItem);
        });
    }

    saveToHistory(userMessage, assistantResponse, mood) {
        this.messageHistory.push({
            userMessage,
            assistantResponse,
            mood,
            timestamp: new Date().toISOString()
        });

        // Keep only last 50 conversations
        if (this.messageHistory.length > 50) {
            this.messageHistory = this.messageHistory.slice(-50);
        }

        // Save to localStorage
        localStorage.setItem('moodify_history', JSON.stringify(this.messageHistory));
    }

    clearHistory() {
        if (confirm('Are you sure you want to clear all conversation history?')) {
            this.messageHistory = [];
            localStorage.removeItem('moodify_history');
            this.updateHistoryDisplay();
        }
    }

    // Settings Management
    loadSettings() {
        const settings = JSON.parse(localStorage.getItem('moodify_settings') || '{}');
        const history = JSON.parse(localStorage.getItem('moodify_history') || '[]');

        // Apply settings
        this.themeSelect.value = settings.theme || 'light';
        this.autoJoke.checked = settings.autoJoke || false; // default false
        this.soundEnabled.checked = settings.soundEnabled || false;

        // Load history
        this.messageHistory = history;

        // Apply theme
        this.applyTheme(settings.theme || 'light');
    }

    checkForContext() {
        // Check URL parameters for context
        const urlParams = new URLSearchParams(window.location.search);
        const context = urlParams.get('context');

        if (context) {
            // Show connection badge
            const connectionBadge = document.getElementById('connectionBadge');
            if (connectionBadge) {
                connectionBadge.style.display = 'inline-block';
            }

            // Show back to recommendations link
            const backLink = document.getElementById('backToRecommendations');
            if (backLink) {
                backLink.style.display = 'inline-block';
                backLink.href = window.location.origin.replace(':3001', '') + '/MoodifyMe/pages/recommendations.php';
            }

            // Decode and auto-send the context message
            const decodedContext = decodeURIComponent(context);
            console.log('Found context:', decodedContext);

            // Wait a moment for the interface to fully load, then send the message
            setTimeout(() => {
                // Show a welcome message first
                this.addMessage('assistant',
                    `Hello! I see you're coming from the MoodifyMe recommendations page. I'm here to help you with your emotional journey. Let me address your request:`,
                    { mood: { mood: 'neutral' }, type: 'context_welcome' }
                );

                // Then send the context message
                setTimeout(() => {
                    this.messageInput.value = decodedContext;
                    this.sendMessage(decodedContext);
                }, 1500);
            }, 1000);
        }
    }

    saveSettings() {
        const settings = {
            theme: this.themeSelect.value,
            autoJoke: this.autoJoke.checked,
            soundEnabled: this.soundEnabled.checked
        };

        localStorage.setItem('moodify_settings', JSON.stringify(settings));
    }

    changeTheme() {
        this.applyTheme(this.themeSelect.value);
        this.saveSettings();
    }

    applyTheme(theme) {
        if (theme === 'auto') {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            theme = prefersDark ? 'dark' : 'light';
        }

        document.documentElement.setAttribute('data-theme', theme);
    }

    // Utility Methods
    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--danger-color);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            max-width: 400px;
            animation: slideIn 0.3s ease-out;
        `;
        errorDiv.textContent = message;

        document.body.appendChild(errorDiv);

        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }

    showConnectionError() {
        const errorDiv = document.createElement('div');
        errorDiv.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border: 2px solid var(--danger-color);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            max-width: 500px;
            text-align: center;
        `;

        errorDiv.innerHTML = `
            <div style="color: var(--danger-color); font-size: 3rem; margin-bottom: 1rem;">⚠️</div>
            <h3 style="color: var(--text-primary); margin-bottom: 1rem;">Connection Failed</h3>
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                Unable to connect to the MoodifyMe Assistant server. Please ensure the server is running.
            </p>
            <div style="margin-bottom: 1.5rem;">
                <p style="font-size: 0.875rem; color: var(--text-muted);">
                    To start the server, run: <code style="background: var(--bg-secondary); padding: 0.25rem 0.5rem; border-radius: 4px;">npm start</code>
                </p>
            </div>
            <button id="retryConnection" style="
                background: var(--primary-color);
                color: white;
                border: none;
                padding: 0.75rem 1.5rem;
                border-radius: var(--radius-md);
                cursor: pointer;
                font-weight: 500;
                margin-right: 1rem;
            ">Retry Connection</button>
            <button id="dismissError" style="
                background: var(--bg-secondary);
                color: var(--text-secondary);
                border: 1px solid var(--border-color);
                padding: 0.75rem 1.5rem;
                border-radius: var(--radius-md);
                cursor: pointer;
                font-weight: 500;
            ">Continue Offline</button>
        `;

        document.body.appendChild(errorDiv);

        // Add event listeners
        document.getElementById('retryConnection').addEventListener('click', () => {
            errorDiv.remove();
            this.checkConnection();
        });

        document.getElementById('dismissError').addEventListener('click', () => {
            errorDiv.remove();
        });
    }



    createQuickResponses() {
        const quickResponses = document.createElement('div');
        quickResponses.id = 'quickResponses';
        quickResponses.className = 'quick-responses';
        quickResponses.innerHTML = `
            <div class="quick-responses-header">
                <h4>💬 Quick Responses</h4>
            </div>
            <div class="quick-responses-grid">
                <button class="quick-response-btn" data-message="I'm feeling stressed">😫 Stressed</button>
                <button class="quick-response-btn" data-message="I need motivation">💪 Need motivation</button>
                <button class="quick-response-btn" data-message="I'm feeling grateful">🙏 Grateful</button>
                <button class="quick-response-btn" data-message="I'm anxious about something">😰 Anxious</button>
                <button class="quick-response-btn" data-message="I accomplished something today">🎉 Accomplished</button>
                <button class="quick-response-btn" data-message="I need some wisdom">🧠 Need wisdom</button>
            </div>
        `;

        const chatContainer = document.querySelector('.chat-input-container');
        chatContainer.parentNode.insertBefore(quickResponses, chatContainer);

        // Setup event listeners
        document.querySelectorAll('.quick-response-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const message = e.target.dataset.message;
                this.sendMessage(message);
            });
        });
    }





    setupVoiceInput() {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        this.recognition = new SpeechRecognition();
        this.recognition.continuous = false;
        this.recognition.interimResults = false;
        this.recognition.lang = 'en-US';

        // Add voice button to input area
        const voiceBtn = document.createElement('button');
        voiceBtn.id = 'voiceBtn';
        voiceBtn.className = 'action-btn voice-btn';
        voiceBtn.innerHTML = '🎤';
        voiceBtn.title = 'Voice input';

        const inputActions = document.querySelector('.input-actions');
        inputActions.insertBefore(voiceBtn, inputActions.firstChild);

        voiceBtn.addEventListener('click', () => this.toggleVoiceInput());

        this.recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            this.messageInput.value = transcript;
            this.handleInputChange();
            voiceBtn.classList.remove('listening');
        };

        this.recognition.onerror = () => {
            voiceBtn.classList.remove('listening');
        };

        this.recognition.onend = () => {
            voiceBtn.classList.remove('listening');
        };
    }

    toggleVoiceInput() {
        const voiceBtn = document.getElementById('voiceBtn');
        if (voiceBtn.classList.contains('listening')) {
            this.recognition.stop();
            voiceBtn.classList.remove('listening');
        } else {
            this.recognition.start();
            voiceBtn.classList.add('listening');
        }
    }


}

// Quick message function for welcome buttons
function sendQuickMessage(message) {
    if (window.app) {
        // Check if it's a joke request
        const isJokeRequest = message.toLowerCase().includes('joke');
        window.app.sendMessage(message, isJokeRequest);
    }
}

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    try {
        console.log('Initializing MoodifyApp...');
        window.app = new MoodifyApp();
        console.log('MoodifyApp initialized successfully');
    } catch (error) {
        console.error('Error initializing MoodifyApp:', error);
    }
});

// Handle system theme changes
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (window.app && window.app.themeSelect.value === 'auto') {
        window.app.applyTheme('auto');
    }
});     