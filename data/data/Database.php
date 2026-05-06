<?php
class Database {
    private $host = "localhost";
    private $db_name = "gokyuzuair";
    private $username = "root";
    private $password = "";
    private $conn;

    public function connect() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            echo "Hata: " . $e->getMessage();
            die();
        }

        return $this->conn;
    }
}

$db = new Database();
$conn = $db->connect();
?>