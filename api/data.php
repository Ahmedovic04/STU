<?php
require_once '../includes/config.php';
autoResetCalls();
startSecureSession();

$action = $_GET['action'] ?? '';
$db = getDB();
$today = date('Y-m-d');

/* ================= PUBLIC ================= */

if ($action === 'get_classes') {
    $stmt = $db->query("
        SELECT c.*, 
               (SELECT COUNT(*) FROM students s WHERE s.class_id = c.id) as student_count 
        FROM classes c 
        ORDER BY c.grade, c.name
    ");
    jsonResponse(true, '', $stmt->fetchAll());
}

if ($action === 'get_students') {
    $classId = intval($_GET['class_id'] ?? 0);

    $stmt = $db->prepare("
        SELECT s.id, s.full_name, s.student_number,
               dc.call_time, dc.id as call_id,
               u.full_name as called_by_name
        FROM students s
        LEFT JOIN dismissal_calls dc 
            ON dc.student_id = s.id AND dc.call_date = ?
        LEFT JOIN users u 
            ON u.id = dc.called_by
        WHERE s.class_id = ?
        ORDER BY s.full_name
    ");

    $stmt->execute([$today, $classId]);
    jsonResponse(true, '', $stmt->fetchAll());
}

/* ================= TODAY ALL CALLS (PUBLIC) ================= */

if ($action === 'get_today_all_calls') {
    $stmt = $db->prepare("
        SELECT dc.id as call_id, dc.call_time,
               s.id as student_id, s.full_name as student_name,
               c.name as class_name, c.grade,
               u.full_name as called_by_name
        FROM dismissal_calls dc
        JOIN students s ON s.id = dc.student_id
        JOIN classes c ON c.id = s.class_id
        JOIN users u ON u.id = dc.called_by
        WHERE dc.call_date = ?
        ORDER BY dc.call_time DESC
    ");
    $stmt->execute([$today]);
    jsonResponse(true, '', $stmt->fetchAll());
}

/* ================= ADMIN CHECK ================= */

if (in_array($action, [
    'add_class','delete_class','rename_class',
    'add_student','delete_student','update_student',
    'get_all_students','add_user','delete_user',
    'get_users','bulk_import_students'
])) {
    requireAdmin();
}

/* ================= ADMIN ACTIONS ================= */

if ($action === 'get_all_students') {
    $classId = intval($_GET['class_id'] ?? 0);

    if ($classId) {
        $stmt = $db->prepare("
            SELECT s.*, c.name as class_name 
            FROM students s 
            JOIN classes c ON c.id = s.class_id 
            WHERE s.class_id = ? 
            ORDER BY s.full_name
        ");
        $stmt->execute([$classId]);
    } else {
        $stmt = $db->query("
            SELECT s.*, c.name as class_name 
            FROM students s 
            JOIN classes c ON c.id = s.class_id 
            ORDER BY c.name, s.full_name
        ");
    }

    jsonResponse(true, '', $stmt->fetchAll());
}

if ($action === 'add_class') {
    $name  = trim($_POST['name'] ?? '');
    $grade = trim($_POST['grade'] ?? '');

    if (empty($name)) jsonResponse(false, 'اسم الصف مطلوب');

    $stmt = $db->prepare("INSERT INTO classes (name, grade) VALUES (?,?)");
    $stmt->execute([$name, $grade]);

    jsonResponse(true, 'تم إضافة الصف بنجاح');
}

if ($action === 'delete_class') {
    $id = intval($_POST['id'] ?? 0);

    $stmt = $db->prepare("DELETE FROM classes WHERE id = ?");
    $stmt->execute([$id]);

    jsonResponse(true, 'تم حذف الصف وجميع طلابه');
}

if ($action === 'add_student') {
    $name    = trim($_POST['full_name'] ?? '');
    $classId = intval($_POST['class_id'] ?? 0);
    $number  = trim($_POST['student_number'] ?? '');

    if (empty($name) || !$classId)
        jsonResponse(false, 'الاسم والصف مطلوبان');

    // Generate a unique barcode
    $barcode = bin2hex(random_bytes(8));

    $stmt = $db->prepare("
        INSERT INTO students (full_name, class_id, student_number, barcode)
        VALUES (?,?,?,?)
    ");

    $stmt->execute([$name, $classId, $number, $barcode]);

    jsonResponse(true, 'تم إضافة الطالب');
}

if ($action === 'delete_student') {
    $id = intval($_POST['id'] ?? 0);

    $stmt = $db->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);

    jsonResponse(true, 'تم حذف الطالب');
}

if ($action === 'update_student') {
    $id      = intval($_POST['id'] ?? 0);
    $name    = trim($_POST['full_name'] ?? '');
    $classId = intval($_POST['class_id'] ?? 0);
    $number  = trim($_POST['student_number'] ?? '');

    if (!$id || empty($name) || !$classId)
        jsonResponse(false, 'بيانات ناقصة');

    $stmt = $db->prepare("
        UPDATE students 
        SET full_name=?, class_id=?, student_number=? 
        WHERE id=?
    ");

    $stmt->execute([$name, $classId, $number, $id]);

    jsonResponse(true, 'تم تعديل بيانات الطالب');
}

/* ================= MANAGEMENT ================= */

if ($action === 'call_student') {
    if (!requireLogin('management')) {
        jsonResponse(false, 'غير مسجل دخول');
    }

    $studentId = intval($_POST['student_id'] ?? 0);
    $user = currentUser();

    $check = $db->prepare("
        SELECT id FROM dismissal_calls 
        WHERE student_id=? AND call_date=?
    ");
    $check->execute([$studentId, $today]);

    if ($check->fetch()) {
        $del = $db->prepare("
            DELETE FROM dismissal_calls 
            WHERE student_id=? AND call_date=?
        ");
        $del->execute([$studentId, $today]);

        jsonResponse(true, 'تم إلغاء الاستدعاء', ['status' => 'cancelled']);
    }

    $stmt = $db->prepare("
        INSERT INTO dismissal_calls (student_id, called_by, call_date, call_time)
        VALUES (?,?,?,?)
    ");

    $stmt->execute([
        $studentId,
        $user['id'],
        $today,
        date('H:i:s')
    ]);

    jsonResponse(true, 'تم استدعاء الطالب', [
        'status'    => 'called',
        'call_time' => date('H:i'),
        'called_by' => $user['full_name']
    ]);
}

/* ================= USERS ================= */

if ($action === 'get_users') {
    $stmt = $db->query("
        SELECT id, username, full_name, role, created_at 
        FROM users 
        ORDER BY role, full_name
    ");

    jsonResponse(true, '', $stmt->fetchAll());
}

if ($action === 'add_user') {
    $username = trim($_POST['username'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'management';

    if (empty($username) || empty($fullName) || empty($password))
        jsonResponse(false, 'جميع الحقول مطلوبة');

    if (!in_array($role, ['admin','management']))
        jsonResponse(false, 'دور غير صالح');

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $db->prepare("
            INSERT INTO users (username, password, full_name, role)
            VALUES (?,?,?,?)
        ");
        $stmt->execute([$username, $hashed, $fullName, $role]);

        jsonResponse(true, 'تم إضافة المستخدم');
    } catch (PDOException $e) {
        jsonResponse(false, 'اسم المستخدم موجود مسبقاً');
    }
}

if ($action === 'delete_user') {
    $id = intval($_POST['id'] ?? 0);
    $current = currentUser();

    if ($id == $current['id'])
        jsonResponse(false, 'لا يمكنك حذف حسابك الحالي');

    $stmt = $db->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$id]);

    jsonResponse(true, 'تم حذف المستخدم');
}

/* ================= LOG ================= */

if ($action === 'get_today_log') {
    requireLogin();

    $stmt = $db->prepare("
        SELECT dc.*, s.full_name as student_name,
               c.name as class_name,
               u.full_name as called_by_name
        FROM dismissal_calls dc
        JOIN students s ON s.id = dc.student_id
        JOIN classes c ON c.id = s.class_id
        JOIN users u ON u.id = dc.called_by
        WHERE dc.call_date = ?
        ORDER BY dc.call_time DESC
    ");

    $stmt->execute([$today]);

    jsonResponse(true, '', $stmt->fetchAll());
}

/* ================= RENAME CLASS ================= */

if ($action === 'rename_class') {
    requireAdmin();

    $id    = intval($_POST['id'] ?? 0);
    $name  = trim($_POST['name'] ?? '');
    $grade = trim($_POST['grade'] ?? '');

    if (!$id || empty($name))
        jsonResponse(false, 'البيانات ناقصة');

    $stmt = $db->prepare("
        UPDATE classes SET name=?, grade=? WHERE id=?
    ");

    $stmt->execute([$name, $grade, $id]);

    jsonResponse(true, 'تم تعديل اسم الصف');
}

/* ================= BULK IMPORT ================= */

if ($action === 'bulk_import_students') {
    requireAdmin();

    $classId = intval($_POST['class_id'] ?? 0);
    $names   = trim($_POST['names'] ?? '');

    if (!$classId || empty($names))
        jsonResponse(false, 'الصف والأسماء مطلوبة');

    $lines = preg_split('/[\r\n،,]+/u', $names);

    $inserted = 0;
    $skipped = 0;

    $stmt = $db->prepare("
        INSERT INTO students (full_name, class_id, barcode)
        VALUES (?,?,?)
    ");

    foreach ($lines as $line) {
        $name = preg_replace('/^[\d٠-٩]+[\.\-\)\s]+/u', '', trim($line));
        $name = trim($name);

        if (empty($name) || mb_strlen($name) < 2) {
            $skipped++;
            continue;
        }

        try {
            $barcode = bin2hex(random_bytes(8));
            $stmt->execute([$name, $classId, $barcode]);
            $inserted++;
        } catch (Exception $e) {
            $skipped++;
        }
    }

    jsonResponse(true, "تم إضافة {$inserted} طالب", [
        'inserted' => $inserted,
        'skipped'  => $skipped
    ]);
}
/* ================= BARCODE PUBLIC CALL ================= */

if ($action === 'call_by_barcode') {
    if (!isLoggedIn()) {
        jsonResponse(false, 'يرجى تسجيل الدخول أولاً');
    }

    $code = trim($_GET['code'] ?? $_POST['code'] ?? '');
    if (empty($code)) jsonResponse(false, 'رمز غير صالح');

    // Find student by barcode
    $stmt = $db->prepare("
        SELECT s.id, s.full_name, c.name as class_name
        FROM students s
        JOIN classes c ON c.id = s.class_id
        WHERE s.barcode = ?
    ");
    $stmt->execute([$code]);
    $student = $stmt->fetch();

    if (!$student) jsonResponse(false, 'الطالب غير موجود');

    // Use current logged-in user
    $user = currentUser();
    $callerId = $user['id'];

    // Check if already called today
    $check = $db->prepare("SELECT id FROM dismissal_calls WHERE student_id=? AND call_date=?");
    $check->execute([$student['id'], $today]);

    if ($check->fetch()) {
        jsonResponse(true, 'الطالب مستدعى مسبقاً اليوم', [
            'status'     => 'already_called',
            'student'    => $student['full_name'],
            'class_name' => $student['class_name']
        ]);
    }

    $ins = $db->prepare("
        INSERT INTO dismissal_calls (student_id, called_by, call_date, call_time)
        VALUES (?,?,?,?)
    ");
    $ins->execute([$student['id'], $callerId, $today, date('H:i:s')]);

    jsonResponse(true, 'تم استدعاء الطالب بنجاح', [
        'status'     => 'called',
        'student'    => $student['full_name'],
        'class_name' => $student['class_name'],
        'call_time'  => date('H:i')
    ]);
}

/* ================= EXTRA FEATURES ================= */

// ===== LOGOUT =====
if ($action === 'logout') {
    logout();
}

// ===== RESET CALLS =====
if ($action === 'reset_calls') {
    requireAdmin();

    $stmt = $db->prepare("DELETE FROM dismissal_calls");
    $stmt->execute();

    jsonResponse(true, 'تم تصفير جميع الاستدعاءات');
}

// ===== REPORT =====
if ($action === 'report') {
    requireAdmin();

    $from = $_GET['from'] ?? '';
    $to   = $_GET['to'] ?? '';

    if (!$from || !$to) {
        jsonResponse(false, 'حدد التاريخ');
    }

    $stmt = $db->prepare("
        SELECT dc.call_date, dc.call_time,
               s.full_name AS student_name,
               c.name AS class_name,
               u.full_name AS called_by
        FROM dismissal_calls dc
        JOIN students s ON s.id = dc.student_id
        JOIN classes c ON c.id = s.class_id
        JOIN users u ON u.id = dc.called_by
        WHERE dc.call_date BETWEEN ? AND ?
        ORDER BY dc.call_date DESC, dc.call_time DESC
    ");

    $stmt->execute([$from, $to]);

    jsonResponse(true, '', $stmt->fetchAll());
}
if ($action === 'reset_calls') {
    requireAdmin();
    $db->exec("DELETE FROM dismissal_calls");
    jsonResponse(true, 'تم التصفير');
}
/* ================= CARD SETTINGS ================= */

if ($action === 'get_card_settings') {
    try {
        $stmt = $db->query("SELECT * FROM card_settings WHERE id = 1");
        $settings = $stmt->fetch();
    } catch (PDOException $e) {
        $settings = false;
    }
    
    if (!$settings) {
        // Create default settings if not exists
        $db->exec("
            CREATE TABLE IF NOT EXISTS card_settings (
                id INT PRIMARY KEY DEFAULT 1,
                font_size INT DEFAULT 11,
                card_width FLOAT DEFAULT 3.37,
                card_height FLOAT DEFAULT 2.125,
                barcode_size INT DEFAULT 80
            ) ENGINE=InnoDB
        ");
        $db->exec("INSERT IGNORE INTO card_settings (id, font_size, card_width, card_height, barcode_size) VALUES (1, 11, 3.37, 2.125, 80)");
        $settings = $db->query("SELECT * FROM card_settings WHERE id = 1")->fetch();
    }
    
    jsonResponse(true, '', $settings);
}

if ($action === 'update_card_settings') {
    requireAdmin();
    
    $fontSize    = intval($_POST['font_size'] ?? 11);
    $cardWidth   = floatval($_POST['card_width'] ?? 3.37);
    $cardHeight  = floatval($_POST['card_height'] ?? 2.125);
    $barcodeSize = intval($_POST['barcode_size'] ?? 80);
    
    $stmt = $db->prepare("
        UPDATE card_settings 
        SET font_size = ?, card_width = ?, card_height = ?, barcode_size = ? 
        WHERE id = 1
    ");
    $stmt->execute([$fontSize, $cardWidth, $cardHeight, $barcodeSize]);
    
    jsonResponse(true, 'تم حفظ إعدادات البطاقة بنجاح');
}

/* ================= DEFAULT ================= */

jsonResponse(false, 'طلب غير معروف');
