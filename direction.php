<?php
// Подключение к базе данных с использованием PDO и конфигурации из config.php
require_once('data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Проверяем, есть ли параметр id в URL
if (isset($_GET['link'])) {
    $link = $_GET['link'];

    // Запрос к базе данных для получения данных о новости по её идентификатору
    $stmt = $pdo->prepare("SELECT * FROM directions WHERE link = :link");
    $stmt->bindParam(':link', $link, PDO::PARAM_STR);
    $stmt->execute();
    $news = $stmt->fetch(PDO::FETCH_ASSOC);
    // Проверяем, найдены ли данные о новости
    if ($news) {

        $newsTitle = $news['name'];
        $newsContent = $news['description'];
    } else {
        // Если новость не найдена, можно выполнить необходимое действие, например, показать сообщение об ошибке.
        header("Location: /");
        exit;
    }
} else {
    // Если параметр id не указан в URL, можно выполнить необходимое действие, например, перенаправить пользователя на другую страницу.
    echo "Идентификатор новости не указан.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $newsTitle; ?> - IT-Куб Воронеж</title>
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
  <style media="screen">
  /* Стили для заголовка новости */
  .news-title {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 20px;
  }

  /* Стили для даты новости */
  .news-date {
    font-size: 14px;
    color: #666;
    margin-bottom: 10px;
  }

  /* Стили для контейнера новости */
  .news-container {
    padding: 20px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  /* Стили для описания новости */
  .news-description {
    font-size: 16px;
    color: #333;
    margin-bottom: 20px;
  }

  /* Стили для контента новости */
  .news-text {
    font-size: 18px;
    line-height: 1.6;
    color: #333;
    word-wrap: break-word;
  }

  /* Стили для ключевых слов новости */
  .news-keywords {
    font-size: 14px;
    font-style: italic;
    color: #777;
    margin-top: 20px;
  }

  /* Стили для изображения новости */
  .news-text img {
    max-width: 100%;
    height: auto;
    margin-bottom: 20px;
  }

  /* Стили для кнопки "Вернуться к списку новостей" */
  .back-button {
    display: inline-block;
    padding: 10px 20px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    cursor: pointer;
  }

  /* Стили при наведении на кнопку "Вернуться к списку новостей" */
  .back-button:hover {
    background-color: #0056b3;
  }
  h1 {
      font-size: 34px;
      line-height: 1.2;
      font-family: Montserrat;
      font-weight: 500;
  }
  h3 {
    font-size: 24px;
    line-height: 1.4;
    font-family: Montserrat;
    font-weight: 500;
}
  h5 {
      font-size: 17px;
      line-height: 1.4;
      font-family: Montserrat;
      font-weight: 500;
  }
  </style>
    <section class="container news-content py-5">
        <div class="row">
            <div class="col-md-12">
                <h2 class="news-title"><?php echo $newsTitle; ?></h2>
                <div class="news-text">
                    <?php echo $newsContent; ?>
                </div>
            </div>
        </div>
    </section>
    <hr>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css" />
<? require_once('main/footer.php'); ?>
  <script>
  $(document).ready(function() {
  // Ищем изображения внутри элемента #lesson-content и добавляем атрибут data-fancybox
  var images = document.querySelectorAll('.news-text img');

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
});
     </script>
</body>
</html>
