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
        .nav-link { border-radius: 8px; margin-bottom: 0.25rem; transition: background 0.3s; color: #17a2b8; } /* Info blue for BGH */
        .nav-link:hover { background-color: #D1ECF1; }
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
    <!-- Sidebar (Menu Chỉ Cho BGH - Duyệt, Thống Kê, In) -->
    <div class="sidebar" id="sidebar">
        <div class="profile-section">
            <img src="https://via.placeholder.com/80x80?text=BG" alt="Profile" class="rounded-circle mb-2" style="width: 80px; height: 80px; border: 3px solid #17a2b8;">
            <h5 class="fw-bold text-info mb-1">Lê Văn C</h5>
            <p class="text-muted mb-0 small">Mã: BGH01</p>
            <p class="text-muted small">Vai Trò: Ban Giám Hiệu</p>
            <div class="row g-1 mt-2 text-center">
                <div class="col-4"><small class="text-muted">Yêu Cầu</small><br><strong class="text-info fs-6">5</strong></div>
                <div class="col-4"><small class="text-muted">Điểm TB</small><br><strong class="text-success fs-6">8.2</strong></div>
                <div class="col-4"><small class="text-muted">Vắng</small><br><strong class="text-warning fs-6">10%</strong></div>
            </div>
        </div>
        <ul class="nav flex-column px-2">
            <li class="nav-item"><a class="nav-link" href="#overview"><i class="bi bi-speedometer2 me-2"></i>Tổng Quan</a></li>
            <li class="nav-item"><a class="nav-link" href="#xl-yeu-cau-diem"><i class="bi bi-clipboard-check me-2"></i>Xử Lý Yêu Cầu Điểm</a></li>
            <li class="nav-item"><a class="nav-link" href="#xem-tkb"><i class="bi bi-calendar-check me-2"></i>Xem Thời Khóa Biểu</a></li>
            <li class="nav-item"><a class="nav-link" href="#thong-ke"><i class="bi bi-graph-up me-2"></i>Thống Kê Số Liệu</a></li>
            <li class="nav-item"><a class="nav-link" href="#in-tai-lieu"><i class="bi bi-printer me-2"></i>In/Tải Tài Liệu</a></li>
            <li class="nav-item mt-auto p-3 border-top"><a class="nav-link text-danger" href="login.html"><i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <nav class="navbar navbar-expand-lg bg-white shadow-sm mb-4 rounded-3 p-2">
            <div class="container-fluid">
                <button class="btn btn-outline-info d-lg-none me-2 rounded-pill" id="toggleSidebar">
                    <i class="bi bi-list"></i> Menu
                </button>
                <a class="navbar-brand fw-bold text-info" href="#">THPT Manager - BGH</a>
                <form class="d-flex mx-auto w-50">
                    <input class="form-control rounded-pill me-2 shadow-sm" type="search" placeholder="Tìm yêu cầu/điểm..." aria-label="Search">
                    <button class="btn btn-outline-info rounded-pill" type="submit"><i class="bi bi-search"></i></button>
                </form>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3 position-relative">
                        <a href="#" class="nav-link p-2">
                            <i class="bi bi-bell fs-5"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger small">2</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="dropdown-toggle nav-link p-2" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="https://via.placeholder.com/32x32?text=BG" alt="Profile" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                            <span>Lê Văn C</span>
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

        <!-- Overview Stats (2 Cards, Even Height) -->
        <section id="overview" class="mb-5">
            <h2 class="fw-bold text-info mb-4">Tổng Quan Trường</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-clipboard-check fs-1 text-info mb-3"></i>
                            <h5 class="card-title fw-bold text-info">Yêu Cầu Chỉnh Điểm</h5>
                            <h3 class="fw-bold text-info mb-1">5</h3>
                            <small class="text-muted">Chờ duyệt</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-graph-up fs-1 text-success mb-3"></i>
                            <h5 class="card-title fw-bold text-success">Điểm TB Toàn Trường</h5>
                            <h3 class="fw-bold text-success mb-1">8.2</h3>
                            <small class="text-muted">Học kỳ 1</small>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Charts (BGH: Tỷ Lệ Duyệt Yêu Cầu, Điểm Theo Lớp) -->
        <section class="mb-5">
            <h3 class="fw-bold text-info mb-4">Biểu Đồ Thống Kê Trường</h3>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold text-info">Tỷ Lệ Duyệt Yêu Cầu Điểm</h6>
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
                            <h6 class="mb-0 fw-bold text-info">Điểm TB Theo Lớp</h6>
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

        <!-- Table Yêu Cầu Chỉnh Sửa (BGH Specific) -->
        <section class="mb-5">
            <h3 class="fw-bold text-info mb-4">Danh Sách Yêu Cầu Chỉnh Sửa Điểm</h3>
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-info">
                                <tr>
                                    <th>Mã Yêu Cầu</th>
                                    <th>Học Sinh</th>
                                    <th>Môn Học</th>
                                    <th>Điểm Cũ/Mới</th>
                                    <th>Lý Do</th>
                                    <th>Trạng Thái</th>
                                    <th>Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>YC001</td>
                                    <td>Nguyễn Văn X</td>
                                    <td>Toán</td>
                                    <td>7.5 / 8.0</td>
                                    <td>Nhập sai</td>
                                    <td><span class="badge bg-warning">Chờ Duyệt</span></td>
                                    <td><button class="btn btn-sm btn-outline-info">Duyệt</button></td>
                                </tr>
                                <tr>
                                    <td>YC002</td>
                                    <td>Trần Thị Y</td>
                                    <td>Văn</td>
                                    <td>6.0 / 7.0</td>
                                    <td>Thi lại</td>
                                    <td><span class="badge bg-success">Đã Duyệt</span></td>
                                    <td><button class="btn btn-sm btn-outline-secondary">Xem</button></td>
                                </tr>
                                <!-- Thêm row -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cards Chức Năng (BGH Specific) -->
        <section>
            <h3 class="fw-bold text-info mb-4">Chức Năng BGH</h3>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-clipboard-check fs-2 text-info mb-3"></i>
                            <h5 class="card-title fw-bold">Xử Lý Yêu Cầu Điểm</h5>
                            <p class="card-text text-muted small">Duyệt/từ chối phiếu.</p>
                            <button class="btn btn-outline-info mt-auto" data-bs-toggle="modal" data-bs-target="#modalPreview">Duyệt</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-calendar-check fs-2 text-info mb-3"></i>
                            <h5 class="card-title fw-bold">Xem Thời Khóa Biểu</h5>
                            <p class="card-text text-muted small">Lịch chung, bộ lọc.</p>
                            <button class="btn btn-outline-info mt-auto" data-bs-toggle="modal" data-bs-target="#modalPreview">Xem</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-graph-up fs-2 text-info mb-3"></i>
                            <h5 class="card-title fw-bold">Thống Kê Số Liệu</h5>
                            <p class="card-text text-muted small">Báo cáo điểm/vắng.</p>
                            <button class="btn btn-outline-info mt-auto" data-bs-toggle="modal" data-bs-target="#modalPreview">Thống Kê</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-printer fs-2 text-info mb-3"></i>
                            <h5 class="card-title fw-bold">In/Tải Tài Liệu</h5>
                            <p class="card-text text-muted small">TKB, báo cáo PDF.</p>
                            <button class="btn btn-outline-info mt-auto" data-bs-toggle="modal" data-bs-target="#modalPreview">In</button>
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
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title fw-bold" id="modalTitle">Chi Tiết</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modalBody">Nội dung BGH (e.g., form duyệt yêu cầu).</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-info" id="modalAction">Duyệt</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const role = 'bgh'; // Hardcode
        document.getElementById('topRoleName').textContent = 'Ban Giám Hiệu';
        // Charts Data Cho BGH (Tỷ Lệ Duyệt Yêu Cầu)
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
        // Toggle Sidebar
        document.getElementById('toggleSidebar').addEventListener('click', () => document.getElementById('sidebar').classList.toggle('show'));
        // Modal Action
        document.getElementById('modalAction').onclick = () => alert('Duyệt thành công!');
    </script>
</body>
</html>