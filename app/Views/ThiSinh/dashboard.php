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
    :root{ --primary:#8B5CF6; --primary-600:#A78BFA; --muted:#6c757d; --bg:#faf7ff; }
    body{font-family:'Roboto',sans-serif;background-color:var(--bg);}
    .sidebar{width:300px;min-width:300px;position:fixed;height:100vh;background:#fff;box-shadow:2px 0 10px rgba(0,0,0,0.08);overflow-y:auto;transition:transform .3s;z-index:1000;}
    .main-content{margin-left:300px;transition:margin-left .3s;min-height:100vh;padding:1rem;}
    .profile-section{padding:1.5rem 1rem;border-bottom:1px solid #eee;text-align:center;}
    .nav-link{border-radius:8px;margin-bottom:.25rem;transition:background .2s;color:var(--primary);}
    .nav-link:hover{background-color:#f3e9ff}
    .card{border:none;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);display:flex;flex-direction:column;height:100%;}
    .card-body{flex:1;display:flex;flex-direction:column;justify-content:center;padding:1.25rem;}
    .chart-container{position:relative;height:300px;width:100%;}
    .table th,.table td{vertical-align:middle;padding:.6rem .5rem}
    .btn-primary{background:var(--primary);border-color:var(--primary);}
    @media (max-width:992px){.sidebar{transform:translateX(-100%)}.main-content{margin-left:0}.sidebar.show{transform:translateX(0)}}
    .fade-in{animation:fadeIn .45s ease-in}
    @keyframes fadeIn{from{opacity:0}to{opacity:1}}
  </style>
</head>
<body>
  <div class="sidebar" id="sidebar">
    <div class="profile-section">
      <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($data['info']['ho_ten'] ?? 'TS'); ?>&background=8B5CF6&color=fff" alt="TS" class="rounded-circle mb-2" style="width:80px;height:80px;border:3px solid var(--primary);">
      <h5 class="fw-bold" style="color:var(--primary)"><?php echo $data['info']['ho_ten'] ?? 'Thí Sinh'; ?></h5>
      <p class="text-muted mb-0 small">SBD: <?php echo $data['info']['so_bao_danh'] ?? '---'; ?></p>
      <p class="text-muted small">Trường THCS: <?php echo $data['info']['truong_thcs'] ?? '---'; ?></p>
      
      <div class="row g-1 mt-2 text-center border-top pt-2">
        <div class="col-4">
            <small class="text-muted" style="font-size: 0.7rem;">NV Đã ĐK</small><br>
            <strong class="text-primary fs-6"><?php echo $data['nv_count']; ?></strong>
        </div>
        <div class="col-4 border-start border-end">
            <small class="text-muted" style="font-size: 0.7rem;">Kết Quả</small><br>
            <?php if(isset($data['ket_qua']['trang_thai']) && $data['ket_qua']['trang_thai'] == 'Dau'): ?>
                <strong class="text-success fs-6">Đậu</strong>
            <?php elseif(isset($data['ket_qua']['trang_thai']) && $data['ket_qua']['trang_thai'] == 'Truot'): ?>
                <strong class="text-danger fs-6">Trượt</strong>
            <?php else: ?>
                <strong class="text-muted fs-6">...</strong>
            <?php endif; ?>
        </div>
        <div class="col-4">
            <small class="text-muted" style="font-size: 0.7rem;">Xác Nhận</small><br>
            <strong class="text-info fs-6">
                <?php echo ($data['ket_qua']['trang_thai_xac_nhan'] ?? '') == 'Xac_nhan_nhap_hoc' ? 'OK' : '--'; ?>
            </strong>
        </div>
      </div>
    </div>

    <ul class="nav flex-column px-2 py-3">
      <li class="nav-item"><a class="nav-link" href="#overview"><i class="bi bi-speedometer2 me-2"></i>Tổng Quan</a></li>
      <li class="nav-item"><a class="nav-link" href="#thong-tin-thi"><i class="bi bi-file-earmark-text me-2"></i>Thông Tin Thi</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/ThiSinh/nguyenVong"><i class="bi bi-flag me-2"></i>Quản Lý Nguyện Vọng</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/ThiSinh/quan_ly_ho_so"><i class="bi bi-person-vcard me-2"></i>Quản Lý Hồ Sơ</a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/ThisinhNhaphoc/nhapHoc"><i class="bi bi-journal-check me-2"></i>Đăng Ký Nhập Học</a></li>
      <li class="nav-item mt-auto p-3 border-top"><a class="nav-link text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất</a></li>
    </ul>
  </div>

  <div class="main-content fade-in">
    <nav class="navbar navbar-expand-lg bg-white shadow-sm mb-4 rounded-3 p-2">
      <div class="container-fluid">
        <button class="btn btn-outline-primary d-lg-none me-2 rounded-pill" id="toggleSidebar"><i class="bi bi-list"></i> Menu</button>
        <a class="navbar-brand fw-bold" style="color:var(--primary)" href="#">THPT Manager - Cổng Thí Sinh</a>
        
        <div class="ms-auto d-flex align-items-center">
             <span class="me-3 d-none d-md-block text-muted">Chào mừng, <strong><?php echo $data['info']['ho_ten']; ?></strong></span>
             <a href="<?php echo BASE_URL; ?>/auth/logout" class="btn btn-sm btn-outline-danger rounded-pill">Thoát</a>
        </div>
      </div>
    </nav>

    <section id="overview" class="mb-5">
      <h2 class="fw-bold" style="color:var(--primary)">Tổng Quan Hồ Sơ</h2>
      <div class="row g-3">
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <i class="bi bi-flag fs-1" style="color:var(--primary)"></i>
              <h5 class="fw-bold mt-2" style="color:var(--primary)">Nguyện Vọng</h5>
              <h3 class="fw-bold"><?php echo $data['nv_count']; ?></h3>
              <small class="text-muted">
                  <?php 
                    if (!empty($data['nguyen_vong'])) {
                        echo "NV1: " . htmlspecialchars($data['nguyen_vong'][0]['ten_truong']); 
                    } else { echo "Chưa đăng ký"; }
                  ?>
              </small>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <i class="bi bi-award fs-1 mb-2 <?php echo $data['kq_class'] ?? 'text-secondary'; ?>"></i>
              <!-- <h5 class="fw-bold <?php echo $data['kq_class'] ?? 'text-secondary'; ?>">Kết Quả Tuyển Sinh</h5>
              <h4 class="fw-bold"><?php echo $data['kq_text']; ?></h4> -->
              <h5 class="fw-bold <?php echo (isset($data['ket_qua']['trang_thai']) && $data['ket_qua']['trang_thai'] == 'Dau') ? 'text-success' : 'text-secondary'; ?>">
                Kết Quả Tuyển Sinh
            </h5>

            <div class="mt-2">
                <?php if (isset($data['ket_qua']['trang_thai']) && $data['ket_qua']['trang_thai'] == 'Dau'): ?>
                    <h3 class="fw-bold text-success mb-1">TRÚNG TUYỂN</h3>
                    <div class="text-primary fw-bold fs-5 text-uppercase">
                        <?php echo $data['ket_qua']['truong_trung_tuyen']; ?>
                    </div>
                    <small class="text-muted fw-bold">
                        (Nguyện vọng số <?php echo $data['ket_qua']['nguyen_vong_trung_tuyen']; ?>)
                    </small>

                <?php elseif (isset($data['ket_qua']['trang_thai']) && $data['ket_qua']['trang_thai'] == 'Truot'): ?>
                    <h3 class="fw-bold text-danger">KHÔNG TRÚNG TUYỂN</h3>
                    <small class="text-muted">Rất tiếc, bạn chưa đủ điều kiện.</small>

                <?php else: ?>
                    <h4 class="fw-bold text-muted">Chưa công bố</h4>
                    <small class="text-muted">Hệ thống đang xử lý...</small>
                <?php endif; ?>
            </div>
              <small class="text-muted">
                  <?php 
                    // Dùng tổng điểm nếu controller đã truyền, fallback tính lại
                    $tongDiem = $data['tong_diem'] ?? (
                        (($data['diem']['diem_toan'] ?? 0) * 2) + 
                        (($data['diem']['diem_van'] ?? 0) * 2) + 
                        ($data['diem']['diem_anh'] ?? 0)
                    );
                    echo ($tongDiem > 0) ? "Tổng điểm xét tuyển: " . $tongDiem : "Đang chờ chấm điểm";
                  ?>
              </small>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card text-center">
            <div class="card-body">
              <i class="bi bi-journal-check fs-1" style="color:var(--primary)"></i>
              <h5 class="fw-bold mt-2" style="color:var(--primary)">Trạng Thái Hồ Sơ</h5>
              <h3 class="fw-bold"><?php echo $data['xn_text']; ?></h3>
              <small class="text-muted">Hạn xác nhận: 15/06/2025</small>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="mb-5" id="thong-tin-thi">
      <div class="row g-4">
        <div class="col-md-8">
          <div class="card h-100">
            <div class="card-header bg-light d-flex justify-content-between">
              <h6 class="mb-0 fw-bold" style="color:var(--primary)">Biểu Đồ Điểm Thi Của Bạn</h6>
              <span class="badge bg-primary">Năm 2025</span>
            </div>
            <div class="card-body p-0">
              <div class="chart-container p-3">
                <canvas id="scoreBar"></canvas>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold" style="color:var(--primary)">Chi Tiết Điểm Số</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Toán (Hệ số 2)
                            <span class="badge bg-primary rounded-pill fs-6"><?php echo $data['diem']['diem_toan']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Ngữ Văn (Hệ số 2)
                            <span class="badge bg-success rounded-pill fs-6"><?php echo $data['diem']['diem_van']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Tiếng Anh (Hệ số 1)
                            <span class="badge bg-info rounded-pill fs-6"><?php echo $data['diem']['diem_anh']; ?></span>
                        </li>
                        <li class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center mt-3 fw-bold">
                            TỔNG ĐIỂM XÉT TUYỂN
                          <span class="fs-5"><?php echo $data['tong_diem'] ?? ((($data['diem']['diem_toan'] ?? 0) * 2) + (($data['diem']['diem_van'] ?? 0) * 2) + ($data['diem']['diem_anh'] ?? 0)); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
      </div>
    </section>

    <section id="nguyen-vong" class="mb-5">
      <h4 class="fw-bold" style="color:var(--primary)">Danh Sách Nguyện Vọng Đã Đăng Ký</h4>
      <div class="card">
        <div class="card-body table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead class="table-secondary">
              <tr>
                  <th>Thứ Tự</th>
                  <th>Trường THPT</th>
                  <th>Trạng Thái</th>
              </tr>
            </thead>
            <tbody>
              <?php if(!empty($data['nguyen_vong'])): ?>
                  <?php foreach($data['nguyen_vong'] as $nv): ?>
                  <tr>
                    <td class="text-center fw-bold">NV <?php echo $nv['thu_tu_nguyen_vong']; ?></td>
                    <td class="text-primary fw-bold"><?php echo htmlspecialchars($nv['ten_truong']); ?></td>
                
                    <td>
                        <?php 
                            // Logic hiển thị trạng thái từng NV
                            if (isset($data['ket_qua']['trang_thai']) && $data['ket_qua']['trang_thai'] == 'Dau') {
                                // Nếu đậu và đúng trường này
                                if ($data['ket_qua']['truong_trung_tuyen'] == $nv['ten_truong']) {
                                    echo '<span class="badge bg-success">TRÚNG TUYỂN</span>';
                                } else {
                                    echo '<span class="badge bg-secondary">Không trúng tuyển</span>';
                                }
                            } else {
                                echo '<span class="badge bg-info">Đã ghi nhận</span>';
                            }
                        ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
              <?php else: ?>
                  <tr><td colspan="4" class="text-center text-muted">Chưa có dữ liệu nguyện vọng.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <section class="mb-5" id="nhap-hoc">
      <h4 class="fw-bold" style="color:var(--primary)">Chức Năng Thí Sinh</h4>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="card h-100 border-primary">
            <div class="card-body text-center">
              <i class="bi bi-journal-check fs-1 text-primary"></i>
              <h5 class="fw-bold mt-2">Xác Nhận Nhập Học Trực Tuyến</h5>
              <p class="text-muted small">Nếu bạn đã trúng tuyển, vui lòng xác nhận nhập học trước ngày 15/06.</p>
              <?php if(isset($data['ket_qua']['trang_thai']) && $data['ket_qua']['trang_thai'] == 'Dau'): ?>
                  <a href="<?php echo BASE_URL; ?>/ThisinhNhaphoc/nhapHoc" class="btn btn-primary mt-2">
                      <i class="bi bi-arrow-right-circle me-2"></i>Tiến hành Xác Nhận
                  </a>
              <?php else: ?>
                  <button class="btn btn-secondary mt-2" disabled>Chưa mở / Chưa trúng tuyển</button>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="bi bi-download fs-1" style="color:var(--primary)"></i>
              <h5 class="fw-bold mt-2">Xuất Giấy Báo Điểm</h5>
              <p class="text-muted small">Tải xuống file PDF giấy báo điểm thi chính thức.</p>
              <button class="btn btn-outline-primary mt-2" onclick="window.print()">In Kết Quả</button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <footer class="text-center mt-5 text-muted small pb-3">© 2025 THPT Manager | Dành cho Thí Sinh</footer>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('toggleSidebar').addEventListener('click', ()=> document.getElementById('sidebar').classList.toggle('show'));

    // Dữ liệu điểm từ PHP đổ vào JS
    const diemToan = <?php echo $data['diem']['diem_toan']; ?>;
    const diemVan = <?php echo $data['diem']['diem_van']; ?>;
    const diemAnh = <?php echo $data['diem']['diem_anh']; ?>;

    // Chart: Biểu đồ điểm
    const ctx = document.getElementById('scoreBar');
    if(ctx) {
        new Chart(ctx, {
          type:'bar',
          data:{
            labels:['Toán', 'Ngữ Văn', 'Tiếng Anh'],
            datasets:[{
                label:'Điểm Thi', 
                data:[diemToan, diemVan, diemAnh], 
                backgroundColor:['#8B5CF6', '#10B981', '#3B82F6'],
                borderRadius: 5
            }]
          },
          options:{
              responsive:true,
              maintainAspectRatio:false,
              scales:{
                  y:{beginAtZero:true, max:10, grid: { borderDash: [2, 2] }},
                  x:{grid: { display: false }}
              },
              plugins: { legend: { display: false } }
          }
        });
    }
  </script>
</body>
</html>