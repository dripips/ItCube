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

// Если форма для добавления группы отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['group_name'])) {
    // Обработка данных и добавление новой группы в базу данных
    $groupName = $_POST['group_name'];
    $directionId = $_POST['direction_id'];

    // Допустим, что данные о времени и дате занятий будут храниться в таблице `group_schedule`
    // Вам нужно будет создать эту таблицу и настроить соответствующие поля.

    // Пример запроса для добавления группы и ее расписания в таблицу `groups` и `group_schedule`
    $stmt = $pdo->prepare("INSERT INTO groups (name, direction_id) VALUES (:groupName, :directionId)");
    $stmt->bindParam(':groupName', $groupName, PDO::PARAM_STR);
    $stmt->bindParam(':directionId', $directionId, PDO::PARAM_INT);
    $stmt->execute();

    $groupId = $pdo->lastInsertId(); // Получаем ID только что добавленной группы

    // Обработка расписания занятий
    if (isset($_POST['day_of_week']) && isset($_POST['start_time']) && isset($_POST['end_time'])) {
        $daysOfWeek = $_POST['day_of_week'];
        $startTimes = $_POST['start_time'];
        $endTimes = $_POST['end_time'];

        // Проверка наличия одинакового количества дней недели и времени
        if (count($daysOfWeek) === count($startTimes) && count($startTimes) === count($endTimes)) {
            for ($i = 0; $i < count($daysOfWeek); $i++) {
                $dayOfWeek = $daysOfWeek[$i];
                $startTime = $startTimes[$i];
                $endTime = $endTimes[$i];

                // Добавляем запись в таблицу `group_schedule`
                $scheduleStmt = $pdo->prepare("INSERT INTO group_schedule (group_id, day_of_week, start_time, end_time) VALUES (:groupId, :dayOfWeek, :startTime, :endTime)");
                $scheduleStmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
                $scheduleStmt->bindParam(':dayOfWeek', $dayOfWeek, PDO::PARAM_STR);
                $scheduleStmt->bindParam(':startTime', $startTime, PDO::PARAM_STR);
                $scheduleStmt->bindParam(':endTime', $endTime, PDO::PARAM_STR);
                $scheduleStmt->execute();
            }
        } else {
            // Обработка ошибки: массивы имеют разную длину
            echo "Ошибка: Не совпадает количество дней недели и времени.";
        }
    }

    // После добавления группы и ее расписания, можно выполнить дополнительные действия или редирект
    // Например, перенаправить пользователя на страницу с группами
    header("Location: group.php");
    exit();
}

// Получение списка направлений преподавателя (для выпадающего списка)
function getTeacherDirections($pdo, $teacherId) {
    $stmt = $pdo->prepare("SELECT * FROM directions WHERE teacher_id = :teacherId");
    $stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Получение списка направлений преподавателя
$teacherDirections = getTeacherDirections($pdo, $teacherId);
?>
