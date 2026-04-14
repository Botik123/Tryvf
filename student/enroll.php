<?php
require_once __DIR__ . '/../db.php';
if (!isStudent()) redirect('../index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = (int)$_POST['course_id'];
    $user_id = $_SESSION['user']['id'];

    // Check if already enrolled
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, course_id, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$user_id, $course_id]);
        $order_id = $pdo->lastInsertId();
        redirect("../payment-webhook.php?order_id=$order_id");
    }
}
redirect('courses.php');
?>
