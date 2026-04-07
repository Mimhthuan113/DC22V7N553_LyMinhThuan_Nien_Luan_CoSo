<?php
require_once __DIR__ . '/../models/DonHangModel.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Session.php';

class DonHangController
{
    private $donHangModel;

    public function __construct()
    {
        $this->donHangModel = new DonHangModel();
    }

    // Hiển thị danh sách đơn hàng của khách hàng
    public function index()
    {
        Auth::requireLogin();
        
        $user = Auth::user();
        if (!$user || !isset($user['ma_nguoi_dung'])) {
            Session::setFlash('error', 'Không tìm thấy thông tin người dùng');
            header('Location: index.php?page=login');
            exit;
        }
        
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $donHangList = $this->donHangModel->layTheoKhachHang($user['ma_nguoi_dung'], $limit, $offset);
        
        // Đếm tổng số đơn hàng
        $allOrders = $this->donHangModel->layTheoKhachHang($user['ma_nguoi_dung']);
        $total = count($allOrders);
        $totalPages = ceil($total / $limit);
        
        require __DIR__ . '/../views/donhang/index.php';
    }

    // Xem chi tiết đơn hàng của khách hàng
    public function chiTiet()
    {
        Auth::requireLogin();
        
        $user = Auth::user();
        if (!$user || !isset($user['ma_nguoi_dung'])) {
            Session::setFlash('error', 'Không tìm thấy thông tin người dùng');
            header('Location: index.php?page=login');
            exit;
        }
        
        $ma_don_hang = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($ma_don_hang <= 0) {
            Session::setFlash('error', 'Mã đơn hàng không hợp lệ');
            header('Location: index.php?page=donhang_cua_toi');
            exit;
        }
        
        $donHang = $this->donHangModel->layTheoMa($ma_don_hang);
        if (!$donHang || $donHang['ma_khach_hang'] != $user['ma_nguoi_dung']) {
            Session::setFlash('error', 'Không tìm thấy đơn hàng');
            header('Location: index.php?page=donhang_cua_toi');
            exit;
        }
        
        $chiTiet = $this->donHangModel->layChiTietDonHang($ma_don_hang);
        
        require __DIR__ . '/../views/donhang/chi_tiet.php';
    }
}

