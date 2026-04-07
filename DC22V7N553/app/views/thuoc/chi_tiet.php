<?php
$rawImages = [
    $thuoc['hinh_anh'] ?? null,
    $thuoc['hinh_anh_2'] ?? null,
    $thuoc['hinh_anh_3'] ?? null,
    $thuoc['hinh_anh_4'] ?? null,
    $thuoc['hinh_anh_5'] ?? null,
];
$displayImages = array_values(array_filter($rawImages, function($url) {
    return !empty($url);
}));
// Sửa đường dẫn ảnh: thêm / ở đầu nếu chưa có
$displayImages = array_map(function($imgPath) {
    if (!empty($imgPath) && !preg_match('/^(https?:\/\/|\/)/', $imgPath)) {
        return '/' . $imgPath;
    }
    return $imgPath;
}, $displayImages);
$placeholderMain = 'https://dummyimage.com/500x500/f0f0f0/666&text=' . urlencode(mb_substr($thuoc['ten_thuoc'], 0, 20));
$placeholderThumb = 'https://dummyimage.com/80x80/f0f0f0/666&text=' . urlencode(mb_substr($thuoc['ten_thuoc'], 0, 10));
$mainImage = $displayImages[0] ?? $placeholderMain;
$thumbImages = $displayImages ?: [$placeholderThumb];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($thuoc['ten_thuoc']) ?> - DC22V7N553</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f9f9f9; }
        .pc-header { background: #007bdf; color: #fff; padding: 14px 0; }
        .pc-navbar { background: #fff; box-shadow: 0 2px 4px #e2e2e2; }
        .pc-footer { background: #023660; color: #fff; padding: 24px 0; margin-top: 24px; }
        .breadcrumb { background: transparent; padding: 12px 0; }
        .breadcrumb-item a { color: #007bdf; text-decoration: none; }
        .product-detail-container { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .product-image-main { width: 100%; height: 500px; object-fit: contain; background: #f8f9fa; border-radius: 8px; padding: 20px; }
        .product-thumbnails { display: flex; gap: 10px; margin-top: 15px; }
        .product-thumbnail { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #ddd; cursor: pointer; padding: 5px; }
        .product-thumbnail:hover, .product-thumbnail.active { border-color: #1956b2; }
        .product-info h1 { font-size: 1.5rem; font-weight: 700; color: #333; margin-bottom: 12px; }
        .product-code { color: #666; font-size: 0.9rem; margin-bottom: 12px; }
        .product-price { color: #dc3545; font-size: 1.8rem; font-weight: 700; margin: 16px 0; }
        .product-unit { font-size: 1rem; color: #666; margin-left: 8px; }
        .quantity-selector { display: flex; align-items: center; gap: 10px; margin: 20px 0; }
        .quantity-btn { width: 35px; height: 35px; border: 1px solid #ddd; background: #fff; border-radius: 4px; cursor: pointer; }
        .quantity-btn:hover { background: #f8f9fa; }
        .quantity-input { width: 60px; text-align: center; border: 1px solid #ddd; border-radius: 4px; padding: 8px; }
        .btn-buy-now { background: #1956b2; color: #fff; padding: 12px 24px; border-radius: 8px; font-weight: 600; border: none; }
        .btn-buy-now:hover { background: #023660; color: #fff; }
        .btn-add-cart { background: #fff; color: #1956b2; padding: 12px 24px; border-radius: 8px; font-weight: 600; border: 2px solid #1956b2; }
        .btn-add-cart:hover { background: #f8f9fa; color: #1956b2; }
        .service-icons { display: flex; gap: 20px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
        .service-icon { display: flex; align-items: center; gap: 8px; color: #28a745; }
        .product-specs { margin-top: 30px; }
        .spec-table { width: 100%; }
        .spec-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .spec-table td:first-child { font-weight: 600; width: 150px; color: #666; }
        .related-products { margin-top: 40px; }
        .related-product-card { 
            box-shadow: 0 2px 12px rgba(0,0,0,0.1); 
            border-radius: 12px; 
            padding: 0; 
            height: 100%; 
            background: #fff; 
            overflow: hidden; 
            transition: transform 0.3s, box-shadow 0.3s; 
            cursor: pointer;
        }
        .related-product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .related-product-image-wrapper {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .related-product-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        .related-product-card:hover .related-product-image-wrapper img {
            transform: scale(1.05);
        }
        .related-product-info {
            padding: 16px;
        }
        .related-product-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            min-height: 48px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .related-product-price {
            color: #dc3545;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 12px;
        }
        .related-product-unit {
            font-size: 0.85rem;
            color: #666;
            margin-left: 4px;
        }
        .related-product-btn {
            width: 100%;
            background: #1956b2;
            color: #fff !important;
            border: none;
            padding: 10px;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s;
            cursor: pointer;
            text-decoration: none;
            display: block;
        }
        .related-product-btn:hover {
            background: #023660;
            color: #fff !important;
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
            <?php if (!empty($danhMucList)): ?>
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
          <form class="d-flex me-3" role="search">
            <input class="form-control me-2" type="search" placeholder="Tìm kiếm sản phẩm...">
            <button class="btn btn-outline-primary" type="submit">Tìm</button>
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

    <!-- Breadcrumb -->
    <div class="container mt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?page=trangchu">Trang chủ</a></li>
                <?php if ($danhMucCha): ?>
                    <li class="breadcrumb-item"><a href="index.php?page=trangchu&danhmuc=<?= $danhMucCha['ma_danh_muc'] ?>"><?= htmlspecialchars($danhMucCha['ten_danh_muc']) ?></a></li>
                <?php endif; ?>
                <?php if ($danhMucHienTai): ?>
                    <li class="breadcrumb-item"><a href="index.php?page=trangchu&danhmuc=<?= $danhMucHienTai['ma_danh_muc'] ?>"><?= htmlspecialchars($danhMucHienTai['ten_danh_muc']) ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($thuoc['ten_thuoc']) ?></li>
            </ol>
        </nav>
    </div>

    <!-- Product Detail -->
    <div class="container mt-4 mb-5">
        <div class="product-detail-container">
            <div class="row">
                <!-- Left: Product Images -->
                <div class="col-md-6">
                    <div class="product-image-wrapper">
                        <img id="mainProductImage" 
                             src="<?= htmlspecialchars($mainImage) ?>" 
                             alt="<?= htmlspecialchars($thuoc['ten_thuoc']) ?>" 
                             class="product-image-main">
                    </div>
                    <div class="product-thumbnails">
                        <?php foreach ($thumbImages as $index => $img): ?>
                            <img src="<?= htmlspecialchars($img) ?>" 
                                 alt="Thumbnail <?= $index + 1 ?>" 
                                 class="product-thumbnail <?= $index === 0 ? 'active' : '' ?>"
                                 onclick="changeMainImage(this.src)">
                        <?php endforeach; ?>
                    </div>
                    <p class="text-muted mt-3" style="font-size: 0.85rem;">
                        <i class="bi bi-shield-check text-success"></i> Sản phẩm 100% chính hãng, mẫu mã có thể thay đổi theo lô hàng
                    </p>
                </div>

                <!-- Right: Product Info -->
                <div class="col-md-6">
                    <h1 class="product-info"><?= htmlspecialchars($thuoc['ten_thuoc']) ?></h1>
                    <div class="product-code">
                        Mã sản phẩm: <strong>P<?= str_pad($thuoc['ma_thuoc'], 5, '0', STR_PAD_LEFT) ?></strong>
                        <?php if ($danhMucHienTai): ?>
                            - Danh mục: <strong><?= htmlspecialchars($danhMucHienTai['ten_danh_muc']) ?></strong>
                        <?php endif; ?>
                    </div>

                    <div class="product-price">
                        <?php if ($co_sale): ?>
                            <div style="text-decoration: line-through; color: #999; font-size: 1rem; margin-bottom: 5px;">
                                <?= number_format($thuoc['gia'], 0, ',', '.') ?> ₫
                            </div>
                            <div style="color: #dc3545; font-size: 1.8rem; font-weight: 700;">
                                <?= number_format($gia_hien_tai, 0, ',', '.') ?> ₫<span class="product-unit">/<?= htmlspecialchars($thuoc['don_vi']) ?></span>
                            </div>
                            <div style="background: #dc3545; color: #fff; display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 0.9rem; font-weight: 600; margin-top: 8px;">
                                Giảm <?= number_format($phan_tram_giam, 0) ?>%
                            </div>
                        <?php else: ?>
                        <?= number_format($thuoc['gia'], 0, ',', '.') ?> ₫<span class="product-unit">/<?= htmlspecialchars($thuoc['don_vi']) ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted small">Giá đã bao gồm thuế. Phí vận chuyển và các chi phí khác (nếu có) sẽ được thể hiện khi đặt hàng.</p>

                    <?php if ($thuoc['so_luong_ton'] <= 10): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Chỉ còn <?= $thuoc['so_luong_ton'] ?> sản phẩm trong kho
                        </div>
                    <?php endif; ?>

                    <!-- Quantity Selector -->
                    <div class="quantity-selector">
                        <label class="me-2"><strong>Số lượng:</strong></label>
                        <button class="quantity-btn" onclick="decreaseQuantity()">-</button>
                        <input type="number" id="quantity" class="quantity-input" value="1" min="1" max="<?= $thuoc['so_luong_ton'] ?>">
                        <button class="quantity-btn" onclick="increaseQuantity()">+</button>
                        <span class="ms-3 text-muted">(Còn <?= number_format($thuoc['so_luong_ton']) ?> sản phẩm)</span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-3">
                        <button class="btn-buy-now flex-fill" onclick="buyNow()">
                            <i class="bi bi-bag-check"></i> Mua ngay
                        </button>
                        <button class="btn-add-cart flex-fill" onclick="addToCart()">
                            <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                        </button>
                    </div>

                    <!-- Service Icons -->
                    <div class="service-icons">
                        <div class="service-icon">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Đủ thuốc chuẩn</span>
                        </div>
                        <div class="service-icon">
                            <i class="bi bi-truck"></i>
                            <span>Giao hàng siêu tốc</span>
                        </div>
                        <div class="service-icon">
                            <i class="bi bi-truck"></i>
                            <span>Miễn phí vận chuyển</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Details -->
            <div class="product-specs mt-5">
                <h4 class="mb-4">Thông tin sản phẩm</h4>
                <table class="spec-table">
                    <tr>
                        <td>Danh mục</td>
                        <td><?= htmlspecialchars($danhMucHienTai['ten_danh_muc'] ?? 'Chưa phân loại') ?></td>
                    </tr>
                    <tr>
                        <td>Quy cách</td>
                        <td><?= htmlspecialchars($thuoc['don_vi']) ?></td>
                    </tr>
                    <tr>
                        <td>Số lượng tồn</td>
                        <td><?= number_format($thuoc['so_luong_ton']) ?> <?= htmlspecialchars($thuoc['don_vi']) ?></td>
                    </tr>
                    <?php if ($thuoc['han_su_dung']): ?>
                    <tr>
                        <td>Hạn sử dụng</td>
                        <td><?= date('d/m/Y', strtotime($thuoc['han_su_dung'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($thuoc['mo_ta']): ?>
                    <tr>
                        <td>Mô tả</td>
                        <td><?= nl2br(htmlspecialchars($thuoc['mo_ta'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($thuoc['huong_dan_dung']): ?>
                    <tr>
                        <td>Hướng dẫn sử dụng</td>
                        <td><?= nl2br(htmlspecialchars($thuoc['huong_dan_dung'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($thuoc['lieu_dung']): ?>
                    <tr>
                        <td>Liều dùng</td>
                        <td><?= nl2br(htmlspecialchars($thuoc['lieu_dung'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($thuoc['chong_chi_dinh']): ?>
                    <tr>
                        <td>Chống chỉ định</td>
                        <td><?= nl2br(htmlspecialchars($thuoc['chong_chi_dinh'])) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i> <strong>Lưu ý:</strong> Thực phẩm này không phải là thuốc, không có tác dụng thay thế thuốc chữa bệnh.
                </div>
            </div>
        </div>
    </div>

    <!-- Sản phẩm liên quan -->
    <?php if (!empty($sanPhamLienQuan)): ?>
    <div class="container mt-5 mb-5">
        <div class="related-products">
            <h3 class="mb-4" style="font-weight: 700; color: #1956b2;">
                <i class="bi bi-grid-3x3-gap"></i> Sản phẩm liên quan
            </h3>
            <div class="row g-4">
                <?php foreach ($sanPhamLienQuan as $sp): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="related-product-card" onclick="window.location.href='index.php?action=chi_tiet&id=<?= $sp['ma_thuoc'] ?>'">
                            <div class="related-product-image-wrapper">
                                <?php
                                    $thumbUrl = null;
                                    $fields = ['hinh_anh', 'hinh_anh_2', 'hinh_anh_3', 'hinh_anh_4', 'hinh_anh_5'];
                                    foreach ($fields as $field) {
                                        if (!empty($sp[$field])) {
                                            $thumbUrl = $sp[$field];
                                            break;
                                        }
                                    }
                                    if (!$thumbUrl) {
                                        $thumbUrl = 'https://dummyimage.com/200x200/f0f0f0/666';
                                    }
                                ?>
                                <img src="<?= htmlspecialchars($thumbUrl) ?>" 
                                     alt="<?= htmlspecialchars($sp['ten_thuoc']) ?>">
                            </div>
                            <div class="related-product-info">
                                <div class="related-product-name">
                                    <?= htmlspecialchars($sp['ten_thuoc']) ?>
                                </div>
                                <div class="related-product-price">
                                    <?= number_format($sp['gia'], 0, ',', '.') ?> ₫
                                    <span class="related-product-unit">/ <?= htmlspecialchars($sp['don_vi']) ?></span>
                                </div>
                                <button class="related-product-btn" 
                                        onclick="event.stopPropagation(); window.location.href='index.php?action=chi_tiet&id=<?= $sp['ma_thuoc'] ?>'">
                                    Chọn sản phẩm
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="pc-footer mt-5">
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
        function changeMainImage(src) {
            document.getElementById('mainProductImage').src = src;
            document.querySelectorAll('.product-thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        function decreaseQuantity() {
            const input = document.getElementById('quantity');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }

        function increaseQuantity() {
            const input = document.getElementById('quantity');
            const max = <?= $thuoc['so_luong_ton'] ?>;
            if (parseInt(input.value) < max) {
                input.value = parseInt(input.value) + 1;
            }
        }

        function buyNow() {
            const quantity = parseInt(document.getElementById('quantity').value);
            const ma_thuoc = <?= $thuoc['ma_thuoc'] ?>;
            
            if (quantity <= 0) {
                alert('Số lượng phải lớn hơn 0');
                return;
            }
            
            if (quantity > <?= $thuoc['so_luong_ton'] ?>) {
                alert('Số lượng không được vượt quá số lượng tồn kho');
                return;
            }

            // Thêm vào giỏ hàng với số lượng đã chọn
            const formData = new FormData();
            formData.append('ma_thuoc', ma_thuoc);
            formData.append('so_luong', quantity);

            fetch('index.php?action=cart_add', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Server returned non-JSON:', text);
                        throw new Error('Server returned non-JSON response');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Chuyển đến trang checkout
                    window.location.href = 'index.php?page=checkout';
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi thêm vào giỏ hàng');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi thêm vào giỏ hàng');
            });
        }

        function addToCart() {
            const quantity = parseInt(document.getElementById('quantity').value);
            const ma_thuoc = <?= $thuoc['ma_thuoc'] ?>;
            
            if (quantity <= 0) {
                alert('Số lượng phải lớn hơn 0');
                return;
            }

            // Gửi request thêm vào giỏ hàng
            const formData = new FormData();
            formData.append('ma_thuoc', ma_thuoc);
            formData.append('so_luong', quantity);

            fetch('index.php?action=cart_add', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Kiểm tra Content-Type header
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Server returned non-JSON. Content-Type:', contentType);
                        console.error('Response text:', text);
                        throw new Error('Server trả về dữ liệu không hợp lệ. Response: ' + text.substring(0, 200));
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    // Cập nhật số lượng giỏ hàng
                    updateCartCount();
                    alert(data.message || 'Đã thêm sản phẩm vào giỏ hàng!');
                } else {
                    alert(data && data.message ? data.message : 'Có lỗi xảy ra. Vui lòng thử lại.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                console.error('Error details:', error.message);
                alert('Có lỗi xảy ra khi kết nối đến server: ' + error.message);
            });
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

        // Load số lượng giỏ hàng khi trang load
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
                        return; // Thành công, không cần fallback
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
            
            // Chạy ngay và sau khi DOM ready
            initDropdown();
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initDropdown);
            }
        });
    </script>
</body>
</html>

