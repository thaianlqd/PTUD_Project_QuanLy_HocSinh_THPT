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
        :root {
            --primary: #dc3545;
            --bg: #fff5f5;
            --sidebar-width: 300px;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--bg);
            color: #333;
        }
        .sidebar {
            width: var(--sidebar-width);
            position: fixed;
            height: 100vh;
            background: #fff;
            box-shadow: 3px 0 15px rgba(220,53,69,0.1);
            z-index: 1000;
            transition: 0.3s;
        }
        .profile-section {
            padding: 30px 20px;
            text-align: center;
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border-bottom: 4px solid #c82333;
        }
        .nav-link {
            color: #555;
            padding: 14px 20px;
            font-weight: 500;
            margin: 6px 15px;
            border-radius: 10px;
            transition: 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            background-color: #fee2e5;
            color: var(--primary);
            font-weight: 600;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            transition: 0.3s;
        }
        .stat-card {
            border: none;
            border-radius: 16px;
            padding: 25px;
            background: white;
            box-shadow: 0 5px 15px rgba(220,53,69,0.08);
            transition: 0.3s;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(220,53,69,0.15);
        }
        .chart-box {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(220,53,69,0.08);
            height: 100%;
        }
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .sidebar.show { transform: translateX(0); }
        }
    </style>
</head>
<body>

<?php
    $hoc_sinh_info = $data['hoc_sinh_info'] ?? ['ten_con' => 'Chưa liên kết', 'ten_lop' => 'N/A'];
    $ten_con = $hoc_sinh_info['ten_con'] ?? 'Chưa có thông tin';
    $ten_lop = $hoc_sinh_info['ten_lop'] ?? 'N/A';
    $hoa_don_count = $data['hoa_don_count'] ?? 0;
    $phieu_vang_count = $data['phieu_vang_count'] ?? 0;
    $bang_diem = $data['bang_diem'] ?? [];
