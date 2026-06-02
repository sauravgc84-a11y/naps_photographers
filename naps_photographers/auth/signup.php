<?php
// ============================================================
// auth/signup.php - Registration Page
// Naps Photographers
// ============================================================
session_start();
require_once '../db.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? '../admin/dashboard.php' : '../customer/customer_dashboard.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = clean($_POST['full_name'] ?? '');
    $email    = clean($_POST['email'] ?? '');
    $phone    = clean($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($name))     $errors[] = 'Full name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm)  $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        // Check email uniqueness
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'An account with this email already exists.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?,?,?,?,'customer')");
        $stmt->bind_param('ssss', $name, $email, $phone, $hashed);
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            // Welcome notification
            sendNotification($conn, $user_id, 'Welcome to Naps Photographers!',
                'Thank you for joining us, ' . $name . '. We look forward to capturing your most precious moments.', 'system');
            setFlash('success', 'Account created! Please sign in.');
            redirect('login.php');
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account — Naps Photographers</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root { 
    --gold:#64cbfa; --gold-light:#9fe0ff; --black:#0A0A0A; --dark:#111111; --white:#F8F6F1; --gray:#888; }
  * { margin:0; padding:0; box-sizing:border-box; }
  body {
    font-family:'Montserrat',sans-serif;
    background:var(--black); min-height:100vh;
    display:flex; align-items:center; justify-content:center;
    position:relative; overflow:hidden; padding:20px 0;
  }
  body::before {
    content:''; position:absolute; inset:0;
    background:url('https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=1200') center/cover;
    opacity:.1; filter:blur(4px);
  }
  .auth-container { position:relative; z-index:2; width:100%; max-width:480px; padding:20px; }
  .auth-logo { text-align:center; margin-bottom:40px; }
  .auth-logo a { text-decoration:none; }
  .auth-logo .brand { font-family:'Cormorant Garamond',serif; font-size:2rem; font-weight:600; color:#F8F6F1; letter-spacing:2px; }
  .auth-logo .sub { font-size:.6rem; color:#64cbfa; letter-spacing:5px; text-transform:uppercase; display:block; margin-top:4px; }
  .auth-card { background:rgba(17,17,17,0.95); border:1px solid rgba(201,168,76,0.2); border-radius:4px; padding:48px 40px; backdrop-filter:blur(20px); }
  .auth-card h2 { font-family:'Cormorant Garamond',serif; font-size:1.8rem; color:#F8F6F1; margin-bottom:8px; font-weight:500; }
  .auth-card p { color:#888; font-size:.8rem; margin-bottom:32px; }
  .error-list { background:rgba(220,53,69,0.12); border:1px solid rgba(220,53,69,0.3); color:#ff6b7a; border-radius:3px; padding:12px 16px; margin-bottom:24px; font-size:.8rem; }
  .error-list li { margin-left:16px; margin-bottom:4px; }
  .form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
  .form-group { margin-bottom:18px; }
  label { display:block; color:#888; font-size:.7rem; letter-spacing:2px; text-transform:uppercase; margin-bottom:8px; }
  input { width:100%; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); border-radius:3px; padding:13px 16px; color:#F8F6F1; font-family:'Montserrat',sans-serif; font-size:.87rem; transition:border-color .3s; outline:none; }
  input:focus { border-color:#64cbfa; }
  .btn-primary { width:100%; padding:14px; background:#64cbfa; color:#0A0A0A; border:none; border-radius:3px; cursor:pointer; font-family:'Montserrat',sans-serif; font-size:.78rem; font-weight:600; letter-spacing:3px; text-transform:uppercase; transition:all .3s; margin-top:8px; }
  .btn-primary:hover { background:#E8C97E; transform:translateY(-1px); }
  .auth-links { text-align:center; margin-top:24px; color:#888; font-size:.78rem; }
  .auth-links a { color:#64cbfa; text-decoration:none; }
  .auth-links a:hover { color:#9fe0ff; }
  @media(max-width:520px) { .form-row { grid-template-columns:1fr; } .auth-card { padding:36px 24px; } }
</style>
</head>
<body>
<div class="auth-container">
  <div class="auth-logo">
    <a href="../index.php">
      <div class="brand">NAPS</div>
      <span class="sub">Photographers</span>
    </a>
  </div>
  <div class="auth-card">
    <h2>Create Account</h2>
    <p>Join us to start booking your sessions</p>
    <?php if (!empty($errors)): ?>
      <div class="error-list"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="POST" id="signupForm">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="full_name" placeholder="Your full name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label>Phone</label>
          <input type="text" name="phone" placeholder="+1 555 0100" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Min 8 characters" required>
        </div>
        <div class="form-group">
          <label>Confirm Password</label>
          <input type="password" name="confirm_password" placeholder="Repeat password" required>
        </div>
      </div>
      <button type="submit" class="btn-primary">Create Account</button>
    </form>
    <div class="auth-links">
      Already have an account? <a href="login.php">Sign in</a>
      &nbsp;·&nbsp; <a href="../index.php">Back to site</a>
    </div>
  </div>
</div>
</body>
</html>
