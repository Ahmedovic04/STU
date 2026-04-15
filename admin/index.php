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
$calledToday   = $db->prepare("SELECT COUNT(*) FROM dismissal_calls WHERE call_date=?");
$calledToday->execute([$today]);
$calledToday   = $calledToday->fetchColumn();

include '../includes/header.php';
?>

<!-- Sidebar overlay -->
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
    <div class="nav-section-label">الإدارة</div>
    <a class="nav-item active" onclick="showSection('dashboard')"><span class="nav-icon">📊</span> الرئيسية</a>
    <a class="nav-item" onclick="showSection('classes')"><span class="nav-icon">🏛️</span> الصفوف</a>
    <a class="nav-item" onclick="showSection('students')"><span class="nav-icon">👨‍🎓</span> الطلاب</a>
    <a class="nav-item" onclick="showSection('users')"><span class="nav-icon">👥</span> المستخدمون</a>
    <div class="nav-section-label" style="margin-top:16px">التقارير</div>
    <a class="nav-item" onclick="showSection('log')"><span class="nav-icon">📋</span> سجل اليوم</a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar">👤</div>
      <div class="user-info">
        <div class="user-name"><?= htmlspecialchars($user['full_name']) ?></div>
        <div class="user-role">مدير النظام</div>
      </div>
    </div>
    <a href="../api/auth.php?action=logout" class="btn-logout">🚪 تسجيل الخروج</a>
  </div>
</aside>

<!-- MAIN -->
<div class="main-wrapper">
  <div class="topbar">
    <div style="display:flex;align-items:center;gap:12px">
      <button class="hamburger" onclick="toggleSidebar()">☰</button>
      <div class="topbar-title" id="sectionTitle">لوحة التحكم</div>
    </div>
    <div class="topbar-right">
      <div class="topbar-date" id="todayDate"></div>
    </div>
  </div>

  <div class="page-content">

    <!-- ===== DASHBOARD ===== -->
    <div id="section-dashboard">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon" style="background:#e8f0fe">🏛️</div>
          <div class="stat-info">
            <div class="stat-number"><?= $totalClasses ?></div>
            <div class="stat-label">إجمالي الصفوف</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:#e9f7ef">👨‍🎓</div>
          <div class="stat-info">
            <div class="stat-number"><?= $totalStudents ?></div>
            <div class="stat-label">إجمالي الطلاب</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:#fff3e0">🔔</div>
          <div class="stat-info">
            <div class="stat-number"><?= $calledToday ?></div>
            <div class="stat-label">مستدعون اليوم</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:#fce4ec">👥</div>
          <div class="stat-info">
            <div class="stat-number"><?= $totalUsers ?></div>
            <div class="stat-label">المستخدمون</div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h2>📋 آخر استدعاءات اليوم</h2></div>
        <div class="card-body" id="dashLogBody">
          <div style="text-align:center;padding:40px;color:var(--text-muted)">جاري التحميل...</div>
        </div>
      </div>
    </div>

    <!-- ===== CLASSES ===== -->
    <div id="section-classes" style="display:none">
      <div class="card">
        <div class="card-header">
          <h2>🏛️ إدارة الصفوف</h2>
          <button class="btn btn-accent" onclick="openModal('modalAddClass')">+ إضافة صف</button>
        </div>
        <div class="card-body">
          <div class="table-wrap">
            <table>
              <thead><tr><th>#</th><th>اسم الصف</th><th>المرحلة</th><th>عدد الطلاب</th><th>الإجراءات</th></tr></thead>
              <tbody id="classesTable"><tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted)">جاري التحميل...</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== STUDENTS ===== -->
    <div id="section-students" style="display:none">
      <div class="card">
        <div class="card-header">
          <h2>👨‍🎓 إدارة الطلاب</h2>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button class="btn btn-ghost" onclick="openBulkImport()">📋 استيراد قائمة</button>
            <button class="btn btn-accent" onclick="openModal('modalAddStudent')">+ إضافة طالب</button>
          </div>
        </div>
        <div class="card-body">
          <div class="form-group" style="max-width:280px">
            <select class="form-control" id="filterClass" onchange="loadStudentsAdmin()">
              <option value="">— جميع الصفوف —</option>
            </select>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>#</th><th>اسم الطالب</th><th>الرقم</th><th>الصف</th><th>الإجراءات</th></tr></thead>
              <tbody id="studentsTable"><tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted)">جاري التحميل...</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== USERS ===== -->
    <div id="section-users" style="display:none">
      <div class="card">
        <div class="card-header">
          <h2>👥 إدارة المستخدمين</h2>
          <button class="btn btn-accent" onclick="openModal('modalAddUser')">+ إضافة مستخدم</button>
        </div>
        <div class="card-body">
          <div class="table-wrap">
            <table>
              <thead><tr><th>#</th><th>الاسم</th><th>اسم المستخدم</th><th>الدور</th><th>الإجراءات</th></tr></thead>
              <tbody id="usersTable"><tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted)">جاري التحميل...</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== LOG ===== -->
    <div id="section-log" style="display:none">
      <div class="card">
        <div class="card-header">
          <h2>📋 سجل استدعاءات اليوم</h2>
          <button class="btn btn-ghost btn-sm" onclick="loadLog()">🔄 تحديث</button>
          <button onclick="resetCalls()">تصفير الاستدعاءات</button>
        </div>
        <div class="card-body" id="logBody">جاري التحميل...</div>
      </div>
    </div>

  </div><!-- /page-content -->
