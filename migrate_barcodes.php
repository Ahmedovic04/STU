<?php
/**
 * Migration Script - Student Barcodes
 * Run this ONCE to:
 * 1. Add `barcode` column to students table
 * 2. Generate barcodes for existing students
 * 3. Add a "barcode_system" user for QR-based calls
 * 
 * Access: /migrate_barcodes.php (then DELETE this file)
 */
require_once 'includes/config.php';

// Simple auth: require a secret key or local IP
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']) && ($_GET['key'] ?? '') !== 'migrate2024secret') {
    die('Access denied. Add ?key=migrate2024secret');
}

$db = getDB();
$results = [];

// 1. Add barcode column if not exists
try {
    $db->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS barcode VARCHAR(64) NULL UNIQUE");
    $results[] = "✅ Column `barcode` added (or already exists)";
} catch (PDOException $e) {
    // Fallback for MySQL versions without IF NOT EXISTS
    try {
        $db->exec("ALTER TABLE students ADD COLUMN barcode VARCHAR(64) NULL UNIQUE");
        $results[] = "✅ Column `barcode` added";
    } catch (PDOException $e2) {
        if (strpos($e2->getMessage(), 'Duplicate column') !== false) {
            $results[] = "ℹ️ Column `barcode` already exists";
        } else {
            $results[] = "❌ Error adding column: " . $e2->getMessage();
        }
    }
}

// 2. Generate barcodes for students without one
$stmt = $db->query("SELECT id FROM students WHERE barcode IS NULL OR barcode = ''");
$students = $stmt->fetchAll();
$count = 0;

$upd = $db->prepare("UPDATE students SET barcode = ? WHERE id = ?");
foreach ($students as $s) {
    $code = bin2hex(random_bytes(8)); // 16-char unique hex
    $upd->execute([$code, $s['id']]);
    $count++;
}
$results[] = "✅ Generated barcodes for {$count} students";

// 3. Add barcode_system user if not exists
try {
    $systemHash = password_hash('barcode_system_internal_' . date('Y'), PASSWORD_DEFAULT);
    $check = $db->query("SELECT id FROM users WHERE username = 'barcode_system'")->fetch();
    if (!$check) {
        $db->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?,?,?,?)")
           ->execute(['barcode_system', $systemHash, 'نظام الباركود', 'management']);
        $results[] = "✅ System user 'barcode_system' created";
    } else {
        $results[] = "ℹ️ System user 'barcode_system' already exists";
    }
} catch (PDOException $e) {
    $results[] = "❌ Error creating system user: " . $e->getMessage();
}

$results[] = "🎉 Migration complete!";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>Migration Results</title>
<style>body{font-family:sans-serif;padding:40px;background:#f5f5f5} .box{background:#fff;padding:30px;border-radius:10px;max-width:600px;margin:auto;box-shadow:0 4px 20px rgba(0,0,0,0.1)} p{padding:10px;border-radius:6px;margin-bottom:8px;font-size:16px} .warn{background:#fff3cd;padding:15px;border-radius:8px;margin-top:20px;border:1px solid #ffc107}</style>
</head>
<body>
<div class="box">
<h2>Migration Script Results</h2>
<?php foreach($results as $r): ?>
<p><?= htmlspecialchars($r) ?></p>
<?php endforeach; ?>
<div class="warn">⚠️ <strong>Important:</strong> Delete this file after running it: <code>migrate_barcodes.php</code></div>
</div>
</body>
</html>
