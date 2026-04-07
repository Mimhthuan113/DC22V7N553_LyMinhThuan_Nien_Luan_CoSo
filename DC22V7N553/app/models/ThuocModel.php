<?php
/**
 * app/models/ThuocModel.php - Model quản lý thuốc/sản phẩm
 * 
 * Class này chứa tất cả các phương thức để thao tác với bảng thuoc trong database
 * Bao gồm: thêm, sửa, xóa, tìm kiếm, lọc theo danh mục, kiểm tra hạn sử dụng, ...
 */

// Nạp class Model cơ sở
require_once __DIR__ . '/../core/Model.php';

class ThuocModel extends Model
{
    /**
     * Lấy tất cả thuốc với thông tin danh mục (dành cho admin)
     * 
     * @param int|null $limit Số lượng thuốc cần lấy (null = lấy tất cả)
     * @param int $offset Vị trí bắt đầu (dùng cho phân trang)
     * @param string $search Từ khóa tìm kiếm (tìm trong tên thuốc và mô tả)
     * @return array Mảng các thuốc với thông tin danh mục
     */
    public function layTatCa($limit = null, $offset = 0, $search = '')
    {
        // Query SQL: Lấy tất cả thông tin thuốc và tên danh mục
        // LEFT JOIN: Lấy cả thuốc không có danh mục
        $sql = "SELECT t.*, d.ten_danh_muc 
                FROM thuoc t
                LEFT JOIN danh_muc d ON t.ma_danh_muc = d.ma_danh_muc
                WHERE 1=1";  // WHERE 1=1 để dễ dàng thêm điều kiện sau
        
        // Mảng chứa các tham số để bind vào SQL (tránh SQL Injection)
        $params = [];
        
        // Nếu có từ khóa tìm kiếm, thêm điều kiện tìm kiếm
        if (!empty($search)) {
            // Tìm trong tên thuốc hoặc mô tả
            $sql .= " AND (t.ten_thuoc LIKE :search OR t.mo_ta LIKE :search)";
            // % ở đầu và cuối để tìm kiếm phần từ
            $params[':search'] = '%' . $search . '%';
        }
        
        // Sắp xếp theo ngày tạo mới nhất trước
        $sql .= " ORDER BY t.ngay_tao DESC";
        
        // Nếu có giới hạn số lượng, thêm LIMIT và OFFSET (phân trang)
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
        }
        
        // Chuẩn bị câu lệnh SQL
        $stmt = $this->db->prepare($sql);
        
