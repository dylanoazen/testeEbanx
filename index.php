<?php  
session_set_cookie_params(3600);
session_start();

if (!isset($_SESSION['accounts'])) {
    $_SESSION['accounts'] = [];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' and $_SERVER['REQUEST_URI'] === '/reset') {
    $_SESSION['accounts'] = []; 
    http_response_code(200);
    echo 'OK';
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $request_uri = $_SERVER['REQUEST_URI'];
    $parsed_url = parse_url($request_uri);

    if ($parsed_url['path'] === '/balance') {
        $account_id = $_GET['account_id'] ?? null;
        $exist = accountFinder($account_id);

        if ($exist != 'N') {
            http_response_code(200);
            echo json_encode($exist);
        } else {
            http_response_code(404);
            echo '0';
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' and $_SERVER['REQUEST_URI'] === '/event') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data['type'] === 'deposit') {
        $destination = $data['destination'];
        $amount = $data['amount'];
        $exist = accountFinder($destination);
        if ($exist == '1') {
            $_SESSION['accounts'][$destination] = ['id' => $destination, 'balance' => $amount];
            $newArray = accountFinder($destination);
            http_response_code(201);
            echo json_encode(['destination' => $newArray]);
        } else {
            $accountNewValue = $exist['balance'] + $amount;
            $exist['balance'] = $accountNewValue;
            http_response_code(201);
            echo json_encode(['destination' => $exist]); 
        }
    } elseif ($data['type'] === 'withdraw') {
        $origin = $data['origin'];
        $amount = $data['amount'];
        if(!isset($_SESSION['accounts'][$origin]) or $_SESSION['accounts'][$origin]['balance'] < $amount){
            http_response_code(404);
        }else{
            $_SESSION['accounts'][$origin]['balance'] -= $amount;
            http_response_code(201);
            echo json_encode(['origin' => $_SESSION['accounts'][$origin]]);
        }
    } elseif ($data['type'] === 'transfer') {
        $origin = $data['origin'];
        $destination = $data['destination'];
        $amount = $data['amount'];
        
        $existOrigin = accountFinder($origin);
        $existDestination = accountFinder($destination);
        
        if ($existOrigin != 'N' && $existDestination != 'N' && $existOrigin['balance'] >= $amount) {
            $_SESSION['accounts'][$origin]['balance'] -= $amount;       
            $_SESSION['accounts'][$destination]['balance'] += $amount;
            http_response_code(201);
            echo json_encode(['origin' => $_SESSION['accounts'][$origin], 'destination' => $_SESSION['accounts'][$destination]]); 
        } else {
            http_response_code(404);
            echo '0';
        }              
    }else{
        http_response_code(400);
    }
}else {
    http_response_code(404);
}

function accountFinder($id) {
    if (isset($_SESSION['accounts'][$id])) {
        return $_SESSION['accounts'][$id];
    } else {
        return 'N';
    }
}
