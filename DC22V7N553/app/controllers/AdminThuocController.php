<?php
/**
 * app/controllers/AdminThuocController.php - Controller quản lý Thuốc cho Admin/Nhân viên
 *
 * Controller này xử lý các yêu cầu liên quan đến quản lý thuốc từ phía admin/nhân viên,
 * bao gồm hiển thị danh sách, thêm, sửa, xóa thuốc, upload ảnh và xuất Excel.
 */

require_once __DIR__ . '/../models/ThuocModel.php'; // Nạp Model quản lý thuốc
require_once __DIR__ . '/../models/DanhMucModel.php'; // Nạp Model quản lý danh mục
require_once __DIR__ . '/../core/Auth.php'; // Nạp lớp xác thực
require_once __DIR__ . '/../core/Session.php'; // Nạp lớp quản lý session

class AdminThuocController
{
    // $thuocModel: Đối tượng ThuocModel để tương tác với database về thuốc
    private $thuocModel;
    // $danhMucModel: Đối tượng DanhMucModel để tương tác với database về danh mục
    private $danhMucModel;

    /**
     * __construct() - Hàm khởi tạo của Controller
     *
     * Khởi tạo các đối tượng Model cần thiết cho controller.
     */
    public function __construct()
    {
        $this->thuocModel = new ThuocModel(); // Tạo đối tượng ThuocModel
        $this->danhMucModel = new DanhMucModel(); // Tạo đối tượng DanhMucModel
    }

    /**
     * index() - Hiển thị danh sách thuốc với phân trang, tìm kiếm và lọc
     *
     * Hỗ trợ lọc theo trạng thái hết hạn (expired, expiring) và tìm kiếm theo tên thuốc.
     * Yêu cầu người dùng phải là nhân viên hoặc admin.
     */
    public function index()
    {
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Lấy các tham số từ query string để phân trang, tìm kiếm và lọc
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1; // Trang hiện tại (mặc định là 1)
        $search = $_GET['search'] ?? ''; // Chuỗi tìm kiếm (mặc định là rỗng)
        $filter = $_GET['filter'] ?? 'all'; // Bộ lọc: 'all' (tất cả), 'expired' (hết hạn), 'expiring' (sắp hết hạn)
        $limit = 10; // Số lượng thuốc hiển thị trên mỗi trang
        $offset = ($page - 1) * $limit; // Vị trí bắt đầu lấy dữ liệu (dùng cho phân trang)

        // Xử lý filter: Lấy danh sách thuốc theo bộ lọc đã chọn
        if ($filter === 'expired') {
            // Lấy danh sách thuốc đã hết hạn
            $thuocList = $this->thuocModel->layThuocHetHan($limit, $offset);
            $total = $this->thuocModel->demThuocHetHan(); // Đếm tổng số thuốc đã hết hạn
            $tongChiPhi = $this->thuocModel->tinhTongChiPhiThuocHetHan(); // Tính tổng chi phí thuốc hết hạn
        } elseif ($filter === 'expiring') {
            // Lấy danh sách thuốc sắp hết hạn (trong 30 ngày tới)
            $thuocList = $this->thuocModel->layThuocSapHetHan($limit, $offset);
            $total = $this->thuocModel->demThuocSapHetHan(); // Đếm tổng số thuốc sắp hết hạn
            $tongChiPhi = $this->thuocModel->tinhTongChiPhiThuocSapHetHan(); // Tính tổng chi phí thuốc sắp hết hạn
        } else {
            // Lấy tất cả thuốc (có thể kèm tìm kiếm)
            $thuocList = $this->thuocModel->layTatCa($limit, $offset, $search);
            $total = $this->thuocModel->demTongSo($search); // Đếm tổng số thuốc (có thể kèm tìm kiếm)
            $tongChiPhi = null; // Không tính tổng chi phí cho danh sách tất cả
        }
        
        $totalPages = ceil($total / $limit); // Tính tổng số trang (làm tròn lên)
        $danhMucList = $this->danhMucModel->layTatCa(); // Lấy danh sách tất cả danh mục để hiển thị

        // Load view tùy theo vai trò: Admin hoặc Nhân viên có view khác nhau
        $isAdmin = Auth::isAdmin(); // Kiểm tra xem người dùng có phải admin không
        $viewPath = $isAdmin ? '/../views/admin/thuoc.php' : '/../views/nhanvien/thuoc.php';
        // Các biến sẽ được truyền vào view: $thuocList, $total, $totalPages, $page, $search, $filter, $tongChiPhi, $limit
        require __DIR__ . $viewPath; // Nạp và hiển thị view
    }

