<?php
require_once '../includes/config.php';
requireLogin('management');
$user = currentUser();
$pageTitle = 'واجهة الإدارة - ' . SITE_NAME;
include '../includes/header.php';
?>

<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon">📋</div>
    <div>
      <div class="logo-text">مدرسة معيذر</div>
      <div class="logo-sub">واجهة الإداري</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">الاستدعاء</div>
    <a class="nav-item active" onclick="showSection('call')"><span class="nav-icon">🔔</span> استدعاء الطلاب</a>
    <a class="nav-item" onclick="showSection('log')"><span class="nav-icon">📋</span> سجل اليوم</a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar">👤</div>
      <div class="user-info">
        <div class="user-name"><?= htmlspecialchars($user['full_name']) ?></div>
        <div class="user-role">إداري</div>
      </div>
    </div>
    <a href="../api/auth.php?action=logout" class="btn-logout">🚪 تسجيل الخروج</a>
  </div>
</aside>

<div class="main-wrapper">
  <div class="topbar">
    <div style="display:flex;align-items:center;gap:12px">
      <button class="hamburger" onclick="toggleSidebar()">☰</button>
      <div class="topbar-title" id="sectionTitle">استدعاء الطلاب</div>
    </div>
    <div class="topbar-right">
      <div class="topbar-date" id="todayDate"></div>
    </div>
  </div>

  <div class="page-content">

    <!-- ===== CALL SECTION ===== -->
    <div id="section-call">

      <!-- Step 1: Choose class -->
      <div class="card" style="margin-bottom:20px">
        <div class="card-header"><h2>1️⃣ اختر الصف</h2></div>
        <div class="card-body">
          <div class="pill-grid" id="classPills">
            <div class="skeleton" style="width:80px;height:36px;border-radius:20px"></div>
            <div class="skeleton" style="width:80px;height:36px;border-radius:20px"></div>
            <div class="skeleton" style="width:80px;height:36px;border-radius:20px"></div>
          </div>
        </div>
      </div>

      <!-- Step 2: Choose student -->
      <div class="card" id="studentsCard" style="display:none">
        <div class="card-header">
          <h2>2️⃣ اختر الطالب للاستدعاء</h2>
          <div style="display:flex;gap:8px;align-items:center">
            <input type="text" class="form-control" id="studentSearch" placeholder="🔍 بحث عن طالب..." style="width:220px;margin-bottom:0" oninput="filterStudents()">
            <button class="btn btn-ghost btn-sm" onclick="loadStudents()">🔄</button>
          </div>
        </div>
        <div class="card-body">
          <div id="callStats" style="margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap"></div>
          <div class="student-grid" id="studentGrid"></div>
        </div>
      </div>
    </div>

    <!-- ===== LOG SECTION ===== -->
    <div id="section-log" style="display:none">
      <div class="card">
        <div class="card-header">
          <h2>📋 استدعاءاتي اليوم</h2>
          <button class="btn btn-ghost btn-sm" onclick="loadLog()">🔄 تحديث</button>
        </div>
        <div class="card-body" id="logBody"></div>
      </div>
    </div>

  </div>
</div>

<div id="toast-container"></div>

<style>
/* Student grid for management */
.student-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px;
}

.student-card {
  border: 2px solid var(--border);
  border-radius: 14px;
  padding: 16px 18px;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
  background: var(--bg-card);
  position: relative;
  overflow: hidden;
}

.student-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, transparent, rgba(240,165,0,0.05));
  opacity: 0;
  transition: opacity 0.3s;
}

.student-card:hover {
  border-color: var(--accent);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(240,165,0,0.15);
}

.student-card:hover::before { opacity: 1; }

