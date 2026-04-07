<?php
/**
 * app/controllers/AdminSaleController.php - Controller quản lý Sale (Khuyến mãi) cho Admin/Nhân viên
 *
 * Controller này xử lý các yêu cầu liên quan đến quản lý chương trình khuyến mãi từ phía admin/nhân viên,
 * bao gồm hiển thị danh sách, thêm, sửa, xóa chương trình sale.
 */

require_once __DIR__ . '/../models/SaleModel.php'; // Nạp Model quản lý sale
require_once __DIR__ . '/../models/ThuocModel.php'; // Nạp Model quản lý thuốc
require_once __DIR__ . '/../core/Auth.php'; // Nạp lớp xác thực
require_once __DIR__ . '/../core/Session.php'; // Nạp lớp quản lý session

class AdminSaleController
{
    // $saleModel: Đối tượng SaleModel để tương tác với database về sale
    private $saleModel;
    // $thuocModel: Đối tượng ThuocModel để tương tác với database về thuốc
    private $thuocModel;

    /**
     * __construct() - Hàm khởi tạo của Controller
     *
     * Khởi tạo các đối tượng Model cần thiết cho controller.
     */
    public function __construct()
    {
        $this->saleModel = new SaleModel(); // Tạo đối tượng SaleModel
        $this->thuocModel = new ThuocModel(); // Tạo đối tượng ThuocModel
    }

    /**
     * index() - Hiển thị danh sách chương trình sale với phân trang và tìm kiếm
     *
     * Hỗ trợ tìm kiếm theo tên thuốc và phân trang.
     * Yêu cầu người dùng phải là nhân viên hoặc admin.
     */
    public function index()
    {
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Lấy các tham số từ query string để phân trang và tìm kiếm
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1; // Trang hiện tại (mặc định là 1)
        $search = $_GET['search'] ?? ''; // Chuỗi tìm kiếm (mặc định là rỗng)
        $limit = 10; // Số lượng sale hiển thị trên mỗi trang
        $offset = ($page - 1) * $limit; // Vị trí bắt đầu lấy dữ liệu (dùng cho phân trang)

        // Lấy danh sách sale và tổng số sale
        $saleList = $this->saleModel->layTatCa($limit, $offset, $search);
        $total = $this->saleModel->demTongSo($search); // Đếm tổng số sale (có thể kèm tìm kiếm)
        $totalPages = ceil($total / $limit); // Tính tổng số trang (làm tròn lên)

        // Load view tùy theo vai trò: Admin hoặc Nhân viên có view khác nhau
        $isAdmin = Auth::isAdmin(); // Kiểm tra xem người dùng có phải admin không
        $viewPath = $isAdmin ? '/../views/admin/sale.php' : '/../views/nhanvien/sale.php';
        require __DIR__ . $viewPath; // Nạp và hiển thị view
    }

    /**
     * showForm() - Hiển thị form thêm/sửa chương trình sale
     *
     * Nếu có tham số 'id' trong query string, hiển thị form sửa với dữ liệu sale tương ứng.
     * Nếu không có 'id', hiển thị form thêm mới.
     * Yêu cầu người dùng phải là nhân viên hoặc admin.
     */
    public function showForm()
    {
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Lấy mã sale từ query string (nếu có thì là form sửa, không có thì là form thêm)
        $ma_sale = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $sale = null; // Biến lưu thông tin sale (null nếu là form thêm mới)
        
        // Nếu có mã sale, lấy thông tin sale để hiển thị trong form sửa
        if ($ma_sale > 0) {
            $sale = $this->saleModel->layTheoMa($ma_sale);
            if (!$sale) {
                // Nếu không tìm thấy sale, hiển thị thông báo lỗi và chuyển hướng
                Session::setFlash('error', 'Không tìm thấy sale');
                $redirectPage = Auth::isAdmin() ? 'admin_sale' : 'nhanvien_sale';
                header('Location: index.php?page=' . $redirectPage);
                exit;
            }
        }

        // Lấy danh sách tất cả thuốc để hiển thị trong dropdown chọn sản phẩm
        $thuocList = $this->thuocModel->layTatCa();

        require __DIR__ . '/../views/admin/sale_form.php'; // Nạp và hiển thị form
        exit;
    }

    /**
     * create() - Xử lý thêm chương trình sale mới
     *
     * Nhận dữ liệu từ form POST, validate và lưu vào database.
     * Yêu cầu người dùng phải là nhân viên hoặc admin.
     */
    public function create()
    {
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Chỉ chấp nhận request POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_sale');
            exit;
        }

        // Validate dữ liệu từ form
        $data = $this->validateData($_POST);
        $redirectPage = Auth::isAdmin() ? 'admin_sale' : 'nhanvien_sale';
        
        // Nếu validate thất bại, quay lại form
        if (!$data) {
            header('Location: index.php?page=' . $redirectPage . '&action=admin_sale_form');
            exit;
        }

