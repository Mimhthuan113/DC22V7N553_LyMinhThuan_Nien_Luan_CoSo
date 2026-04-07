<?php
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/Auth.php';
$user = Auth::user();
$error = Session::getFlash('error');
$success = Session::getFlash('success');
$search = $_GET['search'] ?? '';
$trang_thai_filter = $_GET['trang_thai'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - Nhân viên</title>
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
        .table thead th { background: #f1f3f5; font-weight: 600; }
        .badge { font-size: 0.75rem; }
        .toolbar { gap: 10px; flex-wrap: wrap; }
        .content-wrapper { max-width: 1300px; margin: 0 auto; }
        .table-responsive { max-height: 70vh; overflow-y: auto; }
        .btn-action { padding: 4px 8px; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="sidebar" id="sidebar">
                <h4 class="mb-4"><i class="bi bi-person-badge"></i> Nhân viên</h4>
                <a href="index.php?page=nhanvien"><i class="bi bi-speedometer2"></i> <span class="label">Dashboard</span></a>
                <a href="index.php?page=nhanvien_donhang" class="active"><i class="bi bi-receipt"></i> <span class="label">Quản lý đơn hàng</span></a>
                <a href="index.php?page=nhanvien_thuoc"><i class="bi bi-capsule"></i> <span class="label">Quản lý thuốc</span></a>
                <a href="index.php?page=nhanvien_danhmuc"><i class="bi bi-folder"></i> <span class="label">Quản lý danh mục</span></a>
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
                    <div class="d-flex justify-content-between align-items-center mb-3 toolbar">
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-outline-secondary btn-sm" id="toggleSidebar" type="button"><i class="bi bi-layout-sidebar-inset"></i></button>
                            <h2 class="mb-0"><i class="bi bi-receipt"></i> Quản lý đơn hàng</h2>
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

                    <!-- Toolbar -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <form method="GET" class="d-flex gap-2 flex-wrap">
                                <input type="hidden" name="page" value="admin_donhang">
                                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo mã đơn, tên khách hàng, email..." value="<?= htmlspecialchars($search) ?>" style="width: 300px;">
                                <select name="trang_thai" class="form-select" style="width: 200px;">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="CHO_XU_LY" <?= $trang_thai_filter === 'CHO_XU_LY' ? 'selected' : '' ?>>Chờ xử lý</option>
                                    <option value="DANG_XU_LY" <?= $trang_thai_filter === 'DANG_XU_LY' ? 'selected' : '' ?>>Đang xử lý</option>
                                    <option value="DANG_GIAO" <?= $trang_thai_filter === 'DANG_GIAO' ? 'selected' : '' ?>>Đang giao</option>
                                    <option value="HOAN_TAT" <?= $trang_thai_filter === 'HOAN_TAT' ? 'selected' : '' ?>>Hoàn tất</option>
                                    <option value="DA_HUY" <?= $trang_thai_filter === 'DA_HUY' ? 'selected' : '' ?>>Đã hủy</option>
                                </select>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Tìm kiếm</button>
                                <?php if (!empty($search) || !empty($trang_thai_filter)): ?>
                                    <a href="index.php?page=nhanvien_donhang" class="btn btn-outline-secondary"><i class="bi bi-x"></i> Xóa bộ lọc</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Mã đơn</th>
                                            <th>Khách hàng</th>
                                            <th>Ngày đặt</th>
                                            <th>Tổng tiền</th>
                                            <th>Trạng thái đơn</th>
                                            <th>Trạng thái thanh toán</th>
                                            <th>Hình thức TT</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($donHangList)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                                    <p class="mt-2 text-muted">Chưa có đơn hàng nào</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($donHangList as $dh): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($dh['ma_don']) ?></strong></td>
                                                    <td>
                                                        <div><?= htmlspecialchars($dh['ho_ten']) ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($dh['email']) ?></small>
                                                    </td>
                                                    <td><?= date('d/m/Y H:i', strtotime($dh['ngay_dat'])) ?></td>
                                                    <td><strong class="text-danger"><?= number_format($dh['tong_tien'], 0, ',', '.') ?> ₫</strong></td>
                                                    <td>
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
                                                        <span class="badge <?= $badgeClass[$dh['trang_thai_don']] ?? 'bg-secondary' ?>">
                                                            <?= $statusText[$dh['trang_thai_don']] ?? $dh['trang_thai_don'] ?>
                                                        </span>
                                                    </td>
                                                    <td>
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
                                                        <span class="badge <?= $paymentBadgeClass[$dh['trang_thai_thanh_toan']] ?? 'bg-secondary' ?>">
                                                            <?= $paymentText[$dh['trang_thai_thanh_toan']] ?? $dh['trang_thai_thanh_toan'] ?>
                                                        </span>
                                                    </td>
                                                    <td><?= $dh['hinh_thuc_thanh_toan'] === 'COD' ? 'COD' : 'Chuyển khoản' ?></td>
                                                    <td>
                                                        <a href="index.php?action=admin_donhang_chi_tiet&id=<?= $dh['ma_don_hang'] ?>" class="btn btn-sm btn-primary">
                                                            <i class="bi bi-eye"></i> Xem
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <nav aria-label="Page navigation" class="mt-3">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=nhanvien_donhang&p=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&trang_thai=<?= urlencode($trang_thai_filter) ?>">Trước</a>
                                            </li>
                                        <?php endif; ?>
                                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=nhanvien_donhang&p=<?= $i ?>&search=<?= urlencode($search) ?>&trang_thai=<?= urlencode($trang_thai_filter) ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=nhanvien_donhang&p=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&trang_thai=<?= urlencode($trang_thai_filter) ?>">Sau</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
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

