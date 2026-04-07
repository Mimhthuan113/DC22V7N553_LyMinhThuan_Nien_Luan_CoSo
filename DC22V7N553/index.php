<?php
/**
 * index.php - File entry point chính của ứng dụng
 * 
 * File này đóng vai trò là router trung tâm, xử lý tất cả các request từ người dùng
 * và điều hướng đến các controller tương ứng dựa trên tham số ?action= và ?page=
 */

// ============================================
// PHẦN 1: XỬ LÝ FILE TĨNH (ẢNH, CSS, JS)
// ============================================

// Lấy đường dẫn URL mà người dùng đang truy cập (ví dụ: /uploads/thuoc/image.jpg)
$requestUri = $_SERVER['REQUEST_URI'] ?? '';

// Phân tích URL để lấy phần path (bỏ qua query string và fragment)
$parsedUrl = parse_url($requestUri);

// Lấy phần đường dẫn từ URL đã phân tích (ví dụ: /uploads/thuoc/image.jpg)
$path = $parsedUrl['path'] ?? '';

// Kiểm tra xem request có phải là file trong thư mục uploads không
// Nếu có, serve file trực tiếp mà không cần qua routing (tăng tốc độ tải ảnh)
if (preg_match('#^/uploads/#', $path)) {
    // Tạo đường dẫn đầy đủ đến file trên server
    $filePath = __DIR__ . $path;
    
    // Kiểm tra file có tồn tại và là file thật (không phải thư mục) không
    if (file_exists($filePath) && is_file($filePath)) {
        // Lấy MIME type của file (loại file: image/jpeg, image/png, ...)
        $mimeType = mime_content_type($filePath);
        
        // Nếu không detect được MIME type, tự động xác định dựa trên đuôi file
        if (!$mimeType) {
            // Lấy đuôi file (jpg, png, avif, ...)
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            
            // Mảng map đuôi file với MIME type tương ứng
            $mimeTypes = [
                'jpg' => 'image/jpeg',   // File ảnh JPEG
                'jpeg' => 'image/jpeg',  // File ảnh JPEG
                'png' => 'image/png',    // File ảnh PNG
                'gif' => 'image/gif',    // File ảnh GIF
                'webp' => 'image/webp',  // File ảnh WebP
                'avif' => 'image/avif',  // File ảnh AVIF
            ];
            
            // Lấy MIME type từ mảng, nếu không có thì dùng mặc định
            $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
        }
        
        // Gửi header HTTP để trình duyệt biết loại file
        header('Content-Type: ' . $mimeType);
        
        // Gửi header HTTP để trình duyệt biết kích thước file
        header('Content-Length: ' . filesize($filePath));
        
        // Đọc và gửi nội dung file về trình duyệt
        readfile($filePath);
        
        // Dừng xử lý, không chạy code phía dưới
        exit;
    }
}

// ============================================
// PHẦN 2: CẤU HÌNH CƠ BẢN
// ============================================

// Bật output buffering: lưu tất cả output vào buffer trước khi gửi về trình duyệt
// Giúp tránh lỗi "headers already sent" khi có output trước khi gửi header
ob_start();

// Báo cáo tất cả các loại lỗi (E_ALL = tất cả lỗi)
error_reporting(E_ALL);

// Tắt hiển thị lỗi trên màn hình (để người dùng không thấy lỗi kỹ thuật)
ini_set('display_errors', 0);

// Bật ghi log lỗi vào file log (để developer debug)
ini_set('log_errors', 1);

// ============================================
// PHẦN 3: NẠP CÁC FILE CẦN THIẾT
// ============================================

// Nạp file cấu hình (database, BASE_URL, ...)
require_once 'config.php';

// Nạp class quản lý session (lưu trữ thông tin người dùng)
require_once 'app/core/Session.php';

// Nạp class xác thực người dùng (kiểm tra đăng nhập, phân quyền)
require_once 'app/core/Auth.php';

