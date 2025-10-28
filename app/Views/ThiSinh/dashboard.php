<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard - Thí Sinh | THPT Manager</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    :root{
      --primary:#8B5CF6; /* tím */
      --primary-600:#A78BFA;
      --muted:#6c757d;
      --bg:#faf7ff;
    }
    body{font-family:'Roboto',sans-serif;background-color:var(--bg);}
    .sidebar{width:300px;min-width:300px;position:fixed;height:100vh;background:#fff;box-shadow:2px 0 10px rgba(0,0,0,0.08);overflow-y:auto;transition:transform .3s;z-index:1000;}
    .main-content{margin-left:300px;transition:margin-left .3s;min-height:100vh;padding:1rem;}
    .profile-section{height:200px;padding:1rem;border-bottom:1px solid #eee;text-align:center;}
    .nav-link{border-radius:8px;margin-bottom:.25rem;transition:background .2s;color:var(--primary);}
    .nav-link:hover{background-color:#f3e9ff}
    .card{border:none;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);display:flex;flex-direction:column;height:100%;}
    .card-body{flex:1;display:flex;flex-direction:column;justify-content:center;padding:1.25rem;}
    .chart-container{position:relative;height:300px;width:100%;}
    .table th,.table td{vertical-align:middle;padding:.6rem .5rem}
    .btn-primary{background:var(--primary);border-color:var(--primary);}
    @media (max-width:992px){.sidebar{transform:translateX(-100%)}.main-content{margin-left:0}.sidebar.show{transform:translateX(0)}}
    @media (max-width:576px){.chart-container{height:240px}}
    .fade-in{animation:fadeIn .45s ease-in}
    @keyframes fadeIn{from{opacity:0}to{opacity:1}}
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="profile-section">
      <img src="https://via.placeholder.com/80x80?text=TS" alt="TS" class="rounded-circle mb-2" style="width:80px;height:80px;border:3px solid var(--primary);">
      <h5 class="fw-bold" style="color:var(--primary)">Phan Thị B</h5>
      <p class="text-muted mb-0 small">Số BD: TS2025-001</p>
      <p class="text-muted small">Nguyện vọng: 3 đã đăng</p>
      <div class="row g-1 mt-2 text-center">
        <div class="col-4"><small class="text-muted">NV Đã ĐK</small><br><strong class="text-primary fs-6">3</strong></div>
        <div class="col-4"><small class="text-muted">Kết Quả</small><br><strong class="text-success fs-6">Chưa có</strong></div>
        <div class="col-4"><small class="text-muted">Xác Nhận</small><br><strong class="text-info fs-6">Chưa</strong></div>
      </div>
    </div>

    <ul class="nav flex-column px-2 py-3">
      <li class="nav-item"><a class="nav-link" href="#overview"><i class="bi bi-speedometer2 me-2"></i>Tổng Quan</a></li>
      <li class="nav-item"><a class="nav-link" href="#thong-tin-thi"><i class="bi bi-file-earmark-text me-2"></i>Thông Tin Thi</a></li>
      <li class="nav-item"><a class="nav-link" href="#nguyen-vong"><i class="bi bi-flag me-2"></i>Nguyện Vọng</a></li>
      <li class="nav-item"><a class="nav-link" href="#nhap-hoc"><i class="bi bi-journal-check me-2"></i>Đăng Ký Nhập Học</a></li>
      <li class="nav-item mt-auto p-3 border-top"><a class="nav-link text-danger" href="login.html"><i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất</a></li>
    </ul>
  </div>

  <!-- Main -->
  <div class="main-content fade-in">
    <nav class="navbar navbar-expand-lg bg-white shadow-sm mb-4 rounded-3 p-2">
      <div class="container-fluid">
        <button class="btn btn-outline-primary d-lg-none me-2 rounded-pill" id="toggleSidebar"><i class="bi bi-list"></i> Menu</button>
        <a class="navbar-brand fw-bold" style="color:var(--primary)" href="#">THPT Manager - Thí Sinh</a>
        <form class="d-flex mx-auto w-50">
          <input class="form-control rounded-pill me-2 shadow-sm" type="search" placeholder="Tìm NV/mã số báo danh..." aria-label="search">
          <button class="btn btn-outline-primary rounded-pill" type="submit"><i class="bi bi-search"></i></button>
        </form>
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item me-3 position-relative">
            <a class="nav-link p-2" href="#"><i class="bi bi-bell fs-5"></i><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger small">1</span></a>
          </li>
          <li class="nav-item dropdown">
            <a class="dropdown-toggle nav-link p-2" href="#" data-bs-toggle="dropdown"><img src="https://via.placeholder.com/32x32?text=TS" class="rounded-circle me-2" style="width:32px;height:32px;">Phan Thị B</a>
            <ul class="dropdown-menu dropdown-menu-end shadow">
              <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Cài đặt</a></li>
              <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Đổi mật khẩu</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="login.html"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>

    <!-- Overview -->
    <section id="overview" class="mb-5">
      <h2 class="fw-bold" style="color:var(--primary)">Tổng Quan Thí Sinh</h2>
      <div class="row g-3">
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <i class="bi bi-flag fs-1" style="color:var(--primary)"></i>
              <h5 class="fw-bold" style="color:var(--primary)">Nguyện Vọng Đã ĐK</h5>
              <h3 class="fw-bold">3</h3>
              <small class="text-muted">Ưu tiên NV1: ABC</small>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <i class="bi bi-award fs-1 text-success mb-3"></i>
              <h5 class="fw-bold text-success">Kết Quả Thi</h5>
              <h3 class="fw-bold">Chưa có</h3>
              <small class="text-muted">Đợi công bố</small>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <i class="bi bi-journal-check fs-1" style="color:var(--primary)"></i>
              <h5 class="fw-bold" style="color:var(--primary)">Xác Nhận Nhập Học</h5>
              <h3 class="fw-bold">Chưa</h3>
              <small class="text-muted">Sau khi trúng tuyển</small>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Charts -->
    <section class="mb-5">
      <div class="row g-4">
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-header bg-light">
              <h6 class="mb-0 fw-bold" style="color:var(--primary)">Tỷ Lệ Trúng NV (ước tính)</h6>
            </div>
            <div class="card-body p-0">
              <div class="chart-container p-3">
                <canvas id="nvPie"></canvas>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-header bg-light">
              <h6 class="mb-0 fw-bold" style="color:var(--primary)">Điểm Thi Theo Môn</h6>
            </div>
            <div class="card-body p-0">
              <div class="chart-container p-3">
                <canvas id="scoreBar"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Table Nguyện Vọng -->
    <section id="nguyen-vong" class="mb-5">
      <h4 class="fw-bold" style="color:var(--primary)">Danh Sách Nguyện Vọng</h4>
      <div class="card">
        <div class="card-body table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead class="table-secondary">
              <tr><th>STT</th><th>Trường</th><th>Ngành</th><th>Tổ Hợp</th><th>Ưu Tiên</th><th>Trạng Thái</th><th>Hành Động</th></tr>
            </thead>
            <tbody>
              <tr><td>1</td><td>THPT A</td><td>Khoa Học Tự Nhiên</td><td>Toán-Lý-Hóa</td><td>1</td><td><span class="badge bg-info">Đã đăng</span></td><td><button class="btn btn-sm btn-outline-primary">Sửa</button></td></tr>
              <tr><td>2</td><td>THPT B</td><td>Khoa Học Xã Hội</td><td>Văn-Sử-Địa</td><td>2</td><td><span class="badge bg-info">Đã đăng</span></td><td><button class="btn btn-sm btn-outline-primary">Sửa</button></td></tr>
              <tr><td>3</td><td>THPT C</td><td>Tiếng Anh Chuyên</td><td>Toán-Anh-Văn</td><td>3</td><td><span class="badge bg-info">Đã đăng</span></td><td><button class="btn btn-sm btn-outline-primary">Sửa</button></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- Cards chức năng -->
    <section class="mb-5">
      <h4 class="fw-bold" style="color:var(--primary)">Chức Năng</h4>
      <div class="row g-3">
        <div class="col-md-3">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="bi bi-journal-text fs-2" style="color:var(--primary)"></i>
              <h5 class="fw-bold mt-2">Quản Lý Thông Tin Thi</h5>
              <p class="text-muted small">SBD, Lịch thi, Phòng thi</p>
              <a href="#" class="btn btn-outline-primary mt-2">Xem</a>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="bi bi-flag fs-2" style="color:var(--primary)"></i>
              <h5 class="fw-bold mt-2">Quản Lý Nguyện Vọng</h5>
              <p class="text-muted small">Đăng ký/Điều chỉnh NV</p>
              <a href="#" class="btn btn-outline-primary mt-2">Quản lý</a>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="bi bi-journal-check fs-2" style="color:var(--primary)"></i>
              <h5 class="fw-bold mt-2">Đăng Ký Nhập Học</h5>
              <p class="text-muted small">Xác nhận nhập học khi trúng</p>
              <a href="#" class="btn btn-outline-primary mt-2">Đăng ký</a>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="bi bi-download fs-2" style="color:var(--primary)"></i>
              <h5 class="fw-bold mt-2">Xuất Tài Liệu</h5>
              <p class="text-muted small">In/ tải kết quả & giấy tờ</p>
              <a href="#" class="btn btn-outline-primary mt-2">Xuất</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <footer class="text-center mt-5 text-muted small pb-3">© 2025 THPT Manager | Dành cho Thí Sinh</footer>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('toggleSidebar').addEventListener('click', ()=> document.getElementById('sidebar').classList.toggle('show'));

    // Charts
    new Chart(document.getElementById('nvPie'), {
      type:'pie',
      data:{
        labels:['NV1','NV2','NV3'],
        datasets:[{data:[45,30,25], backgroundColor:['#8B5CF6','#C084FC','#F0ABFC']}]
      },
      options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}}}
    });

    new Chart(document.getElementById('scoreBar'), {
      type:'bar',
      data:{
        labels:['Toán','Văn','Anh','Lý','Hóa'],
        datasets:[{label:'Điểm Thi', data:[8.0,7.5,8.8,7.2,8.1], backgroundColor:'#8B5CF6'}]
      },
      options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true,max:10}}}
    });
  </script>
</body>
</html>
