<?php
// Simple JSON API using PDO. Uses env vars set by docker-compose.
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://127.0.0.1:5500');
header('Access-Control-Allow-Methods: GET, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'webdev_asg_db';
$user = getenv('DB_USER') ?: 'user1';
$pass = getenv('DB_PASS') ?: '1234';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Example query — adjust the table name/columns to your schema
    $stmt = $pdo->query('SELECT * FROM user');
    $rows = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $rows]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>