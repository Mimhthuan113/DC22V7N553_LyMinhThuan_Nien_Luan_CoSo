<?php
/**
 * app/controllers/AdminDonHangController.php - Controller quản lý Đơn hàng cho Admin/Nhân viên
 *
 * Controller này xử lý các yêu cầu liên quan đến quản lý đơn hàng từ phía admin/nhân viên:
 * - Hiển thị danh sách đơn hàng (có phân trang, tìm kiếm, lọc theo trạng thái)
 * - Xem chi tiết đơn hàng
 * - Cập nhật trạng thái đơn hàng (CHO_XU_LY, DANG_XU_LY, DANG_GIAO, HOAN_TAT, DA_HUY)
 * - Cập nhật trạng thái thanh toán (CHUA_THANH_TOAN, DA_THANH_TOAN, HOAN_TIEN)
 *
 * Routes:
 * - index.php?page=admin_donhang hoặc index.php?page=nhanvien_donhang -> index()
 * - index.php?page=admin_donhang&action=admin_donhang_chi_tiet&id={ma_don_hang} -> chiTiet()
 * - index.php?action=admin_donhang_cap_nhat_trang_thai (POST) -> capNhatTrangThai()
 * - index.php?action=admin_donhang_cap_nhat_trang_thai_thanh_toan (POST) -> capNhatTrangThaiThanhToan()
 */

require_once __DIR__ . '/../models/DonHangModel.php'; // Nạp Model quản lý đơn hàng
require_once __DIR__ . '/../core/Auth.php'; // Nạp lớp xác thực
require_once __DIR__ . '/../core/Session.php'; // Nạp lớp quản lý session

class AdminDonHangController
{
    // $donHangModel: Đối tượng DonHangModel để tương tác với database về đơn hàng
    private $donHangModel;

    /**
     * __construct() - Hàm khởi tạo của Controller
     *
     * Khởi tạo đối tượng DonHangModel để sử dụng trong các phương thức.
     */
    public function __construct()
    {
        $this->donHangModel = new DonHangModel(); // Tạo đối tượng DonHangModel
    }

    /**
     * index() - Hiển thị danh sách đơn hàng với phân trang, tìm kiếm và lọc theo trạng thái
     *
     * Route: index.php?page=admin_donhang hoặc index.php?page=nhanvien_donhang
     * Query parameters:
     *   - p: Số trang (mặc định là 1)
     *   - search: Chuỗi tìm kiếm theo mã đơn, tên khách hàng, email hoặc địa chỉ giao
     *   - trang_thai: Lọc theo trạng thái đơn hàng (CHO_XU_LY, DANG_XU_LY, DANG_GIAO, HOAN_TAT, DA_HUY)
     *
     * Chức năng:
     * 1. Yêu cầu người dùng phải là nhân viên hoặc admin
     * 2. Clean output buffer để tránh hiển thị nội dung không mong muốn (do có thể có output trước đó)
     * 3. Lấy các tham số phân trang, tìm kiếm và lọc từ query string
     * 4. Lấy danh sách đơn hàng từ database với các điều kiện đã lọc
     * 5. Tính tổng số trang
     * 6. Hiển thị view tùy theo vai trò (admin hoặc nhân viên có view khác nhau)
     *    - Admin: /views/admin/donhang.php
     *    - Nhân viên: /views/nhanvien/donhang.php
     */
    public function index()
    {
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Clean output buffer để tránh hiển thị nội dung không mong muốn (có thể có output từ header, session_start, etc.)
        while (ob_get_level()) {
            ob_end_clean(); // Xóa tất cả output buffer đang tồn tại
        }
        
        // Lấy các tham số từ query string
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1; // Trang hiện tại (mặc định là 1)
        $search = $_GET['search'] ?? ''; // Chuỗi tìm kiếm (mặc định là rỗng)
        $trang_thai_filter = $_GET['trang_thai'] ?? ''; // Lọc theo trạng thái (mặc định là rỗng - hiển thị tất cả)
        $limit = 10; // Số lượng đơn hàng hiển thị trên mỗi trang
        $offset = ($page - 1) * $limit; // Vị trí bắt đầu lấy dữ liệu (dùng cho phân trang)

        // Lấy danh sách đơn hàng từ database với các điều kiện đã lọc
        $donHangList = $this->donHangModel->layTatCa($limit, $offset, $search, $trang_thai_filter);
        // Đếm tổng số đơn hàng (có thể kèm tìm kiếm và lọc trạng thái)
        $total = $this->donHangModel->demTongSo($search, $trang_thai_filter);
        $totalPages = ceil($total / $limit); // Tính tổng số trang (làm tròn lên)

        // Xác định view path dựa trên vai trò người dùng
        $isAdmin = Auth::isAdmin(); // Kiểm tra xem người dùng có phải admin không
        $viewPath = $isAdmin ? '/../views/admin/donhang.php' : '/../views/nhanvien/donhang.php';
        require __DIR__ . $viewPath; // Nạp và hiển thị view
        exit; // Dừng script sau khi hiển thị view
    }

