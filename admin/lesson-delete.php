<?php
require_once __DIR__ . '/../db.php';
if (!isAdmin()) redirect('../index.php');

$id = (int)$_GET['id'];
$course_id = (int)$_GET['course_id'];

if ($id) {
    // Check if can delete
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE course_id = ?');
    $stmt->execute([$course_id]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = ?");
        $stmt->execute([$id]);
    }
}
redirect("lessons.php?course_id=$course_id");
?>
