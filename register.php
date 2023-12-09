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
    $name = validateInput($_POST['name']);
    $surname = validateInput($_POST['surname']);
    $patronymic = validateInput($_POST['patronymic']);
    $login = validateInput($_POST['login']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $password_repeat = $_POST['password_repeat'];
    $accept = isset($_POST['accept']) ? true : false;

    $error_msg = "";

    // Проверка наличия логина в базе
    $check_query = "SELECT id FROM users WHERE login = :login";
    $check_statement = $pdo->prepare($check_query);
    $check_statement->bindParam(':login', $login);
    $check_statement->execute();

    if ($check_statement->rowCount() > 0) {
        $error_msg = "Такой логин уже занят, попробуйте другой";
    } elseif ($password !== $password_repeat) {
        $error_msg = "Повторный пароль не совпадает с паролем";
    } elseif (!$email) {
        $error_msg = "Некорректный формат email";
    } elseif (strlen($password) < 6) {
        $error_msg = "Пароль должен содержать не менее 6 символов";
    } elseif (!$accept) {
        $error_msg = "Необходимо согласиться с правилами регистрации";
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

    // Обработка ошибок валидации
    if ($name === false) {
        $error_msg = "Некорректное имя";
    } elseif ($surname === false) {
        $error_msg = "Некорректная фамилия";
    } elseif ($login === false) {
        $error_msg = "Некорректный логин";
    }
}

// Функция для валидации введенных данных
function validateInput($input) {
    // Разрешенные символы: кириллица, пробел и тире
    $pattern = "/^[\p{Cyrillic} -]+$/u";
    
    // Проверка соответствия паттерну
    if (preg_match($pattern, $input)) {
        return $input;
    } else {
        // Если данные не прошли валидацию, возвращаем false
        return false;
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
    echo "<p class='register-error'>$error_msg</p>";
    ?>
    <form method="POST" action="register.php" class="register-form">
        <div class="register-container">
            <label for="name">Имя:</label>
            <input type="text" id="name" name="name" required maxlength="255">
        </div>

        <div class="register-container">
            <label for="surname">Фамилия:</label>
            <input type="text" id="surname" name="surname" required maxlength="255">
        </div>

        <div class="register-container">
            <label for="patronymic">*Отчество:</label>
            <input type="text" id="patronymic" name="patronymic" maxlength="255">
        </div>
        
        <div class="register-container">
            <label for="login">Логин:</label>
            <input type="text" id="login" name="login" maxlength="255">
        </div>

        <div class="register-container">
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" maxlength="255">
        </div>

        <div class="register-container">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required maxlength="255">
        </div>

        <div class="register-container">
            <label for="password_repeat">Повторите пароль:</label>
            <input type="password" id="password_repeat" name="password_repeat" required maxlength="255">
        </div>

        <div class="register-container">
            <label class="register-personal-data">
                <input type="checkbox" name="accept" required>
                Я согласен с обработкой персональных данных
            </label>
        </div>

        <input type="submit" value="Зарегистрироваться">
    </form>
</body>
</html>