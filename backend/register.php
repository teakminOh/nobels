<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

session_start();

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: http://localhost:3000');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Credentials: true');
    exit;
}

// Check if the user is already logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    echo json_encode([
        'success' => false,
        'message' => 'Už ste prihlásený.',
        'redirect' => 'index.php'
    ]);
    exit;
}

// Load dependencies
try {
    require_once __DIR__ . "/config.php"; // Loads connectDatabase function
    require_once __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/utilities.php';
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load dependencies: ' . $e->getMessage()
    ]);
    file_put_contents(__DIR__ . '/debug.log', "Dependency error: " . $e->getMessage() . "\n", FILE_APPEND);
    exit;
}

// Initialize database connection
$hostname = getenv('DB_HOST') ?: 'localhost';
$database = getenv('DB_DATABASE') ?: 'nobels';
$username = getenv('DB_USERNAME') ?: 'xoh';
$password = getenv('DB_PASSWORD') ?: '';

$pdo = connectDatabase($hostname, $database, $username, $password);
if (!$pdo) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to connect to database.'
    ]);
    file_put_contents(__DIR__ . '/debug.log', "Database connection failed\n", FILE_APPEND);
    exit;
}

use RobThree\Auth\Providers\Qr\EndroidQrCodeProvider;
use RobThree\Auth\TwoFactorAuth;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    file_put_contents(__DIR__ . '/debug.log', "POST data: " . json_encode($data) . "\n", FILE_APPEND);

    $firstname = trim($data['firstname'] ?? '');
    $lastname = trim($data['lastname'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    $errors = [];

    if (isEmpty($email)) {
        $errors[] = 'Nevyplnený e-mail.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Neplatný formát e-mailu.';
    }

    if (userExist($pdo, $email)) {
        $errors[] = 'Používateľ s týmto e-mailom už existuje.';
    }

    if (isEmpty($firstname)) {
        $errors[] = 'Nevyplnené meno.';
    } elseif (strlen($firstname) > 50) {
        $errors[] = 'Meno je príliš dlhé (max 50 znakov).';
    } elseif (!preg_match('/^[a-zA-ZáäčďéíľňóôŕšťúýžÁÄČĎÉÍĽŇÓÔŔŠŤÚÝŽ\s-]+$/', $firstname)) {
        $errors[] = 'Meno obsahuje nepovolené znaky.';
    }

    if (isEmpty($lastname)) {
        $errors[] = 'Nevyplnené priezvisko.';
    } elseif (strlen($lastname) > 50) {
        $errors[] = 'Priezvisko je príliš dlhé (max 50 znakov).';
    } elseif (!preg_match('/^[a-zA-ZáäčďéíľňóôŕšťúýžÁÄČĎÉÍĽŇÓÔŔŠŤÚÝŽ\s-]+$/', $lastname)) {
        $errors[] = 'Priezvisko obsahuje nepovolené znaky.';
    }

    if (isEmpty($password)) {
        $errors[] = 'Nevyplnené heslo.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Heslo musí mať aspoň 8 znakov.';
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO users (fullname, email, password, 2fa_code) VALUES (:fullname, :email, :password, :2fa_code)";
            $fullname = "$firstname $lastname";
            $pw_hash = password_hash($password, PASSWORD_ARGON2ID);

            $tfa = new TwoFactorAuth(new EndroidQrCodeProvider());
            $user_secret = $tfa->createSecret();
            $qr_code = $tfa->getQRCodeImageAsDataUri('Nobel Prizes', $user_secret);

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":fullname", $fullname, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":password", $pw_hash, PDO::PARAM_STR);
            $stmt->bindParam(":2fa_code", $user_secret, PDO::PARAM_STR);

            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Registrácia prebehla úspešne.',
                    'qr_code' => $qr_code,
                    'secret' => $user_secret
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Ups. Niečo sa pokazilo pri registrácii.'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ]);
            file_put_contents(__DIR__ . '/debug.log', "Execution error: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Chyby pri registrácii.',
            'errors' => $errors
        ]);
    }

    unset($stmt);
    unset($pdo);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Nepodporovaná metóda požiadavky.'
    ]);
}
?>