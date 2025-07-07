-- MoodifyMe African Meals Seed Data
-- This file contains comprehensive African meal recommendations for different mood transitions

USE modifyMe1;

-- African Meals for Sad to Happy transitions
INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, created_at)
VALUES 
('Jollof Rice - West African Comfort', 'A vibrant, flavorful one-pot rice dish that brings warmth and joy. The aromatic spices and colorful presentation can help lift your spirits.', 'african_meals', 'sad', 'happy', 'Ingredients: 2 cups jasmine rice, 1 can tomato paste, 1 onion, 3 cloves garlic, 1 tsp curry powder, 1 tsp thyme, 2 bay leaves, 3 cups chicken stock, salt and pepper. Instructions: Sauté onions and garlic, add tomato paste and spices, add rice and stock, simmer for 20-25 minutes until rice is tender.', 'assets/images/meals/jollof_rice.jpg', '', NOW()),

('Injera with Doro Wat - Ethiopian Soul Food', 'This traditional Ethiopian dish combines spongy sourdough flatbread with a rich, spicy chicken stew. The communal eating style promotes connection and comfort.', 'african_meals', 'sad', 'happy', 'Doro Wat ingredients: 2 lbs chicken, 2 onions, 4 hard-boiled eggs, 2 tbsp berbere spice, 1 tbsp ginger, 4 cloves garlic, 1/4 cup oil. Simmer chicken with spices for 45 minutes. Serve with injera bread for a warming, communal meal.', 'assets/images/meals/doro_wat.jpg', '', NOW()),

('Bobotie - South African Comfort Casserole', 'A sweet and savory baked dish with aromatic spices that creates a sense of home and comfort. The golden custard topping is particularly mood-lifting.', 'african_meals', 'sad', 'happy', 'Ingredients: 1 lb ground beef, 2 onions, 2 slices bread, 1 cup milk, 2 eggs, 2 tbsp curry powder, 1 tbsp turmeric, 2 bay leaves, raisins, almonds. Bake at 350°F for 45 minutes until golden.', 'assets/images/meals/bobotie.jpg', '', NOW());

-- African Meals for Angry to Calm transitions
INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, created_at)
VALUES 
('Moroccan Mint Tea with Dates', 'The ritual of preparing and sipping this soothing tea helps calm the mind. The natural sweetness of dates provides gentle energy while mint relaxes.', 'african_meals', 'angry', 'calm', 'Ingredients: 2 tbsp green tea, fresh mint leaves, 3 tbsp sugar, 4 cups water, fresh dates. Steep tea for 3 minutes, add mint and sugar, serve in small glasses with dates on the side. The preparation ritual is as calming as the drink itself.', 'assets/images/meals/mint_tea.jpg', '', NOW()),

('Ugali with Sukuma Wiki - Kenyan Simplicity', 'This simple, grounding meal of cornmeal porridge with collard greens promotes mindfulness and calm through its humble, nourishing nature.', 'african_meals', 'angry', 'calm', 'Ugali: 2 cups white cornmeal, 3 cups water, salt. Sukuma Wiki: 1 bunch collard greens, 2 onions, 3 tomatoes, garlic, oil. The simple preparation and earthy flavors help center the mind and body.', 'assets/images/meals/ugali_sukuma.jpg', '', NOW()),

('Harira Soup - Moroccan Healing Bowl', 'This hearty lentil and tomato soup is traditionally used to break fasts. Its warm, comforting nature helps soothe anger and restore balance.', 'african_meals', 'angry', 'calm', 'Ingredients: 1 cup lentils, 1 can diced tomatoes, 1 onion, celery, cilantro, parsley, 1 tsp cinnamon, 1 tsp ginger, 6 cups broth. Simmer for 30 minutes. The warm spices and hearty texture provide deep comfort.', 'assets/images/meals/harira_soup.jpg', '', NOW());

-- African Meals for Anxious to Calm transitions
INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, created_at)
VALUES 
('Chamomile Rooibos Tea - South African Calm', 'This caffeine-free herbal tea from South Africa is naturally calming and helps reduce anxiety. The earthy, vanilla notes are deeply soothing.', 'african_meals', 'anxious', 'calm', 'Ingredients: 2 tbsp rooibos tea, 1 tsp dried chamomile, honey to taste, 2 cups hot water. Steep for 5-7 minutes. The natural minerals and lack of caffeine make this perfect for anxiety relief.', 'assets/images/meals/rooibos_tea.jpg', '', NOW()),

('Thieboudienne - Senegalese Comfort Rice', 'This national dish of Senegal combines fish, rice, and vegetables in a one-pot meal that promotes mindfulness through its careful preparation and balanced flavors.', 'african_meals', 'anxious', 'calm', 'Ingredients: 2 lbs fish fillets, 2 cups rice, mixed vegetables (carrots, cabbage, eggplant), tomato paste, onions, garlic, scotch bonnet pepper. The slow cooking process and aromatic spices create a meditative cooking experience.', 'assets/images/meals/thieboudienne.jpg', '', NOW());

-- African Meals for Tired to Energetic transitions
INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, created_at)
VALUES 
('Suya Spice Grilled Meat - Nigerian Energy', 'This protein-rich, spicy grilled meat provides sustained energy. The bold peanut-based spice blend awakens the senses and boosts vitality.', 'african_meals', 'tired', 'energetic', 'Suya spice mix: ground peanuts, cayenne pepper, ginger, garlic powder, onion powder, bouillon cube. Marinate beef or chicken strips, grill until charred. The protein and spices provide natural energy boost.', 'assets/images/meals/suya.jpg', '', NOW()),

