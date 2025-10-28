<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GV Bộ Môn | THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Same CSS as above */
        body { font-family: 'Roboto', sans-serif; background-color: #F0F8FF; }
        .sidebar { width: 300px; min-width: 300px; transition: transform 0.3s ease; position: fixed; height: 100vh; overflow-y: auto; z-index: 1000; background: white; box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .main-content { margin-left: 300px; transition: margin-left 0.3s ease; min-height: 100vh; padding: 1rem; }
        .profile-section { height: 200px; padding: 1rem; border-bottom: 1px solid #dee2e6; text-align: center; }
        .nav-link { border-radius: 8px; margin-bottom: 0.25rem; transition: background 0.3s; color: #ffc107; } /* Warning yellow for GV */
        .nav-link:hover { background-color: #FFF3CD; }
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
    <!-- Sidebar (Menu Chỉ Cho GV Bộ Môn - Điểm Danh, Bài Tập, Nhập Điểm, Giảng Dạy) -->
    <div class="sidebar" id="sidebar">
        <div class="profile-section">
            <img src="https://via.placeholder.com/80x80?text=GV" alt="Profile" class="rounded-circle mb-2" style="width: 80px; height: 80px; border: 3px solid #ffc107;">
            <h5 class="fw-bold text-warning mb-1">Phạm Thị D</h5>
            <p class="text-muted mb-0 small">Mã GV: GVM01</p>
            <p class="text-muted small">Vai Trò: Giáo Viên Bộ Môn (Toán)</p>
            <div class="row g-1 mt-2 text-center">
                <div class="col-4"><small class="text-muted">Lớp Dạy</small><br><strong class="text-warning fs-6">6</strong></div>
                <div class="col-4"><small class="text-muted">Bài Nộp</small><br><strong class="text-success fs-6">80%</strong></div>
                <div class="col-4"><small class="text-muted">Phiên DD</small><br><strong class="text-info fs-6">10</strong></div>
            </div>
        </div>
        <ul class="nav flex-column px-2">
            <li class="nav-item"><a class="nav-link" href="#overview"><i class="bi bi-speedometer2 me-2"></i>Tổng Quan</a></li>
            <li class="nav-item"><a class="nav-link" href="#xem-tkb-ca-nhan"><i class="bi bi-calendar-check me-2"></i>Lịch Cá Nhân</a></li>
            <li class="nav-item"><a class="nav-link" href="#ql-diem-danh"><i class="bi bi-check-circle me-2"></i>Quản Lý Điểm Danh</a></li>
            <li class="nav-item"><a class="nav-link" href="#ql-bai-tap"><i class="bi bi-journal-text me-2"></i>Quản Lý Bài Tập</a></li>
            <li class="nav-item"><a class="nav-link" href="#nhap-diem"><i class="bi bi-calculator me-2"></i>Nhập Điểm</a></li>
            <li class="nav-item"><a class="nav-link" href="#ql-giang-day"><i class="bi bi-easel me-2"></i>Quản Lý Giảng Dạy</a></li>
            <li class="nav-item mt-auto p-3 border-top"><a class="nav-link text-danger" href="login.html"><i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <nav class="navbar navbar-expand-lg bg-white shadow-sm mb-4 rounded-3 p-2">
            <div class="container-fluid">
                <button class="btn btn-outline-warning d-lg-none me-2 rounded-pill" id="toggleSidebar">
                    <i class="bi bi-list"></i> Menu
                </button>
                <a class="navbar-brand fw-bold text-warning" href="#">THPT Manager - GV Bộ Môn</a>
                <form class="d-flex mx-auto w-50">
                    <input class="form-control rounded-pill me-2 shadow-sm" type="search" placeholder="Tìm lớp/bài tập..." aria-label="Search">
                    <button class="btn btn-outline-warning rounded-pill" type="submit"><i class="bi bi-search"></i></button>
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
                            <img src="https://via.placeholder.com/32x32?text=GV" alt="Profile" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                            <span>Phạm Thị D</span>
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
            <h2 class="fw-bold text-warning mb-4">Tổng Quan Giảng Dạy</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-check-circle fs-1 text-warning mb-3"></i>
                            <h5 class="card-title fw-bold text-warning">Lớp Dạy Tuần Này</h5>
                            <h3 class="fw-bold text-warning mb-1">6</h3>
                            <small class="text-muted">Lớp phân công</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-journal-text fs-1 text-danger mb-3"></i>
                            <h5 class="card-title fw-bold text-danger">Tỷ Lệ Nộp Bài</h5>
                            <h3 class="fw-bold text-danger mb-1">80%</h3>
                            <small class="text-muted">Trung bình lớp</small>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Charts (GV: Nộp Bài Theo Lớp, Tỷ Lệ Điểm Danh) -->
        <section class="mb-5">
            <h3 class="fw-bold text-warning mb-4">Biểu Đồ Giảng Dạy</h3>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 fw-bold text-warning">Tỷ Lệ Nộp Bài Theo Lớp</h6>
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
                            <h6 class="mb-0 fw-bold text-warning">Tỷ Lệ Điểm Danh Phiên</h6>
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

        <!-- Table Phiên Điểm Danh (GV Specific) -->
        <section class="mb-5">
            <h3 class="fw-bold text-warning mb-4">Danh Sách Phiên Điểm Danh</h3>
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-warning">
                                <tr>
                                    <th>Phiên</th>
                                    <th>Lớp</th>
                                    <th>Thời Gian</th>
                                    <th>Tỷ Lệ Có Mặt</th>
                                    <th>Lý Do Vắng</th>
                                    <th>Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>PD001</td>
                                    <td>10A1</td>
                                    <td>22/10/2025 8:00</td>
                                    <td>95%</td>
                                    <td>1 có phép</td>
                                    <td><button class="btn btn-sm btn-outline-warning">Thống Kê</button></td>
                                </tr>
                                <tr>
                                    <td>PD002</td>
                                    <td>10A2</td>
                                    <td>23/10/2025 9:00</td>
                                    <td>90%</td>
                                    <td>2 không phép</td>
                                    <td><button class="btn btn-sm btn-outline-warning">Cập Nhật</button></td>
                                </tr>
                                <!-- Thêm row -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cards Chức Năng (GV Specific) -->
        <section>
            <h3 class="fw-bold text-warning mb-4">Chức Năng Giảng Dạy</h3>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-check-circle fs-2 text-warning mb-3"></i>
                            <h5 class="card-title fw-bold">Quản Lý Điểm Danh</h5>
                            <p class="card-text text-muted small">Tạo phiên, thống kê.</p>
                            <button class="btn btn-outline-warning mt-auto" data-bs-toggle="modal" data-bs-target="#modalPreview">Tạo Phiên</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-journal-text fs-2 text-warning mb-3"></i>
                            <h5 class="card-title fw-bold">Quản Lý Bài Tập</h5>
                            <p class="card-text text-muted small">Giao trắc nghiệm/upload.</p>
                            <button class="btn btn-outline-warning mt-auto" data-bs-toggle="modal" data-bs-target="#modalPreview">Giao Bài</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-calculator fs-2 text-warning mb-3"></i>
                            <h5 class="card-title fw-bold">Nhập Điểm</h5>
                            <p class="card-text text-muted small">Lớp phân công, thang 0-10.</p>
                            <button class="btn btn-outline-warning mt-auto" data-bs-toggle="modal" data-bs-target="#modalPreview">Nhập</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <i class="bi bi-easel fs-2 text-warning mb-3"></i>
                            <h5 class="card-title fw-bold">Giảng Dạy</h5>
                            <p class="card-text text-muted small">Gửi yêu cầu chỉnh điểm.</p>
                            <button class="btn btn-outline-warning mt-auto" data-bs-toggle="modal" data-bs-target="#modalPreview">Gửi Yêu Cầu</button>
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
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold" id="modalTitle">Chi Tiết</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modalBody">Nội dung GV Bộ Môn (e.g., form tạo phiên điểm danh).</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-warning" id="modalAction">Lưu</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const role = 'gv-bo-mon'; // Hardcode
        document.getElementById('topRoleName').textContent = 'Giáo Viên Bộ Môn';
        // Charts Data Cho GV (Nộp Bài Theo Lớp)
        new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: { labels: ['10A1', '10A2', '11B1'], datasets: [{ data: [85, 75, 90], backgroundColor: ['#4CAF50', '#FF9800', '#2196F3'] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: { labels: ['Phiên 1', 'Phiên 2', 'Phiên 3'], datasets: [{ label: 'Tỷ Lệ %', data: [95, 90, 85], backgroundColor: '#ffc107' }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100 } } }
        });
        // Toggle Sidebar
        document.getElementById('toggleSidebar').addEventListener('click', () => document.getElementById('sidebar').classList.toggle('show'));
        // Modal Action
        document.getElementById('modalAction').onclick = () => alert('Lưu thành công!');
    </script>
</body>
</html>