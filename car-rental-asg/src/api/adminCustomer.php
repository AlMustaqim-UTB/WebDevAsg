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

        if ($customerId === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'customerID is required.']);
            return;
        }

        $pdo->beginTransaction();
        try {
            $stmtCustomer = $pdo->prepare('DELETE FROM customer WHERE customerID = :id');
            $stmtCustomer->execute([':id' => $customerId]);

            $stmtUser = $pdo->prepare('DELETE FROM user WHERE userID = :id');
            $stmtUser->execute([':id' => $customerId]);

            if ($stmtCustomer->rowCount() === 0) {
                $pdo->rollBack();
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Customer not found.']);
                return;
            }

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