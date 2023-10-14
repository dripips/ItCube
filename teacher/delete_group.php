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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['group_id'])) {
    $groupId = $_POST['group_id'];

    // Удаление записей о расписании этой группы из таблицы group_schedule
    $deleteScheduleStmt = $pdo->prepare("DELETE FROM group_schedule WHERE group_id = :groupId");
    $deleteScheduleStmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
    $deleteScheduleStmt->execute();

    // Удаление группы из таблицы groups
    $deleteGroupStmt = $pdo->prepare("DELETE FROM groups WHERE id = :groupId");
    $deleteGroupStmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
    $deleteGroupStmt->execute();

    // После удаления, можно выполнить дополнительные действия или редирект
    // Например, перенаправить пользователя на страницу с группами
    header("Location: group.php");
    exit();
} else {
    // Если запрос не является POST-запросом или отсутствует ID группы, перенаправьте пользователя на страницу с группами
    header("Location: group.php");
    exit();
}
?>
