<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - Phụ Huynh | THPT Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: 'Roboto', sans-serif; background-color: #F0F8FF; }
    .sidebar { width: 300px; min-width: 300px; position: fixed; height: 100vh; overflow-y: auto; background: white; box-shadow: 2px 0 10px rgba(0,0,0,0.1); transition: transform 0.3s ease; z-index: 1000; }
    .main-content { margin-left: 300px; transition: margin-left 0.3s ease; min-height: 100vh; padding: 1rem; }
    .profile-section { height: 200px; padding: 1rem; border-bottom: 1px solid #dee2e6; text-align: center; }
    .nav-link { border-radius: 8px; margin-bottom: 0.25rem; transition: background 0.3s; color: #dc3545; }
    .nav-link:hover { background-color: #F8D7DA; }
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
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="profile-section">
      <img src="https://via.placeholder.com/80x80?text=PH" alt="Profile" class="rounded-circle mb-2" style="width: 80px; height: 80px; border: 3px solid #dc3545;">
      <h5 class="fw-bold text-danger mb-1">Ngô Thị F</h5>
      <p class="text-muted mb-0 small">Mã PH: PH001</p>
      <p class="text-muted small">Con: Vũ Văn G - 10A1</p>
      <div class="row g-1 mt-2 text-center">
        <div class="col-4"><small class="text-muted">Hóa Đơn</small><br><strong class="text-danger fs-6">2</strong></div>
        <div class="col-4"><small class="text-muted">Phiếu Vắng</small><br><strong class="text-warning fs-6">1</strong></div>
        <div class="col-4"><small class="text-muted">Điểm TB</small><br><strong class="text-success fs-6">8.0</strong></div>
      </div>
    </div>

    <ul class="nav flex-column px-2">
      <li class="nav-item"><a class="nav-link" href="#overview"><i class="bi bi-speedometer2 me-2"></i>Tổng Quan</a></li>
      <li class="nav-item"><a class="nav-link" href="#ho-so-hs"><i class="bi bi-file-person me-2"></i>Xem Hồ Sơ HS</a></li>
      <li class="nav-item"><a class="nav-link" href="#thanh-toan"><i class="bi bi-credit-card me-2"></i>Thanh Toán</a></li>
      <li class="nav-item"><a class="nav-link" href="#xin-phep-vang"><i class="bi bi-envelope-check me-2"></i>Xin Phép Vắng</a></li>
      <li class="nav-item"><a class="nav-link" href="#lich-con"><i class="bi bi-calendar-event me-2"></i>Lịch Học</a></li>
      <li class="nav-item mt-auto p-3 border-top"><a class="nav-link text-danger" href="login.html"><i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất</a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main-content fade-in">
    <nav class="navbar navbar-expand-lg bg-white shadow-sm mb-4 rounded-3 p-2">
      <div class="container-fluid">
        <button class="btn btn-outline-danger d-lg-none me-2 rounded-pill" id="toggleSidebar"><i class="bi bi-list"></i> Menu</button>
        <a class="navbar-brand fw-bold text-danger" href="#">THPT Manager - Phụ Huynh</a>
        <form class="d-flex mx-auto w-50">
          <input class="form-control rounded-pill me-2 shadow-sm" type="search" placeholder="Tìm hồ sơ con/thanh toán..." aria-label="Search">
          <button class="btn btn-outline-danger rounded-pill" type="submit"><i class="bi bi-search"></i></button>
        </form>
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item me-3 position-relative">
            <a href="#" class="nav-link p-2">
              <i class="bi bi-bell fs-5"></i>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger small">2</span>
            </a>
          </li>
          <li class="nav-item dropdown">
            <a class="dropdown-toggle nav-link p-2" href="#" data-bs-toggle="dropdown">
              <img src="https://via.placeholder.com/32x32?text=PH" class="rounded-circle me-2" style="width: 32px; height: 32px;">
              <span>Ngô Thị F</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow">
              <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Cài Đặt</a></li>
              <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Đổi Mật Khẩu</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="login.html"><i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>

    <!-- Tổng Quan -->
    <section id="overview" class="mb-5">
      <h2 class="fw-bold text-danger mb-4">Tổng Quan Con (Vũ Văn G - 10A1)</h2>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="card text-center">
            <div class="card-body">
              <i class="bi bi-credit-card fs-1 text-danger mb-3"></i>
              <h5 class="fw-bold text-danger">Hóa Đơn Chờ Thanh Toán</h5>
              <h3 class="fw-bold text-danger">2</h3>
              <small class="text-muted">Kỳ 1: 5.000.000 VNĐ</small>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card text-center">
            <div class="card-body">
              <i class="bi bi-envelope-check fs-1 text-warning mb-3"></i>
              <h5 class="fw-bold text-warning">Phiếu Vắng Chờ Duyệt</h5>
              <h3 class="fw-bold text-warning">1</h3>
              <small class="text-muted">Ngày 25/10</small>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Biểu đồ điểm -->
    <section id="chart" class="mb-5">
      <h4 class="fw-bold text-danger mb-3">Biểu Đồ Kết Quả Học Tập</h4>
      <div class="card">
        <div class="card-body">
          <div class="chart-container">
            <canvas id="gradeChart"></canvas>
          </div>
        </div>
      </div>
    </section>

    <!-- Bảng điểm -->
    <section id="bang-diem" class="mb-5">
      <h4 class="fw-bold text-danger mb-3">Điểm Trung Bình Các Môn</h4>
      <div class="card">
        <div class="card-body table-responsive">
          <table class="table table-striped align-middle">
            <thead class="table-danger">
              <tr>
                <th>Môn</th>
                <th>Điểm Miệng</th>
                <th>Điểm 15'</th>
                <th>Điểm 1 Tiết</th>
                <th>Điểm Thi</th>
                <th>Điểm TB</th>
                <th>Chi Tiết</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Toán</td><td>8</td><td>7.5</td><td>9</td><td>8</td><td><strong>8.1</strong></td>
                <td><button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDiem">Xem</button></td>
              </tr>
              <tr>
                <td>Văn</td><td>7</td><td>8</td><td>8.5</td><td>8</td><td><strong>7.9</strong></td>
                <td><button class="btn btn-sm btn-outline-danger">Xem</button></td>
              </tr>
              <tr>
                <td>Anh</td><td>8</td><td>9</td><td>9</td><td>9</td><td><strong>8.9</strong></td>
                <td><button class="btn btn-sm btn-outline-danger">Xem</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- Modal điểm -->
    <div class="modal fade" id="modalDiem" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">Chi Tiết Điểm Môn Toán</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p><strong>Giáo viên:</strong> Nguyễn Thị H</p>
            <ul>
              <li>Miệng: 8</li>
              <li>15 phút: 7.5</li>
              <li>1 tiết: 9</li>
              <li>Thi: 8</li>
            </ul>
            <p><strong>Nhận xét:</strong> Học tốt, cần tích cực phát biểu hơn.</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="text-center mt-5 text-muted small pb-3">
      © 2025 THPT Manager | Dành cho Phụ Huynh
    </footer>
  </div>

  <!-- Script -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('toggleSidebar').addEventListener('click', function() {
      document.getElementById('sidebar').classList.toggle('show');
    });

    const ctx = document.getElementById('gradeChart');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Toán', 'Văn', 'Anh', 'Lý', 'Hóa', 'Sinh'],
        datasets: [{
          label: 'Điểm Trung Bình',
          data: [8.1, 7.9, 8.9, 7.5, 8.3, 8.0],
          backgroundColor: '#dc3545'
        }]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true, max: 10 } }
      }
    });
  </script>
</body>
</html>
