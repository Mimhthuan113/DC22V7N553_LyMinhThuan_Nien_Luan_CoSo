<?php
/**
 * app/models/TinTucModel.php - Model quản lý tin tức
 * 
 * Class này chứa tất cả các phương thức để thao tác với bảng tin_tuc:
 * - Thêm, sửa, xóa tin tức
 * - Lấy tin tức cho khách hàng (chỉ hiển thị tin đang hoạt động)
 * - Tăng lượt xem
 */

// Nạp class Model cơ sở
require_once __DIR__ . '/../core/Model.php';

class TinTucModel extends Model
{
    /**
     * Lấy tất cả tin tức (dành cho admin)
     * Lấy cả tin đang hoạt động và đã tạm ngưng
     * 
     * @param int|null $limit Số lượng tin tức cần lấy (null = lấy tất cả)
     * @param int $offset Vị trí bắt đầu (dùng cho phân trang)
     * @param string $search Từ khóa tìm kiếm (tìm trong tiêu đề, tóm tắt, nội dung)
     * @return array Mảng tất cả các tin tức
     */
    public function layTatCa($limit = null, $offset = 0, $search = '')
    {
        // Query SQL: Lấy tất cả tin tức
        $sql = "SELECT * FROM tin_tuc WHERE 1=1";  // WHERE 1=1 để dễ dàng thêm điều kiện
        
        // Mảng chứa các tham số
        $params = [];
        
        // Nếu có từ khóa tìm kiếm, thêm điều kiện
        if (!empty($search)) {
            // Tìm trong tiêu đề, tóm tắt, hoặc nội dung
            $sql .= " AND (tieu_de LIKE :search OR tom_tat LIKE :search OR noi_dung LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        // Sắp xếp theo ngày tạo mới nhất trước
        $sql .= " ORDER BY ngay_tao DESC";
        
        // Nếu có giới hạn, thêm LIMIT và OFFSET (phân trang)
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
                // Các tham số khác bind như string
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy tin tức cho khách hàng (chỉ hiển thị tin đang hoạt động)
     * 
     * @param int|null $limit Số lượng tin tức cần lấy (null = lấy tất cả)
     * @param int $offset Vị trí bắt đầu (dùng cho phân trang)
     * @return array Mảng các tin tức đang hoạt động
     */
    public function layTatCaChoKhachHang($limit = null, $offset = 0)
    {
        // Query SQL: Lấy tin tức đang hoạt động (trang_thai = 1)
        $sql = "SELECT * FROM tin_tuc 
                WHERE trang_thai = 1                    -- Chỉ lấy tin đang hoạt động
                ORDER BY ngay_tao DESC";                // Sắp xếp mới nhất trước
        
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
     * Lấy thông tin tin tức theo mã
     * 
     * @param int $ma_tin_tuc Mã tin tức cần lấy
     * @return array|null Thông tin tin tức hoặc null nếu không tìm thấy
     */
    public function layTheoMa($ma_tin_tuc)
    {
        // Query SQL: Lấy tin tức theo mã
        $sql = "SELECT * FROM tin_tuc WHERE ma_tin_tuc = :ma_tin_tuc";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_tin_tuc', $ma_tin_tuc, PDO::PARAM_INT);
        $stmt->execute();
        
        // Trả về 1 dòng kết quả (hoặc null nếu không tìm thấy)
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Đếm tổng số tin tức trong database
     * Dùng cho phân trang
     * 
     * @param string $search Từ khóa tìm kiếm (tìm trong tiêu đề, tóm tắt, nội dung)
     * @return int Tổng số tin tức
     */
    public function demTongSo($search = '')
    {
        // Query SQL: Đếm tổng số tin tức
        $sql = "SELECT COUNT(*) as total FROM tin_tuc WHERE 1=1";
        
        // Nếu có từ khóa tìm kiếm, thêm điều kiện
        if (!empty($search)) {
            $sql .= " AND (tieu_de LIKE :search OR tom_tat LIKE :search OR noi_dung LIKE :search)";
        }
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        if (!empty($search)) {
            $stmt->bindValue(':search', '%' . $search . '%');
        }
        $stmt->execute();
        
        // Lấy kết quả
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Trả về tổng số (ép kiểu int)
        return (int)$result['total'];
    }

    /**
     * Thêm tin tức mới vào database
     * 
     * @param array $data Mảng chứa thông tin tin tức:
     *   - tieu_de: Tiêu đề (bắt buộc)
     *   - slug: URL thân thiện (tự động tạo từ tiêu đề nếu không có)
     *   - tom_tat: Tóm tắt (tùy chọn)
     *   - noi_dung: Nội dung (bắt buộc)
     *   - hinh_anh đến hinh_anh_5: Đường dẫn ảnh (tối đa 5 ảnh, tùy chọn)
     *   - tac_gia: Tác giả (tùy chọn)
     *   - trang_thai: Trạng thái (1 = hoạt động, 0 = tạm ngưng, mặc định: 1)
     * @return int Mã tin tức vừa được tạo (lastInsertId)
     */
    public function themTinTuc($data)
    {
        // Query SQL: Thêm tin tức mới
        $sql = "INSERT INTO tin_tuc (tieu_de, slug, tom_tat, noi_dung, hinh_anh, hinh_anh_2, hinh_anh_3, hinh_anh_4, hinh_anh_5, tac_gia, trang_thai)
                VALUES (:tieu_de, :slug, :tom_tat, :noi_dung, :hinh_anh, :hinh_anh_2, :hinh_anh_3, :hinh_anh_4, :hinh_anh_5, :tac_gia, :trang_thai)";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tieu_de' => $data['tieu_de'],                        // Tiêu đề (bắt buộc)
            ':slug' => $data['slug'] ?? $this->taoSlug($data['tieu_de']),  // Slug (tự động tạo nếu không có)
            ':tom_tat' => $data['tom_tat'] ?? null,               // Tóm tắt (tùy chọn)
            ':noi_dung' => $data['noi_dung'],                      // Nội dung (bắt buộc)
            ':hinh_anh' => $data['hinh_anh'] ?? null,              // Ảnh chính
            ':hinh_anh_2' => $data['hinh_anh_2'] ?? null,          // Ảnh phụ 1
            ':hinh_anh_3' => $data['hinh_anh_3'] ?? null,          // Ảnh phụ 2
            ':hinh_anh_4' => $data['hinh_anh_4'] ?? null,          // Ảnh phụ 3
            ':hinh_anh_5' => $data['hinh_anh_5'] ?? null,          // Ảnh phụ 4
            ':tac_gia' => $data['tac_gia'] ?? null,                // Tác giả (tùy chọn)
            ':trang_thai' => $data['trang_thai'] ?? 1              // Trạng thái (mặc định: 1 = hoạt động)
        ]);
        
        // Trả về mã tin tức vừa được tạo
        return $this->db->lastInsertId();
    }

    /**
     * Cập nhật thông tin tin tức
     * 
     * @param int $ma_tin_tuc Mã tin tức cần cập nhật
     * @param array $data Mảng chứa thông tin cần cập nhật (tương tự như themTinTuc)
     * @return bool true nếu cập nhật thành công, false nếu không
     */
    public function capNhatTinTuc($ma_tin_tuc, $data)
    {
        // Query SQL: Cập nhật thông tin tin tức
        $sql = "UPDATE tin_tuc 
                SET tieu_de = :tieu_de,
                    slug = :slug,
                    tom_tat = :tom_tat,
                    noi_dung = :noi_dung,
                    hinh_anh = :hinh_anh,
                    hinh_anh_2 = :hinh_anh_2,
                    hinh_anh_3 = :hinh_anh_3,
                    hinh_anh_4 = :hinh_anh_4,
                    hinh_anh_5 = :hinh_anh_5,
                    tac_gia = :tac_gia,
                    trang_thai = :trang_thai
                WHERE ma_tin_tuc = :ma_tin_tuc";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':ma_tin_tuc' => $ma_tin_tuc,                           // Mã tin tức cần cập nhật
            ':tieu_de' => $data['tieu_de'],                         // Tiêu đề
            ':slug' => $data['slug'] ?? $this->taoSlug($data['tieu_de']),  // Slug (tự động tạo nếu không có)
            ':tom_tat' => $data['tom_tat'] ?? null,                // Tóm tắt
            ':noi_dung' => $data['noi_dung'],                       // Nội dung
            ':hinh_anh' => $data['hinh_anh'] ?? null,               // Ảnh chính
            ':hinh_anh_2' => $data['hinh_anh_2'] ?? null,           // Ảnh phụ 1
            ':hinh_anh_3' => $data['hinh_anh_3'] ?? null,           // Ảnh phụ 2
            ':hinh_anh_4' => $data['hinh_anh_4'] ?? null,           // Ảnh phụ 3
            ':hinh_anh_5' => $data['hinh_anh_5'] ?? null,           // Ảnh phụ 4
            ':tac_gia' => $data['tac_gia'] ?? null,                 // Tác giả
            ':trang_thai' => $data['trang_thai'] ?? 1              // Trạng thái
        ]);
    }