    /**
     * chiTiet() - Xem chi tiết một đơn hàng cụ thể
     *
     * Route: index.php?page=admin_donhang&action=admin_donhang_chi_tiet&id={ma_don_hang}
     *        hoặc index.php?page=nhanvien_donhang&action=admin_donhang_chi_tiet&id={ma_don_hang}
     * Query parameters:
     *   - id: Mã đơn hàng cần xem chi tiết
     *
     * Chức năng:
     * 1. Yêu cầu người dùng phải là nhân viên hoặc admin
     * 2. Clean output buffer để tránh hiển thị nội dung không mong muốn
     * 3. Lấy mã đơn hàng từ query string và kiểm tra tính hợp lệ
     * 4. Lấy thông tin đơn hàng từ database
     * 5. Nếu không tìm thấy, hiển thị thông báo lỗi và chuyển hướng về trang danh sách
     * 6. Lấy chi tiết các sản phẩm trong đơn hàng
     * 7. Hiển thị view chi tiết đơn hàng tùy theo vai trò:
     *    - Admin: /views/admin/donhang_chi_tiet.php
     *    - Nhân viên: /views/nhanvien/donhang_chi_tiet.php
     */
    public function chiTiet()
    {
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Clean output buffer để tránh hiển thị nội dung không mong muốn
        while (ob_get_level()) {
            ob_end_clean(); // Xóa tất cả output buffer đang tồn tại
        }
        
        // Lấy mã đơn hàng từ query string
        $ma_don_hang = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        // Kiểm tra mã đơn hàng hợp lệ
        if ($ma_don_hang <= 0) {
            Session::setFlash('error', 'Mã đơn hàng không hợp lệ');
            $redirectPage = Auth::isAdmin() ? 'admin_donhang' : 'nhanvien_donhang';
            header('Location: index.php?page=' . $redirectPage); // Chuyển hướng về trang danh sách
            exit;
        }

        // Lấy thông tin đơn hàng từ database
        $donHang = $this->donHangModel->layTheoMa($ma_don_hang);
        // Kiểm tra đơn hàng có tồn tại không
        if (!$donHang) {
            Session::setFlash('error', 'Không tìm thấy đơn hàng');
            $redirectPage = Auth::isAdmin() ? 'admin_donhang' : 'nhanvien_donhang';
            header('Location: index.php?page=' . $redirectPage); // Chuyển hướng về trang danh sách
            exit;
        }

        // Lấy chi tiết các sản phẩm trong đơn hàng (từ bảng chi_tiet_don_hang)
        $chiTiet = $this->donHangModel->layChiTietDonHang($ma_don_hang);

        // Xác định view path và redirect page dựa trên vai trò người dùng
        $isAdmin = Auth::isAdmin();
        $redirectPage = $isAdmin ? 'admin_donhang' : 'nhanvien_donhang';
        $viewPath = $isAdmin ? '/../views/admin/donhang_chi_tiet.php' : '/../views/nhanvien/donhang_chi_tiet.php';
        require __DIR__ . $viewPath; // Nạp và hiển thị view chi tiết đơn hàng
        exit; // Dừng script sau khi hiển thị view
    }

    /**
     * capNhatTrangThai() - Cập nhật trạng thái đơn hàng
     *
     * Route: index.php?action=admin_donhang_cap_nhat_trang_thai (POST)
     * POST data:
     *   - ma_don_hang: Mã đơn hàng cần cập nhật
     *   - trang_thai_don: Trạng thái mới (CHO_XU_LY, DANG_XU_LY, DANG_GIAO, HOAN_TAT, DA_HUY)
     *
     * Chức năng:
     * 1. Yêu cầu người dùng phải là nhân viên hoặc admin
     * 2. Chỉ chấp nhận request POST (bảo mật)
     * 3. Lấy mã đơn hàng và trạng thái mới từ form POST
     * 4. Validate trạng thái có nằm trong danh sách cho phép không
     * 5. Cập nhật trạng thái đơn hàng trong database
     * 6. Hiển thị thông báo thành công hoặc lỗi
     * 7. Chuyển hướng về trang chi tiết đơn hàng để xem kết quả
     *
     * Các trạng thái đơn hàng:
     * - CHO_XU_LY: Đơn hàng mới, chờ xử lý
     * - DANG_XU_LY: Đơn hàng đang được xử lý (đóng gói, chuẩn bị)
     * - DANG_GIAO: Đơn hàng đang được giao
     * - HOAN_TAT: Đơn hàng đã hoàn tất (khách hàng đã nhận)
     * - DA_HUY: Đơn hàng đã bị hủy
     */
    public function capNhatTrangThai()
    {
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Chỉ chấp nhận request POST (bảo mật)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $redirectPage = Auth::isAdmin() ? 'admin_donhang' : 'nhanvien_donhang';
            header('Location: index.php?page=' . $redirectPage); // Chuyển hướng về trang danh sách
            exit;
        }

