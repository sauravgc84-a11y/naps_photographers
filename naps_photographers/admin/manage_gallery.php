<?php
session_start();
require_once '../db.php';
requireLogin(); requireAdmin();
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = clean($_POST['action'] ?? '');
    if ($action === 'add') {
        $title   = clean($_POST['title'] ?? '');
        $caption = clean($_POST['caption'] ?? '');
        $img     = clean($_POST['image_url'] ?? '');
        $cat     = clean($_POST['category'] ?? 'general');
        if ($img) {
            $stmt = $conn->prepare("INSERT INTO gallery (title,caption,image_url,category) VALUES (?,?,?,?)");
            $stmt->bind_param('ssss', $title,$caption,$img,$cat);
            $stmt->execute(); $stmt->close();
            setFlash('success', 'Image added to gallery.');
        }
    } elseif ($action === 'delete') {
        $gid = (int)($_POST['gallery_id'] ?? 0);
        $conn->query("DELETE FROM gallery WHERE id={$gid}");
        setFlash('info', 'Image removed from gallery.');
    } elseif ($action === 'edit') {
        $gid     = (int)($_POST['gallery_id'] ?? 0);
        $title   = clean($_POST['title'] ?? '');
        $caption = clean($_POST['caption'] ?? '');
        $cat     = clean($_POST['category'] ?? 'general');
        $stmt = $conn->prepare("UPDATE gallery SET title=?,caption=?,category=? WHERE id=?");
        $stmt->bind_param('sssi', $title,$caption,$cat,$gid);
        $stmt->execute(); $stmt->close();
        setFlash('success', 'Gallery item updated.');
    }
    redirect('manage_gallery.php');
}

$gallery = $conn->query("SELECT * FROM gallery ORDER BY sort_order, created_at DESC")->fetch_all(MYSQLI_ASSOC);
require_once 'partials/admin_header.php';
?>
<div class="admin-content">
  <div class="content-header"><h1>Manage Gallery</h1></div>
  <?php if ($flash): ?><div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div><?php endif; ?>

  <div style="display:grid;grid-template-columns:340px 1fr;gap:24px;align-items:start;">
    <div class="admin-card">
      <h3 style="margin-bottom:20px;">Add New Image</h3>
      <form method="POST">
        <input type="hidden" name="action" value="add">
        <div class="form-group"><label>Title</label><input type="text" name="title" placeholder="Image title"></div>
        <div class="form-group"><label>Image URL</label><input type="url" name="image_url" placeholder="https://..." required></div>
        <div class="form-group"><label>Category</label>
          <select name="category">
            <option value="general">General</option>
            <option value="wedding">Wedding</option>
            <option value="portrait">Portrait</option>
            <option value="corporate">Corporate</option>
            <option value="family">Family</option>
            <option value="commercial">Commercial</option>
            <option value="aerial">Aerial</option>
          </select>
        </div>
        <div class="form-group"><label>Caption</label><textarea name="caption" rows="3" placeholder="Optional caption"></textarea></div>
        <button type="submit" class="btn-primary">Add to Gallery</button>
      </form>
    </div>

    <div class="admin-card">
      <h3 style="margin-bottom:20px;">Gallery Images (<?= count($gallery) ?>)</h3>
      <div class="gallery-admin-grid">
        <?php foreach ($gallery as $img): ?>
        <div class="gallery-admin-item">
          <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="<?= htmlspecialchars($img['title']) ?>">
          <div class="item-actions">
            <button class="btn-sm btn-outline-sm" onclick="editGallery(<?= $img['id'] ?>,'<?= htmlspecialchars(addslashes($img['title'])) ?>','<?= htmlspecialchars(addslashes($img['caption'])) ?>','<?= $img['category'] ?>')">Edit</button>
            <form method="POST" style="display:inline">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="gallery_id" value="<?= $img['id'] ?>">
              <button class="btn-sm btn-danger-sm" data-confirm="Remove this image?">Delete</button>
            </form>
          </div>
          <div class="item-caption"><?= htmlspecialchars($img['title'] ?: 'Untitled') ?> · <?= $img['category'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- EDIT MODAL -->
<div style="position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:999;display:none;align-items:center;justify-content:center;" id="editGalleryModal">
  <div style="background:var(--dark3);border:1px solid var(--border);border-radius:var(--radius);padding:36px;width:100%;max-width:420px;">
    <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;margin-bottom:24px;">Edit Gallery Item</h3>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="gallery_id" id="editGid">
      <div class="form-group"><label>Title</label><input type="text" name="title" id="editTitle"></div>
      <div class="form-group"><label>Category</label>
        <select name="category" id="editCat">
          <option value="general">General</option><option value="wedding">Wedding</option><option value="portrait">Portrait</option>
          <option value="corporate">Corporate</option><option value="family">Family</option><option value="commercial">Commercial</option><option value="aerial">Aerial</option>
        </select>
      </div>
      <div class="form-group"><label>Caption</label><textarea name="caption" id="editCaption" rows="3"></textarea></div>
      <div style="display:flex;gap:10px;">
        <button type="submit" class="btn-primary">Save</button>
        <button type="button" class="btn-secondary" onclick="document.getElementById('editGalleryModal').style.display='none'">Cancel</button>
      </div>
    </form>
  </div>
</div>
<script>
function editGallery(id, title, caption, cat) {
  document.getElementById('editGid').value = id;
  document.getElementById('editTitle').value = title;
  document.getElementById('editCaption').value = caption;
  document.getElementById('editCat').value = cat;
  document.getElementById('editGalleryModal').style.display = 'flex';
}
</script>
<?php require_once 'partials/admin_footer.php'; ?>
