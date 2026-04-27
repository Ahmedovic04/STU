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
      <div class="logo-text">مدرسة معيذر</div>
      <div class="logo-sub">لوحة المدير</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">الإدارة</div>
    <a class="nav-item active" onclick="showSection('dashboard')"><span class="nav-icon">📊</span> الرئيسية</a>
    <a class="nav-item" onclick="showSection('classes')"><span class="nav-icon">🏛️</span> الصفوف</a>
    <a class="nav-item" onclick="showSection('students')"><span class="nav-icon">👨‍🎓</span> الطلاب</a>
    <a class="nav-item" onclick="showSection('users')"><span class="nav-icon">👥</span> المستخدمون</a>
    <a class="nav-item" onclick="showSection('card-settings')"><span class="nav-icon">📇</span> إعدادات البطاقة</a>
    <div class="nav-section-label" style="margin-top:16px">التقارير</div>
    <a class="nav-item" onclick="showSection('log')"><span class="nav-icon">📋</span> سجل اليوم</a>
    <a class="nav-item" onclick="showSection('reports')"><span class="nav-icon">📄</span> تقارير مخصصة</a>
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

    <!-- ===== CARD SETTINGS ===== -->
    <div id="section-card-settings" style="display:none">
      <div class="card">
        <div class="card-header">
          <h2>📇 إعدادات بطاقة الطالب</h2>
          <button class="btn btn-accent" onclick="saveCardSettings()">💾 حفظ الإعدادات</button>
        </div>
        <div class="card-body">
          <div class="settings-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));gap:20px">
            <div class="form-group">
              <label class="form-label">حجم الخط الأساسي (pt)</label>
              <input type="number" class="form-control" id="set-font-size" value="11">
              <small style="color:var(--text-muted)">القيمة الافتراضية: 11</small>
            </div>
            <div class="form-group">
              <label class="form-label">عرض البطاقة (بوصة - inch)</label>
              <input type="number" step="0.01" class="form-control" id="set-card-width" value="3.37">
              <small style="color:var(--text-muted)">القياسي: 3.37</small>
            </div>
            <div class="form-group">
              <label class="form-label">ارتفاع البطاقة (بوصة - inch)</label>
              <input type="number" step="0.01" class="form-control" id="set-card-height" value="2.125">
              <small style="color:var(--text-muted)">القياسي: 2.125</small>
            </div>
            <div class="form-group">
              <label class="form-label">حجم الباركود (px)</label>
              <input type="number" class="form-control" id="set-barcode-size" value="80">
              <small style="color:var(--text-muted)">الافتراضي: 80</small>
            </div>
          </div>
          
          <div class="preview-area" style="margin-top:40px;padding:20px;background:#f0f4f9;border-radius:12px;text-align:center">
            <h3 style="margin-bottom:20px;color:var(--primary)">👀 معاينة مباشرة</h3>
            <div id="card-preview-container"></div>
            <button class="btn btn-ghost btn-sm" style="margin-top:20px" onclick="updatePreview()">تحديث المعاينة</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== REPORTS ===== -->
    <div id="section-reports" style="display:none">
      <div class="card">
        <div class="card-header">
          <h2>📄 تقارير الاستدعاءات</h2>
        </div>
        <div class="card-body">
          <div style="display:flex;gap:15px;align-items:flex-end;margin-bottom:25px;flex-wrap:wrap;background:#f8fafc;padding:20px;border-radius:12px;border:1px solid var(--border)">
            <div class="form-group" style="margin:0;flex:1;min-width:150px">
              <label class="form-label">من تاريخ</label>
              <input type="date" class="form-control" id="reportFrom" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:150px">
              <label class="form-label">إلى تاريخ</label>
              <input type="date" class="form-control" id="reportTo" value="<?= date('Y-m-d') ?>">
            </div>
            <button class="btn btn-primary" style="height:46px" onclick="generateReport()">🔍 عرض التقرير</button>
            <button class="btn btn-ghost" id="btnExportPDF" style="height:46px;display:none" onclick="exportReportPDF()">🖨️ تحميل PDF</button>
          </div>

          <div id="reportResultArea">
            <div style="text-align:center;padding:40px;color:var(--text-muted)">اختر التاريخ واضغط على "عرض التقرير"</div>
          </div>
        </div>
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
</style>

<!-- Hidden Container for PDF Generation -->
<div id="print-area" style="position: absolute; top: 0; left: 0; width: 210mm; z-index: -1000; opacity: 0; background: white; overflow: visible;"></div>

