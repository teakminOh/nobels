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

if (!preg_match('/Bearer (.+)/', $authHeader, $matches)) {
  echo json_encode(['success' => false, 'message' => 'Neautorizovaný prístup']);
  exit;
}

$token = $matches[1];
$key = 'your-secret-key-here'; // This key is correct

try {
  $decoded = JWT::decode($token, new \Firebase\JWT\Key($key, 'HS256'));
  $userId = $decoded->sub;

  // Read JSON input from client
  $input = json_decode(file_get_contents('php://input'), true);
  $currentPassword = $input['currentPassword'] ?? '';
  $newPassword = $input['newPassword'] ?? '';

  // Connect to the database using config parameters
  $pdo = connectDatabase($hostname, $database, $username, $password);
  $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
  $stmt->execute([$userId]);
  $storedHash = $stmt->fetchColumn();

  // Verify current password against stored hash
  if (!$storedHash || !password_verify($currentPassword, $storedHash)) {
    echo json_encode(['success' => false, 'message' => 'Nesprávne súčasné heslo']);
    exit;
  }

  // Hash the new password and update it in the database
  $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
  $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
  $stmt->execute([$newHash, $userId]);

  echo json_encode(['success' => true]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Chyba: ' . $e->getMessage()]);
}
