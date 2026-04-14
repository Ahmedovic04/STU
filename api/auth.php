<?php
require_once '../includes/config.php';
startSecureSession();

$action = $_GET['action'] ?? '';

if ($action === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        jsonResponse(false, 'يرجى إدخال اسم المستخدم وكلمة المرور');
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    $isValid = false;
    if ($user && str_starts_with($user['password'], 'PLAIN:')) {
        $isValid = ($password === substr($user['password'], 6));
    } else if ($user) {
        $isValid = password_verify($password, $user['password']);
    }

    if (!$user || !$isValid) {
        jsonResponse(false, 'اسم المستخدم أو كلمة المرور غير صحيحة');
    }

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];

    // ✅ الحل هنا (روابط نسبية بدون SITE_URL)
    $redirect = ($user['role'] === 'admin') 
        ? '/admin/index.php' 
        : '/management/index.php';

    jsonResponse(true, 'تم تسجيل الدخول بنجاح', ['redirect' => $redirect]);
}

if ($action === 'logout') {
    logout();
}

jsonResponse(false, 'طلب غير صالح');
