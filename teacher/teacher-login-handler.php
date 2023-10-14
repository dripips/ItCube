<?php
session_start();
require_once '../data/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['login'];
    $password = $_POST['password'];

    try {
        $dbh = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Поиск пользователя с указанным username
        $stmt = $dbh->prepare("SELECT * FROM teachers WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {

            // Успешная аутентификация
            $_SESSION['teacher_id'] = $user['id'];
            echo json_encode(['success' => true]);
        } else {
            // Неверные учетные данные
            echo $user['password'];
            echo json_encode(['success' => false, 'message' => 'Неверное имя пользователя или пароль']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Произошла ошибка базы данных']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Недопустимый метод запроса']);
}
?>
