<?php
require_once __DIR__ . '/../models/DonHangModel.php';
require_once __DIR__ . '/../models/ThuocModel.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Session.php';

class ExportController
{
    private $donHangModel;
    private $thuocModel;

    public function __construct()
    {
        $this->donHangModel = new DonHangModel();
        $this->thuocModel = new ThuocModel();
    }

    // Xuất Excel thống kê doanh thu và thuốc bán
    public function xuatExcelThongKe()
    {
        Auth::requireNhanVien();
        
        $type = $_GET['type'] ?? 'ngay';
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $year = isset($_GET['year']) ? (int)$_GET['year'] : null;

        // Tạo tên file
        $fileName = 'ThongKe_' . date('Y-m-d_His') . '.xls';
        
        // Set headers để download file Excel
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        // BOM UTF-8 để Excel hiển thị tiếng Việt đúng
        echo "\xEF\xBB\xBF";
        
        // Bắt đầu tạo nội dung Excel
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1">';
        
        // Sheet 1: Thống kê doanh thu
        echo '<tr><th colspan="4" style="background-color: #4CAF50; color: white; font-weight: bold; padding: 10px;">THỐNG KÊ DOANH THU</th></tr>';
        echo '<tr><th style="background-color: #f0f0f0; font-weight: bold;">Thời gian</th><th style="background-color: #f0f0f0; font-weight: bold;">Doanh thu (₫)</th><th style="background-color: #f0f0f0; font-weight: bold;">Số đơn hàng</th><th style="background-color: #f0f0f0; font-weight: bold;">Ghi chú</th></tr>';
        
        $labels = [];
        $revenues = [];
        $orders = [];
        
        switch ($type) {
            case 'ngay':
                $result = $this->donHangModel->thongKeDoanhThuTheoNgay($startDate, $endDate);
                foreach ($result as $row) {
                    $labels[] = date('d/m/Y', strtotime($row['ngay']));
                    $revenues[] = (float)$row['doanh_thu'];
                    $orders[] = (int)$row['so_don'];
                }
                break;
                
            case 'thang':
                $result = $this->donHangModel->thongKeDoanhThuTheoThang($year);
                $dataMap = [];
                foreach ($result as $row) {
                    $dataMap[(int)$row['thang']] = [
                        'doanh_thu' => (float)$row['doanh_thu'],
                        'so_don' => (int)$row['so_don']
                    ];
                }
                $selectedYear = $year ?: date('Y');
                for ($thang = 1; $thang <= 12; $thang++) {
                    $labels[] = "Tháng " . $thang . "/" . $selectedYear;
                    if (isset($dataMap[$thang])) {
                        $revenues[] = $dataMap[$thang]['doanh_thu'];
                        $orders[] = $dataMap[$thang]['so_don'];
                    } else {
                        $revenues[] = 0;
                        $orders[] = 0;
                    }
                }
                break;
                
            case 'nam':
                $result = $this->donHangModel->thongKeDoanhThuTheoNam();
                foreach ($result as $row) {
                    $labels[] = "Năm " . $row['nam'];
                    $revenues[] = (float)$row['doanh_thu'];
                    $orders[] = (int)$row['so_don'];
                }
                break;
        }
        
        // Hiển thị dữ liệu
        for ($i = 0; $i < count($labels); $i++) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($labels[$i]) . '</td>';
            echo '<td style="text-align: right;">' . number_format($revenues[$i], 0, ',', '.') . ' ₫</td>';
            echo '<td style="text-align: center;">' . $orders[$i] . '</td>';
            echo '<td>' . ($orders[$i] > 0 ? 'Có đơn hàng' : 'Không có đơn hàng') . '</td>';
            echo '</tr>';
        }
        
        // Tổng kết
        $tongKet = $this->donHangModel->tongDoanhThu($startDate, $endDate);
        echo '<tr style="background-color: #ffffcc; font-weight: bold;">';
        echo '<td>TỔNG CỘNG</td>';
        echo '<td style="text-align: right;">' . number_format($tongKet['tong_doanh_thu'] ?? 0, 0, ',', '.') . ' ₫</td>';
        echo '<td style="text-align: center;">' . ($tongKet['tong_so_don'] ?? 0) . '</td>';
        echo '<td></td>';
        echo '</tr>';
        
