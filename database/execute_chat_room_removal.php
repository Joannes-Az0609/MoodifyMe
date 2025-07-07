<?php
// Execute chat room removal script
require_once '../includes/db_connect.php';

try {
    echo "Starting chat room removal process...\n";
    
    // Read the SQL script
    $sql = file_get_contents('remove_chat_rooms.sql');
    
    if (!$sql) {
        throw new Exception("Could not read remove_chat_rooms.sql file");
    }
    
    // Execute the multi-query
    if ($conn->multi_query($sql)) {
        do {
            // Store first result set
            if ($result = $conn->store_result()) {
                while ($row = $result->fetch_assoc()) {
                    print_r($row);
                }
                $result->free();
            }
            
            // Check for more results
            if ($conn->more_results()) {
                echo "More results available...\n";
            }
        } while ($conn->next_result());
        
        echo "✅ Chat room removal completed successfully!\n";
    } else {
        throw new Exception("Error executing SQL: " . $conn->error);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
