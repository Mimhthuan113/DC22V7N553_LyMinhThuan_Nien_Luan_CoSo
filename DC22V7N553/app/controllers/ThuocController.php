<?php
/**
 * app/controllers/ThuocController.php - Controller quản lý thuốc/sản phẩm (dành cho khách hàng)
 * 
 * Controller này xử lý các yêu cầu liên quan đến thuốc từ phía khách hàng:
 * - Hiển thị danh sách thuốc (ẩn thuốc hết hạn)
 * - Tìm kiếm thuốc
 * - Xem chi tiết thuốc (có áp dụng giá sale)
 * - API gợi ý tìm kiếm (autocomplete)
 */

// Nạp các Model cần thiết
require_once __DIR__ . '/../models/ThuocModel.php';
require_once __DIR__ . '/../models/DanhMucModel.php';
require_once __DIR__ . '/../models/SaleModel.php';

class ThuocController
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
        $this->saleModel = new SaleModel();
    }

    /**
     * Hiển thị danh sách thuốc (dành cho khách hàng)
     * Chỉ hiển thị thuốc đang hoạt động và chưa hết hạn
     * 
     * Route: index.php?page=thuoc hoặc index.php (mặc định)
     */
    public function index()
    {
        // Lấy danh sách thuốc cho khách hàng (ẩn thuốc hết hạn)
        $dsThuoc = $this->thuocModel->layTatCaChoKhachHang();
        
        // Hiển thị view danh sách thuốc
        require __DIR__ . '/../views/thuoc/index.php';
    }

    /**
     * Tìm kiếm thuốc
     * Hiển thị kết quả tìm kiếm với phân trang
     * 
     * Route: index.php?page=timkiem&q=từ khóa&p=trang
     * 
     * @param string $q Từ khóa tìm kiếm (từ $_GET)
     * @param int $p Số trang (từ $_GET, mặc định: 1)
     */
    public function timKiem()
    {
        // Lấy từ khóa tìm kiếm từ URL (loại bỏ khoảng trắng đầu/cuối)
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        
        // Lấy số trang từ URL (mặc định: trang 1)
        $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        
        // Số lượng thuốc mỗi trang
        $limit = 12;
        
        // Tính offset (vị trí bắt đầu) cho phân trang
        // Ví dụ: trang 1 = offset 0, trang 2 = offset 12, trang 3 = offset 24
        $offset = ($page - 1) * $limit;

        // Lấy danh sách tất cả danh mục (để hiển thị menu)
        $danhMucList = $this->danhMucModel->layTatCa();
        
        // Nếu không có từ khóa tìm kiếm, chuyển về trang chủ
        if (empty($search)) {
            header('Location: index.php?page=trangchu');
            exit;
        }

        // Lấy danh sách thuốc theo từ khóa tìm kiếm (có phân trang)
        $dsThuoc = $this->thuocModel->layTatCaChoKhachHang($limit, $offset, $search);
        
        // Đếm tổng số thuốc tìm được (để tính số trang)
        $total = $this->thuocModel->demTongSoChoKhachHang($search);
        
        // Tính tổng số trang
        // ceil(): Làm tròn lên (ví dụ: 25 thuốc / 12 mỗi trang = 3 trang)
        $totalPages = ceil($total / $limit);

        // Hiển thị view kết quả tìm kiếm
        require __DIR__ . '/../views/thuoc/timkiem.php';
    }

    /**
     * API: Lấy gợi ý tìm kiếm (AJAX)
     * Được gọi từ JavaScript khi người dùng gõ vào ô tìm kiếm
     * Trả về JSON với danh sách gợi ý (tối đa 5 kết quả)
     * 
     * Route: index.php?action=search_suggestions&q=từ khóa
     */
    public function timKiemSuggestions()
    {
        // Set header để trình duyệt biết đây là JSON
        header('Content-Type: application/json');
        
        // Lấy từ khóa tìm kiếm từ URL
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        
        // Nếu từ khóa rỗng hoặc ít hơn 2 ký tự, trả về mảng rỗng
        // (Để tránh tìm kiếm quá nhiều khi người dùng chỉ gõ 1 ký tự)
        if (empty($search) || strlen($search) < 2) {
            echo json_encode([]);
            exit;
        }

        // Lấy danh sách gợi ý tìm kiếm (tối đa 5 kết quả)
        // Kết quả được sắp xếp thông minh: khớp chính xác trước, sau đó là bắt đầu bằng, cuối cùng là chứa từ khóa
        $suggestions = $this->thuocModel->layGoiYTimKiem($search, 5);
        
        // Trả về JSON
        echo json_encode($suggestions);
        exit;
    }

    /**
     * Hiển thị chi tiết một thuốc
     * Kiểm tra thuốc có tồn tại, đang hoạt động, chưa hết hạn
     * Áp dụng giá sale nếu có
     * Hiển thị sản phẩm liên quan
     * 
     * Route: index.php?action=chi_tiet&id=mã thuốc
     * 
     * @param int $ma_thuoc Mã thuốc cần xem chi tiết
     */
    public function chiTiet($ma_thuoc)
    {
        // Debug: Ghi log để kiểm tra (có thể xóa sau khi hoàn thiện)
        error_log("chiTiet called with ma_thuoc = " . $ma_thuoc);
        
        // Kiểm tra mã thuốc hợp lệ (phải > 0)
        if ($ma_thuoc <= 0) {
            // Debug: Ghi log
            error_log("chiTiet: ma_thuoc <= 0, ma_thuoc = " . $ma_thuoc);
            // Chuyển về trang chủ nếu mã không hợp lệ
            header('Location: index.php?page=trangchu');
            exit;
        }

        // Lấy thông tin thuốc từ database
        $thuoc = $this->thuocModel->layTheoMa($ma_thuoc);
        error_log("chiTiet: thuoc result = " . ($thuoc ? 'found' : 'not found'));
        
        // Nếu không tìm thấy thuốc, chuyển về trang chủ
        if (!$thuoc) {
            // Debug: Ghi log
            error_log("chiTiet: Không tìm thấy thuốc với ma_thuoc = " . $ma_thuoc);
            header('Location: index.php?page=trangchu');
            exit;
        }
        
        // Kiểm tra trạng thái thuốc (phải = 1 = đang hoạt động)
        error_log("chiTiet: thuoc trang_thai = " . ($thuoc['trang_thai'] ?? 'not set'));
        if ($thuoc['trang_thai'] != 1) {
            // Debug: Ghi log
            error_log("chiTiet: Thuốc có trang_thai != 1, ma_thuoc = " . $ma_thuoc . ", trang_thai = " . ($thuoc['trang_thai'] ?? 'not set'));
            // Chuyển về trang chủ nếu thuốc không hoạt động
            header('Location: index.php?page=trangchu');
            exit;
        }

        // Kiểm tra hạn sử dụng (ẩn nếu đã hết hạn)
        // Chỉ kiểm tra nếu có hạn sử dụng (một số thuốc không có hạn sử dụng)
        if (!empty($thuoc['han_su_dung']) && strtotime($thuoc['han_su_dung']) < strtotime('today')) {
            // Debug: Ghi log
            error_log("chiTiet: Thuốc đã hết hạn, ma_thuoc = " . $ma_thuoc . ", han_su_dung = " . $thuoc['han_su_dung']);
            // Chuyển về trang chủ nếu thuốc đã hết hạn
            header('Location: index.php?page=trangchu');
            exit;
        }
        
        // Debug: Ghi log khi tất cả kiểm tra đều pass
        error_log("chiTiet: All checks passed, loading view");

        // Lấy danh sách tất cả danh mục (để tìm danh mục hiện tại và danh mục cha)
        $danhMucList = $this->danhMucModel->layTatCa();
        
        // Tìm danh mục hiện tại (danh mục của thuốc này)
        $danhMucHienTai = null;
        // Tìm danh mục cha (nếu thuốc thuộc danh mục con)
        $danhMucCha = null;
        
        // Duyệt qua danh sách danh mục để tìm danh mục hiện tại
        foreach ($danhMucList as $dm) {
            if ($dm['ma_danh_muc'] == $thuoc['ma_danh_muc']) {
                $danhMucHienTai = $dm;
                
                // Tìm danh mục cha nếu có (nếu thuốc thuộc danh mục con)
                if ($dm['ma_danh_muc_cha']) {
                    foreach ($danhMucList as $dmCha) {
                        if ($dmCha['ma_danh_muc'] == $dm['ma_danh_muc_cha']) {
                            $danhMucCha = $dmCha;
                            break;
                        }
                    }
                }
                break;
            }
        }

        // Lấy sản phẩm liên quan (cùng danh mục cha cấp 1)
        // Để hiển thị "Sản phẩm liên quan" trên trang chi tiết
        $sanPhamLienQuan = [];
        if ($thuoc['ma_danh_muc']) {
            // Xác định danh mục cha cấp 1
            // Nếu thuốc thuộc danh mục con, lấy danh mục cha
            // Nếu thuốc thuộc danh mục cấp 1, dùng chính nó
            $ma_danh_muc_cha_cap_1 = null;
            if ($danhMucHienTai) {
                if ($danhMucHienTai['ma_danh_muc_cha']) {
                    // Sản phẩm thuộc danh mục con, lấy danh mục cha
                    $ma_danh_muc_cha_cap_1 = $danhMucHienTai['ma_danh_muc_cha'];
                } else {
                    // Sản phẩm thuộc danh mục cấp 1, dùng chính nó
                    $ma_danh_muc_cha_cap_1 = $danhMucHienTai['ma_danh_muc'];
                }
            }
            
            // Nếu có danh mục cha cấp 1, lấy sản phẩm liên quan
            if ($ma_danh_muc_cha_cap_1) {
                // Lấy tất cả danh mục con của danh mục cha cấp 1 (bao gồm cả danh mục cha)
                // Để lấy sản phẩm từ tất cả các danh mục con
                $allChildCategoryIds = $this->danhMucModel->layTatCaDanhMucCon($ma_danh_muc_cha_cap_1, $danhMucList);
                
                // Thêm chính danh mục cha cấp 1 vào danh sách
                $categoryIdsToFetch = array_merge([$ma_danh_muc_cha_cap_1], $allChildCategoryIds);
                
                // Lấy sản phẩm liên quan (tối đa 8 sản phẩm, loại trừ sản phẩm hiện tại)
                $sanPhamLienQuan = $this->thuocModel->laySanPhamLienQuan($thuoc['ma_danh_muc'], $ma_thuoc, $categoryIdsToFetch, 8);
            }
        }

        // Kiểm tra xem sản phẩm có đang trong chương trình sale không
        $sale = $this->saleModel->layTheoMaThuoc($ma_thuoc);
        
        // Giá hiện tại mặc định là giá gốc
        $gia_hien_tai = $thuoc['gia'];
        // Cờ xác định có sale hay không
        $co_sale = false;
        // Phần trăm giảm giá (mặc định: 0)
        $phan_tram_giam = 0;
        
        // Nếu có sale đang hoạt động, cập nhật giá và thông tin sale
        if ($sale && isset($sale['gia_sale'])) {
            $gia_hien_tai = $sale['gia_sale'];        // Giá hiện tại = giá sale
            $co_sale = true;                           // Đánh dấu có sale
            $phan_tram_giam = $sale['phan_tram_giam'] ?? 0;  // Phần trăm giảm giá
        }

        // Hiển thị view chi tiết thuốc
        // Truyền các biến: $thuoc, $danhMucHienTai, $danhMucCha, $sanPhamLienQuan, $co_sale, $gia_hien_tai, $phan_tram_giam
        require __DIR__ . '/../views/thuoc/chi_tiet.php';
    }
}
