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

function getAdminRecords($pdo, $statusFilter) {
    $statusCondition = '';
    if ($statusFilter) {
        $statusCondition = 'AND records.status = :status';
    }

    // Запрос к базе данных
    $query = "SELECT records.id, records.price_id, records.schedule_id, records.photographer_id, records.status,
                     prices.name AS tariff_name, DATE_FORMAT(schedules.start_time, '%d-%m-%Y %H:%i') AS formatted_start_time,
                     photographers.name AS photographer_name, 
                     users.name AS client_name, users.surname AS client_surname, users.patronymic AS client_patronymic
              FROM records
              JOIN prices ON records.price_id = prices.id
              JOIN schedules ON records.schedule_id = schedules.id
              JOIN photographers ON records.photographer_id = photographers.id
              JOIN users ON records.user_id = users.id
              WHERE 1 {$statusCondition}
              ORDER BY records.id DESC";

    $statement = $pdo->prepare($query);

    if ($statusFilter) {
        $statement->bindParam(':status', $statusFilter, PDO::PARAM_STR);
    }

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
    <title>Панель администратора</title>
</head>
<body>
    <h2>
        Управление записями
    </h2>
    <div>
        <label for="statusFilter">Фильтр по статусу:</label>
        <select id="statusFilter" onchange="applyStatusFilter()">
            <option value="">Все записи</option>
            <option value="новая">Новые</option>
            <option value="подтверждена">Подтвержденные</option>
            <option value="отменена">Отмененные</option>
        </select>
    </div>
    <div class="admin-cards">
        <?php
        $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
        $adminRecords = getAdminRecords($pdo, $statusFilter);

        // Вывод данных
        foreach ($adminRecords as $record) {
            echo "<div class='admin-card'>";
            echo "<p><b>Фотосессия №:</b> {$record['id']}</p>";
            echo "<p><b>Тариф фотосессии:</b> {$record['tariff_name']}</p>";
            echo "<p><b>Дата фотосессии:</b> {$record['formatted_start_time']}</p>";
            echo "<p><b>Фотограф:</b> {$record['photographer_name']}</p>";
            echo "<p><b>Клиент:</b> {$record['client_surname']} {$record['client_name']} {$record['client_patronymic']}</p>";
            echo "<p><b>Статус:</b> {$record['status']}</p>";
            echo "<button onclick='cancelRecord({$record['id']})'>Отменить</button>";
            echo "<button onclick='confirmRecord({$record['id']})'>Подтвердить</button>";
            echo "</div>";
        }
        ?>
    </div>

    <script>
        function applyStatusFilter() {
            var statusFilter = document.getElementById('statusFilter').value;
            window.location.href = 'admin.php?status=' + encodeURIComponent(statusFilter);
        }

        function cancelRecord(recordId) {
            updateRecordStatus(recordId, 'отменена');
        }

        function confirmRecord(recordId) {
            updateRecordStatus(recordId, 'подтверждена');
        }

        function updateRecordStatus(recordId, status) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'process_update_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            // Формируем данные для отправки
            var data = 'record_id=' + encodeURIComponent(recordId);
            data += '&status=' + encodeURIComponent(status);

            // Устанавливаем обработчик события при завершении запроса
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Обработка успешного ответа от сервера
                    alert('Статус записи обновлен!');
                    location.reload();
                } else {
                    if (xhr.status === 401) {
                        alert('Вы не авторизованы. Войдите или зарегистрируйтесь!');
                    } else {
                        alert('Произошла ошибка при обновлении статуса записи.');
                    }
                }
            };

            // Отправляем данные на сервер
            xhr.send(data);
        }
    </script>
</body>
</html>