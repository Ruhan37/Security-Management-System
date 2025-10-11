<?php
class Database {
    private $host = 'localhost';
    private $port = '4306';
    private $db_name = 'security_management_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }

    public function createDatabase() {
        try {
            $conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port,
                $this->username,
                $this->password
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "CREATE DATABASE IF NOT EXISTS " . $this->db_name;
            $conn->exec($sql);
            return true;
        } catch(PDOException $e) {
            echo "Database creation error: " . $e->getMessage();
            return false;
        }
    }
}
?>