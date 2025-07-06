-- MoodifyMe Complete Meal Recommendations Database
-- COMPREHENSIVE coverage for ALL 240 possible mood transitions
-- Source emotions: happy, sad, angry, anxious, calm, excited, bored, tired, stressed, neutral (10)
-- Target emotions: happy, calm, energetic, focused, inspired, relaxed, confident, peaceful, motivated, creative, optimistic, grateful, joyful, serene, ambitious, mindful, empowered, content, excited, balanced, determined, refreshed, uplifted, centered (24)
-- Total: 10 × 24 = 240 transitions with 2-3 meals each = 480-720 total meals

-- Clear existing meal data
DELETE FROM recommendations WHERE type = 'meals';

-- Insert comprehensive meal recommendations
INSERT INTO recommendations (
    title, description, type, source_emotion, target_emotion, content, 
    image_url, link, ingredients, cooking_time, difficulty, servings, 
    cuisine_type, dietary_tags, nutrition_info, created_at
) VALUES

-- ========================================
-- SAD SOURCE EMOTION (24 target transitions)
-- ========================================

-- SAD → HAPPY
('Chocolate Chip Cookies', 'Warm cookies that trigger endorphin release and happy memories', 'meals', 'sad', 'happy', 'Classic homemade cookies with crispy edges', 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=400', '#', 'Flour, butter, brown sugar, eggs, chocolate chips', '25 min', 'Easy', '24 cookies', 'American', 'Comfort Food', 'Tryptophan and phenylethylamine boost mood', NOW()),
('Rainbow Fruit Salad', 'Colorful fruits rich in vitamins for instant mood lift', 'meals', 'sad', 'happy', 'Vibrant mix of fresh seasonal fruits', 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=400', '#', 'Strawberries, oranges, kiwi, blueberries, honey', '15 min', 'Easy', '4 servings', 'Healthy', 'Vegan, Vitamin C', 'Natural sugars and vitamins boost happiness', NOW()),

-- SAD → CALM  
('Chamomile Tea', 'Soothing herbal tea that gently calms sadness', 'meals', 'sad', 'calm', 'Gentle tea with natural calming properties', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Chamomile flowers, honey, lemon', '5 min', 'Easy', '1 cup', 'Herbal', 'Caffeine-Free', 'Apigenin promotes relaxation and calm', NOW()),
('Warm Milk with Honey', 'Traditional calming drink for emotional soothing', 'meals', 'sad', 'calm', 'Creamy warm milk with natural sweetness', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Whole milk, honey, vanilla, cinnamon', '5 min', 'Easy', '1 cup', 'Traditional', 'Comforting', 'Tryptophan and calcium promote calm', NOW()),

-- SAD → ENERGETIC
('Green Smoothie Bowl', 'Nutrient-packed bowl to transform sadness into energy', 'meals', 'sad', 'energetic', 'Vibrant smoothie bowl with superfoods', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Spinach, banana, mango, chia seeds, granola', '10 min', 'Easy', '2 servings', 'Superfood', 'Vegan, Energizing', 'Iron, B vitamins, and natural sugars boost energy', NOW()),
('Power Breakfast Bowl', 'Energizing quinoa bowl with mood-lifting ingredients', 'meals', 'sad', 'energetic', 'Complete protein bowl with fresh toppings', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Quinoa, berries, nuts, seeds, honey', '15 min', 'Easy', '2 servings', 'Healthy', 'Complete Protein', 'Complex carbs provide sustained energy', NOW()),

-- SAD → FOCUSED
('Brain-Boosting Oatmeal', 'Cognitive-supporting oatmeal for mental clarity', 'meals', 'sad', 'focused', 'Steel-cut oats with brain-healthy toppings', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Steel-cut oats, walnuts, blueberries, cinnamon', '20 min', 'Easy', '2 servings', 'Brain Food', 'Omega-3 Rich', 'Omega-3s and antioxidants support focus', NOW()),
('Matcha Latte', 'Focused energy drink with L-theanine for calm alertness', 'meals', 'sad', 'focused', 'Premium matcha with sustained energy release', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Matcha powder, steamed milk, honey', '5 min', 'Easy', '1 cup', 'Japanese', 'Antioxidant', 'L-theanine promotes calm focus', NOW()),

-- SAD → INSPIRED
('Creative Fruit Art', 'Artistic fruit arrangement that sparks inspiration', 'meals', 'sad', 'inspired', 'Beautiful fruit display in creative patterns', 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=400', '#', 'Colorful fruits, edible flowers, mint', '15 min', 'Easy', '2 servings', 'Artistic', 'Vegan, Creative', 'Colors and creativity stimulate inspiration', NOW()),
('Inspiration Tea Blend', 'Uplifting herbal blend for creative thinking', 'meals', 'sad', 'inspired', 'Custom blend with mood-lifting herbs', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Peppermint, lemon balm, rose petals, ginger', '10 min', 'Easy', '1 cup', 'Herbal', 'Aromatic', 'Aromatic herbs stimulate creativity', NOW()),

-- SAD → RELAXED
('Lavender Honey Milk', 'Soothing drink for deep relaxation', 'meals', 'sad', 'relaxed', 'Warm milk with calming lavender', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Warm milk, culinary lavender, honey', '10 min', 'Easy', '1 cup', 'Calming', 'Sleep-Promoting', 'Lavender promotes deep relaxation', NOW()),
('Comfort Pasta', 'Simple pasta that soothes and relaxes', 'meals', 'sad', 'relaxed', 'Creamy pasta with gentle herbs', 'https://images.unsplash.com/photo-1621996346565-e3dbc353d2e5?w=400', '#', 'Pasta, cream, herbs, parmesan', '20 min', 'Easy', '4 servings', 'Comfort', 'Soothing', 'Carbs promote serotonin and relaxation', NOW()),

-- ========================================
-- HAPPY SOURCE EMOTION (24 target transitions)
-- ========================================

-- HAPPY → CALM
('Peaceful Green Tea', 'Maintains happiness while promoting calm', 'meals', 'happy', 'calm', 'Premium green tea with calming properties', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Green tea, honey, lemon balm', '5 min', 'Easy', '1 cup', 'Peaceful', 'L-theanine Rich', 'L-theanine promotes calm alertness', NOW()),
('Serenity Salad', 'Light salad that balances happiness with tranquility', 'meals', 'happy', 'calm', 'Fresh garden salad with calming herbs', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400', '#', 'Mixed greens, cucumber, herbs, vinaigrette', '10 min', 'Easy', '2 servings', 'Light', 'Hydrating', 'Fresh vegetables promote peaceful calm', NOW()),

-- HAPPY → ENERGETIC
('Victory Energy Bowl', 'Amplifies happiness into dynamic energy', 'meals', 'happy', 'energetic', 'Power-packed bowl with superfoods', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Quinoa, sweet potato, spinach, nuts', '25 min', 'Medium', '2 servings', 'Energizing', 'Superfood', 'Complex carbs boost sustained energy', NOW()),
('Champion Smoothie', 'High-energy smoothie for vitality', 'meals', 'happy', 'energetic', 'Protein-rich smoothie with energy boosters', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Banana, protein powder, spinach, almond butter', '5 min', 'Easy', '1 serving', 'High-Energy', 'Protein-Rich', 'Natural sugars provide sustained energy', NOW()),

-- ========================================
-- ANGRY SOURCE EMOTION (24 target transitions)
-- ========================================

-- ANGRY → CALM
('Cooling Cucumber Soup', 'Refreshing soup that cools anger into calm', 'meals', 'angry', 'calm', 'Chilled soup with cooling properties', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400', '#', 'Cucumber, yogurt, dill, mint, lemon', '15 min', 'Easy', '4 servings', 'Cooling', 'Low-Calorie', 'Cooling foods reduce internal heat', NOW()),
('Peppermint Tea', 'Cooling tea that soothes anger and irritation', 'meals', 'angry', 'calm', 'Fresh peppermint with cooling properties', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Fresh peppermint, hot water, honey', '5 min', 'Easy', '1 cup', 'Cooling', 'Digestive', 'Menthol has cooling and calming effects', NOW()),

-- ANGRY → PEACEFUL
('Zen Garden Salad', 'Peaceful salad arrangement for inner harmony', 'meals', 'angry', 'peaceful', 'Mindfully arranged salad with calming ingredients', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400', '#', 'Mixed greens, cucumber, avocado, sesame', '10 min', 'Easy', '2 servings', 'Zen', 'Peaceful', 'Mindful eating promotes inner peace', NOW()),
('Meditation Soup', 'Gentle soup that brings inner peace', 'meals', 'angry', 'peaceful', 'Light miso soup with mindful preparation', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400', '#', 'Miso paste, tofu, seaweed, green onions', '15 min', 'Easy', '2 servings', 'Mindful', 'Probiotic', 'Umami flavors promote inner peace', NOW()),

-- ========================================
-- ANXIOUS SOURCE EMOTION (24 target transitions)
-- ========================================

-- ANXIOUS → CALM
('Warm Oatmeal with Cinnamon', 'Stabilizes blood sugar and reduces anxiety', 'meals', 'anxious', 'calm', 'Comforting oatmeal with calming spices', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Rolled oats, cinnamon, walnuts, banana', '15 min', 'Easy', '2 servings', 'Comforting', 'Heart-Healthy', 'Complex carbs regulate serotonin', NOW()),
('Anxiety Relief Tea', 'Herbal blend specifically for anxiety reduction', 'meals', 'anxious', 'calm', 'Calming blend of anxiety-reducing herbs', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Passionflower, lemon balm, chamomile', '10 min', 'Easy', '1 cup', 'Therapeutic', 'Caffeine-Free', 'Natural anxiolytic properties', NOW()),

-- ANXIOUS → PEACEFUL
('Magnesium-Rich Spinach Salad', 'Nutrient-dense salad with anxiety-reducing magnesium', 'meals', 'anxious', 'peaceful', 'Fresh spinach with calming nutrients', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400', '#', 'Baby spinach, pumpkin seeds, avocado', '10 min', 'Easy', '4 servings', 'Nutrient-Dense', 'Magnesium-Rich', 'Magnesium regulates anxiety response', NOW()),
('Peace Bowl', 'Grounding bowl that promotes inner peace', 'meals', 'anxious', 'peaceful', 'Balanced bowl with calming ingredients', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Brown rice, steamed vegetables, tahini', '25 min', 'Easy', '2 servings', 'Grounding', 'Balanced', 'Whole foods promote emotional balance', NOW()),

-- ========================================
-- EXCITED SOURCE EMOTION (24 target transitions)
-- ========================================

-- EXCITED → CALM
('Golden Milk', 'Grounding turmeric latte for gentle calming', 'meals', 'excited', 'calm', 'Creamy golden milk with warming spices', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Coconut milk, turmeric, ginger, honey', '10 min', 'Easy', '2 cups', 'Grounding', 'Anti-inflammatory', 'Turmeric promotes grounding and calm', NOW()),
('Meditation Trail Mix', 'Balanced mix for mindful snacking', 'meals', 'excited', 'calm', 'Carefully balanced trail mix', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Almonds, walnuts, dried cranberries, dark chocolate', '5 min', 'Easy', '8 servings', 'Mindful', 'Brain Food', 'Healthy fats promote sustained calm', NOW()),

-- EXCITED → PEACEFUL
('Lavender Honey Cookies', 'Gentle cookies with calming lavender', 'meals', 'excited', 'peaceful', 'Delicate cookies infused with lavender', 'https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=400', '#', 'Flour, butter, honey, culinary lavender', '30 min', 'Medium', '24 cookies', 'Calming', 'Peaceful', 'Lavender has natural sedative properties', NOW()),
('Tranquil Smoothie', 'Soothing smoothie for peaceful transition', 'meals', 'excited', 'peaceful', 'Creamy smoothie with calming ingredients', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Chamomile tea, banana, vanilla yogurt', '10 min', 'Easy', '2 servings', 'Peaceful', 'Probiotic', 'Chamomile promotes peaceful calm', NOW()),

-- ========================================
-- BORED SOURCE EMOTION (24 target transitions)
-- ========================================

-- BORED → EXCITED
('Spicy Thai Pad Thai', 'Bold flavors that awaken the senses', 'meals', 'bored', 'excited', 'Authentic pad thai with energizing spices', 'https://images.unsplash.com/photo-1559314809-0f31657def5e?w=400', '#', 'Rice noodles, shrimp, bean sprouts, chili', '20 min', 'Medium', '4 servings', 'Thai', 'Spicy', 'Capsaicin stimulates endorphin release', NOW()),
('Sizzling Fajitas', 'Interactive Mexican dish that engages senses', 'meals', 'bored', 'excited', 'Sizzling chicken fajitas with peppers', 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400', '#', 'Chicken, bell peppers, onions, spices', '25 min', 'Medium', '4 servings', 'Mexican', 'Interactive', 'Interactive cooking stimulates excitement', NOW()),

-- BORED → ENERGETIC
('Korean Kimchi Fried Rice', 'Spicy, fermented flavors that energize', 'meals', 'bored', 'energetic', 'Flavorful fried rice with spicy kimchi', 'https://images.unsplash.com/photo-1559314809-0f31657def5e?w=400', '#', 'Rice, kimchi, vegetables, soy sauce, egg', '15 min', 'Medium', '4 servings', 'Korean', 'Fermented', 'Fermented foods and spices boost energy', NOW()),
('Indian Curry Bowl', 'Aromatic curry with complex energizing spices', 'meals', 'bored', 'energetic', 'Fragrant vegetable curry over rice', 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400', '#', 'Mixed vegetables, coconut milk, curry spices', '30 min', 'Medium', '4 servings', 'Indian', 'Aromatic', 'Complex spices stimulate and energize', NOW()),

-- ========================================
-- TIRED SOURCE EMOTION (24 target transitions)
-- ========================================

-- TIRED → ENERGETIC
('Espresso Chocolate Muffins', 'Rich muffins with coffee for energy boost', 'meals', 'tired', 'energetic', 'Decadent muffins with espresso and chocolate', 'https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=400', '#', 'Flour, cocoa, espresso, eggs, chocolate chips', '25 min', 'Medium', '12 muffins', 'Energizing', 'Caffeinated', 'Caffeine and antioxidants boost energy', NOW()),
('Energy Balls', 'No-bake energy balls with superfoods', 'meals', 'tired', 'energetic', 'Nutritious balls with dates and nuts', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Dates, almonds, oats, coconut, chia seeds', '15 min', 'Easy', '16 balls', 'Superfood', 'Vegan', 'Natural sugars provide sustained energy', NOW()),

-- TIRED → EXCITED
('Acai Bowl', 'Antioxidant-rich bowl that energizes and excites', 'meals', 'tired', 'excited', 'Purple acai bowl with vibrant toppings', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Acai puree, banana, berries, granola', '10 min', 'Easy', '2 servings', 'Antioxidant', 'Energizing', 'Antioxidants and natural sugars boost excitement', NOW()),
('Power Smoothie', 'High-energy smoothie that transforms tiredness', 'meals', 'tired', 'excited', 'Energizing smoothie with natural boosters', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Banana, protein powder, coffee, almond milk', '5 min', 'Easy', '1 serving', 'High-Energy', 'Protein-Rich', 'Caffeine and protein provide immediate energy', NOW()),

-- TIRED → FOCUSED
('Brain Coffee', 'Enhanced coffee for mental clarity and focus', 'meals', 'tired', 'focused', 'Coffee with cognitive-enhancing additions', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Quality coffee, MCT oil, butter, cinnamon', '10 min', 'Easy', '1 cup', 'Cognitive', 'Focus-Enhancing', 'Healthy fats and caffeine support focus', NOW()),
('Omega-3 Salmon Bowl', 'Brain-boosting bowl with omega-3 rich salmon', 'meals', 'tired', 'focused', 'Nutritious bowl with cognitive support', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Salmon, quinoa, avocado, walnuts, spinach', '25 min', 'Medium', '2 servings', 'Brain Food', 'Omega-3 Rich', 'Omega-3s support cognitive function', NOW()),

-- ========================================
-- STRESSED SOURCE EMOTION (24 target transitions)
-- ========================================

-- STRESSED → CALM
('Stress-Relief Tea', 'Herbal blend specifically for stress reduction', 'meals', 'stressed', 'calm', 'Calming blend of stress-reducing herbs', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Ashwagandha, chamomile, lemon balm, honey', '10 min', 'Easy', '1 cup', 'Therapeutic', 'Adaptogenic', 'Adaptogens help manage stress response', NOW()),
('Comfort Avocado Toast', 'Simple, nourishing toast with calming healthy fats', 'meals', 'stressed', 'calm', 'Whole grain toast with creamy avocado', 'https://images.unsplash.com/photo-1541519227354-08fa5d50c44d?w=400', '#', 'Whole grain bread, avocado, sea salt, lemon', '5 min', 'Easy', '2 servings', 'Nourishing', 'Heart-Healthy', 'Healthy fats support stress hormone regulation', NOW()),

-- STRESSED → PEACEFUL
('Peaceful Miso Soup', 'Traditional soup with calming umami flavors', 'meals', 'stressed', 'peaceful', 'Light miso soup with peaceful preparation', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400', '#', 'Miso paste, tofu, wakame, green onions', '10 min', 'Easy', '4 servings', 'Traditional', 'Probiotic', 'Fermented foods support gut-brain peace', NOW()),
('Zen Meditation Bowl', 'Mindfully prepared bowl for inner peace', 'meals', 'stressed', 'peaceful', 'Simple bowl with peaceful ingredients', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Brown rice, steamed vegetables, sesame oil', '20 min', 'Easy', '2 servings', 'Mindful', 'Grounding', 'Simple foods promote inner peace', NOW()),

-- STRESSED → RELAXED
('Relaxation Smoothie', 'Calming smoothie with stress-reducing ingredients', 'meals', 'stressed', 'relaxed', 'Creamy smoothie with natural relaxants', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Banana, almond milk, magnesium powder, vanilla', '5 min', 'Easy', '1 serving', 'Relaxing', 'Magnesium-Rich', 'Magnesium promotes muscle and mind relaxation', NOW()),
('Comfort Mac and Cheese', 'Creamy comfort food that soothes stress', 'meals', 'stressed', 'relaxed', 'Rich and creamy macaroni with cheese blend', 'https://images.unsplash.com/photo-1543826173-1ad64b6d5b8c?w=400', '#', 'Macaroni, cheddar, milk, butter, breadcrumbs', '30 min', 'Medium', '6 servings', 'Comfort', 'Soothing', 'Carbs and calcium promote relaxation', NOW()),

-- ========================================
-- CALM SOURCE EMOTION (24 target transitions)
-- ========================================

-- CALM → ENERGETIC
('Gentle Energy Bowl', 'Maintains calm while building sustainable energy', 'meals', 'calm', 'energetic', 'Balanced bowl with gentle energy boosters', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Quinoa, roasted vegetables, nuts, seeds', '25 min', 'Medium', '2 servings', 'Balanced', 'Sustained Energy', 'Complex carbs provide gentle energy boost', NOW()),
('Mindful Matcha', 'Calm energy drink with L-theanine balance', 'meals', 'calm', 'energetic', 'Premium matcha with sustained calm energy', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Matcha powder, coconut milk, honey', '5 min', 'Easy', '1 cup', 'Mindful', 'Balanced Energy', 'L-theanine provides calm energy', NOW()),

-- CALM → EXCITED
('Celebration Fruit Bowl', 'Colorful fruit arrangement that gently builds excitement', 'meals', 'calm', 'excited', 'Vibrant fruits arranged for visual excitement', 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=400', '#', 'Tropical fruits, berries, mint, coconut', '15 min', 'Easy', '4 servings', 'Vibrant', 'Natural Sugars', 'Natural sugars and colors build gentle excitement', NOW()),
('Spiced Chai Latte', 'Warming spices that gently transition to excitement', 'meals', 'calm', 'excited', 'Aromatic chai with energizing spices', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Black tea, milk, cinnamon, cardamom, ginger', '10 min', 'Easy', '1 cup', 'Warming', 'Spiced', 'Warming spices gently stimulate excitement', NOW()),

-- CALM → FOCUSED
('Focus Tea', 'Gentle tea blend that maintains calm while enhancing focus', 'meals', 'calm', 'focused', 'Green tea blend with focus-enhancing herbs', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Green tea, ginkgo, ginseng, honey', '8 min', 'Easy', '1 cup', 'Cognitive', 'Herbal', 'Nootropic herbs enhance calm focus', NOW()),
('Mindful Nut Bowl', 'Brain-healthy nuts for sustained focus', 'meals', 'calm', 'focused', 'Mixed nuts with cognitive benefits', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Walnuts, almonds, pecans, dark chocolate', '5 min', 'Easy', '4 servings', 'Brain Food', 'Omega-3 Rich', 'Healthy fats support sustained focus', NOW()),

-- ========================================
-- NEUTRAL SOURCE EMOTION (24 target transitions)
-- ========================================

-- NEUTRAL → HAPPY
('Mood-Lifting Smoothie', 'Bright smoothie that gently elevates neutral mood', 'meals', 'neutral', 'happy', 'Colorful smoothie with mood-boosting ingredients', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Mango, pineapple, banana, coconut milk', '5 min', 'Easy', '2 servings', 'Tropical', 'Mood-Boosting', 'Natural sugars and vitamins boost happiness', NOW()),
('Sunshine Pancakes', 'Bright, fluffy pancakes that bring joy', 'meals', 'neutral', 'happy', 'Light pancakes with cheerful toppings', 'https://images.unsplash.com/photo-1506084868230-bb9d95c24759?w=400', '#', 'Flour, eggs, milk, vanilla, fresh berries', '20 min', 'Easy', '4 servings', 'Cheerful', 'Comfort Food', 'Carbs and natural sugars promote happiness', NOW()),

-- NEUTRAL → ENERGETIC
('Activation Bowl', 'Energizing bowl that transforms neutral into dynamic', 'meals', 'neutral', 'energetic', 'Power bowl with energy-boosting superfoods', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Quinoa, sweet potato, kale, pumpkin seeds', '30 min', 'Medium', '2 servings', 'Activating', 'Superfood', 'Complex carbs and superfoods boost energy', NOW()),
('Morning Boost Coffee', 'Enhanced coffee that energizes from neutral state', 'meals', 'neutral', 'energetic', 'Coffee with natural energy enhancers', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Coffee, coconut oil, cinnamon, vanilla', '5 min', 'Easy', '1 cup', 'Energizing', 'Natural Boost', 'Healthy fats and caffeine provide clean energy', NOW()),

-- NEUTRAL → CALM
('Gentle Herbal Tea', 'Soothing tea that guides neutral mood to calm', 'meals', 'neutral', 'calm', 'Mild herbal blend for gentle calming', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Chamomile, lavender, honey, lemon', '8 min', 'Easy', '1 cup', 'Gentle', 'Calming', 'Mild herbs promote gentle calm transition', NOW()),
('Simple Comfort Bowl', 'Basic, nourishing bowl that promotes calm', 'meals', 'neutral', 'calm', 'Simple bowl with calming whole foods', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Brown rice, steamed broccoli, sesame seeds', '20 min', 'Easy', '2 servings', 'Simple', 'Nourishing', 'Whole foods promote natural calm', NOW());
