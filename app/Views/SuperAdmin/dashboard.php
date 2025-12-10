<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sở Giáo Dục | THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary-color: #003366; /* Màu xanh đậm của Sở GD */
            --secondary-color: #f8f9fa;
            --text-color: #333;
            --sidebar-width: 260px;
            --sidebar-bg: #fff;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f2f5;
            color: var(--text-color);
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--sidebar-bg);
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        .profile-section {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid #eee;
            background: linear-gradient(180deg, rgba(0,51,102,0.05) 0%, rgba(255,255,255,0) 100%);
        }

        .profile-section img {
            border: 3px solid var(--primary-color);
            padding: 3px;
            margin-bottom: 15px;
        }

        .profile-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
            font-size: 0.85rem;
        }

        .profile-stats .stat-item {
            display: flex;
            flex-direction: column;
        }

        .nav-flex-column {
            padding: 20px 10px;
            list-style: none;
            overflow-y: auto;
        }

        .nav-link {
            color: #555;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.2s;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .nav-link i {
            margin-right: 12px;
            font-size: 1.2rem;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(0, 51, 102, 0.1);
            color: var(--primary-color);
        }

        /* Main Content Styling */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px 30px;
            transition: all 0.3s ease;
        }

        .navbar-top {
            background-color: #fff;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            margin-bottom: 30px;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        .stat-card {
            padding: 25px;
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            background: #fff;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: block;
        }

        .chart-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow: hidden;
            background: #fff;
            height: 100%;
        }

        .chart-card .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
            font-weight: 600;
        }
        
        .chart-container {
            position: relative; 
            height: 300px; 
            width: 100%; 
            padding: 10px;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
        
        /* Table Styles */
        .table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            color: #666;
        }
    </style>
