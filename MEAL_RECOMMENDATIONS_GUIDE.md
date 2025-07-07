# MoodifyMe African Meals Recommendation System Guide

## Overview
Your MoodifyMe project now has a comprehensive African meals recommendation system that suggests traditional African dishes based on mood transitions. The system includes 20+ carefully curated meal recommendations with detailed recipes, mood benefits, and cultural context.

## What's Been Added

### 1. Database Content
- **20+ African meal recommendations** covering all major mood transitions
- **Comprehensive recipe details** including ingredients and instructions
- **Mood-specific benefits** explaining why each meal helps with emotional transitions
- **Regional diversity** featuring dishes from West, East, North, and Southern Africa

### 2. Enhanced API Features
- **Mood-based filtering** - Get meals for specific emotion transitions
- **Regional filtering** - Browse by African regions (West, East, North, South)
- **Country-specific searches** - Find meals from specific African countries
- **Search functionality** - Search by dish name or ingredients
- **Popular meals** - Get trending meals based on user feedback
- **Random discovery** - Explore new meals randomly

### 3. Additional Meal Data
- **Cooking time estimates** - Know how long each recipe takes
- **Difficulty levels** - Choose recipes based on your cooking skills
- **Ingredient counts** - See how many ingredients you'll need
- **Dietary tags** - Identify vegetarian, spicy, or other dietary preferences
- **Cultural context** - Learn about the origin and significance of each dish

## Setup Instructions

### Step 1: Import the African Meals Data
1. Navigate to your MoodifyMe directory in a web browser
2. Go to: `http://localhost/MoodifyMe/database/setup_african_meals.php`
3. This will automatically import all 20+ African meal recommendations
4. Verify the setup was successful (you should see confirmation messages)

### Step 2: Create Meal Images (Optional)
1. Run: `http://localhost/MoodifyMe/database/create_meal_images.php`
2. This creates placeholder images for all meals
3. Replace placeholders with actual food photos for better visual appeal

### Step 3: Test the System
Visit these URLs to test different features:
- **Mood-based recommendations**: `http://localhost/MoodifyMe/api/african_meals.php?action=get_by_mood&source=sad&target=happy`
- **Random meals**: `http://localhost/MoodifyMe/api/african_meals.php?action=get_random&limit=5`
- **Search meals**: `http://localhost/MoodifyMe/api/african_meals.php?action=search&query=jollof`

## Featured African Meals

### Comfort & Healing (Sad → Happy)
- **Jollof Rice** - West African comfort food with vibrant spices
- **Injera with Doro Wat** - Ethiopian communal meal promoting connection
- **Bobotie** - South African sweet and savory casserole

### Calming & Soothing (Angry/Anxious → Calm)
- **Moroccan Mint Tea** - Ritual preparation promotes mindfulness
- **Ugali with Sukuma Wiki** - Simple, grounding Kenyan meal
- **Harira Soup** - Moroccan healing lentil soup
- **Rooibos Tea** - Naturally caffeine-free South African calm

### Energy & Vitality (Tired → Energetic)
- **Suya** - Nigerian spiced grilled meat for protein energy
- **Ethiopian Coffee Ceremony** - Traditional energizing ritual
- **Mandazi** - East African energy-boosting sweet bread

### Stress Relief (Stressed → Relaxed)
- **Tagine** - Slow-cooked Moroccan meditation in a pot
- **Biltong & Rooibos** - Simple South African stress relief combo

### Excitement & Adventure (Bored → Excited)
- **Bunny Chow** - South African street food adventure
- **Kelewele** - Ghanaian spicy plantain excitement

### Celebration & Joy (Happy → Happy, Neutral → Happy)
- **Celebration Jollof** - Festive West African party rice
- **Peri-Peri Chicken** - Mozambican fire that awakens senses
- **Malva Pudding** - South African joy dessert

## How Users Experience It

1. **Mood Assessment**: User completes mood check-in
2. **Recommendation Options**: System shows African Meals as an option
3. **Personalized Suggestions**: User sees 3-6 relevant African meals
4. **Detailed Information**: Each meal shows:
   - Beautiful food image
   - Cultural background
   - Complete recipe with ingredients
   - Cooking time and difficulty
   - Why it helps with their mood transition
   - User ratings and feedback

5. **Interactive Features**: Users can:
   - Like/dislike meals for better future recommendations
   - View full recipes
   - Search for specific dishes
   - Explore by region or country

## API Endpoints Available

### Get Meals by Mood
```
GET /api/african_meals.php?action=get_by_mood&source=sad&target=happy&limit=5
```

### Search Meals
```
GET /api/african_meals.php?action=search&query=jollof&limit=10
```

### Get by Region
```
GET /api/african_meals.php?action=get_by_region&region=west_africa&limit=8
```

### Get by Country
```
GET /api/african_meals.php?action=get_by_country&country=nigeria&limit=6
```

### Get Popular Meals
```
GET /api/african_meals.php?action=get_popular&period=week&limit=10
```

### Get Random Meals
```
GET /api/african_meals.php?action=get_random&limit=5
```

### Submit Feedback
```
POST /api/african_meals.php?action=add_feedback
Body: {"meal_id": 123, "feedback_type": "like"}
```

## Customization Options

### Adding More Meals
1. Insert new records into the `recommendations` table with `type='african_meals'`
2. Include proper `source_emotion` and `target_emotion` values
3. Add detailed `content` with ingredients and instructions
4. Specify appropriate `image_url` paths

### Adding Images
1. Place meal photos in `assets/images/meals/`
2. Use descriptive filenames (e.g., `jollof_rice.jpg`)
3. Recommended size: 400x300 pixels
4. Keep file sizes under 200KB for fast loading

### Customizing Mood Benefits
Edit the `getMoodBenefits()` function in `api/african_meals.php` to modify explanations of how meals help with mood transitions.

## Integration with Main App

The African meals system integrates seamlessly with your existing MoodifyMe features:
- **Mood tracking** - Recommendations based on current and desired emotions
- **User feedback** - Like/dislike system improves future suggestions
- **Community features** - Users can share favorite recipes
- **Progress tracking** - See which meals helped with mood improvements

## Next Steps

1. **Test thoroughly** - Try different mood combinations
2. **Add real images** - Replace placeholders with appetizing food photos
3. **Gather feedback** - See which meals users prefer
4. **Expand content** - Add more regional specialties
5. **Consider dietary restrictions** - Add vegetarian, vegan, gluten-free options

Your African meals recommendation system is now fully functional and ready to help users discover the mood-boosting power of traditional African cuisine!
