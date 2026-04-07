<?php
/**
 * app/models/DanhMucModel.php - Model quản lý danh mục
 * 
 * Class này chứa tất cả các phương thức để thao tác với bảng danh_muc:
 * - Thêm, sửa, xóa danh mục
 * - Quản lý danh mục cha và danh mục con (cấu trúc cây)
 * - Tạo slug từ tên danh mục
 */

// Nạp class Model cơ sở
require_once __DIR__ . '/../core/Model.php';

class DanhMucModel extends Model
{
    /**
     * Lấy tất cả danh mục với thông tin bổ sung
     * 
     * @return array Mảng tất cả danh mục với thông tin:
     *   - Thông tin danh mục (ma_danh_muc, ten_danh_muc, slug, mo_ta, ma_danh_muc_cha, trang_thai)
     *   - so_luong_thuoc: Số lượng thuốc trong danh mục này
     *   - ten_danh_muc_cha: Tên danh mục cha (nếu có)
     */
    public function layTatCa()
    {
        // Query SQL: Lấy tất cả danh mục với thông tin bổ sung
        // Subquery 1: Đếm số lượng thuốc trong mỗi danh mục
        // Subquery 2: Lấy tên danh mục cha (nếu có)
        $sql = "SELECT d.*, 
                (SELECT COUNT(*) FROM thuoc WHERE ma_danh_muc = d.ma_danh_muc) as so_luong_thuoc,
                (SELECT ten_danh_muc FROM danh_muc WHERE ma_danh_muc = d.ma_danh_muc_cha) as ten_danh_muc_cha
                FROM danh_muc d
                ORDER BY d.ten_danh_muc";  // Sắp xếp theo tên danh mục
        
        // Thực thi query (không cần prepare vì không có tham số từ người dùng)
        $stmt = $this->db->query($sql);
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin danh mục theo mã
     * 
     * @param int $ma_danh_muc Mã danh mục cần lấy
     * @return array|null Thông tin danh mục hoặc null nếu không tìm thấy
     */
    public function layTheoMa($ma_danh_muc)
    {
        // Query SQL: Lấy danh mục theo mã
        $sql = "SELECT * FROM danh_muc WHERE ma_danh_muc = :ma_danh_muc";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_danh_muc', $ma_danh_muc, PDO::PARAM_INT);
        $stmt->execute();
        
        // Trả về 1 dòng kết quả (hoặc null nếu không tìm thấy)
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm danh mục mới vào database
     * 
     * @param array $data Mảng chứa thông tin danh mục:
     *   - ten_danh_muc: Tên danh mục (bắt buộc)
     *   - slug: URL thân thiện (tự động tạo nếu không có)
     *   - mo_ta: Mô tả (tùy chọn)
     *   - ma_danh_muc_cha: Mã danh mục cha (null nếu là danh mục cấp 1)
     *   - trang_thai: Trạng thái (1 = hoạt động, 0 = tạm ngưng, mặc định: 1)
     * @return int Mã danh mục vừa được tạo (lastInsertId)
     */
    public function themDanhMuc($data)
    {
        // Query SQL: Thêm danh mục mới
        $sql = "INSERT INTO danh_muc (ten_danh_muc, slug, mo_ta, ma_danh_muc_cha, trang_thai)
                VALUES (:ten_danh_muc, :slug, :mo_ta, :ma_danh_muc_cha, :trang_thai)";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ten_danh_muc' => $data['ten_danh_muc'],              // Tên danh mục (bắt buộc)
            ':slug' => $data['slug'] ?? null,                      // URL thân thiện (tùy chọn)
            ':mo_ta' => $data['mo_ta'] ?? null,                    // Mô tả (tùy chọn)
            ':ma_danh_muc_cha' => $data['ma_danh_muc_cha'] ?? null, // Mã danh mục cha (null = danh mục cấp 1)
            ':trang_thai' => $data['trang_thai'] ?? 1              // Trạng thái (mặc định: 1 = hoạt động)
        ]);
        
        // Trả về mã danh mục vừa được tạo
        return $this->db->lastInsertId();
    }

