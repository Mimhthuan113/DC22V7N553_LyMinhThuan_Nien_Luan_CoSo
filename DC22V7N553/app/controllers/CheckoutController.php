<?php
/**
 * app/controllers/CheckoutController.php - Controller xử lý thanh toán/đặt hàng
 * 
 * Controller này xử lý quá trình đặt hàng từ giỏ hàng:
 * - Hiển thị trang checkout (xác nhận đơn hàng)
 * - Xử lý đặt hàng (tạo đơn hàng, trừ tồn kho, xóa giỏ hàng)
 * - Trang xác nhận đơn hàng thành công
 */

// Nạp các Model và Core cần thiết
require_once __DIR__ . '/../models/GioHangModel.php';
require_once __DIR__ . '/../models/DonHangModel.php';
require_once __DIR__ . '/../models/ThuocModel.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Session.php';

class CheckoutController
{
    /**
     * Model quản lý giỏ hàng
     */
    private $gioHangModel;
    
    /**
     * Model quản lý đơn hàng
     */
    private $donHangModel;
    
    /**
     * Model quản lý thuốc
     */
    private $thuocModel;

    /**
     * Constructor: Khởi tạo các Model
     */
    public function __construct()
    {
        $this->gioHangModel = new GioHangModel();
        $this->donHangModel = new DonHangModel();
        $this->thuocModel = new ThuocModel();
    }

    /**
     * Hiển thị trang checkout (xác nhận đơn hàng)
     * Yêu cầu người dùng phải đăng nhập
     * Kiểm tra giỏ hàng không rỗng và số lượng tồn kho
     * 
     * Route: index.php?page=checkout
     */
    public function index()
    {
        // Kiểm tra người dùng đã đăng nhập chưa
        // Nếu chưa đăng nhập, tự động chuyển về trang đăng nhập
        Auth::requireLogin();
        
        // Lấy thông tin người dùng từ session
        $user = Auth::user();
        if (!$user || !isset($user['ma_nguoi_dung'])) {
            Session::setFlash('error', 'Không tìm thấy thông tin người dùng');
            header('Location: index.php?page=login');
            exit;
        }
        
        // Lấy tất cả sản phẩm trong giỏ hàng
        // Tự động áp dụng giá sale nếu có (từ GioHangModel)
        $sanPham = $this->gioHangModel->layTatCaSanPham($user['ma_nguoi_dung']);
        
        // Nếu giỏ hàng rỗng, chuyển về trang giỏ hàng
        if (empty($sanPham)) {
            Session::setFlash('error', 'Giỏ hàng của bạn đang trống');
            header('Location: index.php?page=giohang');
            exit;
        }
        
        // Kiểm tra số lượng tồn kho cho từng sản phẩm
        // Đảm bảo số lượng trong giỏ hàng không vượt quá số lượng tồn kho
        $errors = [];
        $tongTien = 0;
        foreach ($sanPham as $sp) {
            // Nếu số lượng trong giỏ hàng > số lượng tồn kho
            if ($sp['so_luong'] > $sp['so_luong_ton']) {
                $errors[] = "Sản phẩm '{$sp['ten_thuoc']}' chỉ còn {$sp['so_luong_ton']} sản phẩm trong kho";
            }
            // Tính tổng tiền (cộng dồn thành tiền của từng sản phẩm)
            $tongTien += $sp['thanh_tien'];
        }
        
        // Nếu có lỗi về số lượng tồn kho, chuyển về trang giỏ hàng
        if (!empty($errors)) {
            Session::setFlash('errors', $errors);
            header('Location: index.php?page=giohang');
            exit;
        }
        
        // Lấy thông tin đầy đủ của người dùng để hiển thị (địa chỉ, số điện thoại)
        require_once __DIR__ . '/../models/NguoiDungModel.php';
        $nguoiDungModel = new NguoiDungModel();
        $nguoiDung = $nguoiDungModel->layTheoMa($user['ma_nguoi_dung']);
        
        // Merge thông tin vào $user để view có thể sử dụng
        if ($nguoiDung) {
            $user['dia_chi'] = $nguoiDung['dia_chi'] ?? '';           // Địa chỉ
            $user['so_dien_thoai'] = $nguoiDung['so_dien_thoai'] ?? ''; // Số điện thoại
        }
        
        // Lấy các flash message (nếu có)
        $error = Session::getFlash('error');
        $success = Session::getFlash('success');
        $errors = Session::getFlash('errors');
        
        // Hiển thị view checkout
        // Truyền các biến: $sanPham, $tongTien, $user, $error, $success, $errors
        require __DIR__ . '/../views/checkout/index.php';
    }

