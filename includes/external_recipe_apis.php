<?php
/**
 * MoodifyMe - External Recipe APIs Integration
 *
 * This file provides integration with external recipe APIs to fetch real African meal data
 * Supports: Spoonacular API, Edamam Recipe API
 */

// External API Configuration
define('SPOONACULAR_API_KEY', '660179464e11465d94c31025711c3546'); // Get from https://spoonacular.com/food-api
define('EDAMAM_RECIPE_APP_ID', 'your_edamam_app_id_here'); // Get from https://developer.edamam.com
define('EDAMAM_RECIPE_APP_KEY', 'your_edamam_app_key_here');

/**
 * Fetch African recipes from Spoonacular API
 *
 * @param string $cuisine African cuisine type (e.g., 'african', 'moroccan', 'ethiopian')
 * @param int $number Number of recipes to fetch (max 100)
 * @param array $diet Diet restrictions (e.g., ['vegetarian', 'vegan'])
 * @param string $type Meal type (e.g., 'main course', 'dessert', 'appetizer')
 * @return array|false Recipe data or false on error
 */
function fetchSpoonacularAfricanRecipes($cuisine = 'african', $number = 10, $diet = [], $type = '') {
    $apiKey = SPOONACULAR_API_KEY;

    if (empty($apiKey) || $apiKey === 'your_spoonacular_api_key_here') {
        error_log('Spoonacular API key not configured');
        return false;
    }

    // Build query parameters
    $params = [
        'apiKey' => $apiKey,
        'cuisine' => $cuisine,
        'number' => min($number, 100), // Max 100 per request
        'addRecipeInformation' => 'true',
        'addRecipeNutrition' => 'true',
        'fillIngredients' => 'true'
    ];

    // Add diet restrictions
    if (!empty($diet)) {
        $params['diet'] = implode(',', $diet);
    }

    // Add meal type
    if (!empty($type)) {
        $params['type'] = $type;
    }

    $url = 'https://api.spoonacular.com/recipes/complexSearch?' . http_build_query($params);

    $response = makeApiRequest($url);

    if ($response && isset($response['results'])) {
        return $response['results'];
    }

    return false;
}

/**
 * Fetch African recipes from Edamam Recipe API
 *
 * @param string $cuisine Cuisine type (e.g., 'African', 'Middle Eastern')
 * @param int $from Starting index (for pagination)
 * @param int $to Ending index (max 100 results per request)
 * @param array $diet Diet restrictions (e.g., ['vegetarian', 'vegan'])
 * @param string $mealType Meal type (e.g., 'Breakfast', 'Lunch', 'Dinner')
 * @return array|false Recipe data or false on error
 */
function fetchEdamamAfricanRecipes($cuisine = 'African', $from = 0, $to = 10, $diet = [], $mealType = '') {
    $appId = EDAMAM_RECIPE_APP_ID;
    $appKey = EDAMAM_RECIPE_APP_KEY;

    if (empty($appId) || empty($appKey) || $appId === 'your_edamam_app_id_here') {
        error_log('Edamam API credentials not configured');
        return false;
    }

    // Build query parameters
    $params = [
        'type' => 'public',
        'app_id' => $appId,
        'app_key' => $appKey,
        'cuisineType' => $cuisine,
        'from' => $from,
        'to' => min($to, 100) // Max 100 per request
    ];

    // Add diet restrictions
    if (!empty($diet)) {
        foreach ($diet as $dietType) {
            $params['diet'][] = $dietType;
        }
    }

    // Add meal type
    if (!empty($mealType)) {
        $params['mealType'] = $mealType;
    }

    $url = 'https://api.edamam.com/api/recipes/v2?' . http_build_query($params);

    $response = makeApiRequest($url);

    if ($response && isset($response['hits'])) {
        return array_map(function($hit) {
            return $hit['recipe'];
        }, $response['hits']);
    }

    return false;
}

/**
 * Search for specific African dishes by name
 *
 * @param string $dishName Name of the dish (e.g., 'jollof rice', 'tagine', 'injera')
 * @param string $api API to use ('spoonacular' or 'edamam')
 * @param int $limit Number of results to return
 * @return array|false Recipe data or false on error
 */
function searchAfricanDishByName($dishName, $api = 'spoonacular', $limit = 5) {
    switch ($api) {
        case 'spoonacular':
            return searchSpoonacularByName($dishName, $limit);
        case 'edamam':
            return searchEdamamByName($dishName, $limit);
        default:
            return false;
    }
}

/**
 * Search Spoonacular by dish name
 */
