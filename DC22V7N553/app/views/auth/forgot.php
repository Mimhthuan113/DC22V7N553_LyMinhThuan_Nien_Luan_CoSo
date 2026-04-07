<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - DC22V7N553</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .auth-container {
            width: 100%;
            max-width: 480px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .auth-header {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #fff;
            padding: 40px 30px;
            text-align: center;
        }
        .auth-header h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .auth-header p {
            font-size: 14px;
            opacity: 0.9;
        }
        .auth-body {
            padding: 40px 35px;
        }
        .input-group-custom {
            position: relative;
            margin-bottom: 25px;
        }
        .input-group-custom i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            z-index: 10;
        }
        .input-group-custom .form-control {
            padding-left: 45px;
            height: 50px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }
        .input-group-custom .form-control:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.15);
        }
        .btn-warning-custom {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            border: none;
            height: 50px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            color: #fff;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
        }
        .btn-warning-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4);
            color: #fff;
        }
        .auth-links {
            margin-top: 25px;
            text-align: center;
        }
        .auth-links a {
            color: #ff9800;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }
        .auth-links a:hover {
            color: #ffc107;
            text-decoration: underline;
        }
        .auth-links .divider {
            margin: 0 10px;
            color: #ccc;
        }
        .alert {
            border-radius: 10px;
            border: none;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-info {
            background: #e7f3ff;
            color: #004085;
            border-left: 4px solid #007bff;
        }
        .alert-info a {
            color: #007bff;
            word-break: break-all;
        }
        .back-home {
            text-align: center;
            margin-top: 20px;
        }
        .back-home a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .back-home a:hover {
            color: #ff9800;
        }
        .info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
        .info-box i {
            color: #ff9800;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .info-box p {
            margin: 0;
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h2><i class="bi bi-key"></i> Khôi phục mật khẩu</h2>
            <p>Nhập email để nhận link đặt lại mật khẩu</p>
        </div>
        <div class="auth-body">
            <?php
            require_once __DIR__ . '/../../core/Session.php';
            Session::start();
            $error = Session::getFlash('error');
            $success = Session::getFlash('success');
            $reset_link = Session::getFlash('reset_link');
            ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($reset_link): ?>
                <div class="alert alert-info">
                    <strong><i class="bi bi-info-circle"></i> Link đặt lại mật khẩu (dùng để kiểm thử):</strong><br>
                    <a href="<?= htmlspecialchars($reset_link) ?>" target="_blank">
                        <?= htmlspecialchars($reset_link) ?>
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
                <div class="info-box text-center">
                    <i class="bi bi-envelope-paper"></i>
                    <p>Chúng tôi sẽ gửi link đặt lại mật khẩu đến email của bạn. Vui lòng kiểm tra hộp thư (có thể trong thư mục Spam).</p>
                </div>
                
                <form method="POST" action="index.php?action=auth_forgot">
                    <div class="input-group-custom">
                        <i class="bi bi-envelope"></i>
                        <input type="email" name="email" class="form-control" 
                               placeholder="Nhập email đã đăng ký" required autofocus>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning btn-warning-custom">
                            <i class="bi bi-send"></i> Gửi yêu cầu
                        </button>
                    </div>
                </form>
            <?php endif; ?>
            
            <div class="auth-links">
                <a href="index.php?page=login">
                    <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                </a>
                <span class="divider">|</span>
                <a href="index.php?page=register">
                    <i class="bi bi-person-plus"></i> Đăng ký mới
                </a>
            </div>
            
            <div class="back-home">
                <a href="index.php?page=trangchu">
                    <i class="bi bi-arrow-left"></i> Về trang chủ
                </a>
            </div>
        </div>
    </div>
</body>
</html>
