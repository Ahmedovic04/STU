<?php
require_once '../includes/config.php';
requireAdmin();
$user = currentUser();
$pageTitle = 'لوحة المدير - ' . SITE_NAME;

$db = getDB();
$today = date('Y-m-d');

$totalClasses  = $db->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$totalStudents = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalUsers    = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

$calledToday = $db->prepare("SELECT COUNT(*) FROM dismissal_calls WHERE call_date=?");
$calledToday->execute([$today]);
$calledToday = $calledToday->fetchColumn();

include '../includes/header.php';
?>

<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar">

  <div class="sidebar-logo">
    <div class="logo-icon">🏫</div>
    <div>
      <div class="logo-text">نظام الاستدعاء</div>
      <div class="logo-sub">لوحة المدير</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <a class="nav-item active" onclick="showSection('dashboard')">📊 الرئيسية</a>
    <a class="nav-item" onclick="showSection('classes')">🏛️ الصفوف</a>
    <a class="nav-item" onclick="showSection('students')">👨‍🎓 الطلاب</a>
    <a class="nav-item" onclick="showSection('users')">👥 المستخدمون</a>
    <a class="nav-item" onclick="showSection('log')">📋 السجل</a>
    <a class="nav-item" onclick="showSection('report')">📊 التقارير</a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar">👤</div>
      <div>
        <div><?= htmlspecialchars($user['full_name']) ?></div>
        <div>مدير النظام</div>
      </div>
    </div>

    <a href="../api/auth.php?action=logout" class="btn-logout">🚪 تسجيل خروج</a>
  </div>

</aside>

<!-- MAIN -->
<div class="main-wrapper">

  <div class="topbar">
    <button onclick="toggleSidebar()">☰</button>
    <div>لوحة التحكم</div>
    <div id="todayDate"></div>
  </div>

  <div class="page-content">

    <!-- ===== DASHBOARD ===== -->
    <div id="section-dashboard">

      <!-- 🔴 زر تصفير الاستدعاءات -->
      <div style="display:flex;justify-content:flex-end;margin-bottom:15px">
        <button class="btn btn-danger" onclick="resetCalls()">
          🧹 تصفير الاستدعاءات
        </button>
      </div>

      <div class="stats-grid">
        <div class="stat-card">🏛️ الصفوف: <?= $totalClasses ?></div>
        <div class="stat-card">👨‍🎓 الطلاب: <?= $totalStudents ?></div>
        <div class="stat-card">🔔 اليوم: <?= $calledToday ?></div>
        <div class="stat-card">👥 المستخدمين: <?= $totalUsers ?></div>
      </div>

      <div class="card">
        <h3>آخر الاستدعاءات</h3>
        <div id="dashLogBody">جاري التحميل...</div>
      </div>

    </div>

    <!-- ===== REPORT ===== -->
    <div id="section-report" style="display:none">

      <div class="card">
        <h2>📊 تقرير الاستدعاءات</h2>

        <div style="display:flex;gap:10px;margin:15px 0">
          <input type="date" id="fromDate">
          <input type="date" id="toDate">
          <button onclick="loadReport()">عرض</button>
        </div>

        <div id="reportBody">حدد تاريخ</div>
      </div>

    </div>

    <!-- باقي الأقسام موجودة عندك (بدون تغيير) -->

  </div>
</div>

<script src="../assets/js/common.js"></script>

<script>
document.getElementById('todayDate').textContent = new Date().toLocaleDateString();

function showSection(name) {
  document.querySelectorAll('[id^="section-"]').forEach(e => e.style.display = 'none');
  document.getElementById('section-' + name).style.display = 'block';
}

// =======================
// 🔴 RESET CALLS
// =======================
async function resetCalls() {
  if (!confirm("هل أنت متأكد من تصفير الاستدعاءات؟")) return;

  const r = await api('reset_calls', 'POST', new FormData());

  if (r.success) {
    alert("تم التصفير بنجاح");
    loadDashboard();
    loadLog();
  } else {
    alert(r.message);
  }
}

// =======================
// 📊 REPORT
// =======================
async function loadReport() {
  const from = document.getElementById('fromDate').value;
  const to = document.getElementById('toDate').value;

  const r = await apiGet('get_report', { from, to });

  const body = document.getElementById('reportBody');

  if (!r.data?.length) {
    body.innerHTML = "لا توجد بيانات";
    return;
  }

  body.innerHTML = `
    <table border="1" width="100%">
      <tr>
        <th>الوقت</th>
        <th>الطالب</th>
        <th>الصف</th>
        <th>المستدعي</th>
      </tr>
      ${r.data.map(d => `
        <tr>
          <td>${d.call_time}</td>
          <td>${d.student_name}</td>
          <td>${d.class_name}</td>
          <td>${d.called_by_name}</td>
        </tr>
      `).join('')}
    </table>
  `;
}
</script>

</body>
</html>
