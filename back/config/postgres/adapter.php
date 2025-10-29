<?php
class RepositoryAdapter {
    private $host = 'localhost';
    private $port = '5432';
    private $name = 'postgres';
    private $username = 'postgres';
    private $password = '123';
    private $conn;

    public function getConnection() {
        if (!$this->conn) {
            $this->conn = pg_connect(
                "host={$this->host} port={$this->port} dbname={$this->name} user={$this->username} password={$this->password}"
            );
            if (!$this->conn) {
                throw new Exception("Não foi possível conectar ao PostgreSQL");
            }
        }
        return $this->conn;
    }

    public function closeConnection() {
        if ($this->conn) {
            pg_close($this->conn);
            $this->conn = null;
        }
    }

    public function execute($sql, $params = []) {
        $conn = $this->getConnection();
        return pg_query_params($conn, $sql, $params);
    }

    public function fetchAll($sql, $params = []) {
        $conn = $this->getConnection();
        $result = pg_query_params($conn, $sql, $params);

        if (!$result) return false;

        $rows = [];
        while ($row = pg_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function beginTransaction() {
        $this->execute("BEGIN");
    }

    public function commit() {
        $this->execute("COMMIT");
    }

    public function rollBack() {
        $this->execute("ROLLBACK");
    }
}