// Nạp các controller xử lý logic nghiệp vụ
require_once 'app/controllers/ThuocController.php';          // Controller quản lý thuốc (khách hàng)
require_once 'app/controllers/AuthController.php';            // Controller xử lý đăng nhập/đăng ký
require_once 'app/controllers/AdminUserController.php';       // Controller quản lý người dùng (admin)
require_once 'app/controllers/AdminThuocController.php';     // Controller quản lý thuốc (admin/nhân viên)
require_once 'app/controllers/AdminDanhMucController.php';   // Controller quản lý danh mục (admin/nhân viên)
require_once 'app/controllers/AccountController.php';         // Controller tài khoản cá nhân
require_once 'app/controllers/TrangChuController.php';       // Controller trang chủ
require_once 'app/controllers/GioHangController.php';        // Controller giỏ hàng
require_once 'app/controllers/AdminTinTucController.php';    // Controller quản lý tin tức (admin/nhân viên)
require_once 'app/controllers/AdminBannerController.php';    // Controller quản lý banner (admin/nhân viên)
require_once 'app/controllers/AdminDonHangController.php';    // Controller quản lý đơn hàng (admin/nhân viên)
require_once 'app/controllers/AdminSaleController.php';     // Controller quản lý sale (admin/nhân viên)
require_once 'app/controllers/TinTucController.php';         // Controller tin tức (khách hàng)
require_once 'app/controllers/CheckoutController.php';       // Controller thanh toán
require_once 'app/controllers/ExportController.php';        // Controller xuất Excel
require_once 'app/controllers/LienHeController.php';        // Controller liên hệ
require_once 'app/models/BannerModel.php';                   // Model quản lý banner

// Khởi động session để lưu trữ thông tin người dùng
Session::start();

// ============================================
// PHẦN 4: LẤY THAM SỐ TỪ URL
// ============================================

// Lấy tham số action từ URL (ví dụ: ?action=auth_login)
// Nếu không có thì mặc định là chuỗi rỗng
$action = $_GET['action'] ?? '';

// Lấy tham số page từ URL (ví dụ: ?page=trangchu)
// Nếu không có thì mặc định là chuỗi rỗng
$page = $_GET['page'] ?? '';

// ============================================
// PHẦN 5: XỬ LÝ CÁC ACTION CỤ THỂ
// ============================================

// Xử lý action gửi form liên hệ
if ($action === 'lienhe_submit') {
    // Tạo controller liên hệ
    $lienHeController = new LienHeController();
    
    // Gọi phương thức xử lý submit form
    $lienHeController->submit();
    
    // Dừng xử lý
    exit;
}

// Xử lý các action liên quan đến xác thực (đăng nhập, đăng ký, đăng xuất, ...)
// Kiểm tra xem action có bắt đầu bằng "auth_" không
if (strpos($action, 'auth_') === 0) {
    // Tạo controller xác thực
    $authController = new AuthController();
    
    // Xử lý từng loại action cụ thể
    switch ($action) {
        case 'auth_login':
            // Xử lý đăng nhập
            $authController->login();
            break;
            
        case 'auth_register':
            // Xử lý đăng ký tài khoản mới
            $authController->register();
            break;
            
        case 'auth_logout':
            // Xử lý đăng xuất
            $authController->logout();
            break;
            
        case 'auth_forgot':
            // Xử lý quên mật khẩu
            $authController->forgot();
            break;
            
        case 'auth_reset':
            // Xử lý reset mật khẩu
            $authController->reset();
            break;
            
        default:
            // Nếu không phải các action trên, chuyển về trang chủ
            header('Location: index.php?page=trangchu');
            exit;
    }
}

// Xử lý các action liên quan đến tài khoản cá nhân (cập nhật thông tin, đổi mật khẩu)
// Chỉ dành cho người dùng đã đăng nhập
if (strpos($action, 'account_') === 0) {
    // Kiểm tra người dùng đã đăng nhập chưa, nếu chưa thì chuyển về trang đăng nhập
    Auth::requireLogin();
    
    // Tạo controller tài khoản
    $accountController = new AccountController();
    
    switch ($action) {
        case 'account_update':
            // Cập nhật thông tin cá nhân
            $accountController->updateProfile();
            break;
            
        case 'account_change_password':
            // Đổi mật khẩu
            $accountController->changePassword();
            break;
            
        default:
            // Chuyển về trang tài khoản
            header('Location: index.php?page=account');
            exit;
    }
}

