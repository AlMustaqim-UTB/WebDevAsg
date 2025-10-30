<?php
// Set response headers for JSON and CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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

    // Route to appropriate function
    $action = $_GET['action'] ?? '';

    if ($action === 'getReport') {
        getReport($pdo);
    } else {
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
 * Generate business report with rental, fleet, and profit summaries
 * Filters by date range if provided
 */
function getReport($pdo) {
    // Get optional date range parameters
    $startDate = $_GET['startDate'] ?? null;
    $endDate = $_GET['endDate'] ?? null;

    // Build date filter for SQL query
    $dateFilter = '';
    $params = [];
    
    if ($startDate && $endDate) {
        $dateFilter = " WHERE r.startDate >= :startDate AND r.endDate <= :endDate";
        $params = [':startDate' => $startDate, ':endDate' => $endDate];
    }

    // ==========================================
    // RENTALS SUMMARY - Count rentals by status
    // ==========================================
    $rentalsSql = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN rentalStatus = 'Active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN rentalStatus = 'Pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN rentalStatus = 'Completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN rentalStatus = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
        FROM rental r
        $dateFilter
    ";
    
    $stmt = $pdo->prepare($rentalsSql);
    $stmt->execute($params);
    $rentals = $stmt->fetch();

    // ==========================================
    // FLEET SUMMARY - Count cars by status
    // ==========================================
    $carsSql = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) as available,
            SUM(CASE WHEN status = 'Rented' THEN 1 ELSE 0 END) as rented,
            SUM(CASE WHEN status = 'Maintenance' THEN 1 ELSE 0 END) as maintenance
        FROM car
    ";
    
    $stmt = $pdo->query($carsSql);
    $cars = $stmt->fetch();

    // ==========================================
    // PROFIT SUMMARY - Calculate revenue and payment counts
    // ==========================================
    $profitSql = "
        SELECT 
            SUM(r.totalPrice) as gross,
            SUM(CASE WHEN p.paymentStatus = 'Pending' THEN r.totalPrice ELSE 0 END) as outstanding,
            SUM(CASE WHEN p.paymentStatus = 'Paid' THEN 1 ELSE 0 END) as completedCount,
            SUM(CASE WHEN p.paymentStatus = 'Pending' THEN 1 ELSE 0 END) as pendingCount,
            SUM(CASE WHEN p.paymentStatus = 'Cancelled' THEN 1 ELSE 0 END) as cancelledCount
        FROM rental r
        LEFT JOIN payment p ON r.paymentID = p.paymentID
        $dateFilter
    ";
    
    $stmt = $pdo->prepare($profitSql);
    $stmt->execute($params);
    $profit = $stmt->fetch();

    // Calculate net profit (total revenue minus pending payments)
    $gross = (float)($profit['gross'] ?? 0);
    $outstanding = (float)($profit['outstanding'] ?? 0);
    $net = $gross - $outstanding;

    // Return JSON response with all summary data
    echo json_encode([
        'success' => true,
        'rentals' => [
            'total' => (int)($rentals['total'] ?? 0),
            'active' => (int)($rentals['active'] ?? 0),
            'pending' => (int)($rentals['pending'] ?? 0),
            'completed' => (int)($rentals['completed'] ?? 0),
            'cancelled' => (int)($rentals['cancelled'] ?? 0)
        ],
        'cars' => [
            'total' => (int)($cars['total'] ?? 0),
            'available' => (int)($cars['available'] ?? 0),
            'rented' => (int)($cars['rented'] ?? 0),
            'maintenance' => (int)($cars['maintenance'] ?? 0)
        ],
        'profit' => [
            'gross' => $gross,
            'outstanding' => $outstanding,
            'net' => $net,
            'completedCount' => (int)($profit['completedCount'] ?? 0),
            'pendingCount' => (int)($profit['pendingCount'] ?? 0),
            'cancelledCount' => (int)($profit['cancelledCount'] ?? 0)
        ]
    ]);
}
?>