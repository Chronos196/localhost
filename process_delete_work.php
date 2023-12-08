<?php
session_start();

include('db.php'); // Подключение к базе данных

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $work_id = $_POST['work_id'];

    // Получение пути к папке с изображениями
    $queryGetImagePath = "SELECT image_folder FROM works WHERE id = :work_id";
    $statementGetImagePath = $pdo->prepare($queryGetImagePath);
    $statementGetImagePath->bindParam(':work_id', $work_id, PDO::PARAM_INT);
    $statementGetImagePath->execute();
    $image_folder = $statementGetImagePath->fetchColumn();

    // Удаление изображений
    if (file_exists($image_folder)) {
        $files = glob($image_folder . '/*');
        foreach ($files as $file) {
            unlink($file);
        }
        rmdir($image_folder);
    }

    // Удаление записи о фотосессии из базы данных
    $queryDeleteWork = "DELETE FROM works WHERE id = :work_id";
    $statementDeleteWork = $pdo->prepare($queryDeleteWork);
    $statementDeleteWork->bindParam(':work_id', $work_id, PDO::PARAM_INT);

    try {
        $pdo->beginTransaction();
        $statementDeleteWork->execute();
        $pdo->commit();
        echo "Фотосессия успешно удалена!";
        echo '<a href="admin.php">Вернуться на админскую панель</a>';
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Ошибка при удалении фотосессии: " . $e->getMessage();
    }
}
?>
