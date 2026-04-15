<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>واجهة المعلم - نظام الاستدعاء</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
<style>
:root {
  --primary: #1a3a5c;
  --accent: #f0a500;
  --accent-glow: rgba(240,165,0,0.2);
  --danger: #e74c3c;
  --danger-bg: #fff0ef;
  --danger-border: rgba(231,76,60,0.25);
  --success: #1a8a4a;
  --success-bg: #f0fff6;
  --bg: #f0f4f9;
  --bg-card: #fff;
  --text-main: #1a2535;
  --text-muted: #6b7a8d;
  --border: #dde3ed;
  --radius: 16px;
  --shadow: 0 4px 20px rgba(0,0,0,0.08);
}

* { margin:0; padding:0; box-sizing:border-box; }

body {
  font-family: 'Tajawal', sans-serif;
  background: var(--bg);
  color: var(--text-main);
  min-height: 100vh;
}

/* Header */
.top-header {
  background: var(--primary);
  color: #fff;
  padding: 0 24px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 50;
  box-shadow: 0 2px 16px rgba(0,0,0,0.2);
}

.header-brand {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, var(--accent), #e8940d);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.header-title { font-size: 18px; font-weight: 800; }
.header-sub   { font-size: 12px; color: rgba(255,255,255,0.6); }

.header-right { display: flex; align-items: center; gap: 12px; }

.live-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  background: rgba(46,204,113,0.15);
  border: 1px solid rgba(46,204,113,0.3);
  color: #5dde8a;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 700;
}

.live-dot {
  width: 8px;
  height: 8px;
  background: #2ecc71;
  border-radius: 50%;
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50%       { opacity: 0.5; transform: scale(0.8); }
}

.time-display {
  font-size: 14px;
  font-weight: 700;
  color: rgba(255,255,255,0.8);
  font-variant-numeric: tabular-nums;
}

/* Content */
.content { max-width: 1200px; margin: 0 auto; padding: 28px 20px; }

/* Class selector */
.class-selector-card {
  background: var(--bg-card);
  border-radius: var(--radius);
  border: 1px solid var(--border);
  box-shadow: var(--shadow);
  overflow: hidden;
  margin-bottom: 24px;
}