</div><!-- /main-wrapper -->

<!-- ===== MODALS ===== -->

<!-- Add Class Modal -->
<div class="modal-overlay" id="modalAddClass">
  <div class="modal">
    <div class="modal-header"><h3>➕ إضافة صف دراسي</h3><button class="modal-close" onclick="closeModal('modalAddClass')">✕</button></div>
    <div class="modal-body">
      <p style="font-size:13px;color:var(--text-muted);margin-bottom:18px;line-height:1.7;background:var(--bg);padding:12px;border-radius:8px">
        💡 اكتب اسم الصف كما تريد تماماً — مثل: <strong>1/أ</strong> أو <strong>الأول الابتدائي أ</strong> أو <strong>Grade 3A</strong>
      </p>
      <div class="form-group">
        <label class="form-label">اسم الصف *</label>
        <input class="form-control" id="className" placeholder="اكتب اسم الصف بأي صيغة تريد..." style="font-size:17px;font-weight:700">
      </div>
      <div class="form-group">
        <label class="form-label">المرحلة / التصنيف <span style="color:var(--text-muted);font-weight:400">(اختياري - للتجميع)</span></label>
        <input class="form-control" id="classGrade" placeholder="مثال: الابتدائية، المتوسطة، الثانوية...">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modalAddClass')">إلغاء</button>
      <button class="btn btn-accent" onclick="addClass()">✅ إضافة الصف</button>
    </div>
  </div>
</div>

<!-- Rename Class Modal -->
<div class="modal-overlay" id="modalRenameClass">
  <div class="modal">
    <div class="modal-header"><h3>✏️ تعديل اسم الصف</h3><button class="modal-close" onclick="closeModal('modalRenameClass')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="renameClassId">
      <div class="form-group">
        <label class="form-label">الاسم الجديد للصف *</label>
        <input class="form-control" id="renameClassName" placeholder="اكتب الاسم الجديد..." style="font-size:17px;font-weight:700">
      </div>
      <div class="form-group">
        <label class="form-label">المرحلة / التصنيف <span style="color:var(--text-muted);font-weight:400">(اختياري)</span></label>
        <input class="form-control" id="renameClassGrade" placeholder="مثال: الابتدائية...">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modalRenameClass')">إلغاء</button>
      <button class="btn btn-accent" onclick="renameClass()">✅ حفظ التعديل</button>
    </div>
  </div>
