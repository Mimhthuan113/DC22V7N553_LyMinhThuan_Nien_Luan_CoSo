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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - DC22V7N553</title>
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
        .cart-container { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .cart-item { border-bottom: 1px solid #eee; padding: 20px 0; }
        .cart-item:last-child { border-bottom: none; }
        .product-image { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; }
        .quantity-control { display: flex; align-items: center; gap: 10px; }
        .quantity-btn { width: 30px; height: 30px; border: 1px solid #ddd; background: #fff; border-radius: 4px; cursor: pointer; }
        .quantity-input { width: 60px; text-align: center; border: 1px solid #ddd; border-radius: 4px; padding: 5px; }
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
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#fff" class="bi bi-cart" viewBox="0 0 16 16">
              <path d="M0 1.5A.5.5 0 0 1 .5 1h1a.5.5 0 0 1 .485.379L2.89 5H14.5a.5.5 0 0 1 .49.598l-1.5 7A.5.5 0 0 1 13 13H4a.5.5 0 0 1-.491-.408L1.01 2H.5a.5.5 0 0 1-.5-.5zM3.102 6l1.313 6h8.17l1.313-6H3.102z"/>
            </svg>
            <span id="cartBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none; font-size: 0.7rem; min-width: 18px; height: 18px; line-height: 18px; padding: 0 4px;">0</span>
          </a>
          <?php
          require_once __DIR__ . '/../../core/Auth.php';
          $user = Auth::user();
          if ($user): ?>
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
    <!-- Cart Content -->
    <div class="container mt-4 mb-5">
        <h2 class="mb-4">Giỏ hàng của tôi</h2>
        
        <?php if (empty($sanPhamTrongGio)): ?>
            <div class="cart-container text-center py-5">
                <i class="bi bi-cart-x" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3">Giỏ hàng trống</h4>
                <p class="text-muted">Bạn chưa có sản phẩm nào trong giỏ hàng</p>
                <a href="index.php?page=trangchu" class="btn btn-primary mt-3">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="cart-container">
                        <?php foreach ($sanPhamTrongGio as $sp): ?>
                            <div class="cart-item">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <?php $thumb = layAnhChinhThuoc($sp, '100x100'); ?>
                                        <img src="<?= htmlspecialchars($thumb) ?>" 
                                             alt="<?= htmlspecialchars($sp['ten_thuoc']) ?>" 
                                             class="product-image">
                                    </div>
                                    <div class="col-md-4">
                                        <h5 class="mb-1"><?= htmlspecialchars($sp['ten_thuoc']) ?></h5>
                                        <small class="text-muted"><?= htmlspecialchars($sp['don_vi']) ?></small>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="quantity-control">
                                            <button class="quantity-btn" onclick="updateQuantity(<?= $sp['ma_chi_tiet'] ?>, -1)">-</button>
                                            <input type="number" 
                                                   id="qty_<?= $sp['ma_chi_tiet'] ?>" 
                                                   class="quantity-input" 
                                                   value="<?= $sp['so_luong'] ?>" 
                                                   min="1" 
                                                   max="<?= $sp['so_luong_ton'] ?>"
                                                   onchange="updateQuantity(<?= $sp['ma_chi_tiet'] ?>, 0, this.value)">
                                            <button class="quantity-btn" onclick="updateQuantity(<?= $sp['ma_chi_tiet'] ?>, 1)">+</button>
                                        </div>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <strong class="text-danger"><?= number_format($sp['thanh_tien'], 0, ',', '.') ?> ₫</strong>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <button class="btn btn-sm btn-outline-danger" onclick="removeItem(<?= $sp['ma_chi_tiet'] ?>)">
                                            <i class="bi bi-trash"></i> Xóa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="cart-container">
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
                        <button class="btn btn-primary w-100 btn-lg" onclick="checkout()">
                            <i class="bi bi-bag-check"></i> Thanh toán
                        </button>
                        <a href="index.php?page=trangchu" class="btn btn-outline-secondary w-100 mt-2">
                            Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            </div>
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
        function updateQuantity(ma_chi_tiet, change, newValue = null) {
            const input = document.getElementById('qty_' + ma_chi_tiet);
            let quantity = parseInt(input.value);
            
            if (newValue !== null) {
                quantity = parseInt(newValue);
            } else {
                quantity += change;
            }
            
            if (quantity < 1) {
                quantity = 1;
            }
            
            const max = parseInt(input.getAttribute('max'));
            if (quantity > max) {
                alert('Số lượng không được vượt quá ' + max);
                quantity = max;
            }
            
            // Gửi request cập nhật
            const formData = new FormData();
            formData.append('ma_chi_tiet', ma_chi_tiet);
            formData.append('so_luong', quantity);
            
            fetch('index.php?action=cart_update', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi cập nhật số lượng');
            });
        }
        
        function removeItem(ma_chi_tiet) {
            if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('ma_chi_tiet', ma_chi_tiet);
            
            fetch('index.php?action=cart_remove', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xóa sản phẩm');
            });
        }
        
        function checkout() {
            window.location.href = 'index.php?page=checkout';
        }
        
        // Update cart count
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
        
        // Load số lượng giỏ hàng khi trang load
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

