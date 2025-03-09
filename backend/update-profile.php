<?php
require 'vendor/autoload.php';
require 'config.php';
use Firebase\JWT\JWT;

// Set CORS headers before any output
header('Access-Control-Allow-Origin: http://localhost:3000'); // Allow only this origin
header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // Allowed HTTP methods
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Allowed headers
header('Access-Control-Allow-Credentials: true'); // Allow credentials (cookies, auth headers, etc.)

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

header('Content-Type: application/json');

// Get request headers
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';

// Check for Bearer token format
if (!preg_match('/Bearer (.+)/', $authHeader, $matches)) {
  echo json_encode(['success' => false, 'message' => 'Neautorizovaný prístup']);
  exit;
}

$token = $matches[1];
$key = 'your-secret-key-here'; // The key here is correct

try {
  $decoded = JWT::decode($token, new \Firebase\JWT\Key($key, 'HS256'));
  $userId = $decoded->sub;

  // Get JSON input from client
  $input = json_decode(file_get_contents('php://input'), true);
  $newFullname = $input['fullname'] ?? '';

  // Connect to the database using your config parameters
  $pdo = connectDatabase($hostname, $database, $username, $password);
  $stmt = $pdo->prepare('UPDATE users SET fullname = ? WHERE id = ?');
  $stmt->execute([$newFullname, $userId]);

  // Optionally issue a new token with the updated fullname
  $payload = (array)$decoded;
  $payload['fullname'] = $newFullname;
  $newToken = JWT::encode($payload, $key, 'HS256');

  echo json_encode(['success' => true, 'token' => $newToken]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Chyba: ' . $e->getMessage()]);
}
