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
        /* (Các style khác của ní...) */
    </style>
</head>

<body>
    <?php
        $hoc_sinh_info = $data['hoc_sinh_info'] ?? ['ten_con' => 'N/A', 'ten_lop' => 'N/A'];
        $hoa_don_count = $data['hoa_don_count'] ?? 0;
        $phieu_vang_count = $data['phieu_vang_count'] ?? 0;
        $bang_diem = $data['bang_diem'] ?? [];
        
        // Chuẩn bị data cho ChartJS
        $chart_labels = json_encode(array_keys($bang_diem));
        $chart_data = json_encode(array_column($bang_diem, 'TB'));
    ?>

    <div class="sidebar" id="sidebar">
        <div class="profile-section">
            <img src="https://via.placeholder.com/80x80?text=PH" alt="Profile" class="rounded-circle mb-2" style="width: 80px; height: 80px; border: 3px solid #dc3545;">
            <h5 class="fw-bold text-danger mb-1"><?php echo htmlspecialchars($data['user_name']); ?></h5>
            <p class="text-muted mb-0 small">Mã PH: <?php echo htmlspecialchars($data['user_id']); ?></p>
            <p class="text-muted small">Con: <?php echo htmlspecialchars($hoc_sinh_info['ten_con'] . ' - ' . $hoc_sinh_info['ten_lop']); ?></p>
            <div class="row g-1 mt-2 text-center">
                <div class="col-4"><small class="text-muted">Hóa Đơn</small><br><strong class="text-danger fs-6"><?php echo $hoa_don_count; ?></strong></div>
                <div class="col-4"><small class="text-muted">Phiếu Vắng</small><br><strong class="text-warning fs-6"><?php echo $phieu_vang_count; ?></strong></div>
                <div class="col-4"><small class="text-muted">Điểm TB</small><br><strong class="text-success fs-6">...</strong></div>
            </div>
        </div>

        <ul class="nav flex-column px-2">
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-speedometer2 me-2"></i>Tổng Quan</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-file-person me-2"></i>Xem Hồ Sơ HS</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/thanhtoan/index"><i class="bi bi-credit-card me-2"></i>Thanh Toán</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-envelope-check me-2"></i>Xin Phép Vắng</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-calendar-event me-2"></i>Lịch Học</a></li>
            <li class="nav-item mt-auto p-3 border-top"><a class="nav-link text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất</a></li>
        </ul>
    </div>

    <div class="main-content fade-in">
        <nav class="navbar navbar-expand-lg bg-white shadow-sm mb-4 rounded-3 p-2">
            <div class="container-fluid">
                <button class="btn btn-outline-danger d-lg-none me-2 rounded-pill" id="toggleSidebar"><i class="bi bi-list"></i> Menu</button>
                <a class="navbar-brand fw-bold text-danger" href="#">THPT Manager - Phụ Huynh</a>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item dropdown">
                        <a class="dropdown-toggle nav-link p-2" href="#" data-bs-toggle="dropdown">
                            <img src="https://via.placeholder.com/32x32?text=PH" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                            <span><?php echo htmlspecialchars($data['user_name']); ?></span>
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
            <h2 class="fw-bold text-danger mb-4">Tổng Quan Con (<?php echo htmlspecialchars($hoc_sinh_info['ten_con'] . ' - ' . $hoc_sinh_info['ten_lop']); ?>)</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-credit-card fs-1 text-danger mb-3"></i>
                            <h5 class="fw-bold text-danger">Hóa Đơn Chờ Thanh Toán</h5>
                            <h3 class="fw-bold text-danger"><?php echo $hoa_don_count; ?></h3>
                            <small class="text-muted">(Bấm "Thanh Toán" ở menu để xem chi tiết)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-envelope-check fs-1 text-warning mb-3"></i>
                            <h5 class="fw-bold text-warning">Phiếu Vắng Chờ Duyệt</h5>
                            <h3 class="fw-bold text-warning"><?php echo $phieu_vang_count; ?></h3>
                            <small class="text-muted">(Bấm "Xin Phép Vắng" ở menu để xem)</small>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="chart" class="mb-5">
            <h4 class="fw-bold text-danger mb-3">Biểu Đồ Kết Quả Học Tập (TB Môn)</h4>
            <div class="card">
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="gradeChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

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
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bang_diem)): ?>
                                <tr><td colspan="6" class="text-center p-3">Chưa có dữ liệu điểm.</td></tr>
                            <?php else: ?>
                                <?php foreach ($bang_diem as $mon => $diem): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($mon); ?></td>
                                        <td><?php echo implode(', ', $diem['DiemMieng']); ?></td>
                                        <td><?php echo implode(', ', $diem['Diem15Phut']); ?></td>
                                        <td><?php echo implode(', ', $diem['Diem1Tiet']); ?></td>
                                        <td><?php echo implode(', ', $diem['DiemHocKy']); ?></td>
                                        <td><strong><?php echo $diem['TB']; ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <div class="modal fade" id="modalDiem" ...> ... </div>

        <footer class="text-center mt-5 text-muted small pb-3">
            © 2025 THPT Manager | Dành cho Phụ Huynh
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // --- SỬA: NẠP DATA CHART TỪ PHP ---
        const ctx = document.getElementById('gradeChart');
        const chartLabels = <?php echo $chart_labels; ?>;
        const chartData = <?php echo $chart_data; ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels, // Dùng data thật
                datasets: [{
                    label: 'Điểm Trung Bình',
                    data: chartData, // Dùng data thật
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