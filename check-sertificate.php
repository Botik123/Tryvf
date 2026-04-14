<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $cert_number = $data['sertikate_number'] ?? '';

    if (substr($cert_number, -1) === '1') {
        jsonResponse(['status' => 'success']);
    } else {
        jsonResponse(['status' => 'failed']);
    }
}
?>
