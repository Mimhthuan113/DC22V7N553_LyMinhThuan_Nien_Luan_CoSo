<?php
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/Auth.php';
$user = Auth::user();
$error = Session::getFlash('error');
$success = Session::getFlash('success');
$errors = Session::getFlash('errors');
$isEdit = isset($sale) && $sale;
$isAdmin = Auth::isAdmin();
$redirectPage = $isAdmin ? 'admin_sale' : 'nhanvien_sale';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Sửa Sale' : 'Thêm Sale mới' ?> - Admin</title>
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
            border-radius: 8px;
            margin: 2px 10px;
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
        .main-content { 
            padding: 30px; 
            margin-left: 200px; 
            transition: margin-left 0.25s; 
            max-width: calc(100% - 200px);
            min-height: 100vh;
        }
        .page-header {
            background: linear-gradient(135deg, #023660 0%, #1956b2 100%);
            color: #fff;
            padding: 25px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(2, 54, 96, 0.3);
        }
        .card { 
            box-shadow: 0 8px 30px rgba(0,0,0,0.12); 
            border-radius: 15px;
            border: none;
            background: #fff;
        }
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px 30px;
            border-bottom: 2px solid #e9ecef;
            font-weight: 600;
            color: #023660;
        }
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1956b2;
            box-shadow: 0 0 0 0.2rem rgba(25, 86, 178, 0.15);
        }
        .btn-primary {
            background: linear-gradient(135deg, #023660 0%, #1956b2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="sidebar" id="sidebar">
                <h4 class="mb-4"><i class="bi bi-shield-check"></i> Admin Panel</h4>
                <a href="index.php?page=admin"><i class="bi bi-speedometer2"></i> <span class="label">Dashboard</span></a>
                <a href="index.php?page=admin_thuoc"><i class="bi bi-capsule"></i> <span class="label">Quản lý thuốc</span></a>
                <a href="index.php?page=admin_sale" class="active"><i class="bi bi-tag-fill"></i> <span class="label">Quản lý Sale</span></a>
                <a href="index.php?page=admin_donhang"><i class="bi bi-cart-check"></i> <span class="label">Quản lý đơn hàng</span></a>
                <a href="index.php?page=admin_danhmuc"><i class="bi bi-folder"></i> <span class="label">Quản lý danh mục</span></a>
                <a href="index.php?page=admin_tintuc"><i class="bi bi-newspaper"></i> <span class="label">Quản lý tin tức</span></a>
                <a href="index.php?page=admin_banner"><i class="bi bi-image"></i> <span class="label">Quản lý banner</span></a>
                <hr>
                <a href="index.php?page=trangchu"><i class="bi bi-house"></i> <span class="label">Về trang chủ</span></a>
                <a href="index.php?action=auth_logout"><i class="bi bi-box-arrow-right"></i> <span class="label">Đăng xuất</span></a>
            </div>

            <!-- Main Content -->
            <div class="main-content" id="mainContent">
                <div class="page-header">
                    <h2><i class="bi bi-tag-fill"></i> <?= $isEdit ? 'Sửa Sale' : 'Thêm Sale mới' ?></h2>
                    <div class="mt-2">
                        <span><i class="bi bi-person-circle"></i> Xin chào, <strong><?= htmlspecialchars($user['name']) ?></strong></span>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
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

                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> Thông tin Sale
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="index.php?action=<?= $isEdit ? 'admin_sale_update' : 'admin_sale_create' ?>">
                            <?php if ($isEdit): ?>
                                <input type="hidden" name="ma_sale" value="<?= $sale['ma_sale'] ?>">
                            <?php endif; ?>

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-capsule"></i> Sản phẩm <span class="text-danger">*</span>
                                </label>
                                <select name="ma_thuoc" class="form-select" required>
                                    <option value="">-- Chọn sản phẩm --</option>
                                    <?php foreach ($thuocList as $thuoc): ?>
                                        <option value="<?= $thuoc['ma_thuoc'] ?>" 
                                            <?= ($isEdit && $sale['ma_thuoc'] == $thuoc['ma_thuoc']) ? 'selected' : '' ?>
                                            data-gia="<?= $thuoc['gia'] ?>">
                                            <?= htmlspecialchars($thuoc['ten_thuoc']) ?> - <?= number_format($thuoc['gia'], 0, ',', '.') ?> ₫
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-percent"></i> Phần trăm giảm giá (%) <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="phan_tram_giam" class="form-control" 
                                       value="<?= $isEdit ? $sale['phan_tram_giam'] : '' ?>" 
                                       min="1" max="100" step="0.01" 
                                       placeholder="Nhập phần trăm giảm (1-100)" required
                                       id="phan_tram_giam">
                                <small class="text-muted">Ví dụ: 25 = giảm 25%</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-calendar-event"></i> Thời gian bắt đầu <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" name="thoi_gian_bat_dau" class="form-control" 
                                       value="<?= $isEdit ? date('Y-m-d\TH:i', strtotime($sale['thoi_gian_bat_dau'])) : '' ?>" 
                                       required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-calendar-x"></i> Thời gian kết thúc <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" name="thoi_gian_ket_thuc" class="form-control" 
                                       value="<?= $isEdit ? date('Y-m-d\TH:i', strtotime($sale['thoi_gian_ket_thuc'])) : '' ?>" 
                                       required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-toggle-on"></i> Trạng thái
                                </label>
                                <select name="trang_thai" class="form-select">
                                    <option value="1" <?= ($isEdit && $sale['trang_thai'] == 1) ? 'selected' : '' ?>>Hoạt động</option>
                                    <option value="0" <?= ($isEdit && $sale['trang_thai'] == 0) ? 'selected' : '' ?>>Tạm ngưng</option>
                                </select>
                            </div>

                            <div class="d-flex gap-3 mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> <?= $isEdit ? 'Cập nhật Sale' : 'Thêm Sale mới' ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tính giá sale tự động khi chọn sản phẩm và phần trăm giảm
        document.querySelector('select[name="ma_thuoc"]')?.addEventListener('change', function() {
            const giaGoc = parseFloat(this.options[this.selectedIndex].dataset.gia) || 0;
            const phanTramGiam = parseFloat(document.getElementById('phan_tram_giam').value) || 0;
            if (giaGoc > 0 && phanTramGiam > 0) {
                const giaSale = giaGoc * (1 - phanTramGiam / 100);
                console.log('Giá sale sẽ là:', giaSale.toLocaleString('vi-VN') + ' ₫');
            }
        });
        document.getElementById('phan_tram_giam')?.addEventListener('input', function() {
            const select = document.querySelector('select[name="ma_thuoc"]');
            if (select && select.value) {
                const giaGoc = parseFloat(select.options[select.selectedIndex].dataset.gia) || 0;
                const phanTramGiam = parseFloat(this.value) || 0;
                if (giaGoc > 0 && phanTramGiam > 0) {
                    const giaSale = giaGoc * (1 - phanTramGiam / 100);
                    console.log('Giá sale sẽ là:', giaSale.toLocaleString('vi-VN') + ' ₫');
                }
            }
        });
    </script>
</body>
</html>

