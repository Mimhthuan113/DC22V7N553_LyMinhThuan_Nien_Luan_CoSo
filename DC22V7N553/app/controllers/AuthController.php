<?php
/**
 * app/controllers/AuthController.php - Controller xử lý xác thực người dùng
 * 
 * Controller này xử lý tất cả các yêu cầu liên quan đến đăng nhập, đăng ký, đăng xuất:
 * - Đăng nhập (login)
 * - Đăng ký (register)
 * - Đăng xuất (logout)
 * - Quên mật khẩu (forgot password)
 * - Đặt lại mật khẩu (reset password)
 * 
 * Sử dụng PHPMailer để gửi email đặt lại mật khẩu
 */

// Đảm bảo config.php được load (nếu chưa được load từ index.php)
// Cần config.php để lấy thông tin SMTP
if (!defined('SMTP_HOST')) {
    require_once __DIR__ . '/../../config.php';
}

// Nạp PHPMailer (thư viện gửi email)
require_once __DIR__ . '/../../vendor/autoload.php';

// Nạp các Model và Core cần thiết
require_once __DIR__ . '/../models/NguoiDungModel.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Session.php';

// Load PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class AuthController
{
    /**
     * Model quản lý người dùng
     */
    private $nguoiDungModel;

    /**
     * Constructor: Khởi tạo Model
     */
    public function __construct()
    {
        $this->nguoiDungModel = new NguoiDungModel();
    }

    /**
     * Hiển thị form đăng nhập
     * 
     * Route: index.php?page=login
     */
    public function showLogin()
    {
        // Hiển thị view form đăng nhập
        require __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Xử lý đăng nhập
     * Kiểm tra email và mật khẩu, lưu thông tin vào session
     * Chuyển hướng theo vai trò: Admin -> trang admin, Nhân viên -> trang nhân viên, Khách hàng -> trang chủ
     * 
     * Route: index.php?action=auth_login
     * Method: POST
     * 
     * POST data:
     *   - username: Email hoặc username
     *   - password: Mật khẩu
     */
    public function login()
    {
        // Chỉ chấp nhận request POST (bảo mật)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=login');
            exit;
        }

        // Lấy email và mật khẩu từ form POST
        $email = $_POST['username'] ?? '';      // Lấy từ trường "username" (có thể là email)
        $password = $_POST['password'] ?? '';    // Mật khẩu

        // Kiểm tra dữ liệu đầu vào
        if (empty($email) || empty($password)) {
            // Lưu thông báo lỗi vào flash message
            Session::setFlash('error', 'Vui lòng nhập đầy đủ thông tin');
            // Chuyển về trang đăng nhập
            header('Location: index.php?page=login');
            exit;
        }

        // Tìm người dùng theo email (không phân biệt hoa thường)
        // Nếu không tìm thấy, coi như email hoặc mật khẩu sai (bảo mật)
        $user = $this->nguoiDungModel->layTheoEmail($email);

        // Nếu không tìm thấy người dùng, trả về lỗi
        if (!$user) {
            Session::setFlash('error', 'Email hoặc mật khẩu không đúng');
            header('Location: index.php?page=login');
            exit;
        }

        // Kiểm tra trạng thái tài khoản trước khi verify password
        // trang_thai = 0: Tài khoản bị khóa
        if ($user['trang_thai'] == 0) {
            Session::setFlash('error', 'Tài khoản của bạn đã bị khóa');
            header('Location: index.php?page=login');
            exit;
        }

        // Verify mật khẩu bằng password_verify()
        // So sánh mật khẩu người dùng nhập với hash trong database
        if (!password_verify($password, $user['mat_khau'])) {
            // Mật khẩu không đúng
            Session::setFlash('error', 'Email hoặc mật khẩu không đúng');
            header('Location: index.php?page=login');
            exit;
        }

        // Đăng nhập thành công: Lưu thông tin người dùng vào session
        Auth::login($user);
        
        // Lưu thông báo thành công vào flash message
        Session::setFlash('success', 'Đăng nhập thành công!');

        // Chuyển hướng theo vai trò của người dùng
        if ($user['ten_vai_tro'] === 'QUAN_TRI') {
            // Admin -> chuyển đến trang quản trị
            header('Location: index.php?page=admin');
        } elseif ($user['ten_vai_tro'] === 'NHAN_VIEN') {
            // Nhân viên -> chuyển đến trang nhân viên
            header('Location: index.php?page=nhanvien');
        } else {
            // Khách hàng -> chuyển về trang chủ
            header('Location: index.php?page=trangchu');
        }
        exit;
    }

    /**
     * Hiển thị form đăng ký
     * 
     * Route: index.php?page=register
     */
    public function showRegister()
    {
        // Hiển thị view form đăng ký
        require __DIR__ . '/../views/auth/register.php';
    }

    /**
     * Xử lý đăng ký tài khoản mới
     * Validate dữ liệu đầu vào, tạo tài khoản mới với vai trò KHACH_HANG
     * 
     * Route: index.php?action=auth_register
     * Method: POST
     * 
     * POST data:
     *   - ho_ten: Họ tên
     *   - email: Email
     *   - password: Mật khẩu
     *   - confirm_password: Xác nhận mật khẩu
     *   - so_dien_thoai: Số điện thoại (tùy chọn)
     *   - dia_chi: Địa chỉ (tùy chọn)
     */
    public function register()
    {
        // Chỉ chấp nhận request POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=register');
            exit;
        }

        // Lấy dữ liệu từ form POST (loại bỏ khoảng trắng đầu/cuối)
        $ho_ten = trim($_POST['ho_ten'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
        $dia_chi = trim($_POST['dia_chi'] ?? '');

        // Mảng chứa các lỗi validation
        $errors = [];

        // Validate họ tên
        if (empty($ho_ten)) {
            $errors[] = 'Vui lòng nhập họ tên';
        } elseif (strlen($ho_ten) < 2) {
            $errors[] = 'Họ tên phải có ít nhất 2 ký tự';
        } elseif (strlen($ho_ten) > 100) {
            $errors[] = 'Họ tên không được vượt quá 100 ký tự';
        }

        // Validate email
        if (empty($email)) {
            $errors[] = 'Vui lòng nhập email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Kiểm tra định dạng email
            $errors[] = 'Email không hợp lệ';
        } elseif (strlen($email) > 100) {
            $errors[] = 'Email không được vượt quá 100 ký tự';
        } elseif ($this->nguoiDungModel->kiemTraEmailTonTai($email)) {
            // Kiểm tra email đã tồn tại chưa
            $errors[] = 'Email này đã được sử dụng';
        }

        // Validate mật khẩu
        if (empty($password)) {
            $errors[] = 'Vui lòng nhập mật khẩu';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
        } elseif (strlen($password) > 255) {
            $errors[] = 'Mật khẩu quá dài';
        }

        // Validate xác nhận mật khẩu
        if (empty($confirm_password)) {
            $errors[] = 'Vui lòng xác nhận mật khẩu';
        } elseif ($password !== $confirm_password) {
            // Kiểm tra mật khẩu xác nhận có khớp với mật khẩu không
            $errors[] = 'Mật khẩu xác nhận không khớp';
        }

        // Validation số điện thoại (nếu có)
        if (!empty($so_dien_thoai)) {
            // Loại bỏ khoảng trắng và ký tự đặc biệt, chỉ giữ lại số
            $so_dien_thoai = preg_replace('/[^0-9]/', '', $so_dien_thoai);
            
            // Kiểm tra độ dài số điện thoại (10-11 chữ số cho Việt Nam)
            if (strlen($so_dien_thoai) < 10 || strlen($so_dien_thoai) > 11) {
                $errors[] = 'Số điện thoại không hợp lệ (10-11 chữ số)';
            }
        }

        // Validate địa chỉ (nếu có)
        if (!empty($dia_chi) && strlen($dia_chi) > 255) {
            $errors[] = 'Địa chỉ không được vượt quá 255 ký tự';
        }

        // Nếu có lỗi validation, lưu lại dữ liệu form để hiển thị lại
        // (Để người dùng không phải nhập lại từ đầu)
        if (!empty($errors)) {
            // Lưu danh sách lỗi vào flash message
            Session::setFlash('errors', $errors);
            
            // Lưu dữ liệu form (không lưu mật khẩu vì lý do bảo mật)
            Session::setFlash('form_data', [
                'ho_ten' => $ho_ten,
                'email' => $email,
                'so_dien_thoai' => $so_dien_thoai,
                'dia_chi' => $dia_chi
            ]);
            
            // Chuyển về trang đăng ký để hiển thị lỗi
            header('Location: index.php?page=register');
            exit;
        }

        try {
            // Tạo tài khoản mới (tự động gán vai trò KHACH_HANG)
            $ma_nguoi_dung = $this->nguoiDungModel->dangKy([
                'ho_ten' => $ho_ten,
                'email' => $email,
                'mat_khau' => $password,  // Mật khẩu sẽ được hash trong Model
                'so_dien_thoai' => !empty($so_dien_thoai) ? $so_dien_thoai : null,  // SĐT (tùy chọn)
                'dia_chi' => !empty($dia_chi) ? $dia_chi : null                     // Địa chỉ (tùy chọn)
            ]);

            // Nếu tạo tài khoản thành công
            if ($ma_nguoi_dung) {
                // Lưu thông báo thành công
                Session::setFlash('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
                // Chuyển về trang đăng nhập
                header('Location: index.php?page=login');
                exit;
            } else {
                // Nếu không tạo được tài khoản, ném exception
                throw new Exception('Không thể tạo tài khoản. Vui lòng thử lại.');
            }
        } catch (PDOException $e) {
            // Xử lý lỗi database cụ thể
            $errorMessage = 'Có lỗi xảy ra khi đăng ký.';
            
            // Nếu lỗi là duplicate entry (email đã tồn tại), hiển thị thông báo cụ thể
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $errorMessage = 'Email này đã được sử dụng.';
            }
            
            // Lưu lỗi và dữ liệu form
            Session::setFlash('error', $errorMessage);
            Session::setFlash('form_data', [
                'ho_ten' => $ho_ten,
                'email' => $email,
                'so_dien_thoai' => $so_dien_thoai,
                'dia_chi' => $dia_chi
            ]);
            
            // Chuyển về trang đăng ký
            header('Location: index.php?page=register');
            exit;
        } catch (Exception $e) {
            // Xử lý lỗi khác
            Session::setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            Session::setFlash('form_data', [
                'ho_ten' => $ho_ten,
                'email' => $email,
                'so_dien_thoai' => $so_dien_thoai,
                'dia_chi' => $dia_chi
            ]);
            header('Location: index.php?page=register');
            exit;
        }
    }

    /**
     * Xử lý đăng xuất
     * Xóa tất cả thông tin người dùng khỏi session
     * 
     * Route: index.php?action=auth_logout
     */
    public function logout()
    {
        // Xóa thông tin người dùng khỏi session và hủy session
        Auth::logout();
        
        // Lưu thông báo thành công
        Session::setFlash('success', 'Đăng xuất thành công!');
        
        // Chuyển về trang chủ
        header('Location: index.php?page=trangchu');
        exit;
    }

    /**
     * Hiển thị form quên mật khẩu
     * 
     * Route: index.php?page=forgot
     */
    public function showForgot()
    {
        // Hiển thị view form quên mật khẩu
        require __DIR__ . '/../views/auth/forgot.php';
    }

    /**
     * Xử lý quên mật khẩu
     * Tạo token reset mật khẩu và gửi email chứa link đặt lại mật khẩu
     * Token có thời hạn 2 giờ
     * 
     * Route: index.php?action=auth_forgot
     * Method: POST
     * 
     * POST data:
     *   - email: Email cần đặt lại mật khẩu
     */
    public function forgot()
    {
        // Chỉ chấp nhận request POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=forgot');
            exit;
        }

        // Lấy email từ form POST
        $email = trim($_POST['email'] ?? '');

        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Email không hợp lệ');
            header('Location: index.php?page=forgot');
            exit;
        }

        // Tìm người dùng theo email
        $user = $this->nguoiDungModel->layTheoEmail($email);

        // Nếu không tìm thấy người dùng, vẫn hiển thị thông báo thành công
        // (Bảo mật: không cho biết email có tồn tại hay không)
        if (!$user) {
            Session::setFlash('success', 'Nếu email tồn tại, chúng tôi đã gửi link đặt lại mật khẩu.');
            header('Location: index.php?page=forgot');
            exit;
        }

        // Tạo token ngẫu nhiên (64 ký tự hex = 32 bytes)
        // bin2hex: Chuyển binary sang hex (an toàn hơn base64 cho URL)
        $token = bin2hex(random_bytes(32));
        
        // Thời hạn token: 2 giờ từ bây giờ
        $thoi_han = date('Y-m-d H:i:s', strtotime('+2 hours'));

        try {
            // Lưu token vào database
            $this->nguoiDungModel->taoTokenQuenMatKhau($user['ma_nguoi_dung'], $token, $thoi_han);

            // Tạo link reset mật khẩu
            // Tự động phát hiện BASE_URL từ config hoặc từ request hiện tại
            if (defined('BASE_URL') && !empty(BASE_URL)) {
                // Nếu đã có BASE_URL trong config, dùng nó
                $baseUrl = rtrim(BASE_URL, '/') . '/';
            } else {
                // Tự động phát hiện URL từ request hiện tại
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                
                // Lấy đường dẫn script từ server
                $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
                // Lấy thư mục chứa script
                $scriptDir = dirname($scriptName);
                // Chuyển dấu \ thành / (tương thích Windows)
                $scriptDir = str_replace('\\', '/', $scriptDir);
                // Nếu thư mục là . hoặc / thì coi như rỗng
                if ($scriptDir === '.' || $scriptDir === '/') {
                    $scriptDir = '';
                }
                
                // Tạo BASE_URL
                $baseUrl = $protocol . $host . $scriptDir . '/';
            }
            
            // Tạo link reset: BASE_URL + index.php?page=reset&token=...
            $reset_link = $baseUrl . "index.php?page=reset&token=" . $token;

            // Gửi email qua PHPMailer
            $mail = new PHPMailer(true);

            try {
                // Đảm bảo config.php được load
                $configPath = __DIR__ . '/../../config.php';
                if (file_exists($configPath)) {
                    require_once $configPath;
                }
                
                // Kiểm tra các constant SMTP đã được định nghĩa chưa
                if (!defined('SMTP_HOST') || !defined('SMTP_PORT') || !defined('SMTP_USER') || !defined('SMTP_PASS')) {
                    throw new Exception('Cấu hình SMTP chưa được thiết lập. Vui lòng kiểm tra file config.php');
                }
                
                // Cấu hình SMTP
                $mail->isSMTP();                                    // Sử dụng SMTP
                $mail->Host = SMTP_HOST;                            // Máy chủ SMTP (ví dụ: smtp.gmail.com)
                $mail->SMTPAuth = true;                             // Bật xác thực SMTP
                $mail->Username = SMTP_USER;                        // Email đăng nhập SMTP
                $mail->Password = SMTP_PASS;                         // Mật khẩu ứng dụng Gmail
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Mã hóa STARTTLS
                $mail->Port = SMTP_PORT;                             // Cổng SMTP (587 cho STARTTLS)
                $mail->CharSet = 'UTF-8';                            // Mã hóa UTF-8 (hỗ trợ tiếng Việt)

                // Người gửi và người nhận
                $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);          // Email và tên người gửi
                $mail->addAddress($email, $user['ho_ten']);         // Email và tên người nhận

                // Nội dung email (HTML)
                $mail->isHTML(true);
                $mail->Subject = 'Đặt lại mật khẩu - DC22V7N553';
                $mail->Body = '
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background: #007bdf; color: #fff; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px; }
                            .button { display: inline-block; padding: 12px 30px; background: #007bdf; color: #fff; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <div class="header">
                                <h2>DC22V7N553</h2>
                            </div>
                            <div class="content">
                                <p>Xin chào <strong>' . htmlspecialchars($user['ho_ten']) . '</strong>,</p>
                                <p>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản của mình.</p>
                                <p>Vui lòng nhấp vào nút bên dưới để đặt lại mật khẩu:</p>
                                <p style="text-align: center;">
                                    <a href="' . $reset_link . '" class="button">Đặt lại mật khẩu</a>
                                </p>
                                <p>Hoặc copy và dán link sau vào trình duyệt:</p>
                                <p style="word-break: break-all; background: #fff; padding: 10px; border-radius: 3px;">' . $reset_link . '</p>
                                <p><strong>Lưu ý:</strong> Link này chỉ có hiệu lực trong 2 giờ.</p>
                                <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
                            </div>
                            <div class="footer">
                                <p>© ' . date('Y') . ' DC22V7N553. Tất cả quyền được bảo lưu.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ';
                
                // Nội dung email dạng text thuần (cho email client không hỗ trợ HTML)
                $mail->AltBody = "Xin chào " . $user['ho_ten'] . ",\n\nBạn đã yêu cầu đặt lại mật khẩu. Vui lòng truy cập link sau:\n" . $reset_link . "\n\nLink này chỉ có hiệu lực trong 2 giờ.";

                // Gửi email
                $mail->send();

                // Lưu thông báo thành công
                Session::setFlash('success', 'Chúng tôi đã gửi link đặt lại mật khẩu đến email của bạn. Vui lòng kiểm tra hộp thư (có thể trong thư mục Spam).');
                header('Location: index.php?page=forgot');
                exit;
            } catch (PHPMailerException $e) {
                // Nếu gửi email thất bại, vẫn hiển thị link để test (trong môi trường development)
                Session::setFlash('error', 'Không thể gửi email. Lỗi: ' . $mail->ErrorInfo);
                Session::setFlash('reset_link', $reset_link);  // Lưu link để hiển thị (test)
                Session::setFlash('success', 'Link đặt lại mật khẩu đã được tạo (hiển thị bên dưới để test):');
                header('Location: index.php?page=forgot');
                exit;
            }
        } catch (Exception $e) {
            // Xử lý lỗi khác
            Session::setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            header('Location: index.php?page=forgot');
            exit;
        }
    }

    /**
     * Hiển thị form đặt lại mật khẩu
     * Kiểm tra token có hợp lệ và chưa hết hạn
     * 
     * Route: index.php?page=reset&token=...
     * 
     * @param string $token Token reset mật khẩu (từ $_GET)
     */
    public function showReset()
    {
        // Lấy token từ URL
        $token = $_GET['token'] ?? '';
        
        // Nếu không có token, chuyển về trang quên mật khẩu
        if (empty($token)) {
            Session::setFlash('error', 'Token không hợp lệ');
            header('Location: index.php?page=forgot');
            exit;
        }

        // Kiểm tra token có hợp lệ và chưa hết hạn không
        $tokenData = $this->nguoiDungModel->layTokenQuenMatKhau($token);
        
        // Nếu token không hợp lệ hoặc đã hết hạn
        if (!$tokenData) {
            Session::setFlash('error', 'Token không hợp lệ hoặc đã hết hạn');
            header('Location: index.php?page=forgot');
            exit;
        }

        // Hiển thị view form đặt lại mật khẩu
        // Truyền biến $token và $tokenData cho view
        require __DIR__ . '/../views/auth/reset.php';
    }

    /**
     * Xử lý đặt lại mật khẩu
     * Kiểm tra token, validate mật khẩu mới, cập nhật mật khẩu và đánh dấu token đã sử dụng
     * 
     * Route: index.php?action=auth_reset
     * Method: POST
     * 
     * POST data:
     *   - token: Token reset mật khẩu
     *   - password: Mật khẩu mới
     *   - confirm_password: Xác nhận mật khẩu mới
     */
    public function reset()
    {
        // Chỉ chấp nhận request POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=forgot');
            exit;
        }

        // Lấy dữ liệu từ form POST
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate dữ liệu đầu vào
        if (empty($token) || empty($password)) {
            Session::setFlash('error', 'Vui lòng nhập đầy đủ thông tin');
            header('Location: index.php?page=forgot');
            exit;
        }

        // Validate độ dài mật khẩu
        if (strlen($password) < 6) {
            Session::setFlash('error', 'Mật khẩu phải có ít nhất 6 ký tự');
            header('Location: index.php?page=reset&token=' . $token);
            exit;
        }

        // Validate mật khẩu xác nhận
        if ($password !== $confirm_password) {
            Session::setFlash('error', 'Mật khẩu xác nhận không khớp');
            header('Location: index.php?page=reset&token=' . $token);
            exit;
        }

        // Kiểm tra lại token (đảm bảo token vẫn hợp lệ)
        $tokenData = $this->nguoiDungModel->layTokenQuenMatKhau($token);
        
        // Nếu token không hợp lệ hoặc đã hết hạn
        if (!$tokenData) {
            Session::setFlash('error', 'Token không hợp lệ hoặc đã hết hạn');
            header('Location: index.php?page=forgot');
            exit;
        }

        try {
            // Đổi mật khẩu mới (mật khẩu sẽ được hash trong Model)
            $this->nguoiDungModel->doiMatKhau($tokenData['ma_nguoi_dung'], $password);
            
            // Đánh dấu token đã được sử dụng (đảm bảo token chỉ dùng 1 lần)
            $this->nguoiDungModel->danhDauTokenDaSuDung($token);

            // Lưu thông báo thành công
            Session::setFlash('success', 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập.');
            
            // Chuyển về trang đăng nhập
            header('Location: index.php?page=login');
            exit;
        } catch (Exception $e) {
            // Xử lý lỗi
            Session::setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            header('Location: index.php?page=reset&token=' . $token);
            exit;
        }
    }
}
