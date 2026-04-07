<?php
/**
 * app/core/Session.php - Class quản lý session
 * 
 * Class này cung cấp các phương thức tiện lợi để làm việc với session PHP
 * Bao gồm: lưu, lấy, xóa dữ liệu session và flash messages
 */

class Session
{
    /**
     * Khởi động session nếu chưa được khởi động
     * Phải gọi trước khi sử dụng bất kỳ phương thức nào khác
     */
    public static function start()
    {
        // Kiểm tra session đã được khởi động chưa
        // PHP_SESSION_NONE = session chưa được khởi động
        if (session_status() === PHP_SESSION_NONE) {
            // Khởi động session
            session_start();
        }
    }

    /**
     * Lưu giá trị vào session với key cho trước
     * 
     * @param string $key Tên key để lưu giá trị
     * @param mixed $value Giá trị cần lưu (có thể là string, array, object, ...)
     */
    public static function set($key, $value)
    {
        // Đảm bảo session đã được khởi động
        self::start();
        
        // Lưu giá trị vào $_SESSION
        $_SESSION[$key] = $value;
    }

    /**
     * Lấy giá trị từ session theo key
     * 
     * @param string $key Tên key cần lấy giá trị
     * @param mixed $default Giá trị mặc định nếu key không tồn tại
     * @return mixed Giá trị trong session hoặc giá trị mặc định
     */
    public static function get($key, $default = null)
    {
        // Đảm bảo session đã được khởi động
        self::start();
        
        // Lấy giá trị từ $_SESSION, nếu không có thì trả về giá trị mặc định
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Kiểm tra xem key có tồn tại trong session không
     * 
     * @param string $key Tên key cần kiểm tra
     * @return bool true nếu key tồn tại, false nếu không
     */
    public static function has($key)
    {
        // Đảm bảo session đã được khởi động
        self::start();
        
        // Kiểm tra key có tồn tại trong $_SESSION không
        return isset($_SESSION[$key]);
    }

    /**
     * Xóa một key khỏi session
     * 
     * @param string $key Tên key cần xóa
     */
    public static function remove($key)
    {
        // Đảm bảo session đã được khởi động
        self::start();
        
        // Xóa key khỏi $_SESSION
        unset($_SESSION[$key]);
    }

    /**
     * Hủy toàn bộ session (đăng xuất)
     * Xóa tất cả dữ liệu trong session và hủy session
     */
    public static function destroy()
    {
        // Đảm bảo session đã được khởi động
        self::start();
        
        // Hủy session
        session_destroy();
    }

    /**
     * Lưu flash message (thông báo chỉ hiển thị 1 lần)
     * Flash message thường dùng để hiển thị thông báo thành công/lỗi sau khi submit form
     * 
     * @param string $key Tên key của flash message (ví dụ: 'success', 'error')
     * @param string $message Nội dung thông báo
     */
    public static function setFlash($key, $message)
    {
        // Lưu message với prefix 'flash_' để phân biệt với session thông thường
        self::set('flash_' . $key, $message);
    }

    /**
     * Lấy flash message và tự động xóa sau khi lấy
     * Flash message chỉ được lấy 1 lần, sau đó tự động bị xóa
     * 
     * @param string $key Tên key của flash message
     * @return string|null Nội dung thông báo hoặc null nếu không có
     */
    public static function getFlash($key)
    {
        // Lấy message từ session
        $message = self::get('flash_' . $key);
        
        // Xóa message sau khi lấy (đảm bảo chỉ hiển thị 1 lần)
        self::remove('flash_' . $key);
        
        // Trả về message
        return $message;
    }
}