<script src="../assets/js/common.js"></script>
<script src="../assets/js/qrcode.min.js"></script>
<script src="../assets/js/html2pdf.bundle.min.js"></script>

<script>
let allClasses = [];
let allStudents = [];
let cardSettings = {
    font_size: 11,
    card_width: 3.37,
    card_height: 2.125,
    barcode_size: 80
};
const SITE_BASE = window.location.origin;
const SITE_NAME = '<?= SITE_NAME ?>';

document.getElementById('todayDate').textContent = arabicDate();

async function initPage() {
    await loadInitialData();
}

async function loadInitialData() {
    try {
        const settingsRes = await apiGet('get_card_settings');
        if (settingsRes.success) cardSettings = settingsRes.data;
        applySettingsToUI();

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

function applySettingsToUI() {
    document.getElementById('set-font-size').value = cardSettings.font_size;
    document.getElementById('set-card-width').value = cardSettings.card_width;
    document.getElementById('set-card-height').value = cardSettings.card_height;
    document.getElementById('set-barcode-size').value = cardSettings.barcode_size;
}

async function saveCardSettings() {
    const fd = new FormData();
    fd.append('font_size', document.getElementById('set-font-size').value);
    fd.append('card_width', document.getElementById('set-card-width').value);
    fd.append('card_height', document.getElementById('set-card-height').value);
    fd.append('barcode_size', document.getElementById('set-barcode-size').value);
    
    const r = await api('update_card_settings', 'POST', fd);
    if (r.success) {
        toast(r.message);
        cardSettings = {
            font_size: parseInt(document.getElementById('set-font-size').value),
            card_width: parseFloat(document.getElementById('set-card-width').value),
            card_height: parseFloat(document.getElementById('set-card-height').value),
            barcode_size: parseInt(document.getElementById('set-barcode-size').value)
        };
        updatePreview();
    } else {
        toast(r.message, 'error');
    }
}

function updatePreview() {
    // تحديث الإعدادات محلياً من المدخلات لرؤية المعاينة قبل الحفظ
    cardSettings = {
        font_size: parseInt(document.getElementById('set-font-size').value) || 11,
        card_width: parseFloat(document.getElementById('set-card-width').value) || 3.37,
        card_height: parseFloat(document.getElementById('set-card-height').value) || 2.125,
        barcode_size: parseInt(document.getElementById('set-barcode-size').value) || 80
    };

    const container = document.getElementById('card-preview-container');
    const dummyStudent = { id: 'preview', full_name: 'اسم الطالب التجريبي', class_name: 'الصف التجريبي', barcode: 'test' };
    container.innerHTML = createCardHTML(dummyStudent);
    
    const qrBox = document.getElementById('qr-box-preview');
    if (qrBox) {
        new QRCode(qrBox, {
            text: SITE_BASE + '/call.php?code=test',
            width: cardSettings.barcode_size - 10,
            height: cardSettings.barcode_size - 10,
            correctLevel: 1
        });
    }
}

function renderFilters() {
    const filters = ['filterClass', 'studentClass', 'editStudentClass', 'bulkClassId'];
    const options = allClasses.map(c => '<option value="' + c.id + '">' + c.name + '</option>').join('');
    filters.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            const firstOpt = id === 'filterClass' ? '<option value="">— جميع الصفوف —</option>' : '<option value="">اختر الصف</option>';
            el.innerHTML = firstOpt + options;
        }
    });
}

function showSection(name) {
    document.querySelectorAll('[id^="section-"]').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
    document.getElementById('section-' + name).style.display = 'block';
    document.getElementById('sectionTitle').textContent = sectionTitles[name] || 'لوحة التحكم';
    if (name === 'dashboard') loadDashboard();
    if (name === 'classes')   loadClasses();
    if (name === 'students')  loadInitialData();
    if (name === 'users')     loadUsers();
    if (name === 'log')       loadLog();
    if (name === 'card-settings') updatePreview();
    if (name === 'reports')   { /* logic handled by button */ }
}

const sectionTitles = {
    dashboard: 'لوحة التحكم',
    classes: 'إدارة الصفوف',
    students: 'إدارة الطلاب',
    users: 'إدارة المستخدمين',
    log: 'سجل الاستدعاءات',
    'card-settings': 'إعدادات البطاقة',
    reports: 'تقارير مخصصة'
};

