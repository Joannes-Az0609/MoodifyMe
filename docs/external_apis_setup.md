# External Recipe APIs Setup Guide

This guide will help you set up external recipe APIs to populate your MoodifyMe database with real African meal data.

## Overview

MoodifyMe supports two major recipe APIs:

1. **Spoonacular API** - Comprehensive recipe database with 5,000+ recipes
2. **Edamam Recipe API** - Large recipe database with 2+ million recipes

## Option 1: Spoonacular API (Recommended)

### Features
- ✅ 5,000+ recipes with African cuisine filter
- ✅ Detailed nutrition information
- ✅ Recipe instructions included
- ✅ High-quality images
- ✅ Ingredient lists with measurements
- ✅ Cooking time and difficulty ratings

### Pricing
- **Free Tier**: 150 points/day (about 150 recipe searches)
- **Basic Plan**: $29/month for 1,500 points/day
- **Pro Plan**: $99/month for 5,000 points/day

### Setup Instructions

1. **Sign up for Spoonacular API**
   - Go to [https://spoonacular.com/food-api](https://spoonacular.com/food-api)
   - Click "Get Started" and create an account
   - Choose your plan (Free tier is good for testing)

2. **Get your API Key**
   - After signing up, go to your dashboard
   - Copy your API key (it looks like: `abc123def456ghi789`)

3. **Configure MoodifyMe**
   - Open `includes/external_recipe_apis.php`
   - Find this line:
     ```php
     define('SPOONACULAR_API_KEY', 'your_spoonacular_api_key_here');
     ```
   - Replace `your_spoonacular_api_key_here` with your actual API key:
     ```php
     define('SPOONACULAR_API_KEY', 'abc123def456ghi789');
     ```

4. **Test the Connection**
   - Go to `/pages/african_meals_manager.php`
   - Click "Test API Connections"
   - You should see "Spoonacular: API connection successful"

## Option 2: Edamam Recipe API

### Features
- ✅ 2+ million recipes
- ✅ Comprehensive nutrition data (25+ nutrients)
- ✅ Diet and health filters
- ✅ African cuisine support
- ✅ Recipe images and links

### Pricing
- **Enterprise Basic**: $9/month for 10,000 calls
- **Enterprise Core**: $99/month for 500,000 calls
- **Free Trial**: 10 days (Basic plan)

### Setup Instructions

1. **Sign up for Edamam API**
   - Go to [https://developer.edamam.com](https://developer.edamam.com)
   - Click "Get Started" and create an account
   - Choose the Recipe Search API

2. **Get your Credentials**
   - After signing up, go to your dashboard
   - Find your Application ID and Application Key
   - They look like:
     - App ID: `12345678`
     - App Key: `abcdef1234567890abcdef1234567890`

3. **Configure MoodifyMe**
   - Open `includes/external_recipe_apis.php`
   - Find these lines:
     ```php
     define('EDAMAM_RECIPE_APP_ID', 'your_edamam_app_id_here');
     define('EDAMAM_RECIPE_APP_KEY', 'your_edamam_app_key_here');
     ```
   - Replace with your actual credentials:
     ```php
     define('EDAMAM_RECIPE_APP_ID', '12345678');
     define('EDAMAM_RECIPE_APP_KEY', 'abcdef1234567890abcdef1234567890');
     ```

4. **Test the Connection**
   - Go to `/pages/african_meals_manager.php`
   - Click "Test API Connections"
   - You should see "Edamam: API connection successful"

## Using Both APIs (Recommended)

You can configure both APIs to have more recipe sources:

1. Set up both Spoonacular and Edamam as described above
2. Use Spoonacular for detailed recipes with instructions
3. Use Edamam for broader recipe discovery and nutrition data

## Populating Your Database

Once you have at least one API configured, you can populate your database:

### Method 1: Auto-Populate Popular Dishes
1. Go to `/pages/african_meals_manager.php`
2. Click "Start Auto-Population"
3. This will automatically fetch popular African dishes like:
   - Jollof Rice
   - Tagine
   - Injera
   - Doro Wat
   - Fufu
   - And many more!

### Method 2: Search for Specific Dishes
1. Go to `/pages/african_meals_manager.php`
2. Click "Search Dish"
3. Enter a dish name (e.g., "Moroccan Tagine")
4. Select your preferred API
5. Click "Search & Add"

### Method 3: Fetch by Cuisine Type
1. Go to `/pages/african_meals_manager.php`
2. Click "Fetch Cuisine"
3. Select cuisine type (African, Moroccan, Ethiopian, etc.)
4. Choose number of recipes to fetch
5. Click "Fetch Recipes"

## API Usage Tips

### For Spoonacular:
- **Free Tier Limits**: 150 points/day
- **Rate Limiting**: No specific rate limit, but be reasonable
- **Best For**: Getting detailed recipes with instructions
- **Cuisine Filters**: Use "african", "moroccan", "middle eastern"

### For Edamam:
- **Free Tier Limits**: 10,000 calls/month, 10 calls/minute
- **Rate Limiting**: Respect the 10 calls/minute limit
- **Best For**: Large-scale recipe discovery
- **Cuisine Filters**: Use "African", "Middle Eastern", "Mediterranean"

## Troubleshooting

### "API connection failed" Error
1. **Check API Key/Credentials**: Make sure they're correctly entered
2. **Check Internet Connection**: APIs require internet access
3. **Check API Quota**: You might have exceeded your daily/monthly limit
4. **Check API Status**: The external API service might be down

### "No recipes found" Error
1. **Try Different Search Terms**: Use more general terms
2. **Try Different Cuisine Types**: Some APIs have different cuisine classifications
3. **Check API Documentation**: Verify the cuisine filters are correct

### "Failed to save recipe" Error
1. **Check Database Connection**: Make sure your database is accessible
2. **Check Database Permissions**: Ensure write permissions
3. **Check for Duplicates**: The system prevents duplicate recipes

## API Comparison

| Feature | Spoonacular | Edamam |
|---------|-------------|---------|
| Free Tier | 150 calls/day | 10,000 calls/month |
| Recipe Instructions | ✅ Yes | ❌ No (links only) |
| Nutrition Data | ✅ Basic | ✅ Comprehensive |
| African Cuisine | ✅ Good | ✅ Excellent |
| Image Quality | ✅ High | ✅ Good |
| Setup Difficulty | ⭐ Easy | ⭐⭐ Medium |
| Cost (Paid Plans) | $29/month | $9/month |

## Recommended Workflow

1. **Start with Spoonacular** (easier setup, good free tier)
2. **Test with a few popular dishes** to verify everything works
3. **Auto-populate 20-30 popular African dishes**
4. **Add Edamam** if you need more recipes or better nutrition data
5. **Use the search function** to add specific dishes your users request

## Security Notes

- ✅ **Never commit API keys to version control**
- ✅ **Store API keys in environment variables** for production
- ✅ **Monitor your API usage** to avoid unexpected charges
- ✅ **Implement caching** to reduce API calls (if allowed by API terms)

## Support

If you encounter issues:

1. **Check the Activity Log** in the African Meals Manager
2. **Test API connections** using the built-in test function
3. **Review API documentation** for the specific service
4. **Check server logs** for detailed error messages

## Next Steps

After setting up external APIs:

1. **Populate your database** with 50-100 African recipes
2. **Test the mood-based recommendations** with real data
3. **Configure emotion mappings** for different dish types
4. **Set up automated daily imports** (optional)
5. **Monitor user feedback** to improve recommendations

---

**Note**: This setup gives you access to thousands of real African recipes with authentic ingredients, cooking instructions, and nutritional information. Your users will have access to genuine African cuisine recommendations based on their mood transitions!
