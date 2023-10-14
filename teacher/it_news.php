<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Проверяем, установлена ли сессия и есть ли в ней информация о пользователе
if (!isset($_SESSION['teacher_id'])) {
    // Сессия не активна, перенаправляем на страницу авторизации
    header("Location: teacher_login.php");
    exit();
}

// Подключение к базе данных с использованием PDO и конфигурации из config.php
require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Получение списка всех новостей
function getAllNews($pdo) {
    $query = "SELECT * FROM news ORDER BY date_added DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Функция для удаления новости по ID
function deleteNews($pdo, $newsId) {
    $query = "DELETE FROM news WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $newsId, PDO::PARAM_INT);
    $stmt->execute();
}

// Получение списка всех новостей
$newsList = getAllNews($pdo);

// Обработка удаления новости
if (isset($_GET['delete_news_id'])) {
    $newsId = $_GET['delete_news_id'];
    deleteNews($pdo, $newsId);
    header("Location: it_news.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT-Куб Воронеж - Новости</title>

    <!-- Подключение Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<!-- Верхняя панель навигации -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="/teacher/teacher-dashboard.php">Панель преподавателя</a>
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

<!-- Основное содержимое -->
<main class="container my-5">
    <h2 class="mb-4">Новости</h2>
    <a href="/teacher/add_news.php" class="btn btn-success mb-2">Добавить новость</a>
    <table class="table">
        <thead>
            <tr>
                <th>Заголовок</th>
                <th>Дата добавления</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($newsList as $news) : ?>
                <tr>
                    <td><?php echo $news['title']; ?></td>
                    <td><?php echo $news['date_added']; ?></td>
                    <td>
                        <a href="edit_news.php?edit_news_id=<?php echo $news['id']; ?>" class="btn btn-primary">Редактировать</a>
                        <a href="it_news.php?delete_news_id=<?php echo $news['id']; ?>" class="btn btn-danger" onclick="return confirm('Вы уверены, что хотите удалить эту новость?')">Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<!-- Подключение Bootstrap JS (необходим для некоторых компонентов) -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
