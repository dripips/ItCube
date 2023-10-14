<?php
session_start();

// Подключение к базе данных с использованием PDO и конфигурации из config.php
require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login']) && isset($_POST['password'])) {
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Поиск студента в базе данных по логину
    $stmt = $pdo->prepare("SELECT * FROM students WHERE login = :login");
    $stmt->bindParam(':login', $login, PDO::PARAM_STR);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student && password_verify($password, $student['password'])) {
        // Авторизация успешна, создаем сессию для студента
        $_SESSION['student_id'] = $student['id'];
        echo "success";
    } else {
      echo "error";
        // Неверные учетные данные

    }
} else {
    // Некорректный запрос
    echo "error";
}
?>
