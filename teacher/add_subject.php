<?php
session_start();

// Проверяем, установлена ли сессия и есть ли в ней информация о пользователе
if (!isset($_SESSION['teacher_id'])) {
    // Сессия не активна, возвращаем ошибку
    http_response_code(403); // Запрещено
    exit();
}

// Если сессия активна, получаем ID преподавателя
$teacherId = $_SESSION['teacher_id'];

// Проверяем, что запрос был отправлен методом POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из POST-запроса
    $subjectName = $_POST['subject_name'];
    $directionId = $_POST['direction_id'];

    // Подключение к базе данных с использованием PDO и конфигурации из config.php
    require_once('../data/config.php');
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Вставляем новую тему в базу данных
    $stmt = $pdo->prepare("INSERT INTO subjects (name, direction_id) VALUES (:subjectName, :directionId)");
    $stmt->bindParam(':subjectName', $subjectName, PDO::PARAM_STR);
    $stmt->bindParam(':directionId', $directionId, PDO::PARAM_INT);

    try {
        $stmt->execute();
        // Возвращаем успешный статус
        http_response_code(200); // OK
        echo "Тема успешно добавлена!";
    } catch (PDOException $e) {
        // Возвращаем статус ошибки и сообщение об ошибке
        http_response_code(500); // Внутренняя ошибка сервера
        echo "Ошибка при добавлении темы: " . $e->getMessage();
    }
} else {
    // Если запрос не был отправлен методом POST, возвращаем ошибку
    http_response_code(400); // Плохой запрос
    echo "Неверный метод запроса.";
}
?>
