<?php
require_once __DIR__ . '/../models/TinTucModel.php';
require_once __DIR__ . '/../core/Session.php';

class TinTucController
{
    private $tinTucModel;

    public function __construct()
    {
        $this->tinTucModel = new TinTucModel();
    }

    public function chiTiet($ma_tin_tuc)
    {
        if ($ma_tin_tuc <= 0) {
            header('Location: index.php?page=trangchu');
            exit;
        }

        $tinTuc = $this->tinTucModel->layTheoMa($ma_tin_tuc);
        // Chỉ cho phép tin đang hiển thị
        if (!$tinTuc || (isset($tinTuc['trang_thai']) && (int)$tinTuc['trang_thai'] !== 1)) {
            Session::setFlash('error', 'Tin tức không tồn tại hoặc đã ẩn');
            header('Location: index.php?page=trangchu');
            exit;
        }

        // Tăng lượt xem (không block nếu lỗi)
        $this->tinTucModel->tangLuotXem($ma_tin_tuc);

        require __DIR__ . '/../views/tintuc/chi_tiet.php';
    }
}