        // Lấy mã đơn hàng và trạng thái mới từ form POST
        $ma_don_hang = isset($_POST['ma_don_hang']) ? (int)$_POST['ma_don_hang'] : 0;
        $trang_thai_don = $_POST['trang_thai_don'] ?? '';
        
        // Danh sách các trạng thái hợp lệ
        $allowedStatuses = ['CHO_XU_LY', 'DANG_XU_LY', 'DANG_GIAO', 'HOAN_TAT', 'DA_HUY'];
        // Validate dữ liệu: mã đơn hàng phải > 0 và trạng thái phải nằm trong danh sách cho phép
        if ($ma_don_hang <= 0 || !in_array($trang_thai_don, $allowedStatuses)) {
            Session::setFlash('error', 'Dữ liệu không hợp lệ');
        } else {
            // Cập nhật trạng thái đơn hàng trong database
            if ($this->donHangModel->capNhatTrangThai($ma_don_hang, $trang_thai_don)) {
                Session::setFlash('success', 'Đã cập nhật trạng thái đơn hàng');
            } else {
                Session::setFlash('error', 'Không thể cập nhật trạng thái đơn hàng');
            }
        }

        // Chuyển hướng về trang chi tiết đơn hàng để xem kết quả
        $redirectPage = Auth::isAdmin() ? 'admin_donhang' : 'nhanvien_donhang';
        header('Location: index.php?page=' . $redirectPage . '&action=admin_donhang_chi_tiet&id=' . $ma_don_hang);
        exit;
    }

    /**
     * capNhatTrangThaiThanhToan() - Cập nhật trạng thái thanh toán của đơn hàng
     *
     * Route: index.php?action=admin_donhang_cap_nhat_trang_thai_thanh_toan (POST)
     * POST data:
     *   - ma_don_hang: Mã đơn hàng cần cập nhật
     *   - trang_thai_thanh_toan: Trạng thái thanh toán mới (CHUA_THANH_TOAN, DA_THANH_TOAN, HOAN_TIEN)
     *
     * Chức năng:
     * 1. Yêu cầu người dùng phải là nhân viên hoặc admin
     * 2. Chỉ chấp nhận request POST (bảo mật)
     * 3. Lấy mã đơn hàng và trạng thái thanh toán mới từ form POST
     * 4. Validate trạng thái thanh toán có nằm trong danh sách cho phép không
     * 5. Cập nhật trạng thái thanh toán trong database
     * 6. Hiển thị thông báo thành công hoặc lỗi
     * 7. Chuyển hướng về trang chi tiết đơn hàng để xem kết quả
     *
     * Các trạng thái thanh toán:
     * - CHUA_THANH_TOAN: Đơn hàng chưa thanh toán (mặc định cho đơn COD)
     * - DA_THANH_TOAN: Đơn hàng đã thanh toán
     * - HOAN_TIEN: Đơn hàng đã được hoàn tiền (khi hủy đơn hoặc trả hàng)
     */
    public function capNhatTrangThaiThanhToan()
    {
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Chỉ chấp nhận request POST (bảo mật)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $redirectPage = Auth::isAdmin() ? 'admin_donhang' : 'nhanvien_donhang';
            header('Location: index.php?page=' . $redirectPage); // Chuyển hướng về trang danh sách
            exit;
        }

        // Lấy mã đơn hàng và trạng thái thanh toán mới từ form POST
        $ma_don_hang = isset($_POST['ma_don_hang']) ? (int)$_POST['ma_don_hang'] : 0;
        $trang_thai_thanh_toan = $_POST['trang_thai_thanh_toan'] ?? '';
        
        // Danh sách các trạng thái thanh toán hợp lệ
        $allowedStatuses = ['CHUA_THANH_TOAN', 'DA_THANH_TOAN', 'HOAN_TIEN'];
        // Validate dữ liệu: mã đơn hàng phải > 0 và trạng thái thanh toán phải nằm trong danh sách cho phép
        if ($ma_don_hang <= 0 || !in_array($trang_thai_thanh_toan, $allowedStatuses)) {
            Session::setFlash('error', 'Dữ liệu không hợp lệ');
        } else {
            // Cập nhật trạng thái thanh toán trong database
            if ($this->donHangModel->capNhatTrangThaiThanhToan($ma_don_hang, $trang_thai_thanh_toan)) {
                Session::setFlash('success', 'Đã cập nhật trạng thái thanh toán');
            } else {
                Session::setFlash('error', 'Không thể cập nhật trạng thái thanh toán');
            }
        }

        // Chuyển hướng về trang chi tiết đơn hàng để xem kết quả
        $redirectPage = Auth::isAdmin() ? 'admin_donhang' : 'nhanvien_donhang';
        header('Location: index.php?page=' . $redirectPage . '&action=admin_donhang_chi_tiet&id=' . $ma_don_hang);
        exit;
    }
}

