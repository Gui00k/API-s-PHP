<?php
date_default_timezone_set('America/Sao_Paulo');
//Checando se o metodo é POST
$method = $_SERVER['REQUEST_METHOD'];
if ($method != 'POST') {
    http_response_code(404);
    return;
} //method not is POST 

header('Content-Type: application/json; charset=utf-8');

//Credenciais do bd
$host = 'localhost';
$name = 'db_waifu';
$user = 'app';
$pass = 'z&Y2pyUvys4fIAy*r$AFgbPnZSD';

//Pegando dados
$data = json_decode(file_get_contents("php://input"), true);
$cardId = @$data['cardid'];
$address = @$data['address'];

if (is_null($cardId) || is_null($address)) {
    echo json_encode(['status' => 'failed']);
    return;
} //No have data

//Verifica se a carta pode ser usada(já se passaram 24 horas desde o ultimo uso)
$conexaoDb = new mysqli($host,  $user, $pass, $name);
$sql = "SELECT * FROM tb_assets WHERE asset_id = ?";
$stmt = $conexaoDb->prepare($sql);
$stmt->bind_param('i', $cardId);
$stmt->execute();
$asset = @$stmt->get_result()->fetch_all(MYSQLI_ASSOC)[0];
if (!$asset) {
    echo json_encode(['status' => 'failed']);
    return;
}

if ($asset['user_address'] != $address) {
    echo json_encode(['status' => 'failed']);
    return;
}

$dataAtual = date('Y/m/d H:i:s');
$strDataAtual = strtotime($dataAtual);

$dataDesbloqueio = $asset['asset_unlock'];
$strDataDesbloqueio = strtotime($dataDesbloqueio);


if ($strDataAtual > $strDataDesbloqueio) { //Carta bloqueada
    echo json_encode(['status' => 'failed']);
    return;
}

//Sorteia um valor de acordo com a classe da carta
$conexaoDb = new mysqli($host,  $user, $pass, $name);
$sql = "SELECT * FROM tb_cards WHERE card_id = ?";
$stmt = $conexaoDb->prepare($sql);
$stmt->bind_param('i', $asset['card_id']);
$stmt->execute();
$card = @$stmt->get_result()->fetch_all(MYSQLI_ASSOC)[0];

if (!$card) {
    echo json_encode(['status' => 'failed']);
    return;
}

$novoSaldo = 0;
if ($card['card_type'] == 'common') {
    $novoSaldo = rand(1, 10);
} else if ($$card['card_type'] == 'rare') {
    $novoSaldo = rand(1, 15);
} else if ($$card['card_type'] == 'legendary') {
    $novoSaldo = rand(1, 20);
} else if ($$card['card_type'] == 'epic') {
    $novoSaldo = rand(1, 25);
} else {
    echo json_encode(['status' => 'failed']);
    return;
}

//Insere saldo na tb_balances, com o campo balance_unlock para daqui a 5 dias
$dataDesbloqueioSaldo = date('Y/m/d H:i:s', strtotime("5 days", $strDataAtual));
$conexaoDb = new mysqli($host,  $user, $pass, $name);
$sql = "INSERT INTO tb_balances(balance_unlock, balance_value, asset_id) VALUES (?, ?, ?)";
$stmt = $conexaoDb->prepare($sql);
$stmt->bind_param('sdi', $dataDesbloqueioSaldo, $novoSaldo, $cardId);

//testando se tudo ocorreu bem
if (!$stmt->execute()) {
    echo json_encode(['status' => 'failed']);
    return;
}

//Bloqueia carta por 24 horas
$dataDesbloqueioConta = date('Y/m/d H:i:s', strtotime("1 days", $strDataAtual));

$conexaoDb = new mysqli($host,  $user, $pass, $name);
$sql = "UPDATE tb_assets SET asset_unlock = ? WHERE asset_id = ?";
$stmt = $conexaoDb->prepare($sql);
$stmt->bind_param('si', $dataDesbloqueioConta, $cardId);
$stmt->execute();

//testando se tudo ocorreu bem
if (!$stmt->affected_rows) {
    echo json_encode(['status' => 'failed']);
    return;
}

//Retorna valor gerado
echo json_encode(['valor' => $novoSaldo]);
