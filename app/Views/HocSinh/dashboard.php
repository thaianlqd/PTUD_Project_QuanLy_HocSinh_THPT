<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard - Học Sinh | THPT Manager</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    :root{
      --primary:#0d6efd; /* xanh dương */
      --primary-600:#3B82F6;
      --muted:#6c757d;
      --bg:#f0f8ff;
    }
    body{font-family:'Roboto',sans-serif;background-color:var(--bg);}
    .sidebar{width:300px;min-width:300px;position:fixed;height:100vh;background:#fff;box-shadow:2px 0 10px rgba(0,0,0,0.08);overflow-y:auto;transition:transform .3s;z-index:1000;}
    .main-content{margin-left:300px;transition:margin-left .3s;min-height:100vh;padding:1rem;}
    .profile-section{height:200px;padding:1rem;border-bottom:1px solid #e9ecef;text-align:center;}
    .nav-link{border-radius:8px;margin-bottom:.25rem;transition:background .2s;color:var(--primary);}
    .nav-link:hover{background-color:#e8f2ff}
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
      <img src="https://via.placeholder.com/80x80?text=HS" alt="HS" class="rounded-circle mb-2" style="width:80px;height:80px;border:3px solid var(--primary);">
      <!-- SỬA LỖI: Lấy thông tin từ $data do Controller truyền vào -->
      <?php // $student_info = $this->userModel->getStudentInfo($_SESSION['user_id'] ?? 0); // XÓA DÒNG NÀY ?>
      <h5 class="fw-bold text-primary mb-1"><?php echo htmlspecialchars($data['student_info']['ho_ten'] ?? $data['user_name'] ?? 'Học Sinh'); ?></h5>
      <p class="text-muted mb-0 small">Mã HS: <?php echo htmlspecialchars($data['student_info']['ma_hoc_sinh'] ?? 'HS???'); ?></p>
      <p class="text-muted small">Lớp: <?php echo htmlspecialchars($data['student_info']['ten_lop'] ?? 'N/A'); ?></p>
      <!-- HẾT SỬA -->
      <div class="row g-1 mt-2 text-center">
        <!-- Nên lấy dữ liệu này từ Model trong DashboardController -->
        <div class="col-4"><small class="text-muted">Bài Chưa Nộp</small><br><strong class="text-primary fs-6"><?php echo $data['bai_chua_nop'] ?? '?'; ?></strong></div>
        <div class="col-4"><small class="text-muted">Điểm TB</small><br><strong class="text-success fs-6"><?php echo $data['diem_tb_hk'] ?? '?'; ?></strong></div>
        <div class="col-4"><small class="text-muted">Lịch Tuần</small><br><strong class="text-info fs-6"><?php echo $data['lich_tuan_count'] ?? '?'; ?> buổi</strong></div>
      </div>
    </div>

    <ul class="nav flex-column px-2 py-3">
      <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/dashboard"><i class="bi bi-speedometer2 me-2"></i>Tổng Quan</a></li>
      <!-- SỬA LINK Ở ĐÂY -->
      <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/baitap/index"><i class="bi bi-journal-text me-2"></i>Bài Tập</a></li>
      <!-- HẾT SỬA LINK -->
      <li class="nav-item"><a class="nav-link" href="#diem-so"><i class="bi bi-bar-chart-line me-2"></i>Điểm Cá Nhân</a></li>
      <li class="nav-item"><a class="nav-link" href="#lich-hoc"><i class="bi bi-calendar-event me-2"></i>Thời Khóa Biểu</a></li>
      <li class="nav-item mt-auto p-3 border-top"><a class="nav-link text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất</a></li>
    </ul>
  </div>

  <!-- Main -->
  <div class="main-content fade-in">
    <nav class="navbar navbar-expand-lg bg-white shadow-sm mb-4 rounded-3 p-2">
      <div class="container-fluid">
        <button class="btn btn-outline-primary d-lg-none me-2 rounded-pill" id="toggleSidebar"><i class="bi bi-list"></i> Menu</button>
        <a class="navbar-brand fw-bold text-primary" href="#">THPT Manager - Học Sinh</a>
        <form class="d-flex mx-auto w-50">
          <input class="form-control rounded-pill me-2 shadow-sm" type="search" placeholder="Tìm bài/tên môn..." aria-label="search">
          <button class="btn btn-outline-primary rounded-pill" type="submit"><i class="bi bi-search"></i></button>
        </form>
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item me-3 position-relative">
            <a class="nav-link p-2" href="#"><i class="bi bi-bell fs-5"></i><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger small">3</span></a>
          </li>
          <li class="nav-item dropdown">
            <!-- SỬA LỖI: Lấy user_name từ $data -->
            <a class="dropdown-toggle nav-link p-2" href="#" data-bs-toggle="dropdown"><img src="https://via.placeholder.com/32x32?text=HS" class="rounded-circle me-2" style="width:32px;height:32px;"><?php echo htmlspecialchars($data['user_name'] ?? 'Học Sinh'); ?></a>
            <!-- HẾT SỬA -->
            <ul class="dropdown-menu dropdown-menu-end shadow">
              <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Cài đặt</a></li>
              <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Đổi mật khẩu</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>

    <!-- Overview -->
    <section id="overview" class="mb-5">
      <h2 class="fw-bold text-primary mb-4">Tổng Quan Cá Nhân</h2>
       <div class="row g-3">
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <i class="bi bi-journal-x fs-1 text-primary mb-3"></i> <!-- Icon khác -->
              <h5 class="fw-bold text-primary">Bài Tập Chưa Nộp</h5>
              <h3 class="fw-bold"><?php echo $data['bai_chua_nop'] ?? '?'; ?></h3>
              <small class="text-muted">Trong tuần này</small>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <i class="bi bi-award fs-1 text-success mb-3"></i>
              <h5 class="fw-bold text-success">Điểm TB Học Kỳ</h5>
              <h3 class="fw-bold"><?php echo $data['diem_tb_hk'] ?? '?'; ?></h3>
              <small class="text-muted">Học kỳ I</small>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <i class="bi bi-calendar-event fs-1 text-info mb-3"></i>
              <h5 class="fw-bold text-info">Lịch Học Tuần</h5>
              <h3 class="fw-bold"><?php echo $data['lich_tuan_count'] ?? '?'; ?> buổi</h3>
              <small class="text-muted">Tuần này</small>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Charts (dynamic data) -->
    <section class="mb-5">
       <div class="row g-4">
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-header bg-light">
              <h6 class="mb-0 fw-bold text-primary">Tỷ Lệ Hoàn Thành Bài Tập (%)</h6>
            </div>
            <div class="card-body p-0">
              <div class="chart-container p-3">
                <canvas id="submitPie"></canvas>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-header bg-light">
              <h6 class="mb-0 fw-bold text-primary">Điểm Trung Bình Theo Môn</h6>
            </div>
            <div class="card-body p-0">
              <div class="chart-container p-3">
                <canvas id="subjectBar"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Table Lịch (dynamic từ DB) -->
    <section id="lich-hoc" class="mb-5">
       <h4 class="fw-bold text-primary mb-3">Lịch Học Tuần Này</h4>
      <div class="card">
        <div class="card-body table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead class="table-primary">
              <tr><th>Thứ</th><th>Tiết</th><th>Môn</th><th>GV</th><th>Phòng</th><th>Hành Động</th></tr>
            </thead>
            <tbody>
              <?php
                    // Lấy dữ liệu lịch học từ $data (cần được chuẩn bị trong DashboardController)
                    $lich_hoc_tuan = $data['lich_hoc_tuan'] ?? [];
                 ?>
                 <?php if (empty($lich_hoc_tuan)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">Chưa có lịch học cho tuần này.</td></tr>
                 <?php else: ?>
                    <?php foreach ($lich_hoc_tuan as $lich): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($lich['thu'] ?? '?'); ?></td>
                            <td><?php echo htmlspecialchars($lich['tiet'] ?? '?'); ?></td>
                            <td><?php echo htmlspecialchars($lich['mon'] ?? '?'); ?></td>
                            <td><?php echo htmlspecialchars($lich['gv'] ?? '?'); ?></td>
                            <td><?php echo htmlspecialchars($lich['phong'] ?? '?'); ?></td>
                            <td><button class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Xem</button></td>
                        </tr>
                    <?php endforeach; ?>
                 <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- Cards chức năng -->
    <section class="mb-5">
      <h4 class="fw-bold text-primary mb-3">Chức Năng Chính</h4>
      <div class="row g-3">
        <div class="col-md-3">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="bi bi-journal-text fs-2 text-primary mb-3"></i>
              <h5 class="fw-bold">Bài Tập</h5>
              <p class="text-muted small">Xem & nộp bài</p>
              <!-- SỬA LINK Ở ĐÂY -->
              <a href="<?php echo BASE_URL; ?>/baitap/index" class="btn btn-outline-primary mt-2">Truy cập</a>
              <!-- HẾT SỬA LINK -->
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="bi bi-bar-chart-line fs-2 text-primary mb-3"></i>
              <h5 class="fw-bold">Xem Điểm</h5>
              <p class="text-muted small">Chi tiết điểm theo kỳ</p>
              <!-- Sửa link nếu có Controller/Action riêng -->
              <a href="#" class="btn btn-outline-primary mt-2">Xem</a>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="bi bi-calendar-check fs-2 text-primary mb-3"></i>
              <h5 class="fw-bold">Xem Lịch</h5>
              <p class="text-muted small">Thời khóa biểu cá nhân</p>
              <!-- Sửa link nếu có Controller/Action riêng -->
              <a href="#" class="btn btn-outline-primary mt-2">Xem</a>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="bi bi-graph-up fs-2 text-primary mb-3"></i>
              <h5 class="fw-bold">Thống Kê</h5>
              <p class="text-muted small">Báo cáo học tập cá nhân</p>
               <!-- Sửa link nếu có Controller/Action riêng -->
              <a href="#" class="btn btn-outline-primary mt-2">Xem</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <footer class="text-center mt-5 text-muted small pb-3">© <?php echo date("Y"); ?> THPT Manager | Dành cho Học Sinh</footer>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Toggle sidebar (mobile)
    document.getElementById('toggleSidebar').addEventListener('click', ()=> document.getElementById('sidebar').classList.toggle('show'));

    // Charts (Cần lấy dữ liệu động từ PHP)
    // SỬA LỖI: Lấy dữ liệu từ $data, không phải $data['student_data']
    const baiTapStats = <?php echo json_encode($data['bai_tap_stats'] ?? ['da_nop' => 0, 'chua_nop' => 0]); ?>; // Default về 0
    const diemMon = <?php echo json_encode($data['diem_tb_mon'] ?? []); ?>; // Default về mảng rỗng
    // HẾT SỬA

    // --- Biểu đồ Pie ---
    const pieCtx = document.getElementById('submitPie');
    // Kiểm tra xem có dữ liệu không trước khi vẽ
    if (pieCtx && (parseInt(baiTapStats.da_nop) > 0 || parseInt(baiTapStats.chua_nop) > 0)) { // Thêm parseInt
        new Chart(pieCtx, {
          type: 'pie',
          data: {
            labels: ['Đã hoàn thành', 'Chưa hoàn thành'],
            datasets: [{
                 // Đảm bảo có giá trị số
                 data: [parseInt(baiTapStats.da_nop) || 0, parseInt(baiTapStats.chua_nop) || 0],
                 backgroundColor: ['#198754', '#ffc107'] // Green, Yellow
             }]
          },
          options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}}}
        });
    } else if(pieCtx) {
        // Hiển thị thông báo nếu không có dữ liệu
         const ctx = pieCtx.getContext('2d');
         ctx.textAlign = 'center'; // Căn giữa text
         ctx.fillStyle = '#6c757d'; // Màu xám
         ctx.fillText("Chưa có dữ liệu bài tập.", pieCtx.width / 2, pieCtx.height / 2);
    }


    // --- Biểu đồ Bar ---
    const barCtx = document.getElementById('subjectBar');
     // Kiểm tra xem có dữ liệu không
    if (barCtx && Object.keys(diemMon).length > 0) {
        new Chart(barCtx, {
          type: 'bar',
          data: {
            labels: Object.keys(diemMon),
            datasets:[{label:'Điểm TB', data: Object.values(diemMon), backgroundColor:'#0d6efd'}] // Blue
          },
          options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true, max:10}}}
        });
    } else if(barCtx) {
        // Hiển thị thông báo nếu không có dữ liệu
        const ctx = barCtx.getContext('2d');
        ctx.textAlign = 'center';
        ctx.fillStyle = '#6c757d';
        ctx.fillText("Chưa có dữ liệu điểm.", barCtx.width / 2, barCtx.height / 2);
    }
  </script>
</body>
</html>
