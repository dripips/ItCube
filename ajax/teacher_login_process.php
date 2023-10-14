<?php
session_start();
require_once '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Запрос к базе данных для проверки логина и пароля
    $stmt = $dbh->prepare("SELECT id FROM teachers WHERE username = :username AND password = :password");
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":password", $password);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        // Авторизация успешна, сохраняем идентификатор преподавателя в сессии
        $_SESSION["teacher_id"] = $stmt->fetchColumn();
        echo "success";
    } else {
        echo "error";
    }
}
?>
