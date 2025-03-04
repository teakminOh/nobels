<?php
require_once __DIR__ . '/config.php';

try {
    $db = connectDatabase($hostname, $database, $username, $password);

    // Disable foreign key checks to avoid constraint errors
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Truncate all tables
    $db->exec("TRUNCATE TABLE laureates_prizes");
    $db->exec("TRUNCATE TABLE laureates_countries");
    $db->exec("TRUNCATE TABLE prizes");
    $db->exec("TRUNCATE TABLE prize_details");
    $db->exec("TRUNCATE TABLE laureates");
    $db->exec("TRUNCATE TABLE countries");

    // Re-enable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "Database cleared successfully!\n";
} catch (Exception $e) {
    die("Error clearing database: " . $e->getMessage());
}