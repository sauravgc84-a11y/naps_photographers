<?php
session_start();
require_once '../db.php';
requireLogin(); requireAdmin();
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid    = (int)($_POST['user_id'] ?? 0);
    $action = clean($_POST['action'] ?? '');
    if ($uid && $uid !== (int)$_SESSION['user_id']) {
        if ($action === 'block') {
            $conn->query("UPDATE users SET is_blocked=1 WHERE id={$uid}");
            setFlash('info', 'User blocked.');
        } elseif ($action === 'unblock') {
            $conn->query("UPDATE users SET is_blocked=0 WHERE id={$uid}");
            setFlash('success', 'User unblocked.');
        } elseif ($action === 'delete') {
            $conn->query("DELETE FROM users WHERE id={$uid} AND role='customer'");
            setFlash('success', 'User deleted.');
        }
    }
    redirect('manage_users.php');
}

$users = $conn->query("
    SELECT u.*, COUNT(b.id) as booking_count
    FROM users u LEFT JOIN bookings b ON b.user_id=u.id
    WHERE u.role='customer'
    GROUP BY u.id ORDER BY u.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

require_once 'partials/admin_header.php';
?>
<div class="admin-content">
  <div class="content-header"><h1>Manage Customers</h1></div>
  <?php if ($flash): ?><div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div><?php endif; ?>
  <div class="admin-card">
    <table class="data-table">
      <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Bookings</th><th>Joined</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td style="font-weight:500;"><?= htmlspecialchars($u['full_name']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['phone'] ?: '—') ?></td>
          <td><span class="badge badge-primary"><?= $u['booking_count'] ?></span></td>
          <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
          <td><?= $u['is_blocked'] ? '<span class="badge badge-danger">Blocked</span>' : '<span class="badge badge-success">Active</span>' ?></td>
          <td>
            <div style="display:flex;gap:6px;">
              <form method="POST" style="display:inline">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <input type="hidden" name="action" value="<?= $u['is_blocked']?'unblock':'block' ?>">
                <button class="btn-sm <?= $u['is_blocked']?'btn-success-sm':'btn-outline-sm' ?>"><?= $u['is_blocked']?'Unblock':'Block' ?></button>
              </form>
              <form method="POST" style="display:inline">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <input type="hidden" name="action" value="delete">
                <button class="btn-sm btn-danger-sm" data-confirm="Delete this user and all their data?">Delete</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once 'partials/admin_footer.php'; ?>
