<?php
require_once __DIR__ . '/../core/Model.php';

class AdminModel extends Model
{
    // Lấy tổng số người dùng
    public function tongNguoiDung()
    {
        $sql = "SELECT COUNT(*) as total FROM nguoi_dung";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // Lấy tổng số thuốc
    public function tongThuoc()
    {
        $sql = "SELECT COUNT(*) as total FROM thuoc";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // Lấy tổng số đơn hàng
    public function tongDonHang()
    {
        $sql = "SELECT COUNT(*) as total FROM don_hang";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // Lấy tổng doanh thu
    public function tongDoanhThu()
    {
        $sql = "SELECT COALESCE(SUM(tong_tien), 0) as total 
                FROM don_hang 
                WHERE trang_thai_thanh_toan = 'DA_THANH_TOAN' 
                AND trang_thai_don != 'DA_HUY'";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // Lấy đơn hàng gần đây
    public function donHangGanDay($limit = 5)
    {
        $sql = "SELECT d.*, n.ho_ten, n.email 
                FROM don_hang d
                JOIN nguoi_dung n ON d.ma_khach_hang = n.ma_nguoi_dung
                ORDER BY d.ngay_tao DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy người dùng mới đăng ký gần đây
    public function nguoiDungMoi($limit = 5)
    {
        $sql = "SELECT n.*, v.ten_vai_tro, v.mo_ta as mo_ta_vai_tro
                FROM nguoi_dung n
                JOIN vai_tro v ON n.ma_vai_tro = v.ma_vai_tro
                ORDER BY n.ngay_tao DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Thống kê theo vai trò
    public function thongKeTheoVaiTro()
    {
        $sql = "SELECT v.ten_vai_tro, v.mo_ta, COUNT(n.ma_nguoi_dung) as so_luong
                FROM vai_tro v
                LEFT JOIN nguoi_dung n ON v.ma_vai_tro = n.ma_vai_tro
                GROUP BY v.ma_vai_tro, v.ten_vai_tro, v.mo_ta
                ORDER BY so_luong DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Thống kê đơn hàng theo trạng thái
    public function thongKeDonHangTheoTrangThai()
    {
        $sql = "SELECT trang_thai_don, COUNT(*) as so_luong
                FROM don_hang
                GROUP BY trang_thai_don";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Đếm số thuốc sắp hết hạn (trong 30 ngày)
    public function demThuocSapHetHan()
    {
        $sql = "SELECT COUNT(*) as total
                FROM thuoc
                WHERE han_su_dung IS NOT NULL
                AND han_su_dung >= CURDATE()
                AND han_su_dung <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // Đếm số thuốc đã hết hạn
    public function demThuocHetHan()
    {
        $sql = "SELECT COUNT(*) as total
                FROM thuoc
                WHERE han_su_dung IS NOT NULL
                AND han_su_dung < CURDATE()";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}

