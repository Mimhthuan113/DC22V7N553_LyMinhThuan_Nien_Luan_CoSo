<?php
/**
 * app/models/SaleModel.php - Model quản lý chương trình sale/khuyến mãi
 * 
 * Class này chứa tất cả các phương thức để thao tác với bảng sale:
 * - Lấy danh sách sale đang hoạt động
 * - Lấy sale theo danh mục
 * - Thêm, sửa, xóa sale
 * - Tính giá sale
 */

// Nạp class Model cơ sở
require_once __DIR__ . '/../core/Model.php';

class SaleModel extends Model
{
    /**
     * Lấy tất cả sale đang hoạt động (dành cho khách hàng)
     * Chỉ lấy sale có: trang_thai = 1, đã bắt đầu, chưa kết thúc
     * 
     * @param int|null $limit Số lượng sale cần lấy (null = lấy tất cả)
     * @param int $offset Vị trí bắt đầu (dùng cho phân trang)
     * @return array Mảng các sale với thông tin đầy đủ:
     *   - Thông tin sale (ma_sale, phan_tram_giam, gia_sale, thoi_gian_bat_dau, thoi_gian_ket_thuc)
     *   - Thông tin thuốc (ten_thuoc, gia_goc, hinh_anh, don_vi, so_luong_ton)
     *   - Thông tin danh mục (ten_danh_muc, ma_danh_muc)
     */
    public function layDangHoatDong($limit = null, $offset = 0)
    {
        // Query SQL: Lấy sale đang hoạt động với thông tin đầy đủ
        // JOIN thuoc: Lấy thông tin thuốc (tên, giá gốc, ảnh, đơn vị, số lượng tồn)
        // LEFT JOIN danh_muc: Lấy tên danh mục
        // Điều kiện:
        //   - trang_thai = 1: Sale đang hoạt động
        //   - NOW() >= thoi_gian_bat_dau: Đã bắt đầu
        //   - NOW() <= thoi_gian_ket_thuc: Chưa kết thúc
        // Sắp xếp: Sắp hết hạn trước (thoi_gian_ket_thuc ASC)
        $sql = "SELECT s.*, t.ten_thuoc, t.gia as gia_goc, t.hinh_anh, t.don_vi, t.so_luong_ton, d.ten_danh_muc, t.ma_danh_muc
                FROM sale s
                JOIN thuoc t ON s.ma_thuoc = t.ma_thuoc
                LEFT JOIN danh_muc d ON t.ma_danh_muc = d.ma_danh_muc
                WHERE s.trang_thai = 1                          -- Sale đang hoạt động
                AND NOW() >= s.thoi_gian_bat_dau                 -- Đã bắt đầu
                AND NOW() <= s.thoi_gian_ket_thuc                -- Chưa kết thúc
                ORDER BY s.thoi_gian_ket_thuc ASC";              // Sắp hết hạn trước
        
        // Nếu có giới hạn, thêm LIMIT và OFFSET (phân trang)
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
     * Lấy sale đang hoạt động theo danh mục
     * Dùng để hiển thị sale theo từng danh mục trên trang chủ
     * 
     * @param int $ma_danh_muc Mã danh mục cần lấy sale
     * @param int|null $limit Số lượng sale cần lấy (null = lấy tất cả)
     * @param int $offset Vị trí bắt đầu (dùng cho phân trang)
     * @return array Mảng các sale trong danh mục
     */
    public function layDangHoatDongTheoDanhMuc($ma_danh_muc, $limit = null, $offset = 0)
    {
        // Query SQL: Lấy sale đang hoạt động trong danh mục cụ thể
        // Tương tự layDangHoatDong() nhưng thêm điều kiện: t.ma_danh_muc = :ma_danh_muc
        $sql = "SELECT s.*, t.ten_thuoc, t.gia as gia_goc, t.hinh_anh, t.don_vi, t.so_luong_ton, d.ten_danh_muc, t.ma_danh_muc
                FROM sale s
                JOIN thuoc t ON s.ma_thuoc = t.ma_thuoc
                LEFT JOIN danh_muc d ON t.ma_danh_muc = d.ma_danh_muc
                WHERE s.trang_thai = 1                          -- Sale đang hoạt động
                AND NOW() >= s.thoi_gian_bat_dau                 -- Đã bắt đầu
                AND NOW() <= s.thoi_gian_ket_thuc                -- Chưa kết thúc
                AND t.ma_danh_muc = :ma_danh_muc                 -- Thuộc danh mục này
                ORDER BY s.thoi_gian_ket_thuc ASC";              // Sắp hết hạn trước
        
        // Nếu có giới hạn, thêm LIMIT và OFFSET
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_danh_muc', $ma_danh_muc, PDO::PARAM_INT);
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy tất cả sale (dành cho admin)
     * Lấy cả sale đang hoạt động và đã kết thúc
     * 
     * @param int|null $limit Số lượng sale cần lấy (null = lấy tất cả)
     * @param int $offset Vị trí bắt đầu (dùng cho phân trang)
     * @param string $search Từ khóa tìm kiếm (tìm trong tên thuốc)
     * @return array Mảng tất cả các sale
     */
    public function layTatCa($limit = null, $offset = 0, $search = '')
    {
        // Query SQL: Lấy tất cả sale (không lọc theo thời gian)
        $sql = "SELECT s.*, t.ten_thuoc, t.gia as gia_goc, t.hinh_anh, d.ten_danh_muc
                FROM sale s
                JOIN thuoc t ON s.ma_thuoc = t.ma_thuoc
                LEFT JOIN danh_muc d ON t.ma_danh_muc = d.ma_danh_muc
                WHERE 1=1";  // WHERE 1=1 để dễ dàng thêm điều kiện
        
        // Mảng chứa các tham số
        $params = [];
        
        // Nếu có từ khóa tìm kiếm, thêm điều kiện
        if (!empty($search)) {
            $sql .= " AND (t.ten_thuoc LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        // Sắp xếp theo ngày tạo mới nhất trước
        $sql .= " ORDER BY s.ngay_tao DESC";
        
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
     * Lấy thông tin sale theo mã sale
     * 
     * @param int $ma_sale Mã sale cần lấy
     * @return array|null Thông tin sale (bao gồm thông tin thuốc) hoặc null nếu không tìm thấy
     */
    public function layTheoMa($ma_sale)
    {
        // Query SQL: Lấy sale theo mã với thông tin thuốc
        $sql = "SELECT s.*, t.ten_thuoc, t.gia as gia_goc, t.hinh_anh
                FROM sale s
                JOIN thuoc t ON s.ma_thuoc = t.ma_thuoc
                WHERE s.ma_sale = :ma_sale";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_sale', $ma_sale, PDO::PARAM_INT);
        $stmt->execute();
        
        // Trả về 1 dòng kết quả (hoặc null nếu không tìm thấy)
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy sale đang hoạt động của một thuốc cụ thể
     * Dùng để kiểm tra xem thuốc có đang trong chương trình sale không
     * 
     * @param int $ma_thuoc Mã thuốc cần kiểm tra
     * @return array|null Thông tin sale đang hoạt động hoặc null nếu không có
     */
    public function layTheoMaThuoc($ma_thuoc)
    {
        // Query SQL: Lấy sale đang hoạt động của thuốc
        // Chỉ lấy 1 sale (LIMIT 1) - nếu có nhiều sale thì lấy sale sắp hết hạn nhất
        $sql = "SELECT s.*
                FROM sale s
                WHERE s.ma_thuoc = :ma_thuoc                    -- Thuộc thuốc này
                AND s.trang_thai = 1                             -- Sale đang hoạt động
                AND NOW() >= s.thoi_gian_bat_dau                 -- Đã bắt đầu
                AND NOW() <= s.thoi_gian_ket_thuc                -- Chưa kết thúc
                ORDER BY s.thoi_gian_ket_thuc ASC                -- Sắp hết hạn trước
                LIMIT 1";                                        // Chỉ lấy 1 sale
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_thuoc', $ma_thuoc, PDO::PARAM_INT);
        $stmt->execute();
        
        // Trả về 1 dòng kết quả (hoặc null nếu không có sale)
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm sale mới
     * 
     * @param array $data Mảng chứa thông tin sale:
     *   - ma_thuoc: Mã thuốc (bắt buộc)
     *   - phan_tram_giam: Phần trăm giảm giá (ví dụ: 10 = 10%)
     *   - gia_sale: Giá sau khi giảm (bắt buộc)
     *   - thoi_gian_bat_dau: Thời gian bắt đầu (format: Y-m-d H:i:s)
     *   - thoi_gian_ket_thuc: Thời gian kết thúc (format: Y-m-d H:i:s)
     *   - trang_thai: Trạng thái (1 = hoạt động, 0 = tạm ngưng, mặc định: 1)
     * @return int Mã sale vừa được tạo (lastInsertId)
     */
    public function themSale($data)
    {
        // Query SQL: Thêm sale mới vào database
        $sql = "INSERT INTO sale (ma_thuoc, phan_tram_giam, gia_sale, thoi_gian_bat_dau, thoi_gian_ket_thuc, trang_thai)
                VALUES (:ma_thuoc, :phan_tram_giam, :gia_sale, :thoi_gian_bat_dau, :thoi_gian_ket_thuc, :trang_thai)";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ma_thuoc' => $data['ma_thuoc'],                    // Mã thuốc
            ':phan_tram_giam' => $data['phan_tram_giam'],        // Phần trăm giảm giá
            ':gia_sale' => $data['gia_sale'],                    // Giá sau khi giảm
            ':thoi_gian_bat_dau' => $data['thoi_gian_bat_dau'],  // Thời gian bắt đầu
            ':thoi_gian_ket_thuc' => $data['thoi_gian_ket_thuc'], // Thời gian kết thúc
            ':trang_thai' => $data['trang_thai'] ?? 1,           // Trạng thái (mặc định: 1 = hoạt động)
        ]);
        
        // Trả về mã sale vừa được tạo
        return $this->db->lastInsertId();
    }

    /**
     * Cập nhật thông tin sale
     * 
     * @param int $ma_sale Mã sale cần cập nhật
     * @param array $data Mảng chứa thông tin cần cập nhật (tương tự như themSale)
     * @return bool true nếu cập nhật thành công, false nếu không
     */
    public function capNhatSale($ma_sale, $data)
    {
        // Query SQL: Cập nhật thông tin sale
        $sql = "UPDATE sale 
                SET ma_thuoc = :ma_thuoc,
                    phan_tram_giam = :phan_tram_giam,
                    gia_sale = :gia_sale,
                    thoi_gian_bat_dau = :thoi_gian_bat_dau,
                    thoi_gian_ket_thuc = :thoi_gian_ket_thuc,
                    trang_thai = :trang_thai
                WHERE ma_sale = :ma_sale";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ma_thuoc' => $data['ma_thuoc'],                    // Mã thuốc
            ':phan_tram_giam' => $data['phan_tram_giam'],        // Phần trăm giảm giá
            ':gia_sale' => $data['gia_sale'],                    // Giá sau khi giảm
            ':thoi_gian_bat_dau' => $data['thoi_gian_bat_dau'],  // Thời gian bắt đầu
            ':thoi_gian_ket_thuc' => $data['thoi_gian_ket_thuc'], // Thời gian kết thúc
            ':trang_thai' => $data['trang_thai'] ?? 1,           // Trạng thái
            ':ma_sale' => $ma_sale                                // Mã sale cần cập nhật
        ]);
        
        // Trả về true nếu có ít nhất 1 dòng được cập nhật
        return $stmt->rowCount() > 0;
    }

    /**
     * Xóa sale khỏi database
     * 
     * @param int $ma_sale Mã sale cần xóa
     * @return bool true nếu xóa thành công, false nếu không
     */
    public function xoaSale($ma_sale)
    {
        // Query SQL: Xóa sale theo mã
        $sql = "DELETE FROM sale WHERE ma_sale = :ma_sale";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_sale', $ma_sale, PDO::PARAM_INT);
        $stmt->execute();
        
        // Trả về true nếu có ít nhất 1 dòng bị xóa
        return $stmt->rowCount() > 0;
    }

    /**
     * Đếm tổng số sale trong database
     * Dùng cho phân trang
     * 
     * @param string $search Từ khóa tìm kiếm (tìm trong tên thuốc)
     * @return int Tổng số sale
     */
    public function demTongSo($search = '')
    {
        // Query SQL: Đếm tổng số sale
        $sql = "SELECT COUNT(*) as total 
                FROM sale s
                JOIN thuoc t ON s.ma_thuoc = t.ma_thuoc
                WHERE 1=1";
        
        // Mảng chứa các tham số
        $params = [];
        
        // Nếu có từ khóa tìm kiếm, thêm điều kiện
        if (!empty($search)) {
            $sql .= " AND (t.ten_thuoc LIKE :search)";
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
     * Tính giá sau khi giảm (giá sale)
     * Công thức: giá_sale = giá_gốc × (1 - phần_trăm_giảm / 100)
     * 
     * @param float $gia_goc Giá gốc của sản phẩm
     * @param float $phan_tram_giam Phần trăm giảm giá (ví dụ: 10 = 10%)
     * @return float Giá sau khi giảm
     */
    public function tinhGiaSale($gia_goc, $phan_tram_giam)
    {
        // Tính giá sale: giá_gốc × (1 - phần_trăm_giảm / 100)
        // Ví dụ: giá_gốc = 100000, phần_trăm_giảm = 10
        //   => giá_sale = 100000 × (1 - 10/100) = 100000 × 0.9 = 90000
        return $gia_goc * (1 - $phan_tram_giam / 100);
    }
}
