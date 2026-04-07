<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - DC22V7N553</title>
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
            max-width: 550px;
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: #fff;
            padding: 35px 30px;
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
            padding: 35px;
            max-height: 70vh;
            overflow-y: auto;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .form-label .text-danger {
            color: #dc3545;
        }
        .input-group-custom {
            position: relative;
            margin-bottom: 18px;
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
            height: 48px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }
        .input-group-custom .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.15);
        }
        .form-text {
            font-size: 12px;
            margin-top: 5px;
        }
        .btn-success-custom {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            height: 50px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        .btn-success-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }
        .auth-links {
            margin-top: 25px;
            text-align: center;
        }
        .auth-links a {
            color: #28a745;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }
        .auth-links a:hover {
            color: #20c997;
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
        .alert ul {
            margin-bottom: 0;
            padding-left: 20px;
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
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h2><i class="bi bi-person-plus"></i> Đăng ký tài khoản</h2>
            <p>Tạo tài khoản mới để bắt đầu mua sắm</p>
        </div>
        <div class="auth-body">
            <?php
            require_once __DIR__ . '/../../core/Session.php';
            Session::start();
            $error = Session::getFlash('error');
            $errors = Session::getFlash('errors');
            $success = Session::getFlash('success');
            if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <?php if (is_array($errors) && !empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <?php
            $form_data = Session::getFlash('form_data');
            $ho_ten_value = $form_data['ho_ten'] ?? '';
            $email_value = $form_data['email'] ?? '';
            $so_dien_thoai_value = $form_data['so_dien_thoai'] ?? '';
            $dia_chi_value = $form_data['dia_chi'] ?? '';
            ?>
            
            <form method="POST" action="index.php?action=auth_register" id="registerForm">
                <div class="input-group-custom">
                    <i class="bi bi-person"></i>
                    <input type="text" name="ho_ten" id="ho_ten" class="form-control" 
                           value="<?= htmlspecialchars($ho_ten_value) ?>" 
                           placeholder="Họ và tên" required minlength="2" maxlength="100">
                </div>
                <small class="form-text text-muted">Tối thiểu 2 ký tự</small>
                
                <div class="input-group-custom">
                    <i class="bi bi-envelope"></i>
                    <input type="email" name="email" id="email" class="form-control" 
                           value="<?= htmlspecialchars($email_value) ?>" 
                           placeholder="Email" required maxlength="100">
                </div>
                
                <div class="input-group-custom">
                    <i class="bi bi-telephone"></i>
                    <input type="tel" name="so_dien_thoai" id="so_dien_thoai" class="form-control" 
                           value="<?= htmlspecialchars($so_dien_thoai_value) ?>" 
                           placeholder="Số điện thoại (tùy chọn)" pattern="[0-9]{10,11}">
                </div>
                <small class="form-text text-muted">10-11 chữ số</small>
                
                <div class="input-group-custom">
                    <i class="bi bi-geo-alt"></i>
                    <input type="text" name="dia_chi" id="dia_chi" class="form-control" 
                           value="<?= htmlspecialchars($dia_chi_value) ?>" 
                           placeholder="Địa chỉ (tùy chọn)" maxlength="255">
                </div>
                
                <div class="input-group-custom">
                    <i class="bi bi-lock"></i>
                    <input type="password" name="password" id="password" class="form-control" 
                           placeholder="Mật khẩu" required minlength="6" maxlength="255">
                </div>
                <small class="form-text text-muted">Tối thiểu 6 ký tự</small>
                
                <div class="input-group-custom">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                           placeholder="Xác nhận mật khẩu" required minlength="6" maxlength="255">
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-success btn-success-custom">
                        <i class="bi bi-check-circle"></i> Đăng ký
                    </button>
                </div>
            </form>
            
            <div class="auth-links">
                <a href="index.php?page=login">
                    <i class="bi bi-box-arrow-in-right"></i> Đã có tài khoản? Đăng nhập
                </a>
                <span class="divider">|</span>
                <a href="index.php?page=forgot">
                    <i class="bi bi-question-circle"></i> Quên mật khẩu?
                </a>
            </div>
            
            <div class="back-home">
                <a href="index.php?page=trangchu">
                    <i class="bi bi-arrow-left"></i> Về trang chủ
                </a>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
                document.getElementById('confirm_password').focus();
                return false;
            }
        });
    </script>
</body>
</html>
