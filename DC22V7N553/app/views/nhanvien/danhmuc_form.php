<?php
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/Auth.php';
$user = Auth::user();
$error = Session::getFlash('error');
$success = Session::getFlash('success');
$errors = Session::getFlash('errors');
$isEdit = isset($danhMuc) && $danhMuc;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Sửa danh mục' : 'Thêm danh mục mới' ?> - Nhân viên</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; }
        .sidebar { background: #0b3d72; min-height: 100vh; color: #fff; padding: 20px 0; position: fixed; width: 200px; transition: width 0.25s; overflow: hidden; }
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
        .card { box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 10px; }
        .content-wrapper { max-width: 1000px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="sidebar" id="sidebar">
                <h4 class="mb-4"><i class="bi bi-person-badge"></i> Nhân viên</h4>
                <a href="index.php?page=nhanvien"><i class="bi bi-speedometer2"></i> <span class="label">Dashboard</span></a>
                <a href="index.php?page=nhanvien_donhang"><i class="bi bi-receipt"></i> <span class="label">Quản lý đơn hàng</span></a>
                <a href="index.php?page=nhanvien_thuoc"><i class="bi bi-capsule"></i> <span class="label">Quản lý thuốc</span></a>
                <a href="index.php?page=nhanvien_danhmuc" class="active"><i class="bi bi-folder"></i> <span class="label">Quản lý danh mục</span></a>
                <a href="index.php?page=nhanvien_tintuc"><i class="bi bi-newspaper"></i> <span class="label">Quản lý tin tức</span></a>
                <a href="index.php?page=nhanvien_banner"><i class="bi bi-image"></i> <span class="label">Quản lý banner</span></a>
                <a href="index.php?page=nhanvien_sale"><i class="bi bi-tag-fill"></i> <span class="label">Quản lý Sale</span></a>
                <hr style="border-color: #1956b2;">
                <a href="index.php?page=trangchu"><i class="bi bi-house"></i> <span class="label">Về trang chủ</span></a>
                <a href="index.php?action=auth_logout"><i class="bi bi-box-arrow-right"></i> <span class="label">Đăng xuất</span></a>
            </div>

            <!-- Main Content -->
            <div class="main-content" id="mainContent">
                <div class="content-wrapper">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-outline-secondary btn-sm" id="toggleSidebar" type="button"><i class="bi bi-layout-sidebar-inset"></i></button>
                            <h2 class="mb-0"><i class="bi bi-folder"></i> <?= $isEdit ? 'Sửa danh mục' : 'Thêm danh mục mới' ?></h2>
                        </div>
                        <div>
                            <span>Xin chào, <strong><?= htmlspecialchars($user['name']) ?></strong></span>
                            <span class="badge bg-success ms-2"><?= htmlspecialchars($user['role']) ?></span>
                        </div>
                    </div>

                    <!-- Alerts -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
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

                    <!-- Form -->
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="index.php?action=<?= $isEdit ? 'admin_danhmuc_update' : 'admin_danhmuc_create' ?>">
                                <?php if ($isEdit): ?>
                                    <input type="hidden" name="ma_danh_muc" value="<?= $danhMuc['ma_danh_muc'] ?>">
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                                    <input type="text" name="ten_danh_muc" class="form-control" 
                                           value="<?= htmlspecialchars($isEdit ? $danhMuc['ten_danh_muc'] : '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Danh mục cha</label>
                                    <select name="ma_danh_muc_cha" class="form-select">
                                        <option value="">-- Không có (Danh mục gốc) --</option>
                                        <?php foreach ($danhMucChaList as $dm): ?>
                                            <option value="<?= $dm['ma_danh_muc'] ?>" 
                                                <?= ($isEdit && isset($danhMuc['ma_danh_muc_cha']) && $danhMuc['ma_danh_muc_cha'] == $dm['ma_danh_muc']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($dm['ten_danh_muc']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">Để trống nếu là danh mục gốc. Chọn danh mục cha nếu muốn tạo danh mục con.</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="mo_ta" class="form-control" rows="4"><?= htmlspecialchars($isEdit ? ($danhMuc['mo_ta'] ?? '') : '') ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <select name="trang_thai" class="form-select">
                                        <option value="1" <?= ($isEdit && $danhMuc['trang_thai'] == 1) ? 'selected' : '' ?>>Hoạt động</option>
                                        <option value="0" <?= ($isEdit && $danhMuc['trang_thai'] == 0) ? 'selected' : '' ?>>Tạm ngưng</option>
                                    </select>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> <?= $isEdit ? 'Cập nhật' : 'Thêm mới' ?>
                                    </button>
                                    <a href="index.php?page=nhanvien_danhmuc" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Quay lại
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('toggleSidebar')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
    </script>
</body>
</html>
