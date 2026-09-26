<?php
/**
 * Migration Script - Student RFID Cards
 * Run this ONCE to:
 * 1. Add `rfid_uid` column to students table
 * 2. Add index on `rfid_uid` for fast lookup
 * 3. Add `rfid_system` user for RFID-based calls
 * 
 * Access: /migrate_rfid.php (or ?key=migrate2024secret)
 */
require_once 'includes/config.php';

$isCli = (php_sapi_name() === 'cli');
$isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', 'localhost']);
$hasKey = (($_GET['key'] ?? '') === 'migrate2024secret');

if (!$isCli && !$isLocal && !$hasKey) {
    die('Access denied. Add ?key=migrate2024secret');
}

$db = getDB();
$results = [];

// 1. Add rfid_uid column if not exists
try {
    $db->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS rfid_uid VARCHAR(50) NULL UNIQUE");
    $results[] = "✅ حقل `rfid_uid` تم إنشاؤه أو موجود مسبقاً في جدول الطلاب";
} catch (PDOException $e) {
    try {
        $db->exec("ALTER TABLE students ADD COLUMN rfid_uid VARCHAR(50) NULL UNIQUE");
        $results[] = "✅ حقل `rfid_uid` تم إضافته بنجاح لجدول الطلاب";
    } catch (PDOException $e2) {
        if (strpos($e2->getMessage(), 'Duplicate column') !== false) {
            $results[] = "ℹ️ حقل `rfid_uid` موجود بالفعل مسبقاً";
        } else {
            $results[] = "❌ خطأ في إضافة حقل `rfid_uid`: " . $e2->getMessage();
        }
    }
}

// 2. Add rfid_system user if not exists
try {
    $systemHash = password_hash('rfid_device_secure_salt_' . date('Y'), PASSWORD_DEFAULT);
    $check = $db->query("SELECT id FROM users WHERE username = 'rfid_system'")->fetch();
    if (!$check) {
        $db->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?,?,?,?)")
           ->execute(['rfid_system', $systemHash, 'بطاقة ولي الأمر (RFID)', 'management']);
        $results[] = "✅ تم إنشاء مستخدم النظام 'rfid_system' بنجاح";
    } else {
        $results[] = "ℹ️ مستخدم النظام 'rfid_system' موجود مسبقاً";
    }
} catch (PDOException $e) {
    $results[] = "❌ خطأ في إنشاء مستخدم النظام: " . $e->getMessage();
}

$results[] = "🎉 اكتمل تجهيز ميزة بطاقات RFID بنجاح!";

if ($isCli) {
    foreach ($results as $res) {
        echo strip_tags($res) . PHP_EOL;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ترقية قاعدة البيانات - ميزة بطاقات RFID</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body { font-family: 'Tajawal', sans-serif; background: #0f2238; color: #fff; padding: 40px 20px; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
.card { background: #1a3a5c; border-radius: 16px; padding: 35px; max-width: 580px; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); }
h2 { color: #f0a500; margin-top: 0; margin-bottom: 20px; font-size: 24px; text-align: center; }
.item { background: rgba(255,255,255,0.06); padding: 12px 18px; border-radius: 10px; margin-bottom: 10px; font-size: 15px; border-right: 4px solid #f0a500; }
.btn { display: inline-block; background: #f0a500; color: #1a3a5c; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 700; margin-top: 20px; text-align: center; width: 100%; box-sizing: border-box; }
</style>
</head>
<body>
<div class="card">
    <h2>📡 ترقية قاعدة البيانات - ميزة بطاقات RFID</h2>
    <?php foreach ($results as $msg): ?>
        <div class="item"><?= htmlspecialchars($msg) ?></div>
    <?php endforeach; ?>
    <a href="admin/" class="btn">الذهاب إلى لوحة الإدارة ➔</a>
</div>
</body>
</html>
