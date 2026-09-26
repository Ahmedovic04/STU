<?php
/**
 * RFID Direct Call API for ESP32 + RC522
 * ====================================================
 * Endpoint: POST or GET /api/rfid_call.php
 * Parameters:
 *   - card_uid (or rfid_uid or uid) : Hex UID of RFID card (e.g. "8A2B3C4D" or "8A:2B:3C:4D")
 *   - device_id : Optional name of scanning gate (e.g. "Gate-1")
 *   - action    : Optional action ("call" [default], "check", "assign")
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../includes/config.php';

// Support JSON input (commonly sent by ESP32 HTTPClient)
$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true) ?: [];

$action = $_GET['action'] ?? $_POST['action'] ?? $jsonData['action'] ?? 'call';
$cardUid = $_POST['card_uid'] ?? $_GET['card_uid'] ?? $jsonData['card_uid'] 
           ?? $_POST['rfid_uid'] ?? $_GET['rfid_uid'] ?? $jsonData['rfid_uid'] 
           ?? $_POST['uid'] ?? $_GET['uid'] ?? $jsonData['uid'] ?? '';
$deviceId = trim($_POST['device_id'] ?? $_GET['device_id'] ?? $jsonData['device_id'] ?? 'بوابة المدرسة');

// Clean and normalize UID (remove spaces, colons, dashes, and uppercase)
$cleanUid = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', (string)$cardUid));

if (empty($cleanUid)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'code'    => 'EMPTY_UID',
        'message' => 'لم يتم إرسال رقم بطاقة RFID صالح (card_uid مطلوب)'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$db = getDB();
$today = date('Y-m-d');
$nowTime = date('H:i:s');
$displayTime = date('h:i A');

// Find student by rfid_uid (also match with cleaned or stored variation)
$stmt = $db->prepare("
    SELECT s.id, s.full_name, s.student_number, s.rfid_uid,
           c.id as class_id, c.name as class_name, c.grade
    FROM students s
    JOIN classes c ON c.id = s.class_id
    WHERE UPPER(REPLACE(REPLACE(REPLACE(s.rfid_uid, ':', ''), ' ', ''), '-', '')) = ?
       OR UPPER(s.rfid_uid) = ?
    LIMIT 1
");
$stmt->execute([$cleanUid, $cleanUid]);
$student = $stmt->fetch();

/* ================= ACTION: CHECK ONLY ================= */
if ($action === 'check') {
    if (!$student) {
        echo json_encode([
            'success'  => false,
            'code'     => 'NOT_FOUND',
            'message'  => 'البطاقة غير معرفة لأي طالب',
            'card_uid' => $cleanUid
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'success'  => true,
        'code'     => 'STUDENT_FOUND',
        'student'  => [
            'id'             => (int)$student['id'],
            'full_name'      => $student['full_name'],
            'student_number' => $student['student_number'],
            'class_name'     => $student['class_name'],
            'grade'          => $student['grade']
        ],
        'card_uid' => $cleanUid
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ================= ACTION: ASSIGN CARD TO STUDENT ================= */
if ($action === 'assign') {
    startSecureSession();
    if (!requireLogin('management')) {
        http_response_code(401);
        echo json_encode(['success' => false, 'code' => 'UNAUTHORIZED', 'message' => 'يتطلب تسجيل دخول مدير أو إداري'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $studentId = intval($_POST['student_id'] ?? $_GET['student_id'] ?? $jsonData['student_id'] ?? 0);
    if (!$studentId) {
        echo json_encode(['success' => false, 'code' => 'NO_STUDENT', 'message' => 'معرف الطالب مطلوب'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Check if card is already assigned to someone else
    $checkCard = $db->prepare("SELECT id, full_name FROM students WHERE rfid_uid = ? AND id != ?");
    $checkCard->execute([$cleanUid, $studentId]);
    $assignedTo = $checkCard->fetch();

    if ($assignedTo) {
        echo json_encode([
            'success' => false,
            'code'    => 'CARD_ALREADY_ASSIGNED',
            'message' => 'هذه البطاقة مسجلة مسبقاً للطالب: ' . $assignedTo['full_name']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $upd = $db->prepare("UPDATE students SET rfid_uid = ? WHERE id = ?");
    $upd->execute([$cleanUid, $studentId]);

    echo json_encode([
        'success'  => true,
        'code'     => 'ASSIGNED_SUCCESS',
        'message'  => 'تم ربط بطاقة RFID بالطالب بنجاح',
        'card_uid' => $cleanUid,
        'student_id' => $studentId
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ================= ACTION: CALL STUDENT (DEFAULT) ================= */
if (!$student) {
    echo json_encode([
        'success'  => false,
        'code'     => 'NOT_FOUND',
        'message'  => 'البطاقة غير مسجلة لأي طالب. يرجى تعريفها في لوحة التحكم أولاً.',
        'card_uid' => $cleanUid,
        'buzzer'   => 1 // 1 long beep indicates card error/unknown
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Get or ensure system user for RFID calls
$rfidUser = $db->query("SELECT id FROM users WHERE username = 'rfid_system'")->fetch();
$callerId = $rfidUser ? (int)$rfidUser['id'] : 1;

// Check if student was already called today
$checkCall = $db->prepare("
    SELECT id, call_time FROM dismissal_calls 
    WHERE student_id = ? AND call_date = ?
");
$checkCall->execute([$student['id'], $today]);
$existingCall = $checkCall->fetch();

if ($existingCall) {
    $existingTime = date('h:i A', strtotime($existingCall['call_time']));
    echo json_encode([
        'success'   => true,
        'code'      => 'ALREADY_CALLED',
        'message'   => "تم استدعاء الطالب مسبقاً اليوم في تمام الساعة ({$existingTime})",
        'student'   => [
            'id'             => (int)$student['id'],
            'full_name'      => $student['full_name'],
            'student_number' => $student['student_number'],
            'class_name'     => $student['class_name'],
            'grade'          => $student['grade']
        ],
        'call_time' => $existingTime,
        'card_uid'  => $cleanUid,
        'buzzer'    => 3 // 3 short beeps: already called
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Insert dismissal call
$ins = $db->prepare("
    INSERT INTO dismissal_calls (student_id, called_by, call_date, call_time, notes)
    VALUES (?, ?, ?, ?, ?)
");
$notes = "استدعاء تلقائي عبر بطاقة RFID من جهاز ({$deviceId})";
$ins->execute([$student['id'], $callerId, $today, $nowTime, $notes]);

// Output success response formatted for ESP32 and web apps
echo json_encode([
    'success'   => true,
    'code'      => 'CALLED_SUCCESS',
    'message'   => 'تم استدعاء الطالب بنجاح! سيظهر اسم الطالب الآن على شاشة المعلم فوراً.',
    'student'   => [
        'id'             => (int)$student['id'],
        'full_name'      => $student['full_name'],
        'student_number' => $student['student_number'],
        'class_name'     => $student['class_name'],
        'grade'          => $student['grade']
    ],
    'call_time' => $displayTime,
    'card_uid'  => $cleanUid,
    'buzzer'    => 2 // 2 pleasant beeps: success!
], JSON_UNESCAPED_UNICODE);
