<?php
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    // Handle preflight requests from browsers
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    require_once 'config.php';
    
    try {
        $pdo = new PDO($connectionString, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit;
    }

    // FUNCTION: List All Available Cars
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
        echo json_encode(['success' => true, 'cars' => $stmt->fetchAll()]);
    }

    function listCategories(PDO $pdo): void {
        $stmt = $pdo->query("SELECT categoryID, categoryName FROM carCategory ORDER BY categoryName");
        echo json_encode(['success' => true, 'categories' => $stmt->fetchAll()]);
    }

    function getCarDetails(PDO $pdo): void {
        $carID = trim($_REQUEST['carID'] ?? '');
        
        if ($carID === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Car ID is required']);
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
        $car = $stmt->fetch();

        if (!$car) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Car not found']);
            return;
        }

        echo json_encode(['success' => true, 'car' => $car]);
    }

    function generatePaymentID(PDO $pdo): string {
        $maxAttempts = 100; // Increase attempts
        
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            // Get the highest numeric value from existing payment IDs
            $stmt = $pdo->query("
                SELECT paymentID 
                FROM payment 
                WHERE paymentID LIKE 'PI%' 
                ORDER BY CAST(SUBSTRING(paymentID, 3) AS UNSIGNED) DESC 
                LIMIT 1
            ");
            $lastID = $stmt->fetchColumn();
            
            if ($lastID) {
                // Extract number: PI0004 -> 4, then add 1
                $num = intval(substr($lastID, 2)) + 1;
            } else {
                // No payments exist yet
                $num = 1;
            }
            
            // Add the attempt number to avoid duplicates in rapid succession
            $num += $attempt;
            $newID = 'PI' . str_pad($num, 4, '0', STR_PAD_LEFT);
            
            // Check if this ID already exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM payment WHERE paymentID = ?");
            $checkStmt->execute([$newID]);
            
            if ($checkStmt->fetchColumn() == 0) {
                // ID is unique, return it
                return $newID;
            }
        }
        
        throw new Exception("Unable to generate unique payment ID after $maxAttempts attempts");
    }

    function generateRentalID(PDO $pdo): string {
        $maxAttempts = 100; // Increase attempts
        
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            // Get the highest numeric value from existing rental IDs
            $stmt = $pdo->query("
                SELECT rentalID 
                FROM rental 
                WHERE rentalID LIKE 'RI%' 
                ORDER BY CAST(SUBSTRING(rentalID, 3) AS UNSIGNED) DESC 
                LIMIT 1
            ");
            $lastID = $stmt->fetchColumn();
            
            if ($lastID) {
                $num = intval(substr($lastID, 2)) + 1;
            } else {
                $num = 1;
            }
            
            // Add the attempt number to avoid duplicates
            $num += $attempt;
            $newID = 'RI' . str_pad($num, 4, '0', STR_PAD_LEFT);
            
            // Check if this ID already exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM rental WHERE rentalID = ?");
            $checkStmt->execute([$newID]);
            
            if ($checkStmt->fetchColumn() == 0) {
                return $newID;
            }
        }
        
        throw new Exception("Unable to generate unique rental ID after $maxAttempts attempts");
    }

    // FUNCTION: Create New Booking
    // Steps:
    // 1. Validate input data
    // 2. Check if customer has active rentals
    // 3. Create payment record
    // 4. Create rental record
    // 5. Update car status to 'Rented'
    function createBooking(PDO $pdo): void {
        // Get input data from request body
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        
        $customerID = trim($input['customerID'] ?? '');
        $carID = trim($input['carID'] ?? '');
        $startDate = trim($input['startDate'] ?? '');
        $endDate = trim($input['endDate'] ?? '');
        $handoverMethod = trim($input['handoverMethod'] ?? 'pickup'); // 'pickup' or 'delivery'
        $deliveryAddress = trim($input['deliveryAddress'] ?? '');
        $paymentMethod = trim($input['paymentMethod'] ?? 'cash'); // 'online' or 'cash'
        $totalAmount = floatval($input['totalAmount'] ?? 0);

        // ----------------------------------------
        // VALIDATION: Check required fields
        // ----------------------------------------
        if (empty($customerID) || empty($carID) || empty($startDate) || empty($endDate)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            return;
        }

        // If delivery is selected, address is required
        if ($handoverMethod === 'delivery' && empty($deliveryAddress)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Delivery address is required']);
            return;
        }

        try {
            // Start database transaction (all-or-nothing)
            $pdo->beginTransaction();

            // STEP 1: Check if customer has active rentals
            // Customer can only have 1 active rental at a time
            $stmtCheck = $pdo->prepare(
                "SELECT COUNT(*) as activeRentals 
                 FROM rental 
                 WHERE customerID = :customerID 
                 AND rentalStatus NOT IN ('Completed', 'Cancelled')"
            );
            $stmtCheck->execute([':customerID' => $customerID]);
            $result = $stmtCheck->fetch();
            
            if ($result['activeRentals'] > 0) {
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'You already have an active rental']);
                return;
            }

            // STEP 2: Generate payment ID and create payment record
            $paymentID = generatePaymentID($pdo);
            $paymentStatus = ($paymentMethod === 'online') ? 'Paid' : 'Pending';
            $paymentDate = ($paymentMethod === 'online') ? date('Y-m-d H:i:s') : null;
            
            $stmtPayment = $pdo->prepare(
                "INSERT INTO payment (paymentID, paymentDate, paymentMethod, paymentStatus)
                 VALUES (:paymentID, :paymentDate, :paymentMethod, :paymentStatus)"
            );
            $stmtPayment->execute([
                ':paymentID' => $paymentID,
                ':paymentDate' => $paymentDate,
                ':paymentMethod' => $paymentMethod,
                ':paymentStatus' => $paymentStatus
            ]);

            // STEP 3: Generate rental ID and create rental record
            $rentalID = generateRentalID($pdo);
            
            // Set delivery location (HQ for pickup, address for delivery)
            $deliveryLocation = ($handoverMethod === 'delivery') ? $deliveryAddress : "HQ";
            
            $stmtRental = $pdo->prepare(
                "INSERT INTO rental (rentalID, customerID, carID, startDate, endDate, 
                                     totalPrice, rentalStatus, paymentID, deliveryLocation)
                 VALUES (:rentalID, :customerID, :carID, :startDate, :endDate, 
                         :totalPrice, 'Pending', :paymentID, :deliveryLocation)"
            );
            $stmtRental->execute([
                ':rentalID' => $rentalID,
                ':customerID' => $customerID,
                ':carID' => $carID,
                ':startDate' => $startDate,
                ':endDate' => $endDate,
                ':totalPrice' => $totalAmount,
                ':paymentID' => $paymentID,
                ':deliveryLocation' => $deliveryLocation
            ]);

            // STEP 4: Update car status to 'Rented'
            $stmtUpdateCar = $pdo->prepare("UPDATE car SET status = 'Rented' WHERE carID = :carID");
            $stmtUpdateCar->execute([':carID' => $carID]);

            // Commit transaction (save all changes)
            $pdo->commit();

            // Return success response
            echo json_encode([
                'success' => true,
                'rentalID' => $rentalID,
                'paymentID' => $paymentID,
                'message' => 'Booking created successfully'
            ]);

        } catch (Exception $e) {
            // Rollback transaction (undo all changes)
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Booking failed: ' . $e->getMessage()]);
        }
    }

    $action = $_REQUEST['action'] ?? null;

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
        case 'createBooking':
            createBooking($pdo);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
?>