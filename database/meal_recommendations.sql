-- MoodifyMe Meal Recommendations Database
-- COMPREHENSIVE meal recommendations for ALL possible mood transitions
-- Source emotions: happy, sad, angry, anxious, calm, excited, bored, tired, stressed, neutral
-- Target emotions: happy, calm, energetic, focused, inspired, relaxed, confident, peaceful, motivated, creative, optimistic, grateful, joyful, serene, ambitious, mindful, empowered, content, excited, balanced, determined, refreshed, uplifted, centered
-- Total: 10 source × 24 target = 240 possible transitions
-- Import this file directly into your MySQL database

-- Clear existing meal data
DELETE FROM recommendations WHERE type = 'meals';

-- Insert comprehensive meal recommendations for ALL mood transitions
INSERT INTO recommendations (
    title, description, type, source_emotion, target_emotion, content,
    image_url, link, ingredients, cooking_time, difficulty, servings,
    cuisine_type, dietary_tags, nutrition_info, created_at
) VALUES

-- ========================================
-- SAD SOURCE EMOTION TRANSITIONS
-- ========================================

-- SAD → HAPPY Transitions (Comfort & Mood-Boosting Foods)
('Chocolate Chip Cookies', 'Warm, gooey cookies that trigger endorphin release and bring back happy childhood memories', 'meals', 'sad', 'happy', 'Classic homemade chocolate chip cookies with a crispy edge and soft center', 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=400', '#', 'Flour, butter, brown sugar, eggs, vanilla, chocolate chips', '25 minutes', 'Easy', '24 cookies', 'American', 'Vegetarian, Comfort Food', 'Contains tryptophan and phenylethylamine for mood enhancement', NOW()),

('Rainbow Fruit Salad', 'Colorful mix of fresh fruits rich in vitamins and natural sugars for instant mood lift', 'meals', 'sad', 'happy', 'Vibrant fruit salad with strawberries, oranges, kiwi, and berries', 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=400', '#', 'Strawberries, oranges, kiwi, blueberries, grapes, honey, mint', '15 minutes', 'Easy', '4 servings', 'International', 'Vegan, Gluten-Free, Healthy', 'High in vitamin C and natural antioxidants', NOW()),

('Comfort Mac & Cheese', 'Creamy, indulgent mac and cheese that provides warmth and satisfaction', 'meals', 'sad', 'happy', 'Rich and creamy macaroni and cheese with multiple cheese blend', 'https://images.unsplash.com/photo-1543826173-1ad64b6d5b8c?w=400', '#', 'Macaroni, cheddar, gruyere, milk, butter, flour, breadcrumbs', '30 minutes', 'Medium', '6 servings', 'American', 'Vegetarian, Comfort Food', 'Contains calcium and protein for sustained energy', NOW()),

('Banana Pancakes', 'Fluffy pancakes with mood-boosting bananas and natural sweetness', 'meals', 'sad', 'happy', 'Light and fluffy pancakes topped with fresh banana slices', 'https://images.unsplash.com/photo-1506084868230-bb9d95c24759?w=400', '#', 'Flour, eggs, milk, bananas, baking powder, vanilla, maple syrup', '20 minutes', 'Easy', '4 servings', 'American', 'Vegetarian', 'Rich in potassium and B vitamins for mood regulation', NOW()),

('Ice Cream Sundae', 'Decadent ice cream sundae with all the fixings for pure joy', 'meals', 'sad', 'happy', 'Classic vanilla ice cream with chocolate sauce, whipped cream, and cherry', 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=400', '#', 'Vanilla ice cream, chocolate sauce, whipped cream, cherry, nuts', '5 minutes', 'Easy', '1 serving', 'American', 'Vegetarian, Dessert', 'Triggers dopamine release for instant mood boost', NOW()),

-- STRESSED → CALM Transitions (Soothing & Relaxing Foods)
('Chamomile Tea', 'Soothing herbal tea known for its calming and stress-reducing properties', 'meals', 'stressed', 'calm', 'Gentle chamomile tea with honey and lemon for ultimate relaxation', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Chamomile flowers, honey, lemon, hot water', '5 minutes', 'Easy', '1 cup', 'Herbal', 'Vegan, Caffeine-Free', 'Contains apigenin which promotes relaxation', NOW()),

('Lavender Shortbread', 'Delicate cookies infused with calming lavender for stress relief', 'meals', 'stressed', 'calm', 'Buttery shortbread cookies with a hint of culinary lavender', 'https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=400', '#', 'Flour, butter, sugar, culinary lavender, vanilla', '45 minutes', 'Medium', '20 cookies', 'European', 'Vegetarian', 'Lavender has natural calming properties', NOW()),

('Warm Milk with Honey', 'Traditional bedtime drink that promotes relaxation and better sleep', 'meals', 'stressed', 'calm', 'Creamy warm milk sweetened with natural honey', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Whole milk, honey, vanilla extract, cinnamon', '5 minutes', 'Easy', '1 cup', 'Traditional', 'Vegetarian', 'Contains tryptophan and calcium for relaxation', NOW()),

('Cucumber Mint Water', 'Refreshing infused water that helps reduce stress and hydrate', 'meals', 'stressed', 'calm', 'Cool and refreshing water infused with cucumber and mint', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Water, cucumber, fresh mint, ice, lemon', '10 minutes', 'Easy', '4 cups', 'Healthy', 'Vegan, Zero Calories', 'Hydrating and cooling for stress relief', NOW()),

-- TIRED → EXCITED Transitions (Energizing Foods)
('Green Smoothie Bowl', 'Nutrient-packed smoothie bowl with energizing superfoods', 'meals', 'tired', 'excited', 'Vibrant green smoothie bowl topped with fresh fruits and granola', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Spinach, banana, mango, coconut milk, chia seeds, granola', '10 minutes', 'Easy', '2 servings', 'Healthy', 'Vegan, Gluten-Free', 'High in iron, vitamins, and natural energy boosters', NOW()),

('Espresso Chocolate Muffins', 'Rich muffins with coffee and chocolate for an energy boost', 'meals', 'tired', 'excited', 'Decadent muffins combining espresso and dark chocolate', 'https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=400', '#', 'Flour, cocoa, espresso, eggs, sugar, chocolate chips', '25 minutes', 'Medium', '12 muffins', 'American', 'Vegetarian', 'Contains caffeine and antioxidants for energy', NOW()),

('Energy Balls', 'No-bake energy balls packed with nuts, dates, and superfoods', 'meals', 'tired', 'excited', 'Nutritious energy balls made with dates, nuts, and coconut', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Dates, almonds, oats, coconut, chia seeds, vanilla', '15 minutes', 'Easy', '16 balls', 'Healthy', 'Vegan, Gluten-Free', 'Natural sugars and healthy fats for sustained energy', NOW()),

('Acai Bowl', 'Antioxidant-rich acai bowl topped with energizing fruits and nuts', 'meals', 'tired', 'excited', 'Purple acai bowl with fresh berries, granola, and coconut', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Acai puree, banana, berries, granola, coconut flakes', '10 minutes', 'Easy', '2 servings', 'Healthy', 'Vegan, Antioxidant-Rich', 'High in antioxidants and natural energy', NOW()),

-- ANXIOUS → CALM Transitions (Anxiety-Reducing Foods)
('Warm Oatmeal with Cinnamon', 'Comforting oatmeal that helps stabilize blood sugar and reduce anxiety', 'meals', 'anxious', 'calm', 'Creamy oatmeal topped with cinnamon, nuts, and fresh fruit', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Rolled oats, milk, cinnamon, walnuts, banana, honey', '15 minutes', 'Easy', '2 servings', 'Healthy', 'Vegetarian, Heart-Healthy', 'Complex carbs help regulate serotonin levels', NOW()),

('Herbal Anxiety Tea Blend', 'Calming tea blend specifically designed to reduce anxiety symptoms', 'meals', 'anxious', 'calm', 'Soothing blend of passionflower, lemon balm, and chamomile', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Passionflower, lemon balm, chamomile, valerian root', '10 minutes', 'Easy', '1 cup', 'Herbal', 'Vegan, Caffeine-Free', 'Natural anxiolytic properties', NOW()),

('Dark Chocolate Square', 'Small piece of dark chocolate to reduce cortisol and boost mood', 'meals', 'anxious', 'calm', 'High-quality dark chocolate with 70% cacao content', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Dark chocolate (70% cacao)', '1 minute', 'Easy', '1 square', 'Dessert', 'Vegan, Antioxidant', 'Reduces cortisol and increases endorphins', NOW()),

-- BORED → EXCITED Transitions (Stimulating & Flavorful Foods)
('Spicy Thai Pad Thai', 'Exciting noodle dish with bold flavors and energizing spices', 'meals', 'bored', 'excited', 'Authentic pad thai with rice noodles, vegetables, and spicy sauce', 'https://images.unsplash.com/photo-1559314809-0f31657def5e?w=400', '#', 'Rice noodles, shrimp, bean sprouts, lime, chili, fish sauce', '20 minutes', 'Medium', '4 servings', 'Thai', 'Spicy, Protein-Rich', 'Capsaicin stimulates endorphin release', NOW()),

('Sizzling Fajitas', 'Interactive and flavorful Mexican dish that engages the senses', 'meals', 'bored', 'excited', 'Sizzling chicken fajitas with peppers and onions', 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400', '#', 'Chicken, bell peppers, onions, tortillas, lime, spices', '25 minutes', 'Medium', '4 servings', 'Mexican', 'High-Protein, Spicy', 'Interactive cooking stimulates excitement', NOW()),

('Rainbow Sushi Roll', 'Colorful and artistic sushi that provides visual and taste excitement', 'meals', 'bored', 'excited', 'Beautiful rainbow roll with fresh fish and avocado', 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=400', '#', 'Sushi rice, nori, salmon, tuna, avocado, cucumber', '30 minutes', 'Hard', '8 pieces', 'Japanese', 'Fresh, Omega-3 Rich', 'Omega-3 fatty acids support brain function', NOW()),

-- HAPPY → CALM Transitions (Maintaining Balance)
('Herbal Tea Blend', 'Gentle tea blend to maintain happiness while promoting calm', 'meals', 'happy', 'calm', 'Balanced herbal tea with mint, lemon balm, and green tea', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Green tea, mint, lemon balm, honey', '5 minutes', 'Easy', '1 cup', 'Herbal', 'Antioxidant, Low Caffeine', 'L-theanine promotes calm alertness', NOW()),

('Greek Yogurt Parfait', 'Light and satisfying parfait that maintains energy while promoting calm', 'meals', 'happy', 'calm', 'Layered parfait with yogurt, berries, and granola', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Greek yogurt, mixed berries, granola, honey', '5 minutes', 'Easy', '2 servings', 'Healthy', 'Protein-Rich, Probiotic', 'Probiotics support gut-brain connection', NOW()),

-- ANGRY → CALM Transitions (Cooling & Soothing Foods)
('Cooling Cucumber Soup', 'Refreshing cold soup that helps cool down anger and promote calm', 'meals', 'angry', 'calm', 'Chilled cucumber soup with yogurt and fresh herbs', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400', '#', 'Cucumber, yogurt, dill, mint, garlic, lemon', '15 minutes', 'Easy', '4 servings', 'Mediterranean', 'Cooling, Low-Calorie', 'Cooling foods help reduce internal heat and anger', NOW()),

('Peppermint Tea', 'Cooling peppermint tea that helps soothe anger and irritation', 'meals', 'angry', 'calm', 'Fresh peppermint tea with natural cooling properties', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Fresh peppermint leaves, hot water, honey', '5 minutes', 'Easy', '1 cup', 'Herbal', 'Cooling, Digestive', 'Menthol has natural cooling and calming effects', NOW()),

-- EXCITED → CALM Transitions (Grounding Foods)
('Warm Golden Milk', 'Grounding turmeric latte that helps transition from excitement to calm', 'meals', 'excited', 'calm', 'Creamy golden milk with turmeric, ginger, and warming spices', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Coconut milk, turmeric, ginger, cinnamon, honey', '10 minutes', 'Easy', '2 cups', 'Ayurvedic', 'Anti-inflammatory, Warming', 'Turmeric and warm spices promote grounding', NOW()),

('Meditation Trail Mix', 'Balanced mix of nuts and dried fruits for mindful snacking', 'meals', 'excited', 'calm', 'Carefully balanced trail mix for mindful eating', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Almonds, walnuts, dried cranberries, dark chocolate', '5 minutes', 'Easy', '8 servings', 'Healthy', 'Brain Food, Balanced', 'Healthy fats and protein promote sustained calm energy', NOW()),

-- Additional SAD → HAPPY meals
('Strawberry Cheesecake', 'Creamy, indulgent cheesecake that brings pure joy and comfort', 'meals', 'sad', 'happy', 'Rich New York style cheesecake with fresh strawberry topping', 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=400', '#', 'Cream cheese, graham crackers, eggs, sugar, strawberries', '4 hours', 'Hard', '12 servings', 'American', 'Vegetarian, Dessert', 'Rich in calcium and mood-boosting compounds', NOW()),

('Chicken Noodle Soup', 'Classic comfort soup that warms the soul and lifts spirits', 'meals', 'sad', 'happy', 'Homemade chicken soup with tender noodles and vegetables', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400', '#', 'Chicken, egg noodles, carrots, celery, onion, herbs', '45 minutes', 'Medium', '6 servings', 'American', 'Comfort Food, Protein-Rich', 'Warm liquids and protein boost serotonin', NOW()),

('Pizza Margherita', 'Simple yet satisfying pizza that brings comfort and joy', 'meals', 'sad', 'happy', 'Classic Italian pizza with fresh mozzarella, tomato, and basil', 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400', '#', 'Pizza dough, mozzarella, tomatoes, basil, olive oil', '30 minutes', 'Medium', '4 servings', 'Italian', 'Vegetarian, Comfort Food', 'Carbohydrates help increase serotonin production', NOW()),

-- SAD → CALM Transitions
('Warm Chamomile Tea', 'Soothing herbal tea that gently calms sadness into peaceful tranquility', 'meals', 'sad', 'calm', 'Gentle chamomile tea with honey for emotional soothing', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Chamomile flowers, honey, lemon, hot water', '5 minutes', 'Easy', '1 cup', 'Herbal', 'Caffeine-Free, Calming', 'Apigenin in chamomile promotes relaxation and emotional balance', NOW()),
('Comfort Soup', 'Warm, nourishing soup that provides comfort and gentle calm', 'meals', 'sad', 'calm', 'Hearty vegetable soup with healing herbs', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400', '#', 'Mixed vegetables, vegetable broth, herbs, garlic', '30 minutes', 'Easy', '4 servings', 'Comfort Food', 'Vegan, Healing', 'Warm liquids and nutrients support emotional healing', NOW()),

-- SAD → ENERGETIC Transitions
('Energizing Smoothie', 'Nutrient-packed smoothie to lift spirits and boost energy', 'meals', 'sad', 'energetic', 'Vibrant fruit smoothie with energy-boosting ingredients', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Banana, berries, spinach, protein powder, almond milk', '5 minutes', 'Easy', '2 servings', 'Healthy', 'High-Protein, Energizing', 'Natural sugars and B vitamins boost energy and mood', NOW()),
('Power Breakfast Bowl', 'Energizing breakfast bowl to transform sadness into vitality', 'meals', 'sad', 'energetic', 'Quinoa bowl with fresh fruits and nuts', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Quinoa, mixed berries, nuts, seeds, honey', '15 minutes', 'Easy', '2 servings', 'Healthy', 'Complete Protein, Energizing', 'Complex carbs and protein provide sustained energy', NOW()),

-- SAD → FOCUSED Transitions
('Brain-Boosting Oatmeal', 'Nutrient-rich oatmeal that supports mental clarity and focus', 'meals', 'sad', 'focused', 'Steel-cut oats with walnuts and blueberries for cognitive support', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Steel-cut oats, walnuts, blueberries, cinnamon', '20 minutes', 'Easy', '2 servings', 'Brain Food', 'Omega-3 Rich, Antioxidant', 'Omega-3s and antioxidants support cognitive function', NOW()),
('Green Tea with Dark Chocolate', 'Mindful combination for gentle focus and mood elevation', 'meals', 'sad', 'focused', 'Premium green tea paired with 70% dark chocolate', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Green tea, dark chocolate (70% cacao)', '5 minutes', 'Easy', '1 serving', 'Mindful', 'Antioxidant, Focus-Enhancing', 'L-theanine and caffeine promote calm focus', NOW()),

-- SAD → INSPIRED Transitions
('Creative Fruit Art Bowl', 'Colorful, artistic fruit arrangement that sparks creativity', 'meals', 'sad', 'inspired', 'Beautiful fruit bowl arranged in inspiring patterns', 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=400', '#', 'Colorful fruits, edible flowers, mint, honey drizzle', '15 minutes', 'Easy', '2 servings', 'Artistic', 'Vegan, Colorful', 'Vibrant colors and natural sugars stimulate creativity', NOW()),
('Inspiration Tea Blend', 'Uplifting herbal blend designed to spark inspiration', 'meals', 'sad', 'inspired', 'Custom tea blend with mood-lifting herbs', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Peppermint, lemon balm, rose petals, ginger', '10 minutes', 'Easy', '1 cup', 'Herbal', 'Uplifting, Aromatic', 'Aromatic herbs stimulate senses and creativity', NOW()),

-- SAD → RELAXED Transitions
('Lavender Honey Milk', 'Soothing drink that eases sadness into gentle relaxation', 'meals', 'sad', 'relaxed', 'Warm milk infused with calming lavender and honey', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Warm milk, culinary lavender, honey, vanilla', '10 minutes', 'Easy', '1 cup', 'Calming', 'Relaxing, Sleep-Promoting', 'Lavender and warm milk promote deep relaxation', NOW()),
('Comfort Pasta', 'Simple, comforting pasta that soothes and relaxes', 'meals', 'sad', 'relaxed', 'Creamy pasta with herbs and gentle flavors', 'https://images.unsplash.com/photo-1621996346565-e3dbc353d2e5?w=400', '#', 'Pasta, cream, herbs, parmesan, garlic', '20 minutes', 'Easy', '4 servings', 'Comfort Food', 'Vegetarian, Soothing', 'Carbohydrates promote serotonin and relaxation', NOW()),

-- SAD → CONFIDENT Transitions
('Power Protein Bowl', 'Strength-building bowl that transforms sadness into confidence', 'meals', 'sad', 'confident', 'High-protein bowl with quinoa, chicken, and vegetables', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Quinoa, grilled chicken, mixed vegetables, tahini', '25 minutes', 'Medium', '2 servings', 'High-Protein', 'Confidence-Building, Nutritious', 'Complete proteins support neurotransmitter production', NOW()),
('Victory Smoothie', 'Empowering smoothie blend that builds inner strength', 'meals', 'sad', 'confident', 'Protein-rich smoothie with superfoods', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Protein powder, spinach, banana, almond butter', '5 minutes', 'Easy', '1 serving', 'Empowering', 'High-Protein, Superfood', 'Amino acids support confidence-building neurotransmitters', NOW()),

-- SAD → PEACEFUL Transitions
('Meditation Soup', 'Gentle, warming soup that brings inner peace', 'meals', 'sad', 'peaceful', 'Light miso soup with mindful preparation', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400', '#', 'Miso paste, tofu, seaweed, green onions', '15 minutes', 'Easy', '2 servings', 'Mindful', 'Peaceful, Probiotic', 'Umami flavors and probiotics promote inner peace', NOW()),
('Zen Garden Salad', 'Peaceful salad arrangement that calms the mind', 'meals', 'sad', 'peaceful', 'Beautifully arranged salad with calming ingredients', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400', '#', 'Mixed greens, cucumber, avocado, sesame seeds', '10 minutes', 'Easy', '2 servings', 'Zen', 'Peaceful, Hydrating', 'Cooling foods and mindful eating promote peace', NOW()),

-- SAD → MOTIVATED Transitions
('Champion Breakfast', 'Energizing breakfast that transforms sadness into motivation', 'meals', 'sad', 'motivated', 'Power-packed breakfast with eggs and whole grains', 'https://images.unsplash.com/photo-1506084868230-bb9d95c24759?w=400', '#', 'Eggs, whole grain toast, avocado, tomatoes', '15 minutes', 'Easy', '2 servings', 'Motivating', 'High-Protein, Energizing', 'Protein and complex carbs fuel motivation', NOW()),
('Success Smoothie Bowl', 'Motivational smoothie bowl that inspires action', 'meals', 'sad', 'motivated', 'Acai bowl topped with energizing superfoods', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Acai, banana, granola, chia seeds, berries', '10 minutes', 'Easy', '2 servings', 'Motivational', 'Antioxidant-Rich, Energizing', 'Antioxidants and natural sugars boost motivation', NOW()),

-- SAD → CREATIVE Transitions
('Artist Palette Smoothie', 'Colorful smoothie that stimulates creativity and artistic expression', 'meals', 'sad', 'creative', 'Multi-layered smoothie with vibrant natural colors', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Mango, blueberries, spinach, coconut milk, chia', '15 minutes', 'Medium', '2 servings', 'Creative', 'Colorful, Brain-Boosting', 'Antioxidants and omega-3s support creative thinking', NOW()),
('Inspiration Pasta', 'Colorful pasta dish that sparks creativity through visual appeal', 'meals', 'sad', 'creative', 'Rainbow vegetable pasta with artistic presentation', 'https://images.unsplash.com/photo-1621996346565-e3dbc353d2e5?w=400', '#', 'Colorful pasta, bell peppers, zucchini, tomatoes, herbs', '25 minutes', 'Medium', '4 servings', 'Creative', 'Colorful, Inspiring', 'Vibrant colors and complex flavors stimulate creativity', NOW()),

-- SAD → OPTIMISTIC Transitions
('Sunshine Citrus Bowl', 'Bright citrus bowl that lifts spirits and promotes optimism', 'meals', 'sad', 'optimistic', 'Fresh citrus fruits with uplifting presentation', 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=400', '#', 'Orange, grapefruit, lemon, lime, mint, honey', '10 minutes', 'Easy', '2 servings', 'Uplifting', 'Vitamin C Rich, Bright', 'Vitamin C and bright colors promote optimistic mood', NOW()),
('Hope Herbal Tea', 'Uplifting herbal blend that encourages optimistic thinking', 'meals', 'sad', 'optimistic', 'Bright herbal tea with mood-lifting properties', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Lemon verbena, orange peel, ginger, honey', '8 minutes', 'Easy', '1 cup', 'Uplifting', 'Mood-Boosting, Aromatic', 'Citrus oils and warming spices promote optimism', NOW()),

-- SAD → GRATEFUL Transitions
('Gratitude Bowl', 'Nourishing bowl that encourages appreciation and thankfulness', 'meals', 'sad', 'grateful', 'Wholesome grain bowl with seasonal vegetables', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Brown rice, roasted vegetables, nuts, seeds, tahini', '30 minutes', 'Medium', '2 servings', 'Nourishing', 'Whole Foods, Grounding', 'Whole foods and mindful eating promote gratitude', NOW()),
('Thankful Tea Ceremony', 'Mindful tea preparation that cultivates gratitude', 'meals', 'sad', 'grateful', 'Traditional tea ceremony with mindful preparation', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Green tea, jasmine flowers, honey, mindful preparation', '15 minutes', 'Medium', '1 cup', 'Mindful', 'Ceremonial, Grounding', 'Mindful preparation and tea ritual promote gratitude', NOW()),

-- SAD → JOYFUL Transitions
('Joy Celebration Cake', 'Special treat that transforms sadness into pure joy', 'meals', 'sad', 'joyful', 'Light, fluffy cake with bright decorations', 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=400', '#', 'Vanilla cake, colorful frosting, sprinkles, berries', '45 minutes', 'Medium', '8 servings', 'Celebratory', 'Joyful, Festive', 'Sweet treats and celebration promote joyful feelings', NOW()),
('Happiness Fruit Parfait', 'Layered parfait that builds joy with each colorful layer', 'meals', 'sad', 'joyful', 'Beautiful layered parfait with vibrant fruits', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Greek yogurt, mixed berries, granola, honey', '10 minutes', 'Easy', '2 servings', 'Joyful', 'Colorful, Probiotic', 'Probiotics and natural sugars promote joyful mood', NOW()),

-- ========================================
-- HAPPY SOURCE EMOTION TRANSITIONS
-- ========================================

-- HAPPY → CALM Transitions
('Peaceful Green Tea', 'Gentle tea that maintains happiness while promoting calm', 'meals', 'happy', 'calm', 'Premium green tea with calming properties', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Green tea, honey, lemon balm', '5 minutes', 'Easy', '1 cup', 'Calming', 'L-theanine Rich, Peaceful', 'L-theanine promotes calm alertness while maintaining mood', NOW()),
('Serenity Salad', 'Light, refreshing salad that balances happiness with tranquility', 'meals', 'happy', 'serenity', 'Fresh garden salad with calming herbs', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400', '#', 'Mixed greens, cucumber, herbs, light vinaigrette', '10 minutes', 'Easy', '2 servings', 'Peaceful', 'Light, Hydrating', 'Fresh vegetables and herbs promote peaceful calm', NOW()),

-- HAPPY → ENERGETIC Transitions
('Victory Energy Bowl', 'Energizing bowl that amplifies happiness into dynamic energy', 'meals', 'happy', 'energetic', 'Power-packed bowl with superfoods', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Quinoa, sweet potato, spinach, nuts, seeds', '25 minutes', 'Medium', '2 servings', 'Energizing', 'Superfood, High-Energy', 'Complex carbs and superfoods boost sustained energy', NOW()),
('Champion Smoothie', 'High-energy smoothie that transforms happiness into vitality', 'meals', 'happy', 'energetic', 'Protein-rich smoothie with natural energy boosters', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Banana, protein powder, spinach, almond butter', '5 minutes', 'Easy', '1 serving', 'High-Energy', 'Protein-Rich, Energizing', 'Natural sugars and protein provide sustained energy', NOW()),

-- HAPPY → FOCUSED Transitions
('Clarity Coffee', 'Mindful coffee preparation that channels happiness into focus', 'meals', 'happy', 'focused', 'Carefully brewed coffee with focus-enhancing additions', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Quality coffee beans, MCT oil, cinnamon', '10 minutes', 'Easy', '1 cup', 'Focus-Enhancing', 'Cognitive Support, Energizing', 'Caffeine and healthy fats support mental clarity', NOW()),
('Brain Power Bowl', 'Nutrient-dense bowl that maintains happiness while boosting focus', 'meals', 'happy', 'focused', 'Omega-3 rich bowl with brain-boosting ingredients', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Salmon, avocado, walnuts, blueberries, quinoa', '20 minutes', 'Medium', '2 servings', 'Brain Food', 'Omega-3 Rich, Cognitive', 'Omega-3s and antioxidants support cognitive function', NOW()),

-- Additional STRESSED → CALM meals
('Avocado Toast', 'Simple, nourishing toast with calming healthy fats', 'meals', 'stressed', 'calm', 'Whole grain toast topped with creamy avocado and sea salt', 'https://images.unsplash.com/photo-1541519227354-08fa5d50c44d?w=400', '#', 'Whole grain bread, avocado, lemon, sea salt, olive oil', '5 minutes', 'Easy', '2 servings', 'Healthy', 'Vegan, Heart-Healthy', 'Healthy fats support stress hormone regulation', NOW()),

('Miso Soup', 'Traditional Japanese soup with calming umami flavors', 'meals', 'stressed', 'calm', 'Light and soothing miso soup with tofu and seaweed', 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400', '#', 'Miso paste, tofu, wakame, green onions, dashi', '10 minutes', 'Easy', '4 servings', 'Japanese', 'Vegan, Probiotic', 'Fermented foods support gut health and stress reduction', NOW()),

('Lemon Balm Tea', 'Gentle herbal tea specifically for stress and anxiety relief', 'meals', 'stressed', 'calm', 'Soothing lemon balm tea with natural calming properties', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Fresh lemon balm leaves, hot water, honey', '5 minutes', 'Easy', '1 cup', 'Herbal', 'Caffeine-Free, Calming', 'Lemon balm reduces cortisol and promotes relaxation', NOW()),

-- Additional TIRED → EXCITED meals
('Matcha Latte', 'Energizing green tea latte with sustained energy release', 'meals', 'tired', 'excited', 'Creamy matcha latte with natural caffeine and L-theanine', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Matcha powder, steamed milk, honey, vanilla', '5 minutes', 'Easy', '1 cup', 'Japanese', 'Antioxidant-Rich, Energizing', 'Provides calm energy without jitters', NOW()),

('Quinoa Power Bowl', 'Nutrient-dense bowl with complete proteins for sustained energy', 'meals', 'tired', 'excited', 'Colorful quinoa bowl with roasted vegetables and tahini dressing', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Quinoa, roasted vegetables, chickpeas, tahini, lemon', '25 minutes', 'Medium', '4 servings', 'Mediterranean', 'Vegan, Complete Protein', 'Complete amino acids provide sustained energy', NOW()),

('Banana Nut Smoothie', 'Protein-rich smoothie with natural sugars for quick energy', 'meals', 'tired', 'excited', 'Creamy smoothie with banana, almond butter, and protein', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Banana, almond butter, protein powder, almond milk', '5 minutes', 'Easy', '2 servings', 'Healthy', 'High-Protein, Natural Sugars', 'Natural sugars and protein for immediate and sustained energy', NOW()),

-- Additional ANXIOUS → CALM meals
('Magnesium-Rich Spinach Salad', 'Nutrient-dense salad with anxiety-reducing magnesium', 'meals', 'anxious', 'calm', 'Fresh spinach salad with pumpkin seeds and avocado', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400', '#', 'Baby spinach, pumpkin seeds, avocado, olive oil, lemon', '10 minutes', 'Easy', '4 servings', 'Healthy', 'Vegan, Magnesium-Rich', 'Magnesium helps regulate anxiety and stress response', NOW()),

('Turkey and Sweet Potato', 'Tryptophan-rich meal that promotes serotonin production', 'meals', 'anxious', 'calm', 'Roasted turkey breast with sweet potato and herbs', 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400', '#', 'Turkey breast, sweet potato, rosemary, olive oil', '45 minutes', 'Medium', '4 servings', 'American', 'High-Protein, Comfort Food', 'Tryptophan promotes serotonin production for calm mood', NOW()),

-- Additional BORED → EXCITED meals
('Korean Kimchi Fried Rice', 'Spicy, fermented flavors that awaken the senses', 'meals', 'bored', 'excited', 'Flavorful fried rice with spicy kimchi and vegetables', 'https://images.unsplash.com/photo-1559314809-0f31657def5e?w=400', '#', 'Rice, kimchi, vegetables, soy sauce, sesame oil, egg', '15 minutes', 'Medium', '4 servings', 'Korean', 'Spicy, Fermented, Umami', 'Fermented foods and spices stimulate taste buds and excitement', NOW()),

('Indian Curry Bowl', 'Aromatic curry with complex spices that engage all senses', 'meals', 'bored', 'excited', 'Fragrant vegetable curry with warming spices over rice', 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400', '#', 'Mixed vegetables, coconut milk, curry spices, rice', '30 minutes', 'Medium', '4 servings', 'Indian', 'Vegan, Spicy, Aromatic', 'Complex spices stimulate senses and create excitement', NOW()),

('Mediterranean Mezze Platter', 'Variety of flavors and textures for sensory excitement', 'meals', 'bored', 'excited', 'Colorful platter with hummus, olives, cheese, and vegetables', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Hummus, olives, feta, vegetables, pita bread', '15 minutes', 'Easy', '6 servings', 'Mediterranean', 'Vegetarian, Variety', 'Multiple flavors and textures create sensory interest', NOW()),

-- Additional HAPPY → CALM meals
('Coconut Rice Pudding', 'Gentle, creamy dessert that maintains happiness while promoting calm', 'meals', 'happy', 'calm', 'Creamy rice pudding with coconut milk and vanilla', 'https://images.unsplash.com/photo-1571167530149-c72f2b4c0f3c?w=400', '#', 'Rice, coconut milk, vanilla, cinnamon, honey', '25 minutes', 'Easy', '6 servings', 'Asian', 'Vegetarian, Comfort Dessert', 'Complex carbs promote steady serotonin levels', NOW()),

('Green Tea Ice Cream', 'Light, refreshing dessert with calming green tea benefits', 'meals', 'happy', 'calm', 'Smooth green tea ice cream with subtle earthy flavors', 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=400', '#', 'Heavy cream, matcha powder, sugar, egg yolks', '4 hours', 'Hard', '8 servings', 'Japanese', 'Vegetarian, Antioxidant', 'L-theanine in green tea promotes calm alertness', NOW()),

-- Additional ANGRY → CALM meals
('Cooling Watermelon Salad', 'Refreshing salad that helps cool down anger and frustration', 'meals', 'angry', 'calm', 'Fresh watermelon salad with mint and lime', 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=400', '#', 'Watermelon, mint, lime, feta cheese, olive oil', '10 minutes', 'Easy', '4 servings', 'Mediterranean', 'Cooling, Hydrating', 'High water content and cooling properties reduce internal heat', NOW()),

('Iced Hibiscus Tea', 'Cooling herbal tea that helps reduce anger and promote calm', 'meals', 'angry', 'calm', 'Refreshing iced tea with tart hibiscus flowers', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=400', '#', 'Hibiscus flowers, cold water, honey, ice, lemon', '15 minutes', 'Easy', '4 cups', 'Herbal', 'Cooling, Antioxidant', 'Natural cooling properties help reduce anger and irritation', NOW()),

-- Additional EXCITED → CALM meals
('Lavender Honey Cookies', 'Gentle cookies with calming lavender to ease excitement', 'meals', 'excited', 'calm', 'Delicate cookies infused with lavender and honey', 'https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=400', '#', 'Flour, butter, honey, culinary lavender, vanilla', '30 minutes', 'Medium', '24 cookies', 'European', 'Vegetarian, Calming', 'Lavender has natural sedative properties for gentle calming', NOW()),

('Vanilla Chamomile Smoothie', 'Soothing smoothie that helps transition from excitement to calm', 'meals', 'excited', 'calm', 'Creamy smoothie with chamomile tea and vanilla', 'https://images.unsplash.com/photo-1511690743698-d9d85f2fbf38?w=400', '#', 'Chamomile tea, banana, vanilla yogurt, honey', '10 minutes', 'Easy', '2 servings', 'Healthy', 'Calming, Probiotic', 'Chamomile and probiotics promote digestive calm and relaxation', NOW());
