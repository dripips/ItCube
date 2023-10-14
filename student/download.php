<?php
session_start();
require_once '../data/config.php';

// Проверка авторизации пользователя
if (!isset($_SESSION['student_id'])) {
    // Пользователь не авторизован, перенаправление на страницу входа
    header('Location: ../student_dashboard.php');
    exit;
}

// Получение идентификатора пользователя из сессии
$userId = $_SESSION['student_id'];

// Получение информации о файле из базы данных
$fileId = $_GET['file_id'] ?? 0; // Идентификатор файла, переданный через GET параметр file_id

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

    $filepath = $file['filepath'];
    $filename = $file['filename'];

    // Отправка файла на скачивание
    header("Content-Disposition: attachment; filename=\"$filename\"");
    readfile($filepath);
} catch (PDOException $e) {
    // Обработка ошибки запроса
    die('Error downloading file');
}
