<?php
session_start();

// Проверяем, установлена ли сессия и есть ли в ней информация о пользователе
if (!isset($_SESSION['teacher_id'])) {
    // Сессия не активна, возвращаем ошибку
    http_response_code(401);
    die("Сессия не активна. Пожалуйста, авторизуйтесь.");
}

// Если сессия активна, получаем ID преподавателя
$teacherId = $_SESSION['teacher_id'];

// Подключение к базе данных с использованием PDO и конфигурации из config.php
require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['direction_name'])) {
    // Обработка данных и добавление нового направления в базу данных
    $directionName = $_POST['direction_name'];

    $stmt = $pdo->prepare("INSERT INTO directions (teacher_id, name) VALUES (:teacherId, :directionName)");
    $stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
    $stmt->bindParam(':directionName', $directionName, PDO::PARAM_STR);

    if ($stmt->execute()) {
        // Ответ в случае успеха
        echo "Направление успешно создано!";
    } else {
        // Ответ в случае ошибки
        http_response_code(500);
        echo "Произошла ошибка при создании направления.";
    }
} else {
    // Некорректный запрос
    http_response_code(400);
    echo "Некорректный запрос.";
}
?>
