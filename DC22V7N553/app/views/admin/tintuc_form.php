<?php
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/Auth.php';
$user = Auth::user();
$error = Session::getFlash('error');
$success = Session::getFlash('success');
$isEdit = isset($tinTuc) && $tinTuc;
$isAdmin = Auth::isAdmin();
$redirectPage = $isAdmin ? 'admin_tintuc' : 'nhanvien_tintuc';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Sửa tin tức' : 'Thêm tin tức mới' ?> - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        .sidebar { 
            background: linear-gradient(180deg, #023660 0%, #1956b2 100%);
            min-height: 100vh; 
            color: #fff; 
            padding: 20px 0; 
            position: fixed; 
            width: 200px; 
            transition: width 0.25s; 
            overflow: hidden;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .sidebar h4 { 
            padding: 0 20px; 
            font-size: 1.2rem; 
            font-weight: 700;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .sidebar a { 
            color: #fff; 
            text-decoration: none; 
            padding: 14px 20px; 
            display: block; 
            transition: all 0.3s;
            border-radius: 0;
            margin: 2px 10px;
            border-radius: 8px;
        }
        .sidebar a:hover { 
            background: rgba(255,255,255,0.15); 
            padding-left: 25px;
            transform: translateX(5px);
        }
        .sidebar a.active { 
            background: rgba(255,255,255,0.25); 
            border-left: 4px solid #fff;
            font-weight: 600;
        }
        .sidebar hr { 
            margin: 15px 10px; 
            border-color: rgba(255,255,255,0.2);
        }
        .sidebar.collapsed { width: 64px; }
        .sidebar.collapsed h4 { display: none; }
        .sidebar.collapsed a { padding-left: 18px; }
        .sidebar.collapsed a span.label { display: none; }
        .main-content { 
            padding: 30px; 
            margin-left: 200px; 
            transition: margin-left 0.25s; 
            max-width: calc(100% - 200px);
            min-height: 100vh;
        }
        .main-content.expanded { 
            margin-left: 64px; 
            max-width: calc(100% - 64px); 
        }
        .page-header {
            background: linear-gradient(135deg, #023660 0%, #1956b2 100%);
            color: #fff;
            padding: 25px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(2, 54, 96, 0.3);
        }
        .page-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .page-header .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
        }
        .page-header .badge {
            padding: 8px 15px;
            font-size: 0.85rem;
            border-radius: 20px;
        }
        .card { 
            box-shadow: 0 8px 30px rgba(0,0,0,0.12); 
            border-radius: 15px;
            border: none;
            overflow: hidden;
            background: #fff;
        }
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px 30px;
            border-bottom: 2px solid #e9ecef;
            font-weight: 600;
            color: #023660;
            font-size: 1.1rem;
        }
        .card-body {
            padding: 30px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1956b2;
            box-shadow: 0 0 0 0.2rem rgba(25, 86, 178, 0.15);
            outline: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #023660 0%, #1956b2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(2, 54, 96, 0.3);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(2, 54, 96, 0.4);
            background: linear-gradient(135deg, #1956b2 0%, #023660 100%);
        }
        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
        }
        .file-upload-wrapper {
            position: relative;
            border: 2px dashed #1956b2;
            border-radius: 10px;
            padding: 20px;
            background: #f8f9ff;
            transition: all 0.3s;
        }
        .file-upload-wrapper:hover {
            border-color: #023660;
            background: #f0f4ff;
        }
        .toggle-sidebar-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: #fff;
            border-radius: 8px;
            padding: 8px 12px;
        }
        .toggle-sidebar-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        .content-wrapper { max-width: 1200px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="sidebar" id="sidebar" style="background: <?= $isAdmin ? 'linear-gradient(180deg, #023660 0%, #1956b2 100%)' : 'linear-gradient(180deg, #0b3d72 0%, #1956b2 100%)' ?>;">
                <?php if ($isAdmin): ?>
                    <h4 class="mb-4"><i class="bi bi-shield-check"></i> Admin Panel</h4>
                    <a href="index.php?page=admin"><i class="bi bi-speedometer2"></i> <span class="label">Dashboard</span></a>
                    <a href="index.php?page=admin_users"><i class="bi bi-people"></i> <span class="label">Quản lý người dùng</span></a>
                    <a href="index.php?page=admin_thuoc"><i class="bi bi-capsule"></i> <span class="label">Quản lý thuốc</span></a>
                    <a href="index.php?page=admin_donhang"><i class="bi bi-cart-check"></i> <span class="label">Quản lý đơn hàng</span></a>
                    <a href="index.php?page=admin_danhmuc"><i class="bi bi-folder"></i> <span class="label">Quản lý danh mục</span></a>
                    <a href="index.php?page=admin_tintuc" class="active"><i class="bi bi-newspaper"></i> <span class="label">Quản lý tin tức</span></a>
                    <a href="index.php?page=admin_banner"><i class="bi bi-image"></i> <span class="label">Quản lý banner</span></a>
                    <a href="index.php?page=admin_sale"><i class="bi bi-tag-fill"></i> <span class="label">Quản lý Sale</span></a>
                <?php else: ?>
                    <h4 class="mb-4"><i class="bi bi-person-badge"></i> Nhân viên</h4>
                    <a href="index.php?page=nhanvien"><i class="bi bi-speedometer2"></i> <span class="label">Dashboard</span></a>
                    <a href="index.php?page=nhanvien_donhang"><i class="bi bi-receipt"></i> <span class="label">Quản lý đơn hàng</span></a>
                    <a href="index.php?page=nhanvien_thuoc"><i class="bi bi-capsule"></i> <span class="label">Quản lý thuốc</span></a>
                    <a href="index.php?page=nhanvien_tintuc" class="active"><i class="bi bi-newspaper"></i> <span class="label">Quản lý tin tức</span></a>
                    <a href="index.php?page=nhanvien_banner"><i class="bi bi-image"></i> <span class="label">Quản lý banner</span></a>
                    <a href="index.php?page=nhanvien_sale"><i class="bi bi-tag-fill"></i> <span class="label">Quản lý Sale</span></a>
                <?php endif; ?>
                <hr style="border-color: rgba(255,255,255,0.2);">
                <a href="index.php?page=trangchu"><i class="bi bi-house"></i> <span class="label">Về trang chủ</span></a>
                <a href="index.php?action=auth_logout"><i class="bi bi-box-arrow-right"></i> <span class="label">Đăng xuất</span></a>
            </div>

            <!-- Main Content -->
            <div class="main-content" id="mainContent">
                <div class="content-wrapper">
                    <div class="page-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <button class="toggle-sidebar-btn" id="toggleSidebar" type="button">
                                    <i class="bi bi-layout-sidebar-inset"></i>
                                </button>
                                <h2><i class="bi bi-newspaper"></i> <?= $isEdit ? 'Sửa tin tức' : 'Thêm tin tức mới' ?></h2>
                            </div>
                        </div>
                        <div class="user-info">
                            <span><i class="bi bi-person-circle"></i> Xin chào, <strong><?= htmlspecialchars($user['name']) ?></strong></span>
                            <span class="badge bg-light text-dark"><?= htmlspecialchars($user['role']) ?></span>
                        </div>
                    </div>

                    <!-- Alerts -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-info-circle"></i> Thông tin tin tức
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data" action="index.php?action=<?= $isEdit ? 'admin_tintuc_update' : 'admin_tintuc_create' ?>">
                                <?php if ($isEdit): ?>
                                    <input type="hidden" name="ma_tin_tuc" value="<?= $tinTuc['ma_tin_tuc'] ?>">
                                <?php endif; ?>

                                <div class="mb-4">
                                    <label for="tieu_de" class="form-label">
                                        <i class="bi bi-heading"></i> Tiêu đề <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="tieu_de" name="tieu_de" 
                                           value="<?= htmlspecialchars($tinTuc['tieu_de'] ?? '') ?>" 
                                           placeholder="Nhập tiêu đề tin tức" required>
                                </div>

                                <div class="mb-4">
                                    <label for="tom_tat" class="form-label">
                                        <i class="bi bi-file-text"></i> Tóm tắt
                                    </label>
                                    <textarea class="form-control" id="tom_tat" name="tom_tat" rows="3" placeholder="Nhập tóm tắt ngắn gọn về tin tức"><?= htmlspecialchars($tinTuc['tom_tat'] ?? '') ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="noi_dung" class="form-label">
                                        <i class="bi bi-file-earmark-text"></i> Nội dung <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="noi_dung" name="noi_dung" rows="10" placeholder="Nhập nội dung chi tiết của tin tức" required><?= htmlspecialchars($tinTuc['noi_dung'] ?? '') ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-8 mb-4">
                                        <label class="form-label d-flex justify-content-between align-items-center mb-3">
                                            <span><i class="bi bi-images"></i> Hình ảnh (tối đa 5 ảnh, upload từ máy)</span>
                                            <small class="text-muted">Ảnh 1 sẽ hiển thị chính</small>
                                        </label>
                                        <div class="row g-3">
                                            <?php 
                                                $imageFields = [
                                                    ['name' => 'hinh_anh', 'label' => 'Ảnh 1 (chính)', 'icon' => 'bi-star-fill'],
                                                    ['name' => 'hinh_anh_2', 'label' => 'Ảnh 2', 'icon' => 'bi-image'],
                                                    ['name' => 'hinh_anh_3', 'label' => 'Ảnh 3', 'icon' => 'bi-image'],
                                                    ['name' => 'hinh_anh_4', 'label' => 'Ảnh 4', 'icon' => 'bi-image'],
                                                    ['name' => 'hinh_anh_5', 'label' => 'Ảnh 5', 'icon' => 'bi-image'],
                                                ];
                                                foreach ($imageFields as $field):
                                                    $value = $isEdit ? ($tinTuc[$field['name']] ?? '') : '';
                                            ?>
                                                <div class="col-md-6">
                                                    <div class="file-upload-wrapper">
                                                        <label class="form-label">
                                                            <i class="<?= $field['icon'] ?>"></i> <?= $field['label'] ?>
                                                        </label>
                                                    <input type="hidden" name="old_<?= $field['name'] ?>" value="<?= htmlspecialchars($value) ?>">
                                                    <input type="file" name="<?= $field['name'] ?>" class="form-control" accept="image/*">
                                                    <?php if ($value): ?>
                                                            <small class="text-muted d-block mt-2">
                                                                <i class="bi bi-check-circle text-success"></i> Đang dùng: <?= htmlspecialchars($value) ?>
                                                            </small>
                                                    <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <label for="tac_gia" class="form-label">
                                            <i class="bi bi-person"></i> Tác giả
                                        </label>
                                        <input type="text" class="form-control" id="tac_gia" name="tac_gia" 
                                               value="<?= htmlspecialchars($tinTuc['tac_gia'] ?? '') ?>" 
                                               placeholder="Tên tác giả">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="trang_thai" class="form-label">
                                        <i class="bi bi-toggle-on"></i> Trạng thái
                                    </label>
                                    <select class="form-select" id="trang_thai" name="trang_thai">
                                        <option value="1" <?= (!isset($tinTuc) || $tinTuc['trang_thai'] == 1) ? 'selected' : '' ?>>Hiển thị</option>
                                        <option value="0" <?= (isset($tinTuc) && $tinTuc['trang_thai'] == 0) ? 'selected' : '' ?>>Ẩn</option>
                                    </select>
                                </div>

                                <div class="d-flex gap-3 mt-4 pt-3 border-top">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> <?= $isEdit ? 'Cập nhật tin tức' : 'Thêm tin tức mới' ?>
                                    </button>
                                    <a href="index.php?page=<?= $redirectPage ?>" class="btn btn-secondary">
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
