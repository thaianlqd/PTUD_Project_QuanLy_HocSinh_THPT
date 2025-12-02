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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #0d6efd;
            --secondary: #6c757d;
            --success: #198754;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #0dcaf0;
            --dark: #1a1a2e;
            --light: #f8f9fa;
            --lighter: #f0f2f5;
            --border: #e9ecef;
        }

        body { 
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: #333;
        }

        /* ========== SIDEBAR ========== */
        .sidebar { 
            width: 280px;
            min-width: 280px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            box-shadow: 4px 0 15px rgba(0,0,0,0.15);
            transition: transform 0.3s ease;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.05);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.3);
        }

        /* <CHANGE> Improved sidebar profile section styling */
        .profile-section { 
            padding: 2rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            background: rgba(13, 110, 253, 0.05);
        }

        .profile-section img {
            width: 70px;
            height: 70px;
            border: 3px solid var(--primary);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
            transition: transform 0.3s ease;
        }

        .profile-section img:hover {
            transform: scale(1.05);
        }

        .profile-section h5 {
            color: #fff;
            margin-top: 0.75rem;
            font-weight: 600;
        }

        .profile-section p {
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
            margin: 0.25rem 0;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .profile-stats .stat-item {
            background: rgba(13, 110, 253, 0.1);
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .profile-stats .stat-item:hover {
            background: rgba(13, 110, 253, 0.2);
            transform: translateY(-2px);
        }

        .profile-stats small {
            color: rgba(255,255,255,0.6);
            display: block;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .profile-stats strong {
            color: var(--primary);
            font-size: 1rem;
            display: block;
            margin-top: 0.25rem;
        }

        /* <CHANGE> Enhanced nav links with better hover effects */
        .nav-link { 
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
            color: rgba(255,255,255,0.75);
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }

        .nav-link:hover { 
            background: rgba(13, 110, 253, 0.2);
            color: #fff;
            transform: translateX(4px);
        }

        .nav-link.active {
            background: var(--primary);
            color: #fff;
        }

        .nav-link.tuyen-sinh {
            color: rgba(255,255,255,0.75);
        }

        .nav-link.tuyen-sinh:hover {
            background: rgba(13, 110, 253, 0.2);
        }

        .nav-link i {
            font-size: 1.1rem;
        }

        .nav-flex-column {
            padding: 0.5rem;
            display: flex;
            flex-direction: column;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content { 
            margin-left: 280px;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
            padding: 2rem 1.5rem;
        }

        /* <CHANGE> Improved navbar styling with better shadow and colors */
        .navbar-top {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
        }

        .navbar-brand {
            font-size: 1.3rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--info));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .search-bar {
            border: 1px solid var(--border);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .search-bar:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        /* ========== CARDS & SECTIONS ========== */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            height: 100%;
            display: flex;
            flex-direction: column;
            background: #fff;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.12);
        }

        .card-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 1.75rem;
        }

        /* <CHANGE> Improved stat cards with gradient icons */
        .stat-card {
            text-align: center;
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }

        .card:hover .stat-card i {
            transform: scale(1.1) rotate(5deg);
        }

        .stat-card h5 {
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-card small {
            color: #6c757d;
            font-size: 0.9rem;
        }

        /* ========== CHART SECTION ========== */
        .chart-card {
            padding: 0;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-bottom: 1px solid var(--border);
            padding: 1.25rem 1.5rem;
        }

        .card-header h6 {
            margin: 0;
            font-weight: 600;
            color: #333;
            font-size: 1rem;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            padding: 1.5rem;
        }

        /* ========== TABLE SECTION ========== */
        .table {
            font-size: 0.95rem;
            margin: 0;
        }

        .table thead {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }

        .table thead th {
            color: #333;
            font-weight: 600;
            padding: 1rem 0.75rem;
            border: none;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            border-color: var(--border);
        }

        .table tbody tr {
            transition: background 0.3s ease;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 500;
            font-size: 0.85rem;
            border-radius: 6px;
        }

        .card-footer {
            background: #f8f9fa;
            border-top: 1px solid var(--border);
            padding: 1.25rem;
            text-align: center;
        }

        /* ========== FUNCTION CARDS ========== */
        .func-card {
            transition: all 0.3s ease;
        }

        .func-card .card-body {
            padding: 2rem 1.5rem;
        }

        .func-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .func-card .card:hover i {
            transform: scale(1.15) rotate(-5deg);
        }

        .func-card h5 {
            font-weight: 700;
            font-size: 1.05rem;
            margin-bottom: 0.5rem;
        }

        .func-card .card-text {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .btn {
            border-radius: 8px;
            padding: 0.6rem 1.2rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .btn-outline-success {
            color: var(--success);
            border-color: var(--success);
        }

        .btn-outline-success:hover {
            background: var(--success);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
        }

        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }

        .btn-success {
            background: var(--success);
            border-color: var(--success);
        }

        .btn-success:hover {
            background: #157347;
            border-color: #157347;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
        }

        /* ========== FOOTER ========== */
        footer {
            background: #fff;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.05);
            border-top: 1px solid var(--border);
            padding: 1.5rem;
            margin-top: 3rem;
            border-radius: 12px;
        }

        footer p {
            color: #6c757d;
            font-size: 0.95rem;
        }

        /* ========== SECTION TITLES ========== */
        section h2, section h3 {
            color: #1a1a2e;
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.75rem;
        }

        section h2::after, section h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--info));
            border-radius: 2px;
        }

        /* ========== RESPONSIVE DESIGN ========== */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                width: 250px;
                min-width: 250px;
            }

            .main-content {
                margin-left: 0;
                padding: 1.5rem 1rem;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .func-card {
                flex: 0 0 50%;
                max-width: 50%;
            }

            .chart-container {
                height: 250px;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 100%;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem 0.75rem;
            }

            .func-card {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .chart-container {
                height: 200px;
            }

            .navbar-top {
                padding: 1rem;
            }

            .search-bar {
                max-width: 100%;
            }

            .table {
                font-size: 0.85rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.6rem 0.4rem;
            }

            section h2, section h3 {
                font-size: 1.3rem;
            }
        }

        /* ========== ANIMATIONS ========== */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        /* <CHANGE> Custom notification badge styling */
        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--danger);
            color: #fff;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <!-- ... existing code ... -->
    <!-- Sidebar (dynamic name từ session) -->
    <div class="sidebar" id="sidebar">
        <div class="profile-section">
            <img src="https://via.placeholder.com/80x80?text=QT" alt="Profile" class="rounded-circle">
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
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/quantri/xeptkb"><i class="bi bi-calendar-check"></i>Xếp Thời Khóa Biểu</a></li>
            <li class="nav-item"><a class="nav-link tuyen-sinh" href="<?php echo BASE_URL; ?>/quantri/quanlytuyensinh"><i class="bi bi-mortarboard"></i>Quản Lý Tuyển Sinh</a></li>
            <li class="nav-item"><a class="nav-link" href="#ql-to-hop-lop"><i class="bi bi-bookmark-star"></i>Tổ Hợp Môn Lớp</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/quantri/quanlygiaovien"><i class="bi bi-people"></i>Quản Lý GV</a></li>
            <li class="nav-item"><a class="nav-link" href="#ql-lop-hoc"><i class="bi bi-house-door"></i>Quản Lý Lớp</a></li>
            <li class="nav-item"><a class="nav-link" href="#ql-hoc-sinh"><i class="bi bi-person-badge"></i>Quản Lý HS</a></li>
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
                    <a class="navbar-brand" href="#">THPT Manager</a>
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
                                <img src="https://via.placeholder.com/32x32?text=QT" alt="Profile" class="rounded-circle" style="width: 32px; height: 32px;">
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
                    <div class="card">
                        <div class="card-body stat-card">
                            <i class="bi bi-person-gear text-success"></i>
                            <h5>Quản Lý Tài Khoản</h5>
                            <p class="card-text">Sửa, xóa, phân quyền.</p>
                            <a href="<?php echo BASE_URL; ?>/quantri/quanlytaikhoan" class="btn btn-outline-success mt-auto">Quản Lý</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 func-card">
                    <div class="card">
                        <div class="card-body stat-card">
                            <i class="bi bi-calendar3-week text-success"></i>
                            <h5>Xếp TKB</h5>
                            <p class="card-text">Lưới lịch, ràng buộc.</p>
                            <a href="<?php echo BASE_URL; ?>/quantri/xeptkb" class="btn btn-outline-success mt-auto">Xếp</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 func-card">
                    <div class="card">
                        <div class="card-body stat-card">
                            <i class="bi bi-people text-success"></i>
                            <h5>Quản Lý GV</h5>
                            <p class="card-text">CRUD GV, phân công.</p>
                            <a href="<?php echo BASE_URL; ?>/quantri/quanlygiaovien" class="btn btn-outline-success mt-auto">Quản Lý</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 func-card">
                    <div class="card">
                        <div class="card-body stat-card">
                            <i class="bi bi-mortarboard text-primary"></i>
                            <h5 class="text-primary">Quản Lý Tuyển Sinh</h5>
                            <p class="card-text">Nhập điểm, lọc ảo, xét tuyển.</p>
                            <a href="<?php echo BASE_URL; ?>/quantri/quanlytuyensinh" class="btn btn-outline-primary mt-auto">Vào</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 func-card">
                    <div class="card">
                        <div class="card-body stat-card">
                            <i class="bi bi-house-door text-success"></i>
                            <h5>Quản Lý Lớp</h5>
                            <p class="card-text">CRUD lớp, sĩ số.</p>
                            <a href="#" class="btn btn-outline-success mt-auto" disabled>Quản lý (Sắp có)</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 func-card">
                    <div class="card">
                        <div class="card-body stat-card">
                            <i class="bi bi-person-badge text-success"></i>
                            <h5>Quản Lý HS</h5>
                            <p class="card-text">CRUD HS, xếp lớp.</p>
                            <a href="#" class="btn btn-outline-success mt-auto" disabled>Quản lý (Sắp có)</a>
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