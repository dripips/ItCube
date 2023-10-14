<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация студента</title>

    <!-- Подключение Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Подключение Toastify CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Регистрация студента</div>
                    <div class="card-body">
                        <form id="registrationForm">
                          <div class="form-group">
  <label for="teacher">Выберите преподавателя:</label>
  <select class="form-control" id="teacher" name="teacher" required>

      <!-- Опции будут добавлены динамически с использованием AJAX -->
  </select>
</div>
<div class="form-group">
  <label for="direction">Выберите направление:</label>
  <select class="form-control" id="direction" name="direction" required>
      <!-- Опции будут добавлены динамически с использованием AJAX -->
  </select>
</div>
<div class="form-group">
  <label for="group">Выберите группу:</label>
  <select class="form-control" id="group" name="group" required>
      <!-- Опции будут добавлены динамически с использованием AJAX -->
  </select>
</div>

                            <div class="form-group">
                                <label for="studentlogin">Логин студента:</label>
                                <input type="text" class="form-control" id="studentlogin" name="studentlogin" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Пароль:</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-group">
                                <label for="fullname">ФИО:</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" required>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 100%">Зарегистрироваться</button>
                        </form>
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
    $(document).ready(function() {
        // Функция для загрузки данных и обновления выпадающего списка
        function loadOptionss(selectId, url) {
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    var select = $('#' + selectId);
                    select.empty(); // Очищаем текущие опции
                    select.append($('<option>---</option>'));
                    // Добавляем полученные опции
                    $.each(data, function(key, value) {
                        select.append($('<option>').text(value.last_name + " " + value.first_name).attr('value', value.id));
                    });
                },
                error: function() {
                    console.error('Произошла ошибка при загрузке данных.');
                }
            });
        }
        function loadOptions(selectId, url) {
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    var select = $('#' + selectId);
                    select.empty(); // Очищаем текущие опции
                    select.append($('<option>---</option>'));
                    // Добавляем полученные опции
                    $.each(data, function(key, value) {
                        select.append($('<option>').text(value.name).attr('value', value.id));
                    });
                },
                error: function() {
                    console.error('Произошла ошибка при загрузке данных.');
                }
            });
        }

        // Загрузка данных для преподавателей при загрузке страницы
        loadOptionss('teacher', 'get_teachers.php');

        // Обновление данных для направления при выборе преподавателя
        $('#teacher').on('change', function() {
            var teacherId = $(this).val();
            loadOptions('direction', 'get_directions.php?teacher_id=' + teacherId);
        });

        // Обновление данных для группы при выборе направления
        $('#direction').on('change', function() {
            var directionId = $(this).val();
            loadOptions('group', 'get_groups.php?direction_id=' + directionId);
        });
    });

        // Обработчик отправки формы
        $("#registrationForm").submit(function (e) {
            e.preventDefault(); // Предотвратить отправку формы по умолчанию

            // Получить данные из формы
            var studentlogin = $("#studentlogin").val();
            var password = $("#password").val();
            var fullname = $("#fullname").val();
            var group = $('#group').val();

            // Отправить данные на сервер с использованием AJAX
            $.ajax({
                type: "POST",
                url: "student_registration_process.php", // Создайте этот файл для обработки запроса
                data: {
                    studentlogin: studentlogin,
                    password: password,
                    fullname: fullname,
                    group: group
                },
                success: function (response) {
                    // Обработка успешного ответа от сервера
                    if (response === "success") {
                        // Перенаправление на страницу после успешной регистрации
                        window.location.href = "student_dashboard.php"; // Замените на URL страницы для студентов
                    } else if (response === "errorName") {
                      Toastify({
                          text: "Ошибка такой логин уже существует",
                          duration: 3000,
                          gravity: "bottom",
                          position: "right",
                          backgroundColor: "orange"
                      }).showToast();
                    } else {
                        // Отображение уведомления об ошибке
                        Toastify({
                            text: "Ошибка при регистрации студента",
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
