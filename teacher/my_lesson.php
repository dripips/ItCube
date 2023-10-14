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

// Если сессия активна, получаем ID преподавателя
$teacherId = $_SESSION['teacher_id'];

// Подключение к базе данных с использованием PDO и конфигурации из config.php
require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Функция для получения списка уроков преподавателя
function getTeacherLessons($pdo, $teacherId, $search = '', $sortField = 'date_added', $sortOrder = 'DESC') {
    $query = "SELECT l.id, l.name AS lesson_name, l.date_added, s.name AS subject_name
              FROM lessons AS l
              INNER JOIN subjects AS s ON l.subject_id = s.id
              INNER JOIN directions AS d ON s.direction_id = d.id
              WHERE d.teacher_id = :teacherId";

    if (!empty($search)) {
        $query .= " AND l.name LIKE :search";
    }

    $query .= " ORDER BY $sortField $sortOrder";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);

    if (!empty($search)) {
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
    }

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Получение параметров поиска и сортировки из GET-запроса (если есть)
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$sortField = isset($_GET['sort_field']) ? $_GET['sort_field'] : 'date_added';
$sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';

// Получение списка уроков с применением параметров поиска и сортировки
$teacherLessons = getTeacherLessons($pdo, $teacherId, $searchQuery, $sortField, $sortOrder);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои уроки</title>

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
<main class="col-md-12 ms-sm-auto col-lg-12 px-md-12 text-center">
    <div class="my-5">
        <h2>Мои уроки</h2>

        <!-- Форма для поиска и сортировки уроков -->
        <form method="get" class="mb-4">
            <div class="form-row align-items-center">
                <div class="col-auto">
                    <label for="search" class="sr-only">Поиск</label>
                    <input type="text" class="form-control mb-2" id="search" name="search" placeholder="Поиск" value="<?php echo $searchQuery; ?>">
                </div>
                <div class="col-auto">
                    <label class="sr-only" for="sortField">Сортировка</label>
                    <select class="form-control mb-2" id="sortField" name="sort_field">
                        <option value="date_added" <?php if ($sortField === 'date_added') echo 'selected'; ?>>Дата добавления</option>
                        <option value="lesson_name" <?php if ($sortField === 'lesson_name') echo 'selected'; ?>>Название урока</option>
                        <!-- Добавьте другие поля сортировки по необходимости -->
                    </select>
                </div>
                <div class="col-auto">
                    <label class="sr-only" for="sortOrder">Порядок сортировки</label>
                    <select class="form-control mb-2" id="sortOrder" name="sort_order">
                        <option value="ASC" <?php if ($sortOrder === 'ASC') echo 'selected'; ?>>По возрастанию</option>
                        <option value="DESC" <?php if ($sortOrder === 'DESC') echo 'selected'; ?>>По убыванию</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary mb-2">Применить</button>
                    <a href="/teacher/add_lesson.php" class="btn btn-success mb-2">Добавить урок</a>
                </div>
            </div>
        </form>

        <!-- Таблица с уроками -->
        <table class="table">
            <thead>
                <tr>
                    <th>Название урока</th>
                    <th>Дата добавления</th>
                    <th>Тема урока</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teacherLessons as $lesson) : ?>
                    <tr>
                        <td><?php echo $lesson['lesson_name']; ?></td>
                        <td><?php echo $lesson['date_added']; ?></td>
                        <td><?php echo $lesson['subject_name']; ?></td>
                        <td>
                            <!-- Кнопки для редактирования и удаления урока -->
                            <a href="edit_lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-primary">Редактировать</a>
                            <a href="delete_lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-danger">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Подключение Bootstrap JS (необходим для некоторых компонентов) -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
