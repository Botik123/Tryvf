<?php
require_once 'header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$course = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM courses WHERE id = ?');
    $stmt->execute([$id]);
    $course = $stmt->fetch();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $hours = $_POST['hours'] ?? '';
    $price = $_POST['price'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    // Validation
    if (empty($name) || mb_strlen($name) > 30) $errors['name'] = 'Обязательно, максимум 30 символов';
    if (mb_strlen($description) > 100) $errors['description'] = 'Максимум 100 символов';
    if (!is_numeric($hours) || $hours < 1 || $hours > 10) $errors['hours'] = 'Обязательно, целое число не больше 10';
    if (!is_numeric($price) || $price < 100) $errors['price'] = 'Обязательное, число не менее 100';
    if (empty($start_date)) $errors['start_date'] = 'Обязательное поле';
    if (empty($end_date)) $errors['end_date'] = 'Обязательное поле';

    // Image handling
    $img_name = $course ? $course['img'] : '';
    if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['img'];
        $type = mime_content_type($file['tmp_name']);
        if ($type !== 'image/jpeg' && $type !== 'image/jpg') {
            $errors['img'] = 'Только JPG (JPEG)';
        } elseif ($file['size'] > 2000 * 1024) {
            $errors['img'] = 'Максимум 2000 Кб';
        } else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $img_name = 'mpic' . time() . '.' . $ext;
            $uploadPath = '../uploads/' . $img_name;
            
            // Thumbnail processing (300x300)
            $src = imagecreatefromjpeg($file['tmp_name']);
            list($width, $height) = getimagesize($file['tmp_name']);
            $ratio = $width / $height;
            if ($ratio > 1) {
                $new_width = 300;
                $new_height = 300 / $ratio;
            } else {
                $new_height = 300;
                $new_width = 300 * $ratio;
            }
            $tmp = imagecreatetruecolor($new_width, $new_height);
            imagecopyresampled($tmp, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagejpeg($tmp, $uploadPath, 90);
            imagedestroy($src);
            imagedestroy($tmp);
        }
    } elseif (!$course) {
        $errors['img'] = 'Обложка обязательна при создании';
    }

    if (empty($errors)) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE courses SET name=?, description=?, hours=?, price=?, start_date=?, end_date=?, img=? WHERE id=?');
            $stmt->execute([$name, $description, $hours, $price, $start_date, $end_date, $img_name, $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO courses (name, description, hours, price, start_date, end_date, img) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $description, $hours, $price, $start_date, $end_date, $img_name]);
        }
        redirect('courses.php');
    }
}
?>

<h1><?php echo $id ? 'Редактировать курс' : 'Добавить курс'; ?></h1>

<form method="POST" enctype="multipart/form-data" class="login-box" style="text-align: left; max-width: 800px; margin: 20px 0;">
    <div class="form-group">
        <label>Название курса</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? $course['name'] ?? ''); ?>" style="<?php echo isset($errors['name']) ? 'border-color: red;' : ''; ?>">
        <?php if (isset($errors['name'])): ?><small style="color: red;"><?php echo $errors['name']; ?></small><?php endif; ?>
    </div>
    <div class="form-group">
        <label>Описание курса</label>
        <textarea name="description" style="<?php echo isset($errors['description']) ? 'border-color: red;' : ''; ?>"><?php echo htmlspecialchars($_POST['description'] ?? $course['description'] ?? ''); ?></textarea>
        <?php if (isset($errors['description'])): ?><small style="color: red;"><?php echo $errors['description']; ?></small><?php endif; ?>
    </div>
    <div class="form-group">
        <label>Продолжительность (в часах)</label>
        <input type="number" name="hours" value="<?php echo htmlspecialchars($_POST['hours'] ?? $course['hours'] ?? ''); ?>" style="<?php echo isset($errors['hours']) ? 'border-color: red;' : ''; ?>">
        <?php if (isset($errors['hours'])): ?><small style="color: red;"><?php echo $errors['hours']; ?></small><?php endif; ?>
    </div>
    <div class="form-group">
        <label>Цена</label>
        <input type="text" name="price" placeholder="100.00" value="<?php echo htmlspecialchars($_POST['price'] ?? $course['price'] ?? ''); ?>" style="<?php echo isset($errors['price']) ? 'border-color: red;' : ''; ?>">
        <?php if (isset($errors['price'])): ?><small style="color: red;"><?php echo $errors['price']; ?></small><?php endif; ?>
    </div>
    <div class="form-group">
        <label>Дата начала</label>
        <input type="date" name="start_date" value="<?php echo htmlspecialchars($_POST['start_date'] ?? $course['start_date'] ?? ''); ?>" style="<?php echo isset($errors['start_date']) ? 'border-color: red;' : ''; ?>">
        <?php if (isset($errors['start_date'])): ?><small style="color: red;"><?php echo $errors['start_date']; ?></small><?php endif; ?>
    </div>
    <div class="form-group">
        <label>Дата окончания</label>
        <input type="date" name="end_date" value="<?php echo htmlspecialchars($_POST['end_date'] ?? $course['end_date'] ?? ''); ?>" style="<?php echo isset($errors['end_date']) ? 'border-color: red;' : ''; ?>">
        <?php if (isset($errors['end_date'])): ?><small style="color: red;"><?php echo $errors['end_date']; ?></small><?php endif; ?>
    </div>
    <div class="form-group">
        <label>Обложка курса (JPG, до 2000 Кб)</label>
        <input type="file" name="img" style="<?php echo isset($errors['img']) ? 'border-color: red;' : ''; ?>">
        <?php if (isset($errors['img'])): ?><small style="color: red;"><?php echo $errors['img']; ?></small><?php endif; ?>
        <?php if ($course): ?>
            <div style="margin-top: 10px;">Текущая: <img src="../uploads/<?php echo $course['img']; ?>" style="width: 100px;"></div>
        <?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary"><?php echo $id ? 'Сохранить' : 'Создать'; ?></button>
    <a href="courses.php" class="btn btn-warning">Отмена</a>
</form>

<?php require_once 'footer.php'; ?>
