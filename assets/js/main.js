/**
 * MoodifyMe - Main JavaScript
 * Contains common functionality used throughout the application
 */

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Initialize Bootstrap components (excluding dropdowns - we handle those manually)
    if (typeof bootstrap !== 'undefined') {
        console.log('Bootstrap is loaded');

        // Initialize other Bootstrap dropdowns (not the user dropdown)
        var otherDropdowns = document.querySelectorAll('.dropdown-toggle:not(#userDropdown)');
        if (otherDropdowns.length > 0) {
            otherDropdowns.forEach(function(dropdown) {
                new bootstrap.Dropdown(dropdown);
            });
            console.log('Initialized', otherDropdowns.length, 'other dropdowns');
        }
    } else {
        console.error('Bootstrap is not loaded!');
    }

    // Check if user dropdown exists (handled by custom function in header)
    const userDropdown = document.getElementById('userDropdown');
    if (userDropdown) {
        console.log('User dropdown found - using custom implementation');
    } else {
        console.log('User dropdown not found');
    }

    // Handle form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Handle mood input option selection
    const inputOptions = document.querySelectorAll('.input-option');
    const inputForms = document.querySelectorAll('.mood-input-form');

    if (inputOptions.length > 0 && inputForms.length > 0) {
        console.log('Setting up input option listeners for', inputOptions.length, 'options');
        inputOptions.forEach(option => {
            option.addEventListener('click', function() {
                console.log('Input option clicked:', this.id);

                // Remove active class from all options
                inputOptions.forEach(opt => opt.classList.remove('active'));

                // Add active class to clicked option
                this.classList.add('active');

                // Hide all input forms
                inputForms.forEach(form => form.style.display = 'none');

                // Show the corresponding input form
                const formId = this.id.replace('-option', '-form');
                console.log('Showing form:', formId);
                const targetForm = document.getElementById(formId);
                if (targetForm) {
                    targetForm.style.display = 'block';


                } else {
                    console.error('Form not found:', formId);
                }
            });
        });
    } else {
        console.log('Input options or forms not found:', {
            inputOptions: inputOptions.length,
            inputForms: inputForms.length
        });
    }

    // Enhanced Theme Toggle System
    const themeToggle = document.getElementById('themeToggle');

    function getCurrentTheme() {
        return document.documentElement.getAttribute('data-theme') || 'light';
    }

    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('moodifyme-theme', theme);

        // Update theme toggle button state
        updateThemeToggleButton(theme);

        // Dispatch custom event for other components
        window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));
    }

    function updateThemeToggleButton(theme) {
        if (themeToggle) {
            const title = theme === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode';
            themeToggle.setAttribute('title', title);
        }
    }

    function toggleTheme() {
        const currentTheme = getCurrentTheme();
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        setTheme(newTheme);
    }

    // Theme toggle event listener
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);

        // Initialize button state
        updateThemeToggleButton(getCurrentTheme());
    }

    // Listen for system theme changes
    if (window.matchMedia) {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        mediaQuery.addEventListener('change', function(e) {
            // Only auto-switch if user hasn't manually set a preference
            if (!localStorage.getItem('moodifyme-theme')) {
                setTheme(e.matches ? 'dark' : 'light');
            }
        });
    }

    // Keyboard shortcut for theme toggle (Ctrl/Cmd + Shift + D)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'D') {
            e.preventDefault();
            toggleTheme();
        }
    });

    // Legacy dark mode support (for backward compatibility)
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            toggleTheme();
        });
    }

    // Handle notification dismissal
    const notifications = document.querySelectorAll('.notification-item');
    if (notifications.length > 0) {
        notifications.forEach(notification => {
            const dismissBtn = notification.querySelector('.dismiss-notification');
            if (dismissBtn) {
                dismissBtn.addEventListener('click', function() {
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        notification.style.display = 'none';
                    }, 300);

                    // You can add AJAX call here to mark notification as read in the database
                });
            }
        });
    }

    // Handle recommendation feedback
    const feedbackButtons = document.querySelectorAll('.recommendation-feedback');
    if (feedbackButtons.length > 0) {
        feedbackButtons.forEach(button => {
            button.addEventListener('click', function() {
                const recommendationId = this.dataset.recommendationId;
                const feedbackType = this.dataset.feedbackType;

                // Send feedback to server via AJAX
                // Determine the correct API URL based on current location
                let feedbackApiUrl = 'api/recommendation_feedback.php';
                if (window.location.pathname.includes('/pages/')) {
                    feedbackApiUrl = '../api/recommendation_feedback.php';
                }

                fetch(feedbackApiUrl, {
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
                        // Update UI to show feedback was received
                        this.classList.add('active');

                        // If this is a like/dislike system, toggle the other button off
                        if (feedbackType === 'like' || feedbackType === 'dislike') {
                            const oppositeType = feedbackType === 'like' ? 'dislike' : 'like';
                            const oppositeButton = document.querySelector(`.recommendation-feedback[data-recommendation-id="${recommendationId}"][data-feedback-type="${oppositeType}"]`);
                            if (oppositeButton) {
                                oppositeButton.classList.remove('active');
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });
    }

    // Note: Text input form handling is now managed by emotion-detection.js
    // This prevents duplicate event handlers and button creation




});
