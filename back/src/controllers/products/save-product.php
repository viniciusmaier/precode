<?php
    require_once '../../repositories/product.respository.php';

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(["error" => "JSON inválido"]);
        exit;
    }

    $product = $data ?? [];
    $sku = $product["sku"] ?? null;
    $name = $product["name"] ?? "";
    $description = $product["description"] ?? "";
    $shortName = $product["shortName"] ?? "";
    $status = $product["status"] ?? "enabled";
    $wordKeys = $product["wordKeys"] ?? "";
    $price = $product["price"] ?? 0;
    $promotional_price = $product["promotional_price"] ?? 0;
    $cost = $product["cost"] ?? 0;
    $weight = $product["weight"] ?? 0;
    $width = $product["width"] ?? 0;
    $height = $product["height"] ?? 0;
    $length = $product["length"] ?? 0;
    $brand = $product["brand"] ?? "";
    $urlYoutube = $product["urlYoutube"] ?? "";
    $googleDescription = $product["googleDescription"] ?? "";
    $manufacturing = $product["manufacturing"] ?? "";
    $nbm = $product["nbm"] ?? "";
    $model = $product["model"] ?? "";
    $gender = $product["gender"] ?? "";
    $volumes = $product["volumes"] ?? 0;
    $warrantyTime = $product["warrantyTime"] ?? 0;
    $category = $product["category"] ?? "";
    $subcategory = $product["subcategory"] ?? "";
    $endcategory = $product["endcategory"] ?? "";
    $attributes = $product["attribute"] ?? [];
    $variations = $product["variations"] ?? [];

    $repository = new ProductRepository();
        
    $result = $repository->upsert($product);

    if (!$data) {
        http_response_code(200);
        echo json_encode(["message" => "Produto salvo com sucesso!"]);
        exit;
    }

    if(!$result) {
        http_response_code(400);
        echo json_encode(["erro" => "Falha ao atualizar produto!"]);
        exit;  
    }
    else {
        http_response_code(200);
        echo json_encode(["success" => "Produto salvo com sucesso!"]);
        exit;  
    }
    

    

    