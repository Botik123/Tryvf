<?php
require_once 'header.php';

$stmt = $pdo->query('SELECT * FROM courses ORDER BY start_date ASC');
$courses = $stmt->fetchAll();

// Check if student is already enrolled
$stmt = $pdo->prepare('SELECT course_id FROM orders WHERE user_id = ?');
$stmt->execute([$_SESSION['user']['id']]);
$enrolled = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<h1>Доступные курсы</h1>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; margin-top: 20px;">
    <?php foreach ($courses as $course): ?>
    <div class="login-box course-card" style="text-align: left; padding: 0; overflow: hidden;">
        <img src="../uploads/<?php echo $course['img']; ?>" alt="" style="width: 100%; height: 180px; object-fit: cover;">
        <div style="padding: 20px; flex-grow: 1; display: flex; flex-direction: column;">
            <h3 style="color: #2e7d32; margin-bottom: 10px; font-size: 20px;"><?php echo htmlspecialchars($course['name']); ?></h3>
            <p style="font-size: 14px; color: #666; margin-bottom: 15px; flex-grow: 1;"><?php echo htmlspecialchars($course['description']); ?></p>
            
            <div style="font-size: 13px; color: #555; margin-bottom: 15px;">
                <div style="margin-bottom: 5px;">📅 <strong>Старт:</strong> <?php echo date('d.m.Y', strtotime($course['start_date'])); ?></div>
                <div>⏱️ <strong>Длительность:</strong> <?php echo $course['hours']; ?> ч.</div>
            </div>

            <div class="course-meta">
                <div class="price-tag"><?php echo number_format($course['price'], 0, '.', ' '); ?> ₽</div>
                
                <?php if (in_array($course['id'], $enrolled)): ?>
                    <a href="lessons.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary">Обучение</a>
                <?php else: ?>
                    <?php 
                    $today = date('Y-m-d');
                    if ($today < $course['start_date']): 
                    ?>
                        <form action="enroll.php" method="POST" style="margin: 0;">
                            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                            <button type="submit" class="btn btn-primary">Записаться</button>
                        </form>
                    <?php else: ?>
                        <span style="color: #f44336; font-size: 12px; font-weight: bold;">ЗАПИСЬ ЗАКРЫТА</span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require_once 'footer.php'; ?>
