<?php
// Function to load .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        die("No .env file found at $path");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue; // Skip empty lines & comments
        
        $pair = explode('=', $line, 2);
        if (count($pair) === 2) {
            list($key, $value) = $pair;
            putenv("$key=$value");
        }
    }
}

// Load environment variables
loadEnv(__DIR__ . '/.env');

// Get credentials securely
$hostname = getenv('DB_HOST') ?: 'localhost';
$database = getenv('DB_DATABASE') ?: 'nobels';
$username = getenv('DB_USERNAME') ?: 'xoh';
$password = getenv('DB_PASSWORD') ?: '';

function connectDatabase($hostname, $database, $username, $password) {
    try {
        $conn = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        return null;
    }
}

