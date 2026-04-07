<?php
/**
 * app/controllers/GioHangController.php - Controller quản lý giỏ hàng
 * 
 * Controller này xử lý các yêu cầu liên quan đến giỏ hàng:
 * - Thêm sản phẩm vào giỏ hàng (API)
 * - Cập nhật số lượng sản phẩm (API)
 * - Xóa sản phẩm khỏi giỏ hàng (API)
 * - Lấy số lượng sản phẩm trong giỏ hàng (API)
 * - Hiển thị trang giỏ hàng
 * 
 * Tất cả các API đều trả về JSON
 */

// Nạp các Model và Core cần thiết
require_once __DIR__ . '/../models/GioHangModel.php';
require_once __DIR__ . '/../models/ThuocModel.php';
require_once __DIR__ . '/../models/SaleModel.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Session.php';

class GioHangController
{
    /**
     * Model quản lý giỏ hàng
     */
    private $gioHangModel;
    
    /**
     * Model quản lý thuốc
     */
    private $thuocModel;

    /**
     * Model quản lý sale
     */
    private $saleModel;

    /**
     * Constructor: Khởi tạo các Model
     */
    public function __construct()
    {
        $this->gioHangModel = new GioHangModel();
        $this->thuocModel = new ThuocModel();
        $this->saleModel = new SaleModel();
    }