        try {
            // Thêm chương trình sale mới vào database
            $this->saleModel->themSale($data);
            Session::setFlash('success', 'Thêm sale thành công!');
            header('Location: index.php?page=' . $redirectPage);
            exit;
        } catch (Exception $e) {
            // Nếu có lỗi, hiển thị thông báo lỗi và quay lại form
            Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
            header('Location: index.php?page=' . $redirectPage . '&action=admin_sale_form');
            exit;
        }
    }

    /**
     * update() - Xử lý cập nhật thông tin chương trình sale
     *
     * Nhận dữ liệu từ form POST, validate và cập nhật vào database.
     * Yêu cầu người dùng phải là nhân viên hoặc admin.
     */
    public function update()
    {
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Chỉ chấp nhận request POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_sale');
            exit;
        }

        // Lấy mã sale từ form POST
        $ma_sale = isset($_POST['ma_sale']) ? (int)$_POST['ma_sale'] : 0;
        $redirectPage = Auth::isAdmin() ? 'admin_sale' : 'nhanvien_sale';
        
        // Kiểm tra mã sale hợp lệ
        if ($ma_sale <= 0) {
            Session::setFlash('error', 'Mã sale không hợp lệ');
            header('Location: index.php?page=' . $redirectPage);
            exit;
        }

        // Validate dữ liệu từ form
        $data = $this->validateData($_POST);
        if (!$data) {
            // Nếu validate thất bại, quay lại form với mã sale
            header('Location: index.php?page=' . $redirectPage . '&action=admin_sale_form&id=' . $ma_sale);
            exit;
        }

        try {
            // Cập nhật thông tin chương trình sale trong database
            $this->saleModel->capNhatSale($ma_sale, $data);
            Session::setFlash('success', 'Cập nhật sale thành công!');
            header('Location: index.php?page=' . $redirectPage);
            exit;
        } catch (Exception $e) {
            // Nếu có lỗi, hiển thị thông báo lỗi và quay lại form
            Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
            header('Location: index.php?page=' . $redirectPage . '&action=admin_sale_form&id=' . $ma_sale);
            exit;
        }
    }

    /**
     * delete() - Xóa một chương trình sale khỏi database
     *
     * Yêu cầu người dùng phải là nhân viên hoặc admin.
     * Lấy mã sale từ query string 'id'.
     */
    public function delete()
    {
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Lấy mã sale từ query string
        $ma_sale = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $redirectPage = Auth::isAdmin() ? 'admin_sale' : 'nhanvien_sale';
        
        // Kiểm tra mã sale hợp lệ
        if ($ma_sale <= 0) {
            Session::setFlash('error', 'Mã sale không hợp lệ');
            header('Location: index.php?page=' . $redirectPage);
            exit;
        }

        try {
            // Xóa chương trình sale khỏi database
            $this->saleModel->xoaSale($ma_sale);
            Session::setFlash('success', 'Xóa sale thành công!');
        } catch (Exception $e) {
            // Nếu có lỗi, hiển thị thông báo lỗi
            Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
        }

        // Chuyển hướng về trang danh sách sale
        header('Location: index.php?page=' . $redirectPage);
        exit;
    }

    /**
     * validateData() - Validate dữ liệu từ form
     *
     * Kiểm tra các trường bắt buộc: sản phẩm, phần trăm giảm, thời gian bắt đầu, thời gian kết thúc.
     * Tính toán giá sale dựa trên giá gốc và phần trăm giảm.
     * Nếu có lỗi, lưu vào flash message và trả về false.
     *
     * @param array $post Mảng chứa dữ liệu từ form POST.
     * @return array|false Mảng dữ liệu đã được validate và format, hoặc false nếu có lỗi.
     */
    private function validateData($post)
    {
        $errors = []; // Mảng chứa danh sách lỗi validation

        // Lấy và ép kiểu các trường dữ liệu từ form
        $ma_thuoc = isset($post['ma_thuoc']) ? (int)$post['ma_thuoc'] : 0;
        $phan_tram_giam = isset($post['phan_tram_giam']) ? (float)$post['phan_tram_giam'] : 0;
        $thoi_gian_bat_dau = $post['thoi_gian_bat_dau'] ?? '';
        $thoi_gian_ket_thuc = $post['thoi_gian_ket_thuc'] ?? '';

        // Kiểm tra các trường bắt buộc và điều kiện
        if ($ma_thuoc <= 0) {
            $errors[] = 'Vui lòng chọn sản phẩm';
        }
        if ($phan_tram_giam <= 0 || $phan_tram_giam > 100) {
            $errors[] = 'Phần trăm giảm phải từ 1 đến 100';
        }
        if (empty($thoi_gian_bat_dau)) {
            $errors[] = 'Vui lòng chọn thời gian bắt đầu';
        }
        if (empty($thoi_gian_ket_thuc)) {
            $errors[] = 'Vui lòng chọn thời gian kết thúc';
        }
        // Kiểm tra thời gian kết thúc phải sau thời gian bắt đầu
        if (!empty($thoi_gian_bat_dau) && !empty($thoi_gian_ket_thuc)) {
            if (strtotime($thoi_gian_ket_thuc) <= strtotime($thoi_gian_bat_dau)) {
                $errors[] = 'Thời gian kết thúc phải sau thời gian bắt đầu';
            }
        }

        // Nếu có lỗi, lưu vào flash message và trả về false
        if (!empty($errors)) {
            Session::setFlash('errors', $errors);
            return false;
        }

        // Lấy thông tin thuốc để lấy giá gốc
        $thuoc = $this->thuocModel->layTheoMa($ma_thuoc);
        if (!$thuoc) {
            Session::setFlash('error', 'Không tìm thấy sản phẩm');
            return false;
        }

        // Tính giá sale dựa trên giá gốc và phần trăm giảm
        $gia_sale = $this->saleModel->tinhGiaSale($thuoc['gia'], $phan_tram_giam);

        // Trả về mảng dữ liệu đã được validate và format
        return [
            'ma_thuoc' => $ma_thuoc,
            'phan_tram_giam' => $phan_tram_giam,
            'gia_sale' => $gia_sale, // Giá sale đã được tính toán
            'thoi_gian_bat_dau' => $thoi_gian_bat_dau,
            'thoi_gian_ket_thuc' => $thoi_gian_ket_thuc,
            'trang_thai' => isset($post['trang_thai']) ? (int)$post['trang_thai'] : 1, // Trạng thái mặc định là 1 (hoạt động)
        ];
    }
}

