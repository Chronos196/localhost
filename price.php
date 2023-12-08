<?php 
include('db.php');
include("header.php");

function getPhotographerDropdown($pdo) {
    // Запрос для получения данных о фотографах
    $photographersQuery = "SELECT * FROM photographers";
    $photographersResult = $pdo->query($photographersQuery);

    // Строка для хранения HTML-кода выпадающего списка
    $dropdown = '<label for="photographer">Выберите фотографа:</label>';
    $dropdown .= '<select name="photographer" class="photographer-dropdown">';

    if ($photographersResult) {
        while ($photographer = $photographersResult->fetch(PDO::FETCH_ASSOC)) {
            // Добавляем каждого фотографа в виде опции
            $dropdown .= '<option value="' . $photographer['id'] . '">' . $photographer['name'] . '</option>';
        }
    }

    $dropdown .= '</select>';

    return $dropdown;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Прайс</title>
</head>
<body>
    <h2>Прайс</h2>
    <div class="price-cards">
        <?php
        $query = "SELECT p.*, c.name as category_name
        FROM prices p
        JOIN categories c ON p.category_id = c.id";
        
        $result = $pdo->query($query);

        if ($result) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="price-card">';
                echo '<img src="' .$row['photo_filename'] . '" alt="Пример фото">';
                echo '<p>' . $row['category_name'] . '</p>';
                echo '<h3>' . $row['name'] . '</h3>';
                echo '<p>' . nl2br($row['description']) . '</p>';
                echo '<p>' . number_format($row['price'], 0, ',', ' ') . ' руб</p>';
                echo getPhotographerDropdown($pdo);
                echo '<button class="recordButton" onclick="redirectToRecord(' . $row['id'] . ')">Записаться</button>';
                echo '</div>';
            }
        } 
        ?>
    </div>
    <script>
        function redirectToRecord(priceId) {
            var photographerId = document.querySelector('.photographer-dropdown').value;
            
            var redirectUrl = "record.php?photographer=" + photographerId + "&price=" + priceId;

            window.location.href = redirectUrl;
        }
    </script>
</body>
</html>