<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Phụ Huynh | THPT Manager</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root { --primary: #dc3545; --bg: #fff5f5; --sidebar-width: 300px; }
        body { font-family: 'Roboto', sans-serif; background-color: var(--bg); color: #333; }
        
        .sidebar { width: var(--sidebar-width); position: fixed; height: 100vh; background: #fff; box-shadow: 3px 0 15px rgba(220,53,69,0.1); z-index: 1000; transition: 0.3s; }
        .profile-section { padding: 30px 20px; text-align: center; background: linear-gradient(135deg, #dc3545, #c82333); color: white; border-bottom: 4px solid #c82333; }
        .nav-link { color: #555; padding: 14px 20px; font-weight: 500; margin: 6px 15px; border-radius: 10px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background-color: #fee2e5; color: var(--primary); font-weight: 600; }
        .main-content { margin-left: var(--sidebar-width); padding: 30px; transition: 0.3s; }
        .stat-card { border: none; border-radius: 16px; padding: 25px; background: white; box-shadow: 0 5px 15px rgba(220,53,69,0.08); transition: 0.3s; height: 100%; }
        .stat-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(220,53,69,0.15); }
        .chart-box { background: white; border-radius: 16px; padding: 25px; box-shadow: 0 5px 15px rgba(220,53,69,0.08); height: 100%; }
        
        @media (max-width: 992px) { 
            .sidebar { transform: translateX(-100%); } 
            .main-content { margin-left: 0; } 
            .sidebar.show { transform: translateX(0); } 
        }
    </style>
</head>
<body>

<?php
    // SỬA LẠI: Dùng trực tiếp tên biến (vì Controller đã extract ra rồi)
    // Không dùng $data['...'] nữa
    
    $info = $hoc_sinh_info ?? []; // Biến $hoc_sinh_info được truyền từ Controller
    $ten_con = $info['ten_con'] ?? 'Chưa cập nhật';
    $ten_lop = $info['ten_lop'] ?? 'N/A';
    
    // Các biến khác cũng dùng trực tiếp luôn
    $hoa_don_count = $hoa_don_count ?? 0;
    $phieu_vang_count = $phieu_vang_count ?? 0;
    $bang_diem = $bang_diem ?? []; // Biến $bang_diem từ Controller

    // --- LOGIC TÍNH TOÁN BÙ (Giữ nguyên đoạn fix điểm số cũ) ---
    foreach ($bang_diem as $mon => &$hk_data) {
        if (is_array($hk_data)) {
            foreach ($hk_data as $hk => &$d) {
                // ... (Giữ nguyên logic tính điểm ở đây) ...
                $tb = isset($d['TB']) ? (float)$d['TB'] : 0;
                if ($tb == 0) {
                    $m = (float)($d['DiemMieng'] ?? 0);
                    $p15 = (float)($d['Diem15Phut'] ?? 0);
                    $p45 = (float)($d['Diem1Tiet'] ?? 0);
                    $gk = (float)($d['DiemGiuaKy'] ?? 0);
                    $ck = (float)($d['DiemCuoiKy'] ?? 0);
                    if ($gk > 0 || $ck > 0 || $m > 0) {
                        $d['TB'] = round((($m + $p15 + $p45)/3 + $gk*2 + $ck*3)/6, 2);
                        if ($d['TB'] >= 8.0) $d['XepLoai'] = 'Gioi';
                        elseif ($d['TB'] >= 6.5) $d['XepLoai'] = 'Kha';
                        elseif ($d['TB'] >= 5.0) $d['XepLoai'] = 'Dat';
                        else $d['XepLoai'] = 'ChuaDat';
                    }
                }
            }
        }
    }
    unset($hk_data);
?>

<div class="sidebar" id="sidebar">
    <div class="profile-section">
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="rounded-circle border border-4 border-white shadow-lg mb-3" width="90" alt="Phụ huynh">
        <h5 class="fw-bold"><?= htmlspecialchars($data['user_name']) ?></h5>
        <p class="mb-1 opacity-90">Phụ huynh học sinh</p>
        <span class="badge bg-white text-danger px-3 py-2 rounded-pill fw-bold"><?= htmlspecialchars($ten_con) ?></span>
        <p class="mt-2 mb-0 small opacity-75">Lớp <?= htmlspecialchars($ten_lop) ?></p>
    </div>
    <ul class="nav flex-column mt-4 px-3">
        <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-speedometer2 me-3"></i>Tổng Quan</a></li>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/phuhuynh/hoso"> <i class="bi bi-person-lines-fill me-3"></i>Hồ Sơ Con
            </a>
        </li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/thanhtoan/index"><i class="bi bi-credit-card me-3"></i>Thanh Toán Học Phí</a></li>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/phuhuynh/xinphep"> <i class="bi bi-envelope-paper me-3"></i>Xin Phép Vắng
            </a>
        </li>
        <li class="nav-item mt-auto pb-4"><a class="nav-link text-danger fw-bold" href="<?= BASE_URL ?>/auth/logout"><i class="bi bi-box-arrow-right me-3"></i>Đăng Xuất</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="d-none d-lg-flex justify-content-between align-items-center mb-4 bg-white p-4 rounded-4 shadow-sm">
        <div class="d-flex align-items-center gap-4">
            <img src="https://via.placeholder.com/60x60/dc3545/ffffff?text=PH" class="rounded" alt="Logo">
            <div>
                <h3 class="fw-bold mb-0 text-danger"><?= htmlspecialchars($data['school_name'] ?? 'THPT MANAGER') ?></h3>
                <small class="text-muted">Hệ thống dành cho Phụ huynh</small>
            </div>
        </div>
        <span class="badge bg-danger fs-5 px-4 py-2">HỌC KỲ 1</span>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 d-lg-none bg-white p-3 rounded-3 shadow-sm">
        <button class="btn btn-outline-danger" id="toggleSidebar"><i class="bi bi-list fs-4"></i></button>
        <span class="fw-bold text-danger">PHỤ HUYNH</span>
    </div>

    <div class="alert border-0 text-white p-4 mb-4 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #dc3545, #e74c3c);">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="fw-bold mb-1">Xin chào <?= htmlspecialchars($data['user_name']) ?>!</h4>
                <p class="mb-0 opacity-90">Đang xem kết quả học tập của con: <strong><?= htmlspecialchars($ten_con) ?></strong></p>
            </div>
            <i class="bi bi-heart-fill fs-1 opacity-25"></i>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-4">
            <div class="stat-card text-center" onclick="location.href='<?= BASE_URL ?>/thanhtoan/index'">
                <i class="bi bi-credit-card fs-1 text-danger mb-3"></i>
                <h5 class="fw-bold text-danger">Hóa Đơn Chưa Trả</h5>
                <h2 class="fw-bold text-danger mb-0"><?= $hoa_don_count ?></h2>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="stat-card text-center">
                <i class="bi bi-envelope-paper fs-1 text-warning mb-3"></i>
                <h5 class="fw-bold text-warning">Phiếu Chờ Duyệt</h5>
                <h2 class="fw-bold text-warning mb-0"><?= $phieu_vang_count ?></h2>
            </div>
        </div>
        <div class="col-md-12 col-lg-4">
            <div class="stat-card text-center">
                <i class="bi bi-graph-up-arrow fs-1 text-success mb-3"></i>
                <h5 class="fw-bold text-success">Điểm Trung Bình Chung</h5>
                <?php 
                    $tong_diem = 0; $so_mon = 0;
                    if (!empty($bang_diem) && is_array($bang_diem)) {
                        foreach ($bang_diem as $mon => $hoc_ky_data) {
                            if (is_array($hoc_ky_data)) {
                                foreach ($hoc_ky_data as $hk => $d) {
                                    if (isset($d['TB']) && $d['TB'] > 0) {
                                        $tong_diem += $d['TB']; $so_mon++;
                                    }
                                }
                            }
                        }
                    }
                    $dtb_chung = $so_mon > 0 ? round($tong_diem / $so_mon, 2) : '--';
                ?>
                <h2 class="fw-bold text-success mb-0"><?= $dtb_chung ?></h2>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="chart-box">
                <h5 class="fw-bold text-danger mb-4"><i class="bi bi-bar-chart-fill me-2"></i>Biểu Đồ Điểm Các Môn</h5>
                <canvas id="gradeChart" height="100"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-box text-center p-5">
                <img src="https://cdn-icons-png.flaticon.com/512/3767/3767088.png" width="120" class="mb-3 opacity-75">
                <h5 class="fw-bold">Theo dõi sát sao</h5>
                <p class="text-muted small">Hệ thống cập nhật điểm số realtime.</p>
            </div>
        </div>
    </div>

    <div class="chart-box">
        <h5 class="fw-bold text-danger mb-4"><i class="bi bi-table me-2"></i>Bảng Điểm Chi Tiết</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-danger text-white">
                    <tr>
                        <th>Môn Học</th>
                        <th>Học Kỳ</th>
                        <th>Miệng</th>
                        <th>15 Phút</th>
                        <th>1 Tiết</th>
                        <th>Giữa Kỳ</th>
                        <th>Cuối Kỳ</th>
                        <th class="text-center">TB Môn</th>
                        <th>Xếp Loại</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bang_diem)): ?>
                        <tr><td colspan="9" class="text-center py-5">Chưa có dữ liệu</td></tr>
                    <?php else: ?>
                        <?php foreach ($bang_diem as $mon => $hoc_ky_data): ?>
                            <?php foreach ($hoc_ky_data as $hoc_ky => $d): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($mon) ?></td>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($hoc_ky) ?></span></td>
                                    <td><?= $d['DiemMieng'] ?? '-' ?></td>
                                    <td><?= $d['Diem15Phut'] ?? '-' ?></td>
                                    <td><?= $d['Diem1Tiet'] ?? '-' ?></td>
                                    <td><?= $d['DiemGiuaKy'] ?? '-' ?></td>
                                    <td><?= $d['DiemCuoiKy'] ?? '-' ?></td>
                                    <td class="text-center fw-bold fs-6">
                                        <?php 
                                            $tb = $d['TB'] ?? 0;
                                            $color = $tb >= 8 ? 'text-success' : ($tb >= 5 ? 'text-primary' : 'text-danger');
                                            echo $tb > 0 ? "<span class='$color'>$tb</span>" : '-';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            $xl = $d['XepLoai'] ?? '';
                                            $badges = [
                                                'Gioi' => ['bg-success', 'Giỏi'], 'Kha' => ['bg-primary', 'Khá'],
                                                'Dat' => ['bg-warning text-dark', 'Đạt'], 'ChuaDat' => ['bg-danger', 'Chưa Đạt']
                                            ];
                                            echo isset($badges[$xl]) ? "<span class='badge {$badges[$xl][0]}'>{$badges[$xl][1]}</span>" : '-';
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <footer class="text-center mt-5 text-muted small">© 2025 THPT Manager</footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('toggleSidebar')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });

    const ctx = document.getElementById('gradeChart');
    
    // --- LẤY DỮ LIỆU ĐÃ FIX ĐỂ VẼ CHART ---
    <?php
        $chart_data_arr = [];
        foreach ($bang_diem as $mon => $hoc_ky_data) {
            $tong_tb = 0; $count = 0;
            foreach ($hoc_ky_data as $d) {
                if (isset($d['TB']) && $d['TB'] > 0) {
                    $tong_tb += $d['TB']; $count++;
                }
            }
            if ($count > 0) $chart_data_arr[$mon] = round($tong_tb / $count, 2);
        }
    ?>
    
    const labels = <?= json_encode(array_keys($chart_data_arr)) ?>;
    const data = <?= json_encode(array_values($chart_data_arr)) ?>;

    if (labels.length > 0) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Điểm trung bình',
                    data: data,
                    backgroundColor: '#dc3545',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true, max: 10 } }
            }
        });
    }
</script>
</body>
</html>