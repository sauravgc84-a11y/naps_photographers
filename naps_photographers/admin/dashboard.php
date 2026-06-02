<?php
// ============================================================
// admin/dashboard.php — Admin Dashboard Overview
// ============================================================
session_start();
require_once '../db.php';
requireLogin();
requireAdmin();

$flash = getFlash();

// Stats
$totalUsers    = $conn->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetch_row()[0];
$totalBookings = $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0];
$pendingBook   = $conn->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetch_row()[0];
$totalRevenue  = $conn->query("SELECT SUM(amount) FROM payments WHERE status='success'")->fetch_row()[0] ?? 0;
$totalServices = $conn->query("SELECT COUNT(*) FROM services WHERE is_active=1")->fetch_row()[0];
$pendingReviews= $conn->query("SELECT COUNT(*) FROM reviews WHERE is_approved=0")->fetch_row()[0];

// Recent bookings
$recentBookings = $conn->query("
    SELECT b.*, u.full_name, s.name as service_name
    FROM bookings b
    JOIN users u ON u.id=b.user_id
    JOIN services s ON s.id=b.service_id
    ORDER BY b.created_at DESC LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

// Monthly revenue chart data
$revenueData = $conn->query("
    SELECT DATE_FORMAT(paid_at,'%b') as month, SUM(amount) as total
    FROM payments WHERE status='success' AND paid_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(paid_at,'%Y-%m')
    ORDER BY DATE_FORMAT(paid_at,'%Y-%m')
")->fetch_all(MYSQLI_ASSOC);

require_once 'partials/admin_header.php';
?>

<div class="admin-content">
  <div class="content-header">
    <h1>Dashboard Overview</h1>
    <span class="content-date"><?= date('l, F j, Y') ?></span>
  </div>

  <?php if ($flash): ?>
  <div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
  <?php endif; ?>

  <!-- STAT CARDS -->
  <div class="admin-stats">
    <div class="admin-stat-card">
      <div class="asc-icon gold">👥</div>
      <div class="asc-body">
        <div class="asc-num"><?= $totalUsers ?></div>
        <div class="asc-lbl">Total Customers</div>
      </div>
    </div>
    <div class="admin-stat-card">
      <div class="asc-icon blue">📅</div>
      <div class="asc-body">
        <div class="asc-num"><?= $totalBookings ?></div>
        <div class="asc-lbl">Total Bookings</div>
      </div>
    </div>
    <div class="admin-stat-card">
      <div class="asc-icon orange">⏳</div>
      <div class="asc-body">
        <div class="asc-num"><?= $pendingBook ?></div>
        <div class="asc-lbl">Pending Bookings</div>
      </div>
    </div>
    <div class="admin-stat-card">
      <div class="asc-icon green">💰</div>
      <div class="asc-body">
        <div class="asc-num">$<?= number_format($totalRevenue, 0) ?></div>
        <div class="asc-lbl">Total Revenue</div>
      </div>
    </div>
    <div class="admin-stat-card">
      <div class="asc-icon purple">📷</div>
      <div class="asc-body">
        <div class="asc-num"><?= $totalServices ?></div>
        <div class="asc-lbl">Active Services</div>
      </div>
    </div>
    <div class="admin-stat-card">
      <div class="asc-icon red">⭐</div>
      <div class="asc-body">
        <div class="asc-num"><?= $pendingReviews ?></div>
        <div class="asc-lbl">Pending Reviews</div>
      </div>
    </div>
  </div>

  <!-- RECENT BOOKINGS -->
  <div class="admin-card">
    <div class="card-head">
      <h3>Recent Bookings</h3>
      <a href="manage_bookings.php" class="btn-link">View All →</a>
    </div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Ref</th><th>Customer</th><th>Service</th><th>Date</th><th>Price</th><th>Status</th><th>Payment</th><th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentBookings as $b): ?>
        <tr>
          <td><span class="ref-tag"><?= htmlspecialchars($b['booking_ref']) ?></span></td>
          <td><?= htmlspecialchars($b['full_name']) ?></td>
          <td><?= htmlspecialchars($b['service_name']) ?></td>
          <td><?= date('M j, Y', strtotime($b['booking_date'])) ?></td>
          <td style="color:var(--gold);">$<?= number_format($b['total_price'],2) ?></td>
          <td><?= statusBadge($b['status']) ?></td>
          <td><?= paymentBadge($b['payment_status']) ?></td>
          <td><a href="manage_bookings.php?id=<?= $b['id'] ?>" class="btn-sm btn-outline-sm">Manage</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- QUICK ACTIONS -->
  <div class="quick-actions">
    <h3>Quick Actions</h3>
    <div class="qa-grid">
      <a href="manage_bookings.php" class="qa-card">
        <span class="qa-icon">📋</span>
        <span>Manage Bookings</span>
      </a>
      <a href="manage_services.php" class="qa-card">
        <span class="qa-icon">🎯</span>
        <span>Add Service</span>
      </a>
      <a href="manage_gallery.php" class="qa-card">
        <span class="qa-icon">🖼</span>
        <span>Upload Gallery</span>
      </a>
      <a href="manage_reviews.php" class="qa-card">
        <span class="qa-icon">⭐</span>
        <span>Approve Reviews</span>
      </a>
      <a href="manage_users.php" class="qa-card">
        <span class="qa-icon">👤</span>
        <span>View Users</span>
      </a>
      <a href="manage_payments.php" class="qa-card">
        <span class="qa-icon">💳</span>
        <span>Payments</span>
      </a>
    </div>
  </div>
</div>

<?php require_once 'partials/admin_footer.php'; ?>
