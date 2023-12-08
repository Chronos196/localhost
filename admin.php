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
    $query = "SELECT records.id, records.price_id, records.schedule_id, records.photographer_id, records.status, records.timestamp,
                     prices.name AS tariff_name, DATE_FORMAT(schedules.start_time, '%d-%m-%Y %H:%i') AS formatted_start_time,
                     photographers.name AS photographer_name, 
                     users.name AS client_name, users.surname AS client_surname, users.patronymic AS client_patronymic
              FROM records
              JOIN prices ON records.price_id = prices.id
              JOIN schedules ON records.schedule_id = schedules.id
              JOIN photographers ON records.photographer_id = photographers.id
              JOIN users ON records.user_id = users.id
              WHERE 1 {$statusCondition}
              ORDER BY records.timestamp DESC";

    $statement = $pdo->prepare($query);

    if ($statusFilter) {
        $statement->bindParam(':status', $statusFilter, PDO::PARAM_STR);
    }

    $statement->execute();

    // Получение результатов запроса
    $records = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $records;
}

function getPhotographers($pdo) {
    $query = "SELECT * FROM photographers";
    $statement = $pdo->query($query);
    $photographers = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $photographers;
}

// Обработка изменений в таблице categories
$categories_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'updateCategory') {
            // Обновление данных в таблице categories
            $categories_msg = updateCategory($pdo);
        } elseif ($_POST['action'] === 'addCategory') {
            // Добавление новой категории
            $categories_msg = addCategory($pdo);
        }
    }
}

// Функция для получения категорий
function getCategories($pdo) {
    $query = "SELECT * FROM categories";
    $statement = $pdo->query($query);
    $categories = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $categories;
}

function getWorks($pdo) {
    $query = "SELECT id, name FROM works";
    $statement = $pdo->query($query);
    $works = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $works;
}

// Функция для обновления категории
function updateCategory($pdo) {
    if (isset($_POST['category_id']) && isset($_POST['new_name'])) {
        $categoryId = intval($_POST['category_id']);
        $newName = $_POST['new_name'];

        // Обновление данных в таблице categories
        $queryUpdateCategory = "UPDATE categories SET name = :new_name WHERE id = :category_id";
        $statementUpdateCategory = $pdo->prepare($queryUpdateCategory);
        $statementUpdateCategory->bindParam(':new_name', $newName, PDO::PARAM_STR);
        $statementUpdateCategory->bindParam(':category_id', $categoryId, PDO::PARAM_INT);

        try {
            $statementUpdateCategory->execute();
            return "Категория успешно обновлена!";
        } catch (Exception $e) {
            return "Ошибка при обновлении категории: " . $e->getMessage();
        }
    } else {
        return "Некорректные данные для обновления категории.";
    }
}

