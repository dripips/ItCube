<?php
// Подключение к базе данных
require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Получить данные из POST-запроса
    $studentlogin = $_POST["studentlogin"];
    $password = $_POST["password"];
    $fullname = $_POST["fullname"];
    $group = $_POST["group"];

    // Хеширование пароля
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Запрос для вставки данных студента в базу данных
    $query = $pdo->prepare("INSERT INTO students (group_id, login, password, full_name) VALUES (:group_id, :studentlogin, :password, :fullname)");

    // Привязываем параметры
    $query->bindParam(':group_id', $group, PDO::PARAM_STR);
    $query->bindParam(':studentlogin', $studentlogin, PDO::PARAM_STR);
    $query->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
    $query->bindParam(':fullname', $fullname, PDO::PARAM_STR);

    try {
        // Выполнить запрос
        $query->execute();
        $id = $pdo->lastInsertId();
        session_start();
        $_SESSION['student_id'] = $id;
        // Возвращаем успешный статус
        echo "success";
    } catch (PDOException $e) {
        //echo $e->getMessage();
        // Если произошла ошибка, возвращаем сообщение об ошибке
        echo "errorName";
    }
} else {
    // Если запрос не является POST-запросом, возвращаем сообщение об ошибке
    echo "error";
}
?>