    /**
     * API: Thêm sản phẩm vào giỏ hàng
     * Được gọi từ JavaScript khi người dùng click "Thêm vào giỏ"
     * Tự động áp dụng giá sale nếu sản phẩm đang trong chương trình sale
     * 
     * Route: index.php?action=cart_add
     * Method: POST
     * 
     * POST data:
     *   - ma_thuoc: Mã thuốc cần thêm
     *   - so_luong: Số lượng (mặc định: 1)
     * 
     * Response JSON:
     *   - success: true/false
     *   - message: Thông báo
     *   - tong_so_luong: Tổng số lượng sản phẩm trong giỏ hàng (nếu thành công)
     */
    public function themVaoGioHang()
    {
        // Đảm bảo không có output trước header (để tránh lỗi "headers already sent")
        // Xóa tất cả output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set header để trình duyệt biết đây là JSON (hỗ trợ UTF-8)
        header('Content-Type: application/json; charset=utf-8');

        try {
            // Kiểm tra người dùng đã đăng nhập chưa
            // Giỏ hàng chỉ dành cho người dùng đã đăng nhập
            if (!Auth::check()) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Lấy thông tin người dùng từ session
            $user = Auth::user();
            if (!$user || !isset($user['id'])) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Lấy mã người dùng (ép kiểu int để đảm bảo an toàn)
            $ma_nguoi_dung = (int)$user['id'];

            // Lấy dữ liệu từ POST
            $ma_thuoc = isset($_POST['ma_thuoc']) ? (int)$_POST['ma_thuoc'] : 0;  // Mã thuốc
            $so_luong = isset($_POST['so_luong']) ? (int)$_POST['so_luong'] : 1;   // Số lượng (mặc định: 1)

            // Validate dữ liệu đầu vào
            if ($ma_thuoc <= 0 || $so_luong <= 0) {
                echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Kiểm tra sản phẩm có tồn tại trong database không
            $thuoc = $this->thuocModel->layTheoMa($ma_thuoc);
            if (!$thuoc) {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Kiểm tra trạng thái sản phẩm (phải = 1 = đang hoạt động)
            if ($thuoc['trang_thai'] != 1) {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm đã ngừng bán'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Kiểm tra hạn sử dụng (không cho thêm vào giỏ hàng nếu đã hết hạn)
            // Chỉ kiểm tra nếu có hạn sử dụng (một số thuốc không có hạn sử dụng)
            if (!empty($thuoc['han_su_dung']) && strtotime($thuoc['han_su_dung']) < strtotime('today')) {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm đã hết hạn'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Kiểm tra số lượng tồn kho
            // Không cho thêm vào giỏ hàng nếu số lượng yêu cầu > số lượng tồn kho
            if ($so_luong > $thuoc['so_luong_ton']) {
                echo json_encode(['success' => false, 'message' => 'Số lượng sản phẩm không đủ. Chỉ còn ' . $thuoc['so_luong_ton'] . ' sản phẩm'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Kiểm tra xem sản phẩm có đang trong chương trình sale không
            $sale = $this->saleModel->layTheoMaThuoc($ma_thuoc);
            
            // Giá mặc định là giá gốc
            $don_gia = $thuoc['gia'];
            
            // Nếu có sale đang hoạt động, dùng giá sale thay vì giá gốc
            if ($sale && isset($sale['gia_sale'])) {
                $don_gia = $sale['gia_sale'];
            }

            // Thêm sản phẩm vào giỏ hàng với giá đúng (sale hoặc giá gốc)
            // Nếu sản phẩm đã có trong giỏ hàng, cập nhật số lượng (cộng dồn)
            $this->gioHangModel->themVaoGioHang($ma_nguoi_dung, $ma_thuoc, $so_luong, $don_gia);
            
            // Lấy số lượng mới trong giỏ hàng (để cập nhật icon giỏ hàng trên header)
            $tongSoLuong = $this->gioHangModel->demSoLuongSanPham($ma_nguoi_dung);
            
            // Trả về JSON thành công
            echo json_encode([
                'success' => true, 
                'message' => 'Đã thêm sản phẩm vào giỏ hàng',
                'tong_so_luong' => $tongSoLuong  // Tổng số lượng để cập nhật icon giỏ hàng
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (PDOException $e) {
            // Xử lý lỗi database
            error_log('GioHangController PDO Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            echo json_encode([
                'success' => false, 
                'message' => 'Lỗi database: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            // Xử lý lỗi khác
            error_log('GioHangController Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            echo json_encode([
                'success' => false, 
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        } catch (Error $e) {
            // Xử lý lỗi fatal (PHP 7+)
            error_log('GioHangController Fatal Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            echo json_encode([
                'success' => false, 
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * API: Lấy số lượng sản phẩm trong giỏ hàng
     * Được gọi từ JavaScript để cập nhật số lượng trên icon giỏ hàng
     * 
     * Route: index.php?action=cart_count
     * 
     * Response JSON:
     *   - tong_so_luong: Tổng số lượng sản phẩm trong giỏ hàng (0 nếu chưa đăng nhập)
     */
    public function laySoLuong()
    {
        // Đảm bảo không có output trước header
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set header để trình duyệt biết đây là JSON
        header('Content-Type: application/json; charset=utf-8');

        // Nếu chưa đăng nhập, trả về 0
        if (!Auth::check()) {
            echo json_encode(['tong_so_luong' => 0], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Lấy thông tin người dùng
        $user = Auth::user();
        if (!$user || !isset($user['id'])) {
            echo json_encode(['tong_so_luong' => 0], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Đếm tổng số lượng sản phẩm trong giỏ hàng
        $tongSoLuong = $this->gioHangModel->demSoLuongSanPham($user['id']);
        
        // Trả về JSON với tổng số lượng
        echo json_encode(['tong_so_luong' => $tongSoLuong], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Hiển thị trang giỏ hàng
     * Yêu cầu người dùng phải đăng nhập
     * 
     * Route: index.php?page=giohang
     */
    public function index()
    {
        // Kiểm tra người dùng đã đăng nhập chưa
        // Nếu chưa đăng nhập, tự động chuyển về trang đăng nhập
        Auth::requireLogin();
        
        // Lấy thông tin người dùng từ session
        $user = Auth::user();
        $ma_nguoi_dung = $user['id'];
        
        // Lấy tất cả sản phẩm trong giỏ hàng
        // Tự động áp dụng giá sale nếu có (từ GioHangModel)
        $sanPhamTrongGio = $this->gioHangModel->layTatCaSanPham($ma_nguoi_dung);
        
        // Tính tổng tiền của giỏ hàng
        $tongTien = 0;
        foreach ($sanPhamTrongGio as $sp) {
            // Cộng dồn thành tiền của từng sản phẩm
            // thanh_tien = số lượng × đơn giá (đã được tính trong Model)
            $tongTien += (float)$sp['thanh_tien'];
        }
        
        // Hiển thị view giỏ hàng
        // Truyền các biến: $sanPhamTrongGio, $tongTien
        require __DIR__ . '/../views/giohang/index.php';
    }

    /**
     * API: Cập nhật số lượng sản phẩm trong giỏ hàng
     * Được gọi từ JavaScript khi người dùng thay đổi số lượng trong giỏ hàng
     * Nếu số lượng <= 0, tự động xóa sản phẩm khỏi giỏ hàng
     * 
     * Route: index.php?action=cart_update
     * Method: POST
     * 
     * POST data:
     *   - ma_chi_tiet: Mã chi tiết giỏ hàng
     *   - so_luong: Số lượng mới
     * 
     * Response JSON:
     *   - success: true/false
     *   - message: Thông báo
     */
    public function capNhatSoLuong()
    {
        // Đảm bảo không có output trước header
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set header để trình duyệt biết đây là JSON
        header('Content-Type: application/json; charset=utf-8');

        // Kiểm tra đăng nhập
        if (!Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Lấy dữ liệu từ POST
        $ma_chi_tiet = isset($_POST['ma_chi_tiet']) ? (int)$_POST['ma_chi_tiet'] : 0;  // Mã chi tiết giỏ hàng
        $so_luong = isset($_POST['so_luong']) ? (int)$_POST['so_luong'] : 1;             // Số lượng mới

        // Validate dữ liệu đầu vào
        if ($ma_chi_tiet <= 0 || $so_luong <= 0) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            // Cập nhật số lượng trong giỏ hàng
            // Nếu số lượng <= 0, Model sẽ tự động xóa sản phẩm
            $this->gioHangModel->capNhatSoLuong($ma_chi_tiet, $so_luong);
            
            // Trả về JSON thành công
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật số lượng'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            // Xử lý lỗi
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * API: Xóa sản phẩm khỏi giỏ hàng
     * Được gọi từ JavaScript khi người dùng click "Xóa" trong giỏ hàng
     * 
     * Route: index.php?action=cart_remove
     * Method: POST
     * 
     * POST data:
     *   - ma_chi_tiet: Mã chi tiết giỏ hàng cần xóa
     * 
     * Response JSON:
     *   - success: true/false
     *   - message: Thông báo
     */
    public function xoaSanPham()
    {
        // Đảm bảo không có output trước header
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set header để trình duyệt biết đây là JSON
        header('Content-Type: application/json; charset=utf-8');

        // Kiểm tra đăng nhập
        if (!Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Lấy mã chi tiết giỏ hàng từ POST
        $ma_chi_tiet = isset($_POST['ma_chi_tiet']) ? (int)$_POST['ma_chi_tiet'] : 0;

        // Validate dữ liệu đầu vào
        if ($ma_chi_tiet <= 0) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            // Xóa sản phẩm khỏi giỏ hàng
            $this->gioHangModel->xoaSanPham($ma_chi_tiet);
            
            // Trả về JSON thành công
            echo json_encode(['success' => true, 'message' => 'Đã xóa sản phẩm khỏi giỏ hàng'], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            // Xử lý lỗi
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}
