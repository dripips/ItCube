<?php
session_start();

// Проверяем, установлена ли сессия и есть ли в ней информация о пользователе
if (!isset($_SESSION['teacher_id'])) {
    // Сессия не активна, перенаправляем на страницу авторизации
    header("Location: teacher_login.php");
    exit();
}

// Если сессия активна, получаем ID пользователя
$teacherId = $_SESSION['teacher_id'];

// Подключение к базе данных с использованием PDO и конфигурации из config.php
require_once('../data/config.php');

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получение имени преподавател
    $stmt = $pdo->prepare("SELECT first_name FROM teachers WHERE id = :teacherId");
    $stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
    $stmt->execute();

    $teacherName = "";
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $teacherName = $row['first_name'];
    }

} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель преподавателя</title>

    <!-- Подключение Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Ваши собственные стили -->
    <style>
        /* Ваши стили могут быть добавлены здесь */
    </style>
</head>
<body>

<!-- Верхняя панель навигации -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">Панель преподавателя</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="/logout.php">Выход</a>
            </li>
        </ul>
    </div>
</nav>

<!-- Боковая панель навигации -->
<div class="container-fluid">
    <div class="row">

        <!-- Основное содержимое -->
        <main class="col-md-12 ms-sm-auto col-lg-12 px-md-12 text-center">
            <div class="my-5">
                <?php
                echo "<h2>Добро пожаловать, $teacherName!</h2>";
                ?>

                <p>Выберите действие:</p>
                <div class="row justify-content-center">
                    <div class="col-md-4 mb-4">
                        <a href="/teacher/directions.php" class="btn btn-primary btn-lg btn-block">Направления</a>
                    </div>
                    <div class="col-md-4 mb-4">
                        <a href="/teacher/group.php" class="btn btn-primary btn-lg btn-block">Группы</a>
                    </div>
                </div>
                <div class="row justify-content-center">
                  <div class="col-md-4 mb-4">
                      <a href="/teacher/it_news.php" class="btn btn-primary btn-lg btn-block">Новости куба</a>
                  </div>
                    <div class="col-md-4 mb-4">
                        <a href="/teacher/journal.php" class="btn btn-primary btn-lg btn-block">Журналы посещаемости</a>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-4 mb-4">
                        <a href="/teacher/subject.php" class="btn btn-primary btn-lg btn-block">Темы занятий</a>
                    </div>
                    <div class="col-md-4 mb-4">
                        <a href="/teacher/my_lesson.php" class="btn btn-primary btn-lg btn-block">Мои уроки</a>
                    </div>
                </div>
                <div class="row justify-content-center">
                  <div class="col-md-4 mb-4">
                      <a href="/teacher/edit_profile.php" class="btn btn-primary btn-lg btn-block">Редактировать профиль</a>
                  </div>
                    <div class="col-md-4 mb-4">
                        <a href="/teacher/tg_settings.php" class="btn btn-primary btn-lg btn-block">Настройки чата</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Подключение Bootstrap JS (необходим для некоторых компонентов) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Ваши собственные скрипты -->
<script>
    // Здесь могут быть ваши скрипты JavaScript
</script>

</body>
</html>
