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
// Функция для получения направлений преподавателя
function getTeacherDirections($pdo, $teacherId) {

    $stmt = $pdo->prepare("SELECT * FROM directions WHERE teacher_id = :teacherId");
    $stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Если форма для создания направления отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['direction_name'])) {
    // Обработка данных и добавление нового направления в базу данных
    $directionName = $_POST['direction_name'];

    $stmt = $pdo->prepare("INSERT INTO directions (teacher_id, name) VALUES (:teacherId, :directionName)");
    $stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
    $stmt->bindParam(':directionName', $directionName, PDO::PARAM_STR);
    $stmt->execute();
}

// Получение списка направлений преподавателя
$teacherDirections = getTeacherDirections($pdo, $teacherId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Направления</title>

    <!-- Подключение Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Ваши собственные стили -->
    <style>
        /* Ваши стили могут быть добавлены здесь */
    </style>
</head>
<body>

<!-- Верхняя панель навигации -->
<!-- ... Код верхней панели навигации, аналогичный teacher-dashboard.php ... -->
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
        <h2>Направления</h2>

        <!-- Кнопка для открытия модального окна создания направления -->
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addDirectionModal">
            Создать направление
        </button>

        <!-- Таблица с отображением направлений -->
        <table class="table">
            <thead>
                <tr>
                    <th>Название направления</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teacherDirections as $direction) : ?>
                    <tr>
                        <td><?php echo $direction['name']; ?></td>
                        <td>
                            <!-- Кнопка для редактирования направления -->
                            <button type="button" class="btn btn-primary edit-direction" data-toggle="modal" data-target="#editDirectionModal" data-direction-id="<?php echo $direction['id']; ?>" data-direction-name="<?php echo $direction['name']; ?>">
                                Редактировать
                            </button>

                            <!-- Кнопка для удаления направления -->
                            <button type="button" class="btn btn-danger delete-direction" data-direction-id="<?php echo $direction['id']; ?>">
                                Удалить
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Модальное окно для создания направления -->
<div class="modal fade" id="addDirectionModal" tabindex="-1" role="dialog" aria-labelledby="addDirectionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="addDirectionForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDirectionModalLabel">Создать направление</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="direction_name">Название направления:</label>
                        <input type="text" class="form-control" id="direction_name" name="direction_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="submit" class="btn btn-primary">Создать</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Модальное окно для редактирования направления -->
<div class="modal fade" id="editDirectionModal" tabindex="-1" role="dialog" aria-labelledby="editDirectionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editDirectionForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDirectionModalLabel">Редактировать направление</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" name="direction_id" id="edit_direction_id">
                        <label for="edit_direction_name">Название направления:</label>
                        <input type="text" class="form-control" id="edit_direction_name" name="edit_direction_name" required>
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
    // Обработчик для кнопки создания направления
    $("#addDirectionForm").submit(function(e) {
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: "add_direction.php", // Создайте этот файл на сервере для обработки запроса
            data: $(this).serialize(),
            success: function(response) {
                // Обновите страницу или добавьте логику для обновления списка направлений на странице
                alert("Направление успешно создано!");
                location.reload(); // Перезагрузить страницу после создания
            }
        });
    });

    $(".edit-direction").click(function() {
       // Получаем ID и текущее имя направления
       var directionId = $(this).data("direction-id");
       var directionName = $(this).data("direction-name");

       // Заполняем форму редактирования данными
       $("#editDirectionForm input[name='direction_id']").val(directionId);
       $("#editDirectionForm input[name='edit_direction_name']").val(directionName);
   });

   // Обработчик для формы редактирования направления
   $("#editDirectionForm").submit(function(e) {
       e.preventDefault();

       $.ajax({
           type: "POST",
           url: "edit_direction.php",
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

    // Обработчик для кнопки удаления направления
    $(".delete-direction").click(function() {
        // Получаем ID направления
        var directionId = $(this).data("direction-id");

        if (confirm("Вы уверены, что хотите удалить это направление?")) {
            $.ajax({
                type: "POST",
                url: "delete_direction.php", // Создайте этот файл на сервере для обработки запроса удаления
                data: { direction_id: directionId },
                success: function(response) {
                    // Обновите страницу или добавьте логику для обновления списка направлений на странице
                    alert("Направление успешно удалено!");
                    location.reload(); // Перезагрузить страницу после удаления
                }
            });
        }
    });
});
</script>

</body>
</html>
