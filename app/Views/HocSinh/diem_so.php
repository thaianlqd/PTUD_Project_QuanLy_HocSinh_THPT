<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Bảng Điểm Chi Tiết | THPT Manager</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root {
            --primary: #0d6efd;
            --bg: #f8f9fa;
            --sidebar-width: 280px;
        }
        body { font-family: 'Roboto', sans-serif; background-color: var(--bg); color: #333; }
        
        /* Sidebar Styles (Giống Dashboard) */
        .sidebar { width: var(--sidebar-width); position: fixed; height: 100vh; background: #fff; box-shadow: 2px 0 10px rgba(0,0,0,0.05); z-index: 1000; transition: 0.3s; display: flex; flex-direction: column; }
        .profile-section { padding: 25px 15px; text-align: center; border-bottom: 1px dashed #eee; background: linear-gradient(180deg, #e3f2fd 0%, #fff 100%); }
        .nav-link { color: #555; padding: 12px 20px; font-weight: 500; margin: 4px 10px; border-radius: 8px; transition: 0.2s; }
        .nav-link:hover { background-color: #e3f2fd; color: var(--primary); }
        .nav-link.active { background-color: #e3f2fd; color: var(--primary); font-weight: bold; }
        
        /* Main Content */
        .main-content { margin-left: var(--sidebar-width); padding: 25px; transition: 0.3s; }
        .card-custom { border: none; border-radius: 15px; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        
        /* Table Styles */
        .table-custom th { background-color: #f8f9fa; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; color: #6c757d; }
        .fw-medium { font-weight: 500; }
        
        @media (max-width: 992px) { .sidebar { transform: translateX(-100%); } .main-content { margin-left: 0; } .sidebar.show { transform: translateX(0); } }
    </style>
</head>
<body>

<?php 
    $student = $data['student_info'] ?? [];
    $ho_ten  = $student['ho_ten'] ?? $_SESSION['user_name'];
    $ten_lop = $student['ten_lop'] ?? '---';
    $ma_hs   = $student['ma_hoc_sinh'] ?? '---';
    $avatar  = 'https://cdn-icons-png.flaticon.com/512/3135/3135823.png';
    $bang_diem = $data['bang_diem'] ?? [];
?>

<div class="sidebar" id="sidebar">
    <div class="profile-section">
        <img src="<?= htmlspecialchars($avatar) ?>" width="80" height="80" class="rounded-circle mb-3 border border-3 border-white shadow-sm">
        <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($ho_ten) ?></h6>
        <div class="badge bg-primary bg-opacity-10 text-primary mb-2">Học Sinh - Lớp <?= htmlspecialchars($ten_lop) ?></div>
        <div class="small text-muted">Mã HS: <?= htmlspecialchars($ma_hs) ?></div>
    </div>

    <ul class="nav flex-column mt-3">
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/hocsinh/index"><i class="bi bi-grid-fill me-2"></i> Tổng Quan</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/baitap/index"><i class="bi bi-journal-text me-2"></i> Bài Tập & Nộp Bài</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/hocsinh/diemdanh"><i class="bi bi-calendar-check me-2"></i> Điểm Danh</a></li>
        
        <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-bar-chart-line-fill me-2"></i> Bảng Điểm</a></li>
        
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/hocsinhTkb/index"><i class="bi bi-calendar-week me-2"></i> Thời Khóa Biểu</a></li>
        <li class="nav-item mt-auto p-3 pt-0">
            <a class="nav-link text-danger bg-danger bg-opacity-10" href="<?= BASE_URL ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i> Đăng Xuất</a>
        </li>
    </ul>
</div>

<div class="main-content">
    
    <div class="d-flex justify-content-between align-items-center mb-4 d-lg-none bg-white p-3 rounded-3 shadow-sm">
        <button class="btn btn-light" id="toggleSidebar"><i class="bi bi-list fs-4"></i></button>
        <span class="fw-bold text-primary">BẢNG ĐIỂM</span>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-primary">Bảng Điểm Chi Tiết</h4>
            <p class="text-muted mb-0">Theo dõi kết quả học tập của bạn theo từng học kỳ</p>
        </div>
        <button class="btn btn-outline-primary" onclick="window.print()"><i class="bi bi-printer me-2"></i>In Bảng Điểm</button>
    </div>

    <div class="card card-custom p-4">
        <?php if (empty($bang_diem)): ?>
            <div class="text-center py-5">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486754.png" width="80" class="opacity-50 mb-3" alt="Empty">
                <h5 class="text-muted">Chưa có dữ liệu điểm</h5>
                <p class="small text-secondary">Vui lòng quay lại sau khi giáo viên nhập điểm.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-custom align-middle">
                    <thead>
                        <tr>
                            <th style="min-width: 150px;">Môn Học</th>
                            <th>Học Kỳ</th>
                            <th class="text-center">Miệng</th>
                            <th class="text-center">15 Phút</th>
                            <th class="text-center">1 Tiết</th>
                            <th class="text-center">Giữa Kỳ</th>
                            <th class="text-center">Cuối Kỳ</th>
                            <th class="text-center table-primary">TB Môn</th>
                            <th class="text-center">Xếp Loại</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bang_diem as $mon => $hoc_ky_data): ?>
                            <?php foreach ($hoc_ky_data as $hk => $d): ?>
                                <tr>
                                    <td class="fw-bold text-primary">
                                        <i class="bi bi-book me-2 opacity-50"></i><?= htmlspecialchars($mon) ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($hk) ?></span></td>
                                    
                                    <td class="text-center"><?= $d['DiemMieng'] ?? '-' ?></td>
                                    <td class="text-center"><?= $d['Diem15Phut'] ?? '-' ?></td>
                                    <td class="text-center"><?= $d['Diem1Tiet'] ?? '-' ?></td>
                                    <td class="text-center fw-medium"><?= $d['DiemGiuaKy'] ?? '-' ?></td>
                                    <td class="text-center fw-bold text-dark"><?= $d['DiemCuoiKy'] ?? '-' ?></td>
                                    
                                    <td class="text-center table-primary fw-bold fs-6">
                                        <?php 
                                            // 1. Lấy giá trị từ DB
                                            $tb = $d['TB'] ?? 0;

                                            // 2. LOGIC FIX: Nếu DB chưa có điểm TB (hoặc = 0) thì tự tính lại từ điểm thành phần
                                            if (empty($tb) || $tb == 0) {
                                                // Lấy điểm thành phần (ép kiểu float để tính toán)
                                                $m   = isset($d['DiemMieng']) ? (float)$d['DiemMieng'] : 0;
                                                $p15 = isset($d['Diem15Phut']) ? (float)$d['Diem15Phut'] : 0;
                                                $p45 = isset($d['Diem1Tiet'])  ? (float)$d['Diem1Tiet']  : 0;
                                                $gk  = isset($d['DiemGiuaKy']) ? (float)$d['DiemGiuaKy'] : 0;
                                                $ck  = isset($d['DiemCuoiKy']) ? (float)$d['DiemCuoiKy'] : 0;

                                                // Kiểm tra xem đã có dữ liệu chưa (tránh tính toán khi chưa nhập điểm)
                                                // Chỉ tính khi các điểm quan trọng (GK, CK) đã có
                                                if ($gk > 0 && $ck > 0) {
                                                    // Công thức: TX = (M + 15p + 1T) / 3
                                                    $tx = ($m + $p15 + $p45) / 3;
                                                    
                                                    // Công thức TB = (TX + GK*2 + CK*3) / 6
                                                    $tb_tinh = ($tx + $gk * 2 + $ck * 3) / 6;
                                                    
                                                    $tb = round($tb_tinh, 2); // Làm tròn 2 số thập phân
                                                }
                                            }

                                            // 3. Hiển thị màu sắc
                                            $color = $tb >= 8 ? 'success' : ($tb >= 5 ? 'primary' : 'danger');
                                            
                                            // 4. In ra màn hình
                                            echo $tb > 0 ? "<span class='text-$color'>$tb</span>" : '-';
                                        ?>
                                    </td>

                                    <td class="text-center">
                                        <?php
                                            $xl = $d['XepLoai'] ?? '';
                                            $badges = [
                                                'Gioi' => ['bg-success', 'Giỏi'],
                                                'Kha' => ['bg-primary', 'Khá'],
                                                'Dat' => ['bg-warning text-dark', 'Đạt'],
                                                'ChuaDat' => ['bg-danger', 'Chưa Đạt']
                                            ];
                                            if (isset($badges[$xl])) {
                                                echo "<span class='badge {$badges[$xl][0]}'>{$badges[$xl][1]}</span>";
                                            } else {
                                                echo "<span class='text-muted small'>-</span>";
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 p-3 bg-light rounded border border-dashed">
                <small class="text-muted d-block fw-bold mb-2"><i class="bi bi-info-circle me-1"></i> Ghi chú:</small>
                <div class="row g-2 small text-secondary">
                    <div class="col-md-3">TB Môn = (TX + GK*2 + CK*3) / 6</div>
                    <div class="col-md-3"><span class="badge bg-success me-1">Giỏi</span> Điểm TB ≥ 8.0</div>
                    <div class="col-md-3"><span class="badge bg-primary me-1">Khá</span> 6.5 ≤ TB < 8.0</div>
                    <div class="col-md-3"><span class="badge bg-danger me-1">Chưa Đạt</span> TB < 5.0</div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer class="text-center text-muted small pb-4 mt-5">
        &copy; <?= date("Y") ?> <?= htmlspecialchars($data['school_name'] ?? 'THPT Manager') ?>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('toggleSidebar')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });
</script>
</body>
</html>