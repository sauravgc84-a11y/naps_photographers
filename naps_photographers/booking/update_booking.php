<?php
// ============================================================
// booking/update_booking.php — Cancel booking (customer)
// ============================================================
session_start();
require_once '../db.php';
requireLogin();

$booking_id = (int)($_GET['id'] ?? 0);
$action     = clean($_GET['action'] ?? '');
$user_id    = (int)$_SESSION['user_id'];

if (!$booking_id || !in_array($action, ['cancel'])) {
    setFlash('error', 'Invalid request.');
    redirect('../customer/customer_dashboard.php');
}

// Verify ownership
$stmt = $conn->prepare("SELECT id, status FROM bookings WHERE id=? AND user_id=?");
$stmt->bind_param('ii', $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    setFlash('error', 'Booking not found.');
    redirect('../customer/customer_dashboard.php');
}

if (in_array($booking['status'], ['completed','cancelled'])) {
    setFlash('error', 'This booking cannot be cancelled.');
    redirect('../customer/customer_dashboard.php');
}

$stmt = $conn->prepare("UPDATE bookings SET status='cancelled', updated_at=NOW() WHERE id=?");
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$stmt->close();

sendNotification($conn, $user_id, 'Booking Cancelled',
    'Your booking #' . $booking_id . ' has been cancelled.', 'booking');

setFlash('success', 'Booking cancelled successfully.');
redirect('../customer/customer_dashboard.php');
