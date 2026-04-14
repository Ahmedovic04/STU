<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'نظام استدعاء الطلاب' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
<style>
:root {
  --primary: #1a3a5c;
  --primary-light: #1e4976;
  --primary-dark: #0f2238;
  --accent: #f0a500;
  --accent-glow: rgba(240,165,0,0.25);
  --danger: #e74c3c;
  --danger-soft: rgba(231,76,60,0.12);
  --success: #2ecc71;
  --success-soft: rgba(46,204,113,0.12);
  --warning: #f39c12;
  --bg: #f0f4f9;
  --bg-card: #ffffff;
  --sidebar-bg: #0f2238;
  --sidebar-text: rgba(255,255,255,0.75);
  --sidebar-active: rgba(240,165,0,0.15);
  --text-main: #1a2535;
  --text-muted: #6b7a8d;
  --border: #dde3ed;
  --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
  --shadow-md: 0 8px 24px rgba(0,0,0,0.1);
  --shadow-lg: 0 16px 48px rgba(0,0,0,0.14);
  --radius: 14px;
  --radius-sm: 8px;
  --sidebar-w: 260px;
  --header-h: 64px;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: 'Tajawal', sans-serif;
  background: var(--bg);
  color: var(--text-main);
  min-height: 100vh;
  display: flex;
}

/* ============ SIDEBAR ============ */
.sidebar {
  width: var(--sidebar-w);
  background: var(--sidebar-bg);
  min-height: 100vh;
  position: fixed;
  top: 0;
  right: 0;
  z-index: 100;
  display: flex;
  flex-direction: column;
  transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
  box-shadow: -4px 0 24px rgba(0,0,0,0.3);
}

