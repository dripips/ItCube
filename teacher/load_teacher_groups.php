<?php
session_start();

// Проверяем, установлена ли сессия и есть ли в ней информация о пользователе
if (!isset($_SESSION['teacher_id'])) {
    // Сессия не активна, перенаправляем на страницу авторизации
    header("Location: teacher_login.php");
    exit();
}

// Если сессия активна, получаем ID преподавателя
$teacherId = $_SESSION['teacher_id'];

// Подключение к базе данных с использованием PDO и конфигурации из config.php
require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("SELECT g.id, g.name, d.name AS direction_name FROM groups AS g
    INNER JOIN directions AS d ON g.direction_id = d.id
    WHERE d.teacher_id = :teacherId");
$stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
$stmt->execute();
$teacherGroups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Отправляем данные в виде JSON
header('Content-Type: application/json');
echo json_encode($teacherGroups);
?>
