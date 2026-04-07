<?php
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class LienHeController
{
    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setFlash('error', 'Phương thức không hợp lệ');
            header('Location: index.php?page=trangchu');
            exit;
        }

        // Lấy dữ liệu từ form
        $ho_ten = trim($_POST['ho_ten'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
        $chu_de = trim($_POST['chu_de'] ?? '');
        $noi_dung = trim($_POST['noi_dung'] ?? '');

        // Validate
        $errors = [];
        
        if (empty($ho_ten)) {
            $errors[] = 'Vui lòng nhập họ và tên';
        } elseif (strlen($ho_ten) > 100) {
            $errors[] = 'Họ và tên không được vượt quá 100 ký tự';
        }

        if (empty($email)) {
            $errors[] = 'Vui lòng nhập email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        } elseif (strlen($email) > 100) {
            $errors[] = 'Email không được vượt quá 100 ký tự';
        }

        if (!empty($so_dien_thoai) && strlen($so_dien_thoai) > 20) {
            $errors[] = 'Số điện thoại không được vượt quá 20 ký tự';
        }

        if (empty($chu_de)) {
            $errors[] = 'Vui lòng chọn chủ đề';
        }

        if (empty($noi_dung)) {
            $errors[] = 'Vui lòng nhập nội dung tin nhắn';
        } elseif (strlen($noi_dung) > 2000) {
            $errors[] = 'Nội dung tin nhắn không được vượt quá 2000 ký tự';
        }

        if (!empty($errors)) {
            Session::setFlash('errors', $errors);
            Session::setFlash('form_data', [
                'ho_ten' => $ho_ten,
                'email' => $email,
                'so_dien_thoai' => $so_dien_thoai,
                'chu_de' => $chu_de,
                'noi_dung' => $noi_dung
            ]);
            header('Location: index.php?page=trangchu#lienhe');
            exit;
        }

        // Map chủ đề
        $chuDeMap = [
            'tu_van' => 'Tư vấn sản phẩm',
            'dat_hang' => 'Đặt hàng',
            'giao_hang' => 'Giao hàng',
            'doi_tra' => 'Đổi trả',
            'khac' => 'Khác'
        ];
        $chuDeText = $chuDeMap[$chu_de] ?? 'Khác';

        // Gửi email
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

            // Email nhận (từ config hoặc mặc định)
            $emailNhan = defined('SMTP_FROM') ? SMTP_FROM : 'lyminhthuan.dhbk@gmail.com';

            // Tạo PHPMailer instance
            $mail = new PHPMailer(true);

            // Cấu hình SMTP
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            $mail->CharSet = 'UTF-8';

            // Người gửi và người nhận
            $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            $mail->addAddress($emailNhan, 'DC22V7N553');
            $mail->addReplyTo($email, $ho_ten); // Reply to email của người gửi

            // Nội dung email
            $mail->isHTML(true);
            $mail->Subject = 'Liên hệ từ website - ' . $chuDeText . ' - DC22V7N553';
            
            $mail->Body = '
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(135deg, #023660 0%, #1956b2 100%); color: #fff; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none; }
                        .info-row { margin-bottom: 15px; }
                        .label { font-weight: bold; color: #1956b2; width: 120px; display: inline-block; }
                        .value { color: #333; }
                        .message-box { background: #fff; padding: 15px; border-left: 4px solid #1956b2; margin-top: 15px; }
                        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <h2 style="margin: 0;">Thông tin liên hệ từ website</h2>
                        </div>
                        <div class="content">
                            <div class="info-row">
                                <span class="label">Họ và tên:</span>
                                <span class="value">' . htmlspecialchars($ho_ten) . '</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Email:</span>
                                <span class="value">' . htmlspecialchars($email) . '</span>
                            </div>';
            
            if (!empty($so_dien_thoai)) {
                $mail->Body .= '
                            <div class="info-row">
                                <span class="label">Số điện thoại:</span>
                                <span class="value">' . htmlspecialchars($so_dien_thoai) . '</span>
                            </div>';
            }
            
            $mail->Body .= '
                            <div class="info-row">
                                <span class="label">Chủ đề:</span>
                                <span class="value">' . htmlspecialchars($chuDeText) . '</span>
                            </div>
                            <div class="message-box">
                                <strong>Nội dung tin nhắn:</strong>
                                <p style="margin-top: 10px; white-space: pre-wrap;">' . nl2br(htmlspecialchars($noi_dung)) . '</p>
                            </div>
                            <div class="footer">
                                <p>Email này được gửi tự động từ form liên hệ trên website DC22V7N553</p>
                                <p>Thời gian: ' . date('d/m/Y H:i:s') . '</p>
                            </div>
                        </div>
                    </div>
                </body>
                </html>
            ';

            $mail->AltBody = "Thông tin liên hệ từ website DC22V7N553\n\n" .
                           "Họ và tên: " . $ho_ten . "\n" .
                           "Email: " . $email . "\n" .
                           (!empty($so_dien_thoai) ? "Số điện thoại: " . $so_dien_thoai . "\n" : "") .
                           "Chủ đề: " . $chuDeText . "\n\n" .
                           "Nội dung tin nhắn:\n" . $noi_dung . "\n\n" .
                           "Thời gian: " . date('d/m/Y H:i:s');

            // Gửi email
            $mail->send();

            Session::setFlash('success', 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.');
            header('Location: index.php?page=trangchu#lienhe');
            exit;

        } catch (PHPMailerException $e) {
            Session::setFlash('error', 'Không thể gửi email. Vui lòng thử lại sau hoặc liên hệ trực tiếp qua hotline.');
            Session::setFlash('form_data', [
                'ho_ten' => $ho_ten,
                'email' => $email,
                'so_dien_thoai' => $so_dien_thoai,
                'chu_de' => $chu_de,
                'noi_dung' => $noi_dung
            ]);
            header('Location: index.php?page=trangchu#lienhe');
            exit;
        } catch (Exception $e) {
            Session::setFlash('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
            Session::setFlash('form_data', [
                'ho_ten' => $ho_ten,
                'email' => $email,
                'so_dien_thoai' => $so_dien_thoai,
                'chu_de' => $chu_de,
                'noi_dung' => $noi_dung
            ]);
            header('Location: index.php?page=trangchu#lienhe');
            exit;
        }
    }
}