// Xử lý các action quản lý người dùng (chỉ dành cho admin)
if (strpos($action, 'admin_user_') === 0) {
    // Kiểm tra quyền admin, nếu không phải admin thì chuyển về trang chủ
    Auth::requireAdmin();
    
    // Tạo controller quản lý người dùng
    $adminUserController = new AdminUserController();
    
    switch ($action) {
        case 'admin_user_create':
            // Tạo người dùng mới
            $adminUserController->create();
            break;
            
        case 'admin_user_update_role':
            // Cập nhật vai trò người dùng (admin, nhân viên, khách hàng)
            $adminUserController->updateRole();
            break;
            
        case 'admin_user_toggle_status':
            // Bật/tắt trạng thái người dùng (kích hoạt/khóa tài khoản)
            $adminUserController->toggleStatus();
            break;
            
        case 'admin_user_delete':
            // Xóa người dùng
            $adminUserController->delete();
            break;
            
        default:
            // Chuyển về trang quản lý người dùng
            header('Location: index.php?page=admin_users');
            exit;
    }
}

// Xử lý các action quản lý thuốc (dành cho admin và nhân viên)
if (strpos($action, 'admin_thuoc_') === 0) {
    // Kiểm tra quyền nhân viên hoặc admin
    Auth::requireNhanVien();
    
    // Tạo controller quản lý thuốc
    $adminThuocController = new AdminThuocController();
    
    switch ($action) {
        case 'admin_thuoc_form':
            // Hiển thị form thêm/sửa thuốc
            $adminThuocController->showForm();
            break;
            
        case 'admin_thuoc_create':
            // Tạo thuốc mới
            $adminThuocController->create();
            break;
            
        case 'admin_thuoc_update':
            // Cập nhật thông tin thuốc
            $adminThuocController->update();
            break;
            
        case 'admin_thuoc_delete':
            // Xóa thuốc
            $adminThuocController->delete();
            break;
            
        case 'admin_thuoc_xuat_excel_het_han':
            // Xuất Excel danh sách thuốc đã hết hạn
            // Xóa output buffer để tránh lỗi khi xuất file Excel
            ob_end_clean();
            $adminThuocController->xuatExcelThuocHetHan();
            break;
            
        case 'admin_thuoc_xuat_excel_sap_het_han':
            // Xuất Excel danh sách thuốc sắp hết hạn
            // Xóa output buffer để tránh lỗi khi xuất file Excel
            ob_end_clean();
            $adminThuocController->xuatExcelThuocSapHetHan();
            break;
            
        default:
            // Chuyển về trang quản lý thuốc tương ứng với quyền
            $redirectPage = Auth::isAdmin() ? 'admin_thuoc' : 'nhanvien_thuoc';
            header('Location: index.php?page=' . $redirectPage);
            exit;
    }
}

// Xử lý các action quản lý danh mục (dành cho admin và nhân viên)
if (strpos($action, 'admin_danhmuc_') === 0) {
    // Kiểm tra quyền nhân viên hoặc admin
    Auth::requireNhanVien();
    
    // Tạo controller quản lý danh mục
    $adminDanhMucController = new AdminDanhMucController();
    
    switch ($action) {
        case 'admin_danhmuc_form':
            // Hiển thị form thêm/sửa danh mục
            $adminDanhMucController->showForm();
            break;
            
        case 'admin_danhmuc_create':
            // Tạo danh mục mới
            $adminDanhMucController->create();
            break;
            
        case 'admin_danhmuc_update':
            // Cập nhật thông tin danh mục
            $adminDanhMucController->update();
            break;
            
        case 'admin_danhmuc_delete':
            // Xóa danh mục
            $adminDanhMucController->delete();
            break;
            
        default:
            // Chuyển về trang quản lý danh mục tương ứng với quyền
            $redirectPage = Auth::isAdmin() ? 'admin_danhmuc' : 'nhanvien_danhmuc';
            header('Location: index.php?page=' . $redirectPage);
            exit;
    }
}

