<?php
date_default_timezone_set('America/Sao_Paulo');
//Credenciais do bd
$host = 'localhost';
$name = 'db_waifu';
$user = 'app';
$pass = 'z&Y2pyUvys4fIAy*r$AFgbPnZSD';

//Validando método de solicitação
$method = $_SERVER['REQUEST_METHOD'];

if ($method != 'POST') {
    http_response_code(404);
    return;
} //method not is GET

//Configurando header com json
header('Content-Type: application/json; charset=utf-8');

//Pegando dados enviados no get
$data = json_decode(file_get_contents("php://input"), true);;
$balanceId = @$data['balanceId'];

if (empty($balanceId)) {
    echo json_encode(['status' => 'failed']);
    return;
} //No have data

//Buscando balance no banco de dados 
$conexaoDb = new mysqli($host,  $user, $pass, $name);
$sql = "SELECT * 
        FROM tb_balances 
            INNER JOIN tb_assets ON tb_assets.asset_id = tb_balances.asset_id
            INNER JOIN  tb_users ON tb_users.user_address = tb_assets.user_address
        WHERE balance_id = ?";
$stmt = $conexaoDb->prepare($sql);
$stmt->bind_param('i', $balanceId);
$stmt->execute();
$balance = @$stmt->get_result()->fetch_all(MYSQLI_ASSOC)[0];

if (empty($balance)) {
    echo json_encode(['status' => 'failed']);
    return;
}

//verificar se a data de desbloqueio já passou
$dataAtual = date('Y/m/d H:i:s');
$strDataAtual = strtotime($dataAtual);

$dataDesbloqueio = $balance['balance_unlock'];
$strDataDesbloqueio = strtotime($dataDesbloqueio);


if ($strDataAtual < $strDataDesbloqueio) { //Balance bloqueado
    echo json_encode(['status' => 'failed']);
    return;
}

//apaga balance da tabela
$conexaoDb = new mysqli($host,  $user, $pass, $name);
$sql = "DELETE FROM tb_balances WHERE balance_id = ?";
$stmt = $conexaoDb->prepare($sql);
$stmt->bind_param('i', $balanceId);
$stmt->execute();

if ($stmt->affected_rows != 1) { //Erro ao deletar
    echo json_encode(['status' => 'failed']);
    return;
}

//insere valor no balance do usuario
$conexaoDb = new mysqli($host,  $user, $pass, $name);
$sql = "UPDATE tb_users SET user_balance = user_balance + ? WHERE user_address = ?";
$stmt = $conexaoDb->prepare($sql);
$stmt->bind_param('ds', $balance['balance_value'], $balance['user_address']);
$stmt->execute();

if (!$stmt->affected_rows) {
    echo json_encode(['status' => 'failed']);
    return;
}

//retorna successfully
echo json_encode(['status' => 'successfully']);