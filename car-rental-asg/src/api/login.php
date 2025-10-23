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

    // const res = await fetch('http://localhost:8080/api/login.php?action=verify', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify(payload)
    // });

    function verifyUser(PDO $pdo): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $email = trim($input['email'] ?? '');
        $password = trim($input['password'] ?? '');

        if ($email === '' || $password === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Email and password are required.']);
            return;
        }

        // Check customer table
        $stmtCustomer = $pdo->prepare('SELECT customerID AS userID, password FROM customer WHERE email = :email LIMIT 1');
        $stmtCustomer->execute([':email' => $email]);
        $customer = $stmtCustomer->fetch(PDO::FETCH_ASSOC);

        if ($customer && password_verify($password, $customer['password'])) {
            echo json_encode(['success' => true, 'role' => 'customer', 'userID' => $customer['userID']]);
            return;
        }

        // Check admin table
        $stmtAdmin = $pdo->prepare('SELECT adminID AS userID, adminPassword FROM admin WHERE adminEmail = :email LIMIT 1');
        $stmtAdmin->execute([':email' => $email]);
        $admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['adminPassword'])) {
            echo json_encode(['success' => true, 'role' => 'admin', 'userID' => $admin['userID']]);
            return;
        }

        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid email or password.']);
    }

    switch ($action) {
        case 'verify': 
            verifyUser($pdo); 
            break;
        default:
            http_response_code(400);
            echo json_encode(['success'=>false,'error'=>'Unknown action']);
    }
?>
