<?php
/**
 * MoodifyMe - Populate Meal Data
 * Populates the database with curated meal recommendations for different moods
 */

// Include configuration
require_once '../config.php';
require_once '../includes/db_connect.php';

// Set content type
header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n<title>Populate Meal Data</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }\n";
echo ".container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }\n";
echo ".success { color: #28a745; }\n";
echo ".error { color: #dc3545; }\n";
echo ".info { color: #17a2b8; }\n";
echo "h1 { color: #333; }\n";
echo "h2 { color: #666; margin-top: 30px; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";
echo "<div class='container'>\n";

echo "<h1>🍽️ Populating Meal Recommendations</h1>\n";
echo "<p>Adding curated meal recommendations for different mood transitions...</p>\n";

// Define comprehensive meal recommendations for all mood transitions
$mealRecommendations = [
    // Sad to Happy
    [
        'title' => 'Chocolate Chip Cookies',
        'description' => 'Warm, freshly baked chocolate chip cookies that bring instant comfort and joy. The sweet aroma and taste can help lift your spirits.',
        'type' => 'meals',
        'source_emotion' => 'sad',
        'target_emotion' => 'happy',
        'content' => 'Classic comfort food that triggers happy memories and releases endorphins.',
        'image_url' => 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=400',
        'link' => 'https://www.allrecipes.com/recipe/10813/best-chocolate-chip-cookies/',
        'ingredients' => json_encode([
            '2 1/4 cups all-purpose flour',
            '1 tsp baking soda',
            '1 tsp salt',
            '1 cup butter, softened',
            '3/4 cup granulated sugar',
            '3/4 cup brown sugar',
            '2 large eggs',
            '2 tsp vanilla extract',
            '2 cups chocolate chips'
        ]),
        'cooking_time' => '25 minutes',
        'difficulty' => 'Easy',
        'servings' => '24 cookies',
        'cuisine_type' => 'American',
        'dietary_tags' => 'Vegetarian',
        'nutrition_info' => 'Approximately 150 calories per cookie'
    ],
    [
        'title' => 'Rainbow Fruit Salad',
        'description' => 'A vibrant, colorful fruit salad with fresh berries, citrus, and tropical fruits that naturally boost mood with vitamins and natural sugars.',
        'type' => 'meals',
        'source_emotion' => 'sad',
        'target_emotion' => 'happy',
        'content' => 'Colorful foods and natural sugars help stimulate serotonin production.',
        'image_url' => 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=400',
        'link' => 'https://www.allrecipes.com/recipe/14276/fresh-fruit-salad/',
        'ingredients' => json_encode([
            '2 cups strawberries, sliced',
            '1 cup blueberries',
            '1 cup pineapple chunks',
            '2 oranges, segmented',
            '1 cup grapes',
            '2 kiwis, sliced',
            '2 tbsp honey',
            'Fresh mint leaves'
        ]),
        'cooking_time' => '15 minutes',
        'difficulty' => 'Easy',
        'servings' => '6 people',
        'cuisine_type' => 'Healthy',
        'dietary_tags' => 'Vegan, Gluten-Free, Raw',
        'nutrition_info' => 'High in vitamin C and natural antioxidants'
    ],
    [
        'title' => 'Comfort Mac and Cheese',
        'description' => 'Creamy, cheesy macaroni and cheese that provides ultimate comfort and nostalgic happiness.',
        'type' => 'meals',
        'source_emotion' => 'sad',
        'target_emotion' => 'happy',
        'content' => 'Ultimate comfort food that triggers positive childhood memories.',
        'image_url' => 'https://images.unsplash.com/photo-1543826173-1ad8b0b1c7b3?w=400',
        'link' => 'https://www.allrecipes.com/recipe/238691/simple-macaroni-and-cheese/',
        'ingredients' => json_encode([
            '1 lb elbow macaroni',
            '4 cups sharp cheddar cheese, shredded',
            '3 cups whole milk',
            '4 tbsp butter',
            '4 tbsp flour',
            '1 tsp salt',
            '1/2 tsp pepper',
            'Breadcrumb topping'
        ]),
        'cooking_time' => '45 minutes',
        'difficulty' => 'Medium',
        'servings' => '8 people',
        'cuisine_type' => 'American',
        'dietary_tags' => 'Vegetarian',
        'nutrition_info' => 'Rich in calcium and protein'
    ],
    [
        'title' => 'Chicken Noodle Soup',
        'description' => 'A warm, comforting bowl of homemade chicken noodle soup that soothes the soul and brings feelings of care and warmth.',
        'type' => 'meals',
        'source_emotion' => 'sad',
        'target_emotion' => 'happy',
        'content' => 'The ultimate comfort food that provides warmth and nourishment.',
        'image_url' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400',
        'link' => 'https://www.allrecipes.com/recipe/26460/chicken-noodle-soup/',
        'ingredients' => json_encode([
            '1 whole chicken',
            '8 cups water',
            '2 carrots, sliced',
            '2 celery stalks, chopped',
            '1 onion, diced',
            '2 cups egg noodles',
            'Salt and pepper to taste',
            'Fresh parsley'
        ]),
        'cooking_time' => '1 hour 30 minutes',
        'difficulty' => 'Medium',
        'servings' => '6 people',
        'cuisine_type' => 'Comfort Food',
        'dietary_tags' => 'Gluten-Free option available',
        'nutrition_info' => 'Rich in protein and vitamins'
    ],
    
    // Stressed to Calm
    [
        'title' => 'Chamomile Tea with Honey',
        'description' => 'A soothing herbal tea blend that helps reduce stress and promotes relaxation with natural calming properties.',
        'type' => 'meals',
        'source_emotion' => 'stressed',
        'target_emotion' => 'calm',
        'content' => 'Herbal remedy known for its calming and stress-reducing effects.',
        'image_url' => 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400',
        'link' => 'https://www.healthline.com/nutrition/chamomile-tea',
        'ingredients' => json_encode([
            '1 chamomile tea bag or 1 tsp dried chamomile',
            '1 cup hot water',
            '1 tsp honey',
            'Lemon slice (optional)'
        ]),
        'cooking_time' => '5 minutes',
        'difficulty' => 'Easy',
        'servings' => '1 person',
        'cuisine_type' => 'Herbal',
        'dietary_tags' => 'Vegan, Caffeine-Free, Gluten-Free',
        'nutrition_info' => 'Low calorie, rich in antioxidants'
    ],
    [
        'title' => 'Lavender Shortbread',
        'description' => 'Delicate, buttery shortbread cookies infused with calming lavender that helps reduce anxiety and stress.',
        'type' => 'meals',
        'source_emotion' => 'stressed',
        'target_emotion' => 'calm',
        'content' => 'Lavender has natural calming properties that can help reduce stress levels.',
        'image_url' => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400',
        'link' => 'https://www.foodnetwork.com/recipes/lavender-shortbread-recipe',
        'ingredients' => json_encode([
            '2 cups all-purpose flour',
            '1/2 cup powdered sugar',
            '1 cup butter, softened',
            '1 tbsp dried lavender buds',
            '1/4 tsp salt'
        ]),
        'cooking_time' => '45 minutes',
        'difficulty' => 'Medium',
        'servings' => '20 cookies',
        'cuisine_type' => 'European',
        'dietary_tags' => 'Vegetarian',
        'nutrition_info' => 'Contains calming lavender compounds'
    ],
    
    // Tired to Energized
    [
        'title' => 'Green Smoothie Bowl',
        'description' => 'A nutrient-packed smoothie bowl with spinach, banana, and berries that provides natural energy and vitality.',
        'type' => 'meals',
        'source_emotion' => 'tired',
        'target_emotion' => 'excited',
        'content' => 'Packed with vitamins, minerals, and natural sugars for sustained energy.',
        'image_url' => 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400',
        'link' => 'https://minimalistbaker.com/green-smoothie-bowl/',
        'ingredients' => json_encode([
            '2 cups fresh spinach',
            '1 frozen banana',
            '1/2 cup mixed berries',
            '1/2 avocado',
            '1 cup almond milk',
            'Granola for topping',
            'Chia seeds',
            'Fresh fruit for garnish'
        ]),
        'cooking_time' => '10 minutes',
        'difficulty' => 'Easy',
        'servings' => '1 person',
        'cuisine_type' => 'Health Food',
        'dietary_tags' => 'Vegan, Gluten-Free, Raw',
        'nutrition_info' => 'High in vitamins A, C, K, and fiber'
    ],
    [
        'title' => 'Espresso Chocolate Muffins',
        'description' => 'Rich, moist muffins with a double shot of espresso and dark chocolate for an energy boost and mood lift.',
        'type' => 'meals',
        'source_emotion' => 'tired',
        'target_emotion' => 'excited',
        'content' => 'Caffeine and chocolate combination for natural energy and happiness boost.',
        'image_url' => 'https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=400',
        'link' => 'https://www.kingarthurbaking.com/recipes/espresso-chocolate-chip-muffins-recipe',
        'ingredients' => json_encode([
            '2 cups all-purpose flour',
            '3/4 cup sugar',
            '2 tsp baking powder',
            '1/2 cup butter, melted',
            '2 eggs',
            '1 cup milk',
            '2 shots espresso',
            '1 cup dark chocolate chips'
        ]),
        'cooking_time' => '35 minutes',
        'difficulty' => 'Medium',
        'servings' => '12 muffins',
        'cuisine_type' => 'American',
        'dietary_tags' => 'Vegetarian',
        'nutrition_info' => 'Contains caffeine and antioxidants'
    ],
    
    // Anxious to Calm
    [
        'title' => 'Warm Oatmeal with Cinnamon',
        'description' => 'Creamy, warm oatmeal with cinnamon and honey that provides comfort and helps stabilize blood sugar for reduced anxiety.',
        'type' => 'meals',
        'source_emotion' => 'anxious',
        'target_emotion' => 'calm',
        'content' => 'Complex carbohydrates help stabilize mood and reduce anxiety symptoms.',
        'image_url' => 'https://images.unsplash.com/photo-1517686469429-8bdb88b9f907?w=400',
        'link' => 'https://www.quaker.com/recipes/hot-cereals/basic-oatmeal-recipe',
        'ingredients' => json_encode([
            '1 cup rolled oats',
            '2 cups water or milk',
            '1 tsp cinnamon',
            '2 tbsp honey',
            '1/4 cup chopped nuts',
            'Fresh berries',
            'Pinch of salt'
        ]),
        'cooking_time' => '15 minutes',
        'difficulty' => 'Easy',
        'servings' => '2 people',
        'cuisine_type' => 'Comfort Food',
        'dietary_tags' => 'Vegetarian, Gluten-Free option',
        'nutrition_info' => 'High in fiber and complex carbohydrates'
    ],
    
    // Bored to Excited
    [
        'title' => 'Spicy Thai Pad Thai',
        'description' => 'Vibrant, flavorful stir-fried noodles with a perfect balance of sweet, sour, and spicy flavors that awaken the senses.',
        'type' => 'meals',
        'source_emotion' => 'bored',
        'target_emotion' => 'excited',
        'content' => 'Bold flavors and aromatic spices stimulate the senses and create excitement.',
        'image_url' => 'https://images.unsplash.com/photo-1559314809-0f31657def5e?w=400',
        'link' => 'https://www.allrecipes.com/recipe/231609/simple-pad-thai/',
        'ingredients' => json_encode([
            '8 oz rice noodles',
            '3 tbsp fish sauce',
            '3 tbsp brown sugar',
            '2 tbsp lime juice',
            '2 eggs',
            '1 cup bean sprouts',
            '3 green onions',
            '1/4 cup peanuts',
            'Chili flakes to taste'
        ]),
        'cooking_time' => '30 minutes',
        'difficulty' => 'Medium',
        'servings' => '4 people',
        'cuisine_type' => 'Thai',
        'dietary_tags' => 'Gluten-Free, Spicy',
        'nutrition_info' => 'Rich in protein and vegetables'
    ],
    [
        'title' => 'Sizzling Fajitas',
        'description' => 'Exciting sizzling chicken fajitas with colorful peppers and onions that create a fun, interactive dining experience.',
        'type' => 'meals',
        'source_emotion' => 'bored',
        'target_emotion' => 'excited',
        'content' => 'Interactive cooking and vibrant presentation create excitement and engagement.',
        'image_url' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400',
        'link' => 'https://www.allrecipes.com/recipe/76362/chicken-fajitas/',
        'ingredients' => json_encode([
            '1 lb chicken breast, sliced',
            '2 bell peppers, sliced',
            '1 large onion, sliced',
            '2 tbsp fajita seasoning',
            '2 tbsp olive oil',
            'Flour tortillas',
            'Sour cream',
            'Guacamole',
            'Salsa'
        ]),
        'cooking_time' => '25 minutes',
        'difficulty' => 'Easy',
        'servings' => '4 people',
        'cuisine_type' => 'Mexican',
        'dietary_tags' => 'Gluten-Free option',
        'nutrition_info' => 'High in protein and vegetables'
    ],

    // Happy to Calm
    [
        'title' => 'Herbal Tea Blend',
        'description' => 'A soothing blend of chamomile, lavender, and lemon balm that helps transition from excitement to peaceful calm.',
        'type' => 'meals',
        'source_emotion' => 'happy',
        'target_emotion' => 'calm',
        'content' => 'Herbal compounds naturally promote relaxation and peaceful feelings.',
        'image_url' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400',
        'link' => 'https://www.healthline.com/nutrition/chamomile-tea',
        'ingredients' => json_encode([
            '1 tsp dried chamomile',
            '1/2 tsp dried lavender',
            '1/2 tsp lemon balm',
            '1 cup hot water',
            'Honey to taste'
        ]),
        'cooking_time' => '5 minutes',
        'difficulty' => 'Easy',
        'servings' => '1 person',
        'cuisine_type' => 'Herbal',
        'dietary_tags' => 'Vegan, Caffeine-Free, Gluten-Free',
        'nutrition_info' => 'Calming herbs with antioxidants'
    ],

    // Angry to Calm
    [
        'title' => 'Cooling Cucumber Soup',
        'description' => 'A refreshing, cooling cucumber soup that helps reduce heat and anger while promoting inner peace.',
        'type' => 'meals',
        'source_emotion' => 'angry',
        'target_emotion' => 'calm',
        'content' => 'Cooling foods help reduce internal heat and promote emotional balance.',
        'image_url' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400',
        'link' => 'https://www.allrecipes.com/recipe/213742/cold-cucumber-soup/',
        'ingredients' => json_encode([
            '4 large cucumbers, peeled',
            '1 cup Greek yogurt',
            '2 tbsp fresh dill',
            '1 tbsp lemon juice',
            '1 clove garlic, minced',
            'Salt and pepper to taste',
            'Fresh mint for garnish'
        ]),
        'cooking_time' => '15 minutes',
        'difficulty' => 'Easy',
        'servings' => '4 people',
        'cuisine_type' => 'Mediterranean',
        'dietary_tags' => 'Vegetarian, Gluten-Free, Low-Calorie',
        'nutrition_info' => 'Hydrating and cooling with probiotics'
    ],

    // Excited to Calm
    [
        'title' => 'Warm Golden Milk',
        'description' => 'A soothing turmeric latte with warm spices that helps transition from high energy to peaceful relaxation.',
        'type' => 'meals',
        'source_emotion' => 'excited',
        'target_emotion' => 'calm',
        'content' => 'Turmeric and warm spices have natural calming and anti-inflammatory properties.',
        'image_url' => 'https://images.unsplash.com/photo-1571934811356-5cc061b6821f?w=400',
        'link' => 'https://www.healthline.com/nutrition/golden-milk-turmeric-latte',
        'ingredients' => json_encode([
            '1 cup coconut milk',
            '1 tsp turmeric powder',
            '1/2 tsp cinnamon',
            '1/4 tsp ginger powder',
            'Pinch of black pepper',
            '1 tbsp honey',
            'Cardamom pods'
        ]),
        'cooking_time' => '10 minutes',
        'difficulty' => 'Easy',
        'servings' => '1 person',
        'cuisine_type' => 'Ayurvedic',
        'dietary_tags' => 'Vegan option, Gluten-Free, Anti-inflammatory',
        'nutrition_info' => 'Rich in antioxidants and anti-inflammatory compounds'
    ]
];

