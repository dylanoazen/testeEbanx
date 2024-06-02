<?php  
header('Content-Type: application/json');

// Define the path to the JSON file
$jsonFilePath = 'accounts.json';

// Load existing account data from the JSON file
$accounts = [];
if (file_exists($jsonFilePath)) {
    $jsonAccounts = file_get_contents($jsonFilePath);
    $accounts = json_decode($jsonAccounts, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' and $_SERVER['REQUEST_URI'] === '/reset') {
    $accounts = []; // Reset accounts data
    saveDataToJsonFile($accounts, $jsonFilePath); // Save updated data to the JSON file
    http_response_code(200);
    echo 'OK';
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $request_uri = $_SERVER['REQUEST_URI'];
    $parsed_url = parse_url($request_uri);

    if ($parsed_url['path'] === '/balance') {
        $account_id = $_GET['account_id'] ?? null;
        $exist = accountFinder($account_id);

        if ($exist != null) {
            http_response_code(200);
            echo json_encode($exist['balance']);
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
        
        if ($exist == null) {
            $exist['balance'] = $amount;
        } else {
            $exist['balance'] += $amount;
        }
        $accounts[$destination] = ['id' => $destination, 'balance' => $exist['balance']];
        
        saveDataToJsonFile($accounts, $jsonFilePath); // Save updated data to the JSON file
        http_response_code(201);
        echo json_encode(['destination' => accountFinder($destination)]);
    } elseif ($data['type'] === 'withdraw') {
        $origin = $data['origin'];
        $amount = $data['amount'];
        $exist = accountFinder($origin);
        if (!isset($accounts[$origin]) || $accounts[$origin]['balance'] < $amount) {
            http_response_code(404);
            echo 0;
        } else {
            $accounts[$origin]['balance'] -= $amount;
            saveDataToJsonFile($accounts, $jsonFilePath); // Save updated data to the JSON file
            //justo to the array json on the text be te same
            $originArray['origin'] = ['id'=> $origin, 'balance'=> $accounts[$origin]['balance']];
            http_response_code(201);
            echo json_encode($originArray);
        }
    } elseif ($data['type'] === 'transfer') {
        $origin = $data['origin'];
        $destination = $data['destination'];
        $amount = $data['amount'];

        $existOrigin = accountFinder($origin);
        $existDestination = accountFinder($destination);

        if ($existOrigin != null && $existOrigin['balance'] >= $amount) {
            $accounts[$origin]['balance'] -= $amount;
            $accounts[$destination]['id'] = $destination;
            $accounts[$destination]['balance'] += $amount;
            saveDataToJsonFile($accounts, $jsonFilePath); // Save updated data to the JSON file
            http_response_code(201);
            echo json_encode(['origin' => $accounts[$origin], 'destination' => $accounts[$destination]]);
        } else {
            http_response_code(404);
            echo '0';
        }
    } else {
        http_response_code(400);
    }
} else {
    http_response_code(404);
}

function accountFinder($id) {
    global $accounts;
    if (isset($accounts[$id])) {
        return $accounts[$id];
    } else {
        return null;
    }
}

function saveDataToJsonFile($data, $filePath) {
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($filePath, $jsonData);
}