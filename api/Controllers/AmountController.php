<?php
class AmountController extends Controller
{
    public function index($param)
    {
        $this->get($param);
    }
    public function get($addres)
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method != 'GET') {
            http_response_code(500);
            return;
        } //method not is GET 

        if (!$addres) {
            http_response_code(500);
            return;
        } //No have addres
        $conexaoDb = new Database();
        $conexaoDb = $conexaoDb->connection;

        $sql = "SELECT user_balance FROM tb_users WHERE user_addres = ?";
        $stmt = $conexaoDb->prepare($sql);
        $stmt->bind_param('s', $addres);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC)[0]['user_balance'];

        $result = ['balance' => $result];
        //Send amount
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
    }
}
