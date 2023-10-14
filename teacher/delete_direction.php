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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['direction_id'])) {
    // Получаем ID направления для удаления
    $directionId = $_POST['direction_id'];

    // Проверяем, принадлежит ли направление текущему преподавателю
    $stmt = $pdo->prepare("SELECT teacher_id FROM directions WHERE id = :directionId");
    $stmt->bindParam(':directionId', $directionId, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['teacher_id'] == $teacherId) {
        // Удаляем направление
        $deleteStmt = $pdo->prepare("DELETE FROM directions WHERE id = :directionId");
        $deleteStmt->bindParam(':directionId', $directionId, PDO::PARAM_INT);

        if ($deleteStmt->execute()) {
            // Ответ в случае успеха
            echo "Направление успешно удалено!";
        } else {
            // Ответ в случае ошибки удаления
            http_response_code(500);
            echo "Произошла ошибка при удалении направления.";
        }
    } else {
        // Направление не найдено или не принадлежит текущему преподавателю
        http_response_code(403);
        echo "У вас нет прав на удаление этого направления.";
    }
} else {
    // Некорректный запрос
    http_response_code(400);
    echo "Некорректный запрос.";
}
?>
