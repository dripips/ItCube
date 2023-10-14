<?
session_start();

if (isset($_SESSION['student_id'])) {
    // Сессия не активна, перенаправляем на страницу авторизации
    header("Location: student_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация для студентов</title>

    <!-- Подключение Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Подключение Toastify CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body>
  <style>
      body {
          display: flex;
          align-items: center;
          justify-content: center;
          margin: 0;
      }

      .welcome-container {
          text-align: center;
          margin-top: 50px;
      }
      .hexagon {
          width: 100px;
          height: 115px;
          background-color: transparent;
          margin: 0 auto;
          position: relative;
          z-index: 1;
      }

      .hexagon:before, .hexagon:after {
          content: "";
          position: absolute;
          width: 0;
          border-left: 50px solid transparent;
          border-right: 50px solid transparent;
      }

      .hexagon:before {
          top: -25px;
          border-bottom: 25px solid transparent;
          border-top: 25px solid transparent;
          border-right: 50px solid #007bff;
      }

      .hexagon:after {
          bottom: -25px;
          border-top: 25px solid transparent;
          border-bottom: 25px solid transparent;
          border-left: 50px solid #007bff;
      }

      .it-text {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          font-size: 36px;
          font-weight: bold;
          color: #007bff;
      }
  </style>
    <div class="container mt-5">
      <div class="welcome-container">
          <div class="hexagon">
              <div class="it-text">IT</div>
          </div>
          <h1 class="text-center font-weight-bold">Авторизация в личном кабинете</h1>
      </div>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Вход для студентов</div>
                    <div class="card-body">
                        <form id="loginForm">
                            <div class="form-group">
                                <label for="studentId">Логин студента:</label>
                                <input type="text" class="form-control" id="studentlogin" name="studentlogin" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Пароль:</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 100%">Войти</button>
                        </form>
                        <div class="mt-3">
                            <a href="student_registration.php" class="btn btn-success" style="width: 100%">Регистрация</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Подключение jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>

    <!-- Подключение Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Подключение Toastify JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <!-- Ваш собственный JavaScript -->
    <script>
        // Обработчик отправки формы
        $("#loginForm").submit(function (e) {
            e.preventDefault(); // Предотвратить отправку формы по умолчанию

            // Получить данные из формы
            var studentlogin = $("#studentlogin").val();
            var password = $("#password").val();

            // Отправить данные на сервер с использованием AJAX
            $.ajax({
                type: "POST",
                url: "student_login_process.php", // Создайте этот файл для обработки запроса
                data: {
                    login: studentlogin,
                    password: password
                },
                success: function (response) {
                    // Обработка успешного ответа от сервера
                    if (response === "success") {
                        // Перенаправление на страницу после успешной авторизации
                        window.location.href = "student_dashboard.php"; // Замените на URL страницы для студентов
                    } else {
                        // Отображение уведомления об ошибке
                        Toastify({
                            text: "Неверный логин студента или пароль",
                            duration: 3000,
                            gravity: "bottom",
                            position: "right",
                            backgroundColor: "red"
                        }).showToast();
                    }
                },
                error: function (error) {
                    // Обработка ошибки
                    Toastify({
                        text: "Произошла ошибка при отправке данных.",
                        duration: 3000,
                        gravity: "bottom",
                        position: "right",
                        backgroundColor: "red"
                    }).showToast();
                }
            });
        });
    </script>
</body>
</html>
