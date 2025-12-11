<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Dạy - Giáo Viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { 
        font-family: 'Segoe UI', Roboto, sans-serif; 
        background-color: #f3f4f6; 
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
        border-radius: 15px; 
        padding: 25px; 
        box-shadow: 0 5px 15px rgba(0,0,0,0.03); 
        margin-bottom: 30px; 
    }
    .header-card h5 { 
        color: #fd7e14; 
        font-weight: bold; 
    }
    .stat-card { 
        background: white; 
        border-radius: 12px; 
        padding: 15px; 
        border-left: 4px solid #fd7e14; 
        margin-bottom: 15px; 
    }
    .stat-card h6 { 
        color: #333; 
        font-weight: 600; 
        margin-bottom: 5px; 
    }
    .stat-card .value { 
        font-size: 1.8rem; 
        font-weight: bold; 
        color: #fd7e14; 
    }

    /* ====================== BẢNG THỜI KHÓA BIỂU ====================== */
    .table-tkb { 
        background: white; 
        border-radius: 12px; 
        padding: 20px; 
        box-shadow: 0 5px 15px rgba(0,0,0,0.03); 
        margin-bottom: 30px; 
        overflow: hidden;
    }
    .table-tkb table { 
        margin-bottom: 0; 
        width: 100%;
        table-layout: fixed; /* ✅ Bắt buộc các cột chia đều width */
    }
    .table-tkb thead th { 
        background: #f8f9fa; 
        color: #333; 
        font-weight: 600; 
        border: none; 
        padding: 12px; 
        text-align: center; 
        vertical-align: middle;
    }
    
    /* ✅ Set width cố định cho cột Tiết (đã gộp với Giờ) */
    .table-tkb thead th:nth-child(1),
    .table-tkb tbody td:nth-child(1) {
        width: 80px;
    }
    
    /* ✅ Các cột Thứ 2-CN sẽ tự động chia đều phần còn lại */
    .table-tkb thead th:nth-child(n+2),
    .table-tkb tbody td:nth-child(n+2) {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    /* Dòng phân cách buổi học */
    .buoi-header {
        background: linear-gradient(135deg, #fd7e14 0%, #ff9800 100%);
        color: white;
        font-weight: bold;
        text-align: center;
        padding: 8px;
        font-size: 0.95rem;
    }

    /* Quan trọng: Sửa lỗi lệch lạc, to nhỏ */
    .table-tkb tbody td {
        padding: 10px 8px;
        border-bottom: 1px solid #e9ecef;
        text-align: center;
        vertical-align: middle !important;
        height: auto !important;      /* Bỏ height cố định 70px */
        min-height: 92px;             /* Đủ chỗ cho 3 dòng + phòng */
        position: relative;
        line-height: 1.4;
    }
    .table-tkb tbody tr:hover { 
        background-color: #f8f9fa; 
    }

    /* Nền buổi sáng / chiều */
    .buoi-sang { 
        background: #eef7ff; 
        border-left: 4px solid #2196F3; 
    }
    .buoi-chieu { 
        background: #fff7ed; 
        border-left: 4px solid #ff9800; 
    }

    /* Nội dung trong ô có tiết dạy */
    .mon-cell { 
        font-weight: 700; 
        color: #1f2937; 
        font-size: 0.95rem;
        display: block;
        margin-bottom: 3px;
    }
    .lop-cell { 
        color: #6b7280; 
        font-size: 0.83rem;
        display: block;
        margin-bottom: 5px;
    }
    .phong-cell { 
        display: inline-block; 
            background: #e8f5e9; 
            padding: 4px 9px; 
            border-radius: 999px; 
            font-size: 0.78rem; 
            color: #0f5132; 
            border: 1px solid #b7e4c7;
            font-weight: 500;
        }
        /* CSS cho loại tiết */
        .loai-hoc {
            background-color: #c5f0d6 !important;
            border-left: 4px solid #15803d !important;
        }
        .loai-hoc .mon-cell {
            color: #166534 !important;
        }

        .loai-thi {
            background-color: #ffe0b3 !important;
            border-left: 4px solid #c2410c !important;
        }
        .loai-thi .mon-cell {
            color: #b45309 !important;
        }

        .loai-nghi {
            background-color: #ffc6c6 !important;
            border-left: 4px solid #b91c1c !important;
        }
        .loai-nghi .mon-cell {
            color: #b91c1c !important;
        }

        .loai-nghi .lop-cell {
            color: #b91c1c !important;
        }

        .ghi-chu-note {
            font-size: 0.7rem;
            color: #666;
            margin-top: 3px;
            font-style: italic;
            display: block;
        }
        /* Giờ học trong cột tiết */
        .tiet-number { 
            font-weight: bold;
            font-size: 1.1rem;
            display: block;
            margin-bottom: 3px;
        }
        .gio-cell { 
            color: #6b7280; 
            font-size: 0.75rem; 
            line-height: 1.2; 
            white-space: nowrap; 
        }

        /* Ô trống: căn giữa dấu gạch ngang */
        .table-tkb tbody td:empty::after,
        .table-tkb tbody td .text-muted {
            content: "–";
            color: #adb5bd;
            font-size: 1.4rem;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .table-tkb tbody td .text-muted {
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #adb5bd;
        }

        /* Các phần còn lại giữ nguyên */
        .spinner-border { color: #fd7e14; }
        .nav-link { 
            color: #666; 
            text-decoration: none; 
            padding: 12px 16px; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            transition: all 0.3s; 
        }
        .nav-link:hover { 
            background: #f3f4f6; 
            color: #fd7e14; 
        }
        .nav-link.active { 
            color: #fd7e14; 
            border-left: 4px solid #fd7e14; 
            background: #fff8f0; 
            font-weight: 600; 
        }
        .sidebar-header { 
            padding: 20px; 
            text-align: center; 
            background: linear-gradient(135deg, #fd7e14 0%, #b35200 100%); 
            color: white; 
        }
        .sidebar-header img { 
            width: 70px; 
            margin-bottom: 10px; 
            border: 3px solid white; 
            border-radius: 50%; 
        }
        .sidebar-header h6 { 
            font-weight: bold; 
            margin-bottom: 5px; 
        }
        .btn-hoc-ky { 
            margin: 5px; 
        }

        @media (max-width: 991px) { 
            .sidebar { 
                transform: translateX(-100%); 
                transition: 0.3s; 
            } 
            .main-content { 
                margin-left: 0; 
            } 
        }

        .empty-cell {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.8rem;
            color: #adb5bd;
            font-weight: 300;
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="border border-3 border-white">
            <h6 class="mb-1"><?php echo htmlspecialchars($data['user_name'] ?? 'Giáo Viên'); ?></h6>
            <small>Giáo Viên Bộ Môn</small>
        </div>
        <nav class="nav flex-column mt-3 px-2">
            <a class="nav-link" href="<?php echo BASE_URL; ?>/dashboard"><i class="bi bi-grid-fill me-2"></i>Dashboard</a>
            <a class="nav-link active" href="#"><i class="bi bi-calendar-week"></i> Lịch Dạy</a>
            <a class="nav-link" href="<?php echo BASE_URL; ?>/giaovien/diemdanh"><i class="bi bi-upc-scan"></i> Điểm Danh</a>
            <a class="nav-link" href="<?php echo BASE_URL; ?>/giaovien/baitap"><i class="bi bi-journal-text"></i> Bài Tập</a>
        </nav>
        <div class="mt-auto pt-4 border-top px-2" style="margin-top: 300px;">
            <a class="nav-link text-danger" href="<?php echo BASE_URL; ?>/auth/logout"><i class="bi bi-box-arrow-right"></i> Đăng Xuất</a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        
        <!-- HEADER -->
        <div class="header-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1"><i class="bi bi-calendar-week me-2"></i>Lịch Dạy Của Tôi</h5>
                    <small class="text-muted">Quản lý thời khóa biểu các lớp đang dạy</small>
                </div>
                <button class="btn btn-outline-secondary" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i> Làm Mới</button>
            </div>
        </div>

        <!-- CHỌN LỚP -->
        <div class="stat-card d-flex align-items-center gap-3 flex-wrap">
            <div class="flex-grow-1">
                <label class="form-label fw-bold mb-2">Chọn Lớp</label>
                <select class="form-select" id="selectLop" onchange="onChangeLop()" <?php echo isset($data['lop_list']) && empty($data['lop_list']) ? 'disabled' : ''; ?> disabled>
                    <option value="">-- Chọn lớp để xem --</option>
                    <?php if (!empty($data['lop_list'])): ?>
                        <?php foreach ($data['lop_list'] as $lop): ?>
                            <option value="<?php echo $lop['ma_lop']; ?>">
                                <?php echo htmlspecialchars($lop['ten_lop']) . ' (Sĩ số: ' . $lop['si_so'] . ')'; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option disabled>Không có lớp nào</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="text-end">
                <label class="form-label fw-bold mb-2 d-block">Chế độ</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="toggleAll" onchange="toggleAllMode()" checked>
                    <label class="form-check-label" for="toggleAll">Xem tất cả lớp</label>
                </div>
            </div>
        </div>

        <!-- THỐNG KÊ -->
        <?php if (!empty($data['stats'])): ?>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <h6>Tổng Tiết Dạy</h6>
                    <div class="value"><?php echo $data['stats']['tong_tiet_da_xep'] ?? 0; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h6>Số Lớp</h6>
                    <div class="value"><?php echo $data['stats']['so_lop'] ?? 0; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h6>Số Môn</h6>
                    <div class="value"><?php echo $data['stats']['so_mon'] ?? 0; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h6>Giờ Vào - Tan</h6>
                    <div class="value" style="font-size: 1.3rem;">
                        <?php echo ($data['stats']['gio_vao_som_nhat'] ?? '--') . ' - ' . ($data['stats']['gio_tan_muon_nhat'] ?? '--'); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- CHỌN HỌC KỲ -->
        <div class="stat-card">
            <label class="fw-bold mb-2">Học Kỳ</label>
            <div>
                <button class="btn btn-warning btn-hoc-ky" onclick="changeHocKy('HK1')" id="btnHK1">HK1</button>
                <button class="btn btn-outline-warning btn-hoc-ky" onclick="changeHocKy('HK2')" id="btnHK2">HK2</button>
            </div>
        </div>

        <!-- BẢNG TKB -->
        <div class="table-tkb">
            <div id="loadingSpinner" class="text-center py-5">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
            </div>
            <div id="tkbContent" style="display: none;"></div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentHocKy = '1';
        let viewAll = true;

        function changeHocKy(hk) {
            // Map HK1 → '1', HK2 → '2'
            const hkMap = { 'HK1': '1', 'HK2': '2' };
            currentHocKy = hkMap[hk] || '1';
            
            document.getElementById('btnHK1').classList.toggle('btn-warning', hk === 'HK1');
            document.getElementById('btnHK1').classList.toggle('btn-outline-warning', hk !== 'HK1');
            document.getElementById('btnHK2').classList.toggle('btn-warning', hk === 'HK2');
            document.getElementById('btnHK2').classList.toggle('btn-outline-warning', hk !== 'HK2');
            loadData();
        }

        function toggleAllMode() {
            viewAll = document.getElementById('toggleAll').checked;
            document.getElementById('selectLop').disabled = viewAll;
            loadData();
        }

        function onChangeLop() {
            if (!viewAll) loadData();
        }

        function loadData() {
            if (viewAll) return loadTkbAll();
            return loadTkbByLop();
        }

        function loadTkbByLop() {
            const maLop = document.getElementById('selectLop').value;
            if (!maLop) {
                document.getElementById('tkbContent').style.display = 'none';
                document.getElementById('loadingSpinner').style.display = 'none';
                return;
            }

            document.getElementById('loadingSpinner').style.display = 'block';
            document.getElementById('tkbContent').style.display = 'none';

            fetch('<?php echo BASE_URL; ?>/giaovien/xemtkbbylopapi', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    ma_lop: maLop,
                    ma_hoc_ky: currentHocKy
                })
            })
            .then(res => res.json())
            .then(data => {
                console.log('API Response:', data);
                document.getElementById('loadingSpinner').style.display = 'none';
                
                if (!data.success) {
                    alert('Lỗi: ' + data.message);
                    return;
                }

                renderTkbTable(data.data, data.tiet_hoc, data.lop_info, data.ma_hoc_ky);
                document.getElementById('tkbContent').style.display = 'block';
            })
            .catch(err => {
                document.getElementById('loadingSpinner').style.display = 'none';
                console.error('Lỗi:', err);
                alert('Lỗi kết nối: ' + err.message);
            });
        }

        function loadTkbAll() {
            document.getElementById('loadingSpinner').style.display = 'block';
            document.getElementById('tkbContent').style.display = 'none';

            fetch('<?php echo BASE_URL; ?>/giaovien/xemtkballapi', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    ma_hoc_ky: currentHocKy
                })
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('loadingSpinner').style.display = 'none';
                if (!data.success) {
                    alert('Lỗi: ' + data.message);
                    return;
                }

                renderTkbTableAll(data.data, data.tiet_hoc, data.ma_hoc_ky);
                document.getElementById('tkbContent').style.display = 'block';
            })
            .catch(err => {
                document.getElementById('loadingSpinner').style.display = 'none';
                console.error('Lỗi:', err);
                alert('Lỗi kết nối: ' + err.message);
            });
        }

        function renderTkbTable(tkbData, tietHoc, lopInfo, maHocKy) {
            // ✅ FIX: Convert số thành chuỗi (2 → 'Thu2')
            const thuMap = {
                '2': 'Thu2',
                '3': 'Thu3', 
                '4': 'Thu4',
                '5': 'Thu5',
                '6': 'Thu6',
                '7': 'Thu7',
                '8': 'CN'
            };

            // Nhóm dữ liệu theo (Thứ, Tiết)
            const tkbGrouped = {};
            tkbData.forEach(item => {
                // ✅ Convert: 2 → 'Thu2'
                const thuKey = thuMap[item.thu] || item.thu;
                const key = thuKey + '_' + item.tiet;
                tkbGrouped[key] = item;
            });

            console.log('DEBUG tkbGrouped:', tkbGrouped); // ✅ Debug

            const hkDisplay = maHocKy == '1' ? 'HK1' : 'HK2';

            let html = `
                <h6 class="fw-bold mb-3">Lớp: <span class="text-primary">${lopInfo.ten_lop}</span> - 
                    Học Kỳ: <span class="text-warning">${hkDisplay}</span></h6>
                
                <div style="overflow-x: auto;">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Tiết</th>
                                <th>Thứ 2</th>
                                <th>Thứ 3</th>
                                <th>Thứ 4</th>
                                <th>Thứ 5</th>
                                <th>Thứ 6</th>
                                <th>Thứ 7</th>
                                <th>Chủ Nhật</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            const maxTiet = 7;
            for (let tiet = 1; tiet <= maxTiet; tiet++) {
                // Thêm dòng phân cách buổi
                if (tiet === 1) {
                    html += `<tr><td colspan="8" class="buoi-header">☀️ Buổi Sáng</td></tr>`;
                } else if (tiet === 5) {
                    html += `<tr><td colspan="8" class="buoi-header">🌙 Buổi Chiều</td></tr>`;
                }
                
                const tietInfo = tietHoc.find(t => t.so_tiet == tiet) || {};
                const buoiClass = tiet <= 4 ? 'buoi-sang' : 'buoi-chieu';
                
                html += `<tr class="${buoiClass}">
                    <td>
                        <span class="tiet-number">${tiet}</span>
                        <small class="gio-cell">${tietInfo.gio_bat_dau || '--'}<br>${tietInfo.gio_ket_thuc || '--'}</small>
                    </td>`;

                const thuArray = ['Thu2', 'Thu3', 'Thu4', 'Thu5', 'Thu6', 'Thu7', 'CN'];
                thuArray.forEach(thu => {
                    const key = thu + '_' + tiet;
                    const item = tkbGrouped[key];
                    
                    // ✅ Hardcode Chào cờ & Sinh hoạt cho Thứ 2, tiết 1-2
                    if (thu === 'Thu2' && tiet === 1) {
                        html += `<td>
                            <div class="mon-cell">Chào cờ</div>
                            <small class="lop-cell">${lopInfo.ten_lop}</small><br>
                            <span class="phong-cell">Sân trường</span>
                        </td>`;
                    } else if (thu === 'Thu2' && tiet === 2) {
                        html += `<td>
                            <div class="mon-cell">Sinh hoạt lớp</div>
                            <small class="lop-cell">${lopInfo.ten_lop}</small><br>
                            <span class="phong-cell">Phòng học</span>
                        </td>`;
                    } else if (item) {
                        const loaiTiet = item.loai_tiet || 'hoc';
                        const loaiClass = loaiTiet === 'thi' ? 'loai-thi' : (loaiTiet === 'tam_nghi' ? 'loai-nghi' : 'loai-hoc');
                        html += `<td class="${loaiClass}">
                            <div class="mon-cell">${item.mon}</div>
                            <small class="lop-cell">${item.lop}</small><br>
                            <span class="phong-cell">${item.phong || 'N/A'}</span>
                            ${item.ghi_chu ? `<span class="ghi-chu-note"><i class="bi bi-info-circle"></i> ${item.ghi_chu}</span>` : ''}
                        </td>`;
                    } else {
                        html += `<td><span class="empty-cell">–</span></td>`;
                    }
                });

                html += `</tr>`;
            }

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            document.getElementById('tkbContent').innerHTML = html;
        }

        function renderTkbTableAll(tkbData, tietHoc, maHocKy) {
            const thuMap = {
                '2': 'Thu2',
                '3': 'Thu3', 
                '4': 'Thu4',
                '5': 'Thu5',
                '6': 'Thu6',
                '7': 'Thu7',
                '8': 'CN'
            };

            const tkbGrouped = {};
            tkbData.forEach(item => {
                const thuKey = thuMap[item.thu] || item.thu;
                const key = thuKey + '_' + item.tiet;
                tkbGrouped[key] = item;
            });

            const hkDisplay = maHocKy == '1' ? 'HK1' : 'HK2';

            let html = `
                <h6 class="fw-bold mb-3">Lịch dạy tất cả lớp - Học Kỳ: <span class="text-warning">${hkDisplay}</span></h6>
                <div style="overflow-x: auto;">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Tiết</th>
                                <th>Thứ 2</th>
                                <th>Thứ 3</th>
                                <th>Thứ 4</th>
                                <th>Thứ 5</th>
                                <th>Thứ 6</th>
                                <th>Thứ 7</th>
                                <th>Chủ Nhật</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            const maxTiet = 7;
            for (let tiet = 1; tiet <= maxTiet; tiet++) {
                // Thêm dòng phân cách buổi
                if (tiet === 1) {
                    html += `<tr><td colspan="8" class="buoi-header">☀️ Buổi Sáng</td></tr>`;
                } else if (tiet === 5) {
                    html += `<tr><td colspan="8" class="buoi-header">🌙 Buổi Chiều</td></tr>`;
                }
                
                const tietInfo = tietHoc.find(t => t.so_tiet == tiet) || {};
                const buoiClass = tiet <= 4 ? 'buoi-sang' : 'buoi-chieu';
                
                html += `<tr class="${buoiClass}">
                    <td>
                        <span class="tiet-number">${tiet}</span>
                        <small class="gio-cell">${tietInfo.gio_bat_dau || '--'}<br>${tietInfo.gio_ket_thuc || '--'}</small>
                    </td>`;

                const thuArray = ['Thu2', 'Thu3', 'Thu4', 'Thu5', 'Thu6', 'Thu7', 'CN'];
                thuArray.forEach(thu => {
                    const key = thu + '_' + tiet;
                    const item = tkbGrouped[key];
                    
                    // ✅ Hardcode Chào cờ & Sinh hoạt cho Thứ 2, tiết 1-2 (tất cả lớp)
                    if (thu === 'Thu2' && tiet === 1) {
                        html += `<td>
                            <div class="mon-cell">Chào cờ</div>
                            <small class="lop-cell">Tất cả lớp</small><br>
                            <span class="phong-cell">Sân trường</span>
                        </td>`;
                    } else if (thu === 'Thu2' && tiet === 2) {
                        html += `<td>
                            <div class="mon-cell">Sinh hoạt lớp</div>
                            <small class="lop-cell">Lớp chủ nhiệm</small><br>
                            <span class="phong-cell">Phòng học</span>
                        </td>`;
                    } else if (item) {
                        const loaiTiet = item.loai_tiet || 'hoc';
                        const loaiClass = loaiTiet === 'thi' ? 'loai-thi' : (loaiTiet === 'tam_nghi' ? 'loai-nghi' : 'loai-hoc');
                        html += `<td class="${loaiClass}">
                            <div class="mon-cell">${item.mon}</div>
                            <small class="lop-cell">${item.lop}</small><br>
                            <span class="phong-cell">${item.phong || 'N/A'}</span>
                            ${item.ghi_chu ? `<span class="ghi-chu-note"><i class="bi bi-info-circle"></i> ${item.ghi_chu}</span>` : ''}
                        </td>`;
                    } else {
                        html += `<td><span class="empty-cell">–</span></td>`;
                    }
                });

                html += `</tr>`;
            }

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            document.getElementById('tkbContent').innerHTML = html;
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Mặc định bật chế độ xem tất cả khi vào trang
            document.getElementById('toggleAll').checked = true;
            document.getElementById('selectLop').disabled = true;
            loadData();
        });
    </script>

</body>
</html>