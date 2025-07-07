<?php
/**
 * MoodifyMe - Create Sample Community Posts
 * Simple PHP script to create sample posts directly
 */

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db_connect.php';

echo "🚀 Creating Sample Community Posts...\n\n";

try {
    // First, ensure we have sample users
    echo "👥 Creating sample users...\n";
    
    $users = [
        ['sarah_wellness', 'sarah@example.com', 'Sarah', 'Johnson'],
        ['mike_mindful', 'mike@example.com', 'Mike', 'Chen'],
        ['emma_support', 'emma@example.com', 'Emma', 'Davis'],
        ['alex_journey', 'alex@example.com', 'Alex', 'Rodriguez'],
        ['lisa_hope', 'lisa@example.com', 'Lisa', 'Thompson'],
        ['david_strength', 'david@example.com', 'David', 'Wilson'],
        ['maya_peace', 'maya@example.com', 'Maya', 'Patel']
    ];
    
    $userIds = [];
    foreach ($users as $user) {
        $stmt = $conn->prepare("INSERT IGNORE INTO users (username, email, password_hash, first_name, last_name, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
        $stmt->bind_param("sssss", $user[0], $user[1], $passwordHash, $user[2], $user[3]);
        $stmt->execute();
        
        // Get user ID
        $result = $conn->query("SELECT id FROM users WHERE username = '{$user[0]}'");
        if ($result && $row = $result->fetch_assoc()) {
            $userIds[] = $row['id'];
        }
    }
    
    echo "✅ Sample users created/verified\n";
    
    // Clear existing posts
    echo "🧹 Cleaning existing community posts...\n";
    $conn->query("DELETE FROM post_comments WHERE post_id IN (SELECT id FROM community_posts)");
    $conn->query("DELETE FROM post_reactions WHERE post_id IN (SELECT id FROM community_posts)");
    $conn->query("DELETE FROM community_posts");
    $conn->query("ALTER TABLE community_posts AUTO_INCREMENT = 1");
    echo "✅ Existing data cleaned\n";
    
    // Sample posts data
    $posts = [
        // General Posts
        [
            'user_id' => $userIds[0] ?? 2,
            'title' => 'Starting My Mental Health Journey',
            'content' => "Hi everyone! 👋 I'm new to this community and wanted to introduce myself. I've been struggling with anxiety for a while now, and I finally decided to take the first step towards better mental health.\n\nI've heard amazing things about this community and how supportive everyone is. I'm looking forward to connecting with others who understand what it's like to navigate these challenges.\n\nWhat advice would you give to someone just starting their mental health journey? Any resources or practices that have been particularly helpful for you?\n\nThank you for creating such a welcoming space! 💙",
            'post_type' => 'general',
            'mood_tag' => 'hopeful',
            'is_anonymous' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ],
        [
            'user_id' => $userIds[1] ?? 3,
            'title' => 'The Power of Small Daily Habits',
            'content' => "I wanted to share something that's been really helping me lately - the power of tiny, consistent habits! 🌱\n\nInstead of trying to overhaul my entire life at once (which always led to burnout), I've been focusing on just ONE small thing each day:\n\n✅ 5 minutes of morning meditation\n✅ Writing down 3 things I'm grateful for\n✅ Taking a 10-minute walk outside\n✅ Drinking an extra glass of water\n\nThese might seem insignificant, but after 3 weeks, I can genuinely feel the difference in my mood and energy levels. The key is being consistent, not perfect.\n\nWhat small habits have made a big difference in your life? I'd love to hear your experiences! 🌟",
            'post_type' => 'general',
            'mood_tag' => 'motivated',
            'is_anonymous' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ],
        [
            'user_id' => $userIds[2] ?? 4,
            'title' => 'Dealing with Seasonal Changes',
            'content' => "Is anyone else feeling the effects of the changing seasons? 🍂\n\nI've noticed my mood tends to dip as the days get shorter and the weather gets colder. It's like my energy just drains away, and simple tasks feel so much harder.\n\nI've been trying to combat this by:\n- Using a light therapy lamp in the mornings\n- Maintaining my exercise routine (even if it's just indoor yoga)\n- Making sure to get outside during lunch breaks\n- Eating more warming, nutritious foods\n\nBut some days are still really tough. How do you all cope with seasonal mood changes? Any tips or strategies that work for you?\n\nSending warm thoughts to everyone who might be struggling with this too. We're in this together! 🤗",
            'post_type' => 'general',
            'mood_tag' => 'contemplative',
            'is_anonymous' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-6 hours'))
        ],
        
        // Support Posts
        [
            'user_id' => $userIds[3] ?? 5,
            'title' => 'Struggling with Panic Attacks - Need Advice',
            'content' => "Hi everyone, I'm reaching out because I've been having more frequent panic attacks lately, and I'm feeling really overwhelmed. 😰\n\nThey seem to come out of nowhere - my heart starts racing, I can't breathe properly, and I feel like I'm going to pass out. The worst part is the fear of having another one, which creates this awful cycle.\n\nI've tried some breathing exercises I found online, but in the moment, it's so hard to remember what to do. Has anyone found techniques that actually work during a panic attack?\n\nI'm also wondering if I should talk to a professional. How did you know when it was time to seek help? Any recommendations for finding the right therapist?\n\nThank you for listening. This community means so much to me. 💙",
            'post_type' => 'support',
            'mood_tag' => 'anxious',
            'is_anonymous' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ],
        [
            'user_id' => $userIds[4] ?? 6,
            'title' => 'Feeling Isolated and Lonely',
            'content' => "I've been feeling really isolated lately, and I'm not sure how to break out of this cycle. 😔\n\nEven when I'm around people, I feel disconnected and alone. It's like there's this invisible wall between me and everyone else. I want to reach out and connect, but I don't know how, and I'm afraid of being a burden.\n\nSocial situations that used to feel natural now feel exhausting and fake. I find myself withdrawing more and more, which only makes the loneliness worse.\n\nHas anyone else experienced this? How did you start to reconnect with others and rebuild those social connections? I'm open to any advice or just knowing that I'm not alone in feeling this way.\n\nSending love to anyone else who might be struggling with loneliness. 🤗",
            'post_type' => 'support',
            'mood_tag' => 'lonely',
            'is_anonymous' => 1,
            'created_at' => date('Y-m-d H:i:s', strtotime('-8 hours'))
        ],
        
        // Celebration Posts
        [
            'user_id' => $userIds[0] ?? 2,
            'title' => '6 Months Therapy Milestone! 🎉',
            'content' => "I can't believe I'm writing this, but today marks 6 months since I started therapy! 🎊\n\nWhen I first walked into my therapist's office, I was a mess. I could barely articulate what was wrong, and I felt so broken and hopeless. I honestly didn't think therapy would help - I thought I was just \"too damaged\" to fix.\n\nBut here I am, 6 months later, and while I'm not \"cured\" (because that's not how mental health works!), I feel like a completely different person. I've learned:\n\n✨ How to identify and challenge negative thought patterns\n✨ Healthy coping strategies for anxiety and stress\n✨ The importance of setting boundaries\n✨ How to practice self-compassion\n✨ That healing isn't linear, and that's okay!\n\nTo anyone considering therapy but feeling scared or skeptical - it's worth it. Finding the right therapist might take time, but when you do, it can be life-changing.\n\nThank you to this community for encouraging me to take that first step! 💙",
            'post_type' => 'celebration',
            'mood_tag' => 'proud',
            'is_anonymous' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
        ],
        [
            'user_id' => $userIds[1] ?? 3,
            'title' => 'I Finally Asked for Help at Work! 💪',
            'content' => "This might seem small to some people, but for me, this is HUGE! Today I finally had the courage to talk to my manager about my workload and ask for support. 🙌\n\nI've been struggling for months, working late nights and weekends, feeling completely overwhelmed but too afraid to speak up. I kept thinking I should be able to handle everything on my own, and asking for help felt like admitting failure.\n\nBut you know what? My manager was incredibly understanding! She thanked me for being honest, acknowledged that my workload was unrealistic, and we're now working together to redistribute some tasks and bring in additional support.\n\nI walked out of that meeting feeling 10 pounds lighter. Why did I wait so long to have this conversation?\n\nKey lessons learned:\n🌟 Asking for help is a sign of strength, not weakness\n🌟 Most people want to support you - you just have to let them know what you need\n🌟 Suffering in silence helps no one\n🌟 Advocating for yourself gets easier with practice\n\nHere's to setting better boundaries and prioritizing mental health! 🥳",
            'post_type' => 'celebration',
            'mood_tag' => 'empowered',
            'is_anonymous' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ]
    ];
    
    echo "📝 Creating community posts...\n";
    
    $postIds = [];
    foreach ($posts as $post) {
        $stmt = $conn->prepare("INSERT INTO community_posts (user_id, title, content, post_type, mood_tag, is_anonymous, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssis", $post['user_id'], $post['title'], $post['content'], $post['post_type'], $post['mood_tag'], $post['is_anonymous'], $post['created_at']);
        
        if ($stmt->execute()) {
            $postIds[] = $conn->insert_id;
            echo "  ✅ Created: " . substr($post['title'], 0, 30) . "...\n";
        } else {
            echo "  ❌ Failed: " . substr($post['title'], 0, 30) . "... - " . $stmt->error . "\n";
        }
    }
    
    echo "✅ Community posts created: " . count($postIds) . "\n";
    
    // Add some reactions
    echo "👍 Adding post reactions...\n";
    $reactions = [
        ['post_id' => $postIds[0] ?? 1, 'user_id' => $userIds[1] ?? 3, 'reaction_type' => 'heart'],
        ['post_id' => $postIds[0] ?? 1, 'user_id' => $userIds[2] ?? 4, 'reaction_type' => 'like'],
        ['post_id' => $postIds[1] ?? 2, 'user_id' => $userIds[0] ?? 2, 'reaction_type' => 'like'],
        ['post_id' => $postIds[5] ?? 6, 'user_id' => $userIds[1] ?? 3, 'reaction_type' => 'celebrate'],
        ['post_id' => $postIds[5] ?? 6, 'user_id' => $userIds[2] ?? 4, 'reaction_type' => 'heart']
    ];
    
    foreach ($reactions as $reaction) {
        $stmt = $conn->prepare("INSERT INTO post_reactions (post_id, user_id, reaction_type, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $reaction['post_id'], $reaction['user_id'], $reaction['reaction_type']);
        $stmt->execute();
    }
    
    echo "✅ Post reactions added: " . count($reactions) . "\n";
    
    // Add some comments
    echo "💬 Adding post comments...\n";
    $comments = [
        ['post_id' => $postIds[0] ?? 1, 'user_id' => $userIds[1] ?? 3, 'content' => "Welcome to the community! 🤗 You've taken such a brave first step. My advice would be to be patient with yourself - healing isn't linear, and that's perfectly okay. You're not alone in this journey!"],
        ['post_id' => $postIds[1] ?? 2, 'user_id' => $userIds[2] ?? 4, 'content' => "This is so inspiring! I love how you're focusing on small, manageable changes. I've been trying to implement a gratitude practice too - it really does make a difference over time. Thank you for sharing! ✨"],
        ['post_id' => $postIds[5] ?? 6, 'user_id' => $userIds[1] ?? 3, 'content' => "This made me tear up! 😭 So proud of you for sticking with therapy and doing the hard work. Your progress is incredible and gives me hope for my own journey. Thank you for sharing this! 🎉"]
    ];
    
    foreach ($comments as $comment) {
        $stmt = $conn->prepare("INSERT INTO post_comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $comment['post_id'], $comment['user_id'], $comment['content']);
        $stmt->execute();
    }
    
    echo "✅ Post comments added: " . count($comments) . "\n";
    
    // Final verification
    echo "\n🔍 Verifying import...\n";
    
    $result = $conn->query("SELECT COUNT(*) as total FROM community_posts");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "   📝 Total posts in database: " . $row['total'] . "\n";
    }
    
    $result = $conn->query("SELECT post_type, COUNT(*) as count FROM community_posts GROUP BY post_type");
    if ($result) {
        echo "   📊 Posts by category:\n";
        while ($row = $result->fetch_assoc()) {
            echo "      • " . ucfirst($row['post_type']) . ": " . $row['count'] . " posts\n";
        }
    }
    
    echo "\n🎉 Sample community posts created successfully!\n";
    echo "🌐 Visit: http://localhost/MoodifyMe/pages/community_posts.php\n";
    echo "📱 Or use the Community dropdown menu!\n\n";
    
} catch (Exception $e) {
    echo "💥 Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "✨ Happy community building! ✨\n";
?>
