<?php
session_start();
require_once '../db.php';
requireLogin(); requireAdmin();
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = clean($_POST['action'] ?? '');
    if ($action === 'add' || $action === 'edit') {
        $name     = clean($_POST['name'] ?? '');
        $desc     = clean($_POST['description'] ?? '');
        $short    = clean($_POST['short_desc'] ?? '');
        $price    = (float)($_POST['price'] ?? 0);
        $duration = clean($_POST['duration'] ?? '');
        $img      = clean($_POST['image_url'] ?? '');
        $active   = isset($_POST['is_active']) ? 1 : 0;

        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO services (name,description,short_desc,price,duration,image_url,is_active) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param('sssdssi', $name,$desc,$short,$price,$duration,$img,$active);
            $stmt->execute(); $stmt->close();
            setFlash('success', 'Service added successfully.');
        } else {
            $sid = (int)($_POST['service_id'] ?? 0);
            $stmt = $conn->prepare("UPDATE services SET name=?,description=?,short_desc=?,price=?,duration=?,image_url=?,is_active=? WHERE id=?");
            $stmt->bind_param('sssdssii', $name,$desc,$short,$price,$duration,$img,$active,$sid);
            $stmt->execute(); $stmt->close();
            setFlash('success', 'Service updated.');
        }
    } elseif ($action === 'delete') {
        $sid = (int)($_POST['service_id'] ?? 0);
        $conn->query("UPDATE services SET is_active=0 WHERE id={$sid}");
        setFlash('info', 'Service deactivated.');
    }
    redirect('manage_services.php');
}

$services = $conn->query("SELECT * FROM services ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
$edit = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    foreach ($services as $s) { if ($s['id']==$eid) { $edit=$s; break; } }
}

require_once 'partials/admin_header.php';
?>
<div class="admin-content">
  <div class="content-header"><h1><?= $edit ? 'Edit Service' : 'Manage Services' ?></h1></div>
  <?php if ($flash): ?><div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div><?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:28px;align-items:start;">
    <!-- FORM -->
    <div class="admin-card">
      <h3 style="margin-bottom:20px;"><?= $edit ? 'Edit: '.htmlspecialchars($edit['name']) : 'Add New Service' ?></h3>
      <form method="POST">
        <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>">
        <?php if ($edit): ?><input type="hidden" name="service_id" value="<?= $edit['id'] ?>"><?php endif; ?>
        <div class="form-group"><label>Service Name</label><input type="text" name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Short Description</label><input type="text" name="short_desc" value="<?= htmlspecialchars($edit['short_desc'] ?? '') ?>"></div>
        <div class="form-group"><label>Full Description</label><textarea name="description" rows="4"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea></div>
        <div class="form-row-2">
          <div class="form-group"><label>Price ($)</label><input type="number" name="price" step="0.01" min="0" value="<?= $edit['price'] ?? '' ?>" required></div>
          <div class="form-group"><label>Duration</label><input type="text" name="duration" placeholder="e.g. 2 hours" value="<?= htmlspecialchars($edit['duration'] ?? '') ?>"></div>
        </div>
        <div class="form-group"><label>Image URL</label><input type="url" name="image_url" placeholder="https://..." value="<?= htmlspecialchars($edit['image_url'] ?? '') ?>"></div>
        <div class="form-group" style="display:flex;align-items:center;gap:10px;">
          <input type="checkbox" name="is_active" id="is_active" <?= (!$edit || $edit['is_active']) ? 'checked' : '' ?> style="width:auto;">
          <label for="is_active" style="text-transform:none;letter-spacing:0;font-size:.85rem;color:var(--white);">Active (visible on website)</label>
        </div>
        <div style="display:flex;gap:10px;">
          <button type="submit" class="btn-primary"><?= $edit ? 'Update Service' : 'Add Service' ?></button>
          <?php if ($edit): ?><a href="manage_services.php" class="btn-secondary">Cancel</a><?php endif; ?>
        </div>
      </form>
    </div>

    <!-- SERVICES LIST -->
    <div class="admin-card">
      <table class="data-table">
        <thead><tr><th>Service</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($services as $s): ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <?php if ($s['image_url']): ?><img src="<?= htmlspecialchars($s['image_url']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:2px;"><?php endif; ?>
                <div>
                  <div style="font-weight:500;"><?= htmlspecialchars($s['name']) ?></div>
                  <div style="font-size:.72rem;color:var(--gray);"><?= htmlspecialchars($s['duration']) ?></div>
                </div>
              </div>
            </td>
            <td style="color:var(--gold);">$<?= number_format($s['price'],2) ?></td>
            <td><?= $s['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>' ?></td>
            <td>
              <a href="?edit=<?= $s['id'] ?>" class="btn-sm btn-outline-sm">Edit</a>
              <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                <button class="btn-sm btn-danger-sm" data-confirm="Deactivate this service?">Deactivate</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once 'partials/admin_footer.php'; ?>
