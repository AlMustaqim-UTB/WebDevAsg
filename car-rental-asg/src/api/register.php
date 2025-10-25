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

    function generateUserId(PDO $pdo): string {
        $prefix = 'CU';
        $stmt = $pdo->prepare('SELECT userID FROM user WHERE userID LIKE :prefix ORDER BY userID DESC LIMIT 1');
        $stmt->execute([':prefix' => $prefix . '%']);
        $lastId = $stmt->fetchColumn();
        $next = $lastId ? (int) substr($lastId, 2) + 1 : 1;

        return sprintf('%s%05d', $prefix, $next);
    }

    function registerUser(PDO $pdo): void{
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $firstName = trim($input['firstName'] ?? '');
        $lastName = trim($input['lastName'] ?? '');
        $phoneNum = trim($input['phoneNumber'] ?? '');
        $license = trim($input['license'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = trim($input['password'] ?? '');

        // Add validation
        if (empty($firstName) || empty($lastName) || empty($phoneNum) || empty($license) || empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'All fields are required.']);
            return;
        }

        $pdo->beginTransaction();

        try{
            $userId = generateUserId($pdo);
            //prepared statement for user table
            $insertUserTableStmt = $pdo->prepare(
                'INSERT INTO user (userID, firstName, lastName, phoneNo) VALUES (:userID, :firstName, :lastName, :phoneNo)'
            );
            $insertUserTableStmt->execute([
                ':userID'    => $userId,
                ':firstName' => $firstName,
                ':lastName'  => $lastName,
                ':phoneNo'   => $phoneNum
            ]);

            $hashedPass = password_hash($password, PASSWORD_DEFAULT);

            $insertCustTableStmt = $pdo->prepare(
                'INSERT INTO customer (customerID, licenseNo, email, password) VALUES (:userID, :licenseNo, :email, :password)'
            );
            $insertCustTableStmt->execute([
                    ':userID'    => $userId,
                    ':licenseNo' => $license,
                    ':email'     => $email,
                    ':password'  => $hashedPass
            ]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Registration successful!']);

        } catch (Exception $e){
            $pdo->rollBack();
            http_response_code(500);
            // Check for duplicate entry specifically
            if ($e instanceof PDOException && $e->errorInfo[1] == 1062) {
                 echo json_encode(['success' => false, 'error' => 'This email is already registered.']);
            } else {
                 echo json_encode(['success' => false, 'error' => 'An error occurred: ' . $e->getMessage()]);
            }
        }

    }

    switch ($action) {
        case 'register': 
            registerUser($pdo); 
            break;
        default:
            http_response_code(400);
            echo json_encode(['success'=>false,'error'=>'Unknown action']);
    }
?>