// Xử lý các action quản lý tin tức (dành cho admin và nhân viên)
if (strpos($action, 'admin_tintuc_') === 0) {
    // Kiểm tra quyền nhân viên hoặc admin
    Auth::requireNhanVien();
    
    // Tạo controller quản lý tin tức
    $adminTinTucController = new AdminTinTucController();
    
    switch ($action) {
        case 'admin_tintuc_form':
            // Hiển thị form thêm/sửa tin tức
            $adminTinTucController->showForm();
            break;
            
        case 'admin_tintuc_create':
            // Tạo tin tức mới
            $adminTinTucController->create();
            break;
            
        case 'admin_tintuc_update':
            // Cập nhật thông tin tin tức
            $adminTinTucController->update();
            break;
            
        case 'admin_tintuc_delete':
            // Xóa tin tức
            $adminTinTucController->delete();
            break;
            
        default:
            // Chuyển về trang quản lý tin tức tương ứng với quyền
            $redirectPage = Auth::isAdmin() ? 'admin_tintuc' : 'nhanvien_tintuc';
            header('Location: index.php?page=' . $redirectPage);
            exit;
    }
}

// Xử lý các action quản lý đơn hàng (dành cho admin và nhân viên)
if (strpos($action, 'admin_donhang_') === 0) {
    // Kiểm tra quyền nhân viên hoặc admin
    Auth::requireNhanVien();
    
    // Tạo controller quản lý đơn hàng
    $adminDonHangController = new AdminDonHangController();
    
    switch ($action) {
        case 'admin_donhang_chi_tiet':
            // Hiển thị chi tiết đơn hàng
            $adminDonHangController->chiTiet();
            break;
            
        case 'admin_donhang_cap_nhat_trang_thai':
            // Cập nhật trạng thái đơn hàng (chờ xử lý, đang xử lý, đã giao, ...)
            $adminDonHangController->capNhatTrangThai();
            break;
            
        case 'admin_donhang_cap_nhat_thanh_toan':
            // Cập nhật trạng thái thanh toán (chưa thanh toán, đã thanh toán)
            $adminDonHangController->capNhatTrangThaiThanhToan();
            break;
            
        default:
            // Chuyển về trang quản lý đơn hàng tương ứng với quyền
            $redirectPage = Auth::isAdmin() ? 'admin_donhang' : 'nhanvien_donhang';
            header('Location: index.php?page=' . $redirectPage);
            exit;
    }
}

// Xử lý các action quản lý banner (dành cho admin và nhân viên)
if (strpos($action, 'admin_banner_') === 0) {
    // Kiểm tra quyền nhân viên hoặc admin
    Auth::requireNhanVien();
    
    // Tạo controller quản lý banner
    $adminBannerController = new AdminBannerController();
    
    switch ($action) {
        case 'admin_banner_form':
            // Hiển thị form thêm/sửa banner
            $adminBannerController->showForm();
            break;
            
        case 'admin_banner_create':
            // Tạo banner mới
            $adminBannerController->create();
            break;
            
        case 'admin_banner_update':
            // Cập nhật thông tin banner
            $adminBannerController->update();
            break;
            
        case 'admin_banner_delete':
            // Xóa banner
            $adminBannerController->delete();
            break;
            
        default:
            // Chuyển về trang quản lý banner tương ứng với quyền
            $redirectPage = Auth::isAdmin() ? 'admin_banner' : 'nhanvien_banner';
            header('Location: index.php?page=' . $redirectPage);
            exit;
    }
}

// Xử lý các action quản lý sale (dành cho admin và nhân viên)
if (strpos($action, 'admin_sale_') === 0) {
    // Kiểm tra quyền nhân viên hoặc admin
    Auth::requireNhanVien();
    
    // Tạo controller quản lý sale
    $adminSaleController = new AdminSaleController();
    
    switch ($action) {
        case 'admin_sale_form':
            // Hiển thị form thêm/sửa sale
            $adminSaleController->showForm();
            break;
            
        case 'admin_sale_create':
            // Tạo sale mới
            $adminSaleController->create();
            break;
            
        case 'admin_sale_update':
            // Cập nhật thông tin sale
            $adminSaleController->update();
            break;
            
        case 'admin_sale_delete':
            // Xóa sale
            $adminSaleController->delete();
            break;
            
        default:
            // Chuyển về trang quản lý sale tương ứng với quyền
            $redirectPage = Auth::isAdmin() ? 'admin_sale' : 'nhanvien_sale';
            header('Location: index.php?page=' . $redirectPage);
            exit;
    }
}

