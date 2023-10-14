<?php
session_start();

// Проверяем, установлена ли сессия и есть ли в ней информация о пользователе
if (!isset($_SESSION['teacher_id'])) {
    // Сессия не активна, перенаправляем на страницу авторизации
    header("Location: teacher_login.php");
    exit();
}

// Если сессия активна, получаем ID пользователя
$teacherId = $_SESSION['teacher_id'];

// Подключение к базе данных
require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Получаем данные преподавателя из базы данных
$query = $pdo->prepare('SELECT first_name, last_name, description, image FROM teachers WHERE id = :teacher_id');
$query->bindParam(':teacher_id', $teacherId, PDO::PARAM_INT);
$query->execute();
$teacherData = $query->fetch(PDO::FETCH_ASSOC);

$firstName = $teacherData['first_name'];
$lastName = $teacherData['last_name'];
$description = $teacherData['description'];
$image = $teacherData['image'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование профиля</title>
    <!-- Подключите стили Bootstrap и Toastify здесь -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.9.2/toastify.min.css">
</head>
<body>
  <!-- Верхняя панель навигации -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <a class="navbar-brand" href="teacher-dashboard.php">Панель преподавателя</a>
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
        <h1 class="mb-4">Редактирование профиля</h1>
        <form id="profileForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="first_name">Имя:</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $firstName; ?>">
            </div>
            <div class="form-group">
                <label for="last_name">Фамилия:</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $lastName; ?>">
            </div>
            <div class="form-group">
                <label for="description">Описание:</label>
                <textarea class="form-control" id="description" name="description" rows="4"><?php echo $description; ?></textarea>
            </div>
            <div class="form-group">
                <label for="image">Фотография профиля:</label>
                <input type="file" class="form-control-file" id="image" name="image">
            </div>
            <div class="form-group">
                <label for="password">Новый пароль:</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div>

    <!-- Подключите скрипты Bootstrap и Toastify здесь -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastify-js/1.9.2/toastify.min.js"></script>
    <script type="text/javascript">
    // Когда документ загружен
$(document).ready(function() {
  // Обработка отправки формы
  $('#profileForm').submit(function(e) {
      e.preventDefault(); // Предотвратить отправку формы

      // Создать объект FormData для передачи данных формы
      var formData = new FormData(this);

      $.ajax({
          type: 'POST',
          url: 'update_profile.php', // Укажите путь к вашему PHP-скрипту
          data: formData,
          contentType: false,
          processData: false,
          success: function(response) {
            console.log(response);
              if (response == '{"success":true}') {
                  // Если обновление прошло успешно, показать уведомление
                  Toastify({
                      text: "Профиль успешно обновлен!",
                      backgroundColor: "green",
                  }).showToast();
              } else {
                  // Если произошла ошибка, показать уведомление об ошибке
                  Toastify({
                      text: "Ошибка при обновлении профиля.",
                      backgroundColor: "red",
                  }).showToast();
              }
          },
          error: function() {
              // В случае ошибки показать уведомление об ошибке
              Toastify({
                  text: "Произошла ошибка при отправке запроса.",
                  backgroundColor: "red",
              }).showToast();
          }
      });
  });
});

    </script>
</body>
</html>
