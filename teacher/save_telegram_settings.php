<?php
session_start();

// Проверяем, установлена ли сессия и есть ли в ней информация о пользователе
if (!isset($_SESSION['teacher_id'])) {
    // Сессия не активна, перенаправляем на страницу авторизации
    header("Location: teacher_login.php");
    exit();
}

// Если сессия активна, получаем ID преподавателя
$teacherId = $_SESSION['teacher_id'];

// Подключение к базе данных с использованием PDO и конфигурации из config.php
require_once('../data/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group = $_POST['group'];
    $connectionLink = $_POST['connection_link'];

    if (isset($_FILES['qr_code'])) {
        // Обработка загрузки изображения QR-кода
        $qrCodePath = '../upload/tg/' . uniqid() . '_' .  $_FILES['qr_code']['name'];
        move_uploaded_file($_FILES['qr_code']['tmp_name'], $qrCodePath);
    }

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Вставляем данные в таблицу tg_settings
        $stmt = $pdo->prepare("INSERT INTO tg_settings (teacher_id, group_id, qr_code_image_path, connection_link) VALUES (:teacherId, :group, :qrCodePath, :connectionLink)");
        $stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
        $stmt->bindParam(':group', $group, PDO::PARAM_INT);
        $stmt->bindParam(':qrCodePath', $qrCodePath, PDO::PARAM_STR);
        $stmt->bindParam(':connectionLink', $connectionLink, PDO::PARAM_STR);
        $stmt->execute();

        // Верните успешный ответ клиенту (например, JSON с сообщением "Настройки сохранены")
        echo json_encode(["message" => "Настройки сохранены"]);
    } catch (PDOException $e) {
        // В случае ошибки верните ошибку клиенту
        echo json_encode(["error" => "Ошибка при сохранении настроек: " . $e->getMessage()]);
    }
} else {
    // Верните ошибку клиенту (например, JSON с сообщением об ошибке)
    echo json_encode(["error" => "Неверный запрос"]);
}
?>
