<?php
session_start();
require_once '../data/config.php';

// Проверка авторизации пользователя
if (!isset($_SESSION['student_id'])) {
    die('Unauthorized');
}

// Получение идентификатора пользователя из сессии
$userId = $_SESSION['student_id'];

// Подключение к базе данных
try {
    $dbh = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Обработка ошибки подключения к базе данных
    die('Database connection error');
}

// Проверка наличия параметров file_id и new_filename
if (!isset($_POST['file_id']) || !isset($_POST['new_filename'])) {
    die('File ID or new filename not provided');
}

$fileId = $_POST['file_id'];
$newFilename = $_POST['new_filename'];

// Получение информации о файле из базы данных
try {
    $stmt = $dbh->prepare("SELECT * FROM files WHERE id = :file_id AND user_id = :user_id");
    $stmt->bindParam(':file_id', $fileId, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file) {
        die('File not found');
    }

    $filepath = $file['filepath'];

    // Переименование файла на сервере
    $newFilepath = dirname($filepath) . '/' . uniqid() . '_' . $newFilename;
    rename($filepath, $newFilepath);

    // Обновление информации о файле в базе данных
    $stmt = $dbh->prepare("UPDATE files SET filename = :filename, filepath = :filepath WHERE id = :file_id");
    $stmt->bindParam(':filename', $newFilename, PDO::PARAM_STR);
    $stmt->bindParam(':filepath', $newFilepath, PDO::PARAM_STR);
    $stmt->bindParam(':file_id', $fileId, PDO::PARAM_INT);
    $stmt->execute();

    echo 'Файл успешно переименован';
} catch (PDOException $e) {
    // Обработка ошибки запроса
    die('Error editing file');
}
?>