</div>

<!-- Bulk Import Students Modal -->
<div class="modal-overlay" id="modalBulkImport">
  <div class="modal" style="max-width:560px">
    <div class="modal-header"><h3>📋 استيراد أسماء الطلاب</h3><button class="modal-close" onclick="closeModal('modalBulkImport')">✕</button></div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">اختر الصف *</label>
        <select class="form-control" id="bulkClassId"></select>
      </div>
      <div class="form-group">
        <label class="form-label">الصق الأسماء هنا *</label>
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:8px;line-height:1.8">
          📌 كل اسم في سطر منفصل — يمكن نسخها من Excel أو Word أو أي مصدر<br>
          ✅ يتجاهل الترقيم التلقائي (١- أو 1. أو 1) تلقائياً
        </div>
        <textarea class="form-control" id="bulkNames" rows="12"
          placeholder="أحمد محمد العتيبي&#10;عبدالله سالم الزهراني&#10;فيصل عمر الشمري&#10;خالد ناصر الدوسري&#10;..."
          style="font-size:15px;line-height:2;resize:vertical"></textarea>
        <div id="bulkPreviewCount" style="margin-top:8px;font-size:13px;color:var(--text-muted)"></div>
      </div>
    </div>
    <div class="modal-footer" style="justify-content:space-between;flex-wrap:wrap;gap:10px">
      <div id="bulkResult" style="font-size:13px;color:var(--success)"></div>
      <div style="display:flex;gap:10px">
        <button class="btn btn-ghost" onclick="closeModal('modalBulkImport')">إغلاق</button>
        <button class="btn btn-accent" onclick="bulkImport()" id="bulkImportBtn">📥 استيراد الأسماء</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Student Modal -->
<div class="modal-overlay" id="modalAddStudent">
  <div class="modal">
    <div class="modal-header"><h3>إضافة طالب</h3><button class="modal-close" onclick="closeModal('modalAddStudent')">✕</button></div>
    <div class="modal-body">
      <div class="form-group"><label class="form-label">الاسم الكامل *</label><input class="form-control" id="studentName" placeholder="اسم الطالب"></div>
      <div class="form-group"><label class="form-label">الصف *</label><select class="form-control" id="studentClass"></select></div>
      <div class="form-group"><label class="form-label">الرقم الطلابي</label><input class="form-control" id="studentNum" placeholder="اختياري"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modalAddStudent')">إلغاء</button>
      <button class="btn btn-accent" onclick="addStudent()">حفظ</button>
    </div>
  </div>
</div>

<!-- Edit Student Modal -->
<div class="modal-overlay" id="modalEditStudent">
  <div class="modal">
    <div class="modal-header"><h3>تعديل بيانات الطالب</h3><button class="modal-close" onclick="closeModal('modalEditStudent')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="editStudentId">
      <div class="form-group"><label class="form-label">الاسم الكامل *</label><input class="form-control" id="editStudentName"></div>
      <div class="form-group"><label class="form-label">الصف *</label><select class="form-control" id="editStudentClass"></select></div>
      <div class="form-group"><label class="form-label">الرقم الطلابي</label><input class="form-control" id="editStudentNum"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modalEditStudent')">إلغاء</button>
      <button class="btn btn-accent" onclick="updateStudent()">حفظ التعديل</button>
    </div>
  </div>
</div>