.sidebar-logo {
  padding: 24px 20px;
  border-bottom: 1px solid rgba(255,255,255,0.06);
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-icon {
  width: 42px;
  height: 42px;
  background: linear-gradient(135deg, var(--accent), #e8940d);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
}

.logo-text {
  font-size: 14px;
  font-weight: 700;
  color: #fff;
  line-height: 1.3;
}

.logo-sub {
  font-size: 11px;
  color: var(--sidebar-text);
  font-weight: 400;
}

/* Nav */
.sidebar-nav {
  flex: 1;
  padding: 16px 12px;
  overflow-y: auto;
}

.nav-section-label {
  font-size: 10px;
  font-weight: 700;
  color: rgba(255,255,255,0.3);
  letter-spacing: 1.5px;
  text-transform: uppercase;
  padding: 8px 10px 6px;
  margin-top: 8px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 14px;
  border-radius: 10px;
  color: var(--sidebar-text);
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
  margin-bottom: 2px;
  cursor: pointer;
  border: none;
  background: transparent;
  width: 100%;
  text-align: right;
}

.nav-item:hover { background: rgba(255,255,255,0.07); color: #fff; }

.nav-item.active {
  background: var(--sidebar-active);
  color: var(--accent);
  border: 1px solid rgba(240,165,0,0.2);
}

.nav-icon { font-size: 18px; width: 24px; text-align: center; flex-shrink: 0; }

/* Sidebar footer */
.sidebar-footer {
  padding: 16px 12px;
  border-top: 1px solid rgba(255,255,255,0.06);
}

.user-card {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 10px;
  background: rgba(255,255,255,0.05);
  margin-bottom: 10px;
}

.user-avatar {
  width: 36px;
  height: 36px;
  background: linear-gradient(135deg, var(--primary-light), var(--primary));
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  flex-shrink: 0;
}

.user-info { flex: 1; overflow: hidden; }
.user-name { font-size: 13px; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-role { font-size: 11px; color: var(--sidebar-text); }

.btn-logout {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 10px 14px;
  background: rgba(231,76,60,0.1);
  border: 1px solid rgba(231,76,60,0.2);
  border-radius: 8px;
  color: #ff7675;
  font-family: 'Tajawal', sans-serif;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
  justify-content: center;
}
.btn-logout:hover { background: rgba(231,76,60,0.2); }

/* ============ MAIN ============ */
.main-wrapper {
  margin-right: var(--sidebar-w);
  flex: 1;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.topbar {
  height: var(--header-h);
  background: var(--bg-card);
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  position: sticky;
  top: 0;
  z-index: 50;
  box-shadow: var(--shadow-sm);
}

.topbar-title {
  font-size: 20px;
  font-weight: 800;
  color: var(--text-main);
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.topbar-date {
  font-size: 13px;
  color: var(--text-muted);
  background: var(--bg);
  padding: 6px 14px;
  border-radius: 20px;
  border: 1px solid var(--border);
}

.hamburger {
  display: none;
  background: none;
  border: none;
  font-size: 22px;
  cursor: pointer;
  color: var(--text-main);
  padding: 6px;
}

.page-content {
  flex: 1;
  padding: 28px 24px;
  max-width: 1400px;
  width: 100%;
}

/* ============ CARDS ============ */
.card {
  background: var(--bg-card);
  border-radius: var(--radius);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

.card-header {
  padding: 20px 24px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
}

.card-header h2 {
  font-size: 17px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
}

.card-body { padding: 24px; }

/* ============ BUTTONS ============ */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 18px;
  border-radius: 10px;
  border: none;
  font-family: 'Tajawal', sans-serif;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
  white-space: nowrap;
}

.btn-primary { background: var(--primary); color: #fff; }
.btn-primary:hover { background: var(--primary-light); box-shadow: 0 4px 12px rgba(26,58,92,0.3); transform: translateY(-1px); }

.btn-accent { background: var(--accent); color: #1a1a1a; }
.btn-accent:hover { box-shadow: 0 4px 12px var(--accent-glow); transform: translateY(-1px); }

.btn-danger { background: var(--danger-soft); color: var(--danger); border: 1px solid rgba(231,76,60,0.2); }
.btn-danger:hover { background: var(--danger); color: #fff; }

.btn-success { background: var(--success-soft); color: var(--success); border: 1px solid rgba(46,204,113,0.2); }
.btn-success:hover { background: var(--success); color: #fff; }

.btn-ghost { background: var(--bg); color: var(--text-main); border: 1px solid var(--border); }
.btn-ghost:hover { background: var(--border); }

.btn-sm { padding: 6px 12px; font-size: 13px; border-radius: 8px; }

/* ============ FORM CONTROLS ============ */
.form-group { margin-bottom: 18px; }

.form-label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: var(--text-muted);
  margin-bottom: 7px;
}

.form-control {
  width: 100%;
  background: var(--bg);
  border: 1.5px solid var(--border);
  border-radius: var(--radius-sm);
  padding: 11px 14px;
  font-family: 'Tajawal', sans-serif;
  font-size: 15px;
  color: var(--text-main);
  transition: all 0.2s;
  outline: none;
  -webkit-appearance: none;
}

.form-control:focus {
  border-color: var(--accent);
  background: #fff;
  box-shadow: 0 0 0 3px var(--accent-glow);
}

/* ============ TABLE ============ */
.table-wrap { overflow-x: auto; }

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

thead th {
  padding: 12px 16px;
  background: var(--bg);
  font-size: 12px;
  font-weight: 700;
  color: var(--text-muted);
  letter-spacing: 0.5px;
  text-transform: uppercase;
  border-bottom: 1px solid var(--border);
  white-space: nowrap;
  text-align: right;
}

tbody td {
  padding: 14px 16px;
  border-bottom: 1px solid var(--border);
  vertical-align: middle;
}

tbody tr:last-child td { border-bottom: none; }
tbody tr:hover { background: rgba(240,165,0,0.03); }

/* ============ BADGES ============ */
.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 700;
}

.badge-admin { background: rgba(26,58,92,0.1); color: var(--primary); }
.badge-mgmt  { background: rgba(240,165,0,0.12); color: #a87200; }
.badge-called { background: var(--danger-soft); color: var(--danger); }
.badge-free   { background: var(--success-soft); color: #1a8a4a; }

/* ============ TOAST ============ */
#toast-container {
  position: fixed;
  bottom: 24px;
  left: 24px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.toast {
  background: var(--text-main);
  color: #fff;
  padding: 14px 20px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  box-shadow: var(--shadow-lg);
  display: flex;
  align-items: center;
  gap: 10px;
  max-width: 320px;
  animation: toastIn 0.3s ease;
}

.toast.toast-success { background: #1a5c35; border-left: 4px solid var(--success); }
.toast.toast-error   { background: #5c1a1a; border-left: 4px solid var(--danger); }

@keyframes toastIn {
  from { opacity: 0; transform: translateX(-20px); }
  to   { opacity: 1; transform: translateX(0); }
}

/* ============ MODAL ============ */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  z-index: 200;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 20px;
  backdrop-filter: blur(4px);
}

.modal-overlay.open { display: flex; }

.modal {
  background: var(--bg-card);
  border-radius: 18px;
  width: 100%;
  max-width: 440px;
  box-shadow: var(--shadow-lg);
  animation: modalIn 0.3s cubic-bezier(0.34,1.56,0.64,1);
}

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-header h3 { font-size: 17px; font-weight: 800; }

.modal-close {
  width: 32px;
  height: 32px;
  border: none;
  background: var(--bg);
  border-radius: 8px;
  cursor: pointer;
  font-size: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
}

.modal-body { padding: 24px; }
.modal-footer { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; gap: 10px; justify-content: flex-end; }

@keyframes modalIn {
  from { opacity: 0; transform: scale(0.9) translateY(10px); }
  to   { opacity: 1; transform: scale(1) translateY(0); }
}

/* ============ STATS ============ */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: var(--shadow-sm);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  flex-shrink: 0;
}

.stat-info { flex: 1; }
.stat-number { font-size: 28px; font-weight: 900; line-height: 1; color: var(--text-main); }
.stat-label  { font-size: 12px; color: var(--text-muted); margin-top: 4px; font-weight: 500; }

/* ============ OVERLAY (mobile sidebar) ============ */
.sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  z-index: 99;
  backdrop-filter: blur(2px);
}

/* ============ RESPONSIVE ============ */
@media (max-width: 900px) {
  :root { --sidebar-w: 260px; }

  .sidebar {
    transform: translateX(100%);
  }

  .sidebar.open {
    transform: translateX(0);
  }

  .sidebar-overlay.open { display: block; }

  .main-wrapper { margin-right: 0; }

  .hamburger { display: flex; }

  .page-content { padding: 20px 16px; }

  .topbar { padding: 0 16px; }

  .topbar-date { display: none; }
}

@media (max-width: 600px) {
  .stats-grid { grid-template-columns: 1fr 1fr; }
  .card-header { padding: 16px; }
  .card-body   { padding: 16px; }
}

/* ============ EMPTY STATE ============ */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: var(--text-muted);
}

.empty-state-icon { font-size: 64px; margin-bottom: 16px; opacity: 0.6; }
.empty-state h3   { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
.empty-state p    { font-size: 14px; }

/* ============ LOADING SKELETON ============ */
.skeleton {
  background: linear-gradient(90deg, var(--bg) 25%, var(--border) 50%, var(--bg) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 8px;
}

@keyframes shimmer {
  from { background-position: 200% 0; }
  to   { background-position: -200% 0; }
}

/* Pill select */
.pill-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.pill {
  padding: 8px 16px;
  border-radius: 20px;
  border: 1.5px solid var(--border);
  background: var(--bg);
  font-family: 'Tajawal', sans-serif;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  color: var(--text-main);
}

.pill:hover { border-color: var(--accent); color: var(--accent); }
.pill.active { background: var(--accent); border-color: var(--accent); color: #1a1a1a; box-shadow: 0 4px 12px var(--accent-glow); }
</style>
</head>
<body>
