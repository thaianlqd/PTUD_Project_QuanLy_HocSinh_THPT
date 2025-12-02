<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BGH | THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #F0F8FF; }
        .sidebar { width: 300px; min-width: 300px; transition: transform 0.3s ease; position: fixed; height: 100vh; overflow-y: auto; z-index: 1000; background: white; box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .main-content { margin-left: 300px; transition: margin-left 0.3s ease; min-height: 100vh; padding: 1rem; }
        .profile-section { height: 200px; padding: 1rem; border-bottom: 1px solid #dee2e6; text-align: center; }
        .nav-link { border-radius: 8px; margin-bottom: 0.25rem; transition: background 0.3s; color: #17a2b8; }
        .nav-link:hover { background-color: #D1ECF1; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); height: 100%; display: flex; flex-direction: column; }
        .card-body { flex-grow: 1; display: flex; flex-direction: column; justify-content: center; padding: 1.5rem; }
        .row { --bs-gutter-x: 1rem; }
        .chart-container { position: relative; height: 300px; width: 100%; }
        .table { font-size: 0.9rem; }
        .table th, .table td { padding: 0.75rem 0.5rem; vertical-align: middle; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .diem-cu { text-decoration: line-through; color: #dc3545; }
        .diem-moi { color: #198754; font-weight: bold; font-size: 1.1em; }
        /* (... các style khác của bạn ...) */
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="profile-section">
            <img src="https://via.placeholder.com/80x80?text=BG" alt="Profile" class="rounded-circle mb-2" style="width: 80px; height: 80px; border: 3px solid #17a2b8;">
            <h5 class="fw-bold text-info mb-1"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h5>
            <p class="text-muted mb-0 small">Mã: <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
            <p class="text-muted small">Vai Trò: Ban Giám Hiệu</p>
            
            <div class="row g-1 mt-2 text-center">
                <div class="col-4"><small class="text-muted">Yêu Cầu</small><br><strong class="text-info fs-6"><?php echo $data['yeu_cau_count'] ?? 0; ?></strong></div>
                <div class="col-4"><small class="text-muted">Điểm TB</small><br><strong class="text-success fs-6"><?php echo $data['diem_tb_truong'] ?? 0; ?></strong></div>
                <div class="col-4"><small class="text-muted">Vắng</small><br><strong class="text-warning fs-6"><?php echo $data['ti_le_vang'] ?? 0; ?>%</strong></div>
            </div>
        </div>
        <ul class="nav flex-column px-2">
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-speedometer2 me-2"></i>Tổng Quan</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/bgh/duyetdiem"><i class="bi bi-clipboard-check me-2"></i>Xử Lý Yêu Cầu Điểm</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-calendar-check me-2"></i>Xem Thời Khóa Biểu</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-graph-up me-2"></i>Thống Kê Số Liệu</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-printer me-2"></i>In/Tải Tài Liệu</a></li>
            <li class="nav-item mt-auto p-3 border-top"><a class="nav-link text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất</a></li>
        </ul>
    </div>

    <div class="main-content">
        <nav class="navbar navbar-expand-lg bg-white shadow-sm mb-4 rounded-3 p-2">
            <div class="container-fluid">
                <button class="btn btn-outline-info d-lg-none me-2 rounded-pill" id="toggleSidebar">
                    <i class="bi bi-list"></i> Menu
                </button>
                <a class="navbar-brand fw-bold text-info" href="#">THPT Manager - BGH</a>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item dropdown">
                        <a class="dropdown-toggle nav-link p-2" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="https://via.placeholder.com/32x32?text=BG" alt="Profile" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                            <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Cài Đặt</a></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <section id="overview" class="mb-5">
            <h2 class="fw-bold text-info mb-4">Tổng Quan Trường</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-clipboard-check fs-1 text-info mb-3"></i>
                            <h5 class="card-title fw-bold text-info">Yêu Cầu Chỉnh Điểm</h5>
                            <h3 class="fw-bold text-info mb-1"><?php echo $data['yeu_cau_count'] ?? 0; ?></h3>
                            <small class="text-muted">Chờ duyệt</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-graph-up fs-1 text-success mb-3"></i>
                            <h5 class="card-title fw-bold text-success">Điểm TB Toàn Trường</h5>
                            <h3 class="fw-bold text-success mb-1"><?php echo $data['diem_tb_truong'] ?? 0; ?></h3>
                            <small class="text-muted">Học kỳ 1</small>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-5"> ... </section>

        <section class="mb-5">
            <h3 class="fw-bold text-info mb-4">
                Danh Sách Yêu Cầu Mới Nhất
                <a href="<?php echo BASE_URL; ?>/bgh/duyetdiem" class="btn btn-sm btn-outline-info float-end">
                    Xem tất cả <i class="bi bi-arrow-right-short"></i>
                </a>
            </h3>
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-info">
                                <tr>
                                    <th>Học Sinh</th>
                                    <th>Môn Học</th>
                                    <th>Điểm Cũ/Mới</th>
                                    <th>Lý Do</th>
                                    <th>Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data['phieu_moi_nhat'])): ?>
                                    <tr>
                                        <td colspan="5" class="text-center p-3 text-muted">Không có phiếu nào chờ duyệt.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($data['phieu_moi_nhat'] as $phieu): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($phieu['ten_hoc_sinh']); ?></td>
                                            <td><?php echo htmlspecialchars($phieu['ten_mon_hoc']); ?></td>
                                            <td>
                                                <span class="diem-cu"><?php echo $phieu['diem_cu']; ?></span>
                                                <i class="bi bi-arrow-right-short"></i>
                                                <span class="diem-moi"><?php echo $phieu['diem_de_nghi']; ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($phieu['ly_do_chinh_sua']); ?></td>
                                            <td><span class="badge bg-warning">Chờ Duyệt</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <h3 class="fw-bold text-info mb-4">Chức Năng BGH</h3>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-clipboard-check fs-2 text-info mb-3"></i>
                            <h5 class="card-title fw-bold">Xử Lý Yêu Cầu Điểm</h5>
                            <p class="card-text text-muted small">Duyệt/từ chối phiếu.</p>
                            <a href="<?php echo BASE_URL; ?>/bgh/duyetdiem" class="btn btn-outline-info mt-auto">Duyệt</a>
                        </div>
                    </div>
                </div>
                </div>
        </section>

        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // SỬA: Xóa các dòng JS hardcode
        // document.getElementById('topRoleName').textContent = 'Ban Giám Hiệu';
        
        // (Phần code của Charts giữ nguyên)
        new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: { labels: ['Đã Duyệt', 'Từ Chối', 'Chờ'], datasets: [{ data: [60, 20, 20], backgroundColor: ['#4CAF50', '#F44336', '#FF9800'] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: { labels: ['Lớp 10A1', '10A2', '11B1', '12C1'], datasets: [{ label: 'Điểm TB', data: [8.5, 7.8, 8.2, 8.0], backgroundColor: '#17a2b8' }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 10 } } }
        });
        
        // (Code Toggle Sidebar và Modal giữ nguyên)
        document.getElementById('toggleSidebar').addEventListener('click', () => document.getElementById('sidebar').classList.toggle('show'));
        document.getElementById('modalAction').onclick = () => alert('Chức năng này đang được liên kết...');
    </script>
</body>
</html>