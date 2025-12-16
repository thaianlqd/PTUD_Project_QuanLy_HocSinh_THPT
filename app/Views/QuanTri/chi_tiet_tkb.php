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
            --sidebar-width: 380px;
        }
        body { background-color: #f8f9fa; }
        .timetable-container { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
        @media (min-width: 1200px) { .timetable-container { grid-template-columns: 1fr var(--sidebar-width); } }
        .timetable-main { overflow-x: auto; background-color: #fff; border: 1px solid var(--fc-border-color); border-radius: 0.375rem; }
        .timetable-grid { display: grid; grid-template-columns: 80px repeat(7, minmax(140px, 1fr)); grid-template-rows: auto auto repeat(4, minmax(var(--slot-min-height), auto)) auto repeat(3, minmax(var(--slot-min-height), auto)); }

        .timetable-header, .timetable-slot, .timetable-period-label {
            padding: 0.5rem; text-align: center;
            border-bottom: 1px solid var(--fc-border-color); border-right: 1px solid var(--fc-border-color);
            font-size: 0.9rem;
        }
        .timetable-grid > div:nth-child(8n) { border-right: none; }
        .timetable-period-label, .timetable-grid > div:nth-child(8n+1) { border-left: 1px solid var(--fc-border-color); }
        .timetable-grid > div:nth-last-child(-n+8) { border-bottom: none; }

        .timetable-header { font-weight: 600; background-color: #f1f3f5; position: sticky; top: 0; z-index: 10; }
        .timetable-period-label { font-weight: bold; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; position: sticky; left: 0; z-index: 5; writing-mode: vertical-rl; transform: rotate(180deg); }
        .timetable-session-label { grid-column: 1 / -1; text-align: center; font-weight: bold; font-size: 1rem; padding: 0.5rem; border: 1px solid var(--fc-border-color); position: sticky; top: 58px; z-index: 9; }
        .session-morning { background-color: #fff3cd; color: #664d03; }
        .session-afternoon { background-color: #cfe2ff; color: #0a3675; }
        
        .timetable-slot { position: relative; padding: 5px; transition: background-color 0.2s; }
        .timetable-slot:hover { background-color: #f8f9fa; }
        .timetable-slot.slot-fixed { background-color: #e9ecef; cursor: not-allowed; }
        
        .add-event-btn { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 36px; height: 36px; border-radius: 50%; border: 2px dashed #adb5bd; display: flex; align-items: center; justify-content: center; cursor: pointer; opacity: 0; transition: all 0.2s; color: #495057; background-color: rgba(233, 236, 239, 0.5); }
        .timetable-slot:not(.slot-fixed):hover .add-event-btn { opacity: 1; transform: translate(-50%, -50%) scale(1.1); background-color: #e9ecef; }
        
        .event-card { border-radius: 6px; padding: 8px; color: #fff; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; font-size: 0.85rem; text-align: left; height: 100%; overflow: hidden; display: flex; flex-direction: column; justify-content: center; line-height: 1.3; position: relative; }
        .event-card:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .event-card .event-title { font-weight: bold; margin-bottom: 3px; font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .event-card p { margin-bottom: 1px; font-size: 0.78rem; opacity: 0.9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .event-card i { font-size: 0.7rem; margin-right: 3px; }
        
        .icon-changed { position: absolute; top: 2px; right: 2px; font-size: 10px; background: white; color: #dc3545; border-radius: 50%; padding: 1px 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.2); z-index: 2; }

        .info-sidebar .card { border: 1px solid var(--fc-border-color); }
        .progress-bar.bg-danger::after { content: " (Vượt!)"; font-weight: normal; font-size: 0.8em;}
        
        /* MÀU SẮC CÁC LOẠI TIẾT */
        .event-hoc    { background-color: #198754; } /* Xanh lá */
        .event-thi    { background-color: #f59e0b; } /* Vàng */
        .event-nghi   { background-color: #dc3545; } /* Đỏ */
        .event-bu { 
            background-color: #0d6efd !important; /* Màu xanh Bootstrap chuẩn */
            color: #fff;
        }
        .event-changed { border: 2px solid #ffc107; } 
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?php echo BASE_URL ?? ''; ?>/dashboard">
                <i class="bi bi-calendar-check-fill"></i> Hệ Thống Quản Lý THPT
            </a>
            <div class="navbar-text text-white">
                <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($data['user_name'] ?? 'Quản Trị'); ?>
            </div>
        </div>
    </header>

    <main class="container-fluid p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h3 mb-0">Xếp TKB: <span class="text-primary fw-bold"><?php echo htmlspecialchars($data['rang_buoc']['ten_lop'] ?? 'N/A'); ?></span></h1>
                <p class="text-muted mb-0">GVCN: <?php echo htmlspecialchars($data['rang_buoc']['gvcn'] ?? 'N/A'); ?> | Phòng: <?php echo htmlspecialchars($data['rang_buoc']['phong_chinh'] ?? 'N/A'); ?></p>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <input type="date" id="date_picker" class="form-control" style="width: auto;" value="<?php echo htmlspecialchars($data['selected_date']); ?>" onchange="jumpToDate(this.value)">
                <a href="<?php echo htmlspecialchars($data['prev_week_link']); ?>" class="btn btn-outline-primary"><i class="bi bi-chevron-left"></i></a>
                <a href="<?php echo htmlspecialchars($data['next_week_link']); ?>" class="btn btn-outline-primary"><i class="bi bi-chevron-right"></i></a>
                
                <select id="lop_select" class="form-select w-auto shadow-sm ms-2" onchange="changeLop(this.value)">
                    <?php foreach ($data['danh_sach_lop'] as $lop): ?>
                        <option value="<?php echo $lop['ma_lop']; ?>" <?php echo ($data['ma_lop'] == $lop['ma_lop']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lop['ten_lop']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (isset($data['flash_message'])): ?>
            <div class="alert alert-<?php echo htmlspecialchars($data['flash_message']['type']); ?> alert-dismissible fade show">
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
                        <?php for($i=0; $i<7; $i++): ?>
                            <div class="timetable-header">
                                <?php echo ($i==6) ? 'Chủ Nhật' : 'Thứ ' . ($i+2); ?><br>
                                <small class="fw-normal"><?php echo $data['week_dates'][$i]; ?></small>
                            </div>
                        <?php endfor; ?>

                        <div class="timetable-session-label session-morning"><i class="bi bi-brightness-high-fill me-2"></i> SÁNG</div>
                        <?php 
                            $tkb = $data['tkb_data'] ?? [];
                            function getYMD($dmy) {
                                $dt = DateTime::createFromFormat('d/m/Y', $dmy);
                                return $dt ? $dt->format('Y-m-d') : '';
                            }
                        ?>
                        
                        <?php for ($tiet = 1; $tiet <= 4; $tiet++): ?>
                            <div class="timetable-period-label">Tiết <?php echo $tiet; ?></div>
                            <?php for ($thu = 2; $thu <= 8; $thu++): ?>
                                <?php
                                    $slot = $tkb[$thu][$tiet] ?? null;
                                    $is_fixed = false; $fixed_data = null; $cls_border = '';
                                    
                                    if ($thu == 2 && $tiet == 1) { $is_fixed = true; $fixed_data = ['mon' => 'Chào cờ', 'gv' => 'GVCN', 'phong' => 'Sân trường']; }
                                    elseif ($thu == 2 && $tiet == 2) { $is_fixed = true; $fixed_data = ['mon' => 'Sinh hoạt', 'gv' => 'GVCN', 'phong' => 'Lớp học']; }
                                    elseif ($slot) { 
                                        $is_fixed = true; $fixed_data = $slot; 
                                        if (!empty($slot['is_thay_doi'])) $cls_border = 'event-changed';
                                    }

                                    $current_date_dmy = $data['week_dates'][$thu - 2];
                                    $current_date_ymd = getYMD($current_date_dmy);
                                ?>
                                <div class="timetable-slot <?php echo ($thu == 2 && $tiet <= 2) ? 'slot-fixed' : ''; ?>">
                                    <?php if ($is_fixed): ?>
                                        <?php
                                            $loai = $fixed_data['loai_tiet'] ?? 'hoc';
                                            $cls = '';
                                            if ($thu == 2 && $tiet <= 2) {
                                                $cls = 'bg-secondary';
                                            } else {
                                                // [LOGIC MÀU SẮC]
                                                switch ($loai) {
                                                    case 'thi':      $cls = 'event-thi'; break;
                                                    case 'tam_nghi': $cls = 'event-nghi'; break;
                                                    case 'day_bu':   $cls = 'event-bu'; break; // Màu tím
                                                    default:         $cls = 'event-hoc'; break;
                                                }
                                            }
                                            
                                            // [LOGIC HIỂN THỊ TÊN MÔN]
                                            $mon_raw = $fixed_data['mon'] ?? '';
                                            if ($loai == 'tam_nghi') {
                                                $mon_display = !empty($mon_raw) ? "(Nghỉ) " . htmlspecialchars($mon_raw) : "Tạm nghỉ";
                                            } else {
                                                $mon_display = htmlspecialchars($mon_raw);
                                            }
                                        ?>
                                        <div class="event-card <?php echo $cls . ' ' . $cls_border; ?>" 
                                             data-bs-toggle="modal" data-bs-target="#scheduleModal"
                                             data-thu="<?php echo $thu; ?>" 
                                             data-tiet="<?php echo $tiet; ?>"
                                             data-ngay="<?php echo $current_date_ymd; ?>" 
                                             data-ngay-display="<?php echo $current_date_dmy; ?>"
                                             data-ma-phan-cong="<?php echo $fixed_data['ma_phan_cong'] ?? ''; ?>"
                                             data-loai-tiet="<?php echo $loai; ?>"
                                             data-ghi-chu="<?php echo htmlspecialchars($fixed_data['ghi_chu'] ?? ''); ?>"
                                             data-is-thay-doi="<?php echo !empty($fixed_data['is_thay_doi']) ? '1' : '0'; ?>"
                                             data-is-fixed="<?php echo ($thu == 2 && $tiet <= 2) ? '1' : '0'; ?>">
                                            
                                            <?php if (!empty($fixed_data['is_thay_doi'])): ?>
                                                <div class="icon-changed" title="Tiết này đã thay đổi"><i class="bi bi-exclamation-lg"></i></div>
                                            <?php endif; ?>
                                            
                                            <div class="event-title"><?php echo $mon_display; ?></div>
                                            
                                            <?php if (!empty($fixed_data['gv'])): ?>
                                                <p><i class="bi bi-person-fill"></i> <?php echo htmlspecialchars($fixed_data['gv']); ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($fixed_data['phong'])): ?>
                                                <p>
                                                    <i class="bi bi-geo-alt-fill"></i> 
                                                    <span class="<?php echo ($loai == 'tam_nghi') ? 'text-decoration-line-through opacity-50' : ''; ?>">
                                                        <?php echo htmlspecialchars($fixed_data['phong']); ?>
                                                    </span>
                                                </p>
                                            <?php endif; ?>

                                            <?php if (!empty($fixed_data['ghi_chu'])): ?>
                                                <p class="small fst-italic mt-1"><i class="bi bi-chat-quote"></i> <?php echo htmlspecialchars($fixed_data['ghi_chu']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="add-event-btn" 
                                             data-bs-toggle="modal" data-bs-target="#scheduleModal"
                                             data-thu="<?php echo $thu; ?>" 
                                             data-tiet="<?php echo $tiet; ?>"
                                             data-ngay="<?php echo $current_date_ymd; ?>"
                                             data-ngay-display="<?php echo $current_date_dmy; ?>"
                                             data-is-fixed="0"><i class="bi bi-plus-lg"></i></div>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                        <?php endfor; ?>

                        <div class="timetable-session-label session-afternoon"><i class="bi bi-cloud-sun-fill me-2"></i> CHIỀU</div>
                        <?php for ($tiet = 5; $tiet <= 7; $tiet++): ?>
                            <div class="timetable-period-label">Tiết <?php echo $tiet; ?></div>
                            <?php for ($thu = 2; $thu <= 8; $thu++): ?>
                                <?php
                                    $slot = $tkb[$thu][$tiet] ?? null;
                                    $current_date_dmy = $data['week_dates'][$thu - 2];
                                    $current_date_ymd = getYMD($current_date_dmy);
                                    
                                    $is_fixed = false; $fixed_data = null; $cls_border = '';
                                    if ($slot) { 
                                        $is_fixed = true; $fixed_data = $slot;
                                        if (!empty($slot['is_thay_doi'])) $cls_border = 'event-changed';
                                    }
                                ?>
                                <div class="timetable-slot">
                                    <?php if ($is_fixed): ?>
                                        <?php
                                            $loai = $fixed_data['loai_tiet'] ?? 'hoc';
                                            $cls = 'event-hoc';
                                            switch ($loai) {
                                                case 'thi':      $cls = 'event-thi'; break;
                                                case 'tam_nghi': $cls = 'event-nghi'; break;
                                                case 'day_bu':   $cls = 'event-bu'; break; // Màu tím
                                                default:         $cls = 'event-hoc'; break;
                                            }
                                            
                                            $mon_raw = $fixed_data['mon'] ?? '';
                                            if ($loai == 'tam_nghi') {
                                                $mon_display = !empty($mon_raw) ? "(Nghỉ) " . htmlspecialchars($mon_raw) : "Tạm nghỉ";
                                            } else {
                                                $mon_display = htmlspecialchars($mon_raw);
                                            }
                                        ?>
                                        <div class="event-card <?php echo $cls . ' ' . $cls_border; ?>"
                                             data-bs-toggle="modal" data-bs-target="#scheduleModal"
                                             data-thu="<?php echo $thu; ?>" 
                                             data-tiet="<?php echo $tiet; ?>"
                                             data-ngay="<?php echo $current_date_ymd; ?>" 
                                             data-ngay-display="<?php echo $current_date_dmy; ?>"
                                             data-ma-phan-cong="<?php echo $fixed_data['ma_phan_cong'] ?? ''; ?>"
                                             data-loai-tiet="<?php echo $loai; ?>"
                                             data-ghi-chu="<?php echo htmlspecialchars($fixed_data['ghi_chu'] ?? ''); ?>"
                                             data-is-thay-doi="<?php echo !empty($fixed_data['is_thay_doi']) ? '1' : '0'; ?>">
                                            
                                            <?php if (!empty($fixed_data['is_thay_doi'])): ?>
                                                <div class="icon-changed"><i class="bi bi-exclamation-lg"></i></div>
                                            <?php endif; ?>

                                            <div class="event-title"><?php echo $mon_display; ?></div>
                                            
                                            <?php if (!empty($fixed_data['gv'])): ?>
                                                <p><i class="bi bi-person-fill"></i> <?php echo htmlspecialchars($fixed_data['gv']); ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($fixed_data['phong'])): ?>
                                                <p>
                                                    <i class="bi bi-geo-alt-fill"></i> 
                                                    <span class="<?php echo ($loai == 'tam_nghi') ? 'text-decoration-line-through opacity-50' : ''; ?>">
                                                        <?php echo htmlspecialchars($fixed_data['phong']); ?>
                                                    </span>
                                                </p>
                                            <?php endif; ?>

                                             <?php if (!empty($fixed_data['ghi_chu'])): ?>
                                                <p class="small fst-italic mt-1"><i class="bi bi-chat-quote"></i> <?php echo htmlspecialchars($fixed_data['ghi_chu']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="add-event-btn"
                                             data-bs-toggle="modal" data-bs-target="#scheduleModal"
                                             data-thu="<?php echo $thu; ?>" 
                                             data-tiet="<?php echo $tiet; ?>"
                                             data-ngay="<?php echo $current_date_ymd; ?>"
                                             data-ngay-display="<?php echo $current_date_dmy; ?>"><i class="bi bi-plus-lg"></i></div>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                        <?php endfor; ?>

                    </div>
                <?php else: ?>
                    <div class="text-center p-5 text-muted"><h3>Không có dữ liệu học kỳ</h3></div>
                <?php endif; ?>
            </div>

            <aside class="info-sidebar">
                <div class="card shadow-sm sticky-top" style="top: 80px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i> Tiến độ phân công</h5>
                    </div>
                    <div class="card-body">
                         <?php
                            $tong_da_xep = $data['rang_buoc']['tong_tiet_da_xep'] ?? 0;
                            $tong_ke_hoach = $data['rang_buoc']['tong_tiet_ke_hoach'] ?? 0;
                            $pct = ($tong_ke_hoach > 0) ? min(100, round($tong_da_xep/$tong_ke_hoach*100)) : 0;
                        ?>
                        <p class="mb-1 fw-bold">Tổng số tiết: <?php echo "$tong_da_xep / $tong_ke_hoach"; ?></p>
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar <?php echo ($tong_da_xep > $tong_ke_hoach) ? 'bg-danger' : 'bg-success'; ?>" style="width: <?php echo $pct; ?>%"></div>
                        </div>

                        <ul class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($data['rang_buoc']['mon_hoc'] ?? [] as $ten_mon => $details): ?>
                                <?php if(in_array($ten_mon, ['Chào cờ', 'Sinh hoạt'])) continue; ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center small">
                                    <?php echo $ten_mon; ?>
                                    <span class="badge <?php echo ($details['da_xep'] > $details['ke_hoach']) ? 'bg-danger' : (($details['da_xep'] < $details['ke_hoach']) ? 'bg-warning text-dark' : 'bg-success'); ?>">
                                        <?php echo $details['da_xep'] . '/' . $details['ke_hoach']; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <div class="modal fade" id="scheduleModal" tabindex="-1">
         <div class="modal-dialog">
            <div class="modal-content">
                <form id="tkbForm" method="POST" action="<?php echo BASE_URL . '/tkb/luuTietHoc'; ?>">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold">Cập nhật Tiết học</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="ma_lop" value="<?php echo $data['ma_lop']; ?>">
                        <input type="hidden" name="ma_hoc_ky" value="<?php echo $data['ma_hoc_ky']; ?>">
                        <input type="hidden" id="thu_hidden" name="thu">
                        <input type="hidden" id="tiet_hidden" name="tiet">
                        <input type="hidden" id="ngay_chon_hidden" name="ngay_chon"> 
                        
                        <div class="mb-3 p-3 bg-light rounded border">
                            <label class="form-label fw-bold d-block mb-2">Phạm vi áp dụng:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="kieu_luu" id="kieu_luu_hocky" value="hoc_ky" checked>
                                <label class="form-check-label" for="kieu_luu_hocky">
                                    Áp dụng cho <strong>Cả học kỳ</strong> (Lịch cứng)
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="kieu_luu" id="kieu_luu_ngay" value="ngay">
                                <label class="form-check-label" for="kieu_luu_ngay">
                                    Chỉ thay đổi cho <strong>ngày <span id="label_ngay_chon" class="text-primary"></span></strong>
                                </label>
                            </div>
                        </div>

                        <div id="modalLoading" class="text-center p-3"><div class="spinner-border text-primary"></div></div>
                        <div id="modalFormContent" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Môn học - Giáo viên</label>
                                <select class="form-select" id="ma_phan_cong_select" name="ma_phan_cong" required></select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Loại tiết</label>
                                    <select class="form-select" id="loai_tiet_select" name="loai_tiet">
                                        <option value="hoc">Tiết học chính</option>
                                        <option value="day_bu">Dạy bù / Học bù</option> <option value="thi">Tiết thi</option>
                                        <option value="tam_nghi">Tạm nghỉ (Không tính tiết)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ghi chú</label>
                                <input type="text" class="form-control" name="ghi_chu" id="ghi_chu_input" placeholder="Lý do nghỉ, nội dung thi...">
                            </div>
                            <div id="modalError" class="alert alert-danger d-none"></div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="submit" class="btn btn-danger" id="deleteButton" name="delete" value="1" style="display:none;">Xóa</button>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary" id="saveButton" name="save" value="1">Lưu</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Các biến toàn cục từ PHP
    const maLop = <?php echo $data['ma_lop']; ?>;
    const BASE_URL = "<?php echo BASE_URL ?? ''; ?>";
    const modalElement = document.getElementById('scheduleModal');
    const modal = new bootstrap.Modal(modalElement);
    
    // Elements Form
    const form = document.getElementById('tkbForm');
    const maPhanCongSelect = document.getElementById('ma_phan_cong_select');
    const loaiTietSelect = document.getElementById('loai_tiet_select');
    const loading = document.getElementById('modalLoading');
    const content = document.getElementById('modalFormContent');
    const btnSave = document.getElementById('saveButton');
    const btnDelete = document.getElementById('deleteButton');
    const labelNgayChon = document.getElementById('label_ngay_chon');
    
    // Elements Logic Ràng buộc
    const radioHocKy = document.getElementById('kieu_luu_hocky');
    const radioNgay = document.getElementById('kieu_luu_ngay');

    // ============================================================
    // 1. LOGIC XỬ LÝ GIAO DIỆN (FRONTEND VALIDATION)
    // ============================================================

    // Hàm xử lý khi đổi Kiểu Lưu (Học kỳ <-> Ngày)
    function onChangeKieuLuu() {
        if (radioHocKy.checked) {
            // Nếu chọn "Cả học kỳ":
            // 1. Bắt buộc loại tiết phải là 'hoc'
            if (loaiTietSelect.value !== 'hoc') {
                loaiTietSelect.value = 'hoc';
                toggleRequired(); // Cập nhật lại trạng thái required
            }
            // 2. Khóa các option không hợp lệ (Thi, Nghỉ, Bù)
            disableNonHocOptions(true);
        } else {
            // Nếu chọn "Theo ngày": Mở khóa tất cả
            disableNonHocOptions(false);
        }
    }

    // Hàm xử lý khi đổi Loại Tiết (Học <-> Thi/Nghỉ...)
    function onChangeLoaiTiet() {
        const val = loaiTietSelect.value;
        // Nếu chọn các loại đặc biệt -> Tự động tích vào "Theo ngày" (Logic bổ trợ)
        if (['thi', 'day_bu', 'tam_nghi'].includes(val)) {
            radioNgay.checked = true;
            // Đảm bảo các option được enable
            disableNonHocOptions(false);
        }
        toggleRequired();
    }

    // Hàm phụ: Khóa/Mở các option không phải 'hoc' trong dropdown
    function disableNonHocOptions(disabled) {
        const suffix = " (Chỉ theo Ngày)";
        for (let i = 0; i < loaiTietSelect.options.length; i++) {
            const opt = loaiTietSelect.options[i];
            // Nếu không phải là tiết học chính
            if (opt.value !== 'hoc') {
                opt.disabled = disabled; // Khóa hoặc Mở
                
                // Thêm text hướng dẫn cho người dùng dễ hiểu
                if (disabled) {
                    if (!opt.text.includes(suffix)) opt.text += suffix;
                } else {
                    opt.text = opt.text.replace(suffix, "");
                }
            }
        }
    }

    // Hàm quy định khi nào bắt buộc chọn Môn học
    function toggleRequired() {
        // Tạm nghỉ -> Không cần chọn môn. Còn lại (Học, Thi, Bù) -> Cần chọn môn để tính tiết.
        if (loaiTietSelect.value === 'tam_nghi') {
            maPhanCongSelect.removeAttribute('required');
        } else {
            maPhanCongSelect.setAttribute('required', 'true');
        }
    }

    // Gán sự kiện cho các input
    radioHocKy.addEventListener('change', onChangeKieuLuu);
    radioNgay.addEventListener('change', onChangeKieuLuu);
    loaiTietSelect.addEventListener('change', onChangeLoaiTiet);


    // ============================================================
    // 2. XỬ LÝ KHI MỞ MODAL
    // ============================================================
    modalElement.addEventListener('show.bs.modal', async (e) => {
        const btn = e.relatedTarget;
        const thu = btn.dataset.thu;
        const tiet = btn.dataset.tiet;
        const ngayYMD = btn.dataset.ngay; 
        const ngayDisplay = btn.dataset.ngayDisplay; 
        const isThayDoi = btn.dataset.isThayDoi === '1';
        const currentMPC = btn.dataset.maPhanCong || '';
        const currentLoai = btn.dataset.loaiTiet || 'hoc';
        
        // Fill dữ liệu hidden
        document.getElementById('thu_hidden').value = thu;
        document.getElementById('tiet_hidden').value = tiet;
        document.getElementById('ngay_chon_hidden').value = ngayYMD;
        labelNgayChon.textContent = ngayDisplay;
        
        document.getElementById('ghi_chu_input').value = btn.dataset.ghiChu || '';
        loaiTietSelect.value = currentLoai;

        // Logic chọn Radio Button mặc định
        if (isThayDoi) {
            // Đang là ô thay đổi -> Chọn Radio Ngày
            radioNgay.checked = true;
            btnDelete.textContent = "Xóa thay đổi (Về lịch gốc)";
        } else {
            // Đang là lịch cứng (hoặc trống) -> Chọn Radio Học kỳ
            radioHocKy.checked = true;
            btnDelete.textContent = "Xóa lịch cố định";
        }
        
        // [QUAN TRỌNG] Đồng bộ trạng thái khóa/mở dropdown ngay khi mở modal
        disableNonHocOptions(radioHocKy.checked);

        // Hiển thị nút xóa
        if (btn.classList.contains('event-card')) {
            btnDelete.style.display = 'block';
        } else {
            btnDelete.style.display = 'none';
        }

        // Load danh sách môn học
        content.style.display = 'none';
        loading.style.display = 'block';
        btnSave.disabled = true;
        
        await loadMonHoc(thu, tiet, currentMPC);
        
        loading.style.display = 'none';
        content.style.display = 'block';
        btnSave.disabled = false;
    });

    // ============================================================
    // 3. CÁC HÀM API & ĐIỀU HƯỚNG
    // ============================================================

    async function loadMonHoc(thu, tiet, selectedVal) {
        try {
            // [FIX] Lấy ngày cụ thể từ hidden input để truyền vào API
            const ngayYMD = document.getElementById('ngay_chon_hidden').value;

            // [FIX] Thêm tham số ?date=... vào URL
            const res = await fetch(`${BASE_URL}/tkb/getDanhSachMonHocGV/${maLop}/${thu}/${tiet}?date=${ngayYMD}`);
            const json = await res.json();
            
            maPhanCongSelect.innerHTML = '<option value="">-- Chọn --</option>';
            if (json.mon_hoc_gv) {
                json.mon_hoc_gv.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.ma_phan_cong;
                    opt.textContent = item.ten_hien_thi;
                    
                    // Nếu giáo viên bận và không phải là người đang chọn hiện tại -> Disable
                    if (item.is_ban && item.ma_phan_cong != selectedVal) {
                        opt.disabled = true;
                        opt.style.color = 'red';
                        opt.textContent += ` ${item.ly_do}`;
                    }
                    
                    if (item.ma_phan_cong == selectedVal) opt.selected = true;
                    maPhanCongSelect.appendChild(opt);
                });
            }
            toggleRequired();
        } catch (err) {
            console.error(err);
            alert('Lỗi tải danh sách môn học');
        }
    }

    window.jumpToDate = (d) => window.location.href = `${BASE_URL}/tkb/chiTietTkb/${maLop}?date=${d}`;
    window.changeLop = (id) => window.location.href = `${BASE_URL}/tkb/chiTietTkb/${id}?date=${document.getElementById('date_picker').value}`;
</script>
</body>
</html>