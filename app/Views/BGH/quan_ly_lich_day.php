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
        body { font-family: 'Segoe UI', Roboto, sans-serif; background-color: #f3f4f6; }
        .sidebar { width: 280px; position: fixed; height: 100vh; background: white; z-index: 1000; box-shadow: 4px 0 15px rgba(0,0,0,0.05); overflow-y: auto; }
        .main-content { margin-left: 280px; padding: 30px; }
        .header-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); margin-bottom: 30px; }
        .header-card h5 { color: #fd7e14; font-weight: bold; }
        .stat-card { background: white; border-radius: 12px; padding: 15px; border-left: 4px solid #fd7e14; margin-bottom: 15px; }
        .stat-card h6 { color: #333; font-weight: 600; margin-bottom: 5px; }
        .stat-card .value { font-size: 1.8rem; font-weight: bold; color: #fd7e14; }
        .table-tkb { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); margin-bottom: 30px; }
        .table-tkb table { margin-bottom: 0; }
        .table-tkb thead th { background: #f8f9fa; color: #333; font-weight: 600; border: none; padding: 12px; text-align: center; }
        .table-tkb tbody td { padding: 12px; border-bottom: 1px solid #e9ecef; text-align: center; vertical-align: middle; height: 70px; }
        .table-tkb tbody tr:hover { background-color: #f8f9fa; }
        .buoi-sang { background: #eef7ff; border-left: 4px solid #2196F3; }
        .buoi-chieu { background: #fff7ed; border-left: 4px solid #ff9800; }
        .mon-cell { font-weight: 700; color: #1f2937; }
        .lop-cell { color: #6b7280; font-size: 0.85rem; }
        .phong-cell { display: inline-block; background: #e8f5e9; padding: 4px 8px; border-radius: 999px; font-size: 0.78rem; color: #0f5132; border: 1px solid #b7e4c7; }
        .gio-cell { color: #4b5563; font-size: 0.85rem; line-height: 1.2; white-space: nowrap; }
        .spinner-border { color: #fd7e14; }
        .nav-link { color: #666; text-decoration: none; padding: 12px 16px; display: flex; align-items: center; gap: 10px; transition: all 0.3s; }
        .nav-link:hover { background: #f3f4f6; color: #fd7e14; }
        .nav-link.active { color: #fd7e14; border-left: 4px solid #fd7e14; background: #fff8f0; font-weight: 600; }
        .sidebar-header { padding: 20px; text-align: center; background: linear-gradient(135deg, #fd7e14 0%, #b35200 100%); color: white; border-radius: 0 0 15px 0; }
        .sidebar-header img { width: 70px; margin-bottom: 10px; border: 3px solid white; border-radius: 50%; }
        .sidebar-header h6 { font-weight: bold; margin-bottom: 5px; }
        .btn-hoc-ky { margin: 5px; }
        @media (max-width: 991px) { 
            .sidebar { transform: translateX(-100%); transition: 0.3s; } 
            .main-content { margin-left: 0; } 
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
            <a class="nav-link" href="<?php echo BASE_URL; ?>/giaovien"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
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
        <div class="stat-card">
            <label class="form-label fw-bold mb-2">Chọn Lớp</label>
            <select class="form-select" id="selectLop" onchange="loadTkbByLop()">
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
        let currentHocKy = '1';  // ✅ Đổi: 'HK1' → '1'

        function changeHocKy(hk) {
            // Map HK1 → '1', HK2 → '2'
            const hkMap = { 'HK1': '1', 'HK2': '2' };
            currentHocKy = hkMap[hk] || '1';
            
            document.getElementById('btnHK1').classList.toggle('btn-warning', hk === 'HK1');
            document.getElementById('btnHK1').classList.toggle('btn-outline-warning', hk !== 'HK1');
            document.getElementById('btnHK2').classList.toggle('btn-warning', hk === 'HK2');
            document.getElementById('btnHK2').classList.toggle('btn-outline-warning', hk !== 'HK2');
            loadTkbByLop();
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

        function renderTkbTable(tkbData, tietHoc, lopInfo, maHocKy) {
            // ✅ FIX: Convert số thành chuỗi (2 → 'Thu2')
            const thuMap = {
                '2': 'Thu2',
                '3': 'Thu3', 
                '4': 'Thu4',
                '5': 'Thu5',
                '6': 'Thu6',
                '7': 'Thu7'
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
                                <th style="width: 80px;">Tiết</th>
                                <th style="width: 80px;">Giờ</th>
                                <th>Thứ 2</th>
                                <th>Thứ 3</th>
                                <th>Thứ 4</th>
                                <th>Thứ 5</th>
                                <th>Thứ 6</th>
                                <th>Thứ 7</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            const maxTiet = 7;
            for (let tiet = 1; tiet <= maxTiet; tiet++) {
                const tietInfo = tietHoc.find(t => t.so_tiet == tiet) || {};
                const buoiClass = tiet <= 4 ? 'buoi-sang' : 'buoi-chieu';
                
                html += `<tr class="${buoiClass}">
                    <td class="fw-bold">${tiet}</td>
                    <td class="gio-cell">
                        <small>${tietInfo.gio_bat_dau || '--'}<br>${tietInfo.gio_ket_thuc || '--'}</small>
                    </td>`;

                const thuArray = ['Thu2', 'Thu3', 'Thu4', 'Thu5', 'Thu6', 'Thu7'];
                thuArray.forEach(thu => {
                    const key = thu + '_' + tiet;
                    const item = tkbGrouped[key];
                    
                    if (item) {
                        html += `<td>
                            <div class="mon-cell">${item.mon}</div>
                            <small class="lop-cell">${item.lop}</small><br>
                            <span class="phong-cell">${item.phong || 'N/A'}</span>
                        </td>`;
                    } else {
                        html += `<td class="text-muted">-</td>`;
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
    </script>

</body>
</html>