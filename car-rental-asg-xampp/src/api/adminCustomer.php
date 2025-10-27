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

    function listCustomers(PDO $pdo): void {
        $listCustomerStmt = $pdo->query(
            "SELECT c.customerID, c.licenseNo, c.email, u.phoneNo
            FROM customer c
            JOIN user u ON u.userID = c.customerID
            ORDER BY c.customerID"
        );
        echo json_encode(['success' => true, 'customers' => $listCustomerStmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    function deleteCustomer(PDO $pdo): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $customerId = trim($input['customerID'] ?? ($_REQUEST['customerID'] ?? ''));

        $pdo->beginTransaction();
        try {
            $stmtCustomer = $pdo->prepare('DELETE FROM customer WHERE customerID = :id');
            $stmtCustomer->execute([':id' => $customerId]);

            $stmtUser = $pdo->prepare('DELETE FROM user WHERE userID = :id');
            $stmtUser->execute([':id' => $customerId]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Customer deleted.']);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unable to delete customer.']);
        }
    }

    switch ($action) {
        case 'list':
            listCustomers($pdo);
            break;
        case 'delete':
            deleteCustomer($pdo);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
?>