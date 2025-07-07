<?php
/**
 * MoodifyMe - Add More Sample Community Posts
 * Adds additional posts to create more variety
 */

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db_connect.php';

echo "🚀 Adding More Sample Community Posts...\n\n";

try {
    // Get existing user IDs
    $result = $conn->query("SELECT id FROM users ORDER BY id LIMIT 10");
    $userIds = [];
    while ($row = $result->fetch_assoc()) {
        $userIds[] = $row['id'];
    }
    
    if (empty($userIds)) {
        echo "❌ No users found. Please run create_sample_posts.php first.\n";
        exit(1);
    }
    
    echo "👥 Found " . count($userIds) . " users\n";
    
    // Additional sample posts
    $morePosts = [
        // More General Posts
        [
            'user_id' => $userIds[0] ?? 2,
            'title' => 'Finding Balance in a Busy World',
            'content' => "Does anyone else feel like they're constantly juggling a million things? 🤹‍♀️\n\nBetween work, family, social commitments, and trying to maintain some semblance of self-care, I often feel like I'm barely keeping my head above water.\n\nI've been working on setting better boundaries and learning to say \"no\" to things that don't align with my priorities. It's harder than it sounds! There's always this guilt that comes with putting myself first.\n\nHow do you find balance in your life? What strategies help you prioritize your mental health when everything feels urgent and important?\n\nWould love to hear your thoughts and experiences! 💭",
            'post_type' => 'general',
            'mood_tag' => 'overwhelmed',
            'is_anonymous' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours'))
        ],
        [
            'user_id' => $userIds[1] ?? 3,
            'title' => 'The Art of Self-Forgiveness',
            'content' => "I've been reflecting a lot lately on how hard I am on myself. 🤔\n\nWe often extend compassion and understanding to our friends when they make mistakes, but when it comes to ourselves? We become our own worst critics. I've been practicing self-forgiveness, and it's harder than I thought it would be.\n\nSome things I'm learning:\n• Mistakes don't define my worth as a person\n• I'm human, and humans are imperfect by nature\n• Self-compassion isn't self-indulgence - it's necessary for growth\n• Forgiving myself doesn't mean excusing harmful behavior\n\nHow do you practice self-forgiveness? What helps you treat yourself with the same kindness you'd show a good friend?",
            'post_type' => 'general',
            'mood_tag' => 'reflective',
            'is_anonymous' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-4 days'))
        ],
        
        // More Support Posts
        [
            'user_id' => $userIds[2] ?? 4,
            'title' => 'Struggling with Perfectionism',
            'content' => "Does anyone else struggle with perfectionism to the point where it's paralyzing? 😓\n\nI find myself procrastinating on important tasks because I'm terrified they won't be \"perfect.\" I'll spend hours on something that should take 30 minutes, or worse, I won't start at all because I'm convinced I'll fail.\n\nThis perfectionism is affecting my work, relationships, and mental health. I know logically that \"perfect\" doesn't exist, but emotionally, I can't seem to let go of these impossible standards.\n\nHas anyone found strategies that help with perfectionist tendencies? How do you embrace \"good enough\" when your brain is screaming that it needs to be flawless?\n\nI'd really appreciate any insights or experiences you're willing to share. 💙",
            'post_type' => 'support',
            'mood_tag' => 'frustrated',
            'is_anonymous' => 1,
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ],
        [
            'user_id' => $userIds[3] ?? 5,
            'title' => 'Dealing with Work-Related Stress and Burnout',
            'content' => "I think I'm experiencing burnout, and I'm not sure what to do about it. 😵‍💫\n\nWork has been incredibly demanding lately, and I feel like I'm constantly behind. I'm working longer hours, skipping breaks, and bringing stress home with me. My sleep is terrible, I'm irritable with my family, and I've lost interest in things I used to enjoy.\n\nThe worst part is feeling like I can't take time off because there's always something urgent that needs my attention. I know this isn't sustainable, but I'm scared of falling behind or letting my team down.\n\nHow do you set boundaries at work? Have you ever had to have difficult conversations with your boss about workload? I'm worried about how it might affect my career, but I know something has to change.\n\nAny advice would be greatly appreciated. Thank you for being such a supportive community. 🙏",
            'post_type' => 'support',
            'mood_tag' => 'stressed',
            'is_anonymous' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-4 hours'))
        ],
        
        // More Celebration Posts
        [
            'user_id' => $userIds[4] ?? 6,
            'title' => 'One Year Anxiety-Free! My Journey to Recovery 🌈',
            'content' => "Today marks exactly one year since my last major anxiety attack, and I had to share this milestone with you all! 🎈\n\nA year ago, I was having daily panic attacks, couldn't leave my house without feeling terrified, and genuinely thought I'd never feel \"normal\" again. Anxiety had completely taken over my life.\n\nMy recovery journey included:\n🌱 Therapy (CBT was a game-changer for me)\n🌱 Medication (took a while to find the right one)\n🌱 Regular exercise (started with just 5-minute walks)\n🌱 Meditation and mindfulness practices\n🌱 Building a strong support network\n🌱 Learning to challenge catastrophic thinking\n🌱 Gradual exposure to feared situations\n\nThe most important thing I learned: recovery is possible, but it takes time, patience, and self-compassion. There were setbacks, bad days, and moments when I wanted to give up. But I kept going, one day at a time.\n\nTo anyone currently struggling with anxiety: you are stronger than you know, and this won't last forever. Keep fighting, keep seeking help, and be gentle with yourself.\n\nYou've got this! 💪✨",
            'post_type' => 'celebration',
            'mood_tag' => 'grateful',
            'is_anonymous' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours'))
        ],
        [
            'user_id' => $userIds[5] ?? 7,
            'title' => 'I Set a Boundary and the World Didn\'t End! 🎉',
            'content' => "Small victory alert! I actually said \"no\" to something today without feeling guilty about it! 🙌\n\nMy friend asked me to help her move this weekend, but I already had plans for some much-needed self-care time. Old me would have immediately said yes and then resented it later.\n\nBut today I said: \"I'd love to help, but I already have commitments this weekend. Could we find another time, or is there another way I can support you?\"\n\nAnd you know what? She was totally understanding! She thanked me for being honest and we figured out an alternative solution.\n\nWhy did I think the world would end if I prioritized my own needs? 🤷‍♀️\n\nHere's to more boundary-setting and less people-pleasing! Who else is working on this? 💪",
            'post_type' => 'celebration',
            'mood_tag' => 'empowered',
            'is_anonymous' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-6 hours'))
        ],
        [
            'user_id' => $userIds[6] ?? 8,
            'title' => 'Three Months of Daily Meditation! 🧘‍♂️',
            'content' => "I can't believe I'm saying this, but I've meditated every single day for the past three months! 🎊\n\nI started with just 2 minutes a day using a meditation app, and now I'm up to 15-20 minutes. Some days it's peaceful and blissful, other days my mind is like a monkey swinging from branch to branch - and that's okay!\n\nWhat I've noticed:\n✨ I'm less reactive to stressful situations\n✨ I sleep better at night\n✨ I'm more aware of my thoughts and emotions\n✨ I have more patience with myself and others\n✨ I feel more connected to the present moment\n\nThe key for me was starting small and being consistent rather than perfect. Missing a day here and there doesn't matter - what matters is coming back to it.\n\nTo anyone thinking about starting a meditation practice: you don't need to sit in lotus position for an hour. Even 2 minutes counts! 🌟",
            'post_type' => 'celebration',
            'mood_tag' => 'accomplished',
            'is_anonymous' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-8 hours'))
        ]
    ];
    
    echo "📝 Creating additional community posts...\n";
    
    $newPostIds = [];
    foreach ($morePosts as $post) {
        $stmt = $conn->prepare("INSERT INTO community_posts (user_id, title, content, post_type, mood_tag, is_anonymous, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssis", $post['user_id'], $post['title'], $post['content'], $post['post_type'], $post['mood_tag'], $post['is_anonymous'], $post['created_at']);
        
        if ($stmt->execute()) {
            $newPostIds[] = $conn->insert_id;
            echo "  ✅ Created: " . substr($post['title'], 0, 30) . "...\n";
        } else {
            echo "  ❌ Failed: " . substr($post['title'], 0, 30) . "... - " . $stmt->error . "\n";
        }
    }
    
    echo "✅ Additional community posts created: " . count($newPostIds) . "\n";
    
    // Add more reactions
    echo "👍 Adding more post reactions...\n";
    $moreReactions = [
        ['post_id' => $newPostIds[0] ?? 8, 'user_id' => $userIds[1] ?? 3, 'reaction_type' => 'support'],
        ['post_id' => $newPostIds[1] ?? 9, 'user_id' => $userIds[2] ?? 4, 'reaction_type' => 'heart'],
        ['post_id' => $newPostIds[4] ?? 12, 'user_id' => $userIds[0] ?? 2, 'reaction_type' => 'celebrate'],
        ['post_id' => $newPostIds[4] ?? 12, 'user_id' => $userIds[1] ?? 3, 'reaction_type' => 'heart'],
        ['post_id' => $newPostIds[5] ?? 13, 'user_id' => $userIds[2] ?? 4, 'reaction_type' => 'empowered'],
        ['post_id' => $newPostIds[6] ?? 14, 'user_id' => $userIds[3] ?? 5, 'reaction_type' => 'peaceful']
    ];
    
    foreach ($moreReactions as $reaction) {
        $stmt = $conn->prepare("INSERT INTO post_reactions (post_id, user_id, reaction_type, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $reaction['post_id'], $reaction['user_id'], $reaction['reaction_type']);
        $stmt->execute();
    }
    
    echo "✅ Additional post reactions added: " . count($moreReactions) . "\n";
    
    // Add more comments
    echo "💬 Adding more post comments...\n";
    $moreComments = [
        ['post_id' => $newPostIds[1] ?? 9, 'user_id' => $userIds[0] ?? 2, 'content' => "This resonates so deeply with me! I'm also working on self-forgiveness and it's such a challenging but important practice. Thank you for sharing your insights! 💙"],
        ['post_id' => $newPostIds[4] ?? 12, 'user_id' => $userIds[1] ?? 3, 'content' => "One year is incredible! I'm so inspired by your journey and the fact that you never gave up. Your story gives me hope for my own recovery. Thank you for sharing! 🌟"],
        ['post_id' => $newPostIds[5] ?? 13, 'user_id' => $userIds[2] ?? 4, 'content' => "YES! 🙌 Boundary setting is so hard but so necessary. I'm proud of you for prioritizing your needs. Your friend's understanding response shows that good people respect boundaries!"],
        ['post_id' => $newPostIds[6] ?? 14, 'user_id' => $userIds[0] ?? 2, 'content' => "Three months is amazing! I've been wanting to start a meditation practice but keep making excuses. Your post is the motivation I needed to finally begin. Starting with 2 minutes today! 🧘‍♀️"]
    ];
    
    foreach ($moreComments as $comment) {
        $stmt = $conn->prepare("INSERT INTO post_comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $comment['post_id'], $comment['user_id'], $comment['content']);
        $stmt->execute();
    }
    
    echo "✅ Additional post comments added: " . count($moreComments) . "\n";
    
    // Final verification
    echo "\n🔍 Final verification...\n";
    
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
    
    $result = $conn->query("SELECT mood_tag, COUNT(*) as count FROM community_posts WHERE mood_tag IS NOT NULL GROUP BY mood_tag ORDER BY count DESC");
    if ($result) {
        echo "   🎭 Posts by mood:\n";
        while ($row = $result->fetch_assoc()) {
            echo "      • " . ucfirst($row['mood_tag']) . ": " . $row['count'] . " posts\n";
        }
    }
    
    echo "\n🎉 Additional sample community posts created successfully!\n";
    echo "🌐 Visit: http://localhost/MoodifyMe/pages/community_posts.php\n";
    echo "📱 Your community now has a rich variety of content!\n\n";
    
} catch (Exception $e) {
    echo "💥 Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "✨ Your community is now thriving! ✨\n";
?>
