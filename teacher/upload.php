<?php
$uploadDirectory = '../upload/'; // Папка для загрузки файлов
$allowedExtensions = array('jpg', 'jpeg', 'png', 'gif', 'webp'); // Разрешенные расширения файлов

if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
    $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    if (in_array(strtolower($extension), $allowedExtensions)) {
        // Генерируем уникальное имя для файла
        $uniqueFilename = uniqid() . '.' . $extension;
        $uploadPath = $uploadDirectory . $uniqueFilename;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath)) {
            $fileUrl = 'upload/' . $uniqueFilename;
            echo json_encode(['success' => true, 'message' => 'Файл успешно загружен.', 'file_url' => "../" . $fileUrl]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Не удалось загрузить файл.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Недопустимое расширение файла.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при загрузке файла.']);
}

?>
