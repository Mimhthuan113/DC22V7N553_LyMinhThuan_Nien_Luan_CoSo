<?php
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/Auth.php';
$user = Auth::user();
$error = Session::getFlash('error');
$success = Session::getFlash('success');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tin tức - Admin</title>
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
                <h4 class="mb-4"><i class="bi bi-shield-check"></i> Admin Panel</h4>
                <a href="index.php?page=admin"><i class="bi bi-speedometer2"></i> <span class="label">Dashboard</span></a>
                <a href="index.php?page=admin_users"><i class="bi bi-people"></i> <span class="label">Quản lý người dùng</span></a>
                <a href="index.php?page=admin_thuoc"><i class="bi bi-capsule"></i> <span class="label">Quản lý thuốc</span></a>
                <a href="index.php?page=admin_donhang"><i class="bi bi-cart-check"></i> <span class="label">Quản lý đơn hàng</span></a>
                <a href="index.php?page=admin_danhmuc"><i class="bi bi-folder"></i> <span class="label">Quản lý danh mục</span></a>
                <a href="index.php?page=admin_tintuc" class="active"><i class="bi bi-newspaper"></i> <span class="label">Quản lý tin tức</span></a>
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
                            <h2 class="mb-0"><i class="bi bi-newspaper"></i> Quản lý tin tức</h2>
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
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div class="d-flex gap-2">
                                    <a href="index.php?action=admin_tintuc_form" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> Thêm tin tức mới
                                    </a>
                                </div>
                                <form method="GET" class="d-flex gap-2">
                                    <input type="hidden" name="page" value="admin_tintuc">
                                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm tin tức..." value="<?= htmlspecialchars($search ?? '') ?>" style="width: 300px;">
                                    <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
                                    <?php if (!empty($search)): ?>
                                        <a href="index.php?page=admin_tintuc" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Tiêu đề</th>
                                            <th>Tác giả</th>
                                            <th>Lượt xem</th>
                                            <th>Ngày tạo</th>
                                            <th>Trạng thái</th>
                                            <th style="width: 150px;">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($tinTucList)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">
                                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i><br>
                                                    Chưa có tin tức nào
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($tinTucList as $index => $tin): ?>
                                                <tr>
                                                    <td><?= ($page - 1) * $limit + $index + 1 ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($tin['tieu_de']) ?></strong>
                                                        <?php if ($tin['tom_tat']): ?>
                                                            <br><small class="text-muted"><?= htmlspecialchars(mb_substr($tin['tom_tat'], 0, 100)) ?>...</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($tin['tac_gia'] ?? 'N/A') ?></td>
                                                    <td><?= number_format($tin['luot_xem']) ?></td>
                                                    <td><?= date('d/m/Y H:i', strtotime($tin['ngay_tao'])) ?></td>
                                                    <td>
                                                        <?php if ($tin['trang_thai'] == 1): ?>
                                                            <span class="badge bg-success">Hiển thị</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Ẩn</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="index.php?action=admin_tintuc_form&id=<?= $tin['ma_tin_tuc'] ?>" 
                                                               class="btn btn-outline-primary btn-action" title="Sửa">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <a href="index.php?action=admin_tintuc_delete&id=<?= $tin['ma_tin_tuc'] ?>" 
                                                               class="btn btn-outline-danger btn-action" 
                                                               onclick="return confirm('Bạn có chắc muốn xóa tin tức này?')" title="Xóa">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php if ($totalPages > 1): ?>
                            <div class="card-footer">
                                <nav>
                                    <ul class="pagination pagination-sm mb-0 justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=admin_tintuc&p=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Trước</a>
                                            </li>
                                        <?php endif; ?>
                                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=admin_tintuc&p=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=admin_tintuc&p=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">Sau</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar
        document.getElementById('toggleSidebar')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
    </script>
</body>
</html>

