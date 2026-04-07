<?php
/**
 * app/controllers/AdminDanhMucController.php - Controller quản lý Danh mục cho Admin/Nhân viên
 *
 * Controller này xử lý các yêu cầu liên quan đến quản lý danh mục sản phẩm từ phía admin/nhân viên:
 * - Hiển thị danh sách danh mục dạng cây (cha-con) với tìm kiếm
 * - Thêm danh mục mới (có thể có danh mục cha)
 * - Sửa thông tin danh mục
 * - Xóa danh mục
 *
 * Routes:
 * - index.php?page=admin_danhmuc hoặc index.php?page=nhanvien_danhmuc -> index()
 * - index.php?page=admin_danhmuc&action=admin_danhmuc_form hoặc index.php?page=admin_danhmuc&action=admin_danhmuc_form&id={ma_danh_muc} -> showForm()
 * - index.php?action=admin_danhmuc_create (POST) -> create()
 * - index.php?action=admin_danhmuc_update (POST) -> update()
 * - index.php?page=admin_danhmuc&action=admin_danhmuc_delete&id={ma_danh_muc} -> delete()
 */

require_once __DIR__ . '/../models/DanhMucModel.php'; // Nạp Model quản lý danh mục
require_once __DIR__ . '/../core/Auth.php'; // Nạp lớp xác thực
require_once __DIR__ . '/../core/Session.php'; // Nạp lớp quản lý session

class AdminDanhMucController
{
    // $danhMucModel: Đối tượng DanhMucModel để tương tác với database về danh mục
    private $danhMucModel;

    /**
     * __construct() - Hàm khởi tạo của Controller
     *
     * Khởi tạo đối tượng DanhMucModel để sử dụng trong các phương thức.
     */
    public function __construct()
    {
        $this->danhMucModel = new DanhMucModel(); // Tạo đối tượng DanhMucModel
    }

    /**
     * index() - Hiển thị danh sách danh mục dạng cây (cha-con) với tìm kiếm
     *
     * Route: index.php?page=admin_danhmuc hoặc index.php?page=nhanvien_danhmuc
     * Query parameters:
     *   - search: Chuỗi tìm kiếm theo tên danh mục hoặc mô tả (tùy chọn)
     *
     * Chức năng:
     * 1. Yêu cầu người dùng phải là nhân viên hoặc admin
     * 2. Lấy chuỗi tìm kiếm từ query string (nếu có)
     * 3. Lấy tất cả danh mục từ database
     * 4. Lọc danh mục theo chuỗi tìm kiếm (tìm trong tên danh mục và mô tả, không phân biệt hoa thường)
     * 5. Tổ chức danh mục thành cấu trúc cây (cha-con) để hiển thị phân cấp
     * 6. Hiển thị view tùy theo vai trò:
     *    - Admin: /views/admin/danhmuc.php
     *    - Nhân viên: /views/nhanvien/danhmuc.php
     */
    public function index()
    {
        // Cho phép cả admin và nhân viên truy cập
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Lấy chuỗi tìm kiếm từ query string (nếu có)
        $search = $_GET['search'] ?? '';
        // Lấy tất cả danh mục từ database
        $danhMucList = $this->danhMucModel->layTatCa();
        
        // Lọc theo tìm kiếm nếu có (tìm trong tên danh mục và mô tả, không phân biệt hoa thường)
        if (!empty($search)) {
            $danhMucList = array_filter($danhMucList, function($dm) use ($search) {
                return stripos($dm['ten_danh_muc'], $search) !== false || // Tìm trong tên danh mục
                       stripos($dm['mo_ta'] ?? '', $search) !== false; // Tìm trong mô tả
            });
        }

        // Tổ chức danh mục theo cây (cha-con) để hiển thị phân cấp
        $danhMucTree = $this->organizeTree($danhMucList);

        // Xác định view path dựa trên quyền người dùng
        $isAdmin = Auth::isAdmin(); // Kiểm tra xem người dùng có phải admin không
        $viewPath = $isAdmin ? '/../views/admin/danhmuc.php' : '/../views/nhanvien/danhmuc.php';
        require __DIR__ . $viewPath; // Nạp và hiển thị view
    }

