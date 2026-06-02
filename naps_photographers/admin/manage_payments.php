<?php
session_start();
require_once '../db.php';
requireLogin(); requireAdmin();

$payments = $conn->query("
    SELECT p.*, b.booking_ref, u.full_name, s.name as service_name
    FROM payments p
    JOIN bookings b ON b.id=p.booking_id
    JOIN users u ON u.id=p.user_id
    JOIN services s ON s.id=b.service_id
    ORDER BY p.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$totalRevenue  = $conn->query("SELECT SUM(amount) FROM payments WHERE status='success'")->fetch_row()[0] ?? 0;
$successCount  = $conn->query("SELECT COUNT(*) FROM payments WHERE status='success'")->fetch_row()[0];
$failedCount   = $conn->query("SELECT COUNT(*) FROM payments WHERE status='failed'")->fetch_row()[0];
$pendingCount  = $conn->query("SELECT COUNT(*) FROM payments WHERE status='pending'")->fetch_row()[0];

require_once 'partials/admin_header.php';
?>
<div class="admin-content">
  <div class="content-header"><h1>Payment Records</h1></div>
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;">
    <div class="admin-stat-card"><div class="asc-icon green">💰</div><div class="asc-body"><div class="asc-num">$<?= number_format($totalRevenue,0) ?></div><div class="asc-lbl">Total Revenue</div></div></div>
    <div class="admin-stat-card"><div class="asc-icon gold">✓</div><div class="asc-body"><div class="asc-num"><?= $successCount ?></div><div class="asc-lbl">Successful</div></div></div>
    <div class="admin-stat-card"><div class="asc-icon red">✗</div><div class="asc-body"><div class="asc-num"><?= $failedCount ?></div><div class="asc-lbl">Failed</div></div></div>
    <div class="admin-stat-card"><div class="asc-icon orange">⏳</div><div class="asc-body"><div class="asc-num"><?= $pendingCount ?></div><div class="asc-lbl">Pending</div></div></div>
  </div>
  <div class="admin-card">
    <table class="data-table">
      <thead><tr><th>Transaction</th><th>Customer</th><th>Booking</th><th>Service</th><th>Amount</th><th>Card</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach ($payments as $p): ?>
        <tr>
          <td><span class="ref-tag" style="font-size:.7rem;"><?= htmlspecialchars($p['transaction_ref']) ?></span></td>
          <td><?= htmlspecialchars($p['full_name']) ?></td>
          <td><span class="ref-tag"><?= htmlspecialchars($p['booking_ref']) ?></span></td>
          <td><?= htmlspecialchars($p['service_name']) ?></td>
          <td style="color:var(--gold);font-weight:600;">$<?= number_format($p['amount'],2) ?></td>
          <td style="font-family:monospace;color:var(--gray);">**** **** **** <?= htmlspecialchars($p['card_last_four'] ?? '????') ?></td>
          <td><?= paymentBadge($p['status']) ?></td>
          <td style="font-size:.75rem;color:var(--gray);"><?= $p['paid_at'] ? date('M j, Y g:i A', strtotime($p['paid_at'])) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($payments)): ?><tr><td colspan="8" style="text-align:center;color:var(--gray);padding:40px;">No payment records found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once 'partials/admin_footer.php'; ?>
