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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once('../data/config.php');
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Получаем данные из формы
    $lessonName = $_POST['lesson_name'];
    $lessonContent = $_POST['lesson_content'];
    $codeType = $_POST['code_type'];
    $subjectId = $_POST['subject_id'];

    // Допустим, что у вас есть таблица `lessons` для хранения уроков
    $stmt = $pdo->prepare("INSERT INTO lessons (teacher_id, name, content, code_type, subject_id) VALUES (:teacher_id, :name, :content, :code_type, :subject_id)");
    $stmt->bindParam(':teacher_id', $_SESSION['teacher_id'], PDO::PARAM_STR);
    $stmt->bindParam(':name', $lessonName, PDO::PARAM_STR);
    $stmt->bindParam(':content', $lessonContent, PDO::PARAM_STR);
    $stmt->bindParam(':code_type', $codeType, PDO::PARAM_STR);
    $stmt->bindParam(':subject_id', $subjectId, PDO::PARAM_INT);


    // Выполняем запрос на добавление урока
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Урок успешно добавлен']);

        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении урока']);
        exit;
    }
} else {
    // Обработка случая, когда запрос не является POST
    echo json_encode(['success' => false, 'message' => 'Недопустимый метод запроса']);
    exit;
}
?>
