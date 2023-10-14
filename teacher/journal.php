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

// Получение списка групп преподавателя для выпадающего списка
function getTeacherGroups($pdo, $teacherId) {
    $stmt = $pdo->prepare("SELECT g.* FROM groups AS g
                           INNER JOIN directions AS d ON g.direction_id = d.id
                           WHERE d.teacher_id = :teacherId");
    $stmt->bindParam(':teacherId', $teacherId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Функция для получения журнала для группы
function getGroupJournal($pdo, $groupId, $selectedYear, $selectedMonth) {
    $result = [];

    // Получите информацию о группе из базы данных
    $groupStmt = $pdo->prepare("SELECT * FROM groups WHERE id = :groupId");
    $groupStmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
    $groupStmt->execute();
    $groupInfo = $groupStmt->fetch(PDO::FETCH_ASSOC);

    if ($groupInfo) {
        $result['groupInfo'] = $groupInfo;

        // Получите информацию о студентах группы из базы данных
        $studentsStmt = $pdo->prepare("SELECT * FROM students WHERE group_id = :groupId");
        $studentsStmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $studentsStmt->execute();
        $studentsInfo = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);
        $result['studentsInfo'] = $studentsInfo;

        // Получите информацию о занятиях группы из базы данных
        $lessonsStmt = $pdo->prepare("SELECT gs.*, TIME_FORMAT(gs.start_time, '%H:%i') AS start_time, TIME_FORMAT(gs.end_time, '%H:%i') AS end_time FROM group_schedule AS gs WHERE gs.group_id = :groupId");
        $lessonsStmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $lessonsStmt->execute();
        $allLessonsInfo = $lessonsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Фильтруем занятия для выбранных дат
        $firstDayOfMonth = date("$selectedYear-$selectedMonth-01");
        $lastDayOfMonth = date("Y-m-t", strtotime($firstDayOfMonth));
        $filteredLessonsInfo = [];

        foreach ($allLessonsInfo as $lesson) {
            $lessonDate = date('Y-m-d', strtotime($lesson['day_of_week'], strtotime($firstDayOfMonth)));
            if ($lessonDate >= $firstDayOfMonth && $lessonDate <= $lastDayOfMonth) {
                $filteredLessonsInfo[$lessonDate][] = $lesson;
            }
        }

        $result['lessonsInfo'] = $filteredLessonsInfo;
    }

    return $result;
}



// Обработка выбора группы, года и месяца
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['group_id']) && isset($_POST['selected_year']) && isset($_POST['selected_month'])) {
    $groupId = $_POST['group_id'];
    $selectedYear = $_POST['selected_year'];
    $selectedMonth = $_POST['selected_month'];

    // Получите информацию о группе и занятиях этой группы из базы данных
    $journalData = getGroupJournal($pdo, $groupId, $selectedYear, $selectedMonth);

    // Разбор данных журнала
    $groupInfo = $journalData['groupInfo'];
    $studentsInfo = $journalData['studentsInfo'];
    $lessonsInfo = $journalData['lessonsInfo'];
}
function generateMonthDates($year, $month) {
    $numDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $dates = [];

    for ($day = 1; $day <= $numDays; $day++) {
        $date = "$year-$month-$day";
        $dates[] = $date;
    }

    return $dates;
}
function getGroupDatesInMonth($pdo, $groupId, $selectedYear, $selectedMonth) {
    $result = [];

    // Определение первого и последнего дней выбранного месяца
    $firstDayOfMonth = date("$selectedYear-$selectedMonth-01");
    $lastDayOfMonth = date("Y-m-t", strtotime($firstDayOfMonth));

    // Получите информацию о днях недели и времени занятий группы из базы данных
    $stmt = $pdo->prepare("SELECT day_of_week, start_time, end_time FROM group_schedule WHERE group_id = :groupId");
    $stmt->bindParam(':groupId', $_POST['group_id'], PDO::PARAM_INT);
    $stmt->execute();
    $lessonsInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Создаем список дат внутри выбранного месяца
$numDays = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
for ($day = 1; $day <= $numDays; $day++) {
    $currentDate = "$selectedYear-$selectedMonth-$day";
    $result[] = $currentDate;
}

    // Фильтруем даты, оставляем только те, когда есть занятия
    $validDates = [];

    foreach ($result as $date) {
        $dayOfWeek = date('N', strtotime($date)); // Получаем день недели для данной даты

        foreach ($lessonsInfo as $lesson) {

            if ($lesson['day_of_week'] == $dayOfWeek) {
                // Если день недели совпадает с днем занятия, добавляем дату
                $validDates[] =  $date;
                break; // Переходим к следующей дате
            }
        }
    }

    return $validDates;
}
function getAttendanceData($pdo, $groupId, $selectedYear, $selectedMonth, $student) {
    $result = [];

    // Получите даты занятий для выбранной группы в выбранном месяце
    $groupDatesInMonth = getGroupDatesInMonth($pdo, $groupId, $selectedYear, $selectedMonth);

    // Получите данные о посещаемости из базы данных для каждой даты
    foreach ($groupDatesInMonth as $date) {
        $stmt = $pdo->prepare("SELECT status FROM attendance WHERE student_id = :studentId AND date = :date");
        $stmt->bindParam(':studentId', $student, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->execute();
        $attendanceData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Если есть данные о посещаемости, добавьте их в результат
        if ($attendanceData) {
          $result[$date] = $attendanceData['status'];
        }


    }

    return $result;

}
?>

<!DOCTYPE html>
<html lang="ru" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Журнал</title>

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

<main class="container mt-5">
    <h2>Журнал</h2>
    <form method="POST" action="journal.php">
      <div class="form-group">
    <label for="group_id">Выберите группу:</label>
    <select class="form-control" id="group_id" name="group_id">
        <?php
        // Получение списка групп преподавателя
        $teacherGroups = getTeacherGroups($pdo, $teacherId);
        foreach ($teacherGroups as $group) {
            $groupId = $group['id'];
            $groupName = $group['name'];
            // Проверяем, соответствует ли текущая группа значению group_id из POST-запроса
            $selected = ($_POST['group_id'] == $groupId) ? 'selected' : '';
            echo "<option value='$groupId' $selected>$groupName</option>";
        }
        ?>
    </select>
</div>
<div class="form-group">
    <label for="selected_year">Выберите год:</label>
    <select class="form-control" id="selected_year" name="selected_year">
        <?php
        // Создание выпадающего списка с годами
        $currentYear = date('Y');
        for ($year = 2022; $year <= $currentYear + 5; $year++) {
            // Проверяем, соответствует ли текущий год значению selected_year из POST-запроса
            $selected = ($_POST['selected_year'] == $year) ? 'selected' : '';
            echo "<option value='$year' $selected>$year</option>";
        }
        ?>
    </select>
</div>
<div class="form-group">
    <label for="selected_month">Выберите месяц:</label>
    <select class="form-control" id="selected_month" name="selected_month">
        <?php
        // Создание выпадающего списка с месяцами
        $months = [
            1 => 'Январь',
            2 => 'Февраль',
            3 => 'Март',
            4 => 'Апрель',
            5 => 'Май',
            6 => 'Июнь',
            7 => 'Июль',
            8 => 'Август',
            9 => 'Сентябрь',
            10 => 'Октябрь',
            11 => 'Ноябрь',
            12 => 'Декабрь'
        ];

        foreach ($months as $monthNumber => $monthName) {
            // Проверяем, соответствует ли текущий месяц значению selected_month из POST-запроса
            $selected = ($_POST['selected_month'] == $monthNumber) ? 'selected' : '';
            echo "<option value='$monthNumber' $selected>$monthName</option>";
        }
        ?>
    </select>
</div>

        <button type="submit" class="btn btn-primary">Показать журнал</button>
    </form>

    <?php
    // Если выбрана группа, год и месяц, отобразите журнал
    if (isset($groupId) && isset($selectedYear) && isset($selectedMonth)) {
    // Получите даты занятий для выбранной группы в выбранном месяце
    $groupDatesInMonth = getGroupDatesInMonth($pdo, $groupId, $selectedYear, $selectedMonth);

    // Здесь создайте таблицу журнала
    echo "<h3>Группа: " . $groupInfo['name'] . "</h3>";
    echo "<h4>Год: $selectedYear, Месяц: " . $months[$selectedMonth] . "</h4>";
    echo "<table class='table'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>ФИО</th>";

    // Заголовки с датами занятий
    foreach ($groupDatesInMonth as $date) {
        echo "<th>$date</th>";
    }

    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    // Здесь нужно добавить строки с ФИО студентов и данными по посещаемости
    foreach ($studentsInfo as $student) {
      $attendanceDatas = getAttendanceData($pdo, $groupId, $selectedYear, $selectedMonth, $student['id']);
    echo "<tr>";
    echo "<td>" . $student['full_name'] . "</td>";
    foreach ($groupDatesInMonth as $date) {

      $attendanceData = isset($attendanceDatas[$date]) ? $attendanceDatas[$date] : 0;

        echo "<td><input class='attendance-checkbox' type='checkbox' name='attendance[$student[id]][$date]' data-student='$student[id]' data-date='$date' " . ($attendanceData == 1 ? "checked" : "") . "/></td>";
    }

    echo "</tr>";
}

    echo "</tbody>";
    echo "</table>";
}


    ?>
</main>
<!-- Подключение библиотеки Toastify -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.11.0/toastify.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.11.0/toastify.min.js"></script>

<!-- Подключение Bootstrap JS (необходим для некоторых компонентов) -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Ваши собственные скрипты -->
<script>
function saveAttendance(studentId, date, status) {
    // Отправляем AJAX-запрос
    $.ajax({
        type: 'POST',
        url: 'save_attendance.php',
        data: {
            student_id: studentId,
            date: date,
            status: status
        },
        success: function(response) {
            // Обработка успешного ответа от сервера
            Toastify({
                text: 'Информация о посещаемости сохранена!',
                duration: 3000,
                gravity: 'bottom',
                position: 'right',
                backgroundColor: 'green'
            }).showToast();
        },
        error: function(error) {
            // Обработка ошибки
            Toastify({
                text: 'Произошла ошибка при сохранении данных.',
                duration: 3000,
                gravity: 'bottom',
                position: 'right',
                backgroundColor: 'red'
            }).showToast();
        }
    });
}

// Обработчик клика на чекбокс
$('.attendance-checkbox').on('change', function() {
    var studentId = $(this).data('student');
    var date = $(this).data('date');
    var status = this.checked ? 1 : 0; // 1 - присутствовал, 0 - отсутствовал

    // Вызываем функцию для сохранения данных
    saveAttendance(studentId, date, status);
});

</script>

</body>
</html>
