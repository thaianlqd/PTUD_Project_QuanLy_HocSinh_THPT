<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Kết quả Tuyển sinh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .header { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white; padding: 20px 0; margin-bottom: 30px; }
        .card { border: none; box-shadow: 0 0 20px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .card-header { background-color: #0d6efd; color: white; font-weight: bold; border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem; }
        .btn-primary { background-color: #0d6efd; border: none; }
        .btn-primary:hover { background-color: #0a58ca; }
        .function-card { transition: all 0.3s ease; cursor: pointer; height: 100%; border-radius: 0.5rem; }
        .function-card:hover { transform: translateY(-5px); box-shadow: 0 5px 25px rgba(0,0,0,0.15); }
        .function-card i { font-size: 48px; color: #0d6efd; }
        .table-container { max-height: 500px; overflow-y: auto; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
        .status-full { background-color: #d1e7dd; color: #0f5132; } /* Green */
        .status-not-full { background-color: #fff3cd; color: #664d03; } /* Yellow */
        .status-closed { background-color: #f8d7da; color: #842029; } /* Red */
        .loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.7); display: flex; align-items: center; justify-content: center; z-index: 2000; transition: opacity 0.3s; }
        .diem-input.is-invalid { border-color: #dc3545; }
        /* Style cho hàng bị ẩn (nếu cần) */
        /* #locAoTableBody tr.filtered-out { display: none; } */
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingSpinner" style="display: none;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div class="header">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="bi bi-mortarboard-fill"></i> Hệ thống Quản lý Tuyển sinh</h2>
                <p class="mb-0">Quản trị viên Sở Giáo dục và Đào tạo</p>
            </div>
             <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-light btn-sm">
                 <i class="bi bi-arrow-left"></i> Quay lại Dashboard
             </a>
        </div>
    </div>

    <div class="container">
        <div id="globalNotification" class="alert" style="display: none;" role="alert"></div>

        <div id="mainMenu">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card function-card" onclick="showChiTieuForm()">
                        <div class="card-body text-center">
                            <i class="bi bi-clipboard-data"></i>
                            <h5 class="mt-3">Nhập chỉ tiêu tuyển sinh</h5>
                            <p class="text-muted">Quản lý chỉ tiêu tuyển sinh các trường THPT</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card function-card" onclick="showDiemForm()">
                        <div class="card-body text-center">
                            <i class="bi bi-file-earmark-text"></i>
                            <h5 class="mt-3">Nhập điểm tuyển sinh</h5>
                            <p class="text-muted">Nhập điểm thi cho thí sinh</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card function-card" onclick="showLocAoForm()">
                        <div class="card-body text-center">
                            <i class="bi bi-funnel"></i>
                            <h5 class="mt-3">Lọc ảo & Chốt danh sách</h5>
                            <p class="text-muted">Chạy lọc ảo, xác nhận và chốt danh sách nhập học</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="chiTieuForm" style="display: none;">
             <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clipboard-data me-2"></i>Nhập Chỉ Tiêu Tuyển Sinh</span>
                    <button class="btn btn-sm btn-light" onclick="backToMenu()">
                        <i class="bi bi-arrow-left"></i> Quay lại
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã trường</th>
                                    <th>Tên trường</th>
                                    <th>Sĩ số hiện tại (Thực tế)</th>
                                    <th>Chỉ tiêu tuyển sinh</th>
                                </tr>
                            </thead>
                            <tbody id="chiTieuTableBody">
                                </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button class="btn btn-primary" onclick="saveChiTieu()">
                            <i class="bi bi-save"></i> Lưu chỉ tiêu
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="diemForm" style="display: none;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-file-earmark-text me-2"></i>Nhập Điểm Tuyển Sinh</span>
                    <button class="btn btn-sm btn-light" onclick="backToMenu()">
                        <i class="bi bi-arrow-left"></i> Quay lại
                    </button>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Chọn địa điểm thi (Trường THPT - NV1):</label>
                        <select class="form-select" id="diaDiemThi" onchange="loadThiSinh()">
                            <option value="">-- Vui lòng chọn --</option>
                            </select>
                    </div>

                    <div id="diemInputSection" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Nhập điểm thủ công:</h6>
                            <button class="btn btn-success btn-sm" onclick="saveDiem()">
                                <i class="bi bi-save"></i> Lưu điểm nhập tay
                            </button>
                        </div>

                        <div class="table-container">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>SBD</th>
                                        <th>Họ tên</th>
                                        <th>Ngày sinh</th>
                                        <th>Trường THCS</th>
                                        <th>Lớp</th>
                                        <th style="width: 100px;">Toán</th>
                                        <th style="width: 100px;">Ngữ văn</th>
                                        <th style="width: 100px;">Tiếng Anh</th>
                                    </tr>
                                </thead>
                                <tbody id="diemTableBody">
                                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="locAoForm" style="display: none;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                     <span><i class="bi bi-funnel me-2"></i>Lọc Ảo & Chốt Danh Sách Nhập Học</span>
                    <button class="btn btn-sm btn-light" onclick="backToMenu()">
                        <i class="bi bi-arrow-left"></i> Quay lại
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> <strong>Quy trình:</strong>
                        1. Chạy <strong>Lọc Ảo Toàn Cục</strong> (nếu chưa chạy).
                        2. Chọn trường để xem chi tiết.
                        3. Tick <strong>Xác nhận / Từ chối</strong> cho thí sinh và <strong>Lưu xác nhận</strong>.
                        4. Chọn <strong>Lớp đích</strong> và bấm <strong>Chốt Danh Sách</strong> để tạo hồ sơ học sinh mới.
                    </div>

                    <div class="bg-light p-3 rounded mb-4 d-flex flex-wrap align-items-center gap-2">
                        <button class="btn btn-warning me-2" onclick="locAoToanCuc()">
                            <i class="bi bi-filter-circle"></i> (1) Chạy Lọc Ảo Toàn Cục
                        </button>

                        <div class="flex-grow-1">
                            <label class="form-label small fw-bold">(2) Chọn trường xem chi tiết:</label>
                            <select class="form-select" id="selectTruongLocAo" onchange="loadThiSinhTrungTuyen()">
                                <option value="">-- Chọn trường --</option>
                                </select>
                        </div>

                        <div id="chotDanhSachSection" class="w-100 mt-2" style="display: none;">
                            <hr>
                            <div class="d-flex flex-wrap align-items-end gap-2">
                                 <div class="flex-grow-1">
                                     <label class="form-label small fw-bold">(3) Chọn lớp đích (Khối 10) để tạo hồ sơ:</label>
                                    <select class="form-select" id="selectLopDich">
                                        <option value="">-- Vui lòng chọn trường trước --</option>
                                    </select>
                                 </div>
                                 <button class="btn btn-danger" id="btnChotDanhSach" onclick="chotDanhSach()">
                                     <i class="bi bi-check-circle-fill"></i> (4) Chốt Danh Sách Nhập Học
                                 </button>
                            </div>
                        </div>
                    </div>

                    <div id="locAoResult" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                             <h6 class="mb-0">Danh sách thí sinh trúng tuyển:</h6>
                             <div>
                                 <button class="btn btn-outline-primary btn-sm me-2" onclick="filterConfirmedStudents()">
                                     <i class="bi bi-funnel-fill"></i> Lọc Đã Xác Nhận
                                 </button>
                                 <button class="btn btn-outline-secondary btn-sm" onclick="showAllStudents()">
                                     <i class="bi bi-list-ul"></i> Hiện Tất Cả
                                 </button>
                             </div>
                             </div>

                        <div class="table-container mb-4">
                            <table class="table table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>SBD</th>
                                        <th>Họ tên</th>
                                        <th>Ngày sinh</th>
                                        <th>Tổng điểm</th>
                                        <th>Trạng thái hiện tại</th>
                                        <th>Hành động (Lưu trước khi chốt)</th>
                                    </tr>
                                </thead>
                                <tbody id="locAoTableBody">
                                    </tbody>
                            </table>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-primary" onclick="luuXacNhan()">
                                <i class="bi bi-save"></i> (3) Lưu Trạng Thái Xác Nhận
                            </button>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Trạng thái chỉ tiêu các trường (sau lọc):</h6>
                        </div>
                        <div id="trangThaiTruong">
                           <div class="table-container">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tên trường</th>
                                            <th>Chỉ tiêu</th>
                                            <th>Đã trúng tuyển (Tạm thời)</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody id="trangThaiTruongTableBody">
                                        </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 text-muted">
        &copy; <?php echo date("Y"); ?> Hệ thống Quản lý Trường THPT
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- Globals ---
        const BASE_URL = "<?php echo rtrim(BASE_URL, '/'); ?>";
        const loadingSpinner = document.getElementById('loadingSpinner');
        let dsTruongData = [];
        let dsLopData = [];
        // let dsThiSinhData = []; // Không cần cache ở đây nữa vì dùng data attribute

        // --- Helper Functions ---
        function showLoading() { loadingSpinner.style.display = 'flex'; }
        function hideLoading() { loadingSpinner.style.display = 'none'; }
        function showNotification(message, type = 'success') {
             const el = document.getElementById('globalNotification');
             el.textContent = message;
             el.className = `alert alert-${type} alert-dismissible fade show`;
             el.style.display = 'block';
             if (!el.querySelector('.btn-close')) {
                 const closeButton = document.createElement('button');
                 closeButton.type = 'button'; closeButton.className = 'btn-close';
                 closeButton.setAttribute('data-bs-dismiss', 'alert');
                 el.appendChild(closeButton);
             }
             window.scrollTo(0, 0);
             setTimeout(() => {
                 const alertInstance = bootstrap.Alert.getInstance(el);
                 if (alertInstance) alertInstance.close();
             }, 5000);
         }
        async function apiCall(url, options = {}) {
             showLoading();
             try {
                 const response = await fetch(url, options);
                 const responseData = await response.json().catch(() => null);
                 if (!response.ok) {
                     let errorMessage = 'Lỗi không xác định';
                     if (responseData && responseData.message) {
                         errorMessage = responseData.message;
                     } else {
                         errorMessage = `HTTP ${response.status}: ${response.statusText}`;
                     }
                     throw new Error(errorMessage);
                 }
                 if (responseData === null && response.ok) {
                     return { success: true, message: 'Thao tác thành công nhưng không có dữ liệu trả về.' };
                 }
                 return responseData;
             } catch (error) {
                 console.error("Lỗi API:", error);
                 showNotification(`Lỗi: ${error.message}`, 'danger');
                 throw error;
             } finally {
                 hideLoading();
             }
         }
         function escapeHtml(unsafe) {
              if (unsafe === null || typeof unsafe === 'undefined') return '';
              if (typeof unsafe !== 'string') unsafe = String(unsafe);
              return unsafe.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
         }


        // --- Navigation Functions ---
        async function showChiTieuForm() {
             document.getElementById('mainMenu').style.display = 'none';
             document.getElementById('chiTieuForm').style.display = 'block';
             try {
                 const result = await apiCall(`${BASE_URL}/quantri/getDsTruongApi`);
                 dsTruongData = result.data;
                 const tableBody = document.getElementById('chiTieuTableBody');
                 tableBody.innerHTML = '';
                 result.data.forEach(truong => {
                     tableBody.innerHTML += `
                         <tr data-ma-truong="${truong.ma_truong}">
                             <td>${truong.ma_truong}</td>
                             <td>${escapeHtml(truong.ten_truong)}</td>
                             <td>${truong.so_luong_hoc_sinh || 0}</td>
                             <td><input type="number" class="form-control form-control-sm" value="${truong.chi_tieu_hoc_sinh || 0}" min="0" max="1000" data-ma-truong="${truong.ma_truong}"></td>
                         </tr>`;
                 });
             } catch (e) { /* Lỗi đã xử lý */ }
         }
        async function showDiemForm() {
             document.getElementById('mainMenu').style.display = 'none';
             document.getElementById('diemForm').style.display = 'block';
             document.getElementById('diemInputSection').style.display = 'none';
             if (dsTruongData.length === 0) {
                 try {
                     const result = await apiCall(`${BASE_URL}/quantri/getDsTruongApi`);
                     dsTruongData = result.data;
                 } catch (e) { /* Lỗi đã xử lý */ }
             }
             const select = document.getElementById('diaDiemThi');
             select.innerHTML = '<option value="">-- Vui lòng chọn --</option>';
             dsTruongData.forEach(truong => {
                 select.innerHTML += `<option value="${truong.ma_truong}">${escapeHtml(truong.ten_truong)}</option>`;
             });
         }
        async function showLocAoForm() {
            document.getElementById('mainMenu').style.display = 'none';
            document.getElementById('locAoForm').style.display = 'block';
            document.getElementById('locAoResult').style.display = 'none';
            document.getElementById('trangThaiTruong').style.display = 'none';
            document.getElementById('chotDanhSachSection').style.display = 'none';

            // Load dropdown trường (nếu chưa có)
            if (dsTruongData.length === 0) {
                try {
                    const resultTruong = await apiCall(`${BASE_URL}/quantri/getDsTruongApi`);
                    dsTruongData = resultTruong.data;
                    const selectTruong = document.getElementById('selectTruongLocAo');
                    selectTruong.innerHTML = '<option value="">-- Chọn trường để lọc ảo chi tiết --</option>';
                    dsTruongData.forEach(truong => {
                        selectTruong.innerHTML += `<option value="${truong.ma_truong}">${escapeHtml(truong.ten_truong)}</option>`;
                    });
                } catch (e) { /* Lỗi đã xử lý */ }
            } else {
                const selectTruong = document.getElementById('selectTruongLocAo');
                selectTruong.innerHTML = '<option value="">-- Chọn trường để lọc ảo chi tiết --</option>';
                dsTruongData.forEach(truong => {
                    selectTruong.innerHTML += `<option value="${truong.ma_truong}">${escapeHtml(truong.ten_truong)}</option>`;
                });
            }

            // Load danh sách LỚP (nếu chưa có) - SỬA LỖI THIẾU AWAIT
            if (dsLopData.length === 0) {
                 try {
                    const resultLop = await apiCall(`${BASE_URL}/quantri/getDsLopApi`); // <-- ĐÃ THÊM AWAIT
                    dsLopData = resultLop.data; // Cache lại
                } catch (e) { /* Lỗi đã xử lý */ }
            }
        }
        function backToMenu() {
            document.getElementById('mainMenu').style.display = 'block';
            document.getElementById('chiTieuForm').style.display = 'none';
            document.getElementById('diemForm').style.display = 'none';
            document.getElementById('locAoForm').style.display = 'none';
        }


        // --- CHỨC NĂNG 1: CHỈ TIÊU ---
        async function saveChiTieu() {
            const rows = document.querySelectorAll('#chiTieuTableBody tr');
            let payload = {}; let valid = true;
            rows.forEach(row => {
                const ma_truong = row.dataset.maTruong; const input = row.querySelector('input[type="number"]'); const value = parseInt(input.value);
                if (isNaN(value) || value < 0) { valid = false; input.classList.add('is-invalid'); }
                else { input.classList.remove('is-invalid'); payload[ma_truong] = value; }
            });
            if (!valid) { showNotification('Chỉ tiêu không hợp lệ! (Phải là số >= 0).', 'danger'); return; }
            try {
                const result = await apiCall(`${BASE_URL}/quantri/updateChiTieuApi`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                showNotification(result.message, 'success');
                showChiTieuForm();
            } catch (e) { /* Lỗi đã xử lý */ }
        }


        // --- CHỨC NĂNG 2: NHẬP ĐIỂM ---
        async function loadThiSinh() {
            const ma_truong = document.getElementById('diaDiemThi').value;
            const diemSection = document.getElementById('diemInputSection'); const tableBody = document.getElementById('diemTableBody');
            if (!ma_truong) { diemSection.style.display = 'none'; return; }
            diemSection.style.display = 'block';
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Đang tải danh sách thí sinh...</td></tr>';
            try {
                const result = await apiCall(`${BASE_URL}/quantri/getDsThiSinhApi/${ma_truong}`);
                dsThiSinhData = result.data; tableBody.innerHTML = '';
                if (dsThiSinhData.length === 0) { tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Không có thí sinh nào đăng ký NV1 tại trường này.</td></tr>'; return; }
                dsThiSinhData.forEach(ts => {
                    let ngaySinhDisplay = 'N/A';
                    if (ts.ngay_sinh) { try { const [y, m, d] = ts.ngay_sinh.split('-'); ngaySinhDisplay = `${d}/${m}/${y}`; } catch (e) { ngaySinhDisplay = ts.ngay_sinh; } }
                    tableBody.innerHTML += `
                        <tr data-ma-nguoi-dung="${ts.ma_nguoi_dung}">
                            <td>${escapeHtml(ts.so_bao_danh)}</td><td>${escapeHtml(ts.ho_ten)}</td><td>${ngaySinhDisplay}</td><td>${escapeHtml(ts.truong_thcs)}</td><td>${escapeHtml(ts.lop_hoc)}</td>
                            <td><input type="number" class="form-control form-control-sm diem-input" value="${ts.diem_toan || ''}" min="0" max="10" step="0.25"></td>
                            <td><input type="number" class="form-control form-control-sm diem-input" value="${ts.diem_van || ''}" min="0" max="10" step="0.25"></td>
                            <td><input type="number" class="form-control form-control-sm diem-input" value="${ts.diem_anh || ''}" min="0" max="10" step="0.25"></td>
                        </tr>`;
                });
            } catch (e) { /* Lỗi đã xử lý */ }
        }
        async function saveDiem() {
            const rows = document.querySelectorAll('#diemTableBody tr[data-ma-nguoi-dung]');
            let payload = []; let valid = true;
            rows.forEach(row => {
                const ma_nguoi_dung = parseInt(row.dataset.maNguoiDung); const inputs = row.querySelectorAll('input.diem-input');
                const diem_toan = inputs[0].value === '' ? null : parseFloat(inputs[0].value); const diem_van = inputs[1].value === '' ? null : parseFloat(inputs[1].value); const diem_anh = inputs[2].value === '' ? null : parseFloat(inputs[2].value);
                if (diem_toan !== null || diem_van !== null || diem_anh !== null) {
                    if ((diem_toan !== null && (diem_toan < 0 || diem_toan > 10)) || (diem_van !== null && (diem_van < 0 || diem_van > 10)) || (diem_anh !== null && (diem_anh < 0 || diem_anh > 10))) { valid = false; inputs.forEach(inp => inp.classList.add('is-invalid')); }
                    else { inputs.forEach(inp => inp.classList.remove('is-invalid')); payload.push({ ma_nguoi_dung: ma_nguoi_dung, diem_toan: diem_toan, diem_van: diem_van, diem_anh: diem_anh }); }
                }
            });
            if (!valid) { showNotification('Điểm nhập không hợp lệ! (Phải từ 0-10).', 'danger'); return; }
            if (payload.length === 0) { showNotification('Không có điểm mới nào để lưu.', 'warning'); return; }
            try {
                const result = await apiCall(`${BASE_URL}/quantri/updateDiemApi`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                showNotification(result.message, 'success');
                loadThiSinh();
            } catch (e) { /* Lỗi đã xử lý */ }
        }

        // --- CHỨC NĂNG 3: LỌC ẢO ---
        async function locAoToanCuc() {
            if (!confirm('Chạy lọc ảo sẽ XÓA KẾT QUẢ CŨ và tạo kết quả mới. Bạn chắc chắn?')) return;
            document.getElementById('locAoResult').style.display = 'none'; document.getElementById('chotDanhSachSection').style.display = 'none'; document.getElementById('selectTruongLocAo').value = '';
            try {
                const resultRun = await apiCall(`${BASE_URL}/quantri/runLocAoApi`, { method: 'POST' });
                showNotification(resultRun.message, 'success');
                const resultKetQua = await apiCall(`${BASE_URL}/quantri/getKetQuaLocApi`);
                const tableBody = document.getElementById('locAoTableBody'); tableBody.innerHTML = '';
                if (resultKetQua.thi_sinh && resultKetQua.thi_sinh.length > 0) {
                    resultKetQua.thi_sinh.forEach(ts => { const statusClass = ts.trang_thai === 'Dau' ? 'bg-success' : 'bg-danger'; tableBody.innerHTML += `<tr><td>${escapeHtml(ts.so_bao_danh)}</td><td>${escapeHtml(ts.ho_ten)}</td><td>${escapeHtml(ts.truong_trung_tuyen)}</td><td>${ts.tong_diem ? ts.tong_diem.toFixed(2) : 'N/A'}</td><td><span class="badge ${statusClass} text-white">${ts.trang_thai}</span></td><td><span class="badge bg-secondary">${ts.trang_thai_xac_nhan.replace('_', ' ')}</span></td></tr>`; });
                } else { tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Không có kết quả lọc.</td></tr>'; }
                const truongTableBody = document.getElementById('trangThaiTruongTableBody'); truongTableBody.innerHTML = '';
                resultKetQua.truong.forEach(tr => { const chi_tieu = parseInt(tr.chi_tieu_hoc_sinh) || 0; const da_trung = parseInt(tr.so_luong_hoc_sinh) || 0; let statusClass = 'status-not-full'; let statusText = 'Chưa đủ chỉ tiêu'; if (chi_tieu === 0) { statusClass = 'bg-secondary text-white'; statusText = 'Chưa đặt CT'; } else if (da_trung >= chi_tieu) { statusClass = 'status-full'; statusText = 'Đủ chỉ tiêu'; } truongTableBody.innerHTML += `<tr><td>${escapeHtml(tr.ten_truong)}</td><td>${chi_tieu}</td><td>${da_trung}</td><td><span class="status-badge ${statusClass}">${statusText}</span></td></tr>`; });
                document.getElementById('locAoResult').style.display = 'block'; document.getElementById('trangThaiTruong').style.display = 'block';
            } catch (e) { /* Lỗi đã xử lý */ }
        }

        async function loadThiSinhTrungTuyen() {
            console.log("Đã gọi loadThiSinhTrungTuyen!"); // Debug log 1

            const ma_truong = document.getElementById('selectTruongLocAo').value;
            const chotSection = document.getElementById('chotDanhSachSection');
            const selectLopDich = document.getElementById('selectLopDich');
            const truongTableBody = document.getElementById('trangThaiTruongTableBody');
            const tableBody = document.getElementById('locAoTableBody'); // Lấy table body trước

            tableBody.innerHTML = ''; // Xóa bảng thí sinh trước
            truongTableBody.innerHTML = '';
            document.getElementById('trangThaiTruong').style.display = 'none';

            if (!ma_truong) {
                document.getElementById('locAoResult').style.display = 'none';
                chotSection.style.display = 'none';
                return;
            }

            // Lọc danh sách lớp (từ cache)
            const lopCuaTruong = dsLopData.filter(lop => lop.ma_truong == ma_truong && lop.khoi == 10);
            selectLopDich.innerHTML = '';
            if (lopCuaTruong.length > 0) {
                selectLopDich.innerHTML = '<option value="">-- Chọn lớp đích --</option>';
                lopCuaTruong.forEach(lop => {
                    selectLopDich.innerHTML += `<option value="${lop.ma_lop}">${escapeHtml(lop.ten_lop)}</option>`;
                });
            } else {
                selectLopDich.innerHTML = '<option value="">-- Trường này chưa có lớp khối 10 --</option>';
            }
            chotSection.style.display = 'block';

            // Load danh sách thí sinh
            try {
                console.log("Đang gọi API: ", `${BASE_URL}/quantri/getThiSinhTrungTuyenTheoTruongApi/${ma_truong}`); // Debug log 2
                const result = await apiCall(`${BASE_URL}/quantri/getThiSinhTrungTuyenTheoTruongApi/${ma_truong}`);
                console.log("API Result Data:", result.data); // Debug log 3

                if (!result.data || result.data.length === 0) {
                     tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Không có thí sinh trúng tuyển.</td></tr>'; // Sửa lại message
                } else {
                    result.data.forEach((ts, index) => {
                        try {
                            const sbd = ts.so_bao_danh || 'N/A';
                            const hoTen = ts.ho_ten || 'N/A';
                            const maKetQua = ts.ma_ket_qua_tuyen_sinh || '';

                            let ngaySinhDisplay = 'N/A';
                            if (ts.ngay_sinh) {
                                try {
                                    ngaySinhDisplay = new Date(ts.ngay_sinh).toLocaleDateString('vi-VN');
                                    if (ngaySinhDisplay === "Invalid Date") { ngaySinhDisplay = ts.ngay_sinh; }
                                } catch (dateError) { console.error(`Lỗi định dạng ngày sinh cho SBD ${sbd}:`, dateError); ngaySinhDisplay = ts.ngay_sinh; }
                            }

                            const tongDiemDisplay = (ts.tong_diem !== null && !isNaN(ts.tong_diem)) ? parseFloat(ts.tong_diem).toFixed(2) : 'N/A';

                            let trangThaiHienTai = 'CHỜ XÁC NHẬN';
                            let badgeClass = 'bg-secondary';
                            const trangThaiXN = ts.trang_thai_xac_nhan || 'Cho_xac_nhan';
                            if(trangThaiXN === 'Xac_nhan_nhap_hoc') { trangThaiHienTai = 'ĐÃ XÁC NHẬN'; badgeClass = 'bg-success'; }
                            else if (trangThaiXN === 'Tu_choi_nhap_hoc') { trangThaiHienTai = 'ĐÃ TỪ CHỐI'; badgeClass = 'bg-danger'; }

                            const checkedXacNhan = trangThaiXN === 'Xac_nhan_nhap_hoc' ? 'checked' : '';
                            const checkedTuChoi = trangThaiXN === 'Tu_choi_nhap_hoc' ? 'checked' : '';

                            // Tạo HTML cho dòng - ##### THÊM data-status #####
                            tableBody.innerHTML += `
                                <tr data-ma-ket-qua="${maKetQua}" data-status="${trangThaiXN}">
                                    <td>${escapeHtml(sbd)}</td>
                                    <td>${escapeHtml(hoTen)}</td>
                                    <td>${ngaySinhDisplay}</td>
                                    <td>${tongDiemDisplay}</td>
                                    <td><span class="badge ${badgeClass}">${trangThaiHienTai}</span></td>
                                    <td>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input xac-nhan-checkbox" type="radio" name="trang_thai_${sbd}" value="Xac_nhan_nhap_hoc" ${checkedXacNhan}>
                                            <label class="form-check-label text-success">Xác nhận</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input tu-choi-checkbox" type="radio" name="trang_thai_${sbd}" value="Tu_choi_nhap_hoc" ${checkedTuChoi}>
                                            <label class="form-check-label text-danger">Từ chối</label>
                                        </div>
                                    </td>
                                </tr>`;

                        } catch(loopError) {
                            console.error(`Lỗi xử lý dữ liệu thí sinh thứ ${index} (SBD: ${ts.so_bao_danh}):`, loopError);
                            tableBody.innerHTML += `<tr><td colspan="6" class="text-center text-danger">Lỗi hiển thị dữ liệu cho SBD ${escapeHtml(ts.so_bao_danh)}</td></tr>`;
                        }
                    });
                }
                document.getElementById('locAoResult').style.display = 'block';

            } catch (e) {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Có lỗi xảy ra khi tải danh sách thí sinh. Kiểm tra Console (F12).</td></tr>';
                document.getElementById('locAoResult').style.display = 'block';
                console.error("Lỗi trong loadThiSinhTrungTuyen (catch ngoài):", e);
            }
        }

        async function luuXacNhan() {
            const rows = document.querySelectorAll('#locAoTableBody tr[data-ma-ket-qua]');
            let payload = { danh_sach_xac_nhan: [], ma_truong: document.getElementById('selectTruongLocAo').value }; let hasChanges = false;
            rows.forEach(row => {
                const ma_ket_qua = row.dataset.maKetQua; const xacNhanInput = row.querySelector('.xac-nhan-checkbox:checked'); const tuChoiInput = row.querySelector('.tu-choi-checkbox:checked');
                if (xacNhanInput || tuChoiInput) { payload.danh_sach_xac_nhan.push({ ma_ket_qua: ma_ket_qua, trang_thai: xacNhanInput ? 'Xac_nhan_nhap_hoc' : 'Tu_choi_nhap_hoc' }); hasChanges = true; }
            });
            if (!hasChanges) { showNotification('Không có thay đổi nào để lưu.', 'warning'); return; }
            try {
                const result = await apiCall(`${BASE_URL}/quantri/capNhatXacNhanApi`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                showNotification(result.message, 'success');
                loadThiSinhTrungTuyen();
            } catch (e) { /* Lỗi đã xử lý */ }
        }
        async function chotDanhSach() {
            const ma_truong = document.getElementById('selectTruongLocAo').value; const ma_lop_dich = document.getElementById('selectLopDich').value;
            if (!ma_truong || !ma_lop_dich) { showNotification('Vui lòng chọn trường VÀ lớp đích trước khi chốt.', 'warning'); return; }
            if (!confirm(`Bạn chắc chắn muốn CHỐT danh sách và tạo hồ sơ học sinh mới cho những em đã xác nhận nhập học vào lớp đã chọn?`)) { return; }
            try {
                const result = await apiCall(`${BASE_URL}/quantri/chotNhapHocApi`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ ma_truong: ma_truong, ma_lop_dich: ma_lop_dich }) });
                showNotification(result.message, 'success');
                loadThiSinhTrungTuyen();
                apiCall(`${BASE_URL}/quantri/getDsTruongApi`).then(res => { dsTruongData = res.data; });
            } catch (e) { /* Lỗi đã xử lý */ }
        }

        // ##### HÀM LỌC MỚI #####
        function filterConfirmedStudents() {
            const rows = document.querySelectorAll('#locAoTableBody tr[data-ma-ket-qua]');
            let count = 0;
            rows.forEach(row => {
                // Kiểm tra xem hàng có data-status không trước khi truy cập
                if (row.dataset && row.dataset.status === 'Xac_nhan_nhap_hoc') {
                    row.style.display = ''; // Hiện
                    count++;
                } else if (row.dataset && row.dataset.status) { // Chỉ ẩn nếu có data-status
                    row.style.display = 'none'; // Ẩn
                }
                // Bỏ qua các hàng không có data-ma-ket-qua (ví dụ hàng báo lỗi/trống)
            });
             if (count === 0 && rows.length > 0 && rows[0].querySelectorAll('td').length > 1) { // Kiểm tra thêm để chắc chắn có hàng dữ liệu
                 showNotification('Không có thí sinh nào đã xác nhận nhập học để hiển thị.', 'info');
             }
        }

        function showAllStudents() {
            const rows = document.querySelectorAll('#locAoTableBody tr[data-ma-ket-qua]');
            rows.forEach(row => {
                row.style.display = ''; // Hiện tất cả
            });
        }
        // ##### HẾT HÀM LỌC MỚI #####

    </script>
</body>
</html>