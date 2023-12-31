<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

include('db.php');

// Получаем данные из POST-запроса
$recordId = isset($_POST['record_id']) ? intval($_POST['record_id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';
$reason = isset($_POST['reason']) ? $_POST['reason'] : '';

if ($recordId <= 0 || empty($status)) {
    http_response_code(400);
    exit('Bad Request');
}

// Проверка на администратора
if ($_SESSION['is_admin'] != 1) {
    http_response_code(403);
    exit('Forbidden');
}

// Обновляем статус записи в базе данных
$queryUpdateRecord = "UPDATE records SET status = :status WHERE id = :record_id";
$statementUpdateRecord = $pdo->prepare($queryUpdateRecord);
$statementUpdateRecord->bindParam(':status', $status, PDO::PARAM_STR);
$statementUpdateRecord->bindParam(':record_id', $recordId, PDO::PARAM_INT);

// Обновляем статус в таблице schedules, если запись отменена или подтверждена
$queryUpdateSchedule = "UPDATE schedules SET status = :new_status WHERE id = 
                        (SELECT schedule_id FROM records WHERE id = :record_id)";
$statementUpdateSchedule = $pdo->prepare($queryUpdateSchedule);
$statementUpdateSchedule->bindParam(':record_id', $recordId, PDO::PARAM_INT);

try {
    $pdo->beginTransaction();

    // Выполняем запросы в транзакции
    $statementUpdateRecord->execute();

    if ($status === 'отменён') {
        // Если статус "отменён", то ставим "свободен" в таблице schedules
        $newStatus = 'свободен';
        $statementUpdateSchedule->bindParam(':new_status', $newStatus, PDO::PARAM_STR);
        $statementUpdateSchedule->execute();

        // Также сохраняем причину отказа в records
        if (!empty($reason)) {
            $queryUpdateReason = "UPDATE records SET cancel_reason = :reason WHERE id = :record_id";
            $statementUpdateReason = $pdo->prepare($queryUpdateReason);
            $statementUpdateReason->bindParam(':reason', $reason, PDO::PARAM_STR);
            $statementUpdateReason->bindParam(':record_id', $recordId, PDO::PARAM_INT);
            $statementUpdateReason->execute();
        }
    } elseif ($status === 'подтверждён') {
        // Если статус "подтверждён", то ставим "занят" в таблице schedules
        $newStatus = 'занят';
        $statementUpdateSchedule->bindParam(':new_status', $newStatus, PDO::PARAM_STR);
        $statementUpdateSchedule->execute();

        // Очищаем cancel_reason
        $queryClearReason = "UPDATE records SET cancel_reason = NULL WHERE id = :record_id";
        $statementClearReason = $pdo->prepare($queryClearReason);
        $statementClearReason->bindParam(':record_id', $recordId, PDO::PARAM_INT);
        $statementClearReason->execute();
    }

    // Подтверждаем транзакцию
    $pdo->commit();

    // Успешное выполнение запросов
    http_response_code(200);
    exit('OK');
} catch (Exception $e) {
    // Ошибка при выполнении запросов
    $pdo->rollBack();
    http_response_code(500);
    exit('Internal Server Error');
}
?>
