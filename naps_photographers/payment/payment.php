<?php
// ============================================================
// payment/payment.php — Process Simulated Card Payment
// ============================================================
session_start();
require_once '../db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../customer/customer_dashboard.php');
}

$user_id    = (int)$_SESSION['user_id'];
$booking_id = (int)($_POST['booking_id'] ?? 0);
$card_raw   = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
$card_hold  = clean($_POST['card_holder'] ?? '');
$expiry     = clean($_POST['expiry'] ?? '');
$cvv        = clean($_POST['cvv'] ?? '');

if (!$booking_id || strlen($card_raw) < 13 || empty($card_hold) || !preg_match('/^\d{2}\/\d{2}$/', $expiry) || strlen($cvv) < 3) {
    setFlash('error', 'Invalid payment details. Please check your card information.');
    redirect('../customer/customer_dashboard.php');
}

// Expiry check
list($em, $ey) = explode('/', $expiry);
$exp_year = 2000 + (int)$ey;
if ($exp_year < (int)date('Y') || ($exp_year == (int)date('Y') && (int)$em < (int)date('m'))) {
    setFlash('error', 'Card expired. Please use a valid card.');
    redirect('../customer/customer_dashboard.php');
}

// Verify booking
$stmt = $conn->prepare("SELECT * FROM bookings WHERE id=? AND user_id=? AND status='approved' AND payment_status='unpaid'");
$stmt->bind_param('ii', $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    setFlash('error', 'Booking not eligible for payment (must be approved and unpaid).');
    redirect('../customer/customer_dashboard.php');
}

// Simulate payment — 90% success
$success   = (rand(1, 10) <= 9);
$txn_ref   = generateTransactionRef();
$last4     = substr($card_raw, -4);
$status    = $success ? 'success' : 'failed';
$amount    = (float)$booking['total_price'];

if ($success) {
    $paid_at = date('Y-m-d H:i:s');
    $conn->query("INSERT INTO payments (booking_id,user_id,transaction_ref,amount,card_last_four,card_holder,status,paid_at) VALUES ({$booking_id},{$user_id},'" . $conn->real_escape_string($txn_ref) . "',{$amount},'" . $conn->real_escape_string($last4) . "','" . $conn->real_escape_string($card_hold) . "','success','" . $paid_at . "')");
    $conn->query("UPDATE bookings SET payment_status='paid', updated_at=NOW() WHERE id={$booking_id}");
    sendNotification($conn, $user_id, 'Payment Successful — ' . $txn_ref,
        'Your payment of $' . number_format($amount, 2) . ' for booking ' . $booking['booking_ref'] . ' was successful. TXN: ' . $txn_ref, 'payment');
    setFlash('success', 'Payment of $' . number_format($amount, 2) . ' successful! TXN: ' . $txn_ref);
} else {
    $conn->query("INSERT INTO payments (booking_id,user_id,transaction_ref,amount,card_last_four,card_holder,status) VALUES ({$booking_id},{$user_id},'" . $conn->real_escape_string($txn_ref) . "',{$amount},'" . $conn->real_escape_string($last4) . "','" . $conn->real_escape_string($card_hold) . "','failed')");
    $conn->query("UPDATE bookings SET payment_status='failed', updated_at=NOW() WHERE id={$booking_id}");
    sendNotification($conn, $user_id, 'Payment Failed',
        'Payment for booking ' . $booking['booking_ref'] . ' could not be processed. Please try again.', 'payment');
    setFlash('error', 'Payment failed. Please check your card details and try again.');
}

redirect('../customer/customer_dashboard.php');
