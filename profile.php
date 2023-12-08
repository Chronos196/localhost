<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include('db.php');
include("header.php");

function getUserRecords($pdo, $userId) {
    // Запрос к базе данных
    $query = "SELECT records.id, records.price_id, records.schedule_id, records.photographer_id,
                     prices.name AS tariff_name, DATE_FORMAT(schedules.start_time, '%d-%m-%Y %H:%i') AS formatted_start_time,
                     photographers.name AS photographer_name
              FROM records
              JOIN prices ON records.price_id = prices.id
              JOIN schedules ON records.schedule_id = schedules.id
              JOIN photographers ON records.photographer_id = photographers.id
              WHERE records.user_id = :userId
              ORDER BY records.id DESC";

    $statement = $pdo->prepare($query);
    $statement->bindParam(':userId', $userId, PDO::PARAM_INT);
    $statement->execute();

    // Получение результатов запроса
    $records = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $records;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
</head>
<body>
    <h2>
        Записи
    </h2>
    <div class="session-cards">
        <?php
        $userId = $_SESSION['user_id'];
        $userRecords = getUserRecords($pdo, $userId);

        // Вывод данных
        foreach ($userRecords as $record) {
            echo "<div class='session-card'>";
            echo "<p><b>Фотосессия №:</b> {$record['id']}</p>";
            echo "<p><b>Тариф фотосессии:</b> {$record['tariff_name']}</p>";
            echo "<p><b>Дата фотосессии:</b> {$record['formatted_start_time']}</p>";
            echo "<p><b>Имя и фамилия фотографа:</b> {$record['photographer_name']}</p>";
            echo "<button onclick='deleteRecord({$record['id']})'>Удалить</button>";
            echo "</div>";
        }
        ?>
    </div>

    <script>
        function deleteRecord(recordId) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'process_delete.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            // Формируем данные для отправки
            var data = 'record_id=' + encodeURIComponent(recordId);

            // Устанавливаем обработчик события при завершении запроса
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Обработка успешного ответа от сервера
                    alert('Запись удалена!');
                    location.reload();
                } else {
                    if (xhr.status === 401) {
                        alert('Вы не авторизованы. Войдите или зарегистрируйтесь!');
                    } else {
                        alert('Произошла ошибка при удалении записи.');
                    }
                }
            };

            // Отправляем данные на сервер
            xhr.send(data);
        }
    </script>
</body>
</html>
