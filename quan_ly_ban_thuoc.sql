-- =========================
-- TẠO DATABASE
-- =========================
CREATE DATABASE IF NOT EXISTS quan_ly_ban_thuoc
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE quan_ly_ban_thuoc;

-- =========================
-- XÓA CÁC BẢNG CŨ (NẾU CÓ) - THEO THỨ TỰ NGƯỢC LẠI
-- =========================
SET FOREIGN_KEY_CHECKS = 0;


SET FOREIGN_KEY_CHECKS = 1;

-- =========================
-- 1. BẢNG VAI TRÒ
-- =========================
CREATE TABLE vai_tro (
    ma_vai_tro   INT AUTO_INCREMENT PRIMARY KEY,
    ten_vai_tro  VARCHAR(50) NOT NULL UNIQUE,  -- KHACH_HANG, NHAN_VIEN, DUOC_SI, QUAN_TRI
    mo_ta        VARCHAR(255)
) ENGINE=InnoDB;

-- =========================
-- 2. BẢNG NGƯỜI DÙNG
-- =========================
CREATE TABLE nguoi_dung (
    ma_nguoi_dung   INT AUTO_INCREMENT PRIMARY KEY,
    ho_ten          VARCHAR(100) NOT NULL,
    email           VARCHAR(100) NOT NULL UNIQUE,
    mat_khau        VARCHAR(255) NOT NULL,
    so_dien_thoai   VARCHAR(20),
    dia_chi         VARCHAR(255),
    ma_vai_tro      INT NOT NULL,
    trang_thai      TINYINT(1) NOT NULL DEFAULT 1, -- 1: hoạt động, 0: khóa
    ngay_tao        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat   DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_nguoi_dung_vai_tro
        FOREIGN KEY (ma_vai_tro) REFERENCES vai_tro(ma_vai_tro)
) ENGINE=InnoDB;

