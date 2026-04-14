-- =============================================
-- School Dismissal System - Database Schema
-- =============================================

CREATE DATABASE IF NOT EXISTS school_dismissal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_dismissal;

-- Users table (admin + management staff)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'management') NOT NULL DEFAULT 'management',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Classes table
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    grade VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    class_id INT NOT NULL,
    student_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Dismissal calls table (resets daily)
CREATE TABLE IF NOT EXISTS dismissal_calls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    called_by INT NOT NULL,
    call_date DATE NOT NULL DEFAULT (CURRENT_DATE),
    call_time TIME NOT NULL,
    notes TEXT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (called_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- =============================================
-- Default data
-- =============================================

-- Default admin user (password: admin123)
INSERT INTO users (username, password, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدير النظام', 'admin'),
('mgmt1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'أحمد العلي', 'management'),
('mgmt2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'فاطمة المطيري', 'management');

-- Sample classes
INSERT INTO classes (name, grade) VALUES
('1/أ', 'الأول'),('1/ب', 'الأول'),('1/ج', 'الأول'),
('2/أ', 'الثاني'),('2/ب', 'الثاني'),('2/ج', 'الثاني'),
('3/أ', 'الثالث'),('3/ب', 'الثالث'),
('4/أ', 'الرابع'),('4/ب', 'الرابع'),
('5/أ', 'الخامس'),('5/ب', 'الخامس'),
('6/أ', 'السادس'),('6/ب', 'السادس');

-- Sample students
INSERT INTO students (full_name, class_id, student_number) VALUES
('محمد أحمد الزهراني', 1, 'S001'),
('عبدالله سالم العتيبي', 1, 'S002'),
('فيصل عمر الشمري', 1, 'S003'),
('خالد ناصر الدوسري', 1, 'S004'),
('عمر يوسف القحطاني', 1, 'S005'),
('سعد علي المطيري', 2, 'S006'),
('ماجد فهد الحربي', 2, 'S007'),
('يوسف إبراهيم السبيعي', 2, 'S008'),
('أنس محمد الغامدي', 3, 'S009'),
('بدر سلطان العنزي', 3, 'S010');
