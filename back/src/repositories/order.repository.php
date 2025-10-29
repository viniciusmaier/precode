<?php
require_once '../../../config/postgres/adapter.php';

class OrderRepository {
    private $repository;

    public function __construct() {
        $this->repository = new RepositoryAdapter();
    }

    public function upsertCliente(array $cliente) {
        $sql = "INSERT INTO clientes (
                    cpf_cnpj, nome_razao, email, cep, numero, bairro, complemento, cidade, uf,
                    contato_residencial, contato_comercial, contato_celular, responsavel_recebimento, fantasia, endereco
                ) VALUES (
                    $1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15
                )
                ON CONFLICT (cpf_cnpj) DO UPDATE SET
                    nome_razao = EXCLUDED.nome_razao,
                    email = EXCLUDED.email,
                    cep = EXCLUDED.cep,
                    numero = EXCLUDED.numero,
                    bairro = EXCLUDED.bairro,
                    complemento = EXCLUDED.complemento,
                    cidade = EXCLUDED.cidade,
                    uf = EXCLUDED.uf,
                    contato_residencial = EXCLUDED.contato_residencial,
                    contato_comercial = EXCLUDED.contato_comercial,
                    contato_celular = EXCLUDED.contato_celular,
                    responsavel_recebimento = EXCLUDED.responsavel_recebimento,
                    fantasia = EXCLUDED.fantasia,
                    endereco = EXCLUDED.endereco,
                    atualizado_em = NOW()
                RETURNING id";

        $params = [
            $cliente["cpf_cnpj"] ?? '',
            $cliente["nome_razao"] ?? '',
            $cliente["email"] ?? '',
            $cliente["cep"] ?? '',
            $cliente["numero"] ?? '',
            $cliente["bairro"] ?? '',
            $cliente["complemento"] ?? '',
            $cliente["cidade"] ?? '',
            $cliente["uf"] ?? '',
            $cliente["contato_residencial"] ?? '',
            $cliente["contato_comercial"] ?? '',
            $cliente["contato_celular"] ?? '',
            $cliente["responsavel_recebimento"] ?? '',
            $cliente["fantasia"] ?? '',
            $cliente["endereco"] ?? ''
        ];

