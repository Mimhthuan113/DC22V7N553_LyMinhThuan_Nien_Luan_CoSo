<?php
require_once __DIR__ . '/../../core/Auth.php';
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công - DC22V7N553</title>
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
        .success-container { 
            background: #fff; 
            border-radius: 12px; 
            padding: 40px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .success-icon i {
            font-size: 50px;
            color: #fff;
        }
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
          <?php if (Auth::isLoggedIn()): ?>
            <div class="dropdown">
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
    <div class="container my-5">
        <div class="success-container">
            <div class="success-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h2 class="text-success mb-3">Đặt hàng thành công!</h2>
            <p class="lead mb-4">Cảm ơn bạn đã đặt hàng tại DC22V7N553</p>
            
            <div class="card mb-4">
                <div class="card-body text-start">
                    <h5 class="card-title mb-3">Thông tin đơn hàng</h5>
                    <p><strong>Mã đơn hàng:</strong> <span class="text-primary"><?= htmlspecialchars($donHang['ma_don']) ?></span></p>
                    <p><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($donHang['ngay_dat'])) ?></p>
                    <p><strong>Địa chỉ giao hàng:</strong> <?= htmlspecialchars($donHang['dia_chi_giao']) ?></p>
                    <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($donHang['so_dien_thoai_giao']) ?></p>
                    <p><strong>Hình thức thanh toán:</strong> Thanh toán khi nhận hàng (COD)</p>
                    <p><strong>Tổng tiền:</strong> <span class="text-danger fs-5"><?= number_format($donHang['tong_tien'], 0, ',', '.') ?> ₫</span></p>
                    <?php if (!empty($donHang['ghi_chu'])): ?>
                        <p><strong>Ghi chú:</strong> <?= htmlspecialchars($donHang['ghi_chu']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Chi tiết đơn hàng</h5>
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
                                        <td><?= htmlspecialchars($ct['ten_thuoc']) ?> (<?= htmlspecialchars($ct['don_vi']) ?>)</td>
                                        <td><?= $ct['so_luong'] ?></td>
                                        <td><?= number_format($ct['don_gia'], 0, ',', '.') ?> ₫</td>
                                        <td><strong><?= number_format($ct['thanh_tien'], 0, ',', '.') ?> ₫</strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2 justify-content-center">
                <a href="index.php?page=trangchu" class="btn btn-primary btn-lg">
                    <i class="bi bi-house"></i> Về trang chủ
                </a>
                <a href="index.php?page=account" class="btn btn-outline-primary btn-lg">
                    <i class="bi bi-person"></i> Xem đơn hàng của tôi
                </a>
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