    /**
     * showForm() - Hiển thị form thêm/sửa thuốc
     *
     * Nếu có tham số 'id' trong query string, hiển thị form sửa với dữ liệu thuốc tương ứng.
     * Nếu không có 'id', hiển thị form thêm mới.
     * Yêu cầu người dùng phải là nhân viên hoặc admin.
     */
    public function showForm()
    {
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Lấy mã thuốc từ query string (nếu có thì là form sửa, không có thì là form thêm)
        $ma_thuoc = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $thuoc = null; // Biến lưu thông tin thuốc (null nếu là form thêm mới)
        $danhMucListRaw = $this->danhMucModel->layTatCa(); // Lấy danh sách danh mục từ database
        
        // Tổ chức danh mục thành cây và format để hiển thị trong dropdown với indent theo cấp độ
        $danhMucList = $this->formatDanhMucForDropdown($danhMucListRaw);

        // Nếu có mã thuốc, lấy thông tin thuốc để hiển thị trong form sửa
        if ($ma_thuoc > 0) {
            $thuoc = $this->thuocModel->layTheoMa($ma_thuoc);
            if (!$thuoc) {
                // Nếu không tìm thấy thuốc, hiển thị thông báo lỗi và chuyển hướng
                Session::setFlash('error', 'Không tìm thấy thuốc');
                $redirectPage = Auth::isAdmin() ? 'admin_thuoc' : 'nhanvien_thuoc';
                header('Location: index.php?page=' . $redirectPage);
                exit;
            }
        }

        require __DIR__ . '/../views/admin/thuoc_form.php'; // Nạp và hiển thị form
        exit;
    }

    /**
     * formatDanhMucForDropdown() - Format danh mục để hiển thị trong dropdown với indent theo cấp độ
     *
     * Chuyển đổi danh sách danh mục phẳng thành danh sách có cấu trúc cây,
     * thêm ký tự indent (└─) để thể hiện mối quan hệ cha-con.
     *
     * @param array $danhMucList Mảng chứa danh sách danh mục từ database.
     * @return array Mảng chứa danh sách danh mục đã được format với indent.
     */
    private function formatDanhMucForDropdown($danhMucList)
    {
        $result = []; // Mảng kết quả chứa danh sách danh mục đã được format
        
        // Hàm đệ quy để format danh mục theo cấu trúc cây
        $formatCategory = function($category, $level = 0) use (&$formatCategory, &$result, $danhMucList) {
            // Tạo prefix indent dựa trên cấp độ (cấp 0 không có indent, cấp 1 trở lên có └─)
            $prefix = $level > 0 ? str_repeat('└─ ', $level) : '';
            // Thêm danh mục vào kết quả với tên đã được format (có indent)
            $result[] = [
                'ma_danh_muc' => $category['ma_danh_muc'],
                'ten_danh_muc' => $prefix . $category['ten_danh_muc'], // Tên có indent
                'ten_danh_muc_original' => $category['ten_danh_muc'], // Tên gốc (không có indent)
                'ma_danh_muc_cha' => $category['ma_danh_muc_cha']
            ];
            
            // Tìm và format các danh mục con (đệ quy)
            foreach ($danhMucList as $dm) {
                if ($dm['ma_danh_muc_cha'] == $category['ma_danh_muc']) {
                    $formatCategory($dm, $level + 1); // Gọi đệ quy với cấp độ tăng lên 1
                }
            }
        };
        
        // Bắt đầu với các danh mục gốc (không có cha, ma_danh_muc_cha = null)
        foreach ($danhMucList as $dm) {
            if ($dm['ma_danh_muc_cha'] === null) {
                $formatCategory($dm, 0); // Format danh mục gốc với cấp độ 0
            }
        }
        
        return $result; // Trả về danh sách danh mục đã được format
    }