<!-- Add User Modal -->
<div class="modal-overlay" id="modalAddUser">
  <div class="modal">
    <div class="modal-header"><h3>إضافة مستخدم</h3><button class="modal-close" onclick="closeModal('modalAddUser')">✕</button></div>
    <div class="modal-body">
      <div class="form-group"><label class="form-label">الاسم الكامل *</label><input class="form-control" id="userFullName"></div>
      <div class="form-group"><label class="form-label">اسم المستخدم *</label><input class="form-control" id="userUsername" dir="ltr"></div>
      <div class="form-group"><label class="form-label">كلمة المرور *</label><input type="password" class="form-control" id="userPassword"></div>
      <div class="form-group"><label class="form-label">الدور *</label>
        <select class="form-control" id="userRole">
          <option value="management">إداري (يستطيع استدعاء الطلاب)</option>
          <option value="admin">مدير (صلاحيات كاملة)</option>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modalAddUser')">إلغاء</button>
      <button class="btn btn-accent" onclick="addUser()">حفظ</button>
    </div>
  </div>
</div>

<div id="toast-container"></div>

<script src="../assets/js/common.js"></script>
<script>
let allClasses = [];

document.getElementById('todayDate').textContent = arabicDate();

// ---- Section switching ----
const sectionTitles = {
  dashboard: 'لوحة التحكم',
  classes:   'إدارة الصفوف',
  students:  'إدارة الطلاب',
  users:     'إدارة المستخدمين',
  log:       'سجل الاستدعاءات'
};

function showSection(name) {
  document.querySelectorAll('[id^="section-"]').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
  document.getElementById('section-' + name).style.display = 'block';
  document.getElementById('sectionTitle').textContent = sectionTitles[name];
  const navItems = document.querySelectorAll('.nav-item');
  navItems.forEach(item => {
    if (item.getAttribute('onclick')?.includes(name)) item.classList.add('active');
  });
  // Close mobile sidebar
  document.querySelector('.sidebar').classList.remove('open');
  document.querySelector('.sidebar-overlay').classList.remove('open');

  if (name === 'dashboard') loadDashboard();
  if (name === 'classes')   loadClasses();
  if (name === 'students')  { loadClassesIntoSelect(); loadStudentsAdmin(); }
  if (name === 'users')     loadUsers();
  if (name === 'log')       loadLog();
}

// ---- DASHBOARD ----
async function loadDashboard() {
  const r = await apiGet('get_today_log');
  const body = document.getElementById('dashLogBody');
  if (!r.success || !r.data.length) {
    body.innerHTML = '<div class="empty-state"><div class="empty-state-icon">📋</div><h3>لا توجد استدعاءات اليوم</h3></div>';
    return;
  }
  body.innerHTML = `<div class="table-wrap"><table>
    <thead><tr><th>الوقت</th><th>الطالب</th><th>الصف</th><th>بواسطة</th></tr></thead>
    <tbody>${r.data.slice(0,10).map(d => `
      <tr>
        <td><span class="badge badge-called">${formatTime(d.call_time)}</span></td>
        <td style="font-weight:700">${d.student_name}</td>
        <td>${d.class_name}</td>
        <td style="color:var(--text-muted)">${d.called_by_name}</td>
      </tr>`).join('')}
    </tbody>
  </table></div>`;
}

// ---- CLASSES ----
async function loadClasses() {
  const r = await apiGet('get_classes');
  allClasses = r.data || [];
  const tbody = document.getElementById('classesTable');
  if (!allClasses.length) {
    tbody.innerHTML = '<tr><td colspan="5" class="empty-state" style="padding:40px">لا توجد صفوف</td></tr>';
    return;
  }
  // Get student counts
  const db2 = await apiGet('get_all_students');
  const countMap = {};
  (db2.data || []).forEach(s => { countMap[s.class_id] = (countMap[s.class_id] || 0) + 1; });

  tbody.innerHTML = allClasses.map((c, i) => `
    <tr>
      <td style="color:var(--text-muted)">${i+1}</td>
      <td style="font-weight:700;font-size:16px">${c.name}</td>
      <td>${c.grade || '—'}</td>
      <td><span class="badge badge-admin">${countMap[c.id] || 0} طالب</span></td>
      <td style="display:flex;gap:6px;flex-wrap:wrap">
        <button class="btn btn-ghost btn-sm" onclick="openRenameClass(${c.id},'${c.name.replace(/'/g,"\\'")}','${(c.grade||'').replace(/'/g,"\\'")}')">✏️ تعديل</button>
        <button class="btn btn-danger btn-sm" onclick="deleteClass(${c.id},'${c.name}')">🗑️ حذف</button>
      </td>
    </tr>`).join('');
}

