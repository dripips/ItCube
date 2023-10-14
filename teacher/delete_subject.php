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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject_id'])) {
    // Получаем ID темы из POST-запроса
    $subjectId = $_POST['subject_id'];

    // Подключение к базе данных с использованием PDO и конфигурации из config.php
    require_once('../data/config.php');
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Проверяем, принадлежит ли тема преподавателю
    $stmt = $pdo->prepare("SELECT s.id FROM subjects AS s
        INNER JOIN directions AS d ON s.direction_id = d.id
        WHERE s.id = :subjectId AND d.teacher_id = :teacherId");
    $stmt->bindParam(':subjectId', $subjectId, PDO::PARAM_INT);
    $stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
    $stmt->execute();
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($subject) {
        // Тема принадлежит преподавателю, удаляем её
        $deleteStmt = $pdo->prepare("DELETE FROM subjects WHERE id = :subjectId");
        $deleteStmt->bindParam(':subjectId', $subjectId, PDO::PARAM_INT);

        try {
            $deleteStmt->execute();
            // Возвращаем успешный статус
            http_response_code(200); // OK
            echo "Тема успешно удалена!";
        } catch (PDOException $e) {
            // Возвращаем статус ошибки и сообщение об ошибке
            http_response_code(500); // Внутренняя ошибка сервера
            echo "Ошибка при удалении темы: " . $e->getMessage();
        }
    } else {
        // Тема не принадлежит преподавателю, возвращаем ошибку доступа
        http_response_code(403); // Запрещено
        echo "У вас нет доступа к удалению этой темы.";
    }
} else {
    // Если запрос не был отправлен методом POST или отсутствует ID темы, возвращаем ошибку
    http_response_code(400); // Плохой запрос
    echo "Неверный метод запроса или отсутствует ID темы.";
}
?>
