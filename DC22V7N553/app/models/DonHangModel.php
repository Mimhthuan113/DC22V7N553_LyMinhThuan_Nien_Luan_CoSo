<?php
/**
 * app/models/DonHangModel.php - Model quản lý đơn hàng
 * 
 * Class này chứa tất cả các phương thức để thao tác với bảng don_hang:
 * - Tạo đơn hàng mới (từ giỏ hàng)
 * - Cập nhật trạng thái đơn hàng và thanh toán
 * - Lấy danh sách đơn hàng (cho admin và khách hàng)
 * - Thống kê doanh thu (theo ngày, tháng, năm)
 */

// Nạp class Model cơ sở
require_once __DIR__ . '/../core/Model.php';

class DonHangModel extends Model
{
    /**
     * Lấy tất cả đơn hàng (dành cho admin/nhân viên)
     * 
     * @param int|null $limit Số lượng đơn hàng cần lấy (null = lấy tất cả)
     * @param int $offset Vị trí bắt đầu (dùng cho phân trang)
     * @param string $search Từ khóa tìm kiếm (tìm trong mã đơn, tên khách hàng, email, địa chỉ giao)
     * @param string $trang_thai_filter Lọc theo trạng thái đơn hàng (CHO_XU_LY, DANG_XU_LY, HOAN_TAT, ...)
     * @return array Mảng các đơn hàng với thông tin khách hàng
     */
    public function layTatCa($limit = null, $offset = 0, $search = '', $trang_thai_filter = '')
    {
        // Query SQL: Lấy tất cả đơn hàng với thông tin khách hàng
        // JOIN nguoi_dung: Lấy thông tin khách hàng (họ tên, email, số điện thoại)
        $sql = "SELECT d.*, n.ho_ten, n.email, n.so_dien_thoai as sdt_khach_hang
                FROM don_hang d
                JOIN nguoi_dung n ON d.ma_khach_hang = n.ma_nguoi_dung
                WHERE 1=1";  // WHERE 1=1 để dễ dàng thêm điều kiện
        
        // Mảng chứa các tham số
        $params = [];
        
        // Nếu có từ khóa tìm kiếm, thêm điều kiện
        if (!empty($search)) {
            // Tìm trong mã đơn, tên khách hàng, email, địa chỉ giao
            $sql .= " AND (d.ma_don LIKE :search OR n.ho_ten LIKE :search OR n.email LIKE :search OR d.dia_chi_giao LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        // Nếu có lọc theo trạng thái, thêm điều kiện
        if (!empty($trang_thai_filter)) {
            $sql .= " AND d.trang_thai_don = :trang_thai";
            $params[':trang_thai'] = $trang_thai_filter;
        }
        
        // Sắp xếp theo ngày tạo mới nhất trước
        $sql .= " ORDER BY d.ngay_tao DESC";
        
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
                // Các tham số khác bind như string
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đếm tổng số đơn hàng
     * Dùng cho phân trang
     * 
     * @param string $search Từ khóa tìm kiếm
     * @param string $trang_thai_filter Lọc theo trạng thái
     * @return int Tổng số đơn hàng
     */
    public function demTongSo($search = '', $trang_thai_filter = '')
    {
        // Query SQL: Đếm tổng số đơn hàng
        $sql = "SELECT COUNT(*) as total
                FROM don_hang d
                JOIN nguoi_dung n ON d.ma_khach_hang = n.ma_nguoi_dung
                WHERE 1=1";
        
        // Mảng chứa các tham số
        $params = [];
        
        // Nếu có từ khóa tìm kiếm, thêm điều kiện
        if (!empty($search)) {
            $sql .= " AND (d.ma_don LIKE :search OR n.ho_ten LIKE :search OR n.email LIKE :search OR d.dia_chi_giao LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        // Nếu có lọc theo trạng thái, thêm điều kiện
        if (!empty($trang_thai_filter)) {
            $sql .= " AND d.trang_thai_don = :trang_thai";
            $params[':trang_thai'] = $trang_thai_filter;
        }
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        // Lấy kết quả
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Trả về tổng số (ép kiểu int)
        return (int)$result['total'];
    }

    /**
     * Lấy thông tin đơn hàng theo mã đơn hàng
     * 
     * @param int $ma_don_hang Mã đơn hàng cần lấy
     * @return array|null Thông tin đơn hàng (bao gồm thông tin khách hàng) hoặc null nếu không tìm thấy
     */
    public function layTheoMa($ma_don_hang)
    {
        // Query SQL: Lấy đơn hàng theo mã với thông tin khách hàng
        $sql = "SELECT d.*, n.ho_ten, n.email, n.so_dien_thoai as sdt_khach_hang
                FROM don_hang d
                JOIN nguoi_dung n ON d.ma_khach_hang = n.ma_nguoi_dung
                WHERE d.ma_don_hang = :ma_don_hang";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_don_hang', $ma_don_hang, PDO::PARAM_INT);
        $stmt->execute();
        
        // Trả về 1 dòng kết quả (hoặc null nếu không tìm thấy)
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết đơn hàng (danh sách sản phẩm trong đơn hàng)
     * 
     * @param int $ma_don_hang Mã đơn hàng
     * @return array Mảng các sản phẩm trong đơn hàng với thông tin:
     *   - ma_chi_tiet: Mã chi tiết đơn hàng
     *   - ma_thuoc: Mã thuốc
     *   - ten_thuoc: Tên thuốc
     *   - don_vi: Đơn vị
     *   - so_luong: Số lượng
     *   - don_gia: Đơn giá
     *   - thanh_tien: Thành tiền
     */
    public function layChiTietDonHang($ma_don_hang)
    {
        // Query SQL: Lấy chi tiết đơn hàng với thông tin thuốc
        // JOIN thuoc: Lấy tên thuốc và đơn vị
        $sql = "SELECT ctdh.*, t.ten_thuoc, t.don_vi
                FROM chi_tiet_don_hang ctdh
                JOIN thuoc t ON ctdh.ma_thuoc = t.ma_thuoc
                WHERE ctdh.ma_don_hang = :ma_don_hang";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_don_hang', $ma_don_hang, PDO::PARAM_INT);
        $stmt->execute();
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật trạng thái đơn hàng
     * Trạng thái: CHO_XU_LY, DANG_XU_LY, DANG_GIAO, HOAN_TAT, HUY
     * 
     * @param int $ma_don_hang Mã đơn hàng cần cập nhật
     * @param string $trang_thai_don Trạng thái mới
     * @return bool true nếu cập nhật thành công, false nếu không
     */
    public function capNhatTrangThai($ma_don_hang, $trang_thai_don)
    {
        // Query SQL: Cập nhật trạng thái đơn hàng
        $sql = "UPDATE don_hang 
                SET trang_thai_don = :trang_thai_don 
                WHERE ma_don_hang = :ma_don_hang";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':trang_thai_don' => $trang_thai_don,    // Trạng thái mới
            ':ma_don_hang' => $ma_don_hang           // Mã đơn hàng
        ]);
        
        // Trả về true nếu có ít nhất 1 dòng được cập nhật
        return $stmt->rowCount() > 0;
    }

    /**
     * Cập nhật trạng thái thanh toán
     * Trạng thái: CHUA_THANH_TOAN, DA_THANH_TOAN
     * 
     * @param int $ma_don_hang Mã đơn hàng cần cập nhật
     * @param string $trang_thai_thanh_toan Trạng thái thanh toán mới
     * @return bool true nếu cập nhật thành công, false nếu không
     */
    public function capNhatTrangThaiThanhToan($ma_don_hang, $trang_thai_thanh_toan)
    {
        // Query SQL: Cập nhật trạng thái thanh toán
        $sql = "UPDATE don_hang 
                SET trang_thai_thanh_toan = :trang_thai_thanh_toan 
                WHERE ma_don_hang = :ma_don_hang";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':trang_thai_thanh_toan' => $trang_thai_thanh_toan,  // Trạng thái thanh toán mới
            ':ma_don_hang' => $ma_don_hang                        // Mã đơn hàng
        ]);
        
        // Trả về true nếu có ít nhất 1 dòng được cập nhật
        return $stmt->rowCount() > 0;
    }

    /**
     * Tạo mã đơn hàng duy nhất (phương thức private)
     * Format: DH + YYYYMMDD + 4 số ngẫu nhiên
     * Ví dụ: DH202412260123
     * 
     * @return string Mã đơn hàng duy nhất
     */
    private function taoMaDon()
    {
        // Lặp cho đến khi tạo được mã đơn hàng duy nhất
        do {
            // Tạo mã đơn hàng: DH + ngày hiện tại (YYYYMMDD) + 4 số ngẫu nhiên
            // str_pad: Đảm bảo 4 số (nếu < 1000 thì thêm số 0 ở đầu)
            $ma_don = 'DH' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            
            // Kiểm tra xem mã đơn hàng đã tồn tại chưa
            $sql = "SELECT COUNT(*) as count FROM don_hang WHERE ma_don = :ma_don";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':ma_don', $ma_don);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Nếu count > 0, mã đã tồn tại, tạo lại
        } while ($result['count'] > 0);
        
        // Trả về mã đơn hàng duy nhất
        return $ma_don;
    }

