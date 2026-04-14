<?php
require_once __DIR__ . '/../db.php';
if (!isAdmin()) redirect('../index.php');

$id = (int)$_GET['id'];
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$id]);
}
redirect('courses.php');
?>
