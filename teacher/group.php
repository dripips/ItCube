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
function getScheduleForGroup($pdo, $groupId) {
    $stmt = $pdo->prepare("SELECT * FROM group_schedule WHERE group_id = :groupId");
    $stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Получение списка направлений преподавателя (для выпадающего списка)
$teacherDirections = getTeacherDirections($pdo, $teacherId);

// Если форма для добавления группы отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['group_name'])) {
    // Обработка данных и добавление новой группы в базу данных
    $groupName = $_POST['group_name'];
    $directionId = $_POST['direction_id'];

    // Допустим, что данные о времени и дате занятий будут храниться в таблице `group_schedule`
    // Вам нужно будет создать эту таблицу и настроить соответствующие поля.

    // Пример запроса для добавления группы и ее расписания в таблицу `group_schedule`
    $stmt = $pdo->prepare("INSERT INTO groups (name, direction_id) VALUES (:groupName, :directionId)");
    $stmt->bindParam(':groupName', $groupName, PDO::PARAM_STR);
    $stmt->bindParam(':directionId', $directionId, PDO::PARAM_INT);
    $stmt->execute();

    // Добавьте код для добавления расписания занятий в таблицу `group_schedule`
}

// Получение списка созданных групп
$stmt = $pdo->prepare("SELECT g.id, g.name, d.name AS direction_name FROM groups AS g
    INNER JOIN directions AS d ON g.direction_id = d.id
    WHERE d.teacher_id = :teacherId");
$stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
$stmt->execute();
$teacherGroups = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Группы</title>

    <!-- Подключение Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Ваши собственные стили -->
    <style>
        /* Ваши стили могут быть добавлены здесь */
    </style>
</head>
<body>

<!-- Верхняя панель навигации -->
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
        <h2>Группы</h2>

        <!-- Кнопка для открытия модального окна создания группы -->
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addGroupModal">
            Добавить группу
        </button>

        <!-- Таблица с отображением созданных групп -->
        <table class="table">
    <thead>
        <tr>
            <th>Название группы</th>
            <th>Направление</th>
            <th>Время занятий</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($teacherGroups as $group) : ?>
            <tr>
                <td><?php echo $group['name']; ?></td>
                <td><?php echo $group['direction_name']; ?></td>
                <td>
                    <?php
                    // Массив с названиями дней недели
$daysOfWeek = array(
1 => 'Понедельник',
2 => 'Вторник',
3 => 'Среда',
4 => 'Четверг',
5 => 'Пятница',
6 => 'Суббота',
7 => 'Воскресенье'
);


                        // Здесь вы можете добавить код для вывода времени занятий для этой группы
                        // Предположим, что у вас есть функция getScheduleForGroup($pdo, $group['id'])
                        // для получения расписания занятий для группы
                        $groupSchedule = getScheduleForGroup($pdo, $group['id']);
                        foreach ($groupSchedule as $scheduleItem) {
                            echo $daysOfWeek[$scheduleItem['day_of_week']] . ": " . $scheduleItem['start_time'] . " - " . $scheduleItem['end_time'] . "<br>";
                        }
                    ?>
                </td>
                <td>
                    <!-- Кнопка для редактирования группы -->
                    <button type="button" class="btn btn-primary edit-group" data-toggle="modal" data-target="#editGroupModal" data-group-id="<?php echo $group['id']; ?>" data-group-name="<?php echo $group['name']; ?>" data-direction-id="<?php echo $group['direction_id']; ?>">
                        Редактировать
                    </button>

                    <!-- Кнопка для удаления группы -->
                    <button type="button" class="btn btn-danger delete-group" data-group-id="<?php echo $group['id']; ?>">
                        Удалить
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    </div>
</main>

<!-- Модальное окно для создания группы -->
<div class="modal fade" id="addGroupModal" tabindex="-1" role="dialog" aria-labelledby="addGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="addGroupForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addGroupModalLabel">Добавить группу</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="group_name">Название группы:</label>
                        <input type="text" class="form-control" id="group_name" name="group_name" required>
                    </div>
                    <div class="form-group">
                        <label for="direction_id">Выберите направление:</label>
                        <select class="form-control" id="direction_id" name="direction_id">
                            <?php foreach ($teacherDirections as $direction) : ?>
                                <option value="<?php echo $direction['id']; ?>"><?php echo $direction['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                      <label for="schedule">Расписание занятий:</label>
                      <div class="schedule">
                          <div class="schedule-item">
                              <select class="form-control" name="day_of_week[]">
                                  <option value="1">Понедельник</option>
                                  <option value="2">Вторник</option>
                                  <option value="3">Среда</option>
                                  <option value="4">Четверг</option>
                                  <option value="5">Пятница</option>
                                  <option value="6">Суббота</option>
                                  <option value="7">Воскресенье</option>
                              </select>
                              <input type="time" class="form-control" name="start_time[]" />
                              <input type="time" class="form-control" name="end_time[]" />
                          </div>
                          <!-- Повторяйте блоки для добавления разных дней и времени -->
                      </div>
                      <button type="button" class="btn btn-primary" id="addScheduleItem">Добавить день и время</button>
                  </div>

                    <!-- Добавьте здесь поля для выбора времени и даты занятий -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button type="submit" class="btn btn-primary">Добавить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно для редактирования группы -->
<div class="modal fade" id="editGroupModal" tabindex="-1" role="dialog" aria-labelledby="editGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editGroupForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editGroupModalLabel">Редактировать группу</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" name="group_id" id="edit_group_id">
                        <label for="edit_group_name">Название группы:</label>
                        <input type="text" class="form-control" id="edit_group_name" name="edit_group_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_direction_id">Выберите направление:</label>
                        <select class="form-control" id="edit_direction_id" name="edit_direction_id">
                            <?php foreach ($teacherDirections as $direction) : ?>
                                <option value="<?php echo $direction['id']; ?>"><?php echo $direction['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
    <label for="schedule">Расписание занятий:</label>
    <div class="schedule">
        <div class="schedule-item">
            <select class="form-control" name="day_of_week[]">
                <option value="Понедельник">Понедельник</option>
                <option value="Вторник">Вторник</option>
                <option value="Среда">Среда</option>
                <option value="Четверг">Четверг</option>
                <option value="Пятница">Пятница</option>
                <option value="Суббота">Суббота</option>
                <option value="Воскресенье">Воскресенье</option>
            </select>
            <input type="time" class="form-control" name="start_time[]" />
            <input type="time" class="form-control" name="end_time[]" />
        </div>
        <!-- Повторяйте блоки для добавления разных дней и времени -->
    </div>
    <button type="button" class="btn btn-primary" id="addScheduleItem">Добавить день и время</button>
</div>

                    <!-- Добавьте здесь поля для редактирования времени и даты занятий -->
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
$(document).ready(function () {
    // Добавление нового блока для дня и времени
    $("#addScheduleItem").click(function () {
        var newScheduleItem = `
            <div class="schedule-item">
                <select class="form-control" name="day_of_week[]">
                    <option value="1">Понедельник</option>
                    <option value="2">Вторник</option>
                    <option value="3">Среда</option>
                    <option value="4">Четверг</option>
                    <option value="5">Пятница</option>
                    <option value="6">Суббота</option>
                    <option value="7">Воскресенье</option>
                </select>
                <input type="time" class="form-control" name="start_time[]" />
                <input type="time" class="form-control" name="end_time[]" />
            </div>
        `;
        $(".schedule").append(newScheduleItem);
    });
});

$(document).ready(function() {
    // Обработчик для кнопки создания группы
    $("#addGroupForm").submit(function(e) {
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: "add_group.php", // Создайте этот файл на сервере для обработки запроса
            data: $(this).serialize(),
            success: function(response) {
                // Обновите страницу или добавьте логику для обновления списка групп на странице
                alert("Группа успешно добавлена!");
                location.reload(); // Перезагрузить страницу после создания
            }
        });
    });

    // Обработчик для кнопки редактирования группы
    $(".edit-group").click(function() {
        // Получаем ID и текущее имя группы
        var groupId = $(this).data("group-id");
        var groupName = $(this).data("group-name");
        var directionId = $(this).data("direction-id");

        // Заполняем форму редактирования данными
        $("#editGroupForm input[name='group_id']").val(groupId);
        $("#editGroupForm input[name='edit_group_name']").val(groupName);
        $("#editGroupForm select[name='edit_direction_id']").val(directionId);
    });
    $(document).ready(function() {
    // Обработчик для кнопки создания группы

    // Обработчик для кнопки редактирования группы
    $(".edit-group").click(function() {
        // Получаем ID и текущее имя группы
        var groupId = $(this).data("group-id");
        var groupName = $(this).data("group-name");
        var directionId = $(this).data("direction-id");

        // Заполняем форму редактирования данными
        $("#editGroupForm input[name='group_id']").val(groupId);
        $("#editGroupForm input[name='edit_group_name']").val(groupName);
        $("#editGroupForm select[name='edit_direction_id']").val(directionId);
    });

    // Обработчик для отправки формы редактирования группы через AJAX
    $("#editGroupForm").submit(function(e) {
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: "edit_group.php", // Создайте этот файл на сервере для обработки запроса редактирования
            data: $(this).serialize(),
            success: function(response) {
                // Обновите страницу или добавьте логику для обновления списка групп на странице
                alert("Группа успешно обновлена!");
                location.reload(); // Перезагрузить страницу после обновления
            }
        });
    });

    // Обработчик для кнопки удаления группы
    $(".delete-group").click(function() {
        // Получаем ID группы
        var groupId = $(this).data("group-id");

        if (confirm("Вы уверены, что хотите удалить эту группу?")) {
            $.ajax({
                type: "POST",
                url: "delete_group.php", // Создайте этот файл на сервере для обработки запроса удаления
                data: { group_id: groupId },
                success: function(response) {
                    // Обновите страницу или добавьте логику для обновления списка групп на странице
                    alert("Группа успешно удалена!");
                    location.reload(); // Перезагрузить страницу после удаления
                }
            });
        }
    });
});
});
</script>

</body>
</html>
