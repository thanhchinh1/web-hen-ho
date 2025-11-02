<?php
/**
 * Password Validator Class
 * Validate password strength
 */
class PasswordValidator {
    
    /**
     * Validate password strength
     * 
     * @param string $password
     * @return array ['valid' => bool, 'message' => string, 'strength' => int]
     */
    public static function validate($password) {
        $errors = [];
        $strength = 0;
        
        // Check minimum length
        if (strlen($password) < 8) {
            $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự';
        } else {
            $strength += 20;
        }
        
        // Check for lowercase
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 chữ thường (a-z)';
        } else {
            $strength += 20;
        }
        
        // Check for uppercase
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 chữ hoa (A-Z)';
        } else {
            $strength += 20;
        }
        
        // Check for numbers
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 số (0-9)';
        } else {
            $strength += 20;
        }
        
        // Check for special characters
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'Mật khẩu phải có ít nhất 1 ký tự đặc biệt (!@#$%^&*...)';
        } else {
            $strength += 20;
        }
        
        // Bonus for length > 12
        if (strlen($password) >= 12) {
            $strength += 10;
        }
        
        // Check common passwords
        $commonPasswords = [
            '12345678', 'password', 'qwerty123', 'abc12345', 
            '11111111', '123456789', 'password123', 'admin123'
        ];
        
        if (in_array(strtolower($password), $commonPasswords)) {
            $errors[] = 'Mật khẩu này quá phổ biến, vui lòng chọn mật khẩu khác';
            $strength = 0;
        }
        
        return [
            'valid' => empty($errors),
            'message' => empty($errors) ? 'Mật khẩu mạnh!' : implode(', ', $errors),
            'errors' => $errors,
            'strength' => min(100, $strength),
            'strengthText' => self::getStrengthText($strength)
        ];
    }
    
    /**
     * Get strength text
     */
    private static function getStrengthText($strength) {
        if ($strength >= 80) return 'Rất mạnh';
        if ($strength >= 60) return 'Mạnh';
        if ($strength >= 40) return 'Trung bình';
        if ($strength >= 20) return 'Yếu';
        return 'Rất yếu';
    }
    
    /**
     * Generate random strong password
     */
    public static function generateStrongPassword($length = 12) {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $special = '!@#$%^&*()';
        
        $password = '';
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];
        
        $allChars = $lowercase . $uppercase . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        return str_shuffle($password);
    }
}
?>
