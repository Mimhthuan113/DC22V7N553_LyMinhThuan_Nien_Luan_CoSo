<?php
require_once __DIR__ . '/../models/NguoiDungModel.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Session.php';

class AdminUserController
{
    private $model;

    public function __construct()
    {
        $this->model = new NguoiDungModel();
    }

    // Trang danh sách người dùng
    public function index()
    {
        $users = $this->model->layTatCa();
        $roles = $this->model->layTatCaVaiTro();
        require __DIR__ . '/../views/admin/users.php';
    }

    // Tạo tài khoản (mặc định dùng cho nhân viên hoặc khách hàng)
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_users');
            exit;
        }

        $ho_ten = trim($_POST['ho_ten'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
        $dia_chi = trim($_POST['dia_chi'] ?? '');
        $ma_vai_tro = (int)($_POST['ma_vai_tro'] ?? 0);

        $errors = [];

        if (empty($ho_ten) || strlen($ho_ten) < 2) {
            $errors[] = 'Họ tên phải có ít nhất 2 ký tự';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        }

        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
        }

        if ($ma_vai_tro <= 0) {
            $errors[] = 'Vui lòng chọn vai trò';
        }

        if (!empty($so_dien_thoai) && !preg_match('/^[0-9]{10,11}$/', $so_dien_thoai)) {
            $errors[] = 'Số điện thoại không hợp lệ (10-11 chữ số)';
        }

        if (!empty($dia_chi) && strlen($dia_chi) > 255) {
            $errors[] = 'Địa chỉ không được vượt quá 255 ký tự';
        }

        if (!empty($errors)) {
            Session::setFlash('errors', $errors);
            Session::setFlash('form_data', [
                'ho_ten' => $ho_ten,
                'email' => $email,
                'so_dien_thoai' => $so_dien_thoai,
                'dia_chi' => $dia_chi,
                'ma_vai_tro' => $ma_vai_tro
            ]);
            header('Location: index.php?page=admin_users');
            exit;
        }

        try {
            $this->model->taoNguoiDungAdmin([
                'ho_ten' => $ho_ten,
                'email' => $email,
                'mat_khau' => $password,
                'so_dien_thoai' => $so_dien_thoai ?: null,
                'dia_chi' => $dia_chi ?: null,
                'ma_vai_tro' => $ma_vai_tro
            ]);
            Session::setFlash('success', 'Đã tạo tài khoản thành công');
        } catch (Exception $e) {
            Session::setFlash('error', $e->getMessage());
            Session::setFlash('form_data', [
                'ho_ten' => $ho_ten,
                'email' => $email,
                'so_dien_thoai' => $so_dien_thoai,
                'dia_chi' => $dia_chi,
                'ma_vai_tro' => $ma_vai_tro
            ]);
        }

        header('Location: index.php?page=admin_users');
        exit;
    }

    // Cập nhật vai trò người dùng
    public function updateRole()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_users');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $ma_vai_tro = (int)($_POST['ma_vai_tro'] ?? 0);
        $currentUser = Auth::user();

        if ($id === 0 || $ma_vai_tro === 0) {
            Session::setFlash('error', 'Thiếu thông tin cập nhật vai trò');
            header('Location: index.php?page=admin_users');
            exit;
        }

        // Không cho tự hạ quyền chính mình
        if ($currentUser && $currentUser['id'] == $id && $ma_vai_tro != $this->getRoleId('QUAN_TRI')) {
            Session::setFlash('error', 'Bạn không thể thay đổi vai trò của chính mình');
            header('Location: index.php?page=admin_users');
            exit;
        }

        $this->model->capNhatVaiTro($id, $ma_vai_tro);
        Session::setFlash('success', 'Đã cập nhật vai trò');
        header('Location: index.php?page=admin_users');
        exit;
    }

    // Khóa/mở khóa người dùng
    public function toggleStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_users');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $trang_thai = (int)($_POST['trang_thai'] ?? 1);
        $currentUser = Auth::user();

        if ($id === 0) {
            Session::setFlash('error', 'Thiếu thông tin người dùng');
            header('Location: index.php?page=admin_users');
            exit;
        }

        $user = $this->model->layTheoMa($id);
        if (!$user) {
            Session::setFlash('error', 'Không tìm thấy người dùng');
            header('Location: index.php?page=admin_users');
            exit;
        }

        // Không cho khóa admin QUAN_TRI
        if ($user['ten_vai_tro'] === 'QUAN_TRI' && $trang_thai == 0) {
            Session::setFlash('error', 'Không thể khóa tài khoản quản trị');
            header('Location: index.php?page=admin_users');
            exit;
        }

        // Không cho khóa chính mình
        if ($currentUser && $currentUser['id'] == $id) {
            Session::setFlash('error', 'Không thể khóa tài khoản của chính bạn');
            header('Location: index.php?page=admin_users');
            exit;
        }

        $this->model->capNhatTrangThai($id, $trang_thai);
        Session::setFlash('success', 'Đã cập nhật trạng thái');
        header('Location: index.php?page=admin_users');
        exit;
    }

    // Xóa người dùng
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_users');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $currentUser = Auth::user();

        if ($id === 0) {
            Session::setFlash('error', 'Thiếu thông tin người dùng');
            header('Location: index.php?page=admin_users');
            exit;
        }

        $user = $this->model->layTheoMa($id);
        if (!$user) {
            Session::setFlash('error', 'Không tìm thấy người dùng');
            header('Location: index.php?page=admin_users');
            exit;
        }

        if ($user['ten_vai_tro'] === 'QUAN_TRI') {
            Session::setFlash('error', 'Không thể xóa tài khoản quản trị');
            header('Location: index.php?page=admin_users');
            exit;
        }

        // Không cho tự xóa chính mình
        if ($currentUser && $currentUser['id'] == $id) {
            Session::setFlash('error', 'Không thể xóa tài khoản của chính bạn');
            header('Location: index.php?page=admin_users');
            exit;
        }

        $this->model->xoaNguoiDung($id);
        Session::setFlash('success', 'Đã xóa người dùng');
        header('Location: index.php?page=admin_users');
        exit;
    }

    // Lấy id vai trò theo tên
    private function getRoleId($ten_vai_tro)
    {
        return $this->model->layMaVaiTroTheoTen($ten_vai_tro);
    }
}

