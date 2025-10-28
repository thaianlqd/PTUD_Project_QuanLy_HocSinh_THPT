<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Quản Trị | THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #F0F8FF; }
        .sidebar { width: 300px; min-width: 300px; transition: transform 0.3s ease; position: fixed; height: 100vh; overflow-y: auto; z-index: 1000; background: white; box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .main-content { margin-left: 300px; transition: margin-left 0.3s ease; min-height: 100vh; padding: 1rem; }
        .profile-section { height: 200px; padding: 1rem; border-bottom: 1px solid #dee2e6; text-align: center; }
        .nav-link { border-radius: 8px; margin-bottom: 0.25rem; transition: background 0.3s; color: #28a745; }
        .nav-link:hover { background-color: #E8F5E8; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); height: 100%; display: flex; flex-direction: column; }
        .card-body { flex-grow: 1; display: flex; flex-direction: column; justify-content: center; padding: 1.5rem; }
        .row { --bs-gutter-x: 1rem; }
        .chart-container { position: relative; height: 300px; width: 100%; }
        .table { font-size: 0.9rem; }
        .table th, .table td { padding: 0.75rem 0.5rem; vertical-align: middle; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .fade-in { animation: fadeIn 0.5s ease-in; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        /* Sửa layout card cho 6 chức năng */
        @media (max-width: 992px) { 
            .sidebar { transform: translateX(-100%); } 
            .main-content { margin-left: 0; } 
            .sidebar.show { transform: translateX(0); } 
            .func-card { flex: 0 0 50%; max-width: 50%; } /* 2 cột trên tablet */
        }
        @media (max-width: 576px) { 
            .func-card { flex: 0 0 100%; max-width: 100%; } /* 1 cột trên mobile */
            .chart-container { height: 250px; } 
        }
        .modal-dialog { max-width: 600px; }
        .modal-body { max-height: 60vh; overflow-y: auto; }
        /* Style riêng cho link Tuyển sinh */
        .nav-link.tuyen-sinh { color: #0d6efd; } /* Blue */
        .nav-link.tuyen-sinh:hover { background-color: #e7f0ff; }
    </style>
</head>
<body>
    <!-- Sidebar (dynamic name từ session) -->
    <div class="sidebar" id="sidebar">
        <div class="profile-section">
            <img src="https://via.placeholder.com/80x80?text=QT" alt="Profile" class="rounded-circle mb-2" style="width: 80px; height: 80px; border: 3px solid #28a745;">
            <h5 class="fw-bold text-success mb-1"><?php echo htmlspecialchars($data['user_name'] ?? 'Trần Thị B'); ?></h5>
            <p class="text-muted mb-0 small">Mã: QT001 (<?php echo htmlspecialchars($_SESSION['user_id'] ?? 0); ?>)</p>
            <p class="text-muted small">Vai Trò: Quản Trị Viên</p>
            <div class="row g-1 mt-2 text-center">
                <div class="col-4"><small class="text-muted">Tài Khoản</small><br><strong class="text-success fs-6"><?php echo $data['tk_count'] ?? '?'; ?></strong></div>
                <div class="col-4"><small class="text-muted">Lớp</small><br><strong class="text-warning fs-6"><?php echo $data['lop_count'] ?? '?'; ?></strong></div>
                <div class="col-4"><small class="text-muted">HS</small><br><strong class="text-info fs-6"><?php echo $data['hs_count'] ?? '?'; ?></strong></div>
            </div>
        </div>
        <ul class="nav flex-column px-2">
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/dashboard"><i class="bi bi-speedometer2 me-2"></i>Tổng Quan</a></li>
            
            <!-- SỬA LINK 1 -->
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/quantri/quanlytaikhoan"><i class="bi bi-person-gear me-2"></i>Quản Lý Tài Khoản</a></li>
            <!-- HẾT SỬA 1 -->

            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/quantri/xeptkb"><i class="bi bi-calendar-check me-2"></i>Xếp Thời Khóa Biểu</a></li>
            
            <!-- THÊM LINK TUYỂN SINH -->
             <li class="nav-item"><a class="nav-link tuyen-sinh" href="<?php echo BASE_URL; ?>/quantri/quanlytuyensinh"><i class="bi bi-mortarboard me-2"></i>Quản Lý Tuyển Sinh</a></li>
            <!-- HẾT THÊM LINK -->
            
            <li class="nav-item"><a class="nav-link" href="#ql-to-hop-lop"><i class="bi bi-bookmark-star me-2"></i>Tổ Hợp Môn Lớp</a></li>
            
            <!-- SỬA LINK 2 -->
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/quantri/quanlygiaovien"><i class="bi bi-people me-2"></i>Quản Lý GV</a></li>
            <!-- HẾT SỬA 2 -->

            <li class="nav-item"><a class="nav-link" href="#ql-lop-hoc"><i class="bi bi-house-door me-2"></i>Quản Lý Lớp</a></li>
            <li class="nav-item"><a class="nav-link" href="#ql-hoc-sinh"><i class="bi bi-person-badge me-2"></i>Quản Lý HS</a></li>
            <li class="nav-item mt-auto p-3 border-top"><a class="nav-link text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <nav class="navbar navbar-expand-lg bg-white shadow-sm mb-4 rounded-3 p-2">
            <div class="container-fluid">
                <button class="btn btn-outline-success d-lg-none me-2 rounded-pill" id="toggleSidebar">
                    <i class="bi bi-list"></i> Menu
                </button>
                <a class="navbar-brand fw-bold text-success" href="#">THPT Manager - Quản Trị</a>
                <form class="d-flex mx-auto w-50">
                    <input class="form-control rounded-pill me-2 shadow-sm" type="search" placeholder="Tìm tài khoản/lớp..." aria-label="Search">
                    <button class="btn btn-outline-success rounded-pill" type="submit"><i class="bi bi-search"></i></button>
                </form>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3 position-relative">
                        <a href="#" class="nav-link p-2">
                            <i class="bi bi-bell fs-5"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger small">4</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="dropdown-toggle nav-link p-2" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="https://via.placeholder.com/32x32?text=QT" alt="Profile" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                            <span><?php echo htmlspecialchars($data['user_name'] ?? 'Trần Thị B'); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Cài Đặt</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Mật Khẩu</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Overview Stats (dynamic) -->
        <section id="overview" class="mb-5">
            <h2 class="fw-bold text-success mb-4">Tổng Quan Hệ Thống</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-people-fill fs-1 text-success mb-3"></i> <!-- Icon đẹp hơn -->
                            <h5 class="card-title fw-bold text-success">Tài Khoản Người Dùng</h5>
                            <h3 class="fw-bold text-success mb-1"><?php echo $data['tk_count'] ?? '?'; ?></h3>
                            <small class="text-muted">Hoạt động</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-collection fs-1 text-warning mb-3"></i> <!-- Icon đẹp hơn -->
                            <h5 class="card-title fw-bold text-warning">Tổng Số Lớp</h5>
                            <h3 class="fw-bold text-warning mb-1"><?php echo $data['lop_count'] ?? '?'; ?></h3>
                            <small class="text-muted">Năm học 2025-2026</small>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Charts (dynamic) -->
        <section class="mb-5">
            <h3 class="fw-bold text-success mb-4">Biểu Đồ Thống Kê Quản Lý</h3>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold text-success">Tài Khoản Theo Vai Trò</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="chart-container p-3">
                                <canvas id="pieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold text-success">Sĩ Số Học Sinh Theo Khối</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="chart-container p-3">
                                <canvas id="barChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Table Danh Sách Tài Khoản (Dữ liệu từ CSDL) -->
        <section class="mb-5">
            <h3 class="fw-bold text-success mb-4">Danh Sách Tài Khoản Mới</h3>
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-success">
                                <tr>
                                    <th>Tên Đăng Nhập</th>
                                    <th>Họ Tên</th> <!-- Thêm cột Họ Tên -->
                                    <th>SĐT</th> <!-- Thêm cột SĐT -->
                                    <th>Vai Trò</th>
                                    <th>Trạng Thái</th>
                                    <th>Ngày Tạo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Sử dụng vòng lặp PHP để đổ dữ liệu từ $data['users_list'] -->
                                <?php if (isset($data['users_list']) && !empty($data['users_list'])): ?>
                                    <?php foreach ($data['users_list'] as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['ho_ten']); ?></td> <!-- Cột mới -->
                                            <td><?php echo htmlspecialchars($user['so_dien_thoai']); ?></td> <!-- Cột mới -->
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
                                    <tr><td colspan="6" class="text-center">Không có dữ liệu tài khoản mới.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                     <div class="card-footer text-center">
                        <a href="<?php echo BASE_URL; ?>/quantri/quanlytaikhoan" class="btn btn-success">
                            Đi đến trang Quản lý Chi tiết <i class="bi bi-arrow-right-circle-fill ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cards Chức Năng (dynamic links) -->
        <section>
            <h3 class="fw-bold text-success mb-4">Chức Năng Quản Trị</h3>
            <!-- SỬA: Đổi sang 3 cột (col-md-4) để chứa 6 card -->
            <div class="row g-3">
                
                <div class="col-md-4 func-card"> <!-- Sửa col-md-3 -> col-md-4 -->
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-person-gear fs-2 text-success mb-3"></i>
                            <h5 class="card-title fw-bold">Quản Lý Tài Khoản</h5>
                            <p class="card-text text-muted small">Sửa, xóa, phân quyền.</p>
                            <a href="<?php echo BASE_URL; ?>/quantri/quanlytaikhoan" class="btn btn-outline-success mt-auto">Quản Lý</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 func-card"> <!-- Sửa col-md-3 -> col-md-4 -->
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-calendar3-week fs-2 text-success mb-3"></i>
                            <h5 class="card-title fw-bold">Xếp TKB</h5>
                            <p class="card-text text-muted small">Lưới lịch, ràng buộc.</p>
                            <a href="<?php echo BASE_URL; ?>/quantri/xeptkb" class="btn btn-outline-success mt-auto">Xếp</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 func-card"> <!-- Sửa col-md-3 -> col-md-4 -->
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-people fs-2 text-success mb-3"></i>
                            <h5 class="card-title fw-bold">Quản Lý GV</h5>
                            <p class="card-text text-muted small">CRUD GV, phân công.</p>
                            <a href="<?php echo BASE_URL; ?>/quantri/quanlygiaovien" class="btn btn-outline-success mt-auto">Quản Lý</a>
                        </div>
                    </div>
                </div>
                
                <!-- THÊM CARD TUYỂN SINH -->
                <div class="col-md-4 func-card">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-mortarboard fs-2 text-primary mb-3"></i> <!-- Icon Tuyển Sinh -->
                            <h5 class="card-title fw-bold text-primary">Quản Lý Tuyển Sinh</h5>
                            <p class="card-text text-muted small">Nhập điểm, lọc ảo, xét tuyển.</p>
                            <a href="<?php echo BASE_URL; ?>/quantri/quanlytuyensinh" class="btn btn-outline-primary mt-auto">Vào</a>
                        </div>
                    </div>
                </div>
                <!-- HẾT THÊM CARD -->
                
                <div class="col-md-4 func-card"> <!-- Sửa col-md-3 -> col-md-4 -->
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-house-door fs-2 text-success mb-3"></i>
                            <h5 class="card-title fw-bold">Quản Lý Lớp</h5>
                            <p class="card-text text-muted small">CRUD lớp, sĩ số.</p>
                            <a href="#" class="btn btn-outline-success mt-auto" disabled>Quản lý (Sắp có)</a>
                        </div>
                    </div>
                </div>
                
                 <!-- THÊM CARD QUẢN LÝ HS -->
                 <div class="col-md-4 func-card">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-person-badge fs-2 text-success mb-3"></i>
                            <h5 class="card-title fw-bold">Quản Lý HS</h5>
                            <p class="card-text text-muted small">CRUD HS, xếp lớp.</p>
                            <a href="#" class="btn btn-outline-success mt-auto" disabled>Quản lý (Sắp có)</a>
                        </div>
                    </div>
                </div>
                <!-- HẾT THÊM CARD -->
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white shadow-sm mt-5 p-3 text-center">
            <p class="mb-0 text-muted">&copy; <?php echo date("Y"); ?> Hệ Thống Quản Lý Trường THPT.</p>
        </footer>
    </div>

    <!-- Modal (Không dùng cho chức năng này) -->
    <div class="modal fade" id="modalPreview" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold" id="modalTitle">Chi Tiết</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modalBody">Nội dung Quản Trị.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-success" id="modalAction">Lưu</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar
        document.getElementById('toggleSidebar').addEventListener('click', () => document.getElementById('sidebar').classList.toggle('show'));

        // Charts (Sử dụng dữ liệu từ $data)
        const tkRoleData = <?php echo json_encode($data['tk_role_data'] ?? []); ?>;
        const siSoKhoiData = <?php echo json_encode($data['si_so_khoi'] ?? []); ?>;

        // Biểu đồ Pie
        const pieCtx = document.getElementById('pieChart');
        if (pieCtx && Object.keys(tkRoleData).length > 0) {
            new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: Object.keys(tkRoleData),
                    datasets: [{
                        data: Object.values(tkRoleData),
                        backgroundColor: ['#198754', '#0dcaf0', '#ffc107', '#0d6efd', '#6f42c1', '#dc3545']
                    }]
                },
                options: {responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'bottom'}}}
            });
        }

        // Biểu đồ Bar
        const barCtx = document.getElementById('barChart');
         if (barCtx && Object.keys(siSoKhoiData).length > 0) {
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(siSoKhoiData),
                    datasets:[{
                        label: 'Sĩ Số',
                        data: Object.values(siSoKhoiData),
                        backgroundColor: '#28a745'
                    }]
                },
                options: {responsive:true, maintainAspectRatio:false, scales:{y:{beginAtZero:true}}}
            });
        }

    </script>
</body>
</html>

