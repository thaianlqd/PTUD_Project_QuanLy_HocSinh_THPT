<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Tổng Hợp | THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Segoe UI', Roboto, sans-serif; background-color: #f0f2f5; }
        
        /* Sidebar */
        .sidebar { width: 280px; position: fixed; height: 100vh; background: #fff; z-index: 1000; box-shadow: 2px 0 10px rgba(0,0,0,0.05); }
        .main-content { margin-left: 280px; padding: 25px; transition: 0.3s; }
        
        /* Profile Section Multi-Color */
        .profile-section { padding: 25px; text-align: center; border-bottom: 1px dashed #eee; }
        /* Nếu là BGH thì nền xanh dương */
        .role-bgh { background: linear-gradient(180deg, #e3f2fd 0%, #fff 100%); }
        /* Nếu chỉ là GVCN thì nền xanh lá */
        .role-gvcn { background: linear-gradient(180deg, #d1e7dd 0%, #fff 100%); }
        /* Nếu chỉ là GV bộ môn thì nền vàng */
        .role-gv { background: linear-gradient(180deg, #fff3cd 0%, #fff 100%); }

        .nav-link { color: #555; padding: 12px 20px; font-weight: 500; border-radius: 8px; margin: 5px 10px; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { background-color: #f8f9fa; color: #000; font-weight: bold; }
        
        /* Tabs Styling */
        .nav-tabs .nav-link { border: none; font-weight: 600; color: #6c757d; padding: 12px 25px; }
        .nav-tabs .nav-link.active { background-color: #fff; border-bottom: 3px solid #0d6efd; color: #0d6efd; }
        .tab-content { background: #fff; padding: 25px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); min-height: 500px; }
        
        /* Stats Card */
        .stat-card { border: none; border-radius: 12px; padding: 20px; height: 100%; box-shadow: 0 2px 4px rgba(0,0,0,0.03); transition: 0.2s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
        
        @media (max-width: 991px) { .sidebar { transform: translateX(-100%); } .sidebar.show { transform: translateX(0); } .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    <?php
        // Xác định class màu nền cho profile
        $profileClass = 'role-gv'; // Mặc định vàng
        if ($data['is_bgh']) $profileClass = 'role-bgh'; // Xanh dương
        elseif ($data['is_gvcn']) $profileClass = 'role-gvcn'; // Xanh lá
    ?>
    <div class="sidebar" id="sidebar">
        <div class="profile-section <?php echo $profileClass; ?>">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="rounded-circle mb-2 border border-3 border-white shadow-sm" width="80">
            <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($data['user_name']); ?></h6>
            
            <div class="d-flex flex-column gap-1 align-items-center mt-2">
                <?php if($data['is_bgh']): ?>
                    <span class="badge bg-primary w-100">Ban Giám Hiệu</span>
                <?php endif; ?>
                <?php if($data['is_gvcn']): ?>
                    <span class="badge bg-success w-100">CN Lớp <?php echo $data['cn_info']['ten_lop']; ?></span>
                <?php endif; ?>
                <span class="badge bg-warning text-dark border w-100">GV: <?php echo htmlspecialchars($data['gd_mon']); ?></span>
            </div>
        </div>
        <nav class="nav flex-column mt-3">
            <a class="nav-link active" href="#"><i class="bi bi-grid-fill me-2"></i> Tổng Quan</a>
            
            <?php if($data['is_bgh']): ?>
                <a class="nav-link text-primary" href="<?php echo BASE_URL; ?>/bgh/duyetdiem"><i class="bi bi-pencil-square me-2"></i> Duyệt Điểm (BGH)</a>
            <?php endif; ?>
            
            <a class="nav-link" href="#lich-day"><i class="bi bi-calendar-check me-2"></i> Lịch Giảng Dạy</a>
            <a class="nav-link" href="<?php echo BASE_URL; ?>/giaovien/diemdanh"><i class="bi bi-person-check me-2"></i> Điểm Danh</a>
            <a class="nav-link" href="<?php echo BASE_URL; ?>/giaovien/baitap"><i class="bi bi-file-earmark-text me-2"></i> Bài Tập & Điểm</a>
            
            <div class="mt-auto border-top pt-3">
                <a class="nav-link text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i> Đăng Xuất</a>
            </div>
        </nav>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-3 shadow-sm">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-3" id="toggleSidebar"><i class="bi bi-list"></i></button>
                <span class="fw-bold text-uppercase text-secondary fs-5">
                    <i class="bi bi-buildings-fill me-2"></i>
                    <?php echo isset($_SESSION['school_name']) ? $_SESSION['school_name'] : 'THPT MANAGER'; ?>
                </span>
            </div>
            <span class="badge bg-light text-dark border">HK1 - 2025</span>
        </div>

        <ul class="nav nav-tabs" id="dashboardTab" role="tablist">
            <?php if($data['is_bgh']): ?>
            <li class="nav-item">
                <button class="nav-link active" id="bgh-tab" data-bs-toggle="tab" data-bs-target="#tab-bgh" type="button"><i class="bi bi-bank me-2"></i>Quản Lý Trường</button>
            </li>
            <?php endif; ?>

            <?php if($data['is_gvcn']): ?>
            <li class="nav-item">
                <button class="nav-link <?php echo (!$data['is_bgh']) ? 'active' : ''; ?>" id="cn-tab" data-bs-toggle="tab" data-bs-target="#tab-cn" type="button"><i class="bi bi-star-fill me-2"></i>Lớp Chủ Nhiệm</button>
            </li>
            <?php endif; ?>

            <li class="nav-item">
                <button class="nav-link <?php echo (!$data['is_bgh'] && !$data['is_gvcn']) ? 'active' : ''; ?>" id="gd-tab" data-bs-toggle="tab" data-bs-target="#tab-gd" type="button"><i class="bi bi-book-half me-2"></i>Công Tác Giảng Dạy</button>
            </li>
        </ul>

        <div class="tab-content" id="dashboardTabContent">
            
            <?php if($data['is_bgh']): ?>
            <div class="tab-pane fade show active" id="tab-bgh">
                <div class="alert alert-primary border-0 d-flex align-items-center p-3 mb-3 rounded-3">
                    <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                    <div>Chào mừng Ban Giám Hiệu. Dưới đây là thống kê toàn trường.</div>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="stat-card border-start border-4 border-warning bg-light">
                            <h6 class="text-muted small fw-bold">Yêu Cầu Chờ Duyệt</h6>
                            <h2 class="fw-bold text-dark mb-0"><?php echo $data['bgh_yeu_cau_count']; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card border-start border-4 border-success bg-light">
                            <h6 class="text-muted small fw-bold">Điểm TB Trường</h6>
                            <h2 class="fw-bold text-dark mb-0"><?php echo number_format($data['bgh_diem_tb'], 2); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="stat-card">
                            <h6 class="fw-bold text-primary mb-3">Biểu Đồ Điểm TB Các Lớp</h6>
                            <div style="height: 300px;"><canvas id="chartBghBar"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="stat-card">
                            <h6 class="fw-bold text-primary mb-3">Trạng Thái Yêu Cầu</h6>
                            <div style="height: 300px; display:flex; justify-content:center;"><canvas id="chartBghPie"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if($data['is_gvcn']): ?>
            <div class="tab-pane fade <?php echo (!$data['is_bgh']) ? 'show active' : ''; ?>" id="tab-cn">
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="stat-card border-start border-4 border-success bg-light">
                            <h6 class="text-muted small fw-bold">Lớp Chủ Nhiệm</h6>
                            <h2 class="fw-bold text-dark mb-0"><?php echo $data['cn_info']['ten_lop']; ?></h2>
                            <small>Sĩ số: <?php echo $data['cn_info']['si_so_thuc']; ?></small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card border-start border-4 border-warning bg-light">
                            <h6 class="text-muted small fw-bold">Tổng Vắng</h6>
                            <?php 
                                $tongVang = 0;
                                foreach($data['cn_hs_list'] as $hs) $tongVang += $hs['so_buoi_vang'];
                            ?>
                            <h2 class="fw-bold text-dark mb-0"><?php echo $tongVang; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="stat-card">
                            <h6 class="fw-bold text-success mb-3">Danh Sách Học Sinh Lớp <?php echo $data['cn_info']['ten_lop']; ?></h6>
                            <div class="table-responsive" style="max-height: 300px;">
                                <table class="table table-hover table-sm align-middle">
                                    <thead class="table-light sticky-top"><tr><th>Họ Tên</th><th class="text-center">Vắng</th><th class="text-center">Điểm TB</th><th>Hạnh Kiểm</th></tr></thead>
                                    <tbody>
                                        <?php foreach($data['cn_hs_list'] as $hs): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($hs['ho_ten']); ?></td>
                                            <td class="text-center"><span class="badge bg-danger"><?php echo $hs['so_buoi_vang'] ?: '-'; ?></span></td>
                                            <td class="text-center fw-bold text-primary"><?php echo $hs['diem_tb_hoc_ky'] ?? '--'; ?></td>
                                            <td><?php echo $hs['xep_loai_hanh_kiem'] ?: 'Chưa xếp'; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="stat-card">
                            <h6 class="fw-bold text-success mb-3">Tỷ Lệ Hạnh Kiểm</h6>
                            <div style="height: 300px; display:flex; justify-content:center;"><canvas id="chartCnHk"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="tab-pane fade <?php echo (!$data['is_bgh'] && !$data['is_gvcn']) ? 'show active' : ''; ?>" id="tab-gd">
                
                <div class="d-flex justify-content-end mb-3">
                    <a href="<?php echo BASE_URL; ?>/giaovien/baitap" class="btn btn-primary shadow-sm">
                        <i class="bi bi-plus-circle me-2"></i>Giao Bài Tập Mới
                    </a>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="stat-card border-start border-4 border-primary bg-light">
                            <h6 class="text-muted small fw-bold">Lớp Đang Dạy</h6>
                            <h2 class="fw-bold text-dark mb-0"><?php echo $data['gd_lop_count']; ?></h2>
                            <div class="mt-2">
                                <?php if(!empty($data['gd_lop_list'])) foreach($data['gd_lop_list'] as $lop): ?>
                                    <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 me-1"><?php echo $lop; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card border-start border-4 border-warning bg-light">
                            <h6 class="text-muted small fw-bold">Tỷ Lệ Nộp Bài TB</h6>
                            <h2 class="fw-bold text-dark mb-0"><?php echo $data['gd_nopbai_pct']; ?>%</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card border-start border-4 border-info bg-light">
                            <h6 class="text-muted small fw-bold">Phiên Điểm Danh</h6>
                            <h2 class="fw-bold text-dark mb-0"><?php echo $data['gd_dd_count']; ?></h2>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="stat-card">
                            <h6 class="fw-bold text-primary mb-3">Thống Kê Nộp Bài Theo Lớp</h6>
                            <div style="height: 300px;"><canvas id="chartGdBar"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="stat-card">
                            <h6 class="fw-bold text-primary mb-3">Tình Hình Điểm Danh</h6>
                            <div style="height: 300px; display:flex; justify-content:center;"><canvas id="chartGdPie"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // === CHART LOGIC (Chỉ vẽ nếu có dữ liệu) ===

        // 1. Chart BGH
        <?php if($data['is_bgh']): ?>
            // Pie Chart Yêu Cầu
            <?php 
                $pieBghData = $data['bgh_chart_tron'] ?? [];
                $pieBghValues = [$pieBghData['DaDuyet']??0, $pieBghData['TuChoi']??0, $pieBghData['ChoDuyet']??0];
            ?>
            new Chart(document.getElementById('chartBghPie'), {
                type: 'doughnut',
                data: { labels: ['Đã Duyệt', 'Từ Chối', 'Chờ'], datasets: [{ data: <?php echo json_encode($pieBghValues); ?>, backgroundColor: ['#198754', '#dc3545', '#ffc107'] }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom' } } }
            });

            // Bar Chart Điểm TB
            <?php 
                $barBghData = $data['bgh_chart_cot'] ?? [];
                $lblBgh = []; $valBgh = [];
                foreach($barBghData as $i) { $lblBgh[] = $i['ten_lop']; $valBgh[] = $i['diem_tb']; }
            ?>
            new Chart(document.getElementById('chartBghBar'), {
                type: 'bar',
                data: { labels: <?php echo json_encode($lblBgh); ?>, datasets: [{ label: 'Điểm TB', data: <?php echo json_encode($valBgh); ?>, backgroundColor: '#0d6efd', borderRadius: 5 }] },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 10 } } }
            });
        <?php endif; ?>

        // 2. Chart Chủ Nhiệm
        <?php if($data['is_gvcn']): ?>
            <?php 
                $hkData = $data['cn_chart_hk'] ?? [];
                $hkLabels = array_keys($hkData);
                $hkValues = array_values($hkData);
            ?>
            new Chart(document.getElementById('chartCnHk'), {
                type: 'doughnut',
                data: { labels: <?php echo json_encode($hkLabels); ?>, datasets: [{ data: <?php echo json_encode($hkValues); ?>, backgroundColor: ['#198754', '#0dcaf0', '#ffc107', '#dc3545', '#6c757d'] }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom' } } }
            });
        <?php endif; ?>

        // 3. Chart Giảng Dạy (Luôn có)
        <?php 
            $nopLabels = []; $nopValues = [];
            foreach($data['gd_chart_nop'] as $i) { $nopLabels[] = $i['ten_lop']; $nopValues[] = $i['so_luong_nop']; }
            
            $ddData = $data['gd_chart_dd'] ?? ['CoMat'=>0, 'Vang'=>0];
        ?>
        new Chart(document.getElementById('chartGdBar'), {
            type: 'bar',
            data: { labels: <?php echo json_encode($nopLabels); ?>, datasets: [{ label: 'Bài nộp', data: <?php echo json_encode($nopValues); ?>, backgroundColor: '#ffc107', borderRadius: 5 }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('chartGdPie'), {
            type: 'doughnut',
            data: { labels: ['Có Mặt', 'Vắng'], datasets: [{ data: [<?php echo $ddData['CoMat']; ?>, <?php echo $ddData['Vang']; ?>], backgroundColor: ['#198754', '#dc3545'] }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom' } } }
        });
    </script>
</body>
</html>