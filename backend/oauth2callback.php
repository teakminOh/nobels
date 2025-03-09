<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Credentials: true');

require_once __DIR__ . '/vendor/autoload.php';
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
    $payload = [
        'iat' => time(),
        'exp' => time() + 7200,
        'sub' => $userInfo->id,
        'email' => $userInfo->email,
        'fullname' => $userInfo->name,
        'created_at' => null,
        'gid' => $userInfo->id
    ];
    $jwt = JWT::encode($payload, $secretKey, 'HS256');
    header('Location: http://localhost:3000/?token=' . urlencode($jwt));
    exit;
}

if (isset($_GET['error'])) {
    echo json_encode(['success' => false, 'message' => 'Autorizačná chyba: ' . $_GET['error']]);
    exit;
}
?>