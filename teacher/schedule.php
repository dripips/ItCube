<?php
session_start();

// Проверяем, установлена ли сессия и есть ли в ней информация о пользователе
if (!isset($_SESSION['teacher_id'])) {
    // Сессия не активна, перенаправляем на страницу авторизации
    header("Location: teacher_login.php");
    exit();
}

// Если сессия активна, получаем ID преподавателя
$teacherId = $_SESSION['teacher_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Расписание занятий</title>
    <!-- Подключите ваш CSS (например, Bootstrap) -->
</head>
<body>
    <h1>Расписание занятий</h1>
    <div id="schedule">
        <!-- Здесь будет отображаться ваше расписание -->
    </div>

    <!-- Форма для добавления, редактирования и удаления групп -->
    <form id="groupForm">
        <input type="hidden" id="group_id">
        <div class="form-group">
            <label for="group_name">Название группы:</label>
            <input type="text" class="form-control" id="group_name" required>
        </div>
        <button type="submit" class="btn btn-primary">Добавить</button>
        <button type="button" class="btn btn-warning" id="updateButton">Обновить</button>
        <button type="button" class="btn btn-danger" id="deleteButton">Удалить</button>
    </form>

    <!-- Таблица для отображения и выбора групп -->
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название группы</th>
            </tr>
        </thead>
        <tbody id="groupTable">
            <!-- Здесь будут отображаться группы из базы данных -->
        </tbody>
    </table>

    <!-- Подключите JavaScript для взаимодействия с базой данных и обновления страницы -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script>
    // Обработчик события отправки формы
$("#groupForm").submit(function(e) {
  e.preventDefault();

  var groupId = $("#group_id").val();
  var groupName = $("#group_name").val();

  if (groupName) {
      if (groupId) {
          // Выполнить обновление группы
          $.ajax({
              type: "POST",
              url: "update_group.php", // Обработчик на сервере для обновления
              data: {
                  id: groupId,
                  name: groupName
              },
              success: function(response) {
                  alert("Группа успешно обновлена.");
                  resetForm();
                  loadGroups();
              },
              error: function(xhr, status, error) {
                  alert("Произошла ошибка: " + error);
              }
          });
      } else {
          // Выполнить добавление новой группы
          $.ajax({
              type: "POST",
              url: "add_group.php", // Обработчик на сервере для добавления
              data: {
                  name: groupName
              },
              success: function(response) {
                  alert("Группа успешно добавлена.");
                  resetForm();
                  loadGroups();
              },
              error: function(xhr, status, error) {
                  alert("Произошла ошибка: " + error);
              }
          });
      }
  }
});

// Обработчик события нажатия кнопки "Обновить"
$("#updateButton").click(function() {
  var groupId = $("#group_id").val();
  var groupName = $("#group_name").val();

  if (groupId && groupName) {
      // Выполнить обновление группы
      $.ajax({
          type: "POST",
          url: "update_group.php", // Обработчик на сервере для обновления
          data: {
              id: groupId,
              name: groupName
          },
          success: function(response) {
              alert("Группа успешно обновлена.");
              resetForm();
              loadGroups();
          },
          error: function(xhr, status, error) {
              alert("Произошла ошибка: " + error);
          }
      });
  } else {
      alert("Выберите группу для обновления.");
  }
});

// Обработчик события нажатия кнопки "Удалить"
$("#deleteButton").click(function() {
  var groupId = $("#group_id").val();

  if (groupId) {
      // Выполнить удаление группы
      if (confirm("Вы уверены, что хотите удалить эту группу?")) {
          $.ajax({
              type: "POST",
              url: "delete_group.php", // Обработчик на сервере для удаления
              data: {
                  id: groupId
              },
              success: function(response) {
                  alert("Группа успешно удалена.");
                  resetForm();
                  loadGroups();
              },
              error: function(xhr, status, error) {
                  alert("Произошла ошибка: " + error);
              }
          });
      }
  } else {
      alert("Выберите группу для удаления.");
  }
});

// Функция сброса формы
function resetForm() {
  $("#group_id").val("");
  $("#group_name").val("");
}

// Функция загрузки групп из базы данных
function loadGroups() {
  $.ajax({
      type: "GET",
      url: "get_groups.php", // Обработчик на сервере для получения групп
      success: function(response) {
          $("#groupTable").html(response);
      },
      error: function(xhr, status, error) {
          alert("Произошла ошибка: " + error);
      }
  });
}

// Вызов функции загрузки групп при загрузке страницы
$(document).ready(function() {
  loadGroups();
});

    </script>
</body>
</html>
