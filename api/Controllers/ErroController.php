<?php
class ErroController extends Controller
{
    public function index($param)
    {
        http_response_code(404);
    }
}
