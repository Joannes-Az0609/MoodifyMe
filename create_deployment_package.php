<?php
/**
 * MoodifyMe - Create Deployment Package for InfinityFree
 * This script creates a ZIP file ready for upload to InfinityFree
 */

echo "Creating MoodifyMe deployment package for InfinityFree...\n";

// Define source and destination
$sourceDir = __DIR__;
$zipFile = __DIR__ . '/moodifyme-infinityfree.zip';

// Files and directories to exclude
$excludePatterns = [
    '.git',
    '.gitignore',
    'node_modules',
    'vendor',
    'composer.lock',
    'package-lock.json',
    '*.log',
    'debug_*',
    'test_*',
    'RENDER_DEPLOYMENT.md',
    'VERCEL_DEPLOYMENT.md',
    'config.render.php',
    'config.vercel.php',
    'render.yaml',
    'vercel.json',
    'Dockerfile',
    'create_deployment_package.php',
    'moodifyme-infinityfree.zip'
];

/**
 * Check if a file should be excluded
 */
function shouldExclude($path, $excludePatterns) {
    $relativePath = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $path);
    $relativePath = str_replace('\\', '/', $relativePath);
    
    foreach ($excludePatterns as $pattern) {
        if (fnmatch($pattern, basename($path)) || 
            fnmatch($pattern, $relativePath) ||
            strpos($relativePath, $pattern) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * Add files to ZIP recursively
 */
function addFilesToZip($zip, $dir, $excludePatterns, $basePath = '') {
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
        $relativePath = $basePath . $file;
        
        if (shouldExclude($fullPath, $excludePatterns)) {
            echo "Excluding: $relativePath\n";
            continue;
        }
        
        if (is_dir($fullPath)) {
            echo "Adding directory: $relativePath/\n";
            $zip->addEmptyDir($relativePath);
            addFilesToZip($zip, $fullPath, $excludePatterns, $relativePath . '/');
        } else {
            echo "Adding file: $relativePath\n";
            $zip->addFile($fullPath, $relativePath);
        }
    }
}

// Create ZIP file
if (file_exists($zipFile)) {
    unlink($zipFile);
}

$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) {
    die("Cannot create ZIP file: $zipFile\n");
}

// Add files to ZIP
addFilesToZip($zip, $sourceDir, $excludePatterns);

// Close ZIP file
$zip->close();

echo "\n✅ Deployment package created successfully!\n";
echo "📦 File: moodifyme-infinityfree.zip\n";
echo "📁 Size: " . round(filesize($zipFile) / 1024 / 1024, 2) . " MB\n";

echo "\n🚀 Next steps:\n";
echo "1. Download the ZIP file: moodifyme-infinityfree.zip\n";
echo "2. Update config.infinityfree.php with your database details\n";
echo "3. Upload to InfinityFree File Manager (htdocs folder)\n";
echo "4. Extract the ZIP file in htdocs\n";
echo "5. Run migration: yourdomain.epizy.com/database/migrate.php?migrate=1\n";

echo "\n📋 Don't forget to:\n";
echo "- Update database credentials in config.infinityfree.php\n";
echo "- Update APP_URL in config.infinityfree.php\n";
echo "- Update Google OAuth redirect URI\n";
echo "- Test all functionality after deployment\n";

echo "\n🎉 Ready for InfinityFree deployment!\n";
?>
