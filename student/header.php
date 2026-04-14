<?php
require_once __DIR__ . '/../db.php';
if (!isStudent()) {
    redirect('../index.php');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет — Платформа онлайн-обучения</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <nav class="admin-nav student-nav">
        <div class="nav-logo">EduStudent</div>
        <ul class="nav-links">
            <li><a href="courses.php">Все курсы</a></li>
            <li><a href="my-courses.php">Мои курсы</a></li>
            <li><a href="../logout.php">Выход</a></li>
        </ul>
    </nav>
    <div class="admin-container">