async function addClass() {
  const name = document.getElementById('className').value.trim();
  const grade = document.getElementById('classGrade').value.trim();
  if (!name) { toast('اسم الصف مطلوب', 'error'); return; }
  const fd = new FormData();
  fd.append('name', name); fd.append('grade', grade);
  const r = await api('add_class', 'POST', fd);
  if (r.success) {
    toast(r.message);
    closeModal('modalAddClass');
    document.getElementById('className').value = '';
    document.getElementById('classGrade').value = '';
    loadClasses();
  } else toast(r.message, 'error');
}

async function deleteClass(id, name) {
  const ok = await confirmDialog(`هل أنت متأكد من حذف الصف <strong>${name}</strong>؟<br>سيتم حذف جميع الطلاب المرتبطين به.`);
  if (!ok) return;
  const fd = new FormData(); fd.append('id', id);
  const r = await api('delete_class', 'POST', fd);
  if (r.success) { toast(r.message); loadClasses(); } else toast(r.message, 'error');
}

// ---- STUDENTS ----
async function loadClassesIntoSelect() {
  const r = await apiGet('get_classes');
  allClasses = r.data || [];
  const opts = allClasses.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
  document.getElementById('filterClass').innerHTML = '<option value="">— جميع الصفوف —</option>' + opts;
  document.getElementById('studentClass').innerHTML = '<option value="">اختر الصف</option>' + opts;
  document.getElementById('editStudentClass').innerHTML = '<option value="">اختر الصف</option>' + opts;
}

async function loadStudentsAdmin() {
  const classId = document.getElementById('filterClass').value;
  const r = await apiGet('get_all_students', classId ? {class_id: classId} : {});
  const tbody = document.getElementById('studentsTable');
  if (!r.data?.length) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted)">لا يوجد طلاب</td></tr>';
    return;
  }
  tbody.innerHTML = r.data.map((s, i) => `
    <tr>
      <td style="color:var(--text-muted)">${i+1}</td>
      <td style="font-weight:700">${s.full_name}</td>
      <td>${s.student_number || '—'}</td>
      <td><span class="badge badge-admin">${s.class_name}</span></td>
      <td style="display:flex;gap:6px;flex-wrap:wrap">
        <button class="btn btn-ghost btn-sm" onclick="editStudent(${s.id},'${s.full_name.replace(/'/g,"\\'")}',${s.class_id},'${s.student_number||''}')">✏️ تعديل</button>
        <button class="btn btn-danger btn-sm" onclick="deleteStudent(${s.id},'${s.full_name.replace(/'/g,"\\'")}')">🗑️</button>
      </td>
    </tr>`).join('');
}

async function addStudent() {
  const name    = document.getElementById('studentName').value.trim();
  const classId = document.getElementById('studentClass').value;
  const num     = document.getElementById('studentNum').value.trim();
  if (!name || !classId) { toast('الاسم والصف مطلوبان', 'error'); return; }
  const fd = new FormData();
  fd.append('full_name', name); fd.append('class_id', classId); fd.append('student_number', num);
  const r = await api('add_student', 'POST', fd);
  if (r.success) {
    toast(r.message); closeModal('modalAddStudent');
    document.getElementById('studentName').value = '';
    document.getElementById('studentNum').value = '';
    loadStudentsAdmin();
  } else toast(r.message, 'error');
}

function editStudent(id, name, classId, num) {
  document.getElementById('editStudentId').value  = id;
  document.getElementById('editStudentName').value = name;
  document.getElementById('editStudentClass').value = classId;
  document.getElementById('editStudentNum').value  = num;
  openModal('modalEditStudent');
}

