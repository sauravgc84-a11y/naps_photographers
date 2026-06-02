<?php
// ============================================================
// customer/customer_dashboard.php — Customer Dashboard
// ============================================================
session_start();
require_once '../db.php';
requireLogin();
if (isAdmin()) redirect('../admin/dashboard.php');

$user_id   = (int)$_SESSION['user_id'];
$flash     = getFlash();

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get bookings
$stmt = $conn->prepare("
    SELECT b.*, s.name as service_name, s.image_url as service_img
    FROM bookings b
    JOIN services s ON s.id=b.service_id
    WHERE b.user_id=?
    ORDER BY b.created_at DESC
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Mark notifications as read
$conn->query("UPDATE notifications SET is_read=1 WHERE user_id={$user_id} AND is_read=0");

$unread = 0;
$totalBookings = count($bookings);
$upcomingCount = 0;
$completedCount = 0;
foreach ($bookings as $b) {
    if (in_array($b['status'], ['pending','approved','rescheduled'])) $upcomingCount++;
    if ($b['status'] === 'completed') $completedCount++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Dashboard — Naps Photographers</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--gold:#64cbfa;--gold-light:#E8C97E;--black:#0A0A0A;--dark:#111;--dark2:#161616;--dark3:#1E1E1E;--white:#F8F6F1;--gray:#888;--border:rgba(201,168,76,0.18);--border-dim:rgba(255,255,255,0.06);--radius:4px;}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Montserrat',sans-serif;background:var(--dark);color:var(--white);min-height:100vh;}
a{text-decoration:none;color:inherit;}
/* HEADER */
.dash-header{background:var(--black);border-bottom:1px solid var(--border);padding:0 40px;display:flex;align-items:center;justify-content:space-between;height:64px;position:sticky;top:0;z-index:100;}
.dash-logo{font-family:'Cormorant Garamond',serif;font-size:1.4rem;letter-spacing:3px;color:var(--white);}
.dash-logo span{font-size:.5rem;letter-spacing:5px;color:var(--gold);display:block;}
.header-nav{display:flex;align-items:center;gap:16px;}
.header-nav a{font-size:.72rem;letter-spacing:2px;text-transform:uppercase;color:var(--gray);transition:color .3s;}
.header-nav a:hover{color:var(--gold);}
.header-user{display:flex;align-items:center;gap:10px;}
.user-avatar{width:36px;height:36px;border-radius:50%;background:var(--gold);color:var(--black);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;}
/* MAIN */
.dash-main{max-width:1200px;margin:0 auto;padding:40px;}
.page-title{font-family:'Cormorant Garamond',serif;font-size:2rem;margin-bottom:4px;}
.page-sub{color:var(--gray);font-size:.82rem;margin-bottom:40px;}
/* STATS */
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:40px;}
.stat-card{background:var(--dark2);border:1px solid var(--border-dim);border-radius:var(--radius);padding:24px;transition:border-color .3s;}
.stat-card:hover{border-color:var(--border);}
.stat-card .num{font-family:'Cormorant Garamond',serif;font-size:2.2rem;color:var(--gold);line-height:1;}
.stat-card .lbl{font-size:.72rem;letter-spacing:2px;text-transform:uppercase;color:var(--gray);margin-top:8px;}
/* FLASH */
.flash{padding:14px 20px;border-radius:var(--radius);margin-bottom:24px;font-size:.84rem;}
.flash-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#6ee7a0;}
.flash-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;}
/* TABS */
.tabs{display:flex;gap:0;border-bottom:1px solid var(--border-dim);margin-bottom:32px;}
.tab-btn{padding:12px 28px;background:none;border:none;color:var(--gray);font-family:'Montserrat',sans-serif;font-size:.75rem;letter-spacing:2px;text-transform:uppercase;cursor:pointer;border-bottom:2px solid transparent;transition:all .3s;margin-bottom:-1px;}
.tab-btn.active{color:var(--gold);border-bottom-color:var(--gold);}
.tab-panel{display:none;}
.tab-panel.active{display:block;}
/* TABLE */
.data-table{width:100%;border-collapse:collapse;}
.data-table th{text-align:left;font-size:.68rem;letter-spacing:2px;text-transform:uppercase;color:var(--gold);padding:12px 16px;border-bottom:1px solid var(--border);}
.data-table td{padding:16px;border-bottom:1px solid var(--border-dim);font-size:.83rem;vertical-align:middle;}
.data-table tr:hover td{background:rgba(255,255,255,.02);}
.badge{padding:3px 10px;border-radius:20px;font-size:.68rem;font-weight:600;}
.badge-success{background:rgba(34,197,94,.15);color:#6ee7a0;border:1px solid rgba(34,197,94,.3);}
.badge-warning{background:rgba(234,179,8,.15);color:#fde047;border:1px solid rgba(234,179,8,.3);}
.badge-danger{background:rgba(239,68,68,.15);color:#fca5a5;border:1px solid rgba(239,68,68,.3);}
.badge-info{background:rgba(59,130,246,.15);color:#93c5fd;border:1px solid rgba(59,130,246,.3);}
.badge-primary{background:rgba(201,168,76,.15);color:var(--gold);border:1px solid rgba(201,168,76,.3);}
.badge-secondary{background:rgba(100,116,139,.15);color:#94a3b8;border:1px solid rgba(100,116,139,.3);}
/* ACTIONS */
.btn-sm{padding:6px 16px;border-radius:2px;font-size:.7rem;letter-spacing:1px;text-transform:uppercase;font-weight:600;cursor:pointer;border:none;font-family:'Montserrat',sans-serif;transition:all .3s;display:inline-block;}
.btn-gold-sm{background:var(--gold);color:var(--black);}
.btn-gold-sm:hover{background:var(--gold-light);}
.btn-outline-sm{background:transparent;border:1px solid rgba(255,255,255,.2);color:var(--white);}
.btn-outline-sm:hover{border-color:var(--gold);color:var(--gold);}
.btn-danger-sm{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5;}
.btn-danger-sm:hover{background:rgba(239,68,68,.3);}
/* NOTIFICATIONS */
.notif-list{display:flex;flex-direction:column;gap:12px;}
.notif-item{background:var(--dark2);border:1px solid var(--border-dim);border-radius:var(--radius);padding:16px 20px;display:flex;gap:16px;align-items:flex-start;}
.notif-item.unread{border-color:rgba(201,168,76,.25);background:rgba(201,168,76,.04);}
.notif-icon{font-size:1.2rem;flex-shrink:0;}
.notif-title{font-size:.85rem;font-weight:600;margin-bottom:4px;}
.notif-msg{font-size:.8rem;color:var(--gray);line-height:1.6;}
.notif-time{font-size:.7rem;color:var(--gray);margin-top:4px;}
/* PAYMENT MODAL */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:1000;display:none;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:var(--dark2);border:1px solid var(--border);border-radius:var(--radius);padding:40px;width:100%;max-width:480px;}
.modal h3{font-family:'Cormorant Garamond',serif;font-size:1.6rem;margin-bottom:6px;}
.modal .amount-due{color:var(--gold);font-size:1.1rem;margin-bottom:28px;}
.form-group{margin-bottom:16px;}
.form-group label{display:block;color:var(--gray);font-size:.68rem;letter-spacing:2px;text-transform:uppercase;margin-bottom:8px;}
.form-group input{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:var(--radius);padding:12px 16px;color:var(--white);font-family:'Montserrat',sans-serif;font-size:.85rem;outline:none;transition:border-color .3s;}
.form-group input:focus{border-color:var(--gold);}
.card-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.btn-pay{width:100%;padding:14px;background:var(--gold);color:var(--black);border:none;border-radius:2px;font-family:'Montserrat',sans-serif;font-size:.78rem;font-weight:700;letter-spacing:3px;text-transform:uppercase;cursor:pointer;transition:all .3s;margin-top:8px;}
.btn-pay:hover{background:var(--gold-light);}
.btn-cancel-modal{width:100%;padding:10px;background:transparent;border:1px solid rgba(255,255,255,.1);color:var(--gray);border-radius:2px;font-family:'Montserrat',sans-serif;font-size:.72rem;letter-spacing:2px;text-transform:uppercase;cursor:pointer;margin-top:8px;transition:all .3s;}
.btn-cancel-modal:hover{border-color:rgba(255,255,255,.3);color:var(--white);}
.secure-badge{text-align:center;font-size:.7rem;color:var(--gray);margin-top:16px;}
.booking-ref-tag{font-family:monospace;background:rgba(201,168,76,.1);color:var(--gold);padding:2px 8px;border-radius:2px;font-size:.8rem;}
@media(max-width:768px){.stats-row{grid-template-columns:1fr 1fr;}.dash-main{padding:24px 16px;}.data-table{font-size:.75rem;}}
</style>
</head>
<body>

<header class="dash-header">
  <a href="../index.php" class="dash-logo">NAPS<span>PHOTOGRAPHERS</span></a>
  <nav class="header-nav">
    <a href="../index.php">Home</a>
    <a href="../index.php#booking">Book Session</a>
    <div class="header-user">
      <div class="user-avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
      <span style="font-size:.8rem;color:var(--gray);"><?= htmlspecialchars($_SESSION['full_name']) ?></span>
    </div>
    <a href="../auth/logout.php" style="color:var(--gold);font-size:.72rem;letter-spacing:2px;text-transform:uppercase;">Logout</a>
  </nav>
</header>

<main class="dash-main">
  <h1 class="page-title">My Dashboard</h1>
  <p class="page-sub">Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?></p>

  <?php if ($flash): ?>
  <div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
  <?php endif; ?>

  <!-- STATS -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="num"><?= $totalBookings ?></div>
      <div class="lbl">Total Bookings</div>
    </div>
    <div class="stat-card">
      <div class="num"><?= $upcomingCount ?></div>
      <div class="lbl">Upcoming</div>
    </div>
    <div class="stat-card">
      <div class="num"><?= $completedCount ?></div>
      <div class="lbl">Completed</div>
    </div>
    <div class="stat-card">
      <div class="num"><?= count($notifications) ?></div>
      <div class="lbl">Notifications</div>
    </div>
  </div>

  <!-- TABS -->
  <div class="tabs">
    <button class="tab-btn active" onclick="showTab('bookings', this)">My Bookings</button>
    <button class="tab-btn" onclick="showTab('notifications', this)">Notifications</button>
    <button class="tab-btn" onclick="showTab('profile', this)">Profile</button>
  </div>

  <!-- BOOKINGS TAB -->
  <div class="tab-panel active" id="tab-bookings">
    <?php if (empty($bookings)): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--gray);">
      <div style="font-size:3rem;margin-bottom:16px;">📷</div>
      <p>No bookings yet. <a href="../index.php#booking" style="color:var(--gold);">Book your first session!</a></p>
    </div>
    <?php else: ?>
    <table class="data-table">
      <thead>
        <tr>
          <th>Reference</th>
          <th>Service</th>
          <th>Date & Time</th>
          <th>Price</th>
          <th>Status</th>
          <th>Payment</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($bookings as $b): ?>
        <tr>
          <td><span class="booking-ref-tag"><?= htmlspecialchars($b['booking_ref']) ?></span></td>
          <td><?= htmlspecialchars($b['service_name']) ?></td>
          <td>
            <div><?= date('M j, Y', strtotime($b['booking_date'])) ?></div>
            <div style="color:var(--gray);font-size:.75rem;"><?= date('g:i A', strtotime($b['booking_time'])) ?></div>
          </td>
          <td style="color:var(--gold);">$<?= number_format($b['total_price'], 2) ?></td>
          <td><?= statusBadge($b['status']) ?></td>
          <td><?= paymentBadge($b['payment_status']) ?></td>
          <td style="display:flex;gap:6px;flex-wrap:wrap;">
            <?php if ($b['payment_status'] === 'unpaid' && $b['status'] === 'approved'): ?>
              <button class="btn-sm btn-gold-sm" onclick="openPayment(<?= $b['id'] ?>, '<?= htmlspecialchars($b['booking_ref']) ?>', <?= $b['total_price'] ?>)">Pay Now</button>
            <?php endif; ?>
            <?php if (!in_array($b['status'], ['completed','cancelled','rejected'])): ?>
              <a href="../booking/update_booking.php?id=<?= $b['id'] ?>&action=cancel"
                 class="btn-sm btn-danger-sm"
                 onclick="return confirm('Cancel this booking?')">Cancel</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- NOTIFICATIONS TAB -->
  <div class="tab-panel" id="tab-notifications">
    <?php if (empty($notifications)): ?>
    <div style="text-align:center;padding:60px;color:var(--gray);">No notifications yet.</div>
    <?php else: ?>
    <div class="notif-list">
      <?php foreach ($notifications as $n): ?>
      <div class="notif-item">
        <div class="notif-icon"><?= $n['type']==='booking'?'📅':($n['type']==='payment'?'💳':'🔔') ?></div>
        <div>
          <div class="notif-title"><?= htmlspecialchars($n['title']) ?></div>
          <div class="notif-msg"><?= htmlspecialchars($n['message']) ?></div>
          <div class="notif-time"><?= date('M j, Y · g:i A', strtotime($n['created_at'])) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- PROFILE TAB -->
  <div class="tab-panel" id="tab-profile">
    <div style="max-width:480px;">
      <div style="background:var(--dark2);border:1px solid var(--border-dim);border-radius:var(--radius);padding:32px;">
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:28px;">
          <div style="width:64px;height:64px;border-radius:50%;background:var(--gold);color:var(--black);display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;">
            <?= strtoupper(substr($user['full_name'],0,1)) ?>
          </div>
          <div>
            <div style="font-size:1.1rem;font-weight:600;"><?= htmlspecialchars($user['full_name']) ?></div>
            <div style="color:var(--gold);font-size:.72rem;letter-spacing:2px;text-transform:uppercase;">Customer Account</div>
          </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px;">
          <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border-dim);">
            <span style="color:var(--gray);font-size:.8rem;">Email</span>
            <span style="font-size:.85rem;"><?= htmlspecialchars($user['email']) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border-dim);">
            <span style="color:var(--gray);font-size:.8rem;">Phone</span>
            <span style="font-size:.85rem;"><?= htmlspecialchars($user['phone'] ?: 'Not set') ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border-dim);">
            <span style="color:var(--gray);font-size:.8rem;">Member Since</span>
            <span style="font-size:.85rem;"><?= date('F Y', strtotime($user['created_at'])) ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- PAYMENT MODAL -->
