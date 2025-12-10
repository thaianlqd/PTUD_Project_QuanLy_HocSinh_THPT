<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Quản Trị | THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <link rel="stylesheet" href="../public/css/Quantri/dashboard_quantri.css">
</head>
<body>
    <!-- ... existing code ... -->
    <!-- Sidebar (dynamic name từ session) -->
    <div class="sidebar" id="sidebar">
        <div class="profile-section">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($data['user_name'] ?? 'QT'); ?>&background=0d6efd&color=fff&size=80&bold=true&font-size=0.4&rounded=true" alt="Profile" class="rounded-circle">
            <h5><?php echo htmlspecialchars($data['user_name'] ?? 'Trần Thị B'); ?></h5>
            <p>Mã: QT001 (<?php echo htmlspecialchars($_SESSION['user_id'] ?? 0); ?>)</p>
            <p>Vai Trò: Quản Trị Viên</p>
            <div class="profile-stats">
                <div class="stat-item">
                    <small>Tài Khoản</small>
                    <strong><?php echo $data['tk_count'] ?? '?'; ?></strong>
                </div>
                <div class="stat-item">
                    <small>Lớp</small>
                    <strong><?php echo $data['lop_count'] ?? '?'; ?></strong>
                </div>
                <div class="stat-item">
                    <small>HS</small>
                    <strong><?php echo $data['hs_count'] ?? '?'; ?></strong>
                </div>
            </div>
        </div>
        <ul class="nav nav-flex-column">
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/dashboard"><i class="bi bi-speedometer2"></i>Tổng Quan</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/quantri/quanlytaikhoan"><i class="bi bi-person-gear"></i>Quản Lý Tài Khoản</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/tkb/xeptkb"><i class="bi bi-calendar-check"></i>Xếp Thời Khóa Biểu</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/quantri/quanlygiaovien"><i class="bi bi-people"></i>Quản Lý GV</a></li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/LopHoc">
                    <i class="bi bi-house-door"></i>Quản Lý Lớp
                </a>
            </li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/quantri/quanlyhocsink"><i class="bi bi-person-badge"></i>Quản Lý HS</a></li>
            <li class="nav-item mt-auto"><a class="nav-link text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right"></i>Đăng Xuất</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <nav class="navbar-top">
            <div class="container-fluid">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <button class="btn btn-outline-primary d-lg-none" id="toggleSidebar">
                        <i class="bi bi-list"></i> Menu
                    </button>
                    <a class="navbar-brand" href="#">
                        <?php 
                            // Nếu có tên trường trong session thì hiện, không thì hiện mặc định
                            echo isset($_SESSION['school_name']) ? strtoupper($_SESSION['school_name']) : 'THPT MANAGER'; 
                        ?>
                    </a>
                    <div class="flex-grow-1 mx-3">
                        <form class="d-flex" style="max-width: 400px;">
                            <input class="form-control search-bar me-2" type="search" placeholder="Tìm tài khoản/lớp..." aria-label="Search">
                            <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                        </form>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="position-relative">
                            <a href="#" class="nav-link p-2">
                                <i class="bi bi-bell fs-5"></i>
                                <span class="notification-badge">4</span>
                            </a>
                        </div>
                        <div class="dropdown">
                            <a class="dropdown-toggle nav-link p-2 d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($data['user_name'] ?? 'QT'); ?>&background=0d6efd&color=fff&size=32&bold=true&font-size=0.5&rounded=true" alt="Profile" class="rounded-circle" style="width: 32px; height: 32px;">
                                <span class="d-none d-sm-inline"><?php echo htmlspecialchars($data['user_name'] ?? 'Trần Thị B'); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Cài Đặt</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Mật Khẩu</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Overview Stats -->
        <section id="overview" class="mb-5">
            <h2>Tổng Quan Hệ Thống</h2>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body stat-card">
                            <i class="bi bi-people-fill text-primary"></i>
                            <h5 class="text-primary">Tài Khoản Người Dùng</h5>
                            <h3 class="text-primary"><?php echo $data['tk_count'] ?? '?'; ?></h3>
                            <small>Hoạt động</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body stat-card">
                            <i class="bi bi-collection text-warning"></i>
                            <h5 class="text-warning">Tổng Số Lớp</h5>
                            <h3 class="text-warning"><?php echo $data['lop_count'] ?? '?'; ?></h3>
                            <small>Năm học 2025-2026</small>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Charts -->
        <section class="mb-5">
            <h3>Biểu Đồ Thống Kê Quản Lý</h3>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card chart-card">
                        <div class="card-header">
                            <h6>Tài Khoản Theo Vai Trò</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="chart-container">
                                <canvas id="pieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card chart-card">
                        <div class="card-header">
                            <h6>Sĩ Số Học Sinh Theo Khối</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="chart-container">
                                <canvas id="barChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Table -->
        <section class="mb-5">
            <h3>Danh Sách Tài Khoản Mới</h3>
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Tên Đăng Nhập</th>
                                    <th>Họ Tên</th>
                                    <th>SĐT</th>
                                    <th>Vai Trò</th>
                                    <th>Trạng Thái</th>
                                    <th>Ngày Tạo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($data['users_list']) && !empty($data['users_list'])): ?>
                                    <?php foreach ($data['users_list'] as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['ho_ten']); ?></td>
                                            <td><?php echo htmlspecialchars($user['so_dien_thoai']); ?></td>
                                            <td><?php echo htmlspecialchars($user['vai_tro']); ?></td>
                                            <td>
                                                <?php if ($user['trang_thai'] == 'HoatDong'): ?>
                                                    <span class="badge bg-success">Hoạt Động</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($user['trang_thai']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($user['ngay_tao_tai_khoan'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center p-4">Không có dữ liệu tài khoản mới.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo BASE_URL; ?>/quantri/quanlytaikhoan" class="btn btn-success">
                            Đi đến trang Quản lý Chi tiết <i class="bi bi-arrow-right-circle-fill ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Function Cards -->
        <section>
            <h3>Chức Năng Quản Trị</h3>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 func-card">
                    <div class="card h-100">
                        <div class="card-body stat-card d-flex flex-column">
                            <i class="bi bi-person-gear text-success fs-1 mb-3"></i>
                            <h5 class="fw-bold text-dark">Quản Lý Tài Khoản</h5>
                            <p class="card-text text-muted flex-grow-1">
                                Tạo, chỉnh sửa, xóa tài khoản người dùng. Phân quyền vai trò (Quản Trị, Giáo Viên, Học Sinh, Phụ Huynh). Kích hoạt/Khóa tài khoản.
                            </p>
                            <a href="<?php echo BASE_URL; ?>/quantri/quanlytaikhoan" class="btn btn-outline-success mt-auto align-self-stretch">
                                <i class="bi bi-arrow-right me-2"></i>Quản Lý
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 func-card">
                    <div class="card h-100">
                        <div class="card-body stat-card d-flex flex-column">
                            <i class="bi bi-calendar3-week text-info fs-1 mb-3"></i>
                            <h5 class="fw-bold text-dark">Xếp Thời Khóa Biểu</h5>
                            <p class="card-text text-muted flex-grow-1">
                                Xếp lịch học cho các lớp theo tuần. Sắp xếp giờ học, môn học, giáo viên. Quản lý ràng buộc lịch học.
                            </p>
                            <a href="<?php echo BASE_URL; ?>/tkb/xeptkb" class="btn btn-outline-info mt-auto align-self-stretch">
                                <i class="bi bi-arrow-right me-2"></i>Xếp TKB
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 func-card">
                    <div class="card h-100">
                        <div class="card-body stat-card d-flex flex-column">
                            <i class="bi bi-people text-success fs-1 mb-3"></i>
                            <h5 class="fw-bold text-dark">Quản Lý Giáo Viên</h5>
                            <p class="card-text text-muted flex-grow-1">
                                Thêm, sửa, xóa thông tin giáo viên. Phân công giảng dạy cho các lớp. Quản lý chuyên môn, chủ nhiệm lớp.
                            </p>
                            <a href="<?php echo BASE_URL; ?>/quantri/quanlygiaovien" class="btn btn-outline-success mt-auto align-self-stretch">
                                <i class="bi bi-arrow-right me-2"></i>Quản Lý
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 func-card offset-lg-2">
                    <div class="card h-100">
                        <div class="card-body stat-card d-flex flex-column">
                            <i class="bi bi-house-door text-warning fs-1 mb-3"></i>
                            <h5 class="fw-bold text-dark">Quản Lý Lớp</h5>
                            <p class="card-text text-muted flex-grow-1">
                                Tạo, chỉnh sửa thông tin lớp học. Quản lý sĩ số, khối, năm học. Phân công giáo viên chủ nhiệm.
                            </p>
                            <a href="<?php echo BASE_URL; ?>/LopHoc" class="btn btn-outline-warning mt-auto align-self-stretch">
                                <i class="bi bi-arrow-right me-2"></i>Quản Lý
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 func-card">
                    <div class="card h-100">
                        <div class="card-body stat-card d-flex flex-column">
                            <i class="bi bi-person-badge text-danger fs-1 mb-3"></i>
                            <h5 class="fw-bold text-dark">Quản Lý Học Sinh</h5>
                            <p class="card-text text-muted flex-grow-1">
                                Thêm, sửa, xóa thông tin học sinh. Xếp lớp, chuyển lớp học sinh. Quản lý trạng thái (Đang học, Nghỉ, Chuyển trường).
                            </p>
                            
                            <a href="<?php echo BASE_URL; ?>/quantri/quanlyhocsink" class="btn btn-outline-danger mt-auto align-self-stretch">
                                <i class="bi bi-arrow-right me-2"></i>Quản Lý
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer>
            <p class="mb-0">&copy; <?php echo date("Y"); ?> Hệ Thống Quản Lý Trường THPT. Tất cả quyền được bảo lưu.</p>
        </footer>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalPreview" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--primary); color: #fff;">
                    <h5 class="modal-title fw-bold" id="modalTitle">Chi Tiết</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modalBody">Nội dung Quản Trị.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" id="modalAction">Lưu</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar
        document.getElementById('toggleSidebar').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Charts
        const tkRoleData = <?php echo json_encode($data['tk_role_data'] ?? []); ?>;
        const siSoKhoiData = <?php echo json_encode($data['si_so_khoi'] ?? []); ?>;

        // Pie Chart
        const pieCtx = document.getElementById('pieChart');
        if (pieCtx && Object.keys(tkRoleData).length > 0) {
            new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: Object.keys(tkRoleData),
                    datasets: [{
                        data: Object.values(tkRoleData),
                        backgroundColor: ['#0d6efd', '#198754', '#0dcaf0', '#ffc107', '#0d6efd', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // Bar Chart
        const barCtx = document.getElementById('barChart');
        if (barCtx && Object.keys(siSoKhoiData).length > 0) {
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(siSoKhoiData),
                    datasets: [{
                        label: 'Sĩ Số',
                        data: Object.values(siSoKhoiData),
                        backgroundColor: '#0d6efd'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    </script>
</body>
</html>