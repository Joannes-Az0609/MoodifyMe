# MoodifyMe Social Messaging System

A comprehensive social messaging and community system for the MoodifyMe emotional wellness platform.

## 🌟 Features

### Community Chat
- **Public Chat Rooms**: Join themed rooms for group discussions
- **Real-time Messaging**: Live message updates every 3 seconds
- **Participant Lists**: See who's online and active
- **Moderation Tools**: Report inappropriate content
- **Mobile Responsive**: Works seamlessly on all devices

### Direct Messaging
- **Private Conversations**: One-on-one messaging between connected users
- **Connection System**: Users must connect before messaging
- **Message History**: Persistent conversation history
- **Unread Indicators**: Visual badges for new messages
- **Real-time Updates**: Live message delivery

### Social Features
- **Follow System**: Follow other users to see their activity
- **Connection Requests**: Send and manage friend requests
- **User Profiles**: Enhanced profiles with social stats
- **User Directory**: Search and discover other users
- **Online Status**: See when users are active

### Privacy & Safety
- **User Blocking**: Block unwanted users completely
- **Privacy Controls**: Public/private profiles, message permissions
- **Content Filtering**: Basic profanity and spam filtering
- **Message Reporting**: Report inappropriate content
- **Privacy Settings**: Granular control over visibility and messaging

## 📁 File Structure

```
MoodifyMe/
├── pages/
│   ├── chat_hub.php              # Main messaging hub
│   ├── community_chat.php        # Public chat rooms
│   ├── messages.php              # Direct messaging
│   ├── connections.php           # Manage followers/following
│   ├── user_directory.php        # Find and search users
│   ├── social_profile.php        # Enhanced user profiles
│   └── privacy_settings.php      # Privacy and safety settings
├── api/
│   ├── chat_messages.php         # Get chat room messages
│   ├── direct_messages.php       # Get direct messages
│   ├── send_message.php          # Send messages (both types)
│   ├── chat_participants.php     # Get room participants
│   ├── social_actions.php        # Follow/connect/block actions
│   ├── update_online_status.php  # Update user online status
│   └── report_message.php        # Report inappropriate messages
├── includes/
│   ├── social_functions.php      # Core social functionality
│   └── header.php                # Updated with chat navigation
├── database/
│   ├── social_messaging_schema.sql    # Database tables
│   └── user_social_updates.sql        # User table updates
└── setup_social_messaging.php         # Setup script
```

## 🗄️ Database Schema

### New Tables Created
- `chat_rooms` - Public chat rooms
- `user_follows` - User following relationships
- `user_connections` - Friend/connection system
- `conversations` - Direct message conversations
- `conversation_participants` - Who's in each conversation
- `messages` - All messages (community and direct)
- `chat_room_participants` - Who's in each chat room
- `message_reactions` - Like/heart reactions to messages
- `user_blocks` - Blocked user relationships
- `message_reports` - Reported messages for moderation
- `user_online_status` - User online/offline status

### Updated Tables
- `users` - Added social fields (follower_count, following_count, etc.)

## 🚀 Installation

1. **Run the setup script**:
   ```
   http://your-domain/MoodifyMe/setup_social_messaging.php
   ```

2. **Verify installation**:
   - Check that all database tables were created
   - Ensure default chat rooms exist
   - Test user registration/login

3. **Access the features**:
   - Visit `/pages/chat_hub.php` for the main hub
   - Navigate through the Chat dropdown in the header

## 🎯 Usage Guide

### For Users

1. **Getting Started**:
   - Register/login to your MoodifyMe account
   - Visit the Chat Hub from the navigation menu
   - Complete your profile with a display name and bio

2. **Community Chat**:
   - Join public rooms like "General Support" or "Daily Check-ins"
   - Participate in group discussions
   - Follow community guidelines

3. **Direct Messaging**:
   - Find users in the User Directory
   - Send connection requests
   - Start private conversations once connected

4. **Building Connections**:
   - Follow users you find inspiring
   - Send connection requests for closer relationships
   - Manage your network in the Connections page

5. **Privacy & Safety**:
   - Configure privacy settings
   - Block users if needed
   - Report inappropriate content

### For Administrators

1. **Moderation**:
   - Monitor the `message_reports` table for reported content
   - Review and take action on inappropriate messages
   - Manage chat room settings

2. **User Management**:
   - Monitor user blocks and reports
   - Handle escalated issues
   - Maintain community guidelines

## 🔧 Technical Details

### Real-time Updates
- Uses AJAX polling every 3 seconds for new messages
- Online status updates every 30 seconds
- Efficient database queries with proper indexing

### Security Features
- SQL injection protection with prepared statements
- XSS protection with proper HTML escaping
- Content filtering for basic profanity/spam
- User blocking and reporting system

### Mobile Optimization
- Bootstrap 5 responsive design
- Touch-friendly interface elements
- Optimized for mobile chat experience

### Performance
- Indexed database queries
- Efficient message pagination
- Optimized for concurrent users

## 🎨 Customization

### Adding New Chat Rooms
```sql
INSERT INTO chat_rooms (name, description, created_by, room_type) 
VALUES ('Room Name', 'Room Description', 1, 'public');
```

### Modifying Privacy Settings
Edit `pages/privacy_settings.php` to add new privacy options.

### Custom Message Filtering
Modify the `filterMessage()` function in `api/send_message.php`.

## 🐛 Troubleshooting

### Common Issues

1. **Messages not loading**:
   - Check database connection
   - Verify user permissions
   - Check browser console for JavaScript errors

2. **Real-time updates not working**:
   - Ensure AJAX polling is enabled
   - Check network connectivity
   - Verify API endpoints are accessible

3. **Database errors**:
   - Run the setup script again
   - Check MySQL user permissions
   - Verify all tables exist

### Debug Mode
Add `?debug=1` to any URL to enable debug information.

## 🔮 Future Enhancements

- WebSocket implementation for true real-time messaging
- File/image sharing in messages
- Voice/video calling integration
- Advanced moderation tools
- Message encryption
- Group chat rooms (private)
- Message threading/replies
- Emoji reactions
- Push notifications

## 📞 Support

For issues or questions about the social messaging system:
1. Check this README first
2. Review the database setup
3. Test with the setup script
4. Check browser console for errors

## 🏆 Integration with MoodifyMe

This social messaging system seamlessly integrates with the existing MoodifyMe features:
- Uses existing user authentication
- Maintains the African Sunset theme
- Complements the emotional wellness focus
- Provides community support for mental health journey

The system is designed to enhance the MoodifyMe experience by providing users with peer support and community connections while maintaining focus on emotional wellness and mental health.
