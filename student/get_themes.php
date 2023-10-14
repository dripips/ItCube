<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Проверяем наличие параметра directionId в POST-запросе
if (isset($_POST['direction_id'])) {
    // Подключение к базе данных
    require_once('../data/config.php');
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $directionId = $_POST['direction_id']; // Замените на фактический ID направления.

    // SQL-запрос для получения тем, относящихся к указанному направлению
    $query = $pdo->prepare('SELECT id, name FROM subjects WHERE direction_id = :directionId');
    $query->bindParam(':directionId', $directionId, PDO::PARAM_INT);
    $query->execute();

    $themes = $query->fetchAll(PDO::FETCH_ASSOC);

    // Возвращаем данные в формате JSON
    header('Content-Type: application/json');
    echo json_encode($themes);
} else {
    // Если параметр directionId отсутствует, вернуть ошибку
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(array('error' => 'Missing directionId parameter'));
}
?>
