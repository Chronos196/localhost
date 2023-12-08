<?php
session_start();

include('db.php');
include('header.php');

function get_last_photo($pdo, $category_id) {
    $query = "SELECT w.name, w.image_folder, c.name AS category_name
              FROM works w
              JOIN categories c ON w.category_id = c.id
              WHERE w.category_id = :category_id
              ORDER BY w.photoshoot_date DESC
              LIMIT 1";

    $statement = $pdo->prepare($query);
    $statement->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $statement->execute();
    
    return $statement->fetch(PDO::FETCH_ASSOC);
}

function get_last_photo_file($image_folder) {
    // Путь к папке с изображениями
    $folder_path = __DIR__ . '/' . $image_folder;

    // Получаем список файлов в папке
    $files = scandir($folder_path, SCANDIR_SORT_DESCENDING);

    // Ищем первый файл с допустимым расширением
    foreach ($files as $file) {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
            return $file;
        }
    }

    return null;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Наши работы</title>
</head>

<body>
    <h2>Категории фотосъемок</h2>

    <div class="portfolio-cards">
    <?php
    $categories = getCategories($pdo);

    foreach ($categories as $category) {
        $latestPhoto = get_last_photo($pdo, $category['id']);

        echo "<div class='portfolio-card'>";
        echo "<h3>{$category['name']}</h3>";

        if ($latestPhoto) {
            echo '<img src="' . $latestPhoto['image_folder'] . '\\' . get_last_photo_file($latestPhoto['image_folder']) . '" alt="Пример фото">';
            echo "<p>{$latestPhoto['name']}</p>";
            echo "<a href='category.php?category_id={$category['id']}'>Смотреть</a>";
        } else {
            echo "<p>Нет доступных фотографий</p>";
        }

        echo "</div>";
    }
    ?>
    </div>

</body>
</html>
