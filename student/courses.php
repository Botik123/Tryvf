<?php
require_once 'header.php';

$stmt = $pdo->query('SELECT * FROM courses ORDER BY start_date ASC');
$courses = $stmt->fetchAll();

// Check if student is already enrolled
$stmt = $pdo->prepare('SELECT course_id FROM orders WHERE user_id = ?');
$stmt->execute([$_SESSION['user']['id']]);
$enrolled = $stmt->fetchAll(PDO::PARAM_COLUMN);
?>

<h1>Доступные курсы</h1>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
    <?php foreach ($courses as $course): ?>
    <div class="login-box" style="text-align: left; padding: 20px;">
        <img src="../uploads/<?php echo $course['img']; ?>" alt="" style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px; margin-bottom: 15px;">
        <h3 style="color: #2e7d32; margin-bottom: 10px;"><?php echo htmlspecialchars($course['name']); ?></h3>
        <p style="font-size: 14px; color: #666; margin-bottom: 10px;"><?php echo htmlspecialchars($course['description']); ?></p>
        <div style="font-size: 13px; margin-bottom: 15px;">
            <div><strong>Часов:</strong> <?php echo $course['hours']; ?></div>
            <div><strong>Цена:</strong> <?php echo number_format($course['price'], 2); ?> руб.</div>
            <div><strong>Начало:</strong> <?php echo date('d.m.Y', strtotime($course['start_date'])); ?></div>
        </div>
        
        <?php if (in_array($course['id'], $enrolled)): ?>
            <a href="lessons.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary" style="display: block; text-align: center;">Перейти к обучению</a>
        <?php else: ?>
            <?php 
            $today = date('Y-m-d');
            if ($today < $course['start_date']): 
            ?>
                <form action="enroll.php" method="POST">
                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                    <button type="submit" class="login-btn" style="font-size: 14px; padding: 8px;">Записаться и купить</button>
                </form>
            <?php else: ?>
                <span style="color: red; font-size: 12px;">Запись окончена</span>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<?php require_once 'footer.php'; ?>
