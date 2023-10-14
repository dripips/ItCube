<?php
session_start();

if (!isset($_SESSION['teacher_id'])) {
    http_response_code(403); // Запрещено
    exit();
}

require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $newsTitle = $_POST['news_title'];
    $newsContent = $_POST['news_content'];
    $newsPoster = $_POST['news_poster'];

    // Вставляем новость в базу данных
    $stmt = $pdo->prepare("INSERT INTO news (title, content, poster, date_added) VALUES (:title, :content, :poster, NOW())");
    $stmt->bindParam(':title', $newsTitle, PDO::PARAM_STR);
    $stmt->bindParam(':content', $newsContent, PDO::PARAM_STR);
    $stmt->bindParam(':poster', $newsPoster, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $response = ['success' => true, 'message' => 'Новость успешно добавлена.'];
    } catch (PDOException $e) {
        $response = ['success' => false, 'message' => 'Ошибка при добавлении новости: ' . $e->getMessage()];
    }

    echo json_encode($response);
} else {
    http_response_code(400); // Плохой запрос
    exit();
}
?>
