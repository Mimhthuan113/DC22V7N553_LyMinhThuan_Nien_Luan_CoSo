<?php
if (!function_exists('layAnhChinhThuoc')) {
    function layAnhChinhThuoc($thuoc, $size = '300x200') {
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
if (!function_exists('layAnhChinhTinTuc')) {
    function layAnhChinhTinTuc($tin, $size = '300x200') {
        $fields = ['hinh_anh', 'hinh_anh_2', 'hinh_anh_3', 'hinh_anh_4', 'hinh_anh_5'];
        foreach ($fields as $field) {
            if (!empty($tin[$field])) {
                return $tin[$field];
            }
        }
        $text = isset($tin['tieu_de']) ? mb_substr($tin['tieu_de'], 0, 20) : 'No Image';
        return "https://dummyimage.com/{$size}/f0f0f0/666&text=" . urlencode($text);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DC22V7N553 - Trang chủ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { background: #f9f9f9; }
        .pc-header { 
            background: linear-gradient(135deg, #023660 0%, #1956b2 50%, #007bdf 100%);
            color: #fff; 
            padding: 20px 0;
            box-shadow: 0 4px 12px rgba(2, 54, 96, 0.3);
            position: relative;
            overflow: hidden;
        }
        .pc-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        .pc-header-content {
            position: relative;
            z-index: 1;
        }
        .pc-header h2 {
            font-weight: 800;
            font-size: 2.2rem;
            letter-spacing: 2px;
            margin-bottom: 8px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .pc-header p {
            margin: 0;
            font-size: 1rem;
            font-weight: 500;
            letter-spacing: 1px;
            opacity: 0.95;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.15);
        }
        .pc-navbar { background: #fff; box-shadow: 0 2px 4px #e2e2e2; }
        .pc-banner { background: transparent; padding: 12px 0 0 0; }
        .pc-banner .banner-wrapper { max-width: 1200px; margin: 0 auto; }
        .pc-banner .carousel-item img { width: 100%; height: auto; max-height: 430px; object-fit: contain; }
        .pc-banner .carousel-indicators button { width: 10px; height: 10px; border-radius: 50%; }
        .product-card { 
            box-shadow: 0 2px 12px rgba(0,0,0,0.1); 
            border-radius: 12px; 
            padding: 0; 
            height: 100%; 
            background: #fff;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .product-image-wrapper {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .product-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .product-info {
            padding: 16px;
        }
        .product-name {
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
        .product-price {
            color: #dc3545;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 12px;
        }
        .product-unit {
            font-size: 0.85rem;
            color: #666;
            margin-left: 4px;
        }
        .product-btn {
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
        .product-btn:hover {
            background: #023660;
            color: #fff !important;
        }
        .flash-sale { background: #f64242; color: #fff; font-weight: 500; padding: 10px 0; text-align: center; }
        .pc-footer { background: #023660; color: #fff; padding: 24px 0; margin-top: 24px; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="pc-header">
        <div class="pc-header-content text-center">
            <h2>DC22V7N553</h2>
            <p>Uy tín - Nhanh chóng - Chính hãng</p>
        </div>
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
              <?php 
              $danhmucHienTai = isset($_GET['danhmuc']) ? (int)$_GET['danhmuc'] : 0;
              foreach ($danhMucList as $dm): ?>
                <?php if ($dm['ma_danh_muc_cha'] === null && $dm['trang_thai'] == 1): ?>
                  <li class="nav-item">
                    <a class="nav-link <?= $danhmucHienTai == $dm['ma_danh_muc'] ? 'active fw-bold' : '' ?>" 
                       href="index.php?page=trangchu&danhmuc=<?= $dm['ma_danh_muc'] ?>">
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
                   autocomplete="off"
                   value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
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
    <!-- Banner -->
    <?php
        $bannerSlides = [];
        if (!empty($banners)) {
            foreach ($banners as $bn) {
                $imgs = [];
                $fields = ['hinh_anh','hinh_anh_2','hinh_anh_3','hinh_anh_4','hinh_anh_5'];
                foreach ($fields as $f) {
                    if (!empty($bn[$f])) $imgs[] = $bn[$f];
                }
                if (empty($imgs)) continue;
                foreach ($imgs as $img) {
                    $bannerSlides[] = [
                        'src' => $img,
                        'title' => $bn['tieu_de'] ?? '',
                    ];
                }
            }
        }
    ?>
    <?php if (!empty($bannerSlides)): ?>
    <section class="pc-banner container-fluid px-0 mt-2">
        <div class="banner-wrapper">
        <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php foreach ($bannerSlides as $idx => $_): ?>
                    <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="<?= $idx ?>" class="<?= $idx === 0 ? 'active' : '' ?>" aria-current="<?= $idx === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $idx + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner">
                <?php foreach ($bannerSlides as $idx => $slide): ?>
                    <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                        <img src="<?= htmlspecialchars($slide['src']) ?>" class="d-block w-100" alt="<?= htmlspecialchars($slide['title'] ?: 'banner') ?>">
                        <?php if (!empty($slide['title'])): ?>
                            <div class="carousel-caption d-none d-md-block">
                                <h5><?= htmlspecialchars($slide['title']) ?></h5>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($bannerSlides) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
            <?php endif; ?>
        </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Flash Sales Section -->
    <?php if (!empty($saleList)): 
        // Lấy sale đang diễn ra (có thể có nhiều sale với thời gian khác nhau)
        $saleDangDienRa = [];
        $saleSapDienRa = [];
        $now = time();
        
        foreach ($saleList as $sale) {
            $startTime = strtotime($sale['thoi_gian_bat_dau']);
            $endTime = strtotime($sale['thoi_gian_ket_thuc']);
            
            if ($now >= $startTime && $now <= $endTime) {
                $saleDangDienRa[] = $sale;
            } elseif ($now < $startTime) {
                $saleSapDienRa[] = $sale;
            }
        }
        
        // Lấy sale đang diễn ra đầu tiên để hiển thị countdown
        $saleHienTai = !empty($saleDangDienRa) ? $saleDangDienRa[0] : (!empty($saleSapDienRa) ? $saleSapDienRa[0] : null);
        $saleToShow = !empty($saleDangDienRa) ? $saleDangDienRa : array_slice($saleSapDienRa, 0, 10);
        
        // Lấy ngày bắt đầu và kết thúc của sale đang diễn ra
        $ngayBatDau = !empty($saleDangDienRa) ? date('d/m', strtotime($saleDangDienRa[0]['thoi_gian_bat_dau'])) : date('d/m');
        $ngayKetThuc = !empty($saleDangDienRa) ? date('d/m', strtotime($saleDangDienRa[0]['thoi_gian_ket_thuc'])) : date('d/m');
        $gioBatDau = !empty($saleDangDienRa) ? date('H:i', strtotime($saleDangDienRa[0]['thoi_gian_bat_dau'])) : '00:00';
        $gioKetThuc = !empty($saleDangDienRa) ? date('H:i', strtotime($saleDangDienRa[0]['thoi_gian_ket_thuc'])) : '23:59';
        
        // Lấy ngày của sale sắp diễn ra
        $ngaySapDienRa = !empty($saleSapDienRa) ? date('d/m', strtotime($saleSapDienRa[0]['thoi_gian_bat_dau'])) : '';
        $gioSapDienRaBatDau = !empty($saleSapDienRa) ? date('H:i', strtotime($saleSapDienRa[0]['thoi_gian_bat_dau'])) : '00:00';
        $gioSapDienRaKetThuc = !empty($saleSapDienRa) ? date('H:i', strtotime($saleSapDienRa[0]['thoi_gian_ket_thuc'])) : '23:59';
    ?>
    <div class="flash-sale-section mt-4 mb-5" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); padding: 30px 0; box-shadow: 0 8px 24px rgba(220, 53, 69, 0.3); width: 100%;">
        <div class="container">
        <!-- Header với timeline -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div class="d-flex gap-2">
                <div class="timeline-box active" style="background: rgba(255,255,255,0.2); padding: 12px 20px; border-radius: 10px; color: #fff; cursor: pointer;">
                    <div class="fw-bold"><i class="bi bi-lightning-charge-fill"></i> Đang diễn ra</div>
                    <small><?= $ngayBatDau ?> - <?= $ngayKetThuc ?></small>
                    <div style="font-size: 0.75rem; opacity: 0.9;"><?= $gioBatDau ?> - <?= $gioKetThuc ?></div>
                </div>
                <?php if (!empty($saleSapDienRa)): ?>
                <div class="timeline-box" style="background: rgba(255,255,255,0.1); padding: 12px 20px; border-radius: 10px; color: #fff; cursor: pointer;">
                    <div class="fw-bold">Sắp diễn ra</div>
                    <small><?= $ngaySapDienRa ?></small>
                    <div style="font-size: 0.75rem; opacity: 0.9;"><?= $gioSapDienRaBatDau ?> - <?= $gioSapDienRaKetThuc ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($saleHienTai): 
                $endTime = strtotime($saleHienTai['thoi_gian_ket_thuc']);
            ?>
            <div class="countdown-timer" style="background: rgba(255,255,255,0.2); padding: 12px 20px; border-radius: 10px; color: #fff;">
                <span class="fw-bold mb-2 d-block" style="font-size: 0.9rem;">Kết thúc sau:</span>
                <div id="countdown" class="d-flex gap-2 align-items-center" style="font-size: 1.1rem;">
                    <div class="countdown-item">
                        <div class="countdown-value" id="countdown-days">00</div>
                        <div class="countdown-label" style="font-size: 0.7rem; opacity: 0.8;">Ngày</div>
                    </div>
                    <span style="font-size: 1.5rem; opacity: 0.7;">:</span>
                    <div class="countdown-item">
                        <div class="countdown-value" id="countdown-hours">00</div>
                        <div class="countdown-label" style="font-size: 0.7rem; opacity: 0.8;">Giờ</div>
                    </div>
                    <span style="font-size: 1.5rem; opacity: 0.7;">:</span>
                    <div class="countdown-item">
                        <div class="countdown-value" id="countdown-minutes">00</div>
                        <div class="countdown-label" style="font-size: 0.7rem; opacity: 0.8;">Phút</div>
                    </div>
                    <span style="font-size: 1.5rem; opacity: 0.7;">:</span>
                    <div class="countdown-item">
                        <div class="countdown-value" id="countdown-seconds">00</div>
                        <div class="countdown-label" style="font-size: 0.7rem; opacity: 0.8;">Giây</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Category tabs -->
        <div class="d-flex gap-2 mb-4 flex-wrap" id="categoryTabs">
            <?php if (!empty($danhMucCap2)): ?>
                <?php foreach ($danhMucCap2 as $index => $dm): ?>
                    <button class="btn category-tab-btn <?= $index === 0 ? 'btn-light active' : 'btn-outline-light' ?>" 
                            style="border-radius: 20px; font-weight: 600;"
                            data-category-id="<?= $dm['ma_danh_muc'] ?>"
                            onclick="filterSaleByCategory(<?= $dm['ma_danh_muc'] ?>, this)">
                        <?= htmlspecialchars($dm['ten_danh_muc']) ?>
                    </button>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback nếu không có danh mục cấp 2 -->
                <button class="btn btn-light btn-sm active" style="border-radius: 20px; font-weight: 600;">
                    Tất cả sản phẩm sale
                </button>
            <?php endif; ?>
        </div>
        
        <!-- Products carousel -->
        <div class="sale-products-wrapper" style="position: relative;">
            <div class="sale-products-scroll" id="saleProductsScroll" style="display: flex; gap: 20px; overflow-x: auto; padding: 10px 0; scroll-behavior: smooth;">
                <?php 
                // Lưu tất cả sale vào JavaScript để filter
                $allSalesJson = json_encode($saleList);
                ?>
                <script>
                    const allSales = <?= $allSalesJson ?>;
                    let currentCategoryId = null;
                    
                    function filterSaleByCategory(categoryId, buttonElement = null) {
                        currentCategoryId = categoryId;
                        
                        // Update active tab
                        document.querySelectorAll('.category-tab-btn').forEach(btn => {
                            btn.classList.remove('btn-light', 'active');
                            btn.classList.add('btn-outline-light');
                        });
                        
                        if (buttonElement) {
                            buttonElement.classList.remove('btn-outline-light');
                            buttonElement.classList.add('btn-light', 'active');
                        } else {
                            // Tìm button theo data-category-id
                            const btn = document.querySelector(`.category-tab-btn[data-category-id="${categoryId}"]`);
                            if (btn) {
                                btn.classList.remove('btn-outline-light');
                                btn.classList.add('btn-light', 'active');
                            }
                        }
                        
                        // Filter products
                        const filteredSales = categoryId ? 
                            allSales.filter(sale => sale.ma_danh_muc == categoryId) : 
                            allSales;
                        
                        // Render products
                        renderSaleProducts(filteredSales);
                    }
                    
                    function renderSaleProducts(sales) {
                        const container = document.getElementById('saleProductsScroll');
                        if (sales.length === 0) {
                            container.innerHTML = '<div class="text-center text-white w-100 py-5">Không có sản phẩm sale trong danh mục này</div>';
                            return;
                        }
                        
                        container.innerHTML = sales.map(sale => {
                            const giaGoc = parseFloat(sale.gia_goc).toLocaleString('vi-VN');
                            const giaSale = parseFloat(sale.gia_sale).toLocaleString('vi-VN');
                            const phanTramGiam = parseFloat(sale.phan_tram_giam).toLocaleString('vi-VN');
                            let hinhAnh = sale.hinh_anh || 'https://dummyimage.com/200x200/f0f0f0/666';
                            // Sửa đường dẫn ảnh: thêm / ở đầu nếu chưa có
                            if (hinhAnh && !hinhAnh.match(/^(https?:\/\/|\/)/)) {
                                hinhAnh = '/' + hinhAnh;
                            }
                            const tenThuoc = sale.ten_thuoc.replace(/'/g, "\\'");
                            const donVi = sale.don_vi || '';
                            
                            return `
                                <div class="sale-product-card" style="min-width: 280px; max-width: 280px; background: #fff; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.2); position: relative;">
                                    <div class="position-absolute top-0 start-0 m-2">
                                        <span class="badge bg-danger" style="font-size: 0.9rem; padding: 8px 12px;">
                                            Giảm ${phanTramGiam}%
                                        </span>
                                    </div>
                                    <div style="height: 200px; overflow: hidden; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                        <img src="${hinhAnh}" alt="${tenThuoc}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                    </div>
                                    <div style="padding: 15px;">
                                        <div class="mb-2">
                                            <div style="text-decoration: line-through; color: #999; font-size: 0.85rem;">
                                                ${giaGoc} ₫
                                            </div>
                                            <div class="text-danger fw-bold" style="font-size: 1.3rem;">
                                                ${giaSale} ₫
                                                <span style="font-size: 0.9rem; color: #666;">/${donVi}</span>
                                            </div>
                                        </div>
                                        <div class="mb-3" style="font-size: 0.9rem; color: #333; min-height: 40px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            ${tenThuoc}
                                        </div>
                                        <button type="button" class="btn btn-primary w-100" style="background: #1956b2; border: none; font-weight: 600;" onclick="window.location.href='index.php?action=chi_tiet&id=${sale.ma_thuoc}'">
                                            Chọn sản phẩm
                                        </button>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    }
                    
                    // Initial render
                    <?php if (!empty($danhMucCap2) && !empty($danhMucCap2[0])): ?>
                    // Filter theo danh mục đầu tiên
                    const firstCategoryId = <?= $danhMucCap2[0]['ma_danh_muc'] ?>;
                    setTimeout(() => filterSaleByCategory(firstCategoryId), 100);
                    <?php else: ?>
                    // Hiển thị tất cả nếu không có danh mục
                    setTimeout(() => renderSaleProducts(allSales), 100);
                    <?php endif; ?>
                </script>
                
                <!-- Initial products (will be replaced by JavaScript) -->
                <?php foreach (array_slice($saleToShow, 0, 6) as $sale): 
                    $giaGoc = $sale['gia_goc'];
                    $giaSale = $sale['gia_sale'];
                    $phanTramGiam = $sale['phan_tram_giam'];
                    $hinhAnh = $sale['hinh_anh'] ?? 'https://dummyimage.com/200x200/f0f0f0/666';
                    // Sửa đường dẫn ảnh: thêm / ở đầu nếu chưa có
                    if ($hinhAnh && !preg_match('/^(https?:\/\/|\/)/', $hinhAnh)) {
                        $hinhAnh = '/' . $hinhAnh;
                    }
                ?>
                <div class="sale-product-card" style="min-width: 280px; max-width: 280px; background: #fff; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.2); position: relative;">
                    <!-- Discount badge -->
                    <div class="position-absolute top-0 start-0 m-2">
                        <span class="badge bg-danger" style="font-size: 0.9rem; padding: 8px 12px;">
                            Giảm <?= number_format($phanTramGiam, 0) ?>%
                        </span>
                    </div>
                    
                    <!-- Product image -->
                    <div style="height: 200px; overflow: hidden; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                        <img src="<?= htmlspecialchars($hinhAnh) ?>" alt="<?= htmlspecialchars($sale['ten_thuoc']) ?>" 
                             style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                    
                    <!-- Product info -->
                    <div style="padding: 15px;">
                        <!-- Price -->
                        <div class="mb-2">
                            <div style="text-decoration: line-through; color: #999; font-size: 0.85rem;">
                                <?= number_format($giaGoc, 0, ',', '.') ?> ₫
                            </div>
                            <div class="text-danger fw-bold" style="font-size: 1.3rem;">
                                <?= number_format($giaSale, 0, ',', '.') ?> ₫
                                <span style="font-size: 0.9rem; color: #666;">/<?= htmlspecialchars($sale['don_vi']) ?></span>
                            </div>
                        </div>
                        
                        <!-- Product name -->
                        <div class="mb-3" style="font-size: 0.9rem; color: #333; min-height: 40px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= htmlspecialchars($sale['ten_thuoc']) ?>
                        </div>
                        
                        <!-- Select button -->
                        <button type="button" 
                                class="btn btn-primary w-100" 
                                style="background: #1956b2; border: none; font-weight: 600;"
                                onclick="window.location.href='index.php?action=chi_tiet&id=<?= $sale['ma_thuoc'] ?>'">
                            Chọn sản phẩm
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Scroll arrows -->
            <button class="sale-scroll-btn sale-scroll-left" style="position: absolute; left: -15px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.9); border: none; width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 2px 8px rgba(0,0,0,0.2); cursor: pointer; z-index: 10;">
                <i class="bi bi-chevron-left text-danger"></i>
            </button>
            <button class="sale-scroll-btn sale-scroll-right" style="position: absolute; right: -15px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.9); border: none; width: 40px; height: 40px; border-radius: 50%; box-shadow: 0 2px 8px rgba(0,0,0,0.2); cursor: pointer; z-index: 10;">
                <i class="bi bi-chevron-right text-danger"></i>
            </button>
        </div>
        </div>
    </div>
    </div>
    
    <style>
        .sale-products-scroll::-webkit-scrollbar {
            height: 8px;
        }
        .sale-products-scroll::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
        }
        .sale-products-scroll::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 10px;
        }
        .sale-products-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.5);
        }
        .sale-product-card {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .sale-product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        .countdown-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 45px;
        }
        .countdown-value {
            background: rgba(255,255,255,0.3);
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.5rem;
            min-width: 50px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .countdown-label {
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
    
    <script>
        // Countdown timer
        <?php if ($saleHienTai): 
            $endTime = strtotime($saleHienTai['thoi_gian_ket_thuc']);
        ?>
        function updateCountdown() {
            const endTime = <?= $endTime ?> * 1000;
            const now = new Date().getTime();
            const distance = endTime - now;
            
            if (distance < 0) {
                document.getElementById('countdown').innerHTML = '<div class="text-center w-100">Đã kết thúc</div>';
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Cập nhật từng phần tử
            document.getElementById('countdown-days').textContent = String(days).padStart(2, '0');
            document.getElementById('countdown-hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('countdown-minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('countdown-seconds').textContent = String(seconds).padStart(2, '0');
            
            // Ẩn phần "Ngày" nếu còn ít hơn 1 ngày
            const daysItem = document.getElementById('countdown-days').parentElement;
            const daysSeparator = daysItem.nextElementSibling;
            if (days === 0) {
                daysItem.style.display = 'none';
                if (daysSeparator && daysSeparator.tagName === 'SPAN') {
                    daysSeparator.style.display = 'none';
                }
            } else {
                daysItem.style.display = 'flex';
                if (daysSeparator && daysSeparator.tagName === 'SPAN') {
                    daysSeparator.style.display = 'inline';
                }
            }
        }
        updateCountdown();
        setInterval(updateCountdown, 1000);
        <?php endif; ?>
        
        // Scroll products
        const scrollContainer = document.querySelector('.sale-products-scroll');
        const scrollLeftBtn = document.querySelector('.sale-scroll-left');
        const scrollRightBtn = document.querySelector('.sale-scroll-right');
        
        if (scrollLeftBtn && scrollRightBtn && scrollContainer) {
            scrollLeftBtn.addEventListener('click', () => {
                scrollContainer.scrollBy({ left: -300, behavior: 'smooth' });
            });
            scrollRightBtn.addEventListener('click', () => {
                scrollContainer.scrollBy({ left: 300, behavior: 'smooth' });
            });
        }
    </script>
    <?php endif; ?>
    
    <!-- Danh mục sản phẩm nổi bật -->
    <div class="container mt-4">
      <h3 class="mb-3">
        <?php if ($danhMucHienTai): ?>
          <?= htmlspecialchars($danhMucHienTai['ten_danh_muc']) ?>
          <a href="index.php?page=trangchu" class="btn btn-sm btn-outline-secondary ms-2">Xem tất cả</a>
        <?php else: ?>
          Sản phẩm mới nhất
        <?php endif; ?>
      </h3>
      <div class="row g-3">
        <?php if (empty($thuocMoiNhat)): ?>
          <div class="col-12">
            <div class="alert alert-info text-center">
              <p class="mb-0">Chưa có sản phẩm nào. Vui lòng quay lại sau!</p>
            </div>
          </div>
        <?php else: ?>
          <?php foreach ($thuocMoiNhat as $thuoc): ?>
            <div class="col-md-3 mb-4">
              <div class="product-card">
                <div class="product-image-wrapper">
                  <?php 
                    $thumb = layAnhChinhThuoc($thuoc); 
                    // Đảm bảo đường dẫn đúng
                    if (strpos($thumb, 'dummyimage.com') === false && strpos($thumb, '/') !== 0 && strpos($thumb, 'http') !== 0) {
                        $thumb = '/' . ltrim($thumb, '/');
                    }
                  ?>
                  <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($thuoc['ten_thuoc']) ?>" onerror="this.onerror=null; this.src='https://dummyimage.com/300x200/f0f0f0/666&text=<?= urlencode(mb_substr($thuoc['ten_thuoc'], 0, 20)) ?>';">
                  <?php if ($thuoc['so_luong_ton'] <= 10): ?>
                    <span class="badge bg-warning position-absolute top-0 end-0 m-2">Sắp hết</span>
                  <?php endif; ?>
                </div>
                <div class="product-info">
                  <h5 class="product-name"><?= htmlspecialchars($thuoc['ten_thuoc']) ?></h5>
                  <?php if (!$danhMucHienTai): ?>
                    <div class="mb-2">
                      <small class="text-muted"><?= htmlspecialchars($thuoc['ten_danh_muc'] ?? 'Chưa phân loại') ?></small>
                    </div>
                  <?php endif; ?>
                  <div class="product-price">
                    <?= number_format($thuoc['gia'], 0, ',', '.') ?> ₫<span class="product-unit">/<?= htmlspecialchars($thuoc['don_vi']) ?></span>
                  </div>
                        <button type="button" 
                                class="product-btn" 
                                onclick="window.location.href='index.php?action=chi_tiet&id=<?= isset($thuoc['ma_thuoc']) ? (int)$thuoc['ma_thuoc'] : 0 ?>'"
                                style="width: 100%; border: none; cursor: pointer;">
                          Chọn sản phẩm
                        </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Hiển thị sản phẩm theo danh mục (chỉ khi không filter theo danh mục cụ thể) -->
    <?php if (!$danhMucHienTai && !empty($thuocTheoDanhMuc)): ?>
      <?php foreach ($thuocTheoDanhMuc as $data): 
        $dm = $data['danh_muc'];
        $thuocList = $data['thuoc'];
      ?>
        <div class="container mt-5">
          <h3 class="mb-3"><?= htmlspecialchars($dm['ten_danh_muc']) ?></h3>
          <div class="row g-3">
                <?php foreach ($thuocList as $thuoc): ?>
                  <div class="col-md-3 mb-4">
                    <div class="product-card">
                      <div class="product-image-wrapper">
                        <?php 
                          $thumb = layAnhChinhThuoc($thuoc); 
                          // Đảm bảo đường dẫn đúng
                          if (strpos($thumb, 'dummyimage.com') === false && strpos($thumb, '/') !== 0 && strpos($thumb, 'http') !== 0) {
                              $thumb = '/' . ltrim($thumb, '/');
                          }
                        ?>
                        <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($thuoc['ten_thuoc']) ?>" onerror="this.onerror=null; this.src='https://dummyimage.com/300x200/f0f0f0/666&text=<?= urlencode(mb_substr($thuoc['ten_thuoc'], 0, 20)) ?>';">
                        <?php if ($thuoc['so_luong_ton'] <= 10): ?>
                          <span class="badge bg-warning position-absolute top-0 end-0 m-2">Sắp hết</span>
                        <?php endif; ?>
                      </div>
                      <div class="product-info">
                        <h5 class="product-name"><?= htmlspecialchars($thuoc['ten_thuoc']) ?></h5>
                        <div class="mb-2">
                          <small class="text-muted"><?= htmlspecialchars($dm['ten_danh_muc']) ?></small>
                        </div>
                        <div class="product-price">
                          <?= number_format($thuoc['gia'], 0, ',', '.') ?> ₫<span class="product-unit">/<?= htmlspecialchars($thuoc['don_vi']) ?></span>
                        </div>
                        <button type="button" 
                                class="product-btn" 
                                onclick="window.location.href='index.php?action=chi_tiet&id=<?= isset($thuoc['ma_thuoc']) ? (int)$thuoc['ma_thuoc'] : 0 ?>'"
                                style="width: 100%; border: none; cursor: pointer;">
                          Chọn sản phẩm
                        </button>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- Tin tức -->
    <?php 
    // Đảm bảo biến $tinTucList tồn tại
    if (!isset($tinTucList)) {
        $tinTucList = [];
    }
    if (!empty($tinTucList)): ?>
    <div class="container mt-5">
      <h3 class="mb-4">
        <i class="bi bi-newspaper"></i> Tin tức
      </h3>
      <div class="row g-4">
        <?php foreach ($tinTucList as $tin): ?>
          <div class="col-md-4 mb-4">
            <div class="card h-100" style="box-shadow: 0 2px 12px rgba(0,0,0,0.1); border-radius: 12px; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s; cursor: pointer;" 
                 onclick="window.location.href='index.php?page=tintuc&id=<?= $tin['ma_tin_tuc'] ?>'">
              <?php $newsImg = layAnhChinhTinTuc($tin, '300x200'); ?>
              <?php if ($newsImg): ?>
                <img src="<?= htmlspecialchars($newsImg) ?>" 
                     class="card-img-top" 
                     alt="<?= htmlspecialchars($tin['tieu_de']) ?>"
                     style="height: 200px; object-fit: cover;">
              <?php endif; ?>
              <div class="card-body">
                <h5 class="card-title" style="font-size: 1rem; font-weight: 600; color: #333; margin-bottom: 10px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                  <?= htmlspecialchars($tin['tieu_de']) ?>
                </h5>
                <?php if ($tin['tom_tat']): ?>
                  <p class="card-text text-muted" style="font-size: 0.875rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                    <?= htmlspecialchars($tin['tom_tat']) ?>
                  </p>
                <?php endif; ?>
                <div class="d-flex justify-content-between align-items-center mt-3">
                  <small class="text-muted">
                    <i class="bi bi-calendar"></i> <?= date('d/m/Y', strtotime($tin['ngay_tao'])) ?>
                    <?php if ($tin['tac_gia']): ?>
                      | <i class="bi bi-person"></i> <?= htmlspecialchars($tin['tac_gia']) ?>
                    <?php endif; ?>
                  </small>
                  <small class="text-muted">
                    <i class="bi bi-eye"></i> <?= number_format($tin['luot_xem']) ?>
                  </small>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Section: Giới thiệu về cửa hàng -->
    <div id="gioithieu" class="about-box mt-5 mb-4 mx-auto" style="max-width: 1100px; background: #fff; border-radius: 14px; box-shadow: 0 6px 24px #e6ecf0; padding: 36px;">
        <?php include_once __DIR__.'/../gioithieu.php'; ?>
    </div>
    <!-- Section: Liên hệ -->
    <div id="lienhe" class="about-box mt-4 mb-4 mx-auto" style="max-width: 1100px; background: #fff; border-radius: 14px; box-shadow: 0 6px 24px #e6ecf0; padding: 36px;">
        <?php include_once __DIR__.'/../lienhe.php'; ?>
    </div>
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
    
    <script>
        // Autocomplete functionality
        const searchInput = document.getElementById('searchInput');
        const suggestionsDiv = document.getElementById('searchSuggestions');
        let searchTimeout;

        if (searchInput && suggestionsDiv) {
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    suggestionsDiv.style.display = 'none';
                    return;
                }

                searchTimeout = setTimeout(function() {
                    fetch('index.php?action=search_suggestions&q=' + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(data => {
                            if (data.length > 0) {
                                let html = '<div class="list-group list-group-flush">';
                                data.forEach(function(item) {
                                    let image = item.hinh_anh || 'https://dummyimage.com/50x50/f0f0f0/666';
                                    // Sửa đường dẫn ảnh: thêm / ở đầu nếu chưa có
                                    if (image && !image.match(/^(https?:\/\/|\/)/)) {
                                        image = '/' + image;
                                    }
                                    html += `
                                        <a href="index.php?action=chi_tiet&id=${item.ma_thuoc}" class="list-group-item list-group-item-action">
                                            <div class="d-flex align-items-center">
                                                <img src="${image}" alt="${item.ten_thuoc}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; margin-right: 12px;">
                                                <div class="flex-grow-1">
                                                    <div class="fw-semibold">${item.ten_thuoc}</div>
                                                    <small class="text-muted">${item.ten_danh_muc || 'Chưa phân loại'}</small>
                                                    <div class="text-danger fw-bold">${new Intl.NumberFormat('vi-VN').format(item.gia)} ₫</div>
                                                </div>
                                            </div>
                                        </a>
                                    `;
                                });
                                html += '</div>';
                                suggestionsDiv.innerHTML = html;
                                suggestionsDiv.style.display = 'block';
                            } else {
                                suggestionsDiv.style.display = 'none';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            suggestionsDiv.style.display = 'none';
                        });
                }, 300);
            });

            // Hide suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
                    suggestionsDiv.style.display = 'none';
                }
            });

            // Hide suggestions on form submit
            const searchForm = searchInput.closest('form');
            if (searchForm) {
                searchForm.addEventListener('submit', function() {
                    suggestionsDiv.style.display = 'none';
                });
            }
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
