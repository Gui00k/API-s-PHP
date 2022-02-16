<?php
$host = 'localhost';
$name = 'db_waifu';
$user = 'app';
$pass = 'z&Y2pyUvys4fIAy*r$AFgbPnZSD';

$method = $_SERVER['REQUEST_METHOD'];

if ($method != 'GET') {
    http_response_code(500);
    return;
} //method not is GET

header('Content-Type: application/json; charset=utf-8');

$addres = @$_GET['address'];
if (!$addres) {
    echo json_encode(['status' => 'failed']);
    return;
} //No have addres

$conexaoDb = new mysqli($host,  $user, $pass, $name);

$sql = "SELECT user_balance FROM tb_users WHERE user_address = ?";
$stmt = $conexaoDb->prepare($sql);
$stmt->bind_param('s', $addres);
$stmt->execute();

$result = @$stmt->get_result()->fetch_all(MYSQLI_ASSOC)[0]['user_balance'];

if (!$result) {
    echo json_encode(['status' => 'failed']);
    return;
} //No have addres
$result = ['balance' => $result];
//Send amount
echo json_encode($result);
