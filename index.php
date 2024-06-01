<?php  
session_start();

if (!isset($_SESSION['accounts'])) {
    $_SESSION['accounts'] = [];
}

$accounts = &$_SESSION['accounts'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/reset') {
    $_SESSION['accounts'] = [];
    http_response_code(200);
    echo "OK";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/balance') {
    $account_id = $_GET['account_id'] ?? null;
    if ($account_id !== null && isset($accounts[$account_id])) {
        http_response_code(200);
        echo $accounts[$account_id]['balance']; // Retorna o saldo como um valor puro
    } else {
        http_response_code(404);
        echo json_encode(0);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/event') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data['type'] === 'deposit') {
        $destination = $data['destination'];
        $amount = $data['amount'];
        if (!isset($accounts[$destination])) {
            $accounts[$destination] = ['id' => $destination, 'balance' => $amount];
        } else {
            $accounts[$destination]['balance'] += $amount;
        }
        http_response_code(201);
        echo json_encode(['destination' => $accounts[$destination]]);
    } elseif ($data['type'] === 'withdraw') {
        $origin = $data['origin'];
        $amount = $data['amount'];
        if (!isset($accounts[$origin]) || $accounts[$origin]['balance'] < $amount) {
            http_response_code(404);
            echo json_encode(0);
        } else {
            $accounts[$origin]['balance'] -= $amount;
            http_response_code(201);
            echo json_encode(['origin' => $accounts[$origin]]);
        }
    } elseif ($data['type'] === 'transfer') {
        $origin = $data['origin'];
        $destination = $data['destination'];
        $amount = $data['amount'];
        if (!isset($accounts[$origin]) || $accounts[$origin]['balance'] < $amount) {
            http_response_code(404);
            echo json_encode(0);
        } else {
            if (!isset($accounts[$destination])) {
                $accounts[$destination] = ['id' => $destination, 'balance' => 0];
            }
            $accounts[$origin]['balance'] -= $amount;
            $accounts[$destination]['balance'] += $amount;
            http_response_code(201);
            echo json_encode(['origin' => $accounts[$origin], 'destination' => $accounts[$destination]]);
        }
    } else {
        http_response_code(400);
    }
    exit();
}

http_response_code(404);
echo json_encode(0);
