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

// Получение информации о файлах пользователя из базы данных
try {
    $stmt = $dbh->prepare("SELECT * FROM files WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Генерация списка файлов
    foreach ($files as $file) {
        echo '<div class="file-item" data-file-id="'.$file['id'].'">';
        echo '<div class="file-name">' . $file['filename'] . '</div>';
        echo '<div class="file-size">' . formatFileSize($file['file_size']) . '</div>';
        echo '<div class="download-btn">';
        echo '<a href="'.$file['filepath'].'" download="'.$file['filename'] .'" class="btn btn-success">Скачать</a>';
        echo '</div>';
        echo '<div class="delete-btn">';
        echo '<button onclick="deleteFile(' . $file['id'] . ')" class="btn btn-danger">Удалить</button>';
        echo '</div>';
        echo '<div class="edit-btn">';
        echo '<button onclick="showEditModal(' . $file['id'] . ')" class="btn btn-primary">Редактировать</button>';
        echo '</div>';
        echo '</div>';
    }
} catch (PDOException $e) {
    // Обработка ошибки запроса
    die('Error fetching files');
}

// Функция для форматирования размера файла
function formatFileSize($size)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}
?>
