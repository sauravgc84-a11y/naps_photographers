<?php
// ============================================================
// admin/manage_bookings.php — Manage All Bookings
// ============================================================
session_start();
require_once '../db.php';
requireLogin(); requireAdmin();

$flash = getFlash();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bid    = (int)($_POST['booking_id'] ?? 0);
    $action = clean($_POST['action'] ?? '');

    if ($bid) {
        // Fetch booking for notification
        $bk = $conn->query("SELECT b.*, u.id as uid FROM bookings b JOIN users u ON u.id=b.user_id WHERE b.id={$bid}")->fetch_assoc();

        switch ($action) {
            case 'approve':
                $conn->query("UPDATE bookings SET status='approved', updated_at=NOW() WHERE id={$bid}");
                if ($bk) sendNotification($conn, $bk['uid'], 'Booking Approved!', 'Your booking ' . $bk['booking_ref'] . ' has been approved. Please proceed to payment.', 'booking');
                setFlash('success', 'Booking approved.');
                break;
            case 'reject':
                $notes = clean($_POST['admin_notes'] ?? '');
                $conn->prepare("UPDATE bookings SET status='rejected', admin_notes=?, updated_at=NOW() WHERE id=?")->bind_param('si', $notes, $bid)->execute() ?? $conn->query("UPDATE bookings SET status='rejected', updated_at=NOW() WHERE id={$bid}");
                if ($bk) sendNotification($conn, $bk['uid'], 'Booking Update', 'Your booking ' . $bk['booking_ref'] . ' has been declined. ' . ($notes ? 'Reason: '.$notes : ''), 'booking');
                setFlash('info', 'Booking rejected.');
                break;
            case 'complete':
                $conn->query("UPDATE bookings SET status='completed', updated_at=NOW() WHERE id={$bid}");
                if ($bk) sendNotification($conn, $bk['uid'], 'Session Completed!', 'Your session ' . $bk['booking_ref'] . ' has been marked as completed. Thank you!', 'booking');
                setFlash('success', 'Booking marked complete.');
                break;
            case 'reschedule':
                $nd = clean($_POST['new_date'] ?? '');
                $nt = clean($_POST['new_time'] ?? '');
                if ($nd && $nt) {
                    $stmt = $conn->prepare("UPDATE bookings SET booking_date=?, booking_time=?, status='rescheduled', updated_at=NOW() WHERE id=?");
                    $stmt->bind_param('ssi', $nd, $nt, $bid);
                    $stmt->execute(); $stmt->close();
                    if ($bk) sendNotification($conn, $bk['uid'], 'Booking Rescheduled', 'Your booking ' . $bk['booking_ref'] . ' has been rescheduled to ' . date('M j, Y', strtotime($nd)) . ' at ' . date('g:i A', strtotime($nt)) . '.', 'booking');
                    setFlash('success', 'Booking rescheduled.');
                }
                break;
            case 'delete':
                $conn->query("DELETE FROM bookings WHERE id={$bid}");
                setFlash('success', 'Booking deleted.');
                break;
        }
    }
    redirect('manage_bookings.php');
}

// Fetch bookings with filters
$status_filter = clean($_GET['status'] ?? '');
$where = $status_filter ? "WHERE b.status='" . $conn->real_escape_string($status_filter) . "'" : '';

