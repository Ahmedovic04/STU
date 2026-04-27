<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>نظام استدعاء الطلاب - تسجيل الدخول</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
<style>
  :root {
    --primary: #1a3a5c;
    --primary-light: #1e4976;
    --accent: #f0a500;
    --accent-glow: rgba(240,165,0,0.3);
    --bg-dark: #0d1f33;
    --bg-card: rgba(255,255,255,0.05);
    --text-main: #e8f0fe;
    --text-muted: #8eaac8;
    --error: #e74c3c;
    --success: #2ecc71;
    --border: rgba(255,255,255,0.1);
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }

  html, body {
    height: 100%;
    width: 100%;
    margin: 0;
    padding: 0;
  }

  body {
    font-family: 'Tajawal', sans-serif;
    background: var(--bg-dark);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow-x: hidden;
  }

  /* Animated background */
  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background:
      radial-gradient(ellipse 80% 60% at 20% 10%, rgba(26,58,92,0.8) 0%, transparent 60%),
      radial-gradient(ellipse 60% 50% at 80% 90%, rgba(240,165,0,0.1) 0%, transparent 50%),
      linear-gradient(135deg, #0d1f33 0%, #0a1628 50%, #0f2a1a 100%);
    z-index: 0;
  }

  /* Floating orbs */
  .orb {
    position: fixed;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.15;
    animation: float 8s ease-in-out infinite;
    pointer-events: none;
  }
  .orb-1 { width: 400px; height: 400px; background: #1a3a5c; top: -100px; right: -100px; animation-delay: 0s; }
  .orb-2 { width: 300px; height: 300px; background: #f0a500; bottom: -50px; left: -50px; animation-delay: 3s; }
  .orb-3 { width: 200px; height: 200px; background: #1e7a4e; top: 50%; left: 20%; animation-delay: 6s; }

  @keyframes float {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(-30px) scale(1.05); }
  }

  /* Grid pattern overlay */
  body::after {
    content: '';
    position: fixed;
    inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
    background-size: 40px 40px;
    z-index: 0;
  }

  .login-wrapper {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 480px;
    padding: 20px;
  }

  /* Logo / Header */
  .school-header {
    text-align: center;
    margin-bottom: 40px;
    animation: slideDown 0.6s ease both;
  }

  .school-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--accent), #e8940d);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    box-shadow: 0 0 40px var(--accent-glow), 0 8px 32px rgba(0,0,0,0.4);
    transform: rotate(-5deg);
    font-size: 36px;
  }

  .school-title {
    font-size: 28px;
    font-weight: 800;
    color: var(--text-main);
    letter-spacing: -0.5px;
    line-height: 1.2;
  }

  .school-subtitle {
    font-size: 14px;
    color: var(--text-muted);
    margin-top: 6px;
    font-weight: 400;
  }

  /* Login Card */
  .login-card {
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 40px;
    backdrop-filter: blur(20px);
    box-shadow:
      0 32px 64px rgba(0,0,0,0.4),
      inset 0 1px 0 rgba(255,255,255,0.08);
    animation: slideUp 0.6s ease 0.2s both;
  }

  .card-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .card-title::before {
    content: '';
    display: block;
    width: 4px;
    height: 24px;
    background: var(--accent);
    border-radius: 2px;
  }

  /* Role selector */
  .role-tabs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-bottom: 28px;
    background: rgba(0,0,0,0.2);
    border-radius: 12px;
    padding: 6px;
  }

  .role-tab {
    padding: 10px;
    border: none;
    border-radius: 8px;
    background: transparent;
    color: var(--text-muted);
    font-family: 'Tajawal', sans-serif;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
  }

  .role-tab.active {
    background: var(--accent);
    color: #1a1a1a;
    font-weight: 700;
    box-shadow: 0 4px 12px var(--accent-glow);
  }

  /* Teacher access (no login) */
  .teacher-access {
    text-align: center;
    padding: 20px;
    display: none;
  }

  .teacher-access.show { display: block; }

  .teacher-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #1e7a4e, #155c3b);
    color: white;
    padding: 16px 32px;
    border-radius: 14px;
    text-decoration: none;
    font-size: 18px;
    font-weight: 700;
    transition: all 0.3s;
    box-shadow: 0 8px 24px rgba(30,122,78,0.3);
    border: none;
    cursor: pointer;
    font-family: 'Tajawal', sans-serif;
    width: 100%;
    justify-content: center;
  }

  .teacher-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(30,122,78,0.4);
  }

  .teacher-note {
    color: var(--text-muted);
    font-size: 13px;
    margin-top: 16px;
    line-height: 1.6;
  }

  /* Form */
  .login-form { display: block; }
  .login-form.hide { display: none; }

  .form-group {
    margin-bottom: 20px;
  }

  .form-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
    margin-bottom: 8px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }

  .form-input {
    width: 100%;
    background: rgba(0,0,0,0.3);
    border: 1.5px solid var(--border);
    border-radius: 12px;
    padding: 14px 18px;
    font-family: 'Tajawal', sans-serif;
    font-size: 16px;
    color: var(--text-main);
    transition: all 0.3s;
    outline: none;
    -webkit-appearance: none;
  }

  .form-input:focus {
    border-color: var(--accent);
    background: rgba(240,165,0,0.05);
    box-shadow: 0 0 0 4px var(--accent-glow);
  }

  .form-input::placeholder { color: rgba(142,170,200,0.4); }

  .btn-login {
    width: 100%;
    background: linear-gradient(135deg, var(--accent), #e8940d);
    color: #1a1a1a;
    border: none;
    border-radius: 14px;
    padding: 16px;
    font-family: 'Tajawal', sans-serif;
    font-size: 18px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 8px 24px var(--accent-glow);
    margin-top: 8px;
  }

  .btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(240,165,0,0.4);
  }

  .btn-login:active { transform: translateY(0); }

  /* Alert */
  .alert {
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 20px;
    display: none;
  }

  .alert.show { display: block; }
  .alert-error { background: rgba(231,76,60,0.15); border: 1px solid rgba(231,76,60,0.3); color: #ff7675; }

  /* Default credentials note */
  .creds-note {
    margin-top: 20px;
    padding: 14px;
    background: rgba(240,165,0,0.08);
    border: 1px solid rgba(240,165,0,0.2);
    border-radius: 10px;
    font-size: 12px;
    color: var(--text-muted);
    line-height: 1.8;
    text-align: center;
  }

  .creds-note strong { color: var(--accent); }

  @keyframes slideDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  @keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* Loading spinner */
  .btn-login.loading::after {
    content: '';
    display: inline-block;
    width: 18px;
    height: 18px;
    border: 3px solid rgba(0,0,0,0.3);
    border-top-color: #000;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-right: 10px;
    vertical-align: middle;
  }

  @keyframes spin { to { transform: rotate(360deg); } }

  @media (max-width: 480px) {
    .login-wrapper { padding: 15px; }
    .login-card { padding: 24px 16px; border-radius: 20px; }
    .school-title { font-size: 20px; }
    .school-icon { width: 64px; height: 64px; font-size: 30px; }
    .btn-login { font-size: 16px; padding: 14px; }
    .role-tab { font-size: 13px; padding: 8px; }
  }

  @media (max-width: 360px) {
    .school-title { font-size: 18px; }
    .role-tabs { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="login-wrapper">
  <div class="school-header">
    <div class="school-icon">🏫</div>
    <h1 class="school-title">نظام استدعاء الطلاب</h1>
    <p class="school-subtitle">إدارة مغادرة طلاب السيارات الخاصة</p>
  </div>

  <div class="login-card">
    <div class="card-title">تسجيل الدخول</div>

    <!-- Role Tabs -->
    <div class="role-tabs">
      <button class="role-tab active" onclick="switchTab('staff')">👤 الإداري / المدير</button>
      <button class="role-tab" onclick="switchTab('teacher')">📋 المعلم</button>
    </div>

    <!-- Alert -->
    <div class="alert alert-error" id="loginAlert">اسم المستخدم أو كلمة المرور غير صحيحة</div>

    <!-- Staff Login Form -->
    <form class="login-form" id="staffForm" onsubmit="doLogin(event)">
      <div class="form-group">
        <label class="form-label">اسم المستخدم</label>
        <input type="text" class="form-input" id="username" placeholder="أدخل اسم المستخدم" required autocomplete="username">
      </div>
      <div class="form-group">
        <label class="form-label">كلمة المرور</label>
        <input type="password" class="form-input" id="password" placeholder="أدخل كلمة المرور" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn-login" id="loginBtn">دخول</button>

      <div class="creds-note">
        جميع الحقوق محفوظة &copy; <?= date('Y') ?> مدرسة معيذر الابتدائية للبنين
      </div>
    </form>

    <!-- Teacher Access (No Login Required) -->
    <div class="teacher-access" id="teacherAccess">
      <a href="teacher/index.php" class="teacher-btn">
        📋 دخول واجهة المعلم
      </a>
      <p class="teacher-note">
        واجهة المعلم لا تتطلب تسجيل دخول<br>
        فقط اختر الصف الذي تدرّس فيه
      </p>
    </div>
  </div>
</div>

<?php
require_once 'includes/config.php';
startSecureSession();
if (isLoggedIn()) {
    $u = currentUser();
    $redirect = $_GET['redirect'] ?? '';
    if ($redirect) {
        header('Location: ' . $redirect);
    } else {
        if ($u['role'] === 'admin') {
            header('Location: admin/index.php');
        } else {
            header('Location: management/index.php');
        }
    }
    exit;
}
?>

<script>
function switchTab(tab) {
  const tabs = document.querySelectorAll('.role-tab');
  tabs.forEach(t => t.classList.remove('active'));

  if (tab === 'staff') {
    tabs[0].classList.add('active');
    document.getElementById('staffForm').classList.remove('hide');
    document.getElementById('teacherAccess').classList.remove('show');
  } else {
    tabs[1].classList.add('active');
    document.getElementById('staffForm').classList.add('hide');
    document.getElementById('teacherAccess').classList.add('show');
  }
}

async function doLogin(e) {
  e.preventDefault();
  const btn = document.getElementById('loginBtn');
  const alert = document.getElementById('loginAlert');
  alert.classList.remove('show');
  btn.classList.add('loading');
  btn.disabled = true;

  const formData = new FormData();
  formData.append('username', document.getElementById('username').value);
  formData.append('password', document.getElementById('password').value);

  try {
    const res = await fetch('api/auth.php?action=login', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.success) {
      const urlParams = new URLSearchParams(window.location.search);
      const redirect = urlParams.get('redirect');
      window.location.href = redirect || data.data?.redirect || '/';
    } else {
      alert.textContent = data.message || 'بيانات غير صحيحة';
      alert.classList.add('show');
    }
  } catch (err) {
    alert.textContent = 'حدث خطأ، يرجى المحاولة مجدداً';
    alert.classList.add('show');
  } finally {
    btn.classList.remove('loading');
    btn.disabled = false;
  }
}
</script>
</body>
</html>