async function updateStudent() {
  const fd = new FormData();
  fd.append('id',             document.getElementById('editStudentId').value);
  fd.append('full_name',      document.getElementById('editStudentName').value.trim());
  fd.append('class_id',       document.getElementById('editStudentClass').value);
  fd.append('student_number', document.getElementById('editStudentNum').value.trim());
  const r = await api('update_student', 'POST', fd);
  if (r.success) { toast(r.message); closeModal('modalEditStudent'); loadStudentsAdmin(); }
  else toast(r.message, 'error');
}

async function deleteStudent(id, name) {
  const ok = await confirmDialog(`هل أنت متأكد من حذف الطالب <strong>${name}</strong>؟`);
  if (!ok) return;
  const fd = new FormData(); fd.append('id', id);
  const r = await api('delete_student', 'POST', fd);
  if (r.success) { toast(r.message); loadStudentsAdmin(); } else toast(r.message, 'error');
}

// ---- USERS ----
async function loadUsers() {
  const r = await apiGet('get_users');
  const tbody = document.getElementById('usersTable');
  if (!r.data?.length) { tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px">لا يوجد مستخدمون</td></tr>'; return; }
  tbody.innerHTML = r.data.map((u, i) => `
    <tr>
      <td style="color:var(--text-muted)">${i+1}</td>
      <td style="font-weight:700">${u.full_name}</td>
      <td dir="ltr" style="font-family:monospace;font-size:13px">${u.username}</td>
      <td><span class="badge ${u.role==='admin'?'badge-admin':'badge-mgmt'}">${u.role==='admin'?'🛡️ مدير':'📋 إداري'}</span></td>
      <td><button class="btn btn-danger btn-sm" onclick="deleteUser(${u.id},'${u.full_name.replace(/'/g,"\\'")}')">🗑️</button></td>
    </tr>`).join('');
}

async function addUser() {
  const fd = new FormData();
  fd.append('full_name', document.getElementById('userFullName').value.trim());
  fd.append('username',  document.getElementById('userUsername').value.trim());
  fd.append('password',  document.getElementById('userPassword').value);
  fd.append('role',      document.getElementById('userRole').value);
  const r = await api('add_user', 'POST', fd);
  if (r.success) {
    toast(r.message); closeModal('modalAddUser'); loadUsers();
    ['userFullName','userUsername','userPassword'].forEach(id => document.getElementById(id).value = '');
  } else toast(r.message, 'error');
}

async function deleteUser(id, name) {
  const ok = await confirmDialog(`هل أنت متأكد من حذف المستخدم <strong>${name}</strong>؟`);
  if (!ok) return;
  const fd = new FormData(); fd.append('id', id);
  const r = await api('delete_user', 'POST', fd);
  if (r.success) { toast(r.message); loadUsers(); } else toast(r.message, 'error');
}

// ---- LOG ----
async function loadLog() {
  const r = await apiGet('get_today_log');
  const body = document.getElementById('logBody');
  if (!r.data?.length) {
    body.innerHTML = '<div class="empty-state"><div class="empty-state-icon">📋</div><h3>لا توجد استدعاءات اليوم</h3><p>ستظهر هنا عند استدعاء الطلاب</p></div>';
    return;
  }
  function resetCalls() {
    if (!confirm('هل أنت متأكد من تصفير الاستدعاءات؟')) return;

    fetch('data.php?action=reset_calls')
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        location.reload();
    });
}
  body.innerHTML = `<div class="table-wrap"><table>
    <thead><tr><th>الوقت</th><th>الطالب</th><th>الصف</th><th>بواسطة</th></tr></thead>
    <tbody>${r.data.map(d => `
      <tr>
        <td><span class="badge badge-called">🕐 ${formatTime(d.call_time)}</span></td>
        <td style="font-weight:700">${d.student_name}</td>
        <td>${d.class_name}</td>
        <td style="color:var(--text-muted)">${d.called_by_name}</td>
      </tr>`).join('')}
    </tbody>
  </table></div>`;
}


