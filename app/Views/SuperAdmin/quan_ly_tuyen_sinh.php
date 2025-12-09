<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tuyển Sinh | Sở GD</title>
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
        .status-full { background-color: #d1e7dd; color: #0f5132; }
        .status-not-full { background-color: #fff3cd; color: #664d03; }
        .status-closed { background-color: #f8d7da; color: #842029; }
        .loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.7); display: flex; align-items: center; justify-content: center; z-index: 2000; transition: opacity 0.3s; }
        .diem-input.is-invalid { border-color: #dc3545; }
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
                            <h5 class="mt-3">Lọc ảo & Xem danh sách</h5>
                            <p class="text-muted">Chạy lọc ảo và xem danh sách học sinh đậu</p>
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
                            <tbody id="chiTieuTableBody"></tbody>
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
                        <label class="form-label fw-bold">Chọn Trường THCS:</label>
                        <select class="form-select" id="truongThcs" onchange="loadThiSinh()">
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
                                        <th>Lớp</th>
                                        <th>NV1 (Trường)</th>
                                        <th>NV2 (Trường)</th>
                                        <th>NV3 (Trường)</th>
                                        <th style="width: 100px;">Toán</th>
                                        <th style="width: 100px;">Ngữ văn</th>
                                        <th style="width: 100px;">Tiếng Anh</th>
                                    </tr>
                                </thead>
                                <tbody id="diemTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="locAoForm" style="display: none;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                     <span><i class="bi bi-funnel me-2"></i>Lọc Ảo & Xem Danh Sách Trúng Tuyển</span>
                    <button class="btn btn-sm btn-light" onclick="backToMenu()">
                        <i class="bi bi-arrow-left"></i> Quay lại
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> <strong>Quy trình:</strong>
                        1. Bấm <strong>Chạy Lọc Ảo Toàn Cục</strong> để reset & tính lại kết quả.
                        2. Chọn trường để xem chi tiết những học sinh đậu vào trường đó.
                        3. Dùng nút Lọc để xem học sinh chưa xác nhận / đã xác nhận / từ chối.
                    </div>

                    <div class="bg-light p-3 rounded mb-4">
                        <button class="btn btn-warning" onclick="locAoToanCuc()">
                            <i class="bi bi-filter-circle"></i> Chạy Lọc Ảo Toàn Cục
                        </button>
                    </div>

                    <div id="locAoResult" style="display: none;">
                        <!-- Menu tab xem kết quả -->
                        <div class="mb-4">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tabDau-tab" data-bs-toggle="tab" data-bs-target="#tabDau" type="button" role="tab" aria-controls="tabDau" aria-selected="true">
                                        <i class="bi bi-check-circle me-2"></i>Danh sách Đậu
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tabTruot-tab" data-bs-toggle="tab" data-bs-target="#tabTruot" type="button" role="tab" aria-controls="tabTruot" aria-selected="false">
                                        <i class="bi bi-x-circle me-2"></i>Danh sách Trượt & Điểm Chuẩn
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content mt-3">
                                <!-- Tab Danh sách Đậu -->
                                <div class="tab-pane fade show active" id="tabDau" role="tabpanel" aria-labelledby="tabDau-tab">
                                    <!-- Phần chọn trường -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Chọn trường để xem chi tiết:</label>
                                        <select class="form-select" id="selectTruongLocAo" onchange="loadThiSinhTrungTuyen()">
                                            <option value="">-- Chọn trường --</option>
                                        </select>
                                    </div>

                                    <!-- Phần danh sách thí sinh đậu -->
                                    <div id="thiSinhDauSection" style="display: none;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Danh sách học sinh đậu vào trường:</h6>
                                            <div>
                                                <button class="btn btn-outline-warning btn-sm me-2" onclick="filterPendingStudents()">
                                                    <i class="bi bi-hourglass-split"></i> Chưa Xác Nhận
                                                </button>
                                                <button class="btn btn-outline-success btn-sm me-2" onclick="filterConfirmedStudents()">
                                                    <i class="bi bi-check-circle-fill"></i> Đã Xác Nhận
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm me-2" onclick="filterRejectedStudents()">
                                                    <i class="bi bi-x-circle-fill"></i> Từ Chối
                                                </button>
                                                <button class="btn btn-outline-secondary btn-sm" onclick="showAllStudents()">
                                                    <i class="bi bi-list-ul"></i> Tất Cả
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
                                                        <th>Trạng thái xác nhận</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="locAoTableBody"></tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Trạng thái chỉ tiêu -->
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
                                                        <th>Đã trúng tuyển</th>
                                                        <th>Trạng thái</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="trangThaiTruongTableBody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab Danh sách Trượt & Điểm Chuẩn -->
                                <div class="tab-pane fade" id="tabTruot" role="tabpanel" aria-labelledby="tabTruot-tab">
                                    <!-- Phần điểm chuẩn các trường -->
                                    <div class="mb-4">
                                        <h6 class="mb-3 fw-bold">Điểm Chuẩn Tuyển Sinh Các Trường:</h6>
                                        <div class="table-container">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Tên Trường</th>
                                                        <th>Chỉ Tiêu</th>
                                                        <th>Đã Trúng Tuyển</th>
                                                        <th>Điểm Chuẩn</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="diemChuanTableBody"></tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <hr>

                                    <!-- Phần danh sách thí sinh trượt -->
                                    <div>
                                        <h6 class="mb-3 fw-bold">Danh Sách Thí Sinh Trượt:</h6>
                                        <div class="table-container">
                                            <table class="table table-striped">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>SBD</th>
                                                        <th>Họ Tên</th>
                                                        <th>Tổng Điểm</th>
                                                        <th>NV1 (Trường)</th>
                                                        <th>NV2 (Trường)</th>
                                                        <th>NV3 (Trường)</th>
                                                        <th>Lý Do Trượt</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="thiSinhTruotTableBody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
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
        // SỬA URL ĐỂ GỌI ĐÚNG CONTROLLER
        const BASE_URL = "<?php echo rtrim(BASE_URL, '/'); ?>";
        const API_URL = BASE_URL + '/quanlytuyensinh'; 
        
        const loadingSpinner = document.getElementById('loadingSpinner');
        let dsTruongData = [];
        let dsLopData = [];

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
                     if (responseData && responseData.message) errorMessage = responseData.message;
                     else errorMessage = `HTTP ${response.status}: ${response.statusText}`;
                     throw new Error(errorMessage);
                 }
                 if (responseData === null && response.ok) return { success: true, message: 'Thành công nhưng không có dữ liệu.' };
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

        // --- Navigation ---
        async function showChiTieuForm() {
             document.getElementById('mainMenu').style.display = 'none';
             document.getElementById('chiTieuForm').style.display = 'block';
             try {
                 const result = await apiCall(`${API_URL}/getDsTruongApi`);
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
             } catch (e) {}
         }
        async function showDiemForm() {
             document.getElementById('mainMenu').style.display = 'none';
             document.getElementById('diemForm').style.display = 'block';
             document.getElementById('diemInputSection').style.display = 'none';
             try {
                 const result = await apiCall(`${API_URL}/getDsTruongThcsApi`);
                 const select = document.getElementById('truongThcs');
                 select.innerHTML = '<option value="">-- Vui lòng chọn --</option>';
                 result.data.forEach(truong => {
                     select.innerHTML += `<option value="${truong.truong_thcs}">${escapeHtml(truong.truong_thcs)}</option>`;
                 });
             } catch (e) {}
         }
        async function showLocAoForm() {
            document.getElementById('mainMenu').style.display = 'none';
            document.getElementById('locAoForm').style.display = 'block';
            document.getElementById('locAoResult').style.display = 'none';
            document.getElementById('thiSinhDauSection').style.display = 'none';

            if (dsTruongData.length === 0) {
                try {
                    const resultTruong = await apiCall(`${API_URL}/getDsTruongApi`);
                    dsTruongData = resultTruong.data;
                } catch (e) {}
            }
            const selectTruong = document.getElementById('selectTruongLocAo');
            selectTruong.innerHTML = '<option value="">-- Chọn trường --</option>';
            dsTruongData.forEach(truong => {
                selectTruong.innerHTML += `<option value="${truong.ma_truong}">${escapeHtml(truong.ten_truong)}</option>`;
            });
        }
        function backToMenu() {
            document.getElementById('mainMenu').style.display = 'block';
            document.getElementById('chiTieuForm').style.display = 'none';
            document.getElementById('diemForm').style.display = 'none';
            document.getElementById('locAoForm').style.display = 'none';
        }

        // --- Logic ---
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
                const result = await apiCall(`${API_URL}/updateChiTieuApi`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                showNotification(result.message, 'success');
                showChiTieuForm();
            } catch (e) {}
        }

        async function loadThiSinh() {
            const truong_thcs = document.getElementById('truongThcs').value;
            const diemSection = document.getElementById('diemInputSection'); 
            const tableBody = document.getElementById('diemTableBody');
            
            if (!truong_thcs) { 
                diemSection.style.display = 'none'; 
                return; 
            }
            
            diemSection.style.display = 'block';
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Đang tải danh sách thí sinh...</td></tr>';
            
            try {
                // ✅ FIX: Gọi POST thay vì GET để tránh URL encode
                const result = await apiCall(`${API_URL}/getDsThiSinhByTruongThcsApi`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ truong_thcs: truong_thcs })
                });
                
                const dsThiSinhData = result.data; 
                tableBody.innerHTML = '';
                
                if (dsThiSinhData.length === 0) { 
                    tableBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Không có thí sinh nào từ trường này.</td></tr>'; 
                    return; 
                }
                
                dsThiSinhData.forEach(ts => {
                    let ngaySinhDisplay = 'N/A';
                    if (ts.ngay_sinh) { 
                        try { 
                            ngaySinhDisplay = new Date(ts.ngay_sinh).toLocaleDateString('vi-VN'); 
                        } catch (e) { 
                            ngaySinhDisplay = ts.ngay_sinh; 
                        } 
                    }
                    tableBody.innerHTML += `
                        <tr data-ma-nguoi-dung="${ts.ma_nguoi_dung}">
                        <td>${escapeHtml(ts.so_bao_danh)}</td>
                        <td>${escapeHtml(ts.ho_ten)}</td>
                        <td>${ngaySinhDisplay}</td>
                        <td>${escapeHtml(ts.lop_hoc || '')}</td>
                        <td><strong>${escapeHtml(ts.ten_truong_nv1 || 'Chưa đăng ký')}</strong></td>
                        <td>${escapeHtml(ts.ten_truong_nv2 || '—')}</td>
                        <td>${escapeHtml(ts.ten_truong_nv3 || '—')}</td>
                        <td><input type="number" class="form-control form-control-sm diem-input" value="${ts.diem_toan || ''}" min="0" max="10" step="0.25"></td>
                        <td><input type="number" class="form-control form-control-sm diem-input" value="${ts.diem_van || ''}" min="0" max="10" step="0.25"></td>
                        <td><input type="number" class="form-control form-control-sm diem-input" value="${ts.diem_anh || ''}" min="0" max="10" step="0.25"></td>
                    </tr>`;
                });
            } catch (e) {}
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
                const result = await apiCall(`${API_URL}/updateDiemApi`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                showNotification(result.message, 'success');
                loadThiSinh();
            } catch (e) {}
        }

        async function locAoToanCuc() {
            if (!confirm('Chạy lọc ảo sẽ XÓA KẾT QUẢ CŨ và tạo kết quả mới. Bạn chắc chắn?')) return;
            
            document.getElementById('locAoResult').style.display = 'none';
            document.getElementById('thiSinhDauSection').style.display = 'none';
            document.getElementById('selectTruongLocAo').value = '';
            
            try {
                const resultRun = await apiCall(`${API_URL}/runLocAoApi`, { method: 'POST' });
                showNotification(resultRun.message, 'success');
                
                const resultKetQua = await apiCall(`${API_URL}/getKetQuaLocApi`);
                
                // Lưu dữ liệu toàn cục để dùng trong tab trượt
                window.allThiSinhData = resultKetQua.thi_sinh || [];
                window.allTruongData = resultKetQua.truong || [];
                
                // Hiển thị trạng thái chỉ tiêu (Tab Đậu)
                const truongTableBody = document.getElementById('trangThaiTruongTableBody');
                truongTableBody.innerHTML = '';
                resultKetQua.truong.forEach(tr => {
                    const chi_tieu = parseInt(tr.chi_tieu_hoc_sinh) || 0;
                    const da_trung = parseInt(tr.so_luong_hoc_sinh) || 0;
                    let statusClass = 'status-not-full';
                    let statusText = 'Chưa đủ chỉ tiêu';
                    if (chi_tieu === 0) {
                        statusClass = 'bg-secondary text-white';
                        statusText = 'Chưa đặt CT';
                    } else if (da_trung >= chi_tieu) {
                        statusClass = 'status-full';
                        statusText = 'Đủ chỉ tiêu';
                    }
                    truongTableBody.innerHTML += `<tr><td>${escapeHtml(tr.ten_truong)}</td><td>${chi_tieu}</td><td>${da_trung}</td><td><span class="status-badge ${statusClass}">${statusText}</span></td></tr>`;
                });
                
                // Hiển thị điểm chuẩn (Tab Trượt)
                const diemChuanTableBody = document.getElementById('diemChuanTableBody');
                diemChuanTableBody.innerHTML = '';
                resultKetQua.truong.forEach(tr => {
                    const chi_tieu = parseInt(tr.chi_tieu_hoc_sinh) || 0;
                    const da_trung = parseInt(tr.so_luong_hoc_sinh) || 0;
                    
                    // Tìm học sinh có tổng điểm thấp nhất nhưng vẫn trúng tuyển vào trường này
                    let diemChuan = 'Chưa có';
                    if (da_trung > 0) {
                        const thiSinhCuaTruong = resultKetQua.thi_sinh.filter(ts => ts.ten_truong_trung_tuyen === tr.ten_truong && ts.trang_thai === 'Dau');
                        if (thiSinhCuaTruong.length > 0) {
                            // Sắp xếp tăng dần, lấy thí sinh có điểm thấp nhất
                            thiSinhCuaTruong.sort((a, b) => (parseFloat(a.tong_diem) || 0) - (parseFloat(b.tong_diem) || 0));
                            diemChuan = parseFloat(thiSinhCuaTruong[0].tong_diem).toFixed(2);
                        }
                    }
                    
                    diemChuanTableBody.innerHTML += `<tr><td><strong>${escapeHtml(tr.ten_truong)}</strong></td><td>${chi_tieu}</td><td>${da_trung}</td><td><strong class="text-danger">${diemChuan}</strong></td></tr>`;
                });
                
                // Hiển thị danh sách trượt
                const thiSinhTruotTableBody = document.getElementById('thiSinhTruotTableBody');
                thiSinhTruotTableBody.innerHTML = '';
                
                const thiSinhTruot = resultKetQua.thi_sinh.filter(ts => ts.trang_thai === 'Truot');
                if (thiSinhTruot.length === 0) {
                    thiSinhTruotTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Không có thí sinh nào trượt.</td></tr>';
                } else {
                    thiSinhTruot.forEach(ts => {
                        thiSinhTruotTableBody.innerHTML += `
                            <tr>
                                <td>${escapeHtml(ts.so_bao_danh)}</td>
                                <td>${escapeHtml(ts.ho_ten)}</td>
                                <td><strong>${ts.tong_diem ? parseFloat(ts.tong_diem).toFixed(2) : 'N/A'}</strong></td>
                                <td>${escapeHtml(ts.ten_truong_nv1 || '—')}</td>
                                <td>${escapeHtml(ts.ten_truong_nv2 || '—')}</td>
                                <td>${escapeHtml(ts.ten_truong_nv3 || '—')}</td>
                                <td><span class="badge bg-danger">Tất cả NV đầy</span></td>
                            </tr>`;
                    });
                }
                
                document.getElementById('locAoResult').style.display = 'block';
            } catch (e) {}
        }

        async function loadThiSinhTrungTuyen() {
            const ma_truong = document.getElementById('selectTruongLocAo').value;
            const thiSinhSection = document.getElementById('thiSinhDauSection');
            const tableBody = document.getElementById('locAoTableBody');

            tableBody.innerHTML = '';

            if (!ma_truong) {
                thiSinhSection.style.display = 'none';
                return;
            }

            thiSinhSection.style.display = 'block';

            try {
                const result = await apiCall(`${API_URL}/getThiSinhTrungTuyenTheoTruongApi/${ma_truong}`);
                
                if (!result.data || result.data.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Không có thí sinh trúng tuyển.</td></tr>';
                } else {
                    result.data.forEach(ts => {
                        let ngaySinhDisplay = 'N/A';
                        if (ts.ngay_sinh) {
                            try {
                                ngaySinhDisplay = new Date(ts.ngay_sinh).toLocaleDateString('vi-VN');
                            } catch (e) {
                                ngaySinhDisplay = ts.ngay_sinh;
                            }
                        }
                        
                        let badgeClass = 'bg-secondary';
                        let trangThaiText = 'CHỜ XÁC NHẬN';
                        let statusValue = 'pending';
                        
                        if (ts.trang_thai_xac_nhan === 'Xac_nhan_nhap_hoc') {
                            trangThaiText = 'ĐÃ XÁC NHẬN';
                            badgeClass = 'bg-success';
                            statusValue = 'confirmed';
                        } else if (ts.trang_thai_xac_nhan === 'Tu_choi_nhap_hoc') {
                            trangThaiText = 'ĐÃ TỪ CHỐI';
                            badgeClass = 'bg-danger';
                            statusValue = 'rejected';
                        }
                        
                        tableBody.innerHTML += `
                            <tr data-status="${statusValue}">
                                <td>${escapeHtml(ts.so_bao_danh)}</td>
                                <td>${escapeHtml(ts.ho_ten)}</td>
                                <td>${ngaySinhDisplay}</td>
                                <td>${ts.tong_diem ? parseFloat(ts.tong_diem).toFixed(2) : 'N/A'}</td>
                                <td><span class="badge ${badgeClass}">${trangThaiText}</span></td>
                            </tr>`;
                    });
                }
            } catch (e) {}
        }

        function filterPendingStudents() {
            const rows = document.querySelectorAll('#locAoTableBody tr[data-status]');
            let count = 0;
            rows.forEach(row => {
                if (row.dataset.status === 'pending') {
                    row.style.display = '';
                    count++;
                } else {
                    row.style.display = 'none';
                }
            });
            if (count === 0 && rows.length > 0) {
                showNotification('Không có thí sinh nào chưa xác nhận.', 'info');
            }
        }

        function filterConfirmedStudents() {
            const rows = document.querySelectorAll('#locAoTableBody tr[data-status]');
            let count = 0;
            rows.forEach(row => {
                if (row.dataset.status === 'confirmed') {
                    row.style.display = '';
                    count++;
                } else {
                    row.style.display = 'none';
                }
            });
            if (count === 0 && rows.length > 0) {
                showNotification('Không có thí sinh nào đã xác nhận.', 'info');
            }
        }

        function filterRejectedStudents() {
            const rows = document.querySelectorAll('#locAoTableBody tr[data-status]');
            let count = 0;
            rows.forEach(row => {
                if (row.dataset.status === 'rejected') {
                    row.style.display = '';
                    count++;
                } else {
                    row.style.display = 'none';
                }
            });
            if (count === 0 && rows.length > 0) {
                showNotification('Không có thí sinh nào từ chối.', 'info');
            }
        }

        function showAllStudents() {
            document.querySelectorAll('#locAoTableBody tr[data-status]').forEach(row => row.style.display = '');
        }
    </script>
</body>
</html>