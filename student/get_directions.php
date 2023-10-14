<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Подключение к базе данных
session_start();

require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_GET['teacher_id']) && is_numeric($_GET['teacher_id'])) {
    $teacherId = $_GET['teacher_id'];

    // Запрос для получения списка направлений на основе выбранного преподавателя
    $query = $pdo->prepare('SELECT id, name FROM directions WHERE teacher_id = :teacherId');
    $query->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
    $query->execute();
    $directions = $query->fetchAll(PDO::FETCH_ASSOC);

    // Возвращаем данные в формате JSON
    header('Content-Type: application/json');
    echo json_encode($directions);
} else {
    echo json_encode([]);
}
?>
