<?php 
    require_once '../../../config/postgres/adapter.php';

    class ProductRepository {
        private $repository;
        public function __construct() {
            $this->repository = new RepositoryAdapter();
        }

       public function upsert($product)
        {
            $variations = $product["variations"] ?? [];

            if (is_string($variations)) {
                $decoded = json_decode($variations, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $variations = $decoded;
                }
            }

            $sku = $product["sku"] ?? null;

            if (empty($sku)) {
                throw new Exception("SKU não informado para o upsert.");
            }

            $sql = "
                INSERT INTO products (
                    sku, name, description, short_name, status, price, promotional_price,
                    cost, weight, width, height, length, brand, variations, created_at, updated_at
                ) VALUES (
                    $1, $2, $3, $4, $5, $6, $7,
                    $8, $9, $10, $11, $12, $13, $14::jsonb, NOW(), NOW()
                )
                ON CONFLICT (sku) DO UPDATE SET
                    name = EXCLUDED.name,
                    description = EXCLUDED.description,
                    short_name = EXCLUDED.short_name,
                    status = EXCLUDED.status,
                    price = EXCLUDED.price,
                    promotional_price = EXCLUDED.promotional_price,
                    cost = EXCLUDED.cost,
                    weight = EXCLUDED.weight,
                    width = EXCLUDED.width,
                    height = EXCLUDED.height,
                    length = EXCLUDED.length,
                    brand = EXCLUDED.brand,
                    variations = EXCLUDED.variations,
                    updated_at = NOW()
                RETURNING sku
            ";

            $params = [
                $sku,
                $product["name"] ?? "",
                $product["description"] ?? "",
                $product["shortName"] ?? "",
                $product["status"] ?? "enabled",
                $product["price"] ?? 0,
                $product["promotional_price"] ?? 0,
                $product["cost"] ?? 0,
                $product["weight"] ?? 0,
                $product["width"] ?? 0,
                $product["height"] ?? 0,
                $product["length"] ?? 0,
                $product["brand"] ?? "",
                json_encode($variations)
            ];

           $result = $this->repository->fetchAll($sql, $params);

            if (!$result || count($result) === 0) {
                return null;
            }

            return $result[0]["sku"] ?? null;
        }

        public function saveVariations($productSku, $variation) {
            $sql = "
                INSERT INTO product_variation (
                    sku, sku_variation, qty, ean, specification
                ) VALUES (
                    $1, $2, $3, $4, $5::jsonb
                )
                ON CONFLICT (sku, sku_variation) DO UPDATE SET
                    qty = EXCLUDED.qty,
                    ean = EXCLUDED.ean,
                    specification = EXCLUDED.specification
                RETURNING sku
            ";

            $params = [
                $productSku,
                $variation['sku'],
                $variation['qty'] ?? 0,
                $variation['ean'] ?? '',
                json_encode($variation['specification'] ?? [])
            ];

            $result = $this->repository->fetchAll($sql, $params);

            return $result[0]['sku'] ?? null;
        }

        public function getByVariation($sku) {
            $sql = 'SELECT v.sku_variation,
                           prod.description,
                           prod.price,
                           v.qty
                    FROM product_variation v
                    INNER JOIN products prod ON prod.sku = v.sku 
                    WHERE v.sku_variation = $1';

            $params = [$sku];

            $result = $this->repository->fetchAll($sql, $params);

            return !empty($result) ? $result[0] : null;

        }

        public function listAll($filters = []) {
            $sql = "SELECT 
                id, sku, description, status, updated_at, price
            FROM products";

            $params = [];

            $numericFields = ['id', 'sku', 'price', 'promotional_price', 'cost', 'weight', 'width', 'height', 'length'];

            if (!empty($filters)) {
                $clauses = [];
                $i = 1;

                foreach ($filters as $key => $value) {
                    if ($value === '' || $value === null) continue;

                    if (in_array($key, $numericFields)) {
                        $clauses[] = "CAST($key AS TEXT) ILIKE '%' || $" . $i . " || '%'";
                    } else {
                        $clauses[] = "$key ILIKE '%' || $" . $i . " || '%'";
                    }

                    $params[] = $value;
                    $i++;
                }

                if (!empty($clauses)) {
                    $sql .= " WHERE " . implode(" AND ", $clauses);
                }
            }

            return $this->repository->fetchAll($sql, $params);
        }

        public function getBySku($sku) {
            $sql = 'SELECT p.sku,
                           p.description,
                           p.*
                    FROM products p
                    WHERE sku = $1';

            $params = [$sku];

            $result = $this->repository->fetchAll($sql, $params);

            return !empty($result) ? $result[0] : null;
        }

        public function getById($id) {
            $sql = 'SELECT p.*,
                            p.short_name as "shortName" 
                    FROM products p
                    WHERE id = $1';

            $params = [$id];

            $result = $this->repository->fetchAll($sql, $params);
            return !empty($result) ? $result[0] : null;
        }
    }