// ============================================
// Shared utilities - school dismissal system
// ============================================

const API_BASE = window.location.origin + '/school-dismissal/api/data.php';

// ---- API helper ----
async function api(action, method = 'GET', body = null) {
  const url = `${API_BASE}?action=${action}`;
  const opts = { method };
  if (body) {
    opts.body = body instanceof FormData ? body : new URLSearchParams(body);
  }
  try {
    const res = await fetch(url, opts);
    return await res.json();
  } catch (e) {
    return { success: false, message: 'خطأ في الاتصال بالخادم' };
  }
}

async function apiGet(action, params = {}) {
  let url = `${API_BASE}?action=${action}`;
  for (const k in params) url += `&${k}=${encodeURIComponent(params[k])}`;
  try {
    const res = await fetch(url);
    return await res.json();
  } catch (e) {
    return { success: false, message: 'خطأ في الاتصال' };
  }
}

// ---- Toast ----
function toast(msg, type = 'success') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }
  const el = document.createElement('div');
  el.className = `toast toast-${type}`;
  el.innerHTML = `${type === 'success' ? '✅' : '❌'} ${msg}`;
  container.appendChild(el);
  setTimeout(() => { el.style.opacity = '0'; el.style.transform = 'translateX(-20px)'; el.style.transition = '0.3s'; }, 2800);
  setTimeout(() => el.remove(), 3200);
}

// ---- Modal ----
function openModal(id) {
  document.getElementById(id).classList.add('open');
}

function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}

// Close modal on overlay click
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('open');
  }
});

// ---- Sidebar (mobile) ----
function toggleSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  sidebar.classList.toggle('open');
  overlay.classList.toggle('open');
}

// ---- Confirm dialog ----
function confirmDialog(message) {
  return new Promise(resolve => {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay open';
    overlay.innerHTML = `
      <div class="modal">
        <div class="modal-header"><h3>⚠️ تأكيد</h3></div>
        <div class="modal-body"><p style="font-size:16px;line-height:1.7">${message}</p></div>
        <div class="modal-footer">
          <button class="btn btn-ghost" id="cfn-cancel">إلغاء</button>
          <button class="btn btn-danger" id="cfn-ok">تأكيد الحذف</button>
        </div>
      </div>
    `;
    document.body.appendChild(overlay);
    overlay.querySelector('#cfn-cancel').onclick = () => { overlay.remove(); resolve(false); };
    overlay.querySelector('#cfn-ok').onclick    = () => { overlay.remove(); resolve(true); };
  });
}

// ---- Arabic date ----
function arabicDate() {
  return new Date().toLocaleDateString('ar-SA', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
  });
}

// ---- Format time ----
function formatTime(t) {
  if (!t) return '';
  const [h, m] = t.split(':');
  const hour = parseInt(h);
  const ampm = hour >= 12 ? 'م' : 'ص';
  const h12  = hour % 12 || 12;
  return `${h12}:${m} ${ampm}`;
}

// ---- Loading state ----
function setLoading(btn, loading) {
  if (loading) {
    btn.dataset.orig = btn.innerHTML;
    btn.innerHTML = '<span class="skeleton" style="width:60px;height:16px;display:inline-block;border-radius:4px"></span>';
    btn.disabled = true;
  } else {
    btn.innerHTML = btn.dataset.orig || btn.innerHTML;
    btn.disabled = false;
  }
}
