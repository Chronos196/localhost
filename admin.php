<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include('db.php');
include('header.php');

// Проверка на администратора
if ($_SESSION['is_admin'] == 1) {
    echo "Вы успешно вошли в админскую панель";
}
else {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
</head>
<body>
    <h2>Админская панель</h2>
</body>
</html>