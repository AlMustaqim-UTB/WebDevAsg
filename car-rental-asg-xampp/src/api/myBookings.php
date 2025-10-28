<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS headers for XAMPP local access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// DB configuration
require_once 'config.php';
$action = $_REQUEST['action'] ?? null;

try {
    $pdo = new PDO($connectionString, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($action === 'getActive') {
        $customerID = $_GET['customerID'] ?? '';
        
        if (empty($customerID)) {
            echo json_encode(['success' => false, 'error' => 'Customer ID required']);
            exit;
        }

        $sql = "SELECT r.*, cm.makeName, c.carModel, c.imageURL,
                DATEDIFF(r.endDate, r.startDate) as days
                FROM rental r
                JOIN car c ON r.carID = c.carID
                JOIN carmake cm ON c.makeID = cm.makeID
                WHERE r.customerID = :customerID
                AND r.rentalStatus IN ('Pending', 'Active')
                ORDER BY r.startDate DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':customerID', $customerID);
        $stmt->execute();
        
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'bookings' => $bookings]);
        
    } elseif ($action === 'getPast') {
        $customerID = $_GET['customerID'] ?? '';
        
        if (empty($customerID)) {
            echo json_encode(['success' => false, 'error' => 'Customer ID required']);
            exit;
        }

        $sql = "SELECT r.*, cm.makeName, c.carModel, c.imageURL,
                DATEDIFF(r.endDate, r.startDate) as days
                FROM rental r
                JOIN car c ON r.carID = c.carID
                JOIN carmake cm ON c.makeID = cm.makeID
                WHERE r.customerID = :customerID
                AND r.rentalStatus IN ('Completed', 'Cancelled')
                ORDER BY r.endDate DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':customerID', $customerID);
        $stmt->execute();
        
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'bookings' => $bookings]);
        
    } elseif ($action === 'cancel') {
        $input = json_decode(file_get_contents('php://input'), true);
        $rentalID = $input['rentalID'] ?? '';
        
        if (empty($rentalID)) {
            echo json_encode(['success' => false, 'error' => 'Rental ID required']);
            exit;
        }

        $sql = "UPDATE rental SET rentalStatus = 'Cancelled' WHERE rentalID = :rentalID";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':rentalID', $rentalID);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to cancel booking']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>