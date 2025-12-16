<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Nguyện Vọng | Thí Sinh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --primary: #667eea; --secondary: #764ba2; --bg: #f3f4f6; }
        body { background-color: var(--bg); font-family: 'Segoe UI', sans-serif; }
        
        /* Sidebar Styles */
        .sidebar { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; min-height: 100vh; padding: 20px; box-shadow: 4px 0 10px rgba(0,0,0,0.1); }
        .sidebar-item a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 12px 15px; border-radius: 8px; display: block; transition: 0.3s; font-weight: 500; margin-bottom: 5px; }
        .sidebar-item a:hover, .sidebar-item a.active { background: rgba(255,255,255,0.25); color: white; transform: translateX(5px); }
        
        /* Main Content */
        .main-content { padding: 30px; }
        .page-title { font-weight: 800; color: #2d3748; margin-bottom: 1.5rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }

        /* NV Card Styles */
        .nv-card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.3s ease, box-shadow 0.3s ease; background: white; height: 100%; overflow: hidden; }
        .nv-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
        
        .nv-header { padding: 15px; text-align: center; color: white; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .nv-1 .nv-header { background: linear-gradient(45deg, #FF512F, #DD2476); } /* Màu đỏ cam nổi bật cho NV1 */
        .nv-2 .nv-header { background: linear-gradient(45deg, #1FA2FF, #12D8FA); }
        .nv-3 .nv-header { background: linear-gradient(45deg, #11998e, #38ef7d); }
        
        .nv-body { padding: 25px; }
        .form-select { border-radius: 8px; padding: 12px; border: 2px solid #e2e8f0; font-size: 1rem; cursor: pointer; }
        .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2); }

        .btn-save { background: linear-gradient(to right, #667eea, #764ba2); color: white; border: none; padding: 12px 40px; border-radius: 30px; font-weight: bold; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(118, 75, 162, 0.4); transition: 0.3s; }
        .btn-save:hover { transform: scale(1.05); box-shadow: 0 6px 20px rgba(118, 75, 162, 0.6); }
        .btn-save:disabled { background: #cbd5e0; cursor: not-allowed; transform: none; box-shadow: none; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-md-4 sidebar d-none d-md-block">
                <div class="d-flex align-items-center mb-4 px-2">
                    <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                        <i class="bi bi-mortarboard-fill fs-5"></i>
                    </div>
                    <h5 class="m-0">Cổng Thí Sinh</h5>
                </div>
                
                <div class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/ThiSinh/dashboard">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </div>
                <div class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/ThiSinh/nguyenVong" class="active">
                        <i class="bi bi-list-check me-2"></i> Nguyện Vọng
                    </a>
                </div>
                <hr style="border-color: rgba(255,255,255,0.3);">
                <div class="sidebar-item">
                    <a href="<?php echo BASE_URL; ?>/Auth/logout">
                        <i class="bi bi-box-arrow-right me-2"></i> Đăng Xuất
                    </a>
                </div>
            </div>

            <div class="col-lg-9 col-md-8 main-content">
                <h2 class="page-title"><i class="bi bi-pencil-square text-primary"></i> Đăng Ký Nguyện Vọng</h2>

                <div id="alertMessage"></div>

                <?php 
                    // Helper logic: Map dữ liệu đã có vào biến để hiển thị selected
                    $nv1_sel = ''; $nv2_sel = ''; $nv3_sel = '';
                    if (!empty($nguyen_vong)) {
                        foreach ($nguyen_vong as $nv) {
                            if ($nv['thu_tu_nguyen_vong'] == 1) $nv1_sel = $nv['ma_truong'];
                            if ($nv['thu_tu_nguyen_vong'] == 2) $nv2_sel = $nv['ma_truong'];
                            if ($nv['thu_tu_nguyen_vong'] == 3) $nv3_sel = $nv['ma_truong'];
                        }
                    }
                ?>

                <div class="alert alert-light border-start border-4 border-info shadow-sm">
                    <i class="bi bi-info-circle-fill text-info me-2"></i>
                    <strong>Lưu ý:</strong> Vui lòng chọn trường theo thứ tự ưu tiên từ cao xuống thấp. Không được chọn trùng trường giữa các nguyện vọng.
                </div>

                <form id="frmNguyenVong" onsubmit="saveNguyenVong(event)">
                    <div class="row g-4 mb-5">
                        <div class="col-md-4">
                            <div class="nv-card nv-1">
                                <div class="nv-header">
                                    <i class="bi bi-star-fill text-warning"></i> Nguyện Vọng 1 (Ưu tiên)
                                </div>
                                <div class="nv-body text-center">
                                    <p class="text-muted small mb-3">Trường mong muốn nhất</p>
                                    <select class="form-select school-select" id="nv1" required onchange="validateSelection()">
                                        <option value="">-- Chọn Trường --</option>
                                        <?php foreach ($truongs as $truong): ?>
                                            <option value="<?= $truong['ma_truong'] ?>" <?= ($nv1_sel == $truong['ma_truong']) ? 'selected' : '' ?>>
                                                <?= $truong['ten_truong'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="nv-card nv-2">
                                <div class="nv-header">
                                    <i class="bi bi-2-circle"></i> Nguyện Vọng 2
                                </div>
                                <div class="nv-body text-center">
                                    <p class="text-muted small mb-3">Lựa chọn dự phòng 1</p>
                                    <select class="form-select school-select" id="nv2" onchange="validateSelection()">
                                        <option value="">-- Chọn Trường (Không bắt buộc) --</option>
                                        <?php foreach ($truongs as $truong): ?>
                                            <option value="<?= $truong['ma_truong'] ?>" <?= ($nv2_sel == $truong['ma_truong']) ? 'selected' : '' ?>>
                                                <?= $truong['ten_truong'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="nv-card nv-3">
                                <div class="nv-header">
                                    <i class="bi bi-3-circle"></i> Nguyện Vọng 3
                                </div>
                                <div class="nv-body text-center">
                                    <p class="text-muted small mb-3">Lựa chọn dự phòng 2</p>
                                    <select class="form-select school-select" id="nv3" onchange="validateSelection()">
                                        <option value="">-- Chọn Trường (Không bắt buộc) --</option>
                                        <?php foreach ($truongs as $truong): ?>
                                            <option value="<?= $truong['ma_truong'] ?>" <?= ($nv3_sel == $truong['ma_truong']) ? 'selected' : '' ?>>
                                                <?= $truong['ten_truong'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-save" id="btnSubmit">
                            <i class="bi bi-floppy2-fill me-2"></i> Lưu Thay Đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ✅ ĐÃ SỬA: Dùng BASE_URL để đảm bảo gọi đúng địa chỉ API dù ở thư mục nào
        const API_URL = '<?php echo BASE_URL; ?>/ThiSinh';

        function showAlert(message, type) {
            const alertBox = document.getElementById('alertMessage');
            alertBox.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            // Tự động cuộn lên thông báo
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Hàm kiểm tra trùng lặp ngay khi chọn
        function validateSelection() {
            const nv1 = document.getElementById('nv1').value;
            const nv2 = document.getElementById('nv2').value;
            const nv3 = document.getElementById('nv3').value;
            const btn = document.getElementById('btnSubmit');

            // Reset style
            document.querySelectorAll('.form-select').forEach(el => el.classList.remove('is-invalid'));

            let hasError = false;

            // Check NV2 trùng NV1
            if (nv2 && nv2 === nv1) {
                document.getElementById('nv2').classList.add('is-invalid');
                hasError = true;
            }
            // Check NV3 trùng NV1 hoặc NV2
            if (nv3 && (nv3 === nv1 || nv3 === nv2)) {
                document.getElementById('nv3').classList.add('is-invalid');
                hasError = true;
            }

            if (hasError) {
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Lỗi: Trùng Trường!';
                btn.classList.replace('btn-save', 'btn-danger');
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-floppy2-fill me-2"></i> Lưu Thay Đổi';
                btn.classList.remove('btn-danger');
                btn.classList.add('btn-save');
            }
        }

        // Hàm gửi dữ liệu lên server
        function saveNguyenVong(e) {
            e.preventDefault();
            
            const nv1 = document.getElementById('nv1').value;
            const nv2 = document.getElementById('nv2').value;
            const nv3 = document.getElementById('nv3').value;

            if (!nv1) {
                showAlert('Nguyện vọng 1 là bắt buộc!', 'danger');
                return;
            }

            const btn = document.getElementById('btnSubmit');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang lưu...';

            // Gọi API
            fetch(`${API_URL}/luuTatCaNguyenVongApi`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nv1, nv2, nv3 })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    // Reload sau 1.5s để cập nhật giao diện
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message, 'danger');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('Lỗi kết nối: ' + err.message, 'danger');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }
    </script>
</body>
</html>