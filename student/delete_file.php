<?php
session_start();
require_once '../data/config.php';

// Проверка авторизации пользователя
if (!isset($_SESSION['student_id'])) {
    die('Unauthorized');
}

// Удаление файла
if (isset($_POST['file_id'])) {
    $fileId = $_POST['file_id'];

    // Получение информации о файле из базы данных
    try {
        $dbh = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $dbh->prepare("SELECT * FROM files WHERE id = :file_id");
        $stmt->bindParam(':file_id', $fileId, PDO::PARAM_INT);
        $stmt->execute();
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$file) {
            die('File not found');
        }

        $filepath = $file['filepath'];

        // Удаление файла из папки uploads
        unlink($filepath);

        // Удаление информации о файле из базы данных
        $stmt = $dbh->prepare("DELETE FROM files WHERE id = :file_id");
        $stmt->bindParam(':file_id', $fileId, PDO::PARAM_INT);
        $stmt->execute();

        // Отправка успешного JSON-ответа
        echo "Файл удален";
        exit;
    } catch (PDOException $e) {
        // Обработка ошибки запроса
        die('Error deleting file'.$fileId);
    }
}