    /**
     * Xử lý đặt hàng
     * Tạo đơn hàng mới, trừ số lượng tồn kho, xóa giỏ hàng
     * Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
     * 
     * Route: index.php?action=checkout_order
     * Method: POST
     * 
     * POST data:
     *   - dia_chi_giao: Địa chỉ giao hàng (bắt buộc)
     *   - so_dien_thoai_giao: Số điện thoại giao hàng (bắt buộc)
     *   - ghi_chu: Ghi chú (tùy chọn)
     */
    public function datHang()
    {
        // Kiểm tra đăng nhập
        Auth::requireLogin();
        
        // Chỉ chấp nhận request POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=checkout');
            exit;
        }
        
        // Lấy thông tin người dùng
        $user = Auth::user();
        
        // Lấy lại sản phẩm trong giỏ hàng (để đảm bảo dữ liệu mới nhất)
        $sanPham = $this->gioHangModel->layTatCaSanPham($user['ma_nguoi_dung']);
        
        // Nếu giỏ hàng rỗng, chuyển về trang giỏ hàng
        if (empty($sanPham)) {
            Session::setFlash('error', 'Giỏ hàng của bạn đang trống');
            header('Location: index.php?page=giohang');
            exit;
        }
        
        // Validate dữ liệu từ form POST
        $dia_chi_giao = trim($_POST['dia_chi_giao'] ?? '');           // Địa chỉ giao hàng
        $so_dien_thoai_giao = trim($_POST['so_dien_thoai_giao'] ?? ''); // Số điện thoại giao hàng
        $ghi_chu = trim($_POST['ghi_chu'] ?? '');                     // Ghi chú
        
        // Kiểm tra địa chỉ giao hàng (bắt buộc)
        if (empty($dia_chi_giao)) {
            Session::setFlash('error', 'Vui lòng nhập địa chỉ giao hàng');
            header('Location: index.php?page=checkout');
            exit;
        }
        
        // Kiểm tra số điện thoại giao hàng (bắt buộc)
        if (empty($so_dien_thoai_giao)) {
            Session::setFlash('error', 'Vui lòng nhập số điện thoại giao hàng');
            header('Location: index.php?page=checkout');
            exit;
        }
        
        // Kiểm tra lại số lượng tồn kho (kiểm tra lại lần nữa trước khi đặt hàng)
        // Đảm bảo số lượng tồn kho không thay đổi giữa lúc xem checkout và lúc đặt hàng
        $tongTien = 0;
        $chiTiet = [];
        foreach ($sanPham as $sp) {
            // Lấy lại số lượng tồn kho mới nhất từ database
            $thuoc = $this->thuocModel->layTheoMa($sp['ma_thuoc']);
            
            // Nếu không tìm thấy thuốc hoặc số lượng trong giỏ hàng > số lượng tồn kho
            if (!$thuoc || $sp['so_luong'] > $thuoc['so_luong_ton']) {
                Session::setFlash('error', "Sản phẩm '{$sp['ten_thuoc']}' không đủ số lượng trong kho");
                header('Location: index.php?page=checkout');
                exit;
            }
            
            // Tính thành tiền cho sản phẩm này
            $thanh_tien = $sp['so_luong'] * $sp['don_gia'];
            
            // Cộng dồn vào tổng tiền
            $tongTien += $thanh_tien;
            
            // Thêm vào mảng chi tiết đơn hàng
            $chiTiet[] = [
                'ma_thuoc' => $sp['ma_thuoc'],      // Mã thuốc
                'so_luong' => $sp['so_luong'],      // Số lượng
                'don_gia' => $sp['don_gia'],        // Đơn giá (có thể là giá sale)
                'thanh_tien' => $thanh_tien         // Thành tiền
            ];
        }
        
