<?php
/**
 * Error Logger Class
 * Log errors, exceptions, security events
 */

// Define DEBUG_MODE if not already defined
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', false); // Set to true for development, false for production
}

class ErrorLogger {
    private static $logDir = __DIR__ . '/../logs/';
    
    /**
     * Log error
     */
    public static function logError($message, $context = []) {
        self::log('ERROR', $message, $context);
    }
    
    /**
     * Log warning
     */
    public static function logWarning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }
    
    /**
     * Log info
     */
    public static function logInfo($message, $context = []) {
        self::log('INFO', $message, $context);
    }
    
    /**
     * Log security event
     */
    public static function logSecurity($message, $context = []) {
        self::log('SECURITY', $message, $context, 'security.log');
    }
    
    /**
     * Log database error
     */
    public static function logDatabase($message, $query = '', $context = []) {
        $context['query'] = $query;
        self::log('DATABASE', $message, $context, 'database.log');
    }
    
    /**
     * Log exception
     */
    public static function logException($exception, $context = []) {
        $message = sprintf(
            '%s: %s in %s:%d',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        
        $context['trace'] = $exception->getTraceAsString();
        self::log('EXCEPTION', $message, $context, 'exceptions.log');
    }
    
    /**
     * Main log function
     */
    private static function log($level, $message, $context = [], $filename = 'app.log') {
        // Tạo thư mục logs nếu chưa có
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0777, true);
        }
        
        // Format log entry
        $timestamp = date('Y-m-d H:i:s');
        $userId = $_SESSION['user_id'] ?? 'guest';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        
        $logEntry = sprintf(
            "[%s] [%s] [User:%s] [IP:%s] [URI:%s] %s\n",
            $timestamp,
            $level,
            $userId,
            $ip,
            $uri,
            $message
        );
        
        // Add context if exists
        if (!empty($context)) {
            $logEntry .= "Context: " . json_encode($context, JSON_UNESCAPED_UNICODE) . "\n";
        }
        
        $logEntry .= str_repeat('-', 80) . "\n";
        
        // Write to file
        $logFile = self::$logDir . $filename;
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Rotate log nếu quá lớn (> 10MB)
        self::rotateLog($logFile);
    }
    
    /**
     * Rotate log file nếu quá lớn
     */
    private static function rotateLog($logFile, $maxSize = 10485760) {
        if (file_exists($logFile) && filesize($logFile) > $maxSize) {
            $timestamp = date('Ymd_His');
            $rotatedFile = $logFile . '.' . $timestamp;
            rename($logFile, $rotatedFile);
            
            // Compress old log
            if (function_exists('gzopen')) {
                $gzFile = $rotatedFile . '.gz';
                $gz = gzopen($gzFile, 'w9');
                gzwrite($gz, file_get_contents($rotatedFile));
                gzclose($gz);
                unlink($rotatedFile);
            }
        }
    }
    
    /**
     * Cleanup old logs (older than X days)
     */
    public static function cleanupOldLogs($daysOld = 30) {
        $files = glob(self::$logDir . '*.log.*');
        $cutoffTime = time() - ($daysOld * 24 * 60 * 60);
        $deletedCount = 0;
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                if (unlink($file)) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount;
    }
    
    /**
     * Get recent errors
     */
    public static function getRecentErrors($limit = 50, $filename = 'app.log') {
        $logFile = self::$logDir . $filename;
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES);
        $lines = array_reverse($lines);
        $lines = array_slice($lines, 0, $limit);
        
        return $lines;
    }
    
    /**
     * Setup error handler
     */
    public static function setupErrorHandler() {
        // Error handler
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            $message = sprintf('%s in %s:%d', $errstr, $errfile, $errline);
            self::logError($message, ['errno' => $errno]);
            return false; // Let PHP's error handler continue
        });
        
        // Exception handler
        set_exception_handler(function($exception) {
            self::logException($exception);
            
            // Display user-friendly error in production
            if (!defined('DEBUG_MODE') || !DEBUG_MODE) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau!'
                ]);
                exit;
            }
        });
        
        // Shutdown function to catch fatal errors
        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
                $message = sprintf(
                    'Fatal Error: %s in %s:%d',
                    $error['message'],
                    $error['file'],
                    $error['line']
                );
                self::logError($message, ['type' => $error['type']]);
            }
        });
    }
}

// Auto setup error handler
ErrorLogger::setupErrorHandler();
?>
