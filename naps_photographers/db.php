<?php
// ============================================================
// db.php — Database Connection & Helpers
// Naps Photographers
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change if your MySQL user differs
define('DB_PASS', '');           // Change if you have a MySQL password
define('DB_NAME', 'naps_photographers');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;background:#111;color:#ff6b6b;">
        <h2>Database Connection Failed</h2>
        <p>' . htmlspecialchars($conn->connect_error) . '</p>
        <p>Please ensure MySQL is running and the database <strong>naps_photographers</strong> has been imported.</p>
    </div>');
}

$conn->set_charset('utf8mb4');

// ─── HELPERS ────────────────────────────────────────────────

function clean($data) {
    return htmlspecialchars(strip_tags(trim($data ?? '')));
}

function generateBookingRef() {
    return 'NAPS-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function generateTransactionRef() {
    return 'TXN-' . strtoupper(substr(uniqid(), -6)) . '-' . rand(1000, 9999);
}

function formatPrice($amount) {
    return '$' . number_format($amount, 2);
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function formatTime($time) {
    return date('g:i A', strtotime($time));
}

function statusBadge($status) {
    $map = [
        'pending'     => ['badge-warning',   'Pending'],
        'approved'    => ['badge-success',   'Approved'],
        'rejected'    => ['badge-danger',    'Rejected'],
        'rescheduled' => ['badge-info',      'Rescheduled'],
        'completed'   => ['badge-primary',   'Completed'],
        'cancelled'   => ['badge-secondary', 'Cancelled'],
    ];
    $cls = $map[$status][0] ?? 'badge-secondary';
    $lbl = $map[$status][1] ?? ucfirst($status);
    return "<span class=\"badge {$cls}\">{$lbl}</span>";
}

function paymentBadge($status) {
    $map = [
        'unpaid'   => ['badge-warning',   'Unpaid'],
        'paid'     => ['badge-success',   'Paid'],
        'failed'   => ['badge-danger',    'Failed'],
        'refunded' => ['badge-info',      'Refunded'],
    ];
    $cls = $map[$status][0] ?? 'badge-secondary';
    $lbl = $map[$status][1] ?? ucfirst($status);
    return "<span class=\"badge {$cls}\">{$lbl}</span>";
}

function redirect($url) {
    header("Location: {$url}");
    exit;
}

function setFlash($type, $message) {
    if (!isset($_SESSION)) session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        $path = $_SERVER['REQUEST_URI'] ?? '';
        $depth = substr_count(trim($path, '/'), '/');
        $base = str_repeat('../', $depth);
        redirect($base . 'auth/login.php');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect('../index.php');
    }
}

function sendNotification($conn, $user_id, $title, $message, $type = 'system') {
    $uid = (int)$user_id;
    $t   = $conn->real_escape_string($title);
    $m   = $conn->real_escape_string($message);
    $tp  = $conn->real_escape_string($type);
    $conn->query("INSERT INTO notifications (user_id, title, message, type) VALUES ({$uid}, '{$t}', '{$m}', '{$tp}')");
}

function getUnreadCount($conn, $user_id) {
    $uid = (int)$user_id;
    $res = $conn->query("SELECT COUNT(*) FROM notifications WHERE user_id={$uid} AND is_read=0");
    return $res ? (int)$res->fetch_row()[0] : 0;
}
