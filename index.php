<?php
session_start();

// Подключение к базе данных с использованием PDO и конфигурации из config.php
require_once('data/config.php');
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Обработка ошибки подключения к базе данных
    die('Database connection error');
}
$sql = "SELECT * FROM teachers ORDER BY last_name ASC";
$result = $pdo->query($sql);
$stmt = $pdo->prepare("SELECT * FROM news ORDER BY date_added DESC LIMIT 12");
$stmt->execute();
$newsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM teachers ORDER BY last_name ASC";
$result = $pdo->query($sql);

// Функция для получения расписания занятий
function getSchedule($pdo) {
    $daysOfWeeks = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт'];

    // Получаем уникальные временные интервалы из таблицы group_schedule
    $timesQuery = $pdo->query('SELECT DISTINCT start_time, end_time FROM group_schedule ORDER BY start_time');
    $times = $timesQuery->fetchAll(PDO::FETCH_ASSOC);

    // Создаем шапку таблицы с днями недели и временными интервалами
    echo '<table border="1">';
    echo '<tr><th>Группы</th>'; // Ячейка для названий групп

    foreach ($daysOfWeeks as $day) {
        echo '<th>' . $day . '</th>';
    }

    echo '</tr>';

    // Получаем список всех групп
    $groupsQuery = $pdo->query('SELECT * FROM groups ORDER BY direction_id ASC, created_at ASC');
    $groups = $groupsQuery->fetchAll(PDO::FETCH_ASSOC);

    // Создаем массив для отслеживания объединенных ячеек
    $mergedCells = [];

    // Выводим расписание для каждой группы
    foreach ($groups as $group) {
        echo '<tr>';
        echo '<td>' . $group['name'] . '</td>'; // Название группы

        foreach ($daysOfWeeks as $day) {
            $dayIndex = array_search($day, $daysOfWeeks) + 1;
            $scheduleQuery = $pdo->prepare('SELECT * FROM group_schedule WHERE day_of_week = :day_of_week AND group_id = :group_id ORDER BY start_time');
            $scheduleQuery->bindParam(':day_of_week', $dayIndex, PDO::PARAM_INT);
            $scheduleQuery->bindParam(':group_id', $group['id'], PDO::PARAM_INT);
            $scheduleQuery->execute();
            $schedule = $scheduleQuery->fetchAll(PDO::FETCH_ASSOC);

            if (count($schedule) > 0) {
                // Если есть расписание, объединяем ячейки и выводим время
                $colspan = count($schedule);
                $mergedCells[] = 'r' . ($dayIndex - 1) . 'c' . count($mergedCells);
                echo '<td colspan="' . $colspan . '">';
                foreach ($schedule as $index => $entry) {
                    echo $entry['start_time'] . '-' . $entry['end_time'];
                    if ($index < $colspan - 1) {
                        echo '<br>';
                    }
                }
                echo '</td>';
            } else {
                // Если нет расписания, выводим пустую ячейку
                echo '<td></td>';
            }
        }

        echo '</tr>';
    }

    // Выводим объединенные ячейки
    echo '<style>';
    foreach ($mergedCells as $cell) {
        echo 'td[data-merged-cell="' . $cell . '"] { display: none; }';
    }
    echo '</style>';

    echo '</table>';
}


?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT-Куб Воронеж</title>
    <!-- Подключение Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Подключение собственных стилей -->
    <link rel="stylesheet" href="/css.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
   <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">