('Ethiopian Coffee Ceremony with Popcorn', 'The traditional coffee ceremony is energizing both physically and spiritually. Fresh roasted coffee beans provide natural caffeine while the ritual awakens the mind.', 'african_meals', 'tired', 'energetic', 'Green coffee beans, frankincense, popcorn. Roast beans over charcoal, grind by hand, brew in traditional pot. The ceremony takes time but the result is incredibly energizing and the ritual itself is invigorating.', 'assets/images/meals/coffee_ceremony.jpg', '', NOW()),

('Mandazi - East African Energy Bites', 'These lightly sweetened fried doughnuts provide quick energy and the cardamom and coconut flavors are naturally uplifting and energizing.', 'african_meals', 'tired', 'energetic', 'Ingredients: 2 cups flour, 1/2 cup coconut milk, 1/4 cup sugar, 1 tsp cardamom, 1 tsp baking powder, oil for frying. Mix ingredients, roll out, cut into triangles, fry until golden. Perfect with tea for an energy boost.', 'assets/images/meals/mandazi.jpg', '', NOW());

-- African Meals for Stressed to Relaxed transitions
INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, created_at)
VALUES 
('Tagine - Moroccan Slow-Cooked Meditation', 'The slow cooking process of tagine is meditative, and the aromatic spices help reduce stress. The communal serving style promotes relaxation and connection.', 'african_meals', 'stressed', 'relaxed', 'Chicken tagine: 2 lbs chicken, preserved lemons, olives, onions, ginger, saffron, cinnamon, 2 cups broth. Layer ingredients in tagine pot, cook slowly for 1.5 hours. The slow cooking process is therapeutic and stress-relieving.', 'assets/images/meals/tagine.jpg', '', NOW()),

('Biltong and Rooibos - South African Zen', 'This simple combination of dried meat and herbal tea provides protein for stability and calming herbs for relaxation. Perfect for stress relief.', 'african_meals', 'stressed', 'relaxed', 'Traditional biltong (air-dried meat) paired with rooibos tea. The protein helps stabilize blood sugar while rooibos naturally reduces cortisol levels. Simple preparation allows for mindful eating.', 'assets/images/meals/biltong_rooibos.jpg', '', NOW());

-- African Meals for Bored to Excited transitions
INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, created_at)
VALUES 
('Bunny Chow - South African Street Food Adventure', 'This unique dish of curry served in a hollowed-out bread loaf is exciting to eat and full of bold flavors that awaken the senses.', 'african_meals', 'bored', 'excited', 'Ingredients: 1 unsliced white bread loaf, 2 lbs lamb or chicken, curry powder, garam masala, tomatoes, onions, potatoes. Make a rich curry, hollow out bread, fill with curry. The interactive eating style makes it exciting and fun.', 'assets/images/meals/bunny_chow.jpg', '', NOW()),

('Kelewele - Ghanaian Spiced Plantains', 'These spicy, caramelized plantain cubes are bursting with flavor and provide an exciting taste adventure with their perfect balance of sweet and heat.', 'african_meals', 'bored', 'excited', 'Ingredients: 4 ripe plantains, 2 tsp ginger, 1 tsp cayenne, 1 tsp nutmeg, salt, oil for frying. Cube plantains, toss with spices, fry until caramelized. The bold flavors and crispy texture create excitement with every bite.', 'assets/images/meals/kelewele.jpg', '', NOW());

-- African Meals for Happy to Happy (maintaining happiness)
INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, created_at)
VALUES 
('Celebration Jollof with Fried Plantains', 'The ultimate West African celebration meal. The vibrant colors and festive presentation help maintain and amplify joyful feelings.', 'african_meals', 'happy', 'happy', 'Party jollof: 3 cups rice, rich tomato base, mixed vegetables, fried plantains on the side. Cook rice with extra stock for fluffy texture, garnish with fresh herbs. The festive presentation maintains celebratory mood.', 'assets/images/meals/party_jollof.jpg', '', NOW()),

('Malva Pudding - South African Joy Dessert', 'This sweet, sticky dessert with custard is pure comfort and joy. The warm, caramelized flavors are perfect for maintaining happy feelings.', 'african_meals', 'happy', 'happy', 'Ingredients: 1 cup flour, 1 cup sugar, 1 egg, 1 tbsp apricot jam, 1 tsp baking soda, 1 cup milk, butter. Bake sponge, pour over hot cream sauce. The indulgent sweetness amplifies happiness and celebration.', 'assets/images/meals/malva_pudding.jpg', '', NOW());

-- African Meals for Neutral to Happy transitions
INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, created_at)
VALUES 
('Peri-Peri Chicken - Mozambican Fire', 'This spicy, flavorful grilled chicken with African bird\'s eye chili sauce awakens the senses and brings excitement to any neutral mood.', 'african_meals', 'neutral', 'happy', 'Peri-peri sauce: African bird\'s eye chilies, garlic, lemon juice, paprika, oregano, oil. Marinate chicken for 4 hours, grill until charred. The bold flavors and heat create instant mood elevation.', 'assets/images/meals/peri_peri_chicken.jpg', '', NOW()),

('Koeksisters - South African Sweet Spirals', 'These twisted, syrup-soaked pastries are pure joy to make and eat. The sweet, sticky texture and beautiful spiral shape naturally lift spirits.', 'african_meals', 'neutral', 'happy', 'Dough: flour, butter, eggs, milk. Syrup: sugar, water, lemon juice, ginger. Braid dough into spirals, fry until golden, dip in cold syrup. The sweet indulgence and beautiful presentation create instant happiness.', 'assets/images/meals/koeksisters.jpg', '', NOW());
