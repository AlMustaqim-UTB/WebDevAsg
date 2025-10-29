<?php
    // Set JSON response header
    header('Content-Type: application/json; charset=utf-8');
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // CORS headers for local development
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    require_once 'config.php';

    try {
        // Initialize database connection
        $pdo = new PDO($connectionString, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $action = $_REQUEST['action'] ?? null;

        switch ($action) {
            case 'getActive':
                echo json_encode(getBookings($pdo, $_GET['customerID'] ?? '', ['Pending', 'Active']));
                break;
                
            case 'getPast':
                echo json_encode(getBookings($pdo, $_GET['customerID'] ?? '', ['Completed', 'Cancelled']));
                break;
                
            case 'cancel':
                echo json_encode(cancelBooking($pdo));
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

    function getBookings($pdo, $customerID, $statuses) {
        // Validate customer ID
        if (empty($customerID)) {
            http_response_code(400);
            return ['success' => false, 'error' => 'Customer ID required'];
        }

        // Build SQL with IN clause for multiple statuses
        $placeholders = str_repeat('?,', count($statuses) - 1) . '?';
        
        $sql = "SELECT r.*, cm.makeName, c.carModel, c.imageURL,
                DATEDIFF(r.endDate, r.startDate) as days
                FROM rental r
                JOIN car c ON r.carID = c.carID
                JOIN carmake cm ON c.makeID = cm.makeID
                WHERE r.customerID = ?
                AND r.rentalStatus IN ($placeholders)
                ORDER BY r.startDate DESC";
        
        $stmt = $pdo->prepare($sql);
        
        // Bind customer ID and all statuses
        $params = array_merge([$customerID], $statuses);
        $stmt->execute($params);
        
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ['success' => true, 'bookings' => $bookings];
    }


    function cancelBooking($pdo) {
        // Parse JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $rentalID = $input['rentalID'] ?? '';
        $customerID = $input['customerID'] ?? ''; // Should verify ownership
        
        // Validate rental ID
        if (empty($rentalID)) {
            http_response_code(400);
            return ['success' => false, 'error' => 'Rental ID required'];
        }

        // Begin transaction for data integrity
        $pdo->beginTransaction();
        
        try {
            // Verify booking exists and belongs to customer (if customerID provided)
            // Also get the carID for updating car status
            $checkSql = "SELECT rentalID, carID FROM rental WHERE rentalID = ?";
            $params = [$rentalID];
            
            if (!empty($customerID)) {
                $checkSql .= " AND customerID = ?";
                $params[] = $customerID;
            }
            
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute($params);
            $rental = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$rental) {
                $pdo->rollBack();
                http_response_code(403);
                return ['success' => false, 'error' => 'Booking not found or unauthorized'];
            }
            
            // Update booking status to Cancelled
            $sql = "UPDATE rental SET rentalStatus = 'Cancelled' WHERE rentalID = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$rentalID]);
            
            // Update car status back to Available
            $updateCarSql = "UPDATE car SET status = 'Available' WHERE carID = ?";
            $updateCarStmt = $pdo->prepare($updateCarSql);
            $updateCarStmt->execute([$rental['carID']]);
            
            // Commit transaction
            $pdo->commit();
            
            return ['success' => true, 'message' => 'Booking cancelled successfully'];
            
        } catch (Exception $e) {
            // Rollback on error
            $pdo->rollBack();
            throw $e;
        }
    }
?>