-- MoodifyMe Seed Data

-- Use database
USE moodifyme;

-- Insert sample recommendations for different emotion transitions

-- Happy to Happy (maintain happiness)
INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, created_at)
VALUES 
('Upbeat Playlist: "Happy Vibes"', 'A collection of upbeat songs to keep your spirits high and maintain your positive mood.', 'music', 'happy', 'happy', 'Includes tracks from Pharrell Williams, Daft Punk, and Bruno Mars.', 'assets/images/recommendations/music1.jpg', 'https://open.spotify.com/playlist/37i9dQZF1DXdPec7aLTmlC', NOW()),
('Comedy Movie: "The Grand Budapest Hotel"', 'This whimsical Wes Anderson film will keep you smiling with its quirky characters and vibrant visuals.', 'movies', 'happy', 'happy', 'Directed by Wes Anderson, starring Ralph Fiennes and Tony Revolori.', 'assets/images/recommendations/movie1.jpg', 'https://www.imdb.com/title/tt2278388/', NOW()),
('Outdoor Activity: Picnic in the Park', 'Enjoy the sunshine and fresh air with a picnic in your local park. Pack your favorite snacks and invite friends!', 'activities', 'happy', 'happy', 'Suggested items: blanket, fresh fruits, sandwiches, and games.', 'assets/images/recommendations/activity1.jpg', '', NOW());

-- Sad to Happy
INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, created_at)
VALUES 
('Mood-Lifting Playlist: "From Blue to Bright"', 'A carefully curated playlist that starts with gentle, understanding melodies and gradually transitions to uplifting tunes.', 'music', 'sad', 'happy', 'Features artists like Coldplay, Florence + The Machine, and ending with more upbeat tracks.', 'assets/images/recommendations/music2.jpg', 'https://open.spotify.com/playlist/37i9dQZF1DX3rxVfibe1L0', NOW()),
('Feel-Good Movie: "The Secret Life of Walter Mitty"', 'An inspiring film about adventure and finding joy in life that will help lift your spirits.', 'movies', 'sad', 'happy', 'Directed by Ben Stiller, who also stars alongside Kristen Wiig.', 'assets/images/recommendations/movie2.jpg', 'https://www.imdb.com/title/tt0359950/', NOW()),
('Comfort Food: Homemade Chocolate Chip Cookies', 'The simple act of baking can be therapeutic, and the warm, sweet result is sure to bring some comfort.', 'food', 'sad', 'happy', 'Recipe includes: flour, butter, chocolate chips, brown sugar, and vanilla extract.', 'assets/images/recommendations/food1.jpg', 'https://www.allrecipes.com/recipe/10813/best-chocolate-chip-cookies/', NOW()),
('Mood-Boosting Book: "The House in the Cerulean Sea"', 'A heartwarming fantasy novel that\'s like a warm hug in book form, perfect for lifting your spirits.', 'books', 'sad', 'happy', 'Written by TJ Klune, this charming story combines whimsy with important messages about acceptance.', 'assets/images/recommendations/book1.jpg', 'https://www.goodreads.com/book/show/45047384-the-house-in-the-cerulean-sea', NOW());

-- Angry to Calm
INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, created_at)
VALUES 
('Calming Playlist: "Soothe Your Soul"', 'Gentle instrumental music designed to help you release tension and find your center again.', 'music', 'angry', 'calm', 'Features ambient sounds, piano compositions, and gentle classical pieces.', 'assets/images/recommendations/music3.jpg', 'https://open.spotify.com/playlist/37i9dQZF1DX1s9knjP51Oa', NOW()),
('Breathing Exercise: 4-7-8 Technique', 'A simple breathing technique that can help calm your nervous system and reduce feelings of anger.', 'activities', 'angry', 'calm', 'Inhale for 4 seconds, hold for 7 seconds, exhale for 8 seconds. Repeat 5 times.', 'assets/images/recommendations/activity2.jpg', '', NOW()),
('Calming Tea: Chamomile with Honey', 'A warm cup of chamomile tea with honey can help soothe your nerves and provide a moment of peaceful reflection.', 'food', 'angry', 'calm', 'Ingredients: Chamomile tea bag, hot water, honey, and optionally a slice of lemon.', 'assets/images/recommendations/food2.jpg', '', NOW());

-- Anxious to Calm
INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, created_at)
VALUES 
('Guided Meditation: "Releasing Anxiety"', 'A 10-minute guided meditation specifically designed to help you let go of anxious thoughts and find peace.', 'activities', 'anxious', 'calm', 'Focus on deep breathing and progressive muscle relaxation.', 'assets/images/recommendations/activity3.jpg', 'https://www.youtube.com/watch?v=O-6f5wQXSu8', NOW()),
('Soothing Playlist: "Anxiety Relief"', 'Gentle, ambient music with nature sounds to help quiet your mind and ease anxiety.', 'music', 'anxious', 'calm', 'Features artists like Brian Eno, Nils Frahm, and nature soundscapes.', 'assets/images/recommendations/music4.jpg', 'https://open.spotify.com/playlist/37i9dQZF1DX4PP3DA4J0N8', NOW()),
('Herbal Tea: Lavender and Lemon Balm', 'A calming herbal blend known for its anxiety-reducing properties.', 'food', 'anxious', 'calm', 'Ingredients: Dried lavender, lemon balm, hot water, and honey if desired.', 'assets/images/recommendations/food3.jpg', '', NOW());

