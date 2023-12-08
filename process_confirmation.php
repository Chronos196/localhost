<?php
include('db.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    // Если пользователь не авторизован, отправляем ошибку
    http_response_code(401); // Unauthorized
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем выбранное время из данных запроса
    $selectedTime = $_POST['selectedTime'];

    // Дополнительная обработка (например, обновление базы данных и т.д.)

    // Отправляем успешный ответ
    http_response_code(200);
} else {
    // Неверный метод запроса
    http_response_code(400);
}
?>