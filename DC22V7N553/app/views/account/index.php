<?php
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../core/Auth.php';
$user = Auth::user();
$errors = Session::getFlash('errors');
$form_profile = Session::getFlash('form_profile');
$success_profile = Session::getFlash('success_profile');
$error_profile = Session::getFlash('error_profile');
$errors_password = Session::getFlash('errors_password');
$success_password = Session::getFlash('success_password');
$error_password = Session::getFlash('error_password');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản của tôi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #eef2f7; }
        .container-narrow { max-width: 1080px; margin: 30px auto; }
        .card { box-shadow: 0 6px 18px rgba(0,0,0,0.07); border: none; }
        .form-label { font-weight: 600; color: #1f2d3d; }
        .section-title { font-size: 1.05rem; font-weight: 700; color: #1956b2; }
        .header-card { background: linear-gradient(135deg, #1956b2, #3fa9f5); color: #fff; border-radius: 14px; padding: 18px 20px; box-shadow: 0 8px 20px rgba(25,86,178,0.2); }
        .avatar { width: 54px; height: 54px; border-radius: 50%; background: rgba(255,255,255,0.2); display: inline-flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; color: #fff; }
        .muted { color: #64748b; }
        .badge-role { background: rgba(255,255,255,0.2); color: #fff; }
        .card-shadow-sm { box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="container-narrow">
        <div class="header-card d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                <div>
                    <div class="fw-bold fs-5 mb-1">Tài khoản của tôi</div>
                    <div class="small">Xin chào, <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-role"><?= htmlspecialchars($user['role']) ?></span>
                <a class="btn btn-light btn-sm" href="index.php?page=trangchu"><i class="bi bi-house"></i> Trang chủ</a>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($success_profile): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($success_profile) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error_profile): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($error_profile) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (is_array($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if ($success_password): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($success_password) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error_password): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($error_password) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (is_array($errors_password) && !empty($errors_password)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors_password as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card card-shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="section-title">Thông tin cá nhân</span>
                        </div>
                        <form method="POST" action="index.php?action=account_update">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($detail['email']) ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Họ tên</label>
                                <input type="text" name="ho_ten" class="form-control" required minlength="2"
                                       value="<?= htmlspecialchars($form_profile['ho_ten'] ?? $detail['ho_ten'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="tel" name="so_dien_thoai" class="form-control" pattern="[0-9]{10,11}"
                                       value="<?= htmlspecialchars($form_profile['so_dien_thoai'] ?? $detail['so_dien_thoai'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Địa chỉ</label>
                                <input type="text" name="dia_chi" class="form-control" maxlength="255"
                                       value="<?= htmlspecialchars($form_profile['dia_chi'] ?? $detail['dia_chi'] ?? '') ?>">
                            </div>
                            <div class="d-grid">
                                <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Cập nhật thông tin</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card card-shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="section-title">Đổi mật khẩu</span>
                        </div>
                        <form method="POST" action="index.php?action=account_change_password">
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu hiện tại</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu mới</label>
                                <input type="password" name="new_password" class="form-control" required minlength="6">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Xác nhận mật khẩu mới</label>
                                <input type="password" name="confirm_password" class="form-control" required minlength="6">
                            </div>
                            <div class="d-grid">
                                <button class="btn btn-warning" type="submit"><i class="bi bi-shield-lock"></i> Đổi mật khẩu</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