        try {
            // Tạo đơn hàng mới
            // DonHangModel sẽ sử dụng transaction để đảm bảo:
            //   1. Tạo đơn hàng
            //   2. Thêm chi tiết đơn hàng
            //   3. Trừ số lượng tồn kho
            // Nếu có lỗi ở bất kỳ bước nào, rollback tất cả
            $data = [
                'ma_khach_hang' => $user['ma_nguoi_dung'],              // Mã khách hàng
                'trang_thai_don' => 'CHO_XU_LY',                        // Trạng thái đơn hàng: CHỜ XỬ LÝ
                'hinh_thuc_thanh_toan' => 'COD',                        // Hình thức thanh toán: COD (Cash On Delivery)
                'trang_thai_thanh_toan' => 'CHUA_THANH_TOAN',          // Trạng thái thanh toán: CHƯA THANH TOÁN
                'tong_tien' => $tongTien,                                // Tổng tiền đơn hàng
                'dia_chi_giao' => $dia_chi_giao,                         // Địa chỉ giao hàng
                'so_dien_thoai_giao' => $so_dien_thoai_giao,            // Số điện thoại giao hàng
                'ghi_chu' => $ghi_chu ?: null,                           // Ghi chú (null nếu rỗng)
                'chi_tiet' => $chiTiet                                   // Mảng chi tiết đơn hàng
            ];
            
            // Tạo đơn hàng (trả về mã đơn hàng)
            $ma_don_hang = $this->donHangModel->taoDonHang($data);
            
            // Xóa tất cả sản phẩm trong giỏ hàng sau khi đặt hàng thành công
            $this->gioHangModel->xoaTatCa($user['ma_nguoi_dung']);
            
            // Lấy thông tin đơn hàng để hiển thị mã đơn
            $donHang = $this->donHangModel->layTheoMa($ma_don_hang);
            $ma_don = $donHang['ma_don'] ?? '';  // Mã đơn hàng (ví dụ: DH202412260123)
            
            // Lưu thông báo thành công với mã đơn hàng
            Session::setFlash('success', 'Đơn hàng của bạn đã được đặt thành công! Mã đơn hàng: ' . $ma_don);
            
            // Chuyển đến trang danh sách đơn hàng của khách hàng
            header('Location: index.php?page=donhang_cua_toi');
            exit;
        } catch (Exception $e) {
            // Xử lý lỗi
            Session::setFlash('error', 'Có lỗi xảy ra khi đặt hàng: ' . $e->getMessage());
            header('Location: index.php?page=checkout');
            exit;
        }
    }

    /**
     * Trang xác nhận đơn hàng thành công
     * Hiển thị thông tin đơn hàng vừa đặt
     * 
     * Route: index.php?page=checkout_success&id=mã đơn hàng
     */
    public function success()
    {
        // Kiểm tra đăng nhập
        Auth::requireLogin();
        
        // Lấy mã đơn hàng từ URL
        $ma_don_hang = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        // Kiểm tra mã đơn hàng hợp lệ
        if ($ma_don_hang <= 0) {
            Session::setFlash('error', 'Mã đơn hàng không hợp lệ');
            header('Location: index.php?page=trangchu');
            exit;
        }
        
        // Lấy thông tin người dùng
        $user = Auth::user();
        
        // Lấy thông tin đơn hàng
        $donHang = $this->donHangModel->layTheoMa($ma_don_hang);
        
        // Kiểm tra đơn hàng có tồn tại và thuộc về người dùng này không
        // (Bảo mật: người dùng chỉ có thể xem đơn hàng của mình)
        if (!$donHang || $donHang['ma_khach_hang'] != $user['ma_nguoi_dung']) {
            Session::setFlash('error', 'Không tìm thấy đơn hàng');
            header('Location: index.php?page=trangchu');
            exit;
        }
        
        // Lấy chi tiết đơn hàng (danh sách sản phẩm trong đơn hàng)
        $chiTiet = $this->donHangModel->layChiTietDonHang($ma_don_hang);
        
        // Hiển thị view xác nhận đơn hàng
        // Truyền các biến: $donHang, $chiTiet
        require __DIR__ . '/../views/checkout/success.php';
    }
}
