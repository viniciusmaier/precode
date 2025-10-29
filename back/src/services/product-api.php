<?php

class ProductApiClientPrecode
{
    private $baseUrl;
    private $apiKey;

    public function __construct()
    {
        $this->baseUrl = 'https://www.replicade.com.br/api/';
        $this->apiKey = 'aXdPMzVLZ09EZnRvOHY3M1I6';
    }

    public function updatePriceStock(array $products)
    {
        $payload = json_encode(['products' => $products], JSON_UNESCAPED_UNICODE);

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        if ($this->apiKey) {
            $headers[] = 'Authorization: Basic ' . $this->apiKey; // Ajustado para Basic Auth
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . 'v3/products/inventory',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return [
                'code' => 30,
                'message' => 'Erro inesperado: ' . $error,
            ];
        }

        curl_close($ch);

        $data = json_decode($response, true);

        if (!is_array($data)) {
            return [
                'http_code' => $httpCode,
                'message' => 'Resposta inválida da API',
                'raw' => $response
            ];
        }

        return [
            'http_code' => $httpCode,
            'response' => $data
        ];
    }
    
    public function isAlreadyRegistred(?string $codigo, string $tipo = 'sku')
    {
        $tipo = strtolower($tipo);
        if (!in_array($tipo, ['sku', 'ref', 'group'])) {
            return [
                'code' => 400,
                'message' => "Tipo inválido. Use 'sku', 'ref' ou 'group'."
            ];
        }

        $url = $this->baseUrl . "v3/products/query/{$codigo}/{$tipo}";

        $headers = [
            'Accept: application/json',
        ];

        if ($this->apiKey) {
            $headers[] = 'Authorization: Basic ' . $this->apiKey;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return [
                'code' => 30,
                'message' => 'Erro inesperado: ' . $error,
            ];
        }

        curl_close($ch);

        $data = json_decode($response, true);

        if (!is_array($data)) {
            return [
                'http_code' => $httpCode,
                'message' => 'Resposta inválida da API',
                'raw' => $response
            ];
        }
        return [
            'http_code' => $httpCode,
            'product' => $data['produto']
        ];
    }

    public function sendProduct(array $product)
    {
        $payload = json_encode(['product' => $product], JSON_UNESCAPED_UNICODE);
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        if ($this->apiKey) {
            $headers[] = 'Authorization: Basic ' . $this->apiKey;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . 'v3/products',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return [
                'code' => 30,
                'message' => 'Erro inesperado: ' . $error,
            ];
        }

        curl_close($ch);

        $data = json_decode($response, true);

        if (!is_array($data)) {
            return [
                'code' => $httpCode,
                'message' => 'Resposta inválida da API',
                'raw' => $response
            ];
        }

        return [
            'http_code' => $httpCode,
            'response' => $data
        ];
    }

    public static function getDefaultProduct()
    {
        return [
            "sku" => null,
            "name" => "",
            "description" => "",
            "shortName" => "",
            "status" => "enabled",
            "price" => 0,
            "promotional_price" => 0,
            "cost" => 0,
            "weight" => 0,
            "width" => 0,
            "height" => 0,
            "length" => 0,
            "brand" => "",
            "variations" => [
                [
                    "sku" => null,
                    "qty" => "0",
                    "ean" => "",
                ]
            ]
        ];
    }
}
