<?php
    var_dump('heelo');
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
        CREATE TABLE IF NOT EXISTS products (
            sku BIGINT PRIMARY KEY,
            name TEXT NOT NULL,
            description TEXT,
            short_name TEXT,
            status TEXT DEFAULT 'enabled',
            word_keys TEXT,
            price NUMERIC(12,2) DEFAULT 0,
            promotional_price NUMERIC(12,2) DEFAULT 0,
            cost NUMERIC(12,2) DEFAULT 0,
            weight NUMERIC(12,2) DEFAULT 0,
            width NUMERIC(12,2) DEFAULT 0,
            height NUMERIC(12,2) DEFAULT 0,
            length NUMERIC(12,2) DEFAULT 0,
            brand TEXT,
            url_youtube TEXT,
            google_description TEXT,
            manufacturing TEXT,
            nbm TEXT,
            model TEXT,
            gender TEXT,
            volumes INT DEFAULT 0,
            warranty_time INT DEFAULT 0,
            category TEXT,
            subcategory TEXT,
            endcategory TEXT,
            created_at TIMESTAMP DEFAULT NOW(),
            updated_at TIMESTAMP DEFAULT NOW()
        );
    ";

    $result = pg_query($conn, $sql);

    if ($result) {
        echo "Tabela 'products' criada com sucesso!\n";
    } else {
        echo "Erro ao criar tabela: " . pg_last_error($conn) . "\n";
    }

    pg_close($conn);