    /**
     * Cập nhật thông tin danh mục
     * 
     * @param int $ma_danh_muc Mã danh mục cần cập nhật
     * @param array $data Mảng chứa thông tin cần cập nhật (tương tự như themDanhMuc)
     * @return bool true nếu cập nhật thành công, false nếu không
     */
    public function capNhatDanhMuc($ma_danh_muc, $data)
    {
        // Query SQL: Cập nhật thông tin danh mục
        $sql = "UPDATE danh_muc 
                SET ten_danh_muc = :ten_danh_muc, 
                    slug = :slug, 
                    mo_ta = :mo_ta, 
                    ma_danh_muc_cha = :ma_danh_muc_cha, 
                    trang_thai = :trang_thai
                WHERE ma_danh_muc = :ma_danh_muc";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ten_danh_muc' => $data['ten_danh_muc'],              // Tên danh mục
            ':slug' => $data['slug'] ?? null,                      // URL thân thiện
            ':mo_ta' => $data['mo_ta'] ?? null,                    // Mô tả
            ':ma_danh_muc_cha' => $data['ma_danh_muc_cha'] ?? null, // Mã danh mục cha
            ':trang_thai' => $data['trang_thai'] ?? 1,             // Trạng thái
            ':ma_danh_muc' => $ma_danh_muc                          // Mã danh mục cần cập nhật
        ]);
        
        // Trả về true nếu có ít nhất 1 dòng được cập nhật
        return $stmt->rowCount() > 0;
    }

    /**
     * Xóa danh mục khỏi database
     * Chỉ cho phép xóa nếu:
     *   - Không còn thuốc nào trong danh mục này
     *   - Không có danh mục con
     * 
     * @param int $ma_danh_muc Mã danh mục cần xóa
     * @return bool true nếu xóa thành công, false nếu không
     * @throws Exception Nếu danh mục còn thuốc hoặc còn danh mục con
     */
    public function xoaDanhMuc($ma_danh_muc)
    {
        // Kiểm tra xem danh mục có thuốc không
        // Không cho phép xóa nếu còn thuốc trong danh mục
        $sqlCheck = "SELECT COUNT(*) as count FROM thuoc WHERE ma_danh_muc = :ma_danh_muc";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->bindValue(':ma_danh_muc', $ma_danh_muc, PDO::PARAM_INT);
        $stmtCheck->execute();
        $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        // Nếu còn thuốc, ném exception
        if ($result['count'] > 0) {
            throw new Exception('Không thể xóa danh mục vì còn thuốc trong danh mục này');
        }
        
        // Kiểm tra xem có danh mục con không
        // Không cho phép xóa nếu còn danh mục con (để tránh mất cấu trúc cây)
        $sqlCheckChild = "SELECT COUNT(*) as count FROM danh_muc WHERE ma_danh_muc_cha = :ma_danh_muc";
        $stmtCheckChild = $this->db->prepare($sqlCheckChild);
        $stmtCheckChild->bindValue(':ma_danh_muc', $ma_danh_muc, PDO::PARAM_INT);
        $stmtCheckChild->execute();
        $resultChild = $stmtCheckChild->fetch(PDO::FETCH_ASSOC);
        
        // Nếu còn danh mục con, ném exception
        if ($resultChild['count'] > 0) {
            throw new Exception('Không thể xóa danh mục vì còn danh mục con');
        }
        
        // Nếu không có thuốc và không có danh mục con, cho phép xóa
        $sql = "DELETE FROM danh_muc WHERE ma_danh_muc = :ma_danh_muc";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_danh_muc', $ma_danh_muc, PDO::PARAM_INT);
        $stmt->execute();
        
        // Trả về true nếu có ít nhất 1 dòng bị xóa
        return $stmt->rowCount() > 0;
    }

    /**
     * Lấy danh sách danh mục cha (không bao gồm danh mục hiện tại và con của nó)
     * Dùng để hiển thị dropdown chọn danh mục cha khi thêm/sửa danh mục
     * 
     * @param int|null $excludeId Mã danh mục cần loại trừ (thường là danh mục đang sửa)
     * @return array Mảng các danh mục cha có thể chọn
     */
    public function layDanhMucCha($excludeId = null)
    {
        // Query SQL: Lấy danh mục cha
        $sql = "SELECT * FROM danh_muc WHERE 1=1";
        $params = [];
        
        if ($excludeId !== null) {
            // Loại trừ danh mục hiện tại và tất cả danh mục con của nó
            // Để tránh tạo vòng lặp (danh mục cha của chính nó)
            $sql .= " AND ma_danh_muc != :exclude_id                    -- Loại trừ danh mục hiện tại
                     AND (ma_danh_muc_cha IS NULL OR ma_danh_muc_cha != :exclude_id)";  // Loại trừ danh mục con
            $params[':exclude_id'] = $excludeId;
        } else {
            // Nếu không có excludeId, chỉ lấy danh mục cấp 1 (không có danh mục cha)
            $sql .= " AND ma_danh_muc_cha IS NULL";
        }
        
        // Sắp xếp theo tên danh mục
        $sql .= " ORDER BY ten_danh_muc";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
        $stmt->execute();
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh mục con trực tiếp của một danh mục cha
     * 
     * @param int $ma_danh_muc_cha Mã danh mục cha
     * @return array Mảng các danh mục con trực tiếp
     */
    public function layDanhMucCon($ma_danh_muc_cha)
    {
        // Query SQL: Lấy danh mục con trực tiếp (chỉ cấp 1, không đệ quy)
        $sql = "SELECT * FROM danh_muc WHERE ma_danh_muc_cha = :ma_danh_muc_cha ORDER BY ten_danh_muc";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_danh_muc_cha', $ma_danh_muc_cha, PDO::PARAM_INT);
        $stmt->execute();
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tạo slug (URL thân thiện) từ tên danh mục
     * Ví dụ: "Thuốc kháng sinh" -> "thuoc-khang-sinh"
     * 
     * @param string $ten_danh_muc Tên danh mục
     * @return string Slug đã được tạo
     */
    public function taoSlug($ten_danh_muc)
    {
        // Chuyển tên danh mục thành chữ thường và loại bỏ khoảng trắng đầu/cuối
        $slug = strtolower(trim($ten_danh_muc));
        
        // Thay thế tất cả ký tự không phải chữ cái, số, dấu gạch ngang bằng dấu gạch ngang
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        
        // Thay thế nhiều dấu gạch ngang liên tiếp bằng 1 dấu gạch ngang
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Loại bỏ dấu gạch ngang ở đầu và cuối
        $slug = trim($slug, '-');
        
        return $slug;
    }

    /**
     * Đếm tổng số danh mục trong database
     * Dùng cho phân trang
     * 
     * @param string $search Từ khóa tìm kiếm (tìm trong tên danh mục và mô tả)
     * @return int Tổng số danh mục
     */
    public function demTongSo($search = '')
    {
        // Query SQL: Đếm tổng số danh mục
        $sql = "SELECT COUNT(*) as total FROM danh_muc WHERE 1=1";
        
        // Mảng chứa các tham số
        $params = [];
        
        // Nếu có từ khóa tìm kiếm, thêm điều kiện
        if (!empty($search)) {
            $sql .= " AND (ten_danh_muc LIKE :search OR mo_ta LIKE :search)";
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
     * Lấy tất cả danh mục con (đệ quy) của một danh mục cha
     * Bao gồm cả danh mục con cấp 2, cấp 3, ... (tất cả các cấp)
     * Dùng để lấy tất cả mã danh mục trong cây danh mục
     * 
     * @param int $ma_danh_muc_cha Mã danh mục cha
     * @param array|null $danhMucList Danh sách tất cả danh mục (nếu null thì tự động lấy)
     * @return array Mảng các mã danh mục con (bao gồm cả cấp con, cháu, ...)
     */
    public function layTatCaDanhMucCon($ma_danh_muc_cha, $danhMucList = null)
    {
        // Nếu chưa có danh sách danh mục, lấy tất cả
        if ($danhMucList === null) {
            $danhMucList = $this->layTatCa();
        }
        
        // Mảng kết quả: chứa các mã danh mục con
        $result = [];
        
        // Hàm đệ quy để tìm tất cả danh mục con
        // Hàm này sẽ tìm danh mục con cấp 1, sau đó tìm tiếp danh mục con cấp 2, 3, ...
        $findChildren = function($parentId) use (&$findChildren, $danhMucList, &$result) {
            // Duyệt qua tất cả danh mục
            foreach ($danhMucList as $dm) {
                // Nếu danh mục này có danh mục cha = parentId
                if ($dm['ma_danh_muc_cha'] == $parentId) {
                    // Thêm mã danh mục con vào kết quả
                    $result[] = $dm['ma_danh_muc'];
                    
                    // Tìm tiếp các danh mục con của danh mục này (đệ quy)
                    $findChildren($dm['ma_danh_muc']);
                }
            }
        };
        
        // Bắt đầu tìm từ danh mục cha
        $findChildren($ma_danh_muc_cha);
        
        // Trả về mảng các mã danh mục con
        return $result;
    }
}
