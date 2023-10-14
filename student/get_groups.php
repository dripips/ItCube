<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Подключение к базе данных
session_start();

require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_GET['direction_id']) && is_numeric($_GET['direction_id'])) {
    $directionId = $_GET['direction_id'];

    // Запрос для получения списка групп на основе выбранного направления
    $query = $pdo->prepare('SELECT id, name FROM groups WHERE direction_id = :directionId');
    $query->bindParam(':directionId', $directionId, PDO::PARAM_INT);
    $query->execute();
    $groups = $query->fetchAll(PDO::FETCH_ASSOC);

    // Возвращаем данные в формате JSON
    header('Content-Type: application/json');
    echo json_encode($groups);
} else {
    echo json_encode([]);
}
?>
