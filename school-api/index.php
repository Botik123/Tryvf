<?php
require_once __DIR__ . '/../db.php';

$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/school-api';
$path = str_replace($base_path, '', $request_uri);
$path = explode('?', $path)[0];
$method = $_SERVER['REQUEST_METHOD'];

// Helper for Bearer Token
function getBearerToken() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return null;
}

function getAuthUser($pdo) {
    $token = getBearerToken();
    if (!$token) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE token = ?");
    $stmt->execute([$token]);
    return $stmt->fetch();
}

function requireAuth($pdo) {
    $user = getAuthUser($pdo);
    if (!$user) {
        jsonResponse(['message' => 'Forbidden for you'], 403);
    }
    return $user;
}

// Routing
if ($path === '/registr' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $errors = [];
    if (empty($name)) $errors['name'] = ['Обязательное поле'];
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = ['Некорректный email'];
    if (empty($password) || strlen($password) < 3 || 
        !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || 
        !preg_match('/[0-9]/', $password) || !preg_match('/[_#!%]/', $password)) {
        $errors['password'] = ['Минимум 3 символа, верхний и нижний регистр, цифра и спецсимвол (_#!%)'];
    }

    if (!empty($errors)) jsonResponse(['message' => 'Invalid fields', 'errors' => $errors], 422);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) jsonResponse(['message' => 'Invalid fields', 'errors' => ['email' => ['Уже занят']]], 422);

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, password_hash($password, PASSWORD_BCRYPT)]);
    jsonResponse(['success' => true], 201);
}

if ($path === '/auth' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("UPDATE users SET token = ? WHERE id = ?");
        $stmt->execute([$token, $user['id']]);
        jsonResponse(['token' => $token]);
    } else {
        jsonResponse(['message' => 'Invalid data', 'errors' => ['email' => ['Invalid data']]], 422);
    }
}

if ($path === '/courses' && $method === 'GET') {
    requireAuth($pdo);
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 5;
    $offset = ($page - 1) * $perPage;

    $total = ceil($pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn() / $perPage);
    $stmt = $pdo->prepare("SELECT * FROM courses LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $courses = $stmt->fetchAll();

    $data = [];
    foreach ($courses as $c) {
        $data[] = [
            'id' => $c['id'],
            'name' => $c['name'],
            'description' => $c['description'],
            'hours' => $c['hours'],
            'img' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/uploads/" . $c['img'],
            'start_date' => date('d-m-Y', strtotime($c['start_date'])),
            'end_date' => date('d-m-Y', strtotime($c['end_date'])),
            'price' => number_format($c['price'], 2, '.', '')
        ];
    }

    jsonResponse([
        'data' => $data,
        'pagination' => ['total' => (int)$total, 'current' => $page, 'per_page' => $perPage]
    ]);
}

if (preg_match('/^\/courses\/(\d+)$/', $path, $matches) && $method === 'GET') {
    requireAuth($pdo);
    $course_id = $matches[1];
    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $lessons = $stmt->fetchAll();

    $data = [];
    foreach ($lessons as $l) {
        $data[] = [
            'id' => $l['id'],
            'name' => $l['title'],
            'description' => $l['content'],
            'video_link' => $l['video_link'],
            'hours' => $l['hours']
        ];
    }
    jsonResponse(['data' => $data]);
}

if (preg_match('/^\/courses\/(\d+)\/buy$/', $path, $matches) && $method === 'POST') {
    $user = requireAuth($pdo);
    $course_id = $matches[1];

    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();

    if (!$course) jsonResponse(['message' => 'Course not found'], 404);

    $today = date('Y-m-d');
    if ($today >= $course['start_date']) {
        jsonResponse(['message' => 'Course already started or finished'], 422);
    }

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, course_id, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$user['id'], $course_id]);
    $order_id = $pdo->lastInsertId();

    jsonResponse(['pay_url' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/payment-webhook?order_id=$order_id"]);
}

if ($path === '/payment-webhook' && $method === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    
    $order_id = $data['order_id'] ?? 0;
    $status = $data['status'] ?? '';

    if (!$order_id) {
        jsonResponse(['message' => 'Order ID required'], 422);
    }

    $newStatus = ($status === 'success') ? 'success' : 'failed';
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $order_id]);
    
    jsonResponse(null, 204);
}

if ($path === '/orders' && $method === 'GET') {
    $user = requireAuth($pdo);
    $stmt = $pdo->prepare("SELECT o.*, c.name, c.description, c.hours, c.img, c.start_date, c.end_date, c.price 
                           FROM orders o JOIN courses c ON o.course_id = c.id 
                           WHERE o.user_id = ?");
    $stmt->execute([$user['id']]);
    $orders = $stmt->fetchAll();

    $data = [];
    foreach ($orders as $o) {
        $data[] = [
            'id' => $o['id'],
            'payment_status' => $o['status'],
            'course' => [
                'id' => $o['course_id'],
                'name' => $o['name'],
                'description' => $o['description'],
                'hours' => $o['hours'],
                'img' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/uploads/" . $o['img'],
                'start_date' => date('d-m-Y', strtotime($o['start_date'])),
                'end_date' => date('d-m-Y', strtotime($o['end_date'])),
                'price' => number_format($o['price'], 2, '.', '')
            ]
        ];
    }
    jsonResponse(['data' => $data]);
}

if (preg_match('/^\/orders\/(\d+)$/', $path, $matches) && $method === 'GET') {
    $user = requireAuth($pdo);
    $order_id = $matches[1];

    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user['id']]);
    $order = $stmt->fetch();

    if (!$order) jsonResponse(['message' => 'Order not found'], 404);

    if ($order['status'] === 'success') {
        jsonResponse(['status' => 'was payed'], 418);
    }

    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    jsonResponse(['status' => 'success']);
}

jsonResponse(['message' => 'Not Found'], 404);
?>
