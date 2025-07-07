<?php
/**
 * Simple ping endpoint for connection testing
 * Used by PWA to check if server is reachable
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Simple response to confirm server is reachable
echo json_encode([
    'status' => 'ok',
    'timestamp' => time(),
    'message' => 'Server is reachable'
]);
?>
