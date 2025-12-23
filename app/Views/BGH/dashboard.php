<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Quản Lý THPT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Segoe UI', Roboto, sans-serif; background-color: #f3f4f6; }
        
        /* 1. SIDEBAR THEO VAI TRÒ */
        .sidebar { width: 280px; position: fixed; height: 100vh; background: #fff; z-index: 1000; box-shadow: 4px 0 15px rgba(0,0,0,0.05); transition: 0.3s; }
        
        /* Profile Section: Màu nền thay đổi theo role cao nhất */
        .profile-section { padding: 30px 20px; text-align: center; color: white; }
        
        /* BGH: Xanh Dương Đậm (Quyền lực) */
        .role-bgh .profile-section { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); }
        .role-bgh .nav-link.active { color: #0d6efd; background-color: #e8f2ff; border-left: 4px solid #0d6efd; }
        
        /* GVCN: Xanh Lá (Thân thiện, quản lý học sinh) */
        .role-gvcn .profile-section { background: linear-gradient(135deg, #198754 0%, #0f5132 100%); }
        .role-gvcn .nav-link.active { color: #198754; background-color: #e6f8ed; border-left: 4px solid #198754; }
        
        /* GV Bộ Môn: Cam/Vàng (Năng động) */
        .role-gv .profile-section { background: linear-gradient(135deg, #fd7e14 0%, #b35200 100%); }
        .role-gv .nav-link.active { color: #fd7e14; background-color: #fff4e6; border-left: 4px solid #fd7e14; }

        .nav-link { color: #555; padding: 12px 20px; font-weight: 500; margin: 4px 0; transition: 0.2s; border-left: 4px solid transparent; }
        .nav-link:hover { background-color: #f8f9fa; }
        .nav-link i { width: 25px; display: inline-block; text-align: center; }

        /* Main Content */
        .main-content { margin-left: 280px; padding: 30px; transition: 0.3s; }
        
        /* Tabs Custom */
        .nav-tabs { border-bottom: 2px solid #e9ecef; margin-bottom: 20px; }
        .nav-tabs .nav-link { border: none; font-weight: 600; color: #6c757d; padding: 12px 25px; border-radius: 8px 8px 0 0; }
        /* Active Tab Color theo Role */
        .role-bgh .nav-tabs .nav-link.active { color: #0d6efd; border-bottom: 3px solid #0d6efd; background: transparent; }
        .role-gvcn .nav-tabs .nav-link.active { color: #198754; border-bottom: 3px solid #198754; background: transparent; }
        .role-gv .nav-tabs .nav-link.active { color: #fd7e14; border-bottom: 3px solid #fd7e14; background: transparent; }

        /* Cards */
        .stat-card { border: none; border-radius: 15px; padding: 25px; height: 100%; box-shadow: 0 5px 15px rgba(0,0,0,0.03); transition: 0.3s; background: white; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); }
        
        .badge-role { font-size: 0.75rem; padding: 6px 12px; border-radius: 20px; background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); margin-top: 5px; display: inline-block; }

        @media (max-width: 991px) { .sidebar { transform: translateX(-100%); } .sidebar.show { transform: translateX(0); } .main-content { margin-left: 0; } }
    </style>
</head>
<?php
    // Xác định class CSS cho body/sidebar dựa trên quyền cao nhất
    $bodyClass = 'role-gv'; // Mặc định
    if ($data['is_bgh']) $bodyClass = 'role-bgh';
    elseif ($data['is_gvcn']) $bodyClass = 'role-gvcn';
?>
<body class="<?php echo $bodyClass; ?>">

    <div class="sidebar" id="sidebar">
        <div class="profile-section">
            <div class="position-relative d-inline-block mb-3">
                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="rounded-circle border border-3 border-white shadow-sm" width="90">
                <span class="position-absolute bottom-0 end-0 bg-white text-dark rounded-circle p-1 shadow-sm" style="width:30px;height:30px; display:flex; align-items:center; justify-content:center;">
                    <?php if($data['is_bgh']): ?><i class="bi bi-bank2 text-primary"></i>
                    <?php elseif($data['is_gvcn']): ?><i class="bi bi-star-fill text-success"></i>
                    <?php else: ?><i class="bi bi-book-half text-warning"></i><?php endif; ?>
                </span>
            </div>
            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($data['user_name']); ?></h6>
            
            <div class="d-flex flex-column gap-1 align-items-center mt-2">
                <?php if($data['is_bgh']): ?>
                    <span class="badge-role"><i class="bi bi-shield-check me-1"></i>Ban Giám Hiệu</span>
                <?php endif; ?>
                <?php if($data['is_gvcn']): ?>
                    <span class="badge-role"><i class="bi bi-people-fill me-1"></i>CN Lớp <?php echo $data['cn_info']['ten_lop']; ?></span>
                <?php endif; ?>
                <span class="badge-role"><i class="bi bi-briefcase-fill me-1"></i>GV: <?php echo htmlspecialchars($data['gd_mon']); ?></span>
            </div>
        </div>

        <nav class="nav flex-column mt-3 px-2">
            <a class="nav-link active" href="#"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
            
            <?php if($data['is_bgh']): ?>
                <div class="text-uppercase small text-muted fw-bold mt-3 mb-1 ms-3" style="font-size: 0.75rem;">Quản Lý Trường</div>
                <a class="nav-link" href="<?php echo BASE_URL; ?>/bgh/duyetdiem"><i class="bi bi-check2-square"></i> Duyệt Sửa Điểm</a>
                <a class="nav-link" href="#"><i class="bi bi-file-earmark-bar-graph"></i> Báo Cáo Thống Kê</a>
            <?php endif; ?>

            <div class="text-uppercase small text-muted fw-bold mt-3 mb-1 ms-3" style="font-size: 0.75rem;">Giảng Dạy</div>
            <a class="nav-link" href="<?php echo BASE_URL; ?>/giaovien/lichdayview"><i class="bi bi-calendar-week"></i> Lịch Dạy</a>
            <a class="nav-link" href="<?php echo BASE_URL; ?>/giaovien/diemdanh"><i class="bi bi-upc-scan"></i> Điểm Danh</a>
            <a class="nav-link" href="<?php echo BASE_URL; ?>/giaovien/baitap"><i class="bi bi-journal-text"></i> Bài Tập & Điểm</a>

            <div class="mt-auto pt-4 border-top">
                <a class="nav-link text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right"></i> Đăng Xuất</a>
            </div>
        </nav>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-3" id="toggleSidebar"><i class="bi bi-list"></i></button>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <?php echo isset($_SESSION['school_name']) ? $_SESSION['school_name'] : 'THPT MANAGER'; ?>
                    </h5>
                    <small class="text-muted"><i class="bi bi-clock"></i> Học kỳ 1 - Năm học 2025-2026</small>
                </div>
            </div>
            <div class="position-relative me-3 cursor-pointer">
                <i class="bi bi-bell fs-4 text-secondary"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
            </div>
        </div>

        <ul class="nav nav-tabs" id="dashboardTab" role="tablist">
            <?php if($data['is_bgh']): ?>
            <li class="nav-item">
                <button class="nav-link active" id="bgh-tab" data-bs-toggle="tab" data-bs-target="#tab-bgh" type="button"><i class="bi bi-bank2 me-2"></i>Quản Trị Nhà Trường</button>
            </li>
            <?php endif; ?>

            <?php if($data['is_gvcn']): ?>
            <li class="nav-item">
                <button class="nav-link <?php echo (!$data['is_bgh']) ? 'active' : ''; ?>" id="cn-tab" data-bs-toggle="tab" data-bs-target="#tab-cn" type="button"><i class="bi bi-star-fill me-2"></i>Lớp Chủ Nhiệm (<?php echo $data['cn_info']['ten_lop']; ?>)</button>
            </li>
            <?php endif; ?>

            <li class="nav-item">
                <button class="nav-link <?php echo (!$data['is_bgh'] && !$data['is_gvcn']) ? 'active' : ''; ?>" id="gd-tab" data-bs-toggle="tab" data-bs-target="#tab-gd" type="button"><i class="bi bi-book-half me-2"></i>Công Tác Giảng Dạy</button>
            </li>
            
            <li class="nav-item">
                <button class="nav-link" id="phieu-tab" data-bs-toggle="tab" data-bs-target="#tab-phieu" type="button"><i class="bi bi-file-earmark-check me-2"></i>Phiếu Của Tôi</button>
            </li>
        </ul>

        <div class="tab-content" id="dashboardTabContent">
            
            <?php if($data['is_bgh']): ?>
            <div class="tab-pane fade show active" id="tab-bgh">
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-1">Yêu Cầu Sửa Điểm</h6>
                                    <h2 class="mb-0 fw-bold"><?php echo $data['bgh_yeu_cau_count']; ?></h2>
                                </div>
                                <i class="bi bi-envelope-paper fs-1 text-white-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Điểm TB Toàn Trường</h6>
                                    <h2 class="mb-0 fw-bold text-success"><?php echo number_format($data['bgh_diem_tb'], 2); ?></h2>
                                </div>
                                <i class="bi bi-graph-up-arrow fs-1 text-success opacity-25"></i>
                            </div>
                        </div>
                    </div>
                    </div>
                
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="stat-card">
                            <h6 class="fw-bold mb-3 border-start border-4 border-primary ps-2">Thống Kê Điểm Số Các Lớp</h6>
                            <div style="height: 300px;"><canvas id="chartBghBar"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="stat-card">
                            <h6 class="fw-bold mb-3 border-start border-4 border-warning ps-2">Trạng Thái Yêu Cầu</h6>
                            <div style="height: 300px; display:flex; justify-content:center;"><canvas id="chartBghPie"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if($data['is_gvcn']): ?>
            <div class="tab-pane fade <?php echo (!$data['is_bgh']) ? 'show active' : ''; ?>" id="tab-cn">
                
                <!-- <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-success"><i class="bi bi-people-fill me-2"></i>Quản Lý Lớp <?php echo $data['cn_info']['ten_lop']; ?></h5>
                    <button class="btn btn-success btn-sm shadow-sm"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Xuất Báo Cáo Lớp</button>
                </div> -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-success"><i class="bi bi-people-fill me-2"></i>Quản Lý Lớp <?php echo $data['cn_info']['ten_lop']; ?></h5>
                    
                    <div>
                        <a href="<?php echo BASE_URL; ?>/giaovienchunhiem/duyetdon" class="btn btn-warning btn-sm shadow-sm me-2 text-dark fw-bold">
                            <i class="bi bi-envelope-paper me-2"></i>Duyệt Đơn Xin Nghỉ Học
                        </a>

                        <button class="btn btn-success btn-sm shadow-sm"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Xuất Báo Cáo Lớp</button>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card border-bottom border-4 border-success">
                            <h6 class="text-muted small">Sĩ Số</h6>
                            <h3 class="fw-bold text-dark"><?php echo $data['cn_info']['si_so_thuc']; ?> <span class="fs-6 text-muted font-weight-normal">hs</span></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card border-bottom border-4 border-warning">
                            <h6 class="text-muted small">Vắng Học (Hôm nay)</h6>
                            <h3 class="fw-bold text-dark">0</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card border-bottom border-4 border-danger">
                            <h6 class="text-muted small">Tổng Vắng (Cả kỳ)</h6>
                            <?php $tongVang = 0; foreach($data['cn_hs_list'] as $hs) $tongVang += $hs['so_buoi_vang']; ?>
                            <h3 class="fw-bold text-dark"><?php echo $tongVang; ?></h3>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="stat-card">
                            <h6 class="fw-bold text-success mb-3">Danh Sách Học Sinh</h6>
                            <div class="table-responsive" style="max-height: 400px;">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Họ Tên</th>
                                            <th class="text-center">Vắng</th>
                                            <th class="text-center">Điểm TB</th>
                                            <th>Hạnh Kiểm</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $stt = 1; foreach($data['cn_hs_list'] as $hs): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo $stt++; ?>. <?php echo htmlspecialchars($hs['ho_ten']); ?></td>
                                            <td class="text-center">
                                                <?php if($hs['so_buoi_vang'] > 0): ?>
                                                    <span class="badge bg-danger rounded-pill"><?php echo $hs['so_buoi_vang']; ?></span>
                                                <?php else: ?> <span class="text-muted">-</span> <?php endif; ?>
                                            </td>
                                            <td class="text-center fw-bold text-primary"><?php echo $hs['diem_tb_hk'] ?? '--'; ?></td>
                                            <td>
                                                <?php 
                                                    $hk = $hs['hanh_kiem'] ?? 'Chưa xếp';
                                                    $bg = 'secondary';
                                                    if ($hk === 'Tot') $bg = 'success';
                                                    elseif ($hk === 'Kha') $bg = 'info';
                                                    elseif ($hk === 'Dat') $bg = 'warning';
                                                    elseif ($hk === 'ChuaDat') $bg = 'danger';
                                                    echo "<span class='badge bg-$bg bg-opacity-75'>$hk</span>";
                                                ?>
                                            </td>
                                            <!-- <td class="text-end"><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-three-dots"></i></button></td> -->
                                             <td class="text-end">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="openModalChinhSuaCN(
                                                        <?php echo $hs['ma_hoc_sinh']; ?>,
                                                        '<?php echo htmlspecialchars($hs['ho_ten'], ENT_QUOTES); ?>',
                                                        <?php echo (int)$hs['so_buoi_vang']; ?>,
                                                        '<?php echo htmlspecialchars($hs['hanh_kiem'] ?? '', ENT_QUOTES); ?>',
                                                        '<?php echo htmlspecialchars($hs['nhan_xet_gvcn'] ?? '', ENT_QUOTES); ?>'
                                                    )">
                                                    <i class="bi bi-pencil"></i> Sửa
                                                </button>
                                            </td>

                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="stat-card">
                            <h6 class="fw-bold text-success mb-3">Thống Kê Hạnh Kiểm</h6>
                            <div style="height: 300px; display:flex; justify-content:center;"><canvas id="chartCnHk"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="tab-pane fade <?php echo (!$data['is_bgh'] && !$data['is_gvcn']) ? 'show active' : ''; ?>" id="tab-gd">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold" style="color: #fd7e14;"><i class="bi bi-briefcase-fill me-2"></i>Công Tác Giảng Dạy</h5>
                    <div class="gap-2 d-flex">
                        <a href="<?php echo BASE_URL; ?>/tailieu/quanly" class="btn btn-info text-white fw-bold shadow-sm">
                            <i class="bi bi-file-earmark-pdf me-2"></i>Tài Liệu
                        </a>
                        <a href="<?php echo BASE_URL; ?>/giaovien/baitap" class="btn btn-warning text-dark fw-bold shadow-sm">
                            <i class="bi bi-plus-circle-fill me-2"></i>Giao Bài Tập Mới
                        </a>
                    </div>
                </div>

                <!-- BẢNG ĐIỂM MÔN - GV BỘ MÔN -->
                <?php if(!empty($data['gv_diem_mon'])): ?>
                <div class="stat-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h6 class="fw-bold text-warning mb-0">
                            <i class="bi bi-file-earmark-bar-graph me-2"></i>Danh Sách Điểm Môn (Học Kỳ <?php echo $data['gv_hoc_ky']; ?>)
                        </h6>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <!-- FILTER LỚP -->
                            <select id="filterLop" class="form-select form-select-sm" style="width: 140px;">
                                <option value="">-- Tất cả lớp --</option>
                                <?php 
                                    $lopList = [];
                                    foreach($data['gv_diem_mon'] as $row) {
                                        if (!in_array($row['ten_lop'], $lopList)) {
                                            $lopList[] = $row['ten_lop'];
                                        }
                                    }
                                    sort($lopList);
                                    foreach($lopList as $lop): 
                                ?>
                                <option value="<?php echo htmlspecialchars($lop); ?>"><?php echo $lop; ?></option>
                                <?php endforeach; ?>
                            </select>

                            <!-- FILTER MÔN HỌC -->
                            <select id="filterMon" class="form-select form-select-sm" style="width: 160px;">
                                <option value="">-- Tất cả môn --</option>
                                <?php 
                                    $monList = [];
                                    foreach($data['gv_diem_mon'] as $row) {
                                        // Bỏ môn "Chào cờ" và "Sinh hoạt lớp"
                                        if (!in_array(strtolower(trim($row['ten_mon_hoc'])), ['chào cờ', 'sinh hoạt lớp', 'sinh hoạt', 'chào cờ và sinh hoạt'])) {
                                            if (!in_array($row['ten_mon_hoc'], $monList)) {
                                                $monList[] = $row['ten_mon_hoc'];
                                            }
                                        }
                                    }
                                    sort($monList);
                                    foreach($monList as $mon): 
                                ?>
                                <option value="<?php echo htmlspecialchars($mon); ?>"><?php echo $mon; ?></option>
                                <?php endforeach; ?>
                            </select>

                            <!-- FILTER HỌC KỲ -->
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="?hk=HK1" class="btn btn-outline-warning <?php echo ($data['gv_hoc_ky'] === 'HK1') ? 'active' : ''; ?>">HK1</a>
                                <a href="?hk=HK2" class="btn btn-outline-warning <?php echo ($data['gv_hoc_ky'] === 'HK2') ? 'active' : ''; ?>">HK2</a>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 600px;">
                        <table class="table table-hover align-middle" id="tableDiemMon">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 40px;">STT</th>
                                    <th>Lớp</th>
                                    <th>Môn Học</th>
                                    <th>Học Sinh</th>
                                    <th class="text-center">Miệng</th>
                                    <th class="text-center">15 Phút</th>
                                    <th class="text-center">1 Tiết</th>
                                    <th class="text-center fw-bold">TX (x1)</th>
                                    <th class="text-center">GK (x2)</th>
                                    <th class="text-center">CK (x3)</th>
                                    <th class="text-center fw-bold">ĐTB Môn</th>
                                    <th>Xếp Loại</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    foreach($data['gv_diem_mon'] as $row):
                                    // Bỏ môn "Chào cờ" và "Sinh hoạt lớp"
                                    if (in_array(strtolower(trim($row['ten_mon_hoc'])), ['chào cờ', 'sinh hoạt lớp', 'sinh hoạt', 'chào cờ và sinh hoạt'])) {
                                        continue;
                                    }

                                    // Hiển thị tất cả (cả điểm và chưa nhập)
                                    $hasScore = ($row['diem_tb_mon_hk'] !== null && $row['diem_tb_mon_hk'] !== '');
                                ?>
                                <tr class="row-mon" data-lop="<?php echo htmlspecialchars($row['ten_lop']); ?>" data-mon="<?php echo htmlspecialchars($row['ten_mon_hoc']); ?>" style="<?php echo !$hasScore ? 'background-color: #f8f9fa; opacity: 0.7;' : ''; ?>">
                                    <td class="fw-bold text-center text-muted stt-cell">-</td>
                                    <td><span class="badge bg-light text-dark"><?php echo $row['ten_lop']; ?></span></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($row['ten_mon_hoc']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ho_ten']); ?></td>
                                    <td class="text-center"><?php echo ($hasScore && $row['diem_mieng'] !== null) ? number_format($row['diem_mieng'], 2) : '--'; ?></td>
                                    <td class="text-center"><?php echo ($hasScore && $row['diem_15phut'] !== null) ? number_format($row['diem_15phut'], 2) : '--'; ?></td>
                                    <td class="text-center"><?php echo ($hasScore && $row['diem_1tiet'] !== null) ? number_format($row['diem_1tiet'], 2) : '--'; ?></td>
                                    <td class="text-center fw-bold text-warning"><?php echo ($hasScore && $row['diem_tx'] !== null) ? number_format($row['diem_tx'], 2) : '--'; ?></td>
                                    <td class="text-center"><?php echo ($hasScore && $row['diem_gua_ky'] !== null) ? number_format($row['diem_gua_ky'], 2) : '--'; ?></td>
                                    <td class="text-center"><?php echo ($hasScore && $row['diem_cuoi_ky'] !== null) ? number_format($row['diem_cuoi_ky'], 2) : '--'; ?></td>
                                    <td class="text-center fw-bold <?php echo $hasScore ? 'text-primary' : 'text-muted'; ?>">
                                        <?php echo $hasScore ? number_format($row['diem_tb_mon_hk'], 2) : '<em>Chưa nhập</em>'; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            if (!$hasScore) {
                                                echo '<span class="badge bg-secondary bg-opacity-50">---</span>';
                                            } else {
                                                $xlt = $row['xep_loai_mon'] ?? 'Chưa xếp';
                                                $bgXlt = 'secondary';
                                                if ($xlt === 'Gioi') $bgXlt = 'success';
                                                elseif ($xlt === 'Kha') $bgXlt = 'info';
                                                elseif ($xlt === 'Dat') $bgXlt = 'warning';
                                                elseif ($xlt === 'ChuaDat') $bgXlt = 'danger';
                                                echo "<span class='badge bg-$bgXlt bg-opacity-75'>$xlt</span>";
                                            }
                                        ?>
                                    </td>
                                    <td class="text-end">
                                        <?php 
                                        $da_co_diem = ($row['diem_tb_mon_hk'] !== null && $row['diem_tb_mon_hk'] !== '');
                                        if ($da_co_diem): 
                                        ?>
                                            <button class="btn btn-sm btn-warning" 
                                                    onclick="openModalChinhSuaDiem(<?php echo $row['ma_hoc_sinh']; ?>, <?php echo $row['ma_mon_hoc']; ?>, '<?php echo htmlspecialchars($row['ten_mon_hoc'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['ho_ten'], ENT_QUOTES); ?>', <?php echo $row['diem_mieng'] ?? 'null'; ?>, <?php echo $row['diem_15phut'] ?? 'null'; ?>, <?php echo $row['diem_1tiet'] ?? 'null'; ?>, <?php echo $row['diem_gua_ky'] ?? 'null'; ?>, <?php echo $row['diem_cuoi_ky'] ?? 'null'; ?>)">
                                                <i class="bi bi-pencil-fill"></i> Chỉnh sửa
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-primary" 
                                                    onclick="openModalNhapDiem(<?php echo $row['ma_hoc_sinh']; ?>, <?php echo $row['ma_mon_hoc']; ?>, '<?php echo htmlspecialchars($row['ten_mon_hoc'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['ho_ten'], ENT_QUOTES); ?>')">
                                                <i class="bi bi-pencil-square"></i> Nhập
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Container -->
                    <div id="paginationContainer" class="mt-3"></div>
                </div>
                <?php else: ?>
                <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Chưa có dữ liệu điểm môn.</div>
                <?php endif; ?>

                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="stat-card border-start border-4 border-warning">
                            <h6 class="text-muted small fw-bold">Lớp Đang Dạy</h6>
                            <h2 class="fw-bold text-dark mb-2"><?php echo $data['gd_lop_count']; ?></h2>
                            <div>
                                <?php if(!empty($data['gd_lop_list'])) foreach($data['gd_lop_list'] as $lop): ?>
                                    <span class="badge bg-light text-dark border me-1"><?php echo $lop; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card border-start border-4 border-info">
                            <h6 class="text-muted small fw-bold">Tỷ Lệ Nộp Bài TB</h6>
                            <h2 class="fw-bold text-dark mb-0"><?php echo $data['gd_nopbai_pct']; ?>%</h2>
                            <div class="progress mt-2" style="height: 5px;">
                                <div class="progress-bar bg-info" style="width: <?php echo $data['gd_nopbai_pct']; ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card border-start border-4 border-danger">
                            <h6 class="text-muted small fw-bold">Phiên Điểm Danh</h6>
                            <h2 class="fw-bold text-dark mb-0"><?php echo $data['gd_dd_count']; ?></h2>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="stat-card">
                            <h6 class="fw-bold text-warning mb-3">Tình Hình Nộp Bài Theo Lớp</h6>
                            <div style="height: 300px;"><canvas id="chartGdBar"></canvas></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="stat-card">
                            <h6 class="fw-bold text-warning mb-3">Điểm Danh Tổng Quát</h6>
                            <div style="height: 300px; display:flex; justify-content:center;"><canvas id="chartGdPie"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tab Content: Phiếu Yêu Cầu Của Tôi -->
            <div class="tab-pane fade" id="tab-phieu">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text text-warning me-2"></i>Phiếu Yêu Cầu Chỉnh Sửa Điểm</h5>
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-secondary active" onclick="filterPhieu('TatCa')">Tất cả</button>
                            <button class="btn btn-outline-warning" onclick="filterPhieu('ChoDuyet')">Chờ duyệt</button>
                            <button class="btn btn-outline-success" onclick="filterPhieu('DaDuyet')">Đã duyệt</button>
                            <button class="btn btn-outline-danger" onclick="filterPhieu('TuChoi')">Từ chối</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã Phiếu</th>
                                    <th>Học Sinh</th>
                                    <th>Môn Học</th>
                                    <th>Học Kỳ</th>
                                    <th>Ngày Gửi</th>
                                    <th>Trạng Thái</th>
                                    <th style="width: 80px;">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody id="tablePhieuBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bi bi-hourglass-split fs-4"></i><br>
                                        <span class="small">Đang tải dữ liệu...</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // --- CHART JS LOGIC ---

        // 1. Chart BGH
        <?php if($data['is_bgh']): ?>
            <?php 
                $pieBghData = $data['bgh_chart_tron'] ?? [];
                $pieBghValues = [$pieBghData['DaDuyet']??0, $pieBghData['TuChoi']??0, $pieBghData['ChoDuyet']??0];
                
                $barBghData = $data['bgh_chart_cot'] ?? [];
                $lblBgh = []; $valBgh = [];
                foreach($barBghData as $i) { $lblBgh[] = $i['ten_lop']; $valBgh[] = $i['diem_tb']; }
            ?>
            new Chart(document.getElementById('chartBghPie'), {
                type: 'doughnut',
                data: { labels: ['Đã Duyệt', 'Từ Chối', 'Chờ'], datasets: [{ data: <?php echo json_encode($pieBghValues); ?>, backgroundColor: ['#198754', '#dc3545', '#ffc107'] }] },
                options: { responsive: true, cutout: '70%', plugins: { legend: { position: 'bottom' } } }
            });
            new Chart(document.getElementById('chartBghBar'), {
                type: 'bar',
                data: { labels: <?php echo json_encode($lblBgh); ?>, datasets: [{ label: 'Điểm TB', data: <?php echo json_encode($valBgh); ?>, backgroundColor: '#0d6efd', borderRadius: 5 }] },
                options: { scales: { y: { beginAtZero: true, max: 10 } } }
            });
        <?php endif; ?>

        // 2. Chart Chủ Nhiệm
        <?php if($data['is_gvcn']): ?>
            <?php 
                $hkData = $data['cn_chart_hk'] ?? [];
                $hkLabels = array_keys($hkData);
                $hkValues = array_values($hkData);
            ?>
            new Chart(document.getElementById('chartCnHk'), {
                type: 'pie',
                data: { labels: <?php echo json_encode($hkLabels); ?>, datasets: [{ data: <?php echo json_encode($hkValues); ?>, backgroundColor: ['#198754', '#0dcaf0', '#ffc107', '#dc3545', '#6c757d'] }] },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
        <?php endif; ?>

        // 3. Chart Giảng Dạy
        <?php 
            $nopLabels = []; $nopValues = [];
            foreach($data['gd_chart_nop'] as $i) { $nopLabels[] = $i['ten_lop']; $nopValues[] = $i['so_luong_nop']; }
            $ddData = $data['gd_chart_dd'] ?? ['CoMat'=>0, 'Vang'=>0];
        ?>
        new Chart(document.getElementById('chartGdBar'), {
            type: 'bar',
            data: { labels: <?php echo json_encode($nopLabels); ?>, datasets: [{ label: 'Bài nộp', data: <?php echo json_encode($nopValues); ?>, backgroundColor: '#fd7e14', borderRadius: 5 }] },
            options: { scales: { y: { beginAtZero: true } } }
        });
        new Chart(document.getElementById('chartGdPie'), {
            type: 'doughnut',
            data: { labels: ['Có Mặt', 'Vắng'], datasets: [{ data: [<?php echo $ddData['CoMat']; ?>, <?php echo $ddData['Vang']; ?>], backgroundColor: ['#20c997', '#dc3545'] }] },
            options: { responsive: true, cutout: '70%', plugins: { legend: { position: 'bottom' } } }
        });

        // === FILTER LỚP + MÔN - ĐIỂM MÔN + PHÂN TRANG ===
        const filterLop = document.getElementById('filterLop');
        const filterMon = document.getElementById('filterMon');
        let currentPage = 1;
        const rowsPerPage = 20;
        
        function paginateTable() {
            const selectedLop = filterLop ? filterLop.value : '';
            const selectedMon = filterMon ? filterMon.value : '';
            const rows = document.querySelectorAll('#tableDiemMon tbody tr.row-mon');
            
            // Lọc các dòng phù hợp
            let visibleRows = [];
            rows.forEach(row => {
                const rowLop = row.getAttribute('data-lop');
                const rowMon = row.getAttribute('data-mon');
                const lopMatch = selectedLop === '' || rowLop === selectedLop;
                const monMatch = selectedMon === '' || rowMon === selectedMon;
                
                if (lopMatch && monMatch) {
                    visibleRows.push(row);
                }
            });
            
            // Tính tổng số trang
            const totalPages = Math.ceil(visibleRows.length / rowsPerPage);
            
            // Ẩn tất cả, chỉ hiển thị trang hiện tại
            let stt = 0;
            rows.forEach(row => row.style.display = 'none');
            
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            
            for (let i = start; i < end && i < visibleRows.length; i++) {
                const row = visibleRows[i];
                row.style.display = '';
                stt++;
                const sttCell = row.querySelector('.stt-cell');
                if (sttCell) sttCell.textContent = start + stt;
            }
            
            // Cập nhật nút phân trang
            renderPagination(totalPages, visibleRows.length);
        }
        
        function renderPagination(totalPages, totalRows) {
            const paginationContainer = document.getElementById('paginationContainer');
            if (!paginationContainer) return;
            
            if (totalPages <= 1) {
                paginationContainer.innerHTML = '';
                return;
            }
            
            let html = '<nav><ul class="pagination pagination-sm justify-content-center mb-0">';
            
            // Nút Previous
            html += '<li class="page-item ' + (currentPage === 1 ? 'disabled' : '') + '">' +
                    '<a class="page-link" href="#" onclick="changePage(' + (currentPage - 1) + '); return false;">«</a></li>';
            
            // Các nút số trang
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    html += '<li class="page-item ' + (i === currentPage ? 'active' : '') + '">' +
                            '<a class="page-link" href="#" onclick="changePage(' + i + '); return false;">' + i + '</a></li>';
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            
            // Nút Next
            html += '<li class="page-item ' + (currentPage === totalPages ? 'disabled' : '') + '">' +
                    '<a class="page-link" href="#" onclick="changePage(' + (currentPage + 1) + '); return false;">»</a></li>';
            
            html += '</ul></nav>';
            html += '<div class="text-center text-muted small mt-2">Hiển thị ' + 
                    Math.min((currentPage-1)*rowsPerPage+1, totalRows) + '-' + 
                    Math.min(currentPage*rowsPerPage, totalRows) + ' / ' + totalRows + ' dòng</div>';
            
            paginationContainer.innerHTML = html;
        }
        
        function changePage(page) {
            currentPage = page;
            paginateTable();
        }
        
        // Gọi lại khi filter thay đổi
        function applyFiltersWithPagination() {
            currentPage = 1; // Reset về trang 1
            paginateTable();
        }
        
        // Chạy lần đầu khi load trang
        paginateTable();
        
        if (filterLop) filterLop.addEventListener('change', applyFiltersWithPagination);
        if (filterMon) filterMon.addEventListener('change', applyFiltersWithPagination);
        
        // === NHẬP ĐIỂM - MODAL ===
        function openModalNhapDiem(ma_hs, ma_mon, ten_mon, ten_hs) {
            document.getElementById('modal_hs_name').textContent = ten_hs;
            document.getElementById('modal_mon_name').textContent = ten_mon;
            document.getElementById('modal_ma_hs').value = ma_hs;
            document.getElementById('modal_ma_mon').value = ma_mon;
            
            // Reset form
            document.getElementById('formNhapDiem').reset();
            
            const modal = new bootstrap.Modal(document.getElementById('modalNhapDiem'));
            modal.show();
        }
        
        // function submitNhapDiem() {
        //     const form = document.getElementById('formNhapDiem');
        //     if (!form.checkValidity()) {
        //         alert('Vui lòng điền đầy đủ thông tin!');
        //         return;
        //     }
            
        //     const formData = new FormData();
        //     formData.append('ma_hoc_sinh', document.getElementById('modal_ma_hs').value);
        //     formData.append('ma_mon_hoc', document.getElementById('modal_ma_mon').value);
        //     formData.append('ma_hoc_ky', document.getElementById('modal_hoc_ky').value);
        //     formData.append('diem_mieng', document.getElementById('inp_mieng').value);
        //     formData.append('diem_15phut', document.getElementById('inp_15phut').value);
        //     formData.append('diem_1tiet', document.getElementById('inp_1tiet').value);
        //     formData.append('diem_gua_ky', document.getElementById('inp_gk').value);
        //     formData.append('diem_cuoi_ky', document.getElementById('inp_ck').value);
            
        //     fetch('<?php echo BASE_URL; ?>/diemso/nhap', {
        //         method: 'POST',
        //         body: formData
        //     })
        //     .then(res => res.json())
        //     .then(data => {
        //         if (data.success) {
        //             alert(data.message);
        //             location.reload();
        //         } else {
        //             alert('Lỗi: ' + data.message);
        //         }
        //     })
        //     .catch(err => {
        //         alert('Lỗi kết nối: ' + err.message);
        //     });
        // }
        function submitNhapDiem() {
            // 1. Lấy thông tin cơ bản
            const ma_hs = document.getElementById('modal_ma_hs').value;
            const ma_mon = document.getElementById('modal_ma_mon').value;
            const ma_hk = document.getElementById('modal_hoc_ky').value;

            // Danh sách các ID và tên hiển thị để check lỗi cho nhanh
            const fields = [
                { id: 'inp_mieng', name: 'Điểm miệng' },
                { id: 'inp_15phut', name: 'Điểm 15 phút' },
                { id: 'inp_1tiet', name: 'Điểm 1 tiết' },
                { id: 'inp_gk', name: 'Điểm giữa kỳ' },
                { id: 'inp_ck', name: 'Điểm cuối kỳ' }
            ];

            const formData = new FormData();
            formData.append('ma_hoc_sinh', ma_hs);
            formData.append('ma_mon_hoc', ma_mon);
            formData.append('ma_hoc_ky', ma_hk);

            // 2. Vòng lặp kiểm tra dữ liệu (Validation)
            for (let f of fields) {
                let input = document.getElementById(f.id);
                let val = input.value.trim();

                // Check trống
                if (val === "") {
                    alert(` tên ${f.name} không được để trống nhé!`);
                    input.focus();
                    return;
                }

                // Check giá trị từ 0 đến 10
                let num = parseFloat(val);
                if (isNaN(num) || num < 0 || num > 10) {
                    alert(`${f.name} phải là số và nằm trong khoảng từ 0 đến 10!`);
                    input.focus();
                    return;
                }
                
                // Nếu ổn thì thêm vào FormData (đổi tên id sang tên cột trong DB của bác)
                formData.append(f.id.replace('inp_', 'diem_'), num);
            }

            // 3. Gửi dữ liệu lên Server
            fetch('<?php echo BASE_URL; ?>/diemso/nhap', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Ngon lành! " + data.message);
                    location.reload();
                } else {
                    alert('Lỗi từ Server: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Lỗi kết nối: ' + err.message);
            });
        }
        
        // === CHỈNH SỬA ĐIỂM - GỬI PHIẾU YÊU CẦU ===
        function openModalChinhSuaDiem(ma_hs, ma_mon, ten_mon, ten_hs, diem_mieng, diem_15phut, diem_1tiet, diem_gk, diem_ck) {
            document.getElementById('modal_sua_hs_name').textContent = ten_hs;
            document.getElementById('modal_sua_mon_name').textContent = ten_mon;
            document.getElementById('modal_sua_ma_hs').value = ma_hs;
            document.getElementById('modal_sua_ma_mon').value = ma_mon;
            
            // Điền điểm cũ vào form
            document.getElementById('inp_sua_mieng').value = diem_mieng !== null ? diem_mieng : '';
            document.getElementById('inp_sua_15phut').value = diem_15phut !== null ? diem_15phut : '';
            document.getElementById('inp_sua_1tiet').value = diem_1tiet !== null ? diem_1tiet : '';
            document.getElementById('inp_sua_gk').value = diem_gk !== null ? diem_gk : '';
            document.getElementById('inp_sua_ck').value = diem_ck !== null ? diem_ck : '';
            document.getElementById('inp_ly_do').value = '';
            
            const modal = new bootstrap.Modal(document.getElementById('modalChinhSuaDiem'));
            modal.show();
        }
        
        // function submitChinhSuaDiem() {
        //     const form = document.getElementById('formChinhSuaDiem');
        //     if (!form.checkValidity()) {
        //         alert('Vui lòng điền đầy đủ thông tin và lý do chỉnh sửa!');
        //         return;
        //     }
            
        //     const ly_do = document.getElementById('inp_ly_do').value.trim();
        //     if (ly_do === '') {
        //         alert('Vui lòng nhập lý do chỉnh sửa điểm!');
        //         return;
        //     }
            
        //     const formData = new FormData();
        //     formData.append('ma_hoc_sinh', document.getElementById('modal_sua_ma_hs').value);
        //     formData.append('ma_mon_hoc', document.getElementById('modal_sua_ma_mon').value);
        //     formData.append('ma_hoc_ky', document.getElementById('modal_sua_hoc_ky').value);
        //     formData.append('diem_mieng', document.getElementById('inp_sua_mieng').value);
        //     formData.append('diem_15phut', document.getElementById('inp_sua_15phut').value);
        //     formData.append('diem_1tiet', document.getElementById('inp_sua_1tiet').value);
        //     formData.append('diem_gua_ky', document.getElementById('inp_sua_gk').value);
        //     formData.append('diem_cuoi_ky', document.getElementById('inp_sua_ck').value);
        //     formData.append('ly_do', ly_do);
            
        //     fetch('<?php echo BASE_URL; ?>/diemso/guiPhieuChinhSua', {
        //         method: 'POST',
        //         body: formData
        //     })
        //     .then(res => res.json())
        //     .then(data => {
        //         if (data.success) {
        //             alert(data.message + '\nPhiếu đã được gửi đến Ban Giám Hiệu để duyệt.');
        //             bootstrap.Modal.getInstance(document.getElementById('modalChinhSuaDiem')).hide();
        //         } else {
        //             alert('Lỗi: ' + data.message);
        //         }
        //     })
        //     .catch(err => {
        //         alert('Lỗi kết nối: ' + err.message);
        //     });
        // }
        function submitChinhSuaDiem() {
            const form = document.getElementById('formChinhSuaDiem');
            
            // 1. Lấy thông tin định danh
            const ma_hs = document.getElementById('modal_sua_ma_hs').value;
            const ma_mon = document.getElementById('modal_sua_ma_mon').value;
            const ma_hk = document.getElementById('modal_sua_hoc_ky').value;
            const ly_do = document.getElementById('inp_ly_do').value.trim();

            // 2. Danh sách các ô điểm mới cần check
            const fields = [
                { id: 'inp_sua_mieng', name: 'Điểm miệng' },
                { id: 'inp_sua_15phut', name: 'Điểm 15 phút' },
                { id: 'inp_sua_1tiet', name: 'Điểm 1 tiết' },
                { id: 'inp_sua_gk', name: 'Điểm giữa kỳ' },
                { id: 'inp_sua_ck', name: 'Điểm cuối kỳ' }
            ];

            // 3. Validation - Kiểm tra tính hợp lệ của dữ liệu
            if (ly_do === '') {
                alert('Phải nhập lý do chính đang mới duyệt yêu cầu');
                document.getElementById('inp_ly_do').focus();
                return;
            }

            if (ly_do.length < 10) {
                alert('Lý do hơi ngắn quá, phải viết chi tiết nhé!');
                return;
            }

            const formData = new FormData();
            formData.append('ma_hoc_sinh', ma_hs);
            formData.append('ma_mon_hoc', ma_mon);
            formData.append('ma_hoc_ky', ma_hk);
            formData.append('ly_do', ly_do);

            for (let f of fields) {
                let input = document.getElementById(f.id);
                let val = input.value.trim();

                // Check trống
                if (val === "") {
                    alert(`Bạn quên chưa nhập ${f.name} mới rồi!`);
                    input.focus();
                    return;
                }

                // Check giá trị số từ 0-10
                let num = parseFloat(val);
                if (isNaN(num) || num < 0 || num > 10) {
                    alert(`${f.name} mới không hợp lệ (phải từ 0 đến 10)!`);
                    input.focus();
                    return;
                }

                // Nếu ok thì append vào form (đổi id sang tên cột backend yêu cầu)
                formData.append(f.id.replace('inp_sua_', 'diem_'), num);
            }

            // 4. Xác nhận lần cuối trước khi gửi "tối hậu thư" lên BGH
            if (!confirm("Phiếu yêu cầu sẽ được gửi lên Ban Giám Hiệu. Bạn có chắc chắn dữ liệu đã chuẩn chưa?")) {
                return;
            }

            // 5. Gửi API
            fetch('<?php echo BASE_URL; ?>/diemso/guiPhieuChinhSua', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Thành công! ' + data.message + '\nBan theo dõi trạng thái ở tab "Phiếu của tôi" nhé.');
                    // Đóng modal
                    const modalEl = document.getElementById('modalChinhSuaDiem');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                    
                    // Nếu bác đang ở tab Phiếu thì load lại danh sách luôn cho nóng
                    if(typeof loadDanhSachPhieu === "function") loadDanhSachPhieu('TatCa');
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Lỗi kết nối:', err);
                alert('Không gửi được phiếu, bạn kiểm tra lại mạng xem sao.');
            });
        }
        
        // === QUẢN LÝ PHIẾU YÊU CẦU CỦA GIÁO VIÊN ===
        let currentFilter = 'TatCa';
        let allPhieuData = [];

        function loadDanhSachPhieu(trangThai = 'TatCa') {
            currentFilter = trangThai;
            
            fetch('<?php echo BASE_URL; ?>/diemso/danhSachPhieuCuaToi?trang_thai=' + trangThai)
                .then(res => res.json())
                .then(result => {
                    if (!result.success) {
                        alert(result.message);
                        return;
                    }
                    
                    allPhieuData = result.data;
                    const tbody = document.getElementById('tablePhieuBody');
                    tbody.innerHTML = '';
                    
                    if (result.data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4"></i><br>
                            <span class="small">Không có phiếu nào</span>
                        </td></tr>`;
                        return;
                    }
                    
                    result.data.forEach(phieu => {
                        const badgeClass = phieu.trang_thai_phieu === 'ChoDuyet' ? 'warning' :
                                           phieu.trang_thai_phieu === 'DaDuyet' ? 'success' : 'danger';
                        const badgeText = phieu.trang_thai_phieu === 'ChoDuyet' ? 'Chờ duyệt' :
                                          phieu.trang_thai_phieu === 'DaDuyet' ? 'Đã duyệt' : 'Từ chối';
                        
                        const ngayGui = new Date(phieu.ngay_lap_phieu);
                        const ngayGuiStr = ngayGui.toLocaleDateString('vi-VN');
                        
                        tbody.innerHTML += `
                            <tr>
                                <td><span class="badge bg-secondary">#${phieu.ma_phieu}</span></td>
                                <td>${phieu.ten_hoc_sinh}</td>
                                <td>${phieu.ten_mon_hoc}</td>
                                <td><span class="badge bg-info">${phieu.ma_hoc_ky}</span></td>
                                <td><small>${ngayGuiStr}</small></td>
                                <td><span class="badge bg-${badgeClass}">${badgeText}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="xemChiTietPhieu(${phieu.ma_phieu})" title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                })
                .catch(err => {
                    console.error('Lỗi:', err);
                    alert('Không thể tải danh sách phiếu');
                });
        }

        function filterPhieu(trangThai) {
            // Highlight button active
            document.querySelectorAll('#tab-phieu .btn-group .btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            loadDanhSachPhieu(trangThai);
        }

        function xemChiTietPhieu(maPhieu) {
            const phieu = allPhieuData.find(p => p.ma_phieu == maPhieu);
            if (!phieu) {
                alert('Không tìm thấy thông tin phiếu');
                return;
            }
            
            // Điền thông tin vào modal
            document.getElementById('detailMaPhieu').textContent = '#' + phieu.ma_phieu;
            document.getElementById('detailHocSinh').textContent = phieu.ten_hoc_sinh;
            document.getElementById('detailMonHoc').textContent = phieu.ten_mon_hoc + ' - ' + phieu.ma_hoc_ky;
            document.getElementById('detailLyDo').textContent = phieu.ly_do_chinh_sua || '--';
            
            // Điểm cũ
            document.getElementById('detailDiemCu').innerHTML = `
                Miệng: <strong>${phieu.diem_mieng_cu ?? '--'}</strong> | 
                15p: <strong>${phieu.diem_15phut_cu ?? '--'}</strong> | 
                1T: <strong>${phieu.diem_1tiet_cu ?? '--'}</strong> | 
                GK: <strong>${phieu.diem_gua_ky_cu ?? '--'}</strong> | 
                CK: <strong>${phieu.diem_cuoi_ky_cu ?? '--'}</strong>
            `;
            
            // Điểm mới đề xuất
            document.getElementById('detailDiemMoi').innerHTML = `
                Miệng: <strong class="text-primary">${phieu.diem_mieng_moi ?? '--'}</strong> | 
                15p: <strong class="text-primary">${phieu.diem_15phut_moi ?? '--'}</strong> | 
                1T: <strong class="text-primary">${phieu.diem_1tiet_moi ?? '--'}</strong> | 
                GK: <strong class="text-primary">${phieu.diem_gua_ky_moi ?? '--'}</strong> | 
                CK: <strong class="text-primary">${phieu.diem_cuoi_ky_moi ?? '--'}</strong>
            `;
            
            const statusClass = phieu.trang_thai_phieu === 'ChoDuyet' ? 'warning' :
                               phieu.trang_thai_phieu === 'DaDuyet' ? 'success' : 'danger';
            const statusText = phieu.trang_thai_phieu === 'ChoDuyet' ? 'Chờ duyệt' :
                              phieu.trang_thai_phieu === 'DaDuyet' ? 'Đã duyệt' : 'Từ chối';
            document.getElementById('detailTrangThai').innerHTML = `<span class="badge bg-${statusClass}">${statusText}</span>`;
            
            // Thông tin duyệt
            if (phieu.trang_thai_phieu === 'DaDuyet') {
                const ngayDuyet = new Date(phieu.ngay_duyet);
                document.getElementById('detailNguoiDuyet').innerHTML = `
                    <strong class="text-success">${phieu.ten_nguoi_duyet || 'Ban Giám Hiệu'}</strong><br>
                    <small class="text-muted">Duyệt lúc: ${ngayDuyet.toLocaleString('vi-VN')}</small>
                `;
            } else if (phieu.trang_thai_phieu === 'TuChoi') {
                const ngayDuyet = new Date(phieu.ngay_duyet);
                document.getElementById('detailNguoiDuyet').innerHTML = `
                    <strong class="text-danger">${phieu.ten_nguoi_duyet || 'Ban Giám Hiệu'}</strong><br>
                    <small class="text-muted">Từ chối lúc: ${ngayDuyet.toLocaleString('vi-VN')}</small><br>
                    <div class="alert alert-danger mt-2 mb-0 small">
                        <i class="bi bi-x-circle"></i> <strong>Lý do:</strong> ${phieu.ly_do_tu_choi || 'Không rõ'}
                    </div>
                `;
            } else {
                document.getElementById('detailNguoiDuyet').innerHTML = '<span class="text-muted fst-italic">Chưa xử lý</span>';
            }
            
            const modal = new bootstrap.Modal(document.getElementById('modalChiTietPhieu'));
            modal.show();
        }

        // Load khi tab được click
        document.querySelector('[data-bs-target="#tab-phieu"]').addEventListener('shown.bs.tab', function() {
            loadDanhSachPhieu('TatCa');
        });


        function openModalChinhSuaCN(ma_hs, ho_ten, so_vang, hanh_kiem, nhan_xet) {
            document.getElementById('cn_ma_hoc_sinh').value = ma_hs;
            document.getElementById('cn_ho_ten').textContent = ho_ten;
            document.getElementById('cn_so_buoi_vang').value = so_vang;
            document.getElementById('cn_hanh_kiem').value = hanh_kiem;
            document.getElementById('cn_nhan_xet').value = nhan_xet || '';
            new bootstrap.Modal(document.getElementById('modalChinhSuaCN')).show();
        }

        // function submitChinhSuaCN() {
        //     const ma_hs = document.getElementById('cn_ma_hoc_sinh').value;
        //     const so_vang = document.getElementById('cn_so_buoi_vang').value;
        //     const hanh_kiem = document.getElementById('cn_hanh_kiem').value;
        //     const nhan_xet = document.getElementById('cn_nhan_xet').value;
        //     if (!ma_hs || !hanh_kiem) { alert('Vui lòng nhập đủ thông tin!'); return; }

        //     // Gửi cập nhật số buổi vắng
        //     fetch('<?php echo BASE_URL; ?>/giaovienchunhiem/capnhatvang', {
        //         method: 'POST',
        //         headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        //         body: `ma_hs=${ma_hs}&so_buoi_vang=${so_vang}&hoc_ky=HK1`
        //     })
        //     .then(res => res.json())
        //     .then(data => {
        //         // Gửi tiếp cập nhật hạnh kiểm
        //         return fetch('<?php echo BASE_URL; ?>/giaovienchunhiem/capnhathanhkiem', {
        //             method: 'POST',
        //             headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        //             body: `ma_hs=${ma_hs}&hanh_kiem=${hanh_kiem}&nhan_xet=${encodeURIComponent(nhan_xet)}&hoc_ky=HK1`
        //         });
        //     })
        //     .then(res => res.json())
        //     .then(data => {
        //         alert('Đã lưu thay đổi!');
        //         location.reload();
        //     })
        //     .catch(err => alert('Lỗi: ' + err.message));
        // }
        function submitChinhSuaCN() {
            // 1. Lấy dữ liệu
            const ma_hs = document.getElementById('cn_ma_hoc_sinh').value;
            const so_vang = document.getElementById('cn_so_buoi_vang').value.trim();
            const hanh_kiem = document.getElementById('cn_hanh_kiem').value;
            const nhan_xet = document.getElementById('cn_nhan_xet').value.trim();

            // 2. Kiểm tra dữ liệu (Validation)
            if (!ma_hs) { alert('Lỗi: Không tìm thấy mã học sinh!'); return; }
            
            if (!hanh_kiem) { 
                alert('Bạn vui lòng chọn Hạnh kiểm cho học sinh nhé!'); 
                return; 
            }

            if (so_vang === "" || isNaN(so_vang) || parseInt(so_vang) < 0) {
                alert('Số buổi vắng phải là số nguyên và không được nhỏ hơn 0!');
                document.getElementById('cn_so_buoi_vang').focus();
                return;
            }

            // Kiểm tra xem có phải số nguyên không (vắng 1.5 buổi là không ổn)
            if (!Number.isInteger(Number(so_vang))) {
                alert('Số buổi vắng phải là số nguyên, bạn đừng nhập số lẻ nhé!');
                document.getElementById('cn_so_buoi_vang').focus();
                return;
            }

            // 3. Thực hiện gửi dữ liệu (Xử lý tuần tự)
            // Bước A: Cập nhật số buổi vắng
            fetch('<?php echo BASE_URL; ?>/giaovienchunhiem/capnhatvang', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `ma_hs=${ma_hs}&so_buoi_vang=${so_vang}&hoc_ky=HK1`
            })
            .then(res => res.json())
            .then(dataVang => {
                if (!dataVang.success) throw new Error("Lỗi cập nhật vắng: " + dataVang.message);

                // Bước B: Nếu vắng xong xuôi thì cập nhật tiếp Hạnh kiểm & Nhận xét
                return fetch('<?php echo BASE_URL; ?>/giaovienchunhiem/capnhathanhkiem', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `ma_hs=${ma_hs}&hanh_kiem=${hanh_kiem}&nhan_xet=${encodeURIComponent(nhan_xet)}&hoc_ky=HK1`
                });
            })
            .then(res => res.json())
            .then(dataHk => {
                if (dataHk.success) {
                    alert('Tuyệt vời! Đã lưu mọi thay đổi thành công.');
                    location.reload();
                } else {
                    alert('Lỗi cập nhật hạnh kiểm: ' + dataHk.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('bạn ơi có lỗi rồi: ' + err.message);
            });
        }


    </script>

    <!-- Modal Nhập Điểm -->
    <div class="modal fade" id="modalNhapDiem" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-clipboard-data"></i> Nhập Điểm</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Học sinh:</strong> <span id="modal_hs_name"></span></p>
                    <p><strong>Môn học:</strong> <span id="modal_mon_name"></span></p>
                    <form id="formNhapDiem">
                        <input type="hidden" id="modal_ma_hs">
                        <input type="hidden" id="modal_ma_mon">
                        <input type="hidden" id="modal_hoc_ky" value="<?php echo $data['gv_hoc_ky'] ?? 'HK1'; ?>">
                        
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle"></i> <strong>Công thức tính:</strong><br>
                            Điểm TX = (Miệng + 15 Phút + 1 Tiết) / 3<br>
                            ĐTB Môn = (TX×1 + GK×2 + CK×3) / 6
                        </div>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="form-label small fw-bold">Miệng <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" max="10" class="form-control form-control-sm" id="inp_mieng" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-bold">15 Phút <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" max="10" class="form-control form-control-sm" id="inp_15phut" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-bold">1 Tiết <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" max="10" class="form-control form-control-sm" id="inp_1tiet" required>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Giữa kỳ <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" max="10" class="form-control form-control-sm" id="inp_gk" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Cuối kỳ <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" max="10" class="form-control form-control-sm" id="inp_ck" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="submitNhapDiem()">
                        <i class="bi bi-save"></i> Lưu Điểm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Chỉnh Sửa Điểm (Gửi Phiếu Yêu Cầu) -->
    <div class="modal fade" id="modalChinhSuaDiem" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-fill"></i> Gửi Phiếu Yêu Cầu Chỉnh Sửa Điểm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Lưu ý:</strong> 
                        Điểm đã nhập không thể sửa trực tiếp. Vui lòng gửi phiếu yêu cầu chỉnh sửa đến Ban Giám Hiệu để duyệt.
                    </div>
                    
                    <p><strong>Học sinh:</strong> <span id="modal_sua_hs_name" class="text-primary"></span></p>
                    <p><strong>Môn học:</strong> <span id="modal_sua_mon_name" class="text-primary"></span></p>
                    
                    <form id="formChinhSuaDiem">
                        <input type="hidden" id="modal_sua_ma_hs">
                        <input type="hidden" id="modal_sua_ma_mon">
                        <input type="hidden" id="modal_sua_hoc_ky" value="<?php echo $data['gv_hoc_ky'] ?? 'HK1'; ?>">
                        
                        <h6 class="fw-bold text-dark mt-3 mb-2">Điểm Mới (Cần Chỉnh Sửa)</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Miệng <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" max="10" class="form-control form-control-sm" id="inp_sua_mieng" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">15 Phút <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" max="10" class="form-control form-control-sm" id="inp_sua_15phut" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">1 Tiết <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" max="10" class="form-control form-control-sm" id="inp_sua_1tiet" required>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Giữa kỳ <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" max="10" class="form-control form-control-sm" id="inp_sua_gk" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Cuối kỳ <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" max="10" class="form-control form-control-sm" id="inp_sua_ck" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Lý do chỉnh sửa <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="inp_ly_do" rows="3" placeholder="VD: Nhập sai kết quả bài kiểm tra, cần cập nhật theo phúc khảo..." required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-warning btn-sm text-dark fw-bold" onclick="submitChinhSuaDiem()">
                        <i class="bi bi-send-fill"></i> Gửi Phiếu Yêu Cầu
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Chi Tiết Phiếu -->
    <div class="modal fade" id="modalChiTietPhieu" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">
                        <i class="bi bi-file-earmark-text text-warning"></i> Chi Tiết Phiếu <span id="detailMaPhieu" class="text-primary"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Học sinh</label>
                            <p class="fw-bold mb-0" id="detailHocSinh"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Môn học - Học kỳ</label>
                            <p class="fw-bold mb-0" id="detailMonHoc"></p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Lý do yêu cầu chỉnh sửa</label>
                            <p id="detailLyDo" class="fst-italic bg-light p-2 rounded"></p>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-secondary mb-0">
                                <label class="text-muted small fw-bold d-block mb-1">Điểm cũ (Hiện tại)</label>
                                <p id="detailDiemCu" class="mb-0"></p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-primary mb-0">
                                <label class="text-muted small fw-bold d-block mb-1">Điểm mới (Đề xuất)</label>
                                <p id="detailDiemMoi" class="mb-0"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Trạng thái</label>
                            <p id="detailTrangThai"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Người xử lý</label>
                            <p id="detailNguoiDuyet" class="mb-0"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Chỉnh Sửa Chủ Nhiệm -->
    <div class="modal fade" id="modalChinhSuaCN" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header bg-success text-white">
            <h5 class="modal-title"><i class="bi bi-pencil"></i> Cập Nhật Học Sinh</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <form id="formChinhSuaCN">
            <input type="hidden" id="cn_ma_hoc_sinh">
            <div class="mb-2">
                <label class="form-label fw-bold">Họ tên học sinh:</label>
                <div id="cn_ho_ten" class="fw-bold text-primary"></div>
            </div>
            <div class="mb-2">
                <label class="form-label">Số buổi vắng:</label>
                <input type="number" min="0" class="form-control" id="cn_so_buoi_vang" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Hạnh kiểm:</label>
                <select class="form-select" id="cn_hanh_kiem" required>
                <option value="">-- Chọn --</option>
                <option value="Tot">Tốt</option>
                <option value="Kha">Khá</option>
                <option value="Dat">Đạt</option>
                <option value="ChuaDat">Chưa đạt</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label">Nhận xét GVCN:</label>
                <textarea class="form-control" id="cn_nhan_xet" rows="2"></textarea>
            </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-success btn-sm" onclick="submitChinhSuaCN()">
            <i class="bi bi-save"></i> Lưu
            </button>
        </div>
        </div>
    </div>
    </div>

</body>
</html>