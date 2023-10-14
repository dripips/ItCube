<?php
// Your database connection code (e.g., require_once('db.php'))
// Подключение к базе данных
require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme_id'])) {
    $themeId = $_POST['theme_id'];

    try {
        // Prepare and execute a query to fetch lessons for the given themeId
        $stmt = $pdo->prepare("SELECT id, name FROM lessons WHERE subject_id = :themeId");
        $stmt->bindParam(':themeId', $themeId, PDO::PARAM_INT);
        $stmt->execute();
        $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Send the lessons data as JSON response
        header('Content-Type: application/json');
        echo json_encode($lessons);
    } catch (PDOException $e) {
        // Handle any database errors here
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(['error' => 'Database error']);
    }
} else {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['error' => 'Invalid request']);
}
?>