// ---- RENAME CLASS ----
function openRenameClass(id, name, grade) {
  document.getElementById('renameClassId').value   = id;
  document.getElementById('renameClassName').value  = name;
  document.getElementById('renameClassGrade').value = grade;
  openModal('modalRenameClass');
}

async function renameClass() {
  const fd = new FormData();
  fd.append('id',    document.getElementById('renameClassId').value);
  fd.append('name',  document.getElementById('renameClassName').value.trim());
  fd.append('grade', document.getElementById('renameClassGrade').value.trim());
  if (!fd.get('name')) { toast('اسم الصف مطلوب', 'error'); return; }
  const r = await api('rename_class', 'POST', fd);
  if (r.success) { toast(r.message); closeModal('modalRenameClass'); loadClasses(); loadClassesIntoSelect(); }
  else toast(r.message, 'error');
}

// ---- BULK IMPORT ----
async function openBulkImport() {
  // Make sure classes are loaded into select
  const r = await apiGet('get_classes');
  const opts = (r.data||[]).map(c => `<option value="${c.id}">${c.name}</option>`).join('');
  document.getElementById('bulkClassId').innerHTML = '<option value="">— اختر الصف —</option>' + opts;
  document.getElementById('bulkNames').value = '';
  document.getElementById('bulkResult').textContent = '';
  document.getElementById('bulkPreviewCount').textContent = '';
  openModal('modalBulkImport');
}

// Live count preview
document.addEventListener('DOMContentLoaded', () => {
  const ta = document.getElementById('bulkNames');
  if (ta) {
    ta.addEventListener('input', () => {
      const lines = ta.value.split(/[\r\n،,]+/).map(l => {
        const n = l.replace(/^[\d٠-٩]+[.\-\)\s]+/, '').trim();
        return n;
      }).filter(l => l.length >= 2);
      document.getElementById('bulkPreviewCount').textContent =
        lines.length > 0 ? `✅ سيتم إضافة ${lines.length} اسم` : '';
    });
  }
});

async function bulkImport() {
  const classId = document.getElementById('bulkClassId').value;
  const names   = document.getElementById('bulkNames').value.trim();
  if (!classId) { toast('اختر الصف أولاً', 'error'); return; }
  if (!names)   { toast('الصق الأسماء أولاً', 'error'); return; }

  const btn = document.getElementById('bulkImportBtn');
  btn.disabled = true;
  btn.textContent = 'جاري الاستيراد...';

  const fd = new FormData();
  fd.append('class_id', classId);
  fd.append('names', names);
  const r = await api('bulk_import_students', 'POST', fd);

  btn.disabled = false;
  btn.textContent = '📥 استيراد الأسماء';

  if (r.success) {
    document.getElementById('bulkResult').textContent = r.message;
    document.getElementById('bulkResult').style.color = 'var(--success)';
    document.getElementById('bulkNames').value = '';
    document.getElementById('bulkPreviewCount').textContent = '';
    toast(r.message);
    loadStudentsAdmin();
  } else {
    document.getElementById('bulkResult').textContent = r.message;
    document.getElementById('bulkResult').style.color = 'var(--danger)';
    toast(r.message, 'error');
  }
}

// Init
loadDashboard();
  async function resetCalls() {
  const ok = confirm("هل أنت متأكد من تصفير جميع الاستدعاءات؟");
  if (!ok) return;

  const r = await api('reset_calls', 'POST', new FormData());

  if (r.success) {
    alert("تم تصفير الاستدعاءات بنجاح");
    loadDashboard?.();
    loadLog?.();
  } else {
    alert(r.message || "حدث خطأ");
  }
}
</script>
</body>
</html>
