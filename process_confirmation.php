<?php
include('db.php');

session_start();

if (!isset($_SESSION['user_id'])) {
    // Если пользователь не авторизован, отправляем ошибку
    http_response_code(401); // Unauthorized
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получите данные из POST-запроса
    $userId = isset($_POST['user_id']) ? $_POST['user_id'] : null;
    $scheduleId = isset($_POST['schedule_id']) ? $_POST['schedule_id'] : null;
    $photographerId = isset($_POST['photographer_id']) ? $_POST['photographer_id'] : null;
    $priceId = isset($_POST['price_id']) ? $_POST['price_id'] : null;

    // Проверьте, что все необходимые данные получены
    if ($userId !== null && $scheduleId !== null && $photographerId !== null && $priceId !== null) {
        try {
            $pdo->beginTransaction();

            // Внесите данные в таблицу records
            $insertRecordQuery = "INSERT INTO records (user_id, photographer_id, schedule_id, price_id) VALUES (:user_id, :photographer_id, :schedule_id, :price_id)";
            $insertRecordStatement = $pdo->prepare($insertRecordQuery);
            $insertRecordStatement->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $insertRecordStatement->bindParam(':photographer_id', $photographerId, PDO::PARAM_INT);
            $insertRecordStatement->bindParam(':schedule_id', $scheduleId, PDO::PARAM_INT);
            $insertRecordStatement->bindParam(':price_id', $priceId, PDO::PARAM_INT);
            $insertRecordStatement->execute();

            // Измените статус в таблице schedules на 'занято'
            $updateScheduleQuery = "UPDATE schedules SET status = 'занято' WHERE id = :schedule_id";
            $updateScheduleStatement = $pdo->prepare($updateScheduleQuery);
            $updateScheduleStatement->bindParam(':schedule_id', $scheduleId, PDO::PARAM_INT);
            $updateScheduleStatement->execute();

            // Зафиксируйте изменения
            $pdo->commit();

            // Отправьте успешный ответ
            http_response_code(200);
            echo 'Запись подтверждена!';
        } catch (PDOException $e) {
            // Если произошла ошибка, откатываем транзакцию и отправляем ошибку
            $pdo->rollBack();
            http_response_code(500);
            echo 'Произошла ошибка при подтверждении записи: ' . $e->getMessage();
        }
    } else {
        // Если не все данные получены, отправьте ошибку
        http_response_code(400);
        echo 'Недостаточно данных для подтверждения записи.';
    }
} else {
    // Если запрос не является POST-запросом, отправьте ошибку
    http_response_code(405);
    echo 'Метод не разрешен.';
}
?>