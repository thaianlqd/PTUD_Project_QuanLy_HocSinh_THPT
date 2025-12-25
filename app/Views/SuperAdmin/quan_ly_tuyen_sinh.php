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
            <div class="row justify-content-center">
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
                        1. Bấm <strong>Chạy Lọc Ảo</strong> để tính toán. 2. Chọn trường để xem chi tiết. 3. Xem trạng thái xác nhận.
                    </div>

                    <div class="bg-light p-3 rounded mb-4">
                        <div class="d-flex gap-2">
                            <button class="btn btn-warning fw-bold" onclick="locAoToanCuc('reset')">
                                <i class="bi bi-arrow-counterclockwise"></i> Chạy Lọc Ảo (Reset Toàn Bộ)
                            </button>
                            
                            <button class="btn btn-primary fw-bold" onclick="locAoToanCuc('update')">
                                <i class="bi bi-play-circle-fill"></i> Chạy Lọc Ảo (Giữ Xác Nhận)
                            </button>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            * <strong>Reset:</strong> Xóa sạch kết quả cũ, tính lại từ đầu.<br>
                            * <strong>Không Reset:</strong> Giữ nguyên các em đã "Xác nhận nhập học", chỉ tính toán lại cho các em chưa có kết quả.
                        </small>
                    </div>

                    <div id="locAoResult" style="display: none;">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" id="tabDau-tab" data-bs-toggle="tab" data-bs-target="#tabDau" type="button">
                                    <i class="bi bi-check-circle me-2"></i>Danh sách Đậu
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="tabTruot-tab" data-bs-toggle="tab" data-bs-target="#tabTruot" type="button">
                                    <i class="bi bi-x-circle me-2"></i>Danh sách Trượt & Điểm Chuẩn
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active" id="tabDau">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Chọn trường để xem chi tiết:</label>
                                    <select class="form-select" id="selectTruongLocAo" onchange="loadThiSinhTrungTuyen()">
                                        <option value="">-- Chọn trường --</option>
                                    </select>
                                </div>

                                <div id="thiSinhDauSection" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">Danh sách học sinh đậu vào trường:</h6>
                                        <div>
                                            <button class="btn btn-outline-warning btn-sm me-2" onclick="filterStudents('pending')"><i class="bi bi-hourglass-split"></i> Chưa Xác Nhận</button>
                                            <button class="btn btn-outline-success btn-sm me-2" onclick="filterStudents('confirmed')"><i class="bi bi-check-circle-fill"></i> Đã Xác Nhận</button>
                                            <button class="btn btn-outline-danger btn-sm me-2" onclick="filterStudents('rejected')"><i class="bi bi-x-circle-fill"></i> Từ Chối</button>
                                            <button class="btn btn-outline-secondary btn-sm" onclick="showAllStudents()"><i class="bi bi-list-ul"></i> Tất Cả</button>
                                        </div>
                                    </div>

                                    <div class="table-container mb-4">
                                        <table class="table table-striped">
                                            <thead class="table-light">
                                                <tr><th>SBD</th><th>Họ tên</th><th>Ngày sinh</th><th>Tổng điểm</th><th>Trạng thái xác nhận</th></tr>
                                            </thead>
                                            <tbody id="locAoTableBody"></tbody>
                                        </table>
                                    </div>
                                </div>

                                <hr>
                                <h6 class="mb-3">Trạng thái chỉ tiêu các trường (sau lọc):</h6>
                                <div class="table-container">
                                    <table class="table table-bordered">
                                        <thead class="table-light"><tr><th>Tên trường</th><th>Chỉ tiêu</th><th>Đã trúng tuyển</th><th>Trạng thái</th></tr></thead>
                                        <tbody id="trangThaiTruongTableBody"></tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tabTruot">
                                <h6 class="mb-3 fw-bold">Điểm Chuẩn Tuyển Sinh Các Trường:</h6>
                                <div class="table-container mb-4">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light"><tr><th>Tên Trường</th><th>Chỉ Tiêu</th><th>Đã Trúng Tuyển</th><th>Điểm Chuẩn</th></tr></thead>
                                        <tbody id="diemChuanTableBody"></tbody>
                                    </table>
                                </div>
                                <hr>
                                <h6 class="mb-3 fw-bold">Danh Sách Thí Sinh Trượt:</h6>
                                <div class="table-container">
                                    <table class="table table-striped">
                                        <thead class="table-light">
                                            <tr><th>SBD</th><th>Họ Tên</th><th>Tổng Điểm</th><th>NV1</th><th>NV2</th><th>NV3</th><th>Lý Do</th></tr>
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

    <footer class="text-center py-4 text-muted">
        &copy; <?php echo date("Y"); ?> Hệ thống Quản lý Trường THPT
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- Globals ---
        const BASE_URL = "<?php echo rtrim(BASE_URL, '/'); ?>";
        const API_URL = BASE_URL + '/quanlytuyensinh'; 
        
        const loadingSpinner = document.getElementById('loadingSpinner');
        let dsTruongData = [];

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
             setTimeout(() => { el.style.display = 'none'; }, 5000);
         }

        async function apiCall(url, options = {}) {
             showLoading();
             try {
                 const response = await fetch(url, options);
                 const responseData = await response.json().catch(() => null);
                 if (!response.ok) {
                     throw new Error(responseData?.message || `HTTP ${response.status}`);
                 }
                 return responseData || { success: true };
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
                             <td><input type="number" class="form-control form-control-sm" value="${truong.chi_tieu_hoc_sinh || 0}" min="0" max="1000"></td>
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

        // --- Logic Chỉ Tiêu & Điểm ---
        // async function saveChiTieu() {
        //     const rows = document.querySelectorAll('#chiTieuTableBody tr');
        //     let payload = {}; let valid = true;
        //     rows.forEach(row => {
        //         const ma_truong = row.dataset.maTruong; const input = row.querySelector('input[type="number"]'); const value = parseInt(input.value);
        //         if (isNaN(value) || value < 0) { valid = false; input.classList.add('is-invalid'); }
        //         else { input.classList.remove('is-invalid'); payload[ma_truong] = value; }
        //     });
        //     if (!valid) { showNotification('Chỉ tiêu không hợp lệ!', 'danger'); return; }
        //     try {
        //         const result = await apiCall(`${API_URL}/updateChiTieuApi`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        //         showNotification(result.message, 'success');
        //     } catch (e) {}
        // }
        async function saveChiTieu() {
            const rows = document.querySelectorAll('#chiTieuTableBody tr');
            let payload = {}; 
            let valid = true;
            let errorMessage = "";

            rows.forEach(row => {
                const ma_truong = row.dataset.maTruong; 
                const input = row.querySelector('input[type="number"]'); 
                const value = input.value; // Lấy string để check trống
                const intValue = parseInt(value);

                // Check 1: Không được để trống
                if (value === "") {
                    valid = false;
                    errorMessage = "Không được để trống chỉ tiêu các trường";
                    input.classList.add('is-invalid');
                } 
                // Check 2: Phải là số nguyên (không nhập 50.5)
                else if (!Number.isInteger(Number(value))) {
                    valid = false;
                    errorMessage = "Chỉ tiêu phải là số nguyên dương";
                    input.classList.add('is-invalid');
                }
                // Check 3: Không âm và không quá lớn (ví dụ max 2000)
                else if (intValue < 0 || intValue > 2000) {
                    valid = false;
                    errorMessage = "Chỉ tiêu phải từ 0 đến 2000";
                    input.classList.add('is-invalid');
                }
                else {
                    input.classList.remove('is-invalid'); 
                    payload[ma_truong] = intValue; 
                }
            });

            if (!valid) { 
                alert(errorMessage); 
                return; 
            }

            if (!confirm("Xác nhận lưu thay đổi chỉ tiêu cho tất cả các trường?")) return;

            try {
                const result = await apiCall(`${API_URL}/updateChiTieuApi`, { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json' }, 
                    body: JSON.stringify(payload) 
                });
                showNotification(result.message, 'success');
            } catch (e) {}
        }

        async function loadThiSinh() {
            const truong_thcs = document.getElementById('truongThcs').value;
            const diemSection = document.getElementById('diemInputSection'); 
            const tableBody = document.getElementById('diemTableBody');
            
            if (!truong_thcs) { diemSection.style.display = 'none'; return; }
            
            diemSection.style.display = 'block';
            tableBody.innerHTML = '<tr><td colspan="10" class="text-center">Đang tải danh sách thí sinh...</td></tr>';
            
            try {
                const result = await apiCall(`${API_URL}/getDsThiSinhByTruongThcsApi`, {
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ truong_thcs: truong_thcs })
                });
                
                const dsThiSinhData = result.data; 
                tableBody.innerHTML = '';
                
                if (dsThiSinhData.length === 0) { 
                    tableBody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Không có thí sinh nào.</td></tr>'; 
                    return; 
                }
                
                dsThiSinhData.forEach(ts => {
                    let ngaySinhDisplay = ts.ngay_sinh ? new Date(ts.ngay_sinh).toLocaleDateString('vi-VN') : 'N/A';
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

        // async function saveDiem() {
        //     const rows = document.querySelectorAll('#diemTableBody tr[data-ma-nguoi-dung]');
        //     let payload = []; let valid = true;
        //     rows.forEach(row => {
        //         const ma_nguoi_dung = parseInt(row.dataset.maNguoiDung); const inputs = row.querySelectorAll('input.diem-input');
        //         const diem_toan = inputs[0].value === '' ? null : parseFloat(inputs[0].value); const diem_van = inputs[1].value === '' ? null : parseFloat(inputs[1].value); const diem_anh = inputs[2].value === '' ? null : parseFloat(inputs[2].value);
        //         if (diem_toan !== null || diem_van !== null || diem_anh !== null) {
        //             if ((diem_toan !== null && (diem_toan < 0 || diem_toan > 10)) || (diem_van !== null && (diem_van < 0 || diem_van > 10)) || (diem_anh !== null && (diem_anh < 0 || diem_anh > 10))) { valid = false; inputs.forEach(inp => inp.classList.add('is-invalid')); }
        //             else { inputs.forEach(inp => inp.classList.remove('is-invalid')); payload.push({ ma_nguoi_dung, diem_toan, diem_van, diem_anh }); }
        //         }
        //     });
        //     if (!valid) { showNotification('Điểm nhập không hợp lệ!', 'danger'); return; }
        //     if (payload.length === 0) { showNotification('Không có điểm mới.', 'warning'); return; }
        //     try {
        //         const result = await apiCall(`${API_URL}/updateDiemApi`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        //         showNotification(result.message, 'success'); loadThiSinh();
        //     } catch (e) {}
        // }
        async function saveDiem() {
            const rows = document.querySelectorAll('#diemTableBody tr[data-ma-nguoi-dung]');
            let payload = []; 
            let valid = true;
            let count = 0;

            rows.forEach(row => {
                const ma_nguoi_dung = parseInt(row.dataset.maNguoiDung); 
                const inputs = row.querySelectorAll('input.diem-input');
                
                // Helper để check từng ô điểm
                const checkDiem = (val) => {
                    if (val === '') return null;
                    const d = parseFloat(val);
                    // Check điểm từ 0-10 và phải là bội số của 0.05 hoặc 0.25 (tùy bác)
                    if (isNaN(d) || d < 0 || d > 10) return false;
                    return d;
                };

                const dToan = checkDiem(inputs[0].value);
                const dVan = checkDiem(inputs[1].value);
                const dAnh = checkDiem(inputs[2].value);

                // Nếu bất kỳ ô nào nhập sai định dạng (false)
                if (dToan === false || dVan === false || dAnh === false) {
                    valid = false;
                    inputs.forEach(inp => inp.classList.add('is-invalid'));
                } else {
                    inputs.forEach(inp => inp.classList.remove('is-invalid'));
                    // Chỉ thêm vào payload nếu có ít nhất 1 ô được nhập điểm
                    if (dToan !== null || dVan !== null || dAnh !== null) {
                        payload.push({ 
                            ma_nguoi_dung, 
                            diem_toan: dToan, 
                            diem_van: dVan, 
                            diem_anh: dAnh 
                        });
                        count++;
                    }
                }
            });

            if (!valid) { 
                alert('Điểm phải nằm trong khoảng từ 0 đến 10!'); 
                return; 
            }
            
            if (count === 0) { 
                alert('Chưa nhập điểm mới cho thí sinh nào cả.'); 
                return; 
            }

            if (!confirm(`Bạn sắp lưu điểm cho ${count} thí sinh. Tiếp tục?`)) return;

            try {
                const result = await apiCall(`${API_URL}/updateDiemApi`, { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json' }, 
                    body: JSON.stringify(payload) 
                });
                showNotification(result.message, 'success'); 
                loadThiSinh(); // Load lại để cập nhật giao diện
            } catch (e) {}
        }

        // --- Logic Lọc Ảo ---
        async function locAoToanCuc(mode) {
            let confirmMsg = mode === 'reset' ? '⚠️ XÓA SẠCH kết quả và tính lại từ đầu?' : 'ℹ️ Chỉ tính toán lại cho học sinh chưa có kết quả?';
            if (!confirm(confirmMsg)) return;
            
            document.getElementById('locAoResult').style.display = 'none';
            document.getElementById('thiSinhDauSection').style.display = 'none';
            
            try {
                const resultRun = await apiCall(`${API_URL}/runLocAoApi`, { 
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ mode: mode }) 
                });
                showNotification(resultRun.message, 'success');
                const resultKetQua = await apiCall(`${API_URL}/getKetQuaLocApi`);
                renderAfterLocAo(resultKetQua); 
            } catch (e) {}
        }

        function renderAfterLocAo(resultKetQua) {
             const truongTableBody = document.getElementById('trangThaiTruongTableBody');
             truongTableBody.innerHTML = '';
             resultKetQua.truong.forEach(tr => {
                 const chi_tieu = parseInt(tr.chi_tieu_hoc_sinh) || 0;
                 const da_trung = parseInt(tr.so_luong_hoc_sinh) || 0;
                 let statusText = chi_tieu === 0 ? 'Chưa đặt CT' : (da_trung >= chi_tieu ? 'Đủ chỉ tiêu' : 'Chưa đủ');
                 let statusClass = chi_tieu === 0 ? 'bg-secondary' : (da_trung >= chi_tieu ? 'status-full' : 'status-not-full');
                 truongTableBody.innerHTML += `<tr><td>${escapeHtml(tr.ten_truong)}</td><td>${chi_tieu}</td><td>${da_trung}</td><td><span class="status-badge ${statusClass}">${statusText}</span></td></tr>`;
             });

             const diemChuanTableBody = document.getElementById('diemChuanTableBody');
             diemChuanTableBody.innerHTML = '';
             resultKetQua.truong.forEach(tr => {
                 const da_trung = parseInt(tr.so_luong_hoc_sinh) || 0;
                 let diemChuan = 'Chưa có';
                 if (da_trung > 0) {
                     const thiSinhCuaTruong = resultKetQua.thi_sinh.filter(ts => ts.truong_trung_tuyen === tr.ten_truong && ts.trang_thai === 'Dau');
                     if (thiSinhCuaTruong.length > 0) {
                         thiSinhCuaTruong.sort((a, b) => (parseFloat(a.tong_diem) || 0) - (parseFloat(b.tong_diem) || 0));
                         diemChuan = parseFloat(thiSinhCuaTruong[0].tong_diem).toFixed(2);
                     }
                 }
                 diemChuanTableBody.innerHTML += `<tr><td>${escapeHtml(tr.ten_truong)}</td><td>${tr.chi_tieu_hoc_sinh}</td><td>${da_trung}</td><td class="text-danger fw-bold">${diemChuan}</td></tr>`;
             });

             const thiSinhTruotTableBody = document.getElementById('thiSinhTruotTableBody');
             thiSinhTruotTableBody.innerHTML = '';
             const thiSinhTruot = resultKetQua.thi_sinh.filter(ts => ts.trang_thai === 'Truot');
             if (thiSinhTruot.length === 0) {
                 thiSinhTruotTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Không có thí sinh trượt.</td></tr>';
             } else {
                 thiSinhTruot.forEach(ts => {
                     thiSinhTruotTableBody.innerHTML += `<tr>
                         <td>${escapeHtml(ts.so_bao_danh)}</td><td>${escapeHtml(ts.ho_ten)}</td>
                         <td><strong>${ts.tong_diem}</strong></td>
                         <td>${escapeHtml(ts.ten_truong_nv1)}</td><td>${escapeHtml(ts.ten_truong_nv2)}</td><td>${escapeHtml(ts.ten_truong_nv3)}</td>
                         <td><span class="badge bg-danger">Tất cả NV đầy</span></td></tr>`;
                 });
             }
             document.getElementById('locAoResult').style.display = 'block';
        }

        async function loadThiSinhTrungTuyen() {
            const ma_truong = document.getElementById('selectTruongLocAo').value;
            const tableBody = document.getElementById('locAoTableBody');
            document.getElementById('thiSinhDauSection').style.display = ma_truong ? 'block' : 'none';
            if (!ma_truong) return;

            try {
                const result = await apiCall(`${API_URL}/getThiSinhTrungTuyenTheoTruongApi/${ma_truong}`);
                tableBody.innerHTML = '';
                if (!result.data || result.data.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Không có thí sinh.</td></tr>';
                } else {
                    result.data.forEach(ts => {
                        let statusText = 'CHỜ XÁC NHẬN';
                        let badgeClass = 'bg-warning text-dark';
                        let statusValue = 'pending';

                        if (ts.trang_thai_xac_nhan === 'Xac_nhan_nhap_hoc') {
                            statusText = 'ĐÃ XÁC NHẬN';
                            badgeClass = 'bg-success text-white';
                            statusValue = 'confirmed';
                        }
                        else if (ts.trang_thai_xac_nhan === 'Tu_choi_nhap_hoc' || ts.trang_thai_xac_nhan === 'Tu_choi') {
                            statusText = 'ĐÃ TỪ CHỐI';
                            badgeClass = 'bg-danger text-white';
                            statusValue = 'rejected';
                        }

                        tableBody.innerHTML += `<tr data-status="${statusValue}">
                            <td>${escapeHtml(ts.so_bao_danh)}</td>
                            <td>${escapeHtml(ts.ho_ten)}</td>
                            <td>${ts.ngay_sinh ? new Date(ts.ngay_sinh).toLocaleDateString('vi-VN') : 'N/A'}</td>
                            <td>${ts.tong_diem}</td>
                            <td><span class="badge ${badgeClass}">${statusText}</span></td>
                        </tr>`;
                    });
                }
            } catch (e) {}
        }

        function filterStudents(status) {
            document.querySelectorAll('#locAoTableBody tr[data-status]').forEach(row => {
                row.style.display = row.dataset.status === status ? '' : 'none';
            });
        }
        function showAllStudents() {
            document.querySelectorAll('#locAoTableBody tr').forEach(row => row.style.display = '');
        }

        // --- LOAD TAB TRƯỢT & ĐIỂM CHUẨN ---
        async function loadDanhSachTruot() {
            try {
                const res = await apiCall(`${API_URL}/getDuLieuTruotVaDiemChuanApi`);
                if (!res.success) {
                    showNotification(res.message || 'Không tải được danh sách trượt', 'danger');
                    return;
                }

                const diemChuanData = res.bang_diem_chuan || [];
                const dsTruot = res.ds_truot || [];

                // Render bảng điểm chuẩn
                const diemChuanBody = document.getElementById('diemChuanTableBody');
                diemChuanBody.innerHTML = diemChuanData.length ? diemChuanData.map(t => `
                    <tr>
                        <td>${escapeHtml(t.ten_truong)}</td>
                        <td>${t.chi_tieu_hoc_sinh ?? '-'}</td>
                        <td>${t.da_tuyen ?? 0}</td>
                        <td class="text-danger fw-bold">${t.diem_chuan ? parseFloat(t.diem_chuan).toFixed(2) : '-'}</td>
                    </tr>
                `).join('') : '<tr><td colspan="4" class="text-center text-muted">Không có dữ liệu</td></tr>';

                // Render danh sách trượt
                const truotBody = document.getElementById('thiSinhTruotTableBody');
                if (!dsTruot.length) {
                    truotBody.innerHTML = '<tr><td colspan="7" class="text-center">Không có thí sinh trượt.</td></tr>';
                } else {
                    truotBody.innerHTML = dsTruot.map(ts => `
                        <tr>
                            <td>${escapeHtml(ts.so_bao_danh)}</td>
                            <td>${escapeHtml(ts.ho_ten)}</td>
                            <td class="fw-bold">${ts.tong_diem ?? '-'}</td>
                            <td>${escapeHtml(ts.nv1 || '-')}</td>
                            <td>${escapeHtml(ts.nv2 || '-')}</td>
                            <td>${escapeHtml(ts.nv3 || '-')}</td>
                            <td class="text-muted">${ts.ly_do || 'Không đủ điểm'}</td>
                        </tr>
                    `).join('');
                }
            } catch (e) { console.error(e); }
        }

        // Gắn sự kiện khi nhấn tab "Danh sách Trượt"
        document.addEventListener('DOMContentLoaded', () => {
            const tabTruotBtn = document.getElementById('tabTruot-tab');
            if (tabTruotBtn) {
                tabTruotBtn.addEventListener('click', loadDanhSachTruot);
            }
        });
    </script>
</body>
</html>