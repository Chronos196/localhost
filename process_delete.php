<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверяем, что пользователь авторизован
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        exit('Unauthorized');
    }

    include('db.php');

    // Получаем данные из запроса
    $userId = $_SESSION['user_id'];
    $recordId = isset($_POST['record_id']) ? $_POST['record_id'] : null;

    if (!$recordId) {
        http_response_code(400);
        exit('Bad Request');
    }

    try {
        $pdo->beginTransaction();

        // Получаем информацию о записи
        $recordQuery = "SELECT * FROM records WHERE id = :recordId AND user_id = :userId";
        $recordStatement = $pdo->prepare($recordQuery);
        $recordStatement->bindParam(':recordId', $recordId, PDO::PARAM_INT);
        $recordStatement->bindParam(':userId', $userId, PDO::PARAM_INT);
        $recordStatement->execute();

        $record = $recordStatement->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            http_response_code(403);
            exit('Forbidden');
        }

        // Обновляем статус в таблице schedules
        $updateScheduleQuery = "UPDATE schedules SET status = 'свободен' WHERE id = :scheduleId";
        $updateScheduleStatement = $pdo->prepare($updateScheduleQuery);
        $updateScheduleStatement->bindParam(':scheduleId', $record['schedule_id'], PDO::PARAM_INT);
        $updateScheduleStatement->execute();

        // Удаляем запись из таблицы records
        $deleteRecordQuery = "DELETE FROM records WHERE id = :recordId";
        $deleteRecordStatement = $pdo->prepare($deleteRecordQuery);
        $deleteRecordStatement->bindParam(':recordId', $recordId, PDO::PARAM_INT);
        $deleteRecordStatement->execute();

        $pdo->commit();

        echo 'Success';
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        exit('Internal Server Error');
    }
} else {
    http_response_code(405);
    exit('Method Not Allowed');
}
?>