<?php
require_once 'includes/config.php';
startSecureSession();

if (!isLoggedIn()) {
    header('Location: index.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$code = trim($_GET['code'] ?? '');
$pageTitle = 'استدعاء الطالب - ' . SITE_NAME;

// Pre-fetch student info for display
$student = null;
$error = null;

if ($code) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT s.id, s.full_name, c.name as class_name
        FROM students s
        JOIN classes c ON c.id = s.class_id
        WHERE s.barcode = ?
    ");
    $stmt->execute([$code]);
    $student = $stmt->fetch();
    if (!$student) {
        $error = 'رمز الطالب غير موجود أو تالف';
    }
} else {
    $error = 'رمز الباركود مفقود';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

:root {
  --primary: #1a3a5c;
  --accent: #f0a500;
  --success: #2ecc71;
  --danger: #e74c3c;
  --bg: #f0f4f9;
}

body {
  font-family: 'Tajawal', sans-serif;
  background: linear-gradient(135deg, #0f2238 0%, #1a3a5c 50%, #0f2238 100%);
  min-height: 100vh;
  width: 100%;
  margin: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  position: relative;
  overflow-x: hidden;
}

/* Animated background dots */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background-image: radial-gradient(circle, rgba(240,165,0,0.08) 1px, transparent 1px);
  background-size: 40px 40px;
  pointer-events: none;
}

.card {
  background: rgba(255,255,255,0.97);
  border-radius: 24px;
  padding: 50px 40px;
  max-width: 420px;
  width: 100%;
  text-align: center;
  box-shadow: 0 30px 80px rgba(0,0,0,0.4);
  position: relative;
  z-index: 1;
  animation: slideUp 0.5s cubic-bezier(0.34,1.56,0.64,1);
  margin: auto;
}

@media (max-width: 480px) {
  .card { padding: 30px 20px; border-radius: 20px; }
  .student-name { font-size: 22px; }
  .logo { width: 60px; height: 60px; font-size: 28px; }
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(40px) scale(0.95); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}

.logo {
  width: 70px;
  height: 70px;
  background: linear-gradient(135deg, var(--accent), #e8940d);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 34px;
  margin: 0 auto 24px;
  box-shadow: 0 8px 24px rgba(240,165,0,0.35);
}

.site-name {
  font-size: 13px;
  color: #999;
  margin-bottom: 28px;
  font-weight: 500;
  letter-spacing: 0.5px;
}

/* States */
.state { display: none; }
.state.active { display: block; }

/* Loading */
.spinner {
  width: 60px;
  height: 60px;
  border: 5px solid rgba(240,165,0,0.2);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 20px auto;
}
@keyframes spin { to { transform: rotate(360deg); } }

.loading-text {
  font-size: 18px;
  font-weight: 600;
  color: #555;
  margin-top: 16px;
}

/* Success */
.success-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #2ecc71, #27ae60);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 36px;
  margin: 0 auto 24px;
  box-shadow: 0 8px 30px rgba(46,204,113,0.4);
  animation: popIn 0.4s cubic-bezier(0.34,1.56,0.64,1);
}

.already-icon {
  background: linear-gradient(135deg, #f39c12, #e67e22);
  box-shadow: 0 8px 30px rgba(243,156,18,0.4);
}

@keyframes popIn {
  from { transform: scale(0); }
  to   { transform: scale(1); }
}

.student-name {
  font-size: 26px;
  font-weight: 900;
  color: #1a2535;
  margin-bottom: 6px;
}

.student-class {
  display: inline-block;
  background: rgba(240,165,0,0.12);
  color: #a87200;
  padding: 6px 18px;
  border-radius: 20px;
  font-size: 15px;
  font-weight: 700;
  margin-bottom: 20px;
}

.status-msg {
  font-size: 18px;
  font-weight: 700;
  padding: 14px 20px;
  border-radius: 12px;
  margin-bottom: 20px;
}

.status-called {
  background: rgba(46,204,113,0.1);
  color: #1a8a4a;
  border: 1px solid rgba(46,204,113,0.3);
}

.status-already {
  background: rgba(243,156,18,0.1);
  color: #c67f0a;
  border: 1px solid rgba(243,156,18,0.3);
}

.call-time {
  font-size: 13px;
  color: #999;
  margin-bottom: 24px;
}

/* Error */
.error-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #e74c3c, #c0392b);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 36px;
  margin: 0 auto 24px;
  box-shadow: 0 8px 30px rgba(231,76,60,0.4);
}

.error-title {
  font-size: 22px;
  font-weight: 800;
  color: #1a2535;
  margin-bottom: 10px;
}

.error-msg {
  font-size: 15px;
  color: #777;
  margin-bottom: 24px;
}

/* Buttons */
.btn-home {
  display: inline-block;
  background: var(--primary);
  color: #fff;
  padding: 13px 30px;
  border-radius: 12px;
  text-decoration: none;
  font-size: 15px;
  font-weight: 700;
  transition: all 0.2s;
  box-shadow: 0 4px 14px rgba(26,58,92,0.3);
}
.btn-home:hover { background: #1e4976; transform: translateY(-2px); }

.divider {
  width: 50px;
  height: 3px;
  background: linear-gradient(90deg, var(--accent), transparent);
  border-radius: 3px;
  margin: 0 auto 28px;
}
</style>
</head>
<body>

<div class="card">
  <div class="logo">🏫</div>
  <div class="site-name">مدرسة معيذر الابتدائية للبنين</div>
  <div class="divider"></div>

  <?php if ($error): ?>
  <!-- Error State (static, from PHP) -->
  <div class="state active" id="state-error-static">
    <div class="error-icon">❌</div>
    <div class="error-title">خطأ</div>
    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <a href="/index.php" class="btn-home">🏠 الصفحة الرئيسية</a>
  </div>

  <?php else: ?>
  <!-- Loading State -->
  <div class="state active" id="state-loading">
    <div class="spinner"></div>
    <div class="loading-text">جاري الاستدعاء...</div>
  </div>

  <!-- Success State -->
  <div class="state" id="state-success">
    <div class="success-icon" id="resultIcon">✅</div>
    <div class="student-name" id="studentName"><?= htmlspecialchars($student['full_name']) ?></div>
    <div class="student-class" id="studentClass">🏛️ الصف: <?= htmlspecialchars($student['class_name']) ?></div>
    <div class="status-msg status-called" id="statusMsg"></div>
    <div class="call-time" id="callTime"></div>
    <a href="/index.php" class="btn-home">🏠 الصفحة الرئيسية</a>
  </div>

  <!-- Error State (dynamic) -->
  <div class="state" id="state-error">
    <div class="error-icon">❌</div>
    <div class="error-title">حدث خطأ</div>
    <div class="error-msg" id="errorMsg"></div>
    <a href="/index.php" class="btn-home">🏠 الصفحة الرئيسية</a>
  </div>

  <script>
  (async function() {
    const code = <?= json_encode($code) ?>;
    
    try {
      const res = await fetch('/api/data.php?action=call_by_barcode&code=' + encodeURIComponent(code), {
        method: 'POST',
        credentials: 'include'
      });
      const data = await res.json();

      document.getElementById('state-loading').classList.remove('active');

      if (data.success) {
        const icon    = document.getElementById('resultIcon');
        const msgEl   = document.getElementById('statusMsg');
        const timeEl  = document.getElementById('callTime');
        const success = document.getElementById('state-success');

        if (data.data.status === 'already_called') {
          icon.className = 'success-icon already-icon';
          icon.textContent = '⚠️';
          msgEl.className = 'status-msg status-already';
          msgEl.textContent = '⚠️ الطالب تم استدعاؤه مسبقاً اليوم';
        } else {
          msgEl.textContent = '✅ تم الاستدعاء بنجاح';
          if (data.data.call_time) {
            timeEl.textContent = 'وقت الاستدعاء: ' + data.data.call_time;
          }
        }

        success.classList.add('active');
      } else {
        document.getElementById('errorMsg').textContent = data.message || 'حدث خطأ غير متوقع';
        document.getElementById('state-error').classList.add('active');
      }
    } catch(e) {
      document.getElementById('state-loading').classList.remove('active');
      document.getElementById('errorMsg').textContent = 'خطأ في الاتصال بالخادم';
      document.getElementById('state-error').classList.add('active');
    }
  })();
  </script>
  <?php endif; ?>
</div>

</body>
</html>
