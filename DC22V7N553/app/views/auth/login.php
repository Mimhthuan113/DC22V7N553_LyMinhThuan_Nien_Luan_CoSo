<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - DC22V7N553</title>
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
            color: #007bdf;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h2><i class="bi bi-shield-lock"></i> Đăng nhập</h2>
            <p>Chào mừng bạn trở lại!</p>
        </div>
        <div class="auth-body">
            <?php
            require_once __DIR__ . '/../../core/Session.php';
            Session::start();
            $error = Session::getFlash('error');
            $success = Session::getFlash('success');
            if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="index.php?action=auth_login">
                <div class="input-group-custom">
                    <i class="bi bi-envelope"></i>
                    <input type="text" name="username" class="form-control" 
                           placeholder="Email hoặc tên đăng nhập" required autofocus>
                </div>
                <div class="input-group-custom">
                    <i class="bi bi-lock"></i>
                    <input type="password" name="password" class="form-control" 
                           placeholder="Mật khẩu" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-primary-custom">
                        <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                    </button>
                </div>
            </form>
            
            <div class="auth-links">
                <a href="index.php?page=forgot">
                    <i class="bi bi-question-circle"></i> Quên mật khẩu?
                </a>
                <span class="divider">|</span>
                <a href="index.php?page=register">
                    <i class="bi bi-person-plus"></i> Đăng ký tài khoản
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
