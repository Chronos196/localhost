<?php
include('db.php');
include("header.php");

function getRecordInformation($pdo) {
    $info = "";

    // Проверяем наличие параметров в URL
    if (isset($_GET['photographer']) && isset($_GET['price'])) {
        // Получаем переданные параметры
        $photographerId = $_GET['photographer'];
        $price = $_GET['price'];

        // Выводим информацию о тарифе, фотографе и категории
        $info = getTariffInformation($pdo, $price);
        $info .= getPhotographerInformation($pdo, $photographerId);
        $info .= getScheduleInformation($pdo, $photographerId);
    } else {
        // Если параметры не переданы, выводим сообщение об ошибке
        $info = 'Ошибка: Некоторые параметры отсутствуют.';
    }

    return $info;
}

function getTariffInformation($pdo, $priceId) {
    $tariffInfo = "";

    // Получаем информацию о тарифе
    $tariffQuery = "SELECT name, price, category_id FROM prices WHERE id = :priceId";
    $tariffStatement = $pdo->prepare($tariffQuery);
    $tariffStatement->bindParam(':priceId', $priceId, PDO::PARAM_INT);
    $tariffStatement->execute();

    // Если тариф найден, выводим его информацию
    if ($tariff = $tariffStatement->fetch(PDO::FETCH_ASSOC)) {
        $tariffInfo .= '<p>Тариф: ' . $tariff['name'] . '</p>';
        
        // Получаем название категории
        $categoryQuery = "SELECT name FROM categories WHERE id = :categoryId";
        $categoryStatement = $pdo->prepare($categoryQuery);
        $categoryStatement->bindParam(':categoryId', $tariff['category_id'], PDO::PARAM_INT);
        $categoryStatement->execute();

        // Если категория найдена, выводим её название
        if ($category = $categoryStatement->fetch(PDO::FETCH_ASSOC)) {
            $tariffInfo .= '<p>Категория: ' . $category['name'] . '</p>';
        } else {
            $tariffInfo .= '<p>Ошибка: Категория с указанным ID не найдена.</p>';
        }
    } else {
        $tariffInfo .= '<p>Ошибка: Тариф с указанным ID не найден.</p>';
    }
    $tariffInfo .= '<p>Цена: ' . number_format($tariff['price'], 0, ',', ' ') . ' руб</p>';
    
    return $tariffInfo;
}

function getPhotographerInformation($pdo, $photographerId) {
    $photographerInfo = "";

    // Получаем информацию о фотографе
    $photographerQuery = "SELECT name, photo_filename FROM photographers WHERE id = :photographerId";
    $photographerStatement = $pdo->prepare($photographerQuery);
    $photographerStatement->bindParam(':photographerId', $photographerId, PDO::PARAM_INT);
    $photographerStatement->execute();

    // Если фотограф найден, выводим его информацию
    if ($photographer = $photographerStatement->fetch(PDO::FETCH_ASSOC)) {
        $photographerInfo .= '<p>Имя фотографа: ' . $photographer['name'] . '</p>';
        $photographerInfo .= '<img src="' . $photographer['photo_filename'] . '" alt="Фото фотографа">';
    } else {
        $photographerInfo .= '<p>Ошибка: Фотограф с указанным ID не найден.</p>';
    }

    return $photographerInfo;
}

function getScheduleInformation($pdo, $photographerId) {
    $scheduleInfo = "";

    // Получаем информацию о расписании фотографа
    $scheduleQuery = "SELECT start_time, DATE_FORMAT(start_time, '%d-%m-%Y %H:%i') AS formatted_start_time, status FROM schedule WHERE photographer_id = :photographerId AND status = 'свободен'";
    $scheduleStatement = $pdo->prepare($scheduleQuery);
    $scheduleStatement->bindParam(':photographerId', $photographerId, PDO::PARAM_INT);
    $scheduleStatement->execute();

    // Если есть свободные записи в расписании, выводим выпадающий список
    if ($scheduleRows = $scheduleStatement->fetchAll(PDO::FETCH_ASSOC)) {
        $scheduleInfo .= '<label for="time">Выберите время:</label>';
        $scheduleInfo .= '<select name="time" id="time">';
        foreach ($scheduleRows as $scheduleRow) {
            $scheduleInfo .= '<option value="' . $scheduleRow['start_time'] . '">' . $scheduleRow['formatted_start_time'] . '</option>';
        }
        $scheduleInfo .= '</select>';
        $scheduleInfo .= '<button id="confirmButton">Подтвердить</button>';
    } else {
        $scheduleInfo .= '<p>Фотограф не имеет свободных временных слотов.</p>';
    }

    return $scheduleInfo;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запись</title>
</head>
<body>
    <h2>Запись</h2>
    <div class="record-info">
        <?php 
        echo getRecordInformation($pdo);
        ?>
    </div>
</body>
</html>
