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

    $action = $_REQUEST['action'] ?? ($_SERVER['REQUEST_METHOD'] === 'GET' ? 'list' : null);

    // DB connection with error handling
    $host = getenv('DB_HOST') ?: 'db';
    $db   = getenv('DB_NAME') ?: 'webdev_asg_db';
    $user = getenv('DB_USER') ?: 'user1';
    $pass = getenv('DB_PASS') ?: '1234';
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB connection failed: ' . $e->getMessage()]);
        exit;
    }

    function listUsers($pdo) {
        $stmt = $pdo->query('SELECT userID, firstName, lastName, phoneNo FROM user ORDER BY userID');
        echo json_encode(['success'=>true,'data'=>$stmt->fetchAll()]);
    }

    function addUser($pdo) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $id = trim($input['userID'] ?? '');
        $fn = trim($input['firstName'] ?? '');
        $ln = trim($input['lastName'] ?? '');
        $ph = trim($input['phoneNo'] ?? '');
        if ($id<=0 || $fn===''||$ln===''||$ph==='') {
            http_response_code(400);
            echo json_encode(['success'=>false,'error'=>'Invalid input']); 
            return;
        }
        try {
            $sql = "INSERT INTO user (userID, firstName, lastName, phoneNo) VALUES (:id, :fn, :ln, :ph)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id'=>$id, ':fn'=>$fn,':ln'=>$ln,':ph'=>$ph]);
            echo json_encode(['success'=>true, 'message' => 'User added successfully.']);
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

    switch ($action) {
        case 'list': 
            listUsers($pdo); 
            break;
        case 'add': 
            addUser($pdo); 
            break;
        default:
            http_response_code(400);
            echo json_encode(['success'=>false,'error'=>'Unknown action']);
    }
?>