    /**
     * create() - Xử lý thêm thuốc mới
     *
     * Nhận dữ liệu từ form POST, validate, upload ảnh và lưu vào database.
     * Yêu cầu người dùng phải là nhân viên hoặc admin.
     */
    public function create()
    {
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Chỉ chấp nhận request POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_thuoc');
            exit;
        }

        // Validate dữ liệu từ form
        $data = $this->validateData($_POST);
        $redirectPage = Auth::isAdmin() ? 'admin_thuoc' : 'nhanvien_thuoc';
        
        // Nếu validate thất bại, quay lại form
        if (!$data) {
            header('Location: index.php?page=' . $redirectPage . '&action=admin_thuoc_form');
            exit;
        }

        // Xử lý upload tối đa 5 ảnh cho thuốc
        $images = $this->processThuocImages();
        $data['hinh_anh']   = $images[0] ?? null; // Ảnh chính
        $data['hinh_anh_2'] = $images[1] ?? null; // Ảnh phụ 1
        $data['hinh_anh_3'] = $images[2] ?? null; // Ảnh phụ 2
        $data['hinh_anh_4'] = $images[3] ?? null; // Ảnh phụ 3
        $data['hinh_anh_5'] = $images[4] ?? null; // Ảnh phụ 4

        // Tạo slug từ tên thuốc nếu chưa có (slug dùng cho URL thân thiện)
        if (empty($data['slug'])) {
            $data['slug'] = $this->thuocModel->taoSlug($data['ten_thuoc']);
        }