        // Bind các tham số vào SQL
        foreach ($params as $key => $value) {
            // limit và offset phải là số nguyên
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                // Các tham số khác bind như string
                $stmt->bindValue($key, $value);
            }
        }
        
        // Thực thi query
        $stmt->execute();
        
        // Trả về tất cả kết quả dưới dạng mảng associative
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết 1 thuốc theo mã với thông tin danh mục
     * 
     * @param int $ma_thuoc Mã thuốc cần lấy
     * @return array|null Thông tin thuốc hoặc null nếu không tìm thấy
     */
    public function layTheoMa($ma_thuoc)
    {
        // Query SQL: Lấy thông tin thuốc và tên danh mục theo mã thuốc
        $sql = "SELECT t.*, d.ten_danh_muc 
                FROM thuoc t
                LEFT JOIN danh_muc d ON t.ma_danh_muc = d.ma_danh_muc
                WHERE t.ma_thuoc = :ma_thuoc";
        
        // Chuẩn bị câu lệnh SQL
        $stmt = $this->db->prepare($sql);
        
        // Bind mã thuốc (phải là số nguyên)
        $stmt->bindValue(':ma_thuoc', $ma_thuoc, PDO::PARAM_INT);
        
        // Thực thi query
        $stmt->execute();
        
        // Trả về 1 dòng kết quả (hoặc null nếu không tìm thấy)
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm thuốc mới vào database
     * 
     * @param array $data Mảng chứa thông tin thuốc:
     *   - ma_danh_muc: Mã danh mục
     *   - ten_thuoc: Tên thuốc (bắt buộc)
     *   - slug: URL thân thiện (tự động tạo nếu không có)
     *   - mo_ta: Mô tả
     *   - huong_dan_dung: Hướng dẫn sử dụng
     *   - lieu_dung: Liều dùng
     *   - chong_chi_dinh: Chống chỉ định
     *   - gia: Giá bán (bắt buộc)
     *   - don_vi: Đơn vị (mặc định: "Hộp")
     *   - hinh_anh đến hinh_anh_5: Đường dẫn ảnh (tối đa 5 ảnh)
     *   - so_luong_ton: Số lượng tồn kho (mặc định: 0)
     *   - han_su_dung: Hạn sử dụng (có thể null)
     *   - trang_thai: Trạng thái (1 = hoạt động, 0 = tạm ngưng, mặc định: 1)
     * @return int Mã thuốc vừa được tạo (lastInsertId)
     */
    public function themThuoc($data)
    {
        // Query SQL: Thêm thuốc mới vào bảng thuoc
        $sql = "INSERT INTO thuoc (ma_danh_muc, ten_thuoc, slug, mo_ta, huong_dan_dung, lieu_dung, chong_chi_dinh, gia, don_vi, hinh_anh, hinh_anh_2, hinh_anh_3, hinh_anh_4, hinh_anh_5, so_luong_ton, han_su_dung, trang_thai)
                VALUES (:ma_danh_muc, :ten_thuoc, :slug, :mo_ta, :huong_dan_dung, :lieu_dung, :chong_chi_dinh, :gia, :don_vi, :hinh_anh, :hinh_anh_2, :hinh_anh_3, :hinh_anh_4, :hinh_anh_5, :so_luong_ton, :han_su_dung, :trang_thai)";
        
        // Chuẩn bị câu lệnh SQL
        $stmt = $this->db->prepare($sql);
        
        // Thực thi với dữ liệu từ mảng $data
        // ?? null: Nếu không có giá trị thì dùng null
        // ?? 'Hộp': Nếu không có đơn vị thì mặc định là "Hộp"
        // ?? 0: Nếu không có số lượng thì mặc định là 0
        // ?? 1: Nếu không có trạng thái thì mặc định là 1 (hoạt động)
        $stmt->execute([
            ':ma_danh_muc'     => $data['ma_danh_muc'],              // Mã danh mục
            ':ten_thuoc'       => $data['ten_thuoc'],                // Tên thuốc (bắt buộc)
            ':slug'            => $data['slug'] ?? null,             // URL thân thiện
            ':mo_ta'           => $data['mo_ta'] ?? null,            // Mô tả
            ':huong_dan_dung'  => $data['huong_dan_dung'] ?? null,  // Hướng dẫn sử dụng
            ':lieu_dung'       => $data['lieu_dung'] ?? null,       // Liều dùng
            ':chong_chi_dinh'  => $data['chong_chi_dinh'] ?? null,  // Chống chỉ định
            ':gia'             => $data['gia'],                     // Giá bán (bắt buộc)
            ':don_vi'          => $data['don_vi'] ?? 'Hộp',          // Đơn vị (mặc định: Hộp)
            ':hinh_anh'        => $data['hinh_anh'] ?? null,         // Ảnh chính
            ':hinh_anh_2'      => $data['hinh_anh_2'] ?? null,       // Ảnh phụ 1
            ':hinh_anh_3'      => $data['hinh_anh_3'] ?? null,      // Ảnh phụ 2
            ':hinh_anh_4'      => $data['hinh_anh_4'] ?? null,      // Ảnh phụ 3
            ':hinh_anh_5'      => $data['hinh_anh_5'] ?? null,      // Ảnh phụ 4
            ':so_luong_ton'    => $data['so_luong_ton'] ?? 0,        // Số lượng tồn kho (mặc định: 0)
            ':han_su_dung'     => $data['han_su_dung'] ?? null,     // Hạn sử dụng
            ':trang_thai'      => $data['trang_thai'] ?? 1,          // Trạng thái (1 = hoạt động)
        ]);
        
        // Trả về mã thuốc vừa được tạo (auto increment)
        return $this->db->lastInsertId();
    }

    /**
     * Cập nhật thông tin thuốc
     * 
     * @param int $ma_thuoc Mã thuốc cần cập nhật
     * @param array $data Mảng chứa thông tin cần cập nhật (tương tự như themThuoc)
     * @return bool true nếu cập nhật thành công, false nếu không có dòng nào được cập nhật
     */
    public function capNhatThuoc($ma_thuoc, $data)
    {
        // Query SQL: Cập nhật tất cả các trường của thuốc
        $sql = "UPDATE thuoc 
                SET ma_danh_muc = :ma_danh_muc,
                    ten_thuoc = :ten_thuoc,
                    slug = :slug,
                    mo_ta = :mo_ta,
                    huong_dan_dung = :huong_dan_dung,
                    lieu_dung = :lieu_dung,
                    chong_chi_dinh = :chong_chi_dinh,
                    gia = :gia,
                    don_vi = :don_vi,
                    hinh_anh = :hinh_anh,
                    hinh_anh_2 = :hinh_anh_2,
                    hinh_anh_3 = :hinh_anh_3,
                    hinh_anh_4 = :hinh_anh_4,
                    hinh_anh_5 = :hinh_anh_5,
                    so_luong_ton = :so_luong_ton,
                    han_su_dung = :han_su_dung,
                    trang_thai = :trang_thai
                WHERE ma_thuoc = :ma_thuoc";
        
        // Chuẩn bị câu lệnh SQL
        $stmt = $this->db->prepare($sql);
        
        // Thực thi với dữ liệu từ mảng $data và mã thuốc
        $stmt->execute([
            ':ma_danh_muc'     => $data['ma_danh_muc'],              // Mã danh mục
            ':ten_thuoc'       => $data['ten_thuoc'],                // Tên thuốc
            ':slug'            => $data['slug'] ?? null,             // URL thân thiện
            ':mo_ta'           => $data['mo_ta'] ?? null,           // Mô tả
            ':huong_dan_dung'  => $data['huong_dan_dung'] ?? null,  // Hướng dẫn sử dụng
            ':lieu_dung'       => $data['lieu_dung'] ?? null,      // Liều dùng
            ':chong_chi_dinh'  => $data['chong_chi_dinh'] ?? null,  // Chống chỉ định
            ':gia'             => $data['gia'],                     // Giá bán
            ':don_vi'          => $data['don_vi'] ?? 'Hộp',         // Đơn vị
            ':hinh_anh'        => $data['hinh_anh'] ?? null,        // Ảnh chính
            ':hinh_anh_2'      => $data['hinh_anh_2'] ?? null,      // Ảnh phụ 1
            ':hinh_anh_3'      => $data['hinh_anh_3'] ?? null,      // Ảnh phụ 2
            ':hinh_anh_4'      => $data['hinh_anh_4'] ?? null,      // Ảnh phụ 3
            ':hinh_anh_5'      => $data['hinh_anh_5'] ?? null,      // Ảnh phụ 4
            ':so_luong_ton'    => $data['so_luong_ton'] ?? 0,       // Số lượng tồn kho
            ':han_su_dung'     => $data['han_su_dung'] ?? null,    // Hạn sử dụng
            ':trang_thai'      => $data['trang_thai'] ?? 1,         // Trạng thái
            ':ma_thuoc'        => $ma_thuoc                          // Mã thuốc cần cập nhật
        ]);
        
        // Trả về true nếu có ít nhất 1 dòng được cập nhật
        return $stmt->rowCount() > 0;
    }

    /**
     * Xóa thuốc khỏi database
     * 
     * @param int $ma_thuoc Mã thuốc cần xóa
     * @return bool true nếu xóa thành công, false nếu không tìm thấy thuốc
     */
    public function xoaThuoc($ma_thuoc)
    {
        // Query SQL: Xóa thuốc theo mã
        $sql = "DELETE FROM thuoc WHERE ma_thuoc = :ma_thuoc";
        
        // Chuẩn bị câu lệnh SQL
        $stmt = $this->db->prepare($sql);
        
        // Bind mã thuốc (phải là số nguyên)
        $stmt->bindValue(':ma_thuoc', $ma_thuoc, PDO::PARAM_INT);
        
        // Thực thi query
        $stmt->execute();
        
        // Trả về true nếu có ít nhất 1 dòng bị xóa
        return $stmt->rowCount() > 0;
    }

    /**
     * Đếm tổng số thuốc trong database (dành cho admin)
     * 
     * @param string $search Từ khóa tìm kiếm (tìm trong tên thuốc và mô tả)
     * @return int Tổng số thuốc
     */
    public function demTongSo($search = '')
    {
        // Query SQL: Đếm tổng số thuốc
        $sql = "SELECT COUNT(*) as total FROM thuoc WHERE 1=1";
        
        // Mảng chứa các tham số
        $params = [];
        
        // Nếu có từ khóa tìm kiếm, thêm điều kiện
        if (!empty($search)) {
            $sql .= " AND (ten_thuoc LIKE :search OR mo_ta LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        // Lấy kết quả
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Trả về tổng số (hoặc 0 nếu không có)
        return $result['total'] ?? 0;
    }

    /**
     * Tạo slug (URL thân thiện) từ tên thuốc
     * Ví dụ: "Paracetamol 500mg" -> "paracetamol-500mg"
     * 
     * @param string $ten_thuoc Tên thuốc
     * @return string Slug đã được tạo
     */
    public function taoSlug($ten_thuoc)
    {
        // Chuyển tên thuốc thành chữ thường và loại bỏ khoảng trắng đầu/cuối
        $slug = strtolower(trim($ten_thuoc));
        
        // Thay thế tất cả ký tự không phải chữ cái, số, dấu gạch ngang bằng dấu gạch ngang
        // Ví dụ: "Paracetamol 500mg!" -> "paracetamol-500mg-"
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        
        // Thay thế nhiều dấu gạch ngang liên tiếp bằng 1 dấu gạch ngang
        // Ví dụ: "paracetamol---500mg" -> "paracetamol-500mg"
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Loại bỏ dấu gạch ngang ở đầu và cuối
        // Ví dụ: "-paracetamol-500mg-" -> "paracetamol-500mg"
        $slug = trim($slug, '-');
        
        return $slug;
    }

    /**
     * Lấy tất cả thuốc cho khách hàng (chỉ hiển thị thuốc đang hoạt động và chưa hết hạn)
     * 
     * @param int|null $limit Số lượng thuốc cần lấy (null = lấy tất cả)
     * @param int $offset Vị trí bắt đầu (dùng cho phân trang)
     * @param string $search Từ khóa tìm kiếm
     * @return array Mảng các thuốc với thông tin danh mục
     */
    public function layTatCaChoKhachHang($limit = null, $offset = 0, $search = '')
    {
        // Query SQL: Lấy thuốc đang hoạt động và chưa hết hạn
        // trang_thai = 1: Thuốc đang hoạt động
        // han_su_dung IS NULL: Thuốc không có hạn sử dụng (luôn hiển thị)
        // han_su_dung >= CURDATE(): Thuốc chưa hết hạn (hạn sử dụng >= ngày hiện tại)
        $sql = "SELECT t.*, d.ten_danh_muc 
                FROM thuoc t
                LEFT JOIN danh_muc d ON t.ma_danh_muc = d.ma_danh_muc
                WHERE t.trang_thai = 1
                AND (t.han_su_dung IS NULL OR t.han_su_dung >= CURDATE())";
        
        // Mảng chứa các tham số
        $params = [];
        
        // Nếu có từ khóa tìm kiếm, thêm điều kiện
        if (!empty($search)) {
            $sql .= " AND (t.ten_thuoc LIKE :search OR t.mo_ta LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        // Sắp xếp theo ngày tạo mới nhất trước
        $sql .= " ORDER BY t.ngay_tao DESC";
        
        // Nếu có giới hạn, thêm LIMIT và OFFSET
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
        }
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách thuốc sắp hết hạn (trong vòng 30 ngày tới)
     * Dùng để cảnh báo admin/nhân viên
     * 
     * @param int|null $limit Số lượng thuốc cần lấy (null = lấy tất cả)
     * @param int $offset Vị trí bắt đầu (dùng cho phân trang)
     * @return array Mảng các thuốc sắp hết hạn với thông tin:
     *   - so_ngay_con_lai: Số ngày còn lại trước khi hết hạn
     *   - tong_chi_phi: Tổng chi phí (giá × số lượng tồn kho)
     */
    public function layThuocSapHetHan($limit = null, $offset = 0)
    {
        // Query SQL: Lấy thuốc sắp hết hạn trong 30 ngày tới
        // DATEDIFF: Tính số ngày còn lại (hạn sử dụng - ngày hiện tại)
        // tong_chi_phi: Tính tổng chi phí (giá × số lượng tồn kho)
        $sql = "SELECT t.*, d.ten_danh_muc,
                DATEDIFF(t.han_su_dung, CURDATE()) as so_ngay_con_lai,
                (t.gia * t.so_luong_ton) as tong_chi_phi
                FROM thuoc t
                LEFT JOIN danh_muc d ON t.ma_danh_muc = d.ma_danh_muc
                WHERE t.han_su_dung IS NOT NULL                    -- Phải có hạn sử dụng
                AND t.han_su_dung >= CURDATE()                     -- Chưa hết hạn
                AND t.han_su_dung <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)  -- Trong vòng 30 ngày tới
                ORDER BY t.han_su_dung ASC";                       // Sắp xếp theo hạn sử dụng (sắp hết trước)
        
        // Nếu có giới hạn, thêm LIMIT và OFFSET
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách thuốc đã hết hạn
     * Dùng để admin/nhân viên xử lý thuốc hết hạn
     * 
     * @param int|null $limit Số lượng thuốc cần lấy (null = lấy tất cả)
     * @param int $offset Vị trí bắt đầu (dùng cho phân trang)
     * @return array Mảng các thuốc đã hết hạn với thông tin:
     *   - so_ngay_het_han: Số ngày đã hết hạn
     *   - tong_chi_phi: Tổng chi phí (giá × số lượng tồn kho)
     */
    public function layThuocHetHan($limit = null, $offset = 0)
    {
        // Query SQL: Lấy thuốc đã hết hạn
        // DATEDIFF: Tính số ngày đã hết hạn (ngày hiện tại - hạn sử dụng)
        // tong_chi_phi: Tính tổng chi phí (giá × số lượng tồn kho)
        $sql = "SELECT t.*, d.ten_danh_muc,
                DATEDIFF(CURDATE(), t.han_su_dung) as so_ngay_het_han,
                (t.gia * t.so_luong_ton) as tong_chi_phi
                FROM thuoc t
                LEFT JOIN danh_muc d ON t.ma_danh_muc = d.ma_danh_muc
                WHERE t.han_su_dung IS NOT NULL    -- Phải có hạn sử dụng
                AND t.han_su_dung < CURDATE()      -- Đã hết hạn (hạn sử dụng < ngày hiện tại)
                ORDER BY t.han_su_dung DESC";      // Sắp xếp theo hạn sử dụng (hết hạn lâu nhất trước)
        
        // Nếu có giới hạn, thêm LIMIT và OFFSET
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tính tổng chi phí của tất cả thuốc đã hết hạn
     * Dùng để báo cáo tổn thất do thuốc hết hạn
     * 
     * @return float Tổng chi phí (giá × số lượng tồn kho của tất cả thuốc hết hạn)
     */
    public function tinhTongChiPhiThuocHetHan()
    {
        // Query SQL: Tính tổng chi phí (SUM của giá × số lượng tồn kho)
        $sql = "SELECT SUM(gia * so_luong_ton) as tong_chi_phi
                FROM thuoc
                WHERE han_su_dung IS NOT NULL    -- Phải có hạn sử dụng
                AND han_su_dung < CURDATE()";    // Đã hết hạn
        
        // Thực thi query (không cần prepare vì không có tham số từ người dùng)
        $stmt = $this->db->query($sql);
        
        // Lấy kết quả
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Trả về tổng chi phí (hoặc 0 nếu không có)
        return $result['tong_chi_phi'] ?? 0;
    }

    /**
     * Tính tổng chi phí của tất cả thuốc sắp hết hạn (trong 30 ngày tới)
     * Dùng để báo cáo rủi ro
     * 
     * @return float Tổng chi phí (giá × số lượng tồn kho của tất cả thuốc sắp hết hạn)
     */
    public function tinhTongChiPhiThuocSapHetHan()
    {
        // Query SQL: Tính tổng chi phí của thuốc sắp hết hạn trong 30 ngày
        $sql = "SELECT SUM(gia * so_luong_ton) as tong_chi_phi
                FROM thuoc
                WHERE han_su_dung IS NOT NULL                    -- Phải có hạn sử dụng
                AND han_su_dung >= CURDATE()                     -- Chưa hết hạn
                AND han_su_dung <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";  // Trong vòng 30 ngày tới
        
        // Thực thi query
        $stmt = $this->db->query($sql);
        
        // Lấy kết quả
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Trả về tổng chi phí (hoặc 0 nếu không có)
        return $result['tong_chi_phi'] ?? 0;
    }

    /**
     * Đếm số lượng thuốc sắp hết hạn (trong 30 ngày tới)
     * Dùng để hiển thị cảnh báo trên dashboard
     * 
     * @return int Số lượng thuốc sắp hết hạn
     */
    public function demThuocSapHetHan()
    {
        // Query SQL: Đếm số lượng thuốc sắp hết hạn
        $sql = "SELECT COUNT(*) as total
                FROM thuoc
                WHERE han_su_dung IS NOT NULL                    -- Phải có hạn sử dụng
                AND han_su_dung >= CURDATE()                     -- Chưa hết hạn
                AND han_su_dung <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";  // Trong vòng 30 ngày tới
        
        // Thực thi query
        $stmt = $this->db->query($sql);
        
        // Lấy kết quả
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Trả về số lượng (hoặc 0 nếu không có)
        return $result['total'] ?? 0;
    }

    /**
     * Đếm số lượng thuốc đã hết hạn
     * Dùng để hiển thị cảnh báo trên dashboard
     * 
     * @return int Số lượng thuốc đã hết hạn
     */
    public function demThuocHetHan()
    {
        // Query SQL: Đếm số lượng thuốc đã hết hạn
        $sql = "SELECT COUNT(*) as total
                FROM thuoc
                WHERE han_su_dung IS NOT NULL    -- Phải có hạn sử dụng
                AND han_su_dung < CURDATE()";    // Đã hết hạn
        
        // Thực thi query
        $stmt = $this->db->query($sql);
        
        // Lấy kết quả
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Trả về số lượng (hoặc 0 nếu không có)
        return $result['total'] ?? 0;
    }

    /**
     * Lấy thuốc theo danh mục (chỉ hiển thị thuốc đang hoạt động và chưa hết hạn)
     * Dùng cho khách hàng xem sản phẩm theo danh mục
     * 
     * @param int $ma_danh_muc Mã danh mục cần lấy
     * @param int $limit Số lượng thuốc cần lấy (mặc định: 4)
     * @param int $offset Vị trí bắt đầu (dùng cho phân trang, mặc định: 0)
     * @return array Mảng các thuốc trong danh mục
     */
    public function layTheoDanhMuc($ma_danh_muc, $limit = 4, $offset = 0)
    {
        // Query SQL: Lấy thuốc theo danh mục, chỉ hiển thị thuốc đang hoạt động và chưa hết hạn
        $sql = "SELECT t.*, d.ten_danh_muc 
                FROM thuoc t
                LEFT JOIN danh_muc d ON t.ma_danh_muc = d.ma_danh_muc
                WHERE t.trang_thai = 1                           -- Đang hoạt động
                AND t.ma_danh_muc = :ma_danh_muc                  -- Thuộc danh mục này
                AND (t.han_su_dung IS NULL OR t.han_su_dung >= CURDATE())  -- Chưa hết hạn
                ORDER BY t.ngay_tao DESC                          -- Sắp xếp mới nhất trước
                LIMIT :limit OFFSET :offset";                     // Giới hạn số lượng
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_danh_muc', $ma_danh_muc, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy sản phẩm liên quan (cùng danh mục cha cấp 1, loại trừ sản phẩm hiện tại)
     * Dùng để hiển thị "Sản phẩm liên quan" trên trang chi tiết
     * 
     * @param int $ma_danh_muc Mã danh mục của sản phẩm hiện tại
     * @param int $ma_thuoc_hien_tai Mã thuốc hiện tại (để loại trừ)
     * @param array $danhSachMaDanhMuc Danh sách mã danh mục cùng cấp (bao gồm danh mục cha và các danh mục con)
     * @param int $limit Số lượng sản phẩm cần lấy (mặc định: 8)
     * @return array Mảng các sản phẩm liên quan
     */
    public function laySanPhamLienQuan($ma_danh_muc, $ma_thuoc_hien_tai, $danhSachMaDanhMuc, $limit = 8)
    {
        // Nếu không có danh sách danh mục, trả về mảng rỗng
        if (empty($danhSachMaDanhMuc)) {
            return [];
        }
        
        // Tạo placeholders cho IN clause (ví dụ: :ma_dm_0, :ma_dm_1, :ma_dm_2)
        // Để tránh SQL Injection khi dùng IN với nhiều giá trị
        $placeholders = [];
        $params = [':ma_thuoc_hien_tai' => $ma_thuoc_hien_tai];  // Tham số loại trừ sản phẩm hiện tại
        foreach ($danhSachMaDanhMuc as $index => $ma_dm) {
            $key = ':ma_dm_' . $index;  // Tạo key duy nhất cho mỗi danh mục
            $placeholders[] = $key;     // Thêm vào mảng placeholders
            $params[$key] = $ma_dm;     // Lưu giá trị vào mảng params
        }
        
        // Query SQL: Lấy thuốc trong các danh mục liên quan, loại trừ sản phẩm hiện tại
        $sql = "SELECT t.*, d.ten_danh_muc 
                FROM thuoc t
                LEFT JOIN danh_muc d ON t.ma_danh_muc = d.ma_danh_muc
                WHERE t.trang_thai = 1                           -- Đang hoạt động
                AND t.ma_danh_muc IN (" . implode(',', $placeholders) . ")  -- Trong các danh mục liên quan
                AND t.ma_thuoc != :ma_thuoc_hien_tai             -- Loại trừ sản phẩm hiện tại
                AND (t.han_su_dung IS NULL OR t.han_su_dung >= CURDATE())  -- Chưa hết hạn
                ORDER BY t.ngay_tao DESC                          -- Sắp xếp mới nhất trước
                LIMIT :limit";                                    // Giới hạn số lượng
        
        // Thêm limit vào params
        $params[':limit'] = $limit;
        
        // Chuẩn bị và bind các tham số
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === ':limit') {
                // Limit phải là số nguyên
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                // Các mã danh mục và mã thuốc cũng phải là số nguyên
                $stmt->bindValue($key, (int)$value, PDO::PARAM_INT);
            }
        }
        
        // Thực thi query
        $stmt->execute();
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thuốc theo nhiều danh mục (bao gồm cả danh mục con)
     * Dùng để hiển thị sản phẩm theo nhóm danh mục trên trang chủ
     * 
     * @param array $danhSachMaDanhMuc Danh sách mã danh mục cần lấy
     * @param int|null $limit Số lượng thuốc cần lấy (null = lấy tất cả)
     * @param int $offset Vị trí bắt đầu (dùng cho phân trang)
     * @return array Mảng các thuốc trong các danh mục
     */
    public function layTheoNhieuDanhMuc($danhSachMaDanhMuc, $limit = null, $offset = 0)
    {
        // Nếu không có danh sách danh mục, trả về mảng rỗng
        if (empty($danhSachMaDanhMuc)) {
            return [];
        }
        
        // Tạo placeholders cho IN clause (tránh SQL Injection)
        $placeholders = [];
        $params = [];
        foreach ($danhSachMaDanhMuc as $index => $ma_dm) {
            $key = ':ma_dm_' . $index;  // Tạo key duy nhất
            $placeholders[] = $key;      // Thêm vào mảng placeholders
            $params[$key] = $ma_dm;      // Lưu giá trị
        }
        
        // Query SQL: Lấy thuốc trong các danh mục
        $sql = "SELECT t.*, d.ten_danh_muc 
                FROM thuoc t
                LEFT JOIN danh_muc d ON t.ma_danh_muc = d.ma_danh_muc
                WHERE t.trang_thai = 1                           -- Đang hoạt động
                AND t.ma_danh_muc IN (" . implode(',', $placeholders) . ")  -- Trong các danh mục
                AND (t.han_su_dung IS NULL OR t.han_su_dung >= CURDATE())  -- Chưa hết hạn
                ORDER BY t.ngay_tao DESC";                      // Sắp xếp mới nhất trước
        
        // Nếu có giới hạn, thêm LIMIT và OFFSET
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
        }
        
        // Chuẩn bị và bind các tham số
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                // Limit và offset phải là số nguyên
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                // Các mã danh mục cũng phải là số nguyên
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
        }
        
        // Thực thi query
        $stmt->execute();
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đếm tổng số thuốc cho khách hàng (chỉ đếm thuốc đang hoạt động và chưa hết hạn)
     * Dùng cho phân trang
     * 
     * @param string $search Từ khóa tìm kiếm (tìm trong tên thuốc và mô tả)
     * @return int Tổng số thuốc
     */
    public function demTongSoChoKhachHang($search = '')
    {
        // Query SQL: Đếm tổng số thuốc đang hoạt động và chưa hết hạn
        $sql = "SELECT COUNT(*) as total
                FROM thuoc t
                WHERE t.trang_thai = 1                           -- Đang hoạt động
                AND (t.han_su_dung IS NULL OR t.han_su_dung >= CURDATE())";  // Chưa hết hạn
        
        // Mảng chứa các tham số
        $params = [];
        
        // Nếu có từ khóa tìm kiếm, thêm điều kiện
        if (!empty($search)) {
            $sql .= " AND (t.ten_thuoc LIKE :search OR t.mo_ta LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        // Lấy kết quả
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Trả về tổng số (hoặc 0 nếu không có)
        return $result['total'] ?? 0;
    }

    /**
     * Lấy gợi ý tìm kiếm (cho autocomplete)
     * Dùng để hiển thị dropdown gợi ý khi người dùng gõ vào ô tìm kiếm
     * 
     * @param string $search Từ khóa tìm kiếm
     * @param int $limit Số lượng gợi ý tối đa (mặc định: 5)
     * @return array Mảng các thuốc gợi ý với thông tin: ma_thuoc, ten_thuoc, gia, don_vi, hinh_anh, ten_danh_muc
     */
    public function layGoiYTimKiem($search, $limit = 5)
    {
        // Query SQL: Lấy gợi ý tìm kiếm với sắp xếp thông minh
        // Sắp xếp theo độ khớp:
        //   1. Tên thuốc khớp chính xác (ưu tiên cao nhất)
        //   2. Tên thuốc bắt đầu bằng từ khóa (ưu tiên trung bình)
        //   3. Tên thuốc chứa từ khóa (ưu tiên thấp nhất)
        $sql = "SELECT t.ma_thuoc, t.ten_thuoc, t.gia, t.don_vi, t.hinh_anh, d.ten_danh_muc
                FROM thuoc t
                LEFT JOIN danh_muc d ON t.ma_danh_muc = d.ma_danh_muc
                WHERE t.trang_thai = 1                           -- Đang hoạt động
                AND (t.han_su_dung IS NULL OR t.han_su_dung >= CURDATE())  -- Chưa hết hạn
                AND (t.ten_thuoc LIKE :search OR t.mo_ta LIKE :search)      -- Tìm trong tên hoặc mô tả
                ORDER BY 
                    CASE 
                        WHEN t.ten_thuoc = :search_exact THEN 1   -- Khớp chính xác: ưu tiên 1
                        WHEN t.ten_thuoc LIKE :search_start THEN 2  -- Bắt đầu bằng: ưu tiên 2
                        ELSE 3                                     -- Chứa từ khóa: ưu tiên 3
                    END,
                    t.ten_thuoc                                   -- Sắp xếp thứ cấp theo tên
                LIMIT :limit";                                    // Giới hạn số lượng
        
        // Chuẩn bị câu lệnh SQL
        $stmt = $this->db->prepare($sql);
        
        // Tạo các pattern tìm kiếm
        $searchPattern = '%' . $search . '%';    // Tìm ở bất kỳ đâu: "%từ khóa%"
        $searchExact = $search;                  // Khớp chính xác: "từ khóa"
        $searchStart = $search . '%';            // Bắt đầu bằng: "từ khóa%"
        
        // Bind các tham số
        $stmt->bindValue(':search', $searchPattern);        // Pattern tìm kiếm chung
        $stmt->bindValue(':search_exact', $searchExact);    // Khớp chính xác
        $stmt->bindValue(':search_start', $searchStart);    // Bắt đầu bằng
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT); // Giới hạn số lượng
        
        // Thực thi query
        $stmt->execute();
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
