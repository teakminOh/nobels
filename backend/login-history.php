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
$key = 'your-secret-key-here';

try {
  $decoded = JWT::decode($token, new \Firebase\JWT\Key($key, 'HS256'));
  $userId = $decoded->sub;

  $pdo = connectDatabase($hostname, $database, $username, $password);
  $stmt = $pdo->prepare('
  SELECT user_id, login_time, login_type FROM users_login
  WHERE user_id = ? ORDER BY login_time DESC');
  $stmt->execute([$userId]);
  $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['success' => true, 'history' => $history]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Chyba: ' . $e->getMessage()]);
}
