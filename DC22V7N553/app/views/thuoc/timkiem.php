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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm: <?= htmlspecialchars($search) ?> - DC22V7N553</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f9f9f9; }
        .pc-header { background: #007bdf; color: #fff; padding: 14px 0; }
        .pc-navbar { background: #fff; box-shadow: 0 2px 4px #e2e2e2; }
        .pc-footer { background: #023660; color: #fff; padding: 24px 0; margin-top: 24px; }
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
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s;
            cursor: pointer;
        }
        .product-btn:hover {
            background: #023660;
            color: #fff;
        }
        .search-results-header {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
          <form class="d-flex me-3 position-relative" role="search" action="index.php" method="GET">
            <input type="hidden" name="page" value="timkiem">
            <input class="form-control me-2" 
                   type="search" 
                   name="q" 
                   id="searchInput"
                   placeholder="Tìm kiếm sản phẩm..." 
                   autocomplete="off"
                   value="<?= htmlspecialchars($search) ?>">
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

    <!-- Search Results -->
    <div class="container mt-4 mb-5">
        <div class="search-results-header">
            <h3>Kết quả tìm kiếm: "<strong><?= htmlspecialchars($search) ?></strong>"</h3>
            <p class="text-muted mb-0">Tìm thấy <?= number_format($total) ?> sản phẩm</p>
        </div>

        <?php if (empty($dsThuoc)): ?>
            <div class="alert alert-info text-center">
                <i class="bi bi-search"></i>
                <h5>Không tìm thấy sản phẩm nào</h5>
                <p>Vui lòng thử lại với từ khóa khác hoặc <a href="index.php?page=trangchu">quay về trang chủ</a></p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($dsThuoc as $thuoc): ?>
                    <div class="col-md-3 mb-4">
                        <div class="product-card">
                            <div class="product-image-wrapper">
                                <?php $thumb = layAnhChinhThuoc($thuoc); ?>
                                <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($thuoc['ten_thuoc']) ?>">
                                <?php if ($thuoc['so_luong_ton'] <= 10): ?>
                                    <span class="badge bg-warning position-absolute top-0 end-0 m-2">Sắp hết</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h5 class="product-name"><?= htmlspecialchars($thuoc['ten_thuoc']) ?></h5>
                                <div class="mb-2">
                                    <small class="text-muted"><?= htmlspecialchars($thuoc['ten_danh_muc'] ?? 'Chưa phân loại') ?></small>
                                </div>
                                <div class="product-price">
                                    <?= number_format($thuoc['gia'], 0, ',', '.') ?> ₫<span class="product-unit">/<?= htmlspecialchars($thuoc['don_vi']) ?></span>
                                </div>
                                <button type="button" 
                                        class="product-btn" 
                                        onclick="window.location.href='index.php?action=chi_tiet&id=<?= isset($thuoc['ma_thuoc']) ? (int)$thuoc['ma_thuoc'] : 0 ?>'">
                                  Chọn sản phẩm
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="index.php?page=timkiem&q=<?= urlencode($search) ?>&p=<?= $page - 1 ?>">Trước</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="index.php?page=timkiem&q=<?= urlencode($search) ?>&p=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="index.php?page=timkiem&q=<?= urlencode($search) ?>&p=<?= $page + 1 ?>">Sau</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
                                    let image = item.hinh_anh || item.hinh_anh_2 || item.hinh_anh_3 || item.hinh_anh_4 || item.hinh_anh_5 || 'https://dummyimage.com/50x50/f0f0f0/666';
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

