<?php
if(!isset($_SESSION))
{
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <nav>
            <div>
                <a href="about.php">О нас</a>
                <a href="portfolio.php">Наши работы</a>
                <a href="price.php">Прайс</a>
                <a href="contact.php">Контакты</a>
            </div>
            <div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php echo '<a href="profile.php">' . $_SESSION['name'] . '</a>' ?>
                    <a href="logout.php">Выход</a>
                <?php else: ?>
                    <a href="login.php">Вход</a>
                    <a href="register.php">Регистрация</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
</body>