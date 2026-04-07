<?php
/**
 * app/core/Database.php - Class quản lý kết nối database
 * 
 * Class này sử dụng Singleton Pattern để đảm bảo chỉ có 1 kết nối database duy nhất
 * trong suốt quá trình chạy ứng dụng, giúp tối ưu hiệu suất
 */

class Database
{
    // Biến static lưu instance duy nhất của Database (Singleton Pattern)
    private static $instance = null;
    
    // Đối tượng PDO kết nối đến database
    private $connection;

    /**
     * Constructor private để ngăn việc tạo instance từ bên ngoài
     * Chỉ có thể tạo instance thông qua getInstance()
     */
    private function __construct()
    {
        // Lấy thông tin kết nối từ config.php
        $host = DB_HOST;  // Địa chỉ máy chủ database
        $db = DB_NAME;    // Tên database
        $user = DB_USER;  // Tên người dùng
        $pass = DB_PASS;  // Mật khẩu

        try {
            // Tạo kết nối PDO đến MySQL database
            // charset=utf8: Đảm bảo hỗ trợ tiếng Việt
            $this->connection = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
            
            // Thiết lập chế độ báo lỗi: ném exception khi có lỗi SQL
            // Giúp dễ dàng debug và xử lý lỗi
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Nếu kết nối thất bại, dừng chương trình và hiển thị lỗi
            die("Kết nối database thất bại: " . $e->getMessage());
        }
    }

    /**
     * Phương thức static để lấy instance duy nhất của Database (Singleton Pattern)
     * 
     * @return Database Instance duy nhất của Database
     */
    public static function getInstance()
    {
        // Nếu chưa có instance, tạo mới
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        
        // Trả về instance đã tồn tại hoặc vừa tạo
        return self::$instance;
    }

    /**
     * Lấy đối tượng PDO connection để thực hiện các query
     * 
     * @return PDO Đối tượng PDO kết nối đến database
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
