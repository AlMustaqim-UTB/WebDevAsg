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
            "SELECT cr.carID, cr.plateNo, cr.carModel, cr.description, cr.makeID, cr.imageURL, cm.makeName, 
                    cr.year, cr.capacity, cr.transmission, cr.ratePerDay, cr.status, cr.categoryID
            FROM car cr
            JOIN carMake cm ON cm.makeID = cr.makeID
            ORDER BY cm.makeName, cr.carModel"
        );
        echo json_encode(['success' => true, 'cars' => $listCarStmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    function listMakes(PDO $pdo): void {
        $stmt = $pdo->query("SELECT makeID, makeName FROM carMake ORDER BY makeName");
        echo json_encode(['success' => true, 'makes' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    function listCategories(PDO $pdo): void {
        $stmt = $pdo->query("SELECT categoryID, categoryName FROM carCategory ORDER BY categoryName");
        echo json_encode(['success' => true, 'categories' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    function createCar(PDO $pdo): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $carID        = trim($input['carID'] ?? '');
        $plateNo      = trim($input['plateNo'] ?? '');
        $carModel     = trim($input['carModel'] ?? '');
        $description  = trim($input['description'] ?? '');
        $imgURL       = trim($input['imageURL'] ?? '');
        $makeID       = trim($input['makeID'] ?? '');
        $year         = (int) ($input['year'] ?? 0);
        $capacity     = (int) ($input['capacity'] ?? 0);
        $transmission = trim($input['transmission'] ?? '');
        $ratePerDay   = (float) ($input['ratePerDay'] ?? 0);
        $status       = trim($input['status'] ?? '');
        $categoryID   = trim($input['categoryID'] ?? '');

        if ($carID === '' || $plateNo === '' || $carModel === '' || $description === '' || $makeID === '' ||
            $year <= 0 || $capacity <= 0 || $transmission === '' || $ratePerDay <= 0 ||
            $status === '' || $categoryID === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'All fields are required.']);
            return;
        }

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO car (carID, plateNo, carModel, description, imageURL, makeID, year, capacity, transmission, ratePerDay, status, categoryID)
                 VALUES (:carID, :plateNo, :carModel, :description, :imageURL, :makeID, :year, :capacity, :transmission, :ratePerDay, :status, :categoryID)"
            );
            $stmt->execute([
                ':carID'        => $carID,
                ':plateNo'      => $plateNo,
                ':carModel'     => $carModel,
                ':description'  => $description,
                ':imageURL'     => $imgURL,
                ':makeID'       => $makeID,
                ':year'         => $year,
                ':capacity'     => $capacity,
                ':transmission' => $transmission,
                ':ratePerDay'   => $ratePerDay,
                ':status'       => $status,
                ':categoryID'   => $categoryID
            ]);

            echo json_encode(['success' => true, 'message' => 'Car added successfully.']);
        } catch (PDOException $e) {
            http_response_code(500);
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                echo json_encode(['success' => false, 'error' => 'Duplicate car ID or plate number.']);
            } else {
                echo json_encode(['success' => false,'error' => 'Unable to add car: ' . $e->getMessage()]);
            }
        }
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
    function updateCar(PDO $pdo): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $carID        = trim($input['carID'] ?? '');
        $plateNo      = trim($input['plateNo'] ?? '');
        $carModel     = trim($input['carModel'] ?? '');
        $description  = trim($input['description'] ?? '');
        $imgURL       = trim($input['imageURL'] ?? '');
        $makeID       = trim($input['makeID'] ?? '');
        $year         = (int) ($input['year'] ?? 0);
        $capacity     = (int) ($input['capacity'] ?? 0);
        $transmission = trim($input['transmission'] ?? '');
        $ratePerDay   = (float) ($input['ratePerDay'] ?? 0);
        $status       = trim($input['status'] ?? '');
        $categoryID   = trim($input['categoryID'] ?? '');

        if ($carID === '' || $plateNo === '' || $carModel === '' || $description === '' || $makeID === '' ||
            $year <= 0 || $capacity <= 0 || $transmission === '' || $ratePerDay <= 0 ||
            $status === '' || $categoryID === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'All fields are required.']);
            return;
        }

        try {
            $stmt = $pdo->prepare(
                "UPDATE car 
                 SET plateNo = :plateNo, carModel = :carModel, description = :description, imageURL = :imageURL,
                     makeID = :makeID, year = :year, capacity = :capacity, 
                     transmission = :transmission, ratePerDay = :ratePerDay, 
                     status = :status, categoryID = :categoryID
                 WHERE carID = :carID"
            );
            $stmt->execute([
                ':carID'       => $carID,
                ':plateNo'     => $plateNo,
                ':carModel'    => $carModel,
                ':description' => $description,
                ':imageURL'    => $imgURL,
                ':makeID'      => $makeID,
                ':year'        => $year,
                ':capacity'    => $capacity,
                ':transmission'=> $transmission,
                ':ratePerDay'  => $ratePerDay,
                ':status'      => $status,
                ':categoryID'  => $categoryID
            ]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Car not found or no changes made.']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Car updated successfully.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unable to update car: ' . $e->getMessage()]);
        }
    }

    switch ($action) {
        case 'list':
            listCars($pdo);
            break;
        case 'makes':
            listMakes($pdo);
            break;
        case 'categories':
            listCategories($pdo);
            break;
        case 'create':
            createCar($pdo);
            break;
        case 'update':
            updateCar($pdo);
            break;
        case 'delete':
            deleteCars($pdo);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
?>