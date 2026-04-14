<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $student_id = $data['student_id'] ?? 0;
    $course_id = $data['course_id'] ?? 0;

    // Generate 6 random characters
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $course_number = '';
    for ($i = 0; $i < 6; $i++) {
        $course_number .= $chars[rand(0, strlen($chars) - 1)];
    }

    jsonResponse(['course_number' => $course_number]);
}
?>
