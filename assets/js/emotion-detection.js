/**
 * MoodifyMe - Emotion Detection JavaScript
 * Handles emotion detection from text, voice, and facial expressions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Text-based emotion detection
    const moodTextForm = document.getElementById('mood-text-form');
    if (moodTextForm) {
        moodTextForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const moodText = document.getElementById('mood-text').value;
            if (moodText.trim() === '') {
                alert('Please enter some text about how you feel.');
                return;
            }

            // Show loading indicator
            showLoadingIndicator('Analyzing your mood...');

            // Send text to server for analysis
            fetch(window.location.pathname.includes('/pages/') ? '../api/emotion_analysis.php' : 'api/emotion_analysis.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    input_type: 'text',
                    input_data: moodText
                }),
            })
            .then(response => response.json())
            .then(data => {
                hideLoadingIndicator();

                if (data.success) {
                    // Show detected emotion and ask for target emotion
                    showEmotionResults(
                        data.emotion,
                        data.confidence,
                        data.emotion_id,
                        data.needs_clarification,
                        data.clarification_message
                    );
                } else {
                    // Check if it's a clarification needed error
                    if (data.needs_clarification) {
                        showEmotionResults(
                            data.emotion,
                            data.confidence,
                            data.emotion_id,
                            data.needs_clarification,
                            data.clarification_message
                        );
                    } else {
                        showError(data.message || data.clarification_message || 'An error occurred during emotion analysis.');
                    }
                }
            })
            .catch(error => {
                hideLoadingIndicator();
                showError('Network error. Please try again.');
                console.error('Error:', error);
            });
        });
    }

    // Helper functions
    function showLoadingIndicator(message) {
        // Create loading overlay if it doesn't exist
        let loadingOverlay = document.getElementById('loading-overlay');
        if (!loadingOverlay) {
            loadingOverlay = document.createElement('div');
            loadingOverlay.id = 'loading-overlay';
            loadingOverlay.innerHTML = `
                <div class="loading-spinner"></div>
                <div class="loading-message"></div>
            `;
            document.body.appendChild(loadingOverlay);
        }

        // Set loading message
        loadingOverlay.querySelector('.loading-message').textContent = message;

        // Show loading overlay
        loadingOverlay.style.display = 'flex';
    }

    function hideLoadingIndicator() {
        const loadingOverlay = document.getElementById('loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.style.display = 'none';
        }
    }

    function showError(message) {
        // Create error alert
        const errorAlert = document.createElement('div');
        errorAlert.className = 'alert alert-danger alert-dismissible fade show';
        errorAlert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Add error alert to page
        const container = document.querySelector('.mood-detection-section');
        if (container) {
            container.prepend(errorAlert);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                errorAlert.classList.remove('show');
                setTimeout(() => {
                    errorAlert.remove();
                }, 150);
            }, 5000);
        }
    }
});

// Make showEmotionResults available globally
window.showEmotionResults = function(emotion, confidence, emotionId, needsClarification, clarificationMessage) {
    // Helper function to add results to page
    function addResultsToPage(container) {
        // Try to find the container - could be mood-detection-section or mood-detection-container
        // or the card that contains the mood input forms
        let targetContainer = document.querySelector('.mood-detection-section');
        if (!targetContainer) {
            targetContainer = document.querySelector('.mood-detection-container');
        }
        if (!targetContainer) {
            // Try to find the card that contains the mood input forms
            const moodInputForm = document.querySelector('.mood-input-form');
            if (moodInputForm) {
                targetContainer = moodInputForm.closest('.card-body');
            }
        }

        if (targetContainer) {
            console.log('Found container:', targetContainer);

            // Remove any existing results
            const existingResults = document.querySelector('.emotion-results');
            if (existingResults) {
                existingResults.remove();
            }

            // Hide input forms
            const inputForms = document.querySelectorAll('.mood-input-form');
            inputForms.forEach(form => {
                form.style.display = 'none';
            });

            // Hide input options
            const inputOptions = document.querySelector('.mood-input-options');
            if (inputOptions) {
                inputOptions.style.display = 'none';
            }

            // Add a title to the results
            const titleElement = document.createElement('h2');
            titleElement.className = 'text-center mb-4 mt-4';
            titleElement.textContent = 'Choose Your Target Mood';
            container.prepend(titleElement);

            // Add results
            targetContainer.appendChild(container);

            // Scroll to results
            container.scrollIntoView({ behavior: 'smooth' });
        } else {
            console.error('Container not found for emotion results');
            alert('Could not display emotion results. Please try again.');
        }
    }

    // Create results container
    const resultsContainer = document.createElement('div');
    resultsContainer.className = 'emotion-results';

    // Check if emotion is unknown and needs clarification
    if (emotion === 'unknown' || needsClarification) {
        // Show error message for unknown emotion
        resultsContainer.innerHTML = `
            <div class="alert alert-warning mb-4">
                <h4>We couldn't identify your mood clearly</h4>
                <p>${clarificationMessage || 'Please try describing your feelings in a different way.'}</p>
                <button class="btn btn-primary" onclick="location.reload()">Try Again</button>
            </div>
        `;

        // Add results to page for clarification
        addResultsToPage(resultsContainer);
        return; // Exit early
    }

    // Normal emotion display
    resultsContainer.innerHTML = `
        <div class="detected-emotion">
            <h3>Your Current Mood</h3>
            <div class="emotion-badge ${emotion.toLowerCase()}">
                <i class="fas fa-${getEmotionIcon(emotion)}"></i>
                <span>${emotion}</span>
                <small>(${Math.round(confidence * 100)}% confidence)</small>
            </div>
        </div>
        <div class="target-emotion-selection">
            <h3>What mood would you like to achieve?</h3>
            <div class="emotion-buttons">
                <!-- Emotion buttons will be added here -->
            </div>
        </div>
    `;

    // Add emotion buttons
    const emotionButtons = resultsContainer.querySelector('.emotion-buttons');
    const emotions = [
        'Happy', 'Calm', 'Energetic', 'Focused', 'Inspired', 'Relaxed',
        'Confident', 'Peaceful', 'Motivated', 'Creative', 'Optimistic', 'Grateful',
        'Joyful', 'Serene', 'Ambitious', 'Mindful', 'Empowered', 'Content',
        'Excited', 'Balanced', 'Determined', 'Refreshed', 'Uplifted', 'Centered'
    ];

    // Create a grid layout for the emotion buttons
    emotionButtons.style.display = 'grid';
    emotionButtons.style.gridTemplateColumns = 'repeat(auto-fill, minmax(200px, 1fr))';
    emotionButtons.style.gap = '15px';
    emotionButtons.style.marginTop = '20px';

    emotions.forEach(targetEmotion => {
        // Create the emotion button
        const button = document.createElement('button');
        button.className = 'btn btn-outline-primary emotion-btn';
        button.innerHTML = `
            <i class="fas fa-${getEmotionIcon(targetEmotion)}"></i>
            <span>${targetEmotion}</span>
        `;

        // Add click event listener
        button.addEventListener('click', function() {
            // Construct the redirect URL
            const redirectUrl = window.location.pathname.includes('/pages/') 
                ? `mood_options.php?source=${emotion.toLowerCase()}&target=${targetEmotion.toLowerCase()}`
                : `pages/mood_options.php?source=${emotion.toLowerCase()}&target=${targetEmotion.toLowerCase()}`;

            // Redirect to mood options page
            window.location.href = redirectUrl;
        });

        // Add button to the grid
        emotionButtons.appendChild(button);
    });

    // Add results to page
    addResultsToPage(resultsContainer);
};

// Make getEmotionIcon available globally
window.getEmotionIcon = function(emotion) {
    const icons = {
        'happy': 'smile',
        'sad': 'frown',
        'angry': 'angry',
        'anxious': 'tired',
        'calm': 'peace',
        'energetic': 'bolt',
        'focused': 'eye',
        'inspired': 'lightbulb',
        'relaxed': 'leaf',
        'confident': 'thumbs-up',
        'peaceful': 'dove',
        'motivated': 'fire',
        'creative': 'palette',
        'optimistic': 'sun',
        'grateful': 'heart',
        'joyful': 'laugh',
        'serene': 'water',
        'ambitious': 'mountain',
        'mindful': 'brain',
        'empowered': 'fist-raised',
        'content': 'smile-beam',
        'excited': 'star',
        'balanced': 'yin-yang',
        'determined': 'flag',
        'refreshed': 'seedling',
        'uplifted': 'arrow-up',
        'centered': 'circle-dot',
        'unknown': 'question-circle'
    };

    return icons[emotion.toLowerCase()] || 'smile';
};
