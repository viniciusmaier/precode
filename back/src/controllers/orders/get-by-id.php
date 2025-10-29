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

    $orderId = $_GET['order_id'] ?? null;

    if (!$orderId) {
        http_response_code(400);
        echo json_encode(["erro" => "Pedido não encontrado"]);
        exit;
    }

    $repository = new OrderRepository();        
    $result = $repository->getById($orderId);

    http_response_code(200);
    echo json_encode(["order" => $result]);