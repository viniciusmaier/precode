<?php

    class RepositoryAdapter {
        private $host = 'localhost';
        private $port = '5432';
        private $name = 'postgres';
        private $password = '123';
        private $username = 'postgres';

        private function getConnection() {
            return pg_connect("host={$this->host} port={$this->port} dbname={$this->name} user={$this->username} password={$this->password}");
        }

        public function execute($sql, $params) {
            $conn = $this->getConnection();
            $result = pg_query_params($conn, $sql, $params);
            pg_close($conn);

            return $result;
        }
        
        public function fetchAll($sql, $params = []) {
            $conn = $this->getConnection();
            $result = pg_query_params($conn, $sql, $params);

            if (!$result) {
                pg_close($conn);
                return false;
            }

            $rows = [];
            while ($row = pg_fetch_assoc($result)) {
                $rows[] = $row;
            }

            pg_close($conn);
            return $rows;
        }
    }