// Функция для добавления новой категории
function addCategory($pdo) {
    if (isset($_POST['new_category'])) {
        $newCategory = $_POST['new_category'];

        // Добавление новой категории в таблицу categories
        $queryAddCategory = "INSERT INTO categories (name) VALUES (:new_category)";
        $statementAddCategory = $pdo->prepare($queryAddCategory);
        $statementAddCategory->bindParam(':new_category', $newCategory, PDO::PARAM_STR);

        try {
            $statementAddCategory->execute();
            return "Новая категория успешно добавлена!";
        } catch (Exception $e) {
            return "Ошибка при добавлении новой категории: " . $e->getMessage();
        }
    } else {
        return "Некорректные данные для добавления новой категории.";
    }
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
            <option value="новый">Новые</option>
            <option value="подтверждён">Подтверженные</option>
            <option value="отменён">Отмененные</option>
        </select>
    </div>
    <div class="admin-cards">
        <?php
        $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
        $adminRecords = getAdminRecords($pdo, $statusFilter);

        // Вывод данных
        foreach ($adminRecords as $record) {
            echo "<div class='admin-card'>";
            echo "<p><b>Дата создания:</b> " . date('d-m-Y H:i', strtotime($record['timestamp'])) . "</p>";
            echo "<p><b>Фотосессия №:</b> {$record['id']}</p>";
            echo "<p><b>Тариф фотосессии:</b> {$record['tariff_name']}</p>";
            echo "<p><b>Дата фотосессии:</b> {$record['formatted_start_time']}</p>";
            echo "<p><b>Фотограф:</b> {$record['photographer_name']}</p>";
            echo "<p><b>Клиент:</b> {$record['client_surname']} {$record['client_name']} {$record['client_patronymic']}</p>";
            echo "<p><b>Статус:</b> {$record['status']}</p>";

            // Добавляем уникальный идентификатор на основе идентификатора записи
            $reasonInputId = "reasonInput{$record['id']}";

            echo "<textarea id='{$reasonInputId}' placeholder='Введите причину отмены'></textarea>";
            echo "<button onclick='cancelRecord({$record['id']}, \"{$reasonInputId}\")'>Отменить</button>";
            echo "<button onclick='confirmRecord({$record['id']})'>Подтвердить</button>";
            echo "</div>";
        }
        ?>
    </div>

    <h3>Изменение категорий</h3>
    <form method="post" action="">
        <label for="categorySelect">Выберите категорию:</label>
        <select id="categorySelect" name="category_id">
            <?php
            $categories = getCategories($pdo);
            foreach ($categories as $category) {
                echo "<option value=\"{$category['id']}\">{$category['name']}</option>";
            }
            ?>
        </select>
        <label for="newNameInput">Введите новое название:</label>
        <input type="text" id="newNameInput" name="new_name" required>
        <input type="hidden" name="action" value="updateCategory">
        <button type="submit">Обновить категорию</button>
    </form>

    <h3>Добавление новой категории</h3>
    <form method="post" action="">
        <label for="newCategoryInput">Введите название новой категории:</label>
        <input type="text" id="newCategoryInput" name="new_category" required>
        <input type="hidden" name="action" value="addCategory">
        <button type="submit">Добавить категорию</button>
    </form>

    <?php
    echo $categories_msg;
    ?>
    
    <h3>Добавление новой фотосессии</h3>

    <form action="process_add_work.php" method="post" enctype="multipart/form-data">
        <label for="category">Категория:</label>
        <select id="category" name="category_id" required>
            <?php
            // Получение списка категорий
            $categories = getCategories($pdo);
            foreach ($categories as $category) {
                echo "<option value='{$category['id']}'>{$category['name']}</option>";
            }
            ?>
        </select><br>

        <label for="name">Название:</label>
        <input type="text" id="name" name="name" required><br>

        <label for="photoshoot_date">Дата фотосессии:</label>
        <input type="datetime-local" id="photoshoot_date" name="photoshoot_date" required><br>

        <label for="photographer">Фотограф:</label>
        <select id="photographer" name="photographer_id" required>
            <?php
            // Получение списка фотографов
            $photographers = getPhotographers($pdo);
            foreach ($photographers as $photographer) {
                echo "<option value='{$photographer['id']}'>{$photographer['name']}</option>";
            }
            ?>
        </select><br>

        <label for="image_upload">Загрузить изображения:</label>
        <input type="file" id="image_upload" name="image_upload[]" multiple accept="image/*"><br>

        <input type="submit" value="Добавить фотосессию">
    </form>
    <h3>Удаление фотосесии</h3>
    <form action="process_delete_work.php" method="post" onsubmit="return confirm('Вы уверены, что хотите удалить фотосессию?');">
    <label for="workToDelete">Выберите фотосессию для удаления:</label>
    <select id="workToDelete" name="work_id" required>
        <?php
        // Получение списка фотосессий из базы данных
        $works = getWorks($pdo);

        // Вывод каждой фотосессии в виде опции в выпадающем списке
        foreach ($works as $work) {
            echo "<option value='{$work['id']}'>{$work['name']}</option>";
        }
        ?>
    </select>
    <button type="submit">Удалить фотосессию</button>
</form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // При загрузке страницы устанавливаем значение фильтра из параметра URL
            var statusFilter = getParameterByName('status');
            if (statusFilter !== null) {
                document.getElementById('statusFilter').value = statusFilter;
            }
        });

        function applyStatusFilter() {
            var statusFilter = document.getElementById('statusFilter').value;
            window.location.href = 'admin.php?status=' + encodeURIComponent(statusFilter);
        }

        function cancelRecord(recordId, reasonInputId) {
            var reason = document.getElementById(reasonInputId).value;

            if (!reason.trim()) {
                alert('Введите причину отмены.');
                return;
            }

            updateRecordStatus(recordId, 'отменён', reason);
        }

        function confirmRecord(recordId) {
            updateRecordStatus(recordId, 'подтверждён');
        }

        function updateRecordStatus(recordId, status, reason) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'process_update_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            // Формируем данные для отправки
            var data = 'record_id=' + encodeURIComponent(recordId);
            data += '&status=' + encodeURIComponent(status);
            data += '&reason=' + encodeURIComponent(reason);

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

        function getParameterByName(name, url) {
            if (!url) url = window.location.href;
            name = name.replace(/[\[\]]/g, '\\$&');
            var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        }
    </script>
</body>
</html>