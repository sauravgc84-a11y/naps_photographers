<?php
session_start();
require_once '../db.php';
requireLogin(); requireAdmin();
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rid    = (int)($_POST['review_id'] ?? 0);
    $action = clean($_POST['action'] ?? '');
    if ($rid) {
        if ($action === 'approve') {
            $conn->query("UPDATE reviews SET is_approved=1 WHERE id={$rid}");
            setFlash('success', 'Review approved and published.');
        } elseif ($action === 'delete') {
            $conn->query("DELETE FROM reviews WHERE id={$rid}");
            setFlash('info', 'Review deleted.');
        }
    }
    redirect('manage_reviews.php');
}

$pending  = $conn->query("SELECT * FROM reviews WHERE is_approved=0 ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$approved = $conn->query("SELECT * FROM reviews WHERE is_approved=1 ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
require_once 'partials/admin_header.php';
?>
<div class="admin-content">
  <div class="content-header"><h1>Manage Reviews</h1></div>
  <?php if ($flash): ?><div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div><?php endif; ?>

  <?php if (!empty($pending)): ?>
  <div class="admin-card" style="border-color:rgba(234,179,8,.25);">
    <div class="card-head"><h3>⏳ Pending Approval (<?= count($pending) ?>)</h3></div>
    <?php foreach ($pending as $r): ?>
    <div style="border:1px solid var(--border-dim);border-radius:var(--radius);padding:20px;margin-bottom:12px;">
      <div style="display:flex;justify-content:space-between;align-items:start;flex-wrap:wrap;gap:12px;">
        <div>
          <div style="font-weight:600;margin-bottom:4px;"><?= htmlspecialchars($r['reviewer_name']) ?> <span style="color:var(--gold);"><?= str_repeat('★',$r['rating']) ?></span></div>
          <div style="color:var(--gray);font-size:.75rem;"><?= date('M j, Y', strtotime($r['created_at'])) ?></div>
          <p style="color:var(--white);font-size:.85rem;margin-top:10px;font-style:italic;">"<?= htmlspecialchars($r['review_text']) ?>"</p>
        </div>
        <div style="display:flex;gap:8px;">
          <form method="POST" style="display:inline">
            <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
            <input type="hidden" name="action" value="approve">
            <button class="btn-sm btn-success-sm">✓ Approve</button>
          </form>
          <form method="POST" style="display:inline">
            <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
            <input type="hidden" name="action" value="delete">
            <button class="btn-sm btn-danger-sm" data-confirm="Delete this review?">Delete</button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="admin-card">
    <div class="card-head"><h3>✓ Published Reviews (<?= count($approved) ?>)</h3></div>
    <table class="data-table">
      <thead><tr><th>Reviewer</th><th>Rating</th><th>Review</th><th>Date</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($approved as $r): ?>
        <tr>
          <td style="font-weight:500;"><?= htmlspecialchars($r['reviewer_name']) ?></td>
          <td style="color:var(--gold);"><?= str_repeat('★',$r['rating']) ?></td>
          <td style="max-width:300px;color:var(--gray);font-size:.8rem;"><?= htmlspecialchars(substr($r['review_text'],0,100)) ?>...</td>
          <td><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
          <td>
            <form method="POST" style="display:inline">
              <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
              <input type="hidden" name="action" value="delete">
              <button class="btn-sm btn-danger-sm" data-confirm="Delete this review?">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($approved)): ?><tr><td colspan="5" style="text-align:center;color:var(--gray);padding:30px;">No approved reviews yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once 'partials/admin_footer.php'; ?>
