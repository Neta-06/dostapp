<?php
class Database {
    private $host = "localhost";
    private $db_name = "freeapp";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            die("Veritabanı bağlantı hatası. Lütfen daha sonra tekrar deneyin.");
        }
        return $this->conn;
    }
}
?>
