<?php
/**
 * app/core/Model.php - Class cơ sở cho tất cả các Model
 * 
 * Class này là base class cho tất cả các Model trong ứng dụng
 * Cung cấp kết nối database sẵn cho các Model con
 */

// Nạp class Database để sử dụng
require_once __DIR__ . '/Database.php';

class Model
{
    /**
     * Đối tượng PDO kết nối đến database
     * Tất cả các Model con đều có thể sử dụng $this->db để thực hiện query
     */
    protected $db;

    /**
     * Constructor: Khởi tạo kết nối database
     * Tự động lấy kết nối database từ Database singleton
     */
    public function __construct()
    {
        // Lấy instance duy nhất của Database và lấy connection
        $this->db = Database::getInstance()->getConnection();
    }
}
