<?php
require_once '../../repositories/product.respository.php';

header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(["erro" => "SKU não fornecido"]);
    exit;
}

$repository = new ProductRepository();
$result = $repository->getById($id);

if (!$result) {
    http_response_code(404);
    echo json_encode(["erro" => "Produto não encontrado"]);
    exit;
}

http_response_code(200);
echo json_encode(["product" => $result]);