        // Sheet 2: Thống kê thuốc bán
        echo '<tr><td colspan="4" style="height: 20px;"></td></tr>';
        echo '<tr><th colspan="6" style="background-color: #2196F3; color: white; font-weight: bold; padding: 10px;">THỐNG KÊ THUỐC BÁN</th></tr>';
        echo '<tr><th style="background-color: #f0f0f0; font-weight: bold;">STT</th><th style="background-color: #f0f0f0; font-weight: bold;">Tên thuốc</th><th style="background-color: #f0f0f0; font-weight: bold;">Số lượng bán</th><th style="background-color: #f0f0f0; font-weight: bold;">Đơn giá trung bình (₫)</th><th style="background-color: #f0f0f0; font-weight: bold;">Tổng doanh thu (₫)</th><th style="background-color: #f0f0f0; font-weight: bold;">Đơn vị</th></tr>';
        
        // Lấy thống kê thuốc bán
        $thuocBan = $this->thongKeThuocBan($startDate, $endDate);
        $stt = 1;
        $tongSoLuongBan = 0;
        $tongDoanhThuThuoc = 0;
        
        foreach ($thuocBan as $thuoc) {
            echo '<tr>';
            echo '<td style="text-align: center;">' . $stt++ . '</td>';
            echo '<td>' . htmlspecialchars($thuoc['ten_thuoc']) . '</td>';
            echo '<td style="text-align: right;">' . number_format($thuoc['tong_so_luong'], 0, ',', '.') . '</td>';
            echo '<td style="text-align: right;">' . number_format($thuoc['don_gia_tb'], 0, ',', '.') . ' ₫</td>';
            echo '<td style="text-align: right;">' . number_format($thuoc['tong_doanh_thu'], 0, ',', '.') . ' ₫</td>';
            echo '<td>' . htmlspecialchars($thuoc['don_vi']) . '</td>';
            echo '</tr>';
            
            $tongSoLuongBan += $thuoc['tong_so_luong'];
            $tongDoanhThuThuoc += $thuoc['tong_doanh_thu'];
        }
        
        // Tổng kết thuốc bán
        echo '<tr style="background-color: #ffffcc; font-weight: bold;">';
        echo '<td colspan="2">TỔNG CỘNG</td>';
        echo '<td style="text-align: right;">' . number_format($tongSoLuongBan, 0, ',', '.') . '</td>';
        echo '<td></td>';
        echo '<td style="text-align: right;">' . number_format($tongDoanhThuThuoc, 0, ',', '.') . ' ₫</td>';
        echo '<td></td>';
        echo '</tr>';
        
        // Thông tin xuất file
        echo '<tr><td colspan="6" style="height: 20px;"></td></tr>';
        echo '<tr><td colspan="6" style="font-style: italic; color: #666;">Ngày xuất: ' . date('d/m/Y H:i:s') . '</td></tr>';
        echo '<tr><td colspan="6" style="font-style: italic; color: #666;">Người xuất: ' . htmlspecialchars(Auth::user()['name']) . ' (' . htmlspecialchars(Auth::user()['role']) . ')</td></tr>';
        
        echo '</table></body></html>';
        exit;
    }

    // Thống kê thuốc bán
    private function thongKeThuocBan($startDate = null, $endDate = null)
    {
        $sql = "SELECT 
                    t.ma_thuoc,
                    t.ten_thuoc,
                    t.don_vi,
                    SUM(ctdh.so_luong) as tong_so_luong,
                    AVG(ctdh.don_gia) as don_gia_tb,
                    SUM(ctdh.thanh_tien) as tong_doanh_thu
                FROM chi_tiet_don_hang ctdh
                JOIN don_hang dh ON ctdh.ma_don_hang = dh.ma_don_hang
                JOIN thuoc t ON ctdh.ma_thuoc = t.ma_thuoc
                WHERE dh.trang_thai_don = 'HOAN_TAT'";
        
        $params = [];
        
        if ($startDate) {
            $sql .= " AND DATE(dh.ngay_tao) >= :start_date";
            $params[':start_date'] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND DATE(dh.ngay_tao) <= :end_date";
            $params[':end_date'] = $endDate;
        }
        
        $sql .= " GROUP BY t.ma_thuoc, t.ten_thuoc, t.don_vi ORDER BY tong_doanh_thu DESC";
        
        require_once __DIR__ . '/../core/Database.php';
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

