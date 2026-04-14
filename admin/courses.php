<?php
require_once 'header.php';

$perPage = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $perPage;

$totalStmt = $pdo->query('SELECT COUNT(*) FROM courses');
$totalCourses = $totalStmt->fetchColumn();
$totalPages = ceil($totalCourses / $perPage);

$stmt = $pdo->prepare('SELECT * FROM courses ORDER BY created_at DESC LIMIT ? OFFSET ?');
$stmt->bindValue(1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$courses = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center;">
    <h1>Управление курсами</h1>
    <a href="course-edit.php" class="btn btn-primary">Добавить курс</a>
</div>

<div class="table-responsive">
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Обложка</th>
            <th>Название</th>
            <th>Часы</th>
            <th>Цена</th>
            <th>Период</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($courses as $course): ?>
        <tr>
            <td><?php echo $course['id']; ?></td>
            <td><img src="../uploads/<?php echo $course['img']; ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"></td>
            <td><?php echo htmlspecialchars($course['name']); ?></td>
            <td><?php echo $course['hours']; ?></td>
            <td><?php echo number_format($course['price'], 2); ?></td>
            <td><?php echo date('d.m.Y', strtotime($course['start_date'])); ?> - <?php echo date('d.m.Y', strtotime($course['end_date'])); ?></td>
            <td>
                <a href="lessons.php?course_id=<?php echo $course['id']; ?>" class="btn btn-warning">Уроки</a>
                <a href="course-edit.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">Ред.</a>
                <a href="course-delete.php?id=<?php echo $course['id']; ?>" class="btn btn-danger" onclick="return confirm('Удалить курс?')">Удалить</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?php echo $i; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
</div>

<?php require_once 'footer.php'; ?>
