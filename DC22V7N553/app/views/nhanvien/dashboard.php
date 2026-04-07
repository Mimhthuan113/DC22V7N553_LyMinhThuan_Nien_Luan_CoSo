<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang nhân viên</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; }
        .sidebar { background: #0b3d72; min-height: 100vh; color: #fff; padding: 20px 0; position: fixed; width: 200px; transition: width 0.25s; overflow: hidden; }
        .sidebar h4 { padding: 0 20px; font-size: 1.1rem; }
        .sidebar a { color: #fff; text-decoration: none; padding: 12px 20px; display: block; transition: all 0.3s; }
        .sidebar a:hover { background: #1956b2; padding-left: 25px; }
        .sidebar a.active { background: #1956b2; border-left: 4px solid #fff; }
        .sidebar.collapsed { width: 64px; }
        .sidebar.collapsed h4 { display: none; }
        .sidebar.collapsed a { padding-left: 18px; }
        .sidebar.collapsed a span.label { display: none; }
        .main-content { padding: 24px; margin-left: 200px; transition: margin-left 0.25s; max-width: calc(100% - 200px); }
        .main-content.expanded { margin-left: 64px; max-width: calc(100% - 64px); }
        .content-wrapper { max-width: 1200px; margin: 0 auto; }
        .stat-card { background: #fff; border-radius: 10px; padding: 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 16px; }
        .stat-card h5 { margin: 0; font-size: 1rem; color: #1956b2; }
        .stat-card p { margin: 4px 0 0 0; color: #555; }
        .table-responsive { max-height: 60vh; }
        .toolbar { gap: 10px; flex-wrap: wrap; }
    </style>
</head>
<body>
    <?php
    require_once __DIR__ . '/../../core/Auth.php';
    require_once __DIR__ . '/../../models/ThuocModel.php';
    $user = Auth::user();
    $thuocModel = new ThuocModel();
    $soThuocHetHan = $thuocModel->demThuocHetHan();
    $soThuocSapHetHan = $thuocModel->demThuocSapHetHan();
    $thuocHetHan = $thuocModel->layThuocHetHan(5);
    $thuocSapHetHan = $thuocModel->layThuocSapHetHan(5);
    ?>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="sidebar" id="sidebar">
                <h4 class="mb-4"><i class="bi bi-person-badge"></i> Nhân viên</h4>
                <a href="index.php?page=nhanvien" class="active"><i class="bi bi-speedometer2"></i> <span class="label">Dashboard</span></a>
                <a href="index.php?page=nhanvien_donhang"><i class="bi bi-receipt"></i> <span class="label">Quản lý đơn hàng</span></a>
                <a href="index.php?page=nhanvien_thuoc"><i class="bi bi-capsule"></i> <span class="label">Quản lý thuốc</span></a>
                <a href="index.php?page=nhanvien_danhmuc"><i class="bi bi-folder"></i> <span class="label">Quản lý danh mục</span></a>
                <a href="index.php?page=nhanvien_tintuc"><i class="bi bi-newspaper"></i> <span class="label">Quản lý tin tức</span></a>
                <a href="index.php?page=nhanvien_banner"><i class="bi bi-image"></i> <span class="label">Quản lý banner</span></a>
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
                        <h2 class="mb-0">Dashboard nhân viên</h2>
                    </div>
                    <div>
                        <span>Xin chào, <strong><?= htmlspecialchars($user['name']) ?></strong></span>
                        <span class="badge bg-primary ms-2"><?= htmlspecialchars($user['role']) ?></span>
                    </div>
                </div>

                <!-- Cảnh báo thuốc hết hạn/sắp hết hạn -->
                <?php if ($soThuocHetHan > 0 || $soThuocSapHetHan > 0): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Cảnh báo thuốc hết hạn!</h5>
                        <?php if ($soThuocHetHan > 0): ?>
                            <p class="mb-1"><strong><?= $soThuocHetHan ?></strong> thuốc đã hết hạn cần xử lý ngay!</p>
                        <?php endif; ?>
                        <?php if ($soThuocSapHetHan > 0): ?>
                            <p class="mb-1"><strong><?= $soThuocSapHetHan ?></strong> thuốc sắp hết hạn (trong 30 ngày tới) cần kiểm tra!</p>
                        <?php endif; ?>
                        <hr>
                        <p class="mb-0">
                            <a href="index.php?page=nhanvien_thuoc" class="alert-link">Xem chi tiết và xử lý ngay</a>
                        </p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Thống kê doanh thu -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> Thống kê doanh thu</h5>
                        <button type="button" class="btn btn-light btn-sm" onclick="xuatExcel()">
                            <i class="bi bi-file-earmark-excel"></i> Xuất Excel
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Bộ lọc thời gian -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Loại thống kê</label>
                                <select id="filterType" class="form-select">
                                    <option value="ngay">Theo ngày</option>
                                    <option value="thang">Theo tháng</option>
                                    <option value="nam">Theo năm</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="yearFilter" style="display: none;">
                                <label class="form-label">Năm</label>
                                <select id="filterYear" class="form-select">
                                    <?php
                                    $currentYear = date('Y');
                                    for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                                        echo "<option value='$i'" . ($i == $currentYear ? ' selected' : '') . ">$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3" id="dateRangeFilter">
                                <label class="form-label">Từ ngày</label>
                                <input type="date" id="startDate" class="form-control" value="<?= date('Y-m-01') ?>">
                            </div>
                            <div class="col-md-3" id="endDateFilter">
                                <label class="form-label">Đến ngày</label>
                                <input type="date" id="endDate" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        
                        <!-- Tổng kết -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="stat-card bg-success text-white">
                                    <h5><i class="bi bi-currency-dollar"></i> Tổng doanh thu</h5>
                                    <p class="fs-4 mb-0" id="totalRevenue">0 ₫</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stat-card bg-info text-white">
                                    <h5><i class="bi bi-receipt"></i> Tổng số đơn</h5>
                                    <p class="fs-4 mb-0" id="totalOrders">0</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Biểu đồ -->
                        <div style="position: relative; height: 400px;">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h5>Đơn hàng cần xử lý</h5>
                            <p>Chức năng sẽ bổ sung sau</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h5>Đơn hàng đang giao</h5>
                            <p>Chức năng sẽ bổ sung sau</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h5>Đơn hoàn tất hôm nay</h5>
                            <p>Chức năng sẽ bổ sung sau</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Đơn hàng gần đây</h5>
                        <p class="text-muted mb-0">Danh sách sẽ hiển thị ở đây (chưa kết nối dữ liệu).</p>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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

        // Khởi tạo biểu đồ
        const ctx = document.getElementById('revenueChart').getContext('2d');
        let revenueChart = null;

        // Hàm format số tiền
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount);
        }

        // Hàm load dữ liệu thống kê
        function loadRevenueData() {
            const type = document.getElementById('filterType').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const year = document.getElementById('filterYear').value;

            let url = 'index.php?action=thongke_doanhthu&type=' + type;
            if (type === 'ngay') {
                if (startDate) url += '&start_date=' + startDate;
                if (endDate) url += '&end_date=' + endDate;
            } else if (type === 'thang') {
                if (year) url += '&year=' + year;
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Cập nhật tổng kết
                        document.getElementById('totalRevenue').textContent = formatCurrency(data.tong_doanh_thu);
                        document.getElementById('totalOrders').textContent = data.tong_so_don;

                        // Cập nhật biểu đồ
                        if (revenueChart) {
                            revenueChart.destroy();
                        }

                        revenueChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: data.labels,
                                datasets: [{
                                    label: 'Doanh thu (₫)',
                                    data: data.revenues,
                                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top'
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return 'Doanh thu: ' + formatCurrency(context.parsed.y);
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return formatCurrency(value);
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        alert('Lỗi: ' + (data.message || 'Không thể tải dữ liệu'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi tải dữ liệu thống kê');
                });
        }

        // Xử lý thay đổi bộ lọc
        document.getElementById('filterType').addEventListener('change', function() {
            const type = this.value;
            const yearFilter = document.getElementById('yearFilter');
            const dateRangeFilter = document.getElementById('dateRangeFilter');
            const endDateFilter = document.getElementById('endDateFilter');

            if (type === 'thang') {
                yearFilter.style.display = 'block';
                dateRangeFilter.style.display = 'none';
                endDateFilter.style.display = 'none';
            } else if (type === 'nam') {
                yearFilter.style.display = 'none';
                dateRangeFilter.style.display = 'none';
                endDateFilter.style.display = 'none';
            } else {
                yearFilter.style.display = 'none';
                dateRangeFilter.style.display = 'block';
                endDateFilter.style.display = 'block';
            }
            loadRevenueData();
        });

        document.getElementById('filterYear').addEventListener('change', loadRevenueData);
        document.getElementById('startDate').addEventListener('change', loadRevenueData);
        document.getElementById('endDate').addEventListener('change', loadRevenueData);

        // Load dữ liệu ban đầu
        loadRevenueData();

        // Hàm xuất Excel
        function xuatExcel() {
            const type = document.getElementById('filterType').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const year = document.getElementById('filterYear').value;

            let url = 'index.php?action=xuat_excel_thongke&type=' + type;
            if (type === 'ngay') {
                if (startDate) url += '&start_date=' + startDate;
                if (endDate) url += '&end_date=' + endDate;
            } else if (type === 'thang') {
                if (year) url += '&year=' + year;
            }

            window.location.href = url;
        }
    </script>
</body>
</html>

