<?php
require_once __DIR__ . '/../db.php';
if (!isAdmin()) {
    redirect('../index.php');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель — Платформа онлайн-обучения</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <nav class="admin-nav">
        <div class="nav-logo">EduAdmin</div>
        <ul class="nav-links">
            <li><a href="courses.php">Курсы</a></li>
            <li><a href="students.php">Студенты и записи</a></li>
            <li><a href="../logout.php">Выход</a></li>
        </ul>
    </nav>
    <div class="admin-container">
