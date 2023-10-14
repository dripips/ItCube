// save_attendance.php

<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['teacher_id'])) {
    // Получите данные из запроса
    $studentId = $_POST['student_id'];
    $date = $_POST['date'];
    $status = $_POST['status'];

    // Подключение к базе данных и выполнение запроса
    require_once('../data/config.php');
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Проверить, существует ли запись о посещаемости для данного студента и даты
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE student_id = :studentId AND date = :date");
    $checkStmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
    $checkStmt->bindParam(':date', $date, PDO::PARAM_STR);
    $checkStmt->execute();
    $count = $checkStmt->fetchColumn();

    if ($count > 0) {
        // Если запись существует, то удаляем её
        $deleteStmt = $pdo->prepare("DELETE FROM attendance WHERE student_id = :studentId AND date = :date");
        $deleteStmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
        $deleteStmt->bindParam(':date', $date, PDO::PARAM_STR);
        $deleteStmt->execute();
    } else {
        // Если запись не существует, то создаем её
        $insertStmt = $pdo->prepare("INSERT INTO attendance (student_id, date, status) VALUES (:studentId, :date, :status)");
        $insertStmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
        $insertStmt->bindParam(':date', $date, PDO::PARAM_STR);
        $insertStmt->bindParam(':status', $status, PDO::PARAM_INT);
        $insertStmt->execute();
    }

    // Отправьте ответ клиенту (может быть пустым, если не требуется возвращать какие-либо данные)
    echo "success";
} else {
    // Возвращаем ошибку, если не выполнены необходимые условия (выход пользователя и т. д.)
    http_response_code(403);
    echo "Access Denied";
}
?>
