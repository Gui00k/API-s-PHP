<?php
//Checando se o metodo é POST
$method = $_SERVER['REQUEST_METHOD'];
if ($method != 'POST') {
    http_response_code(404);
    return;
} //method not is POST 

header('Content-Type: application/json; charset=utf-8');

//Pegando dados
$data = json_decode(file_get_contents("php://input"), true);
$cardId = @$data['cardid'];

if (is_null($cardId)) {
    echo json_encode(['status' => 'failed']);
    return;
} //No have data

//Verifica se a carta pode ser usada(já se passaram 24 horas desde o ultimo uso)
$conexaoDb = new mysqli($host,  $user, $pass, $name);
$sql = "SELECT * FROM tb_assets WHERE asset_id = ?";
$stmt = $conexaoDb->prepare($sql);
$stmt->bind_param('i', $cardId);
$stmt->execute();
$assets = @$stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$dataDesbloqueio = strtotime($assets[0]['asset_unlock']);
$dataAtual = strtotime(date('Y/m/d H:i:s'));
if ($dataDesbloqueio < $dataAtual) { //Carta bloqueada
    echo json_encode(['status' => 'failed']);
    return;
}

//Sorteia um valor de acordo com a classe da carta
$conexaoDb = new mysqli($host,  $user, $pass, $name);
$sql = "SELECT * FROM tb_cards WHERE card_id = ?";
$stmt = $conexaoDb->prepare($sql);
$stmt->bind_param('i', $assets[0]['card_id']);
$stmt->execute();
$card = @$stmt->get_result()->fetch_all(MYSQLI_ASSOC)[0];

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
$dataDesbloqueioSaldo = date('Y/m/d H:i:s', strtotime($dataAtual . ' + 5 days'));
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
$dataDesbloqueioConta = date('Y/m/d H:i:s', strtotime($dataAtual . ' + 1 days'));
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