        $result = $this->repository->fetchAll($sql, $params);
        return $result[0]['id'] ?? null;
    }

    public function insertPedido(array $pedido) {
        $sql = "INSERT INTO pedidos (
                    cliente_id, ecommerce_id, valor_frete, prazo_entrega,
                    forma_pagamento, valor,
                    quantidade_parcelas, meio_pagamento, status
                ) VALUES (
                    $1,$2,$3,$4,$5,$6,$7,$8,$9
                )
                RETURNING id";

        $params = [
            $pedido["cliente_id"] ?? 0,
            $pedido["ecommerce_id"] ?? '',
            $pedido["valor_frete"] ?? 0,
            $pedido["prazo_entrega"] ?? 0,
            $pedido["forma_pagamento"] ?? '',
            $pedido["valor"] ?? 0,
            $pedido["quantidade_parcelas"] ?? 1,
            $pedido["meio_pagamento"] ?? '',
            'CONFIRMED'
        ];

        $result = $this->repository->fetchAll($sql, $params);
        return $result[0]['id'] ?? null;
    }

    public function insertPedidoProdutos(int $pedidoId, array $produtos) {
        foreach ($produtos as $produto) {
            $sql = "INSERT INTO pedido_produtos (pedido_id, produto_sku, quantidade, valor_unitario)
                    VALUES ($1,$2,$3,$4)
                    ON CONFLICT (pedido_id, produto_sku) DO NOTHING";

            $params = [
                $pedidoId,
                $produto["sku"] ?? 0,
                $produto["quantidade"] ?? 0,
                $produto["valorUnitario"] ?? 0
            ];

            $this->repository->execute($sql, $params);
        }
        return true;
    }

    public function upsertPedidoTransaction(array $cliente, array $pedido) {
        try {
            $this->repository->beginTransaction();

            $clienteId = $this->upsertCliente($cliente);
            if (!$clienteId) {
                $this->repository->rollBack();
                return false;
            }

            $pedido["cliente_id"] = $clienteId;
            $pedidoId = $this->insertPedido($pedido);

            if (!$pedidoId) {
                $this->repository->rollBack();
                return false;
            }

            $this->insertPedidoProdutos($pedidoId, $pedido["produtos"] ?? []);
            $this->repository->commit();
            return $pedidoId;

        } catch (\Exception $e) {
            $this->repository->rollBack();
            error_log('Erro upsertPedidoTransaction: ' . $e->getMessage());
            return null;
        }
    }

    public function updateNumeroPedido(?string $idPedidoParceiro, int $numeroPedido) {
        if (!$idPedidoParceiro) return false;

        $sql = "UPDATE pedidos 
                SET ecommerce_id = $1, atualizado_em = NOW() 
                WHERE id = $2";

        $params = [$numeroPedido, $idPedidoParceiro];

        try {
            $this->repository->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            error_log('Erro ao atualizar número do pedido: ' . $e->getMessage());
            return false;
        }
    }

    public function getById(int $pedidoId) {
        $sql = "SELECT 
                    p.id AS pedido_id,
                    p.*,
                    c.id AS cliente_id,
                    c.*,
                    pp.produto_sku,
                    pp.quantidade AS produto_quantidade,
                    pp.valor_unitario,
                    pr.name AS produto_nome,
                    pr.price AS produto_preco,
                    pr.sku
                FROM pedidos p
                JOIN clientes c ON p.cliente_id = c.id
                LEFT JOIN pedido_produtos pp ON pp.pedido_id = p.id
                LEFT JOIN products pr ON pr.sku = pp.produto_sku
                WHERE p.id = $1";

        $rows = $this->repository->fetchAll($sql, [$pedidoId]);
        if (!$rows) return null;

        $pedido = [
            "pedido_id" => $rows[0]["pedido_id"] ?? 0,
            "ecommerce_id" => $rows[0]["ecommerce_id"] ?? '',
            "valor" => $rows[0]["valor"] ?? 0,
            "valor_frete" => $rows[0]["valor_frete"] ?? 0,
            "prazo_entrega" => $rows[0]["prazo_entrega"] ?? 0,
            "forma_pagamento" => $rows[0]["forma_pagamento"] ?? '',
            "quantidade_parcelas" => $rows[0]["quantidade_parcelas"] ?? 1,
            "responsavel_recebimento" => $rows[0]["responsavel_recebimento"] ?? '',
            "meio_pagamento" => $rows[0]["meio_pagamento"] ?? '',
            "cliente" => [
                "id" => $rows[0]["cliente_id"] ?? 0,
                "cpf_cnpj" => $rows[0]["cpf_cnpj"] ?? '',
                "nome_razao" => $rows[0]["nome_razao"] ?? '',
                "fantasia" => $rows[0]["fantasia"] ?? '',
                "email" => $rows[0]["email"] ?? '',
                "uf" => $rows[0]["uf"] ?? '',
                "cep" => $rows[0]["cep"] ?? '',
                "endereco" => $rows[0]["endereco"] ?? '',
                "numero" => $rows[0]["numero"] ?? '',
                "bairro" => $rows[0]["bairro"] ?? '',
                "cidade" => $rows[0]["cidade"] ?? '',
                "complemento" => $rows[0]["complemento"] ?? '',
                "responsavel_recebimento" => $rows[0]["responsavel_recebimento"] ?? '',
                "residencial" => $rows[0]["contato_residencial"] ?? '',
                "comercial" => $rows[0]["contato_comercial"] ?? '',
                "celular" => $rows[0]["contato_celular"] ?? '',
            ],
            "produtos" => []
        ];

        foreach ($rows as $row) {
            if (!empty($row["produto_sku"])) {
                $pedido["produtos"][] = [
                    "sku" => $row["sku"] ?? 0,
                    "nome" => $row["produto_nome"] ?? '',
                    "preco" => $row["produto_preco"] ?? 0,
                    "quantidade" => $row["produto_quantidade"] ?? 0
                ];
            }
        }

        return $pedido;
    }

    public function listAll(array $filters = []) {
        $sql = "SELECT 
                    p.id AS pedido_id,
                    c.cpf_cnpj,
                    c.nome_razao,
                    p.valor,
                    p.status,
                    p.criado_em
                FROM pedidos p
                JOIN clientes c ON p.cliente_id = c.id
                LEFT JOIN pedido_produtos pp ON pp.pedido_id = p.id
                LEFT JOIN products pr ON pr.sku = pp.produto_sku";

        $params = [];
        if (!empty($filters)) {
            $clauses = [];
            $i = 1;
            foreach ($filters as $key => $value) {
                $clauses[] = "$key ILIKE '%' || $" . $i . " || '%'";
                $params[] = $value;
                $i++;
            }
            $sql .= " WHERE " . implode(" AND ", $clauses);
        }

        return $this->repository->fetchAll($sql, $params);
    }
}
