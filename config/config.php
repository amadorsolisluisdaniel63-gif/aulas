<?php
date_default_timezone_set('America/Mexico_City');


define('DB_HOST', 'localhost');
define('DB_NAME', 'aulas_magicas');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SESSION_NAME', 'aulas_session');
define('SESSION_LIFETIME', 3600);

function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('
            <div style="font-family:sans-serif;background:#fff0f0;border:2px solid #e04040;padding:20px;margin:40px auto;max-width:600px;border-radius:12px;">
                <h3>Error de conexión a MySQL</h3>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <ul>
                    <li>Verifica que MySQL esté iniciado en XAMPP</li>
                    <li>Que exista la base de datos <strong>aulas_magicas</strong></li>
                </ul>
            </div>');
        }
    }

    return $pdo;
}

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'httponly' => true,
            'secure' => false
        ]);
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: menu.php');
        exit;
    }
}

function clean($val) {
    return trim(htmlspecialchars(strip_tags($val), ENT_QUOTES, 'UTF-8'));
}


function hasRole($role) {
    return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === $role;
}

function requireRole($role) {
    requireLogin();

    if (!hasRole($role)) {
        header('Location: menu.php');
        exit;
    }
}