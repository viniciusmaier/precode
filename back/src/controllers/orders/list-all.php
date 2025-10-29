<?php
    require_once '../../repositories/order.repository.php';

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Content-Type: application/json");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    $filters = $_GET ?? [];

    $repository = new OrderRepository();        
    $result = $repository->listAll($filters);

    http_response_code(200);
    echo json_encode(["orders" => $result]);