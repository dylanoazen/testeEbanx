<?php  

$accounts = [];  

if($_SERVER['REQUEST_METHOD'] === 'POST' and $_SERVER['REQUEST_URI'] === '/reset'){
    $accounts = [];     
    http_response_code(200);     
    exit(); 
}  

if($_SERVER['REQUEST_METHOD'] === 'GET' and $_SERVER['REQUEST_URI'] === '/balance'){     
    $account_id = $_GET['account_id'] ?? null;     
    if(isset($accounts[$account_id])){         
        http_response_code(200);         
        echo json_encode($accounts[$account_id]);     
    }else{     
        http_response_code(404);    
    }     
    exit(); 
} 

if ($_SERVER['REQUEST_METHOD'] === 'POST' and $_SERVER['REQUEST_URI'] === '/event'){     
    $data = json_decode(file_get_contents('php://input'), true);     
    if ($data['type'] === 'deposit') {         
        $destination = $data['destination'];         
        $amount = $data['amount'];
        if(!isset($accounts[$destination])) {
            $accounts[$destination] = ['id' => $destination, 'balance' => $amount];
        }else {
            $accounts[$destination]['balance'] += $amount;
        }
        http_response_code(201);
        echo json_encode(['destination' => $accounts[$destination]]);
    }elseif ($data['type'] === 'withdraw') {
        $origin = $data['origin'];
        $amount = $data['amount'];
        if(!isset($accounts[$origin]) or $accounts[$origin]['balance'] < $amount){
            http_response_code(404);
        }else{
            $accounts[$origin]['balance'] -= $amount;
            http_response_code(201);
            echo json_encode(['origin' => $accounts[$origin]]);
        }
    }elseif ($data['type'] === 'transfer'){
        $origin = $data['origin'];
        $destination = $data['destination'];
        $amount = $data['amount'];
        if(!isset($accounts[$origin]) or !isset($accounts[$destination]) or $accounts[$origin]['balance'] < $amount){
            http_response_code(404);
        } else {
            $accounts[$origin]['balance'] -= $amount;       
            $accounts[$destination]['balance'] += $amount;
            http_response_code(201);
            echo json_encode(['origin' => $accounts[$origin], 'destination' => $accounts[$destination]]);            
        }
    }else{
        http_response_code(400);
    }
    exit();
}

http_response_code(404);
