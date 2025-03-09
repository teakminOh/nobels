<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Credentials: true');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php'; // Provides DB config and connectDatabase()
use Firebase\JWT\JWT;
use Google\Client;
use Google\Service\Oauth2;

$secretKey = 'your-secret-key-here';

$client = new Client();
$client->setAuthConfig(__DIR__ . '/../../client_secret.json');
$client->setRedirectUri('https://node87.webte.fei.stuba.sk/nobels/backend/oauth2callback.php');
$client->addScope(['email', 'profile']);
$client->setAccessType('offline');

if (!isset($_GET['code']) && !isset($_GET['error'])) {
    $auth_url = $client->createAuthUrl();
    echo json_encode(['success' => true, 'auth_url' => $auth_url]);
    exit;
}

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (isset($token['error'])) {
        echo json_encode(['success' => false, 'message' => 'Token error: ' . $token['error']]);
        exit;
    }

    $oauth = new Oauth2($client);
    $userInfo = $oauth->userinfo->get();

    try {
        // Connect to the database
        $pdo = connectDatabase($hostname, $database, $username, $password);
        $pdo->beginTransaction();

        // Check if user already exists in the "users" table (using email as unique identifier)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$userInfo->email]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingUser) {
            // Insert the new user into the users table.
            // For Google users, password and 2fa_code may remain null.
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, `2fa_code`, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([
                $userInfo->name,
                $userInfo->email,
                null,    // password
                null     // 2fa_code
            ]);
            $userId = $pdo->lastInsertId();
        } else {
            $userId = $existingUser['id'];
        }

        // Insert login history into the users_login table
        $stmt = $pdo->prepare("INSERT INTO users_login (user_id, login_type, email, fullname, login_time) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([
            $userId,             // use the local user ID
            'google',            // login_type
            $userInfo->email,
            $userInfo->name
        ]);

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }

    // Prepare JWT payload using the local user ID from the users table
    $payload = [
        'iat'         => time(),
        'exp'         => time() + 7200,
        'sub'         => $userId,
        'email'       => $userInfo->email,
        'fullname'    => $userInfo->name,
        'created_at'  => null, // or include user creation time if available
        'gid'         => $userInfo->id
    ];
    $jwt = JWT::encode($payload, $secretKey, 'HS256');
    
    header('Location: http://localhost:3000/?token=' . urlencode($jwt));
    exit;
}

if (isset($_GET['error'])) {
    echo json_encode(['success' => false, 'message' => 'Autorizačná chyba: ' . $_GET['error']]);
    exit;
}