// ============================================
// PHẦN 6: XỬ LÝ ACTION CHI TIẾT SẢN PHẨM
// ============================================

// Xử lý action xem chi tiết sản phẩm (xử lý trước để tránh conflict với routing khác)
if ($action === 'chi_tiet') {
    // Tạo controller thuốc
    $controller = new ThuocController();
    
    // Lấy mã thuốc từ tham số id trong URL
    $ma_thuoc = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    // Nếu không có id, thử lấy từ tham số ma_thuoc (để tương thích với các link cũ)
    if ($ma_thuoc <= 0) {
        $ma_thuoc = isset($_GET['ma_thuoc']) ? (int)$_GET['ma_thuoc'] : 0;
    }
    
    // Gọi phương thức hiển thị chi tiết sản phẩm
    $controller->chiTiet($ma_thuoc);
    
    // Dừng xử lý
    exit;
}

// ============================================
// PHẦN 7: XỬ LÝ CÁC API (TRẢ VỀ JSON)
// ============================================

// API tìm kiếm gợi ý (autocomplete) - PHẢI XỬ LÝ TRƯỚC PAGE
// Được gọi từ JavaScript khi người dùng gõ vào ô tìm kiếm
if ($action === 'search_suggestions') {
    // Xóa output buffer để tránh lỗi khi trả về JSON
    ob_end_clean();
    
    // Tạo controller thuốc
    $controller = new ThuocController();
    
    // Gọi phương thức tìm kiếm gợi ý (trả về JSON)
    $controller->timKiemSuggestions();
    
    // Dừng xử lý
    exit;
}

// API thêm sản phẩm vào giỏ hàng - PHẢI XỬ LÝ TRƯỚC PAGE
// Được gọi từ JavaScript khi người dùng click "Thêm vào giỏ"
if ($action === 'cart_add') {
    // Xóa output buffer để tránh lỗi khi trả về JSON
    ob_end_clean();
    
    // Tạo controller giỏ hàng
    $gioHangController = new GioHangController();
    
    // Gọi phương thức thêm sản phẩm vào giỏ hàng (trả về JSON)
    $gioHangController->themVaoGioHang();
    
    // Dừng xử lý
    exit;
}

// API lấy số lượng sản phẩm trong giỏ hàng
// Được gọi từ JavaScript để hiển thị số lượng trên icon giỏ hàng
if ($action === 'cart_count') {
    // Xóa output buffer để tránh lỗi khi trả về JSON
    ob_end_clean();
    
    // Tạo controller giỏ hàng
    $gioHangController = new GioHangController();
    
    // Gọi phương thức lấy số lượng (trả về JSON)
    $gioHangController->laySoLuong();
    
    // Dừng xử lý
    exit;
}

// API cập nhật số lượng sản phẩm trong giỏ hàng
// Được gọi từ JavaScript khi người dùng thay đổi số lượng
if ($action === 'cart_update') {
    // Xóa output buffer để tránh lỗi khi trả về JSON
    ob_end_clean();
    
    // Tạo controller giỏ hàng
    $gioHangController = new GioHangController();
    
    // Gọi phương thức cập nhật số lượng (trả về JSON)
    $gioHangController->capNhatSoLuong();
    
    // Dừng xử lý
    exit;
}

// API xóa sản phẩm khỏi giỏ hàng
// Được gọi từ JavaScript khi người dùng click "Xóa"
if ($action === 'cart_remove') {
    // Xóa output buffer để tránh lỗi khi trả về JSON
    ob_end_clean();
    
    // Tạo controller giỏ hàng
    $gioHangController = new GioHangController();
    
    // Gọi phương thức xóa sản phẩm (trả về JSON)
    $gioHangController->xoaSanPham();
    
    // Dừng xử lý
    exit;
}

// API đặt hàng
// Được gọi từ form thanh toán khi người dùng click "Đặt hàng"
if ($action === 'checkout_dat_hang') {
    // Xóa output buffer để tránh lỗi khi redirect
    ob_end_clean();
    
    // Tạo controller thanh toán
    $checkoutController = new CheckoutController();
    
    // Gọi phương thức đặt hàng
    $checkoutController->datHang();
    
    // Dừng xử lý
    exit;
}

