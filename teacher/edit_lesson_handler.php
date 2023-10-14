<?php
session_start();

// Проверяем, установлена ли сессия и есть ли в ней информация о пользователе
if (!isset($_SESSION['teacher_id'])) {
    // Сессия не активна, возвращаем ошибку
    http_response_code(403); // Запрещено
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once('../data/config.php');
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получаем данные из формы
    $lessonId = $_POST['lesson_id'];
    $lessonName = $_POST['lesson_name'];
    $lessonContent = $_POST['lesson_content'];
    $codeType = $_POST['code_type'];
    $subjectId = $_POST['subject_id'];

    // Обновляем урок в базе данных
    $stmt = $pdo->prepare("UPDATE lessons SET name = :name, content = :content, code_type = :code_type, subject_id = :subject_id WHERE id = :id");
    $stmt->bindParam(':name', $lessonName, PDO::PARAM_STR);
    $stmt->bindParam(':content', $lessonContent, PDO::PARAM_STR);
    $stmt->bindParam(':code_type', $codeType, PDO::PARAM_STR);
    $stmt->bindParam(':subject_id', $subjectId, PDO::PARAM_INT);
    $stmt->bindParam(':id', $lessonId, PDO::PARAM_INT);

    // Выполняем запрос на обновление урока
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Урок успешно обновлен']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении урока']);
        exit;
    }
} else {
    // Обработка случая, когда запрос не является POST
    echo json_encode(['success' => false, 'message' => 'Недопустимый метод запроса']);
    exit;
}
?>
