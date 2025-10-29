<?php

class OrderApiClientPrecode
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = 'https://www.replicade.com.br/api/';
        $this->apiKey = 'aXdPMzVLZ09EZnRvOHY3M1I6';
    }

    /**
     * Cria um pedido na API externa
     *
     * @param array $pedidoData Dados completos do pedido
     * @return array Retorna o código HTTP e a resposta decodificada da API
     * @throws Exception Caso ocorra erro de conexão ou resposta inválida
     */
    public function createOrder(array $pedidoData): array
    {
        $dadosCliente = $pedidoData['dadosCliente'] ?? [];
        print_r([
            "pedido" => [
                "idPedidoParceiro" => $pedidoData["idPedidoParceiro"] ?? "",
                "valorFrete" => floatval($pedidoData["valorFrete"] ?? 0),
                "prazoEntrega" => isset($pedidoData["prazoEntrega"]) ? (int)$pedidoData["prazoEntrega"] : null,
                "valorTotalCompra" => floatval($pedidoData["valorTotalCompra"] ?? 0),
                "formaPagamento" => ["VISA", "MASTERCARD"],
                "dadosCliente" => [
                    "cpfCnpj" => $dadosCliente["cpf_cnpj"] ?? "",
                    "nomeRazao" => $dadosCliente["nome_razao"] ?? "",
                    "fantasia" => $dadosCliente["fantasia"] ?? "",
                    "email" => $dadosCliente["email"] ?? "",
                    "dadosEntrega" => [
                        "cep" => $dadosCliente["cep"] ?? "",
                        "endereco" => $dadosCliente["endereco"] ?? "",
                        "numero" => $dadosCliente["numero"] ?? "",
                        "bairro" => $dadosCliente["bairro"] ?? "",
                        "cidade" => $dadosCliente["cidade"] ?? "",
                        "uf" => $dadosCliente["uf"] ?? "",
                        "complemento" => $dadosCliente["complemento"] ?? ""
                    ],
                    "telefones" => [
                        "residencial" => $dadosCliente["contato_residencial"] ,
                        "comercial" => $dadosCliente["contato_comercial"],
                        "celular" => $dadosCliente["contato_celular"],
                    ]
                ],
                "pagamento" => [
                    "valor" => $pedidoData['valorTotalCompra'],
                    "quantidadeParcelas" => 1,
                    "meioPagamento" => "MASTERCARD"
                ],
                "itens" => $pedidoData["itens"] ?? []
            ]
            ]);

        $payload = json_encode([
            "pedido" => [
                "idPedidoParceiro" => $pedidoData["idPedidoParceiro"] ?? "",
                "valorFrete" => floatval($pedidoData["valorFrete"] ?? 0),
                "prazoEntrega" => isset($pedidoData["prazoEntrega"]) ? (int)$pedidoData["prazoEntrega"] : null,
                "valorTotalCompra" => floatval($pedidoData["valorTotalCompra"] ?? 0),
                "formaPagamento" => ["VISA", "MASTERCARD"],
                "dadosCliente" => [
                    "cpfCnpj" => $dadosCliente["cpf_cnpj"] ?? "",
                    "nomeRazao" => $dadosCliente["nome_razao"] ?? "",
                    "fantasia" => $dadosCliente["fantasia"] ?? "",
                    "email" => $dadosCliente["email"] ?? "",
                    "dadosEntrega" => [
                        "cep" => $dadosCliente["cep"] ?? "",
                        "endereco" => $dadosCliente["endereco"] ?? "",
                        "numero" => $dadosCliente["numero"] ?? "",
                        "bairro" => $dadosCliente["bairro"] ?? "",
                        "cidade" => $dadosCliente["cidade"] ?? "",
                        "uf" => $dadosCliente["uf"] ?? "",
                        "complemento" => $dadosCliente["complemento"] ?? ""
                    ],
                    "telefones" => [
                        "residencial" => $dadosCliente["contato_residencial"] ,
                        "comercial" => $dadosCliente["contato_comercial"],
                        "celular" => $dadosCliente["contato_celular"],
                    ]
                ],
                "pagamento" => [
                    "valor" => $pedidoData['valorTotalCompra'],
                    "quantidadeParcelas" => 1
                ],
                "itens" => $pedidoData["itens"] ?? []
            ]
        ], JSON_UNESCAPED_UNICODE);
 

        $ch = curl_init($this->baseUrl . 'v1/pedido/pedido');

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Basic {$this->apiKey}"
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $responseRaw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Erro de conexão com API: $error");
        }

        curl_close($ch);

        $response = json_decode($responseRaw, true);
        print_r($response );

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Resposta inválida da API: $responseRaw");
        }

        return [
            "http_code" => $httpCode,
            "response" => $response
        ];
    }
}
