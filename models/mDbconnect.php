<?php
class clsConnect {
    private static $instance = null;
    private static $connection = null;
    
    // Private constructor để ngăn tạo instance từ bên ngoài
    private function __construct() {}
    
    // Ngăn clone
    private function __clone() {}
    
    // Ngăn unserialize
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get database connection (singleton)
     */
    public function connect() {
        if (self::$connection === null || !self::$connection->ping()) {
            $host = "localhost";
            $user = "root";
            $pass = "";
            $db = "webhenho";
            
            self::$connection = new mysqli($host, $user, $pass, $db);
            
            if (self::$connection->connect_error) {
                error_log("Database connection failed: " . self::$connection->connect_error);
                throw new Exception("Database connection failed");
            }
            
            // Set charset to utf8mb4
            self::$connection->set_charset("utf8mb4");
        }
        
        return self::$connection;
    }
    
    /**
     * Disconnect (not recommended to call directly)
     */
    public function disconnect($conn = null) {
        // Không đóng connection nữa, để reuse
        // Connection sẽ tự đóng khi script kết thúc
        return;
    }
    
    /**
     * Close connection khi script kết thúc
     */
    public static function closeConnection() {
        if (self::$connection !== null) {
            self::$connection->close();
            self::$connection = null;
        }
    }
}

// Đóng connection khi script kết thúc
register_shutdown_function(['clsConnect', 'closeConnection']);
?>
