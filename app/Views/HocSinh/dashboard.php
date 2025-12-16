<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard Học Sinh | THPT Manager</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary: #0d6efd;
            --secondary: #6c757d;
            --bg: #f8f9fa;
            --sidebar-width: 280px;
        }
        body { font-family: 'Roboto', sans-serif; background-color: var(--bg); color: #333; }
        .sidebar { width: var(--sidebar-width); position: fixed; height: 100vh; background: #fff; box-shadow: 2px 0 10px rgba(0,0,0,0.05); z-index: 1000; transition: 0.3s; display: flex; flex-direction: column; }
        .profile-section { padding: 25px 15px; text-align: center; border-bottom: 1px dashed #eee; background: linear-gradient(180deg, #e3f2fd 0%, #fff 100%); }
        .nav-link { color: #555; padding: 12px 20px; font-weight: 500; margin: 4px 10px; border-radius: 8px; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { background-color: #e3f2fd; color: var(--primary); }
        .main-content { margin-left: var(--sidebar-width); padding: 25px; transition: 0.3s; }
        .stat-card { border: none; border-radius: 15px; background: #fff; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); transition: 0.2s; cursor: pointer; height: 100%; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08); }
        .stat-icon { width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 1.5rem; margin-bottom: 15px; }
        .chart-box { background: #fff; border-radius: 15px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); height: 100%; }
        @media (max-width: 992px) { .sidebar { transform: translateX(-100%); } .main-content { margin-left: 0; } .sidebar.show { transform: translateX(0); } }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="profile-section">
        <?php 
            $student       = $data['student_info'] ?? [];
            $ho_ten        = $student['ho_ten'] ?? $_SESSION['user_name'] ?? 'Học Sinh';
            $ten_lop       = $student['ten_lop'] ?? 'Chưa phân lớp';
            $ma_hs         = $student['ma_hoc_sinh'] ?? '---';
            $ma_lop_debug  = $student['ma_lop'] ?? 'N/A';
            $user_id_debug = $data['user_id'] ?? 'N/A';
            $nien_khoa     = $student['nien_khoa'] ?? 'N/A';
            $avatar        = 'https://cdn-icons-png.flaticon.com/512/3135/3135823.png';
            $is_incomplete = empty($student) || $ten_lop === 'Chưa phân lớp';
        ?>

        <img src="<?= htmlspecialchars($avatar) ?>" width="80" height="80"
             class="rounded-circle mb-3 border border-3 border-white shadow-sm" alt="Avatar">

        <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($ho_ten) ?></h6>
        <div class="badge bg-primary bg-opacity-10 text-primary mb-2">
            Học Sinh - Lớp <?= htmlspecialchars($ten_lop) ?>
        </div>

        <div class="d-flex justify-content-center gap-3 mt-2 flex-wrap text-center">
            <div class="lh-1"><small class="text-muted" style="font-size: 0.7rem">MÃ HS</small><br><span class="fw-bold text-dark"><?= htmlspecialchars($ma_hs) ?></span></div>
            <div class="lh-1 border-start ps-3"><small class="text-muted" style="font-size: 0.7rem">USER ID</small><br><span class="fw-bold text-danger"><?= htmlspecialchars($user_id_debug) ?></span></div>
            <div class="lh-1 border-start ps-3"><small class="text-muted" style="font-size: 0.7rem">MÃ LỚP</small><br><span class="fw-bold text-danger"><?= htmlspecialchars($ma_lop_debug) ?></span></div>
            <div class="lh-1 border-start ps-3"><small class="text-muted" style="font-size: 0.7rem">NIÊN KHÓA</small><br><span class="fw-bold text-dark"><?= htmlspecialchars($nien_khoa) ?></span></div>
        </div>
    </div>

    <ul class="nav flex-column mt-3">
        <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-grid-fill me-2"></i> Tổng Quan</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/baitap/index"><i class="bi bi-journal-text me-2"></i> Bài Tập & Nộp Bài</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/hocsinh/diemdanh"><i class="bi bi-calendar-check me-2"></i> Điểm Danh</a></li>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/hocsinh/bangdiem">
                <i class="bi bi-bar-chart-line-fill me-2"></i> Bảng Điểm
            </a>
        </li>
        <!-- Tài liệu học tập cho HS -->
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/tailieu/hienThi">
                <i class="bi bi-file-earmark-pdf-fill me-2"></i> Tài Liệu Học Tập
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/hocsinhTkb/index">
                <i class="bi bi-calendar-week me-2"></i> Thời Khóa Biểu
            </a>
        </li>
        <li class="nav-item mt-auto p-3 pt-0">
            <a class="nav-link text-danger bg-danger bg-opacity-10" href="<?= BASE_URL ?>/auth/logout">
                <i class="bi bi-box-arrow-right me-2"></i> Đăng Xuất
            </a>
        </li>
    </ul>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- DESKTOP HEADER (chỉ hiện trên màn hình lớn) -->
    <div class="d-none d-lg-flex justify-content-between align-items-center mb-4 bg-white p-4 rounded-4 shadow-sm">
        <div class="d-flex align-items-center gap-3">
            <img src="https://via.placeholder.com/60x60/0d6efd/ffffff?text=Logo" class="rounded" alt="Logo trường">
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <?= htmlspecialchars($_SESSION['school_name'] ?? 'THPT MANAGER') ?>
                </h4>
                <small class="text-muted">
                    Hệ thống quản lý học tập trực tuyến • Năm học <?= htmlspecialchars($nien_khoa) ?>
                </small>
            </div>
        </div>
        <span class="badge bg-primary fs-5 px-4 py-2">HỌC KỲ 1</span>
    </div>

    <!-- MOBILE HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 d-lg-none bg-white p-3 rounded-3 shadow-sm">
        <button class="btn btn-light" id="toggleSidebar"><i class="bi bi-list fs-4"></i></button>
        <span class="fw-bold text-primary">
            <?= htmlspecialchars($_SESSION['school_name'] ?? 'THPT MANAGER') ?>
        </span>
        <span class="badge bg-primary">HK1</span>
    </div>

    <!-- GREETING -->
    <div class="alert alert-primary border-0 text-white d-flex align-items-center p-4 mb-4 rounded-4"
         style="background: linear-gradient(45deg, #0d6efd, #0099ff);">
        <div class="me-auto">
            <h4 class="fw-bold mb-1">Chào <?= htmlspecialchars($ho_ten) ?>!</h4>
            <p class="mb-0 opacity-75">
                <?php if ($is_incomplete): ?>
                    Hệ thống chỉ hiển thị thông tin cơ bản. Vui lòng liên hệ nhà trường để được phân lớp.
                <?php else: ?>
                    Hôm nay bạn có <strong><?= $data['lich_tuan_count'] ?? 0 ?></strong> tiết học và
                    <strong><?= $data['bai_chua_nop'] ?? 0 ?></strong> bài tập cần hoàn thành.
                <?php endif; ?>
            </p>
            <small class="d-block mt-2 opacity-75">
                <i class="bi bi-building me-1"></i>
                <?= htmlspecialchars($_SESSION['school_name'] ?? 'Trường THPT Chưa Xác Định') ?>
                • Học kỳ 1 • Năm học <?= htmlspecialchars($nien_khoa) ?>
            </small>
        </div>
        <i class="bi bi-backpack2 fs-1 opacity-50 d-none d-sm-block"></i>
    </div>

    <?php if ($is_incomplete): ?>
        <!-- Thông báo chưa phân lớp -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                    <i class="bi bi-exclamation-octagon-fill text-warning" style="font-size: 3rem;"></i>
                    <h4 class="mt-3 text-dark fw-bold">Hồ sơ Học tập chưa hoàn chỉnh</h4>
                    <p class="text-muted">
                        Chúng tôi không tìm thấy thông tin lớp học, điểm số, hoặc thời khóa biểu của bạn.<br>
                        Vui lòng chờ nhà trường phân bổ hoặc liên hệ quản trị viên để hỗ trợ.
                    </p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- STAT CARDS -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card" onclick="window.location.href='<?= BASE_URL ?>/baitap/index'">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-exclamation-circle-fill"></i></div>
                    <h6 class="text-muted text-uppercase fw-bold small">Bài Tập Chưa Nộp</h6>
                    <h3 class="fw-bold mb-0"><?= $data['bai_chua_nop'] ?? 0 ?></h3>
                    <small class="text-warning">Cần làm ngay</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-trophy-fill"></i></div>
                    <h6 class="text-muted text-uppercase fw-bold small">Điểm Trung Bình HK</h6>
                    <h3 class="fw-bold mb-0"><?= $data['diem_tb_hk'] ?? '--' ?></h3>
                    <small class="text-success">Tạm tính</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card" onclick="window.location.href='#lich-hoc'">
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-calendar-event-fill"></i></div>
                    <h6 class="text-muted text-uppercase fw-bold small">Lịch Học Tuần Này</h6>
                    <h3 class="fw-bold mb-0"><?= $data['lich_tuan_count'] ?? 0 ?> <span class="fs-6 text-muted fw-normal">tiết</span></h3>
                    <small class="text-info">Xem chi tiết bên dưới</small>
                </div>
            </div>
        </div>

        <!-- CHARTS + TIMETABLE (giữ nguyên như cũ) -->
        <!-- ... (phần biểu đồ và thời khóa biểu bạn giữ nguyên hoàn toàn) ... -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="chart-box">
                    <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-bar-chart-fill me-2"></i> Biểu Đồ Điểm Các Môn</h6>
                    <div style="height: 300px; position: relative;">
                        <canvas id="subjectBar"></canvas>
                        <div id="noDataBar" class="position-absolute top-50 start-50 translate-middle text-center text-muted d-none">
                            <i class="bi bi-clipboard-x fs-1 opacity-50"></i><br>Chưa có bảng điểm
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="chart-box">
                    <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-pie-chart-fill me-2"></i> Tình Hình Nộp Bài</h6>
                    <div style="height: 300px; display: flex; justify-content: center; position: relative;">
                        <canvas id="submitPie"></canvas>
                        <div id="noDataPie" class="position-absolute top-50 start-50 translate-middle text-center text-muted d-none">
                            <i class="bi bi-inbox fs-1 opacity-50"></i><br>Chưa có bài tập
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TIMETABLE -->
        <div class="card border-0 shadow-sm rounded-4 mb-5" id="lich-hoc">
            <div class="card-header bg-white border-0 py-3 rounded-top-4">
                <h6 class="fw-bold text-primary mb-0"><i class="bi bi-calendar3 me-2"></i> Thời Khóa Biểu Trong Tuần</h6>
                <p class="mb-0 text-muted small">Thông tin chỉ hiển thị khi bạn đã được phân công đầy đủ.</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Thứ</th><th>Tiết</th><th>Môn Học</th><th>Giáo Viên</th><th>Phòng</th><th class="text-end pe-4">Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $lich = $data['lich_hoc_tuan'] ?? []; 
                            
                            // Sắp xếp theo thứ trước, rồi đến tiết
                            if (!empty($lich)) {
                                usort($lich, function($a, $b) {
                                    // Chuyển "Thứ 2" -> 2, "Thứ 3" -> 3...
                                    $thu_a = (int)filter_var($a['thu'], FILTER_SANITIZE_NUMBER_INT);
                                    $thu_b = (int)filter_var($b['thu'], FILTER_SANITIZE_NUMBER_INT);
                                    
                                    if ($thu_a == $thu_b) {
                                        return ($a['tiet'] ?? 0) <=> ($b['tiet'] ?? 0); // Sắp theo tiết
                                    }
                                    return $thu_a <=> $thu_b; // Sắp theo thứ
                                });
                            }
                            ?>
                            
                            <?php if (empty($lich)): ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">
                                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486754.png" width="50" class="opacity-50 mb-2" alt="Không có lịch">
                                    <p class="mb-0">Không có lịch học tuần này.</p>
                                </td></tr>
                            <?php else: ?>
                                <?php foreach ($lich as $item): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary"><?= htmlspecialchars($item['thu'] ?? '') ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($item['tiet'] ?? '') ?></span></td>
                                        <td class="fw-bold"><?= htmlspecialchars($item['mon'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($item['gv'] ?? 'Chưa có') ?></td>
                                        <td><?= htmlspecialchars($item['phong'] ?? '-') ?></td>
                                        <td class="text-end pe-4"><span class="badge bg-success bg-opacity-10 text-success">Sắp diễn ra</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center p-3 text-muted border-top small">
                    Kiểm tra User ID và Mã lớp trên Sidebar để xác nhận dữ liệu đã được tải đúng.
                </div>
            </div>
        </div>
    <?php endif; ?>

    <footer class="text-center text-muted small pb-4 mt-5">
        &copy; <?= date("Y") ?> <?= htmlspecialchars($_SESSION['school_name'] ?? 'THPT Manager') ?> • Hệ thống quản lý học tập hiện đại
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('toggleSidebar')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });

    const is_incomplete = <?= json_encode($is_incomplete) ?>;

    if (!is_incomplete) {
        const stats   = <?= json_encode($data['bai_tap_stats'] ?? ['da_nop' => 0, 'chua_nop' => 0]) ?>;
        const diemMon = <?= json_encode($data['diem_tb_mon'] ?? []) ?>;

        // Pie Chart
        const pieCtx = document.getElementById('submitPie');
        if (stats.da_nop > 0 || stats.chua_nop > 0) {
            new Chart(pieCtx, { type: 'doughnut', data: { labels: ['Đã Nộp', 'Chưa Nộp'], datasets: [{ data: [stats.da_nop, stats.chua_nop], backgroundColor: ['#198754', '#ffc107'], borderWidth: 0 }] }, options: { responsive: true, cutout: '70%', plugins: { legend: { position: 'bottom' } } } });
        } else {
            pieCtx.classList.add('d-none');
            document.getElementById('noDataPie').classList.remove('d-none');
        }

        // Bar Chart
        const barCtx = document.getElementById('subjectBar');
        if (Object.keys(diemMon).length > 0) {
            new Chart(barCtx, { type: 'bar', data: { labels: Object.keys(diemMon), datasets: [{ label: 'Điểm TB', data: Object.values(diemMon), backgroundColor: '#0d6efd', borderRadius: 5, barThickness: 30 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 10 } } } });
        } else {
            barCtx.classList.add('d-none');
            document.getElementById('noDataBar').classList.remove('d-none');
        }
    }
</script>
</body>
</html>