</head>
<body>
  <? require_once('main/header.php'); ?>
    <!-- Содержание страницы -->
    <section class="container py-5">
        <div class="row">
            <div class="col-lg-9">
                <h2>Добро пожаловать к нам в КУБ</h2>
                <p>Бесплатное обучение в мире IT для детей. Изучите программирование, создавайте веб-сайты, разрабатывайте мобильные приложения и многое другое. Начните свой путь в IT с нами уже сегодня! Выше вы можете более подробно ознакомится с нашими направлениями.</p>
                <p>Наш IT-Куб предлагает:</p>
                <ul class="custom-list">

       <li>
           <img src="/upload/icon.png" alt="IT-Cube">
           <span class="bold-blue-text">Бесплатное обучение</span>
       </li>
       <li>
           <img src="/upload/icon.png" alt="IT-Cube">
           <span class="bold-blue-text">Современные образовательные программы</span>
       </li>
       <li>
           <img src="/upload/icon.png" alt="IT-Cube">
           <span class="bold-blue-text">Высоко квалифицированные наставников</span>
       </li>
       <li>
           <img src="/upload/icon.png" alt="IT-Cube">
           <span class="bold-blue-text">Современное оборудование</span>
       </li>
       <li>
           <img src="/upload/icon.png" alt="IT-Cube">
           <span class="bold-blue-text">Увлекательные мероприятия</span>
       </li>
       <li>
           <img src="/upload/icon.png" alt="IT-Cube">
           <span class="bold-blue-text">Индивидуальный подход</span>
       </li>
   </ul>
                <a href="#contact" class="btn btn-primary">Свяжитесь с нами</a>
            </div>
            <div class="col-lg-3">
                <img src="/upload/it-logo.png" alt="Главное изображение" class="img-fluid" />
            </div>
        </div>
    </section>
    <hr>
    <!-- Новости -->
    <section class="container news " id="news">
        <div class="row">
            <div class="col">
                <h2 class="news-title">Новости</h2>
            </div>
            <div class="col text-right">
                <a href="#" class="btn btn-primary">Читать все новости <i class="fa fa-arrow-right"></i></a>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="custom-slick-prev"><i class="fa fa-chevron-left"></i></div>
                <div class="news-container py-5">
                    <?php
                    foreach ($newsList as $news) :
                        // Обрезаем описание до 200 символов (ищем последний пробел)
                        $description = substr($news['content'], 0, 200);
                        $lastSpace = strrpos($description, ' ');
                        if ($lastSpace !== false) {
                            $description = substr($description, 0, $lastSpace);
                        }
                        $description .= '...';
                    ?>
                        <div class="news-item">
                            <?php
                            if (!empty($news['poster'])) {
                                echo '<img src="'.$news['poster'].'" alt="Изображение новости">';
                            }
                            ?>
                            <h3><?php echo $news['title']; ?></h3>
                            <p><?php echo $description; ?></p>
                            <a href="/read_news.php?id=<?=$news['id']?>" class="btn btn-primary">Читать новость <i class="fa fa-arrow-right"></i></a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="custom-slick-next"><i class="fa fa-chevron-right"></i></div>
            </div>
        </div>
    </section>

    <hr>

    <!-- Блок с преподавателями -->
    <div class="container teachers" id="team">
    <h2 class="text-center">Наши преподаватели</h2>
    <div class="row py-5">
        <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)) { ?>
            <div class="teacher col-12 col-sm-6 col-md-4 col-lg-3">
                <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['first_name'] . ' ' . $row['last_name']; ?>" class="img-fluid" />
                <h3 class="teacher-name"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></h3>
                <span class="toggle-icon plus"><span class="toggle-name-u">Описание</span> <i class="fa fa-plus" aria-hidden="true"></i></span><span class="toggle-icon minus"><i class="fa fa-minus" aria-hidden="true"></i></span>
                <div class="teacher-info">
                    <p><?php echo $row['description']; ?></p>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<hr>

      <div class="container table-responsive" id="schedule">
          <h2  class="text-center">Расписание занятий</h2>
          <div class="py-5">
            <?getSchedule($pdo);?>
          </div>

    </div>
    <hr>
    <!-- Блок с партнерами -->
<section class="container" id="partners">
    <h2 class="text-center">Наши партнеры</h2>
    <div class="row py-5">
        <!-- Партнер 1 -->
        <div class="partner col-md-6">
            <img src="/upload/partner.png" alt="Партнер 1" class="img-fluid" />
            <div class="partner-info">
                <h3 class="partner-name">Название партнера 1</h3>
                <p>Описание партнера 1...</p>
            </div>
        </div>

        <!-- Партнер 2 -->
        <div class="partner col-md-6">
            <div class="partner-info">
                <h3 class="partner-name">Название партнера 2</h3>
                <p>Описание партнера 2...</p>
            </div>
            <img src="/upload/partner.png" alt="Партнер 2" class="img-fluid" />
        </div>

        <!-- Партнер 3 -->
        <div class="partner col-md-6">
            <img src="/upload/partner.png" alt="Партнер 3" class="img-fluid" />
            <div class="partner-info">
                <h3 class="partner-name">Название партнера 3</h3>
                <p>Описание партнера 3...</p>
            </div>
        </div>

        <!-- Партнер 4 -->
        <div class="partner col-md-6">
            <div class="partner-info">
                <h3 class="partner-name">Название партнера 4</h3>
                <p>Описание партнера 4...</p>
            </div>
            <img src="/upload/partner.png" alt="Партнер 4" class="img-fluid" />
        </div>
    </div>
</section>
<hr>
    <? require_once('main/footer.php'); ?>
</body>
</html>
