<?php
session_start();

include('db.php');
include('header.php');

function getPhotographerName($pdo, $photographerId) {
    $query = "SELECT name FROM photographers WHERE id = :photographer_id";
    $statement = $pdo->prepare($query);
    $statement->bindParam(':photographer_id', $photographerId, PDO::PARAM_INT);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['name'] : 'Неизвестно';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Работы в категории</title>
</head>
<body>
    <?php
    include('db.php');

    // Проверка, был ли передан идентификатор категории
    if (isset($_GET['category_id'])) {
        $categoryId = intval($_GET['category_id']);

        // Получение названия категории
        $queryCategoryName = "SELECT name FROM categories WHERE id = :category_id";
        $statementCategoryName = $pdo->prepare($queryCategoryName);
        $statementCategoryName->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $statementCategoryName->execute();
        $categoryName = $statementCategoryName->fetchColumn();

        if ($categoryName) {
            echo "<h2>Работы в категории '{$categoryName}'</h2>";

            // Получение работ в выбранной категории
            $queryWorks = "SELECT id, name, photoshoot_date, photographer_id, image_folder FROM works WHERE category_id = :category_id";
            $statementWorks = $pdo->prepare($queryWorks);
            $statementWorks->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
            $statementWorks->execute();
            $works = $statementWorks->fetchAll(PDO::FETCH_ASSOC);

            // Вывод работ
            echo "<div class='category-cards'>";
            foreach ($works as $work) {
                echo "<div class='category-card'>";
                echo "<div class='category-card-info'>";
                echo "<h3>{$work['name']}</h3>";
                
                // Вывод даты фотосессии без времени
                $formattedDate = date('d-m-Y', strtotime($work['photoshoot_date']));
                echo "<p>Дата фотосессии: {$formattedDate}</p>";

                // Получаем имя фотографа
                $photographerName = getPhotographerName($pdo, $work['photographer_id']);
                echo "<p>Фотограф: {$photographerName}</p>";
                echo "</div>";
                // Получаем список файлов в папке
                $files = scandir($work['image_folder']);

                echo "<div class='category-card-photos'>";
                // Итерируем по файлам, пропускаем . и ..
                foreach ($files as $file) {
                    if ($file != "." && $file != "..") {
                        // Выводим каждое изображение
                        echo "<img src='{$work['image_folder']}/{$file}' alt='Пример фото'><br>";
                    }
                }
                echo "</div>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<p>Категория не найдена.</p>";
        }
    } else {
        echo "<p>Не передан идентификатор категории.</p>";
    }
    ?>
</body>
</html>
