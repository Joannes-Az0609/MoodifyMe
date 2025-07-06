<?php
/**
 * MoodifyMe - Meal Recommendations Page
 * 
 * This page displays meal recommendations based on mood transitions
 */

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect(APP_URL . '/pages/login.php');
}

// Get user ID
$userId = $_SESSION['user_id'];

// Get emotions from URL parameters
$sourceEmotion = isset($_GET['source']) ? sanitizeInput($_GET['source']) : '';
$targetEmotion = isset($_GET['target']) ? sanitizeInput($_GET['target']) : '';
$emotionId = isset($_GET['emotion_id']) ? sanitizeInput($_GET['emotion_id']) : '';

// Validate emotions
if (empty($sourceEmotion) || empty($targetEmotion)) {
    redirect(APP_URL);
}

// Get user info
$user = getUserById($userId);
if (!$user) {
    redirect(APP_URL . '/pages/login.php');
}

// Get page title
$pageTitle = "Meal Recommendations - " . APP_NAME;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    
    <style>
        /* Meal Card Styling - Matching Movie Cards */
        .meal-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            background: #ffffff !important;
            position: relative;
            margin-bottom: 2rem;
        }

        .meal-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        /* Meal Image Container */
        .meal-image-container {
            position: relative;
            height: 250px;
            overflow: hidden;
        }

        .meal-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .meal-card:hover .meal-image {
            transform: scale(1.1);
        }

        /* Meal Overlay */
        .meal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255, 193, 7, 0.9), rgba(255, 152, 0, 0.9));
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .meal-card:hover .meal-overlay {
            opacity: 1;
        }

        .overlay-content {
            text-align: center;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }

        .meal-card:hover .overlay-content {
            transform: translateY(0);
        }
        
        /* Difficulty Badge */
        .meal-rating {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 3;
        }

        .difficulty-badge {
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        /* Meal Title */
        .meal-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        /* Meal Description */
        .meal-description {
            color: #6c757d;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        /* Meal Meta Information */
        .meal-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            color: #6c757d;
        }

        /* Card Entrance Animation */
        .meal-card {
            animation: fadeInUp 0.6s ease forwards;
        }

        .meal-card:nth-child(1) { animation-delay: 0.1s; }
        .meal-card:nth-child(2) { animation-delay: 0.2s; }
        .meal-card:nth-child(3) { animation-delay: 0.3s; }
        .meal-card:nth-child(4) { animation-delay: 0.4s; }
        .meal-card:nth-child(5) { animation-delay: 0.5s; }
        .meal-card:nth-child(6) { animation-delay: 0.6s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .cooking-time {
            color: #6c757d;
            font-size: 0.9em;
        }
        
        .cuisine-tag {
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-right: 5px;
        }
        
        .dietary-tag {
            background: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 0.75em;
            margin: 2px;
        }
        
        .ingredients-preview {
            max-height: 60px;
            overflow: hidden;
            position: relative;
        }
        
        .ingredients-preview::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20px;
            background: linear-gradient(transparent, white);
        }
        
        .mood-transition {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 40px;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .feedback-buttons {
            margin-top: 10px;
        }
        
        .feedback-buttons .btn {
            margin-right: 5px;
            padding: 5px 10px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include '../includes/header.php'; ?>

    <div class="container mt-4">
        <!-- Mood Transition Header -->
        <div class="mood-transition">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-utensils me-3"></i>Meal Recommendations</h1>
                    <p class="mb-0">
                        <i class="fas fa-arrow-right me-2"></i>
                        Transitioning from <strong><?php echo ucfirst($sourceEmotion); ?></strong> 
                        to <strong><?php echo ucfirst($targetEmotion); ?></strong>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?php echo APP_URL; ?>/pages/mood_options.php?source=<?php echo urlencode($sourceEmotion); ?>&target=<?php echo urlencode($targetEmotion); ?>&emotion_id=<?php echo urlencode($emotionId); ?>" 
                       class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to Options
                    </a>
                </div>
            </div>
        </div>

        <!-- Simple Action Section -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="mb-0">
                        <i class="fas fa-utensils me-2"></i>
                        Recommended meals to help you transition from <strong><?php echo ucfirst($sourceEmotion); ?></strong> to <strong><?php echo ucfirst($targetEmotion); ?></strong>
                    </h5>
                    <p class="text-muted mb-0">Comfort foods and recipes scientifically chosen to support your mood journey</p>
                </div>
                <div class="col-md-4 text-end">
                    <button id="surpriseMe" class="btn btn-warning">
                        <i class="fas fa-random me-1"></i>Surprise Me!
                    </button>
                    <button id="refreshMeals" class="btn btn-outline-primary ms-2">
                        <i class="fas fa-refresh me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div class="loading-spinner" id="loadingSpinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Finding perfect meals for your mood...</p>
        </div>

        <!-- Meals Container -->
        <div id="mealsContainer" class="row g-4">
            <!-- Meals will be loaded here via JavaScript -->
        </div>

        <!-- No Results Message -->
        <div class="no-results" id="noResults" style="display: none;">
            <i class="fas fa-search fa-3x mb-3"></i>
            <h3>No meals found</h3>
            <p>Try adjusting your filters or search terms.</p>
        </div>

        <!-- Load More Button -->
        <div class="text-center mt-4" id="loadMoreContainer" style="display: none;">
            <button id="loadMoreBtn" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-plus me-2"></i>Load More Meals
            </button>
        </div>
    </div>

    <!-- Meal Detail Modal -->
    <div class="modal fade" id="mealModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mealModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="mealModalBody">
                    <!-- Meal details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a id="mealModalLink" href="#" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt me-2"></i>View Full Recipe
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Global variables
        let currentPage = 1;
        let isLoading = false;
        let hasMoreResults = true;
        
        const sourceEmotion = '<?php echo $sourceEmotion; ?>';
        const targetEmotion = '<?php echo $targetEmotion; ?>';
        const emotionId = '<?php echo $emotionId; ?>';
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadMeals();
            setupEventListeners();
        });
        
        function setupEventListeners() {
            // Simple action buttons
            document.getElementById('surpriseMe').addEventListener('click', function() {
                loadRandomMeals();
            });

            document.getElementById('refreshMeals').addEventListener('click', function() {
                currentPage = 1;
                hasMoreResults = true;
                loadMeals(true);
            });

            // Load more button
            document.getElementById('loadMoreBtn').addEventListener('click', function() {
                currentPage++;
                loadMeals(false);
            });
        }

        function loadMeals(clearExisting = false) {
            if (isLoading) return;

            isLoading = true;
            showLoading();

            if (clearExisting) {
                document.getElementById('mealsContainer').innerHTML = '';
                document.getElementById('noResults').style.display = 'none';
                document.getElementById('loadMoreContainer').style.display = 'none';
            }

            // Build API URL
            const params = new URLSearchParams({
                action: 'get_by_mood',
                source: sourceEmotion,
                target: targetEmotion,
                page: currentPage,
                limit: 12
            });

            // Simple mood-based loading only

            console.log('Loading meals with URL:', `<?php echo APP_URL; ?>/api/meals.php?${params}`);

            fetch(`<?php echo APP_URL; ?>/api/meals.php?${params}`)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('API Response:', data);
                    hideLoading();

                    if (data.success && data.data && data.data.length > 0) {
                        displayMeals(data.data, clearExisting);

                        // Show load more button if there might be more results
                        if (data.data.length === 12) {
                            document.getElementById('loadMoreContainer').style.display = 'block';
                        } else {
                            hasMoreResults = false;
                            document.getElementById('loadMoreContainer').style.display = 'none';
                        }
                    } else {
                        console.log('No meals found or API error:', data);
                        if (clearExisting || currentPage === 1) {
                            document.getElementById('noResults').style.display = 'block';
                            // Show specific error message if available
                            if (data && data.message) {
                                showError(`Error: ${data.message}`);
                            } else {
                                showError('No meals found for this mood transition. Please try again or contact support.');
                            }
                        }
                        hasMoreResults = false;
                        document.getElementById('loadMoreContainer').style.display = 'none';
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error loading meals:', error);
                    showError(`Failed to load meals: ${error.message}. Please try again.`);
                })
                .finally(() => {
                    isLoading = false;
                });
        }

        function loadRandomMeals() {
            if (isLoading) return;

            isLoading = true;
            showLoading();

            document.getElementById('mealsContainer').innerHTML = '';
            document.getElementById('noResults').style.display = 'none';
            document.getElementById('loadMoreContainer').style.display = 'none';

            fetch(`<?php echo APP_URL; ?>/api/meals.php?action=get_random&limit=12`)
                .then(response => response.json())
                .then(data => {
                    hideLoading();

                    if (data.success && data.data.length > 0) {
                        displayMeals(data.data, true);
                    } else {
                        document.getElementById('noResults').style.display = 'block';
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error loading random meals:', error);
                    showError('Failed to load meals. Please try again.');
                })
                .finally(() => {
                    isLoading = false;
                });
        }

        function displayMeals(meals, clearExisting = false) {
            const container = document.getElementById('mealsContainer');

            if (clearExisting) {
                container.innerHTML = '';
            }

            meals.forEach(meal => {
                const mealCard = createMealCard(meal);
                container.appendChild(mealCard);
            });
        }

        function createMealCard(meal) {
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4 col-xl-3';

            const difficultyColor = {
                'Easy': 'success',
                'Medium': 'warning',
                'Hard': 'danger'
            }[meal.difficulty] || 'secondary';

            const imageUrl = meal.image_url || '<?php echo APP_URL; ?>/assets/images/default-meal.jpg';

            col.innerHTML = `
                <div class="card meal-card h-100">
                    <div class="meal-image-container">
                        <img src="${imageUrl}" class="meal-image" alt="${meal.title}"
                             onerror="this.src='<?php echo APP_URL; ?>/assets/images/default-meal.jpg'">

                        <!-- Meal Overlay -->
                        <div class="meal-overlay">
                            <div class="overlay-content">
                                <button class="btn btn-light btn-lg mb-2" onclick="showMealDetails(${meal.id})">
                                    <i class="fas fa-eye me-2"></i>View Recipe
                                </button>
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-success btn-sm" onclick="likeMeal(${meal.id})" title="Like this meal">
                                        <i class="fas fa-thumbs-up"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="dislikeMeal(${meal.id})" title="Dislike this meal">
                                        <i class="fas fa-thumbs-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Difficulty Badge -->
                        ${meal.difficulty ? `
                            <div class="meal-rating">
                                <span class="difficulty-badge">${meal.difficulty}</span>
                            </div>
                        ` : ''}
                    </div>

                    <div class="card-body d-flex flex-column p-3">
                        <h5 class="meal-title mb-2">${meal.title}</h5>
                        <p class="meal-description flex-grow-1">
                            ${meal.description.substring(0, 120)}${meal.description.length > 120 ? '...' : ''}
                        </p>

                        <div class="meal-meta mb-3">
                            <div>
                                ${meal.cuisine_type ? `<span class="badge bg-secondary me-1">${meal.cuisine_type}</span>` : ''}
                                ${meal.cooking_time ? `<small><i class="fas fa-clock me-1"></i>${meal.cooking_time}</small>` : ''}
                            </div>
                            <div>
                                ${meal.servings ? `<small><i class="fas fa-users me-1"></i>${meal.servings}</small>` : ''}
                            </div>
                        </div>

                        ${meal.dietary_tags_list && meal.dietary_tags_list.length > 0 ? `
                            <div class="mb-2">
                                ${meal.dietary_tags_list.slice(0, 2).map(tag => `<span class="dietary-tag">${tag}</span>`).join('')}
                                ${meal.dietary_tags_list.length > 2 ? `<span class="dietary-tag">+${meal.dietary_tags_list.length - 2}</span>` : ''}
                            </div>
                        ` : ''}

                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <div class="feedback-display">
                                <small class="text-muted">
                                    <i class="fas fa-thumbs-up text-success"></i> ${meal.likes || 0}
                                    <i class="fas fa-thumbs-down text-danger ms-2"></i> ${meal.dislikes || 0}
                                </small>
                            </div>
                            <button class="btn btn-outline-warning btn-sm" onclick="showMealDetails(${meal.id})">
                                <i class="fas fa-info-circle me-1"></i>Details
                            </button>
                        </div>
                    </div>
                </div>
            `;

            return col;
        }

        function showMealDetails(mealId) {
            // Fetch meal details and show in modal
            fetch(`<?php echo APP_URL; ?>/api/recommendations.php?id=${mealId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const meal = data.data;
                        document.getElementById('mealModalTitle').textContent = meal.title;

                        let modalBody = `
                            <div class="row">
                                <div class="col-md-6">
                                    <img src="${meal.image_url || '<?php echo APP_URL; ?>/assets/images/default-meal.jpg'}"
                                         class="img-fluid rounded" alt="${meal.title}"
                                         onerror="this.src='<?php echo APP_URL; ?>/assets/images/default-meal.jpg'">
                                </div>
                                <div class="col-md-6">
                                    <h6>Description</h6>
                                    <p>${meal.description}</p>

                                    ${meal.cooking_time ? `<p><strong>Cooking Time:</strong> ${meal.cooking_time}</p>` : ''}
                                    ${meal.difficulty ? `<p><strong>Difficulty:</strong> <span class="badge bg-secondary">${meal.difficulty}</span></p>` : ''}
                                    ${meal.servings ? `<p><strong>Servings:</strong> ${meal.servings}</p>` : ''}
                                    ${meal.cuisine_type ? `<p><strong>Cuisine:</strong> ${meal.cuisine_type}</p>` : ''}
                                </div>
                            </div>
                        `;

                        if (meal.ingredients_list && meal.ingredients_list.length > 0) {
                            modalBody += `
                                <hr>
                                <h6>Ingredients</h6>
                                <ul class="list-group list-group-flush">
                                    ${meal.ingredients_list.map(ingredient => `<li class="list-group-item">${ingredient}</li>`).join('')}
                                </ul>
                            `;
                        }

                        if (meal.nutrition_info) {
                            modalBody += `
                                <hr>
                                <h6>Nutrition Information</h6>
                                <p>${meal.nutrition_info}</p>
                            `;
                        }

                        document.getElementById('mealModalBody').innerHTML = modalBody;

                        // Set link
                        const linkBtn = document.getElementById('mealModalLink');
                        if (meal.link) {
                            linkBtn.href = meal.link;
                            linkBtn.style.display = 'inline-block';
                        } else {
                            linkBtn.style.display = 'none';
                        }

                        // Show modal
                        new bootstrap.Modal(document.getElementById('mealModal')).show();
                    }
                })
                .catch(error => {
                    console.error('Error loading meal details:', error);
                    showError('Failed to load meal details.');
                });
        }

        function likeMeal(mealId) {
            sendFeedback(mealId, 'like');
        }

        function dislikeMeal(mealId) {
            sendFeedback(mealId, 'dislike');
        }

        function sendFeedback(mealId, type) {
            fetch(`<?php echo APP_URL; ?>/api/feedback.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    recommendation_id: mealId,
                    feedback_type: type
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the button counts
                    const mealCard = document.querySelector(`[onclick="showMealDetails(${mealId})"]`).closest('.card');
                    const likeBtn = mealCard.querySelector('.btn-outline-success');
                    const dislikeBtn = mealCard.querySelector('.btn-outline-danger');

                    // You could update the counts here if the API returns them
                    showSuccess(`Thank you for your feedback!`);
                } else {
                    showError(data.message || 'Failed to submit feedback.');
                }
            })
            .catch(error => {
                console.error('Error submitting feedback:', error);
                showError('Failed to submit feedback.');
            });
        }

        function showLoading() {
            document.getElementById('loadingSpinner').style.display = 'block';
        }

        function hideLoading() {
            document.getElementById('loadingSpinner').style.display = 'none';
        }

        function showError(message) {
            // You could implement a toast notification here
            alert('Error: ' + message);
        }

        function showSuccess(message) {
            // You could implement a toast notification here
            console.log('Success: ' + message);
        }
    </script>
</body>
</html>
