<?php
// ============================================================
// auth/login.php - Login Page
// Naps Photographers
// ============================================================
session_start();
require_once '../db.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? '../admin/dashboard.php' : '../customer/customer_dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, email, password, role, is_blocked FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_blocked']) {
                $error = 'Your account has been suspended. Please contact support.';
            } else {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email']     = $user['email'];
                $_SESSION['role']      = $user['role'];

                $redirect = $_GET['redirect'] ?? '';
                if ($user['role'] === 'admin') {
                    redirect('../admin/dashboard.php');
                } else {
                    redirect($redirect ?: '../customer/customer_dashboard.php');
                }
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Naps Photographers</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --gold: #4c9cc9;
    --gold-light: #4c9cc9;
    --black: #0A0A0A;
    --dark: #111111;
    --dark2: #1A1A1A;
    --white: #F8F6F1;
    --gray: #888;
  }
  * { margin:0; padding:0; box-sizing:border-box; }
  body {
    font-family: 'Montserrat', sans-serif;
    background: var(--black);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
  }
  body::before {
    content:'';
    position:absolute; inset:0;
    background: url('https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=1200') center/cover;
    opacity:.12;
    filter: blur(4px);
  }
  .auth-container {
    position: relative; z-index:2;
    width: 100%; max-width: 460px;
    padding: 20px;
  }
  .auth-logo {
    text-align:center; margin-bottom:40px;
  }
  .auth-logo a { text-decoration:none; }
  .auth-logo .brand {
    font-family:'Cormorant Garamond',serif;
    font-size:2rem; font-weight:600;
    color: var(--white); letter-spacing:2px;
  }
  .auth-logo .sub {
    font-size:.6rem; color:var(--gold);
    letter-spacing:5px; text-transform:uppercase;
    display:block; margin-top:4px;
  }
  .auth-card {
    background: rgba(17,17,17,0.95);
    border:1px solid rgba(201,168,76,0.2);
    border-radius:4px;
    padding:48px 40px;
    backdrop-filter:blur(20px);
  }
  .auth-card h2 {
    font-family:'Cormorant Garamond',serif;
    font-size:1.8rem; color:var(--white);
    margin-bottom:8px; font-weight:500;
  }
  .auth-card p {
    color:var(--gray); font-size:.8rem; margin-bottom:32px;
  }
  .error-box {
    background: rgba(220,53,69,0.15);
    border:1px solid rgba(220,53,69,0.4);
    color:#ff6b7a; border-radius:3px;
    padding:12px 16px; margin-bottom:24px;
    font-size:.82rem;
  }
  .form-group { margin-bottom:20px; }
  label {
    display:block; color:var(--gray);
    font-size:.72rem; letter-spacing:2px;
    text-transform:uppercase; margin-bottom:8px;
  }
  input[type=email], input[type=password], input[type=text] {
    width:100%; background:rgba(255,255,255,0.04);
    border:1px solid rgba(255,255,255,0.1);
    border-radius:3px; padding:13px 16px;
    color:var(--white); font-family:'Montserrat',sans-serif;
    font-size:.88rem; transition:border-color .3s;
    outline:none;
  }
  input:focus { border-color:var(--gold); }
  .btn-primary {
    width:100%; padding:14px;
    background: var(--gold); color:var(--black);
    border:none; border-radius:3px; cursor:pointer;
    font-family:'Montserrat',sans-serif;
    font-size:.78rem; font-weight:600;
    letter-spacing:3px; text-transform:uppercase;
    transition:all .3s; margin-top:8px;
  }
  .btn-primary:hover { background:var(--gold-light); transform:translateY(-1px); }
  .auth-links {
    text-align:center; margin-top:28px;
    color:var(--gray); font-size:.78rem;
  }
  .auth-links a { color:var(--gold); text-decoration:none; }
  .auth-links a:hover { color:var(--gold-light); }
  .divider {
    border:none; border-top:1px solid rgba(255,255,255,0.07);
    margin:24px 0;
  }
  .demo-creds {
    background:rgba(201,168,76,0.06);
    border:1px solid rgba(201,168,76,0.15);
    border-radius:3px; padding:14px;
    font-size:.75rem; color:var(--gray);
  }
  .demo-creds strong { color:var(--gold); display:block; margin-bottom:6px; }
  .demo-creds span { color:rgba(255,255,255,0.6); }
  @media(max-width:480px) { .auth-card { padding:36px 24px; } }
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
    <h2>Welcome Back</h2>
    <p>Sign in to your account to manage bookings</p>
    <?php if ($error): ?>
      <div class="error-box"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" id="loginForm">
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="your@email.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-primary">Sign In</button>
    </form>
    <hr class="divider">
    <div class="auth-links" style="margin-top:20px;">
      Don't have an account? <a href="signup.php">Create one</a>
      &nbsp;·&nbsp; <a href="../index.php">Back to site</a>
    </div>
  </div>
</div>
</body>
</html>
