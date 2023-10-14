<?php
session_start();
// Проверяем, установлена ли сессия и есть ли в ней информация о пользователе
if (!isset($_SESSION['teacher_id'])) {
    // Сессия не активна, перенаправляем на страницу авторизации
    die('Error');
    exit();
}

require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Получение данных из формы
$teacherId = $_SESSION['teacher_id'];
$firstName = $_POST['first_name'];
$lastName = $_POST['last_name'];
$description = $_POST['description'];
$newPassword = $_POST['password'];

// Обновление данных в базе данных
try {
    $query = $pdo->prepare('UPDATE teachers SET first_name = :first_name, last_name = :last_name, description = :description WHERE id = :teacher_id');
    $query->bindParam(':first_name', $firstName, PDO::PARAM_STR);
    $query->bindParam(':last_name', $lastName, PDO::PARAM_STR);
    $query->bindParam(':description', $description, PDO::PARAM_STR);
    $query->bindParam(':teacher_id', $teacherId, PDO::PARAM_INT);
    $query->execute();

    // Если указан новый пароль, обновляем его
    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = $pdo->prepare('UPDATE teachers SET password = :password WHERE id = :teacher_id');
        $query->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $query->bindParam(':teacher_id', $teacherId, PDO::PARAM_INT);
        $query->execute();
    }

    // Если загружено новое изображение, обновляем его
    if (!empty($_FILES['image']['name'])) {
      // Путь к загруженному изображению
$uploadedImagePath = $_FILES['image']['tmp_name'];

// Определите новый путь для сохранения сжатого изображения
$webpImagePath = '../upload/' . uniqid() . '_' . $_FILES['image']['name'] . '.webp';

// Открываем изображение с помощью библиотеки GD
$sourceImage = imagecreatefromjpeg($uploadedImagePath);

// Сохраняем изображение в формате WebP
imagewebp($sourceImage, $webpImagePath, 85); // 85 - качество, можете настроить по вашему усмотрению

// Освобождаем память
imagedestroy($sourceImage);

// Обновляем путь к изображению в базе данных
$query = $pdo->prepare('UPDATE teachers SET image = :image WHERE id = :teacher_id');
$query->bindParam(':image', $webpImagePath, PDO::PARAM_STR);
$query->bindParam(':teacher_id', $teacherId, PDO::PARAM_INT);
$query->execute();

    }

    // Успешный ответ
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Ошибка при обновлении
    echo json_encode(['success' => false]);
}
?>
