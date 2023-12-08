<?php
session_start();

// Проверка авторизации
if (isset($_SESSION['user_id'])) {
    header('Location: about.php');
    exit();
}

include('db.php');
include('header.php');

// Обработчик регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $patronymic = $_POST['patronymic'];
    $login = $_POST['login'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_repeat = $_POST['password_repeat'];

    $error_msg = "";

    if ($password !== $password_repeat) {
        $error_msg = "Повторный пароль не совпадает с паролем";
    } else {
        // Проверка наличия логина в базе
        $check_query = "SELECT id FROM users WHERE login = :login";
        $check_statement = $pdo->prepare($check_query);
        $check_statement->bindParam(':login', $login);
        $check_statement->execute();

        if ($check_statement->rowCount() > 0) {
            $error_msg = "Такой логин уже занят, попробуйте другой";
        } else {
            try {
                // SQL-запрос для вставки данных
                $sql = "INSERT INTO users (name, surname, patronymic, login, email, password) VALUES (?, ?, ?, ?, ?, ?)";
                
                // Подготовка запроса
                $stmt = $pdo->prepare($sql);

                // Привязка параметров
                $stmt->bindParam(1, $name);
                $stmt->bindParam(2, $surname);
                $stmt->bindParam(3, $patronymic);
                $stmt->bindParam(4, $login);
                $stmt->bindParam(5, $email);
                $stmt->bindParam(6, password_hash($password, PASSWORD_DEFAULT));
            
                // Выполнение запроса
                $stmt->execute();

                header('Location: login.php');
                exit();
            } catch (PDOException $e) {
                echo "Ошибка: " . $e->getMessage();
            }
        }
    }


}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Регистрация</h2>
    <?php 
    echo "<p>$error_msg</p>";
    ?>
    <form method="POST" action="register.php" class="register-form">
        <label for="name">Имя:</label>
        <input type="text" id="name" name="name" required maxlength="255">

        <label for="surname">Фамилия:</label>
        <input type="text" id="surname" name="surname" required maxlength="255">

        <label for="patronymic">*Отчество:</label>
        <input type="text" id="patronymic" name="patronymic" maxlength="255">

        <label for="login">Логин:</label>
        <input type="text" id="login" name="login" maxlength="255">

        <label for="email">Email:</label>
        <input type="text" id="email" name="email" maxlength="255">

        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required maxlength="255">

        <label for="password_repeat">Повторите пароль:</label>
        <input type="password" id="password_repeat" name="password_repeat" required maxlength="255">

        <label>
            <input type="checkbox" name="accept" required>
            Я согласен с обработкой персональных данных
        </label>

        <input type="submit" value="Зарегистрироваться">
    </form>
</body>
</html>