        try {
            // Thêm thuốc mới vào database
            $this->thuocModel->themThuoc($data);
            Session::setFlash('success', 'Thêm thuốc thành công!');
            header('Location: index.php?page=' . $redirectPage);
            exit;
        } catch (Exception $e) {
            // Nếu có lỗi, hiển thị thông báo lỗi và quay lại form
            Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
            header('Location: index.php?page=' . $redirectPage . '&action=admin_thuoc_form');
            exit;
        }
    }

    /**
     * update() - Xử lý cập nhật thông tin thuốc
     *
     * Nhận dữ liệu từ form POST, validate, xử lý upload ảnh (giữ ảnh cũ nếu không upload mới)
     * và cập nhật vào database.
     * Yêu cầu người dùng phải là nhân viên hoặc admin.
     */
    public function update()
    {
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Chỉ chấp nhận request POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_thuoc');
            exit;
        }

        // Lấy mã thuốc từ form POST
        $ma_thuoc = isset($_POST['ma_thuoc']) ? (int)$_POST['ma_thuoc'] : 0;
        $redirectPage = Auth::isAdmin() ? 'admin_thuoc' : 'nhanvien_thuoc';
        
        // Kiểm tra mã thuốc hợp lệ
        if ($ma_thuoc <= 0) {
            Session::setFlash('error', 'Mã thuốc không hợp lệ');
            header('Location: index.php?page=' . $redirectPage);
            exit;
        }

        // Validate dữ liệu từ form
        $data = $this->validateData($_POST);
        if (!$data) {
            // Nếu validate thất bại, quay lại form với mã thuốc
            header('Location: index.php?page=' . $redirectPage . '&action=admin_thuoc_form&id=' . $ma_thuoc);
            exit;
        }

        // Lấy đường dẫn ảnh cũ từ form để giữ lại khi không upload ảnh mới
        $oldImages = [
            $_POST['old_hinh_anh'] ?? null,     // Ảnh chính cũ
            $_POST['old_hinh_anh_2'] ?? null,    // Ảnh phụ 1 cũ
            $_POST['old_hinh_anh_3'] ?? null,    // Ảnh phụ 2 cũ
            $_POST['old_hinh_anh_4'] ?? null,    // Ảnh phụ 3 cũ
            $_POST['old_hinh_anh_5'] ?? null,    // Ảnh phụ 4 cũ
        ];
        // Xử lý upload ảnh (sẽ giữ ảnh cũ nếu không upload mới)
        $images = $this->processThuocImages($oldImages);
        $data['hinh_anh']   = $images[0] ?? null;
        $data['hinh_anh_2'] = $images[1] ?? null;
        $data['hinh_anh_3'] = $images[2] ?? null;
        $data['hinh_anh_4'] = $images[3] ?? null;
        $data['hinh_anh_5'] = $images[4] ?? null;

        // Tạo slug từ tên thuốc nếu chưa có
        if (empty($data['slug'])) {
            $data['slug'] = $this->thuocModel->taoSlug($data['ten_thuoc']);
        }

        try {
            // Cập nhật thông tin thuốc trong database
            $this->thuocModel->capNhatThuoc($ma_thuoc, $data);
            Session::setFlash('success', 'Cập nhật thuốc thành công!');
            header('Location: index.php?page=' . $redirectPage);
            exit;
        } catch (Exception $e) {
            // Nếu có lỗi, hiển thị thông báo lỗi và quay lại form
            Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
            header('Location: index.php?page=' . $redirectPage . '&action=admin_thuoc_form&id=' . $ma_thuoc);
            exit;
        }
    }

    /**
     * delete() - Xóa một thuốc khỏi database
     *
     * Yêu cầu người dùng phải là nhân viên hoặc admin.
     * Lấy mã thuốc từ query string 'id'.
     */
    public function delete()
    {
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Lấy mã thuốc từ query string
        $ma_thuoc = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $redirectPage = Auth::isAdmin() ? 'admin_thuoc' : 'nhanvien_thuoc';
        
        // Kiểm tra mã thuốc hợp lệ
        if ($ma_thuoc <= 0) {
            Session::setFlash('error', 'Mã thuốc không hợp lệ');
            header('Location: index.php?page=' . $redirectPage);
            exit;
        }

        try {
            // Xóa thuốc khỏi database
            $this->thuocModel->xoaThuoc($ma_thuoc);
            Session::setFlash('success', 'Xóa thuốc thành công!');
        } catch (Exception $e) {
            // Nếu có lỗi, hiển thị thông báo lỗi
            Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
        }

        // Chuyển hướng về trang danh sách thuốc
        header('Location: index.php?page=' . $redirectPage);
        exit;
    }

    /**
     * validateData() - Validate dữ liệu từ form
     *
     * Kiểm tra các trường bắt buộc: danh mục, tên thuốc, giá, số lượng tồn.
     * Nếu có lỗi, lưu vào flash message và trả về false.
     *
     * @param array $post Mảng chứa dữ liệu từ form POST.
     * @return array|false Mảng dữ liệu đã được validate và format, hoặc false nếu có lỗi.
     */
    private function validateData($post)
    {
        $errors = []; // Mảng chứa danh sách lỗi validation

        // Lấy và ép kiểu các trường dữ liệu từ form
        $ma_danh_muc = isset($post['ma_danh_muc']) ? (int)$post['ma_danh_muc'] : 0;
        $ten_thuoc = trim($post['ten_thuoc'] ?? ''); // Loại bỏ khoảng trắng đầu cuối
        $gia = isset($post['gia']) ? (float)$post['gia'] : 0;
        $so_luong_ton = isset($post['so_luong_ton']) ? (int)$post['so_luong_ton'] : 0;

        // Kiểm tra các trường bắt buộc và điều kiện
        if ($ma_danh_muc <= 0) {
            $errors[] = 'Vui lòng chọn danh mục';
        }
        if (empty($ten_thuoc)) {
            $errors[] = 'Vui lòng nhập tên thuốc';
        }
        if ($gia <= 0) {
            $errors[] = 'Giá phải lớn hơn 0';
        }
        if ($so_luong_ton < 0) {
            $errors[] = 'Số lượng tồn không được âm';
        }

        // Nếu có lỗi, lưu vào flash message và trả về false
        if (!empty($errors)) {
            Session::setFlash('errors', $errors);
            return false;
        }

        // Trả về mảng dữ liệu đã được validate và format
        return [
            'ma_danh_muc'     => $ma_danh_muc,
            'ten_thuoc'       => $ten_thuoc,
            'slug'            => $post['slug'] ?? null, // Slug cho URL thân thiện
            'mo_ta'           => $post['mo_ta'] ?? null,
            'huong_dan_dung'  => $post['huong_dan_dung'] ?? null,
            'lieu_dung'       => $post['lieu_dung'] ?? null,
            'chong_chi_dinh'  => $post['chong_chi_dinh'] ?? null,
            'gia'             => $gia,
            'don_vi'          => $post['don_vi'] ?? 'Hộp', // Đơn vị mặc định là 'Hộp'
            'hinh_anh'        => $post['hinh_anh'] ?? null, // Sẽ thay bằng file upload nếu có
            'hinh_anh_2'      => $post['hinh_anh_2'] ?? null,
            'hinh_anh_3'      => $post['hinh_anh_3'] ?? null,
            'hinh_anh_4'      => $post['hinh_anh_4'] ?? null,
            'hinh_anh_5'      => $post['hinh_anh_5'] ?? null,
            'so_luong_ton'    => $so_luong_ton,
            'han_su_dung'     => !empty($post['han_su_dung']) ? $post['han_su_dung'] : null, // Hạn sử dụng (có thể null)
            'trang_thai'      => isset($post['trang_thai']) ? (int)$post['trang_thai'] : 1, // Trạng thái mặc định là 1 (hoạt động)
        ];
    }

    /**
     * processThuocImages() - Upload tối đa 5 ảnh cho thuốc, giữ nguyên ảnh cũ nếu không chọn mới
     *
     * Xử lý upload 5 ảnh (hinh_anh, hinh_anh_2, ..., hinh_anh_5).
     * Nếu không upload ảnh mới, giữ lại đường dẫn ảnh cũ.
     *
     * @param array $oldImages Mảng chứa đường dẫn ảnh cũ (nếu có).
     * @return array Mảng chứa đường dẫn 5 ảnh (có thể là ảnh mới hoặc ảnh cũ).
     */
    private function processThuocImages($oldImages = [])
    {
        // Danh sách các trường ảnh cần xử lý
        $fields = ['hinh_anh', 'hinh_anh_2', 'hinh_anh_3', 'hinh_anh_4', 'hinh_anh_5'];
        $results = []; // Mảng kết quả chứa đường dẫn 5 ảnh
        // Duyệt qua từng trường ảnh và xử lý upload
        foreach ($fields as $index => $field) {
            $old = $oldImages[$index] ?? null; // Lấy đường dẫn ảnh cũ tương ứng (nếu có)
            $results[] = $this->uploadImage($field, 'thuoc', $old); // Upload ảnh (hoặc giữ ảnh cũ)
        }
        return $results; // Trả về mảng chứa đường dẫn 5 ảnh
    }

    /**
     * uploadImage() - Upload 1 ảnh, trả về đường dẫn tương đối hoặc giá trị cũ nếu không upload mới / lỗi
     *
     * Kiểm tra file upload, validate định dạng (JPG, PNG, WEBP, GIF, AVIF) và kích thước (tối đa 5MB),
     * sau đó lưu vào thư mục uploads/{$folder}/ và trả về đường dẫn tương đối.
     * Nếu không có file upload hoặc có lỗi, trả về giá trị cũ.
     *
     * @param string $fieldName Tên trường file trong $_FILES.
     * @param string $folder Tên thư mục lưu ảnh (ví dụ: 'thuoc').
     * @param string|null $oldValue Đường dẫn ảnh cũ (nếu có).
     * @return string|null Đường dẫn ảnh mới hoặc giá trị cũ.
     */
    private function uploadImage($fieldName, $folder, $oldValue = null)
    {
        // Kiểm tra xem có file upload không (nếu không có thì giữ ảnh cũ)
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            return $oldValue; // Trả về đường dẫn ảnh cũ
        }

        $file = $_FILES[$fieldName];
        // Kiểm tra lỗi upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'Tải ảnh thất bại (mã lỗi: ' . $file['error'] . ')');
            return $oldValue; // Trả về đường dẫn ảnh cũ nếu có lỗi
        }

        // Kiểm tra định dạng file (chỉ cho phép các định dạng ảnh)
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE); // Mở fileinfo để kiểm tra MIME type
        $mime  = finfo_file($finfo, $file['tmp_name']); // Lấy MIME type của file
        finfo_close($finfo);
        if (!in_array($mime, $allowed)) {
            Session::setFlash('error', 'Ảnh không hợp lệ (chỉ cho phép JPG, PNG, WEBP, GIF)');
            return $oldValue; // Trả về đường dẫn ảnh cũ nếu định dạng không hợp lệ
        }
        // Kiểm tra kích thước file (tối đa 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            Session::setFlash('error', 'Ảnh vượt quá 5MB');
            return $oldValue; // Trả về đường dẫn ảnh cũ nếu vượt quá kích thước
        }

        // Lấy phần mở rộng của file (extension)
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) ?: 'jpg';
        // Tạo đường dẫn thư mục upload (uploads/{$folder}/)
        $uploadDir = dirname(__DIR__, 2) . '/uploads/' . $folder;
        // Tạo thư mục nếu chưa tồn tại
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Tạo thư mục với quyền đọc/ghi/thực thi
        }

        // Tạo tên file duy nhất bằng uniqid (tránh trùng tên)
        $fileName = uniqid($folder . '_', true) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $fileName; // Đường dẫn đầy đủ đến file đích

        // Di chuyển file từ thư mục tạm sang thư mục upload
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            Session::setFlash('error', 'Không thể lưu file ảnh');
            return $oldValue; // Trả về đường dẫn ảnh cũ nếu không thể lưu file
        }

        // Trả về đường dẫn tương đối của ảnh đã upload
        return 'uploads/' . $folder . '/' . $fileName;
    }

    /**
     * xuatExcelThuocHetHan() - Xuất Excel danh sách thuốc hết hạn
     *
     * Tạo file Excel chứa danh sách thuốc đã hết hạn, bao gồm thông tin chi tiết
     * và tổng chi phí. File được tạo dưới dạng HTML table với định dạng Excel.
     * Yêu cầu người dùng phải là nhân viên hoặc admin.
     */
    public function xuatExcelThuocHetHan()
    {
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Lấy danh sách thuốc đã hết hạn và tổng chi phí
        $thuocList = $this->thuocModel->layThuocHetHan();
        $tongChiPhi = $this->thuocModel->tinhTongChiPhiThuocHetHan();
        
        // Tạo tên file Excel với timestamp
        $fileName = 'Thuoc_Het_Han_' . date('Y-m-d_His') . '.xls';
        
        // Thiết lập header để trình duyệt hiểu đây là file Excel
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        // BOM UTF-8 để Excel hiển thị đúng tiếng Việt
        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1">';
        
        // Tiêu đề bảng
        echo '<tr><th colspan="8" style="background-color: #dc3545; color: white; font-weight: bold; padding: 15px; font-size: 16px;">DANH SÁCH THUỐC ĐÃ HẾT HẠN</th></tr>';
        // Header các cột
        echo '<tr><th style="background-color: #f0f0f0; font-weight: bold; padding: 10px;">STT</th>';
        echo '<th style="background-color: #f0f0f0; font-weight: bold; padding: 10px;">Tên thuốc</th>';
        echo '<th style="background-color: #f0f0f0; font-weight: bold; padding: 10px;">Danh mục</th>';
        echo '<th style="background-color: #f0f0f0; font-weight: bold; padding: 10px;">Số lượng tồn</th>';
        echo '<th style="background-color: #f0f0f0; font-weight: bold; padding: 10px;">Đơn vị</th>';
        echo '<th style="background-color: #f0f0f0; font-weight: bold; padding: 10px;">Giá (₫)</th>';
        echo '<th style="background-color: #f0f0f0; font-weight: bold; padding: 10px;">Hạn sử dụng</th>';
        echo '<th style="background-color: #f0f0f0; font-weight: bold; padding: 10px;">Tổng chi phí (₫)</th></tr>';
        
        // Duyệt qua danh sách thuốc và hiển thị từng dòng
        $stt = 1; // Số thứ tự
        $tongSoLuong = 0; // Tổng số lượng tồn
        foreach ($thuocList as $thuoc) {
            $tongChiPhiItem = $thuoc['gia'] * $thuoc['so_luong_ton']; // Tính tổng chi phí cho từng thuốc
            $tongSoLuong += $thuoc['so_luong_ton']; // Cộng dồn tổng số lượng
            
            echo '<tr>';
            echo '<td style="text-align: center; padding: 8px;">' . $stt++ . '</td>';
            echo '<td style="padding: 8px;">' . htmlspecialchars($thuoc['ten_thuoc']) . '</td>'; // Escape HTML để tránh XSS
            echo '<td style="padding: 8px;">' . htmlspecialchars($thuoc['ten_danh_muc'] ?? 'N/A') . '</td>';
            echo '<td style="text-align: right; padding: 8px;">' . number_format($thuoc['so_luong_ton'], 0, ',', '.') . '</td>'; // Format số với dấu phẩy
            echo '<td style="padding: 8px;">' . htmlspecialchars($thuoc['don_vi']) . '</td>';
            echo '<td style="text-align: right; padding: 8px;">' . number_format($thuoc['gia'], 0, ',', '.') . '</td>';
            echo '<td style="padding: 8px;">' . date('d/m/Y', strtotime($thuoc['han_su_dung'])) . '</td>'; // Format ngày tháng
            echo '<td style="text-align: right; padding: 8px; font-weight: bold;">' . number_format($tongChiPhiItem, 0, ',', '.') . '</td>';
            echo '</tr>';
        }
        
        // Dòng tổng cộng
        echo '<tr style="background-color: #ffffcc; font-weight: bold;">';
        echo '<td colspan="3" style="padding: 10px;">TỔNG CỘNG</td>';
        echo '<td style="text-align: right; padding: 10px;">' . number_format($tongSoLuong, 0, ',', '.') . '</td>';
        echo '<td colspan="3"></td>';
        echo '<td style="text-align: right; padding: 10px; font-size: 14px; color: #dc3545;">' . number_format($tongChiPhi, 0, ',', '.') . ' ₫</td>';
        echo '</tr>';
        
        // Thông tin người xuất và ngày xuất
        echo '<tr><td colspan="8" style="height: 20px;"></td></tr>';
        echo '<tr><td colspan="8" style="font-style: italic; color: #666; padding: 10px;">Ngày xuất: ' . date('d/m/Y H:i:s') . '</td></tr>';
        echo '<tr><td colspan="8" style="font-style: italic; color: #666; padding: 10px;">Người xuất: ' . htmlspecialchars(Auth::user()['name']) . ' (' . htmlspecialchars(Auth::user()['role']) . ')</td></tr>';
        
        echo '</table></body></html>';
        exit; // Dừng script sau khi xuất Excel
    }

    /**
     * xuatExcelThuocSapHetHan() - Xuất Excel danh sách thuốc sắp hết hạn
     *
     * Tạo file Excel chứa danh sách thuốc sắp hết hạn (trong 30 ngày tới), bao gồm thông tin chi tiết,
     * số ngày còn lại và tổng chi phí. File được tạo dưới dạng HTML table với định dạng Excel.
     * Yêu cầu người dùng phải là nhân viên hoặc admin.
     */
    public function xuatExcelThuocSapHetHan()
    {
        Auth::requireNhanVien(); // Yêu cầu người dùng phải là nhân viên hoặc admin
        
        // Lấy danh sách thuốc sắp hết hạn (trong 30 ngày tới) và tổng chi phí
        $thuocList = $this->thuocModel->layThuocSapHetHan();
        $tongChiPhi = $this->thuocModel->tinhTongChiPhiThuocSapHetHan();
        
        // Tạo tên file Excel với timestamp
        $fileName = 'Thuoc_Sap_Het_Han_' . date('Y-m-d_His') . '.xls';
        
        // Thiết lập header để trình duyệt hiểu đây là file Excel
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        // BOM UTF-8 để Excel hiển thị đúng tiếng Việt
        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1">';
        
        // Tiêu đề bảng
        echo '<tr><th colspan="9" style="background-color: #ffc107; color: #000; font-weight: bold; padding: 15px; font-size: 16px;">DANH SÁCH THUỐC SẮP HẾT HẠN (TRONG 30 NGÀY TỚI)</th></tr>';
        // Header các cột (có thêm cột "Còn lại (ngày)" so với danh sách hết hạn)
        echo '<tr><th style="background-color: #f0f0f0; font-weight: bold; padding: 10px;">STT</th>';
        echo '<th style="background-color: #f0f0f0; font-weight: bold; padding: 10px;">Tên thuốc</th>';
        echo '<th style="background-color: #f0f0f0; font-weight: bold; padding: 10px;">Danh mục</th>';
        echo '<th style="background-color: #f0f0f0; font-weight: bold; padding: 10px;">Số lượng tồn</th>';
        echo '<th style="background-color: #f0f0f0; font-weight: bold; padding: 10px;">Đơn vị</th>';
        echo '<th style="background-color: #f0f0f0; font-weight: bold; padding: 10px;">Giá (₫)</th>';
        echo '<th style="background-color: #f0f0f0; font-weight: bold; padding: 10px;">Hạn sử dụng</th>';
        echo '<th style="background-color: #f0f0f0; font-weight: bold; padding: 10px;">Còn lại (ngày)</th>';
        echo '<th style="background-color: #f0f0f0; font-weight: bold; padding: 10px;">Tổng chi phí (₫)</th></tr>';
        
        // Duyệt qua danh sách thuốc và hiển thị từng dòng
        $stt = 1; // Số thứ tự
        $tongSoLuong = 0; // Tổng số lượng tồn
        foreach ($thuocList as $thuoc) {
            $tongChiPhiItem = $thuoc['gia'] * $thuoc['so_luong_ton']; // Tính tổng chi phí cho từng thuốc
            $tongSoLuong += $thuoc['so_luong_ton']; // Cộng dồn tổng số lượng
            
            echo '<tr>';
            echo '<td style="text-align: center; padding: 8px;">' . $stt++ . '</td>';
            echo '<td style="padding: 8px;">' . htmlspecialchars($thuoc['ten_thuoc']) . '</td>'; // Escape HTML để tránh XSS
            echo '<td style="padding: 8px;">' . htmlspecialchars($thuoc['ten_danh_muc'] ?? 'N/A') . '</td>';
            echo '<td style="text-align: right; padding: 8px;">' . number_format($thuoc['so_luong_ton'], 0, ',', '.') . '</td>'; // Format số với dấu phẩy
            echo '<td style="padding: 8px;">' . htmlspecialchars($thuoc['don_vi']) . '</td>';
            echo '<td style="text-align: right; padding: 8px;">' . number_format($thuoc['gia'], 0, ',', '.') . '</td>';
            echo '<td style="padding: 8px;">' . date('d/m/Y', strtotime($thuoc['han_su_dung'])) . '</td>'; // Format ngày tháng
            echo '<td style="text-align: center; padding: 8px; color: #ff9800; font-weight: bold;">' . $thuoc['so_ngay_con_lai'] . '</td>'; // Số ngày còn lại (màu cam để cảnh báo)
            echo '<td style="text-align: right; padding: 8px; font-weight: bold;">' . number_format($tongChiPhiItem, 0, ',', '.') . '</td>';
            echo '</tr>';
        }
        
        // Dòng tổng cộng
        echo '<tr style="background-color: #ffffcc; font-weight: bold;">';
        echo '<td colspan="3" style="padding: 10px;">TỔNG CỘNG</td>';
        echo '<td style="text-align: right; padding: 10px;">' . number_format($tongSoLuong, 0, ',', '.') . '</td>';
        echo '<td colspan="4"></td>';
        echo '<td style="text-align: right; padding: 10px; font-size: 14px; color: #ff9800;">' . number_format($tongChiPhi, 0, ',', '.') . ' ₫</td>';
        echo '</tr>';
        
        // Thông tin người xuất và ngày xuất
        echo '<tr><td colspan="9" style="height: 20px;"></td></tr>';
        echo '<tr><td colspan="9" style="font-style: italic; color: #666; padding: 10px;">Ngày xuất: ' . date('d/m/Y H:i:s') . '</td></tr>';
        echo '<tr><td colspan="9" style="font-style: italic; color: #666; padding: 10px;">Người xuất: ' . htmlspecialchars(Auth::user()['name']) . ' (' . htmlspecialchars(Auth::user()['role']) . ')</td></tr>';
        
        echo '</table></body></html>';
        exit; // Dừng script sau khi xuất Excel
    }
}

