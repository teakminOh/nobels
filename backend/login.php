<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

ini_set('display_errors', 0); // Don’t show errors in response
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php'; // DB config
require_once __DIR__ . '/utilities.php'; // connectDatabase

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RobThree\Auth\TwoFactorAuth;

$secretKey = 'your-secret-key-here'; // Replace with a secure key

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$pdo = connectDatabase($hostname, $database, $username, $password);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $twoFaCode = $data['2fa'] ?? '';

    error_log("POST data received: " . json_encode($data));

    if (empty($email) || empty($password) || empty($twoFaCode)) {
        echo json_encode(['success' => false, 'message' => 'Vyplňte všetky polia vrátane 2FA kódu.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('SELECT id, fullname, email, password, `2fa_code`, created_at FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Nesprávny e-mail.']);
            exit;
        }

        if (!password_verify($password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Nesprávne heslo.']);
            exit;
        }

        $tfa = new TwoFactorAuth(new RobThree\Auth\Providers\Qr\EndroidQrCodeProvider());
        if (!$tfa->verifyCode($user['2fa_code'], $twoFaCode)) {
            echo json_encode(['success' => false, 'message' => 'Nesprávny 2FA kód.']);
            exit;
        }
         // Save the login time in the users_login table
         $loginType = 'email'; // or any other identifier for your login method
         $insertStmt = $pdo->prepare("INSERT INTO users_login (user_id, login_type, email, fullname, login_time) VALUES (:user_id, :login_type, :email, :fullname, NOW())");
         $insertStmt->execute([
             'user_id'   => $user['id'],
             'login_type'=> $loginType,
             'email'     => $user['email'],
             'fullname'  => $user['fullname']
         ]);

        $payload = [
            'iat' => time(),
            'exp' => time() + 7200,
            'sub' => $user['id'],
            'email' => $user['email'],
            'fullname' => $user['fullname'],
            'created_at' => $user['created_at'],
            'gid' => null
        ];
        $jwt = JWT::encode($payload, $secretKey, 'HS256');
        echo json_encode([
            'success' => true,
            'token' => $jwt,
            'fullname' => $user['fullname'],
            'email' => $user['email'],
            'created_at' => $user['created_at']
        ]);
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer (.+)/', $authHeader, $matches)) {
        $token = $matches[1];
        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            echo json_encode([
                'success' => true,
                'fullname' => $decoded->fullname,
                'email' => $decoded->email,
                'created_at' => $decoded->created_at,
                'gid' => $decoded->gid ?? null
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Neplatný token: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Nie ste prihlásený.']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Metóda nie je povolená.']);
exit;
?>