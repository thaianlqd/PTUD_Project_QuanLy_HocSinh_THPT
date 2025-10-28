<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sở GD | THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #F0F8FF; }
        .sidebar { width: 300px; min-width: 300px; transition: transform 0.3s ease; position: fixed; height: 100vh; overflow-y: auto; z-index: 1000; background: white; box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .main-content { margin-left: 300px; transition: margin-left 0.3s ease; min-height: 100vh; padding: 1rem; }
        .profile-section { height: 200px; padding: 1rem; border-bottom: 1px solid #dee2e6; text-align: center; }
        .nav-link { border-radius: 8px; margin-bottom: 0.25rem; transition: background 0.3s; color: #0A6ED1; }
        .nav-link:hover { background-color: #E3F2FD; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); height: 100%; display: flex; flex-direction: column; }
        .card-body { flex-grow: 1; display: flex; flex-direction: column; justify-content: center; padding: 1.5rem; }
        .row { --bs-gutter-x: 1rem; }
        .chart-container { position: relative; height: 300px; width: 100%; }
        .table { font-size: 0.9rem; }
        .table th, .table td { padding: 0.75rem 0.5rem; vertical-align: middle; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .fade-in { animation: fadeIn 0.5s ease-in; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @media (max-width: 992px) { .sidebar { transform: translateX(-100%); } .main-content { margin-left: 0; } .sidebar.show { transform: translateX(0); } .col-md-3 { flex: 0 0 50%; max-width: 50%; } }
        @media (max-width: 576px) { .col-md-3 { flex: 0 0 100%; max-width: 100%; } .chart-container { height: 250px; } }
        .modal-dialog { max-width: 600px; }
        .modal-body { max-height: 60vh; overflow-y: auto; }
    </style>
</head>
<body>
    <!-- Sidebar (Menu Chỉ Cho Sở GD) -->
    <div class="sidebar" id="sidebar">
        <div class="profile-section">
            <img src="https://via.placeholder.com/80x80?text=SG" alt="Profile" class="rounded-circle mb-2" style="width: 80px; height: 80px; border: 3px solid #0A6ED1;">
            <h5 class="fw-bold text-primary mb-1">Nguyễn Văn A</h5>
            <p class="text-muted mb-0 small">Mã: SG001</p>
            <p class="text-muted small">Vai Trò: Nhân Viên Sở GD</p>
            <div class="row g-1 mt-2 text-center">
                <div class="col-4"><small class="text-muted">Trường</small><br><strong class="text-primary fs-6">50</strong></div>
                <div class="col-4"><small class="text-muted">Thí Sinh</small><br><strong class="text-info fs-6">1,500</strong></div>
                <div class="col-4"><small class="text-muted">Chỉ Tiêu</small><br><strong class="text-success fs-6">10,000</strong></div>
            </div>
        </div>
        <ul class="nav flex-column px-2">
            <li class="nav-item"><a class="nav-link" href="#overview"><i class="bi bi-speedometer2 me-2"></i>Tổng Quan</a></li>
            <li class="nav-item"><a class="nav-link" href="#theo-doi-truong"><i class="bi bi-building me-2"></i>Theo Dõi Trường</a></li>
            <li class="nav-item"><a class="nav-link" href="#ql-ketqua-ts"><i class="bi bi-award me-2"></i>Quản Lý Tuyển Sinh</a></li>
            <li class="nav-item"><a class="nav-link" href="#tao-bai-viet"><i class="bi bi-pencil-square me-2"></i>Tạo Bài Viết</a></li>
            <li class="nav-item"><a class="nav-link" href="#ql-to-hop-mon"><i class="bi bi-bookmark-star me-2"></i>Quản Lý Tổ Hợp Môn</a></li>
            <li class="nav-item mt-auto p-3 border-top"><a class="nav-link text-danger" href="login.html"><i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <nav class="navbar navbar-expand-lg bg-white shadow-sm mb-4 rounded-3 p-2">
            <div class="container-fluid">
                <button class="btn btn-outline-primary d-lg-none me-2 rounded-pill" id="toggleSidebar">
                    <i class="bi bi-list"></i> Menu
                </button>
                <a class="navbar-brand fw-bold text-primary" href="#">THPT Manager - Sở GD</a>
                <form class="d-flex mx-auto w-50">
                    <input class="form-control rounded-pill me-2 shadow-sm" type="search" placeholder="Tìm trường/thí sinh..." aria-label="Search">
                    <button class="btn btn-outline-primary rounded-pill" type="submit"><i class="bi bi-search"></i></button>
                </form>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3 position-relative">
                        <a href="#" class="nav-link p-2">
                            <i class="bi bi-bell fs-5"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger small">3</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="dropdown-toggle nav-link p-2" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="https://via.placeholder.com/32x32?text=SG" alt="Profile" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                            <span>Nguyễn Văn A</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Cài Đặt</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Mật Khẩu</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="login.html"><i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Overview Stats (2 Cards, h-100 Even) -->
        <section id="overview" class="mb-5">
            <h2 class="fw-bold text-primary mb-4">Tổng Quan Sở GD</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-building fs-1 text-primary mb-3"></i>
                            <h5 class="card-title fw-bold text-primary">Số Trường THPT</h5>
                            <h3 class="fw-bold text-primary mb-1">50</h3>
                            <small class="text-muted">+2 so với năm trước</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-people fs-1 text-info mb-3"></i>
                            <h5 class="card-title fw-bold text-info">Tổng Thí Sinh</h5>
                            <h3 class="fw-bold text-info mb-1">1,500</h3>
                            <small class="text-muted">Chờ xét tuyển</small>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Charts (Sở GD: Thí Sinh Theo Trường) -->
        <section class="mb-5">
            <h3 class="fw-bold text-primary mb-4">Biểu Đồ Thống Kê Tuyển Sinh</h3>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold text-primary">Tỷ Lệ Trúng Tuyển Theo Trường</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="chart-container">
                                <canvas id="pieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold text-primary">Chỉ Tiêu Theo Khối</h6>
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

        <!-- Table Danh Sách Trường (Sở GD Specific) -->
        <section class="mb-5">
            <h3 class="fw-bold text-primary mb-4">Danh Sách Trường THPT</h3>
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th>Tên Trường</th>
                                    <th>Sĩ Số</th>
                                    <th>Chỉ Tiêu</th>
                                    <th>Trúng Tuyển</th>
                                    <th>Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>THPT A</td>
                                    <td>1,200</td>
                                    <td>150</td>
                                    <td>145</td>
                                    <td><span class="badge bg-success">Đủ</span></td>
                                </tr>
                                <tr>
                                    <td>THPT B</td>
                                    <td>1,100</td>
                                    <td>140</td>
                                    <td>120</td>
                                    <td><span class="badge bg-warning">Bổ Sung</span></td>
                                </tr>
                                <tr>
                                    <td>THPT C</td>
                                    <td>950</td>
                                    <td>120</td>
                                    <td>118</td>
                                    <td><span class="badge bg-success">Đủ</span></td>
                                </tr>
                                <!-- Thêm row, JS dynamic nếu cần -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cards Chức Năng (Sở GD Specific) -->
        <section>
            <h3 class="fw-bold text-primary mb-4">Chức Năng Chính</h3>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-list-ul fs-2 text-primary mb-3"></i>
                            <h5 class="card-title fw-bold">Danh Sách Trường</h5>
                            <p class="card-text text-muted small">Bộ lọc khối/tổ hợp.</p>
                            <button class="btn btn-outline-primary mt-auto" data-bs-toggle="modal" data-bs-target="#modalPreview">Xem</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-upload fs-2 text-primary mb-3"></i>
                            <h5 class="card-title fw-bold">Nhập Chỉ Tiêu</h5>
                            <p class="card-text text-muted small">Cập nhật chi tiêu HS.</p>
                            <button class="btn btn-outline-primary mt-auto" data-bs-toggle="modal" data-bs-target="#modalPreview">Nhập</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-file-earmark-spreadsheet fs-2 text-primary mb-3"></i>
                            <h5 class="card-title fw-bold">Import Điểm Thi</h5>
                            <p class="card-text text-muted small">Upload CSV/XLSX.</p>
                            <button class="btn btn-outline-primary mt-auto" data-bs-toggle="modal" data-bs-target="#modalPreview">Upload</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-pencil-square fs-2 text-primary mb-3"></i>
                            <h5 class="card-title fw-bold">Tạo Bài Viết</h5>
                            <p class="card-text text-muted small">Tin tức Sở.</p>
                            <button class="btn btn-outline-primary mt-auto" data-bs-toggle="modal" data-bs-target="#modalPreview">Tạo</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white shadow-sm mt-5 p-3 text-center">
            <p class="mb-0 text-muted">&copy; 2025 Hệ Thống Quản Lý Trường THPT. | Liên hệ: info@thpt.edu.vn | Hotline: 1900-1234</p>
        </footer>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalPreview" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="modalTitle">Chi Tiết</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modalBody">Nội dung Sở GD (e.g., form nhập chỉ tiêu).</div>
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
        // JS Switch (Mặc định role = so-gd cho file này)
        const role = 'so-gd'; // Hardcode cho file riêng
        document.getElementById('topRoleName').textContent = 'Nhân Viên Sở GD';
        // Charts Data Cho Sở GD (Thí Sinh Theo Trường)
        new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: { labels: ['Đủ Chỉ Tiêu', 'Bổ Sung', 'Chưa Đủ'], datasets: [{ data: [70, 20, 10], backgroundColor: ['#4CAF50', '#FF9800', '#F44336'] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: { labels: ['Khối 10', 'Khối 11', 'Khối 12'], datasets: [{ label: 'Chỉ Tiêu', data: [3000, 2500, 2000], backgroundColor: '#0A6ED1' }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });
        // Toggle Sidebar
        document.getElementById('toggleSidebar').addEventListener('click', () => document.getElementById('sidebar').classList.toggle('show'));
        // Modal Action
        document.getElementById('modalAction').onclick = () => alert('Lưu thành công!');
    </script>
</body>
</html>