</head>
<body>
    
    <div class="sidebar" id="sidebar">
        <div class="profile-section">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($data['user_name'] ?? 'Super Admin'); ?>&background=003366&color=fff&size=128&bold=true&rounded=true" alt="Profile" class="rounded-circle shadow-sm" width="80">
            <h5 class="fw-bold mt-2 text-dark"><?php echo htmlspecialchars($data['user_name'] ?? 'Quản Trị Viên'); ?></h5>
            <p class="text-muted mb-1"><span class="badge bg-danger">SUPER ADMIN</span></p>
            <p class="small text-muted">Sở Giáo Dục & Đào Tạo</p>
            
            <div class="profile-stats mt-3">
                <div class="stat-item">
                    <small class="text-muted">Tổng User</small>
                    <strong class="text-primary"><?php echo number_format($data['tk_count'] ?? 0); ?></strong>
                </div>
                <div class="stat-item">
                    <small class="text-muted">Học Sinh</small>
                    <strong class="text-primary"><?php echo number_format($data['hs_count'] ?? 0); ?></strong>
                </div>
            </div>
        </div>

        <ul class="nav nav-flex-column">
            <li class="nav-item"><a class="nav-link active" href="<?php echo BASE_URL; ?>/dashboard"><i class="bi bi-speedometer2"></i>Tổng Quan Hệ Thống</a></li>
            
            <li class="nav-header text-uppercase small text-muted ms-3 mt-3 mb-1">Quản Lý Đào Tạo</li>
            
            <li class="nav-item">
                <a class="nav-link text-primary fw-bold bg-light" href="<?php echo BASE_URL; ?>/quanlytuyensinh">
                    <i class="bi bi-mortarboard-fill"></i>Tuyển Sinh 2025
                </a>
            </li>
            
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-bank"></i>Quản Lý Trường Học</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-journal-bookmark"></i>Chương Trình Khung</a></li>
            
            <li class="nav-header text-uppercase small text-muted ms-3 mt-3 mb-1">Hệ Thống</li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/quantri/quanlytaikhoan"><i class="bi bi-people-fill"></i>Tài Khoản Toàn Tỉnh</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-bar-chart-line"></i>Báo Cáo Thống Kê</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-gear"></i>Cấu Hình Hệ Thống</a></li>
            
            <li class="nav-item mt-auto"><a class="nav-link text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right"></i>Đăng Xuất</a></li>
        </ul>
    </div>

    <div class="main-content">
        <nav class="navbar-top d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary d-lg-none" id="toggleSidebar"><i class="bi bi-list"></i></button>
                <div class="d-flex flex-column">
                    <span class="navbar-brand mb-0 h1">SỞ GIÁO DỤC & ĐÀO TẠO</span>
                    <small class="text-muted">Cổng thông tin quản lý giáo dục tập trung</small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                 <div class="d-none d-md-block me-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" placeholder="Tra cứu hồ sơ/trường...">
                    </div>
                </div>

                <div class="position-relative me-2">
                    <a href="#" class="text-secondary"><i class="bi bi-bell fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                    </a>
                </div>
                
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name=Admin&background=003366&color=fff&size=32&rounded=true" class="rounded-circle me-2" width="32">
                        <span class="d-none d-sm-inline fw-medium">Super Admin</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        <li><a class="dropdown-item" href="#">Hồ sơ cá nhân</a></li>
                        <li><a class="dropdown-item" href="#">Cài đặt</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/auth/logout">Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="row g-4 mb-5">
            <div class="col-lg-3 col-md-6">
                <div class="card stat-card border-start border-5 border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2">Tổng Tài Khoản</h6>
                            <h2 class="mb-0 fw-bold text-primary"><?php echo number_format($data['tk_count'] ?? 0); ?></h2>
                        </div>
                        <i class="bi bi-people text-primary opacity-25" style="font-size: 3rem;"></i>
                    </div>
                    <small class="text-success mt-3 d-block"><i class="bi bi-arrow-up-short"></i> Hoạt động trên toàn hệ thống</small>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card stat-card border-start border-5 border-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2">Học Sinh Đang Học</h6>
                            <h2 class="mb-0 fw-bold text-success"><?php echo number_format($data['hs_count'] ?? 0); ?></h2>
                        </div>
                        <i class="bi bi-mortarboard text-success opacity-25" style="font-size: 3rem;"></i>
                    </div>
                    <small class="text-muted mt-3 d-block">Niên khóa 2025-2026</small>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card stat-card border-start border-5 border-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2">Tổng Lớp Học</h6>
                            <h2 class="mb-0 fw-bold text-warning"><?php echo number_format($data['lop_count'] ?? 0); ?></h2>
                        </div>
                        <i class="bi bi-grid-3x3-gap text-warning opacity-25" style="font-size: 3rem;"></i>
                    </div>
                    <small class="text-muted mt-3 d-block">Phân bổ tại các trường</small>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card stat-card border-start border-5 border-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2">Trường Trực Thuộc</h6>
                            <h2 class="mb-0 fw-bold text-info">
                                <?php echo number_format($data['truong_count'] ?? 0); ?>
                            </h2>
                        </div>
                        <i class="bi bi-buildings text-info opacity-25" style="font-size: 3rem;"></i>
                    </div>
                    <small class="text-info mt-3 d-block fw-bold"><i class="bi bi-eye"></i> Xem danh sách trường</small>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-5">
                <div class="card chart-card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Cơ Cấu Nhân Sự Toàn Tỉnh</h6>
                        <button class="btn btn-sm btn-light"><i class="bi bi-three-dots"></i></button>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="pieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card chart-card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Thống Kê Sĩ Số Theo Khối (Tổng hợp các trường)</h6>
                        <select class="form-select form-select-sm w-auto">
                            <option>Năm nay</option>
                            <option>Năm ngoái</option>
                        </select>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold py-3">Chức Năng Quản Trị Hệ Thống</div>
                    <div class="list-group list-group-flush">
                        
                        <a href="<?php echo BASE_URL; ?>/quanlytuyensinh" class="list-group-item list-group-item-action py-3 d-flex align-items-center bg-primary bg-opacity-10 border-bottom-0">
                            <div class="bg-primary text-white p-2 rounded me-3 shadow-sm">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-primary fw-bold">Tuyển Sinh Lớp 10</h6>
                                <small class="text-muted">Nhập điểm, xét tuyển & công bố</small>
                            </div>
                            <i class="bi bi-chevron-right ms-auto text-primary"></i>
                        </a>

                        <a href="#" class="list-group-item list-group-item-action py-3 d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 p-2 rounded me-3 text-primary"><i class="bi bi-plus-circle-fill"></i></div>
                            <div>
                                <h6 class="mb-0 text-dark">Thêm Trường Mới</h6>
                                <small class="text-muted">Cấp phép hoạt động trường mới</small>
                            </div>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/quantri/quanlytaikhoan" class="list-group-item list-group-item-action py-3 d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 p-2 rounded me-3 text-success"><i class="bi bi-person-plus-fill"></i></div>
                            <div>
                                <h6 class="mb-0 text-dark">Tạo Tài Khoản Admin Trường</h6>
                                <small class="text-muted">Cấp quyền quản lý đơn vị</small>
                            </div>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action py-3 d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 p-2 rounded me-3 text-warning"><i class="bi bi-file-earmark-spreadsheet-fill"></i></div>
                            <div>
                                <h6 class="mb-0 text-dark">Xuất Báo Cáo Tổng Hợp</h6>
                                <small class="text-muted">Dữ liệu tuyển sinh & học tập</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between">
                        <span>Hoạt Động Hệ Thống Mới Nhất</span>
                        <a href="#" class="text-decoration-none small">Xem tất cả</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Người Dùng</th>
                                    <th>Vai Trò</th>
                                    <th>SĐT</th>
                                    <th>Ngày Tham Gia</th>
                                    <th>Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($data['users_list']) && !empty($data['users_list'])): ?>
                                    <?php foreach ($data['users_list'] as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-light rounded-circle text-center pt-1 fw-bold text-primary me-2" style="width:35px; height:35px">
                                                    <?php echo substr($user['username'], 0, 1); ?>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 small fw-bold"><?php echo htmlspecialchars($user['ho_ten']); ?></h6>
                                                    <small class="text-muted" style="font-size: 0.75rem"><?php echo htmlspecialchars($user['username']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($user['vai_tro']); ?></span></td>
                                        <td><small class="text-muted"><?php echo htmlspecialchars($user['so_dien_thoai']); ?></small></td>
                                        <td><small><?php echo date('d/m/Y', strtotime($user['ngay_tao_tai_khoan'])); ?></small></td>
                                        <td>
                                            <?php if ($user['trang_thai'] == 'HoatDong'): ?>
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger">Locked</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">Chưa có dữ liệu mới</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <footer class="mt-5 text-center text-muted small">
            <p>&copy; 2025 SỞ GIÁO DỤC VÀ ĐÀO TẠO. Hệ thống quản lý trường học tập trung.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar mobile
        document.getElementById('toggleSidebar').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Data from Controller
        const tkRoleData = <?php echo json_encode($data['tk_role_data'] ?? []); ?>;
        const siSoKhoiData = <?php echo json_encode($data['si_so_khoi'] ?? []); ?>;

        // --- Pie Chart (Cơ cấu nhân sự) ---
        const pieCtx = document.getElementById('pieChart');
        if (pieCtx) {
            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(tkRoleData),
                    datasets: [{
                        data: Object.values(tkRoleData),
                        backgroundColor: [
                            '#003366', // Primary Dark
                            '#0d6efd', // Blue
                            '#198754', // Green
                            '#ffc107', // Yellow
                            '#0dcaf0', // Cyan
                            '#dc3545'  // Red
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } }
                    },
                    cutout: '70%' // Làm rỗng ruột
                }
            });
        }

        // --- Bar Chart (Sĩ số khối) ---
        const barCtx = document.getElementById('barChart');
        if (barCtx) {
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(siSoKhoiData),
                    datasets: [{
                        label: 'Tổng Sĩ Số',
                        data: Object.values(siSoKhoiData),
                        backgroundColor: '#003366',
                        borderRadius: 4,
                        barThickness: 30
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { borderDash: [2, 2] }
                        },
                        x: {
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    </script>
</body>
</html>