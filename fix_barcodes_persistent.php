<?php
/**
 * Migration Script - Fix Barcodes to be Persistent
 * Run this ONCE to link all barcodes to student numbers.
 */
require_once 'includes/config.php';

function generateBarcodeFromNumber($num) {
    return substr(md5("STU_SECURE_SALT_" . $num), 0, 16);
}

function generateRandomStudentNumber($db) {
    do {
        $num = rand(10000000, 99999999);
        $stmt = $db->prepare("SELECT id FROM students WHERE student_number = ?");
        $stmt->execute([$num]);
    } while ($stmt->fetch());
    return (string)$num;
}

$db = getDB();
echo "Starting migration...\n";

// 1. Assign random numbers to students who don't have one
$stmt = $db->query("SELECT id FROM students WHERE student_number IS NULL OR student_number = ''");
$noNum = $stmt->fetchAll();
echo "Found " . count($noNum) . " students without a number.\n";

foreach ($noNum as $s) {
    $num = generateRandomStudentNumber($db);
    $db->prepare("UPDATE students SET student_number = ? WHERE id = ?")->execute([$num, $s['id']]);
}

// 2. Update all barcodes based on student_number
$stmt = $db->query("SELECT id, student_number FROM students");
$all = $stmt->fetchAll();
echo "Updating barcodes for " . count($all) . " students...\n";

$upd = $db->prepare("UPDATE students SET barcode = ? WHERE id = ?");
foreach ($all as $s) {
    $barcode = generateBarcodeFromNumber($s['student_number']);
    $upd->execute([$barcode, $s['id']]);
}

echo "✅ Migration complete. All students now have persistent barcodes linked to their student numbers.\n";
