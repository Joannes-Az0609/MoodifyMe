<?php
/**
 * MoodifyMe - Database Connection
 * Establishes connection to the MySQL database
 */

// Include configuration
require_once dirname(__DIR__) . '/config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");
