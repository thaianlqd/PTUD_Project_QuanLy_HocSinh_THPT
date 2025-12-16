<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Học Sinh | THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root { --primary: #dc3545; --bg: #fff5f5; --sidebar-width: 300px; }
        body { font-family: 'Roboto', sans-serif; background-color: var(--bg); color: #333; }
        
        .sidebar { width: var(--sidebar-width); position: fixed; height: 100vh; background: #fff; box-shadow: 3px 0 15px rgba(220,53,69,0.1); z-index: 1000; transition: 0.3s; }
        .profile-section { padding: 30px 20px; text-align: center; background: linear-gradient(135deg, #dc3545, #c82333); color: white; border-bottom: 4px solid #c82333; }
        .nav-link { color: #555; padding: 14px 20px; font-weight: 500; margin: 6px 15px; border-radius: 10px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background-color: #fee2e5; color: var(--primary); font-weight: 600; }
        .main-content { margin-left: var(--sidebar-width); padding: 30px; transition: 0.3s; }
        .card-profile { border: none; border-radius: 16px; background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; }
        .form-control[readonly] { background-color: #f8f9fa; border: 1px dashed #ced4da; }
        .avatar-box { background: linear-gradient(180deg, #fff0f1 0%, #ffffff 100%); }
        
        /* Style mới cho Hạnh kiểm & Vắng */
        .stat-box { background: #fff; border: 1px solid #eee; border-radius: 12px; padding: 10px; margin-top: 15px; }
        .stat-box i { font-size: 1.5rem; margin-bottom: 5px; display: block; }

        @media (max-width: 992px) { 
            .sidebar { transform: translateX(-100%); } 
            .main-content { margin-left: 0; } 
            .sidebar.show { transform: translateX(0); } 
        }
    </style>
</head>
<body>

<?php
    $info = $hoc_sinh_info ?? [];
    $hs = $student_detail; // Biến này có thể là NULL

    // Kiểm tra dữ liệu
    $has_data = !empty($hs);

    // Nếu có data thì lấy, không thì để mặc định
    $ho_ten_hs = $has_data ? ($hs['ho_ten'] ?? 'Chưa cập nhật') : 'Chưa liên kết';
    $gioi_tinh = ($has_data && ($hs['gioi_tinh'] ?? 'Nam') == 'Nam') ? 'Nam' : 'Nữ';
    $avatar_img = $gioi_tinh == 'Nam' 
        ? 'https://cdn-icons-png.flaticon.com/512/2922/2922510.png' 
        : 'https://cdn-icons-png.flaticon.com/512/2922/2922561.png';

    // Xử lý Hạnh kiểm & Vắng
    $hanh_kiem = $hs['hanh_kiem'] ?? '';
    $so_buoi_vang = $hs['so_buoi_vang'] ?? 0;

    $hk_badges = [
        'Tot' => ['success', 'Tốt'], 'Tốt' => ['success', 'Tốt'], 'Gioi' => ['success', 'Tốt'],
        'Kha' => ['primary', 'Khá'], 'Khá' => ['primary', 'Khá'],
        'TrungBinh' => ['warning', 'Trung Bình'], 'TB' => ['warning', 'Trung Bình'],
        'Yeu' => ['danger', 'Yếu'], 'Kem' => ['danger', 'Kém'],
        'Chưa Xếp' => ['secondary', 'Chưa Xếp'], '' => ['secondary', 'Chưa Xếp']
    ];
    $hk_style = $hk_badges[$hanh_kiem] ?? ['secondary', 'Chưa Xếp'];
?>

<div class="sidebar" id="sidebar">
    <div class="profile-section">
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="rounded-circle border border-4 border-white shadow-lg mb-3" width="90">
        <h5 class="fw-bold"><?= htmlspecialchars($data['user_name']) ?></h5>
        <p class="mb-1 opacity-90">Phụ huynh học sinh</p>
        <span class="badge bg-white text-danger px-3 py-2 rounded-pill fw-bold"><?= htmlspecialchars($info['ten_con'] ?? '---') ?></span>
    </div>
    <ul class="nav flex-column mt-4 px-3">
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/phuhuynh/dashboard"><i class="bi bi-speedometer2 me-3"></i>Tổng Quan</a></li>
        <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-person-lines-fill me-3"></i>Hồ Sơ Con</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/thanhtoan/index"><i class="bi bi-credit-card me-3"></i>Thanh Toán Học Phí</a></li>
        <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-envelope-paper me-3"></i>Xin Phép Vắng</a></li>
        <li class="nav-item mt-auto pb-4"><a class="nav-link text-danger fw-bold" href="<?= BASE_URL ?>/auth/logout"><i class="bi bi-box-arrow-right me-3"></i>Đăng Xuất</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4 d-lg-none bg-white p-3 rounded-3 shadow-sm">
        <button class="btn btn-outline-danger" id="toggleSidebar"><i class="bi bi-list fs-4"></i></button>
        <span class="fw-bold text-danger">HỒ SƠ</span>
    </div>

    <h4 class="fw-bold text-danger mb-4"><i class="bi bi-person-vcard me-2"></i>Thông Tin Hồ Sơ Học Sinh</h4>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card card-profile h-100 text-center p-4 avatar-box">
                <div class="mb-4 mt-3">
                    <img src="<?= $avatar_img ?>" width="150" class="rounded-circle shadow-sm border border-5 border-white">
                </div>
                <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($ho_ten_hs) ?></h4>
                <p class="text-muted">Mã HS: <strong><?= htmlspecialchars($hs['ma_hoc_sinh'] ?? '---') ?></strong></p>
                
                <div class="d-flex justify-content-center gap-2 mb-3 mt-3">
                    <span class="badge bg-danger fs-6 px-3 py-2">Lớp <?= htmlspecialchars($hs['ten_lop'] ?? '---') ?></span>
                    <span class="badge bg-dark fs-6 px-3 py-2">Khối <?= htmlspecialchars($hs['ten_khoi'] ?? '?') ?></span>
                </div>

                <div class="row g-2 mt-4">
                    <div class="col-6">
                        <div class="stat-box text-<?= $hk_style[0] ?>">
                            <i class="bi bi-star-fill"></i>
                            <div class="small text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Hạnh Kiểm HK1</div>
                            <div class="fw-bold fs-5"><?= $hk_style[1] ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box text-warning">
                            <i class="bi bi-calendar-x-fill"></i>
                            <div class="small text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Số Buổi Vắng</div>
                            <div class="fw-bold fs-5"><?= $so_buoi_vang ?></div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-success border-0 bg-opacity-10 bg-success text-success fw-bold mt-4 mb-0">
                    <i class="bi bi-check-circle-fill me-2"></i>Đang theo học
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-profile p-4 h-100">
                <h5 class="fw-bold text-secondary mb-4"><i class="bi bi-info-circle-fill me-2"></i>Thông tin cá nhân</h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Họ và tên</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($ho_ten_hs) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Ngày sinh</label>
                        <input type="text" class="form-control" value="<?= date('d/m/Y', strtotime($hs['ngay_sinh'] ?? 'now')) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Giới tính</label>
                        <input type="text" class="form-control" value="<?= $gioi_tinh ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Niên khóa</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($hs['ten_nam_hoc'] ?? '---') ?>" readonly>
                    </div>
                    
                    <div class="col-12"><hr class="text-muted opacity-25"></div>
                    
                    <h5 class="fw-bold text-secondary mb-2 mt-2"><i class="bi bi-geo-alt-fill me-2"></i>Liên hệ</h5>

                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Số điện thoại</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-dashed"><i class="bi bi-telephone"></i></span>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($hs['so_dien_thoai'] ?? 'Chưa cập nhật') ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-dashed"><i class="bi bi-envelope"></i></span>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($hs['email'] ?? 'Chưa cập nhật') ?>" readonly>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small fw-bold">Địa chỉ thường trú</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-dashed"><i class="bi bi-house"></i></span>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($hs['dia_chi'] ?? 'Chưa cập nhật') ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center mt-5 text-muted small">© 2025 THPT Manager</footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('toggleSidebar')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });
</script>
</body>
</html>