    /**
     * organizeTree() - Tổ chức danh sách danh mục phẳng thành cấu trúc cây (cha-con)
     *
     * Phương thức này chuyển đổi danh sách danh mục phẳng (flat array) thành cấu trúc cây
     * với mối quan hệ cha-con, giúp hiển thị danh mục theo phân cấp.
     *
     * @param array $danhMucList Mảng chứa danh sách danh mục phẳng từ database.
     * @return array Mảng chứa danh sách danh mục đã được tổ chức thành cây (các danh mục gốc có thuộc tính 'children').
     */
    private function organizeTree($danhMucList)
    {
        $tree = []; // Mảng chứa danh sách danh mục gốc (không có cha)
        $map = []; // Map để truy cập nhanh danh mục theo mã danh mục
        
        // Bước 1: Tạo map để truy cập nhanh danh mục theo mã danh mục
        foreach ($danhMucList as $dm) {
            $map[$dm['ma_danh_muc']] = $dm; // Lưu danh mục vào map
            $map[$dm['ma_danh_muc']]['children'] = []; // Khởi tạo mảng children rỗng
        }
        
        // Bước 2: Xây dựng cây bằng cách gán các danh mục con vào danh mục cha
        foreach ($map as $id => $dm) {
            if ($dm['ma_danh_muc_cha'] === null) {
                // Nếu là danh mục gốc (không có cha), thêm vào mảng $tree
                $tree[] = &$map[$id]; // Dùng tham chiếu để có thể sửa đổi sau
            } else {
                // Nếu có danh mục cha, thêm vào mảng children của cha
                if (isset($map[$dm['ma_danh_muc_cha']])) {
                    $map[$dm['ma_danh_muc_cha']]['children'][] = &$map[$id]; // Dùng tham chiếu
                }
            }
        }
        
        return $tree; // Trả về danh sách danh mục gốc (có chứa các danh mục con trong thuộc tính 'children')
    }

    /**
     * showForm() - Hiển thị form thêm/sửa danh mục
     *
     * Route: index.php?page=admin_danhmuc&action=admin_danhmuc_form (thêm mới)
     *        hoặc index.php?page=admin_danhmuc&action=admin_danhmuc_form&id={ma_danh_muc} (sửa)
     * Query parameters:
     *   - id: Mã danh mục cần sửa (nếu có thì là form sửa, không có thì là form thêm)
     *
     * Chức năng:
     * 1. Yêu cầu người dùng phải là nhân viên hoặc admin
     * 2. Lấy mã danh mục từ query string (nếu có)
     * 3. Lấy danh sách danh mục cha có thể chọn (loại trừ chính danh mục đang sửa để tránh vòng lặp)
     * 4. Nếu là form sửa, lấy thông tin danh mục từ database
     * 5. Nếu không tìm thấy danh mục, hiển thị thông báo lỗi và chuyển hướng
     * 6. Hiển thị form tùy theo vai trò:
     *    - Admin: /views/admin/danhmuc_form.php
     *    - Nhân viên: /views/nhanvien/danhmuc_form.php
     */
    public function showForm()
    {
        // Cho phép cả admin và nhân viên truy cập
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Lấy mã danh mục từ query string (nếu có thì là form sửa, không có thì là form thêm)
        $ma_danh_muc = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $danhMuc = null; // Biến lưu thông tin danh mục (null nếu là form thêm mới)
        // Lấy danh sách danh mục cha có thể chọn (loại trừ chính danh mục đang sửa để tránh vòng lặp)
        $danhMucChaList = $this->danhMucModel->layDanhMucCha($ma_danh_muc > 0 ? $ma_danh_muc : null);

        // Nếu có mã danh mục, lấy thông tin danh mục để hiển thị trong form sửa
        if ($ma_danh_muc > 0) {
            $danhMuc = $this->danhMucModel->layTheoMa($ma_danh_muc);
            if (!$danhMuc) {
                // Nếu không tìm thấy danh mục, hiển thị thông báo lỗi và chuyển hướng
                Session::setFlash('error', 'Không tìm thấy danh mục');
                $redirectPage = Auth::isAdmin() ? 'admin_danhmuc' : 'nhanvien_danhmuc';
                header('Location: index.php?page=' . $redirectPage);
                exit;
            }
        }

        // Xác định view path dựa trên quyền người dùng
        $isAdmin = Auth::isAdmin();
        $viewPath = $isAdmin ? '/../views/admin/danhmuc_form.php' : '/../views/nhanvien/danhmuc_form.php';
        require __DIR__ . $viewPath; // Nạp và hiển thị form
        exit;
    }

