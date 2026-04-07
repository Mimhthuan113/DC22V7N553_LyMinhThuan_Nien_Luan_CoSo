<?php
require_once __DIR__ . '/../models/TinTucModel.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Session.php';

class AdminTinTucController
{
    private $tinTucModel;

    public function __construct()
    {
        $this->tinTucModel = new TinTucModel();
    }

    // Hiển thị danh sách tin tức
    public function index()
    {
        Auth::requireNhanVien();
        
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $search = $_GET['search'] ?? '';
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $tinTucList = $this->tinTucModel->layTatCa($limit, $offset, $search);
        $total = $this->tinTucModel->demTongSo($search);
        $totalPages = ceil($total / $limit);

        // Load view tùy theo vai trò
        $isAdmin = Auth::isAdmin();
        $viewPath = $isAdmin ? '/../views/admin/tintuc.php' : '/../views/nhanvien/tintuc.php';
        require __DIR__ . $viewPath;
    }

    // Hiển thị form thêm/sửa tin tức
    public function showForm()
    {
        Auth::requireNhanVien();
        
        $ma_tin_tuc = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $tinTuc = null;

        if ($ma_tin_tuc > 0) {
            $tinTuc = $this->tinTucModel->layTheoMa($ma_tin_tuc);
            if (!$tinTuc) {
                Session::setFlash('error', 'Không tìm thấy tin tức');
                $redirectPage = Auth::isAdmin() ? 'admin_tintuc' : 'nhanvien_tintuc';
                header('Location: index.php?page=' . $redirectPage);
                exit;
            }
        }

        require __DIR__ . '/../views/admin/tintuc_form.php';
        exit;
    }

    // Tạo tin tức mới
    public function create()
    {
        Auth::requireNhanVien();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_tintuc');
            exit;
        }

        $data = [
            'tieu_de' => trim($_POST['tieu_de'] ?? ''),
            'tom_tat' => trim($_POST['tom_tat'] ?? ''),
            'noi_dung' => trim($_POST['noi_dung'] ?? ''),
            'tac_gia' => trim($_POST['tac_gia'] ?? ''),
            'trang_thai' => isset($_POST['trang_thai']) ? (int)$_POST['trang_thai'] : 1
        ];

        if (empty($data['tieu_de']) || empty($data['noi_dung'])) {
            Session::setFlash('error', 'Tiêu đề và nội dung không được để trống');
            header('Location: index.php?action=admin_tintuc_form');
            exit;
        }

        // Upload tối đa 5 ảnh
        $images = $this->processTinTucImages();
        $data['hinh_anh']   = $images[0] ?? null;
        $data['hinh_anh_2'] = $images[1] ?? null;
        $data['hinh_anh_3'] = $images[2] ?? null;
        $data['hinh_anh_4'] = $images[3] ?? null;
        $data['hinh_anh_5'] = $images[4] ?? null;

        $this->tinTucModel->themTinTuc($data);
        Session::setFlash('success', 'Đã thêm tin tức thành công');
        
        $redirectPage = Auth::isAdmin() ? 'admin_tintuc' : 'nhanvien_tintuc';
        header('Location: index.php?page=' . $redirectPage);
        exit;
    }

    // Cập nhật tin tức
    public function update()
    {
        Auth::requireNhanVien();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_tintuc');
            exit;
        }

        $ma_tin_tuc = isset($_POST['ma_tin_tuc']) ? (int)$_POST['ma_tin_tuc'] : 0;
        if ($ma_tin_tuc <= 0) {
            Session::setFlash('error', 'Mã tin tức không hợp lệ');
            header('Location: index.php?page=admin_tintuc');
            exit;
        }

        $data = [
            'tieu_de' => trim($_POST['tieu_de'] ?? ''),
            'tom_tat' => trim($_POST['tom_tat'] ?? ''),
            'noi_dung' => trim($_POST['noi_dung'] ?? ''),
            'tac_gia' => trim($_POST['tac_gia'] ?? ''),
            'trang_thai' => isset($_POST['trang_thai']) ? (int)$_POST['trang_thai'] : 1
        ];

        if (empty($data['tieu_de']) || empty($data['noi_dung'])) {
            Session::setFlash('error', 'Tiêu đề và nội dung không được để trống');
            header('Location: index.php?action=admin_tintuc_form&id=' . $ma_tin_tuc);
            exit;
        }

        // Giữ lại ảnh cũ nếu không upload mới
        $oldImages = [
            $_POST['old_hinh_anh'] ?? null,
            $_POST['old_hinh_anh_2'] ?? null,
            $_POST['old_hinh_anh_3'] ?? null,
            $_POST['old_hinh_anh_4'] ?? null,
            $_POST['old_hinh_anh_5'] ?? null,
        ];
        $images = $this->processTinTucImages($oldImages);
        $data['hinh_anh']   = $images[0] ?? null;
        $data['hinh_anh_2'] = $images[1] ?? null;
        $data['hinh_anh_3'] = $images[2] ?? null;
        $data['hinh_anh_4'] = $images[3] ?? null;
        $data['hinh_anh_5'] = $images[4] ?? null;

        $this->tinTucModel->capNhatTinTuc($ma_tin_tuc, $data);
        Session::setFlash('success', 'Đã cập nhật tin tức thành công');
        
        $redirectPage = Auth::isAdmin() ? 'admin_tintuc' : 'nhanvien_tintuc';
        header('Location: index.php?page=' . $redirectPage);
        exit;
    }

    // Xóa tin tức
    public function delete()
    {
        Auth::requireNhanVien();
        
        $ma_tin_tuc = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($ma_tin_tuc <= 0) {
            Session::setFlash('error', 'Mã tin tức không hợp lệ');
        } else {
            $this->tinTucModel->xoaTinTuc($ma_tin_tuc);
            Session::setFlash('success', 'Đã xóa tin tức thành công');
        }
        
        $redirectPage = Auth::isAdmin() ? 'admin_tintuc' : 'nhanvien_tintuc';
        header('Location: index.php?page=' . $redirectPage);
        exit;
    }

    /**
     * Upload tối đa 5 ảnh cho tin tức, giữ ảnh cũ nếu không chọn mới.
     */
    private function processTinTucImages($oldImages = [])
    {
        $fields = ['hinh_anh', 'hinh_anh_2', 'hinh_anh_3', 'hinh_anh_4', 'hinh_anh_5'];
        $results = [];
        foreach ($fields as $index => $field) {
            $old = $oldImages[$index] ?? null;
            $results[] = $this->uploadImage($field, 'tintuc', $old);
        }
        return $results;
    }

    /**
     * Upload 1 ảnh, trả về đường dẫn tương đối hoặc giá trị cũ nếu không upload.
     */
    private function uploadImage($fieldName, $folder, $oldValue = null)
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            return $oldValue;
        }

        $file = $_FILES[$fieldName];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'Tải ảnh thất bại (mã lỗi: ' . $file['error'] . ')');
            return $oldValue;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed)) {
            Session::setFlash('error', 'Ảnh không hợp lệ (chỉ JPG, PNG, WEBP, GIF)');
            return $oldValue;
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            Session::setFlash('error', 'Ảnh vượt quá 5MB');
            return $oldValue;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) ?: 'jpg';
        $uploadDir = dirname(__DIR__, 2) . '/uploads/' . $folder;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid($folder . '_', true) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            Session::setFlash('error', 'Không thể lưu file ảnh');
            return $oldValue;
        }

        return 'uploads/' . $folder . '/' . $fileName;
    }
}

