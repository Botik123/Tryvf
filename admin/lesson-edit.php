<?php
require_once 'header.php';

$course_id = (int)$_GET['course_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$lesson = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM lessons WHERE id = ?');
    $stmt->execute([$id]);
    $lesson = $stmt->fetch();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $video_link = $_POST['video_link'] ?? '';
    $hours = $_POST['hours'] ?? '';

    if (empty($title) || mb_strlen($title) > 50) $errors['title'] = 'Обязательно, максимум 50 символов';
    if (empty($content)) $errors['content'] = 'Обязательно';
    if (!empty($video_link) && !preg_match('/^https:\/\/super-tube\.cc\/video\/v\d+$/', $video_link)) {
        $errors['video_link'] = 'Некорректная ссылка SuperTube (https://super-tube.cc/video/v23189)';
    }
    if (!is_numeric($hours) || $hours < 1 || $hours > 4) $errors['hours'] = 'Обязательное, целое число, не более 4 часов';

    if (empty($errors)) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE lessons SET title=?, content=?, video_link=?, hours=? WHERE id=?');
            $stmt->execute([$title, $content, $video_link, $hours, $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO lessons (course_id, title, content, video_link, hours) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$course_id, $title, $content, $video_link, $hours]);
        }
        redirect("lessons.php?course_id=$course_id");
    }
}
?>

<h1><?php echo $id ? 'Редактировать урок' : 'Добавить урок'; ?></h1>

<form method="POST" class="login-box" style="text-align: left; max-width: 800px; margin: 20px 0;">
    <div class="form-group">
        <label>Заголовок урока</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? $lesson['title'] ?? ''); ?>" style="<?php echo isset($errors['title']) ? 'border-color: red;' : ''; ?>">
        <?php if (isset($errors['title'])): ?><small style="color: red;"><?php echo $errors['title']; ?></small><?php endif; ?>
    </div>
    <div class="form-group">
        <label>Содержание</label>
        <textarea name="content" rows="10" style="<?php echo isset($errors['content']) ? 'border-color: red;' : ''; ?>"><?php echo htmlspecialchars($_POST['content'] ?? $lesson['content'] ?? ''); ?></textarea>
        <?php if (isset($errors['content'])): ?><small style="color: red;"><?php echo $errors['content']; ?></small><?php endif; ?>
    </div>
    <div class="form-group">
        <label>Видео-ссылка SuperTube (необязательно)</label>
        <input type="text" name="video_link" placeholder="https://super-tube.cc/video/v23189" value="<?php echo htmlspecialchars($_POST['video_link'] ?? $lesson['video_link'] ?? ''); ?>" style="<?php echo isset($errors['video_link']) ? 'border-color: red;' : ''; ?>">
        <?php if (isset($errors['video_link'])): ?><small style="color: red;"><?php echo $errors['video_link']; ?></small><?php endif; ?>
    </div>
    <div class="form-group">
        <label>Длительность (в часах)</label>
        <input type="number" name="hours" value="<?php echo htmlspecialchars($_POST['hours'] ?? $lesson['hours'] ?? ''); ?>" style="<?php echo isset($errors['hours']) ? 'border-color: red;' : ''; ?>">
        <?php if (isset($errors['hours'])): ?><small style="color: red;"><?php echo $errors['hours']; ?></small><?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary"><?php echo $id ? 'Сохранить' : 'Создать'; ?></button>
    <a href="lessons.php?course_id=<?php echo $course_id; ?>" class="btn btn-warning">Отмена</a>
</form>

<?php require_once 'footer.php'; ?>
