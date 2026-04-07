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
    <title>Đơn hàng của tôi - DC22V7N553</title>
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
        .order-container { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .order-header { border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 15px; }
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
          <form class="d-flex me-3 position-relative" role="search" action="index.php" method="GET">
            <input type="hidden" name="page" value="timkiem">
            <input class="form-control me-2" 
                   type="search" 
                   name="q" 
                   id="searchInput"
                   placeholder="Tìm kiếm sản phẩm..." 
                   autocomplete="off">
            <button class="btn btn-outline-primary" type="submit">Tìm</button>
            <div id="searchSuggestions" class="position-absolute top-100 start-0 w-100 bg-white border rounded shadow-lg mt-1" style="display: none; z-index: 1000; max-height: 300px; overflow-y: auto;"></div>
          </form>
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
        <h2 class="mb-4"><i class="bi bi-receipt"></i> Đơn hàng của tôi</h2>
        
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
        
        <?php if (empty($donHangList)): ?>
            <div class="order-container text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3">Chưa có đơn hàng nào</h4>
                <p class="text-muted">Bạn chưa đặt đơn hàng nào</p>
                <a href="index.php?page=trangchu" class="btn btn-primary mt-3">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <?php foreach ($donHangList as $dh): ?>
                <div class="order-container">
                    <div class="order-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-0">
                                    <i class="bi bi-receipt-cutoff"></i> Mã đơn: <strong class="text-primary"><?= htmlspecialchars($dh['ma_don']) ?></strong>
                                </h5>
                                <small class="text-muted">Ngày đặt: <?= date('d/m/Y H:i', strtotime($dh['ngay_dat'])) ?></small>
                            </div>
                            <div class="col-md-6 text-md-end">
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
                                <span class="badge <?= $badgeClass[$dh['trang_thai_don']] ?? 'bg-secondary' ?> fs-6">
                                    <?= $statusText[$dh['trang_thai_don']] ?? $dh['trang_thai_don'] ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <p class="mb-2"><strong>Địa chỉ giao hàng:</strong> <?= htmlspecialchars($dh['dia_chi_giao']) ?></p>
                            <p class="mb-2"><strong>SĐT:</strong> <?= htmlspecialchars($dh['so_dien_thoai_giao']) ?></p>
                            <?php if (!empty($dh['ghi_chu'])): ?>
                                <p class="mb-2"><strong>Ghi chú:</strong> <?= htmlspecialchars($dh['ghi_chu']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <p class="mb-2"><strong>Tổng tiền:</strong></p>
                            <h4 class="text-danger mb-3"><?= number_format($dh['tong_tien'], 0, ',', '.') ?> ₫</h4>
                            <a href="index.php?page=donhang_chi_tiet&id=<?= $dh['ma_don_hang'] ?>" class="btn btn-primary">
                                <i class="bi bi-eye"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-3">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=donhang_cua_toi&p=<?= $page - 1 ?>">Trước</a>
                            </li>
                        <?php endif; ?>
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=donhang_cua_toi&p=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=donhang_cua_toi&p=<?= $page + 1 ?>">Sau</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
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
            
            // Khởi tạo dropdown menu - đảm bảo hoạt động
            function initDropdown() {
                const dropdownBtn = document.getElementById('userDropdown');
                const dropdownMenu = dropdownBtn ? dropdownBtn.nextElementSibling : null;
                
                if (!dropdownBtn || !dropdownMenu) return;
                
                // Thử dùng Bootstrap trước
                if (typeof bootstrap !== 'undefined') {
                    try {
                        const dropdown = new bootstrap.Dropdown(dropdownBtn);
                        return;
                    } catch(e) {
                        console.log('Bootstrap dropdown failed, using fallback');
                    }
                }
                
                // Fallback: Toggle thủ công
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
                
                // Đóng khi click bên ngoài
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