$successCount = 0;
$errorCount = 0;

echo "<h2>Adding Meal Recommendations</h2>\n";

foreach ($mealRecommendations as $index => $meal) {
    echo "<h3>Adding: {$meal['title']}</h3>\n";
    
    try {
        // Check if meal already exists
        $stmt = $conn->prepare("SELECT id FROM recommendations WHERE title = ? AND type = 'meals'");
        $stmt->bind_param("s", $meal['title']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "<p class='info'>ℹ️ Meal '{$meal['title']}' already exists, skipping...</p>\n";
            continue;
        }
        
        // Insert the meal
        $stmt = $conn->prepare("
            INSERT INTO recommendations (
                title, description, type, source_emotion, target_emotion, content, 
                image_url, link, ingredients, cooking_time, difficulty, servings, 
                cuisine_type, dietary_tags, nutrition_info, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->bind_param(
            "sssssssssssssss",
            $meal['title'],
            $meal['description'],
            $meal['type'],
            $meal['source_emotion'],
            $meal['target_emotion'],
            $meal['content'],
            $meal['image_url'],
            $meal['link'],
            $meal['ingredients'],
            $meal['cooking_time'],
            $meal['difficulty'],
            $meal['servings'],
            $meal['cuisine_type'],
            $meal['dietary_tags'],
            $meal['nutrition_info']
        );
        
        if ($stmt->execute()) {
            echo "<p class='success'>✅ Successfully added: {$meal['title']}</p>\n";
            $successCount++;
        } else {
            echo "<p class='error'>❌ Failed to add: {$meal['title']} - " . $stmt->error . "</p>\n";
            $errorCount++;
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error adding {$meal['title']}: " . $e->getMessage() . "</p>\n";
        $errorCount++;
    }
}

// Summary
echo "<h2>📋 Population Summary</h2>\n";
echo "<p class='success'>✅ Successfully added: <strong>$successCount</strong> meals</p>\n";
if ($errorCount > 0) {
    echo "<p class='error'>❌ Errors: <strong>$errorCount</strong></p>\n";
}

// Show current meal count
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM recommendations WHERE type = 'meals'");
    $row = $result->fetch_assoc();
    echo "<p class='info'>📊 Total meals in database: <strong>{$row['count']}</strong></p>\n";
} catch (Exception $e) {
    echo "<p class='error'>❌ Failed to count meals: " . $e->getMessage() . "</p>\n";
}

echo "<h3>Next Steps:</h3>\n";
echo "<ol>\n";
echo "<li>Test the meal recommendations in the MoodifyMe app</li>\n";
echo "<li>Try different mood transitions to see the meals</li>\n";
echo "<li>Add more meals as needed</li>\n";
echo "</ol>\n";

echo "</div>\n</body>\n</html>\n";

$conn->close();
?>
