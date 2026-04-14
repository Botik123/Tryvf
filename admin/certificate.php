<?php
require_once __DIR__ . '/../db.php';
if (!isAdmin()) redirect('../index.php');

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT o.*, u.name as student_name, c.name as course_name 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       JOIN courses c ON o.course_id = c.id 
                       WHERE o.id = ? AND o.status = 'success'");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) die('Запись не найдена или не оплачена');

if (!$order['certificate_number']) {
    // Generate certificate number
    $service_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/create-sertificate";
    
    $ch = curl_init($service_url);
    $payload = json_encode(['student_id' => $order['user_id'], 'course_id' => $order['course_id']]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'ClientId: admin@edu.com'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    $prefix = $data['course_number'] ?? 'ABCDEF';
    
    // 6 digits from system, last is 1
    $suffix = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT) . '1';
    $cert_number = $prefix . $suffix;
    
    $stmt = $pdo->prepare("UPDATE orders SET certificate_number = ? WHERE id = ?");
    $stmt->execute([$cert_number, $id]);
    $order['certificate_number'] = $cert_number;
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Сертификат — <?php echo htmlspecialchars($order['student_name']); ?></title>
    <style>
        body { font-family: 'Times New Roman', serif; background: #f0f0f0; display: flex; justify-content: center; padding: 50px; }
        .certificate {
            width: 800px;
            height: 600px;
            background: white;
            border: 20px solid #4caf50;
            padding: 40px;
            text-align: center;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
        }
        h1 { font-size: 50px; color: #1b5e20; margin-top: 50px; }
        .content { font-size: 24px; margin-top: 30px; }
        .name { font-size: 36px; font-weight: bold; text-decoration: underline; margin: 20px 0; }
        .course { font-size: 30px; color: #2e7d32; font-style: italic; }
        .footer { position: absolute; bottom: 40px; width: 100%; left: 0; display: flex; justify-content: space-around; font-size: 18px; }
        .cert-num { font-weight: bold; color: #555; }
    </style>
</head>
<body>
    <div class="certificate">
        <h1>СЕРТИФИКАТ</h1>
        <div class="content">Настоящим подтверждается, что</div>
        <div class="name"><?php echo htmlspecialchars($order['student_name']); ?></div>
        <div class="content">успешно завершил(а) курс</div>
        <div class="course">«<?php echo htmlspecialchars($order['course_name']); ?>»</div>
        
        <div class="footer">
            <div>Дата: <?php echo date('d.m.Y'); ?></div>
            <div class="cert-num">№ <?php echo $order['certificate_number']; ?></div>
            <div>Подпись: ___________</div>
        </div>
    </div>
    <script>window.print();</script>
</body>
</html>
