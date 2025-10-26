<?php
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

    require_once 'config.php';
    $action = $_REQUEST['action'] ?? null;
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
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