$bookings = $conn->query("
    SELECT b.*, u.full_name, u.email as u_email, s.name as service_name
    FROM bookings b
    JOIN users u ON u.id=b.user_id
    JOIN services s ON s.id=b.service_id
    {$where}
    ORDER BY b.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

require_once 'partials/admin_header.php';
?>
<div class="admin-content">
  <div class="content-header">
    <h1>Manage Bookings</h1>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <a href="manage_bookings.php" class="btn-sm <?= !$status_filter?'btn-gold-sm':'btn-outline-sm' ?>">All</a>
      <a href="?status=pending"    class="btn-sm <?= $status_filter=='pending'?'btn-gold-sm':'btn-outline-sm' ?>">Pending</a>
      <a href="?status=approved"   class="btn-sm <?= $status_filter=='approved'?'btn-gold-sm':'btn-outline-sm' ?>">Approved</a>
      <a href="?status=completed"  class="btn-sm <?= $status_filter=='completed'?'btn-gold-sm':'btn-outline-sm' ?>">Completed</a>
    </div>
  </div>

  <?php if ($flash): ?>
  <div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
  <?php endif; ?>

  <div class="admin-card">
    <table class="data-table">
      <thead>
        <tr><th>Ref</th><th>Customer</th><th>Service</th><th>Date & Time</th><th>Price</th><th>Status</th><th>Payment</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($bookings as $b): ?>
        <tr>
          <td><span class="ref-tag"><?= htmlspecialchars($b['booking_ref']) ?></span></td>
          <td>
            <div style="font-weight:500;"><?= htmlspecialchars($b['full_name']) ?></div>
            <div style="color:var(--gray);font-size:.72rem;"><?= htmlspecialchars($b['u_email']) ?></div>
          </td>
          <td><?= htmlspecialchars($b['service_name']) ?></td>
          <td>
            <div><?= date('M j, Y', strtotime($b['booking_date'])) ?></div>
            <div style="color:var(--gray);font-size:.72rem;"><?= date('g:i A', strtotime($b['booking_time'])) ?></div>
          </td>
          <td style="color:var(--gold);">$<?= number_format($b['total_price'],2) ?></td>
          <td><?= statusBadge($b['status']) ?></td>
          <td><?= paymentBadge($b['payment_status']) ?></td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap;">
              <?php if ($b['status']==='pending'): ?>
              <form method="POST" style="display:inline">
                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                <input type="hidden" name="action" value="approve">
                <button class="btn-sm btn-success-sm">✓ Approve</button>
              </form>
              <form method="POST" style="display:inline">
                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                <input type="hidden" name="action" value="reject">
                <button class="btn-sm btn-danger-sm">✗ Reject</button>
              </form>
              <?php endif; ?>
              <?php if ($b['status']==='approved'): ?>
              <form method="POST" style="display:inline">
                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                <input type="hidden" name="action" value="complete">
                <button class="btn-sm btn-gold-sm">Complete</button>
              </form>
              <?php endif; ?>
              <button class="btn-sm btn-outline-sm" onclick="openReschedule(<?= $b['id'] ?>, '<?= $b['booking_date'] ?>', '<?= $b['booking_time'] ?>')">Reschedule</button>
              <form method="POST" style="display:inline">
                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                <input type="hidden" name="action" value="delete">
                <button class="btn-sm btn-danger-sm" data-confirm="Delete this booking permanently?">Delete</button>
              </form>
            </div>
            <?php if ($b['message']): ?>
            <div style="color:var(--gray);font-size:.7rem;margin-top:4px;">Note: <?= htmlspecialchars(substr($b['message'],0,60)) ?><?= strlen($b['message'])>60?'...':'' ?></div>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($bookings)): ?>
        <tr><td colspan="8" style="text-align:center;color:var(--gray);padding:40px;">No bookings found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- RESCHEDULE MODAL -->
<div style="position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:999;display:none;align-items:center;justify-content:center;" id="rescheduleModal">
  <div style="background:var(--dark3);border:1px solid var(--border);border-radius:var(--radius);padding:36px;width:100%;max-width:420px;">
    <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;margin-bottom:24px;">Reschedule Booking</h3>
    <form method="POST" id="rescheduleForm">
      <input type="hidden" name="action" value="reschedule">
      <input type="hidden" name="booking_id" id="rBid">
      <div class="form-group">
        <label>New Date</label>
        <input type="date" name="new_date" id="rDate" required min="<?= date('Y-m-d',strtotime('+1 day')) ?>">
      </div>
      <div class="form-group">
        <label>New Time</label>
        <select name="new_time" id="rTime" required>
          <option value="09:00">9:00 AM</option><option value="10:00">10:00 AM</option>
          <option value="11:00">11:00 AM</option><option value="12:00">12:00 PM</option>
          <option value="13:00">1:00 PM</option><option value="14:00">2:00 PM</option>
          <option value="15:00">3:00 PM</option><option value="16:00">4:00 PM</option>
        </select>
      </div>
      <div style="display:flex;gap:10px;margin-top:8px;">
        <button type="submit" class="btn-primary">Reschedule</button>
        <button type="button" class="btn-secondary" onclick="closeReschedule()">Cancel</button>
      </div>
    </form>
  </div>
</div>
<script>
function openReschedule(id, date, time) {
  document.getElementById('rBid').value = id;
  document.getElementById('rDate').value = date;
  document.getElementById('rTime').value = time.substring(0,5);
  document.getElementById('rescheduleModal').style.display = 'flex';
}
function closeReschedule() {
  document.getElementById('rescheduleModal').style.display = 'none';
}
</script>
<?php require_once 'partials/admin_footer.php'; ?>
