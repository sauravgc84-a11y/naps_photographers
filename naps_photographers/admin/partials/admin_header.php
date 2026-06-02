<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Naps Photographers</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--gold:#64cbfa;--gold-light:#E8C97E;--black:#0A0A0A;--dark:#0D0D0D;--dark2:#131313;--dark3:#1A1A1A;--sidebar-w:260px;--white:#F8F6F1;--gray:#888;--border:rgba(201,168,76,0.15);--border-dim:rgba(255,255,255,0.05);--radius:4px;}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Montserrat',sans-serif;background:var(--dark);color:var(--white);display:flex;min-height:100vh;}
a{text-decoration:none;color:inherit;}
/* SIDEBAR */
.admin-sidebar{width:var(--sidebar-w);background:var(--dark2);border-right:1px solid var(--border-dim);display:flex;flex-direction:column;position:fixed;top:0;left:0;height:100vh;z-index:200;overflow-y:auto;transition:transform .3s ease;}
.sidebar-logo{padding:28px 24px 20px;border-bottom:1px solid var(--border-dim);}
.sidebar-logo .brand{font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:var(--white);letter-spacing:3px;}
.sidebar-logo .sub{font-size:.5rem;color:var(--gold);letter-spacing:4px;text-transform:uppercase;display:block;margin-top:2px;}
.sidebar-logo .role-tag{display:inline-block;background:rgba(201,168,76,.15);color:var(--gold);font-size:.6rem;letter-spacing:2px;text-transform:uppercase;padding:3px 10px;border-radius:20px;margin-top:10px;border:1px solid rgba(201,168,76,.3);}
.sidebar-nav{padding:20px 0;flex:1;}
.nav-section{padding:0 16px;margin-bottom:4px;}
.nav-section-label{font-size:.6rem;letter-spacing:3px;text-transform:uppercase;color:rgba(136,136,136,.5);padding:10px 8px 6px;display:block;}
.sidebar-link{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:var(--radius);color:rgba(248,246,241,.55);font-size:.78rem;letter-spacing:.5px;transition:all .3s;margin-bottom:2px;}
.sidebar-link:hover{background:rgba(255,255,255,.04);color:var(--white);}
.sidebar-link.active{background:rgba(201,168,76,.12);color:var(--gold);border-left:2px solid var(--gold);}
.sidebar-link .icon{font-size:1rem;width:20px;text-align:center;flex-shrink:0;}
.sidebar-bottom{padding:16px;border-top:1px solid var(--border-dim);}
.sidebar-user{display:flex;align-items:center;gap:10px;padding:10px 0;}
.s-avatar{width:34px;height:34px;border-radius:50%;background:var(--gold);color:var(--black);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0;}
.s-name{font-size:.8rem;color:var(--white);}
.s-email{font-size:.68rem;color:var(--gray);}
.sidebar-logout{display:block;text-align:center;padding:8px;border:1px solid rgba(255,255,255,.1);border-radius:2px;color:var(--gray);font-size:.7rem;letter-spacing:2px;text-transform:uppercase;transition:all .3s;margin-top:10px;}
.sidebar-logout:hover{border-color:rgba(239,68,68,.4);color:#fca5a5;background:rgba(239,68,68,.08);}
/* MAIN CONTENT */
.admin-wrapper{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;min-height:100vh;}
.admin-topbar{background:var(--dark2);border-bottom:1px solid var(--border-dim);padding:0 32px;height:58px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
.topbar-left{display:flex;align-items:center;gap:16px;}
.sidebar-toggle{display:none;background:none;border:none;cursor:pointer;flex-direction:column;gap:4px;padding:4px;}
.sidebar-toggle span{width:20px;height:1.5px;background:var(--white);display:block;}
.topbar-title{font-size:.75rem;letter-spacing:3px;text-transform:uppercase;color:var(--gray);}
.topbar-right{display:flex;align-items:center;gap:16px;}
.topbar-btn{padding:7px 18px;background:var(--gold);color:var(--black);border:none;border-radius:2px;font-family:'Montserrat',sans-serif;font-size:.68rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;transition:all .3s;}
.topbar-btn:hover{background:var(--gold-light);}
.admin-content{padding:36px 32px;flex:1;}
/* FLASH */
.flash{padding:14px 20px;border-radius:var(--radius);margin-bottom:24px;font-size:.83rem;}
.flash-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#6ee7a0;}
.flash-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;}
.flash-info{background:rgba(201,168,76,.08);border:1px solid rgba(201,168,76,.2);color:var(--gold);}
/* CONTENT HEADER */
.content-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;flex-wrap:wrap;gap:12px;}
.content-header h1{font-family:'Cormorant Garamond',serif;font-size:2rem;font-weight:400;}
.content-date{color:var(--gray);font-size:.75rem;}
/* STATS */
.admin-stats{display:grid;grid-template-columns:repeat(6,1fr);gap:14px;margin-bottom:28px;}
.admin-stat-card{background:var(--dark3);border:1px solid var(--border-dim);border-radius:var(--radius);padding:20px 16px;display:flex;align-items:center;gap:14px;transition:border-color .3s;}
.admin-stat-card:hover{border-color:var(--border);}
.asc-icon{font-size:1.4rem;flex-shrink:0;}
.asc-num{font-family:'Cormorant Garamond',serif;font-size:1.8rem;color:var(--white);line-height:1;}
.asc-lbl{font-size:.65rem;letter-spacing:1px;text-transform:uppercase;color:var(--gray);margin-top:4px;}
/* CARDS */
.admin-card{background:var(--dark3);border:1px solid var(--border-dim);border-radius:var(--radius);padding:24px;margin-bottom:24px;}
.card-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;}
.card-head h3{font-size:.9rem;font-weight:600;}
.btn-link{color:var(--gold);font-size:.75rem;letter-spacing:1px;}
.btn-link:hover{color:var(--gold-light);}
/* TABLE */
.data-table{width:100%;border-collapse:collapse;}
.data-table th{text-align:left;font-size:.66rem;letter-spacing:2px;text-transform:uppercase;color:var(--gold);padding:10px 14px;border-bottom:1px solid var(--border);}
.data-table td{padding:14px;border-bottom:1px solid var(--border-dim);font-size:.82rem;vertical-align:middle;}
.data-table tr:hover td{background:rgba(255,255,255,.015);}
.data-table tr:last-child td{border-bottom:none;}
.ref-tag{font-family:monospace;background:rgba(201,168,76,.08);color:var(--gold);padding:2px 8px;border-radius:2px;font-size:.78rem;border:1px solid rgba(201,168,76,.15);}
/* BADGES */
.badge{padding:3px 10px;border-radius:20px;font-size:.66rem;font-weight:600;letter-spacing:.3px;}
.badge-success{background:rgba(34,197,94,.12);color:#6ee7a0;border:1px solid rgba(34,197,94,.25);}
.badge-warning{background:rgba(234,179,8,.12);color:#fde047;border:1px solid rgba(234,179,8,.25);}
.badge-danger{background:rgba(239,68,68,.12);color:#fca5a5;border:1px solid rgba(239,68,68,.25);}
.badge-info{background:rgba(59,130,246,.12);color:#93c5fd;border:1px solid rgba(59,130,246,.25);}
.badge-primary{background:rgba(201,168,76,.12);color:var(--gold);border:1px solid rgba(201,168,76,.25);}
.badge-secondary{background:rgba(100,116,139,.12);color:#94a3b8;border:1px solid rgba(100,116,139,.25);}
/* BUTTONS */
.btn-sm{padding:6px 14px;border-radius:2px;font-size:.68rem;letter-spacing:1px;text-transform:uppercase;font-weight:600;cursor:pointer;border:none;font-family:'Montserrat',sans-serif;transition:all .3s;display:inline-block;}
.btn-gold-sm{background:var(--gold);color:var(--black);}
.btn-gold-sm:hover{background:var(--gold-light);}
.btn-outline-sm{background:transparent;border:1px solid rgba(255,255,255,.18);color:var(--white);}
.btn-outline-sm:hover{border-color:var(--gold);color:var(--gold);}
.btn-danger-sm{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);color:#fca5a5;}
.btn-danger-sm:hover{background:rgba(239,68,68,.25);}
.btn-success-sm{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.25);color:#6ee7a0;}
.btn-success-sm:hover{background:rgba(34,197,94,.25);}
/* QUICK ACTIONS */
.quick-actions{margin-top:24px;}
.quick-actions h3{font-size:.9rem;font-weight:600;margin-bottom:16px;}
.qa-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;}
.qa-card{background:var(--dark3);border:1px solid var(--border-dim);border-radius:var(--radius);padding:20px;text-align:center;display:flex;flex-direction:column;align-items:center;gap:8px;transition:all .3s;font-size:.75rem;color:var(--gray);}
.qa-card:hover{border-color:var(--gold);color:var(--gold);transform:translateY(-2px);}
.qa-icon{font-size:1.5rem;}
/* FORMS */
.admin-form{max-width:700px;}
.form-group{margin-bottom:18px;}
.form-group label{display:block;color:var(--gray);font-size:.68rem;letter-spacing:2px;text-transform:uppercase;margin-bottom:8px;}
.form-group input,.form-group select,.form-group textarea{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:var(--radius);padding:11px 14px;color:var(--white);font-family:'Montserrat',sans-serif;font-size:.84rem;outline:none;transition:border-color .3s;}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--gold);}
.form-group textarea{resize:vertical;min-height:100px;}
.form-row-2{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.btn-primary{padding:11px 28px;background:var(--gold);color:var(--black);border:none;border-radius:2px;font-family:'Montserrat',sans-serif;font-size:.72rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;cursor:pointer;transition:all .3s;}
.btn-primary:hover{background:var(--gold-light);}
.btn-secondary{padding:11px 28px;background:transparent;color:var(--gray);border:1px solid rgba(255,255,255,.15);border-radius:2px;font-family:'Montserrat',sans-serif;font-size:.72rem;letter-spacing:2px;text-transform:uppercase;cursor:pointer;transition:all .3s;}
.btn-secondary:hover{border-color:rgba(255,255,255,.3);color:var(--white);}
/* GALLERY GRID */
.gallery-admin-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:16px;}
.gallery-admin-item{position:relative;border-radius:var(--radius);overflow:hidden;border:1px solid var(--border-dim);}
.gallery-admin-item img{width:100%;height:160px;object-fit:cover;display:block;}
.gallery-admin-item .item-actions{position:absolute;inset:0;background:rgba(0,0,0,.7);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;opacity:0;transition:opacity .3s;}
.gallery-admin-item:hover .item-actions{opacity:1;}
.gallery-admin-item .item-caption{position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.8);padding:8px;font-size:.72rem;color:var(--white);}
/* NOTIFICATION SEND FORM */
.notif-form{background:var(--dark3);border:1px solid var(--border-dim);border-radius:var(--radius);padding:28px;max-width:600px;}
/* RESPONSIVE */
@media(max-width:1200px){.admin-stats{grid-template-columns:repeat(3,1fr);}.qa-grid{grid-template-columns:repeat(3,1fr);}}
@media(max-width:900px){
  .admin-sidebar{
    transform:translateX(-100%);
    z-index:9999;
  }

  .admin-sidebar.active{
    transform:translateX(0);
  }

  .admin-wrapper{
    margin-left:0;
    width:100%;
  }

  .sidebar-toggle{
    display:flex;
  }
}
@media(max-width:600px){.admin-stats{grid-template-columns:repeat(2,1fr);}.qa-grid{grid-template-columns:repeat(2,1fr);}.form-row-2{grid-template-columns:1fr;}}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-logo">
    <a href="../index.php"><div class="brand">NAPS</div><span class="sub">PHOTOGRAPHERS</span></a>
    <div class="role-tag">Admin Panel</div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section">
      <span class="nav-section-label">Overview</span>
      <a href="dashboard.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':'' ?>">
        <span class="icon">🏠</span> Dashboard
      </a>
    </div>
    <div class="nav-section">
      <span class="nav-section-label">Bookings</span>
      <a href="manage_bookings.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF'])=='manage_bookings.php'?'active':'' ?>">
        <span class="icon">📅</span> Manage Bookings
      </a>
    </div>
    <div class="nav-section">
      <span class="nav-section-label">Content</span>
      <a href="manage_services.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF'])=='manage_services.php'?'active':'' ?>">
        <span class="icon">🎯</span> Services
      </a>
      <a href="manage_gallery.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF'])=='manage_gallery.php'?'active':'' ?>">
        <span class="icon">🖼</span> Gallery
      </a>
      <a href="manage_reviews.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF'])=='manage_reviews.php'?'active':'' ?>">
        <span class="icon">⭐</span> Reviews
      </a>
    </div>
    <div class="nav-section">
      <span class="nav-section-label">Users & Finance</span>
      <a href="manage_users.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF'])=='manage_users.php'?'active':'' ?>">
        <span class="icon">👥</span> Customers
      </a>
      <a href="manage_payments.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF'])=='manage_payments.php'?'active':'' ?>">
        <span class="icon">💳</span> Payments
      </a>
    </div>
    <div class="nav-section">
      <span class="nav-section-label">System</span>
      <a href="manage_notifications.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF'])=='manage_notifications.php'?'active':'' ?>">
        <span class="icon">🔔</span> Notifications
      </a>
      <a href="manage_homepage.php" class="sidebar-link <?= basename($_SERVER['PHP_SELF'])=='manage_homepage.php'?'active':'' ?>">
        <span class="icon">🌐</span> Homepage Content
      </a>
    </div>
  </nav>
  <div class="sidebar-bottom">
    <div class="sidebar-user">
      <div class="s-avatar"><?= strtoupper(substr($_SESSION['full_name'],0,1)) ?></div>
      <div>
        <div class="s-name"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
        <div class="s-email"><?= htmlspecialchars($_SESSION['email']) ?></div>
      </div>
    </div>
    <a href="../auth/logout.php" class="sidebar-logout">Logout</a>
  </div>
</aside>
<!-- MAIN WRAPPER -->
<div class="admin-wrapper">
  <div class="admin-topbar">
    <div class="topbar-left">
      <button class="sidebar-toggle" id="sidebarToggle"><span></span><span></span><span></span></button>
      <span class="topbar-title">Naps Photographers · Admin</span>
    </div>
    <div class="topbar-right">
      <a href="../index.php" class="topbar-btn">View Site</a>
    </div>
  </div>
  <script>
document.addEventListener("DOMContentLoaded", function () {
  const sidebarToggle = document.getElementById("sidebarToggle");
  const adminSidebar = document.getElementById("adminSidebar");

  sidebarToggle.onclick = function () {
    adminSidebar.classList.toggle("active");
  };
});
</script>
<script>
document.addEventListener("click", function (e) {

  const sidebar = document.getElementById("adminSidebar");
  const toggle = document.getElementById("sidebarToggle");

  // close sidebar if clicked outside
  if (
    sidebar.classList.contains("active") &&
    !sidebar.contains(e.target) &&
    !toggle.contains(e.target)
  ) {
    sidebar.classList.remove("active");
  }

});
</script>
</body>
