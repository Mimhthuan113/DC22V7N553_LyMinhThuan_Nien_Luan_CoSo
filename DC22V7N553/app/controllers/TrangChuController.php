<?php
/**
 * app/controllers/TrangChuController.php - Controller xử lý trang chủ
 * 
 * Controller này xử lý hiển thị trang chủ với:
 * - Banner (slider)
 * - Sản phẩm sale đang hoạt động
 * - Sản phẩm mới nhất
 * - Sản phẩm theo danh mục
 * - Tin tức mới nhất
 * - Filter theo danh mục (nếu có)
 */

// Nạp các Model cần thiết
require_once __DIR__ . '/../models/ThuocModel.php';
require_once __DIR__ . '/../models/DanhMucModel.php';
require_once __DIR__ . '/../models/TinTucModel.php';
require_once __DIR__ . '/../models/BannerModel.php';
require_once __DIR__ . '/../models/SaleModel.php';

class TrangChuController
{
    /**
     * Model quản lý thuốc
     */
    private $thuocModel;
    
    /**
     * Model quản lý danh mục
     */
    private $danhMucModel;
    
    /**
     * Model quản lý tin tức
     */
    private $tinTucModel;
    
    /**
     * Model quản lý banner
     */
    private $bannerModel;
    
    /**
     * Model quản lý sale
     */
    private $saleModel;

    /**
     * Constructor: Khởi tạo các Model
     */
    public function __construct()
    {
        $this->thuocModel = new ThuocModel();
        $this->danhMucModel = new DanhMucModel();
        $this->tinTucModel = new TinTucModel();
        $this->bannerModel = new BannerModel();
        $this->saleModel = new SaleModel();
    }

