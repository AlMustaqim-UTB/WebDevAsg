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

    require_once 'config.php';
    $action = $_REQUEST['action'] ?? ($_SERVER['REQUEST_METHOD'] === 'GET' ? 'list' : null);
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'DB connection failed: ' . $e->getMessage()]);
        exit;
    }

    function generateUserId(PDO $pdo, string $role): string {
        if (!in_array($role, ['customer', 'admin'], true)) {
            throw new InvalidArgumentException('Invalid role supplied.');
        }

        $prefix = $role === 'customer' ? 'CU' : 'AD';
        $stmt = $pdo->prepare('SELECT userID FROM user WHERE userID LIKE :prefix ORDER BY userID DESC LIMIT 1');
        $stmt->execute([':prefix' => $prefix . '%']);
        $lastId = $stmt->fetchColumn();
        $next = $lastId ? (int) substr($lastId, 2) + 1 : 1;

        return sprintf('%s%05d', $prefix, $next);
    }

    function registerUser(PDO $pdo): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $firstName = trim($input['firstName'] ?? '');
        $lastName  = trim($input['lastName'] ?? '');
        $phoneNo   = trim($input['phoneNo'] ?? '');
        $role      = trim($input['role'] ?? '');
        $email     = trim($input['email'] ?? '');
        $password  = trim($input['password'] ?? '');

        if ($firstName === '' || $lastName === '' || $phoneNo === '' || $role === '' || $email === '' || $password === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid input. All fields are required.']);
            return;
        }

        if (!in_array($role, ['customer', 'admin'], true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid role selected.']);
            return;
        }

        $pdo->beginTransaction();
        try {
            $userId = generateUserId($pdo, $role);

            $stmtUser = $pdo->prepare(
                'INSERT INTO user (userID, firstName, lastName, phoneNo) VALUES (:userID, :firstName, :lastName, :phoneNo)'
            );
            $stmtUser->execute([
                ':userID'    => $userId,
                ':firstName' => $firstName,
                ':lastName'  => $lastName,
                ':phoneNo'   => $phoneNo
            ]);

            $hashedPass = password_hash($password, PASSWORD_DEFAULT);

            if ($role === 'customer') {
                $licenseNo = trim($input['licenseNo'] ?? '');
                if ($licenseNo === '') {
                    throw new InvalidArgumentException('License number is required for customers.');
                }

                $stmtRole = $pdo->prepare(
                    'INSERT INTO customer (customerID, licenseNo, email, password) VALUES (:userID, :licenseNo, :email, :password)'
                );
                $stmtRole->execute([
                    ':userID'    => $userId,
                    ':licenseNo' => $licenseNo,
                    ':email'     => $email,
                    ':password'  => $hashedPass
                ]);
            } else {
                $stmtRole = $pdo->prepare(
                    'INSERT INTO admin (adminID, adminEmail, adminPassword) VALUES (:userID, :adminEmail, :adminPassword)'
                );
                $stmtRole->execute([
                    ':userID'       => $userId,
                    ':adminEmail'   => $email,
                    ':adminPassword'=> $hashedPass
                ]);
            }

            $pdo->commit();
            echo json_encode([
                'success' => true,
                'message' => ucfirst($role) . ' registered successfully.',
                'userID'  => $userId
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);

            if ($e instanceof PDOException && isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                echo json_encode(['success' => false, 'error' => 'Duplicate entry detected.']);
            } else {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    switch ($action) {
        case 'register':
            registerUser($pdo);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
?>