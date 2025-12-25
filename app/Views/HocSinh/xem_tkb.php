<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Thời Khóa Biểu - Lớp <?= htmlspecialchars($ten_lop ?? '') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root { 
            --primary: #0d6efd; 
            --primary-dark: #0b5ed7; 
            --bg: #f5f7fb; 
            --sidebar-width: 280px; 
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; 
            background-color: var(--bg); 
            color: #212529;
        }
        
        /* Sidebar Styles - Đậm và hiện đại hơn */
        .sidebar { 
            width: var(--sidebar-width); 
            position: fixed; 
            height: 100vh; 
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%); 
            box-shadow: var(--shadow); 
            z-index: 1000; 
            transition: 0.3s; 
            display: flex; 
            flex-direction: column; 
            border-right: 1px solid #e9ecef;
        }
        .nav-link { 
            color: #495057; 
            padding: 14px 20px; 
            font-weight: 600; 
            margin: 6px 12px; 
            border-radius: 10px; 
            transition: all 0.3s; 
            font-size: 0.95rem;
        }
        .nav-link:hover, .nav-link.active { 
            background-color: #e0ecff; 
            color: var(--primary-dark); 
            transform: translateX(6px);
        }
        .nav-link.active { 
            background-color: #cfe2ff; 
            box-shadow: 0 2px 8px rgba(13,110,253,0.15);
        }
        .main-content { 
            margin-left: var(--sidebar-width); 
            padding: 30px; 
            transition: 0.3s; 
            min-height: 100vh;
        }
        @media (max-width: 992px) { 
            .sidebar { transform: translateX(-100%); } 
            .main-content { margin-left: 0; } 
            .sidebar.show { transform: translateX(0); } 
        }

        /* TKB Grid - Nâng cấp giao diện */
        .tkb-grid { 
            display: grid; 
            grid-template-columns: 60px repeat(7, 1fr); 
            gap: 2px; 
            background-color: #ced4da; 
            border-radius: 12px; 
            overflow: hidden; 
            box-shadow: var(--shadow); 
        }
        .tkb-cell { 
            background-color: white; 
            padding: 10px; 
            min-height: 110px; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            font-size: 0.95rem; 
            transition: all 0.2s;
        }
        .tkb-cell:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 4px 12px rgba(0,0,0,0.08); 
            z-index: 2; 
            position: relative;
        }
        .tkb-header { 
            background: linear-gradient(135deg, #0d6efd, #0b5ed7); 
            font-weight: 700; 
            text-align: center; 
            padding: 14px; 
            color: white; 
            font-size: 1rem;
        }
        .tkb-header small { 
            font-weight: 400; 
            opacity: 0.9; 
            font-size: 0.85rem; 
        }
        .tkb-tiet-col { 
            background-color: #e9ecef; 
            font-weight: 700; 
            text-align: center; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            color: #495057; 
        }
        .tkb-tiet-col span.fs-6 { 
            font-size: 1.1rem; 
            color: var(--primary-dark); 
        }
        .session-sep { 
            grid-column: 1 / -1; 
            background: linear-gradient(90deg, #fff3cd, #ffeaa7); 
            color: #856404; 
            font-weight: 700; 
            text-align: center; 
            padding: 10px; 
            font-size: 0.95rem; 
            letter-spacing: 1.5px; 
            text-transform: uppercase; 
            box-shadow: inset 0 -2px 6px rgba(0,0,0,0.05);
        }
        .session-sep.bg-info { 
            background: linear-gradient(90deg, #cfe2ff, #a5d8ff); 
            color: var(--primary-dark); 
        }

        /* Card Môn Học - Màu đậm hơn, nổi bật hơn */
        .mon-card { 
            border-left: 6px solid var(--primary-dark); 
            padding: 12px 14px; 
            height: 100%; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            background: #e6f0ff; 
            border-radius: 8px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.06); 
            transition: all 0.3s; 
        }
        .mon-card:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 6px 16px rgba(13,110,253,0.2); 
        }
        .mon-card.fixed-session { 
            border-left-color: #dc3545; 
            background: #fce8e8; 
        }
        .mon-card.hoc { 
            border-left-color: #198754; 
            background: #d1f0e0; 
        }
        .mon-card.thi { 
            border-left-color: #fd7e14; 
            background: #ffedd5; 
        }
        .mon-card.day_bu { 
            border-left-color: #0dcaf0; 
            background: #d0f2ff; 
        }
        .mon-card.tam_nghi { 
            border-left-color: #e74c3c; 
            background: #ffd6d6; 
        }

        .mon-card .ten-mon { 
            font-weight: 800; 
            color: #1e293b; 
            font-size: 1rem; 
            margin-bottom: 6px; 
            letter-spacing: 0.3px;
        }
        .mon-card.fixed-session .ten-mon, 
        .mon-card.tam_nghi .ten-mon { 
            color: #dc3545; 
        }
        .mon-card.thi .ten-mon { 
            color: #c2410c; 
        }
        .mon-card.hoc .ten-mon { 
            color: #166534; 
        }

        .mon-card .info { 
            font-size: 0.82rem; 
            color: #495057; 
            line-height: 1.4; 
            font-weight: 500;
        }
        .mon-card .note { 
            font-size: 0.8rem; 
            color: #6c757d; 
            margin-top: 6px; 
            font-style: italic; 
            opacity: 0.9;
        }

        /* Tiết thay đổi - Nổi bật hơn */
        .mon-card.changed { 
            border: 3px dashed #f59e0b !important; 
            animation: pulse 2s infinite; 
        }
        @keyframes pulse { 
            0% { box-shadow: 0 0 0 0 rgba(245,158,11,0.4); } 
            70% { box-shadow: 0 0 0 10px rgba(245,158,11,0); } 
            100% { box-shadow: 0 0 0 0 rgba(245,158,11,0); } 
        }
        .icon-warn { 
            color: #f59e0b; 
            margin-left: 6px; 
            font-size: 1rem; 
        }

        /* Mobile View - Cũng nâng cấp */
        @media (max-width: 768px) {
            .tkb-grid { display: none; } 
            .tkb-mobile-list { display: block; }
            .mobile-day-card { 
                border: none; 
                border-radius: 14px; 
                margin-bottom: 20px; 
                overflow: hidden; 
                background: white; 
                box-shadow: var(--shadow); 
            }
            .mobile-day-header { 
                background: linear-gradient(135deg, #0d6efd, #0b5ed7); 
                color: white; 
                padding: 14px 18px; 
                font-weight: 700; 
                font-size: 1.1rem;
            }
            .mobile-slot { 
                padding: 14px 18px; 
                border-bottom: 1px solid #eef2f6; 
            }
            .slot-time { 
                width: 80px; 
                font-weight: 700; 
                color: #495057; 
                font-size: 1rem; 
            }
        }
        @media (min-width: 769px) { .tkb-mobile-list { display: none; } }

        /* Card header lớp học */
        .card.border-0 { 
            border-radius: 16px !important; 
            box-shadow: var(--shadow); 
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="p-4 text-center border-bottom bg-white">
        <h5 class="fw-bold text-primary mb-1">HỌC SINH</h5>
        <small class="text-muted fw-medium">Cổng thông tin học tập</small>
    </div>
    <ul class="nav flex-column mt-4">
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/dashboard"><i class="bi bi-grid-fill me-3"></i> Tổng Quan</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/baitap/index"><i class="bi bi-journal-text me-3"></i> Bài Tập</a></li>
        <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-calendar-week me-3"></i> Thời Khóa Biểu</a></li>
        <li class="nav-item mt-auto p-4">
            <a class="nav-link text-danger bg-danger bg-opacity-10 rounded-3 fw-medium" href="<?= BASE_URL ?>/auth/logout"><i class="bi bi-box-arrow-right me-3"></i> Đăng Xuất</a>
        </li>
    </ul>
</div>

<div class="main-content">
    
    <div class="d-lg-none d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-3 shadow-sm">
        <button class="btn btn-light border" id="toggleSidebar"><i class="bi bi-list fs-3"></i></button>
        <span class="fw-bold fs-5">Thời Khóa Biểu</span>
        <div style="width:40px;"></div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-4 p-4">
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
                <p class="text-muted mb-0">
                    <i class="bi bi-clock-history me-1"></i> <?= htmlspecialchars($ten_hoc_ky ?? '') ?>
                </p>
            </div>

            <div class="d-flex align-items-center gap-2 bg-light p-2 rounded-pill shadow-sm">
                <a href="<?= $prev_link ?? '#' ?>" class="btn btn-white rounded-circle shadow-sm" style="width:40px;height:40px;"><i class="bi bi-chevron-left"></i></a>
                <input type="date" class="form-control border-0 bg-transparent fw-bold text-center" 
                       style="width: 140px;" 
                       value="<?= $selected_date ?? '' ?>" 
                       onchange="window.location.href='<?= BASE_URL ?>/hocsinhTkb/index?date='+this.value">
                <a href="<?= $next_link ?? '#' ?>" class="btn btn-white rounded-circle shadow-sm" style="width:40px;height:40px;"><i class="bi bi-chevron-right"></i></a>
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

        <div class="tkb-grid">
            <div class="tkb-header bg-light text-muted fw-bold">Tiết</div>
            <?php foreach ($week_dates as $index => $date): ?>
                <div class="tkb-header">
                    Thứ <?= $index + 2 ?><br>
                    <small><?= $date ?></small>
                </div>
            <?php endforeach; ?>

            <div class="session-sep">Buổi Sáng</div>
            
            <?php 
            function renderSlot($tkb_data, $thu, $tiet) {
                $s = isset($tkb_data[$thu][$tiet]) ? $tkb_data[$thu][$tiet] : null;
                
                $is_fixed_sys = false;
                if ($thu == 2 && $tiet == 1) {
                    $s = ['mon' => 'Chào cờ', 'gv' => 'Toàn trường', 'phong' => 'Sân trường', 'loai_tiet' => 'hoc'];
                    $is_fixed_sys = true;
                } elseif ($thu == 2 && $tiet == 2) {
                    $s = ['mon' => 'Sinh hoạt', 'gv' => 'GVCN', 'phong' => 'Tại lớp', 'loai_tiet' => 'hoc'];
                    $is_fixed_sys = true;
                }

                if ($s) {
                    $loai = $s['loai_tiet'] ?? 'hoc';
                    $cls_sys = $is_fixed_sys ? 'fixed-session' : $loai;
                    $changed_class = (!empty($s['is_changed'])) ? 'changed' : '';
                    
                    echo "<div class='mon-card {$cls_sys} {$changed_class}'>";
                    echo "<div class='ten-mon'>" . htmlspecialchars($s['mon']);
                    if (!empty($s['is_changed'])) {
                        echo "<i class='bi bi-exclamation-circle-fill icon-warn' title='Lịch thay đổi'></i>";
                    }
                    echo "</div>";

                    if (!$is_fixed_sys) {
                        echo "<span class='info'><i class='bi bi-person me-1'></i>" . htmlspecialchars($s['gv']) . "</span>";
                    } else {
                        echo "<span class='info'><i class='bi bi-people me-1'></i>" . htmlspecialchars($s['gv']) . "</span>";
                    }
                    
                    echo "<span class='info'><i class='bi bi-geo-alt me-1'></i>" . htmlspecialchars($s['phong']) . "</span>";
                    
                    if (!empty($s['ghi_chu'])) {
                        echo "<div class='note'>" . htmlspecialchars($s['ghi_chu']) . "</div>";
                    }
                    echo "</div>";
                }
            }
            ?>

            <?php for ($tiet = 1; $tiet <= 4; $tiet++): ?>
                <div class="tkb-tiet-col">
                    <span class="fs-6"><?= $tiet ?></span>
                    <small class="text-muted fw-normal">
                        <?= $gio_hoc[$tiet] ?? '' ?>
                    </small>
                </div>
                <?php for ($thu = 2; $thu <= 8; $thu++): ?>
                    <div class="tkb-cell">
                        <?php renderSlot($tkb_data, $thu, $tiet); ?>
                    </div>
                <?php endfor; ?>
            <?php endfor; ?>

            <div class="session-sep bg-info bg-opacity-10 text-primary">Buổi Chiều</div>
            
            <?php for ($tiet = 5; $tiet <= 7; $tiet++): ?>
                <div class="tkb-tiet-col">
                    <span class="fs-6"><?= $tiet ?></span>
                    <small class="text-muted fw-normal">
                        <?= $gio_hoc[$tiet] ?? '' ?>
                    </small>
                </div>
                <?php for ($thu = 2; $thu <= 8; $thu++): ?>
                    <div class="tkb-cell">
                        <?php renderSlot($tkb_data, $thu, $tiet); ?>
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
                <div class="mobile-day-card">
                    <div class="mobile-day-header">
                        <span>Thứ <?= $thu ?></span>
                        <span class="opacity-75 fw-normal small"><?= $week_dates[$thu-2] ?></span>
                    </div>
                    <?php for ($tiet = 1; $tiet <= 7; $tiet++): 
                        $s = isset($tkb_data[$thu][$tiet]) ? $tkb_data[$thu][$tiet] : null;
                        
                        if ($thu == 2 && $tiet == 1) {
                            $s = ['mon' => 'Chào cờ', 'gv' => 'Toàn trường', 'phong' => 'Sân trường', 'loai_tiet' => 'hoc'];
                        } elseif ($thu == 2 && $tiet == 2) {
                            $s = ['mon' => 'Sinh hoạt', 'gv' => 'GVCN', 'phong' => 'Tại lớp', 'loai_tiet' => 'hoc'];
                        }

                        if ($s): 
                    ?>
                        <div class="mobile-slot">
                            <div class="slot-time">
                                <div>Tiết <?= $tiet ?></div>
                                <small class="text-muted d-block fw-normal">
                                    <?= $gio_hoc[$tiet] ?? '' ?>
                                </small>
                            </div>
                            <div class="flex-grow-1">
                                <?php 
                                    $loai = $s['loai_tiet'] ?? 'hoc'; 
                                    $is_changed = !empty($s['is_changed']);
                                    $text_color = ($loai==='tam_nghi') ? 'text-danger' : (($loai==='thi') ? 'text-orange' : 'text-primary');
                                    if ($thu==2 && $tiet<=2) $text_color = 'text-danger';
                                ?>
                                <h6 class="fw-bold mb-2 <?= $text_color ?>">
                                    <?= htmlspecialchars($s['mon']) ?>
                                    <?php if($is_changed): ?>
                                        <i class="bi bi-exclamation-circle-fill text-warning ms-1"></i>
                                    <?php endif; ?>
                                </h6>
                                <div class="small text-muted mb-1"><i class="bi bi-person-fill me-1"></i> <?= htmlspecialchars($s['gv']) ?></div>
                                <div class="small text-muted mb-1"><i class="bi bi-geo-alt-fill me-1"></i> Phòng: <?= htmlspecialchars($s['phong']) ?></div>
                                <?php if (!empty($s['ghi_chu'])): ?>
                                    <div class="small text-muted fst-italic"><i class="bi bi-info-circle me-1"></i><?= htmlspecialchars($s['ghi_chu']) ?></div>
                                <?php endif; ?>
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