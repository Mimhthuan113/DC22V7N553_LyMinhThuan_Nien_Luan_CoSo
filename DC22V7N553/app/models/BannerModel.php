<?php
/**
 * app/models/BannerModel.php - Model quản lý banner
 * 
 * Class này chứa tất cả các phương thức để thao tác với bảng banner:
 * - Thêm, sửa, xóa banner
 * - Lấy banner đang hiển thị (dành cho khách hàng)
 * - Sắp xếp banner theo thứ tự (thu_tu)
 */

// Nạp class Model cơ sở
require_once __DIR__ . '/../core/Model.php';

class BannerModel extends Model
{
    /**
     * Lấy tất cả banner (dành cho admin)
     * 
     * @param int|null $limit Số lượng banner cần lấy (null = lấy tất cả)
     * @param int $offset Vị trí bắt đầu (dùng cho phân trang)
     * @param string $search Từ khóa tìm kiếm (tìm trong tiêu đề)
     * @return array Mảng tất cả các banner
     */
    public function layTatCa($limit = null, $offset = 0, $search = '')
    {
        // Query SQL: Lấy tất cả banner
        $sql = "SELECT * FROM banner WHERE 1=1";
        $params = [];

        // Nếu có từ khóa tìm kiếm, thêm điều kiện
        if (!empty($search)) {
            $sql .= " AND (tieu_de LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        // Sắp xếp theo thứ tự (thu_tu) tăng dần, sau đó theo ngày tạo mới nhất
        // thu_tu nhỏ hơn sẽ hiển thị trước
        $sql .= " ORDER BY thu_tu ASC, ngay_tao DESC";

        // Nếu có giới hạn, thêm LIMIT và OFFSET
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
        }

        // Chuẩn bị và bind các tham số
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            // Xác định kiểu dữ liệu: limit và offset là INT, còn lại là STRING
            $type = ($key === ':limit' || $key === ':offset') ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->execute();
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy banner đang hiển thị (dành cho khách hàng)
     * Chỉ lấy banner có trang_thai = 1
     * 
     * @param int|null $limit Số lượng banner cần lấy (null = lấy tất cả)
     * @param int $offset Vị trí bắt đầu (dùng cho phân trang)
     * @return array Mảng các banner đang hiển thị, sắp xếp theo thứ tự
     */
    public function layDangHienThi($limit = null, $offset = 0)
    {
        // Query SQL: Lấy banner đang hiển thị
        // trang_thai = 1: Banner đang hoạt động
        // Sắp xếp theo thứ tự (thu_tu) tăng dần, sau đó theo ngày tạo mới nhất
        $sql = "SELECT * FROM banner WHERE trang_thai = 1 ORDER BY thu_tu ASC, ngay_tao DESC";
        
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
     * Lấy thông tin banner theo mã
     * 
     * @param int $id Mã banner cần lấy
     * @return array|null Thông tin banner hoặc null nếu không tìm thấy
     */
    public function layTheoMa($id)
    {
        // Query SQL: Lấy banner theo mã
        $sql = "SELECT * FROM banner WHERE ma_banner = :id";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Trả về 1 dòng kết quả (hoặc null nếu không tìm thấy)
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm banner mới vào database
     * 
     * @param array $data Mảng chứa thông tin banner:
     *   - tieu_de: Tiêu đề banner (tùy chọn)
     *   - hinh_anh: Ảnh chính (bắt buộc)
     *   - hinh_anh_2 đến hinh_anh_5: Ảnh phụ (tùy chọn, tối đa 4 ảnh)
     *   - thu_tu: Thứ tự hiển thị (số nhỏ hơn hiển thị trước, mặc định: 0)
     *   - trang_thai: Trạng thái (1 = hiển thị, 0 = ẩn, mặc định: 1)
     * @return int Mã banner vừa được tạo (lastInsertId)
     */
    public function them($data)
    {
        // Query SQL: Thêm banner mới
        // Lưu ý: ma_danh_muc và lien_ket được set = null (không sử dụng)
        $sql = "INSERT INTO banner (ma_danh_muc, tieu_de, hinh_anh, hinh_anh_2, hinh_anh_3, hinh_anh_4, hinh_anh_5, lien_ket, thu_tu, trang_thai)
                VALUES (:ma_danh_muc, :tieu_de, :hinh_anh, :hinh_anh_2, :hinh_anh_3, :hinh_anh_4, :hinh_anh_5, :lien_ket, :thu_tu, :trang_thai)";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ma_danh_muc' => null,                    // Không sử dụng danh mục
            ':tieu_de' => $data['tieu_de'] ?? null,    // Tiêu đề (tùy chọn)
            ':hinh_anh' => $data['hinh_anh'],          // Ảnh chính (bắt buộc)
            ':hinh_anh_2' => $data['hinh_anh_2'] ?? null,  // Ảnh phụ 1
            ':hinh_anh_3' => $data['hinh_anh_3'] ?? null,  // Ảnh phụ 2
            ':hinh_anh_4' => $data['hinh_anh_4'] ?? null,  // Ảnh phụ 3
            ':hinh_anh_5' => $data['hinh_anh_5'] ?? null,  // Ảnh phụ 4
            ':lien_ket' => null,                        // Không sử dụng URL
            ':thu_tu' => $data['thu_tu'] ?? 0,          // Thứ tự (mặc định: 0)
            ':trang_thai' => $data['trang_thai'] ?? 1,  // Trạng thái (mặc định: 1 = hiển thị)
        ]);
        
        // Trả về mã banner vừa được tạo
        return $this->db->lastInsertId();
    }

    /**
     * Cập nhật thông tin banner
     * 
     * @param int $id Mã banner cần cập nhật
     * @param array $data Mảng chứa thông tin cần cập nhật (tương tự như them)
     * @return bool true nếu cập nhật thành công, false nếu không
     */
    public function capNhat($id, $data)
    {
        // Query SQL: Cập nhật thông tin banner
        $sql = "UPDATE banner SET 
                    ma_danh_muc = :ma_danh_muc,
                    tieu_de = :tieu_de,
                    hinh_anh = :hinh_anh,
                    hinh_anh_2 = :hinh_anh_2,
                    hinh_anh_3 = :hinh_anh_3,
                    hinh_anh_4 = :hinh_anh_4,
                    hinh_anh_5 = :hinh_anh_5,
                    lien_ket = :lien_ket,
                    thu_tu = :thu_tu,
                    trang_thai = :trang_thai
                WHERE ma_banner = :id";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,                               // Mã banner cần cập nhật
            ':ma_danh_muc' => null,                     // Không sử dụng danh mục
            ':tieu_de' => $data['tieu_de'] ?? null,     // Tiêu đề
            ':hinh_anh' => $data['hinh_anh'],           // Ảnh chính
            ':hinh_anh_2' => $data['hinh_anh_2'] ?? null,  // Ảnh phụ 1
            ':hinh_anh_3' => $data['hinh_anh_3'] ?? null,  // Ảnh phụ 2
            ':hinh_anh_4' => $data['hinh_anh_4'] ?? null,  // Ảnh phụ 3
            ':hinh_anh_5' => $data['hinh_anh_5'] ?? null,  // Ảnh phụ 4
            ':lien_ket' => null,                        // Không sử dụng URL
            ':thu_tu' => $data['thu_tu'] ?? 0,          // Thứ tự
            ':trang_thai' => $data['trang_thai'] ?? 1,  // Trạng thái
        ]);
    }

    /**
     * Xóa banner khỏi database
     * 
     * @param int $id Mã banner cần xóa
     * @return bool true nếu xóa thành công, false nếu không
     */
    public function xoa($id)
    {
        // Query SQL: Xóa banner theo mã
        $sql = "DELETE FROM banner WHERE ma_banner = :id";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