-- Bored to Excited
INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, created_at)
VALUES 
('Adventure Book: "Ready Player One"', 'An exciting sci-fi adventure that will transport you to a thrilling virtual world.', 'books', 'bored', 'excited', 'Written by Ernest Cline, this fast-paced novel combines 80s nostalgia with futuristic action.', 'assets/images/recommendations/book2.jpg', 'https://www.goodreads.com/book/show/9969571-ready-player-one', NOW()),
('Action Movie: "Everything Everywhere All at Once"', 'A mind-bending, genre-defying film that will keep you on the edge of your seat.', 'movies', 'bored', 'excited', 'Starring Michelle Yeoh in an interdimensional adventure that blends action, comedy, and heart.', 'assets/images/recommendations/movie3.jpg', 'https://www.imdb.com/title/tt6710474/', NOW()),
('New Hobby: Learn to Draw', 'Challenge yourself with a new skill that can provide endless creative possibilities.', 'activities', 'bored', 'excited', 'Start with basic shapes and gradually move to more complex subjects.', 'assets/images/recommendations/activity4.jpg', 'https://www.youtube.com/watch?v=7TXEZ4tP06c', NOW());

-- Tired to Energetic
INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, created_at)
VALUES 
('Energizing Playlist: "Morning Boost"', 'Upbeat songs to get your energy flowing and help you shake off fatigue.', 'music', 'tired', 'energetic', 'Features upbeat tracks from various genres to get you moving.', 'assets/images/recommendations/music5.jpg', 'https://open.spotify.com/playlist/37i9dQZF1DX8ky12eWIvcW', NOW()),
('Quick Workout: 7-Minute HIIT', 'A short, high-intensity workout that will boost your energy levels and release endorphins.', 'activities', 'tired', 'energetic', 'Includes jumping jacks, push-ups, and other simple exercises requiring no equipment.', 'assets/images/recommendations/activity5.jpg', 'https://www.youtube.com/watch?v=mmq5zZfmIws', NOW()),
('Energy-Boosting Smoothie', 'A nutritious smoothie packed with ingredients to naturally boost your energy levels.', 'food', 'tired', 'energetic', 'Recipe: Banana, spinach, almond milk, chia seeds, and a touch of honey.', 'assets/images/recommendations/food4.jpg', '', NOW());

-- Stressed to Relaxed
INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, created_at)
VALUES 
('Relaxing Bath Ritual', 'A step-by-step guide to creating a deeply relaxing bath experience to wash away stress.', 'activities', 'stressed', 'relaxed', 'Includes Epsom salts, essential oils, and mindfulness techniques.', 'assets/images/recommendations/activity6.jpg', '', NOW()),
('Calming Book: "The Comfort Book"', 'A collection of consolations and stories that offer comfort during stressful times.', 'books', 'stressed', 'relaxed', 'Written by Matt Haig, this book provides gentle reassurance and perspective.', 'assets/images/recommendations/book3.jpg', 'https://www.goodreads.com/book/show/55825273-the-comfort-book', NOW()),
('Stress-Relief Joke', 'Why don\'t scientists trust atoms? Because they make up everything!', 'jokes', 'stressed', 'relaxed', '', 'assets/images/recommendations/joke1.jpg', '', NOW());

-- Neutral to Happy
INSERT INTO recommendations (title, description, type, source_emotion, target_emotion, content, image_url, link, created_at)
VALUES 
('Uplifting Movie: "Soul"', 'A heartwarming Pixar film that celebrates the joy of living and finding your spark.', 'movies', 'neutral', 'happy', 'An animated film that explores the meaning of life with humor and heart.', 'assets/images/recommendations/movie4.jpg', 'https://www.imdb.com/title/tt2948372/', NOW()),
('Joy-Inducing Activity: Random Act of Kindness', 'Performing a small act of kindness for someone else can significantly boost your own happiness.', 'activities', 'neutral', 'happy', 'Suggestions include paying for a stranger\'s coffee, leaving a positive note, or calling someone to tell them you appreciate them.', 'assets/images/recommendations/activity7.jpg', '', NOW()),
('Happiness-Boosting Joke', 'What\'s the best thing about Switzerland? I don\'t know, but the flag is a big plus!', 'jokes', 'neutral', 'happy', '', 'assets/images/recommendations/joke2.jpg', '', NOW());

-- Insert more recommendations as needed for different emotion transitions
