<?php
require_once 'header.php';

$course_id = (int)$_GET['course_id'];

// Verify enrollment
$stmt = $pdo->prepare("SELECT status FROM orders WHERE user_id = ? AND course_id = ?");
$stmt->execute([$_SESSION['user']['id'], $course_id]);
$order = $stmt->fetch();

if (!$order || $order['status'] !== 'success') {
    die('У вас нет доступа к этому курсу или он не оплачен.');
}

$stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ?");
$stmt->execute([$course_id]);
$lessons = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT name FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course_name = $stmt->fetchColumn();
?>

<h1>Уроки курса: <?php echo htmlspecialchars($course_name); ?></h1>

<div style="margin-top: 20px;">
    <?php foreach ($lessons as $lesson): ?>
    <div class="lesson-box">
        <h2 style="color: #2e7d32; margin-bottom: 15px;"><?php echo htmlspecialchars($lesson['title']); ?></h2>
        <div style="margin-bottom: 20px; line-height: 1.6;">
            <?php echo nl2br(htmlspecialchars($lesson['content'])); ?>
        </div>
        
        <?php if ($lesson['video_link']): ?>
            <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; border-left: 4px solid #ffd54f;">
                <strong>Видео:</strong> <a href="<?php echo htmlspecialchars($lesson['video_link']); ?>" target="_blank"><?php echo htmlspecialchars($lesson['video_link']); ?></a>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 15px; font-size: 13px; color: #666;">
            Длительность: <?php echo $lesson['hours']; ?> ч.
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php if (empty($lessons)): ?>
        <p>В этом курсе пока нет уроков.</p>
    <?php endif; ?>
</div>

<a href="my-courses.php" class="btn btn-warning">Назад к моим курсам</a>

<?php require_once 'footer.php'; ?>
