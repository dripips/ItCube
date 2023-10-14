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

// Подключение к базе данных с использованием PDO и конфигурации из config.php
require_once('../data/config.php');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Панель преподавателя</title>

  <!-- Подключение Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

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

    <div class="container">
        <h2>Настройка Чата</h2>
        <form id="telegram-settings-form" enctype="multipart/form-data">
          <div class="form-group">
  <label for="group">Группа</label>
  <select class="form-control" id="group" name="group" required>
      <!-- Опция для выбора -->
      <option value="" disabled selected>Выберите группу</option>
  </select>
</div>
            <div class="form-group">
                <label for="qr_code">QR-код</label>
                <input type="file" class="form-control-file" id="qr_code" name="qr_code" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="connection_link">Ссылка на подключение</label>
                <input type="text" class="form-control" id="connection_link" name="connection_link" required>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>

    <h2>Существующие настройки чатов</h2>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Группа</th>
                <th scope="col">QR-код</th>
                <th scope="col">Ссылка на подключение</th>
                <th scope="col">Действия</th>
            </tr>
        </thead>
        <tbody id="existing-settings">
            <!-- Сюда будут добавляться строки с существующими настройками Telegram -->
        </tbody>
    </table>
</div>

<!-- Модальное окно для обновления QR-кода -->
<div class="modal fade" id="updateQRCodeModal" tabindex="-1" role="dialog" aria-labelledby="updateQRCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateQRCodeModalLabel">Обновить QR-код</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Форма для обновления QR-кода -->
                <form id="updateQRCodeForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="newQRCode">Выберите новый QR-код</label>
                        <input type="file" class="form-control-file" id="newQRCode" name="newQRCode" accept="image/*" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="updateQRCodeButton">Обновить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для изменения ссылки -->
<div class="modal fade" id="updateLinkModal" tabindex="-1" role="dialog" aria-labelledby="updateLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateLinkModalLabel">Изменить ссылку на подключение</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Форма для изменения ссылки -->
                <form id="updateLinkForm">
                    <div class="form-group">
                        <label for="newConnectionLink">Новая ссылка на подключение</label>
                        <input type="text" class="form-control" id="newConnectionLink" name="newConnectionLink" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="updateLinkButton">Сохранить</button>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script type="text/javascript">
    function updateQRCode(settingId) {
        var formData = new FormData($('#updateQRCodeForm')[0]);
        $.ajax({
            type: 'POST',
            url: 'update_qr_code.php', // Создайте этот файл для обновления QR-кода
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                // Обработка успешного обновления QR-кода
                alert('QR-код успешно обновлен.');
                $('#updateQRCodeModal').modal('hide');
                // Дополнительные действия, если необходимо
            },
            error: function (xhr, status, error) {
                // Обработка ошибки (например, вывод уведомления об ошибке)
            }
        });
    }
    function updateLink(settingId) {
        var newLink = $('#newConnectionLink').val();
        $.ajax({
            type: 'POST',
            url: 'update_connection_link.php', // Создайте этот файл для обновления ссылки
            data: { new_link: newLink },
            success: function (response) {
                // Обработка успешного обновления ссылки
                alert('Ссылка на подключение успешно изменена.');
                $('#updateLinkModal').modal('hide');
                // Дополнительные действия, если необходимо
            },
            error: function (xhr, status, error) {
                // Обработка ошибки (например, вывод уведомления об ошибке)
            }
        });
    }
              // Функция для загрузки существующих настроек Telegram
              function loadExistingSettings() {
    $.ajax({
        type: 'GET',
        url: 'load_existing_settings.php', // Создайте этот файл для загрузки существующих настроек
        success: function (data) {
            // Обработка успешного ответа
            var existingSettings = $('#existing-settings');
            existingSettings.empty(); // Очищаем существующие строки
            if (Array.isArray(data)) {
            // Добавляем строки для каждой существующей настройки
            data.forEach(function (setting) {
                var row = '<tr>' +
                    '<td>' + setting.group_name + '</td>' +
                    '<td><img src="' + setting.qr_code_image_path + '" alt="QR-код" style="max-width: 100px;"></td>' +
                    '<td>' + setting.connection_link + '</td>' +
                    '<td><button class="btn btn-danger delete-button" data-setting-id="' + setting.id + '">Удалить</button>' +
                    '<button class="btn btn-primary update-qr-code-button" data-setting-id="' + setting.id + '">Обновить QR-код</button>' +
                    '<button class="btn btn-primary update-link-button" data-setting-id="' + setting.id + '">Изменить ссылку</button></td>' +
                    '</tr>';
                existingSettings.append(row);
            });
          } else {
  // Если data не является массивом, выведите сообщение об ошибке или выполните другие действия по вашему усмотрению
  console.error('Данные не являются массивом:', data);
}
            // Добавляем обработчик события для кнопок удаления
            $('.delete-button').click(function () {
                var settingId = $(this).data('setting-id');
                deleteSetting(settingId);
            });

            // Добавляем обработчик события для кнопок обновления QR-кода
            $('.update-qr-code-button').click(function () {
                var settingId = $(this).data('setting-id');
                updateQRCode(settingId);
            });

            // Добавляем обработчик события для кнопок изменения ссылки
            $('.update-link-button').click(function () {
                var settingId = $(this).data('setting-id');
                updateLink(settingId);
            });
        },
        error: function (xhr, status, error) {
            // Обработка ошибки
        }
    });
}


              // Загрузка существующих настроек при загрузке страницы
              loadExistingSettings();

          function deleteSetting(settingId) {
              $.ajax({
                  type: 'POST',
                  url: 'delete_telegram_setting.php', // Создайте этот файл для удаления настройки
                  data: { setting_id: settingId },
                  success: function (response) {
                      // После успешного удаления обновите таблицу с существующими настройками
                      loadExistingSettings();
                  },
                  error: function (xhr, status, error) {
                      // Обработка ошибки (например, вывод уведомления об ошибке)
                  }
              });
          }
          // Ваш JavaScript-код здесь

$(document).ready(function () {
    // Обработчик события для кнопки "Обновить QR-код"


  function loadTeacherGroups() {
      $.ajax({
          type: 'GET',
          url: 'load_teacher_groups.php', // Создайте этот файл для загрузки групп
          success: function (data) {
              // Обработка успешного ответа
              var selectGroup = $('#group');
              selectGroup.empty(); // Очищаем существующие опции

              // Добавляем опции для каждой группы
              data.forEach(function (group) {
                  selectGroup.append($('<option>', {
                      value: group.id,
                      text: group.name
                  }));
              });
          },
          error: function (xhr, status, error) {
              // Обработка ошибки
          }
      });
  }

  // Загрузка групп при загрузке страницы
  loadTeacherGroups();
    $('#telegram-settings-form').submit(function (e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            type: 'POST',
            url: 'save_telegram_settings.php', // Создайте этот файл для сохранения настроек
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                alert("Успех.");
                loadExistingSettings();
            },
            error: function (xhr, status, error) {
                // Обработка ошибки (например, вывод уведомления об ошибке)
            }
        });
    });
});

    </script>
</body>
</html>
