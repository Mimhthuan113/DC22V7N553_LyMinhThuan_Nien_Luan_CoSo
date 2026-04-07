<?php
$rawImages = [
    $tinTuc['hinh_anh'] ?? null,
    $tinTuc['hinh_anh_2'] ?? null,
    $tinTuc['hinh_anh_3'] ?? null,
    $tinTuc['hinh_anh_4'] ?? null,
    $tinTuc['hinh_anh_5'] ?? null,
];
$displayImages = array_values(array_filter($rawImages, function($url) {
    return !empty($url);
}));
$placeholderMain = 'https://dummyimage.com/800x400/f0f0f0/666&text=' . urlencode(mb_substr($tinTuc['tieu_de'] ?? 'Tin tức', 0, 20));
$placeholderThumb = 'https://dummyimage.com/120x80/f0f0f0/666&text=' . urlencode(mb_substr($tinTuc['tieu_de'] ?? 'Tin', 0, 10));
$mainImage = $displayImages[0] ?? $placeholderMain;
$thumbImages = $displayImages ?: [$placeholderThumb];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tinTuc['tieu_de']) ?> - Tin tức</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; }
        .news-header { background: #023660; color: #fff; padding: 14px 0; }
        .news-navbar { background: #fff; box-shadow: 0 2px 4px #e2e2e2; }
        .news-container { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-top: 20px; }
        .news-main-image { width: 100%; height: 400px; object-fit: cover; border-radius: 12px; background: #f0f0f0; }
        .news-thumbnails { display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap; }
        .news-thumbnail { width: 120px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #ddd; cursor: pointer; }
        .news-thumbnail.active, .news-thumbnail:hover { border-color: #1956b2; }
        .news-meta { color: #6c757d; font-size: 0.95rem; margin-top: 8px; }
        .news-content { margin-top: 18px; line-height: 1.6; }
        .news-content img { max-width: 100%; height: auto; }
    </style>
</head>
<body>
    <div class="news-header">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <a href="index.php?page=trangchu" class="text-white text-decoration-none">
                    <i class="bi bi-house-door-fill"></i> Trang chủ
                </a>
                <span class="text-white-50">/</span>
                <span>Tin tức</span>
            </div>
            <a href="index.php?page=giohang" class="text-white text-decoration-none position-relative">
                <i class="bi bi-cart3" style="font-size: 1.3rem;"></i>
                <span id="cartCountBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;">0</span>
            </a>
        </div>
    </div>

    <div class="container" style="max-width: 1100px;">
        <div class="news-container">
            <h1 class="h3 mb-2"><?= htmlspecialchars($tinTuc['tieu_de']) ?></h1>
            <div class="news-meta">
                <i class="bi bi-calendar-event"></i>
                <?= isset($tinTuc['ngay_tao']) ? htmlspecialchars(date('d/m/Y', strtotime($tinTuc['ngay_tao']))) : '' ?>
                <?php if (!empty($tinTuc['tac_gia'])): ?>
                    &nbsp;•&nbsp;<i class="bi bi-person-circle"></i> <?= htmlspecialchars($tinTuc['tac_gia']) ?>
                <?php endif; ?>
                <?php if (isset($tinTuc['luot_xem'])): ?>
                    &nbsp;•&nbsp;<i class="bi bi-eye"></i> <?= (int)$tinTuc['luot_xem'] + 1 ?>
                <?php endif; ?>
            </div>

            <div class="mt-3">
                <img id="mainNewsImage" src="<?= htmlspecialchars($mainImage) ?>" alt="<?= htmlspecialchars($tinTuc['tieu_de']) ?>" class="news-main-image">
                <div class="news-thumbnails">
                    <?php foreach ($thumbImages as $idx => $img): ?>
                        <img src="<?= htmlspecialchars($img) ?>" 
                             alt="thumb <?= $idx + 1 ?>" 
                             class="news-thumbnail <?= $idx === 0 ? 'active' : '' ?>"
                             onclick="changeMainImage(this)">
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (!empty($tinTuc['tom_tat'])): ?>
                <p class="mt-3 text-muted"><strong>Tóm tắt:</strong> <?= nl2br(htmlspecialchars($tinTuc['tom_tat'])) ?></p>
            <?php endif; ?>

            <div class="news-content">
                <?= nl2br(htmlspecialchars($tinTuc['noi_dung'])) ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeMainImage(el) {
            document.getElementById('mainNewsImage').src = el.src;
            document.querySelectorAll('.news-thumbnail').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
        }

        // Cập nhật badge giỏ hàng (dùng API sẵn có)
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                const res = await fetch('index.php?action=cart_count');
                if (!res.ok) return;
                const data = await res.json();
                const count = data?.count ?? 0;
                const badge = document.getElementById('cartCountBadge');
                if (count > 0) {
                    badge.textContent = count;
                    badge.style.display = 'inline-block';
                }
            } catch (e) {
                console.error(e);
            }
        });
    </script>
</body>
</html>


