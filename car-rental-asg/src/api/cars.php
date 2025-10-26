<?php
    header('Content-Type: application/json; charset=utf-8');

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowed = [
        'http://127.0.0.1:5500',
        'http://localhost:5500',
        'http://localhost:8080',
        'http://127.0.0.1:8080'
    ];
    if (in_array($origin, $allowed, true)) {
        header("Access-Control-Allow-Origin: $origin");
    } else {
        header("Access-Control-Allow-Origin: *");
    }
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

    #db configuration
    require_once 'config.php';
    $action = $_REQUEST['action'] ?? null;
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        // $pdo = new PDO($connectionString, $user, $pass);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB connection failed: ' . $e->getMessage()]);
        exit;
    }

    function listCars(PDO $pdo): void {
        $listCarStmt = $pdo->query(
            "SELECT cr.carID, cr.plateNo, cr.carModel, cm.makeName, cr.year, cr.capacity, cr.transmission, cr.ratePerDay, cr.status
            FROM car cr
            JOIN carMake cm ON cm.makeID = cr.makeID
            ORDER BY cm.makeName, cr.carModel"
        );
        echo json_encode(['success' => true, 'cars' => $listCarStmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    function deleteCars(PDO $pdo): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $carId = trim($input['carID'] ?? ($_REQUEST['carID'] ?? ''));

        $pdo->beginTransaction();
        try {
            $stmtCar = $pdo->prepare('DELETE FROM car WHERE carID = :carId');
            $stmtCar->execute([':carId' => $carId]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Car deleted.']);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unable to delete Car.']);
        }
    }

    switch ($action) {
        case 'list':
            listCars($pdo);
            break;
        case 'delete':
            deleteCars($pdo);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
?>