?>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="profile-section">
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" 
             class="rounded-circle border border-4 border-white shadow-lg mb-3" 
             width="90" alt="Phụ huynh">
        <h5 class="fw-bold"><?= htmlspecialchars($data['user_name']) ?></h5>
        <p class="mb-1 opacity-90">Phụ huynh học sinh</p>
        <span class="badge bg-white text-danger px-3 py-2 rounded-pill fw-bold">
            <?= htmlspecialchars($ten_con) ?>
        </span>
        <p class="mt-2 mb-0 small opacity-75">Lớp <?= htmlspecialchars($ten_lop) ?></p>
    </div>

    <ul class="nav flex-column mt-4 px-3">
        <li class="nav-item">
            <a class="nav-link active" href="#"><i class="bi bi-speedometer2 me-3"></i>Tổng Quan</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#"><i class="bi bi-person-lines-fill me-3"></i>Hồ Sơ Con</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/thanhtoan/index"><i class="bi bi-credit-card me-3"></i>Thanh Toán Học Phí</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#"><i class="bi bi-envelope-paper me-3"></i>Xin Phép Vắng</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#"><i class="bi bi-calendar-event me-3"></i>Lịch Học & Thi</a>
        </li>
        <li class="nav-item mt-auto pb-4">
            <a class="nav-link text-danger fw-bold" href="<?= BASE_URL ?>/auth/logout">
                <i class="bi bi-box-arrow-right me-3"></i>Đăng Xuất
            </a>
        </li>
    </ul>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- DESKTOP HEADER -->
    <div class="d-none d-lg-flex justify-content-between align-items-center mb-4 bg-white p-4 rounded-4 shadow-sm">
        <div class="d-flex align-items-center gap-4">
            <img src="https://via.placeholder.com/60x60/dc3545/ffffff?text=PH" class="rounded" alt="Logo">
            <div>
                <h3 class="fw-bold mb-0 text-danger">
                    <?= htmlspecialchars($data['school_name'] ?? 'THPT MANAGER') ?>
                </h3>
                <small class="text-muted">Hệ thống dành cho Phụ huynh • Năm học <?= date('Y') ?>-<?= date('Y')+1 ?></small>
            </div>
        </div>
        <span class="badge bg-danger fs-5 px-4 py-2">HỌC KỲ 1</span>
    </div>

    <!-- MOBILE HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 d-lg-none bg-white p-3 rounded-3 shadow-sm">
        <button class="btn btn-outline-danger" id="toggleSidebar"><i class="bi bi-list fs-4"></i></button>
        <div class="text-center">
            <div class="fw-bold text-danger"><?= htmlspecialchars($data['school_name'] ?? 'THPT MANAGER') ?></div>
            <small class="text-muted">Phụ huynh</small>
        </div>
        <span class="badge bg-danger">HK1</span>
    </div>

    <!-- GREETING -->
    <div class="alert border-0 text-white p-4 mb-4 rounded-4 shadow-sm"
         style="background: linear-gradient(135deg, #dc3545, #e74c3c);">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="fw-bold mb-1">Xin chào <?= htmlspecialchars($data['user_name']) ?>!</h4>
                <p class="mb-0 opacity-90">
                    Chào mừng quý phụ huynh đến với hệ thống quản lý học tập của con:
                    <strong><?= htmlspecialchars($ten_con) ?> - Lớp <?= htmlspecialchars($ten_lop) ?></strong>
                </p>
                <small class="d-block mt-2 opacity-75">
                    <i class="bi bi-building me-1"></i>
                    <?= htmlspecialchars($data['school_name'] ?? 'Trường THPT') ?> • Học kỳ 1 • Năm học <?= date('Y') ?>-<?= date('Y')+1 ?>
                </small>
            </div>
            <i class="bi bi-heart-fill fs-1 opacity-25"></i>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-4">
            <div class="stat-card text-center" onclick="location.href='<?= BASE_URL ?>/thanhtoan/index'">
                <i class="bi bi-credit-card fs-1 text-danger mb-3"></i>
                <h5 class="fw-bold text-danger">Hóa Đơn Chưa Thanh Toán</h5>
                <h2 class="fw-bold text-danger mb-0"><?= $hoa_don_count ?></h2>
                <small class="text-muted">Nhấn để xem chi tiết</small>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="stat-card text-center">
                <i class="bi bi-envelope-paper fs-1 text-warning mb-3"></i>
                <h5 class="fw-bold text-warning">Phiếu Xin Nghỉ Chờ Duyệt</h5>
                <h2 class="fw-bold text-warning mb-0"><?= $phieu_vang_count ?></h2>
                <small class="text-muted">Đang chờ nhà trường duyệt</small>
            </div>
        </div>
        <div class="col-md-12 col-lg-4">
            <div class="stat-card text-center">
                <i class="bi bi-graph-up-arrow fs-1 text-success mb-3"></i>
                <h5 class="fw-bold text-success">Điểm Trung Bình Chung</h5>
                <?php 
                    $tong_diem = 0;
                    $so_mon = 0;
                    
                    if (!empty($bang_diem) && is_array($bang_diem)) {
                        foreach ($bang_diem as $mon => $hoc_ky_data) {
                            if (is_array($hoc_ky_data)) {
                                foreach ($hoc_ky_data as $hk => $d) {
                                    if (isset($d['TB']) && is_numeric($d['TB']) && $d['TB'] > 0) {
                                        $tong_diem += $d['TB'];
                                        $so_mon++;
                                    }
                                }
                            }
                        }
                    }
                    
                    $dtb_chung = $so_mon > 0 ? round($tong_diem / $so_mon, 2) : '--';
                ?>
                <h2 class="fw-bold text-success mb-0"><?= $dtb_chung ?></h2>
                <small class="text-muted">Tính đến thời điểm hiện tại</small>
            </div>
        </div>
    </div>

    <!-- CHART -->
    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="chart-box">
                <h5 class="fw-bold text-danger mb-4"><i class="bi bi-bar-chart-fill me-2"></i>Kết Quả Học Tập Các Môn - <?= htmlspecialchars($ten_con) ?></h5>
                <canvas id="gradeChart" height="100"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-box text-center p-5">
                <img src="https://cdn-icons-png.flaticon.com/512/3767/3767088.png" width="120" class="mb-3 opacity-75" alt="Học tập">
                <h5 class="fw-bold">Theo dõi sát sao</h5>
                <p class="text-muted small">Hệ thống tự động cập nhật điểm số, bài tập và hạnh kiểm của con bạn mỗi ngày.</p>
            </div>
        </div>
    </div>

    <!-- BẢNG ĐIỂM CHI TIẾT -->
    <div class="chart-box">
        <h5 class="fw-bold text-danger mb-4"><i class="bi bi-table me-2"></i>Bảng Điểm Chi Tiết Các Môn</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-danger text-white">
                    <tr>
                        <th>Môn Học</th>
                        <th>Học Kỳ</th>
                        <th>Điểm Miệng</th>
                        <th>15 Phút</th>
                        <th>1 Tiết</th>
                        <th>Giữa Kỳ</th>
                        <th>Cuối Kỳ</th>
                        <th class="text-center"><strong>TB Môn</strong></th>
                        <th>Xếp Loại</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bang_diem)): ?>
                        <tr><td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                            Chưa có dữ liệu điểm số
                        </td></tr>
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
                                    <td class="text-center">
                                        <?php 
                                            $tb = $d['TB'] ?? 0;
                                            $badge_color = $tb >= 8 ? 'success' : ($tb >= 6.5 ? 'warning' : 'danger');
                                        ?>
                                        <span class="badge bg-<?= $badge_color ?> fs-6 px-3">
                                            <?= number_format($tb, 1) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                            $xep_loai = $d['XepLoai'] ?? 'ChuaXepLoai';
                                            $xep_loai_label = [
                                                'Gioi' => '<span class="badge bg-success">Giỏi</span>',
                                                'Kha' => '<span class="badge bg-primary">Khá</span>',
                                                'Dat' => '<span class="badge bg-warning">Đạt</span>',
                                                'ChuaDat' => '<span class="badge bg-danger">Chưa Đạt</span>',
                                                'ChuaXepLoai' => '<span class="badge bg-secondary">Chưa xếp loại</span>'
                                            ];
                                            echo $xep_loai_label[$xep_loai] ?? '<span class="badge bg-secondary">N/A</span>';
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

    <footer class="text-center mt-5 text-muted small">
        © <?= date("Y") ?> <strong><?= htmlspecialchars($data['school_name'] ?? 'THPT Manager') ?></strong> • Hệ thống dành riêng cho Phụ Huynh
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle sidebar mobile
    document.getElementById('toggleSidebar')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });

    // Biểu đồ điểm
    const ctx = document.getElementById('gradeChart');
    
    <?php
        // Chuẩn bị dữ liệu cho biểu đồ - Tính TB qua tất cả học kỳ
        $chart_data_arr = [];
        foreach ($bang_diem as $mon => $hoc_ky_data) {
            $tong_tb = 0;
            $count = 0;
            foreach ($hoc_ky_data as $hk => $d) {
                if (isset($d['TB']) && $d['TB'] > 0) {
                    $tong_tb += $d['TB'];
                    $count++;
                }
            }
            $chart_data_arr[$mon] = $count > 0 ? round($tong_tb / $count, 2) : 0;
        }
        
        $chart_labels = json_encode(array_keys($chart_data_arr));
        $chart_data = json_encode(array_values($chart_data_arr));
    ?>
    
    const labels = <?= $chart_labels ?>;
    const data = <?= $chart_data ?>;

    if (labels.length > 0) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Điểm trung bình môn',
                    data: data,
                    backgroundColor: '#dc3545',
                    borderColor: '#c82333',
                    borderWidth: 2,
                    borderRadius: 8,
                    barThickness: 35
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 10, ticks: { stepSize: 1 } }
                }
            }
        });
    }
</script>
</body>
</html>