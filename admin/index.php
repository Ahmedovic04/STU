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
            <button class="btn btn-ghost" id="btnDownloadClassCards" onclick="downloadClassCards()" style="display:none">🖨️ طباعة بطاقات الصف</button>
            <button class="btn btn-ghost" onclick="downloadAllCards()">📄 تحميل جميع البطاقات PDF</button>
            <button class="btn btn-accent" onclick="openModal('modalAddStudent')">+ إضافة طالب</button>
          </div>
        </div>
        <div class="card-body">
          <div class="form-group" style="max-width:280px">
            <select class="form-control" id="filterClass" onchange="onFilterClassChange()">
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
        <div class="card-header" style="display:flex;gap:8px;align-items:center;justify-content:flex-end;margin-bottom:15px">
          <h2>📋 سجل استدعاءات اليوم</h2>
          <button class="btn btn-ghost btn-sm" onclick="loadLog()">🔄 تحديث</button>
          <button class="btn btn-danger btn-sm" onclick="resetCalls()" style="display:flex;align-items:center;gap:6px">🧹 تصفير الاستدعاءات</button>
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

<style>
    /* ID Card Styling - Standard CR80 Size: 3.37" x 2.125" */
    .id-card-wrapper {
        width: 3.37in;
        height: 2.125in;
        background: white;
        border-radius: 8px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        direction: rtl;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        border: 1px solid #ddd;
        margin: 0 auto;
        color: #000;
        box-sizing: border-box;
    }
    .id-card-header {
        background: linear-gradient(135deg, #1e3a8a, #3b82f6);
        color: white;
        padding: 6px;
        text-align: center;
        font-weight: bold;
        font-size: 11pt;
        border-bottom: 2px solid #fbbf24;
    }
    .id-card-body {
        flex: 1;
        display: flex;
        padding: 8px 12px;
        align-items: center;
        gap: 12px;
    }
    .id-card-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        overflow: hidden;
    }
    .student-field {
        margin-bottom: 4px;
    }
    .student-label {
        font-size: 8pt;
        color: #64748b;
        display: block;
    }
    .student-value {
        font-size: 11pt;
        font-weight: 800;
        color: #1e293b;
        display: block;
        white-space: nowrap;
    }
    .id-card-qr-box {
        width: 80px;
        height: 80px;
        background: white;
        padding: 4px;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .id-card-footer {
        background: #f8fafc;
        padding: 4px;
        text-align: center;
        font-size: 8pt;
        color: #94a3b8;
        border-top: 1px solid #e2e8f0;
    }
    
    /* Print Layout for Bulk (6 per A4) */
    .bulk-print-page {
        display: grid;
        grid-template-columns: repeat(2, 3.37in);
        grid-template-rows: repeat(3, 2.125in);
        gap: 0.3in;
        padding: 0.5in;
        width: 210mm; /* A4 */
        height: 297mm; /* A4 */
        box-sizing: border-box;
        margin: 0 auto;
        background: white;
        justify-content: center;
        align-content: start;
    }
    .page-break {
        page-break-after: always;
    }
</style>

<!-- Hidden Container for PDF Generation -->
<div id="print-area" style="position: absolute; left: -9999px; top: -9999px;"></div>

<script src="../assets/js/common.js"></script>
<script src="../assets/js/qrcode.min.js"></script>
<script src="../assets/js/html2pdf.bundle.min.js"></script>

<script>
let allClasses = [];
let allStudents = [];
const SITE_BASE = window.location.origin;

// Set current date
document.getElementById('todayDate').textContent = arabicDate();

async function initPage() {
    await loadInitialData();
}

async function loadInitialData() {
    try {
        const classesRes = await apiGet('get_classes');
        allClasses = classesRes.data || [];
        
        const studentsRes = await apiGet('get_all_students');
        allStudents = studentsRes.data || [];
        
        renderFilters();
        loadStudentsAdmin();
        loadDashboard();
    } catch (e) {
        console.error("Init Error:", e);
    }
}

function renderFilters() {
    const filters = ['filterClass', 'studentClass', 'editStudentClass', 'bulkClassId'];
    const options = allClasses.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
    
    filters.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            const firstOpt = id === 'filterClass' ? '<option value="">— جميع الصفوف —</option>' : '<option value="">اختر الصف</option>';
            el.innerHTML = firstOpt + options;
        }
    });
}

function loadStudentsAdmin() {
    const classId = document.getElementById('filterClass').value;
    const tbody = document.getElementById('studentsTable');
    tbody.innerHTML = '';
    
    const filtered = classId ? allStudents.filter(s => s.class_id == classId) : allStudents;
    
    // Toggle bulk class button
    const btnBulkClass = document.getElementById('btnDownloadClassCards');
    if (btnBulkClass) {
        btnBulkClass.style.display = classId ? 'inline-flex' : 'none';
    }

    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px">لا يوجد طلاب</td></tr>';
        return;
    }

    filtered.forEach((s, i) => {
        tbody.innerHTML += `
            <tr>
                <td style="color:var(--text-muted)">${i+1}</td>
                <td style="font-weight:700">${s.full_name}</td>
                <td>${s.student_number || '—'}</td>
                <td><span class="badge badge-admin">${s.class_name}</span></td>
                <td style="display:flex;gap:6px">
                    <button class="btn btn-ghost btn-sm" onclick="printSingleCard(${s.id})" title="طباعة البطاقة">🖨️ بطاقة</button>
                    <button class="btn btn-ghost btn-sm" onclick="editStudent(${s.id},'${s.full_name.replace(/'/g,"\\'")}',${s.class_id},'${s.student_number||''}')">✏️</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteStudent(${s.id},'${s.full_name}')">🗑️</button>
                </td>
            </tr>
        `;
    });
}

