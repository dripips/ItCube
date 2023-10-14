<?php
session_start();
require_once '../data/config.php';

// Проверка авторизации пользователя
if (!isset($_SESSION['student_id'])) {
    die('Not authorized');
}

// Получение идентификатора пользователя из сессии
$userId = $_SESSION['student_id'];

// Получение идентификатора файла из POST параметра file_id
$fileId = $_POST['file_id'] ?? 0;

// Получение информации о файле из базы данных
try {
    $dbh = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $dbh->prepare("SELECT * FROM files WHERE id = :file_id AND user_id = :user_id");
    $stmt->bindParam(':file_id', $fileId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file) {
        die('File not found');
    }

    echo $file['filename'];
} catch (PDOException $e) {
    // Обработка ошибки запроса
    die('Error downloading file');
}
