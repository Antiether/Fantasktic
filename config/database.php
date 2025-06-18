<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'fantasktic';
    private $username = 'root';  // Default Laragon MySQL username
    private $password = '';      // Default Laragon MySQL password (empty)
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            die();
        }
        
        return $this->conn;
    }
}

// Global database connection function
function getDB() {
    static $database = null;
    if ($database === null) {
        $database = new Database();
    }
    return $database->getConnection();
}
?>