    /**
     * Hiển thị trang chủ
     * Hiển thị banner, sản phẩm sale, sản phẩm mới nhất, sản phẩm theo danh mục, tin tức
     * Hỗ trợ filter theo danh mục (nếu có tham số ?danhmuc=...)
     * 
     * Route: index.php?page=trangchu hoặc index.php (mặc định)
     * Route với filter: index.php?page=trangchu&danhmuc=mã danh mục
     */
    public function index()
    {
        // Lấy danh sách tất cả danh mục để hiển thị trong navbar
        $danhMucList = $this->danhMucModel->layTatCa();
        
        // Kiểm tra xem có filter theo danh mục không (từ URL ?danhmuc=...)
        $ma_danh_muc_filter = isset($_GET['danhmuc']) ? (int)$_GET['danhmuc'] : 0;
        $danhMucHienTai = null;
        
        // Nếu có filter theo danh mục, tìm thông tin danh mục đó
        if ($ma_danh_muc_filter > 0) {
            foreach ($danhMucList as $dm) {
                if ($dm['ma_danh_muc'] == $ma_danh_muc_filter) {
                    $danhMucHienTai = $dm;
                    break;
                }
            }
        }
        
        // Nếu có filter theo danh mục
        if ($ma_danh_muc_filter > 0 && $danhMucHienTai) {
            // Lấy tất cả danh mục con của danh mục này (bao gồm cả chính nó)
            // Để hiển thị sản phẩm từ tất cả các danh mục con
            $danhSachMaDanhMuc = [$ma_danh_muc_filter];  // Bắt đầu với danh mục được chọn
            $danhMucCon = $this->danhMucModel->layTatCaDanhMucCon($ma_danh_muc_filter, $danhMucList);
            $danhSachMaDanhMuc = array_merge($danhSachMaDanhMuc, $danhMucCon);
            
            // Lấy tất cả thuốc trong danh mục này và các danh mục con
            $thuocMoiNhat = $this->thuocModel->layTheoNhieuDanhMuc($danhSachMaDanhMuc, 100, 0);
            
            // Nếu không có, thử lấy tất cả thuốc và lọc lại (fallback)
            // Bao gồm cả kiểm tra hạn sử dụng
            if (empty($thuocMoiNhat)) {
                $allThuoc = $this->thuocModel->layTatCa(100, 0, '');
                $thuocMoiNhat = array_filter($allThuoc, function($t) use ($danhSachMaDanhMuc) {
                    $isValidCategory = in_array($t['ma_danh_muc'], $danhSachMaDanhMuc);  // Thuộc danh mục được chọn
                    $isActive = $t['trang_thai'] == 1;                                    // Đang hoạt động
                    $isNotExpired = empty($t['han_su_dung']) || strtotime($t['han_su_dung']) >= strtotime('today');  // Chưa hết hạn
                    return $isValidCategory && $isActive && $isNotExpired;
                });
            }
            
            // Khi đang filter theo danh mục, không hiển thị phần "Sản phẩm theo danh mục" khác
            $thuocTheoDanhMuc = [];
        } else {
            // Nếu không có filter, hiển thị trang chủ bình thường
            
            // Lấy thuốc mới nhất (8 sản phẩm) để hiển thị phần "Sản phẩm mới nhất"
            $thuocMoiNhat = $this->thuocModel->layTatCaChoKhachHang(8, 0, '');
            
            // Nếu không có, thử lấy tất cả thuốc có trang_thai = 1 và chưa hết hạn (fallback)
            if (empty($thuocMoiNhat)) {
                $allThuoc = $this->thuocModel->layTatCa(100, 0, '');
                $thuocMoiNhat = array_filter($allThuoc, function($t) {
                    $isActive = $t['trang_thai'] == 1;                                    // Đang hoạt động
                    $isNotExpired = empty($t['han_su_dung']) || strtotime($t['han_su_dung']) >= strtotime('today');  // Chưa hết hạn
                    return $isActive && $isNotExpired;
                });
                // Chỉ lấy 8 sản phẩm đầu tiên
                $thuocMoiNhat = array_slice($thuocMoiNhat, 0, 8);
            }
            
            // Lấy thuốc theo từng danh mục (chỉ danh mục cha cấp 1)
            // Để hiển thị phần "Sản phẩm theo danh mục"
            $thuocTheoDanhMuc = [];
            foreach ($danhMucList as $dm) {
                // Chỉ lấy danh mục cha (ma_danh_muc_cha === null), đang hoạt động, và có thuốc
                if ($dm['ma_danh_muc_cha'] === null && $dm['trang_thai'] == 1 && $dm['so_luong_thuoc'] > 0) {
                    // Lấy 4 sản phẩm từ danh mục này
                    $thuocList = $this->thuocModel->layTheoDanhMuc($dm['ma_danh_muc'], 4, 0);
                    
                    // Nếu không có, thử lấy tất cả thuốc trong danh mục này và lọc lại (fallback)
                    if (empty($thuocList)) {
                        $allThuoc = $this->thuocModel->layTatCa(100, 0, '');
                        $thuocList = array_filter($allThuoc, function($t) use ($dm) {
                            $isValidCategory = $t['ma_danh_muc'] == $dm['ma_danh_muc'];  // Thuộc danh mục này
                            $isActive = $t['trang_thai'] == 1;                           // Đang hoạt động
                            $isNotExpired = empty($t['han_su_dung']) || strtotime($t['han_su_dung']) >= strtotime('today');  // Chưa hết hạn
                            return $isValidCategory && $isActive && $isNotExpired;
                        });
                        // Chỉ lấy 4 sản phẩm đầu tiên
                        $thuocList = array_slice($thuocList, 0, 4);
                    }
                    
                    // Nếu có sản phẩm, thêm vào mảng
                    if (!empty($thuocList)) {
                        $thuocTheoDanhMuc[$dm['ma_danh_muc']] = [
                            'danh_muc' => $dm,      // Thông tin danh mục
                            'thuoc' => $thuocList   // Danh sách thuốc trong danh mục
                        ];
                    }
                }
            }
        }

        // Lấy tin tức mới nhất (6 tin) để hiển thị phần "Tin tức"
        $tinTucList = $this->tinTucModel->layTatCaChoKhachHang(6, 0);
        
        // Lấy banner đang hiển thị (tối đa 10 banner) để hiển thị slider
        $banners = $this->bannerModel->layDangHienThi(10, 0);
        
        // Lấy danh sách sale đang hoạt động (tối đa 20 sale) để hiển thị phần "Sản phẩm sale"
        $saleList = $this->saleModel->layDangHoatDong(20, 0);
        
        // Lấy danh mục cấp 2 (danh mục con) có sale để hiển thị tabs
        // Mỗi tab sẽ hiển thị sale của một danh mục con
        $danhMucCap2 = [];
        foreach ($danhMucList as $dm) {
            // Chỉ lấy danh mục con (ma_danh_muc_cha !== null) và đang hoạt động
            if ($dm['ma_danh_muc_cha'] !== null && $dm['trang_thai'] == 1) {
                // Kiểm tra xem danh mục này có sale đang hoạt động không
                $saleTheoDanhMuc = $this->saleModel->layDangHoatDongTheoDanhMuc($dm['ma_danh_muc'], 1, 0);
                if (!empty($saleTheoDanhMuc)) {
                    // Nếu có sale, thêm vào danh sách
                    $danhMucCap2[] = $dm;
                }
            }
        }
        // Giới hạn tối đa 3 danh mục để hiển thị (để giao diện không quá dài)
        $danhMucCap2 = array_slice($danhMucCap2, 0, 3);

        // Hiển thị view trang chủ
        // Truyền các biến: $danhMucList, $thuocMoiNhat, $thuocTheoDanhMuc, $tinTucList, $banners, $saleList, $danhMucCap2, $danhMucHienTai
        require __DIR__ . '/../views/trangchu/index.php';
    }
}
