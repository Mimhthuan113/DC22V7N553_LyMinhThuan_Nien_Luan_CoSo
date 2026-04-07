<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Session.php';
$user = Auth::user();
$error = Session::getFlash('error');
$success = Session::getFlash('success');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng - DC22V7N553</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body { 
            background: #f9f9f9;
            display: flex;
            flex-direction: column;
        }
        .pc-header { background: #007bdf; color: #fff; padding: 14px 0; }
        .pc-navbar { background: #fff; box-shadow: 0 2px 4px #e2e2e2; }
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .pc-footer { 
            background: #023660; 
            color: #fff; 
            padding: 24px 0; 
            margin-top: auto;
        }
        .order-detail-container { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="pc-header text-center">
        <h2 style="font-weight:700;">DC22V7N553</h2>
        <p style="margin: 0;">Uy tín - Nhanh chóng - Chính hãng</p>
    </div>
    
    <!-- Navbar -->
    <nav class="pc-navbar navbar navbar-expand-lg navbar-light">
      <div class="container">
        <a class="navbar-brand" href="index.php?page=trangchu">Trang chủ</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <?php 
            require_once __DIR__ . '/../../models/DanhMucModel.php';
            $danhMucModel = new DanhMucModel();
            $danhMucList = $danhMucModel->layTatCa();
            if (!empty($danhMucList)): ?>
              <?php foreach ($danhMucList as $dm): ?>
                <?php if ($dm['ma_danh_muc_cha'] === null && $dm['trang_thai'] == 1): ?>
                  <li class="nav-item">
                    <a class="nav-link" href="index.php?page=trangchu&danhmuc=<?= $dm['ma_danh_muc'] ?>">
                      <?= htmlspecialchars($dm['ten_danh_muc']) ?>
                    </a>
                  </li>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
          <a href="index.php?page=giohang" class="d-flex align-items-center justify-content-center me-2 position-relative"
             style="width:38px;height:38px;background:#1956b2;border-radius:50%;box-shadow:0 2px 8px #dde; text-decoration: none;">
            <i class="bi bi-cart3 text-white" style="font-size:18px;"></i>
            <span id="cartBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none; font-size:0.7rem;">0</span>
          </a>
          <?php if (Auth::check()): ?>
            <div class="dropdown me-2">
              <button class="btn btn-outline-primary dropdown-toggle" 
                      type="button" 
                      id="userDropdown" 
                      data-bs-toggle="dropdown" 
                      data-bs-auto-close="true"
                      aria-expanded="false"
                      aria-haspopup="true">
                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['name']) ?>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown" style="min-width: 200px; z-index: 1050;">
                <li>
                  <h6 class="dropdown-header">
                    <i class="bi bi-person"></i> <?= htmlspecialchars($user['name']) ?>
                  </h6>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" href="index.php?page=account">
                    <i class="bi bi-person-gear"></i> Tài khoản của tôi
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="index.php?page=donhang_cua_toi">
                    <i class="bi bi-receipt"></i> Đơn hàng của tôi
                  </a>
                </li>
                <?php if (Auth::isAdmin()): ?>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <a class="dropdown-item" href="index.php?page=admin">
                      <i class="bi bi-shield-check"></i> Quản trị
                    </a>
                  </li>
                <?php elseif (Auth::isNhanVien()): ?>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <a class="dropdown-item" href="index.php?page=nhanvien">
                      <i class="bi bi-person-badge"></i> Trang nhân viên
                    </a>
                  </li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item text-danger" href="index.php?action=auth_logout">
                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                  </a>
                </li>
              </ul>
            </div>
          <?php else: ?>
            <a href="index.php?page=login" class="btn btn-primary me-2">Đăng nhập</a>
            <a href="index.php?page=register" class="btn btn-outline-primary">Đăng ký</a>
          <?php endif; ?>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
    <div class="container my-4">
        <h2 class="mb-4"><i class="bi bi-receipt"></i> Chi tiết đơn hàng</h2>
        
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
                <div class="order-detail-container mb-4">
                    <h4 class="mb-3"><i class="bi bi-info-circle"></i> Thông tin đơn hàng</h4>
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
                            <strong>Địa chỉ giao hàng:</strong><br>
                            <?= htmlspecialchars($donHang['dia_chi_giao']) ?><br>
                            <strong>SĐT:</strong> <?= htmlspecialchars($donHang['so_dien_thoai_giao']) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Hình thức thanh toán:</strong><br>
                            <?= $donHang['hinh_thuc_thanh_toan'] === 'COD' ? 'Thanh toán khi nhận hàng (COD)' : 'Chuyển khoản' ?><br>
                            <?php
                            $paymentText = [
                                'CHUA_THANH_TOAN' => 'Chưa thanh toán',
                                'DA_THANH_TOAN' => 'Đã thanh toán',
                                'HOAN_TIEN' => 'Hoàn tiền'
                            ];
                            ?>
                            <strong>Trạng thái thanh toán:</strong> <?= $paymentText[$donHang['trang_thai_thanh_toan']] ?? $donHang['trang_thai_thanh_toan'] ?>
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
                
                <div class="order-detail-container">
                    <h4 class="mb-3"><i class="bi bi-list-ul"></i> Chi tiết sản phẩm</h4>
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
            
            <div class="col-md-4">
                <div class="order-detail-container">
                    <h5 class="mb-3">Trạng thái đơn hàng</h5>
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
                    <div class="mb-3">
                        <span class="badge <?= $badgeClass[$donHang['trang_thai_don']] ?? 'bg-secondary' ?> fs-6 p-3 d-block text-center">
                            <?= $statusText[$donHang['trang_thai_don']] ?? $donHang['trang_thai_don'] ?>
                        </span>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <strong>Tổng tiền:</strong><br>
                        <h4 class="text-danger mt-2"><?= number_format($donHang['tong_tien'], 0, ',', '.') ?> ₫</h4>
                    </div>
                    <a href="index.php?page=donhang_cua_toi" class="btn btn-secondary w-100">
                        <i class="bi bi-arrow-left"></i> Quay lại danh sách đơn hàng
                    </a>
                </div>
            </div>
        </div>
    </div>
    </div>
    <!-- End Main Content -->

    <!-- Footer -->
    <div class="pc-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <b>DC22V7N553</b><br>
                    Địa chỉ: 248A Nơ Trang Long, Bình Thạnh, TP.HCM<br>
                    Hotline: 1800 6821 - Email: lienhe@pharmacy.vn
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    © 2025 Nhà thuốc demo dành cho mục đích học tập.
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateCartCount() {
            fetch('index.php?action=cart_count')
                .then(response => response.json())
                .then(data => {
                    const cartBadge = document.getElementById('cartBadge');
                    if (cartBadge) {
                        if (data.tong_so_luong > 0) {
                            cartBadge.textContent = data.tong_so_luong;
                            cartBadge.style.display = 'block';
                        } else {
                            cartBadge.style.display = 'none';
                        }
                    }
                })
                .catch(error => console.error('Error updating cart count:', error));
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
    </script>
    <script>
        // Khởi tạo dropdown menu - đảm bảo hoạt động
        document.addEventListener('DOMContentLoaded', function() {
            function initDropdown() {
                const dropdownBtn = document.getElementById('userDropdown');
                const dropdownMenu = dropdownBtn ? dropdownBtn.nextElementSibling : null;
                
                if (!dropdownBtn || !dropdownMenu) return;
                
                if (typeof bootstrap !== 'undefined') {
                    try {
                        const dropdown = new bootstrap.Dropdown(dropdownBtn);
                        return;
                    } catch(e) {
                        console.log('Bootstrap dropdown failed, using fallback');
                    }
                }
                
                dropdownBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const isOpen = dropdownMenu.classList.contains('show');
                    if (isOpen) {
                        dropdownMenu.classList.remove('show');
                        dropdownBtn.setAttribute('aria-expanded', 'false');
                    } else {
                        dropdownMenu.classList.add('show');
                        dropdownBtn.setAttribute('aria-expanded', 'true');
                    }
                });
                
                document.addEventListener('click', function(e) {
                    if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                        dropdownBtn.setAttribute('aria-expanded', 'false');
                    }
                });
            }
            
            initDropdown();
        });
    </script>
</body>
</html>

