<?php
date_default_timezone_set('America/Sao_Paulo');
//Credenciais do bd
$host = 'localhost';
$name = 'db_waifu';
$user = 'app';
$pass = 'z&Y2pyUvys4fIAy*r$AFgbPnZSD';

//Validando método de solicitação
$method = $_SERVER['REQUEST_METHOD'];

if ($method != 'GET') {
    http_response_code(404);
    return;
} //method not is GET

//Configurando header com json
header('Content-Type: application/json; charset=utf-8');

//Pegando dados enviados no get
$data = $_GET;
$address = @$data['address'];

if (!$address) {
    echo json_encode(['status' => 'failed']);
    return;
} //No have data

//Buscando usuario no banco de dados 
$conexaoDb = new mysqli($host,  $user, $pass, $name);
$sql = "SELECT * FROM tb_users WHERE user_address = ?";
$stmt = $conexaoDb->prepare($sql);
$stmt->bind_param('s', $address);
$stmt->execute();
$usuario = @$stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($usuario)) {
    echo json_encode(['status' => 'failed']);
    return;
}

echo json_encode(getCards(pegarCartas($address)));

function pegarCartas($address)
{
    global $host, $name, $user, $pass;
    $conexaoDb = new mysqli($host,  $user, $pass, $name);
    $sql = "SELECT tb_balances.balance_id AS 'balanceId',
                   tb_assets.asset_id AS 'cardId',
                   tb_balances.balance_unlock AS 'dataDesbloqueio',
                   tb_balances.balance_value AS 'value',
                   tb_cards.card_name AS 'name', 
                   tb_cards.card_type AS 'type', 
                   tb_cards.card_img_src AS 'src'
            FROM tb_assets
            INNER JOIN tb_balances ON tb_balances.asset_id = tb_assets.asset_id
            INNER JOIN  tb_cards ON tb_cards.card_id = tb_assets.card_id
            WHERE tb_assets.user_address = ?;";
    $stmt = $conexaoDb->prepare($sql);
    $stmt->bind_param('s', $address);
    $stmt->execute();
    $data =  @$stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (!$data) {
        echo json_encode(['status' => 'failed']);
        return;
    }
    return $data;
}

function getCards($data)
{
    $card_list = [];
    foreach ($data as $card) {
        //Criando objeto do Balance
        $balance = [
            'id' => $card['balanceId'],
            'value' => $card['value'],
            'dataDesbloqueio' => $card['dataDesbloqueio'],
        ];

        //verificando se o card já foi inserido
        $cardIdNaoExiste = true;
        foreach ($card_list as $card_formatado) {
            if ($card['cardId'] == $card_formatado['id']) {
                $index = array_search($card_formatado, $card_list);
                array_push($card_list[$index]['balances'], $balance);
                $cardIdNaoExiste = false;
            }
        }
        if ($cardIdNaoExiste) {
            array_push($card_list, [
                'id' => $card['cardId'],
                'name' => $card['name'],
                'type' => $card['type'],
                'src' => $card['src'],
                'balances' => [$balance]
            ]);
        }
    }
    return $card_list;
}