function onFilterClassChange() {
    loadStudentsAdmin();
}

function createCardHTML(student) {
    return `
        <div class="id-card-wrapper">
            <div class="id-card-header">بطاقة تعريف الطالب</div>
            <div class="id-card-body">
                <div class="id-card-info">
                    <div class="student-field">
                        <span class="student-label">اسم الطالب:</span>
                        <span class="student-value">${student.full_name}</span>
                    </div>
                    <div class="student-field">
                        <span class="student-label">الصف:</span>
                        <span class="student-value">${student.class_name}</span>
                    </div>
                    ${student.student_number ? `
                    <div class="student-field">
                        <span class="student-label">الرقم:</span>
                        <span class="student-value">${student.student_number}</span>
                    </div>` : ''}
                </div>
                <div class="id-card-qr-box" id="qr-box-${student.id}"></div>
            </div>
            <div class="id-card-footer">${SITE_NAME} - الاستدعاء الذكي</div>
        </div>
    `;
}

async function printSingleCard(studentId) {
    const student = allStudents.find(s => s.id == studentId);
    if (!student) return;

    toast('جاري تجهيز البطاقة...');
    const printArea = document.getElementById('print-area');
    printArea.innerHTML = `<div style="padding:10px; background:white;">${createCardHTML(student)}</div>`;
    
    await new Promise(r => setTimeout(r, 100));

    new QRCode(document.getElementById(`qr-box-${student.id}`), {
        text: `${SITE_BASE}/call.php?code=${student.barcode}`,
        width: 72,
        height: 72,
        correctLevel: QRCode.CorrectLevel.H
    });

    await new Promise(r => setTimeout(r, 500));

    const opt = {
        margin: 0,
        filename: `بطاقة_${student.full_name}.pdf`,
        image: { type: 'jpeg', quality: 1.0 },
        html2canvas: { scale: 3, useCORS: true },
        jsPDF: { unit: 'in', format: [3.37, 2.125], orientation: 'landscape' }
    };

    html2pdf().set(opt).from(printArea.firstElementChild).save().then(() => {
        toast('تم التحميل ✅');
    });
}

async function printBulk(mode) {
    const classId = document.getElementById('filterClass').value;
    const students = mode === 'class' ? allStudents.filter(s => s.class_id == classId) : allStudents;
    
    if (students.length === 0) {
        toast('لا يوجد طلاب لطباعتهم', 'error');
        return;
    }

    toast('جاري معالجة البطاقات... يرجى الانتظار');
    const printArea = document.getElementById('print-area');
    printArea.innerHTML = '';
    
    // Pages of 6
    for (let i = 0; i < students.length; i += 6) {
        const page = document.createElement('div');
        page.className = 'bulk-print-page' + (i + 6 < students.length ? ' page-break' : '');
        
        const chunk = students.slice(i, i + 6);
        chunk.forEach(s => {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = createCardHTML(s);
            page.appendChild(wrapper);
        });
        
        printArea.appendChild(page);
        
        // QRs
        chunk.forEach(s => {
            new QRCode(document.getElementById(`qr-box-${s.id}`), {
                text: `${SITE_BASE}/call.php?code=${s.barcode}`,
                width: 72,
                height: 72
            });
        });
    }

    await new Promise(r => setTimeout(r, 1200));

    const opt = {
        margin: 0,
        filename: mode === 'class' ? 'بطاقات_الصف.pdf' : 'جميع_البطاقات.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(printArea).save().then(() => {
        toast('تم تحميل جميع البطاقات بنجاح ✅');
    });
}

window.downloadClassCards = () => printBulk('class');
window.downloadAllCards = () => printBulk('all');

// --- Standard API Functions ---
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

async function deleteStudent(id, name) {
  if (!confirm(`هل أنت متأكد من حذف الطالب ${name}؟`)) return;
  const fd = new FormData(); fd.append('id', id);
  const r = await api('delete_student', 'POST', fd);
  if (r.success) { toast(r.message); initPage(); } else toast(r.message, 'error');
}

async function resetCalls() {
  if (!confirm("هل أنت متأكد من تصفير جميع الاستدعاءات؟")) return;
  const r = await api('reset_calls', 'POST', new FormData());
  if (r.success) { alert("تم تصفير الاستدعاءات بنجاح"); loadDashboard(); loadLog(); }
  else alert(r.message || "حدث خطأ");
}

function showSection(name) {
  document.querySelectorAll('[id^="section-"]').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
  document.getElementById('section-' + name).style.display = 'block';
  document.getElementById('sectionTitle').textContent = sectionTitles[name] || 'لوحة التحكم';
  if (name === 'dashboard') loadDashboard();
  if (name === 'students') initPage();
  if (name === 'log') loadLog();
}

const sectionTitles = { dashboard: 'لوحة التحكم', classes: 'إدارة الصفوف', students: 'إدارة الطلاب', users: 'إدارة المستخدمين', log: 'سجل الاستدعاءات' };

// Initialize
initPage();

</script>
</body>
</html>
