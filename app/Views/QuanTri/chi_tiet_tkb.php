<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xếp TKB - Lớp <?php echo htmlspecialchars($data['rang_buoc']['ten_lop'] ?? 'N/A'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root {
            --fc-border-color: #dee2e6;
            --slot-min-height: 120px;
            --header-sticky-top: 66px; /* Chiều cao navbar */
            --sidebar-width: 380px;
        }
        body { background-color: #f8f9fa; }
        .timetable-container { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
        @media (min-width: 1200px) { .timetable-container { grid-template-columns: 1fr var(--sidebar-width); } }
        .timetable-main { overflow-x: auto; background-color: #fff; border: 1px solid var(--fc-border-color); border-radius: 0.375rem; }
        .timetable-grid { display: grid; grid-template-columns: 80px repeat(7, minmax(120px, 1fr)); grid-template-rows: auto auto repeat(4, minmax(var(--slot-min-height), auto)) auto repeat(3, minmax(var(--slot-min-height), auto)); }

        .timetable-header, .timetable-slot, .timetable-period-label {
            padding: 0.5rem;
            text-align: center;
            border-bottom: 1px solid var(--fc-border-color);
            border-right: 1px solid var(--fc-border-color);
            font-size: 0.9rem;
        }
        .timetable-grid > div:nth-child(8n) { border-right: none; }
        .timetable-period-label, .timetable-grid > div:nth-child(8n+1) { border-left: 1px solid var(--fc-border-color); }
        .timetable-grid > div:nth-last-child(-n+8) { border-bottom: none; }

        .timetable-header { font-weight: 600; background-color: #f1f3f5; position: sticky; top: 0; z-index: 10; }
        .timetable-period-label { font-weight: bold; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; position: sticky; left: 0; z-index: 5; writing-mode: vertical-rl; transform: rotate(180deg); }
        .timetable-session-label { grid-column: 1 / -1; text-align: center; font-weight: bold; font-size: 1rem; padding: 0.5rem; border-bottom: 1px solid var(--fc-border-color); border-right: 1px solid var(--fc-border-color); border-left: 1px solid var(--fc-border-color); position: sticky; top: 58px; z-index: 9; } /* Cập nhật top */
        .session-morning { background-color: #fff3cd; color: #664d03; }
        .session-afternoon { background-color: #cfe2ff; color: #0a3675; }
        .timetable-slot { position: relative; padding: 5px; }
        .timetable-slot.slot-fixed { background-color: #e9ecef; cursor: not-allowed; }
        .add-event-btn { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 36px; height: 36px; border-radius: 50%; border: 2px dashed #adb5bd; display: flex; align-items: center; justify-content: center; cursor: pointer; opacity: 0; transition: all 0.2s; color: #495057; background-color: rgba(233, 236, 239, 0.5); }
        .timetable-slot:not(.slot-fixed):hover .add-event-btn { opacity: 1; transform: translate(-50%, -50%) scale(1.1); background-color: #e9ecef; }
        .event-card { border-radius: 6px; padding: 10px; color: #fff; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; font-size: 0.85rem; text-align: left; height: 100%; overflow: hidden; display: flex; flex-direction: column; justify-content: center; line-height: 1.3; }
        .event-card:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .event-card .event-title { font-weight: bold; margin-bottom: 3px; font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .event-card p { margin-bottom: 1px; font-size: 0.78rem; opacity: 0.9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .event-card i { font-size: 0.7rem; margin-right: 3px; }
        .info-sidebar .card { border: 1px solid var(--fc-border-color); }
        .info-sidebar .card-body ul li { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; }
        .info-sidebar .badge { font-size: 0.9em; padding: 0.4em 0.6em; }
        .info-sidebar .list-group-item { border-left: none; border-right: none; }
        .info-sidebar .list-group-item:first-child { border-top: none; }
        .info-sidebar .list-group-item:last-child { border-bottom: none; }
        .form-select option:disabled { background-color: #f8d7da !important; color: #842029 !important; font-style: italic; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        .navbar-brand i { color: #198754; }
        .badge.bg-danger i { margin-right: 3px; }
        .progress-bar.bg-danger { background-color: #dc3545 !important; }
        .progress-bar.bg-danger::after { content: " (Vượt quá!)"; font-weight: normal; font-size: 0.8em;}
        
        .slot-holiday {
            background-color: #f8f9fa;
            justify-content: center;
            align-items: center;
            display: flex;
            padding: 10px;
        }
        /* Màu phân loại tiết */
        .event-hoc { background-color: #198754; }
        .event-thi { background-color: #f59e0b; }
        .event-nghi { background-color: #dc3545; }
        .event-note { font-size: 0.75rem; opacity: 0.9; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?php echo BASE_URL ?? ''; ?>/dashboard">
                <i class="bi bi-calendar-check-fill"></i> Hệ Thống Quản Lý THPT
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL ?? ''; ?>/tkb/xeptkb">
                            <i class="bi bi-chevron-left"></i> Quay về Danh sách Lớp
                        </a>
                    </li>
                </ul>
                <div class="navbar-text dropdown">
                    <a href="#" class="d-block link-light text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($data['user_name'] ?? 'Quản Trị'); ?> (Quản Trị)
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end text-small shadow">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL ?? ''; ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <main class="container-fluid p-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h3 mb-0">Xếp Thời Khóa Biểu:
                    <span class="text-primary fw-bold"><?php echo htmlspecialchars($data['rang_buoc']['ten_lop'] ?? 'N/A'); ?></span>
                </h1>
                <p class="text-muted mb-0">
                    Năm học: <?php echo htmlspecialchars($data['nam_hoc'] ?? 'N/A'); ?> |
                    GVCN: <span class="fw-bold"><?php echo htmlspecialchars($data['rang_buoc']['gvcn'] ?? 'N/A'); ?></span> |
                    Phòng chính: <span class="fw-bold"><?php echo htmlspecialchars($data['rang_buoc']['phong_chinh'] ?? 'N/A'); ?></span>
                </p>
            </div>
            
            <div class="d-flex align-items-center mt-2 mt-md-0 gap-2">
                
                <input type="date" id="date_picker" class="form-control" style="width: auto;"
                       value="<?php echo htmlspecialchars($data['selected_date']); ?>" 
                       onchange="jumpToDate(this.value)">

                <a href="<?php echo htmlspecialchars($data['prev_week_link']); ?>" class="btn btn-outline-primary" title="Tuần trước">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <a href="<?php echo htmlspecialchars($data['next_week_link']); ?>" class="btn btn-outline-primary" title="Tuần sau">
                    <i class="bi bi-chevron-right"></i>
                </a>

                <label for="lop_select" class="form-label ms-3 mb-0 fw-bold">Chuyển lớp:</label>
                <select id="lop_select" class="form-select d-inline-block w-auto shadow-sm" onchange="changeLop(this.value)">
                    <?php $lop_hien_tai = $data['ma_lop'] ?? 0; ?>
                     <?php
                        $danh_sach_lop_sorted = $data['danh_sach_lop'] ?? [];
                        usort($danh_sach_lop_sorted, function($a, $b) {
                            preg_match('/^(\d+)/', $a['ten_lop'], $matches_a);
                            preg_match('/^(\d+)/', $b['ten_lop'], $matches_b);
                            $khoi_a = $matches_a[1] ?? 0;
                            $khoi_b = $matches_b[1] ?? 0;
                            if ($khoi_a != $khoi_b) {
                                return $khoi_a <=> $khoi_b;
                            }
                            return strcmp($a['ten_lop'], $b['ten_lop']);
                        });
                     ?>
                    <?php foreach ($danh_sach_lop_sorted as $lop): ?>
                        <option value="<?php echo htmlspecialchars($lop['ma_lop']); ?>" <?php echo $lop_hien_tai == $lop['ma_lop'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lop['ten_lop']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>


        <?php if (isset($data['flash_message'])): ?>
            <div class="alert alert-<?php echo htmlspecialchars($data['flash_message']['type']); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($data['flash_message']['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="timetable-container">

            <div class="timetable-main shadow-sm">

                <div class="alert alert-primary mb-0 border-0 rounded-0 fw-bold text-center">
                    <?php echo htmlspecialchars($data['ten_hoc_ky']); ?>
                </div>

                <?php if ($data['ma_hoc_ky'] !== null): ?>
                
                    <div class="timetable-grid">
                        <div class="timetable-header">Tiết</div>
                        <div class="timetable-header">Thứ 2<br><small class="fw-normal"><?php echo $data['week_dates'][0]; ?></small></div>
                        <div class="timetable-header">Thứ 3<br><small class="fw-normal"><?php echo $data['week_dates'][1]; ?></small></div>
                        <div class="timetable-header">Thứ 4<br><small class="fw-normal"><?php echo $data['week_dates'][2]; ?></small></div>
                        <div class="timetable-header">Thứ 5<br><small class="fw-normal"><?php echo $data['week_dates'][3]; ?></small></div>
                        <div class="timetable-header">Thứ 6<br><small class="fw-normal"><?php echo $data['week_dates'][4]; ?></small></div>
                        <div class="timetable-header">Thứ 7<br><small class="fw-normal"><?php echo $data['week_dates'][5]; ?></small></div>
                        <div class="timetable-header" style="background-color: #fff3cd;">Chủ Nhật<br><small class="fw-normal"><?php echo $data['week_dates'][6] ?? 'N/A'; ?></small></div>

                        <div class="timetable-session-label session-morning"><i class="bi bi-brightness-high-fill me-2"></i> SÁNG</div>
                        <?php $tkb = $data['tkb_data'] ?? []; $rang_buoc_mon = $data['rang_buoc']['mon_hoc'] ?? []; ?>
                        <?php
                            // Lấy mã phân công Chào Cờ, Sinh Hoạt
                            $ma_pc_chao_co = null; $ma_pc_sinh_hoat = null;
                            if(isset($rang_buoc_mon) && is_array($rang_buoc_mon)) {
                                foreach($rang_buoc_mon as $ten_mon => $details) {
                                    if ($ten_mon == 'Chào cờ' && isset($details['ma_phan_cong_list'][0])) $ma_pc_chao_co = $details['ma_phan_cong_list'][0];
                                    if ($ten_mon == 'Sinh hoạt' && isset($details['ma_phan_cong_list'][0])) $ma_pc_sinh_hoat = $details['ma_phan_cong_list'][0];
                                }
                            }
                        ?>
                        <?php for ($tiet = 1; $tiet <= 4; $tiet++): ?>
                            <div class="timetable-period-label">Tiết <?php echo $tiet; ?></div>
                            <?php for ($thu = 2; $thu <= 8; $thu++): ?>
                                <?php
                                    $slot = $tkb[$thu][$tiet] ?? null; $is_fixed = false; $fixed_data = null; $current_ma_phong = null;
                                    if ($thu == 2 && $tiet == 1) { // Chào cờ
                                        $is_fixed = true; $fixed_data = ['mon' => 'Chào cờ', 'gv' => $data['rang_buoc']['gvcn'] ?? 'GVCN', 'phong' => $data['rang_buoc']['phong_chinh'] ?? 'Sân trường', 'ma_phan_cong' => $ma_pc_chao_co]; $current_ma_phong = null;
                                    } elseif ($thu == 2 && $tiet == 2) { // Sinh hoạt
                                        $is_fixed = true; $fixed_data = ['mon' => 'Sinh hoạt', 'gv' => $data['rang_buoc']['gvcn'] ?? 'GVCN', 'phong' => $data['rang_buoc']['phong_chinh'] ?? 'Phòng học', 'ma_phan_cong' => $ma_pc_sinh_hoat]; $current_ma_phong = $data['phong_hoc_chinh_id'];
                                    } elseif ($slot) { // Tiết đã xếp
                                        $is_fixed = true; $fixed_data = $slot; $current_ma_phong = $slot['ma_phong'] ?? $data['phong_hoc_chinh_id'] ?? ''; if ($current_ma_phong === null) $current_ma_phong = '';
                                    }
                                ?>
                                <div class="timetable-slot <?php echo ($is_fixed && ($thu == 2 && ($tiet == 1 || $tiet == 2))) ? 'slot-fixed' : ''; ?>">
                                    <?php if ($is_fixed): ?>
                                        <?php
                                            $loai = $fixed_data['loai_tiet'] ?? 'hoc';
                                            $cls = ($thu == 2 && ($tiet == 1 || $tiet == 2)) ? 'bg-secondary' : ($loai === 'thi' ? 'event-thi' : ($loai === 'tam_nghi' ? 'event-nghi' : 'event-hoc'));
                                            
                                            // Hiển thị môn/GV thực tế, chỉ dùng placeholder khi NULL
                                            $mon_display = !empty($fixed_data['mon']) 
                                                ? htmlspecialchars($fixed_data['mon']) 
                                                : ($loai === 'tam_nghi' ? 'Tạm nghỉ' : 'N/A');
                                            $gv_display = !empty($fixed_data['gv']) 
                                                ? htmlspecialchars($fixed_data['gv']) 
                                                : ($loai === 'tam_nghi' ? '---' : 'N/A');
                                            $phong_display = !empty($fixed_data['phong']) 
                                                ? htmlspecialchars($fixed_data['phong']) 
                                                : ($loai === 'tam_nghi' ? '---' : 'N/A');
                                        ?>
                                        <div class="event-card <?php echo $cls; ?>" data-bs-toggle="modal" data-bs-target="#scheduleModal" data-thu="<?php echo $thu; ?>" data-tiet="<?php echo $tiet; ?>" data-ma-phan-cong="<?php echo $fixed_data['ma_phan_cong'] ?? ''; ?>" data-ma-phong-hoc="<?php echo $current_ma_phong; ?>" data-is-fixed="<?php echo ($thu == 2 && ($tiet == 1 || $tiet == 2)) ? '1' : '0'; ?>" data-loai-tiet="<?php echo $loai; ?>" data-ghi-chu="<?php echo htmlspecialchars($fixed_data['ghi_chu'] ?? ''); ?>">
                                            <div class="event-title"><?php echo $mon_display; ?></div>
                                            <p><i class="bi bi-person-fill"></i> <?php echo $gv_display; ?></p>
                                            <p><i class="bi bi-geo-alt-fill"></i> <?php echo $phong_display; ?></p>
                                            <?php if (!empty($fixed_data['ghi_chu'])): ?>
                                                <p class="event-note"><i class="bi bi-info-circle"></i> <?php echo htmlspecialchars($fixed_data['ghi_chu']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="add-event-btn" data-bs-toggle="modal" data-bs-target="#scheduleModal" data-thu="<?php echo $thu; ?>" data-tiet="<?php echo $tiet; ?>" data-is-fixed="0"><i class="bi bi-plus-lg"></i></div>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                        <?php endfor; ?>
                        <div class="timetable-session-label session-afternoon"><i class="bi bi-cloud-sun-fill me-2"></i> CHIỀU</div>
                        <?php for ($tiet = 5; $tiet <= 7; $tiet++): ?>
                            <div class="timetable-period-label">Tiết <?php echo $tiet; ?></div>
                            <?php for ($thu = 2; $thu <= 8; $thu++): ?>
                                <?php
                                    $slot = $tkb[$thu][$tiet] ?? null; $is_fixed = false; $fixed_data = null; $current_ma_phong = null;
                                    if ($slot) {
                                        $is_fixed = true; $fixed_data = $slot; $current_ma_phong = $slot['ma_phong'] ?? $data['phong_hoc_chinh_id'] ?? ''; if ($current_ma_phong === null) $current_ma_phong = '';
                                    }
                                ?>
                                <div class="timetable-slot">
                                    <?php if ($is_fixed): ?>
                                        <?php
                                            $loai = $fixed_data['loai_tiet'] ?? 'hoc';
                                            $cls = $loai === 'thi' ? 'event-thi' : ($loai === 'tam_nghi' ? 'event-nghi' : 'event-hoc');
                                            
                                            // Hiển thị môn/GV thực tế, chỉ dùng placeholder khi NULL
                                            $mon_display = !empty($fixed_data['mon']) 
                                                ? htmlspecialchars($fixed_data['mon']) 
                                                : ($loai === 'tam_nghi' ? 'Tạm nghỉ' : 'N/A');
                                            $gv_display = !empty($fixed_data['gv']) 
                                                ? htmlspecialchars($fixed_data['gv']) 
                                                : ($loai === 'tam_nghi' ? '---' : 'N/A');
                                            $phong_display = !empty($fixed_data['phong']) 
                                                ? htmlspecialchars($fixed_data['phong']) 
                                                : ($loai === 'tam_nghi' ? '---' : 'N/A');
                                        ?>
                                        <div class="event-card <?php echo $cls; ?>" data-bs-toggle="modal" data-bs-target="#scheduleModal" data-thu="<?php echo $thu; ?>" data-tiet="<?php echo $tiet; ?>" data-ma-phan-cong="<?php echo $fixed_data['ma_phan_cong'] ?? ''; ?>" data-ma-phong-hoc="<?php echo $current_ma_phong; ?>" data-is-fixed="0" data-loai-tiet="<?php echo $loai; ?>" data-ghi-chu="<?php echo htmlspecialchars($fixed_data['ghi_chu'] ?? ''); ?>">
                                            <div class="event-title"><?php echo $mon_display; ?></div>
                                            <p><i class="bi bi-person-fill"></i> <?php echo $gv_display; ?></p>
                                            <p><i class="bi bi-geo-alt-fill"></i> <?php echo $phong_display; ?></p>
                                            <?php if (!empty($fixed_data['ghi_chu'])): ?>
                                                <p class="event-note"><i class="bi bi-info-circle"></i> <?php echo htmlspecialchars($fixed_data['ghi_chu']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="add-event-btn" data-bs-toggle="modal" data-bs-target="#scheduleModal" data-thu="<?php echo $thu; ?>" data-tiet="<?php echo $tiet; ?>" data-is-fixed="0"><i class="bi bi-plus-lg"></i></div>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                        <?php endfor; ?>
                    </div>
                
                <?php else: ?>

                    <div class="d-flex align-items-center justify-content-center" style="min-height: 400px;">
                        <div class="text-center text-muted">
                            <i class="bi bi-calendar-x-fill" style="font-size: 3rem;"></i>
                            <h3 class="mt-2">Ngoài Giai Đoạn Học</h3>
                            <p>Không có lịch học để xếp cho tuần này.</p>
                        </div>
                    </div>

                <?php endif; ?>
                </div>

            <aside class="info-sidebar">
                <div class="card shadow-sm sticky-top" style="top: 80px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-check2-square me-2"></i> Ràng Buộc Lớp</h5>
                    </div>
                    <div class="card-body">
                        <?php
                            $tong_da_xep = $data['rang_buoc']['tong_tiet_da_xep'] ?? 0;
                            $tong_ke_hoach = $data['rang_buoc']['tong_tiet_ke_hoach'] ?? 0;
                            $gioi_han_tuan = 45; 
                            $tong_ke_hoach_safe = ($tong_ke_hoach == 0) ? 1 : $tong_ke_hoach;
                            $max_tiet_tuan = max($tong_ke_hoach, $gioi_han_tuan);
                            $tong_phan_tram = ($max_tiet_tuan == 0) ? 0 : round(($tong_da_xep / $max_tiet_tuan) * 100);
                            $vuot_qua_tuan = $tong_da_xep > $gioi_han_tuan;
                        ?>
                        <h6 class="card-title">Tổng số tiết/tuần (Giới hạn: <?php echo $gioi_han_tuan; ?>)</h6>
                        <div class="progress mb-3" style="height: 25px;" title="<?php echo "$tong_da_xep / $tong_ke_hoach (Kế hoạch) | Giới hạn: $gioi_han_tuan"; ?>">
                            <div class="progress-bar progress-bar-striped fw-bold <?php echo $vuot_qua_tuan ? 'bg-danger' : (($tong_da_xep >= $tong_ke_hoach && $tong_ke_hoach > 0) ? 'bg-success' : 'bg-primary'); ?>"
                                 role="progressbar" style="width: <?php echo min($tong_phan_tram, 100); ?>%;"
                                 aria-valuenow="<?php echo $tong_da_xep; ?>" aria-valuemin="0" aria-valuemax="<?php echo $max_tiet_tuan; ?>">
                                 <?php echo "$tong_da_xep / $tong_ke_hoach"; ?>
                            </div>
                        </div>
                         <?php if($vuot_qua_tuan): ?>
                            <div class="alert alert-danger py-1 px-2 small"><i class="bi bi-exclamation-triangle-fill me-1"></i> Vượt quá giới hạn <?php echo $gioi_han_tuan; ?> tiết/tuần!</div>
                        <?php endif; ?>


                        <h6 class="card-title mt-4">Kế hoạch môn học (Đã xếp / Kế hoạch)</h6>
                        <ul class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                               <?php
                                $rang_buoc_mon_sorted = $rang_buoc_mon; 
                                if(isset($rang_buoc_mon_sorted) && is_array($rang_buoc_mon_sorted)) {
                                    ksort($rang_buoc_mon_sorted);
                                } else {
                                    $rang_buoc_mon_sorted = [];
                                }
                               ?>
                            <?php foreach ($rang_buoc_mon_sorted as $ten_mon => $details): ?>
                                <?php
                                    if ($ten_mon == 'Chào cờ' || $ten_mon == 'Sinh hoạt' || $ten_mon == 'Sinh hoạt lớp') continue;
                                    $da_xep = (int)$details['da_xep']; $ke_hoach = (int)$details['ke_hoach']; $count_str = "$da_xep / $ke_hoach";
                                    $bg_badge = 'bg-warning text-dark'; // Đang làm
                                    $icon_warning = '';
                                    if ($ke_hoach == 0) { $bg_badge = 'bg-secondary'; $count_str = "0 / 0"; }
                                    elseif ($da_xep > $ke_hoach) { $bg_badge = 'bg-danger'; $icon_warning = '<i class="bi bi-exclamation-triangle-fill"></i>'; } // Vượt
                                    elseif ($da_xep == $ke_hoach) { $bg_badge = 'bg-success'; } // Đủ
                                    elseif ($da_xep == 0) { $bg_badge = 'bg-light text-dark border'; } // Chưa làm
                                ?>
                                <li class="list-group-item px-0" title="<?php echo $ten_mon; ?>">
                                    <?php echo htmlspecialchars($ten_mon); ?>
                                    <span class="badge <?php echo $bg_badge; ?> float-end"><?php echo $icon_warning . ' ' . $count_str; ?></span>
                                </li>
                            <?php endforeach; ?>
                            <?php if(empty($rang_buoc_mon_sorted)): ?>
                                <li class="list-group-item px-0 text-muted text-center small">
                                    (Không có kế hoạch môn học)
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog">
            <div class="modal-content">
                <form id="tkbForm" method="POST" action="<?php echo BASE_URL . '/tkb/luuTietHoc'; ?>">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold" id="modalTitle">Quản lý Tiết học</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="ma_lop" value="<?php echo $data['ma_lop']; ?>">
                        <input type="hidden" name="ma_hoc_ky" value="<?php echo $data['ma_hoc_ky']; ?>">
                        <input type="hidden" id="thu_hidden" name="thu">
                        <input type="hidden" id="tiet_hidden" name="tiet">
                        
                        <div id="modalLoading" class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Đang tải...</p></div>
                        <div id="modalFormContent" style="display: none;">
                            <div class="alert alert-info py-2"><strong>Thứ <span id="info_thu"></span> - Tiết <span id="info_tiet"></span></strong></div>
                            <div class="mb-3">
                                <label for="ma_phan_cong_select" class="form-label fw-bold"><i class="bi bi-book-fill text-primary me-2"></i>Môn học - Giáo viên</label>
                                <select class="form-select shadow-sm" id="ma_phan_cong_select" name="ma_phan_cong" required><option value="">Chọn...</option></select>
                                <div class="form-text text-muted small">Giáo viên/Phòng bận sẽ bị vô hiệu hóa.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold"><i class="bi bi-flag-fill text-warning me-2"></i>Loại tiết</label>
                                <select class="form-select shadow-sm" id="loai_tiet_select" name="loai_tiet">
                                    <option value="hoc">Tiết học</option>
                                    <option value="thi">Tiết thi</option>
                                    <option value="tam_nghi">Tạm nghỉ</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold"><i class="bi bi-chat-left-text text-secondary me-2"></i>Ghi chú</label>
                                <textarea class="form-control" rows="2" name="ghi_chu" id="ghi_chu_input" placeholder="Lý do bận / nội dung thi..."></textarea>
                            </div>
                            <div id="modalError" class="alert alert-danger mt-3" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between bg-light">
                        <button type="submit" class="btn btn-danger" id="deleteButton" name="delete" value="1" style="display: none;"><i class="bi bi-trash-fill me-1"></i> Xóa tiết</button>
                        <div><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary" id="saveButton" name="save" value="1" disabled><i class="bi bi-check-circle-fill me-1"></i> Lưu</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer mt-auto py-3 bg-dark text-white mt-5"><div class="container text-center"><span class="text-white-50">&copy; <?php echo date("Y"); ?> Hệ thống Quản lý Trường THPT.</span></div></footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const scheduleModal = new bootstrap.Modal(document.getElementById('scheduleModal'));
        const modalElement = document.getElementById('scheduleModal');
        const modalFormContent = document.getElementById('modalFormContent');
        const modalLoading = document.getElementById('modalLoading');
        const maPhanCongSelect = document.getElementById('ma_phan_cong_select');
        const loaiTietSelect = document.getElementById('loai_tiet_select');
        const ghiChuInput = document.getElementById('ghi_chu_input');
        const deleteButton = document.getElementById('deleteButton');
        const saveButton = document.getElementById('saveButton');
        const infoThu = document.getElementById('info_thu');
        const infoTiet = document.getElementById('info_tiet');
        const modalError = document.getElementById('modalError');

        const maLop = <?php echo $data['ma_lop']; ?>;
        const maHocKy = <?php echo $data['ma_hoc_ky'] ?? 'null'; ?>;
        const BASE_URL = "<?php echo BASE_URL ?? ''; ?>";
        const CURRENT_DATE_PARAM = "<?php echo $data['current_date_param'] ?? ''; ?>";
        
        // Lưu lại date param để dùng cho form POST
        <?php $_SESSION['last_date_param'] = $data['current_date_param'] ?? ''; ?>

        modalElement.addEventListener('show.bs.modal', async (event) => {
            const button = event.relatedTarget;
            
            // Nếu không phải học kỳ, không mở modal
            if (maHocKy === null) {
                event.preventDefault();
                return;
            }

            const thu = button.dataset.thu;
            const tiet = button.dataset.tiet;
            const maPhanCongHienTai = button.dataset.maPhanCong || '';
            const isFixed = button.dataset.isFixed === '1';
            const loaiTietHienTai = button.dataset.loaiTiet || 'hoc';
            const ghiChuHienTai = button.dataset.ghiChu || '';

            // Reset Modal state
            modalFormContent.style.display = 'none'; modalLoading.style.display = 'block'; modalError.style.display = 'none';
            saveButton.disabled = true; deleteButton.style.display = 'none';
            maPhanCongSelect.innerHTML = '<option value="">Đang tải...</option>';

            document.getElementById('thu_hidden').value = thu; document.getElementById('tiet_hidden').value = tiet;
            infoThu.textContent = thu; infoTiet.textContent = tiet;
            loaiTietSelect.value = loaiTietHienTai;
            ghiChuInput.value = ghiChuHienTai;

            if (isFixed) {
                saveButton.disabled = true; deleteButton.style.display = 'none';
                maPhanCongSelect.disabled = true;
                 modalFormContent.style.display = 'block'; modalLoading.style.display = 'none';
            } else {
                 maPhanCongSelect.disabled = false;
                 // ✅ HIỂN THỊ NÚT XÓA: Nếu có tiết (kiểm tra button là event-card)
                 const hasContent = button.classList.contains('event-card');
                 if (hasContent) {
                     deleteButton.style.display = 'block';
                 }
            }

            // Nếu tạm nghỉ thì không bắt buộc chọn phân công (nhưng vẫn cho phép chọn)
            if (loaiTietHienTai === 'tam_nghi') {
                maPhanCongSelect.removeAttribute('required');
                maPhanCongSelect.disabled = false; // ✅ VẪN CHO PHÉP CHỌN
            } else {
                maPhanCongSelect.setAttribute('required', 'required');
                maPhanCongSelect.disabled = false;
            }

            // ✅ XỬ LÝ KHI USER THAY ĐỔI LOẠI TIẾT TRONG MODAL
            loaiTietSelect.addEventListener('change', function() {
                if (this.value === 'tam_nghi') {
                    // Tạm nghỉ: bỏ required, NHƯNG VẪN CHO PHÉP CHỌN
                    maPhanCongSelect.disabled = false; // ✅ VẪN ENABLE
                    maPhanCongSelect.removeAttribute('required');
                    // ✅ Nếu chưa load danh sách, gọi API
                    if (maPhanCongSelect.options.length <= 1) {
                        const thuVal = document.getElementById('thu_hidden').value;
                        const tietVal = document.getElementById('tiet_hidden').value;
                        loadDanhSachMonHocGV(thuVal, tietVal, maPhanCongHienTai);
                    }
                    modalError.style.display = 'none';
                    saveButton.disabled = false;
                } else {
                    // Học/Thi: bắt buộc chọn, reload API
                    maPhanCongSelect.disabled = false;
                    maPhanCongSelect.setAttribute('required', 'required');
                    maPhanCongSelect.innerHTML = '<option value="">Đang tải lại...</option>';
                    saveButton.disabled = true;
                    
                    // Gọi lại API để load danh sách môn-GV
                    const thuVal = document.getElementById('thu_hidden').value;
                    const tietVal = document.getElementById('tiet_hidden').value;
                    loadDanhSachMonHocGV(thuVal, tietVal, maPhanCongHienTai);
                }
            });

            // ✅ HÀM LOAD API (tách riêng để tái sử dụng)
            async function loadDanhSachMonHocGV(thu, tiet, selectedMaPhanCong = '') {
                try {
                    const apiUrl = `${BASE_URL}/tkb/getDanhSachMonHocGV/${maLop}/${thu}/${tiet}`;
                    const response = await fetch(apiUrl);
                    if (!response.ok) throw new Error(`Lỗi HTTP ${response.status}`);
                    const raw = await response.text();
                    let dataApi;
                    try {
                        dataApi = JSON.parse(raw);
                    } catch (e) {
                        console.error('Raw response:', raw.slice(0, 2000));
                        const snippet = raw.replace(/\s+/g, ' ').trim().slice(0, 800);
                        modalError.textContent = `Lỗi: Server trả về không phải JSON. Snippet: ${snippet}`;
                        modalError.style.display = 'block';
                        saveButton.disabled = true;
                        throw new Error('Non-JSON response');
                    }
                    if (dataApi.error) throw new Error(dataApi.error);

                    // Populate select
                    maPhanCongSelect.innerHTML = '<option value="">-- Chọn môn học & giáo viên --</option>';
                    let foundSelected = false;
                    if (dataApi.mon_hoc_gv) {
                        dataApi.mon_hoc_gv.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.ma_phan_cong;
                            option.textContent = item.ten_hien_thi;
                            if (item.is_ban && item.ma_phan_cong != selectedMaPhanCong) {
                                option.disabled = true;
                                option.textContent += ` ${item.ly_do || '(Bận)'}`;
                                option.style.backgroundColor = '#f8d7da';
                                option.style.color = '#842029';
                            }
                            if (item.ma_phan_cong == selectedMaPhanCong) {
                                option.selected = true;
                                foundSelected = true;
                            }
                            maPhanCongSelect.appendChild(option);
                        });
                    }

                    if (selectedMaPhanCong && !foundSelected) {
                        const option = document.createElement('option');
                        option.value = selectedMaPhanCong;
                        option.textContent = `Môn/GV ID ${selectedMaPhanCong} (Lỗi?)`;
                        option.selected = true;
                        option.disabled = true;
                        maPhanCongSelect.insertBefore(option, maPhanCongSelect.firstChild);
                    }

                    saveButton.disabled = false;
                    modalError.style.display = 'none';
                } catch (error) {
                    console.error('Lỗi load API:', error);
                    modalError.textContent = `Lỗi: ${error.message}`;
                    modalError.style.display = 'block';
                    saveButton.disabled = true;
                }
            }

            // Fetch dynamic data - ✅ LUÔN GỌI API (kể cả tạm nghỉ)
            if (!isFixed) {
                await loadDanhSachMonHocGV(thu, tiet, maPhanCongHienTai);
                modalFormContent.style.display = 'block';
                modalLoading.style.display = 'none';
            } else {
                // isFixed = true (Chào cờ/Sinh hoạt)
                modalFormContent.style.display = 'block';
                modalLoading.style.display = 'none';
            }
        });

        // Reset form on modal close
        modalElement.addEventListener('hidden.bs.modal', () => {
             document.getElementById('tkbForm').reset();
             maPhanCongSelect.innerHTML = ''; deleteButton.style.display = 'none';
             saveButton.disabled = true; modalError.style.display = 'none'; modalError.textContent = '';
               maPhanCongSelect.disabled = false;
               loaiTietSelect.value = 'hoc';
               ghiChuInput.value = '';
        });

        // Delete button confirmation
        deleteButton.addEventListener('click', (e) => { if (!confirm('Xóa tiết học này?')) e.preventDefault(); });

        // Save button validation
        document.getElementById('tkbForm').addEventListener('submit', function(e) {
            const submitter = e.submitter;
            if (submitter && submitter.name === 'delete') return;
            if (loaiTietSelect.value !== 'tam_nghi' && (maPhanCongSelect.value === "" || !this.checkValidity())) {
                e.preventDefault(); modalError.textContent = 'Vui lòng chọn Môn học và Giáo viên.';
                modalError.style.display = 'block'; maPhanCongSelect.focus();
                return;
            }
            modalError.style.display = 'none';
        });

        // HÀM MỚI: Dùng cho Date Picker
        function jumpToDate(selectedDate) {
            if (selectedDate) {
                document.body.style.opacity = '0.5'; 
                document.body.style.cursor = 'wait';
                window.location.href = `${BASE_URL}/tkb/chiTietTkb/${maLop}?date=${selectedDate}`;
            }
        }

        // HÀM CŨ: ĐÃ CẬP NHẬT
        function changeLop(ma_lop) {
            if (ma_lop) {
                document.body.style.opacity = '0.5'; 
                document.body.style.cursor = 'wait';
                window.location.href = `${BASE_URL}/tkb/chiTietTkb/${ma_lop}${CURRENT_DATE_PARAM}`;
            }
        }
    </script>
</body>
</html>