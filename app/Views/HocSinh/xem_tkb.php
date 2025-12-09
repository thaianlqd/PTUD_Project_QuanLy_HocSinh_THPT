<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Thời Khóa Biểu - Lớp <?= htmlspecialchars($ten_lop ?? '') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
                .recess-row {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 0;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        :root { --primary: #0d6efd; --bg: #f8f9fa; --sidebar-width: 280px; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: var(--bg); }
        
        /* Sidebar Styles */
        .sidebar { width: var(--sidebar-width); position: fixed; height: 100vh; background: #fff; box-shadow: 2px 0 10px rgba(0,0,0,0.05); z-index: 1000; transition: 0.3s; display: flex; flex-direction: column; }
        .nav-link { color: #555; padding: 12px 20px; font-weight: 500; margin: 4px 10px; border-radius: 8px; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { background-color: #e3f2fd; color: var(--primary); }
        .main-content { margin-left: var(--sidebar-width); padding: 25px; transition: 0.3s; }
        @media (max-width: 992px) { .sidebar { transform: translateX(-100%); } .main-content { margin-left: 0; } .sidebar.show { transform: translateX(0); } }

        /* TKB Custom Styles */
        .tkb-grid { display: grid; grid-template-columns: 50px repeat(7, 1fr); gap: 1px; background-color: #dee2e6; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden; }
        .tkb-cell { background-color: white; padding: 5px; min-height: 90px; display: flex; flex-direction: column; justify-content: center; font-size: 0.9rem; border-bottom: 1px solid #dee2e6; }
        .tkb-header { background-color: #e3f2fd; font-weight: bold; text-align: center; padding: 10px; color: #0d6efd; border-bottom: 1px solid #dee2e6; }
        .tkb-tiet-col { background-color: #f8f9fa; font-weight: bold; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #6c757d; border-bottom: 1px solid #dee2e6; }
        .session-sep { grid-column: 1 / -1; background-color: #fff3cd; color: #856404; font-weight: bold; text-align: center; padding: 5px; font-size: 0.85rem; letter-spacing: 1px; text-transform: uppercase; }
        
        /* Card Môn Học */
        .mon-card { border-left: 3px solid var(--primary); padding-left: 8px; height: 100%; display: flex; flex-direction: column; justify-content: center; }
        .mon-card.fixed-session { border-left-color: #dc3545; }
        .mon-card .ten-mon { font-weight: 700; color: #2c3e50; font-size: 0.9rem; margin-bottom: 2px; }
        .mon-card.fixed-session .ten-mon { color: #dc3545; }
        .mon-card .info { font-size: 0.75rem; color: #6c757d; display: block; line-height: 1.3; }
        
        /* Mobile List View */
        @media (max-width: 768px) {
            .tkb-grid { display: none; } 
            .tkb-mobile-list { display: block; }
            .mobile-day-card { border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 15px; overflow: hidden; background: white; }
            .mobile-day-header { background: #0d6efd; color: white; padding: 10px 15px; font-weight: bold; display: flex; justify-content: space-between; }
            .mobile-slot { padding: 10px 15px; border-bottom: 1px solid #f1f1f1; display: flex; align-items: flex-start; }
            .mobile-slot:last-child { border-bottom: none; }
            .slot-time { width: 70px; font-weight: bold; color: #888; font-size: 0.9rem; margin-right: 10px; text-align: center; }
        }
        @media (min-width: 769px) { .tkb-mobile-list { display: none; } }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="p-4 text-center border-bottom bg-light">
        <h5 class="fw-bold text-primary mb-0">HỌC SINH</h5>
        <small class="text-muted">Cổng thông tin</small>
    </div>
    <ul class="nav flex-column mt-3">
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/dashboard"><i class="bi bi-grid-fill me-2"></i> Tổng Quan</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/baitap/index"><i class="bi bi-journal-text me-2"></i> Bài Tập</a></li>
        <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-calendar-week me-2"></i> Thời Khóa Biểu</a></li>
        <li class="nav-item mt-auto p-3">
            <a class="nav-link text-danger bg-danger bg-opacity-10 rounded-3" href="<?= BASE_URL ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i> Đăng Xuất</a>
        </li>
    </ul>
</div>

<div class="main-content">
    
    <div class="d-lg-none d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-3 shadow-sm">
        <button class="btn btn-light" id="toggleSidebar"><i class="bi bi-list fs-4"></i></button>
        <span class="fw-bold">Thời Khóa Biểu</span>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h4 class="fw-bold mb-1 text-primary">
                    Lớp: <?= htmlspecialchars($ten_lop ?? 'N/A') ?>
                    <span class="text-dark fs-6 ms-2 fw-normal d-none d-sm-inline">
                        | <i class="bi bi-person-badge me-1"></i>GVCN: <?= htmlspecialchars($ten_gvcn ?? '---') ?>
                    </span>
                </h4>
                <div class="d-sm-none text-muted small mb-1">
                    <i class="bi bi-person-badge me-1"></i>GVCN: <?= htmlspecialchars($ten_gvcn ?? '---') ?>
                </div>
                <p class="text-muted mb-0 small">
                    <i class="bi bi-clock-history me-1"></i> <?= htmlspecialchars($ten_hoc_ky ?? '') ?>
                </p>
            </div>

            <div class="d-flex align-items-center gap-2 bg-light p-1 rounded-3">
                <a href="<?= $prev_link ?? '#' ?>" class="btn btn-white shadow-sm border-0 btn-sm"><i class="bi bi-chevron-left"></i></a>
                <input type="date" class="form-control border-0 bg-transparent fw-bold text-center" 
                       style="width: 130px; font-size: 0.9rem;" 
                       value="<?= $selected_date ?? '' ?>" 
                       onchange="window.location.href='<?= BASE_URL ?>/hocsinhTkb/index?date='+this.value">
                <a href="<?= $next_link ?? '#' ?>" class="btn btn-white shadow-sm border-0 btn-sm"><i class="bi bi-chevron-right"></i></a>
            </div>
        </div>
    </div>

    <?php if (($ma_hoc_ky ?? null) === null): ?>
        <div class="alert alert-warning text-center p-5 rounded-4 shadow-sm">
            <i class="bi bi-calendar-x fs-1 d-block mb-3 opacity-50"></i>
            <h5 class="fw-bold">Chưa có lịch học</h5>
            <p class="mb-0">Ngày bạn chọn nằm ngoài thời gian học kỳ.</p>
        </div>
    <?php else: ?>

        <div class="tkb-grid shadow-sm">
            <div class="tkb-header bg-light text-muted">Tiết</div>
            <?php foreach ($week_dates as $index => $date): ?>
                <div class="tkb-header">
                    Thứ <?= $index + 2 ?><br>
                    <small class="fw-normal text-secondary" style="font-size: 0.8rem"><?= $date ?></small>
                </div>
            <?php endforeach; ?>

            <div class="session-sep">Buổi Sáng</div>
            <?php for ($tiet = 1; $tiet <= 4; $tiet++): ?>
                <div class="tkb-tiet-col">
                    <span class="fs-6"><?= $tiet ?></span>
                    <small class="text-muted fw-normal" style="font-size: 0.7rem;">
                        <?= $gio_hoc[$tiet] ?? '' ?>
                    </small>
                </div>
                <?php for ($thu = 2; $thu <= 8; $thu++): ?>
                    <div class="tkb-cell">
                        <?php 
                            $s = isset($tkb_data[$thu][$tiet]) ? $tkb_data[$thu][$tiet] : null;
                            $is_fixed = false;

                            if ($thu == 2 && $tiet == 1) {
                                $s = ['mon' => 'Chào cờ', 'gv' => 'Toàn trường', 'phong' => 'Sân trường'];
                                $is_fixed = true;
                            } elseif ($thu == 2 && $tiet == 2) {
                                $s = ['mon' => 'Sinh hoạt', 'gv' => 'GVCN', 'phong' => 'Tại lớp'];
                                $is_fixed = true;
                            }
                        ?>
                        <?php if ($s): ?>
                            <div class="mon-card <?= $is_fixed ? 'fixed-session' : '' ?>">
                                <div class="ten-mon"><?= htmlspecialchars($s['mon']) ?></div>
                                <span class="info"><i class="bi bi-person me-1"></i><?= htmlspecialchars($s['gv']) ?></span>
                                <span class="info"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($s['phong']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            <?php endfor; ?>

            <div class="session-sep bg-info bg-opacity-10 text-primary">Buổi Chiều</div>
            <?php for ($tiet = 5; $tiet <= 7; $tiet++): ?>
                <div class="tkb-tiet-col">
                    <span class="fs-6"><?= $tiet ?></span>
                    <small class="text-muted fw-normal" style="font-size: 0.7rem;">
                        <?= $gio_hoc[$tiet] ?? '' ?>
                    </small>
                </div>
                <?php for ($thu = 2; $thu <= 8; $thu++): ?>
                    <div class="tkb-cell">
                        <?php 
                            $s = isset($tkb_data[$thu][$tiet]) ? $tkb_data[$thu][$tiet] : null; 
                        ?>
                        <?php if ($s): ?>
                            <div class="mon-card" style="border-left-color: #ffc107;">
                                <div class="ten-mon text-dark"><?= htmlspecialchars($s['mon']) ?></div>
                                <span class="info"><i class="bi bi-person me-1"></i><?= htmlspecialchars($s['gv']) ?></span>
                                <span class="info"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($s['phong']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            <?php endfor; ?>
        </div>

        <div class="tkb-mobile-list">
            <?php for ($thu = 2; $thu <= 8; $thu++): 
                $has_mon = false;
                if ($thu == 2) { $has_mon = true; } else {
                    for ($t=1; $t<=7; $t++) if(isset($tkb_data[$thu][$t])) $has_mon = true;
                }
                if (!$has_mon) continue;
            ?>
                <div class="mobile-day-card shadow-sm">
                    <div class="mobile-day-header">
                        <span>Thứ <?= $thu ?></span>
                        <span class="opacity-75 fw-normal small"><?= $week_dates[$thu-2] ?></span>
                    </div>
                    <?php for ($tiet = 1; $tiet <= 7; $tiet++): 
                        $s = isset($tkb_data[$thu][$tiet]) ? $tkb_data[$thu][$tiet] : null;
                        
                        if ($thu == 2 && $tiet == 1) {
                            $s = ['mon' => 'Chào cờ', 'gv' => 'Toàn trường', 'phong' => 'Sân trường'];
                        } elseif ($thu == 2 && $tiet == 2) {
                            $s = ['mon' => 'Sinh hoạt', 'gv' => 'GVCN', 'phong' => 'Tại lớp'];
                        }

                        if ($s): 
                    ?>
                        <div class="mobile-slot">
                            <div class="slot-time">
                                <div>Tiết <?= $tiet ?></div>
                                <small class="text-muted d-block fw-normal" style="font-size: 0.7rem">
                                    <?= $gio_hoc[$tiet] ?? '' ?>
                                </small>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-primary mb-1 <?= ($thu==2 && $tiet<=2) ? 'text-danger' : '' ?>">
                                    <?= htmlspecialchars($s['mon']) ?>
                                </h6>
                                <div class="small text-muted"><i class="bi bi-person-fill me-1"></i> <?= htmlspecialchars($s['gv']) ?></div>
                                <div class="small text-muted"><i class="bi bi-geo-alt-fill me-1"></i> Phòng: <?= htmlspecialchars($s['phong']) ?></div>
                            </div>
                        </div>
                    <?php endif; endfor; ?>
                </div>
            <?php endfor; ?>
        </div>

    <?php endif; ?>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('toggleSidebar')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });
</script>
</body>
</html>