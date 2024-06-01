<?php  

$acconts = [];  

    if($_SERVER['REQUEST_METHOD'] === 'POST' and $_SERVER['REQUEST_URI'] === '/reset'){
        $acconts = [];     
        http_response_code(200);     
        exit(); 
    }  

    if($_SERVER['REQUEST_METHOD'] === 'GET' and $_SERVER['REQUEST_URI'] === '/balance'){     
        $accont_id = $_GET['account_id'] ?? null;     
        if(isset($accounts[$account_id])){         
            http_response_code(200);         
            echo json_encode($acconts[$accont_id]);     
        }else {     
            http_response_code(404);    
        }     
        exit(); 
    } 
        
    if($_SERVER['REQUEST_METHOD'] === 'POST' and $_SERVER['REQUEST_URI'] === '/event'){     
        $data = json_decode(file_get_contents('php://input'), true);     
        if ($data['type'] === 'deposit') {         
            $destination = $data['destination'];         
            $amout = $data['amount'];
            if(!isset($accounts[$destination])) {
                $acconts[$destination] = ['id' => $destination, 'balance' => $amout];
            }else{
                $acconts[$destination]['balance'] += $amout;
            }
            http_response_code(201);
            echo json_encode(['origin' => $acconts[$origin]]);
        } 
    }elseif ($data['type'] === 'withdraw'){
        $origin = $data['origin'];
        $amout = $data['amout'];
        if(!isset($acconts[$origin]) or $acconts[$origin]['balance']< $amout) {
            http_response_code(404);
        }else{
        $acconts[$origin]['balance'] -= $amout;
        http_response_code(201);
        echo json_encode(['origin' => $acconts[$origin]]);
        }
    }elseif($data['type'] === 'transfer'){
        $origin = $data['orgin'];
        $destination = $data['destination'];
        $amout = $data['amout'];
        if(!isset($acconts[$origin]) or !isset($acconts[$destination]) or $acconts[$origin]['balance'] < $amout){
            http_response_code(404);
        } else {
            $acconts[$origin]['balance'] -= $amout;       
            $acconts[$destination]['balance'] += $amout;
            http_response_code(201);
            echo json_encode(['origin' => $acconts[$origin], 'destination' => $acconts[$destination]]);            
        }
    } else {
        http_response_code(400);
    }
    exit();

}
http_response_code(404);
