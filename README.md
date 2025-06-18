# MoodifyMe - Comprehensive Emotion-Based Recommendation System

MoodifyMe is an AI-powered platform designed to enhance users' emotional well-being by analyzing their current mood and offering personalized suggestions to improve or maintain their desired emotional state.

## Features

- **Multi-modal Emotion Detection**: Express your emotions through text or voice
- **Personalized Recommendations**: Get tailored suggestions for music, movies, activities, and more
- **Emotional Journey Tracking**: Monitor your mood patterns over time
- **User Feedback System**: Rate recommendations to improve future suggestions
- **Responsive Design**: Works on desktop and mobile devices

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)
- Modern web browser

## Installation

### Option 1: Automated Installation

1. Upload all files to your web server
2. Navigate to `http://your-domain.com/install.php` in your browser
3. Follow the on-screen instructions to set up the database and create an admin user
4. Delete the `install.php` file after installation is complete

### Option 2: Manual Installation

1. Upload all files to your web server
2. Create a MySQL database for MoodifyMe
3. Import the database schema from `database/schema.sql`
4. (Optional) Import sample data from `database/seed.sql`
5. Edit `config.php` and update the database connection details
6. Navigate to `http://your-domain.com` and register a new user

## Project Structure

```
MoodifyMe/
├── api/                  # API endpoints
│   ├── emotion_analysis.php
│   ├── recommendations.php
│   └── recommendation_feedback.php
├── assets/               # Frontend assets
│   ├── css/
│   ├── js/
│   └── images/
├── database/             # Database files
│   ├── schema.sql
│   └── seed.sql
├── includes/             # Common PHP includes
│   ├── db_connect.php
│   ├── footer.php
│   ├── functions.php
│   └── header.php
├── models/               # PHP model classes
├── pages/                # Frontend pages
│   ├── dashboard.php
│   ├── login.php
│   ├── profile.php
│   ├── recommendations.php
│   └── register.php
├── config.php            # Configuration file
├── index.php             # Main entry point
├── install.php           # Installation script
└── README.md             # This file
```

## Usage

1. Register a new account or log in
2. On the home page, express your current mood through text, voice, or facial expression
3. The system will analyze your input and detect your emotional state
4. Select your target mood (how you want to feel)
5. Receive personalized recommendations to help you achieve your desired emotional state
6. Provide feedback on recommendations to improve future suggestions
7. Track your emotional journey on your dashboard

## Customization

### Adding New Recommendation Types

1. Edit `config.php` and add new types to the `REC_TYPES` array
2. Add corresponding entries in the `recommendations` table

### Modifying Emotion Categories

1. Edit `config.php` and update the `EMOTION_CATEGORIES` array
2. Update the emotion detection logic in `api/emotion_analysis.php`

## Integration with External APIs

MoodifyMe is designed to work with various external APIs for enhanced functionality:

- Text Analysis: Connect to NLP APIs for more accurate emotion detection
- Voice Analysis: Integrate with speech-to-text and voice sentiment analysis APIs

- Content Recommendations: Integrate with music, movie, or content recommendation APIs

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Bootstrap for the responsive UI framework
- Font Awesome for the icons
- Chart.js for data visualization
- All the open-source libraries and tools that made this project possible

## Support

For questions, issues, or feature requests, please contact support@moodifyme.com or open an issue on GitHub.