function searchSpoonacularByName($dishName, $limit = 5) {
    $apiKey = SPOONACULAR_API_KEY;

    if (empty($apiKey) || $apiKey === 'your_spoonacular_api_key_here') {
        return false;
    }

    $params = [
        'apiKey' => $apiKey,
        'query' => $dishName,
        'number' => $limit,
        'addRecipeInformation' => 'true',
        'addRecipeNutrition' => 'true'
    ];

    $url = 'https://api.spoonacular.com/recipes/complexSearch?' . http_build_query($params);

    $response = makeApiRequest($url);

    if ($response && isset($response['results'])) {
        return $response['results'];
    }

    return false;
}

/**
 * Search Edamam by dish name
 */
function searchEdamamByName($dishName, $limit = 5) {
    $appId = EDAMAM_RECIPE_APP_ID;
    $appKey = EDAMAM_RECIPE_APP_KEY;

    if (empty($appId) || empty($appKey)) {
        return false;
    }

    $params = [
        'type' => 'public',
        'app_id' => $appId,
        'app_key' => $appKey,
        'q' => $dishName,
        'to' => $limit
    ];

    $url = 'https://api.edamam.com/api/recipes/v2?' . http_build_query($params);

    $response = makeApiRequest($url);

    if ($response && isset($response['hits'])) {
        return array_map(function($hit) {
            return $hit['recipe'];
        }, $response['hits']);
    }

    return false;
}

/**
 * Convert external API recipe data to our database format
 *
 * @param array $recipe Recipe data from external API
 * @param string $source API source ('spoonacular' or 'edamam')
 * @param string $sourceEmotion Source emotion for mood mapping
 * @param string $targetEmotion Target emotion for mood mapping
 * @return array Formatted recipe data for our database
 */
function convertExternalRecipeToOurFormat($recipe, $source, $sourceEmotion = '', $targetEmotion = '') {
    $formatted = [
        'title' => '',
        'description' => '',
        'content' => '',
        'type' => 'african_meals',
        'source_emotion' => $sourceEmotion,
        'target_emotion' => $targetEmotion,
        'image_url' => '',
        'link' => '',
        'external_id' => '',
        'external_source' => $source,
        'created_at' => date('Y-m-d H:i:s')
    ];

    switch ($source) {
        case 'spoonacular':
            $formatted['title'] = $recipe['title'] ?? '';
            $formatted['description'] = generateDescriptionFromSpoonacular($recipe);
            $formatted['content'] = generateContentFromSpoonacular($recipe);
            $formatted['image_url'] = $recipe['image'] ?? '';
            $formatted['link'] = $recipe['sourceUrl'] ?? '';
            $formatted['external_id'] = $recipe['id'] ?? '';
            break;

        case 'edamam':
            $formatted['title'] = $recipe['label'] ?? '';
            $formatted['description'] = generateDescriptionFromEdamam($recipe);
            $formatted['content'] = generateContentFromEdamam($recipe);
            $formatted['image_url'] = $recipe['image'] ?? '';
            $formatted['link'] = $recipe['url'] ?? '';
            $formatted['external_id'] = $recipe['uri'] ?? '';
            break;
    }

    return $formatted;
}

/**
 * Generate description from Spoonacular recipe data
 */
function generateDescriptionFromSpoonacular($recipe) {
    $description = '';

    if (isset($recipe['summary'])) {
        // Remove HTML tags and limit length
        $description = strip_tags($recipe['summary']);
        $description = substr($description, 0, 200) . '...';
    } elseif (isset($recipe['cuisines']) && !empty($recipe['cuisines'])) {
        $cuisine = $recipe['cuisines'][0];
        $description = "A delicious {$cuisine} dish";

        if (isset($recipe['dishTypes']) && !empty($recipe['dishTypes'])) {
            $dishType = $recipe['dishTypes'][0];
            $description .= " perfect as a {$dishType}";
        }

        $description .= ".";
    }

    return $description;
}

/**
 * Generate content from Spoonacular recipe data
 */
function generateContentFromSpoonacular($recipe) {
    $content = [];

    // Add ingredients
    if (isset($recipe['extendedIngredients']) && !empty($recipe['extendedIngredients'])) {
        $ingredients = array_map(function($ing) {
            return $ing['original'] ?? $ing['name'] ?? '';
        }, $recipe['extendedIngredients']);

        $content[] = "Ingredients: " . implode(', ', array_filter($ingredients));
    }

    // Add nutrition info
    if (isset($recipe['nutrition']['nutrients'])) {
        $nutrition = [];
        foreach ($recipe['nutrition']['nutrients'] as $nutrient) {
            if (in_array($nutrient['name'], ['Calories', 'Protein', 'Fat', 'Carbohydrates'])) {
                $nutrition[] = $nutrient['name'] . ': ' . $nutrient['amount'] . $nutrient['unit'];
            }
        }
        if (!empty($nutrition)) {
            $content[] = "Nutrition: " . implode(', ', $nutrition);
        }
    }

    // Add cooking time
    if (isset($recipe['readyInMinutes'])) {
        $content[] = "Cooking time: {$recipe['readyInMinutes']} minutes";
    }

    // Add servings
    if (isset($recipe['servings'])) {
        $content[] = "Serves: {$recipe['servings']} people";
    }

    return implode('. ', $content);
}

