<?
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['student_id'])) {
    // Сессия не активна, перенаправляем на страницу авторизации
    header("Location: student.php");
    exit();
}

// Подключение к базе данных (используйте свои данные подключения)
require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Получите идентификатор студента из сессии (предположим, что он уже установлен)
$studentId = $_SESSION['student_id'];

// Запрос для получения имени студента
$stmt = $pdo->prepare("SELECT full_name, group_id FROM students WHERE id = :studentId");
$stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
$stmt->execute();
$studentInfo = $stmt->fetch(PDO::FETCH_ASSOC);

$studentName = $studentInfo['full_name'];
$group_id = $studentInfo['group_id'];
try {
  $stmt = $pdo->prepare("SELECT * FROM tg_settings WHERE group_id = :group_id");
  $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
  $stmt->execute();
  $tgInfo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Студенческая панель</title>

    <!-- Подключение Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Подключение ваших стилей CSS -->
    <link rel="stylesheet" href="styles.css">

    <!-- Подключение jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>

    <!-- Подключение Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Подключение вашего JavaScript -->
    <script src="student_dashboard.js"></script>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <a class="navbar-brand" href="#">Панель студента</a>
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
    <div class="container mt-5">
        <!-- Шапка с приветствием -->
        <div class="jumbotron text-center">
            <h1 class="display-4">Добро пожаловать, <?=$studentName?>!</h1>
        </div>

        <!-- Меню -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Меню</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><a href="go_lesson.php">Перейти к обучению</a></li>
                            <li class="list-group-item"><a href="file_manager.php">Файловый менеджер</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Расписание группы -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Расписание вашей группы</h5>
                        <?php
  // Получите текущую дату
  $currentDate = date('Y-m-d');

  // Запрос для получения расписания занятий группы студента на текущую дату
  $stmt = $pdo->prepare("SELECT day_of_week, start_time, end_time, group_id FROM group_schedule WHERE group_id = :group_id");
  $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
  $stmt->execute();
  $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

  ?>

  <!-- HTML для отображения расписания -->

  <table class="table">
      <thead>
          <tr>
              <th>День недели</th>
              <th>Начало</th>
              <th>Окончание</th>
          </tr>
      </thead>
      <tbody>
          <?php foreach ($lessons as $lesson): ?>
              <tr>
                  <td>      <?php
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
echo $daysOfWeek[$lesson['day_of_week']];
                        ?></td>
                  <td><?php echo $lesson['start_time']; ?></td>
                  <td><?php echo $lesson['end_time']; ?></td>
              </tr>
          <?php endforeach; ?>
      </tbody>
  </table>

                    </div>
                </div>
            </div>
              <div class="col-md-12">
                 <br>
                  <div class="card">
                      <div class="card-body">
                          <h5 class="card-title">Вступите в наш чат</h5>
                          <?php if (!$tgInfo) {
                            echo "Ваш преподаватель еще не внес ссылку на чат. <br> Вы можете ему напомнить.";
                          } else {
                           ?>
                            <img src="<?=$tgInfo['qr_code_image_path']?>" alt="" width="25%">
                            <div class="form-group">
                              <label for="email">Ссылка:</label>
                              <input type="text" class="form-control" value="<?=$tgInfo['connection_link']?>" readOnly="">
                            </div>

                            <a href="<?=$tgInfo['connection_link']?>" class="btn btn-primary btn-lg btn-block" style="width:100%">Вступить</a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</body>
</html>