// API xuất Excel thống kê (dành cho admin và nhân viên)
if ($action === 'xuat_excel_thongke') {
    // Kiểm tra quyền nhân viên hoặc admin
    Auth::requireNhanVien();
    
    // Xóa output buffer để tránh lỗi khi xuất file Excel
    ob_end_clean();
    
    // Tạo controller xuất Excel
    $exportController = new ExportController();
    
    // Gọi phương thức xuất Excel thống kê
    $exportController->xuatExcelThongKe();
    
    // Dừng xử lý
    exit;
}

// API thống kê doanh thu (trả về JSON cho biểu đồ)
// Dành cho admin và nhân viên
if ($action === 'thongke_doanhthu') {
    // Kiểm tra quyền nhân viên hoặc admin
    Auth::requireNhanVien();
    
    // Xóa output buffer để tránh lỗi khi trả về JSON
    ob_end_clean();
    
    // Set header để trình duyệt biết đây là JSON
    header('Content-Type: application/json; charset=utf-8');
    
    // Nạp model quản lý đơn hàng
    require_once __DIR__ . '/app/models/DonHangModel.php';
    $donHangModel = new DonHangModel();
    
    // Lấy các tham số từ URL
    $type = $_GET['type'] ?? 'ngay';        // Loại thống kê: ngay, thang, nam
    $startDate = $_GET['start_date'] ?? null; // Ngày bắt đầu (nếu có)
    $endDate = $_GET['end_date'] ?? null;     // Ngày kết thúc (nếu có)
    $year = isset($_GET['year']) ? (int)$_GET['year'] : null; // Năm (nếu thống kê theo tháng)
    
    try {
        // Khởi tạo các mảng để lưu dữ liệu
        $labels = [];    // Mảng nhãn (ngày/tháng/năm)
        $revenues = [];  // Mảng doanh thu tương ứng
        $orders = [];    // Mảng số đơn hàng tương ứng
        
        // Xử lý theo từng loại thống kê
        switch ($type) {
            case 'ngay':
                // Thống kê theo ngày
                $result = $donHangModel->thongKeDoanhThuTheoNgay($startDate, $endDate);
                
                // Duyệt qua kết quả và lưu vào mảng
                foreach ($result as $row) {
                    // Format ngày thành dd/mm/yyyy
                    $labels[] = date('d/m/Y', strtotime($row['ngay']));
                    // Lưu doanh thu (ép kiểu float)
                    $revenues[] = (float)$row['doanh_thu'];
                    // Lưu số đơn hàng (ép kiểu int)
                    $orders[] = (int)$row['so_don'];
                }
                break;
                
            case 'thang':
                // Thống kê theo tháng
                $result = $donHangModel->thongKeDoanhThuTheoThang($year);
                
                // Tạo map để dễ tìm kiếm (key = tháng, value = dữ liệu)
                $dataMap = [];
                foreach ($result as $row) {
                    $dataMap[(int)$row['thang']] = [
                        'doanh_thu' => (float)$row['doanh_thu'],
                        'so_don' => (int)$row['so_don']
                    ];
                }
                
                // Fill đầy đủ 12 tháng (nếu tháng nào không có dữ liệu thì = 0)
                $selectedYear = $year ?: date('Y'); // Nếu không có năm thì dùng năm hiện tại
                for ($thang = 1; $thang <= 12; $thang++) {
                    // Tạo nhãn: "Tháng 1/2025", "Tháng 2/2025", ...
                    $labels[] = "Tháng " . $thang . "/" . $selectedYear;
                    
                    // Kiểm tra tháng này có dữ liệu không
                    if (isset($dataMap[$thang])) {
                        // Có dữ liệu: lấy từ map
                        $revenues[] = $dataMap[$thang]['doanh_thu'];
                        $orders[] = $dataMap[$thang]['so_don'];
                    } else {
                        // Không có dữ liệu: gán = 0
                        $revenues[] = 0;
                        $orders[] = 0;
                    }
                }
                break;
                
            case 'nam':
                // Thống kê theo năm
                $result = $donHangModel->thongKeDoanhThuTheoNam();
                
                // Duyệt qua kết quả và lưu vào mảng
                foreach ($result as $row) {
                    // Tạo nhãn: "Năm 2024", "Năm 2025", ...
                    $labels[] = "Năm " . $row['nam'];
                    // Lưu doanh thu
                    $revenues[] = (float)$row['doanh_thu'];
                    // Lưu số đơn hàng
                    $orders[] = (int)$row['so_don'];
                }
                break;
        }
        
        // Lấy tổng kết (tổng doanh thu và tổng số đơn hàng)
        $tongKet = $donHangModel->tongDoanhThu($startDate, $endDate);
        
        // Trả về JSON với dữ liệu thống kê
        echo json_encode([
            'success' => true,                                    // Thành công
            'labels' => $labels,                                 // Mảng nhãn
            'revenues' => $revenues,                             // Mảng doanh thu
            'orders' => $orders,                                 // Mảng số đơn hàng
            'tong_doanh_thu' => (float)($tongKet['tong_doanh_thu'] ?? 0),  // Tổng doanh thu
            'tong_so_don' => (int)($tongKet['tong_so_don'] ?? 0)          // Tổng số đơn hàng
        ]);
    } catch (Exception $e) {
        // Nếu có lỗi, trả về JSON với thông báo lỗi
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    
    // Dừng xử lý
    exit;
}

// ============================================
// PHẦN 8: XỬ LÝ CÁC TRANG (PAGE)
// ============================================

// Xử lý trang đăng nhập
if ($page === 'login') {
    $authController = new AuthController();
    $authController->showLogin();
    exit;
    
// Xử lý trang đăng ký
} elseif ($page === 'register') {
    $authController = new AuthController();
    $authController->showRegister();
    exit;
    
// Xử lý trang quên mật khẩu
} elseif ($page === 'forgot') {
    $authController = new AuthController();
    $authController->showForgot();
    exit;
    
// Xử lý trang reset mật khẩu
} elseif ($page === 'reset') {
    $authController = new AuthController();
    $authController->showReset();
    exit;
    
// Xử lý trang giới thiệu
} elseif ($page === 'gioithieu') {
    require __DIR__ . '/app/views/gioithieu.php';
    exit;
    
// Xử lý trang chi tiết tin tức
} elseif ($page === 'tintuc') {
    $tinTucController = new TinTucController();
    // Lấy mã tin tức từ tham số id
    $ma_tin_tuc = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    // Hiển thị chi tiết tin tức
    $tinTucController->chiTiet($ma_tin_tuc);
    exit;
    
// Xử lý trang chủ (mặc định nếu page rỗng)
} elseif ($page === 'trangchu' || $page === '') {
    $trangChuController = new TrangChuController();
    $trangChuController->index();
    exit;
    
// Xử lý các trang admin (bắt đầu bằng "admin_")
} elseif ($page === 'admin' || strpos($page, 'admin_') === 0) {
    // Kiểm tra quyền admin, nếu không phải admin thì chuyển về trang chủ
    Auth::requireAdmin();

    // Xử lý từng trang admin cụ thể
    if ($page === 'admin_users') {
        // Trang quản lý người dùng
        $adminUserController = new AdminUserController();
        $adminUserController->index();
        
    } elseif ($page === 'admin_thuoc') {
        // Trang quản lý thuốc
        $adminThuocController = new AdminThuocController();
        $adminThuocController->index();
        
    } elseif ($page === 'admin_donhang') {
        // Trang quản lý đơn hàng
        $adminDonHangController = new AdminDonHangController();
        $adminDonHangController->index();
        
    } elseif ($page === 'admin_danhmuc') {
        // Trang quản lý danh mục
        $adminDanhMucController = new AdminDanhMucController();
        $adminDanhMucController->index();
        
    } elseif ($page === 'admin_tintuc') {
        // Trang quản lý tin tức
        $adminTinTucController = new AdminTinTucController();
        $adminTinTucController->index();
        
    } elseif ($page === 'admin_banner') {
        // Trang quản lý banner
        $adminBannerController = new AdminBannerController();
        $adminBannerController->index();
        
    } elseif ($page === 'admin_sale') {
        // Trang quản lý sale
        $adminSaleController = new AdminSaleController();
        $adminSaleController->index();
        
    } else {
        // Trang dashboard admin (mặc định)
        require __DIR__ . '/app/views/admin/dashboard.php';
    }
    exit;
    
// Xử lý trang dashboard nhân viên
} elseif ($page === 'nhanvien') {
    // Kiểm tra quyền nhân viên hoặc admin
    Auth::requireNhanVien();
    require __DIR__ . '/app/views/nhanvien/dashboard.php';
    exit;
    
// Xử lý trang quản lý thuốc cho nhân viên
} elseif ($page === 'nhanvien_thuoc') {
    Auth::requireNhanVien();
    $adminThuocController = new AdminThuocController();
    $adminThuocController->index();
    exit;
    
// Xử lý trang quản lý tin tức cho nhân viên
} elseif ($page === 'nhanvien_tintuc') {
    Auth::requireNhanVien();
    $adminTinTucController = new AdminTinTucController();
    $adminTinTucController->index();
    exit;
    
// Xử lý trang quản lý banner cho nhân viên
} elseif ($page === 'nhanvien_banner') {
    Auth::requireNhanVien();
    $adminBannerController = new AdminBannerController();
    $adminBannerController->index();
    exit;
    
// Xử lý trang quản lý đơn hàng cho nhân viên
} elseif ($page === 'nhanvien_donhang') {
    Auth::requireNhanVien();
    $adminDonHangController = new AdminDonHangController();
    $adminDonHangController->index();
    exit;
    
// Xử lý trang quản lý danh mục cho nhân viên
} elseif ($page === 'nhanvien_danhmuc') {
    Auth::requireNhanVien();
    $adminDanhMucController = new AdminDanhMucController();
    $adminDanhMucController->index();
    exit;
    
// Xử lý trang quản lý sale cho nhân viên
} elseif ($page === 'nhanvien_sale') {
    Auth::requireNhanVien();
    $adminSaleController = new AdminSaleController();
    $adminSaleController->index();
    exit;
    
// Xử lý trang tài khoản cá nhân
} elseif ($page === 'account') {
    // Kiểm tra đăng nhập
    Auth::requireLogin();
    $accountController = new AccountController();
    $accountController->show();
    exit;
    
// Xử lý trang giỏ hàng
} elseif ($page === 'giohang') {
    // Kiểm tra đăng nhập
    Auth::requireLogin();
    $gioHangController = new GioHangController();
    $gioHangController->index();
    exit;
    
// Xử lý trang thanh toán
} elseif ($page === 'checkout') {
    // Kiểm tra đăng nhập
    Auth::requireLogin();
    $checkoutController = new CheckoutController();
    $checkoutController->index();
    exit;
    
// Xử lý trang thành công sau khi đặt hàng
} elseif ($page === 'checkout_success') {
    // Kiểm tra đăng nhập
    Auth::requireLogin();
    $checkoutController = new CheckoutController();
    $checkoutController->success();
    exit;
    
// Xử lý trang danh sách đơn hàng của tôi
} elseif ($page === 'donhang_cua_toi') {
    // Kiểm tra đăng nhập
    Auth::requireLogin();
    require_once 'app/controllers/DonHangController.php';
    $donHangController = new DonHangController();
    $donHangController->index();
    exit;
    
// Xử lý trang chi tiết đơn hàng của tôi
} elseif ($page === 'donhang_chi_tiet') {
    // Kiểm tra đăng nhập
    Auth::requireLogin();
    require_once 'app/controllers/DonHangController.php';
    $donHangController = new DonHangController();
    $donHangController->chiTiet();
    exit;
}

// ============================================
// PHẦN 9: XỬ LÝ TRANG TÌM KIẾM
// ============================================

// Xử lý trang tìm kiếm sản phẩm
if ($page === 'timkiem') {
    $controller = new ThuocController();
    $controller->timKiem();
    exit;
}

// ============================================
// PHẦN 10: XỬ LÝ MẶC ĐỊNH (FALLBACK)
// ============================================

// Nếu không khớp với bất kỳ page nào ở trên, xử lý bằng controller thuốc
$controller = new ThuocController();

switch ($action) {
    case 'chi_tiet':
        // Xem chi tiết sản phẩm
        $ma_thuoc = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $controller->chiTiet($ma_thuoc);
        break;

    case 'index':
    default:
        // Trang danh sách sản phẩm mặc định
        $controller->index();
        break;
}
