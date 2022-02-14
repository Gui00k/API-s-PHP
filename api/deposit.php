<?php

$host = 'localhost';
$name = 'db_waifu';
$user = 'app';
$pass = 'z&Y2pyUvys4fIAy*r$AFgbPnZSD';

$method = $_SERVER['REQUEST_METHOD'];

if ($method != 'POST') {
    http_response_code(404);
    return;
} //method not is POST 

header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);

$addres = @$data['addres'];
$hash = @$data['hash'];
$value = @$data['value'];

if (!$addres && !$hash && !$value) {
    echo json_encode(['status' => 'failed']);
    return;
} //No have data


$conexaoDb = new mysqli($host,  $user, $pass, $name);

//Inserindo deposito
$sql = "INSERT INTO tb_deposit(user_addres, deposit_hash, deposit_value) VALUES (?, ?, ?)";
$stmt = $conexaoDb->prepare($sql);
$stmt->bind_param('ssd', $addres, $hash, $value);
$stmt->execute();

//testando se tudo ocorreu bem
/* if (!$stmt->get_result()) {
    echo json_encode(['status' => 'failed']);
    return;
} */

//Pegando balance atual
$conexaoDb = new mysqli($host,  $user, $pass, $name);

$sql = "SELECT user_balance FROM tb_users WHERE user_addres = ?";
$stmt = $conexaoDb->prepare($sql);
$stmt->bind_param('s', $addres);
$stmt->execute();
$balanceUser = $stmt->get_result()->fetch_all(MYSQLI_ASSOC)[0]['user_balance'];



//Somando ao balance do usuario
$conexaoDb = new mysqli($host,  $user, $pass, $name);

$newBalanceUser = $balanceUser + $value;

$sql = "UPDATE tb_users SET user_balance = ? WHERE user_addres = ?";
$stmt = $conexaoDb->prepare($sql);
$stmt->bind_param('ds', $newBalanceUser, $addres);
$stmt->execute();

//testando se tudo ocorreu bem
/* if (!$stmt->rowCount()) {
    echo json_encode(['status' => 'failed']);
    return;
} */

//enviando status de sucesso
echo json_encode(['status' => 'succesfully']);
