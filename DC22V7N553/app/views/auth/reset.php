<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - DC22V7N553</title>
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
            max-width: 450px;
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
            background: linear-gradient(135deg, #007bdf 0%, #1956b2 100%);
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
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .input-group-custom {
            position: relative;
            margin-bottom: 20px;
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
            border-color: #007bdf;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 223, 0.15);
        }
        .form-text {
            font-size: 12px;
            margin-top: 5px;
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #007bdf 0%, #1956b2 100%);
            border: none;
            height: 50px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0, 123, 223, 0.3);
        }
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 223, 0.4);
        }
        .auth-links {
            margin-top: 25px;
            text-align: center;
        }
        .auth-links a {
            color: #007bdf;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }
        .auth-links a:hover {
            color: #1956b2;
            text-decoration: underline;
        }
        .alert {
            border-radius: 10px;
            border: none;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }
        .password-strength .strength-bar {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }
        .password-strength .strength-fill {
            height: 100%;
            transition: all 0.3s;
            border-radius: 2px;
        }
        .strength-weak { background: #dc3545; width: 33%; }
        .strength-medium { background: #ffc107; width: 66%; }
        .strength-strong { background: #28a745; width: 100%; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h2><i class="bi bi-shield-lock"></i> Đặt lại mật khẩu</h2>
            <p>Tạo mật khẩu mới cho tài khoản của bạn</p>
        </div>
        <div class="auth-body">
            <?php
            require_once __DIR__ . '/../../core/Session.php';
            Session::start();
            $error = Session::getFlash('error');
            if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="index.php?action=auth_reset" id="resetForm">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
                
                <div class="input-group-custom">
                    <i class="bi bi-lock"></i>
                    <input type="password" name="password" id="password" class="form-control" 
                           placeholder="Mật khẩu mới" required minlength="6" autofocus>
                </div>
                <small class="form-text text-muted">Tối thiểu 6 ký tự</small>
                <div class="password-strength" id="passwordStrength" style="display: none;">
                    <div class="strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                </div>
                
                <div class="input-group-custom">
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                           placeholder="Xác nhận mật khẩu" required minlength="6">
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-primary-custom">
                        <i class="bi bi-check-circle"></i> Đặt lại mật khẩu
                    </button>
                </div>
            </form>
            
            <div class="auth-links">
                <a href="index.php?page=login">
                    <i class="bi bi-arrow-left"></i> Quay lại đăng nhập
                </a>
            </div>
        </div>
    </div>
    
    <script>
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const strengthBar = document.getElementById('passwordStrength');
        const strengthFill = document.getElementById('strengthFill');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            if (password.length > 0) {
                strengthBar.style.display = 'block';
                let strength = 0;
                if (password.length >= 6) strength++;
                if (password.length >= 8) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;
                
                strengthFill.className = 'strength-fill';
                if (strength <= 2) {
                    strengthFill.classList.add('strength-weak');
                } else if (strength <= 4) {
                    strengthFill.classList.add('strength-medium');
                } else {
                    strengthFill.classList.add('strength-strong');
                }
            } else {
                strengthBar.style.display = 'none';
            }
        });
        
        document.getElementById('resetForm')?.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
                confirmPasswordInput.focus();
                return false;
            }
        });
    </script>
</body>
</html>
