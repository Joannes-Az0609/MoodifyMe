<?php
/**
 * Run Voice Messages Schema Update
 * This script updates the database to support voice messages
 */

require_once 'config.php';
require_once 'includes/db_connect.php';

echo "Starting voice messages schema update...\n";

try {
    // Read the schema file
    $schemaFile = 'database/voice_messages_schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }
    
    $sql = file_get_contents($schemaFile);
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $successCount = 0;
    $errorCount = 0;
    
    // Helper function to check if column exists
    function columnExists($conn, $table, $column) {
        $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $result && $result->num_rows > 0;
    }

    // Helper function to check if table exists
    function tableExists($conn, $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        return $result && $result->num_rows > 0;
    }

    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0 || strpos($statement, 'USE ') === 0) {
            continue;
        }

        echo "Executing: " . substr($statement, 0, 50) . "...\n";

        // Check for ALTER TABLE ADD COLUMN statements
        if (preg_match('/ALTER TABLE (\w+)\s+ADD COLUMN (\w+)/', $statement, $matches)) {
            $table = $matches[1];
            $column = $matches[2];

            if (columnExists($conn, $table, $column)) {
                echo "✓ Column $column already exists in $table\n";
                $successCount++;
                continue;
            }
        }

        // Check for CREATE TABLE statements
        if (preg_match('/CREATE TABLE.*?(\w+)\s*\(/', $statement, $matches)) {
            $table = $matches[1];

            if (tableExists($conn, $table)) {
                echo "✓ Table $table already exists\n";
                $successCount++;
                continue;
            }
        }

        if ($conn->query($statement)) {
            $successCount++;
            echo "✓ Success\n";
        } else {
            $errorCount++;
            echo "✗ Error: " . $conn->error . "\n";

            // Continue with other statements even if one fails
        }
    }
    
    echo "\nSchema update completed!\n";
    echo "Successful statements: $successCount\n";
    echo "Failed statements: $errorCount\n";
    
    // Verify the changes
    echo "\nVerifying changes...\n";
    
    // Check if columns were added to messages table
    $result = $conn->query("DESCRIBE messages");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    $expectedColumns = ['message_content_type', 'voice_file_path', 'voice_duration', 'voice_file_size', 'voice_transcription'];
    $foundColumns = array_intersect($expectedColumns, $columns);
    
    echo "Messages table columns added: " . count($foundColumns) . "/" . count($expectedColumns) . "\n";
    foreach ($foundColumns as $col) {
        echo "✓ $col\n";
    }
    
    // Check if voice_messages table exists
    $result = $conn->query("SHOW TABLES LIKE 'voice_messages'");
    if ($result->num_rows > 0) {
        echo "✓ voice_messages table created\n";
    } else {
        echo "✗ voice_messages table not found\n";
    }
    
    // Check if voice_message_analytics table exists
    $result = $conn->query("SHOW TABLES LIKE 'voice_message_analytics'");
    if ($result->num_rows > 0) {
        echo "✓ voice_message_analytics table created\n";
    } else {
        echo "✗ voice_message_analytics table not found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nVoice messages schema update completed successfully!\n";
?>
