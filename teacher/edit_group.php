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

// Если форма для редактирования группы отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_group_name'])) {
    // Обработка данных и обновление группы в базе данных
    $groupId = $_POST['group_id'];
    $groupName = $_POST['edit_group_name'];
    $directionId = $_POST['edit_direction_id'];

    // Допустим, что данные о времени и дате занятий хранятся в таблице `group_schedule`

    // Пример запроса для обновления группы в таблице `groups`
    $updateGroupStmt = $pdo->prepare("UPDATE groups SET name = :groupName, direction_id = :directionId WHERE id = :groupId");
    $updateGroupStmt->bindParam(':groupName', $groupName, PDO::PARAM_STR);
    $updateGroupStmt->bindParam(':directionId', $directionId, PDO::PARAM_INT);
    $updateGroupStmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
    $updateGroupStmt->execute();

    // Обработка расписания занятий
    if (isset($_POST['day_of_week']) && isset($_POST['start_time']) && isset($_POST['end_time'])) {
        $daysOfWeek = $_POST['day_of_week'];
        $startTimes = $_POST['start_time'];
        $endTimes = $_POST['end_time'];

        // Удаляем существующее расписание для этой группы
        $deleteScheduleStmt = $pdo->prepare("DELETE FROM group_schedule WHERE group_id = :groupId");
        $deleteScheduleStmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $deleteScheduleStmt->execute();

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

    // После обновления группы и ее расписания, можно выполнить дополнительные действия или редирект
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

// Получение данных о группе и ее расписании
if (isset($_GET['group_id'])) {
    $groupId = $_GET['group_id'];

    // Получение данных о группе
    $groupStmt = $pdo->prepare("SELECT * FROM groups WHERE id = :groupId");
    $groupStmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
    $groupStmt->execute();
    $group = $groupStmt->fetch(PDO::FETCH_ASSOC);

    // Получение расписания занятий группы
    $scheduleStmt = $pdo->prepare("SELECT * FROM group_schedule WHERE group_id = :groupId");
    $scheduleStmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
    $scheduleStmt->execute();
    $schedule = $scheduleStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Если не указан ID группы, перенаправьте пользователя на страницу с группами
    header("Location: group.php");
    exit();
}
?>
