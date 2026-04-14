<?php
// =============================================
// Database Configuration
// =============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'school_dismissal');
define('DB_USER', 'root');      // Change to your DB username
define('DB_PASS', '');          // Change to your DB password
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'نظام استدعاء الطلاب');
define('SITE_URL', 'http://localhost/school-dismissal');

// =============================================
// Database Connection (PDO)
// =============================================
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
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
        session_start();
    }
}

function requireLogin($role = null) {
    startSecureSession();
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
    if ($role && $_SESSION['role'] !== $role && $_SESSION['role'] !== 'admin') {
        header('Location: ' . SITE_URL . '/index.php?error=unauthorized');
        exit;
    }
}

function requireAdmin() {
    startSecureSession();
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: ' . SITE_URL . '/index.php?error=unauthorized');
        exit;
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
