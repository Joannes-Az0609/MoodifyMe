-- =====================================================
-- MoodifyMe Community Posts Sample Data
-- Database: modifyMe1
-- =====================================================

USE modifyMe1;

-- First, let's ensure we have some sample users to create posts
-- (Skip if users already exist)
INSERT IGNORE INTO users (id, username, email, password_hash, first_name, last_name, created_at) VALUES
(1, 'admin', 'admin@moodifyme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', NOW()),
(2, 'sarah_wellness', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Johnson', NOW()),
(3, 'mike_mindful', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike', 'Chen', NOW()),
(4, 'emma_support', 'emma@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emma', 'Davis', NOW()),
(5, 'alex_journey', 'alex@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alex', 'Rodriguez', NOW()),
(6, 'lisa_hope', 'lisa@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lisa', 'Thompson', NOW()),
(7, 'david_strength', 'david@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David', 'Wilson', NOW()),
(8, 'maya_peace', 'maya@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maya', 'Patel', NOW());

-- Clear existing community posts to start fresh
DELETE FROM post_comments WHERE post_id IN (SELECT id FROM community_posts);
DELETE FROM post_reactions WHERE post_id IN (SELECT id FROM community_posts);
DELETE FROM community_posts;

-- Reset auto increment
ALTER TABLE community_posts AUTO_INCREMENT = 1;

-- =====================================================
-- GENERAL CATEGORY POSTS
-- =====================================================

INSERT INTO community_posts (user_id, title, content, post_type, mood_tag, is_anonymous, created_at) VALUES

-- General Post 1
(2, 'Starting My Mental Health Journey', 
'Hi everyone! 👋 I''m new to this community and wanted to introduce myself. I''ve been struggling with anxiety for a while now, and I finally decided to take the first step towards better mental health. 

I''ve heard amazing things about this community and how supportive everyone is. I''m looking forward to connecting with others who understand what it''s like to navigate these challenges.

What advice would you give to someone just starting their mental health journey? Any resources or practices that have been particularly helpful for you?

Thank you for creating such a welcoming space! 💙', 
'general', 'hopeful', 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),

-- General Post 2
(3, 'The Power of Small Daily Habits', 
'I wanted to share something that''s been really helping me lately - the power of tiny, consistent habits! 🌱

Instead of trying to overhaul my entire life at once (which always led to burnout), I''ve been focusing on just ONE small thing each day:

✅ 5 minutes of morning meditation
✅ Writing down 3 things I''m grateful for
✅ Taking a 10-minute walk outside
✅ Drinking an extra glass of water

These might seem insignificant, but after 3 weeks, I can genuinely feel the difference in my mood and energy levels. The key is being consistent, not perfect.

What small habits have made a big difference in your life? I''d love to hear your experiences! 🌟', 
'general', 'motivated', 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),

-- General Post 3
(4, 'Dealing with Seasonal Changes', 
'Is anyone else feeling the effects of the changing seasons? 🍂 

I''ve noticed my mood tends to dip as the days get shorter and the weather gets colder. It''s like my energy just drains away, and simple tasks feel so much harder.

I''ve been trying to combat this by:
- Using a light therapy lamp in the mornings
- Maintaining my exercise routine (even if it''s just indoor yoga)
- Making sure to get outside during lunch breaks
- Eating more warming, nutritious foods

But some days are still really tough. How do you all cope with seasonal mood changes? Any tips or strategies that work for you?

Sending warm thoughts to everyone who might be struggling with this too. We''re in this together! 🤗', 
'general', 'contemplative', 0, DATE_SUB(NOW(), INTERVAL 6 HOURS)),

-- General Post 4
(5, 'Finding Balance in a Busy World', 
'Does anyone else feel like they''re constantly juggling a million things? 🤹‍♀️

Between work, family, social commitments, and trying to maintain some semblance of self-care, I often feel like I''m barely keeping my head above water. 

I''ve been working on setting better boundaries and learning to say "no" to things that don''t align with my priorities. It''s harder than it sounds! There''s always this guilt that comes with putting myself first.

How do you find balance in your life? What strategies help you prioritize your mental health when everything feels urgent and important?

Would love to hear your thoughts and experiences! 💭', 
'general', 'overwhelmed', 0, DATE_SUB(NOW(), INTERVAL 3 HOURS));

-- =====================================================
-- SUPPORT CATEGORY POSTS
-- =====================================================

INSERT INTO community_posts (user_id, title, content, post_type, mood_tag, is_anonymous, created_at) VALUES

-- Support Post 1
(6, 'Struggling with Panic Attacks - Need Advice', 
'Hi everyone, I''m reaching out because I''ve been having more frequent panic attacks lately, and I''m feeling really overwhelmed. 😰

They seem to come out of nowhere - my heart starts racing, I can''t breathe properly, and I feel like I''m going to pass out. The worst part is the fear of having another one, which creates this awful cycle.

I''ve tried some breathing exercises I found online, but in the moment, it''s so hard to remember what to do. Has anyone found techniques that actually work during a panic attack?

I''m also wondering if I should talk to a professional. How did you know when it was time to seek help? Any recommendations for finding the right therapist?

Thank you for listening. This community means so much to me. 💙', 
'support', 'anxious', 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),

-- Support Post 2
(1, 'Feeling Isolated and Lonely', 
'I''ve been feeling really isolated lately, and I''m not sure how to break out of this cycle. 😔

Even when I''m around people, I feel disconnected and alone. It''s like there''s this invisible wall between me and everyone else. I want to reach out and connect, but I don''t know how, and I''m afraid of being a burden.

Social situations that used to feel natural now feel exhausting and fake. I find myself withdrawing more and more, which only makes the loneliness worse.

Has anyone else experienced this? How did you start to reconnect with others and rebuild those social connections? I''m open to any advice or just knowing that I''m not alone in feeling this way.

Sending love to anyone else who might be struggling with loneliness. 🤗', 
'support', 'lonely', 1, DATE_SUB(NOW(), INTERVAL 8 HOURS)),

-- Support Post 3
(7, 'Dealing with Work-Related Stress and Burnout', 
'I think I''m experiencing burnout, and I''m not sure what to do about it. 😵‍💫

Work has been incredibly demanding lately, and I feel like I''m constantly behind. I''m working longer hours, skipping breaks, and bringing stress home with me. My sleep is terrible, I''m irritable with my family, and I''ve lost interest in things I used to enjoy.

The worst part is feeling like I can''t take time off because there''s always something urgent that needs my attention. I know this isn''t sustainable, but I''m scared of falling behind or letting my team down.

How do you set boundaries at work? Have you ever had to have difficult conversations with your boss about workload? I''m worried about how it might affect my career, but I know something has to change.

Any advice would be greatly appreciated. Thank you for being such a supportive community. 🙏', 
'support', 'stressed', 0, DATE_SUB(NOW(), INTERVAL 4 HOURS)),

-- Support Post 4
(8, 'Coping with Loss and Grief', 
'I lost someone very important to me recently, and I''m struggling to navigate this grief. 💔

Everyone keeps telling me that "time heals all wounds" and that I need to "stay strong," but honestly, I don''t want to be strong right now. I want to feel sad. I want to miss them. I want to honor what they meant to me.

The hardest part is that grief isn''t linear. Some days I feel like I''m doing okay, and then something small - a song, a smell, a memory - brings me right back to that raw pain.

I''m trying to be patient with myself and allow myself to feel whatever comes up, but it''s scary not knowing when or if this pain will lessen.

If you''ve experienced loss, how did you navigate the grief process? What helped you honor your loved one while also taking care of yourself?

Thank you for holding space for difficult emotions in this community. 🕊️', 
'support', 'grieving', 0, DATE_SUB(NOW(), INTERVAL 2 HOURS));

-- =====================================================
-- CELEBRATION CATEGORY POSTS
-- =====================================================

INSERT INTO community_posts (user_id, title, content, post_type, mood_tag, is_anonymous, created_at) VALUES

-- Celebration Post 1
(2, '6 Months Therapy Milestone! 🎉', 
'I can''t believe I''m writing this, but today marks 6 months since I started therapy! 🎊

When I first walked into my therapist''s office, I was a mess. I could barely articulate what was wrong, and I felt so broken and hopeless. I honestly didn''t think therapy would help - I thought I was just "too damaged" to fix.

But here I am, 6 months later, and while I''m not "cured" (because that''s not how mental health works!), I feel like a completely different person. I''ve learned:

✨ How to identify and challenge negative thought patterns
✨ Healthy coping strategies for anxiety and stress
✨ The importance of setting boundaries
✨ How to practice self-compassion
✨ That healing isn''t linear, and that''s okay!

To anyone considering therapy but feeling scared or skeptical - it''s worth it. Finding the right therapist might take time, but when you do, it can be life-changing.

Thank you to this community for encouraging me to take that first step! 💙', 
'celebration', 'proud', 0, DATE_SUB(NOW(), INTERVAL 3 DAYS)),

-- Celebration Post 2
(3, 'I Finally Asked for Help at Work! 💪', 
'This might seem small to some people, but for me, this is HUGE! Today I finally had the courage to talk to my manager about my workload and ask for support. 🙌

I''ve been struggling for months, working late nights and weekends, feeling completely overwhelmed but too afraid to speak up. I kept thinking I should be able to handle everything on my own, and asking for help felt like admitting failure.

But you know what? My manager was incredibly understanding! She thanked me for being honest, acknowledged that my workload was unrealistic, and we''re now working together to redistribute some tasks and bring in additional support.

I walked out of that meeting feeling 10 pounds lighter. Why did I wait so long to have this conversation?

Key lessons learned:
🌟 Asking for help is a sign of strength, not weakness
🌟 Most people want to support you - you just have to let them know what you need
🌟 Suffering in silence helps no one
🌟 Advocating for yourself gets easier with practice

Here''s to setting better boundaries and prioritizing mental health! 🥳', 
'celebration', 'empowered', 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),

-- Celebration Post 3
(4, 'One Year Anxiety-Free! My Journey to Recovery 🌈', 
'Today marks exactly one year since my last major anxiety attack, and I had to share this milestone with you all! 🎈

A year ago, I was having daily panic attacks, couldn''t leave my house without feeling terrified, and genuinely thought I''d never feel "normal" again. Anxiety had completely taken over my life.

My recovery journey included:
🌱 Therapy (CBT was a game-changer for me)
🌱 Medication (took a while to find the right one)
🌱 Regular exercise (started with just 5-minute walks)
🌱 Meditation and mindfulness practices
🌱 Building a strong support network
🌱 Learning to challenge catastrophic thinking
🌱 Gradual exposure to feared situations

The most important thing I learned: recovery is possible, but it takes time, patience, and self-compassion. There were setbacks, bad days, and moments when I wanted to give up. But I kept going, one day at a time.

To anyone currently struggling with anxiety: you are stronger than you know, and this won''t last forever. Keep fighting, keep seeking help, and be gentle with yourself. 

You''ve got this! 💪✨', 
'celebration', 'grateful', 0, DATE_SUB(NOW(), INTERVAL 5 HOURS)),

-- Celebration Post 4
(5, 'Completed My First 5K for Mental Health Awareness! 🏃‍♀️', 
'I DID IT! I just finished my first ever 5K run, and I''m still buzzing with excitement! 🏃‍♀️💨

This might not seem like a big deal to some, but for someone who used to get winded walking up stairs, this feels like climbing Mount Everest! 

I signed up for this mental health awareness run 3 months ago as a way to challenge myself and support a cause close to my heart. At the time, I could barely run for 30 seconds without stopping.

But with consistent training (shoutout to the Couch to 5K app!), lots of encouragement from this community, and pure determination, I crossed that finish line today! 

The best part? I raised $500 for mental health research and met so many amazing people who are also passionate about mental health advocacy.

Running has become my new therapy. There''s something about the rhythm of my feet hitting the pavement that quiets my anxious mind and makes me feel strong and capable.

Next goal: 10K! Who wants to train with me? 🏃‍♂️🏃‍♀️

Thank you all for cheering me on throughout this journey! This community is the best! 🥰', 
'celebration', 'accomplished', 0, DATE_SUB(NOW(), INTERVAL 2 HOURS));

-- =====================================================
-- Add some sample reactions and comments
-- =====================================================

-- Sample post reactions
INSERT INTO post_reactions (post_id, user_id, reaction_type, created_at) VALUES
(1, 3, 'heart', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 4, 'like', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 5, 'support', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 1, 'like', DATE_SUB(NOW(), INTERVAL 12 HOURS)),
(2, 4, 'heart', DATE_SUB(NOW(), INTERVAL 12 HOURS)),
(3, 2, 'support', DATE_SUB(NOW(), INTERVAL 6 HOURS)),
(3, 5, 'heart', DATE_SUB(NOW(), INTERVAL 6 HOURS)),
(9, 1, 'celebrate', DATE_SUB(NOW(), INTERVAL 2 DAYS)),
(9, 3, 'heart', DATE_SUB(NOW(), INTERVAL 2 DAYS)),
(9, 4, 'celebrate', DATE_SUB(NOW(), INTERVAL 2 DAYS)),
(9, 5, 'like', DATE_SUB(NOW(), INTERVAL 2 DAYS)),
(12, 2, 'celebrate', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(12, 3, 'heart', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(12, 6, 'celebrate', DATE_SUB(NOW(), INTERVAL 1 HOUR));

-- Sample comments
INSERT INTO post_comments (post_id, user_id, content, created_at) VALUES
(1, 3, 'Welcome to the community! 🤗 You''ve taken such a brave first step. My advice would be to be patient with yourself - healing isn''t linear, and that''s perfectly okay. You''re not alone in this journey!', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 4, 'So glad you''re here! This community has been a lifeline for me. One thing that really helped me was starting a mood journal - it helped me identify patterns and triggers. Wishing you all the best! 💙', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 1, 'This is so inspiring! I love how you''re focusing on small, manageable changes. I''ve been trying to implement a gratitude practice too - it really does make a difference over time. Thank you for sharing! ✨', DATE_SUB(NOW(), INTERVAL 12 HOURS)),
(9, 3, 'This made me tear up! 😭 So proud of you for sticking with therapy and doing the hard work. Your progress is incredible and gives me hope for my own journey. Thank you for sharing this! 🎉', DATE_SUB(NOW(), INTERVAL 2 DAYS)),
(9, 4, 'Six months is amazing! I''m about to start therapy myself and feeling nervous. Your post gives me so much encouragement. Thank you for being so open about your experience! 💪', DATE_SUB(NOW(), INTERVAL 2 DAYS)),
(12, 2, 'CONGRATULATIONS! 🎊 This is absolutely incredible! I''m so inspired by your dedication and the fact that you raised money for mental health research too. You''re amazing! 🏃‍♀️✨', DATE_SUB(NOW(), INTERVAL 1 HOUR));

-- =====================================================
-- ADDITIONAL DIVERSE POSTS
-- =====================================================

INSERT INTO community_posts (user_id, title, content, post_type, mood_tag, is_anonymous, created_at) VALUES

-- More General Posts
(6, 'The Art of Self-Forgiveness',
'I''ve been reflecting a lot lately on how hard I am on myself. 🤔

We often extend compassion and understanding to our friends when they make mistakes, but when it comes to ourselves? We become our own worst critics. I''ve been practicing self-forgiveness, and it''s harder than I thought it would be.

Some things I''m learning:
• Mistakes don''t define my worth as a person
• I''m human, and humans are imperfect by nature
• Self-compassion isn''t self-indulgence - it''s necessary for growth
• Forgiving myself doesn''t mean excusing harmful behavior

How do you practice self-forgiveness? What helps you treat yourself with the same kindness you''d show a good friend?',
'general', 'reflective', 0, DATE_SUB(NOW(), INTERVAL 4 DAYS)),

(7, 'Mindful Moments in Everyday Life',
'I''ve been trying to incorporate more mindfulness into my daily routine, not just during formal meditation sessions. 🧘‍♀️

Some of my favorite mindful moments:
🌅 Really tasting my morning coffee instead of rushing through it
🚶‍♀️ Feeling my feet on the ground during walks
🍽️ Eating lunch without scrolling through my phone
🌙 Taking three deep breaths before bed

These tiny moments of presence have been surprisingly powerful. They help me feel more grounded and less scattered throughout the day.

What are your favorite ways to practice mindfulness in everyday activities?',
'general', 'peaceful', 0, DATE_SUB(NOW(), INTERVAL 12 HOURS)),

-- More Support Posts
(1, 'Struggling with Perfectionism',
'Does anyone else struggle with perfectionism to the point where it''s paralyzing? 😓

I find myself procrastinating on important tasks because I''m terrified they won''t be "perfect." I''ll spend hours on something that should take 30 minutes, or worse, I won''t start at all because I''m convinced I''ll fail.

This perfectionism is affecting my work, relationships, and mental health. I know logically that "perfect" doesn''t exist, but emotionally, I can''t seem to let go of these impossible standards.

Has anyone found strategies that help with perfectionist tendencies? How do you embrace "good enough" when your brain is screaming that it needs to be flawless?

I''d really appreciate any insights or experiences you''re willing to share. 💙',
'support', 'frustrated', 1, DATE_SUB(NOW(), INTERVAL 2 DAYS)),

(8, 'Feeling Overwhelmed by Social Media',
'Is anyone else feeling drained by social media lately? 📱😵

I find myself mindlessly scrolling for hours, comparing my life to everyone else''s highlight reels, and feeling worse about myself afterward. The constant stream of news, opinions, and "perfect" lives is overwhelming my brain.

I''ve tried taking breaks, but I always end up going back. It''s like I''m addicted to the dopamine hit, even though it makes me feel terrible overall.

How do you maintain a healthy relationship with social media? Have you found ways to use it that actually enhance your wellbeing rather than detract from it?

Looking for practical strategies and maybe some accountability partners! 🤝',
'support', 'overwhelmed', 0, DATE_SUB(NOW(), INTERVAL 18 HOURS)),

-- More Celebration Posts
(3, 'I Set a Boundary and the World Didn''t End! 🎉',
'Small victory alert! I actually said "no" to something today without feeling guilty about it! 🙌

My friend asked me to help her move this weekend, but I already had plans for some much-needed self-care time. Old me would have immediately said yes and then resented it later.

But today I said: "I''d love to help, but I already have commitments this weekend. Could we find another time, or is there another way I can support you?"

And you know what? She was totally understanding! She thanked me for being honest and we figured out an alternative solution.

Why did I think the world would end if I prioritized my own needs? 🤷‍♀️

Here''s to more boundary-setting and less people-pleasing! Who else is working on this? 💪',
'celebration', 'empowered', 0, DATE_SUB(NOW(), INTERVAL 6 HOURS)),

(5, 'Three Months of Daily Meditation! 🧘‍♂️',
'I can''t believe I''m saying this, but I''ve meditated every single day for the past three months! 🎊

I started with just 2 minutes a day using a meditation app, and now I''m up to 15-20 minutes. Some days it''s peaceful and blissful, other days my mind is like a monkey swinging from branch to branch - and that''s okay!

What I''ve noticed:
✨ I''m less reactive to stressful situations
✨ I sleep better at night
✨ I''m more aware of my thoughts and emotions
✨ I have more patience with myself and others
✨ I feel more connected to the present moment

The key for me was starting small and being consistent rather than perfect. Missing a day here and there doesn''t matter - what matters is coming back to it.

To anyone thinking about starting a meditation practice: you don''t need to sit in lotus position for an hour. Even 2 minutes counts! 🌟',
'celebration', 'accomplished', 0, DATE_SUB(NOW(), INTERVAL 8 HOURS));

-- =====================================================
-- More reactions and comments for new posts
-- =====================================================

INSERT INTO post_reactions (post_id, user_id, reaction_type, created_at) VALUES
(13, 2, 'heart', DATE_SUB(NOW(), INTERVAL 3 DAYS)),
(13, 4, 'like', DATE_SUB(NOW(), INTERVAL 3 DAYS)),
(14, 1, 'peaceful', DATE_SUB(NOW(), INTERVAL 10 HOURS)),
(14, 3, 'like', DATE_SUB(NOW(), INTERVAL 10 HOURS)),
(15, 3, 'support', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(15, 6, 'heart', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(16, 2, 'support', DATE_SUB(NOW(), INTERVAL 15 HOURS)),
(17, 1, 'celebrate', DATE_SUB(NOW(), INTERVAL 5 HOURS)),
(17, 4, 'empowered', DATE_SUB(NOW(), INTERVAL 5 HOURS)),
(18, 2, 'celebrate', DATE_SUB(NOW(), INTERVAL 7 HOURS)),
(18, 6, 'peaceful', DATE_SUB(NOW(), INTERVAL 7 HOURS));

INSERT INTO post_comments (post_id, user_id, content, created_at) VALUES
(13, 2, 'This resonates so deeply with me! I''m also working on self-forgiveness and it''s such a challenging but important practice. Thank you for sharing your insights! 💙', DATE_SUB(NOW(), INTERVAL 3 DAYS)),
(14, 1, 'I love this approach to mindfulness! I''m going to try the mindful coffee drinking tomorrow morning. Sometimes the simplest practices are the most powerful. 🌅☕', DATE_SUB(NOW(), INTERVAL 10 HOURS)),
(17, 4, 'YES! 🙌 Boundary setting is so hard but so necessary. I''m proud of you for prioritizing your needs. Your friend''s understanding response shows that good people respect boundaries!', DATE_SUB(NOW(), INTERVAL 5 HOURS)),
(18, 2, 'Three months is incredible! I''ve been wanting to start a meditation practice but keep making excuses. Your post is the motivation I needed to finally begin. Starting with 2 minutes today! 🧘‍♀️', DATE_SUB(NOW(), INTERVAL 7 HOURS));

-- =====================================================
-- Success message
-- =====================================================

SELECT 'Sample community posts have been successfully added!' as message;
SELECT COUNT(*) as total_posts FROM community_posts;
SELECT post_type, COUNT(*) as count FROM community_posts GROUP BY post_type;
SELECT mood_tag, COUNT(*) as count FROM community_posts WHERE mood_tag IS NOT NULL GROUP BY mood_tag ORDER BY count DESC;