async function loadClasses() {
    const r = await apiGet('get_classes');
    allClasses = r.data || [];
    const tbody = document.getElementById('classesTable');
    if (!allClasses.length) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:40px">لا توجد صفوف</td></tr>';
        return;
    }
    tbody.innerHTML = allClasses.map((c, i) => {
        return '<tr>' +
               '<td>' + (i+1) + '</td>' +
               '<td>' + c.name + '</td>' +
               '<td>' + (c.grade || '—') + '</td>' +
               '<td>' + (c.student_count || 0) + '</td>' +
               '<td>' +
                 '<button class="btn btn-ghost btn-sm" onclick="openRenameClass(' + c.id + ',\'' + c.name.replace(/'/g, "\\'") + '\',\'' + (c.grade || '').replace(/'/g, "\\'") + '\')">✏️</button>' +
                 '<button class="btn btn-danger btn-sm" onclick="deleteClass(' + c.id + ',\'' + c.name + '\')">🗑️</button>' +
               '</td>' +
               '</tr>';
    }).join('');
}

async function addClass() {
    const name = document.getElementById('className').value.trim();
    const grade = document.getElementById('classGrade').value.trim();
    if (!name) { toast('اسم الصف مطلوب', 'error'); return; }
    const fd = new FormData(); fd.append('name', name); fd.append('grade', grade);
    const r = await api('add_class', 'POST', fd);
    if (r.success) { toast(r.message); closeModal('modalAddClass'); loadClasses(); } else toast(r.message, 'error');
}

async function deleteClass(id, name) {
    if (!confirm('هل أنت متأكد من حذف الصف ' + name + '؟')) return;
    const fd = new FormData(); fd.append('id', id);
    const r = await api('delete_class', 'POST', fd);
    if (r.success) { toast(r.message); loadClasses(); } else toast(r.message, 'error');
}

function loadStudentsAdmin() {
    const classId = document.getElementById('filterClass').value;
    const tbody = document.getElementById('studentsTable');
    tbody.innerHTML = '';
    const filtered = classId ? allStudents.filter(s => s.class_id == classId) : allStudents;
    const btnBulkClass = document.getElementById('btnDownloadClassCards');
    if (btnBulkClass) btnBulkClass.style.display = classId ? 'inline-flex' : 'none';
    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px">لا يوجد طلاب</td></tr>';
        return;
    }
    filtered.forEach((s, i) => {
        tbody.innerHTML += '<tr><td>' + (i+1) + '</td><td>' + s.full_name + '</td><td>' + (s.student_number || '—') + '</td>' +
            '<td><span class="badge badge-admin">' + s.class_name + '</span></td>' +
            '<td style="display:flex;gap:6px">' +
            '<button class="btn btn-ghost btn-sm" onclick="printSingleCard(' + s.id + ')">🖨️</button>' +
            '<button class="btn btn-ghost btn-sm" onclick="editStudent(' + s.id + ',\'' + s.full_name.replace(/'/g, "\\'") + '\',' + s.class_id + ',\'' + (s.student_number || '') + '\')">✏️</button>' +
            '<button class="btn btn-danger btn-sm" onclick="deleteStudent(' + s.id + ',\'' + s.full_name + '\')">🗑️</button></td></tr>';
    });
}

function onFilterClassChange() { loadStudentsAdmin(); }

async function addStudent() {
    const name = document.getElementById('studentName').value.trim();
    const classId = document.getElementById('studentClass').value;
    const num = document.getElementById('studentNum').value.trim();
    if (!name || !classId) { toast('الاسم والصف مطلوبان', 'error'); return; }
    const fd = new FormData(); fd.append('full_name', name); fd.append('class_id', classId); fd.append('student_number', num);
    const r = await api('add_student', 'POST', fd);
    if (r.success) { toast(r.message); closeModal('modalAddStudent'); loadInitialData(); } else toast(r.message, 'error');
}

function editStudent(id, name, classId, num) {
    document.getElementById('editStudentId').value = id;
    document.getElementById('editStudentName').value = name;
    document.getElementById('editStudentClass').value = classId;
    document.getElementById('editStudentNum').value = num;
    openModal('modalEditStudent');
}

async function updateStudent() {
    const fd = new FormData();
    fd.append('id', document.getElementById('editStudentId').value);
    fd.append('full_name', document.getElementById('editStudentName').value.trim());
    fd.append('class_id', document.getElementById('editStudentClass').value);
    fd.append('student_number', document.getElementById('editStudentNum').value.trim());
    const r = await api('update_student', 'POST', fd);
    if (r.success) { toast(r.message); closeModal('modalEditStudent'); loadInitialData(); } else toast(r.message, 'error');
}

