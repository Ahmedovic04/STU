<?php
date_default_timezone_set('Asia/Riyadh');

// =============================================
// Database Configuration
// =============================================
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'railway');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', getenv('MYSQLPORT') ?: '3306');

define('SITE_NAME', 'نظام استدعاء الطلاب');

// Dynamically detect SITE_URL — works behind Coolify/Railway/any reverse proxy
$_proto = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'));
$_host  = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
define('SITE_URL', rtrim($_proto . '://' . $_host, '/'));
define('IS_HTTPS', $_proto === 'https');

// =============================================
// Database Connection (PDO)
// =============================================
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['success' => false, 'message' => 'خطأ في الاتصال بقاعدة البيانات: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// =============================================
// Session & Auth Helpers
// =============================================
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Support HTTPS behind reverse proxy (Coolify/Railway/Traefik)
        $isHttps = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                   || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => $isHttps ? 'None' : 'Lax',
        ]);
        session_start();
    }
}

function requireLogin($role = null) {
    startSecureSession();

    if (!isset($_SESSION['user_id'])) {
        // بدل ما نوقف النظام، نرجع null session
        return false;
    }

    if ($role && $_SESSION['role'] !== $role && $_SESSION['role'] !== 'admin') {
        return false;
    }

    return true;
}

function requireAdmin() {
    startSecureSession();

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        jsonResponse(false, 'غير مصرح', ['auth' => false]);
    }
}

function isLoggedIn() {
    startSecureSession();
    return isset($_SESSION['user_id']);
}

function currentUser() {
    return [
        'id'        => $_SESSION['user_id'] ?? null,
        'username'  => $_SESSION['username'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
        'role'      => $_SESSION['role'] ?? null,
    ];
}

function logout() {
    startSecureSession();
    session_destroy();
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

// =============================================
// JSON response helpers
// =============================================
function jsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

// =============================================
// Auto-reset: mark old calls from previous days
// =============================================
function cleanOldCalls() {
    // Calls are filtered by date in queries - no deletion needed
    // This keeps history but only shows today's calls
}
function autoResetCalls() {
    $db = getDB();

    $db->exec("
        CREATE TABLE IF NOT EXISTS settings (
            id INT PRIMARY KEY,
            last_reset DATETIME
        )
    ");

    $stmt = $db->query("SELECT last_reset FROM settings WHERE id = 1");
    $row = $stmt->fetch();

    $now = new DateTime();

    if (!$row) {
        $db->prepare("INSERT INTO settings (id, last_reset) VALUES (1, NOW())")->execute();
        return;
    }

    $last = new DateTime($row['last_reset']);
    $diff = $now->getTimestamp() - $last->getTimestamp();

    if ($diff >= 18000) { // 5 ساعات
        $db->exec("DELETE FROM dismissal_calls");
        $db->prepare("UPDATE settings SET last_reset = NOW() WHERE id = 1")->execute();
    }
}