.cs-header {
  background: linear-gradient(135deg, var(--primary), #1e4976);
  padding: 20px 24px;
  color: #fff;
}

.cs-header h2 { font-size: 18px; font-weight: 800; }
.cs-header p  { font-size: 13px; color: rgba(255,255,255,0.7); margin-top: 4px; }

.cs-body { padding: 24px; }

.grade-section { margin-bottom: 20px; }
.grade-label {
  font-size: 12px;
  font-weight: 700;
  color: var(--text-muted);
  letter-spacing: 0.5px;
  text-transform: uppercase;
  margin-bottom: 10px;
}

.class-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.class-btn {
  padding: 10px 20px;
  border-radius: 24px;
  border: 2px solid var(--border);
  background: var(--bg);
  font-family: 'Tajawal', sans-serif;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  color: var(--text-main);
  position: relative;
}

.class-btn:hover {
  border-color: var(--accent);
  color: var(--accent);
  background: rgba(240,165,0,0.05);
}

.class-btn.selected {
  background: var(--accent);
  border-color: var(--accent);
  color: #1a1a1a;
  box-shadow: 0 4px 16px var(--accent-glow);
}

.class-btn .call-count {
  position: absolute;
  top: -6px;
  left: -6px;
  width: 20px;
  height: 20px;
  background: var(--danger);
  color: #fff;
  border-radius: 50%;
  font-size: 10px;
  font-weight: 900;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 6px rgba(231,76,60,0.4);
}

/* Students panel */
#studentsPanel { display: none; }

.panel-header {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 20px 24px;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  box-shadow: var(--shadow);
}

.panel-title { font-size: 20px; font-weight: 800; }
.panel-stats { display: flex; gap: 10px; flex-wrap: wrap; }

.stat-pill {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 700;
}

.stat-pill.total   { background: #e8f0fe; color: #1a3a5c; }
.stat-pill.called  { background: #fff0ef; color: var(--danger); }
.stat-pill.waiting { background: #f0fff6; color: var(--success); }

/* Student list */
.student-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 12px;
}

.student-row {
  background: var(--bg-card);
  border: 2px solid var(--border);
  border-radius: 14px;
  padding: 18px 20px;
  transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
  position: relative;
  overflow: hidden;
}

.student-row::after {
  content: '';
  position: absolute;
  right: 0; top: 0; bottom: 0;
  width: 4px;
  background: var(--border);
  transition: background 0.3s;
}

.student-row.is-called {
  border-color: var(--danger-border);
  background: var(--danger-bg);
}

.student-row.is-called::after { background: var(--danger); }

/* Flash animation on new call */
@keyframes newCallFlash {
  0%   { background: rgba(231,76,60,0.3); transform: scale(1.02); }
  50%  { background: rgba(231,76,60,0.1); }
  100% { background: var(--danger-bg); transform: scale(1); }
}

.student-row.flash-call { animation: newCallFlash 0.8s ease; }

.sr-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 8px;
}

.sr-number {
  font-size: 11px;
  color: var(--text-muted);
  font-weight: 600;
}

.sr-indicator {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #c8d6e5;
  flex-shrink: 0;
  margin-top: 3px;
  transition: all 0.3s;
}

.sr-indicator.called {
  background: var(--danger);
  box-shadow: 0 0 8px rgba(231,76,60,0.5);
  animation: indicatorPulse 2s ease-in-out infinite;
}

@keyframes indicatorPulse {
  0%, 100% { box-shadow: 0 0 8px rgba(231,76,60,0.5); }
  50%       { box-shadow: 0 0 16px rgba(231,76,60,0.8); }
}

.sr-name {
  font-size: 17px;
  font-weight: 800;
  color: var(--text-main);
  line-height: 1.3;
  margin-bottom: 4px;
  transition: color 0.3s;
}

.sr-name.called-name { color: var(--danger); }

.sr-status {
  font-size: 12px;
  font-weight: 700;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  gap: 5px;
}

.sr-status.is-called-status { color: var(--danger); }

.sr-call-info {
  margin-top: 10px;
  padding: 10px 12px;
  background: rgba(231,76,60,0.08);
  border-radius: 8px;
  font-size: 12px;
  color: #a33;
  line-height: 1.8;
  display: none;
}

.student-row.is-called .sr-call-info { display: block; }

/* Notification bar */
.notification-bar {
  position: fixed;
  bottom: 24px;
  right: 50%;
  transform: translateX(50%);
  background: var(--danger);
  color: #fff;
  padding: 16px 28px;
  border-radius: 16px;
  font-size: 16px;
  font-weight: 800;
  box-shadow: 0 8px 32px rgba(231,76,60,0.4);
  z-index: 999;
  display: none;
  align-items: center;
  gap: 12px;
  white-space: nowrap;
  max-width: 90vw;
  animation: notifIn 0.4s cubic-bezier(0.34,1.56,0.64,1);
}

.notification-bar.show { display: flex; }

@keyframes notifIn {
  from { opacity:0; transform: translateX(50%) translateY(20px); }
  to   { opacity:1; transform: translateX(50%) translateY(0); }
}

.notif-close {
  background: rgba(255,255,255,0.2);
  border: none;
  border-radius: 6px;
  color: #fff;
  cursor: pointer;
  padding: 2px 8px;
  font-size: 16px;
}

/* Empty state */
.empty-state { text-align:center; padding:60px 20px; color:var(--text-muted); }
.empty-icon  { font-size:64px; margin-bottom:16px; }
.empty-state h3 { font-size:20px; font-weight:800; margin-bottom:8px; }

/* Responsive */
@media (max-width:768px) {
  .content { padding:16px; }
  .student-list { grid-template-columns: 1fr 1fr; }
  .sr-name { font-size:14px; }
  .top-header { padding:0 16px; }
  .header-title { font-size:15px; }
  .live-badge span { display:none; }
}

@media (max-width:480px) {
  .student-list { grid-template-columns: 1fr; }
  .panel-header { flex-direction:column; align-items:flex-start; }
}
</style>
</head>
<body>

<!-- TOP HEADER -->
<header class="top-header">
  <div class="header-brand">
    <div class="header-icon">📋</div>
    <div>
      <div class="header-title">نظام استدعاء الطلاب</div>
      <div class="header-sub">واجهة المعلم</div>
    </div>
  </div>
  <div class="header-right">
    <div class="live-badge">
      <div class="live-dot"></div>
      <span>مباشر</span>
    </div>
    <div class="time-display" id="clockDisplay">--:--</div>
  </div>
</header>

<!-- CONTENT -->
<div class="content">

  <!-- Class Selector -->
  <div class="class-selector-card">
    <div class="cs-header">
      <h2>📚 اختر صفك الدراسي</h2>
      <p>اختر الصف الذي تدرّس فيه حالياً لمتابعة استدعاءات الطلاب</p>
    </div>
    <div class="cs-body" id="classSelector">
      <div style="text-align:center;padding:30px;color:var(--text-muted)">جاري تحميل الصفوف...</div>
    </div>
  </div>

  <!-- Students Panel -->
  <div id="studentsPanel">
    <div class="panel-header">
      <div>
        <div class="panel-title" id="panelClassName">الصف</div>
        <div style="font-size:13px;color:var(--text-muted);margin-top:4px" id="panelDate"></div>
      </div>
      <div class="panel-stats" id="panelStats"></div>
    </div>
    <div class="student-list" id="studentList"></div>
  </div>

</div>

<!-- Notification bar for new call -->
<div class="notification-bar" id="notifBar">
  <span>🔔</span>
  <span id="notifText"></span>
  <button class="notif-close" onclick="hideNotif()">✕</button>
</div>

<script>
const API = window.location.origin + '/api/data.php';

async function apiGet(action, params = {}) {
  let url = `${API}?action=${action}`;
  for (const k in params) url += `&${k}=${encodeURIComponent(params[k])}`;
  const r = await fetch(url);
  return r.json();
}

function formatTime(t) {
  if (!t) return '';
  const [h, m] = t.split(':');
  const hr = parseInt(h);
  return `${hr % 12 || 12}:${m} ${hr >= 12 ? 'م' : 'ص'}`;
}

// Clock
function updateClock() {
  const now = new Date();
  const h = now.getHours().toString().padStart(2,'0');
  const m = now.getMinutes().toString().padStart(2,'0');
  const s = now.getSeconds().toString().padStart(2,'0');
  document.getElementById('clockDisplay').textContent = `${h}:${m}:${s}`;
}
setInterval(updateClock, 1000);
updateClock();

// ---- Load Classes ----
let classCallCounts = {};

async function loadClasses() {
  const [cr, lr] = await Promise.all([
    apiGet('get_classes'),
    apiGet('get_today_log')
  ]);

  // Count calls per class
  classCallCounts = {};
  (lr.data || []).forEach(d => {
    // We'd need class_id, but we have class_name — skip for now
  });

  const selector = document.getElementById('classSelector');
  const classes  = cr.data || [];

  if (!classes.length) {
    selector.innerHTML = '<p style="color:var(--text-muted);text-align:center;padding:20px">لا توجد صفوف مسجلة</p>';
    return;
  }

  // Group by grade
  const grades = {};
  classes.forEach(c => {
    const g = c.grade || 'أخرى';
    if (!grades[g]) grades[g] = [];
    grades[g].push(c);
  });

  let html = '';
  for (const grade in grades) {
    html += `<div class="grade-section">
      <div class="grade-label">المرحلة ${grade}</div>
      <div class="class-grid">
        ${grades[grade].map(c => `
          <button class="class-btn" id="cbtn-${c.id}" onclick="selectClass(${c.id}, '${c.name}')">
            ${c.name}
          </button>`).join('')}
      </div>
    </div>`;
  }
  selector.innerHTML = html;
}

// ---- Select Class ----
let selectedClassId = null;
let prevStudents    = {};

function selectClass(id, name) {
  selectedClassId = id;
  document.querySelectorAll('.class-btn').forEach(b => b.classList.remove('selected'));
  document.getElementById('cbtn-' + id).classList.add('selected');
  document.getElementById('studentsPanel').style.display = 'block';
  document.getElementById('panelClassName').textContent = `📚 الصف ${name}`;
  document.getElementById('panelDate').textContent = new Date().toLocaleDateString('ar-SA', {weekday:'long',year:'numeric',month:'long',day:'numeric'});
  loadStudents();
}

async function loadStudents() {
  if (!selectedClassId) return;
  const r = await apiGet('get_students', {class_id: selectedClassId});
  const students = r.data || [];

  // Detect new calls since last refresh
  const newCalls = [];
  students.forEach(s => {
    if (s.call_id && !prevStudents[s.id]) newCalls.push(s);
    prevStudents[s.id] = s.call_id;
  });

  // Update stats
  const called  = students.filter(s => s.call_id).length;
  const waiting = students.length - called;
  document.getElementById('panelStats').innerHTML = `
    <span class="stat-pill total">📊 ${students.length} طالب</span>
    <span class="stat-pill called">🔴 مستدعى: ${called}</span>
    <span class="stat-pill waiting">🟢 لم يُستدعَ: ${waiting}</span>
  `;

  if (!students.length) {
    document.getElementById('studentList').innerHTML = `
      <div class="empty-state" style="grid-column:1/-1">
        <div class="empty-icon">👨‍🎓</div>
        <h3>لا يوجد طلاب في هذا الصف</h3>
      </div>`;
    return;
  }

  // Sort: called first (by time desc), then uncalled
  students.sort((a, b) => {
    if (a.call_id && !b.call_id) return -1;
    if (!a.call_id && b.call_id) return 1;
    if (a.call_id && b.call_id) return b.call_time?.localeCompare(a.call_time) || 0;
    return a.full_name.localeCompare(b.full_name);
  });

  document.getElementById('studentList').innerHTML = students.map(s => `
    <div class="student-row ${s.call_id ? 'is-called' : ''}" id="srow-${s.id}">
      <div class="sr-top">
        <div class="sr-number">${s.student_number || '#' + s.id}</div>
        <div class="sr-indicator ${s.call_id ? 'called' : ''}"></div>
      </div>
      <div class="sr-name ${s.call_id ? 'called-name' : ''}">${s.full_name}</div>
      <div class="sr-status ${s.call_id ? 'is-called-status' : ''}">
        ${s.call_id ? '🔴 تم استدعاؤه' : '🟢 لم يُستدعَ'}
      </div>
      ${s.call_id ? `
        <div class="sr-call-info">
          ⏰ وقت الاستدعاء: <strong>${formatTime(s.call_time)}</strong><br>
          👤 بواسطة: <strong>${s.called_by_name}</strong>
        </div>` : ''}
    </div>`).join('');

  // Flash new calls
  newCalls.forEach(s => {
    const row = document.getElementById('srow-' + s.id);
    if (row) { row.classList.add('flash-call'); setTimeout(() => row.classList.remove('flash-call'), 1000); }
  });

  // Show notification for new calls
  if (newCalls.length > 0) {
    showNotif(`طلب استدعاء: ${newCalls.map(s => s.full_name).join(' | ')}`);
  }
}

// ---- Notifications ----
function showNotif(text) {
  document.getElementById('notifText').textContent = text;
  const bar = document.getElementById('notifBar');
  bar.classList.add('show');
  clearTimeout(window._notifTimer);
  window._notifTimer = setTimeout(hideNotif, 6000);
}

function hideNotif() {
  document.getElementById('notifBar').classList.remove('show');
}

// ---- Auto refresh every 10 seconds ----
setInterval(() => { if (selectedClassId) loadStudents(); }, 10000);

// Update call badge counts on class buttons every 30s
async function refreshClassCounts() {
  if (!document.getElementById('classSelector').querySelector('.class-btn')) return;
  // For simplicity, reload log and re-render counts would require refactor
  // Left as future enhancement
}

// Init
loadClasses();
</script>
</body>
</html>
