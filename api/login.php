<?php
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
$sql = "SELECT COUNT(*) AS resultado FROM tb_users WHERE user_address = ?";
$stmt = $conexaoDb->prepare($sql);
$stmt->bind_param('s', $address);
$stmt->execute();
$result = @$stmt->get_result()->fetch_all(MYSQLI_ASSOC);

//verificando se usuario esta cadastrado
$usuarioCadastrado = $result[0]['resultado'];
if ($usuarioCadastrado) {
    echo json_encode(['status' => 'succesfully']);
    return;
};

//Inserindo usuario no banco de dados
$conexaoDb = new mysqli($host,  $user, $pass, $name);
$sql = "INSERT INTO tb_users(user_address) VALUES (?)";
$stmt = $conexaoDb->prepare($sql);
$stmt->bind_param('s', $address);

//Testando se tudo ocorreu bem
if (!$stmt->execute()) {
    echo json_encode(['status' => 'failed']);
    return;
}

//Pegando transações para salvar no banco de dados
$transactionList = [];
$pageSize = 20;
$pageNumber = 0;
do {
    $haveData = true;
    //Iniciando conexão com morales
    $url  = "https://deep-index.moralis.io/api/v2/$address/erc20/transfers?chain=bsc%20testnet&limit=$pageSize&offset=$pageNumber";
    $headers = ['X-API-Key: HG2arGB4kv14ybDyhRtV29xFtS8iZ3E7cHsFweQnGUBHZI1St2hklDpO6WognuyA'];
    $curl   = curl_init();

    //Configurando request
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_RETURNTRANSFER => true
    ]);

    //Buscando resultado
    $result = json_decode(curl_exec($curl), true);

    //Finalizando conexão
    curl_close($curl);

    //Adcionando respostas a um array
    $transactionList = array_merge($transactionList, $result['result']);

    //Verificando se o loop deve finalizar
    $pageNumber += $pageSize;

    $quantidadePaginas = $result['total'];

    $haveData = $pageNumber <= $quantidadePaginas;
} while ($haveData);

//registrando transações
$valorBalance = 0;
foreach ($transactionList as $transaction) {
    $hash = $transaction['transaction_hash'];
    $value = $transaction['value'] / 1000000000000000000;
    $valorBalance += $value;

    $conexaoDb = new mysqli($host,  $user, $pass, $name);
    $sql = "INSERT INTO tb_deposit(user_address, deposit_hash, deposit_value) VALUES (?, ?, ?)";
    $stmt = $conexaoDb->prepare($sql);
    $stmt->bind_param('ssd', $address, $hash, $value);

    //testando se tudo ocorreu bem
    if (!$stmt->execute()) {
        var_dump($stmt->error);
        echo json_encode(['status' => 'failed']);
        return;
    }
}


//Somando ao balance do usuario
$conexaoDb = new mysqli($host,  $user, $pass, $name);

$sql = "UPDATE tb_users SET user_balance = user_balance + ? WHERE user_address = ?";
$stmt = $conexaoDb->prepare($sql);
$stmt->bind_param('ds', $valorBalance, $address);
$stmt->execute();

//testando se tudo ocorreu bem
if (!$stmt->affected_rows) {
    echo json_encode(['status' => 'failed']);
    return;
}

echo json_encode(['status' => 'succesfully']);