.student-card.called {
  border-color: var(--danger);
  background: linear-gradient(135deg, #fff5f5, #fff);
  cursor: default;
}

.student-card.called:hover {
  transform: none;
  box-shadow: 0 4px 12px rgba(231,76,60,0.15);
}

.sc-number {
  font-size: 11px;
  color: var(--text-muted);
  font-weight: 600;
  margin-bottom: 6px;
}

.sc-name {
  font-size: 16px;
  font-weight: 800;
  color: var(--text-main);
  margin-bottom: 8px;
  line-height: 1.3;
}

.sc-name.called-name { color: var(--danger); }

.sc-status {
  font-size: 12px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 5px;
}

.sc-status.free   { color: #1a8a4a; }
.sc-status.called { color: var(--danger); }

.sc-meta {
  font-size: 11px;
  color: var(--text-muted);
  margin-top: 6px;
  line-height: 1.6;
}

/* Call animation */
@keyframes callPulse {
  0%   { box-shadow: 0 0 0 0 rgba(231,76,60,0.4); }
  70%  { box-shadow: 0 0 0 12px rgba(231,76,60,0); }
  100% { box-shadow: 0 0 0 0 rgba(231,76,60,0); }
}

.student-card.just-called { animation: callPulse 0.6s ease; }

@media (max-width: 600px) {
  .student-grid { grid-template-columns: 1fr 1fr; }
  .sc-name { font-size: 14px; }
}
</style>

<script src="../assets/js/common.js"></script>
<script>
document.getElementById('todayDate').textContent = arabicDate();

let currentClassId = null;
let allStudents    = [];

function showSection(name) {
  document.querySelectorAll('[id^="section-"]').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
  document.getElementById('section-' + name).style.display = 'block';
  document.getElementById('sectionTitle').textContent = name === 'call' ? 'استدعاء الطلاب' : 'سجل اليوم';
  document.querySelectorAll('.nav-item').forEach(item => {
    if (item.getAttribute('onclick')?.includes(name)) item.classList.add('active');
  });
  document.querySelector('.sidebar').classList.remove('open');
  document.querySelector('.sidebar-overlay').classList.remove('open');
  if (name === 'log') loadLog();
}

// ---- Load classes as pills ----
async function loadClasses() {
  const r = await apiGet('get_classes');
  const pills = document.getElementById('classPills');
  if (!r.data?.length) { pills.innerHTML = '<p style="color:var(--text-muted)">لا توجد صفوف</p>'; return; }

  // Group by grade
  const grades = {};
  r.data.forEach(c => {
    if (!grades[c.grade || 'أخرى']) grades[c.grade || 'أخرى'] = [];
    grades[c.grade || 'أخرى'].push(c);
  });

  let html = '';
  for (const grade in grades) {
    html += `<div style="margin-bottom:12px">
      <div style="font-size:12px;font-weight:700;color:var(--text-muted);margin-bottom:8px;letter-spacing:0.5px">المرحلة ${grade}</div>
      <div class="pill-grid">
        ${grades[grade].map(c => `
          <button class="pill" id="pill-${c.id}" onclick="selectClass(${c.id}, '${c.name}')">
            ${c.name}
          </button>`).join('')}
      </div>
    </div>`;
  }
  pills.innerHTML = html;
}

function selectClass(id, name) {
  currentClassId = id;
  document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
  document.getElementById('pill-' + id).classList.add('active');
  document.getElementById('studentsCard').style.display = 'block';
  document.getElementById('studentsCard').scrollIntoView({ behavior: 'smooth', block: 'start' });
  loadStudents();
}

async function loadStudents() {
  if (!currentClassId) return;
  const grid = document.getElementById('studentGrid');
  grid.innerHTML = `<div class="skeleton" style="height:90px;border-radius:14px"></div>`.repeat(6);
  const r = await apiGet('get_students', {class_id: currentClassId});
  allStudents = r.data || [];
  renderStudents(allStudents);
}

function renderStudents(students) {
  const grid = document.getElementById('studentGrid');
  const stats = document.getElementById('callStats');

  const called = students.filter(s => s.call_id);
  const free   = students.filter(s => !s.call_id);

  stats.innerHTML = `
    <span class="badge badge-called">🔴 مستدعى: ${called.length}</span>
    <span class="badge badge-free">🟢 لم يُستدعَ: ${free.length}</span>
    <span class="badge badge-admin">📊 الإجمالي: ${students.length}</span>
  `;

  if (!students.length) {
    grid.innerHTML = '<div class="empty-state"><div class="empty-state-icon">👨‍🎓</div><h3>لا يوجد طلاب في هذا الصف</h3></div>';
    return;
  }

  grid.innerHTML = students.map(s => `
    <div class="student-card ${s.call_id ? 'called' : ''}"
         id="scard-${s.id}"
         onclick="${s.call_id ? '' : `callStudent(${s.id})`}">
      <div class="sc-number">${s.student_number || '#' + s.id}</div>
      <div class="sc-name ${s.call_id ? 'called-name' : ''}">${s.full_name}</div>
      <div class="sc-status ${s.call_id ? 'called' : 'free'}">
        ${s.call_id
          ? `🔴 تم الاستدعاء`
          : `🟢 لم يُستدعَ بعد`}
      </div>
      ${s.call_id ? `
        <div class="sc-meta">
          ⏰ ${formatTime(s.call_time)}<br>
          👤 ${s.called_by_name}
        </div>` : ''}
    </div>`).join('');
}

function filterStudents() {
  const q = document.getElementById('studentSearch').value.trim().toLowerCase();
  if (!q) { renderStudents(allStudents); return; }
  renderStudents(allStudents.filter(s => s.full_name.toLowerCase().includes(q) || (s.student_number||'').includes(q)));
}

async function callStudent(studentId) {
  const card = document.getElementById('scard-' + studentId);
  if (!card || card.classList.contains('called')) return;

  // Optimistic UI
  card.style.opacity = '0.5';
  card.style.pointerEvents = 'none';

  const fd = new FormData(); fd.append('student_id', studentId);
  const r = await api('call_student', 'POST', fd);

  if (r.success) {
    toast('تم استدعاء الطالب بنجاح 🔔');
    card.classList.add('called', 'just-called');
    // Update local data and re-render
    const s = allStudents.find(s => s.id == studentId);
    if (s) {
      s.call_id = 1;
      s.call_time = new Date().toTimeString().slice(0,8);
      s.called_by_name = '<?= addslashes($user['full_name']) ?>';
    }
    renderStudents(allStudents);
  } else {
    toast(r.message, 'error');
    card.style.opacity = '1';
    card.style.pointerEvents = '';
  }
}

// ---- Log ----
async function loadLog() {
  const r = await apiGet('get_today_log');
  const body = document.getElementById('logBody');
  if (!r.data?.length) {
    body.innerHTML = '<div class="empty-state"><div class="empty-state-icon">📋</div><h3>لا توجد استدعاءات اليوم</h3><p>قم باستدعاء الطلاب من الصفحة الرئيسية</p></div>';
    return;
  }
  body.innerHTML = `<div class="table-wrap"><table>
    <thead><tr><th>الوقت</th><th>الطالب</th><th>الصف</th><th>بواسطة</th></tr></thead>
    <tbody>${r.data.map(d => `
      <tr>
        <td><span class="badge badge-called">🕐 ${formatTime(d.call_time)}</span></td>
        <td style="font-weight:700">${d.student_name}</td>
        <td>${d.class_name}</td>
        <td>${d.called_by_name}</td>
      </tr>`).join('')}
    </tbody>
  </table></div>`;
}

// Auto-refresh students every 30s
setInterval(() => { if (currentClassId) loadStudents(); }, 30000);

// Init
loadClasses();
</script>
</body>
</html>
