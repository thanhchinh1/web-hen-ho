<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/mEmailConfig.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Email Service
 * Xử lý gửi email thông qua PHPMailer
 */
class EmailService {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configureSMTP();
    }
    
    /**
     * Cấu hình SMTP
     */
    private function configureSMTP() {
        try {
            $config = EmailConfig::getSMTPConfig();
            
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $config['host'];
            $this->mailer->SMTPAuth = $config['auth'];
            $this->mailer->Username = $config['username'];
            $this->mailer->Password = $config['password'];
            $this->mailer->SMTPSecure = $config['secure'];
            $this->mailer->Port = $config['port'];
            
            // Sender
            $this->mailer->setFrom($config['from_email'], $config['from_name']);
            
            // Encoding
            $this->mailer->CharSet = 'UTF-8';
            
            // Debug (tắt trong production)
            // $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            
        } catch (Exception $e) {
            throw new Exception('Lỗi cấu hình SMTP: ' . $e->getMessage());
        }
    }
    
    /**
     * Gửi email OTP xác thực
     * 
     * @param string $toEmail Email người nhận
     * @param string $otpCode Mã OTP
     * @param int $expiryMinutes Thời gian hết hạn (phút)
     * @return bool
     */
    public function sendOTPEmail($toEmail, $otpCode, $expiryMinutes = 10) {
        try {
            // Recipients
            $this->mailer->addAddress($toEmail);
            
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Xác thực tài khoản DuyenHub';
            
            $htmlBody = $this->getOTPEmailTemplate($otpCode, $expiryMinutes);
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = "Mã xác thực của bạn là: $otpCode. Mã có hiệu lực trong $expiryMinutes phút.";
            
            // Send
            $result = $this->mailer->send();
            
            // Clear addresses for next email
            $this->mailer->clearAddresses();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Template HTML cho email OTP
     */
    private function getOTPEmailTemplate($otpCode, $expiryMinutes) {
        return "
        <!DOCTYPE html>
        <html lang='vi'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .email-container {
                    max-width: 600px;
                    margin: 40px auto;
                    background-color: #ffffff;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                }
                .header {
                    background: linear-gradient(135deg, #FF6B6B 0%, #EE5A6F 100%);
                    color: white;
                    padding: 30px;
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                    font-size: 28px;
                }
                .content {
                    padding: 40px 30px;
                    text-align: center;
                }
                .otp-box {
                    background-color: #f8f9fa;
                    border: 2px dashed #FF6B6B;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 30px 0;
                }
                .otp-code {
                    font-size: 36px;
                    font-weight: bold;
                    color: #FF6B6B;
                    letter-spacing: 8px;
                    margin: 10px 0;
                }
                .note {
                    color: #666;
                    font-size: 14px;
                    line-height: 1.6;
                    margin-top: 20px;
                }
                .footer {
                    background-color: #f8f9fa;
                    padding: 20px;
                    text-align: center;
                    color: #999;
                    font-size: 12px;
                }
                .warning {
                    background-color: #fff3cd;
                    border-left: 4px solid #ffc107;
                    padding: 15px;
                    margin: 20px 0;
                    text-align: left;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='header'>
                    <h1>🎯 DuyenHub</h1>
                    <p>Xác thực tài khoản của bạn</p>
                </div>
                
                <div class='content'>
                    <h2>Chào mừng bạn đến với DuyenHub!</h2>
                    <p>Vui lòng sử dụng mã OTP bên dưới để hoàn tất đăng ký tài khoản:</p>
                    
                    <div class='otp-box'>
                        <div style='color: #666; font-size: 14px;'>MÃ XÁC THỰC CỦA BẠN</div>
                        <div class='otp-code'>$otpCode</div>
                        <div style='color: #999; font-size: 12px; margin-top: 10px;'>
                            ⏱️ Có hiệu lực trong <strong>$expiryMinutes phút</strong>
                        </div>
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ Lưu ý bảo mật:</strong>
                        <ul style='margin: 10px 0; padding-left: 20px; text-align: left;'>
                            <li>Không chia sẻ mã này với bất kỳ ai</li>
                            <li>DuyenHub sẽ không bao giờ yêu cầu mã OTP qua điện thoại</li>
                            <li>Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email</li>
                        </ul>
                    </div>
                    
                    <div class='note'>
                        <p>Nếu bạn gặp khó khăn, vui lòng liên hệ đội ngũ hỗ trợ của chúng tôi.</p>
                        <p>Trân trọng,<br><strong>Đội ngũ DuyenHub</strong></p>
                    </div>
                </div>
                
                <div class='footer'>
                    <p>© 2025 DuyenHub - Kết Nối Yêu Thương</p>
                    <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Gửi email chào mừng sau khi xác thực thành công
     */
    public function sendWelcomeEmail($toEmail, $userName = 'bạn') {
        try {
            $this->mailer->addAddress($toEmail);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Chào mừng đến với DuyenHub!';
            
            $htmlBody = "
            <!DOCTYPE html>
            <html lang='vi'>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #FF6B6B 0%, #EE5A6F 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { padding: 30px; background: #fff; }
                    .button { display: inline-block; padding: 12px 30px; background: #FF6B6B; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                    .footer { text-align: center; color: #999; font-size: 12px; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🎉 Chào mừng đến với DuyenHub!</h1>
                    </div>
                    <div class='content'>
                        <p>Xin chào <strong>$userName</strong>,</p>
                        <p>Tài khoản của bạn đã được xác thực thành công! Bây giờ bạn có thể:</p>
                        <ul>
                            <li>✨ Thiết lập hồ sơ cá nhân</li>
                            <li>💝 Tìm kiếm người phù hợp</li>
                            <li>💬 Trò chuyện và kết nối</li>
                            <li>🎯 Sử dụng tính năng ghép đôi thông minh</li>
                        </ul>
                        <p style='text-align: center;'>
                            <a href='http://localhost/web-hen-ho/views/dangnhap/login.php' class='button'>Đăng nhập ngay</a>
                        </p>
                        <p>Chúc bạn tìm được nửa kia của mình!</p>
                        <p>Trân trọng,<br><strong>Đội ngũ DuyenHub</strong></p>
                    </div>
                    <div class='footer'>
                        <p>© 2025 DuyenHub - Kết Nối Yêu Thương</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $this->mailer->Body = $htmlBody;
            $result = $this->mailer->send();
            $this->mailer->clearAddresses();
            
            return $result;
        } catch (Exception $e) {
            error_log("Welcome email failed: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Gửi email OTP quên mật khẩu
     * 
     * @param string $toEmail Email người nhận
     * @param string $otpCode Mã OTP
     * @param int $expiryMinutes Thời gian hết hạn (phút)
     * @return bool
     */
    public function sendForgotPasswordOTP($toEmail, $otpCode, $expiryMinutes = 10) {
        try {
            $this->mailer->addAddress($toEmail);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Đặt lại mật khẩu - DuyenHub';
            
            $htmlBody = "
            <!DOCTYPE html>
            <html lang='vi'>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
                    .email-container { max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                    .header { background: linear-gradient(135deg, #FF6B6B 0%, #EE5A6F 100%); color: white; padding: 30px; text-align: center; }
                    .header h1 { margin: 0; font-size: 28px; }
                    .content { padding: 40px 30px; text-align: center; }
                    .otp-box { background-color: #f8f9fa; border: 2px dashed #FF6B6B; border-radius: 8px; padding: 20px; margin: 30px 0; }
                    .otp-code { font-size: 36px; font-weight: bold; color: #FF6B6B; letter-spacing: 8px; margin: 10px 0; }
                    .note { color: #666; font-size: 14px; line-height: 1.6; margin-top: 20px; }
                    .footer { background-color: #f8f9fa; padding: 20px; text-align: center; color: #999; font-size: 12px; }
                    .warning { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; text-align: left; }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <div class='header'>
                        <h1>🔐 DuyenHub</h1>
                        <p>Đặt lại mật khẩu</p>
                    </div>
                    
                    <div class='content'>
                        <h2>Yêu cầu đặt lại mật khẩu</h2>
                        <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                        <p>Vui lòng sử dụng mã OTP bên dưới để xác thực:</p>
                        
                        <div class='otp-box'>
                            <div style='color: #666; font-size: 14px;'>MÃ XÁC THỰC CỦA BẠN</div>
                            <div class='otp-code'>$otpCode</div>
                            <div style='color: #999; font-size: 12px; margin-top: 10px;'>
                                ⏱️ Có hiệu lực trong <strong>$expiryMinutes phút</strong>
                            </div>
                        </div>
                        
                        <div class='warning'>
                            <strong>⚠️ Lưu ý bảo mật:</strong>
                            <ul style='margin: 10px 0; padding-left: 20px; text-align: left;'>
                                <li>Không chia sẻ mã này với bất kỳ ai</li>
                                <li>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này</li>
                                <li>Tài khoản của bạn vẫn an toàn và không có thay đổi nào</li>
                            </ul>
                        </div>
                        
                        <div class='note'>
                            <p>Nếu bạn gặp khó khăn, vui lòng liên hệ đội ngũ hỗ trợ.</p>
                            <p>Trân trọng,<br><strong>Đội ngũ DuyenHub</strong></p>
                        </div>
                    </div>
                    
                    <div class='footer'>
                        <p>© 2025 DuyenHub - Kết Nối Yêu Thương</p>
                        <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = "Mã xác thực đặt lại mật khẩu của bạn là: $otpCode. Mã có hiệu lực trong $expiryMinutes phút.";
            
            $result = $this->mailer->send();
            $this->mailer->clearAddresses();
            
            return $result;
        } catch (Exception $e) {
            error_log("Forgot password email failed: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
}
