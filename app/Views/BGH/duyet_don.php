<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duyệt Đơn Xin Phép | GVCN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root { --primary: #198754; --bg: #f0f2f5; --sidebar-width: 280px; }
        body { font-family: 'Roboto', sans-serif; background-color: var(--bg); }
        
        .sidebar { width: var(--sidebar-width); position: fixed; height: 100vh; background: #fff; z-index: 1000; box-shadow: 2px 0 10px rgba(0,0,0,0.05); }
        .profile-section { padding: 30px 20px; text-align: center; background: linear-gradient(135deg, #198754 0%, #0f5132 100%); color: white; }
        
        .nav-link { color: #555; padding: 12px 20px; margin: 4px 0; font-weight: 500; }
        .nav-link:hover, .nav-link.active { background-color: #e6f8ed; color: var(--primary); border-left: 4px solid var(--primary); }
        
        .main-content { margin-left: var(--sidebar-width); padding: 30px; }
        .card-custom { border:none; border-radius:12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); background: white; }
        
        @media (max-width: 992px) { 
            .sidebar { transform: translateX(-100%); } 
            .main-content { margin-left: 0; } 
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="profile-section">
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="rounded-circle border border-3 border-white mb-2" width="80">
        <h6 class="fw-bold mb-0"><?= htmlspecialchars($user_name ?? 'GVCN') ?></h6>
        <small class="opacity-75">GV Chủ Nhiệm: <?= htmlspecialchars($ten_lop ?? '---') ?></small>
    </div>
    <div class="p-3">
        <nav class="nav flex-column">
            <a href="<?= BASE_URL ?>/dashboard#tab-cn" class="nav-link">
                <i class="bi bi-arrow-left-circle me-2"></i> Quay lại Dashboard
            </a>
            
            <a href="#" class="nav-link active">
                <i class="bi bi-envelope-paper me-2"></i> Duyệt Đơn Phép
            </a>
            <a href="<?= BASE_URL ?>/auth/logout" class="nav-link text-danger mt-3 border-top pt-3">
                <i class="bi bi-box-arrow-right me-2"></i> Đăng Xuất
            </a>
        </nav>
    </div>
</div>

<div class="main-content">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-success mb-0">
            <i class="bi bi-check2-square me-2"></i>Quản Lý Đơn Xin Phép - Lớp <?= htmlspecialchars($ten_lop ?? '---') ?>
        </h4>
        <a href="<?= BASE_URL ?>/dashboard#tab-cn" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-x-lg"></i> Đóng
        </a>
    </div>

    <div class="card card-custom p-4">
        <?php if (empty($ds_don)): ?>
            <div class="text-center py-5 text-muted">
                <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" width="80" class="opacity-50 mb-3" alt="Empty">
                <h5>Chưa có đơn xin phép nào</h5>
                <p class="small">Lớp của bạn hiện tại không có học sinh nào gửi đơn.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Học Sinh</th>
                            <th>Ngày Gửi</th>
                            <th>Thời Gian Nghỉ</th>
                            <th>Lý Do</th>
                            <th class="text-center">Trạng Thái</th>
                            <th class="text-end" style="min-width: 180px;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ds_don as $don): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-success"><?= htmlspecialchars($don['ten_hoc_sinh'] ?? 'HS ẩn danh') ?></div>
                                </td>
                                <td><?= date('d/m/Y', strtotime($don['ngay_lam_don'])) ?></td>
                                <td>
                                    <div class="small fw-bold">
                                        <?= date('d/m', strtotime($don['ngay_bat_dau_nghi'])) ?> - <?= date('d/m', strtotime($don['ngay_ket_thuc_nghi'])) ?>
                                    </div>
                                    <div class="small text-muted"><?= $don['so_ngay_nghi'] ?> ngày</div>
                                </td>
                                <td style="max-width: 300px;">
                                    <p class="mb-0 text-truncate" title="<?= htmlspecialchars($don['ly_do_nghi']) ?>">
                                        <?= htmlspecialchars($don['ly_do_nghi']) ?>
                                    </p>
                                </td>
                                <td class="text-center">
                                    <?php 
                                        $tt = $don['trang_thai_don'];
                                        if ($tt == 'ChoDuyet') 
                                            echo '<span class="badge bg-warning text-dark">Chờ duyệt</span>';
                                        elseif ($tt == 'DaDuyet') 
                                            echo '<span class="badge bg-success">Đã duyệt</span>';
                                        else 
                                            echo '<span class="badge bg-danger">Đã từ chối</span>';
                                    ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($tt == 'ChoDuyet'): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/giaovienchunhiem/xulyduyet" class="d-inline">
                                            <input type="hidden" name="ma_phieu" value="<?= $don['ma_phieu'] ?>">
                                            
                                            <button type="submit" name="hanh_dong" value="duyet" class="btn btn-success btn-sm me-1" title="Duyệt" onclick="return confirm('Duyệt đơn nghỉ này?')">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            
                                            <button type="submit" name="hanh_dong" value="tuchoi" class="btn btn-danger btn-sm" title="Từ chối" onclick="return confirm('Từ chối đơn này?')">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic"><i class="bi bi-lock-fill"></i> Đã xử lý</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>