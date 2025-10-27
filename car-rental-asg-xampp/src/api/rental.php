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

    function listRentals(PDO $pdo): void {
        $stmt = $pdo->query(
            "SELECT rentalID, customerID, carID, startDate, endDate, 
                    totalPrice, rentalStatus, paymentID, deliveryLocation
             FROM rental
             ORDER BY startDate DESC"
        );
        echo json_encode(['success' => true, 'rentals' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    function listCustomers(PDO $pdo): void {
        $stmt = $pdo->query("SELECT customerID FROM customer ORDER BY customerID");
        echo json_encode(['success' => true, 'customers' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    function listCars(PDO $pdo): void {
        $stmt = $pdo->query("SELECT carID, carModel, status FROM car ORDER BY carID");
        echo json_encode(['success' => true, 'cars' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    function listPayments(PDO $pdo): void {
        $stmt = $pdo->query("SELECT paymentID, paymentMethod, paymentStatus FROM payment ORDER BY paymentID");
        echo json_encode(['success' => true, 'payments' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    function createRental(PDO $pdo): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $rentalID         = trim($input['rentalID'] ?? '');
        $customerID       = trim($input['customerID'] ?? '');
        $carID            = trim($input['carID'] ?? '');
        $startDate        = trim($input['startDate'] ?? '');
        $endDate          = trim($input['endDate'] ?? '');
        $totalPrice       = (float) ($input['totalPrice'] ?? 0);
        $rentalStatus     = trim($input['rentalStatus'] ?? '');
        $paymentID        = trim($input['paymentID'] ?? '');
        $deliveryLocation = trim($input['deliveryLocation'] ?? '');

        if ($rentalID === '' || $customerID === '' || $carID === '' || 
            $startDate === '' || $endDate === '' || $totalPrice <= 0 || 
            $rentalStatus === '' || $paymentID === '' || $deliveryLocation === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'All fields are required.']);
            return;
        }

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO rental (rentalID, customerID, carID, startDate, endDate, totalPrice, rentalStatus, paymentID, deliveryLocation)
                 VALUES (:rentalID, :customerID, :carID, :startDate, :endDate, :totalPrice, :rentalStatus, :paymentID, :deliveryLocation)"
            );
            $stmt->execute([
                ':rentalID'         => $rentalID,
                ':customerID'       => $customerID,
                ':carID'            => $carID,
                ':startDate'        => $startDate,
                ':endDate'          => $endDate,
                ':totalPrice'       => $totalPrice,
                ':rentalStatus'     => $rentalStatus,
                ':paymentID'        => $paymentID,
                ':deliveryLocation' => $deliveryLocation
            ]);

            echo json_encode(['success' => true, 'message' => 'Rental added successfully.']);
        } catch (PDOException $e) {
            http_response_code(500);
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                echo json_encode(['success' => false, 'error' => 'Duplicate rental ID.']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Unable to add rental: ' . $e->getMessage()]);
            }
        }
    }

    function updateRental(PDO $pdo): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $rentalID         = trim($input['rentalID'] ?? '');
        $customerID       = trim($input['customerID'] ?? '');
        $carID            = trim($input['carID'] ?? '');
        $startDate        = trim($input['startDate'] ?? '');
        $endDate          = trim($input['endDate'] ?? '');
        $totalPrice       = (float) ($input['totalPrice'] ?? 0);
        $rentalStatus     = trim($input['rentalStatus'] ?? '');
        $paymentID        = trim($input['paymentID'] ?? '');
        $deliveryLocation = trim($input['deliveryLocation'] ?? '');

        if ($rentalID === '' || $customerID === '' || $carID === '' || 
            $startDate === '' || $endDate === '' || $totalPrice <= 0 || 
            $rentalStatus === '' || $paymentID === '' || $deliveryLocation === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'All fields are required.']);
            return;
        }

        try {
            $stmt = $pdo->prepare(
                "UPDATE rental 
                 SET customerID = :customerID, carID = :carID, startDate = :startDate,
                     endDate = :endDate, totalPrice = :totalPrice, rentalStatus = :rentalStatus,
                     paymentID = :paymentID, deliveryLocation = :deliveryLocation
                 WHERE rentalID = :rentalID"
            );
            $stmt->execute([
                ':rentalID'         => $rentalID,
                ':customerID'       => $customerID,
                ':carID'            => $carID,
                ':startDate'        => $startDate,
                ':endDate'          => $endDate,
                ':totalPrice'       => $totalPrice,
                ':rentalStatus'     => $rentalStatus,
                ':paymentID'        => $paymentID,
                ':deliveryLocation' => $deliveryLocation
            ]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Rental not found or no changes made.']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Rental updated successfully.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unable to update rental: ' . $e->getMessage()]);
        }
    }

    function deleteRental(PDO $pdo): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $rentalID = trim($input['rentalID'] ?? ($_REQUEST['rentalID'] ?? ''));

        if ($rentalID === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Rental ID is required.']);
            return;
        }

        try {
            $stmt = $pdo->prepare('DELETE FROM rental WHERE rentalID = :rentalID');
            $stmt->execute([':rentalID' => $rentalID]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Rental not found.']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Rental deleted.']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unable to delete rental.']);
        }
    }

    switch ($action) {
        case 'list':
            listRentals($pdo);
            break;
        case 'customers':
            listCustomers($pdo);
            break;
        case 'cars':
            listCars($pdo);
            break;
        case 'payments':
            listPayments($pdo);
            break;
        case 'create':
            createRental($pdo);
            break;
        case 'update':
            updateRental($pdo);
            break;
        case 'delete':
            deleteRental($pdo);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
?>