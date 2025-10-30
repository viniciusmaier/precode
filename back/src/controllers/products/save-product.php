<?php
    require_once '../../repositories/product.respository.php';
    require_once '../../services/product-api.php';

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    $repository = new ProductRepository();
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    if (!$data || !isset($data['name']) || !isset($data['description'])) {
        http_response_code(400);
        echo json_encode(["erro" => "JSON inválido ou campos obrigatórios ausentes (name, description)."]);
        exit;
    }

    $product = [
        "sku" => $data["sku"] ?? null,
        "name" => $data["name"] ?? "",
        "brand" => $data["brand"] ?? "",
        "description" => $data["description"] ?? "",
        "shortName" => $data["shortName"] ?? "",
        "status" => $data["status"] ?? "enabled",
        "price" => isset($data["price"]) ? (float)$data["price"] : 0,
        "promotional_price" => isset($data["promotional_price"]) ? (float)$data["promotional_price"] : 0,
        "cost" => isset($data["cost"]) ? (float)$data["cost"] : 0,
        "weight" => isset($data["weight"]) ? (float)$data["weight"] : 0,
        "width" => isset($data["width"]) ? (float)$data["width"] : 0,
        "height" => isset($data["height"]) ? (float)$data["height"] : 0,
        "length" => isset($data["length"]) ? (float)$data["length"] : 0,
        "variations" => []
    ];

    if (!empty($data["attribute"]) && is_array($data["attribute"])) {
        foreach ($data["attribute"] as $attr) {
            $product["attribute"][] = [
                "key" => $attr["key"] ?? "",
                "value" => $attr["value"] ?? ""
            ];
        }
    }

    if (!empty($data["variations"]) && is_array($data["variations"])) {
        foreach ($data["variations"] as $variation) {
            $specifications = [];
            if (!empty($variation["specifications"]) && is_array($variation["specifications"])) {
                foreach ($variation["specifications"] as $spec) {
                    $specifications[] = [
                        "key" => $spec["key"] ?? "",
                        "value" => $spec["value"] ?? ""
                    ];
                }
            }

            $product["variations"][] = [
                "sku" => isset($variation["sku"]) ? (int)$variation["sku"] : null,
                "qty" => isset($variation["qty"]) ? (int)$variation["qty"] : 0,
                "ean" => $variation["ean"] ?? "",
                "specifications" => $specifications,
            ];
        }
    } else {
        http_response_code(400);
        echo json_encode(["erro" => "O campo 'variations' é obrigatório e deve conter pelo menos uma variação."]);
        exit;
    }

    
    $apiClient = new ProductApiClientPrecode();
    $updatedStockApi = false;
    try {
        foreach ($product["variations"] as &$variation) {
            $repository->saveVariations($product['sku'], [
                "sku" => $variation["sku"],
                "qty" => isset($variation["qty"]) ? (int)$variation["qty"] : 0,
                "ean" => $variation["ean"] ?? "",
                "specifications" => $specifications,
            ]);
            
            $isRegistered = false;

            if (!empty($variation['sku'])) {
                $alreadyRegistred = $apiClient->isAlreadyRegistred($variation['sku']);

                if (!empty($alreadyRegistred['product'])) {
                    $isRegistered = true;

                    $updatePayload = [
                        [
                            "sku" => $variation['sku'] ?? null,
                            "ref" => $variation['ref'] ?? "",
                            "price" => $product["price"],
                            "promotional_price" => $product["promotional_price"],
                            "cost" => $product["cost"],
                            "status" => $product["status"],
                            "shippingTime" => $data["shippingTime"] ?? 0,
                            "stock" => $variation["stock"] ?? [
                                [
                                    "stores" => 1,
                                    "availableStock" => $variation["qty"],
                                    "realStock" => $variation["qty"]
                                ]
                            ]
                        ]
                    ];

                    $apiClient->updatePriceStock($updatePayload);
                    $updatedStockApi = true;

                    $variation['sku'] = $alreadyRegistred['product']['sku'] ?? $variation['sku'];
                }
            }

            $variation['isRegistered'] = $isRegistered;
        }
        unset($variation);

        $apiResponse = $apiClient->sendProduct($product);

        if($updatedStockApi) {
            $result = $repository->upsert($product);
            if (!$result) {
                http_response_code(400);
                echo json_encode([
                    "erro" => "Falha ao Atualizar estoque e preço do produto.",
                    "etapa" => "banco_local"
                ]);
                exit;
            }

            http_response_code(200);
            echo json_encode([
                "success" => "Produto salvo localmente e atualizado na API com sucesso!",
                "sku" => $product["sku"],
                "variations" => $product["variations"],
                "api_response" => $apiResponse
            ]);
            exit;
        }

        if (in_array($apiResponse['http_code'], [200, 201, 204]) ) {
            $product['sku'] = $apiResponse['response']['sku'] ?? $product['sku'];
            if (!empty($apiResponse['response']['variations'])) {
                foreach ($apiResponse['response']['variations'] as $index => $apiVariation) {
                    if (isset($product['variations'][$index])) {
                        $product['variations'][$index]['sku'] = $apiVariation['sku'] ?? $product['variations'][$index]['sku'];
                    }
                }
            }

            $result = $repository->upsert($product);
            if (!$result) {
                http_response_code(400);
                echo json_encode([
                    "erro" => "Falha ao salvar produto localmente.",
                    "etapa" => "banco_local"
                ]);
                exit;
            }

            http_response_code(200);
            echo json_encode([
                "success" => "Produto salvo localmente e enviado para a API com sucesso!",
                "sku" => $product["sku"],
                "variations" => $product["variations"],
                "api_response" => $apiResponse
            ]);
            exit;
        } else {
            http_response_code(400);
            echo json_encode([
                "erro" => "Falha ao salvar produto. Motivo: " . ($apiResponse['response']['message'] ?? 'Não informado'),
                "etapa" => "api_externa"
            ]);
            exit;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "erro" => "Erro ao comunicar com API externa.",
            "mensagem" => $e->getMessage(),
            "etapa" => "api_externa"
        ]);
        exit;
    }
