<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xin Phép Vắng | THPT Manager</title>
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
        .card-custom { background: white; border-radius: 16px; border:none; box-shadow: 0 5px 15px rgba(220,53,69,0.08); padding: 25px; }
        
        @media (max-width: 992px) { 
            .sidebar { transform: translateX(-100%); } 
            .main-content { margin-left: 0; } 
            .sidebar.show { transform: translateX(0); } 
        }
    </style>
</head>
<body>

<?php $info = $hoc_sinh_info ?? []; ?>

<div class="sidebar" id="sidebar">
    <div class="profile-section">
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="rounded-circle border border-4 border-white shadow-lg mb-3" width="90">
        <h5 class="fw-bold"><?= htmlspecialchars($user_name) ?></h5>
        <p class="mb-1 opacity-90">Phụ huynh học sinh</p>
        <span class="badge bg-white text-danger px-3 py-2 rounded-pill fw-bold"><?= htmlspecialchars($info['ten_con'] ?? '---') ?></span>
    </div>
    <ul class="nav flex-column mt-4 px-3">
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/phuhuynh/dashboard"><i class="bi bi-speedometer2 me-3"></i>Tổng Quan</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/phuhuynh/hoso"><i class="bi bi-person-lines-fill me-3"></i>Hồ Sơ Con</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/thanhtoan/index"><i class="bi bi-credit-card me-3"></i>Thanh Toán Học Phí</a></li>
        <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-envelope-paper me-3"></i>Xin Phép Vắng</a></li>
        <li class="nav-item mt-auto pb-4"><a class="nav-link text-danger fw-bold" href="<?= BASE_URL ?>/auth/logout"><i class="bi bi-box-arrow-right me-3"></i>Đăng Xuất</a></li>
    </ul>
</div>

<div class="main-content">
    
    <div class="d-flex justify-content-between align-items-center mb-4 d-lg-none bg-white p-3 rounded-3 shadow-sm">
        <button class="btn btn-outline-danger" id="toggleSidebar"><i class="bi bi-list fs-4"></i></button>
        <span class="fw-bold text-danger">XIN PHÉP</span>
    </div>

    <?= $message ?? '' ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card card-custom h-100">
                <h5 class="fw-bold text-danger mb-4"><i class="bi bi-pen-fill me-2"></i>Tạo Đơn Xin Phép Mới</h5>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Từ ngày</label>
                        <input type="date" name="ngay_bat_dau" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Đến ngày</label>
                        <input type="date" name="ngay_ket_thuc" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">Lý do nghỉ</label>
                        <textarea name="ly_do" class="form-control" rows="4" placeholder="Ví dụ: Cháu bị sốt, nhà có việc bận..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100 py-2 fw-bold">
                        <i class="bi bi-send-fill me-2"></i>Gửi Cho Giáo Viên
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-custom h-100">
                <h5 class="fw-bold text-secondary mb-4"><i class="bi bi-clock-history me-2"></i>Lịch Sử Gửi Đơn</h5>
                <?php if (empty($lich_su_don)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                        Chưa có đơn xin phép nào.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Ngày Gửi</th>
                                    <th>Thời Gian Nghỉ</th>
                                    <th>Lý Do</th>
                                    <th class="text-center">Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lich_su_don as $don): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($don['ngay_lam_don'])) ?></td>
                                        <td>
                                            <div class="small fw-bold text-dark">
                                                <?= date('d/m', strtotime($don['ngay_bat_dau_nghi'])) ?> - 
                                                <?= date('d/m', strtotime($don['ngay_ket_thuc_nghi'])) ?>
                                            </div>
                                            <div class="small text-muted"><?= $don['so_ngay_nghi'] ?> ngày</div>
                                        </td>
                                        <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($don['ly_do_nghi']) ?>">
                                                <?= htmlspecialchars($don['ly_do_nghi']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php 
                                                $status = $don['trang_thai_don'];
                                                if ($status == 'ChoDuyet') 
                                                    echo '<span class="badge bg-warning text-dark">Chờ duyệt</span>';
                                                elseif ($status == 'DaDuyet') 
                                                    echo '<span class="badge bg-success">Đã duyệt</span>';
                                                else 
                                                    echo '<span class="badge bg-danger">Từ chối</span>';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
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