<?php
    $host = "localhost";
    $port = "5432";
    $dbname = "postgres";
    $user = "postgres";
    $password = "123";

    $conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
    $conn = pg_connect($conn_string);

    if (!$conn) {
        die("Erro ao conectar ao banco de dados.");
    }

    $sql = "
        CREATE TABLE IF NOT EXISTS clientes (
            id SERIAL PRIMARY KEY,
            cpf_cnpj VARCHAR(14) UNIQUE,   
            nome_razao VARCHAR(100), 
            fantasia VARCHAR(100),     
            email VARCHAR(100),
            cep VARCHAR(18),
            endereco VARCHAR(100), 
            numero VARCHAR(100),
            bairro VARCHAR(100),
            complemento VARCHAR(100),
            cidade VARCHAR(100),
            uf VARCHAR(10),
            responsavel_recebimento VARCHAR(100),
            contato_residencial VARCHAR(100),
            contato_comercial VARCHAR(100),
            contato_celular VARCHAR(100),

            criado_em TIMESTAMP DEFAULT NOW(),
            atualizado_em TIMESTAMP DEFAULT NOW()
        ); 

        CREATE TABLE IF NOT EXISTS products (
            id SERIAL PRIMARY KEY,
            sku INT UNIQUE,
            name TEXT NOT NULL,
            short_name TEXT,
            description TEXT,
            status TEXT DEFAULT 'enabled',
            price NUMERIC(12,2) DEFAULT 0,
            promotional_price NUMERIC(12,2) DEFAULT 0,
            cost NUMERIC(12,2) DEFAULT 0,
            weight NUMERIC(12,2) DEFAULT 0,
            width NUMERIC(12,2) DEFAULT 0,
            height NUMERIC(12,2) DEFAULT 0,
            length NUMERIC(12,2) DEFAULT 0,
            brand TEXT,
            variations JSONB,
            created_at TIMESTAMP DEFAULT NOW(),
            updated_at TIMESTAMP DEFAULT NOW()
        );

        
        CREATE TABLE IF NOT EXISTS product_variation (
            id SERIAL PRIMARY KEY,
            sku INT NOT NULL,
            sku_variation INT NOT NULL,
            qty INT NOT NULL,
            ean INT NOT NULL,
            specification JSONB,

            UNIQUE(sku, sku_variation)
        );


        CREATE TABLE IF NOT EXISTS pedidos (
            id SERIAL PRIMARY KEY,
            ecommerce_id VARCHAR(100),
            valor_frete NUMERIC(10,2) DEFAULT 0,
            prazo_entrega INT DEFAULT 0,
            valor NUMERIC(10,2) NOT NULL DEFAULT 0,
            forma_pagamento VARCHAR(100),   
            cliente_id INT NOT NULL REFERENCES clientes(id) ON DELETE CASCADE,
            quantidade_parcelas NUMERIC DEFAULT 0,
            meio_pagamento VARCHAR(100),
            status VARCHAR(30),
            
            criado_em TIMESTAMP DEFAULT NOW(),
            atualizado_em TIMESTAMP DEFAULT NOW()
        );

        CREATE TABLE IF NOT EXISTS pedido_produtos (
            id SERIAL PRIMARY KEY,
            pedido_id INT NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
            produto_sku INT NOT NULL REFERENCES products(sku) ON DELETE CASCADE,
            quantidade INT NOT NULL,
            valor_unitario NUMERIC(10,2) NOT NULL,

            UNIQUE (pedido_id, produto_sku)
        );
    ";

    $result = pg_query($conn, $sql);

    if ($result) {
        echo "Tabela 'products' criada com sucesso!\n";
    } else {
        echo "Erro ao criar tabela: " . pg_last_error($conn) . "\n";
    }

    pg_close($conn);