<?php
$method = $_SERVER['REQUEST_METHOD'];
if ($method != 'POST') {
    http_response_code(404);
    return;
} //method not is POST 

header('Content-Type: application/json; charset=utf-8');

//Pegando dados
$data = json_decode(file_get_contents("php://input"), true);
$address = @$data['address'];

if (!$address) {
    echo json_encode(['status' => 'failed']);
    return;
} //No have data

echo json_encode(['buy' => 'buiado']);