<div class="modal-overlay" id="paymentModal">
  <div class="modal">
    <h3>Secure Payment</h3>
    <p class="amount-due" id="payAmount">Amount due: $0.00</p>
    <form method="POST" action="../payment/payment.php" id="paymentForm" onsubmit="return validatePayment()">
      <input type="hidden" name="booking_id" id="payBookingId">
      <div class="form-group">
        <label>Card Number</label>
        <input type="text" name="card_number" id="card_number" placeholder="1234 5678 9012 3456" maxlength="19" required>
      </div>
      <div class="form-group">
        <label>Cardholder Name</label>
        <input type="text" name="card_holder" placeholder="Name as on card" required>
      </div>
      <div class="card-row">
        <div class="form-group">
          <label>Expiry Date</label>
          <input type="text" name="expiry" id="expiry" placeholder="MM/YY" maxlength="5" required>
        </div>
        <div class="form-group">
          <label>CVV</label>
          <input type="text" name="cvv" id="cvv" placeholder="•••" maxlength="4" required>
        </div>
      </div>
      <button type="submit" class="btn-pay">Pay Now</button>
      <button type="button" class="btn-cancel-modal" onclick="closePayment()">Cancel</button>
    </form>
    <div class="secure-badge"> 256-bit SSL encrypted · Demo payment simulation</div>
  </div>
