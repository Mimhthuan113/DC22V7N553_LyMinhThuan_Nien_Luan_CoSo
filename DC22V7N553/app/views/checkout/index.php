<?php
if (!function_exists('layAnhChinhThuoc')) {
    function layAnhChinhThuoc($thuoc, $size = '80x80') {
        $fields = ['hinh_anh', 'hinh_anh_2', 'hinh_anh_3', 'hinh_anh_4', 'hinh_anh_5'];
        foreach ($fields as $field) {
            if (!empty($thuoc[$field])) {
                $imgPath = $thuoc[$field];
                // Nếu đường dẫn không bắt đầu bằng http hoặc /, thêm / ở đầu
                if (!preg_match('/^(https?:\/\/|\/)/', $imgPath)) {
                    $imgPath = '/' . $imgPath;
                }
                return $imgPath;
            }
        }
        $text = isset($thuoc['ten_thuoc']) ? mb_substr($thuoc['ten_thuoc'], 0, 20) : 'No Image';
        return "https://dummyimage.com/{$size}/f0f0f0/666&text=" . urlencode($text);
    }
}
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Session.php';

// Kiểm tra biến $sanPham đã được truyền từ controller chưa
if (!isset($sanPham) || empty($sanPham)) {
    Session::setFlash('error', 'Giỏ hàng của bạn đang trống');
    header('Location: index.php?page=giohang');
    exit;
}

$user = Auth::user();
if (!$user) {
    Session::setFlash('error', 'Vui lòng đăng nhập để thanh toán');
    header('Location: index.php?page=login');
    exit;
}

$tongTien = 0;
foreach ($sanPham as $sp) {
    $tongTien += $sp['thanh_tien'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - DC22V7N553</title>
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
        .checkout-container { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .product-image { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
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
        <h2 class="mb-4"><i class="bi bi-cart-check"></i> Thanh toán</h2>
        
        <?php 
        $error = Session::getFlash('error');
        $errors = Session::getFlash('errors');
        if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($errors && is_array($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="checkout-container mb-4">
                    <h4 class="mb-3">Thông tin giao hàng</h4>
                    <form method="POST" action="index.php?action=checkout_dat_hang">
                        <div class="mb-3">
                            <label class="form-label">Họ tên người nhận <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ho_ten" value="<?= htmlspecialchars($user['name']) ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="dia_chi_giao" rows="3" required placeholder="Nhập địa chỉ giao hàng chi tiết"><?= htmlspecialchars($user['dia_chi'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="so_dien_thoai_giao" value="<?= htmlspecialchars($user['so_dien_thoai'] ?? '') ?>" required placeholder="Nhập số điện thoại">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ghi chú đơn hàng</label>
                            <textarea class="form-control" name="ghi_chu" rows="3" placeholder="Ghi chú cho người giao hàng (tùy chọn)"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hình thức thanh toán</label>
                            <div class="form-control bg-light">
                                <i class="bi bi-cash-coin text-success"></i> Thanh toán khi nhận hàng (COD)
                            </div>
                        </div>
                        
                        <h4 class="mt-4 mb-3">Sản phẩm đặt mua</h4>
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
                                    <?php foreach ($sanPham as $sp): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= htmlspecialchars(layAnhChinhThuoc($sp)) ?>" alt="<?= htmlspecialchars($sp['ten_thuoc']) ?>" class="product-image me-2">
                                                    <div>
                                                        <strong><?= htmlspecialchars($sp['ten_thuoc']) ?></strong><br>
                                                        <small class="text-muted"><?= htmlspecialchars($sp['don_vi']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= $sp['so_luong'] ?></td>
                                            <td><?= number_format($sp['don_gia'], 0, ',', '.') ?> ₫</td>
                                            <td><strong><?= number_format($sp['thanh_tien'], 0, ',', '.') ?> ₫</strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="btnConfirmOrder" onclick="return confirmOrder(event)">
                                <i class="bi bi-check-circle"></i> Xác nhận đặt hàng
                            </button>
                            <a href="index.php?page=giohang" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-arrow-left"></i> Quay lại giỏ hàng
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="checkout-container">
                    <h5 class="mb-3">Tóm tắt đơn hàng</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <strong><?= number_format($tongTien, 0, ',', '.') ?> ₫</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Phí vận chuyển:</span>
                        <strong>Miễn phí</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Tổng cộng:</strong>
                        <strong class="text-danger fs-5"><?= number_format($tongTien, 0, ',', '.') ?> ₫</strong>
                    </div>
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
        function confirmOrder(event) {
            event.preventDefault();
            
            // Hiển thị dialog xác nhận
            if (confirm('Bạn có chắc chắn muốn đặt hàng không?')) {
                // Nếu xác nhận, submit form
                const form = event.target.closest('form');
                if (form) {
                    form.submit();
                }
            }
            return false;
        }
        
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

