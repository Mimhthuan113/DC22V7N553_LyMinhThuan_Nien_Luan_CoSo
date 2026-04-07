<h2 class="mb-4" style="color:#1956b2;" id="lienhe">LIÊN HỆ VỚI CHÚNG TÔI</h2>

<div class="row g-4">
    <!-- Thông tin liên hệ -->
    <div class="col-md-6">
        <div class="mb-4">
            <h5 class="fw-bold mb-3">
                <i class="bi bi-info-circle text-primary"></i> Thông tin liên hệ
            </h5>
            
            <div class="mb-3">
                <p class="mb-1 fw-semibold">
                    <i class="bi bi-geo-alt-fill text-danger"></i> Địa chỉ
                </p>
                <p class="mb-0 ms-4">DC22V7N553 - Nhà thuốc uy tín</p>
            </div>
            
            <div class="mb-3">
                <p class="mb-1 fw-semibold">
                    <i class="bi bi-telephone-fill text-primary"></i> Hotline
                </p>
                <p class="mb-0 ms-4">
                    <a href="tel:0123456789" class="text-decoration-none">0123 456 789</a>
                </p>
            </div>
            
            <div class="mb-3">
                <p class="mb-1 fw-semibold">
                    <i class="bi bi-envelope-fill text-success"></i> Email
                </p>
                <p class="mb-0 ms-4">
                    <a href="mailto:lyminhthuan.dhbk@gmail.com" class="text-decoration-none">lyminhthuan.dhbk@gmail.com</a>
                </p>
            </div>
            
            <div class="mb-3">
                <p class="mb-1 fw-semibold">
                    <i class="bi bi-clock-fill text-warning"></i> Giờ làm việc
                </p>
                <p class="mb-0 ms-4">
                    <strong>Thứ 2 - Chủ nhật:</strong> 7:00 - 22:00<br>
                    <small class="text-muted">Mở cửa cả ngày lễ, Tết</small>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Form liên hệ -->
    <div class="col-md-6">
        <div>
            <h5 class="fw-bold mb-3" style="color:#1956b2;">
                <i class="bi bi-send"></i> Gửi tin nhắn cho chúng tôi
            </h5>
            
            <?php
            require_once __DIR__ . '/../core/Session.php';
            $success = Session::getFlash('success');
            $error = Session::getFlash('error');
            $errors = Session::getFlash('errors');
            $form_data = Session::getFlash('form_data');
            ?>
            
            <!-- Modal thông báo thành công -->
            <?php if ($success): ?>
            <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                        <div class="modal-body text-center p-5">
                            <div class="mb-4">
                                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);">
                                    <i class="bi bi-check-circle-fill text-white" style="font-size: 3rem;"></i>
                                </div>
                            </div>
                            <h4 class="mb-3" style="color: #28a745; font-weight: 700;">Thành công!</h4>
                            <p class="mb-4" style="font-size: 1.1rem; color: #333; line-height: 1.6;">
                                <?= htmlspecialchars($success) ?>
                            </p>
                            <button type="button" class="btn btn-primary btn-lg px-5" data-bs-dismiss="modal" style="background: linear-gradient(135deg, #023660 0%, #1956b2 100%); border: none; border-radius: 25px; padding: 12px 40px; font-weight: 600;">
                                <i class="bi bi-check-lg"></i> Đã hiểu
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                });
            </script>
            <?php endif; ?>
            
            <!-- Modal thông báo lỗi -->
            <?php if ($error): ?>
            <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                        <div class="modal-body text-center p-5">
                            <div class="mb-4">
                                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);">
                                    <i class="bi bi-x-circle-fill text-white" style="font-size: 3rem;"></i>
                                </div>
                            </div>
                            <h4 class="mb-3" style="color: #dc3545; font-weight: 700;">Có lỗi xảy ra!</h4>
                            <p class="mb-4" style="font-size: 1.1rem; color: #333; line-height: 1.6;">
                                <?= htmlspecialchars($error) ?>
                            </p>
                            <button type="button" class="btn btn-danger btn-lg px-5" data-bs-dismiss="modal" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; border-radius: 25px; padding: 12px 40px; font-weight: 600;">
                                <i class="bi bi-x-lg"></i> Đóng
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                    errorModal.show();
                });
            </script>
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
            
            <form id="contactForm" method="POST" action="index.php?action=lienhe_submit">
                <div class="mb-3">
                    <label for="ho_ten" class="form-label fw-semibold">
                        Họ và tên <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="ho_ten" name="ho_ten" 
                           placeholder="Nhập họ và tên của bạn" 
                           value="<?= htmlspecialchars($form_data['ho_ten'] ?? '') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">
                        Email <span class="text-danger">*</span>
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="example@email.com" 
                           value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="so_dien_thoai" class="form-label fw-semibold">
                        Số điện thoại
                    </label>
                    <input type="tel" class="form-control" id="so_dien_thoai" name="so_dien_thoai" 
                           placeholder="0123456789"
                           value="<?= htmlspecialchars($form_data['so_dien_thoai'] ?? '') ?>">
                </div>
                
                <div class="mb-3">
                    <label for="chu_de" class="form-label fw-semibold">
                        Chủ đề <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="chu_de" name="chu_de" required>
                        <option value="">-- Chọn chủ đề --</option>
                        <option value="tu_van" <?= (isset($form_data['chu_de']) && $form_data['chu_de'] === 'tu_van') ? 'selected' : '' ?>>Tư vấn sản phẩm</option>
                        <option value="dat_hang" <?= (isset($form_data['chu_de']) && $form_data['chu_de'] === 'dat_hang') ? 'selected' : '' ?>>Đặt hàng</option>
                        <option value="giao_hang" <?= (isset($form_data['chu_de']) && $form_data['chu_de'] === 'giao_hang') ? 'selected' : '' ?>>Giao hàng</option>
                        <option value="doi_tra" <?= (isset($form_data['chu_de']) && $form_data['chu_de'] === 'doi_tra') ? 'selected' : '' ?>>Đổi trả</option>
                        <option value="khac" <?= (isset($form_data['chu_de']) && $form_data['chu_de'] === 'khac') ? 'selected' : '' ?>>Khác</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="noi_dung" class="form-label fw-semibold">
                        Nội dung tin nhắn <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="noi_dung" name="noi_dung" rows="4" 
                              placeholder="Nhập nội dung tin nhắn của bạn..." required><?= htmlspecialchars($form_data['noi_dung'] ?? '') ?></textarea>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send-fill"></i> Gửi tin nhắn
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .form-control:focus, .form-select:focus {
        border-color: #1956b2;
        box-shadow: 0 0 0 0.2rem rgba(25, 86, 178, 0.15);
    }
    .btn-primary {
        background: linear-gradient(135deg, #023660 0%, #1956b2 100%);
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #1956b2 0%, #023660 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(25, 86, 178, 0.3);
    }
</style>

