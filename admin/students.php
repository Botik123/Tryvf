<?php
require_once 'header.php';

$course_filter = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

$query = "SELECT o.*, u.email, u.name as student_name, c.name as course_name 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          JOIN courses c ON o.course_id = c.id";

if ($course_filter) {
    $query .= " WHERE o.course_id = $course_filter";
}
$query .= " ORDER BY o.created_at DESC";

$stmt = $pdo->query($query);
$orders = $stmt->fetchAll();

$courses = $pdo->query("SELECT id, name FROM courses")->fetchAll();
?>

<h1>Студенты и записи</h1>

<form method="GET" style="margin-bottom: 20px;">
    <label>Фильтр по курсу:</label>
    <select name="course_id" onchange="this.form.submit()">
        <option value="0">Все курсы</option>
        <?php foreach ($courses as $c): ?>
            <option value="<?php echo $c['id']; ?>" <?php echo $course_filter === (int)$c['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($c['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<table>
    <thead>
        <tr>
            <th>Email</th>
            <th>Имя</th>
            <th>Курс</th>
            <th>Дата записи</th>
            <th>Статус оплаты</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
        <tr>
            <td><?php echo htmlspecialchars($order['email']); ?></td>
            <td><?php echo htmlspecialchars($order['student_name']); ?></td>
            <td><?php echo htmlspecialchars($order['course_name']); ?></td>
            <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
            <td>
                <?php 
                $status_map = [
                    'pending' => 'Ожидает оплаты',
                    'success' => 'Оплачено',
                    'failed' => 'Ошибка оплаты'
                ];
                echo $status_map[$order['status']];
                ?>
            </td>
            <td>
                <?php if ($order['status'] === 'success'): ?>
                    <a href="certificate.php?id=<?php echo $order['id']; ?>" class="btn btn-primary" target="_blank">Сертификат</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>
