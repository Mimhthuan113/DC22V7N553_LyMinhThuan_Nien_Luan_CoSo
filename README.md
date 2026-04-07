# 🏥 HỆ THỐNG QUẢN LÝ BÁN THUỐC - PHARMACITY

**Tác giả:** Lý Minh Thuần (DC22V7N553)  
**Loại:** Bài Niên Luận Cơ Sở  
**Ngôn ngữ:** PHP, MySQL, JavaScript  
**Framework:** MVC Pattern (Custom)  
**Trạng thái:** Hoàn thành

---

## 📋 MỤC LỤC

1. [Giới Thiệu](#-giới-thiệu)
2. [Tính Năng Chính](#-tính-năng-chính)
3. [Kiến Trúc Hệ Thống](#-kiến-trúc-hệ-thống)
4. [Cấu Trúc Dự Án](#-cấu-trúc-dự-án)
5. [Hướng Dẫn Cài Đặt](#-hướng-dẫn-cài-đặt)
6. [Hướng Dẫn Sử Dụng](#-hướng-dẫn-sử-dụng)
7. [Công Nghệ Sử Dụng](#-công-nghệ-sử-dụng)
8. [Cơ Sở Dữ Liệu](#-cơ-sở-dữ-liệu)

---

## 🎯 Giới Thiệu

**Hệ Thống Quản Lý Bán Thuốc - Pharmacity** là một ứng dụng web toàn diện được xây dựng dựa trên mô hình **MVC (Model-View-Controller)** để quản lý hiệu thuốc từ phía quản trị viên, nhân viên và khách hàng.

### Mục Tiêu Dự Án:
- ✅ Cung cấp nền tảng quản lý sản phẩm (thuốc) hiện đại
- ✅ Tối ưu hóa quy trình bán hàng và quản lý đơn hàng
- ✅ Quản lý người dùng, tài khoản và phân quyền
- ✅ Hỗ trợ chương trình khuyến mại, sale, giảm giá
- ✅ Cung cấp giao diện thân thiện cho cả admin và khách hàng
- ✅ Hỗ trợ liên hệ, phản hồi từ khách hàng

---

## ⭐ Tính Năng Chính

### 🔐 Quản Lý Người Dùng & Xác Thực
- Đăng ký/Đăng nhập tài khoản khách hàng
- Xác thực đăng nhập cho admin/nhân viên
- Phân quyền: Admin, Nhân viên, Khách hàng
- Quản lý hồ sơ cá nhân
- khôi phục mật khẩu qua email

### 💊 Quản Lý Thuốc & Sản Phẩm
- **Thêm/Sửa/Xóa thuốc** (Admin)
- Phân loại thuốc theo danh mục
- Kiểm tra hạn sử dụng (tự động ẩn thuốc hết hạn)
- Xem chi tiết sản phẩm với hình ảnh
- Tìm kiếm & lọc thuốc theo danh mục
- Hỗ trợ autocomplete tìm kiếm

### 🏷️ Quản Lý Khuyến Mại & Sale
- Tạo chương trình khuyến mại
- Áp dụng giá sale cho sản phẩm
- Quản lý mã giảm giá cho đơn hàng
- Thống kê hiệu quả khuyến mại

### 🛒 Giỏ Hàng & Thanh Toán
- Thêm/xóa sản phẩm vào giỏ hàng
- Cập nhật số lượng sản phẩm
- Tính toán tổng tiền (áp dụng giảm giá, mã sale)
- Checkout và tạo đơn hàng
- Ghi nhớ địa chỉ giao hàng

### 📦 Quản Lý Đơn Hàng
- Tạo đơn hàng từ thanh toán giỏ hàng
- Cập nhật trạng thái đơn hàng: Chờ xử lý → Đang xử lý → Hoàn tất
- Cập nhật trạng thái thanh toán: Chưa thanh toán → Đã thanh toán
- Xem lịch sử đơn hàng (khách hàng)
- Quản lý tất cả đơn hàng (admin/nhân viên)
- Xuất báo cáo đơn hàng (PDF)

### 📰 Quản Lý Tin Tức & Banner
- Tạo/Sửa/Xóa bài viết tin tức
- Upload hình ảnh cho bài viết
- Quản lý banner trang chủ
- Chia sẻ bài viết trên mạng xã hội

### 📞 Quản Lý Liên Hệ & Phản Hồi
- Form liên hệ từ khách hàng
- Quản lý các yêu cầu liên hệ
- Gửi email phản hồi cho khách hàng

### 📊 Dashboard & Thống Kê
- Thống kê doanh thu theo ngày/tháng/năm
- Thống kê sản phẩm bán chạy
- Thống kê số lượng người dùng
- Biểu đồ thống kê (nếu có)

---

## 🏗️ Kiến Trúc Hệ Thống

Dự án sử dụng mô hình **MVC (Model-View-Controller)**:

```
┌─────────────────────────────────────────────────────┐
│                  NGƯỜI DÙNG                         │
│         (Admin / Nhân Viên / Khách Hàng)            │
└──────────────┬──────────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────────┐
│              LAYER: VIEW (Giao Diện)                │
│  - HTML, CSS, JavaScript                            │
│  - Templates từ thư mục views/                      │
└──────────────┬──────────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────────┐
│            LAYER: CONTROLLER (Bộ Điều Khiển)       │
│  - ThuocController, DonHangController, ...          │
│  - Xử lý logic business, nhận request từ user       │
└──────────────┬──────────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────────┐
│            LAYER: MODEL (Dữ Liệu & Logic)          │
│  - ThuocModel, DonHangModel, ...                    │
│  - Tương tác với Database                          │
└──────────────┬──────────────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────────────┐
│              DATABASE: MySQL                        │
│         quan_ly_ban_thuoc.sql                       │
└─────────────────────────────────────────────────────┘
```

### Các Thành Phần Cốt Lõi:

#### **Core Classes** (`app/core/`)
- **Database.php**: Quản lý kết nối MySQL (Singleton Pattern)
- **Model.php**: Lớp cơ sở cho tất cả Models
- **Auth.php**: Xác thực & quản lý phiên làm việc
- **Session.php**: Quản lý session người dùng

#### **Models** (`app/models/`)
- **ThuocModel**: Quản lý thuốc/sản phẩm
- **DonHangModel**: Quản lý đơn hàng
- **NguoiDungModel**: Quản lý người dùng
- **GioHangModel**: Quản lý giỏ hàng
- **DanhMucModel**: Quản lý danh mục sản phẩm
- **SaleModel**: Quản lý khuyến mại/giảm giá
- **BannerModel**: Quản lý banner trang chủ
- **TinTucModel**: Quản lý tin tức
- **AdminModel**: Quản lý người dùng (Admin)

#### **Controllers** (`app/controllers/`)
- **ThuocController**: Xử lý thuốc (khách hàng)
- **DonHangController**: Xử lý đơn hàng (khách hàng)
- **AdminThuocController**: Quản lý thuốc (admin)
- **AdminDonHangController**: Quản lý đơn hàng (admin)
- **AdminUserController**: Quản lý người dùng (admin)
- **AuthController**: Xử lý đăng nhập/đăng ký
- **GioHangController**: Xử lý giỏ hàng
- **CheckoutController**: Xử lý thanh toán
- **TinTucController**: Xử lý tin tức
- **AccountController**: Quản lý hồ sơ cá nhân
- **LienHeController**: Xử lý liên hệ
- **ExportController**: Xuất báo cáo

#### **Views** (`app/views/`)
- **trangchu/**: Trang chủ & hiển thị sản phẩm
- **admin/**: Giao diện quản trị
- **auth/**: Giao diện đăng nhập/đăng ký
- **account/**: Quản lý tài khoản
- **giohang/**: Giỏ hàng
- **checkout/**: Thanh toán
- **donhang/**: Lịch sử đơn hàng
- **thuoc/**: Chi tiết sản phẩm
- **tintuc/**: Tin tức
- **lienhe.php**: Liên hệ
- **gioithieu.php**: Giới thiệu

---

## 📁 Cấu Trúc Dự Án

```
DC22V7N553/
│
├── index.php                          # File entry point (Router chính)
├── config.php                         # File cấu hình cơ sở dữ liệu & BASE_URL
├── composer.json                      # Dependencies (PHPMailer)
│
├── app/
│   ├── core/                          # Lớp cơ sở
│   │   ├── Database.php              # Kết nối MySQL (Singleton)
│   │   ├── Model.php                 # Lớp cơ sở cho Models
│   │   ├── Auth.php                  # Xác thực người dùng
│   │   └── Session.php               # Quản lý session
│   │
│   ├── models/                        # Tầng Model - Tương tác Database
│   │   ├── ThuocModel.php
│   │   ├── DonHangModel.php
│   │   ├── NguoiDungModel.php
│   │   ├── GioHangModel.php
│   │   ├── DanhMucModel.php
│   │   ├── SaleModel.php
│   │   ├── BannerModel.php
│   │   ├── TinTucModel.php
│   │   └── AdminModel.php
│   │
│   ├── controllers/                   # Tầng Controller - Xử lý Logic
│   │   ├── ThuocController.php
│   │   ├── DonHangController.php
│   │   ├── AdminThuocController.php
│   │   ├── AdminDonHangController.php
│   │   ├── AdminUserController.php
│   │   ├── AuthController.php
│   │   ├── GioHangController.php
│   │   ├── CheckoutController.php
│   │   ├── TinTucController.php
│   │   ├── AccountController.php
│   │   ├── LienHeController.php
│   │   ├── ExportController.php
│   │   ├── AdminBannerController.php
│   │   ├── AdminDanhMucController.php
│   │   ├── AdminSaleController.php
│   │   ├── AdminTinTucController.php
│   │   └── TrangChuController.php
│   │
│   └── views/                         # Tầng View - Giao diện HTML
│       ├── trangchu/                 # Trang chủ & danh sách sản phẩm
│       ├── admin/                    # Dashboard quản trị
│       ├── auth/                     # Đăng nhập/Đăng ký
│       ├── account/                  # Quản lý tài khoản cá nhân
│       ├── giohang/                  # Giỏ hàng
│       ├── checkout/                 # Thanh toán
│       ├── donhang/                  # Lịch sử đơn hàng
│       ├── thuoc/                    # Chi tiết sản phẩm
│       ├── tintuc/                   # Tin tức
│       ├── nhanvien/                 # Dashboard nhân viên
│       ├── gioithieu.php             # Trang giới thiệu
│       └── lienhe.php                # Trang liên hệ
│
├── image/                             # Hình ảnh tĩnh (4 loại: cam, cẩm, gangtay, ...)
│   ├── cam.avif
│   ├── cam2.avif
│   └── ...
│
├── uploads/                           # Thư mục upload ảnh từ admin
│   ├── banner/                       # Ảnh banner trang chủ
│   ├── thuoc/                        # Ảnh sản phẩm thuốc
│   └── tintuc/                       # Ảnh tin tức
│
├── vendor/                            # Thư viện bên ngoài (Composer)
│   ├── autoload.php
│   ├── phpmailer/                    # Gửi email
│   └── composer/
│
└── quan_ly_ban_thuoc.sql             # File backup/cấu trúc database
```

---

## 🛠️ Hướng Dẫn Cài Đặt

### 1️⃣ Yêu Cầu Hệ Thống

- **PHP**: Phiên bản 7.4 hoặc cao hơn
- **MySQL**: Phiên bản 5.7 hoặc cao hơn
- **Web Server**: Apache hoặc Nginx
- **Composer**: Để quản lý dependencies (PHPMailer)

### 2️⃣ Bước Cài Đặt

#### **Bước 1: Clone hoặc Download Dự Án**
```bash
# Nếu sử dụng Git
git clone <repository-url>

# Hoặc download file .zip và giải nén
unzip DC22V7N553.zip
cd DC22V7N553
```

#### **Bước 2: Cài Đặt Dependencies**
```bash
# Cài đặt Composer (nếu chưa có)
# Download từ https://getcomposer.org/

# Cài PHPMailer
composer install
```

#### **Bước 3: Cấu Hình Database**

1. **Tạo cơ sở dữ liệu:**
```sql
-- Mở phpMyAdmin hoặc MySQL Command Line
CREATE DATABASE quan_ly_ban_thuoc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quan_ly_ban_thuoc;
```

2. **Import dữ liệu từ file SQL:**
```bash
# Thông qua phpMyAdmin: Import > quan_ly_ban_thuoc.sql

# Hoặc thông qua command line:
mysql -u root -padmin quan_ly_ban_thuoc < quan_ly_ban_thuoc.sql
```

#### **Bước 4: Cấu Hình File config.php**

Chỉnh sửa file `config.php`:

```php
// Cấu hình Database
define('DB_HOST', 'localhost');      // Địa chỉ server MySQL
define('DB_NAME', 'quan_ly_ban_thuoc'); // Tên database
define('DB_USER', 'root');           // Username MySQL
define('DB_PASS', 'admin');          // Password MySQL

// Cấu hình BASE_URL (tự động phát hiện, có thể custom)
// Ví dụ: 'http://localhost:8000/' hoặc 'https://yourdomain.com/'

// Cấu hình Email (SMTP) - nếu cần gửi email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_PORT', 587);
define('FROM_EMAIL', 'your-email@gmail.com');
```

#### **Bước 5: Chạy Ứng Dụng**

```bash
# Sử dụng PHP Built-in Server (Development)
php -S localhost:8000

# Hoặc cấu hình Virtual Host trên Apache/Nginx

# Truy cập: http://localhost:8000
```

### 3️⃣ Tài Khoản Mặc Định

Sau khi import database, bạn sẽ có các tài khoản:

| Email | Mật Khẩu | Vai Trò |
|-------|----------|--------|
| admin@mail.com | admin123 | Admin (Quản Trị Viên) |
| nhanvien@mail.com | nhanvien123 | Nhân Viên |
| khach@mail.com | khach123 | Khách Hàng |

**⚠️ Lưu Ý:** Đổi mật khẩu ngay sau lần đăng nhập đầu tiên!

---

## 📖 Hướng Dẫn Sử Dụng

### 👤 Cho Khách Hàng

#### **Đăng Ký / Đăng Nhập**
1. Vào trang chủ → Nút "Đăng Nhập"
2. Chọn "Đăng Ký" để tạo tài khoản mới
3. Điền email, mật khẩu, họ tên
4. Nhập mã xác thực gửi về email (nếu có)
5. Đăng nhập thành công

#### **Mua Hàng - Quy Trình 4 Bước**

**Bước 1: Duyệt & Tìm Kiếm Thuốc**
- Vào "Tất Cả Sản Phẩm" hoặc lọc theo danh mục
- Tìm kiếm thuốc cần mua (có autocomplete)
- Xem chi tiết sản phẩm (hình ảnh, mô tả, giá)

**Bước 2: Thêm Vào Giỏ Hàng**
- Chọn số lượng cần mua
- Nhấn "Thêm Vào Giỏ Hàng"
- Tiếp tục mua hàng hoặc tới bước 3

**Bước 3: Thanh Toán (Checkout)**
- Vào biểu tượng 🛒 hoặc mục "Giỏ Hàng"
- Kiểm tra sản phẩm, số lượng, giá
- Nhập/Chọn địa chỉ giao hàng
- Nhập mã giảm giá (nếu có)
- Xem tổng tiền cần thanh toán
- Nhấn "Thanh Toán"

**Bước 4: Hoàn Tất Đơn Hàng**
- Chọn phương thức thanh toán
- Xác nhận tạo đơn hàng
- Nhận mã đơn hàng

#### **Quản Lý Tài Khoản & Đơn Hàng**
- Vào "Tài Khoản Của Tôi" → "Đơn Hàng"
- Xem lịch sử đơn hàng
- Xem chi tiết từng đơn:
  - Trạng thái đơn hàng
  - Trạng thái thanh toán
  - Danh sách sản phẩm
  - Ngày đặt & dự kiến giao
- Hủy đơn hàng (nếu chưa xử lý)
- Xuất hóa đơn (PDF)

#### **Liên Hệ & Phản Hồi**
- Vào "Liên Hệ" → Điền form
- Gửi câu hỏi, yêu cầu, góp ý
- Xem tin tức, khuyến mại từ cửa hàng

---

### 👨‍💼 Cho Admin / Quản Trị Viên

**Truy cập:** `http://localhost/admin/dashboard`  
Hoặc từ menu → "Quản Trị"

#### **1. Quản Lý Sản Phẩm (Thuốc)**
- **Danh sách thuốc:** Xem tất cả sản phẩm
  - Tìm kiếm, lọc theo danh mục
  - Xem hạn sử dụng
  - Sắp xếp theo ngày tạo, giá tiền
  
- **Thêm thuốc mới:**
  1. Vào "Thêm Sản Phẩm"
  2. Nhập tên thuốc, mô tả, giá
  3. Chọn danh mục
  4. Nhập hạn sử dụng, số lô
  5. Upload ảnh sản phẩm
  6. Nhấn "Lưu"
  
- **Sửa thuốc:**
  1. Chọn thuốc từ danh sách
  2. Nhấn "Sửa"
  3. Chỉnh sửa thông tin
  4. Nhấn "Cập Nhật"
  
- **Xóa thuốc:**
  1. Chọn thuốc
  2. Nhấn "Xóa"
  3. Xác nhận xóa

#### **2. Quản Lý Danh Mục**
- Thêm danh mục (loại thuốc): Mỏ, Cảm, Dạ Dày, ...
- Sửa/Xóa danh mục
- Gắn thuốc vào danh mục

#### **3. Quản Lý Đơn Hàng**
- **Danh sách đơn hàng:**
  - Tìm kiếm: Mã đơn, tên khách, email, điều chỉ giao
  - Lọc theo trạng thái: Chờ xử lý, Đang xử lý, Hoàn tất
  - Xem chi tiết từng đơn
  
- **Cập nhật trạng thái:**
  - Chờ xử lý → Đang xử lý
  - Đang xử lý → Hoàn tát
  - Áp dụng cho tất cả hoặc từng đơn
  
- **Cập nhật thanh toán:**
  - Chưa thanh toán → Đã thanh toán
  - Ghi chú lý do (số tham chiếu, ngân hàng, ...)
  
- **Xuất báo cáo:**
  - Chọn khoảng ngày
  - Xuất danh sách đơn hàng (PDF)

#### **4. Quản Lý Người Dùng**
- **Danh sách người dùng:**
  - Tìm kiếm theo email, họ tên
  - Lọc theo vai trò: Admin, Nhân Viên, Khách Hàng
  
- **Thêm người dùng:**
  1. Vào "Thêm Người Dùng"
  2. Nhập email, mật khẩu, họ tên
  3. Chọn vai trò
  4. Nhấn "Tạo"
  
- **Sửa/Xóa người dùng:**
  1. Chọn người dùng
  2. Cập nhật thông tin hoặc xóa
  
- **Kích hoạt/Vô hiệu hóa:** Block người dùng không hợp lệ

#### **5. Quản Lý Khuyến Mại & Sale**
- **Tạo chương trình sale:**
  - Chọn sản phẩm được sale
  - Nhập giá sale
  - Đặt ngày bắt đầu/kết thúc
  - Áp dụng
  
- **Quản lý mã giảm giá:**
  - Tạo mã (ví dụ: GIAM10)
  - Nhập % giảm hoặc số tiền giảm
  - Đặt điều kiện (tối thiểu, số lần dùng)
  - Kích hoạt/Vô hiệu hóa

#### **6. Quản Lý Banner & Tin Tức**
- **Banner trang chủ:**
  - Upload ảnh banner
  - Quản lý thứ tự hiển thị
  - Xóa banner cũ
  
- **Tin tức/Blog:**
  - Tạo bài viết mới
  - Nhập tiêu đề, nội dung, ảnh
  - Xuất bản/Nháp
  - Sửa/Xóa bài viết

#### **7. Quản Lý Liên Hệ**
- Xem danh sách liên hệ từ khách hàng
- Đánh dấu đã trả lời
- Gửi email phản hồi

#### **8. Dashboard & Thống Kê**
- **Thống kê doanh thu:**
  - Theo ngày, tháng, năm
  - So sánh năm trước
  
- **Thống kê sản phẩm:**
  - Sản phẩm bán chạy
  - Sản phẩm sắp hết
  - Sản phẩm hết hạn
  
- **Thống kê người dùng:**
  - Tổng số người dùng
  - Người dùng mới trong tháng
  
- **Biểu đồ:** Doanh thu, đơn hàng, ...

---

### 👨‍💻 Cho Developer / Lập Trình Viên

#### **Cấu Trúc Routing**

File `index.php` xử lý routing cơ bản:

```php
// URL: http://localhost/?action=thuoc&page=list
// ?action=thuoc => Gọi ThuocController
// ?page=list => Gọi method list()

// Ví dụ:
// http://localhost/?action=auth&page=login
//   => AuthController->login()
// http://localhost/?action=donhang&page=list
//   => DonHangController->list()
```

#### **Sơ Đồ Luồng Request**

```
Request → index.php → Router Logic → Lấy action & page
         → Load Controller Class
         → Gọi method tương ứng
         → Controller gọi Model
         → Model query Database
         → Trả kết quả về Controller
         → Controller render View
         → Trả HTML về Client
```

#### **Ví Dụ: Thêm Thuốc Mới**

1. **View:** `app/views/admin/thuoc/them_thuoc.php`
   - Form HTML với các trường: tên, giá, danh mục, hạn sử dụng, ảnh
   - Submit tới: `?action=admin_thuoc&page=them`

2. **Controller:** `app/controllers/AdminThuocController.php`
   ```php
   public function them() {
       if ($_SERVER['REQUEST_METHOD'] === 'POST') {
           // Lấy dữ liệu từ form
           $tenThuoc = $_POST['ten_thuoc'] ?? '';
           $gia = $_POST['gia'] ?? '';
           // ...
           
           // Gọi Model
           $result = $this->thuocModel->them($data);
           
           if ($result) {
               header('Location: ?action=admin_thuoc&page=list&msg=success');
           }
       }
   }
   ```

3. **Model:** `app/models/ThuocModel.php`
   ```php
   public function them($data) {
       $sql = "INSERT INTO thuoc (ten_thuoc, gia, ma_danh_muc, ...) 
               VALUES (:ten_thuoc, :gia, :ma_danh_muc, ...)";
       $stmt = $this->db->prepare($sql);
       return $stmt->execute($data);
   }
   ```

#### **Sử Dụng Database Class**

```php
// Lấy instance Database
$db = Database::getInstance();
$connection = $db->getConnection(); // Lấy PDO object

// Thực thi query
$sql = "SELECT * FROM thuoc WHERE ma_danh_muc = :mdc";
$stmt = $connection->prepare($sql);
$stmt->execute([':mdc' => 1]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

#### **Sử Dụng Model**

```php
// Model cơ sở cung cấp các method tiện ích
$thuocModel = new ThuocModel();

// Lấy dữ liệu
$thuoc = $thuocModel->layTatCa();

// Thêm dữ liệu
$thuocModel->them($data);

// Sửa dữ liệu
$thuocModel->sua($id, $data);

// Xóa dữ liệu
$thuocModel->xoa($id);
```

#### **Xác Thực Người Dùng**

```php
// Kiểm tra người dùng đã đăng nhập
if (!Auth::isLoggedIn()) {
    header('Location: ?action=auth&page=login');
    exit;
}

// Lấy thông tin người dùng
$userId = Auth::getUserId();
$userEmail = Auth::getUserEmail();
$userRole = Auth::getUserRole(); // QUAN_TRI, NHAN_VIEN, KHACH_HANG

// Kiểm tra quyền Admin
if (!Auth::isAdmin()) {
    header('Location: ?action=trangchu&page=index');
    exit;
}
```

#### **Session & Cookie**

```php
// Lưu dữ liệu vào session
Session::set('key', 'value');

// Lấy dữ liệu từ session
$value = Session::get('key');

// Xóa dữ liệu session
Session::delete('key');

// Xóa tất cả session
Session::destroy();
```

---

## 💻 Công Nghệ Sử Dụng

### **Backend**
- **PHP 7.4+**: Ngôn ngữ lập trình backend
- **MySQL 5.7+**: Cơ sở dữ liệu
- **PDO**: PHP Data Objects - Kết nối an toàn với database (Prepared Statements)
- **MVC Pattern**: Kiến trúc ứng dụng
- **Composer**: Package Manager cho PHP
- **PHPMailer**: Gửi email SMTP

### **Frontend**
- **HTML5**: Cấu trúc trang web
- **CSS3**: Styling (Bootstrap hoặc custom CSS)
- **JavaScript**: Tương tác động
- **AJAX**: Yêu cầu không làm mới trang
- **jQuery**: (Nếu sử dụng)

### **Tools & Libraries**
- **Bootstrap**: Framework CSS (có thể)
- **Giao diện Responsive**: Mobile-friendly
- **File Upload**: Upload ảnh sản phẩm, banner
- **Generate PDF**: Xuất báo cáo đơn hàng

### **Hosting Requirements**
- **Web Server**: Apache hoặc Nginx
- **PHP Extensions**: PDO, MySQL, OpenSSL
- **File Permissions**: uploads/ cần ghi (777 hoặc 755)

---

## 🗄️ Cơ Sở Dữ Liệu

### **Cấu Trúc Chính**

File `quan_ly_ban_thuoc.sql` chứa 10+ bảng:

| Bảng | Mô Tả |
|------|-------|
| `nguoi_dung` | Người dùng (admin, nhân viên, khách hàng) |
| `vai_tro` | Vai trò (Quản Trị, Nhân Viên, Khách Hàng) |
| `thuoc` | Sản phẩm thuốc/dược phẩm |
| `danh_muc` | Danh mục sản phẩm (Mỏ, Cảm, ...) |
| `don_hang` | Đơn hàng (Order) |
| `chi_tiet_don_hang` | Chi tiết sản phẩm trong đơn hàng |
| `gio_hang` | Giỏ hàng tạm thời |
| `banner` | Banner trang chủ |
| `tin_tuc` | Bài viết tin tức/blog |
| `khuyen_mai` / `sale` | Chương trình giảm giá |
| `lien_he` | Form liên hệ từ khách hàng |

### **Quan Hệ (Relationship)**

```
nguoi_dung ──┬──→ don_hang (khách hàng)
             ├──→ chi_tiet_don_hang
             └──→ gio_hang

thuoc ──────→ chi_tiet_don_hang
             ├──→ danh_muc
             └──→ sale/khuyen_mai

don_hang ────→ chi_tiet_don_hang
```

### **Ví Dụ: Lấy Đơn Hàng Của Khách**

```sql
SELECT 
    d.ma_don_hang,
    d.ngay_lap,
    d.trang_thai,
    d.tong_tien,
    n.ho_ten,
    n.email
FROM don_hang d
JOIN nguoi_dung n ON d.ma_khach_hang = n.ma_nguoi_dung
WHERE d.ma_khach_hang = 1
ORDER BY d.ngay_lap DESC;
```

---

## 🔐 Bảo Mật

### **Biện Pháp Bảo Mật Đã Áp Dụng**

✅ **SQL Injection Prevention:**
- Sử dụng Prepared Statements (PDO)
- Bind parameters thay vì concatenate string

✅ **XSS Prevention:**
- Sanitize input (strip_tags, htmlspec­chars)
- Validate dữ liệu trước khi lưu

✅ **CSRF Protection:**
- Session-based authentication
- Kiểm tra origin header

✅ **Password Security:**
- Hash mật khẩu (bcrypt hoặc password_hash)
- Không lưu mật khẩu plain text

✅ **File Upload Security:**
- Kiểm tra MIME type
- Rename file upload (tránh overwrite)
- Lưu ngoài document root (nếu có thể)

✅ **Session Management:**
- Session timeout
- Invalidate session khi logout
- Secure session cookie

### **Recommendations:**

⚠️ **TODO - Cần Cải Thiện:**
- [] Implement rate limiting (chặn brute force)
- [] Add HTTPS encryption
- [] Two-factor authentication (2FA)
- [] API authentication (if REST API added)
- [] Regular security audit
- [] Update dependencies regularly

---

## 🚀 Tính Năng Có Thể Mở Rộng

1. **Mobile App**: React Native hoặc Flutter
2. **Payment Gateway**: Stripe, VNPay, Momo
3. **Inventory Management**: Tự động quản lý kho
4. **Email Notification**: Gửi thông báo tự động
5. **SMS**: Gửi SMS cho khách (OTP, shipping)
6. **Analytics**: Google Analytics, tracking
7. **Multi-language**: Hỗ trợ tiếng Anh, tiếng Trung
8. **API REST**: Xây dựng REST API cho mobile
9. **Real-time Chat**: Support nhân viên
10. **Subscription/Membership**: Gói đăng ký khách hàng

---

## 📞 Hỗ Trợ & Liên Hệ

**Tác giả:** Lý Minh Thuần  
**Mã Sinh Viên:** DC22V7N553  
**Email:** [your-email@example.com]  
**Phone:** [your-phone-number]  

---

## 📄 License

Dự án này được tạo với mục đích giáo dục - Bài Niên Luận Cơ Sở.  
Không được phép sử dụng thương mại mà không có sự cho phép.

---

## 🙏 Lời Cảm Ơn

Cảm ơn:
- ❤️ Nhà Thuốc **Pharmacity** - Nguồn cảm hứi
- 👨‍🏫 Thầy/Cô hướng dẫn
- 👥 Cộng đồng lập trình viên PHP
- 📚 Các tài liệu open source

---

**Chúc bạn sử dụng ứng dụng vui vẻ! 🎉**

Nếu có bất kỳ vấn đề hoặc câu hỏi, hãy liên hệ qua form "Liên Hệ" trên website!
