<?php
/**
 * app/models/NguoiDungModel.php - Model quản lý người dùng
 * 
 * Class này chứa tất cả các phương thức để thao tác với bảng nguoi_dung:
 * - Đăng ký, đăng nhập
 * - Quên mật khẩu, reset mật khẩu
 * - Quản lý thông tin cá nhân
 * - Quản lý vai trò và trạng thái (dành cho admin)
 */

// Nạp class Model cơ sở
require_once __DIR__ . '/../core/Model.php';

class NguoiDungModel extends Model
{
    /**
     * Lấy thông tin người dùng theo email
     * Tìm kiếm không phân biệt hoa thường
     * 
     * @param string $email Email cần tìm
     * @return array|null Thông tin người dùng (bao gồm vai trò) hoặc null nếu không tìm thấy
     */
    public function layTheoEmail($email)
    {
        // Query SQL: Tìm kiếm email không phân biệt hoa thường
        // LOWER(): Chuyển email về chữ thường để so sánh
        // JOIN vai_tro: Lấy thêm thông tin vai trò (QUAN_TRI, NHAN_VIEN, KHACH_HANG)
        $sql = "SELECT n.*, v.ten_vai_tro 
                FROM nguoi_dung n 
                JOIN vai_tro v ON n.ma_vai_tro = v.ma_vai_tro 
                WHERE LOWER(n.email) = LOWER(:email)";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', trim($email));  // Loại bỏ khoảng trắng đầu/cuối
        $stmt->execute();
        
        // Trả về 1 dòng kết quả (hoặc null nếu không tìm thấy)
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin người dùng theo mã người dùng
     * 
     * @param int $ma_nguoi_dung Mã người dùng
     * @return array|null Thông tin người dùng (bao gồm vai trò) hoặc null nếu không tìm thấy
     */
    public function layTheoMa($ma_nguoi_dung)
    {
        // Query SQL: Lấy thông tin người dùng theo mã
        $sql = "SELECT n.*, v.ten_vai_tro 
                FROM nguoi_dung n 
                JOIN vai_tro v ON n.ma_vai_tro = v.ma_vai_tro 
                WHERE n.ma_nguoi_dung = :ma_nguoi_dung";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_nguoi_dung', $ma_nguoi_dung, PDO::PARAM_INT);
        $stmt->execute();
        
        // Trả về 1 dòng kết quả (hoặc null nếu không tìm thấy)
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Đăng ký người dùng mới (khách hàng)
     * Tự động gán vai trò KHACH_HANG
     * Tự động tạo vai trò KHACH_HANG nếu chưa có trong database
     * 
     * @param array $data Mảng chứa thông tin đăng ký:
     *   - ho_ten: Họ tên (bắt buộc)
     *   - email: Email (bắt buộc)
     *   - mat_khau: Mật khẩu (bắt buộc, sẽ được hash)
     *   - so_dien_thoai: Số điện thoại (tùy chọn)
     *   - dia_chi: Địa chỉ (tùy chọn)
     * @return int Mã người dùng vừa được tạo (lastInsertId)
     * @throws Exception Nếu thiếu thông tin, email đã tồn tại, hoặc lỗi database
     */
    public function dangKy($data)
    {
        // Kiểm tra dữ liệu đầu vào: họ tên, email, mật khẩu là bắt buộc
        if (empty($data['ho_ten']) || empty($data['email']) || empty($data['mat_khau'])) {
            throw new Exception('Thông tin đăng ký không đầy đủ');
        }

        // Kiểm tra email đã tồn tại chưa (double check để đảm bảo)
        if ($this->kiemTraEmailTonTai($data['email'])) {
            throw new Exception('Email này đã được sử dụng');
        }

        // Lấy mã vai trò KHACH_HANG từ database
        $sql_vt = "SELECT ma_vai_tro FROM vai_tro WHERE ten_vai_tro = 'KHACH_HANG' LIMIT 1";
        $stmt_vt = $this->db->prepare($sql_vt);
        $stmt_vt->execute();
        $vai_tro = $stmt_vt->fetch(PDO::FETCH_ASSOC);
        
        // Nếu chưa có vai trò KHACH_HANG trong database, tự động tạo
        if (!$vai_tro) {
            try {
                // Tạo vai trò KHACH_HANG mới
                $sql_insert_vt = "INSERT INTO vai_tro (ten_vai_tro, mo_ta) VALUES ('KHACH_HANG', 'Khách hàng')";
                $this->db->exec($sql_insert_vt);
                
                // Lấy lại mã vai trò vừa tạo
                $stmt_vt->execute();
                $vai_tro = $stmt_vt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Nếu lỗi duplicate key (có thể do race condition), thử lấy lại
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $stmt_vt->execute();
                $vai_tro = $stmt_vt->fetch(PDO::FETCH_ASSOC);
                } else {
                    // Nếu lỗi khác, ném exception
                throw new Exception("Không thể tạo vai trò KHACH_HANG. Vui lòng chạy SQL để tạo vai trò trước.");
            }
        }
        }
        
        // Kiểm tra lại xem có lấy được vai trò không
        if (!$vai_tro || !isset($vai_tro['ma_vai_tro'])) {
            throw new Exception("Không tìm thấy vai trò KHACH_HANG. Vui lòng chạy file quan_ly_ban_thuoc.sql để tạo vai trò.");
        }

        // Hash mật khẩu bằng password_hash() với thuật toán mặc định (bcrypt)
        // Mật khẩu sẽ được hash an toàn, không thể reverse
        $hashedPassword = password_hash($data['mat_khau'], PASSWORD_DEFAULT);
        if ($hashedPassword === false) {
            throw new Exception('Không thể mã hóa mật khẩu');
        }

        // Query SQL: Thêm người dùng mới vào database
        // trang_thai = 1: Tài khoản được kích hoạt ngay sau khi đăng ký
        $sql = "INSERT INTO nguoi_dung (ho_ten, email, mat_khau, so_dien_thoai, dia_chi, ma_vai_tro, trang_thai) 
                VALUES (:ho_ten, :email, :mat_khau, :so_dien_thoai, :dia_chi, :ma_vai_tro, 1)";
        $stmt = $this->db->prepare($sql);
        
        // Thực thi với dữ liệu đã được xử lý
        $result = $stmt->execute([
            ':ho_ten' => trim($data['ho_ten']),                    // Loại bỏ khoảng trắng đầu/cuối
            ':email' => trim(strtolower($data['email'])),         // Chuyển về chữ thường và loại bỏ khoảng trắng
            ':mat_khau' => $hashedPassword,                       // Mật khẩu đã được hash
            ':so_dien_thoai' => !empty($data['so_dien_thoai']) ? trim($data['so_dien_thoai']) : null,  // SĐT (tùy chọn)
            ':dia_chi' => !empty($data['dia_chi']) ? trim($data['dia_chi']) : null,                    // Địa chỉ (tùy chọn)
            ':ma_vai_tro' => $vai_tro['ma_vai_tro']              // Mã vai trò KHACH_HANG
        ]);

        // Kiểm tra xem có thêm thành công không
        if (!$result) {
            throw new Exception('Không thể tạo tài khoản. Vui lòng thử lại.');
        }

        // Trả về mã người dùng vừa được tạo (auto increment)
        return $this->db->lastInsertId();
    }

    /**
     * Kiểm tra email đã tồn tại trong database chưa
     * Dùng để validate trước khi đăng ký
     * 
     * @param string $email Email cần kiểm tra
     * @return bool true nếu email đã tồn tại, false nếu chưa
     */
    public function kiemTraEmailTonTai($email)
    {
        // Query SQL: Đếm số lượng người dùng có email này
        $sql = "SELECT COUNT(*) as count FROM nguoi_dung WHERE email = :email";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        
        // Lấy kết quả
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Trả về true nếu count > 0 (email đã tồn tại)
        return $result['count'] > 0;
    }

    /**
     * Tạo token quên mật khẩu
     * Token này được gửi qua email để người dùng reset mật khẩu
     * Tự động xóa các token cũ đã hết hạn hoặc đã sử dụng
     * 
     * @param int $ma_nguoi_dung Mã người dùng cần reset mật khẩu
     * @param string $token Token ngẫu nhiên (thường là hash)
     * @param string $thoi_han Thời hạn token (format: Y-m-d H:i:s)
     * @return int Mã token vừa được tạo (lastInsertId)
     */
    public function taoTokenQuenMatKhau($ma_nguoi_dung, $token, $thoi_han)
    {
        // Xóa các token cũ của người dùng này (đã hết hạn hoặc đã sử dụng)
        // Để tránh tích lũy token không cần thiết trong database
        $sql_delete = "DELETE FROM token_quen_mat_khau 
                       WHERE ma_nguoi_dung = :ma_nguoi_dung 
                       AND (da_su_dung = 1 OR thoi_han < NOW())";  // Đã sử dụng HOẶC đã hết hạn
        $stmt_delete = $this->db->prepare($sql_delete);
        $stmt_delete->execute([':ma_nguoi_dung' => $ma_nguoi_dung]);
        
        // Tạo token mới cho người dùng
        $sql = "INSERT INTO token_quen_mat_khau (ma_nguoi_dung, token, thoi_han) 
                VALUES (:ma_nguoi_dung, :token, :thoi_han)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ma_nguoi_dung' => $ma_nguoi_dung,  // Mã người dùng
            ':token' => $token,                  // Token ngẫu nhiên
            ':thoi_han' => $thoi_han             // Thời hạn token
        ]);
        
