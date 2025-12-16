<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GV Chủ Nhiệm | THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Segoe UI', Roboto, sans-serif; background-color: #f0f9f4; }
        
        /* Sidebar & Layout */
        .sidebar { width: 280px; position: fixed; height: 100vh; background: #fff; z-index: 1000; box-shadow: 2px 0 10px rgba(0,0,0,0.05); transition: 0.3s; }
        .profile-section { padding: 25px; text-align: center; border-bottom: 1px dashed #eee; background: linear-gradient(180deg, #d1e7dd 0%, #fff 100%); }
        .nav-link { color: #555; padding: 12px 20px; font-weight: 500; border-radius: 8px; margin: 5px 10px; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { background-color: #d1e7dd; color: #198754; }
        .main-content { margin-left: 280px; padding: 25px; transition: 0.3s; }
        
        /* Cards & Tabs */
        .stat-card { border: none; border-radius: 12px; padding: 25px; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.03); height: 100%; transition: 0.2s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
        
        /* Tab Styles */
        .nav-tabs .nav-link { border: none; color: #6c757d; font-weight: 600; padding: 12px 20px; border-radius: 8px 8px 0 0; }
        .nav-tabs .nav-link.active { background-color: #fff; color: #198754; border-bottom: 3px solid #198754; }
        .tab-content { background: #fff; padding: 25px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.01); border: 1px solid #dee2e6; border-top: none; }

        /* Scrollbar */
        .class-list-container { max-height: 80px; overflow-y: auto; }
        .class-list-container::-webkit-scrollbar { width: 4px; }
        .class-list-container::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }

        @media (max-width: 991px) { .sidebar { transform: translateX(-100%); } .sidebar.show { transform: translateX(0); } .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    <div class="sidebar" id="sidebar">
        <div class="profile-section">
            <img src="https://cdn-icons-png.flaticon.com/512/3429/3429569.png" class="rounded-circle mb-2 border border-3 border-success shadow-sm" width="80" height="80">
            <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($data['user_name']); ?></h6>
            
            <div class="mt-2 d-flex flex-column gap-1 align-items-center">
                <span class="badge bg-success">CN Lớp: <?php echo $data['lop_cn_info']['ten_lop'] ?? 'N/A'; ?></span>
                <span class="badge bg-warning text-dark border border-warning">GV Bộ Môn: <?php echo htmlspecialchars($data['mon_giang_day'] ?? '...'); ?></span>
            </div>
        </div>
        <nav class="nav flex-column mt-3">
            <a class="nav-link active" href="#"><i class="bi bi-grid-fill me-2"></i> Tổng Quan</a>
            <a class="nav-link" href="#lich-day"><i class="bi bi-calendar-check me-2"></i> Lịch Giảng Dạy</a>
            <a class="nav-link" href="<?php echo BASE_URL; ?>/giaovien/diemdanh"><i class="bi bi-person-check me-2"></i> Điểm Danh & Sổ Đầu Bài</a>
            <a class="nav-link" href="<?php echo BASE_URL; ?>/giaovien/baitap"><i class="bi bi-file-earmark-text me-2"></i> Bài Tập & Điểm Số</a>
            <div class="mt-auto border-top pt-3">
                <a class="nav-link text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i> Đăng Xuất</a>
            </div>
        </nav>
    </div>

    <div class="main-content">
        <nav class="navbar navbar-light bg-white rounded-3 shadow-sm mb-4 p-3 d-flex justify-content-between">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-3" id="toggleSidebar"><i class="bi bi-list"></i></button>
                <span class="fw-bold text-uppercase text-success fs-5">
                    <i class="bi bi-buildings-fill me-2"></i>
                    <?php echo isset($_SESSION['school_name']) ? $_SESSION['school_name'] : 'THPT MANAGER'; ?>
                </span>
            </div>
            <div><span class="badge bg-success">HK1 - 2025</span></div>
        </nav>

        <?php if (empty($data['lop_cn_info'])): ?>
            <div class="alert alert-warning text-center">
                <h4><i class="bi bi-exclamation-circle"></i> Chưa phân công chủ nhiệm</h4>
            </div>
        <?php endif; ?>

        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="chunhiem-tab" data-bs-toggle="tab" data-bs-target="#chunhiem" type="button" role="tab"><i class="bi bi-star-fill me-2"></i>Lớp Chủ Nhiệm (<?php echo $data['lop_cn_info']['ten_lop'] ?? '?'; ?>)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="giangday-tab" data-bs-toggle="tab" data-bs-target="#giangday" type="button" role="tab"><i class="bi bi-book-half me-2"></i>Công Tác Giảng Dạy</button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            
            <div class="tab-pane fade show active" id="chunhiem" role="tabpanel">
                <?php if (!empty($data['lop_cn_info'])): ?>
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="stat-card border-start border-4 border-success bg-light">
                                <h6 class="text-muted text-uppercase small fw-bold">Sĩ Số Lớp</h6>
                                <h2 class="fw-bold text-dark mb-0"><?php echo $data['lop_cn_info']['si_so_thuc'] ?? 0; ?></h2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card border-start border-4 border-warning bg-light">
                                <h6 class="text-muted text-uppercase small fw-bold">Tổng Số Vắng</h6>
                                <?php 
                                    $tongVang = 0;
                                    if (!empty($data['ds_hoc_sinh'])) {
                                        foreach ($data['ds_hoc_sinh'] as $hs) $tongVang += $hs['so_buoi_vang'];
                                    }
                                ?>
                                <h2 class="fw-bold text-dark mb-0"><?php echo $tongVang; ?></h2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card border-start border-4 border-info bg-light">
                                <h6 class="text-muted text-uppercase small fw-bold">Phòng Học</h6>
                                <h2 class="fw-bold text-dark mb-0 fs-4 mt-1"><?php echo $data['lop_cn_info']['ten_lop']; ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-8">
                            <h6 class="fw-bold text-success mb-3"><i class="bi bi-people-fill me-2"></i>Danh Sách Học Sinh</h6>
                            <div class="table-responsive border rounded-3" style="max-height: 400px;">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Họ Tên</th>
                                            <th class="text-center">Vắng</th>
                                            <th class="text-center">Điểm TB</th>
                                            <th>Hạnh Kiểm</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['ds_hoc_sinh'] as $hs): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($hs['ho_ten']); ?></td>
                                            <td class="text-center">
                                                <?php if($hs['so_buoi_vang'] > 0): ?>
                                                    <span class="badge bg-danger rounded-pill"><?php echo $hs['so_buoi_vang']; ?></span>
                                                <?php else: ?> <span class="text-muted">-</span> <?php endif; ?>
                                            </td>
                                            <td class="text-center fw-bold text-primary"><?php echo $hs['diem_tb_hoc_ky'] ?? '--'; ?></td>
                                            <td>
                                                <?php 
                                                    $hk = $hs['xep_loai_hanh_kiem'] ?? 'Chưa xếp';
                                                    $color = ($hk == 'Tốt') ? 'success' : (($hk == 'Khá') ? 'info' : 'warning');
                                                    echo "<span class='badge bg-$color'>$hk</span>";
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <h6 class="fw-bold text-success mb-3">Tỷ Lệ Hạnh Kiểm</h6>
                            <div class="stat-card bg-light" style="height: 300px; display: flex; justify-content: center;">
                                <canvas id="hkChart"></canvas>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted">Không có dữ liệu chủ nhiệm.</p>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="giangday" role="tabpanel">
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="stat-card border-start border-4 border-primary bg-light">
                            <h6 class="text-muted text-uppercase small fw-bold">Lớp Đang Dạy</h6>
                            <div class="d-flex align-items-baseline mb-2">
                                <h2 class="fw-bold text-dark mb-0 me-2"><?php echo $data['lop_day_count'] ?? 0; ?></h2>
                                <span class="text-muted small">lớp</span>
                            </div>
                            <div class="class-list-container">
                                <?php if (!empty($data['ds_lop_day'])): ?>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach($data['ds_lop_day'] as $lop): ?>
                                            <span class="badge bg-primary bg-opacity-25 text-dark border border-primary border-opacity-25"><?php echo $lop; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card border-start border-4 border-warning bg-light">
                            <h6 class="text-muted text-uppercase small fw-bold">Tỷ Lệ Nộp Bài TB</h6>
                            <h2 class="fw-bold text-dark mb-0"><?php echo $data['bai_nop_percent'] ?? 0; ?>%</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card border-start border-4 border-info bg-light">
                            <h6 class="text-muted text-uppercase small fw-bold">Phiên Điểm Danh</h6>
                            <h2 class="fw-bold text-dark mb-0"><?php echo $data['phien_dd_count'] ?? 0; ?></h2>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="stat-card bg-light">
                            <h6 class="fw-bold text-secondary mb-3">Thống Kê Nộp Bài Theo Lớp</h6>
                            <div style="height: 300px;"><canvas id="barChart"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="stat-card bg-light">
                            <h6 class="fw-bold text-secondary mb-3">Tình Hình Điểm Danh</h6>
                            <div style="height: 300px; display: flex; justify-content: center;"><canvas id="pieChart"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>

        </div> </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('toggleSidebar').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // --- DỮ LIỆU TỪ PHP ---
        
        // 1. Chart Hạnh Kiểm (Cho Tab Chủ Nhiệm)
        <?php
            $hkData = $data['chart_hanh_kiem'] ?? [];
            $hkLabels = array_keys($hkData);
            $hkValues = array_values($hkData);
        ?>

        // 2. Chart Nộp Bài (Cho Tab Giảng Dạy)
        <?php
            $nopBaiData = $data['chart_nop_bai'] ?? [];
            $barLabels = []; $barValues = [];
            foreach ($nopBaiData as $item) {
                $barLabels[] = $item['ten_lop'];
                $barValues[] = $item['so_luong_nop'];
            }
        ?>

        // 3. Chart Điểm Danh (Cho Tab Giảng Dạy)
        <?php
            $ddData = $data['chart_diem_danh'] ?? ['CoMat'=>0, 'Vang'=>0];
            $pieValues = [$ddData['CoMat'], $ddData['Vang']];
        ?>

        // --- VẼ BIỂU ĐỒ ---
        
        // Biểu đồ Hạnh Kiểm (Tab 1)
        const ctxHK = document.getElementById('hkChart');
        if (ctxHK) {
            new Chart(ctxHK, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($hkLabels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($hkValues); ?>,
                        backgroundColor: ['#198754', '#0dcaf0', '#ffc107', '#dc3545', '#6c757d'],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom' } } }
            });
        }

        // Biểu đồ Nộp Bài (Tab 2)
        const ctxBar = document.getElementById('barChart');
        if (ctxBar && <?php echo json_encode(!empty($barLabels)); ?>) {
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($barLabels); ?>,
                    datasets: [{
                        label: 'Số bài nộp',
                        data: <?php echo json_encode($barValues); ?>,
                        backgroundColor: '#ffc107', borderRadius: 5, barThickness: 30
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
            });
        }

        // Biểu đồ Điểm Danh (Tab 2)
        const ctxPie = document.getElementById('pieChart');
        if (ctxPie && <?php echo json_encode(array_sum($pieValues) > 0); ?>) {
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ['Có Mặt', 'Vắng'],
                    datasets: [{
                        data: <?php echo json_encode($pieValues); ?>,
                        backgroundColor: ['#198754', '#dc3545'], borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom' } } }
            });
        }
    </script>
</body>
</html>