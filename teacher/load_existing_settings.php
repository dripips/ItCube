<?php
session_start();

// Проверяем, установлена ли сессия и есть ли в ней информация о пользователе
if (!isset($_SESSION['teacher_id'])) {
    // Сессия не активна, перенаправляем на страницу авторизации
    header("Location: teacher_login.php");
    exit();
}

// Если сессия активна, получаем ID пользователя
$teacherId = $_SESSION['teacher_id'];

// Подключение к базе данных с использованием PDO и конфигурации из config.php
require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// Запрос для получения существующих настроек Telegram
$query = "SELECT s.id, g.name AS group_name, s.qr_code_image_path, s.connection_link
          FROM tg_settings AS s
          INNER JOIN groups AS g ON s.group_id = g.id
          WHERE s.teacher_id = :teacherId";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
$stmt->execute();
$settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($settings);
?>
