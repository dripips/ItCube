<?php
session_start();

// Подключение к базе данных с использованием PDO и конфигурации из config.php
require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Получение списка тем для выпадающего списка
$stmt = $pdo->prepare("SELECT * FROM subjects");
$stmt->execute();
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Проверяем, был ли передан идентификатор урока для редактирования
if (isset($_GET['id'])) {
    $lessonId = $_GET['id'];

    // Получаем данные урока из базы данных
    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = :id");
    $stmt->bindParam(':id', $lessonId, PDO::PARAM_INT);
    $stmt->execute();
    $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

    // Если урок с заданным идентификатором не найден, можно выполнить соответствующие действия
    if (!$lesson) {
        echo "Урок не найден.";
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать урок</title>

    <!-- Подключение Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

</head>
<body>
<!-- Верхняя панель навигации -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="/teacher/teacher-dashboard.php">Панель преподавателя</a>
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

<!-- Основное содержимое -->
<main class="col-md-12 ms-sm-auto col-lg-12 px-md-12 text-center">
    <div class="my-5">
        <h2>Редактировать урок</h2>

        <!-- Форма для редактирования урока -->
        <form id="editLessonForm">
            <div class="form-group">
                <label for="lesson_name">Название урока:</label>
                <input type="text" class="form-control" id="lesson_name" name="lesson_name" required value="<?php echo $lesson['name']; ?>">
            </div>
            <div class="form-group">
                <label for="subject_id">Выберите тему:</label>
                <select class="form-control" id="subject_id" name="subject_id">
                    <?php foreach ($subjects as $subject) : ?>
                        <option value="<?php echo $subject['id']; ?>" <?php if ($subject['id'] == $lesson['subject_id']) echo 'selected'; ?>><?php echo $subject['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
              <label for="lesson_content">Содержание урока:</label>
              <textarea id="lesson_content" name="lesson_content"><?php echo $lesson['content']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="code_type">Тип кода:</label>
                <select class="form-control" id="code_type" name="code_type">
                  <option value="HTML" <?php if ($lesson['code_type'] == 'HTML') echo 'selected'; ?>>HTML</option>
                  <option value="CSS" <?php if ($lesson['code_type'] == 'CSS') echo 'selected'; ?>>CSS</option>
                  <option value="JavaScript" <?php if ($lesson['code_type'] == 'JavaScript') echo 'selected'; ?>>JavaScript</option>
                  <option value="Java" <?php if ($lesson['code_type'] == 'Java') echo 'selected'; ?>>Java</option>
                  <option value="Python" <?php if ($lesson['code_type'] == 'Python') echo 'selected'; ?>>Python</option>
                  <option value="PHP" <?php if ($lesson['code_type'] == 'PHP') echo 'selected'; ?>>PHP</option>
                    <!-- Добавьте другие типы кода по вашему выбору -->
                </select>
            </div>
            <input type="hidden" name="lesson_id" value="<?php echo $lessonId; ?>">
            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
        </form>
    </div>
</main>

<!-- Подключение Bootstrap JS (необходим для некоторых компонентов) -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.2/super-build/ckeditor.js"></script>
<script>
var fileUrl = ''; // Инициализация переменной fileUrl
 // Объявляем переменную в глобальной области видимости

// Обработчик события загрузки файла
function uploadFile(file) {
    var formData = new FormData();
    formData.append('file', file);

    $.ajax({
        url: 'upload.php', // Путь к PHP-скрипту для загрузки файла
        type: 'POST',
        data: formData,
        dataType: 'json',
        contentType: false,
        processData: false,
        success: function (response) {
            if (response.success) {
                // Обработка успешной загрузки
                console.log('Файл успешно загружен:', response.message);

                // Здесь можно выполнить необходимые действия с полученной ссылкой, например, вставить изображение в редактор.
                fileUrl = response.file_url;
            } else {
                // Обработка ошибки загрузки
                console.error('Ошибка загрузки файла:', response.message);
            }
        },
        error: function (xhr, status, error) {
            console.error('Ошибка при выполнении запроса:', error);
        }
    });
}
           // This sample still does not showcase all CKEditor&nbsp;5 features (!)
           // Visit https://ckeditor.com/docs/ckeditor5/latest/features/index.html to browse all the features.
           CKEDITOR.ClassicEditor.create(document.getElementById("lesson_content"), {
               // https://ckeditor.com/docs/ckeditor5/latest/features/toolbar/toolbar.html#extended-toolbar-configuration-format

               toolbar: {
                   items: [
                       'exportPDF','exportWord', '|',
                       'findAndReplace', 'selectAll', '|',
                       'heading', '|',
                       'bold', 'italic', 'strikethrough', 'underline', 'code', 'subscript', 'superscript', 'removeFormat', '|',
                       'bulletedList', 'numberedList', 'todoList', '|',
                       'outdent', 'indent', '|',
                       'undo', 'redo',
                       '-',
                       'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', 'highlight', '|',
                       'alignment', '|',
                       'link', 'insertImage', 'blockQuote', 'insertTable', 'mediaEmbed', 'codeBlock', 'htmlEmbed', '|',
                       'specialCharacters', 'horizontalLine', 'pageBreak', '|',
                       'textPartLanguage', '|',
                       'sourceEditing'
                   ],
                   shouldNotGroupWhenFull: true
               },
               codeBlock: {
            languages: [
                { language: 'php', label: 'PHP', class: 'language-php line-numbers' },
                { language: 'javascript', label: 'JavaScript', class: 'language-js line-numbers' },
                { language: 'css', label: 'CSS', class: 'language-css line-numbers' },
                { language: 'html', label: 'HTML', class: 'language-html line-numbers' },
                { language: 'java', label: 'Java', class: 'language-java line-numbers' },
                { language: 'python', label: 'Python', class: 'language-python line-numbers' }
            ]
        },
               // Changing the language of the interface requires loading the language file using the <script> tag.
               // language: 'es',
               list: {
                   properties: {
                       styles: true,
                       startIndex: true,
                       reversed: true
                   }
               },
               // https://ckeditor.com/docs/ckeditor5/latest/features/headings.html#configuration
               heading: {
                   options: [
                       { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                       { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                       { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                       { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                       { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
                       { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
                       { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' }
                   ]
               },
               // https://ckeditor.com/docs/ckeditor5/latest/features/editor-placeholder.html#using-the-editor-configuration
               // https://ckeditor.com/docs/ckeditor5/latest/features/font.html#configuring-the-font-family-feature
               fontFamily: {
                   options: [
                       'default',
                       'Arial, Helvetica, sans-serif',
                       'Courier New, Courier, monospace',
                       'Georgia, serif',
                       'Lucida Sans Unicode, Lucida Grande, sans-serif',
                       'Tahoma, Geneva, sans-serif',
                       'Times New Roman, Times, serif',
                       'Trebuchet MS, Helvetica, sans-serif',
                       'Verdana, Geneva, sans-serif'
                   ],
                   supportAllValues: true
               },
               // https://ckeditor.com/docs/ckeditor5/latest/features/font.html#configuring-the-font-size-feature
               fontSize: {
                   options: [ 10, 12, 14, 'default', 18, 20, 22 ],
                   supportAllValues: true
               },
               // Be careful with the setting below. It instructs CKEditor to accept ALL HTML markup.
               // https://ckeditor.com/docs/ckeditor5/latest/features/general-html-support.html#enabling-all-html-features
               htmlSupport: {
                   allow: [
                       {
                           name: /.*/,
                           attributes: true,
                           classes: true,
                           styles: true
                       }
                   ]
               },
               // Be careful with enabling previews
               // https://ckeditor.com/docs/ckeditor5/latest/features/html-embed.html#content-previews
               htmlEmbed: {
                   showPreviews: true
               },
               // https://ckeditor.com/docs/ckeditor5/latest/features/link.html#custom-link-attributes-decorators
               link: {
                   decorators: {
                       addTargetToExternalLinks: true,
                       defaultProtocol: 'https://',
                       toggleDownloadable: {
                           mode: 'manual',
                           label: 'Downloadable',
                           attributes: {
                               download: 'file'
                           }
                       }
                   }
               },
               // https://ckeditor.com/docs/ckeditor5/latest/features/mentions.html#configuration
               mention: {
                   feeds: [
                       {
                           marker: '@',
                           feed: [
                               '@apple', '@bears', '@brownie', '@cake', '@cake', '@candy', '@canes', '@chocolate', '@cookie', '@cotton', '@cream',
                               '@cupcake', '@danish', '@donut', '@dragée', '@fruitcake', '@gingerbread', '@gummi', '@ice', '@jelly-o',
                               '@liquorice', '@macaroon', '@marzipan', '@oat', '@pie', '@plum', '@pudding', '@sesame', '@snaps', '@soufflé',
                               '@sugar', '@sweet', '@topping', '@wafer'
                           ],
                           minimumCharacters: 1
                       }
                   ]
               },
               // The "super-build" contains more premium features that require additional configuration, disable them below.
               // Do not turn them on unless you read the documentation and know how to configure them and setup the editor.
               removePlugins: [
                   // These two are commercial, but you can try them out without registering to a trial.
                   // 'ExportPdf',
                   // 'ExportWord',
                   //'CKBox',
                   'CKFinder',
                   // This sample uses the Base64UploadAdapter to handle image uploads as it requires no configuration.
                   // https://ckeditor.com/docs/ckeditor5/latest/features/images/image-upload/base64-upload-adapter.html
                   // Storing images as Base64 is usually a very bad idea.
                   // Replace it on production website with other solutions:
                   // https://ckeditor.com/docs/ckeditor5/latest/features/images/image-upload/image-upload.html
                    'Base64UploadAdapter',
                   'RealTimeCollaborativeComments',
                   'RealTimeCollaborativeTrackChanges',
                   'RealTimeCollaborativeRevisionHistory',
                   'PresenceList',
                   'Comments',
                   'TrackChanges',
                   'TrackChangesData',
                   'RevisionHistory',
                   'Pagination',
                   'WProofreader',
                   // Careful, with the Mathtype plugin CKEditor will not load when loading this sample
                   // from a local file system (file://) - load this site via HTTP server if you enable MathType.
                   'MathType',
                   // The following features are part of the Productivity Pack and require additional license.
                   'SlashCommand',
                   'Template',
                   'DocumentOutline',
                   'FormatPainter',
                   'TableOfContents',
                   'PasteFromOfficeEnhanced'
               ]
           }).then(editor => {
        // Добавьте обработчики событий, если необходимо

        // Добавьте следующий код сюда:
        editor.plugins.get('FileRepository').createUploadAdapter = function (loader) {
            return {
                upload: function () {
                    return new Promise(function (resolve, reject) {
                        loader.file.then(function (file) {
                            uploadFile(file); // Вызываем функцию загрузки файла
                            setTimeout(function(){
                                resolve({ default: fileUrl });
                            }, 5000);
                            console.log(fileUrl);
                        });
                    });
                }
            };
        };
    })
    .catch(error => {
        console.error(error);
    });
       </script>
<script>
$(document).ready(function() {
    $("#editLessonForm").submit(function(e) {
        e.preventDefault();

        // Получаем данные из формы
        var lessonId = $("input[name='lesson_id']").val();
        var lessonName = $("#lesson_name").val();
        var lessonContent = $("#lesson_content").val();
        var codeType = $("#code_type").val();
        var subjectId = $("#subject_id").val();

        $.ajax({
            type: "POST",
            url: "edit_lesson_handler.php",
            data: {
                lesson_id: lessonId,
                lesson_name: lessonName,
                lesson_content: lessonContent,
                code_type: codeType,
                subject_id: subjectId
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    window.location.href = "my_lesson.php"; // Перенаправление после успешного редактирования
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                alert("Произошла ошибка: " + error);
            }
        });
    });
});
</script>
</body>
</html>
