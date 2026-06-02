<?php
// ============================================================
// booking/submit_review.php — Submit customer review
// ============================================================
session_start();
require_once '../db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../index.php');

$user_id  = (int)$_SESSION['user_id'];
$name     = clean($_POST['reviewer_name'] ?? '');
$rating   = max(1, min(5, (int)($_POST['rating'] ?? 5)));
$text     = clean($_POST['review_text'] ?? '');
$email    = clean($_SESSION['email'] ?? '');

if (empty($name) || empty($text)) {
    setFlash('error', 'Please fill in all review fields.');
    redirect('../index.php#reviews');
}

$stmt = $conn->prepare("INSERT INTO reviews (user_id, reviewer_name, reviewer_email, rating, review_text) VALUES (?,?,?,?,?)");
$stmt->bind_param('issis', $user_id, $name, $email, $rating, $text);
$stmt->execute();
$stmt->close();

setFlash('success', 'Thank you for your review! It will appear after approval.');
redirect('../index.php#reviews');
