<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Quản Lý THPT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Segoe UI', Roboto, sans-serif; background-color: #f3f4f6; }
        
        /* 1. SIDEBAR THEO VAI TRÒ */
        .sidebar { width: 280px; position: fixed; height: 100vh; background: #fff; z-index: 1000; box-shadow: 4px 0 15px rgba(0,0,0,0.05); transition: 0.3s; }
        
        /* Profile Section: Màu nền thay đổi theo role cao nhất */
        .profile-section { padding: 30px 20px; text-align: center; color: white; }
        
        /* BGH: Xanh Dương Đậm (Quyền lực) */
        .role-bgh .profile-section { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); }
        .role-bgh .nav-link.active { color: #0d6efd; background-color: #e8f2ff; border-left: 4px solid #0d6efd; }
        
        /* GVCN: Xanh Lá (Thân thiện, quản lý học sinh) */
        .role-gvcn .profile-section { background: linear-gradient(135deg, #198754 0%, #0f5132 100%); }
        .role-gvcn .nav-link.active { color: #198754; background-color: #e6f8ed; border-left: 4px solid #198754; }
        
        /* GV Bộ Môn: Cam/Vàng (Năng động) */
        .role-gv .profile-section { background: linear-gradient(135deg, #fd7e14 0%, #b35200 100%); }
        .role-gv .nav-link.active { color: #fd7e14; background-color: #fff4e6; border-left: 4px solid #fd7e14; }

        .nav-link { color: #555; padding: 12px 20px; font-weight: 500; margin: 4px 0; transition: 0.2s; border-left: 4px solid transparent; }
        .nav-link:hover { background-color: #f8f9fa; }
        .nav-link i { width: 25px; display: inline-block; text-align: center; }

        /* Main Content */
        .main-content { margin-left: 280px; padding: 30px; transition: 0.3s; }
        
        /* Tabs Custom */
        .nav-tabs { border-bottom: 2px solid #e9ecef; margin-bottom: 20px; }
        .nav-tabs .nav-link { border: none; font-weight: 600; color: #6c757d; padding: 12px 25px; border-radius: 8px 8px 0 0; }
        /* Active Tab Color theo Role */
        .role-bgh .nav-tabs .nav-link.active { color: #0d6efd; border-bottom: 3px solid #0d6efd; background: transparent; }
        .role-gvcn .nav-tabs .nav-link.active { color: #198754; border-bottom: 3px solid #198754; background: transparent; }
        .role-gv .nav-tabs .nav-link.active { color: #fd7e14; border-bottom: 3px solid #fd7e14; background: transparent; }

        /* Cards */
        .stat-card { border: none; border-radius: 15px; padding: 25px; height: 100%; box-shadow: 0 5px 15px rgba(0,0,0,0.03); transition: 0.3s; background: white; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); }
        
        .badge-role { font-size: 0.75rem; padding: 6px 12px; border-radius: 20px; background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); margin-top: 5px; display: inline-block; }

        @media (max-width: 991px) { .sidebar { transform: translateX(-100%); } .sidebar.show { transform: translateX(0); } .main-content { margin-left: 0; } }
    </style>
</head>
<?php
    // Xác định class CSS cho body/sidebar dựa trên quyền cao nhất
    $bodyClass = 'role-gv'; // Mặc định
    if ($data['is_bgh']) $bodyClass = 'role-bgh';
    elseif ($data['is_gvcn']) $bodyClass = 'role-gvcn';
?>
<body class="<?php echo $bodyClass; ?>">

    <div class="sidebar" id="sidebar">
        <div class="profile-section">
            <div class="position-relative d-inline-block mb-3">
                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="rounded-circle border border-3 border-white shadow-sm" width="90">
                <span class="position-absolute bottom-0 end-0 bg-white text-dark rounded-circle p-1 shadow-sm" style="width:30px;height:30px; display:flex; align-items:center; justify-content:center;">
                    <?php if($data['is_bgh']): ?><i class="bi bi-bank2 text-primary"></i>
                    <?php elseif($data['is_gvcn']): ?><i class="bi bi-star-fill text-success"></i>
                    <?php else: ?><i class="bi bi-book-half text-warning"></i><?php endif; ?>
                </span>
            </div>
            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($data['user_name']); ?></h6>
            
            <div class="d-flex flex-column gap-1 align-items-center mt-2">
                <?php if($data['is_bgh']): ?>
                    <span class="badge-role"><i class="bi bi-shield-check me-1"></i>Ban Giám Hiệu</span>
                <?php endif; ?>
                <?php if($data['is_gvcn']): ?>
                    <span class="badge-role"><i class="bi bi-people-fill me-1"></i>CN Lớp <?php echo $data['cn_info']['ten_lop']; ?></span>
                <?php endif; ?>
                <span class="badge-role"><i class="bi bi-briefcase-fill me-1"></i>GV: <?php echo htmlspecialchars($data['gd_mon']); ?></span>
            </div>
        </div>

        <nav class="nav flex-column mt-3 px-2">
            <a class="nav-link active" href="#"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
            
            <?php if($data['is_bgh']): ?>
                <div class="text-uppercase small text-muted fw-bold mt-3 mb-1 ms-3" style="font-size: 0.75rem;">Quản Lý Trường</div>
                <a class="nav-link" href="<?php echo BASE_URL; ?>/bgh/duyetdiem"><i class="bi bi-check2-square"></i> Duyệt Sửa Điểm</a>
                <a class="nav-link" href="#"><i class="bi bi-file-earmark-bar-graph"></i> Báo Cáo Thống Kê</a>
            <?php endif; ?>

            <div class="text-uppercase small text-muted fw-bold mt-3 mb-1 ms-3" style="font-size: 0.75rem;">Giảng Dạy</div>
            <a class="nav-link" href="#lich-day"><i class="bi bi-calendar-week"></i> Lịch Dạy</a>
            <a class="nav-link" href="<?php echo BASE_URL; ?>/giaovien/diemdanh"><i class="bi bi-upc-scan"></i> Điểm Danh</a>
            <a class="nav-link" href="<?php echo BASE_URL; ?>/giaovien/baitap"><i class="bi bi-journal-text"></i> Bài Tập & Điểm</a>

            <div class="mt-auto pt-4 border-top">
                <a class="nav-link text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right"></i> Đăng Xuất</a>
            </div>
        </nav>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-3" id="toggleSidebar"><i class="bi bi-list"></i></button>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <?php echo isset($_SESSION['school_name']) ? $_SESSION['school_name'] : 'THPT MANAGER'; ?>
                    </h5>
                    <small class="text-muted"><i class="bi bi-clock"></i> Học kỳ 1 - Năm học 2025-2026</small>
                </div>
            </div>
            <div class="position-relative me-3 cursor-pointer">
                <i class="bi bi-bell fs-4 text-secondary"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
            </div>
        </div>

        <ul class="nav nav-tabs" id="dashboardTab" role="tablist">
            <?php if($data['is_bgh']): ?>
            <li class="nav-item">
                <button class="nav-link active" id="bgh-tab" data-bs-toggle="tab" data-bs-target="#tab-bgh" type="button"><i class="bi bi-bank2 me-2"></i>Quản Trị Nhà Trường</button>
            </li>
            <?php endif; ?>

            <?php if($data['is_gvcn']): ?>
            <li class="nav-item">
                <button class="nav-link <?php echo (!$data['is_bgh']) ? 'active' : ''; ?>" id="cn-tab" data-bs-toggle="tab" data-bs-target="#tab-cn" type="button"><i class="bi bi-star-fill me-2"></i>Lớp Chủ Nhiệm (<?php echo $data['cn_info']['ten_lop']; ?>)</button>
            </li>
            <?php endif; ?>

            <li class="nav-item">
                <button class="nav-link <?php echo (!$data['is_bgh'] && !$data['is_gvcn']) ? 'active' : ''; ?>" id="gd-tab" data-bs-toggle="tab" data-bs-target="#tab-gd" type="button"><i class="bi bi-book-half me-2"></i>Công Tác Giảng Dạy</button>
            </li>
        </ul>

        <div class="tab-content" id="dashboardTabContent">
            
            <?php if($data['is_bgh']): ?>
            <div class="tab-pane fade show active" id="tab-bgh">
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">Yêu Cầu Sửa Điểm</h6>
                                    <h2 class="mb-0 fw-bold"><?php echo $data['bgh_yeu_cau_count']; ?></h2>
                                </div>
                                <i class="bi bi-envelope-paper fs-1 text-white-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Điểm TB Toàn Trường</h6>
                                    <h2 class="mb-0 fw-bold text-success"><?php echo number_format($data['bgh_diem_tb'], 2); ?></h2>
                                </div>
                                <i class="bi bi-graph-up-arrow fs-1 text-success opacity-25"></i>
                            </div>
                        </div>
                    </div>
                    </div>
                
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="stat-card">
                            <h6 class="fw-bold mb-3 border-start border-4 border-primary ps-2">Thống Kê Điểm Số Các Lớp</h6>
                            <div style="height: 300px;"><canvas id="chartBghBar"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="stat-card">
                            <h6 class="fw-bold mb-3 border-start border-4 border-warning ps-2">Trạng Thái Yêu Cầu</h6>
                            <div style="height: 300px; display:flex; justify-content:center;"><canvas id="chartBghPie"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if($data['is_gvcn']): ?>
            <div class="tab-pane fade <?php echo (!$data['is_bgh']) ? 'show active' : ''; ?>" id="tab-cn">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-success"><i class="bi bi-people-fill me-2"></i>Quản Lý Lớp <?php echo $data['cn_info']['ten_lop']; ?></h5>
                    <button class="btn btn-success btn-sm shadow-sm"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Xuất Báo Cáo Lớp</button>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card border-bottom border-4 border-success">
                            <h6 class="text-muted small">Sĩ Số</h6>
                            <h3 class="fw-bold text-dark"><?php echo $data['cn_info']['si_so_thuc']; ?> <span class="fs-6 text-muted font-weight-normal">hs</span></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card border-bottom border-4 border-warning">
                            <h6 class="text-muted small">Vắng Học (Hôm nay)</h6>
                            <h3 class="fw-bold text-dark">0</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card border-bottom border-4 border-danger">
                            <h6 class="text-muted small">Tổng Vắng (Cả kỳ)</h6>
                            <?php $tongVang = 0; foreach($data['cn_hs_list'] as $hs) $tongVang += $hs['so_buoi_vang']; ?>
                            <h3 class="fw-bold text-dark"><?php echo $tongVang; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="stat-card">
                            <h6 class="fw-bold text-success mb-3">Danh Sách Học Sinh</h6>
                            <div class="table-responsive" style="max-height: 400px;">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Họ Tên</th>
                                            <th class="text-center">Vắng</th>
                                            <th class="text-center">Điểm TB</th>
                                            <th>Hạnh Kiểm</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($data['cn_hs_list'] as $hs): ?>
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
                                                    $bg = ($hk == 'Tốt') ? 'success' : 'warning';
                                                    echo "<span class='badge bg-$bg bg-opacity-75'>$hk</span>";
                                                ?>
                                            </td>
                                            <td class="text-end"><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-three-dots"></i></button></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="stat-card">
                            <h6 class="fw-bold text-success mb-3">Thống Kê Hạnh Kiểm</h6>
                            <div style="height: 300px; display:flex; justify-content:center;"><canvas id="chartCnHk"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="tab-pane fade <?php echo (!$data['is_bgh'] && !$data['is_gvcn']) ? 'show active' : ''; ?>" id="tab-gd">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold" style="color: #fd7e14;"><i class="bi bi-briefcase-fill me-2"></i>Công Tác Giảng Dạy</h5>
                    <a href="<?php echo BASE_URL; ?>/giaovien/baitap" class="btn btn-warning text-dark fw-bold shadow-sm">
                        <i class="bi bi-plus-circle-fill me-2"></i>Giao Bài Tập Mới
                    </a>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="stat-card border-start border-4 border-warning">
                            <h6 class="text-muted small fw-bold">Lớp Đang Dạy</h6>
                            <h2 class="fw-bold text-dark mb-2"><?php echo $data['gd_lop_count']; ?></h2>
                            <div>
                                <?php if(!empty($data['gd_lop_list'])) foreach($data['gd_lop_list'] as $lop): ?>
                                    <span class="badge bg-light text-dark border me-1"><?php echo $lop; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card border-start border-4 border-info">
                            <h6 class="text-muted small fw-bold">Tỷ Lệ Nộp Bài TB</h6>
                            <h2 class="fw-bold text-dark mb-0"><?php echo $data['gd_nopbai_pct']; ?>%</h2>
                            <div class="progress mt-2" style="height: 5px;">
                                <div class="progress-bar bg-info" style="width: <?php echo $data['gd_nopbai_pct']; ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card border-start border-4 border-danger">
                            <h6 class="text-muted small fw-bold">Phiên Điểm Danh</h6>
                            <h2 class="fw-bold text-dark mb-0"><?php echo $data['gd_dd_count']; ?></h2>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="stat-card">
                            <h6 class="fw-bold text-warning mb-3">Tình Hình Nộp Bài Theo Lớp</h6>
                            <div style="height: 300px;"><canvas id="chartGdBar"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="stat-card">
                            <h6 class="fw-bold text-warning mb-3">Điểm Danh Tổng Quát</h6>
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

        // --- CHART JS LOGIC ---

        // 1. Chart BGH
        <?php if($data['is_bgh']): ?>
            <?php 
                $pieBghData = $data['bgh_chart_tron'] ?? [];
                $pieBghValues = [$pieBghData['DaDuyet']??0, $pieBghData['TuChoi']??0, $pieBghData['ChoDuyet']??0];
                
                $barBghData = $data['bgh_chart_cot'] ?? [];
                $lblBgh = []; $valBgh = [];
                foreach($barBghData as $i) { $lblBgh[] = $i['ten_lop']; $valBgh[] = $i['diem_tb']; }
            ?>
            new Chart(document.getElementById('chartBghPie'), {
                type: 'doughnut',
                data: { labels: ['Đã Duyệt', 'Từ Chối', 'Chờ'], datasets: [{ data: <?php echo json_encode($pieBghValues); ?>, backgroundColor: ['#198754', '#dc3545', '#ffc107'] }] },
                options: { responsive: true, cutout: '70%', plugins: { legend: { position: 'bottom' } } }
            });
            new Chart(document.getElementById('chartBghBar'), {
                type: 'bar',
                data: { labels: <?php echo json_encode($lblBgh); ?>, datasets: [{ label: 'Điểm TB', data: <?php echo json_encode($valBgh); ?>, backgroundColor: '#0d6efd', borderRadius: 5 }] },
                options: { scales: { y: { beginAtZero: true, max: 10 } } }
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
                type: 'pie',
                data: { labels: <?php echo json_encode($hkLabels); ?>, datasets: [{ data: <?php echo json_encode($hkValues); ?>, backgroundColor: ['#198754', '#0dcaf0', '#ffc107', '#dc3545', '#6c757d'] }] },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
        <?php endif; ?>

        // 3. Chart Giảng Dạy
        <?php 
            $nopLabels = []; $nopValues = [];
            foreach($data['gd_chart_nop'] as $i) { $nopLabels[] = $i['ten_lop']; $nopValues[] = $i['so_luong_nop']; }
            $ddData = $data['gd_chart_dd'] ?? ['CoMat'=>0, 'Vang'=>0];
        ?>
        new Chart(document.getElementById('chartGdBar'), {
            type: 'bar',
            data: { labels: <?php echo json_encode($nopLabels); ?>, datasets: [{ label: 'Bài nộp', data: <?php echo json_encode($nopValues); ?>, backgroundColor: '#fd7e14', borderRadius: 5 }] },
            options: { scales: { y: { beginAtZero: true } } }
        });
        new Chart(document.getElementById('chartGdPie'), {
            type: 'doughnut',
            data: { labels: ['Có Mặt', 'Vắng'], datasets: [{ data: [<?php echo $ddData['CoMat']; ?>, <?php echo $ddData['Vang']; ?>], backgroundColor: ['#20c997', '#dc3545'] }] },
            options: { responsive: true, cutout: '70%', plugins: { legend: { position: 'bottom' } } }
        });
    </script>
</body>
</html>