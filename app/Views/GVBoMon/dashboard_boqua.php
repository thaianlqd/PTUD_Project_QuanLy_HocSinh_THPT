<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Giáo Viên Bộ Môn | THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Segoe UI', Roboto, sans-serif; background-color: #f8f9fa; }
        
        /* Sidebar & Layout */
        .sidebar { width: 280px; position: fixed; height: 100vh; background: #fff; z-index: 1000; box-shadow: 2px 0 10px rgba(0,0,0,0.05); transition: 0.3s; }
        .profile-section { padding: 25px; text-align: center; border-bottom: 1px dashed #eee; background: linear-gradient(180deg, #fff3cd 0%, #fff 100%); }
        .nav-link { color: #555; padding: 12px 20px; font-weight: 500; border-radius: 8px; margin: 5px 10px; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { background-color: #fff3cd; color: #856404; }
        .main-content { margin-left: 280px; padding: 25px; transition: 0.3s; }
        
        /* Navbar */
        .top-navbar { background: #fff; padding: 15px 25px; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.02); display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .school-brand { font-weight: 800; font-size: 1.1rem; color: #856404; text-transform: uppercase; }

        /* Cards */
        .stat-card { border: none; border-radius: 12px; padding: 25px; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.03); height: 100%; transition: 0.2s; display: flex; flex-direction: column; justify-content: center; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
        
        /* Custom Scrollbar cho list lớp */
        .class-list-container { max-height: 80px; overflow-y: auto; margin-top: 10px; padding-right: 5px; }
        .class-list-container::-webkit-scrollbar { width: 4px; }
        .class-list-container::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }

        /* Responsive */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

    <div class="sidebar" id="sidebar">
        <div class="profile-section">
            <img src="https://cdn-icons-png.flaticon.com/512/1995/1995574.png" class="rounded-circle mb-2 border border-3 border-warning shadow-sm" width="80" height="80">
            <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($data['user_name']); ?></h6>
            
            <div class="d-inline-block bg-white border border-warning rounded-pill px-3 py-1 mt-1 shadow-sm">
                <small class="text-warning fw-bold text-uppercase">
                    <i class="bi bi-book me-1"></i> 
                    <?php echo htmlspecialchars($data['mon_giang_day'] ?? 'Bộ Môn'); ?>
                </small>
            </div>
        </div>
        <nav class="nav flex-column mt-3">
            <a class="nav-link active" href="#"><i class="bi bi-grid-fill me-2"></i> Tổng Quan</a>
            <a class="nav-link" href="#lich-day"><i class="bi bi-calendar-check me-2"></i> Lịch Giảng Dạy</a>
            <a class="nav-link" href="<?php echo BASE_URL; ?>/giaovien/diemdanh"><i class="bi bi-person-check me-2"></i> Quản Lý Điểm Danh</a>
            <a class="nav-link" href="<?php echo BASE_URL; ?>/giaovien/baitap"><i class="bi bi-file-earmark-text me-2"></i> Bài Tập & Điểm Số</a>
            <div class="mt-auto border-top pt-3">
                <a class="nav-link text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i> Đăng Xuất</a>
            </div>
        </nav>
    </div>

    <div class="main-content">
        <div class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-3" id="toggleSidebar"><i class="bi bi-list"></i></button>
                <span class="school-brand">
                    <i class="bi bi-buildings-fill me-2 text-warning"></i>
                    <?php echo isset($_SESSION['school_name']) ? $_SESSION['school_name'] : 'THPT MANAGER'; ?>
                </span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-sm-block">
                    <span class="d-block small fw-bold text-muted">HK1 - Năm học 2025-2026</span>
                </div>
                <div class="position-relative">
                    <i class="bi bi-bell-fill fs-5 text-secondary"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                </div>
            </div>
        </div>

        <div class="alert alert-warning border-0 text-dark d-flex align-items-center p-4 mb-4 rounded-4" style="background: linear-gradient(45deg, #ffc107, #ffdb4d);">
            <div class="me-auto">
                <h4 class="fw-bold mb-1">Xin chào, <?php echo htmlspecialchars($data['user_name']); ?>! 🍎</h4>
                <p class="mb-0 opacity-75">Chúc Thầy/Cô một ngày giảng dạy hiệu quả với bộ môn <b><?php echo htmlspecialchars($data['mon_giang_day'] ?? ''); ?></b>.</p>
            </div>
            <i class="bi bi-book-half fs-1 opacity-50"></i>
        </div>

        <div class="row g-4 mb-4">
            
            <div class="col-md-4">
                <div class="stat-card border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="w-100">
                            <h6 class="text-muted text-uppercase small fw-bold">Lớp Đang Phụ Trách</h6>
                            <div class="d-flex align-items-baseline mb-2">
                                <h2 class="fw-bold text-dark mb-0 me-2"><?php echo $data['lop_day_count'] ?? 0; ?></h2>
                                <span class="text-muted small">lớp</span>
                            </div>
                            
                            <div class="class-list-container">
                                <?php if (!empty($data['ds_lop_day'])): ?>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach($data['ds_lop_day'] as $lop): ?>
                                            <span class="badge bg-warning bg-opacity-25 text-dark border border-warning border-opacity-25">
                                                <?php echo htmlspecialchars($lop); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <small class="text-muted fst-italic">Chưa có lớp</small>
                                <?php endif; ?>
                            </div>

                        </div>
                        <div class="fs-1 text-warning opacity-25"><i class="bi bi-easel"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center h-100">
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold">Tỷ Lệ Nộp Bài TB</h6>
                            <h2 class="fw-bold text-dark mb-0"><?php echo $data['bai_nop_percent'] ?? 0; ?>%</h2>
                            <small class="text-success"><i class="bi bi-arrow-up-short"></i> Tốt</small>
                        </div>
                        <div class="fs-1 text-success opacity-25"><i class="bi bi-check2-circle"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card border-start border-4 border-info">
                    <div class="d-flex justify-content-between align-items-center h-100">
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold">Phiên Điểm Danh</h6>
                            <h2 class="fw-bold text-dark mb-0"><?php echo $data['phien_dd_count'] ?? 0; ?></h2>
                            <small class="text-muted">Trong học kỳ này</small>
                        </div>
                        <div class="fs-1 text-info opacity-25"><i class="bi bi-clock-history"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="stat-card">
                    <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-bar-chart-line-fill me-2 text-primary"></i>Thống Kê Nộp Bài Theo Lớp</h6>
                    <div style="height: 300px;">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="stat-card">
                    <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-pie-chart-fill me-2 text-warning"></i>Tình Hình Điểm Danh</h6>
                    <div style="height: 300px; display: flex; justify-content: center;">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Sidebar
        document.getElementById('toggleSidebar').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // --- NHẬN DỮ LIỆU TỪ PHP ---
        <?php
            // 1. Data Biểu đồ Cột (Số bài nộp theo lớp)
            $nopBaiData = $data['chart_nop_bai'] ?? [];
            $labels = []; $values = [];
            foreach ($nopBaiData as $item) {
                $labels[] = $item['ten_lop'];
                $values[] = $item['so_luong_nop'];
            }

            // 2. Data Biểu đồ Tròn (Điểm danh: Có mặt vs Vắng)
            $ddData = $data['chart_diem_danh'] ?? ['CoMat'=>0, 'Vang'=>0];
            $pieValues = [$ddData['CoMat'], $ddData['Vang']];
        ?>

        const barLabels = <?php echo json_encode($labels); ?>;
        const barValues = <?php echo json_encode($values); ?>;
        const pieValues = <?php echo json_encode($pieValues); ?>;

        // --- VẼ BIỂU ĐỒ ---
        
        // 1. Bar Chart
        const ctxBar = document.getElementById('barChart');
        if (barLabels.length > 0) {
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: barLabels,
                    datasets: [{
                        label: 'Số bài đã nộp',
                        data: barValues,
                        backgroundColor: '#ffc107',
                        borderRadius: 6,
                        barThickness: 40
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4] } }, x: { grid: { display: false } } }
                }
            });
        } else {
            ctxBar.parentNode.innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted"><i class="bi bi-inbox fs-1 opacity-25"></i><p class="mt-2 small">Chưa có bài tập nào được giao</p></div>';
        }

        // 2. Pie Chart
        const ctxPie = document.getElementById('pieChart');
        if (pieValues.some(x => x > 0)) {
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ['Có Mặt', 'Vắng'],
                    datasets: [{
                        data: pieValues,
                        backgroundColor: ['#198754', '#dc3545'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } }
                }
            });
        } else {
            ctxPie.parentNode.innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted"><i class="bi bi-calendar-x fs-1 opacity-25"></i><p class="mt-2 small">Chưa có dữ liệu điểm danh</p></div>';
        }
    </script>
</body>
</html>