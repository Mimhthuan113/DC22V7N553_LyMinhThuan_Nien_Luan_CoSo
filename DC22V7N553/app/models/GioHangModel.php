<?php
/**
 * app/models/GioHangModel.php - Model quản lý giỏ hàng
 * 
 * Class này chứa tất cả các phương thức để thao tác với giỏ hàng:
 * - Tạo/lấy giỏ hàng
 * - Thêm, cập nhật, xóa sản phẩm trong giỏ hàng
 * - Lấy danh sách sản phẩm trong giỏ hàng (có áp dụng giá sale)
 * - Đếm số lượng sản phẩm
 */

// Nạp class Model cơ sở
require_once __DIR__ . '/../core/Model.php';

class GioHangModel extends Model
{
    /**
     * Lấy hoặc tạo giỏ hàng cho người dùng
     * Mỗi người dùng chỉ có 1 giỏ hàng duy nhất
     * 
     * @param int $ma_nguoi_dung Mã người dùng
     * @return array Thông tin giỏ hàng (ma_gio_hang, ma_nguoi_dung, ...)
     * @throws Exception Nếu không thể tạo hoặc lấy giỏ hàng
     */
    public function layHoacTaoGioHang($ma_nguoi_dung)
    {
        try {
            // Kiểm tra xem người dùng đã có giỏ hàng chưa
            $sql = "SELECT * FROM gio_hang WHERE ma_nguoi_dung = :ma_nguoi_dung LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':ma_nguoi_dung', $ma_nguoi_dung, PDO::PARAM_INT);
            $stmt->execute();
            $gioHang = $stmt->fetch(PDO::FETCH_ASSOC);

            // Nếu chưa có giỏ hàng, tạo mới
            if (!$gioHang) {
                // Tạo giỏ hàng mới cho người dùng
                $sql = "INSERT INTO gio_hang (ma_nguoi_dung) VALUES (:ma_nguoi_dung)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':ma_nguoi_dung', $ma_nguoi_dung, PDO::PARAM_INT);
                
                // Nếu không thể thực thi, ném exception
                if (!$stmt->execute()) {
                    throw new Exception('Không thể tạo giỏ hàng mới');
                }
                
                // Lấy mã giỏ hàng vừa tạo (auto increment)
                $ma_gio_hang = $this->db->lastInsertId();
                
                // Kiểm tra xem có lấy được mã giỏ hàng không
                if (!$ma_gio_hang) {
                    throw new Exception('Không thể lấy ID giỏ hàng vừa tạo');
                }
                
                // Lấy lại thông tin giỏ hàng vừa tạo để trả về
                $sql = "SELECT * FROM gio_hang WHERE ma_gio_hang = :ma_gio_hang";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':ma_gio_hang', $ma_gio_hang, PDO::PARAM_INT);
                $stmt->execute();
                $gioHang = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Kiểm tra xem có lấy được thông tin không
                if (!$gioHang) {
                    throw new Exception('Không thể lấy thông tin giỏ hàng vừa tạo');
                }
            }

            // Trả về thông tin giỏ hàng (đã có sẵn hoặc vừa tạo)
            return $gioHang;
        } catch (PDOException $e) {
            // Ghi log lỗi database
            error_log('GioHangModel::layHoacTaoGioHang PDO Error: ' . $e->getMessage());
            // Ném exception với thông báo lỗi
            throw new Exception('Lỗi database: ' . $e->getMessage());
        }
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     * Nếu sản phẩm đã có trong giỏ hàng, cập nhật số lượng (cộng dồn)
     * Nếu chưa có, thêm mới
     * 
     * @param int $ma_nguoi_dung Mã người dùng
     * @param int $ma_thuoc Mã thuốc/sản phẩm
     * @param int $so_luong Số lượng cần thêm
     * @param float $don_gia Đơn giá (có thể là giá sale nếu đang có chương trình sale)
     * @return int Mã chi tiết giỏ hàng (ma_chi_tiet)
     * @throws Exception Nếu không thể thêm sản phẩm
     */
    public function themVaoGioHang($ma_nguoi_dung, $ma_thuoc, $so_luong, $don_gia)
    {
        try {
            // Lấy hoặc tạo giỏ hàng cho người dùng
            $gioHang = $this->layHoacTaoGioHang($ma_nguoi_dung);
            
            // Kiểm tra xem có lấy được giỏ hàng không
            if (!$gioHang || !isset($gioHang['ma_gio_hang'])) {
                throw new Exception('Không thể tạo hoặc lấy giỏ hàng');
            }
            $ma_gio_hang = $gioHang['ma_gio_hang'];

            // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
            $sql = "SELECT * FROM chi_tiet_gio_hang 
                    WHERE ma_gio_hang = :ma_gio_hang AND ma_thuoc = :ma_thuoc";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':ma_gio_hang', $ma_gio_hang, PDO::PARAM_INT);
            $stmt->bindValue(':ma_thuoc', $ma_thuoc, PDO::PARAM_INT);
            $stmt->execute();
            $chiTiet = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($chiTiet) {
                // Nếu sản phẩm đã có trong giỏ hàng, cập nhật số lượng (cộng dồn)
                $so_luong_moi = $chiTiet['so_luong'] + $so_luong;  // Cộng số lượng cũ với số lượng mới
                
                // Cập nhật số lượng và giá (giá có thể thay đổi nếu có sale mới)
                $sql = "UPDATE chi_tiet_gio_hang 
                        SET so_luong = :so_luong, don_gia = :don_gia
                        WHERE ma_chi_tiet = :ma_chi_tiet";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':so_luong', $so_luong_moi, PDO::PARAM_INT);
                $stmt->bindValue(':don_gia', $don_gia);  // Cập nhật giá (có thể là giá sale)
                $stmt->bindValue(':ma_chi_tiet', $chiTiet['ma_chi_tiet'], PDO::PARAM_INT);
                
                // Nếu không thể cập nhật, ném exception
                if (!$stmt->execute()) {
                    throw new Exception('Không thể cập nhật giỏ hàng');
                }
                
                // Trả về mã chi tiết giỏ hàng (đã có sẵn)
                return $chiTiet['ma_chi_tiet'];
            } else {
                // Nếu sản phẩm chưa có trong giỏ hàng, thêm mới
                $sql = "INSERT INTO chi_tiet_gio_hang (ma_gio_hang, ma_thuoc, so_luong, don_gia)
                        VALUES (:ma_gio_hang, :ma_thuoc, :so_luong, :don_gia)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':ma_gio_hang', $ma_gio_hang, PDO::PARAM_INT);
                $stmt->bindValue(':ma_thuoc', $ma_thuoc, PDO::PARAM_INT);
                $stmt->bindValue(':so_luong', $so_luong, PDO::PARAM_INT);
                $stmt->bindValue(':don_gia', $don_gia);  // Lưu giá (có thể là giá sale)
                
                // Nếu không thể thêm, ném exception với thông tin lỗi chi tiết
                if (!$stmt->execute()) {
                    $errorInfo = $stmt->errorInfo();
                    throw new Exception('Không thể thêm sản phẩm vào giỏ hàng: ' . ($errorInfo[2] ?? 'Unknown error'));
                }
                
                // Trả về mã chi tiết giỏ hàng vừa tạo
                return $this->db->lastInsertId();
            }
        } catch (PDOException $e) {
            // Ghi log lỗi database
            error_log('GioHangModel::themVaoGioHang PDO Error: ' . $e->getMessage());
            error_log('PDO Error Info: ' . print_r($e->errorInfo ?? [], true));
            // Ném exception với thông báo lỗi
            throw new Exception('Lỗi database: ' . $e->getMessage());
        } catch (Exception $e) {
            // Ghi log lỗi khác
            error_log('GioHangModel::themVaoGioHang Error: ' . $e->getMessage());
            // Ném lại exception
            throw $e;
        }
    }

    /**
     * Đếm tổng số lượng sản phẩm trong giỏ hàng
     * Dùng để hiển thị số lượng trên icon giỏ hàng
     * 
     * @param int $ma_nguoi_dung Mã người dùng
     * @return int Tổng số lượng sản phẩm (tổng của tất cả số lượng trong giỏ hàng)
     */
    public function demSoLuongSanPham($ma_nguoi_dung)
    {
        // Query SQL: Tính tổng số lượng sản phẩm trong giỏ hàng
        // COALESCE: Nếu không có sản phẩm nào thì trả về 0 thay vì NULL
        $sql = "SELECT COALESCE(SUM(ctgh.so_luong), 0) as tong_so_luong
                FROM gio_hang gh
                LEFT JOIN chi_tiet_gio_hang ctgh ON gh.ma_gio_hang = ctgh.ma_gio_hang
                WHERE gh.ma_nguoi_dung = :ma_nguoi_dung";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_nguoi_dung', $ma_nguoi_dung, PDO::PARAM_INT);
        $stmt->execute();
        
        // Lấy kết quả
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Trả về tổng số lượng (hoặc 0 nếu không có)
        return $result['tong_so_luong'] ?? 0;
    }

    /**
     * Lấy tất cả sản phẩm trong giỏ hàng với thông tin đầy đủ
     * Tự động áp dụng giá sale nếu sản phẩm đang trong chương trình sale
     * Tự động cập nhật giá trong database nếu giá sale thay đổi
     * 
     * @param int $ma_nguoi_dung Mã người dùng
     * @return array Mảng các sản phẩm trong giỏ hàng với thông tin:
     *   - ma_chi_tiet: Mã chi tiết giỏ hàng
     *   - ma_thuoc: Mã thuốc
     *   - ten_thuoc: Tên thuốc
     *   - hinh_anh: Ảnh thuốc
     *   - don_vi: Đơn vị
     *   - so_luong: Số lượng trong giỏ hàng
     *   - so_luong_ton: Số lượng tồn kho
     *   - gia_goc: Giá gốc của thuốc
     *   - don_gia: Giá hiện tại (có thể là giá sale hoặc giá gốc)
     *   - thanh_tien: Thành tiền (số lượng × đơn giá)
     */
    public function layTatCaSanPham($ma_nguoi_dung)
    {
        // Query SQL: Lấy tất cả sản phẩm trong giỏ hàng với thông tin đầy đủ
        // LEFT JOIN sale: Lấy giá sale nếu có (sale đang hoạt động và trong thời gian hiệu lực)
        // COALESCE: Nếu không có sale thì dùng giá đã lưu trong giỏ hàng
        $sql = "SELECT ctgh.*, t.ten_thuoc, t.hinh_anh, t.don_vi, t.so_luong_ton, t.gia as gia_goc,
                ctgh.don_gia as don_gia_da_luu,                    -- Giá đã lưu trong database
                COALESCE(s.gia_sale, ctgh.don_gia) as don_gia,    -- Giá hiện tại (sale hoặc giá đã lưu)
                (ctgh.so_luong * COALESCE(s.gia_sale, ctgh.don_gia)) as thanh_tien  -- Thành tiền
                FROM gio_hang gh
                JOIN chi_tiet_gio_hang ctgh ON gh.ma_gio_hang = ctgh.ma_gio_hang
                JOIN thuoc t ON ctgh.ma_thuoc = t.ma_thuoc
                LEFT JOIN sale s ON t.ma_thuoc = s.ma_thuoc       -- LEFT JOIN để lấy sale nếu có
                    AND s.trang_thai = 1                          -- Sale đang hoạt động
                    AND NOW() >= s.thoi_gian_bat_dau              -- Đã bắt đầu
                    AND NOW() <= s.thoi_gian_ket_thuc             -- Chưa kết thúc
                WHERE gh.ma_nguoi_dung = :ma_nguoi_dung
                ORDER BY ctgh.ma_chi_tiet DESC";                  // Sắp xếp mới nhất trước
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_nguoi_dung', $ma_nguoi_dung, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cập nhật giá trong database nếu có sale mới (giá sale khác với giá đã lưu)
        // Đảm bảo giá trong database luôn được cập nhật theo giá sale hiện tại
        foreach ($results as &$sp) {
            // Giá hiện tại (đã được tính từ COALESCE: sale hoặc giá đã lưu)
            $gia_hien_tai = (float)$sp['don_gia'];
            
            // Giá đã lưu trong database
            $gia_da_luu = (float)$sp['don_gia_da_luu'];
            
            // Nếu giá sale khác với giá đã lưu (sai số > 0.01), cập nhật lại giá trong database
            // 0.01 là để tránh lỗi làm tròn số thập phân
            if (abs($gia_hien_tai - $gia_da_luu) > 0.01) {
                $this->capNhatGia($sp['ma_chi_tiet'], $gia_hien_tai);
            }
    }

        // Trả về danh sách sản phẩm
        return $results;
    }
    
    /**
     * Cập nhật giá sản phẩm trong giỏ hàng (phương thức private)
     * Dùng để cập nhật giá khi có sale mới hoặc giá sale thay đổi
     * 
     * @param int $ma_chi_tiet Mã chi tiết giỏ hàng
     * @param float $don_gia Giá mới cần cập nhật
     */
    private function capNhatGia($ma_chi_tiet, $don_gia)
    {
        // Query SQL: Cập nhật giá trong chi tiết giỏ hàng
        $sql = "UPDATE chi_tiet_gio_hang 
                SET don_gia = :don_gia
                WHERE ma_chi_tiet = :ma_chi_tiet";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':don_gia', $don_gia);
        $stmt->bindValue(':ma_chi_tiet', $ma_chi_tiet, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng
     * Nếu số lượng <= 0, tự động xóa sản phẩm khỏi giỏ hàng
     * 
     * @param int $ma_chi_tiet Mã chi tiết giỏ hàng
     * @param int $so_luong Số lượng mới
     * @return bool true nếu cập nhật thành công, false nếu không
     */
    public function capNhatSoLuong($ma_chi_tiet, $so_luong)
    {
        // Nếu số lượng <= 0, xóa sản phẩm khỏi giỏ hàng
        if ($so_luong <= 0) {
            return $this->xoaSanPham($ma_chi_tiet);
        }

        // Query SQL: Cập nhật số lượng
        $sql = "UPDATE chi_tiet_gio_hang 
                SET so_luong = :so_luong
                WHERE ma_chi_tiet = :ma_chi_tiet";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':so_luong', $so_luong, PDO::PARAM_INT);
        $stmt->bindValue(':ma_chi_tiet', $ma_chi_tiet, PDO::PARAM_INT);
        
        // Trả về kết quả (true nếu thành công)
        return $stmt->execute();
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     * 
     * @param int $ma_chi_tiet Mã chi tiết giỏ hàng cần xóa
     * @return bool true nếu xóa thành công, false nếu không
     */
    public function xoaSanPham($ma_chi_tiet)
    {
        // Query SQL: Xóa chi tiết giỏ hàng
        $sql = "DELETE FROM chi_tiet_gio_hang WHERE ma_chi_tiet = :ma_chi_tiet";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_chi_tiet', $ma_chi_tiet, PDO::PARAM_INT);
        
        // Trả về kết quả (true nếu thành công)
        return $stmt->execute();
    }

    /**
     * Xóa tất cả sản phẩm trong giỏ hàng
     * Dùng sau khi đặt hàng thành công
     * 
     * @param int $ma_nguoi_dung Mã người dùng
     * @return bool true nếu xóa thành công, false nếu không
     */
    public function xoaTatCa($ma_nguoi_dung)
    {
        // Lấy giỏ hàng của người dùng
        $gioHang = $this->layHoacTaoGioHang($ma_nguoi_dung);
        
        // Nếu có giỏ hàng, xóa tất cả chi tiết
        if ($gioHang) {
            // Query SQL: Xóa tất cả chi tiết giỏ hàng
            $sql = "DELETE FROM chi_tiet_gio_hang WHERE ma_gio_hang = :ma_gio_hang";
            
            // Chuẩn bị và thực thi query
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':ma_gio_hang', $gioHang['ma_gio_hang'], PDO::PARAM_INT);
            
            // Trả về kết quả (true nếu thành công)
            return $stmt->execute();
        }
        
        // Nếu không có giỏ hàng, trả về false
        return false;
    }
}
