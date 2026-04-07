<?php
/**
 * app/core/Auth.php - Class quản lý xác thực người dùng
 * 
 * Class này cung cấp các phương thức để:
 * - Đăng nhập/đăng xuất người dùng
 * - Kiểm tra quyền truy cập (admin, nhân viên, khách hàng)
 * - Bảo vệ các trang cần đăng nhập
 */

// Nạp class Session để sử dụng
require_once __DIR__ . '/Session.php';

class Auth
{
    /**
     * Đăng nhập người dùng và lưu thông tin vào session
     * 
     * @param array $user Mảng chứa thông tin người dùng (ma_nguoi_dung, email, ho_ten, ten_vai_tro)
     */
    public static function login($user)
    {
        // Lưu mã người dùng vào session
        Session::set('user_id', $user['ma_nguoi_dung']);
        
        // Lưu email vào session
        Session::set('user_email', $user['email']);
        
        // Lưu họ tên vào session
        Session::set('user_name', $user['ho_ten']);
        
        // Lưu vai trò vào session (QUAN_TRI, NHAN_VIEN, KHACH_HANG)
        Session::set('user_role', $user['ten_vai_tro']);
    }

    /**
     * Đăng xuất người dùng và xóa tất cả thông tin session
     */
    public static function logout()
    {
        // Xóa từng thông tin người dùng khỏi session
        Session::remove('user_id');
        Session::remove('user_email');
        Session::remove('user_name');
        Session::remove('user_role');
        
        // Hủy toàn bộ session
        Session::destroy();
    }

    /**
     * Kiểm tra người dùng đã đăng nhập chưa
     * 
     * @return bool true nếu đã đăng nhập, false nếu chưa
     */
    public static function check()
    {
        // Kiểm tra xem có user_id trong session không
        return Session::has('user_id');
    }

    /**
     * Lấy thông tin người dùng hiện tại từ session
     * 
     * @return array|null Mảng thông tin người dùng hoặc null nếu chưa đăng nhập
     */
    public static function user()
    {
        // Nếu chưa đăng nhập, trả về null
        if (!self::check()) {
            return null;
        }
        
        // Trả về mảng thông tin người dùng từ session
        return [
            'id' => Session::get('user_id'),                    // ID người dùng
            'ma_nguoi_dung' => Session::get('user_id'),        // Alias để tương thích với code cũ
            'email' => Session::get('user_email'),              // Email
            'name' => Session::get('user_name'),                // Họ tên
            'role' => Session::get('user_role')                 // Vai trò
        ];
    }

    /**
     * Kiểm tra người dùng có phải là admin không
     * 
     * @return bool true nếu là admin, false nếu không
     */
    public static function isAdmin()
    {
        // Phải đã đăng nhập VÀ có vai trò là QUAN_TRI
        return self::check() && Session::get('user_role') === 'QUAN_TRI';
    }

    /**
     * Kiểm tra người dùng có phải là nhân viên không
     * 
     * @return bool true nếu là nhân viên, false nếu không
     */
    public static function isNhanVien()
    {
        // Phải đã đăng nhập VÀ có vai trò là NHAN_VIEN
        return self::check() && Session::get('user_role') === 'NHAN_VIEN';
    }

    /**
     * Kiểm tra người dùng có phải là khách hàng không
     * 
     * @return bool true nếu là khách hàng, false nếu không
     */
    public static function isKhachHang()
    {
        // Phải đã đăng nhập VÀ có vai trò là KHACH_HANG
        return self::check() && Session::get('user_role') === 'KHACH_HANG';
    }

    /**
     * Yêu cầu người dùng phải đăng nhập
     * Nếu chưa đăng nhập, tự động chuyển về trang đăng nhập
     */
    public static function requireLogin()
    {
        // Nếu chưa đăng nhập
        if (!self::check()) {
            // Chuyển hướng về trang đăng nhập
            header('Location: index.php?page=login');
            // Dừng xử lý
            exit;
        }
    }

    /**
     * Yêu cầu người dùng phải là admin
     * Nếu không phải admin, tự động chuyển về trang chủ
     */
    public static function requireAdmin()
    {
        // Đầu tiên kiểm tra đã đăng nhập chưa
        self::requireLogin();
        
        // Nếu không phải admin
        if (!self::isAdmin()) {
            // Chuyển hướng về trang chủ
            header('Location: index.php?page=trangchu');
            // Dừng xử lý
            exit;
        }
    }

    /**
     * Yêu cầu người dùng phải là nhân viên hoặc admin
     * Nếu không phải, tự động chuyển về trang chủ
     */
    public static function requireNhanVien()
    {
        // Đầu tiên kiểm tra đã đăng nhập chưa
        self::requireLogin();
        
        // Nếu không phải nhân viên VÀ không phải admin
        if (!self::isNhanVien() && !self::isAdmin()) {
            // Chuyển hướng về trang chủ
            header('Location: index.php?page=trangchu');
            // Dừng xử lý
            exit;
        }
    }
}
