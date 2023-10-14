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

$stmt = $pdo->prepare("SELECT id, direction_id FROM groups WHERE id = :group_id");
$stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
$stmt->execute();
$groupInfo = $stmt->fetch(PDO::FETCH_ASSOC);
// var_dump($groupInfo['direction_id']);
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Обучение</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://unpkg.com/prismjs@1.29.0/themes/prism-coy.css">
</head>
<body>
  <style media="screen">
    #lesson-content {
      word-wrap: break-word;

    }
    #lesson-content img{
      max-width: 100%;
      height: auto;
      margin-bottom: 20px;
    }
  </style>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="student_dashboard.php">Панель студента</a>
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
        <div class="jumbotron text-center">
            <h1 class="display-4">Добро пожаловать, <?=$studentName?>!</h1>
        </div>


          <div class="row" id="lesson-lists">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Темы занятий</h5>
                        <div class="list-group">
                          <div id="themeButtons">
                              <!-- Тут будут кнопки с темами, загруженными через AJAX -->
                          </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Уроки</h5>
            <div class="list-group" id="lesson-list">
                <div id="lessonButtons">
                    <!-- Тут будут кнопки с уроками, загруженными через AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>
          </div>

  <div class="row">
<div class="col-md-12" id="individual-lesson-container" style="display: none;">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title" id="lesson-title"></h5>
            <div id="lesson-content">
                <!-- Сюда будет подгружаться контент урока -->
            </div>
            <button id="back-to-lessons" class="btn btn-secondary">Вернуться к урокам</button>
        </div>
    </div>
</div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/prismjs@1.29.0/components/prism-core.js" charset="utf-8"></script>
    <script src="https://unpkg.com/prismjs@1.29.0/plugins/autoloader/prism-autoloader.js" charset="utf-8"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.2.0/jquery.fancybox.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.2.0/jquery.fancybox.css" />

    <script>
    function initFancyBox() {
      // Найти все изображения внутри элемента #lesson-content
  var images = document.querySelectorAll('#lesson-content img');

  // Пройтись по всем найденным изображениям
  images.forEach(function(image) {
      // Создать новый элемент <a> и задать ему атрибуты data-fancybox и data-src
      var link = document.createElement('a');
      link.setAttribute('data-fancybox', '');
      link.setAttribute('data-src', image.getAttribute('src'));

      // Клонировать изображение и добавить его внутрь нового элемента <a>
      var clonedImage = image.cloneNode(true);
      link.appendChild(clonedImage);

      // Заменить изображение на новый элемент <a>
      image.parentNode.replaceChild(link, image);
  });

  }
        $(document).ready(function () {
            // Функция для загрузки тем занятий
            function loadThemes() {
                $.ajax({
                    type: "POST",
                    url: "get_themes.php", // Создайте этот файл для обработки запроса
                    data: {
                      direction_id: <?=$groupInfo['direction_id']?>,
                    },
                    success: function (response) {
                        $("#themeButtons").empty();
                        $.each(response, function (key, theme) {
                            $("#themeButtons").append(
                                $("<button class='list-group-item list-group-item-action'></button>")
                                    .addClass("theme-button")
                                    .attr("data-theme-id", theme.id)
                                    .text(theme.name)
                            );
                        });
                    },
                    error: function (error) {
                        console.log("Произошла ошибка при загрузке тем занятий.");
                    }
                });
            }

            // Функция для загрузки уроков по выбранной теме
            function loadLessons(themeId) {
                $.ajax({
                    type: "POST",
                    url: "get_lessons.php", // Создайте этот файл для обработки запроса
                    data: {
                        theme_id: themeId
                    },
                    success: function (response) {
                        $("#lessonButtons").empty();
                        $.each(response, function (key, lesson) {
                            $("#lessonButtons").append(
                                $("<button class='list-group-item list-group-item-action lesson-button'></button>")
                                    .addClass("lesson-button")
                                    .attr("data-lesson-id", lesson.id)
                                    .text(lesson.name)
                            );
                        });
                    },
                    error: function (error) {
                        console.log("Произошла ошибка при загрузке уроков.");
                    }
                });
            }

            // Обработчик клика по кнопке темы
            $(document).on("click", ".theme-button", function () {
                var themeId = $(this).data("theme-id");
                loadLessons(themeId);
            });

            // Обработчик клика по кнопке урока
            $(document).ready(function () {
              // Show individual lesson view and hide lesson list when a lesson button is clicked
              $('#lessonButtons').on('click', '.lesson-button', function () {
                  var lessonId = $(this).data('lesson-id');
                  var lessonTitle = $(this).text();

                  $.ajax({
                      type: 'POST',
                      url: 'get_lesson_content.php',
                      data: { lesson_id: lessonId },
                      success: function (response) {
                          //console.log('Response:', response[0]['content']); // Log the response to the console
                          $('#lesson-title').text(lessonTitle);
                          $('#lesson-content').html(response[0]['content']);
                          Prism.highlightAll();
                          initFancyBox();
                          // Toggle visibility
                          $('#lesson-lists').hide();
                          $('#individual-lesson-container').show();
                      },
                      error: function (error) {
                          console.error('Error fetching lesson content:', error);
                      }
                  });
              });

              // Show lesson list and hide individual lesson view when "Вернуться к урокам" button is clicked
              $('#back-to-lessons').click(function () {
                  $('#lesson-lists').show();
                  $('#individual-lesson-container').hide();
              });
          });



            // Загрузите темы при загрузке страницы
            loadThemes();
        });
    </script>
</body>
</html>