    /**
     * Xóa tin tức khỏi database
     * 
     * @param int $ma_tin_tuc Mã tin tức cần xóa
     * @return bool true nếu xóa thành công, false nếu không
     */
    public function xoaTinTuc($ma_tin_tuc)
    {
        // Query SQL: Xóa tin tức theo mã
        $sql = "DELETE FROM tin_tuc WHERE ma_tin_tuc = :ma_tin_tuc";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':ma_tin_tuc' => $ma_tin_tuc]);
    }

    /**
     * Tạo slug (URL thân thiện) từ tiêu đề tin tức (phương thức private)
     * Ví dụ: "Tin tức về thuốc" -> "tin-tuc-ve-thuoc"
     * 
     * @param string $tieu_de Tiêu đề tin tức
     * @return string Slug đã được tạo
     */
    private function taoSlug($tieu_de)
    {
        // Chuyển tiêu đề thành chữ thường (hỗ trợ UTF-8)
        $slug = mb_strtolower($tieu_de, 'UTF-8');
        
        // Loại bỏ tất cả ký tự không phải chữ cái, số, khoảng trắng, dấu gạch ngang
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        
        // Thay thế khoảng trắng và dấu gạch ngang liên tiếp bằng 1 dấu gạch ngang
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        
        // Loại bỏ dấu gạch ngang ở đầu và cuối
        $slug = trim($slug, '-');
        
        return $slug;
    }

    /**
     * Tăng lượt xem của tin tức
     * Mỗi lần người dùng xem tin tức, lượt xem sẽ tăng lên 1
     * 
     * @param int $ma_tin_tuc Mã tin tức cần tăng lượt xem
     * @return bool true nếu cập nhật thành công, false nếu không
     */
    public function tangLuotXem($ma_tin_tuc)
    {
        // Query SQL: Tăng lượt xem lên 1
        $sql = "UPDATE tin_tuc SET luot_xem = luot_xem + 1 WHERE ma_tin_tuc = :ma_tin_tuc";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':ma_tin_tuc' => $ma_tin_tuc]);
    }
}
