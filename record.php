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
        $tariffInfo .= '<p data-price-id="' . $priceId . '">Тариф: ' . $tariff['name'] . '</p>';

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
        $photographerInfo .= '<p data-photographer-id="' . $photographerId . '"> Имя фотографа: ' . $photographer['name'] . '</p>';
        $photographerInfo .= '<img src="' . $photographer['photo_filename'] . '" alt="Фото фотографа">';
    } else {
        $photographerInfo .= '<p>Ошибка: Фотограф с указанным ID не найден.</p>';
    }

    return $photographerInfo;
}

function getScheduleInformation($pdo, $photographerId) {
    $scheduleInfo = "";

    // Получаем информацию о расписании фотографа
    $scheduleQuery = "SELECT id, start_time, DATE_FORMAT(start_time, '%d-%m-%Y %H:%i') AS formatted_start_time, status FROM schedule WHERE photographer_id = :photographerId AND status = 'свободен'";
    $scheduleStatement = $pdo->prepare($scheduleQuery);
    $scheduleStatement->bindParam(':photographerId', $photographerId, PDO::PARAM_INT);
    $scheduleStatement->execute();

    // Если есть свободные записи в расписании, выводим выпадающий список
    if ($scheduleRows = $scheduleStatement->fetchAll(PDO::FETCH_ASSOC)) {
        $scheduleInfo .= '<label for="time">Выберите время:</label>';
        $scheduleInfo .= '<select name="time" id="time">';
        foreach ($scheduleRows as $scheduleRow) {
            $scheduleInfo .= '<option value="' . $scheduleRow['id'] . '" data-schedule-id="' . $scheduleRow['id'] . '">' . $scheduleRow['formatted_start_time'] . '</option>';
        }
        $scheduleInfo .= '</select>';
        $scheduleInfo .= '<input type="hidden" name="schedule_id" id="schedule_id" value="">'; // Добавлено скрытое поле для schedule_id
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Находим кнопку "Подтвердить" и выпадающий список с помощью JavaScript
            var confirmButton = document.getElementById('confirmButton');
            var timeSelect = document.getElementById('time');
            var scheduleIdInput = document.getElementById('schedule_id');

            // Добавляем обработчик события клика по кнопке "Подтвердить"
            confirmButton.addEventListener('click', function() {
                // Получаем выбранное время из выпадающего списка
                var selectedTime = timeSelect.value;

                // Получаем user_id из сессии (предполагается, что пользователь авторизован)
                var userId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;

                // Получаем значения атрибутов data-price-id, data-photographer-id, data-schedule-id
                var priceId = document.querySelector('[data-price-id]').getAttribute('data-price-id');
                var photographerId = document.querySelector('[data-photographer-id]').getAttribute('data-photographer-id');
                var scheduleId = timeSelect.value;

                // Отправляем данные на сервер с использованием AJAX
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'process_confirmation.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                // Формируем данные для отправки
                var data = '&user_id=' + encodeURIComponent(userId);
                data += '&schedule_id=' + encodeURIComponent(scheduleId);
                data += '&photographer_id=' + encodeURIComponent(photographerId);
                data += '&price_id=' + encodeURIComponent(priceId);

                // Устанавливаем обработчик события при завершении запроса
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // Обработка успешного ответа от сервера
                        alert('Запись подтверждена!');
                    } else {
                        if (xhr.status === 401) {
                            alert('Вы не авторизованы. Войдите или зарегистрируйтесь.');
                        } else {
                            alert('Произошла ошибка при подтверждении записи.');
                        }
                    }
                };

                // Отправляем данные на сервер
                xhr.send(data);
            });
        });
    </script>
</body>
</html>
