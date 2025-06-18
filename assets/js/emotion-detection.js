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
                    showError(data.message || 'An error occurred during emotion analysis.');
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

    // Make showEmotionResults available globally
    window.showEmotionResults = function(emotion, confidence, emotionId, needsClarification, clarificationMessage) {
        // Create results container
        const resultsContainer = document.createElement('div');
        resultsContainer.className = 'emotion-results';

        // Check if emotion is unknown and needs clarification
        if (emotion === 'unknown' && needsClarification) {
            // Check if the clarification message indicates no mood was detected
            const isNoMoodDetected = clarificationMessage && clarificationMessage.toLowerCase().includes('no mood detected');

            if (isNoMoodDetected) {
                // Show error message for no mood detected
                resultsContainer.innerHTML = `
                    <div class="alert alert-danger mb-4">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Error:</strong>
                        ${clarificationMessage || 'No mood detected in your text. Please be more specific about how you feel.'}
                    </div>
                    <div class="text-center mt-4">
                        <button class="btn btn-primary" id="try-again-button">
                            <i class="fas fa-redo"></i> Try Again
                        </button>
                    </div>
                `;

                // Add the results container to the page first
                addResultsToPage(resultsContainer);

                // Add event listener to the try again button
                document.getElementById('try-again-button').addEventListener('click', function() {
                    // Remove the results container
                    resultsContainer.remove();

                    // Show the input forms again
                    const inputForms = document.querySelectorAll('.mood-input-form');
                    inputForms.forEach(form => {
                        if (form.id === 'text-input-form') {
                            form.style.display = 'block';
                        }
                    });

                    // Show the input options again
                    const inputOptions = document.querySelector('.mood-input-options');
                    if (inputOptions) {
                        inputOptions.style.display = 'flex';
                    }

                    // Clear the text area
                    const moodText = document.getElementById('mood-text');
                    if (moodText) {
                        moodText.value = '';
                        moodText.focus();
                    }
                });

                return; // Exit early
            }

            // Regular clarification needed (uncertain emotion)
            resultsContainer.innerHTML = `
                <div class="alert alert-warning mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Emotion Detection Uncertain:</strong>
                    ${clarificationMessage || 'Could not determine your emotion with confidence.'}
                </div>
                <div class="detected-emotion">
                    <h3>Please Select Your Current Mood</h3>
                    <div class="emotion-selection mb-4">
                        <select class="form-select mb-3" id="clarified-emotion">
                            <option value="">-- Select your current mood --</option>
                            <option value="happy">Happy</option>
                            <option value="sad">Sad</option>
                            <option value="angry">Angry</option>
                            <option value="anxious">Anxious</option>
                            <option value="calm">Calm</option>
                            <option value="excited">Excited</option>
                            <option value="bored">Bored</option>
                            <option value="tired">Tired</option>
                            <option value="stressed">Stressed</option>
                            <option value="neutral">Neutral</option>
                        </select>
                        <button class="btn btn-primary" id="confirm-emotion">
                            <i class="fas fa-check-circle"></i> Confirm Mood
                        </button>
                    </div>
                </div>
            `;

            // Add the results container to the page first
            addResultsToPage(resultsContainer);

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
                    titleElement.textContent = 'Emotion Detection Results';
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

            // Add event listener to the confirm button
            document.getElementById('confirm-emotion').addEventListener('click', function() {
                const selectedEmotion = document.getElementById('clarified-emotion').value;
                if (selectedEmotion) {
                    // Call showEmotionResults again with the selected emotion
                    showEmotionResults(selectedEmotion, 1.0, emotionId);
                } else {
                    alert('Please select your current mood.');
                }
            });

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
            button.className = 'btn btn-primary emotion-button';
            button.dataset.emotion = targetEmotion.toLowerCase();
            button.style.padding = '15px';
            button.style.border = '1px solid #e0e0e0';
            button.style.borderRadius = '10px';
            button.style.backgroundColor = '#007bff';
            button.style.color = 'white';
            button.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
            button.style.cursor = 'pointer';
            button.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
            button.style.width = '100%';
            button.innerHTML = `
                <i class="fas fa-${getEmotionIcon(targetEmotion)} me-2"></i>
                <span>${targetEmotion}</span>
            `;

            // Hover effect
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 8px 15px rgba(0, 0, 0, 0.1)';
                this.style.backgroundColor = '#0056b3';
            });

            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
                this.style.backgroundColor = '#007bff';
            });

            button.addEventListener('click', function(event) {
                // Prevent default behavior
                event.preventDefault();
                event.stopPropagation();

                // Redirect directly to mood_options.php
                const basePath = window.location.pathname.includes('/pages/') ? '' : 'pages/';
                const redirectUrl = `${basePath}mood_options.php?source=${emotion.toLowerCase()}&target=${targetEmotion.toLowerCase()}&emotion_id=${emotionId || ''}`;

                // Redirect to mood options page
                window.location.href = redirectUrl;
            });

            // Add button to the grid
            emotionButtons.appendChild(button);
        });

        // Define the addResultsToPage function
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
                // If container not found, redirect using form submission
                console.error('Container not found for emotion results');

                // Create a form for redirection
                const form = document.createElement('form');
                form.method = 'GET';
                form.action = window.location.pathname.includes('/pages/') ? 'mood_options.php' : 'pages/mood_options.php';

                // Add source emotion input
                const sourceInput = document.createElement('input');
                sourceInput.type = 'hidden';
                sourceInput.name = 'source';
                sourceInput.value = emotion.toLowerCase();
                form.appendChild(sourceInput);

                // Add target emotion input (default to happy)
                const targetInput = document.createElement('input');
                targetInput.type = 'hidden';
                targetInput.name = 'target';
                targetInput.value = 'happy';
                form.appendChild(targetInput);

                // Add emotion ID input if available
                if (emotionId) {
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'emotion_id';
                    idInput.value = emotionId;
                    form.appendChild(idInput);
                }

                // Add the form to the document
                document.body.appendChild(form);

                // Show an alert for debugging
                alert('Fallback: Submitting form to: ' + form.action);

                // Submit the form
                form.submit();
            }
        }

        // Add results to page
        addResultsToPage(resultsContainer);
    }

    // Make getEmotionIcon available globally
    window.getEmotionIcon = function(emotion) {
        const icons = {
            'happy': 'smile',
            'sad': 'frown',
            'angry': 'angry',
            'anxious': 'tired',
            'calm': 'peace',
            'excited': 'grin-stars',
            'bored': 'meh',
            'tired': 'bed',
            'stressed': 'grimace',
            'neutral': 'meh-blank',
            'energetic': 'bolt',
            'focused': 'bullseye',
            'inspired': 'lightbulb',
            'relaxed': 'couch',
            'confident': 'crown',
            'peaceful': 'dove',
            'motivated': 'fire',
            'creative': 'palette',
            'optimistic': 'sun',
            'grateful': 'heart',
            'joyful': 'laugh',
            'serene': 'leaf',
            'ambitious': 'mountain',
            'mindful': 'brain',
            'empowered': 'fist-raised',
            'content': 'smile-beam',
            'balanced': 'yin-yang',
            'determined': 'flag',
            'refreshed': 'seedling',
            'uplifted': 'arrow-up',
            'centered': 'circle-dot',
            'unknown': 'question-circle'
        };

        return icons[emotion.toLowerCase()] || 'smile';
    }
});
