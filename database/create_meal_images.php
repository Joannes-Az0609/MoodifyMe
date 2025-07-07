<?php
/**
 * MoodifyMe - Create Meal Image Placeholders
 * This script creates placeholder images for African meals
 */

// Create meals directory if it doesn't exist
$mealsDir = '../assets/images/meals';
if (!is_dir($mealsDir)) {
    mkdir($mealsDir, 0755, true);
    echo "Created meals directory: {$mealsDir}\n";
}

// List of meal images we need
$mealImages = [
    'jollof_rice.jpg',
    'doro_wat.jpg', 
    'bobotie.jpg',
    'mint_tea.jpg',
    'ugali_sukuma.jpg',
    'harira_soup.jpg',
    'rooibos_tea.jpg',
    'thieboudienne.jpg',
    'suya.jpg',
    'coffee_ceremony.jpg',
    'mandazi.jpg',
    'tagine.jpg',
    'biltong_rooibos.jpg',
    'bunny_chow.jpg',
    'kelewele.jpg',
    'party_jollof.jpg',
    'malva_pudding.jpg',
    'peri_peri_chicken.jpg',
    'koeksisters.jpg'
];

// Create simple placeholder images (if GD extension is available)
if (extension_loaded('gd')) {
    foreach ($mealImages as $imageName) {
        $imagePath = $mealsDir . '/' . $imageName;
        
        if (!file_exists($imagePath)) {
            // Create a 400x300 placeholder image
            $image = imagecreate(400, 300);
            
            // Define colors
            $background = imagecolorallocate($image, 245, 245, 245); // Light gray
            $textColor = imagecolorallocate($image, 100, 100, 100);  // Dark gray
            $borderColor = imagecolorallocate($image, 200, 200, 200); // Border gray
            
            // Fill background
            imagefill($image, 0, 0, $background);
            
            // Add border
            imagerectangle($image, 0, 0, 399, 299, $borderColor);
            
            // Add text
            $mealName = str_replace(['_', '.jpg'], [' ', ''], $imageName);
            $mealName = ucwords($mealName);
            
            // Center the text
            $fontSize = 3;
            $textWidth = imagefontwidth($fontSize) * strlen($mealName);
            $textHeight = imagefontheight($fontSize);
            $x = (400 - $textWidth) / 2;
            $y = (300 - $textHeight) / 2;
            
            imagestring($image, $fontSize, $x, $y, $mealName, $textColor);
            
            // Add "African Meal" subtitle
            $subtitle = "African Meal";
            $subtitleWidth = imagefontwidth(2) * strlen($subtitle);
            $subX = (400 - $subtitleWidth) / 2;
            $subY = $y + 30;
            
            imagestring($image, 2, $subX, $subY, $subtitle, $textColor);
            
            // Save image
            imagejpeg($image, $imagePath, 80);
            imagedestroy($image);
            
            echo "Created placeholder: {$imageName}\n";
        } else {
            echo "Image already exists: {$imageName}\n";
        }
    }
    
    echo "\nPlaceholder images created successfully!\n";
    echo "You can replace these with actual food photos later.\n";
    
} else {
    echo "GD extension not available. Creating empty files instead...\n";
    
    // Create empty files as placeholders
    foreach ($mealImages as $imageName) {
        $imagePath = $mealsDir . '/' . $imageName;
        if (!file_exists($imagePath)) {
            file_put_contents($imagePath, '');
            echo "Created empty placeholder: {$imageName}\n";
        }
    }
    
    echo "\nEmpty placeholder files created.\n";
    echo "Please add actual meal images to: {$mealsDir}\n";
}

echo "\nRecommended image sources:\n";
echo "- Unsplash.com (search for African food)\n";
echo "- Pexels.com (free stock photos)\n";
echo "- Your own photos of African meals\n";
echo "- Creative Commons licensed images\n";

echo "\nImage specifications:\n";
echo "- Recommended size: 400x300 pixels\n";
echo "- Format: JPG or PNG\n";
echo "- Keep file sizes under 200KB for fast loading\n";
?>
