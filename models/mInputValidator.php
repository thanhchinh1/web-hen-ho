<?php
/**
 * Input Validator Class
 * Validate và sanitize user inputs
 */
class InputValidator {
    
    /**
     * Sanitize string - loại bỏ HTML/JS
     */
    public static function sanitizeString($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate email
     */
    public static function validateEmail($email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (Vietnam)
     */
    public static function validatePhone($phone) {
        // Loại bỏ spaces, dashes
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // Pattern cho SĐT Việt Nam: 10 số, bắt đầu với các đầu số hợp lệ
        $pattern = '/^(032|033|034|035|036|037|038|039|096|097|098|086|081|082|083|084|085|088|091|094|070|076|077|078|079|090|093|089|056|058|092|059|099)\d{7}$/';
        
        return preg_match($pattern, $phone) === 1;
    }
    
    /**
     * Validate date (YYYY-MM-DD)
     */
    public static function validateDate($year, $month, $day) {
        if (!is_numeric($year) || !is_numeric($month) || !is_numeric($day)) {
            return false;
        }
        
        // Kiểm tra ngày hợp lệ
        if (!checkdate($month, $day, $year)) {
            return false;
        }
        
        // Kiểm tra tuổi hợp lý (18-100 tuổi)
        $birthDate = new DateTime("$year-$month-$day");
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        
        if ($age < 18 || $age > 100) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate integer trong range
     */
    public static function validateIntRange($value, $min, $max) {
        if (!is_numeric($value)) {
            return false;
        }
        
        $intValue = intval($value);
        return $intValue >= $min && $intValue <= $max;
    }
    
    /**
     * Validate float trong range
     */
    public static function validateFloatRange($value, $min, $max) {
        if (!is_numeric($value)) {
            return false;
        }
        
        $floatValue = floatval($value);
        return $floatValue >= $min && $floatValue <= $max;
    }
    
    /**
     * Validate enum value
     */
    public static function validateEnum($value, $allowedValues) {
        return in_array($value, $allowedValues, true);
    }
    
    /**
     * Validate gender
     */
    public static function validateGender($gender) {
        return self::validateEnum($gender, ['male', 'female', 'other']);
    }
    
    /**
     * Validate marital status
     */
    public static function validateMaritalStatus($status) {
        return self::validateEnum($status, ['single', 'divorced', 'widowed', 'separated']);
    }
    
    /**
     * Validate text length
     */
    public static function validateLength($text, $minLength, $maxLength) {
        $length = mb_strlen($text, 'UTF-8');
        return $length >= $minLength && $length <= $maxLength;
    }
    
    /**
     * Validate URL
     */
    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Validate và sanitize full profile data
     */
    public static function validateProfileData($data) {
        $errors = [];
        
        // Validate tên (2-50 ký tự)
        if (!isset($data['fullName']) || !self::validateLength($data['fullName'], 2, 50)) {
            $errors[] = 'Họ tên phải từ 2-50 ký tự!';
        }
        
        // Validate giới tính
        if (!isset($data['gender']) || !self::validateGender($data['gender'])) {
            $errors[] = 'Giới tính không hợp lệ!';
        }
        
        // Validate ngày sinh
        if (!isset($data['year'], $data['month'], $data['day']) || 
            !self::validateDate($data['year'], $data['month'], $data['day'])) {
            $errors[] = 'Ngày sinh không hợp lệ! (Phải từ 18-100 tuổi)';
        }
        
        // Validate tình trạng hôn nhân
        if (!isset($data['maritalStatus']) || !self::validateMaritalStatus($data['maritalStatus'])) {
            $errors[] = 'Tình trạng hôn nhân không hợp lệ!';
        }
        
        // Validate cân nặng (30-200 kg)
        if (!isset($data['weight']) || !self::validateFloatRange($data['weight'], 30, 200)) {
            $errors[] = 'Cân nặng phải từ 30-200kg!';
        }
        
        // Validate chiều cao (100-250 cm)
        if (!isset($data['height']) || !self::validateFloatRange($data['height'], 100, 250)) {
            $errors[] = 'Chiều cao phải từ 100-250cm!';
        }
        
        // Validate mô tả (10-500 ký tự)
        if (!isset($data['description']) || !self::validateLength($data['description'], 10, 500)) {
            $errors[] = 'Mô tả bản thân phải từ 10-500 ký tự!';
        }
        
        // Validate location
        if (!isset($data['location']) || !self::validateLength($data['location'], 2, 100)) {
            $errors[] = 'Địa chỉ không hợp lệ!';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Sanitize toàn bộ profile data
     */
    public static function sanitizeProfileData($data) {
        return [
            'ten' => self::sanitizeString($data['fullName']),
            'ngaySinh' => sprintf('%04d-%02d-%02d', 
                intval($data['year']), 
                intval($data['month']), 
                intval($data['day'])
            ),
            'gioiTinh' => self::sanitizeString($data['gender']),
            'tinhTrangHonNhan' => self::sanitizeString($data['maritalStatus']),
            'canNang' => floatval($data['weight']),
            'chieuCao' => floatval($data['height']),
            'mucTieuPhatTrien' => self::sanitizeString($data['goal']),
            'hocVan' => self::sanitizeString($data['education']),
            'noiSong' => self::sanitizeString($data['location']),
            'soThich' => self::sanitizeString($data['interests'] ?? ''),
            'moTa' => self::sanitizeString($data['description'])
        ];
    }
    
    /**
     * Prevent SQL Injection - check dangerous patterns
     */
    public static function hasSQLInjection($input) {
        $dangerousPatterns = [
            '/(\bselect\b|\bunion\b|\binsert\b|\bupdate\b|\bdelete\b|\bdrop\b)/i',
            '/--/',
            '/;/',
            '/\/\*/',
            '/\*\//',
            '/\bor\b\s+\d+\s*=\s*\d+/i',
            '/\band\b\s+\d+\s*=\s*\d+/i'
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Prevent XSS - check script tags
     */
    public static function hasXSS($input) {
        $xssPatterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',  // onclick, onload, etc.
            '/<iframe/i',
            '/<embed/i',
            '/<object/i'
        ];
        
        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
}
?>
