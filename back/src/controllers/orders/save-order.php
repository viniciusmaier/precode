<?php
    require_once '../../repositories/order.repository.php';
    require_once '../../services/order-api.php';

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
        echo json_encode(["erro" => "JSON inválido"]);
        exit;
    }

    $cliente = [
        "cpf_cnpj" => $data["cpfCnpj"] ?? null,
        "nome_razao" => $data["nomeRazao"] ?? null,
        "fantasia" => $data["fantasia"] ?? null,
        "email" => $data["email"] ?? null,
        "contato_residencial" => $data["residencial"] ?? null,
        "contato_comercial" => $data["comercial"] ?? null,
        "contato_celular" => $data["celular"] ?? null,
        "responsavel_recebimento" => $data["responsavelRecebimento"] ?? null,
        "endereco" => $data["endereco"] ?? null,
        "numero" => $data["numero"] ?? null,
        "bairro" => $data["bairro"] ?? null,
        "complemento" => $data["complemento"] ?? null,
        "cep" => $data["cep"] ?? null,
        "cidade" => $data["cidade"] ?? null,
        "uf" => $data["uf"] ?? null,
    ];

    $requiredCliente = [
        "cpf_cnpj", "nome_razao", "email",
        "contato_celular", "responsavel_recebimento",
        "endereco", "numero", "bairro", "cidade", "uf", "cep"
    ];

    foreach ($requiredCliente as $field) {
        if (empty($cliente[$field])) {
            http_response_code(400);
            echo json_encode(["erro" => "Campo obrigatório do cliente ausente: $field"]);
            exit;
        }
    }

    if (!isset($data['produtos']) || !is_array($data['produtos']) || empty($data['produtos'])) {
        http_response_code(400);
        echo json_encode(["erro" => "Campo obrigatório 'produtos' ausente ou vazio"]);
        exit;
    }

    $produtos = [];
    foreach ($data['produtos'] as $i => $produto) {
        if (!isset($produto['sku'], $produto['valorUnitario'], $produto['quantidade'])) {
            http_response_code(400);
            echo json_encode(["erro" => "Campos obrigatórios ausentes no produto $i"]);
            exit;
        }
        $produtos[] = [
            "sku" => intval($produto['sku']),
            "quantidade" => intval($produto['quantidade']),
            "valorUnitario" => floatval($produto['valorUnitario'])
        ];
    }

    $pedido = [
        "produtos" => $produtos,
        "valor" => floatval($data['valor'] ?? 0),
        "quantidade_parcelas" => intval($data['quantidadeParcelas'] ?? 1),
        "meio_pagamento" => $data['meioPagamento'] ?? '',
        "valor_frete" => floatval($data['valorFrete'] ?? 0),
        "forma_pagamento" => $data['formaPagamento'] ?? '',
        "prazo_entrega" => intval($data['prazoEntrega'] ?? 0),
    ];

    $repository = new OrderRepository();
    $pedidoId = $repository->upsertPedidoTransaction($cliente, $pedido);

    if (!$pedidoId) {
        http_response_code(400);
        echo json_encode(["erro" => "Falha ao salvar pedido localmente"]);
        exit;
    }

    $apiClient = new OrderApiClientPrecode();

    try {
        $data['idPedidoParceiro'] = $pedidoId;
        $apiResponse = $apiClient->createOrder([
            "idPedidoParceiro" => $pedidoId,
            "valorFrete" => $pedido["valor_frete"],
            "prazoEntrega" => $pedido["prazo_entrega"],
            "valorTotalCompra" => $pedido["valor"],
            "formaPagamento" => $pedido["forma_pagamento"],
            "dadosCliente" => $cliente,
            "itens" => $produtos,
            "pagamento" => $data["pagamento"] ?? []
        ]);

        if( $apiResponse['sucesso']['pedido']['numeroPedido']){
            $repository->updateNumeroPedido((string)$pedidoId, $apiResponse['sucesso']['pedido']['numeroPedido']);
        }

        http_response_code(200);
        echo json_encode([
            "sucesso" => "pedido salvo"
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "erro" => "Erro ao enviar pedido para API",
            "mensagem" => $e->getMessage()
        ]);
    }
