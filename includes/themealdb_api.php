<?php
/**
 * TheMealDB API Integration for African Recipes
 * Free API with better African cuisine coverage
 */

/**
 * Fetch African recipes from TheMealDB by area
 */
function fetchTheMealDBAfricanRecipes($area = 'Moroccan', $limit = 10) {
    $africanAreas = ['Moroccan', 'Egyptian', 'Tunisian', 'Kenyan'];

    if (!in_array($area, $africanAreas)) {
        $area = 'Moroccan'; // Default to Moroccan
    }

    $url = "https://www.themealdb.com/api/json/v1/1/filter.php?a=" . urlencode($area);

    try {
        $response = file_get_contents($url);
        if ($response === false) {
            return false;
        }

        $data = json_decode($response, true);

        if (!isset($data['meals']) || empty($data['meals'])) {
            return false;
        }

        // Get detailed information for each meal
        $detailedRecipes = [];
        $count = 0;

        foreach ($data['meals'] as $meal) {
            if ($count >= $limit) break;

            $detailUrl = "https://www.themealdb.com/api/json/v1/1/lookup.php?i=" . $meal['idMeal'];
            $detailResponse = file_get_contents($detailUrl);

            if ($detailResponse !== false) {
                $detailData = json_decode($detailResponse, true);
                if (isset($detailData['meals'][0])) {
                    $detailedRecipes[] = $detailData['meals'][0];
                    $count++;
                }
            }

            // Small delay to be respectful
            usleep(200000); // 0.2 seconds
        }

        return $detailedRecipes;

    } catch (Exception $e) {
        error_log("TheMealDB API Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Search for specific African dish in TheMealDB
 */
function searchTheMealDBDish($dishName, $limit = 5) {
    $url = "https://www.themealdb.com/api/json/v1/1/search.php?s=" . urlencode($dishName);

    try {
        $response = file_get_contents($url);
        if ($response === false) {
            return false;
        }

        $data = json_decode($response, true);

        if (!isset($data['meals']) || empty($data['meals'])) {
            return false;
        }

        return array_slice($data['meals'], 0, $limit);

    } catch (Exception $e) {
        error_log("TheMealDB Search Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all available African areas from TheMealDB
 */
function getTheMealDBAfricanAreas() {
    return ['Moroccan', 'Egyptian', 'Tunisian', 'Kenyan'];
}

/**
 * Convert TheMealDB recipe to our format
 */
function convertTheMealDBToOurFormat($recipe, $sourceEmotion = '', $targetEmotion = '') {
    // Build ingredients list
    $ingredients = [];
    for ($i = 1; $i <= 20; $i++) {
        $ingredient = $recipe["strIngredient{$i}"] ?? '';
        $measure = $recipe["strMeasure{$i}"] ?? '';

        if (!empty($ingredient) && trim($ingredient) !== '') {
            $ingredients[] = trim($measure) . ' ' . trim($ingredient);
        }
    }

    // Build content with instructions and ingredients
    $content = "**Ingredients:**\n" . implode("\n", $ingredients) . "\n\n";
    $content .= "**Instructions:**\n" . ($recipe['strInstructions'] ?? '');

    return [
        'title' => $recipe['strMeal'] ?? 'Unknown Recipe',
        'description' => 'Authentic ' . ($recipe['strArea'] ?? 'African') . ' cuisine recipe from TheMealDB',
        'content' => $content,
        'type' => 'african_meals',
        'source_emotion' => $sourceEmotion,
        'target_emotion' => $targetEmotion,
        'image_url' => $recipe['strMealThumb'] ?? '',
        'link' => $recipe['strSource'] ?? $recipe['strYoutube'] ?? '',
        'external_id' => $recipe['idMeal'] ?? '',
        'external_source' => 'themealdb',
        'created_at' => date('Y-m-d H:i:s')
    ];
}

/**
 * Get curated African recipes (manual database for dishes not in APIs)
 */
function getCuratedAfricanRecipes() {
    return [
        // West African
        [
            'title' => 'Nigerian Jollof Rice',
            'description' => 'The crown jewel of West African cuisine - a flavorful one-pot rice dish',
            'content' => "**Ingredients:**\n2 cups jasmine rice\n1/4 cup vegetable oil\n1 large onion, diced\n3 cloves garlic, minced\n1 red bell pepper\n1 can tomato paste\n1 can diced tomatoes\n2 cups chicken stock\n1 tsp curry powder\n1 tsp thyme\n2 bay leaves\n1 lb chicken pieces\nSalt and pepper to taste\n\n**Instructions:**\nRinse rice until water runs clear. Heat oil in large pot, sauté onions until golden. Add garlic, bell pepper, cook 2 minutes. Stir in tomato paste, cook 3 minutes. Add diced tomatoes, stock, spices. Bring to boil, add rice and chicken. Cover, simmer 20-25 minutes until rice is tender.",
            'area' => 'Nigerian',
            'image_url' => 'assets/images/recommendations/jollof_rice.jpg',
            'link' => 'https://www.allrecipes.com/recipe/268547/authentic-jollof-rice/'
        ],
        [
            'title' => 'Ghanaian Kelewele',
            'description' => 'Spicy fried plantain cubes - a popular Ghanaian street food',
            'content' => "**Ingredients:**\n4 ripe plantains\n2 tsp ginger, grated\n2 cloves garlic, minced\n1 tsp cayenne pepper\n1 tsp nutmeg\n1 tsp cloves, ground\nSalt to taste\nVegetable oil for frying\n\n**Instructions:**\nPeel and cube plantains. Mix ginger, garlic, and spices. Toss plantains with spice mixture. Heat oil to 350°F. Fry plantain cubes until golden brown, about 3-4 minutes. Drain on paper towels.",
            'area' => 'Ghanaian',
            'image_url' => 'assets/images/recommendations/kelewele.jpg',
            'link' => 'https://www.ghana.com/recipes/kelewele/'
        ],
        [
            'title' => 'Senegalese Thieboudienne',
            'description' => 'The national dish of Senegal - fish and rice with vegetables',
            'content' => "**Ingredients:**\n2 lbs white fish fillets\n2 cups broken rice\n1 large onion\n3 tomatoes\n1 eggplant\n2 carrots\n1 cabbage\n1 sweet potato\n2 tbsp tomato paste\n1 scotch bonnet pepper\nParsley, thyme\nPalm oil\nStock cubes\n\n**Instructions:**\nStuff fish with herbs and spices. Brown fish in palm oil. Sauté onions and tomatoes. Add tomato paste, vegetables, and stock. Simmer until vegetables are tender. Add rice and cook until done.",
            'area' => 'Senegalese',
            'image_url' => 'assets/images/recommendations/thieboudienne.jpg',
            'link' => 'https://www.senegalese-recipes.com/thieboudienne/'
        ],

        // East African
        [
            'title' => 'Ethiopian Doro Wat',
            'description' => 'Ethiopia\'s national dish - spicy chicken stew with berbere spice',
            'content' => "**Ingredients:**\n1 whole chicken, cut up\n2 large onions, diced\n1/4 cup berbere spice blend\n2 tbsp tomato paste\n1 cup chicken stock\n4 hard-boiled eggs\n2 tbsp niter kibbeh (spiced butter)\n3 cloves garlic\n1 inch ginger\nSalt to taste\n\n**Instructions:**\nSauté onions until golden. Add berbere spice, cook 2 minutes. Add tomato paste, garlic, ginger. Add chicken pieces, brown well. Pour in stock, simmer 45 minutes. Add boiled eggs, simmer 10 more minutes.",
            'area' => 'Ethiopian',
            'image_url' => 'assets/images/recommendations/doro_wat.jpg',
            'link' => 'https://www.ethiopian-recipes.com/doro-wat/'
        ],
        [
            'title' => 'Kenyan Ugali',
            'description' => 'Kenya\'s staple food - simple cornmeal dish served with stews',
            'content' => "**Ingredients:**\n2 cups white cornmeal (maize flour)\n3 cups water\nSalt to taste\n\n**Instructions:**\nBoil water with salt in heavy-bottomed pot. Gradually add cornmeal while stirring continuously to prevent lumps. Reduce heat, continue stirring until mixture pulls away from sides of pot and forms a firm dough, about 10-15 minutes. Serve hot with vegetables or meat stews.",
            'area' => 'Kenyan',
            'image_url' => 'assets/images/recommendations/ugali.jpg',
            'link' => 'https://www.kenyan-recipes.com/ugali/'
        ],

        // South African
        [
            'title' => 'South African Bobotie',
            'description' => 'South Africa\'s national dish - spiced meatloaf with egg topping',
            'content' => "**Ingredients:**\n2 lbs ground beef\n2 onions, diced\n2 slices bread, soaked in milk\n2 eggs\n2 tbsp curry powder\n1 tbsp turmeric\n2 tbsp chutney\n2 tbsp vinegar\n1/4 cup raisins\n1/4 cup almonds\nBay leaves\nSalt and pepper\n\n**Instructions:**\nSauté onions, add meat and spices. Mix in bread, chutney, raisins, almonds. Press into baking dish, top with bay leaves. Beat eggs with milk, pour over meat. Bake at 350°F for 45 minutes until golden.",
            'area' => 'South African',
            'image_url' => 'assets/images/recommendations/bobotie.jpg',
            'link' => 'https://www.south-african-recipes.com/bobotie/'
        ],

        // North African
        [
            'title' => 'Moroccan Couscous',
            'description' => 'Traditional Moroccan couscous with seven vegetables',
            'content' => "**Ingredients:**\n2 cups couscous\n2 cups warm water\n1 lb lamb, cubed\n2 onions\n3 carrots\n2 zucchini\n1 turnip\n1 cup chickpeas\n1 bunch cilantro\n1 tsp ginger\n1 tsp turmeric\nSaffron\nSalt and pepper\n\n**Instructions:**\nSteam couscous according to package directions. In large pot, brown lamb with onions. Add spices, vegetables, and water. Simmer until tender. Serve couscous topped with vegetables and broth.",
            'area' => 'Moroccan',
            'image_url' => 'assets/images/recommendations/couscous.jpg',
            'link' => 'https://www.moroccan-recipes.com/couscous/'
        ]
    ];
}

?>
