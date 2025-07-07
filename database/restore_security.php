<?php
/**
 * MoodifyMe - Restore Database Security
 * This script restores the .htaccess security restriction for the database directory
 */

echo "<h2>MoodifyMe - Restore Database Security</h2>\n";

$htaccessPath = '../.htaccess';

if (!file_exists($htaccessPath)) {
    echo "<p style='color: red;'>❌ .htaccess file not found!</p>\n";
    exit;
}

// Read the current .htaccess content
$content = file_get_contents($htaccessPath);

// Check if database restriction is commented out
if (strpos($content, '# RewriteRule ^database/ - [F,L]  # Temporarily commented out') !== false) {
    // Restore the security restriction
    $newContent = str_replace(
        '    # RewriteRule ^database/ - [F,L]  # Temporarily commented out',
        '    RewriteRule ^database/ - [F,L]',
        $content
    );
    
    if (file_put_contents($htaccessPath, $newContent)) {
        echo "<p style='color: green;'>✅ Database security restriction restored!</p>\n";
        echo "<p>The database directory is now protected again.</p>\n";
        echo "<p><strong>Security Status:</strong> ✅ SECURE</p>\n";
        
        echo "<h3>What was restored:</h3>\n";
        echo "<ul>\n";
        echo "<li>✅ Direct access to <code>/database/</code> directory is now blocked</li>\n";
        echo "<li>✅ Database files are protected from web access</li>\n";
        echo "<li>✅ Your application security is restored</li>\n";
        echo "</ul>\n";
        
        echo "<h3>Test the security:</h3>\n";
        echo "<p>Try accessing: <a href='../database/reset_users_and_restrictions.php' target='_blank'>database/reset_users_and_restrictions.php</a></p>\n";
        echo "<p>You should now see a <strong>403 Forbidden</strong> error (which is correct!).</p>\n";
        
    } else {
        echo "<p style='color: red;'>❌ Failed to write to .htaccess file. Check file permissions.</p>\n";
    }
} else if (strpos($content, 'RewriteRule ^database/ - [F,L]') !== false && strpos($content, '# RewriteRule ^database/ - [F,L]') === false) {
    echo "<p style='color: green;'>✅ Database security is already active!</p>\n";
    echo "<p><strong>Security Status:</strong> ✅ SECURE</p>\n";
    echo "<p>No changes needed - your database directory is properly protected.</p>\n";
} else {
    echo "<p style='color: orange;'>⚠️ Unexpected .htaccess configuration detected.</p>\n";
    echo "<p>Please manually check your .htaccess file.</p>\n";
    echo "<p>The database restriction line should be:</p>\n";
    echo "<code>RewriteRule ^database/ - [F,L]</code>\n";
}

echo "<hr>\n";
echo "<p><em>This script helps maintain your application's security by ensuring database files are not directly accessible via web browser.</em></p>\n";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    line-height: 1.6;
}

h2 {
    color: #333;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
}

h3 {
    color: #555;
    margin-top: 30px;
}

p {
    margin: 10px 0;
}

ul {
    margin: 15px 0;
    padding-left: 30px;
}

li {
    margin: 5px 0;
}

code {
    background-color: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
}

hr {
    margin: 30px 0;
    border: none;
    border-top: 1px solid #ddd;
}
</style>
