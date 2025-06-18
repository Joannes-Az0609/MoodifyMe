/**
 * MoodifyMe - Recommendations JavaScript
 * Handles recommendation display and interaction
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get recommendation filters
    const filterButtons = document.querySelectorAll('.recommendation-filter');
    const recommendationCards = document.querySelectorAll('.recommendation-card');
    
    if (filterButtons.length > 0 && recommendationCards.length > 0) {
        // Initialize with 'all' filter active
        let activeFilter = 'all';
        
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.dataset.filter;
                
                // Update active filter
                activeFilter = filter;
                
                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Filter recommendations
                filterRecommendations(filter);
            });
        });
        
        // Filter recommendations based on selected filter
        function filterRecommendations(filter) {
            recommendationCards.forEach(card => {
                if (filter === 'all' || card.dataset.type === filter) {
                    card.style.display = 'block';
                    
                    // Add animation
                    card.classList.add('animate__animated', 'animate__fadeIn');
                    setTimeout(() => {
                        card.classList.remove('animate__animated', 'animate__fadeIn');
                    }, 500);
                } else {
                    card.style.display = 'none';
                }
            });
        }
    }
    
    // Handle recommendation feedback
    const likeButtons = document.querySelectorAll('.recommendation-like');
    const dislikeButtons = document.querySelectorAll('.recommendation-dislike');
    
    if (likeButtons.length > 0 && dislikeButtons.length > 0) {
        // Like button click
        likeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const recommendationId = this.dataset.id;
                sendFeedback(recommendationId, 'like', this);
            });
        });
        
        // Dislike button click
        dislikeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const recommendationId = this.dataset.id;
                sendFeedback(recommendationId, 'dislike', this);
            });
        });
        
        // Send feedback to server
        function sendFeedback(recommendationId, feedbackType, button) {
            // Check if button is already active
            if (button.classList.contains('active')) {
                return;
            }
            
            fetch('api/recommendation_feedback.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    recommendation_id: recommendationId,
                    feedback_type: feedbackType
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    button.classList.add('active');
                    
                    // If this is a like, remove active class from dislike button and vice versa
                    if (feedbackType === 'like') {
                        const dislikeButton = document.querySelector(`.recommendation-dislike[data-id="${recommendationId}"]`);
                        if (dislikeButton && dislikeButton.classList.contains('active')) {
                            dislikeButton.classList.remove('active');
                        }
                    } else {
                        const likeButton = document.querySelector(`.recommendation-like[data-id="${recommendationId}"]`);
                        if (likeButton && likeButton.classList.contains('active')) {
                            likeButton.classList.remove('active');
                        }
                    }
                    
                    // Show thank you message
                    showFeedbackMessage('Thank you for your feedback!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showFeedbackMessage('Error sending feedback. Please try again.', 'error');
            });
        }
        
        // Show feedback message
        function showFeedbackMessage(message, type = 'success') {
            const feedbackMessage = document.createElement('div');
            feedbackMessage.className = `alert alert-${type === 'success' ? 'success' : 'danger'} feedback-message`;
            feedbackMessage.textContent = message;
            
            // Add to page
            const container = document.querySelector('.recommendations-container');
            if (container) {
                container.prepend(feedbackMessage);
                
                // Auto-dismiss after 3 seconds
                setTimeout(() => {
                    feedbackMessage.style.opacity = '0';
                    setTimeout(() => {
                        feedbackMessage.remove();
                    }, 300);
                }, 3000);
            }
        }
    }
    
    // Load more recommendations
    const loadMoreBtn = document.getElementById('load-more-recommendations');
    if (loadMoreBtn) {
        let page = 1;
        
        loadMoreBtn.addEventListener('click', function() {
            // Increment page
            page++;
            
            // Get current filter
            const activeFilter = document.querySelector('.recommendation-filter.active').dataset.filter;
            
            // Show loading indicator
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            this.disabled = true;
            
            // Get source and target emotions from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const sourceEmotion = urlParams.get('source');
            const targetEmotion = urlParams.get('target');
            
            // Load more recommendations
            fetch(`api/recommendations.php?source=${sourceEmotion}&target=${targetEmotion}&page=${page}&filter=${activeFilter}`)
                .then(response => response.json())
                .then(data => {
                    // Reset button
                    loadMoreBtn.innerHTML = 'Load More';
                    loadMoreBtn.disabled = false;
                    
                    if (data.success) {
                        // Add new recommendations
                        const recommendationsContainer = document.querySelector('.recommendations-grid');
                        
                        data.recommendations.forEach(recommendation => {
                            const card = createRecommendationCard(recommendation);
                            recommendationsContainer.appendChild(card);
                        });
                        
                        // Hide load more button if no more recommendations
                        if (data.recommendations.length < 6) { // Assuming 6 recommendations per page
                            loadMoreBtn.style.display = 'none';
                        }
                    } else {
                        // Show error message
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'alert alert-danger';
                        errorMessage.textContent = data.message || 'Error loading more recommendations.';
                        
                        const container = document.querySelector('.recommendations-container');
                        if (container) {
                            container.appendChild(errorMessage);
                            
                            // Auto-dismiss after 5 seconds
                            setTimeout(() => {
                                errorMessage.remove();
                            }, 5000);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Reset button
                    loadMoreBtn.innerHTML = 'Load More';
                    loadMoreBtn.disabled = false;
                    
                    // Show error message
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'alert alert-danger';
                    errorMessage.textContent = 'Network error. Please try again.';
                    
                    const container = document.querySelector('.recommendations-container');
                    if (container) {
                        container.appendChild(errorMessage);
                        
                        // Auto-dismiss after 5 seconds
                        setTimeout(() => {
                            errorMessage.remove();
                        }, 5000);
                    }
                });
        });
        
        // Create recommendation card
        function createRecommendationCard(recommendation) {
            const card = document.createElement('div');
            card.className = 'col-md-4 mb-4';
            card.innerHTML = `
                <div class="card recommendation-card" data-type="${recommendation.type}">
                    <div class="card-img-top" style="background-image: url('${recommendation.image_url}')"></div>
                    <div class="card-body">
                        <h5 class="card-title">${recommendation.title}</h5>
                        <p class="card-text">${recommendation.description}</p>
                        <div class="recommendation-meta">
                            <span class="badge bg-primary">${recommendation.type}</span>
                            <div class="recommendation-actions">
                                <button class="btn btn-sm btn-outline-success recommendation-like" data-id="${recommendation.id}">
                                    <i class="fas fa-thumbs-up"></i> <span>${recommendation.likes}</span>
                                </button>
                                <button class="btn btn-sm btn-outline-danger recommendation-dislike" data-id="${recommendation.id}">
                                    <i class="fas fa-thumbs-down"></i> <span>${recommendation.dislikes}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add event listeners to new buttons
            const likeButton = card.querySelector('.recommendation-like');
            const dislikeButton = card.querySelector('.recommendation-dislike');
            
            likeButton.addEventListener('click', function() {
                sendFeedback(recommendation.id, 'like', this);
            });
            
            dislikeButton.addEventListener('click', function() {
                sendFeedback(recommendation.id, 'dislike', this);
            });
            
            return card;
        }
    }
});