    /**
     * Tạo đơn hàng mới từ giỏ hàng
     * Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu:
     *   - Tạo đơn hàng
     *   - Thêm chi tiết đơn hàng
     *   - Trừ số lượng tồn kho
     * Nếu có lỗi ở bất kỳ bước nào, rollback tất cả
     * 
     * @param array $data Mảng chứa thông tin đơn hàng:
     *   - ma_khach_hang: Mã khách hàng (bắt buộc)
     *   - tong_tien: Tổng tiền đơn hàng (bắt buộc)
     *   - dia_chi_giao: Địa chỉ giao hàng (bắt buộc)
     *   - so_dien_thoai_giao: Số điện thoại giao hàng (bắt buộc)
     *   - hinh_thuc_thanh_toan: Hình thức thanh toán (COD, CHUYEN_KHOAN, mặc định: COD)
     *   - trang_thai_don: Trạng thái đơn hàng (mặc định: CHO_XU_LY)
     *   - trang_thai_thanh_toan: Trạng thái thanh toán (mặc định: CHUA_THANH_TOAN)
     *   - ghi_chu: Ghi chú (tùy chọn)
     *   - chi_tiet: Mảng các sản phẩm trong đơn hàng, mỗi phần tử chứa:
     *     * ma_thuoc: Mã thuốc
     *     * so_luong: Số lượng
     *     * don_gia: Đơn giá
     *     * thanh_tien: Thành tiền
     * @return int Mã đơn hàng vừa được tạo (lastInsertId)
     * @throws Exception Nếu có lỗi trong quá trình tạo đơn hàng
     */
    public function taoDonHang($data)
    {
        // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
        // Nếu có lỗi ở bất kỳ bước nào, rollback tất cả
        $this->db->beginTransaction();
        try {
            // Tạo mã đơn hàng duy nhất
            $ma_don = $this->taoMaDon();
            
            // Query SQL: Thêm đơn hàng mới vào database
            // ngay_dat: Tự động lấy thời gian hiện tại (NOW())
            $sql = "INSERT INTO don_hang (
                        ma_don, ma_khach_hang, ngay_dat, 
                        trang_thai_don, hinh_thuc_thanh_toan, trang_thai_thanh_toan,
                        tong_tien, dia_chi_giao, so_dien_thoai_giao, ghi_chu
                    ) VALUES (
                        :ma_don, :ma_khach_hang, NOW(),
                        :trang_thai_don, :hinh_thuc_thanh_toan, :trang_thai_thanh_toan,
                        :tong_tien, :dia_chi_giao, :so_dien_thoai_giao, :ghi_chu
                    )";
            
            // Chuẩn bị và thực thi query
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':ma_don' => $ma_don,                                    // Mã đơn hàng duy nhất
                ':ma_khach_hang' => $data['ma_khach_hang'],              // Mã khách hàng
                ':trang_thai_don' => $data['trang_thai_don'] ?? 'CHO_XU_LY',  // Trạng thái đơn hàng (mặc định: CHỜ XỬ LÝ)
                ':hinh_thuc_thanh_toan' => $data['hinh_thuc_thanh_toan'] ?? 'COD',  // Hình thức thanh toán (mặc định: COD - Cash On Delivery)
                ':trang_thai_thanh_toan' => $data['trang_thai_thanh_toan'] ?? 'CHUA_THANH_TOAN',  // Trạng thái thanh toán (mặc định: CHƯA THANH TOÁN)
                ':tong_tien' => $data['tong_tien'],                      // Tổng tiền đơn hàng
                ':dia_chi_giao' => $data['dia_chi_giao'],                 // Địa chỉ giao hàng
                ':so_dien_thoai_giao' => $data['so_dien_thoai_giao'],    // Số điện thoại giao hàng
                ':ghi_chu' => $data['ghi_chu'] ?? null                   // Ghi chú (tùy chọn)
            ]);
            
            // Lấy mã đơn hàng vừa được tạo
            $ma_don_hang = $this->db->lastInsertId();
            
            // Thêm chi tiết đơn hàng (từng sản phẩm trong giỏ hàng)
            foreach ($data['chi_tiet'] as $ct) {
                // Query SQL: Thêm chi tiết đơn hàng
                $sql = "INSERT INTO chi_tiet_don_hang (
                            ma_don_hang, ma_thuoc, so_luong, don_gia, thanh_tien
                        ) VALUES (
                            :ma_don_hang, :ma_thuoc, :so_luong, :don_gia, :thanh_tien
                        )";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':ma_don_hang' => $ma_don_hang,      // Mã đơn hàng
                    ':ma_thuoc' => $ct['ma_thuoc'],      // Mã thuốc
                    ':so_luong' => $ct['so_luong'],      // Số lượng
                    ':don_gia' => $ct['don_gia'],        // Đơn giá
                    ':thanh_tien' => $ct['thanh_tien']   // Thành tiền
                ]);
                
                // Trừ số lượng tồn kho sau khi đặt hàng
                // Đảm bảo số lượng tồn kho luôn chính xác
                $sql = "UPDATE thuoc SET so_luong_ton = so_luong_ton - :so_luong 
                        WHERE ma_thuoc = :ma_thuoc";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':so_luong' => $ct['so_luong'],       // Số lượng cần trừ
                    ':ma_thuoc' => $ct['ma_thuoc']        // Mã thuốc
                ]);
            }
            
            // Commit transaction: Xác nhận tất cả thay đổi
            $this->db->commit();
            
            // Trả về mã đơn hàng vừa được tạo
            return $ma_don_hang;
        } catch (Exception $e) {
            // Rollback transaction: Hủy tất cả thay đổi nếu có lỗi
            $this->db->rollBack();
            // Ném lại exception để controller xử lý
            throw $e;
        }
    }

    /**
     * Lấy đơn hàng theo mã khách hàng
     * Dùng để khách hàng xem lịch sử đơn hàng của mình
     * 
     * @param int $ma_khach_hang Mã khách hàng
     * @param int|null $limit Số lượng đơn hàng cần lấy (null = lấy tất cả)
     * @param int $offset Vị trí bắt đầu (dùng cho phân trang)
     * @return array Mảng các đơn hàng của khách hàng
     */
    public function layTheoKhachHang($ma_khach_hang, $limit = null, $offset = 0)
    {
        // Query SQL: Lấy đơn hàng theo mã khách hàng
        $sql = "SELECT * FROM don_hang 
                WHERE ma_khach_hang = :ma_khach_hang 
                ORDER BY ngay_tao DESC";  // Sắp xếp mới nhất trước
        
        // Nếu có giới hạn, thêm LIMIT và OFFSET
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_khach_hang', $ma_khach_hang, PDO::PARAM_INT);
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đếm tổng số đơn hàng theo khách hàng
     * Dùng cho phân trang
     * 
     * @param int $ma_khach_hang Mã khách hàng
     * @return int Tổng số đơn hàng của khách hàng
     */
    public function demTongSoTheoKhachHang($ma_khach_hang)
    {
        // Query SQL: Đếm tổng số đơn hàng của khách hàng
        $sql = "SELECT COUNT(*) as total FROM don_hang WHERE ma_khach_hang = :ma_khach_hang";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_khach_hang', $ma_khach_hang, PDO::PARAM_INT);
        $stmt->execute();
        
        // Lấy kết quả
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Trả về tổng số (ép kiểu int)
        return (int)$result['total'];
    }

    /**
     * Thống kê doanh thu theo ngày (chỉ đơn hàng đã hoàn tất)
     * Dùng để vẽ biểu đồ doanh thu theo ngày
     * 
     * @param string|null $startDate Ngày bắt đầu (format: Y-m-d, null = không giới hạn)
     * @param string|null $endDate Ngày kết thúc (format: Y-m-d, null = không giới hạn)
     * @return array Mảng các ngày với thông tin:
     *   - ngay: Ngày (format: Y-m-d)
     *   - doanh_thu: Tổng doanh thu trong ngày
     *   - so_don: Số đơn hàng trong ngày
     */
    public function thongKeDoanhThuTheoNgay($startDate = null, $endDate = null)
    {
        // Query SQL: Thống kê doanh thu theo ngày
        // DATE(ngay_tao): Lấy phần ngày (bỏ phần giờ)
        // SUM(tong_tien): Tổng doanh thu
        // COUNT(*): Số đơn hàng
        // GROUP BY DATE(ngay_tao): Nhóm theo ngày
        $sql = "SELECT DATE(ngay_tao) as ngay, SUM(tong_tien) as doanh_thu, COUNT(*) as so_don
                FROM don_hang
                WHERE trang_thai_don = 'HOAN_TAT'";  // Chỉ tính đơn hàng đã hoàn tất
        
        // Mảng chứa các tham số
        $params = [];
        
        // Nếu có ngày bắt đầu, thêm điều kiện
        if ($startDate) {
            $sql .= " AND DATE(ngay_tao) >= :start_date";
            $params[':start_date'] = $startDate;
        }
        
        // Nếu có ngày kết thúc, thêm điều kiện
        if ($endDate) {
            $sql .= " AND DATE(ngay_tao) <= :end_date";
            $params[':end_date'] = $endDate;
        }
        
        // Nhóm theo ngày và sắp xếp theo ngày tăng dần
        $sql .= " GROUP BY DATE(ngay_tao) ORDER BY ngay ASC";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thống kê doanh thu theo tháng (chỉ đơn hàng đã hoàn tất)
     * Dùng để vẽ biểu đồ doanh thu theo tháng
     * 
     * @param int|null $year Năm cần thống kê (null = tất cả các năm)
     * @return array Mảng các tháng với thông tin:
     *   - nam: Năm
     *   - thang: Tháng (1-12)
     *   - doanh_thu: Tổng doanh thu trong tháng
     *   - so_don: Số đơn hàng trong tháng
     */
    public function thongKeDoanhThuTheoThang($year = null)
    {
        // Query SQL: Thống kê doanh thu theo tháng
        // YEAR(ngay_tao): Lấy năm
        // MONTH(ngay_tao): Lấy tháng
        // SUM(tong_tien): Tổng doanh thu
        // COUNT(*): Số đơn hàng
        // GROUP BY YEAR, MONTH: Nhóm theo năm và tháng
        $sql = "SELECT YEAR(ngay_tao) as nam, MONTH(ngay_tao) as thang, 
                SUM(tong_tien) as doanh_thu, COUNT(*) as so_don
                FROM don_hang
                WHERE trang_thai_don = 'HOAN_TAT'";  // Chỉ tính đơn hàng đã hoàn tất
        
        // Mảng chứa các tham số
        $params = [];
        
        // Nếu có năm, thêm điều kiện
        if ($year) {
            $sql .= " AND YEAR(ngay_tao) = :year";
            $params[':year'] = $year;
        }
        
        // Nhóm theo năm và tháng, sắp xếp theo năm và tháng tăng dần
        $sql .= " GROUP BY YEAR(ngay_tao), MONTH(ngay_tao) ORDER BY nam ASC, thang ASC";
        
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
     * Thống kê doanh thu theo năm (chỉ đơn hàng đã hoàn tất)
     * Dùng để vẽ biểu đồ doanh thu theo năm
     * 
     * @return array Mảng các năm với thông tin:
     *   - nam: Năm
     *   - doanh_thu: Tổng doanh thu trong năm
     *   - so_don: Số đơn hàng trong năm
     */
    public function thongKeDoanhThuTheoNam()
    {
        // Query SQL: Thống kê doanh thu theo năm
        // YEAR(ngay_tao): Lấy năm
        // SUM(tong_tien): Tổng doanh thu
        // COUNT(*): Số đơn hàng
        // GROUP BY YEAR: Nhóm theo năm
        $sql = "SELECT YEAR(ngay_tao) as nam, SUM(tong_tien) as doanh_thu, COUNT(*) as so_don
                FROM don_hang
                WHERE trang_thai_don = 'HOAN_TAT'                    -- Chỉ tính đơn hàng đã hoàn tất
                GROUP BY YEAR(ngay_tao) ORDER BY nam ASC";          // Nhóm theo năm, sắp xếp tăng dần
        
        // Thực thi query (không cần prepare vì không có tham số từ người dùng)
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tính tổng doanh thu và tổng số đơn hàng (chỉ đơn hàng đã hoàn tất)
     * Dùng để hiển thị tổng kết trên dashboard
     * 
     * @param string|null $startDate Ngày bắt đầu (format: Y-m-d, null = không giới hạn)
     * @param string|null $endDate Ngày kết thúc (format: Y-m-d, null = không giới hạn)
     * @return array Mảng chứa:
     *   - tong_doanh_thu: Tổng doanh thu
     *   - tong_so_don: Tổng số đơn hàng
     */
    public function tongDoanhThu($startDate = null, $endDate = null)
    {
        // Query SQL: Tính tổng doanh thu và tổng số đơn hàng
        // SUM(tong_tien): Tổng doanh thu
        // COUNT(*): Tổng số đơn hàng
        $sql = "SELECT SUM(tong_tien) as tong_doanh_thu, COUNT(*) as tong_so_don
                FROM don_hang
                WHERE trang_thai_don = 'HOAN_TAT'";  // Chỉ tính đơn hàng đã hoàn tất
        
        // Mảng chứa các tham số
        $params = [];
        
        // Nếu có ngày bắt đầu, thêm điều kiện
        if ($startDate) {
            $sql .= " AND DATE(ngay_tao) >= :start_date";
            $params[':start_date'] = $startDate;
        }
        
        // Nếu có ngày kết thúc, thêm điều kiện
        if ($endDate) {
            $sql .= " AND DATE(ngay_tao) <= :end_date";
            $params[':end_date'] = $endDate;
        }
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        // Trả về 1 dòng kết quả
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
