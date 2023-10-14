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
    // Получаем ID настройки, для которой нужно обновить QR-код
    $settingId = $_POST['setting_id'];

    // Здесь вы можете выполнить код для обновления QR-кода, например, заменить существующий файл
    if (isset($_FILES['new_qr_code'])) {
        $newQRCodePath = 'uploads/' . $_FILES['new_qr_code']['name'];
        move_uploaded_file($_FILES['new_qr_code']['tmp_name'], $newQRCodePath);
        // Здесь вы можете сохранить новый путь к QR-коду в базе данных, связанной с настройкой
        // Примечание: вам также нужно удалить старый QR-код, если это необходимо
    }

    // Верните успешный ответ клиенту (например, JSON с сообщением "QR-код успешно обновлен")
    echo json_encode(["message" => "QR-код успешно обновлен"]);
} else {
    // Верните ошибку клиенту (например, JSON с сообщением об ошибке)
    echo json_encode(["error" => "Неверный запрос"]);
}
?>