-- =========================
-- 3. BẢNG QUÊN MẬT KHẨU
-- =========================
CREATE TABLE token_quen_mat_khau (
    ma_token      INT AUTO_INCREMENT PRIMARY KEY,
    ma_nguoi_dung INT NOT NULL,
    token         VARCHAR(255) NOT NULL UNIQUE,
    thoi_han      DATETIME NOT NULL,
    da_su_dung    TINYINT(1) NOT NULL DEFAULT 0,
    ngay_tao      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_token_nguoi_dung
        FOREIGN KEY (ma_nguoi_dung) REFERENCES nguoi_dung(ma_nguoi_dung)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- 4. BẢNG DANH MỤC THUỐC
-- =========================
CREATE TABLE danh_muc (
    ma_danh_muc   INT AUTO_INCREMENT PRIMARY KEY,
    ten_danh_muc  VARCHAR(100) NOT NULL,
    slug          VARCHAR(150) UNIQUE,
    mo_ta         TEXT,
    ma_danh_muc_cha INT NULL,
    trang_thai    TINYINT(1) NOT NULL DEFAULT 1,

    CONSTRAINT fk_danh_muc_cha
        FOREIGN KEY (ma_danh_muc_cha) REFERENCES danh_muc(ma_danh_muc)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================
-- 5. BẢNG THUỐC
-- =========================
CREATE TABLE thuoc (
    ma_thuoc        INT AUTO_INCREMENT PRIMARY KEY,
    ma_danh_muc     INT NOT NULL,
    ten_thuoc       VARCHAR(200) NOT NULL,
    slug            VARCHAR(255) UNIQUE,
    mo_ta           TEXT,
    huong_dan_dung  TEXT,
    lieu_dung       TEXT,
    chong_chi_dinh  TEXT,
    gia             DECIMAL(15,2) NOT NULL,
    don_vi          VARCHAR(50) DEFAULT 'Hộp',
    hinh_anh        VARCHAR(255),
    hinh_anh_2      VARCHAR(255),
    hinh_anh_3      VARCHAR(255),
    hinh_anh_4      VARCHAR(255),
    hinh_anh_5      VARCHAR(255),
    so_luong_ton    INT NOT NULL DEFAULT 0,
    han_su_dung     DATE NULL,
    trang_thai      TINYINT(1) NOT NULL DEFAULT 1,
    ngay_tao        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat   DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_thuoc_danh_muc
        FOREIGN KEY (ma_danh_muc) REFERENCES danh_muc(ma_danh_muc)
) ENGINE=InnoDB;

-- =========================
-- 6. BẢNG TIN TỨC
-- =========================
CREATE TABLE tin_tuc (
    ma_tin_tuc     INT AUTO_INCREMENT PRIMARY KEY,
    tieu_de        VARCHAR(255) NOT NULL,
    slug           VARCHAR(255) UNIQUE,
    tom_tat        TEXT,
    noi_dung       TEXT NOT NULL,
    hinh_anh       VARCHAR(255),
    hinh_anh_2     VARCHAR(255),
    hinh_anh_3     VARCHAR(255),
    hinh_anh_4     VARCHAR(255),
    hinh_anh_5     VARCHAR(255),
    tac_gia        VARCHAR(100),
    luot_xem       INT NOT NULL DEFAULT 0,
    trang_thai     TINYINT(1) NOT NULL DEFAULT 1, -- 1: hiển thị, 0: ẩn
    ngay_tao       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat  DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Banner (quản lý ảnh sự kiện / slide)
CREATE TABLE banner (
    ma_banner      INT AUTO_INCREMENT PRIMARY KEY,
    ma_danh_muc    INT NULL,
    tieu_de        VARCHAR(255) NULL,
    hinh_anh       VARCHAR(255) NOT NULL,
    hinh_anh_2     VARCHAR(255) NULL,
    hinh_anh_3     VARCHAR(255) NULL,
    hinh_anh_4     VARCHAR(255) NULL,
    hinh_anh_5     VARCHAR(255) NULL,
    lien_ket       VARCHAR(255) NULL,
    thu_tu         INT NOT NULL DEFAULT 0,
    trang_thai     TINYINT(1) NOT NULL DEFAULT 1, -- 1: hiển thị, 0: ẩn
    ngay_tao       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat  DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_banner_danh_muc
        FOREIGN KEY (ma_danh_muc) REFERENCES danh_muc(ma_danh_muc)
) ENGINE=InnoDB;

-- =========================
-- BẢNG SALE (Khuyến mãi)
-- =========================
CREATE TABLE sale (
    ma_sale          INT AUTO_INCREMENT PRIMARY KEY,
    ma_thuoc         INT NOT NULL,
    phan_tram_giam   DECIMAL(5,2) NOT NULL COMMENT 'Phần trăm giảm giá (1-100)',
    gia_sale         DECIMAL(12,2) NOT NULL COMMENT 'Giá sau khi giảm',
    thoi_gian_bat_dau DATETIME NOT NULL,
    thoi_gian_ket_thuc DATETIME NOT NULL,
    trang_thai       TINYINT(1) DEFAULT 1 COMMENT '1: Hoạt động, 0: Tạm ngưng',
    ngay_tao         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_sale_thuoc
        FOREIGN KEY (ma_thuoc) REFERENCES thuoc(ma_thuoc)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- 7. BẢNG GIỎ HÀNG
-- =========================
CREATE TABLE gio_hang (
    ma_gio_hang   INT AUTO_INCREMENT PRIMARY KEY,
    ma_nguoi_dung INT NOT NULL,
    ngay_tao      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_gio_hang_nguoi_dung
        FOREIGN KEY (ma_nguoi_dung) REFERENCES nguoi_dung(ma_nguoi_dung)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE chi_tiet_gio_hang (
    ma_chi_tiet   INT AUTO_INCREMENT PRIMARY KEY,
    ma_gio_hang   INT NOT NULL,
    ma_thuoc      INT NOT NULL,
    so_luong      INT NOT NULL DEFAULT 1,
    don_gia       DECIMAL(15,2) NOT NULL,

    CONSTRAINT fk_ctgh_gio_hang
        FOREIGN KEY (ma_gio_hang) REFERENCES gio_hang(ma_gio_hang)
        ON DELETE CASCADE,

    CONSTRAINT fk_ctgh_thuoc
        FOREIGN KEY (ma_thuoc) REFERENCES thuoc(ma_thuoc)
) ENGINE=InnoDB;

-- =========================
-- 8. BẢNG ĐƠN HÀNG
-- =========================
CREATE TABLE don_hang (
    ma_don_hang        INT AUTO_INCREMENT PRIMARY KEY,
    ma_don             VARCHAR(50) UNIQUE,
    ma_khach_hang      INT NOT NULL,
    ngay_dat           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    trang_thai_don     ENUM('CHO_XU_LY','DANG_XU_LY','DANG_GIAO','HOAN_TAT','DA_HUY')
                       NOT NULL DEFAULT 'CHO_XU_LY',

    hinh_thuc_thanh_toan ENUM('COD','CHUYEN_KHOAN')
                         NOT NULL DEFAULT 'COD',

    trang_thai_thanh_toan ENUM('CHUA_THANH_TOAN','DA_THANH_TOAN','HOAN_TIEN')
                          NOT NULL DEFAULT 'CHUA_THANH_TOAN',

    tong_tien          DECIMAL(15,2) NOT NULL DEFAULT 0,
    dia_chi_giao       VARCHAR(255) NOT NULL,
    so_dien_thoai_giao VARCHAR(20) NOT NULL,
    ghi_chu            VARCHAR(255),

    ngay_tao           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat      DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_don_hang_khach_hang
        FOREIGN KEY (ma_khach_hang) REFERENCES nguoi_dung(ma_nguoi_dung)
) ENGINE=InnoDB;

-- =========================
-- 9. CHI TIẾT ĐƠN HÀNG
-- =========================
CREATE TABLE chi_tiet_don_hang (
    ma_chi_tiet   INT AUTO_INCREMENT PRIMARY KEY,
    ma_don_hang   INT NOT NULL,
    ma_thuoc      INT NOT NULL,
    so_luong      INT NOT NULL,
    don_gia       DECIMAL(15,2) NOT NULL,
    thanh_tien    DECIMAL(15,2) NOT NULL,

    CONSTRAINT fk_ctdh_don_hang
        FOREIGN KEY (ma_don_hang) REFERENCES don_hang(ma_don_hang)
        ON DELETE CASCADE,

    CONSTRAINT fk_ctdh_thuoc
        FOREIGN KEY (ma_thuoc) REFERENCES thuoc(ma_thuoc)
) ENGINE=InnoDB;

-- =========================
-- INSERT DỮ LIỆU MẪU
-- =========================

-- Insert các vai trò
INSERT INTO vai_tro (ten_vai_tro, mo_ta) VALUES
('KHACH_HANG', 'Khách hàng'),
('NHAN_VIEN', 'Nhân viên'),
('QUAN_TRI', 'Quản trị viên')
ON DUPLICATE KEY UPDATE mo_ta = VALUES(mo_ta);

-- Insert tài khoản admin mẫu (Email: admin@gmail.com, Mật khẩu: 12345)
-- Lưu ý: Hash mật khẩu được tạo bằng password_hash('12345', PASSWORD_DEFAULT)
-- Nếu hash không đúng, chạy file fix_admin_password.php để cập nhật
INSERT INTO nguoi_dung (ho_ten, email, mat_khau, so_dien_thoai, dia_chi, ma_vai_tro, trang_thai) 
SELECT 
    'Admin', 
    'admin@gmail.com', 
    '$2y$10$Vp7yig1rYH15Mg.koLGTe.NmovK8vKIQBel.fS5GPIQ1WYcVw8taC',  -- Hash của mật khẩu: 12345 (đã verify)
    '0123456789', 
    '248A Nơ Trang Long, Bình Thạnh, TP.HCM', 
    ma_vai_tro, 
    1
FROM vai_tro 
WHERE ten_vai_tro = 'QUAN_TRI'
ON DUPLICATE KEY UPDATE 
    ho_ten = VALUES(ho_ten),
    mat_khau = VALUES(mat_khau),
    ma_vai_tro = (SELECT ma_vai_tro FROM vai_tro WHERE ten_vai_tro = 'QUAN_TRI'),
    trang_thai = 1;

-- =========================
-- KIỂM TRA KẾT QUẢ
-- =========================
SELECT '=== VAI TRÒ ===' as '';
SELECT * FROM vai_tro;

SELECT '=== TÀI KHOẢN ADMIN ===' as '';
SELECT 
    n.ma_nguoi_dung,
    n.ho_ten,
    n.email,
    n.trang_thai,
    v.ten_vai_tro,
    v.mo_ta
FROM nguoi_dung n
JOIN vai_tro v ON n.ma_vai_tro = v.ma_vai_tro
WHERE n.email = 'admin@gmail.com';
