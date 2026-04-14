<?php
$order_id = $_GET['order_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оплата курса</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1 class="login-title">Оплата заказа #<?php echo $order_id; ?></h1>
            <p style="margin-bottom: 20px;">Введите реквизиты карты для оплаты</p>
            <div class="form-group">
                <label>Номер карты</label>
                <input type="text" id="card_number" placeholder="8888 0000 0000 1111" maxlength="19">
            </div>
            <button class="login-btn" onclick="pay()">Оплатить</button>
            <p id="msg" style="margin-top: 15px;"></p>
        </div>
    </div>

    <script>
        async function pay() {
            const card = document.getElementById('card_number').value.replace(/\s/g, '');
            let status = 'failed';
            if (card === '8888000000001111') status = 'success';
            
            const response = await fetch('/school-api/payment-webhook', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    order_id: <?php echo $order_id; ?>,
                    status: status
                })
            });

            if (response.status === 204) {
                document.getElementById('msg').innerText = 'Запрос отправлен. Статус: ' + status;
                setTimeout(() => {
                    window.location.href = 'student/my-courses.php';
                }, 2000);
            }
        }
    </script>
</body>
</html>