/**
 * Generate description from Edamam recipe data
 */
function generateDescriptionFromEdamam($recipe) {
    $description = '';

    if (isset($recipe['cuisineType']) && !empty($recipe['cuisineType'])) {
        $cuisine = ucfirst($recipe['cuisineType'][0]);
        $description = "A traditional {$cuisine} recipe";

        if (isset($recipe['mealType']) && !empty($recipe['mealType'])) {
            $mealType = strtolower($recipe['mealType'][0]);
            $description .= " perfect for {$mealType}";
        }

        $description .= ".";
    }

    // Add diet labels
    if (isset($recipe['dietLabels']) && !empty($recipe['dietLabels'])) {
        $description .= " " . implode(', ', $recipe['dietLabels']) . " friendly.";
    }

    return $description;
}

/**
 * Generate content from Edamam recipe data
 */
function generateContentFromEdamam($recipe) {
    $content = [];

    // Add ingredients
    if (isset($recipe['ingredientLines']) && !empty($recipe['ingredientLines'])) {
        $content[] = "Ingredients: " . implode(', ', array_slice($recipe['ingredientLines'], 0, 5));
        if (count($recipe['ingredientLines']) > 5) {
            $content[count($content) - 1] .= " and more";
        }
    }

    // Add nutrition info
    if (isset($recipe['totalNutrients'])) {
        $nutrition = [];
        $nutrients = ['ENERC_KCAL' => 'Calories', 'PROCNT' => 'Protein', 'FAT' => 'Fat', 'CHOCDF' => 'Carbs'];

        foreach ($nutrients as $key => $label) {
            if (isset($recipe['totalNutrients'][$key])) {
                $nutrient = $recipe['totalNutrients'][$key];
                $nutrition[] = $label . ': ' . round($nutrient['quantity']) . $nutrient['unit'];
            }
        }

        if (!empty($nutrition)) {
            $content[] = "Nutrition per serving: " . implode(', ', $nutrition);
        }
    }

    // Add servings
    if (isset($recipe['yield'])) {
        $content[] = "Serves: {$recipe['yield']} people";
    }

    return implode('. ', $content);
}

/**
 * Make HTTP request to external API
 *
 * @param string $url API endpoint URL
 * @param array $headers Optional headers
 * @return array|false Decoded JSON response or false on error
 */
function makeApiRequest($url, $headers = []) {
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'MoodifyMe/1.0'
    ]);

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        error_log("API Request Error: " . $error);
        return false;
    }

    if ($httpCode !== 200) {
        error_log("API Request Failed: HTTP {$httpCode}");
        return false;
    }

    $decoded = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Decode Error: " . json_last_error_msg());
        return false;
    }

    return $decoded;
}

/**
 * Get list of popular African dishes to search for
 *
 * @return array List of African dish names
 */
function getPopularAfricanDishes() {
    return [
        // West African
        'jollof rice', 'fufu', 'egusi soup', 'suya', 'akara', 'moin moin', 'waakye', 'banku',
        'kelewele', 'groundnut soup', 'palm nut soup', 'yassa chicken', 'thieboudienne',

        // East African
        'injera', 'doro wat', 'kitfo', 'berbere', 'ugali', 'nyama choma', 'pilau', 'mandazi',
        'chapati', 'sukuma wiki', 'githeri', 'mukimo', 'samosa', 'biryani',

        // North African
        'tagine', 'couscous', 'harira', 'pastilla', 'mechoui', 'shakshuka', 'ful medames',
        'koshari', 'mahshi', 'molokhia', 'baba ganoush', 'hummus', 'falafel',

        // Southern African
        'bobotie', 'boerewors', 'biltong', 'potjiekos', 'sosaties', 'malva pudding',
        'chakalaka', 'pap', 'morogo', 'amadumbe', 'samp and beans',

        // Central African
        'moambe chicken', 'cassava leaves', 'plantain', 'fufu de mais', 'ndole',
        'poulet nyembwe', 'saka saka', 'liboke'
    ];
}

?>
