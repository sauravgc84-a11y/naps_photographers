<?php
session_start();
require_once '../db.php';
requireLogin(); requireAdmin();
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id  = (int)($_POST['user_id'] ?? 0);
    $title    = clean($_POST['title'] ?? '');
    $message  = clean($_POST['message'] ?? '');
    $type     = clean($_POST['type'] ?? 'system');
    $send_all = isset($_POST['send_all']);

    if ($title && $message) {
        if ($send_all) {
            $users = $conn->query("SELECT id FROM users WHERE role='customer'")->fetch_all(MYSQLI_ASSOC);
            foreach ($users as $u) {
                sendNotification($conn, $u['id'], $title, $message, $type);
            }
            setFlash('success', 'Notification sent to all customers.');
        } elseif ($user_id) {
            sendNotification($conn, $user_id, $title, $message, $type);
            setFlash('success', 'Notification sent.');
        } else {
            setFlash('error', 'Please select a recipient.');
        }
    }
    redirect('manage_notifications.php');
}

$customers = $conn->query("SELECT id, full_name, email FROM users WHERE role='customer' ORDER BY full_name")->fetch_all(MYSQLI_ASSOC);
$recent    = $conn->query("
    SELECT n.*, u.full_name FROM notifications n JOIN users u ON u.id=n.user_id
    ORDER BY n.created_at DESC LIMIT 20
")->fetch_all(MYSQLI_ASSOC);

require_once 'partials/admin_header.php';
?>
<div class="admin-content">
  <div class="content-header"><h1>Manage Notifications</h1></div>
  <?php if ($flash): ?><div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div><?php endif; ?>

  <div style="display:grid;grid-template-columns:380px 1fr;gap:24px;align-items:start;">
    <div class="notif-form">
      <h3 style="margin-bottom:24px;font-size:.95rem;font-weight:600;">Send Notification</h3>
      <form method="POST">
        <div class="form-group">
          <label>Recipient</label>
          <select name="user_id">
            <option value="">Select customer...</option>
            <?php foreach ($customers as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['full_name']) ?> (<?= htmlspecialchars($c['email']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="display:flex;align-items:center;gap:10px;">
          <input type="checkbox" name="send_all" id="send_all" style="width:auto;">
          <label for="send_all" style="text-transform:none;letter-spacing:0;font-size:.85rem;color:var(--white);cursor:pointer;">Send to ALL customers</label>
        </div>
        <div class="form-group">
          <label>Type</label>
          <select name="type">
            <option value="system">System</option>
            <option value="booking">Booking</option>
            <option value="payment">Payment</option>
            <option value="alert">Alert</option>
          </select>
        </div>
        <div class="form-group"><label>Title</label><input type="text" name="title" placeholder="Notification title" required></div>
        <div class="form-group"><label>Message</label><textarea name="message" rows="4" placeholder="Your message..." required></textarea></div>
        <button type="submit" class="btn-primary">Send Notification</button>
      </form>
    </div>

    <div class="admin-card">
      <h3 style="margin-bottom:20px;">Recent Notifications (<?= count($recent) ?>)</h3>
      <?php foreach ($recent as $n): ?>
      <div style="border-bottom:1px solid var(--border-dim);padding:14px 0;display:flex;gap:14px;align-items:flex-start;">
        <div style="font-size:1.2rem;"><?= $n['type']==='booking'?'📅':($n['type']==='payment'?'💳':($n['type']==='alert'?'⚠️':'🔔')) ?></div>
        <div style="flex:1;">
          <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <strong style="font-size:.85rem;"><?= htmlspecialchars($n['title']) ?></strong>
            <span style="color:var(--gray);font-size:.72rem;">To: <?= htmlspecialchars($n['full_name']) ?></span>
          </div>
          <p style="color:var(--gray);font-size:.8rem;margin-top:4px;"><?= htmlspecialchars($n['message']) ?></p>
          <span style="color:rgba(136,136,136,.5);font-size:.7rem;"><?= date('M j, Y g:i A', strtotime($n['created_at'])) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($recent)): ?><p style="color:var(--gray);">No notifications sent yet.</p><?php endif; ?>
    </div>
  </div>
</div>
<?php require_once 'partials/admin_footer.php'; ?>
