<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Dạy - Giáo Viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { 
        font-family: 'Segoe UI', Roboto, sans-serif; 
        background-color: #f8f9fa; 
    }
    .sidebar { 
        width: 280px; 
        position: fixed; 
        height: 100vh; 
        background: white; 
        z-index: 1000; 
        box-shadow: 4px 0 15px rgba(0,0,0,0.05); 
        overflow-y: auto; 
    }
    .main-content { 
        margin-left: 280px; 
        padding: 30px; 
    }
    .header-card { 
        background: white; 
        border-radius: 12px; 
        padding: 20px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.03); 
        margin-bottom: 25px; 
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .header-card h5 { 
        color: #0d6efd; 
        font-weight: 700; 
        margin: 0;
    }

    /* Card Thống kê */
    .stat-row {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
    }
    .stat-item {
        flex: 1;
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        text-align: center;
        border-bottom: 3px solid transparent;
        transition: transform 0.2s;
    }
    .stat-item:hover { transform: translateY(-3px); }
    .stat-item.blue { border-color: #0d6efd; }
    .stat-item.orange { border-color: #fd7e14; }
    .stat-item.green { border-color: #198754; }
    .stat-item h6 { font-size: 0.85rem; color: #6c757d; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-item .val { font-size: 1.5rem; font-weight: 800; color: #333; }

    /* Thanh công cụ */
    .toolbar-card {
        background: white;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        margin-bottom: 25px;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: center;
        justify-content: space-between;
    }
    .week-navigator {
        display: flex;
        align-items: center;
        background: #f1f3f5;
        border-radius: 8px;
        padding: 4px;
    }
    .week-navigator button {
        border: none;
        background: white;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #495057;
        transition: 0.2s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .week-navigator button:hover { background: #e9ecef; }
    .week-display {
        font-weight: 600;
        color: #333;
        padding: 0 15px;
        font-size: 0.95rem;
        cursor: pointer;
        min-width: 220px;
        text-align: center;
    }
    .week-display:hover { color: #0d6efd; }

    /* Bảng TKB */
    .table-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        overflow-x: auto;
    }
    .table-tkb {
        width: 100%;
        border-collapse: separate; 
        border-spacing: 0;
        table-layout: fixed;
    }
    .table-tkb th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        padding: 15px 10px;
        text-align: center;
        border-bottom: 2px solid #e9ecef;
        border-right: 1px solid #f1f3f5;
    }
    .table-tkb th:first-child { width: 80px; border-top-left-radius: 8px; }
    .table-tkb th:last-child { border-top-right-radius: 8px; border-right: none; }
    
    .table-tkb td {
        padding: 10px;
        border-bottom: 1px solid #f1f3f5;
        border-right: 1px solid #f1f3f5;
        vertical-align: top;
        height: 100px;
        transition: 0.2s;
    }
    .table-tkb td:first-child { 
        text-align: center; 
        vertical-align: middle; 
        background: #fafafa; 
        font-weight: bold;
        color: #adb5bd;
    }
    .table-tkb td:last-child { border-right: none; }
    .table-tkb tr:last-child td:first-child { border-bottom-left-radius: 8px; }
    .table-tkb tr:last-child td:last-child { border-bottom-right-radius: 8px; }
    .table-tkb tr:hover td { background: #fafafa; }

    /* Thẻ tiết học */
    .lesson-card {
        background: #e7f5ff;
        border-left: 4px solid #0d6efd;
        padding: 8px 10px;
        border-radius: 6px;
        font-size: 0.85rem;
        margin-bottom: 5px;
        transition: 0.2s;
    }
    .lesson-card:hover { transform: translateY(-2px); box-shadow: 0 3px 8px rgba(0,0,0,0.08); }
    
    .lesson-card.thi { background: #fff3cd; border-left-color: #ffc107; }
    .lesson-card.nghi { background: #ffe6e6; border-left-color: #dc3545; opacity: 0.8; }
    .lesson-card.bu { background: #d1e7dd; border-left-color: #198754; }

    .lesson-mon { font-weight: 700; color: #333; margin-bottom: 4px; display: block; }
    .lesson-info { display: flex; align-items: center; gap: 5px; color: #666; font-size: 0.75rem; margin-bottom: 2px; }
    .lesson-note { font-size: 0.7rem; color: #888; font-style: italic; margin-top: 4px; border-top: 1px dashed #ccc; padding-top: 4px; }

    /* Session header */
    .session-row td {
        background: #f8f9fa;
        text-align: center;
        padding: 8px;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.8rem;
    }

    .spinner-border { color: #0d6efd; }
    .nav-link { color: #666; padding: 12px 16px; display: flex; align-items: center; gap: 10px; transition: 0.3s; text-decoration: none; }
    .nav-link:hover, .nav-link.active { background: #f0f7ff; color: #0d6efd; font-weight: 600; border-right: 4px solid #0d6efd; }
    .sidebar-header { padding: 25px 20px; text-align: center; border-bottom: 1px solid #f1f1f1; }
    .sidebar-header img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 3px solid #e9ecef; }
    
    @media (max-width: 991px) { 
        .sidebar { transform: translateX(-100%); } 
        .main-content { margin-left: 0; } 
    }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($data['user_name'] ?? 'GV'); ?>&background=0d6efd&color=fff" alt="Avatar">
            <h6 class="mb-0 text-dark"><?php echo htmlspecialchars($data['user_name'] ?? 'Giáo Viên'); ?></h6>
            <small class="text-muted">Giáo Viên Bộ Môn</small>
        </div>
        <nav class="nav flex-column mt-3">
            <a class="nav-link" href="<?php echo BASE_URL; ?>/dashboard"><i class="bi bi-grid-fill"></i> Dashboard</a>
            <a class="nav-link active" href="#"><i class="bi bi-calendar-week"></i> Lịch Dạy</a>
            <a class="nav-link" href="<?php echo BASE_URL; ?>/giaovien/diemdanh"><i class="bi bi-upc-scan"></i> Điểm Danh</a>
            <a class="nav-link" href="<?php echo BASE_URL; ?>/giaovien/baitap"><i class="bi bi-journal-text"></i> Bài Tập</a>
        </nav>
        <div class="mt-auto p-3">
            <a class="nav-link text-danger bg-danger bg-opacity-10 rounded" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right"></i> Đăng Xuất</a>
        </div>
    </div>

    <div class="main-content">
        
        <div class="header-card">
            <div>
                <h5>Lịch Dạy Của Tôi</h5>
                <small class="text-muted">Xem và quản lý thời khóa biểu giảng dạy</small>
            </div>
            <button class="btn btn-light text-primary btn-sm fw-bold" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Cập nhật
            </button>
        </div>

        <?php if (!empty($data['stats'])): ?>
        <div class="stat-row">
            <div class="stat-item blue">
                <h6>Tổng Tiết Dạy</h6>
                <div class="val"><?php echo $data['stats']['tong_tiet_da_xep'] ?? 0; ?></div>
            </div>
            <div class="stat-item orange">
                <h6>Số Lớp</h6>
                <div class="val"><?php echo $data['stats']['so_lop'] ?? 0; ?></div>
            </div>
            <div class="stat-item green">
                <h6>Số Môn</h6>
                <div class="val"><?php echo $data['stats']['so_mon'] ?? 0; ?></div>
            </div>
        </div>
        <?php endif; ?>

        <div class="toolbar-card">
            <div class="d-flex align-items-center gap-3">
                <label class="fw-bold text-muted mb-0">Thời gian:</label>
                <div class="week-navigator">
                    <button onclick="changeWeek(-1)" title="Tuần trước"><i class="bi bi-chevron-left"></i></button>
                    <div class="week-display" id="weekDisplay" title="Click để chọn ngày nhanh">Đang tải...</div>
                    <input type="text" id="datePickerHidden" style="display:none;">
                    <button onclick="changeWeek(1)" title="Tuần sau"><i class="bi bi-chevron-right"></i></button>
                </div>
                <button class="btn btn-sm btn-outline-secondary" onclick="resetToToday()">Hôm nay</button>
            </div>

            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="btnradio" id="btnHK1" autocomplete="off" checked onclick="changeHocKy('HK1')">
                <label class="btn btn-outline-primary" for="btnHK1">Học Kỳ 1</label>

                <input type="radio" class="btn-check" name="btnradio" id="btnHK2" autocomplete="off" onclick="changeHocKy('HK2')">
                <label class="btn btn-outline-primary" for="btnHK2">Học Kỳ 2</label>
            </div>
        </div>

        <div class="table-container">
            <div id="loadingSpinner" class="text-center py-5">
                <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
            </div>
            <div id="tkbContent"></div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/vn.js"></script>

    <script>
        let currentHocKy = '1';
        let currentDate = new Date();
        let flatpickrInstance;

        document.addEventListener('DOMContentLoaded', () => {
            // Khởi tạo lịch
            flatpickrInstance = flatpickr("#datePickerHidden", {
                locale: "vn",
                dateFormat: "Y-m-d",
                defaultDate: currentDate,
                onChange: function(selectedDates, dateStr) {
                    currentDate = new Date(dateStr);
                    loadData();
                }
            });

            document.getElementById('weekDisplay').addEventListener('click', () => {
                flatpickrInstance.open();
            });

            loadData();
        });

        function resetToToday() {
            currentDate = new Date();
            loadData();
        }

        function changeHocKy(hk) {
            const hkMap = { 'HK1': '1', 'HK2': '2' };
            currentHocKy = hkMap[hk] || '1';
            loadData();
        }

        function changeWeek(offset) {
            currentDate.setDate(currentDate.getDate() + (offset * 7));
            loadData();
        }

        function loadData() {
            document.getElementById('loadingSpinner').style.display = 'block';
            document.getElementById('tkbContent').style.opacity = '0.3';

            const dateStr = currentDate.toISOString().slice(0, 10);
            flatpickrInstance.setDate(dateStr);

            fetch('<?php echo BASE_URL; ?>/giaovien/xemtkballapi', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    ma_hoc_ky: currentHocKy,
                    date: dateStr
                })
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('loadingSpinner').style.display = 'none';
                document.getElementById('tkbContent').style.opacity = '1';

                if (!data.success) {
                    alert('Lỗi: ' + data.message);
                    return;
                }

                updateWeekDisplay(dateStr);
                renderTkbTableAll(data.data, data.tiet_hoc);
            })
            .catch(err => {
                console.error(err);
                document.getElementById('loadingSpinner').style.display = 'none';
            });
        }

        function updateWeekDisplay(dateStr) {
            const curr = new Date(dateStr);
            const first = curr.getDate() - curr.getDay() + 1; // Thứ 2
            const last = first + 6; // CN

            const firstDay = new Date(curr.setDate(first)).toLocaleDateString('vi-VN', {day: '2-digit', month: '2-digit'});
            const lastDay = new Date(curr.setDate(last)).toLocaleDateString('vi-VN', {day: '2-digit', month: '2-digit'});
            
            const weekNumber = getWeekNumber(new Date(dateStr));

            document.getElementById('weekDisplay').innerHTML = `
                <span>Tuần ${weekNumber}</span> 
                <span class="text-muted fw-normal mx-1">|</span> 
                <span class="text-primary">${firstDay} - ${lastDay}</span>
            `;
        }

        function getWeekNumber(d) {
            d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
            d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay()||7));
            var yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
            var weekNo = Math.ceil(( ( (d - yearStart) / 86400000) + 1)/7);
            return weekNo;
        }

        function renderTkbTableAll(tkbData, tietHoc) {
            // Gom dữ liệu
            const tkbGrouped = {};
            tkbData.forEach(item => {
                const key = item.thu + '_' + item.tiet;
                tkbGrouped[key] = item;
            });

            let html = `
                <table class="table-tkb">
                    <thead>
                        <tr>
                            <th>Tiết</th>
                            <th>Thứ 2</th><th>Thứ 3</th><th>Thứ 4</th>
                            <th>Thứ 5</th><th>Thứ 6</th><th>Thứ 7</th><th>Chủ Nhật</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            // ✅ FIX: Chỉ hiển thị 7 tiết (4 Sáng, 3 Chiều) theo đúng logic của trường
            const maxTiet = 7; 
            for (let tiet = 1; tiet <= maxTiet; tiet++) {
                
                // Header phân cách
                if (tiet === 1) html += `<tr class="session-row"><td colspan="8">☀️ SÁNG</td></tr>`;
                if (tiet === 5) html += `<tr class="session-row"><td colspan="8">🌙 CHIỀU</td></tr>`;

                const tietInfo = tietHoc.find(t => t.so_tiet == tiet) || {};
                const timeStart = tietInfo.gio_bat_dau ? tietInfo.gio_bat_dau.substring(0, 5) : '--';
                const timeEnd = tietInfo.gio_ket_thuc ? tietInfo.gio_ket_thuc.substring(0, 5) : '--';

                html += `<tr>
                    <td>
                        <div style="font-size: 1.2rem; font-weight: 800; color: #495057;">${tiet}</div>
                        <div style="font-size: 0.75rem; color: #adb5bd; margin-top: 4px;">${timeStart}<br>${timeEnd}</div>
                    </td>`;

                for (let thu = 2; thu <= 8; thu++) {
                    const key = thu + '_' + tiet;
                    const item = tkbGrouped[key];

                    if (item) {
                        const loai = item.loai_tiet || 'hoc';
                        let cardClass = '';
                        let icon = '';

                        if (loai === 'thi') { cardClass = 'thi'; icon = '<i class="bi bi-pencil-square text-warning"></i>'; }
                        else if (loai === 'tam_nghi') { cardClass = 'nghi'; icon = '<i class="bi bi-x-circle text-danger"></i>'; }
                        else if (loai === 'day_bu') { cardClass = 'bu'; icon = '<i class="bi bi-plus-circle text-success"></i>'; }
                        else if (item.is_changed) { cardClass = 'bu'; icon = '<i class="bi bi-exclamation-circle text-primary"></i>'; }

                        html += `<td>
                            <div class="lesson-card ${cardClass}">
                                <span class="lesson-mon">${item.mon} ${icon}</span>
                                <div class="lesson-info"><i class="bi bi-people-fill"></i> ${item.lop}</div>
                                <div class="lesson-info"><i class="bi bi-geo-alt-fill"></i> ${item.phong || 'N/A'}</div>
                                ${item.ghi_chu ? `<div class="lesson-note">${item.ghi_chu}</div>` : ''}
                            </div>
                        </td>`;
                    } else {
                        // Hardcode Chào cờ & SH lớp cho Thứ 2
                        if (thu === 2 && tiet === 1) html += `<td><div class="lesson-card"><span class="lesson-mon text-center">Chào cờ</span></div></td>`;
                        else if (thu === 2 && tiet === 2) html += `<td><div class="lesson-card"><span class="lesson-mon text-center">SH Lớp</span></div></td>`;
                        else html += `<td></td>`;
                    }
                }
                html += `</tr>`;
            }
            html += `</tbody></table>`;
            document.getElementById('tkbContent').innerHTML = html;
        }
    </script>
</body>
</html>