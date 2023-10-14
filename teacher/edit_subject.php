<?php
session_start();

// Подключение к базе данных с использованием PDO и конфигурации из config.php
require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject_id'])) {
    // Получаем данные из AJAX-запроса
    $subjectId = $_POST['subject_id'];
    $subjectName = $_POST['edit_subject_name'];
    $directionId = $_POST['edit_direction_id'];

    // Выполните SQL-запрос для обновления темы в базе данных
    $stmt = $pdo->prepare("UPDATE subjects SET name = :subjectName, direction_id = :directionId WHERE id = :subjectId");
    $stmt->bindParam(':subjectName', $subjectName, PDO::PARAM_STR);
    $stmt->bindParam(':directionId', $directionId, PDO::PARAM_INT);
    $stmt->bindParam(':subjectId', $subjectId, PDO::PARAM_INT);

    try {
        $stmt->execute();
        echo "success"; // Отправляем успешный ответ на клиентскую сторону
    } catch (PDOException $e) {
        echo "error"; // Отправляем сообщение об ошибке на клиентскую сторону
    }
}
?>
