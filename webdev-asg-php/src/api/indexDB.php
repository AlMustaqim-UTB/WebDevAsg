<?php
    // simple API router with safer CORS and error handling
    header('Content-Type: application/json; charset=utf-8');

    // Allow common local dev origins (or use '*' for quick dev)
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
    $action = $_REQUEST['action'] ?? ($_SERVER['REQUEST_METHOD'] === 'GET' ? 'list' : null);
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        // $pdo = new PDO($connectionString, $user, $pass);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB connection failed: ' . $e->getMessage()]);
        exit;
    }

    function addCustomer($pdo) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $id = trim($input['customerID'] ?? '');
        $ln = trim($input['licenseNo'] ?? '');
        $e = trim($input['email'] ?? '');
        $pass = trim($input['password'] ?? '');
        $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

        if ($id === '' || $ln===''||$e===''||$pass==='') {
            http_response_code(400);
            echo json_encode(['success'=>false,'error'=>'Invalid input']); 
            return;
        }
        try {
            $sql = "INSERT INTO customer (customerID, licenseNo, email, password) VALUES (:id, :ln, :e, :pass)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id'=>$id, ':ln'=>$ln,':e'=>$e,':pass'=>$hashedPass]);
            echo json_encode(['success'=>true, 'message' => 'Customer added successfully.']);
        } catch (PDOException $e) {
            http_response_code(500);
            // Catch potential duplicate key errors
            if ($e->errorInfo[1] == 1062) {
                 echo json_encode(['success'=>false,'error'=>'User with this ID already exists.']);
            } else {
                 echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
            }
        }
    }

    function verifyPassword($pdo) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $email = trim($input['email'] ?? '');
        $pass = trim($input['password'] ?? '');

        if ($email === '' || $pass === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Customer ID and password are required.']);
            return;
        }

        try {
            // quick check to see if email belong to customer
            $stmt = $pdo->prepare('SELECT password FROM customer WHERE email = :email');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            //check if the password hash matched
            if ($user && password_verify($pass, $user['password'])){
                echo json_encode(['success' => true, 'role' => 'customer', 'message' => 'Customer logged in successfully.']);
                return;
            }

            $stmt = $pdo->prepare('SELECT adminPassword FROM admin WHERE adminEmail = :adminEmail');
            $stmt->execute([':adminEmail' => $email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($pass, $admin['adminPassword'])) {
                echo json_encode(['success' => true, 'role' => 'admin', 'message' => 'Admin logged in successfully.']);
                return;
            }

            //if not found in both
            http_response_code(401); // Unauthorized
            echo json_encode(['success' => false, 'error' => 'Invalid email or password.']);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    switch ($action) {
        case 'add': 
            addCustomer($pdo); 
            break;
        case 'verify':
            verifyPassword($pdo);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success'=>false,'error'=>'Unknown action']);
    }
?>