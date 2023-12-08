<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include('db.php');

function getCategoryName($pdo, $categoryId) {
    $query = "SELECT name FROM categories WHERE id = :category_id";
    $statement = $pdo->prepare($query);
    $statement->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['name'] : 'Unknown Category';
}

// Проверка на администратора
if ($_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $category_id = $_POST['category_id'];
    $name = $_POST['name'];
    $photoshoot_date = $_POST['photoshoot_date'];
    $photographer_id = $_POST['photographer_id'];

    $category_name = getCategoryName($pdo, $category_id);
    $image_folder = "img/our works/{$category_name}/{$name}/";

    // Создаем папку, если ее нет
    if (!file_exists($image_folder)) {
        mkdir($image_folder, 0777, true);
    }

    // Обработка загрузки изображений
    if (isset($_FILES['image_upload']['name'])) {
        $errors = [];
        $file_names = [];
        $upload_dir = $image_folder;

        foreach ($_FILES['image_upload']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['image_upload']['name'][$key];
            $file_size = $_FILES['image_upload']['size'][$key];
            $file_tmp = $_FILES['image_upload']['tmp_name'][$key];
            $file_type = $_FILES['image_upload']['type'][$key];

            // Проверяем размер файла (в данном случае 5 MB)
            if ($file_size > 5 * 1024 * 1024) {
                $errors[] = 'Файл ' . $file_name . ' слишком велик. Размер не должен превышать 5 MB';
            }

            $desired_dir = $upload_dir;

            if (empty($errors)) {
                if (!is_dir($desired_dir)) {
                    mkdir($desired_dir, 0777, true);
                }
                if (move_uploaded_file($file_tmp, $desired_dir . $file_name)) {
                    $file_names[] = $file_name;
                } else {
                    $errors[] = 'Ошибка при загрузке файла ' . $file_name;
                }
            }
        }

        if (!empty($errors)) {
            echo '<div>';
            foreach ($errors as $error) {
                echo $error . '<br>';
            }
            echo '</div>';
        }

        // Дополнительно обрабатываем сохранение данных в базе данных
        if (empty($errors)) {
            // Вставка данных в таблицу works
            $query = "INSERT INTO works (category_id, name, photoshoot_date, photographer_id, image_folder) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$category_id, $name, $photoshoot_date, $photographer_id, $image_folder]);

            echo 'Фотосессия успешно добавлена!';
            echo '<a href="admin.php">Вернуться на админскую панель</a>';
        }
    }
}
?>
