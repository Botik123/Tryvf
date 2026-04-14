<?php
require_once 'header.php';

$course_id = (int)$_GET['course_id'];
$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = ?');
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) redirect('courses.php');

$stmt = $pdo->prepare('SELECT * FROM lessons WHERE course_id = ?');
$stmt->execute([$course_id]);
$lessons = $stmt->fetchAll();

$canDelete = true;
$stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE course_id = ?');
$stmt->execute([$course_id]);
if ($stmt->fetchColumn() > 0) {
    $canDelete = false;
}
?>

<h1>Уроки курса: <?php echo htmlspecialchars($course['name']); ?></h1>
<div style="margin-bottom: 20px;">
    <?php if (count($lessons) < 5): ?>
        <a href="lesson-edit.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary">Добавить урок</a>
    <?php else: ?>
        <span style="color: red;">Максимум 5 уроков на курс</span>
    <?php endif; ?>
    <a href="courses.php" class="btn btn-warning">Назад к курсам</a>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Заголовок</th>
            <th>Длительность</th>
            <th>Видео</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lessons as $lesson): ?>
        <tr>
            <td><?php echo $lesson['id']; ?></td>
            <td><?php echo htmlspecialchars($lesson['title']); ?></td>
            <td><?php echo $lesson['hours']; ?> ч.</td>
            <td><?php echo $lesson['video_link'] ? 'Есть' : 'Нет'; ?></td>
            <td>
                <a href="lesson-edit.php?id=<?php echo $lesson['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn btn-primary">Ред.</a>
                <?php if ($canDelete): ?>
                    <a href="lesson-delete.php?id=<?php echo $lesson['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn btn-danger" onclick="return confirm('Удалить урок?')">Удалить</a>
                <?php else: ?>
                    <span title="Нельзя удалить, есть записи студентов" style="color: grey; cursor: not-allowed;">Удалить</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>
