<?php
class Session {
    /**
     * Khởi tạo session
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Thiết lập giá trị session
     */
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Lấy giá trị session
     */
    public static function get($key, $default = null) {
        self::start();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
    
    /**
     * Kiểm tra key có tồn tại trong session không
     */
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Xóa một key khỏi session
     */
    public static function delete($key) {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Xóa toàn bộ session
     */
    public static function destroy() {
        self::start();
        
        // Xóa tất cả session variables
        $_SESSION = array();
        
        // Xóa session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }
        
        // Hủy session
        session_unset();
        session_destroy();
    }
    
    /**
     * Kiểm tra user đã đăng nhập chưa
     */
    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Lấy ID user đang đăng nhập
     */
    public static function getUserId() {
        self::start();
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
    
    /**
     * Lấy email user đang đăng nhập
     */
    public static function getUserEmail() {
        self::start();
        return isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null;
    }
    
    /**
     * Thiết lập thông báo flash (hiển thị 1 lần rồi xóa)
     */
    public static function setFlash($key, $message) {
        self::start();
        $_SESSION['flash'][$key] = $message;
    }
    
    /**
     * Lấy và xóa thông báo flash
     */
    public static function getFlash($key) {
        self::start();
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return null;
    }
    
    /**
     * Kiểm tra có thông báo flash không
     */
    public static function hasFlash($key) {
        self::start();
        return isset($_SESSION['flash'][$key]);
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        self::start();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        self::start();
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Get CSRF token
     */
    public static function getCSRFToken() {
        self::start();
        return $_SESSION['csrf_token'] ?? self::generateCSRFToken();
    }
}
?>
