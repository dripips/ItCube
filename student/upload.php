<?php
session_start();
require_once '../data/config.php';

// Check user authentication
if (!isset($_SESSION['student_id'])) {
    die('Unauthorized');
}

// Get user ID from session
$userId = $_SESSION['student_id'];

// Connect to the database
try {
    $dbh = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Database connection error
    die('Database connection error');
}

// Function to format file size
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

// Check if file exists in the request
if (!isset($_FILES['file'])) {
    die('No file uploaded');
}

$file = $_FILES['file'];

$filename = $file['name'];
$filetype = $file['type'];
$filesize = $file['size'];
$filetmp = $file['tmp_name'];

// Check available space
try {
    $stmt = $dbh->prepare("SELECT SUM(file_size) AS total_size FROM files WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $totalSizeResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalSize = $totalSizeResult['total_size'] ?? 0;
    $availableSpace = 300 * 1024 * 1024 - $totalSize; // 300 MB in bytes

    if ($filesize > $availableSpace) {
        die('Not enough space');
    }
} catch (PDOException $e) {
    // Error fetching available space
    die('Error fetching available space');
}

// Generate unique file name
$uniqueFilename = uniqid() . '_' . $filename;

// Move the file to the uploads directory
$uploadPath = '../fm/uploads/' . $uniqueFilename;
move_uploaded_file($filetmp, $uploadPath);

// Add file information to the database
try {
    $stmt = $dbh->prepare("INSERT INTO files (user_id, filename, filepath, file_size) VALUES (:user_id, :filename, :filepath, :file_size)");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
    $stmt->bindParam(':filepath', $uploadPath, PDO::PARAM_STR);
    $stmt->bindParam(':file_size', $filesize, PDO::PARAM_INT);
    $stmt->execute();

    // Get the newly uploaded file information
    $fileId = $dbh->lastInsertId();
    $fileInfo = array(
        'id' => $fileId,
        'filename' => $filename,
        'filesize' => formatFileSize($filesize)
    );
    echo json_encode($fileInfo);
} catch (PDOException $e) {
    // Error uploading file
    unlink($uploadPath); // Delete the file in case of error
    die('Error uploading file');
}
