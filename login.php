<?php
session_start();

// Проверка авторизации
if (isset($_SESSION['user_id'])) {
    header('Location: about.php');
    exit();
}

include('db.php');
include('header.php');

// Обработчик авторизации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Подключение к базе данных (предполагается, что вы уже установили соединение с БД)

    $login = $_POST['login'];
    $password = $_POST['password'];

    $error_msg = "";

    // Поиск пользователя по логину
    $select_query = "SELECT id, password, is_admin, name FROM users WHERE login = :login";
    $select_statement = $pdo->prepare($select_query);
    $select_statement->bindParam(':login', $login);
    $select_statement->execute();
    $user = $select_statement->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = $user['is_admin'];
        $_SESSION['name'] = $user['name'];
        header('Location: about.php');
        exit();
    } else {
        $error_msg = "Неверный логин или пароль";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
</head>
<body>
    <h2>Вход</h2>
    <?php 
    echo "<p>$error_msg</p>";
    ?>
    <form method="POST" action="login.php" class="login-form">
        <label for="login">Логин:</label>
        <input type="text" id="login" name="login" required maxlength="255">

        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required maxlength="255">

        <input type="submit" value="Войти">
    </form>
</body>
</html>