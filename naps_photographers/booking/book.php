<?php
// ============================================================
// booking/book.php - Process Booking
// ============================================================
session_start();
require_once '../db.php';

if (!isLoggedIn()) {
    setFlash('error', 'Please login to book a session.');
    redirect('../auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../index.php#booking');
}

$user_id       = (int)$_SESSION['user_id'];
$name          = clean($_POST['customer_name'] ?? '');
$email         = clean($_POST['customer_email'] ?? '');
$phone         = clean($_POST['customer_phone'] ?? '');
$service_id    = (int)($_POST['service_id'] ?? 0);
$booking_date  = clean($_POST['booking_date'] ?? '');
$booking_time  = clean($_POST['booking_time'] ?? '');
$message       = clean($_POST['message'] ?? '');

// ─── Validation ───
$errors = [];
if (empty($name))         $errors[] = 'Name is required.';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
if (!$service_id)         $errors[] = 'Please select a service.';
if (empty($booking_date)) $errors[] = 'Booking date is required.';
if (empty($booking_time)) $errors[] = 'Time slot is required.';
if (strtotime($booking_date) <= strtotime(date('Y-m-d'))) $errors[] = 'Please select a future date.';

if (!empty($errors)) {
    setFlash('error', implode(' ', $errors));
    redirect('../index.php#booking');
}

// ─── Check for duplicate slot ───
$stmt = $conn->prepare("SELECT id FROM bookings WHERE booking_date=? AND booking_time=? AND status NOT IN ('rejected','cancelled')");
$stmt->bind_param('ss', $booking_date, $booking_time);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    setFlash('error', 'This time slot is already booked. Please choose another time.');
    $stmt->close();
    redirect('../index.php#booking');
}
$stmt->close();

// ─── Get service price ───
$stmt = $conn->prepare("SELECT price FROM services WHERE id=? AND is_active=1");
$stmt->bind_param('i', $service_id);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$service) {
    setFlash('error', 'Selected service not found.');
    redirect('../index.php#booking');
}

// ─── Generate unique booking ref ───
do {
    $ref = generateBookingRef();
    $check = $conn->prepare("SELECT id FROM bookings WHERE booking_ref=?");
    $check->bind_param('s', $ref);
    $check->execute();
    $check->store_result();
    $exists = $check->num_rows > 0;
    $check->close();
} while ($exists);

// ─── Insert booking ───
$price = $service['price'];
$stmt = $conn->prepare("INSERT INTO bookings (booking_ref, user_id, service_id, customer_name, customer_email, customer_phone, booking_date, booking_time, message, total_price) VALUES (?,?,?,?,?,?,?,?,?,?)");
$stmt->bind_param('siissssssd', $ref, $user_id, $service_id, $name, $email, $phone, $booking_date, $booking_time, $message, $price);

if ($stmt->execute()) {
    $booking_id = $conn->insert_id;
    $stmt->close();

    // Notify customer
    sendNotification($conn, $user_id,
        'Booking Request Submitted — ' . $ref,
        "Your booking request (" . $ref . ") has been submitted successfully. We'll confirm within 24 hours.",
        'booking'
    );

    setFlash('success', 'Booking submitted! Reference: ' . $ref . '. We\'ll confirm within 24 hours.');
    redirect('../customer/customer_dashboard.php');
} else {
    $stmt->close();
    setFlash('error', 'Booking failed. Please try again.');
    redirect('../index.php#booking');
}
