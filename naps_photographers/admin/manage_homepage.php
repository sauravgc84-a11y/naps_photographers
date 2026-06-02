<?php
session_start();
require_once '../db.php';
requireLogin(); requireAdmin();
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key     = clean($_POST['section_key'] ?? '');
    $title   = clean($_POST['section_title'] ?? '');
    $content = clean($_POST['section_content'] ?? '');
    $image   = clean($_POST['section_image'] ?? '');

    if ($key) {
        $stmt = $conn->prepare("INSERT INTO homepage_content (section_key,section_title,section_content,section_image) VALUES (?,?,?,?)
            ON DUPLICATE KEY UPDATE section_title=VALUES(section_title), section_content=VALUES(section_content), section_image=VALUES(section_image)");
        $stmt->bind_param('ssss', $key,$title,$content,$image);
        $stmt->execute(); $stmt->close();
        setFlash('success', 'Homepage content updated successfully.');
    }
    redirect('manage_homepage.php');
}

$content = [];
$rows = $conn->query("SELECT * FROM homepage_content")->fetch_all(MYSQLI_ASSOC);
foreach ($rows as $r) $content[$r['section_key']] = $r;

require_once 'partials/admin_header.php';
?>
<div class="admin-content">
  <div class="content-header"><h1>Manage Homepage Content</h1></div>
  <?php if ($flash): ?><div class="flash flash-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div><?php endif; ?>

  <?php
  $sections = [
    ['key'=>'hero_tagline',    'label'=>'Hero Section',     'has_image'=>false],
    ['key'=>'about_title',     'label'=>'About Section',    'has_image'=>true],
    ['key'=>'contact_address', 'label'=>'Contact Address',  'has_image'=>false],
    ['key'=>'contact_phone',   'label'=>'Contact Phone',    'has_image'=>false],
    ['key'=>'contact_email',   'label'=>'Contact Email',    'has_image'=>false],
  ];
  foreach ($sections as $sec):
    $data = $content[$sec['key']] ?? [];
  ?>
  <div class="admin-card" style="margin-bottom:20px;">
    <div class="card-head"><h3><?= $sec['label'] ?></h3></div>
    <form method="POST">
      <input type="hidden" name="section_key" value="<?= $sec['key'] ?>">
      <div class="form-row-2">
        <div class="form-group"><label>Title / Heading</label><input type="text" name="section_title" value="<?= htmlspecialchars($data['section_title'] ?? '') ?>" placeholder="Section title"></div>
        <?php if ($sec['has_image']): ?>
        <div class="form-group"><label>Image URL</label><input type="url" name="section_image" value="<?= htmlspecialchars($data['section_image'] ?? '') ?>" placeholder="https://..."></div>
        <?php else: ?>
        <input type="hidden" name="section_image" value="">
        <?php endif; ?>
      </div>
      <div class="form-group"><label>Content / Text</label><textarea name="section_content" rows="4"><?= htmlspecialchars($data['section_content'] ?? '') ?></textarea></div>
      <button type="submit" class="btn-primary">Save Changes</button>
    </form>
  </div>
  <?php endforeach; ?>
</div>
<?php require_once 'partials/admin_footer.php'; ?>
