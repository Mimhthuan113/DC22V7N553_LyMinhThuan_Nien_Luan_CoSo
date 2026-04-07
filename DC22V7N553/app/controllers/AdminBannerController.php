<?php
require_once __DIR__ . '/../models/BannerModel.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/DanhMucModel.php';

class AdminBannerController
{
    private $bannerModel;
    private $danhMucModel;

    public function __construct()
    {
        $this->bannerModel = new BannerModel();
        $this->danhMucModel = new DanhMucModel();
    }

    public function index()
    {
        Auth::requireNhanVien();
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        $search = $_GET['search'] ?? '';
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $list = $this->bannerModel->layTatCa($limit, $offset, $search);
        $total = count($this->bannerModel->layTatCa(null, 0, $search)); // simple count
        $totalPages = ceil($total / $limit);
        $danhMucMap = []; // không dùng danh mục nữa

        $isAdmin = Auth::isAdmin();
        $viewPath = $isAdmin ? '/../views/admin/banner.php' : '/../views/nhanvien/banner.php';
        require __DIR__ . $viewPath;
    }

    public function showForm()
    {
        Auth::requireNhanVien();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $banner = null;
        if ($id > 0) {
            $banner = $this->bannerModel->layTheoMa($id);
            if (!$banner) {
                Session::setFlash('error', 'Không tìm thấy banner');
                $redirectPage = Auth::isAdmin() ? 'admin_banner' : 'nhanvien_banner';
                header('Location: index.php?page=' . $redirectPage);
                exit;
            }
        }
        require __DIR__ . '/../views/admin/banner_form.php';
        exit;
    }

    public function create()
    {
        Auth::requireNhanVien();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_banner');
            exit;
        }

        $data = $this->validateData($_POST);
        if (!$data) {
            header('Location: index.php?action=admin_banner_form');
            exit;
        }

        // upload tối đa 5 ảnh
        $images = $this->processImages();
        if (empty($images[0])) {
            Session::setFlash('error', 'Vui lòng chọn ít nhất 1 ảnh banner');
            header('Location: index.php?action=admin_banner_form');
            exit;
        }
        $data['hinh_anh']   = $images[0] ?? null;
        $data['hinh_anh_2'] = $images[1] ?? null;
        $data['hinh_anh_3'] = $images[2] ?? null;
        $data['hinh_anh_4'] = $images[3] ?? null;
        $data['hinh_anh_5'] = $images[4] ?? null;

        $this->bannerModel->them($data);
        Session::setFlash('success', 'Đã thêm banner');
        $redirectPage = Auth::isAdmin() ? 'admin_banner' : 'nhanvien_banner';
        header('Location: index.php?page=' . $redirectPage);
        exit;
    }

    public function update()
    {
        Auth::requireNhanVien();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_banner');
            exit;
        }
        $id = isset($_POST['ma_banner']) ? (int)$_POST['ma_banner'] : 0;
        if ($id <= 0) {
            Session::setFlash('error', 'ID banner không hợp lệ');
            header('Location: index.php?page=admin_banner');
            exit;
        }

        $data = $this->validateData($_POST);
        if (!$data) {
            header('Location: index.php?action=admin_banner_form&id=' . $id);
            exit;
        }

        $oldImages = [
            $_POST['old_hinh_anh'] ?? null,
            $_POST['old_hinh_anh_2'] ?? null,
            $_POST['old_hinh_anh_3'] ?? null,
            $_POST['old_hinh_anh_4'] ?? null,
            $_POST['old_hinh_anh_5'] ?? null,
        ];
        $images = $this->processImages($oldImages);
        if (empty($images[0])) {
            Session::setFlash('error', 'Vui lòng giữ hoặc tải lên ít nhất 1 ảnh');
            header('Location: index.php?action=admin_banner_form&id=' . $id);
            exit;
        }
        $data['hinh_anh']   = $images[0] ?? null;
        $data['hinh_anh_2'] = $images[1] ?? null;
        $data['hinh_anh_3'] = $images[2] ?? null;
        $data['hinh_anh_4'] = $images[3] ?? null;
        $data['hinh_anh_5'] = $images[4] ?? null;

        $this->bannerModel->capNhat($id, $data);
        Session::setFlash('success', 'Đã cập nhật banner');
        $redirectPage = Auth::isAdmin() ? 'admin_banner' : 'nhanvien_banner';
        header('Location: index.php?page=' . $redirectPage);
        exit;
    }

    public function delete()
    {
        Auth::requireNhanVien();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            Session::setFlash('error', 'ID banner không hợp lệ');
        } else {
            $this->bannerModel->xoa($id);
            Session::setFlash('success', 'Đã xóa banner');
        }
        $redirectPage = Auth::isAdmin() ? 'admin_banner' : 'nhanvien_banner';
        header('Location: index.php?page=' . $redirectPage);
        exit;
    }

    private function validateData($post)
    {
        $errors = [];
        $tieu_de = trim($post['tieu_de'] ?? '');
        $thu_tu = isset($post['thu_tu']) ? (int)$post['thu_tu'] : 0;
        $trang_thai = isset($post['trang_thai']) ? (int)$post['trang_thai'] : 1;

        // hinh_anh bắt buộc sẽ kiểm tra ở upload

        if (!empty($errors)) {
            Session::setFlash('errors', $errors);
            return false;
        }

        return [
            'tieu_de' => $tieu_de ?: null,
            'thu_tu' => $thu_tu,
            'trang_thai' => $trang_thai,
        ];
    }

    private function processImages($oldImages = [])
    {
        $fields = ['hinh_anh', 'hinh_anh_2', 'hinh_anh_3', 'hinh_anh_4', 'hinh_anh_5'];
        $results = [];
        foreach ($fields as $idx => $field) {
            $old = $oldImages[$idx] ?? null;
            $results[] = $this->uploadImage($field, 'banner', $old);
        }
        return $results;
    }

    private function uploadImage($fieldName, $folder, $oldValue = null)
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            return $oldValue ?: null;
        }

        $file = $_FILES[$fieldName];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'Upload ảnh lỗi (mã ' . $file['error'] . ')');
            return $oldValue;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed)) {
            Session::setFlash('error', 'Ảnh không hợp lệ (JPG, PNG, WEBP, GIF, AVIF)');
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


