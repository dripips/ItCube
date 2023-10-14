<?php
session_start();

// Подключение к базе данных с использованием PDO и конфигурации из config.php
require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Проверяем, был ли отправлен запрос методом POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array();

    // Получаем данные из запроса
    $directionId = $_POST['direction_id'];
    $newDirectionName = $_POST['edit_direction_name'];

    try {
        // Обновляем направление в базе данных
        $stmt = $pdo->prepare("UPDATE directions SET name = :newDirectionName WHERE id = :directionId");
        $stmt->bindParam(':newDirectionName', $newDirectionName, PDO::PARAM_STR);
        $stmt->bindParam(':directionId', $directionId, PDO::PARAM_INT);
        $stmt->execute();

        $response['success'] = true;
    } catch (PDOException $e) {
        $response['success'] = false;
        $response['message'] = "Ошибка при обновлении направления: " . $e->getMessage();
    }

    echo json_encode($response);
} else {
    // Если запрос не методом POST, вернуть ошибку
    header('HTTP/1.1 400 Bad Request');
    exit();
}
?>
