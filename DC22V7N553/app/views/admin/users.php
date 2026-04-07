<?php
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/Auth.php';
$user = Auth::user();
$form_data = Session::getFlash('form_data');
$errors = Session::getFlash('errors');
$error = Session::getFlash('error');
$success = Session::getFlash('success');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý người dùng - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; }
        .sidebar { background: #023660; min-height: 100vh; color: #fff; padding: 20px 0; position: fixed; width: 200px; transition: width 0.25s; overflow: hidden; }
        .sidebar h4 { padding: 0 20px; font-size: 1.2rem; }
        .sidebar a { color: #fff; text-decoration: none; padding: 12px 20px; display: block; transition: all 0.3s; }
        .sidebar a:hover { background: #1956b2; padding-left: 25px; }
        .sidebar a.active { background: #1956b2; border-left: 4px solid #fff; }
        .sidebar hr { margin: 15px 0; }
        .sidebar.collapsed { width: 64px; }
        .sidebar.collapsed h4 { display: none; }
        .sidebar.collapsed a { padding-left: 18px; }
        .sidebar.collapsed a span.label { display: none; }
        .main-content { padding: 24px; margin-left: 200px; transition: margin-left 0.25s; max-width: calc(100% - 200px); }
        .main-content.expanded { margin-left: 64px; max-width: calc(100% - 64px); }
        .card { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .table thead th { background: #f1f3f5; }
        .badge { font-size: 0.75rem; }
        .toolbar { gap: 10px; flex-wrap: wrap; }
        .content-wrapper { max-width: 1300px; margin: 0 auto; }
        .table-responsive { max-height: 70vh; }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="sidebar" id="sidebar">
                <h4 class="mb-4"><i class="bi bi-shield-check"></i> Admin Panel</h4>
                <a href="index.php?page=admin"><i class="bi bi-speedometer2"></i> <span class="label">Dashboard</span></a>
                <a href="index.php?page=admin_users" class="active"><i class="bi bi-people"></i> <span class="label">Quản lý người dùng</span></a>
                <a href="index.php?page=admin_thuoc"><i class="bi bi-capsule"></i> <span class="label">Quản lý thuốc</span></a>
                <a href="index.php?page=admin_donhang"><i class="bi bi-cart-check"></i> <span class="label">Quản lý đơn hàng</span></a>
                <a href="index.php?page=admin_danhmuc"><i class="bi bi-folder"></i> <span class="label">Quản lý danh mục</span></a>
                <a href="index.php?page=admin_tintuc"><i class="bi bi-newspaper"></i> <span class="label">Quản lý tin tức</span></a>
                <a href="index.php?page=admin_banner"><i class="bi bi-image"></i> <span class="label">Quản lý banner</span></a>
                <a href="index.php?page=admin_sale"><i class="bi bi-tag-fill"></i> <span class="label">Quản lý Sale</span></a>
                <hr style="border-color: #1956b2;">
                <a href="index.php?page=trangchu"><i class="bi bi-house"></i> <span class="label">Về trang chủ</span></a>
                <a href="index.php?action=auth_logout"><i class="bi bi-box-arrow-right"></i> <span class="label">Đăng xuất</span></a>
            </div>

            <!-- Main Content -->
            <div class="main-content" id="mainContent">
                <div class="content-wrapper">
                <div class="d-flex justify-content-between align-items-center mb-3 toolbar">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-secondary btn-sm" id="toggleSidebar" type="button"><i class="bi bi-layout-sidebar-inset"></i></button>
                        <h2 class="mb-0">Quản lý người dùng</h2>
                    </div>
                    <div>
                        <span>Xin chào, <strong><?= htmlspecialchars($user['name']) ?></strong></span>
                        <span class="badge bg-success ms-2"><?= htmlspecialchars($user['role']) ?></span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 toolbar">
                    <div>
                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#createUserCard" aria-expanded="false">
                            <i class="bi bi-person-plus"></i> Thêm tài khoản
                        </button>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (is_array($errors) && !empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Form tạo tài khoản (ẩn/hiện) -->
                <div class="collapse mb-4" id="createUserCard">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="card-title mb-0">Tạo tài khoản mới</h5>
                                <button class="btn-close" data-bs-toggle="collapse" data-bs-target="#createUserCard" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="index.php?action=admin_user_create">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                                        <input type="text" name="ho_ten" class="form-control" required minlength="2"
                                               value="<?= htmlspecialchars($form_data['ho_ten'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" required
                                               value="<?= htmlspecialchars($form_data['email'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                        <input type="password" name="password" class="form-control" required minlength="6">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                                        <select name="ma_vai_tro" class="form-select" required>
                                            <option value="">-- Chọn vai trò --</option>
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?= $role['ma_vai_tro'] ?>"
                                                    <?= (isset($form_data['ma_vai_tro']) && $form_data['ma_vai_tro'] == $role['ma_vai_tro']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($role['ten_vai_tro']) ?> - <?= htmlspecialchars($role['mo_ta']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Số điện thoại</label>
                                        <input type="tel" name="so_dien_thoai" class="form-control" pattern="[0-9]{10,11}"
                                               value="<?= htmlspecialchars($form_data['so_dien_thoai'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Địa chỉ</label>
                                        <input type="text" name="dia_chi" class="form-control" maxlength="255"
                                               value="<?= htmlspecialchars($form_data['dia_chi'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3 gap-2">
                                    <button class="btn btn-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#createUserCard">Hủy</button>
                                    <button type="submit" class="btn btn-primary">Tạo tài khoản</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Danh sách người dùng -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Danh sách người dùng</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Họ tên</th>
                                        <th>Email</th>
                                        <th>Vai trò</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                        <tr><td colspan="6" class="text-center text-muted">Chưa có người dùng</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $u): ?>
                                            <tr>
                                                <td><?= (int)$u['ma_nguoi_dung'] ?></td>
                                                <td>
                                                    <div class="fw-semibold"><?= htmlspecialchars($u['ho_ten']) ?></div>
                                                    <?php if (!empty($u['so_dien_thoai'])): ?>
                                                        <div class="text-muted small"><i class="bi bi-telephone"></i> <?= htmlspecialchars($u['so_dien_thoai']) ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($u['dia_chi'])): ?>
                                                        <div class="text-muted small"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($u['dia_chi']) ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($u['email']) ?></td>
                                                <td>
                                                    <form class="d-flex align-items-center" method="POST" action="index.php?action=admin_user_update_role">
                                                        <input type="hidden" name="id" value="<?= (int)$u['ma_nguoi_dung'] ?>">
                                                        <select name="ma_vai_tro" class="form-select form-select-sm me-2" <?= ($u['ten_vai_tro'] === 'QUAN_TRI') ? 'disabled' : '' ?>>
                                                            <?php foreach ($roles as $role): ?>
                                                                <option value="<?= $role['ma_vai_tro'] ?>" <?= ($role['ma_vai_tro'] == $u['ma_vai_tro']) ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($role['ten_vai_tro']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <?php if ($u['ten_vai_tro'] !== 'QUAN_TRI'): ?>
                                                            <button class="btn btn-sm btn-outline-primary" type="submit">Lưu</button>
                                                        <?php endif; ?>
                                                    </form>
                                                </td>
                                                <td>
                                                    <?php if ((int)$u['trang_thai'] === 1): ?>
                                                        <span class="badge bg-success">Hoạt động</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Khóa</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <form method="POST" action="index.php?action=admin_user_toggle_status" onsubmit="return confirm('Bạn có chắc muốn thay đổi trạng thái?');">
                                                            <input type="hidden" name="id" value="<?= (int)$u['ma_nguoi_dung'] ?>">
                                                            <input type="hidden" name="trang_thai" value="<?= $u['trang_thai'] ? 0 : 1 ?>">
                                                            <?php if ($u['ten_vai_tro'] !== 'QUAN_TRI'): ?>
                                                                <button type="submit" class="btn btn-sm <?= $u['trang_thai'] ? 'btn-warning' : 'btn-success' ?>">
                                                                    <?= $u['trang_thai'] ? 'Khóa' : 'Mở khóa' ?>
                                                                </button>
                                                            <?php else: ?>
                                                                <button type="button" class="btn btn-sm btn-light" disabled>Admin</button>
                                                            <?php endif; ?>
                                                        </form>
                                                        <form method="POST" action="index.php?action=admin_user_delete" onsubmit="return confirm('Bạn có chắc muốn xóa người dùng này?');">
                                                            <input type="hidden" name="id" value="<?= (int)$u['ma_nguoi_dung'] ?>">
                                                            <?php if ($u['ten_vai_tro'] !== 'QUAN_TRI'): ?>
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                                            <?php else: ?>
                                                                <button type="button" class="btn btn-sm btn-light" disabled>Admin</button>
                                                            <?php endif; ?>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const toggleBtn = document.getElementById('toggleSidebar');
            if (toggleBtn && sidebar && mainContent) {
                toggleBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                });
            }
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