async function deleteStudent(id, name) {
    if (!confirm('هل أنت متأكد من حذف الطالب ' + name + '؟')) return;
    const fd = new FormData(); fd.append('id', id);
    const r = await api('delete_student', 'POST', fd);
    if (r.success) { toast(r.message); loadInitialData(); } else toast(r.message, 'error');
}

async function openBulkImport() {
    renderFilters();
    document.getElementById('bulkNames').value = '';
    document.getElementById('bulkResult').textContent = '';
    openModal('modalBulkImport');
}

async function bulkImport() {
    const classId = document.getElementById('bulkClassId').value;
    const names = document.getElementById('bulkNames').value.trim();
    if (!classId || !names) { toast('يرجى اختيار الصف ولصق الأسماء', 'error'); return; }
    const btn = document.getElementById('bulkImportBtn');
    btn.disabled = true; btn.textContent = 'جاري المعالجة...';
    const fd = new FormData(); fd.append('class_id', classId); fd.append('names', names);
    const r = await api('bulk_import_students', 'POST', fd);
    btn.disabled = false; btn.textContent = '📥 استيراد الأسماء';
    if (r.success) { toast(r.message); closeModal('modalBulkImport'); loadInitialData(); } else toast(r.message, 'error');
}

async function loadUsers() {
    const r = await apiGet('get_users');
    const tbody = document.getElementById('usersTable');
    if (!r.data?.length) { tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px">لا يوجد مستخدمون</td></tr>'; return; }
    tbody.innerHTML = r.data.map((u, i) => '<tr><td>' + (i+1) + '</td><td>' + u.full_name + '</td><td>' + u.username + '</td><td>' + (u.role === 'admin' ? 'مدير' : 'إداري') + '</td><td><button class="btn btn-danger btn-sm" onclick="deleteUser(' + u.id + ',\'' + u.full_name + '\')">🗑️</button></td></tr>').join('');
}

async function addUser() {
    const fd = new FormData();
    fd.append('full_name', document.getElementById('userFullName').value.trim());
    fd.append('username', document.getElementById('userUsername').value.trim());
    fd.append('password', document.getElementById('userPassword').value);
    fd.append('role', document.getElementById('userRole').value);
    const r = await api('add_user', 'POST', fd);
    if (r.success) { toast(r.message); closeModal('modalAddUser'); loadUsers(); } else toast(r.message, 'error');
}

async function deleteUser(id, name) {
    if (!confirm('حذف المستخدم ' + name + '؟')) return;
    const fd = new FormData(); fd.append('id', id);
    const r = await api('delete_user', 'POST', fd);
    if (r.success) { toast(r.message); loadUsers(); } else toast(r.message, 'error');
}

function createCardHTML(student) {
    const s = cardSettings;
    const style = `width:${s.card_width}in; height:${s.card_height}in;`;
    const bodyStyle = `padding: ${s.card_height * 5}px ${s.card_width * 5}px;`;
    const fontStyle = `font-size:${s.font_size}pt;`;
    const qrStyle = `width:${s.barcode_size}px; height:${s.barcode_size}px;`;

    return `
        <div class="id-card-wrapper" style="${style}">
            <div class="id-card-header" style="font-size: ${s.font_size + 2}pt">بطاقة تعريف الطالب</div>
            <div class="id-card-body" style="${bodyStyle}">
                <div class="id-card-info">
                    <div class="student-field">
                        <span class="student-label" style="font-size: ${s.font_size - 3}pt">اسم الطالب:</span>
                        <span class="student-value" style="${fontStyle}">${student.full_name}</span>
                    </div>
                    <div class="student-field">
                        <span class="student-label" style="font-size: ${s.font_size - 3}pt">الصف:</span>
                        <span class="student-value" style="${fontStyle}">${student.class_name}</span>
                    </div>
                </div>
                <div class="id-card-qr-box" id="qr-box-${student.id}" style="${qrStyle}"></div>
            </div>
            <div class="id-card-footer" style="font-size: ${s.font_size - 3}pt">${SITE_NAME} - الاستدعاء الذكي</div>
        </div>`;
}

async function printSingleCard(studentId) {
    const student = allStudents.find(s => s.id == studentId);
    if (student) openPrintWindow([student]);
}

async function printBulk(mode) {
    const classId = document.getElementById('filterClass').value;
    const students = mode === 'class' ? allStudents.filter(s => s.class_id == classId) : allStudents;
    if (students.length === 0) { toast('لا يوجد طلاب لطباعتهم', 'error'); return; }
    openPrintWindow(students);
}

function openPrintWindow(students) {
    const printWindow = window.open('', '_blank');
    if (!printWindow) { alert('يرجى السماح بالنوافذ المنبثقة'); return; }
    let contentHtml = '';
    for (let i = 0; i < students.length; i += 6) {
        const chunk = students.slice(i, i + 6);
        contentHtml += '<table class="bulk-print-table">';
        for (let j = 0; j < chunk.length; j += 2) {
            contentHtml += '<tr><td>' + (chunk[j] ? createCardHTML(chunk[j]) : '') + '</td><td>' + (chunk[j+1] ? createCardHTML(chunk[j+1]) : '') + '</td></tr>';
        }
        contentHtml += '</table>';
        if (i + 6 < students.length) contentHtml += '<div class="page-break"></div>';
    }
    const currentStyles = Array.from(document.querySelectorAll('style')).map(s => s.innerHTML).join('\n');
    
    printWindow.document.write('<html><head><title>Print</title><style>' + currentStyles + 
        'body{background:white;padding:10mm;margin:0;direction:rtl;}' +
        '.bulk-print-table{width:100%;border-collapse:separate;border-spacing:10mm;table-layout:fixed;}' +
        '.bulk-print-table td{vertical-align:top;width:50%;padding:0;}' +
        '.id-card-wrapper{margin:0 auto;box-shadow:none;border:1px solid #eee;}' +
        '.page-break{page-break-after:always;height:1px;}' +
        '@media print{@page{size:A4 portrait;margin:0;}body{padding:10mm;}.id-card-wrapper{border:1px solid #ddd;-webkit-print-color-adjust:exact;}}' +
        '</style></head><body>' +
        '<div id="print-content">' + contentHtml + '</div>' +
        '<script src="' + SITE_BASE + '/assets/js/qrcode.min.js"><\/script>' +
        '<script>' +
        'function startPrint(){' +
        '  const students = ' + JSON.stringify(students) + ';' +
        '  students.forEach(s => {' +
        '    const el = document.getElementById("qr-box-" + s.id);' +
        '    if(el) new QRCode(el, {text:"' + SITE_BASE + '/call.php?code=" + s.barcode, width:' + (cardSettings.barcode_size - 8) + ', height:' + (cardSettings.barcode_size - 8) + ', correctLevel:1});' +
        '  });' +
        '  setTimeout(() => { window.print(); }, 800);' +
        '}' +
        'window.onload = () => { if(typeof QRCode === "undefined"){ document.querySelectorAll("script").forEach(s => { if(s.src.includes("qrcode")) s.onload = startPrint; }); } else { startPrint(); } };' +
        '<\/script></body></html>');
    printWindow.document.close();
}

window.downloadClassCards = () => printBulk('class');
window.downloadAllCards = () => printBulk('all');

async function loadDashboard() {
    const r = await apiGet('get_today_log');
    const body = document.getElementById('dashLogBody');
    if (!r.success || !r.data.length) { body.innerHTML = '<div class="empty-state"><h3>لا توجد استدعاءات اليوم</h3></div>'; return; }
    body.innerHTML = '<div class="table-wrap"><table><thead><tr><th>الوقت</th><th>الطالب</th><th>الصف</th></tr></thead><tbody>' + r.data.slice(0,10).map(d => '<tr><td>' + formatTime(d.call_time) + '</td><td>' + d.student_name + '</td><td>' + d.class_name + '</td></tr>').join('') + '</tbody></table></div>';
}

async function loadLog() {
    const r = await apiGet('get_today_log');
    const body = document.getElementById('logBody');
    if (!r.data?.length) { body.innerHTML = '<div class="empty-state"><h3>السجل فارغ اليوم</h3></div>'; return; }
    body.innerHTML = '<div class="table-wrap"><table><thead><tr><th>الوقت</th><th>الطالب</th><th>الصف</th><th>بواسطة</th></tr></thead><tbody>' + r.data.map(d => '<tr><td>' + formatTime(d.call_time) + '</td><td>' + d.student_name + '</td><td>' + d.class_name + '</td><td>' + d.called_by_name + '</td></tr>').join('') + '</tbody></table></div>';
}

async function resetCalls() {
    if (!confirm('تصفير جميع الاستدعاءات؟')) return;
    const r = await api('reset_calls', 'POST', new FormData());
    if (r.success) { alert('تم تصفير الاستدعاءات بنجاح'); loadDashboard(); loadLog(); } else alert(r.message);
}

function openRenameClass(id, name, grade) {
    document.getElementById('renameClassId').value = id;
    document.getElementById('renameClassName').value = name;
    document.getElementById('renameClassGrade').value = grade;
    openModal('modalRenameClass');
}

async function renameClass() {
    const fd = new FormData(); fd.append('id', document.getElementById('renameClassId').value);
    fd.append('name', document.getElementById('renameClassName').value.trim());
    fd.append('grade', document.getElementById('renameClassGrade').value.trim());
    const r = await api('rename_class', 'POST', fd);
    if (r.success) { toast(r.message); closeModal('modalRenameClass'); loadClasses(); } else toast(r.message, 'error');
}

async function generateReport() {
    const from = document.getElementById('reportFrom').value;
    const to = document.getElementById('reportTo').value;
    if (!from || !to) { toast('يرجى تحديد التاريخ', 'error'); return; }
    
    const res = await apiGet('report', { from, to });
    const area = document.getElementById('reportResultArea');
    const btnPDF = document.getElementById('btnExportPDF');
    
    if (res.success && res.data.length > 0) {
        btnPDF.style.display = 'inline-flex';
        let html = `
            <div id="report-print-content" style="padding:20px; direction:rtl; font-family:'Tajawal', sans-serif;">
                <div style="text-align:center;margin-bottom:30px;border-bottom:2px solid #1a3a5c;padding-bottom:15px">
                    <h1 style="color:#1a3a5c;margin-bottom:5px">${SITE_NAME}</h1>
                    <h2 style="color:#666">تقرير استدعاءات الطلاب</h2>
                    <p style="color:#888">الفترة من ${from} إلى ${to}</p>
                </div>
                <div class="table-wrap">
                    <table style="width:100%;border-collapse:collapse">
                        <thead>
                            <tr style="background:#f1f5f9">
                                <th style="padding:12px;border:1px solid #ddd">التاريخ</th>
                                <th style="padding:12px;border:1px solid #ddd">الوقت</th>
                                <th style="padding:12px;border:1px solid #ddd">الطالب</th>
                                <th style="padding:12px;border:1px solid #ddd">الصف</th>
                                <th style="padding:12px;border:1px solid #ddd">المستدعي</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${res.data.map(d => `
                                <tr>
                                    <td style="padding:10px;border:1px solid #ddd">${d.call_date}</td>
                                    <td style="padding:10px;border:1px solid #ddd">${formatTime(d.call_time)}</td>
                                    <td style="padding:10px;border:1px solid #ddd;font-weight:700">${d.student_name}</td>
                                    <td style="padding:10px;border:1px solid #ddd">${d.class_name}</td>
                                    <td style="padding:10px;border:1px solid #ddd">${d.called_by}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:30px;text-align:left;font-size:12px;color:#999">
                    تم استخراج التقرير في: ${new Date().toLocaleString('ar-SA')}
                </div>
            </div>
        `;
        area.innerHTML = html;
    } else {
        btnPDF.style.display = 'none';
        area.innerHTML = '<div style="text-align:center;padding:60px;color:var(--text-muted)"><div style="font-size:48px;margin-bottom:15px">📭</div>لا توجد بيانات لهذه الفترة</div>';
    }
}

async function exportReportPDF() {
    const element = document.getElementById('report-print-content');
    const from = document.getElementById('reportFrom').value;
    const to = document.getElementById('reportTo').value;
    
    const opt = {
        margin:       10,
        filename:     `Report_${from}_to_${to}.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save();
}

function formatTime(ts) {
    if (!ts) return '—';
    // Replace space with T to make it ISO compliant for better browser support
    const dateStr = ts.includes(' ') ? ts.replace(' ', 'T') : ts;
    const d = new Date(dateStr);
    
    // Fallback for very old browsers or weird formats
    if (isNaN(d.getTime())) {
        // Try to extract time manually if it's HH:MM:SS
        const parts = ts.split(' ');
        const timePart = parts.length > 1 ? parts[1] : parts[0];
        if (timePart.includes(':')) {
            return timePart.split(':').slice(0, 2).join(':');
        }
        return ts;
    }
    
    return d.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
}

initPage();
</script>
</body>
</html>
