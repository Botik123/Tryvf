<?php
require_once 'header.php';

$stmt = $pdo->prepare("SELECT o.*, c.name, c.img, c.start_date, c.end_date 
                       FROM orders o 
                       JOIN courses c ON o.course_id = c.id 
                       WHERE o.user_id = ? 
                       ORDER BY o.created_at DESC");
$stmt->execute([$_SESSION['user']['id']]);
$myCourses = $stmt->fetchAll();
?>

<h1>Мои курсы</h1>

<div class="table-responsive">
<table>
    <thead>
        <tr>
            <th>Обложка</th>
            <th>Название</th>
            <th>Статус оплаты</th>
            <th>Период</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($myCourses as $order): ?>
        <tr>
            <td><img src="../uploads/<?php echo $order['img']; ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"></td>
            <td><?php echo htmlspecialchars($order['name']); ?></td>
            <td>
                <?php 
                $status_map = [
                    'pending' => '<span class="badge badge-pending">Ожидает оплаты</span>',
                    'success' => '<span class="badge badge-success">Оплачено</span>',
                    'failed' => '<span class="badge badge-failed">Ошибка оплаты</span>'
                ];
                echo $status_map[$order['status']];
                ?>
            </td>
            <td><?php echo date('d.m.Y', strtotime($order['start_date'])); ?> - <?php echo date('d.m.Y', strtotime($order['end_date'])); ?></td>
            <td>
                <?php if ($order['status'] === 'success'): ?>
                    <a href="lessons.php?course_id=<?php echo $order['course_id']; ?>" class="btn btn-primary">Уроки</a>
                <?php elseif ($order['status'] === 'pending' || $order['status'] === 'failed'): ?>
                    <a href="../payment-webhook.php?order_id=<?php echo $order['id']; ?>" class="btn btn-warning">Оплатить</a>
                    <a href="cancel-order.php?id=<?php echo $order['id']; ?>" class="btn btn-danger" onclick="return confirm('Отменить запись?')">Отмена</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php require_once 'footer.php'; ?>
