<?php
    require_once '../../repositories/order.repository.php';
    require_once '../../services/order-api.php';

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Content-Type: application/json");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    $apiClient = new OrderApiClientPrecode();
    $result = $apiClient->cancelOrder($data['pedido_id'], $data['ecommerce_id']);
    $repository = new OrderRepository();        
    $repository->updateStatus($data['pedido_id'], 'CANCELED');
    
    http_response_code(200);
    echo json_encode(["sucesso" => $result['sucesso']['pedido']['mensagem']]);