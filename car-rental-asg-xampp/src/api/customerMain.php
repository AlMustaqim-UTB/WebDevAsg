<?php
    header('Content-Type: application/json; charset=utf-8');

    // CORS headers for XAMPP local access
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    #db configuration
    require_once 'config.php';
    $action = $_REQUEST['action'] ?? null;

    try {
        $pdo = new PDO($connectionString, $user, $pass);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB connection failed: ' . $e->getMessage()]);
        exit;
    }

    function listAvailableCars(PDO $pdo): void {
        $stmt = $pdo->query(
            "SELECT c.carID, c.plateNo, c.carModel, c.description, c.imageURL,
                    mk.makeName, c.year, c.capacity, c.transmission,
                    c.ratePerDay, c.status, cat.categoryName
             FROM car c
             JOIN carMake mk ON mk.makeID = c.makeID
             LEFT JOIN carCategory cat ON cat.categoryID = c.categoryID
             WHERE c.status = 'Available'
             ORDER BY mk.makeName, c.carModel"
        );
        echo json_encode(['success' => true, 'cars' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    function listCategories(PDO $pdo): void {
        $stmt = $pdo->query("SELECT categoryID, categoryName FROM carCategory ORDER BY categoryName");
        echo json_encode(['success' => true, 'categories' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    function getCarDetails(PDO $pdo): void {
        $carID = trim($_REQUEST['carID'] ?? '');
        
        if ($carID === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Car ID is required.']);
            return;
        }

        $stmt = $pdo->prepare(
            "SELECT c.carID, c.plateNo, c.carModel, c.description, c.imageURL,
                    mk.makeName, c.year, c.capacity, c.transmission,
                    c.ratePerDay, c.status, cat.categoryName
             FROM car c
             JOIN carMake mk ON mk.makeID = c.makeID
             LEFT JOIN carCategory cat ON cat.categoryID = c.categoryID
             WHERE c.carID = :carID"
        );
        $stmt->execute([':carID' => $carID]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$car) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Car not found.']);
            return;
        }

        echo json_encode(['success' => true, 'car' => $car]);
    }

    switch ($action) {
        case 'list':
            listAvailableCars($pdo);
            break;
        case 'categories':
            listCategories($pdo);
            break;
        case 'details':
            getCarDetails($pdo);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
?>