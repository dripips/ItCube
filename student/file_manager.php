<?php
session_start();
require_once '../data/config.php';

// Проверка авторизации пользователя

if (!isset($_SESSION['student_id'])) {
    // Сессия не активна, перенаправляем на страницу авторизации
    header("Location: student_dashboard.php");
    exit();
}

// Получение идентификатора пользователя из сессии
$userId = $_SESSION['student_id'];

// Подключение к базе данных
try {
    $dbh = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Обработка ошибки подключения к базе данных
    die('Database connection error');
}

// Функция для форматирования размера файла
function formatFileSize($size)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}

// Создание папки для загрузки файлов, если она отсутствует
$uploadDir = '../fm/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Получение информации о файлах пользователя из базы данных
try {
    $stmt = $dbh->prepare("SELECT * FROM files WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Обработка ошибки запроса
    die('Error fetching files');
}

// Получение информации о доступном пространстве пользователя
try {
    $stmt = $dbh->prepare("SELECT SUM(file_size) AS total_size FROM files WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $totalSizeResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalSize = $totalSizeResult['total_size'] ?? 0;
    $availableSpace = 300 * 1024 * 1024 - $totalSize; // 300 MB in bytes
} catch (PDOException $e) {
    // Обработка ошибки запроса
    die('Error fetching available space');
}

// Обработка загрузки нового файла
if (isset($_FILES['file'])) {
    $file = $_FILES['file'];

    $filename = $file['name'];
    $filetype = $file['type'];
    $filesize = $file['size'];
    $filetmp = $file['tmp_name'];

    // Проверка доступного пространства
    if ($filesize > $availableSpace) {
        die('Not enough space');
    }

    // Генерация уникального имени файла
    $uniqueFilename = uniqid() . '_' . $filename;

    // Перемещение файла в папку uploads
    $uploadPath = $uploadDir . '/' . $uniqueFilename;
    move_uploaded_file($filetmp, $uploadPath);

    // Добавление информации о файле в базу данных
    try {
        $stmt = $dbh->prepare("INSERT INTO files (user_id, filename, filepath, file_size) VALUES (:user_id, :filename, :filepath, :file_size)");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
        $stmt->bindParam(':filepath', $uploadPath, PDO::PARAM_STR);
        $stmt->bindParam(':file_size', $filesize, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        // Обработка ошибки запроса
        die('Error uploading file');
    }
}

// Удаление файла
if (isset($_POST['delete_file'])) {
    $fileId = $_POST['file_id'];

    // Получение информации о файле из базы данных
    try {
        $stmt = $dbh->prepare("SELECT * FROM files WHERE id = :file_id");
        $stmt->bindParam(':file_id', $fileId, PDO::PARAM_INT);
        $stmt->execute();
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$file) {
            die('File not found');
        }

        $filepath = $file['filepath'];

        // Удаление файла из папки uploads
        unlink($filepath);

        // Удаление информации о файле из базы данных
        $stmt = $dbh->prepare("DELETE FROM files WHERE id = :file_id");
        $stmt->bindParam(':file_id', $fileId, PDO::PARAM_INT);
        $stmt->execute();

        // Отправка успешного ответа
        echo 'File deleted successfully';
        exit;
    } catch (PDOException $e) {
        // Обработка ошибки запроса
        die('Error deleting file');
    }
}

// Редактирование файла
if (isset($_POST['edit_file'])) {
    $fileId = $_POST['file_id'];
    $newFilename = $_POST['new_filename'];

    // Получение информации о файле из базы данных
    try {
        $stmt = $dbh->prepare("SELECT * FROM files WHERE id = :file_id");
        $stmt->bindParam(':file_id', $fileId, PDO::PARAM_INT);
        $stmt->execute();
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$file) {
            die('File not found');
        }

        $filepath = $file['filepath'];

        // Переименование файла на сервере
        $newFilepath = dirname($filepath) . '/' . $newFilename;
        rename($filepath, $newFilepath);

        // Обновление информации о файле в базе данных
        $stmt = $dbh->prepare("UPDATE files SET filename = :filename, filepath = :filepath WHERE id = :file_id");
        $stmt->bindParam(':filename', $newFilename, PDO::PARAM_STR);
        $stmt->bindParam(':filepath', $newFilepath, PDO::PARAM_STR);
        $stmt->bindParam(':file_id', $fileId, PDO::PARAM_INT);
        $stmt->execute();

        // Отправка успешного ответа
        echo 'File edited successfully';
        exit;
    } catch (PDOException $e) {
        // Обработка ошибки запроса
        die('Error editing file');
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Файловый менеджер</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
        }

        .card {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-size: 24px;
            font-weight: bold;
        }

        .card-body {
            padding: 40px;
        }

        .file-list {
            margin-top: 20px;
        }

        .file-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #ffffff;
            border-radius: 4px;
        }

        .file-item .file-name {
            flex-grow: 1;
            margin-left: 10px;
        }

        .file-item .file-size {
            margin-left: 10px;
            color: #999999;
        }

        .progress-bar {
            margin-top: 10px;
        }

        .available-space {
            margin-top: 10px;
            color: #999999;
        }

        .delete-btn,
        .edit-btn {
            margin-left: 10px;
        }
    </style>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
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
<div class="container">
    <div class="card">
        <div class="card-body">
            <h2 class="card-title">Файловый менеджер</h2>
            <form id="uploadForm" action="../php/ajax/upload.php" class="dropzone">
                <div class="fallback">
                    <input type="file" name="file" multiple>
                </div>
            </form>
            <div class="progress-bar" style="border-radius: 1.25rem;">
                <div class="progress" id="progress"></div>
            </div>
            <div class="available-space">
                Доступное пространство: <?php echo formatFileSize($availableSpace); ?>
            </div>
            <div class="file-list">
                <?php foreach ($files as $file): ?>
                    <div class="file-item" data-file-id="<?php echo $file['id']; ?>">
                        <div class="file-name"><?php echo $file['filename']; ?></div>
                        <div class="file-size"><?php echo formatFileSize($file['file_size']); ?></div>
                        <div class="download-btn">
                            <button class="btn btn-success">Скачать</button>
                        </div>
                        <div class="delete-btn">
                            <button class="btn btn-danger">Удалить</button>
                        </div>
                        <div class="edit-btn">
                            <button onclick="showEditModal(<?php echo $file['id']; ?>)" class="btn btn-primary">Редактировать</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования файла -->
<div id="editModal" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редактировать файл</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="editFileId" name="file_id">
                    <div class="mb-3">
                        <label for="newFilename" class="form-label">Новое имя файла:</label>
                        <input type="text" class="form-control" id="newFilename" name="new_filename">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    <button type="submit" name="edit_file" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
    Dropzone.autoDiscover = false;

    $(document).ready(function () {
        var myDropzone = new Dropzone("#uploadForm", {
            url: "upload.php",
            acceptedFiles: ".jpg,.jpeg,.png,.gif,.zip,.pptx,.word,.pdf",
            maxFilesize: 10, // MB
            parallelUploads: 2,
            dictDefaultMessage: "Перетащите файлы сюда или кликните для выбора файлов",
            dictFileTooBig: "Файл слишком большой ({{filesize}}МБ). Максимальный размер файла: {{maxFilesize}}МБ.",
            dictInvalidFileType: "Неверный тип файла. Разрешены только файлы с расширениями: {{acceptedFiles}}.",
            dictMaxFilesExceeded: "Вы не можете загрузить больше {{maxFiles}} файлов.",
            headers: {
                "Cache-Control": null,
                "X-Requested-With": null
            },
            init: function () {
                this.on("uploadprogress", function (file, progress, bytesSent) {
                    var totalBytes = file.upload.total;
                    var percent = Math.round((bytesSent / totalBytes) * 100);
                    $('#progress').css('width', percent + '%');
                });

                this.on("success", function (file, response) {
                    // Очистка поля выбора файла после успешной загрузки
                    this.removeFile(file);

                    // Обновление списка файлов
                    fetchFiles();

                    // Отображение уведомления об успешной загрузке
                    Toastify({
                        text: 'Файл успешно загружен',
                        duration: 3000,
                        close: true,
                        gravity: 'bottom',
                        position: 'right',
                        backgroundColor: 'linear-gradient(to right, #00b09b, #96c93d)',
                    }).showToast();
                });
            }
        });

        $('.download-btn button').on('click', function (e) {
            e.stopPropagation();
            var fileId = $(this).closest('.file-item').data('file-id');
            downloadFile(fileId);
        });
        $('.delete-btn button').on('click', function (e) {
            e.stopPropagation();
            var fileId = $(this).closest('.file-item').data('file-id');
            deleteFile(fileId);
        });
    });
        // Скачивание файла
        function downloadFile(fileId) {
            $.ajax({
                url: 'download_file.php',
                type: 'POST',
                data: {
                    file_id: fileId
                },
                success: function (response) {
                    // Создание временной ссылки для скачивания файла
                    var link = document.createElement('a');
                    link.href = 'download.php?file_id=' + fileId;
                    link.download = response;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            });
        }

        // Получение списка файлов
        function fetchFiles() {
            $.ajax({
                url: 'fetch_files.php',
                type: 'GET',
                success: function (response) {
                    $('.file-list').html(response);
                }
            });
        }

        // Удаление файла
        function deleteFile(fileId) {
            if (confirm('Вы уверены, что хотите удалить файл?')) {
                $.ajax({
                    url: 'delete_file.php',
                    type: 'POST',
                    data: {
                        file_id: fileId
                    },
                    success: function (response) {
                        // Обновление списка файлов
                        fetchFiles();

                        // Отображение уведомления об успешном удалении
                        Toastify({
                            text: response,
                            duration: 3000,
                            close: true,
                            gravity: 'bottom',
                            position: 'right',
                            backgroundColor: 'linear-gradient(to right, #00b09b, #96c93d)',
                        }).showToast();
                    }
                });
            }
        }

        // Отображение модального окна редактирования файла
        function showEditModal(fileId) {
            $('#editFileId').val(fileId);
            $('#editModal').modal('show');
        }

        // Отправка формы редактирования файла через Ajax
        $('#editForm').on('submit', function (e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: 'edit_file.php',
                type: 'POST',
                data: formData,
                success: function (response) {
                    // Закрытие модального окна редактирования файла
                    $('#editModal').modal('hide');

                    // Обновление списка файлов
                    fetchFiles();

                    // Отображение уведомления об успешном редактировании
                    Toastify({
                        text: response,
                        duration: 3000,
                        close: true,
                        gravity: 'bottom',
                        position: 'right',
                        backgroundColor: 'linear-gradient(to right, #00b09b, #96c93d)',
                    }).showToast();
                }
            });
        });

        // Получение списка файлов при загрузке страницы
        fetchFiles();

</script>
</body>
</html>
