<?php
/**
 * config.php - File cấu hình chính của ứng dụng
 * 
 * File này chứa tất cả các hằng số cấu hình như:
 * - Thông tin kết nối database
 * - BASE_URL của website
 * - Thông tin SMTP để gửi email
 */

// ============================================
// CẤU HÌNH DATABASE
// ============================================

// Địa chỉ máy chủ database (thường là localhost)
define('DB_HOST', 'localhost');

// Tên database
define('DB_NAME', 'quan_ly_ban_thuoc');

// Tên người dùng database
define('DB_USER', 'root');

// Mật khẩu database
define('DB_PASS', 'admin'); 

// ============================================
// CẤU HÌNH BASE_URL
// ============================================

// Tự động phát hiện BASE_URL nếu chưa được định nghĩa
// BASE_URL là địa chỉ gốc của website (ví dụ: http://localhost:8000/)
if (!defined('BASE_URL')) {
    // Xác định giao thức: https nếu đang dùng HTTPS, ngược lại là http
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    
    // Lấy tên miền từ header HTTP_HOST, nếu không có thì dùng localhost:8000
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    
    // Lấy thư mục chứa script hiện tại (index.php)
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php');
    
    // Chuyển dấu \ thành / để tương thích với Windows
    $scriptDir = str_replace('\\', '/', $scriptDir);
    
    // Nếu thư mục là . hoặc / thì coi như rỗng (script ở thư mục gốc)
    if ($scriptDir === '.' || $scriptDir === '/') {
        $scriptDir = '';
    }
    
    // Tạo BASE_URL: protocol + host + scriptDir + /
    // Ví dụ: http://localhost:8000/
    define('BASE_URL', $protocol . $host . $scriptDir . '/');
}

// ============================================
// CẤU HÌNH SMTP (GỬI EMAIL)
// ============================================

// Địa chỉ máy chủ SMTP (Gmail)
define('SMTP_HOST', 'smtp.gmail.com');

// Cổng SMTP (587 là cổng TLS)
define('SMTP_PORT', 587);

// Email đăng nhập SMTP (tài khoản Gmail)
define('SMTP_USER', 'lyminhthuan.dhbk@gmail.com');      // user Gmail

// Mật khẩu ứng dụng Gmail (không phải mật khẩu đăng nhập thông thường)
define('SMTP_PASS', 'sbkakahgyoypbszd');            // app password

// Email người gửi (thường giống với SMTP_USER)
define('SMTP_FROM', 'lyminhthuan.dhbk@gmail.com');

// Tên hiển thị của người gửi
define('SMTP_FROM_NAME', 'Thuoc Phat Thuoc');   