    /**
     * create() - Xử lý thêm danh mục mới
     *
     * Route: index.php?action=admin_danhmuc_create (POST)
     * POST data:
     *   - ten_danh_muc: Tên danh mục (bắt buộc)
     *   - slug: Slug cho URL (tùy chọn, sẽ tự động tạo nếu không có)
     *   - mo_ta: Mô tả danh mục (tùy chọn)
     *   - ma_danh_muc_cha: Mã danh mục cha (tùy chọn, null nếu là danh mục gốc)
     *   - trang_thai: Trạng thái (1: hoạt động, 0: không hoạt động)
     *
     * Chức năng:
     * 1. Yêu cầu người dùng phải là nhân viên hoặc admin
     * 2. Chỉ chấp nhận request POST (bảo mật)
     * 3. Validate dữ liệu từ form
     * 4. Tạo slug tự động nếu chưa có
     * 5. Thêm danh mục mới vào database
     * 6. Hiển thị thông báo thành công hoặc lỗi
     * 7. Chuyển hướng về trang danh sách danh mục
     */
    public function create()
    {
        // Cho phép cả admin và nhân viên truy cập
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        $redirectPage = Auth::isAdmin() ? 'admin_danhmuc' : 'nhanvien_danhmuc';
        
        // Chỉ chấp nhận request POST (bảo mật)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=' . $redirectPage);
            exit;
        }

        // Validate dữ liệu từ form
        $data = $this->validateData($_POST);
        if (!$data) {
            // Nếu validate thất bại, quay lại form
            header('Location: index.php?page=' . $redirectPage . '&action=admin_danhmuc_form');
            exit;
        }

        // Tạo slug tự động từ tên danh mục nếu chưa có (slug dùng cho URL thân thiện)
        if (empty($data['slug'])) {
            $data['slug'] = $this->danhMucModel->taoSlug($data['ten_danh_muc']);
        }

