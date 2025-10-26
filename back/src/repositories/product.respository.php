<?php 
    require_once '../../../config/postgres/adapter.php';

    class ProductRepository {
        private $repository;
        public function __construct() {
            $this->repository = new RepositoryAdapter();
        }

        public function upsert($product) {
            $sql = "INSERT INTO products (
                        sku, name, description, short_name, status, word_keys, price, 
                        promotional_price, cost, weight, width, height, length, brand, 
                        url_youtube, google_description, manufacturing, nbm, model, gender, 
                        volumes, warranty_time, category, subcategory, endcategory
                    ) VALUES (
                        $1, $2, $3, $4, $5, $6, $7,
                        $8, $9, $10, $11, $12, $13, $14,
                        $15, $16, $17, $18, $19, $20,
                        $21, $22, $23, $24, $25
                    )
                    ON CONFLICT (sku) DO UPDATE SET
                        name = EXCLUDED.name,
                        description = EXCLUDED.description,
                        short_name = EXCLUDED.short_name,
                        status = EXCLUDED.status,
                        word_keys = EXCLUDED.word_keys,
                        price = EXCLUDED.price,
                        promotional_price = EXCLUDED.promotional_price,
                        cost = EXCLUDED.cost,
                        weight = EXCLUDED.weight,
                        width = EXCLUDED.width,
                        height = EXCLUDED.height,
                        length = EXCLUDED.length,
                        brand = EXCLUDED.brand,
                        url_youtube = EXCLUDED.url_youtube,
                        google_description = EXCLUDED.google_description,
                        manufacturing = EXCLUDED.manufacturing,
                        nbm = EXCLUDED.nbm,
                        model = EXCLUDED.model,
                        gender = EXCLUDED.gender,
                        volumes = EXCLUDED.volumes,
                        warranty_time = EXCLUDED.warranty_time,
                        category = EXCLUDED.category,
                        subcategory = EXCLUDED.subcategory,
                        endcategory = EXCLUDED.endcategory,
                        updated_at = NOW()";

            $params = [
                $product["sku"] ?? null,
                $product["name"] ?? "",
                $product["description"] ?? "",
                $product["shortName"] ?? "",
                $product["status"] ?? "enabled",
                $product["wordKeys"] ?? "",
                $product["price"] ?? 0,
                $product["promotional_price"] ?? 0,
                $product["cost"] ?? 0,
                $product["weight"] ?? 0,
                $product["width"] ?? 0,
                $product["height"] ?? 0,
                $product["length"] ?? 0,
                $product["brand"] ?? "",
                $product["urlYoutube"] ?? "",
                $product["googleDescription"] ?? "",
                $product["manufacturing"] ?? "",
                $product["nbm"] ?? "",
                $product["model"] ?? "",
                $product["gender"] ?? "",
                $product["volumes"] ?? 0,
                $product["warrantyTime"] ?? 0,
                $product["category"] ?? "",
                $product["subcategory"] ?? "",
                $product["endcategory"] ?? ""
            ];

            return $this->repository->execute($sql, $params);
        }

       public function listAll($filters = []) {
            $sql = "SELECT 
                sku, description, status, updated_at, price
             FROM products";
            $params = [];

            if (!empty($filters)) {
                $clauses = [];
                $i = 1;
                foreach ($filters as $key => $value) {
                   if ($key === 'sku') {
                        $clauses[] = "CAST($key AS TEXT) ILIKE '%' || $" . $i . " || '%'";
                    } else {
                        $clauses[] = "$key ILIKE '%' || $" . $i . " || '%'";
                    }

                    $params[] = $value;
                    $i++;
                }
                $sql .= " WHERE " . implode(" AND ", $clauses);
            }

            return $this->repository->fetchAll($sql, $params);
        }

        public function getBySku($sku) {
            $sql = "SELECT *
                    FROM products
                    WHERE sku = $1";

            $params = [$sku];

            $result = $this->repository->fetchAll($sql, $params);

            return !empty($result) ? $result[0] : null;
        }
    }