<?php

$host = "localhost";
$dbname = "mydatabase";
$user = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    // Установка режима обработки ошибок
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
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

function getPrices($pdo) {
    $query = "SELECT id, name FROM prices";
    $statement = $pdo->query($query);
    $prices = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $prices;
}

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