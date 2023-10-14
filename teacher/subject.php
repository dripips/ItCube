<?php
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

// Функция для получения списка направлений преподавателя (для выпадающего списка)
function getTeacherDirections($pdo, $teacherId) {
    $stmt = $pdo->prepare("SELECT * FROM directions WHERE teacher_id = :teacherId");
    $stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Получение списка направлений преподавателя (для выпадающего списка)
$teacherDirections = getTeacherDirections($pdo, $teacherId);

// Получение списка тем
$stmt = $pdo->prepare("SELECT s.id, s.name, d.name AS direction_name FROM subjects AS s
    INNER JOIN directions AS d ON s.direction_id = d.id
    WHERE d.teacher_id = :teacherId");
$stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
$stmt->execute();
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Темы</title>

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
        <h2>Темы</h2>

        <!-- Форма для добавления темы -->
        <form id="addSubjectForm">
            <div class="form-group">
                <label for="subject_name">Название темы:</label>
                <input type="text" class="form-control" id="subject_name" name="subject_name" required>
            </div>
            <div class="form-group">
                <label for="direction_id">Выберите направление:</label>
                <select class="form-control" id="direction_id" name="direction_id">
                    <?php foreach ($teacherDirections as $direction) : ?>
                        <option value="<?php echo $direction['id']; ?>"><?php echo $direction['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Добавить тему</button>
        </form>

        <!-- Таблица с отображением тем -->
        <table class="table">
            <thead>
                <tr>
                    <th>Название темы</th>
                    <th>Направление</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject) : ?>
                    <tr>
                        <td><?php echo $subject['name']; ?></td>
                        <td><?php echo $subject['direction_name']; ?></td>
                        <td>
                            <!-- Кнопка для редактирования темы -->
                            <button type="button" class="btn btn-primary edit-subject" data-toggle="modal" data-target="#editSubjectModal" data-subject-id="<?php echo $subject['id']; ?>" data-subject-name="<?php echo $subject['name']; ?>" data-direction-id="<?php echo $subject['direction_id']; ?>">
                                Редактировать
                            </button>

                            <!-- Кнопка для удаления темы -->
                            <button type="button" class="btn btn-danger delete-subject" data-subject-id="<?php echo $subject['id']; ?>">
                                Удалить
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Модальное окно для редактирования темы -->
<div class="modal fade" id="editSubjectModal" tabindex="-1" role="dialog" aria-labelledby="editSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editSubjectForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSubjectModalLabel">Редактировать тему</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" name="subject_id" id="edit_subject_id">
                        <label for="edit_subject_name">Название темы:</label>
                        <input type="text" class="form-control" id="edit_subject_name" name="edit_subject_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_direction_id">Выберите направление:</label>
                        <select class="form-control" id="edit_direction_id" name="edit_direction_id">
                            <?php foreach ($teacherDirections as $direction) : ?>
                                <option value="<?php echo $direction['id']; ?>"><?php echo $direction['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Подключение Bootstrap JS (необходим для некоторых компонентов) -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Ваши собственные скрипты -->
<script>
$(document).ready(function() {
    // Обработчик для кнопки добавления темы
    $("#addSubjectForm").submit(function(e) {
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: "add_subject.php", // Создайте этот файл на сервере для обработки запроса
            data: $(this).serialize(),
            success: function(response) {
                // Обновите страницу или добавьте логику для обновления списка тем на странице
                alert("Тема успешно добавлена!");
                location.reload(); // Перезагрузить страницу после создания
            }
        });
    });

    // Обработчик для кнопки редактирования темы
    $(".edit-subject").click(function() {
        // Получаем ID и текущее имя темы
        var subjectId = $(this).data("subject-id");
        var subjectName = $(this).data("subject-name");
        var directionId = $(this).data("direction-id");

        // Заполняем форму редактирования данными
        $("#editSubjectForm input[name='subject_id']").val(subjectId);
        $("#editSubjectForm input[name='edit_subject_name']").val(subjectName);
        $("#editSubjectForm select[name='edit_direction_id']").val(directionId);
    });
    $("#editSubjectModal").submit(function(e) {
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: "edit_subject.php",
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    // Обновить страницу или обновить только нужные элементы на странице
                    alert("Направление успешно отредактировано!");
                    location.reload(); // Перезагрузить страницу после редактирования
                } else {
                    alert("Ошибка при редактировании направления: " + response.message);
                }
            },
            error: function() {
                alert("Произошла ошибка при отправке запроса.");
            }
        });
    });
    // Обработчик для кнопки удаления темы
    $(".delete-subject").click(function() {
        // Получаем ID темы
        var subjectId = $(this).data("subject-id");

        if (confirm("Вы уверены, что хотите удалить эту тему?")) {
            $.ajax({
                type: "POST",
                url: "delete_subject.php", // Создайте этот файл на сервере для обработки запроса удаления
                data: { subject_id: subjectId },
                success: function(response) {
                    // Обновите страницу или добавьте логику для обновления списка тем на странице
                    alert("Тема успешно удалена!");
                    location.reload(); // Перезагрузить страницу после удаления
                }
            });
        }
    });
});
</script>

</body>
</html>
