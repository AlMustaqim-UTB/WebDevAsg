<?php
// Set response headers for JSON and CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config.php';

try {
    // Connect to database
    $pdo = new PDO($connectionString, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Get action from request
    $action = $_GET['action'] ?? ($_POST['action'] ?? '');

    // Route to appropriate function
    switch ($action) {
        case 'getPayments':
            getPayments($pdo);
            break;
        
        case 'getPayment':
            getPayment($pdo);
            break;
        
        case 'updatePayment':
            updatePayment($pdo);
            break;
        
        case 'deletePayment':
            deletePayment($pdo);
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

/**
 * Get all payments ordered by date (newest first)
 */
function getPayments($pdo) {
    $sql = "SELECT * FROM payment ORDER BY paymentDate DESC";
    $stmt = $pdo->query($sql);
    $payments = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'payments' => $payments
    ]);
}

/**
 * Get single payment by ID
 */
function getPayment($pdo) {
    $paymentID = $_GET['paymentID'] ?? '';

    // Validate payment ID provided
    if (empty($paymentID)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Payment ID required']);
        return;
    }

    // Fetch payment from database
    $sql = "SELECT * FROM payment WHERE paymentID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$paymentID]);
    $payment = $stmt->fetch();

    // Check if payment exists
    if (!$payment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Payment not found']);
        return;
    }

    echo json_encode([
        'success' => true,
        'payment' => $payment
    ]);
}

/**
 * Update payment details
 */
function updatePayment($pdo) {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Extract payment data
    $paymentID = $input['paymentID'] ?? '';
    $paymentDate = $input['paymentDate'] ?? '';
    $paymentMethod = $input['paymentMethod'] ?? '';
    $paymentStatus = $input['paymentStatus'] ?? '';

    // Validate all required fields provided
    if (empty($paymentID) || empty($paymentDate) || empty($paymentMethod) || empty($paymentStatus)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'All fields are required']);
        return;
    }

    // Validate payment status is valid
    if (!in_array($paymentStatus, ['Pending', 'Paid', 'Cancelled'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid payment status']);
        return;
    }

    // Update payment in database
    $sql = "UPDATE payment 
            SET paymentDate = ?, 
                paymentMethod = ?, 
                paymentStatus = ?
            WHERE paymentID = ?";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$paymentDate, $paymentMethod, $paymentStatus, $paymentID]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Payment updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update payment']);
    }
}

/**
 * Delete payment (only if not linked to any rental)
 */
function deletePayment($pdo) {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $paymentID = $input['paymentID'] ?? '';

    // Validate payment ID provided
    if (empty($paymentID)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Payment ID required']);
        return;
    }

    // Check if payment is linked to any rental (prevent deletion if linked)
    $checkSql = "SELECT COUNT(*) as count FROM rental WHERE paymentID = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$paymentID]);
    $result = $checkStmt->fetch();

    if ($result['count'] > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Cannot delete payment linked to rental records']);
        return;
    }

    // Delete payment from database
    $sql = "DELETE FROM payment WHERE paymentID = ?";
    $stmt = $pdo->prepare($sql);
    $deleteResult = $stmt->execute([$paymentID]);

    if ($deleteResult) {
        echo json_encode(['success' => true, 'message' => 'Payment deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete payment']);
    }
}
?>