</div>

<script>
function showTab(id, btn) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-'+id).classList.add('active');
  btn.classList.add('active');
}
function openPayment(id, ref, amount) {
  document.getElementById('payBookingId').value = id;
  document.getElementById('payAmount').textContent = 'Amount due: $' + parseFloat(amount).toFixed(2);
  document.getElementById('paymentModal').classList.add('open');
}
function closePayment() {
  document.getElementById('paymentModal').classList.remove('open');
}
function validatePayment() {
  const card = document.getElementById('card_number').value.replace(/\s/g,'');
  const exp  = document.getElementById('expiry').value;
  const cvv  = document.getElementById('cvv').value;
  if (card.length < 13) { alert('Please enter a valid card number.'); return false; }
  if (!/^\d{2}\/\d{2}$/.test(exp)) { alert('Please enter a valid expiry date (MM/YY).'); return false; }
  if (cvv.length < 3) { alert('Please enter a valid CVV.'); return false; }
  return true;
}
// Card number formatting
document.getElementById('card_number')?.addEventListener('input', e => {
  let v = e.target.value.replace(/\D/g,'').substring(0,16);
  e.target.value = v.match(/.{1,4}/g)?.join(' ') || v;
});
document.getElementById('expiry')?.addEventListener('input', e => {
  let v = e.target.value.replace(/\D/g,'').substring(0,4);
  if (v.length >= 2) v = v.substring(0,2)+'/'+v.substring(2);
  e.target.value = v;
});
document.getElementById('cvv')?.addEventListener('input', e => {
  e.target.value = e.target.value.replace(/\D/g,'').substring(0,4);
});
</script>
</body>
</html>
