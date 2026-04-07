<?php
require_once __DIR__ . '/../models/NguoiDungModel.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Session.php';

class AccountController
{
    private $model;

    public function __construct()
    {
        $this->model = new NguoiDungModel();
    }

    // Trang thông tin tài khoản
    public function show()
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: index.php?page=login');
            exit;
        }
        $detail = $this->model->layTheoMa($user['id']);
        require __DIR__ . '/../views/account/index.php';
    }

    // Cập nhật thông tin cá nhân
    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=account');
            exit;
        }

        $user = Auth::user();
        if (!$user) {
            header('Location: index.php?page=login');
            exit;
        }

        $ho_ten = trim($_POST['ho_ten'] ?? '');
        $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
        $dia_chi = trim($_POST['dia_chi'] ?? '');

        $errors = [];
        if (empty($ho_ten) || strlen($ho_ten) < 2) {
            $errors[] = 'Họ tên phải có ít nhất 2 ký tự';
        }
        if (!empty($so_dien_thoai) && !preg_match('/^[0-9]{10,11}$/', $so_dien_thoai)) {
            $errors[] = 'Số điện thoại không hợp lệ (10-11 chữ số)';
        }
        if (!empty($dia_chi) && strlen($dia_chi) > 255) {
            $errors[] = 'Địa chỉ không được vượt quá 255 ký tự';
        }

        if (!empty($errors)) {
            Session::setFlash('errors', $errors);
            Session::setFlash('form_profile', [
                'ho_ten' => $ho_ten,
                'so_dien_thoai' => $so_dien_thoai,
                'dia_chi' => $dia_chi
            ]);
            header('Location: index.php?page=account');
            exit;
        }

        try {
            $this->model->capNhatThongTinCaNhan($user['id'], [
                'ho_ten' => $ho_ten,
                'so_dien_thoai' => $so_dien_thoai ?: null,
                'dia_chi' => $dia_chi ?: null
            ]);
            // Cập nhật lại session tên hiển thị
            Session::set('user_name', $ho_ten);
            Session::setFlash('success_profile', 'Cập nhật thông tin thành công');
        } catch (Exception $e) {
            Session::setFlash('error_profile', 'Có lỗi xảy ra: ' . $e->getMessage());
        }

        header('Location: index.php?page=account');
        exit;
    }

    // Đổi mật khẩu
    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=account');
            exit;
        }

        $user = Auth::user();
        if (!$user) {
            header('Location: index.php?page=login');
            exit;
        }

        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $errors = [];
        if (empty($current_password)) {
            $errors[] = 'Vui lòng nhập mật khẩu hiện tại';
        }
        if (empty($new_password) || strlen($new_password) < 6) {
            $errors[] = 'Mật khẩu mới phải có ít nhất 6 ký tự';
        }
        if ($new_password !== $confirm_password) {
            $errors[] = 'Mật khẩu xác nhận không khớp';
        }

        $detail = $this->model->layTheoMa($user['id']);
        if (!$detail) {
            $errors[] = 'Không tìm thấy tài khoản';
        } elseif (!password_verify($current_password, $detail['mat_khau'])) {
            $errors[] = 'Mật khẩu hiện tại không đúng';
        }

        if (!empty($errors)) {
            Session::setFlash('errors_password', $errors);
            header('Location: index.php?page=account');
            exit;
        }

        try {
            $this->model->doiMatKhau($user['id'], $new_password);
            Session::setFlash('success_password', 'Đổi mật khẩu thành công');
        } catch (Exception $e) {
            Session::setFlash('error_password', 'Có lỗi xảy ra: ' . $e->getMessage());
        }

        header('Location: index.php?page=account');
        exit;
    }
}