        // Trả về mã token vừa được tạo
        return $this->db->lastInsertId();
    }

    /**
     * Lấy thông tin token quên mật khẩu và kiểm tra tính hợp lệ
     * 
     * @param string $token Token cần kiểm tra
     * @return array|null Thông tin token (bao gồm email người dùng) hoặc null nếu:
     *   - Token không tồn tại
     *   - Token đã được sử dụng
     *   - Token đã hết hạn
     */
    public function layTokenQuenMatKhau($token)
    {
        // Query SQL: Lấy thông tin token và email người dùng
        // Không kiểm tra thời hạn và trạng thái ở đây, sẽ kiểm tra sau
        $sql_check = "SELECT t.*, n.email 
                FROM token_quen_mat_khau t 
                JOIN nguoi_dung n ON t.ma_nguoi_dung = n.ma_nguoi_dung 
                      WHERE t.token = :token";
        $stmt_check = $this->db->prepare($sql_check);
        $stmt_check->bindValue(':token', $token);
        $stmt_check->execute();
        $tokenInfo = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        // Nếu token không tồn tại, trả về null
        if (!$tokenInfo) {
            return null;
        }
        
        // Kiểm tra token đã được sử dụng chưa (da_su_dung = 1)
        // Token chỉ được sử dụng 1 lần duy nhất
        if ($tokenInfo['da_su_dung'] == 1) {
            return null;  // Token đã được sử dụng, không hợp lệ
        }
        
        // Kiểm tra thời hạn token (so sánh với thời gian hiện tại của server)
        $now = date('Y-m-d H:i:s');
        if (strtotime($tokenInfo['thoi_han']) < strtotime($now)) {
            return null;  // Token đã hết hạn, không hợp lệ
        }
        
        // Token hợp lệ, trả về thông tin token
        return $tokenInfo;
    }

    /**
     * Đánh dấu token đã được sử dụng
     * Sau khi người dùng reset mật khẩu thành công, token sẽ được đánh dấu đã sử dụng
     * Đảm bảo token chỉ được dùng 1 lần
     * 
     * @param string $token Token cần đánh dấu
     * @return bool true nếu cập nhật thành công, false nếu không
     */
    public function danhDauTokenDaSuDung($token)
    {
        // Query SQL: Đánh dấu token đã được sử dụng
        $sql = "UPDATE token_quen_mat_khau SET da_su_dung = 1 WHERE token = :token";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':token', $token);
        
        // Trả về kết quả (true nếu thành công)
        return $stmt->execute();
    }

    /**
     * Đổi mật khẩu cho người dùng
     * Mật khẩu mới sẽ được hash trước khi lưu vào database
     * 
     * @param int $ma_nguoi_dung Mã người dùng cần đổi mật khẩu
     * @param string $mat_khau_moi Mật khẩu mới (chưa hash)
     * @return bool true nếu cập nhật thành công, false nếu không
     */
    public function doiMatKhau($ma_nguoi_dung, $mat_khau_moi)
    {
        // Query SQL: Cập nhật mật khẩu mới
        $sql = "UPDATE nguoi_dung SET mat_khau = :mat_khau WHERE ma_nguoi_dung = :ma_nguoi_dung";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':mat_khau' => password_hash($mat_khau_moi, PASSWORD_DEFAULT),  // Hash mật khẩu mới
            ':ma_nguoi_dung' => $ma_nguoi_dung
        ]);
        
        // Trả về true nếu có ít nhất 1 dòng được cập nhật
        return $stmt->rowCount() > 0;
    }

    /**
     * Lấy tất cả người dùng (dành cho admin)
     * 
     * @param int|null $limit Số lượng người dùng cần lấy (null = lấy tất cả)
     * @param int $offset Vị trí bắt đầu (dùng cho phân trang)
     * @return array Mảng các người dùng với thông tin vai trò
     */
    public function layTatCa($limit = null, $offset = 0)
    {
        // Query SQL: Lấy tất cả người dùng với thông tin vai trò
        $sql = "SELECT n.*, v.ten_vai_tro, v.mo_ta as mo_ta_vai_tro
                FROM nguoi_dung n
                JOIN vai_tro v ON n.ma_vai_tro = v.ma_vai_tro
                ORDER BY n.ngay_tao DESC";  // Sắp xếp mới nhất trước
        
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
     * Cập nhật trạng thái người dùng (kích hoạt/khóa tài khoản)
     * Dành cho admin
     * 
     * @param int $ma_nguoi_dung Mã người dùng cần cập nhật
     * @param int $trang_thai Trạng thái mới (1 = kích hoạt, 0 = khóa)
     * @return bool true nếu cập nhật thành công, false nếu không
     */
    public function capNhatTrangThai($ma_nguoi_dung, $trang_thai)
    {
        // Query SQL: Cập nhật trạng thái người dùng
        $sql = "UPDATE nguoi_dung SET trang_thai = :trang_thai WHERE ma_nguoi_dung = :ma_nguoi_dung";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':trang_thai' => $trang_thai,        // Trạng thái mới (1 = kích hoạt, 0 = khóa)
            ':ma_nguoi_dung' => $ma_nguoi_dung   // Mã người dùng
        ]);
        
        // Trả về true nếu có ít nhất 1 dòng được cập nhật
        return $stmt->rowCount() > 0;
    }

    /**
     * Cập nhật vai trò người dùng (QUAN_TRI, NHAN_VIEN, KHACH_HANG)
     * Dành cho admin
     * 
     * @param int $ma_nguoi_dung Mã người dùng cần cập nhật
     * @param int $ma_vai_tro Mã vai trò mới
     * @return bool true nếu cập nhật thành công, false nếu không
     */
    public function capNhatVaiTro($ma_nguoi_dung, $ma_vai_tro)
    {
        // Query SQL: Cập nhật vai trò người dùng
        $sql = "UPDATE nguoi_dung SET ma_vai_tro = :ma_vai_tro WHERE ma_nguoi_dung = :ma_nguoi_dung";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ma_vai_tro' => $ma_vai_tro,        // Mã vai trò mới
            ':ma_nguoi_dung' => $ma_nguoi_dung   // Mã người dùng
        ]);
        
        // Trả về true nếu có ít nhất 1 dòng được cập nhật
        return $stmt->rowCount() > 0;
    }

    /**
     * Lấy danh sách tất cả vai trò trong hệ thống
     * Dùng để hiển thị dropdown chọn vai trò cho admin
     * 
     * @return array Mảng các vai trò (ma_vai_tro, ten_vai_tro, mo_ta)
     */
    public function layTatCaVaiTro()
    {
        // Query SQL: Lấy tất cả vai trò, sắp xếp theo tên
        $sql = "SELECT * FROM vai_tro ORDER BY ten_vai_tro";
        
        // Thực thi query (không cần prepare vì không có tham số từ người dùng)
        $stmt = $this->db->query($sql);
        
        // Trả về tất cả kết quả
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy mã vai trò theo tên vai trò
     * 
     * @param string $ten_vai_tro Tên vai trò (QUAN_TRI, NHAN_VIEN, KHACH_HANG)
     * @return int|null Mã vai trò hoặc null nếu không tìm thấy
     */
    public function layMaVaiTroTheoTen($ten_vai_tro)
    {
        // Query SQL: Lấy mã vai trò theo tên
        $sql = "SELECT ma_vai_tro FROM vai_tro WHERE ten_vai_tro = :ten_vai_tro LIMIT 1";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ten_vai_tro' => $ten_vai_tro]);
        
        // Lấy kết quả
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Trả về mã vai trò (ép kiểu int) hoặc null nếu không tìm thấy
        return $row ? (int)$row['ma_vai_tro'] : null;
    }

    /**
     * Tạo người dùng mới bởi admin với vai trò chỉ định
     * Khác với dangKy(): Admin có thể chọn vai trò (QUAN_TRI, NHAN_VIEN, KHACH_HANG)
     * 
     * @param array $data Mảng chứa thông tin người dùng:
     *   - ho_ten: Họ tên (bắt buộc)
     *   - email: Email (bắt buộc)
     *   - mat_khau: Mật khẩu (bắt buộc, sẽ được hash)
     *   - ma_vai_tro: Mã vai trò (bắt buộc)
     *   - so_dien_thoai: Số điện thoại (tùy chọn)
     *   - dia_chi: Địa chỉ (tùy chọn)
     * @return int Mã người dùng vừa được tạo (lastInsertId)
     * @throws Exception Nếu thiếu thông tin, email đã tồn tại, hoặc lỗi database
     */
    public function taoNguoiDungAdmin($data)
    {
        // Kiểm tra dữ liệu đầu vào: họ tên, email, mật khẩu, mã vai trò là bắt buộc
        if (empty($data['ho_ten']) || empty($data['email']) || empty($data['mat_khau']) || empty($data['ma_vai_tro'])) {
            throw new Exception('Thiếu thông tin bắt buộc');
        }

        // Kiểm tra email đã tồn tại chưa
        if ($this->kiemTraEmailTonTai($data['email'])) {
            throw new Exception('Email này đã được sử dụng');
        }

        // Hash mật khẩu bằng password_hash()
        $hashedPassword = password_hash($data['mat_khau'], PASSWORD_DEFAULT);
        if ($hashedPassword === false) {
            throw new Exception('Không thể mã hóa mật khẩu');
        }

        // Query SQL: Thêm người dùng mới với vai trò do admin chỉ định
        // trang_thai = 1: Tài khoản được kích hoạt ngay
        $sql = "INSERT INTO nguoi_dung (ho_ten, email, mat_khau, so_dien_thoai, dia_chi, ma_vai_tro, trang_thai) 
                VALUES (:ho_ten, :email, :mat_khau, :so_dien_thoai, :dia_chi, :ma_vai_tro, 1)";
        $stmt = $this->db->prepare($sql);
        
        // Thực thi với dữ liệu đã được xử lý
        $result = $stmt->execute([
            ':ho_ten' => trim($data['ho_ten']),                    // Loại bỏ khoảng trắng
            ':email' => trim(strtolower($data['email'])),          // Chuyển về chữ thường
            ':mat_khau' => $hashedPassword,                        // Mật khẩu đã hash
            ':so_dien_thoai' => !empty($data['so_dien_thoai']) ? trim($data['so_dien_thoai']) : null,  // SĐT (tùy chọn)
            ':dia_chi' => !empty($data['dia_chi']) ? trim($data['dia_chi']) : null,                    // Địa chỉ (tùy chọn)
            ':ma_vai_tro' => (int)$data['ma_vai_tro']             // Mã vai trò (ép kiểu int)
        ]);

        // Kiểm tra xem có thêm thành công không
        if (!$result) {
            throw new Exception('Không thể tạo tài khoản. Vui lòng thử lại.');
        }

        // Trả về mã người dùng vừa được tạo
        return $this->db->lastInsertId();
    }

    /**
     * Xóa người dùng khỏi database
     * Dành cho admin
     * 
     * @param int $ma_nguoi_dung Mã người dùng cần xóa
     * @return bool true nếu xóa thành công, false nếu không
     */
    public function xoaNguoiDung($ma_nguoi_dung)
    {
        // Query SQL: Xóa người dùng theo mã
        $sql = "DELETE FROM nguoi_dung WHERE ma_nguoi_dung = :ma_nguoi_dung";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ma_nguoi_dung', $ma_nguoi_dung, PDO::PARAM_INT);
        
        // Trả về kết quả (true nếu thành công)
        return $stmt->execute();
    }

    /**
     * Cập nhật thông tin cá nhân (họ tên, số điện thoại, địa chỉ)
     * Dùng cho người dùng tự cập nhật thông tin của mình
     * Không cho phép cập nhật email và mật khẩu (dùng phương thức khác)
     * 
     * @param int $ma_nguoi_dung Mã người dùng cần cập nhật
     * @param array $data Mảng chứa thông tin cần cập nhật:
     *   - ho_ten: Họ tên
     *   - so_dien_thoai: Số điện thoại (tùy chọn)
     *   - dia_chi: Địa chỉ (tùy chọn)
     * @return bool true nếu cập nhật thành công, false nếu không
     */
    public function capNhatThongTinCaNhan($ma_nguoi_dung, $data)
    {
        // Query SQL: Cập nhật thông tin cá nhân
        // Chỉ cập nhật: họ tên, số điện thoại, địa chỉ
        // Không cập nhật: email, mật khẩu, vai trò, trạng thái
        $sql = "UPDATE nguoi_dung 
                SET ho_ten = :ho_ten, 
                    so_dien_thoai = :so_dien_thoai, 
                    dia_chi = :dia_chi 
                WHERE ma_nguoi_dung = :ma_nguoi_dung";
        
        // Chuẩn bị và thực thi query
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':ho_ten' => trim($data['ho_ten']),                  // Họ tên (loại bỏ khoảng trắng)
            ':so_dien_thoai' => $data['so_dien_thoai'] ?? null,  // Số điện thoại (tùy chọn)
            ':dia_chi' => $data['dia_chi'] ?? null,              // Địa chỉ (tùy chọn)
            ':ma_nguoi_dung' => $ma_nguoi_dung                   // Mã người dùng
        ]);
    }
}