        try {
            // Thêm danh mục mới vào database
            $this->danhMucModel->themDanhMuc($data);
            Session::setFlash('success', 'Thêm danh mục thành công!');
            header('Location: index.php?page=' . $redirectPage); // Chuyển hướng về trang danh sách
            exit;
        } catch (Exception $e) {
            // Nếu có lỗi, hiển thị thông báo lỗi và quay lại form
            Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
            header('Location: index.php?page=' . $redirectPage . '&action=admin_danhmuc_form');
            exit;
        }
    }

    /**
     * update() - Xử lý cập nhật thông tin danh mục
     *
     * Route: index.php?action=admin_danhmuc_update (POST)
     * POST data:
     *   - ma_danh_muc: Mã danh mục cần cập nhật (bắt buộc)
     *   - ten_danh_muc: Tên danh mục mới (bắt buộc)
     *   - slug: Slug cho URL (tùy chọn, sẽ tự động tạo nếu không có)
     *   - mo_ta: Mô tả danh mục (tùy chọn)
     *   - ma_danh_muc_cha: Mã danh mục cha mới (tùy chọn, null nếu là danh mục gốc)
     *   - trang_thai: Trạng thái mới (1: hoạt động, 0: không hoạt động)
     *
     * Chức năng:
     * 1. Yêu cầu người dùng phải là nhân viên hoặc admin
     * 2. Chỉ chấp nhận request POST (bảo mật)
     * 3. Kiểm tra mã danh mục hợp lệ
     * 4. Validate dữ liệu (kiểm tra không được chọn chính nó làm danh mục cha)
     * 5. Tạo slug tự động nếu chưa có
     * 6. Cập nhật thông tin danh mục trong database
     * 7. Hiển thị thông báo thành công hoặc lỗi
     * 8. Chuyển hướng về trang danh sách danh mục
     */
    public function update()
    {
        // Cho phép cả admin và nhân viên truy cập
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        $redirectPage = Auth::isAdmin() ? 'admin_danhmuc' : 'nhanvien_danhmuc';
        
        // Chỉ chấp nhận request POST (bảo mật)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=' . $redirectPage);
            exit;
        }

        // Lấy mã danh mục từ form POST
        $ma_danh_muc = isset($_POST['ma_danh_muc']) ? (int)$_POST['ma_danh_muc'] : 0;
        // Kiểm tra mã danh mục hợp lệ
        if ($ma_danh_muc <= 0) {
            Session::setFlash('error', 'Mã danh mục không hợp lệ');
            header('Location: index.php?page=' . $redirectPage);
            exit;
        }

        // Validate dữ liệu (truyền mã danh mục để kiểm tra không được chọn chính nó làm cha)
        $data = $this->validateData($_POST, $ma_danh_muc);
        if (!$data) {
            // Nếu validate thất bại, quay lại form với mã danh mục
            header('Location: index.php?page=' . $redirectPage . '&action=admin_danhmuc_form&id=' . $ma_danh_muc);
            exit;
        }

        // Tạo slug tự động từ tên danh mục nếu chưa có
        if (empty($data['slug'])) {
            $data['slug'] = $this->danhMucModel->taoSlug($data['ten_danh_muc']);
        }

        try {
            // Cập nhật thông tin danh mục trong database
            $this->danhMucModel->capNhatDanhMuc($ma_danh_muc, $data);
            Session::setFlash('success', 'Cập nhật danh mục thành công!');
            header('Location: index.php?page=' . $redirectPage); // Chuyển hướng về trang danh sách
            exit;
        } catch (Exception $e) {
            // Nếu có lỗi, hiển thị thông báo lỗi và quay lại form
            Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
            header('Location: index.php?page=' . $redirectPage . '&action=admin_danhmuc_form&id=' . $ma_danh_muc);
            exit;
        }
    }

    /**
     * delete() - Xóa một danh mục khỏi database
     *
     * Route: index.php?page=admin_danhmuc&action=admin_danhmuc_delete&id={ma_danh_muc}
     *        hoặc index.php?page=nhanvien_danhmuc&action=admin_danhmuc_delete&id={ma_danh_muc}
     * Query parameters:
     *   - id: Mã danh mục cần xóa
     *
     * Chức năng:
     * 1. Yêu cầu người dùng phải là nhân viên hoặc admin
     * 2. Lấy mã danh mục từ query string
     * 3. Kiểm tra mã danh mục hợp lệ
     * 4. Xóa danh mục khỏi database (có thể xóa cả danh mục con nếu có)
     * 5. Hiển thị thông báo thành công hoặc lỗi
     * 6. Chuyển hướng về trang danh sách danh mục
     */
    public function delete()
    {
        // Cho phép cả admin và nhân viên truy cập
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        $redirectPage = Auth::isAdmin() ? 'admin_danhmuc' : 'nhanvien_danhmuc';
        
        // Lấy mã danh mục từ query string
        $ma_danh_muc = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        // Kiểm tra mã danh mục hợp lệ
        if ($ma_danh_muc <= 0) {
            Session::setFlash('error', 'Mã danh mục không hợp lệ');
            header('Location: index.php?page=' . $redirectPage);
            exit;
        }

        try {
            // Xóa danh mục khỏi database (có thể xóa cả danh mục con nếu có)
            $this->danhMucModel->xoaDanhMuc($ma_danh_muc);
            Session::setFlash('success', 'Xóa danh mục thành công!');
        } catch (Exception $e) {
            // Nếu có lỗi (ví dụ: danh mục đang được sử dụng bởi sản phẩm), hiển thị thông báo lỗi
            Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
        }

        // Chuyển hướng về trang danh sách danh mục
        header('Location: index.php?page=' . $redirectPage);
        exit;
    }

    /**
     * validateData() - Validate dữ liệu từ form
     *
     * Kiểm tra các trường bắt buộc và điều kiện:
     * - Tên danh mục không được rỗng
     * - Không được chọn chính danh mục đang sửa làm danh mục cha (tránh vòng lặp)
     *
     * @param array $post Mảng chứa dữ liệu từ form POST.
     * @param int|null $excludeId Mã danh mục cần loại trừ khi kiểm tra (dùng khi sửa, để tránh chọn chính nó làm cha).
     * @return array|false Mảng dữ liệu đã được validate và format, hoặc false nếu có lỗi.
     */
    private function validateData($post, $excludeId = null)
    {
        $errors = []; // Mảng chứa danh sách lỗi validation

        // Lấy và làm sạch dữ liệu từ form
        $ten_danh_muc = trim($post['ten_danh_muc'] ?? ''); // Loại bỏ khoảng trắng đầu cuối
        $ma_danh_muc_cha = !empty($post['ma_danh_muc_cha']) ? (int)$post['ma_danh_muc_cha'] : null; // Mã danh mục cha (null nếu là danh mục gốc)

        // Kiểm tra các trường bắt buộc
        if (empty($ten_danh_muc)) {
            $errors[] = 'Vui lòng nhập tên danh mục';
        }

        // Kiểm tra không được chọn chính danh mục đang sửa làm danh mục cha (tránh vòng lặp)
        if ($excludeId !== null && $ma_danh_muc_cha == $excludeId) {
            $errors[] = 'Không thể chọn chính danh mục này làm danh mục cha';
        }

        // Nếu có lỗi, lưu vào flash message và trả về false
        if (!empty($errors)) {
            Session::setFlash('errors', $errors);
            return false;
        }

        // Trả về mảng dữ liệu đã được validate và format
        return [
            'ten_danh_muc' => $ten_danh_muc,
            'slug' => $post['slug'] ?? null, // Slug cho URL thân thiện (tùy chọn)
            'mo_ta' => $post['mo_ta'] ?? null, // Mô tả danh mục (tùy chọn)
            'ma_danh_muc_cha' => $ma_danh_muc_cha, // Mã danh mục cha (null nếu là danh mục gốc)
            'trang_thai' => isset($post['trang_thai']) ? (int)$post['trang_thai'] : 1, // Trạng thái mặc định là 1 (hoạt động)
        ];
    }
}

