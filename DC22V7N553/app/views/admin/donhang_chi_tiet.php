<?php
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/Auth.php';
$user = Auth::user();
$error = Session::getFlash('error');
$success = Session::getFlash('success');
$redirectPage = Auth::isAdmin() ? 'admin_donhang' : 'nhanvien_donhang';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng - <?= Auth::isAdmin() ? 'Admin' : 'Nhân viên' ?></title>
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
        .card { box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 10px; }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="sidebar" id="sidebar" style="background: <?= Auth::isAdmin() ? '#023660' : '#0b3d72' ?>;">
                <?php if (Auth::isAdmin()): ?>
                    <h4 class="mb-4"><i class="bi bi-shield-check"></i> Admin Panel</h4>
                    <a href="index.php?page=admin"><i class="bi bi-speedometer2"></i> <span class="label">Dashboard</span></a>
                    <a href="index.php?page=admin_users"><i class="bi bi-people"></i> <span class="label">Quản lý người dùng</span></a>
                    <a href="index.php?page=admin_thuoc"><i class="bi bi-capsule"></i> <span class="label">Quản lý thuốc</span></a>
                    <a href="index.php?page=admin_donhang" class="active"><i class="bi bi-cart-check"></i> <span class="label">Quản lý đơn hàng</span></a>
                    <a href="index.php?page=admin_danhmuc"><i class="bi bi-folder"></i> <span class="label">Quản lý danh mục</span></a>
                    <a href="index.php?page=admin_tintuc"><i class="bi bi-newspaper"></i> <span class="label">Quản lý tin tức</span></a>
                    <a href="index.php?page=admin_banner"><i class="bi bi-image"></i> <span class="label">Quản lý banner</span></a>
                    <a href="index.php?page=admin_sale"><i class="bi bi-tag-fill"></i> <span class="label">Quản lý Sale</span></a>
                <?php else: ?>
                    <h4 class="mb-4"><i class="bi bi-person-badge"></i> Nhân viên</h4>
                    <a href="index.php?page=nhanvien"><i class="bi bi-speedometer2"></i> <span class="label">Dashboard</span></a>
                    <a href="index.php?page=admin_donhang" class="active"><i class="bi bi-receipt"></i> <span class="label">Quản lý đơn hàng</span></a>
                    <a href="index.php?page=nhanvien_thuoc"><i class="bi bi-capsule"></i> <span class="label">Quản lý thuốc</span></a>
                    <a href="index.php?page=nhanvien_tintuc"><i class="bi bi-newspaper"></i> <span class="label">Quản lý tin tức</span></a>
                    <a href="index.php?page=nhanvien_banner"><i class="bi bi-image"></i> <span class="label">Quản lý banner</span></a>
                    <a href="index.php?page=nhanvien_sale"><i class="bi bi-tag-fill"></i> <span class="label">Quản lý Sale</span></a>
                <?php endif; ?>
                <hr style="border-color: #1956b2;">
                <a href="index.php?page=trangchu"><i class="bi bi-house"></i> <span class="label">Về trang chủ</span></a>
                <a href="index.php?action=auth_logout"><i class="bi bi-box-arrow-right"></i> <span class="label">Đăng xuất</span></a>
            </div>

            <!-- Main Content -->
            <div class="main-content" id="mainContent">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-secondary btn-sm" id="toggleSidebar" type="button"><i class="bi bi-layout-sidebar-inset"></i></button>
                        <h2 class="mb-0"><i class="bi bi-receipt"></i> Chi tiết đơn hàng</h2>
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
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8">
                        <!-- Thông tin đơn hàng -->
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Thông tin đơn hàng</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Mã đơn hàng:</strong><br>
                                        <span class="text-primary fs-5"><?= htmlspecialchars($donHang['ma_don']) ?></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Ngày đặt:</strong><br>
                                        <?= date('d/m/Y H:i', strtotime($donHang['ngay_dat'])) ?>
                                    </div>
                                </div>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Khách hàng:</strong><br>
                                        <?= htmlspecialchars($donHang['ho_ten']) ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($donHang['email']) ?></small><br>
                                        <small class="text-muted"><?= htmlspecialchars($donHang['sdt_khach_hang'] ?? '') ?></small>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Địa chỉ giao hàng:</strong><br>
                                        <?= htmlspecialchars($donHang['dia_chi_giao']) ?><br>
                                        <strong>SĐT giao hàng:</strong> <?= htmlspecialchars($donHang['so_dien_thoai_giao']) ?>
                                    </div>
                                </div>
                                <?php if (!empty($donHang['ghi_chu'])): ?>
                                    <hr>
                                    <div>
                                        <strong>Ghi chú:</strong><br>
                                        <?= htmlspecialchars($donHang['ghi_chu']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Chi tiết sản phẩm -->
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Chi tiết sản phẩm</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Sản phẩm</th>
                                                <th>Số lượng</th>
                                                <th>Đơn giá</th>
                                                <th>Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($chiTiet as $ct): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($ct['ten_thuoc']) ?></strong><br>
                                                        <small class="text-muted"><?= htmlspecialchars($ct['don_vi']) ?></small>
                                                    </td>
                                                    <td><?= $ct['so_luong'] ?></td>
                                                    <td><?= number_format($ct['don_gia'], 0, ',', '.') ?> ₫</td>
                                                    <td><strong><?= number_format($ct['thanh_tien'], 0, ',', '.') ?> ₫</strong></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">Tổng cộng:</th>
                                                <th class="text-danger fs-5"><?= number_format($donHang['tong_tien'], 0, ',', '.') ?> ₫</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Cập nhật trạng thái -->
                        <div class="card mb-3">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="bi bi-gear"></i> Cập nhật trạng thái</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="index.php?action=admin_donhang_cap_nhat_trang_thai">
                                    <input type="hidden" name="ma_don_hang" value="<?= $donHang['ma_don_hang'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Trạng thái đơn hàng:</strong></label>
                                        <select name="trang_thai_don" class="form-select">
                                            <option value="CHO_XU_LY" <?= $donHang['trang_thai_don'] === 'CHO_XU_LY' ? 'selected' : '' ?>>Chờ xử lý</option>
                                            <option value="DANG_XU_LY" <?= $donHang['trang_thai_don'] === 'DANG_XU_LY' ? 'selected' : '' ?>>Đang xử lý</option>
                                            <option value="DANG_GIAO" <?= $donHang['trang_thai_don'] === 'DANG_GIAO' ? 'selected' : '' ?>>Đang giao</option>
                                            <option value="HOAN_TAT" <?= $donHang['trang_thai_don'] === 'HOAN_TAT' ? 'selected' : '' ?>>Hoàn tất</option>
                                            <option value="DA_HUY" <?= $donHang['trang_thai_don'] === 'DA_HUY' ? 'selected' : '' ?>>Đã hủy</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-check-circle"></i> Cập nhật trạng thái đơn
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="bi bi-credit-card"></i> Cập nhật thanh toán</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="index.php?action=admin_donhang_cap_nhat_thanh_toan">
                                    <input type="hidden" name="ma_don_hang" value="<?= $donHang['ma_don_hang'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Trạng thái thanh toán:</strong></label>
                                        <select name="trang_thai_thanh_toan" class="form-select">
                                            <option value="CHUA_THANH_TOAN" <?= $donHang['trang_thai_thanh_toan'] === 'CHUA_THANH_TOAN' ? 'selected' : '' ?>>Chưa thanh toán</option>
                                            <option value="DA_THANH_TOAN" <?= $donHang['trang_thai_thanh_toan'] === 'DA_THANH_TOAN' ? 'selected' : '' ?>>Đã thanh toán</option>
                                            <option value="HOAN_TIEN" <?= $donHang['trang_thai_thanh_toan'] === 'HOAN_TIEN' ? 'selected' : '' ?>>Hoàn tiền</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="bi bi-check-circle"></i> Cập nhật thanh toán
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="mb-2">
                                    <strong>Hình thức thanh toán:</strong><br>
                                    <?= $donHang['hinh_thuc_thanh_toan'] === 'COD' ? 'Thanh toán khi nhận hàng (COD)' : 'Chuyển khoản' ?>
                                </div>
                                <hr>
                                <div>
                                    <strong>Trạng thái hiện tại:</strong><br>
                                    <?php
                                    $badgeClass = [
                                        'CHO_XU_LY' => 'bg-warning',
                                        'DANG_XU_LY' => 'bg-info',
                                        'DANG_GIAO' => 'bg-primary',
                                        'HOAN_TAT' => 'bg-success',
                                        'DA_HUY' => 'bg-danger'
                                    ];
                                    $statusText = [
                                        'CHO_XU_LY' => 'Chờ xử lý',
                                        'DANG_XU_LY' => 'Đang xử lý',
                                        'DANG_GIAO' => 'Đang giao',
                                        'HOAN_TAT' => 'Hoàn tất',
                                        'DA_HUY' => 'Đã hủy'
                                    ];
                                    ?>
                                    <span class="badge <?= $badgeClass[$donHang['trang_thai_don']] ?? 'bg-secondary' ?> fs-6">
                                        <?= $statusText[$donHang['trang_thai_don']] ?? $donHang['trang_thai_don'] ?>
                                    </span>
                                </div>
                                <div class="mt-2">
                                    <strong>Thanh toán:</strong><br>
                                    <?php
                                    $paymentBadgeClass = [
                                        'CHUA_THANH_TOAN' => 'bg-warning',
                                        'DA_THANH_TOAN' => 'bg-success',
                                        'HOAN_TIEN' => 'bg-danger'
                                    ];
                                    $paymentText = [
                                        'CHUA_THANH_TOAN' => 'Chưa thanh toán',
                                        'DA_THANH_TOAN' => 'Đã thanh toán',
                                        'HOAN_TIEN' => 'Hoàn tiền'
                                    ];
                                    ?>
                                    <span class="badge <?= $paymentBadgeClass[$donHang['trang_thai_thanh_toan']] ?? 'bg-secondary' ?> fs-6">
                                        <?= $paymentText[$donHang['trang_thai_thanh_toan']] ?? $donHang['trang_thai_thanh_toan'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <a href="index.php?page=<?= $redirectPage ?>" class="btn btn-secondary w-100">
                                <i class="bi bi-arrow-left"></i> Quay lại danh sách
                            </a>
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

