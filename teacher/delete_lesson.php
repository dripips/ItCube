<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Проверяем, установлена ли сессия и есть ли в ней информация о пользователе
if (!isset($_SESSION['teacher_id'])) {
    // Сессия не активна, возвращаем ошибку
    http_response_code(403); // Запрещено
    exit();
}

// Подключение к базе данных (используйте свои данные подключения)
require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Получите id урока из запроса
if (isset($_GET['id'])) {
    $lessonId = $_GET['id'];

    // Подготовьте SQL-запрос для удаления урока
    $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = :lessonId");
    $stmt->bindParam(':lessonId', $lessonId, PDO::PARAM_INT);

    try {
        // Попытайтесь выполнить запрос на удаление
        $stmt->execute();

        // Успешное удаление урока
        header("Location: my_lesson.php"); // Перенаправление обратно на страницу преподавателя
        exit();
    } catch (PDOException $e) {
        // Ошибка при выполнении запроса
        echo "Ошибка при удалении урока: " . $e->getMessage();
    }
} else {
    // Не передан id урока
    echo "Не передан id урока для удаления.";
}
?>
