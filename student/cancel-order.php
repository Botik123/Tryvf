<?php
require_once __DIR__ . '/../db.php';
if (!isStudent()) redirect('../index.php');

$id = (int)$_GET['id'];
$user_id = $_SESSION['user']['id'];

if ($id) {
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $order = $stmt->fetch();

    if ($order && $order['status'] !== 'success') {
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);
    }
}
redirect('my-courses.php');
?>
