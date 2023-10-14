<?
session_start();

if (isset($_SESSION['teacher_id'])) {
    // Сессия не активна, перенаправляем на страницу авторизации
    header("Location: teacher-dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход для преподавателей</title>
    <!-- Подключение стилей Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">
    <!-- Подключение стилей Toastify -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- Стили CSS -->
    <style>
        .container {
            margin-top: 5%;
        }
        .btn-block {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <h2 class="text-center">Вход для преподавателей</h2>
                <form id="teacher-login-form">
                    <div class="form-group">
                        <label for="email">Логин:</label>
                        <input type="text" class="form-control" id="login" name="login" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Пароль:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Войти</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Подключение библиотеки Bootstrap (JS) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Подключение библиотеки Toastify -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const teacherLoginForm = document.getElementById('teacher-login-form');

            teacherLoginForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const login = document.getElementById('login').value;
                const password = document.getElementById('password').value;

                // Отправка AJAX-запроса на сервер для аутентификации
                fetch('teacher-login-handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `login=${encodeURIComponent(login)}&password=${encodeURIComponent(password)}`,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Перенаправление на страницу панели управления
                        window.location.href = 'teacher-dashboard.php';
                    } else {
                        // Отображение ошибки с помощью Toastify
                        Toastify({
                            text: data.message,
                            backgroundColor: 'red',
                            duration: 3000
                        }).showToast();
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                });
            });
        });
    </script>
</body>
</html>
