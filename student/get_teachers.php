<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Подключение к базе данных
session_start();

require_once('../data/config.php');
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Запрос для получения списка преподавателей
$query = $pdo->query('SELECT id, first_name, last_name FROM teachers');
$teachers = $query->fetchAll(PDO::FETCH_ASSOC);

// Возвращаем данные в формате JSON
header('Content-Type: application/json');
echo json_encode($teachers);
