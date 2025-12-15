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
                <div class="col-md-4 mb-3">
                    <div class="card function-card" onclick="showXepLopForm()">
                        <div class="card-body text-center">
                            <i class="bi bi-people-fill"></i>
                            <h5 class="mt-3">Xếp Lớp & Chốt Hồ Sơ</h5>
                            <p class="text-muted">Phân lớp thủ công hoặc tự động cho học sinh</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card function-card" onclick="showXemLopForm()">
                        <div class="card-body text-center">
                            <i class="bi bi-list-columns-reverse"></i>
                            <h5 class="mt-3">Danh Sách Lớp & Học Sinh</h5>
                            <p class="text-muted">Xem sĩ số và danh sách học sinh các lớp</p>
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

        <div id="xepLopForm" style="display: none;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people-fill me-2"></i>Xếp Lớp Học Sinh</span>
                    <button class="btn btn-sm btn-light" onclick="backToMenu()"><i class="bi bi-arrow-left"></i> Quay lại</button>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Chọn Trường THPT cần xếp lớp:</label>
                            <select class="form-select border-primary" id="selectTruongXepLop" onchange="loadStudentsForAssignment()">
                                <option value="">-- Vui lòng chọn trường --</option>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <div id="assignmentArea" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0 text-primary">Danh sách học sinh ĐÃ XÁC NHẬN:</h6>
                            <span class="badge bg-danger" id="waitingCountBadge">0 học sinh</span>
                        </div>

                        <div class="card p-2 mb-3 bg-light border-0">
                            <div class="d-flex gap-2 flex-wrap" id="filterButtonArea">
                                </div>
                        </div>

                        <div class="table-container mb-3" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 40px; text-align: center;">
                                            <input type="checkbox" id="checkAllAssign" onchange="toggleCheckAllAssign()">
                                        </th>
                                        <th>SBD</th>
                                        <th>Họ tên</th>
                                        <th>Điểm</th>
                                        <th>Tổ hợp (Nguyện vọng)</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody id="studentAssignmentBody"></tbody>
                            </table>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-7">
                                <div class="card border-success">
                                    <div class="card-body d-flex align-items-center p-2">
                                        <div style="flex-grow: 1;" class="me-2">
                                            <select class="form-select border-success" id="selectLopDestination">
                                                <option value="">-- Chọn lớp thủ công --</option>
                                            </select>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span id="selectedAssignCount" class="me-2 fw-bold text-success small">0 chọn</span>
                                            <button class="btn btn-success text-nowrap" onclick="executeAssignment()">
                                                <i class="bi bi-box-arrow-in-down-right"></i> Xếp lớp
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="card border-primary bg-primary text-white h-100">
                                    <button class="btn btn-primary h-100 fw-bold w-100" onclick="executeAutoAssignment()">
                                        <i class="bi bi-lightning-charge-fill text-warning"></i> PHÂN LỚP TỰ ĐỘNG
                                    </button>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block">* Phân lớp tự động: Hệ thống sẽ tự tìm lớp phù hợp với Tổ hợp của từng học sinh để xếp vào (Ưu tiên cân bằng sĩ số).</small>
                    </div>
                    
                    <div id="assignmentEmptyState" class="text-center py-5 text-muted" style="display: none;">
                        <i class="bi bi-arrow-up-circle fs-1"></i>
                        <p class="mt-2">Vui lòng chọn trường ở trên để xem dữ liệu.</p>
                    </div>
                </div>
            </div>
        </div>

        <div id="xemLopForm" style="display: none;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-list-ol me-2"></i>Danh Sách Học Sinh Trong Lớp</span>
                    <button class="btn btn-sm btn-light" onclick="backToMenu()"><i class="bi bi-arrow-left"></i> Quay lại</button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Chọn Trường:</label>
                            <select class="form-select" id="viewTruongSelect" onchange="loadLopForView()">
                                <option value="">-- Chọn trường --</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Chọn Lớp:</label>
                            <select class="form-select" id="viewLopSelect" onchange="loadHocSinhInClass()">
                                <option value="">-- Chọn lớp --</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100" onclick="loadHocSinhInClass()">
                                <i class="bi bi-search"></i> Xem
                            </button>
                        </div>
                    </div>

                    <hr>

                    <div id="classDetailArea" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0">Danh sách học sinh lớp <span id="currentClassName" class="text-primary"></span>:</h6>
                            <span class="badge bg-info text-dark" id="currentClassCount">0 học sinh</span>
                        </div>
                        <div class="table-container">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>STT</th>
                                        <th>SBD</th>
                                        <th>Họ và Tên</th>
                                        <th>Ngày Sinh</th>
                                        <th>Ngày Vào Lớp</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody id="classStudentBody"></tbody>
                            </table>
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
        // Biến cho phần Xếp Lớp
        let allLopCuaTruong = [];
        let currentStudentList = [];

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
            document.getElementById('xepLopForm').style.display = 'none';

            // THÊM DÒNG NÀY:
            document.getElementById('xemLopForm').style.display = 'none';
        }

        // --- Logic Chỉ Tiêu & Điểm ---
        async function saveChiTieu() {
            const rows = document.querySelectorAll('#chiTieuTableBody tr');
            let payload = {}; let valid = true;
            rows.forEach(row => {
                const ma_truong = row.dataset.maTruong; const input = row.querySelector('input[type="number"]'); const value = parseInt(input.value);
                if (isNaN(value) || value < 0) { valid = false; input.classList.add('is-invalid'); }
                else { input.classList.remove('is-invalid'); payload[ma_truong] = value; }
            });
            if (!valid) { showNotification('Chỉ tiêu không hợp lệ!', 'danger'); return; }
            try {
                const result = await apiCall(`${API_URL}/updateChiTieuApi`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
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

        async function saveDiem() {
            const rows = document.querySelectorAll('#diemTableBody tr[data-ma-nguoi-dung]');
            let payload = []; let valid = true;
            rows.forEach(row => {
                const ma_nguoi_dung = parseInt(row.dataset.maNguoiDung); const inputs = row.querySelectorAll('input.diem-input');
                const diem_toan = inputs[0].value === '' ? null : parseFloat(inputs[0].value); const diem_van = inputs[1].value === '' ? null : parseFloat(inputs[1].value); const diem_anh = inputs[2].value === '' ? null : parseFloat(inputs[2].value);
                if (diem_toan !== null || diem_van !== null || diem_anh !== null) {
                    if ((diem_toan !== null && (diem_toan < 0 || diem_toan > 10)) || (diem_van !== null && (diem_van < 0 || diem_van > 10)) || (diem_anh !== null && (diem_anh < 0 || diem_anh > 10))) { valid = false; inputs.forEach(inp => inp.classList.add('is-invalid')); }
                    else { inputs.forEach(inp => inp.classList.remove('is-invalid')); payload.push({ ma_nguoi_dung, diem_toan, diem_van, diem_anh }); }
                }
            });
            if (!valid) { showNotification('Điểm nhập không hợp lệ!', 'danger'); return; }
            if (payload.length === 0) { showNotification('Không có điểm mới.', 'warning'); return; }
            try {
                const result = await apiCall(`${API_URL}/updateDiemApi`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                showNotification(result.message, 'success'); loadThiSinh();
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
                        // Hỗ trợ cả 'Tu_choi_nhap_hoc' và 'Tu_choi'
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

        // --- Logic Xếp Lớp (SMART) ---
        async function showXepLopForm() {
            document.getElementById('mainMenu').style.display = 'none';
            document.getElementById('xepLopForm').style.display = 'block';
            document.getElementById('assignmentArea').style.display = 'none';
            document.getElementById('assignmentEmptyState').style.display = 'block';
            
            const select = document.getElementById('selectTruongXepLop');
            select.innerHTML = '<option value="">-- Vui lòng chọn trường --</option>';
            if (dsTruongData.length === 0) {
                try { const res = await apiCall(`${API_URL}/getDsTruongApi`); dsTruongData = res.data; } catch(e) {}
            }
            dsTruongData.forEach(tr => {
                select.innerHTML += `<option value="${tr.ma_truong}">${escapeHtml(tr.ten_truong)}</option>`;
            });
        }

        async function loadStudentsForAssignment() {
            const ma_truong = document.getElementById('selectTruongXepLop').value;
            if (!ma_truong) {
                document.getElementById('assignmentArea').style.display = 'none';
                document.getElementById('assignmentEmptyState').style.display = 'block';
                return;
            }

            try {
                // Load song song học sinh và danh sách lớp
                const [resStudents, resClasses] = await Promise.all([
                    apiCall(`${API_URL}/getHocSinhChoXepLopApi`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ ma_truong }) }),
                    apiCall(`${API_URL}/getDsLopApi`)
                ]);

                // Lưu dữ liệu toàn cục
                allLopCuaTruong = (resClasses.data || []).filter(c => c.ma_truong == ma_truong);
                currentStudentList = resStudents.data || [];

                // Render giao diện
                renderFilterButtons();
                renderStudentTable(currentStudentList);
                renderClassOptions(allLopCuaTruong);

                document.getElementById('assignmentArea').style.display = 'block';
                document.getElementById('assignmentEmptyState').style.display = 'none';

            } catch(e) { console.error(e); }
        }

        // Tạo nút lọc nhóm
        function renderFilterButtons() {
            const filterArea = document.getElementById('filterButtonArea');
            const groups = [...new Set(currentStudentList.map(item => item.ten_to_hop))];
            
            let html = `<button class="btn btn-sm btn-secondary active" onclick="filterByGroup('all', this)">Tất cả (${currentStudentList.length})</button>`;
            groups.forEach(g => {
                const count = currentStudentList.filter(s => s.ten_to_hop === g).length;
                html += `<button class="btn btn-sm btn-outline-primary" onclick="filterByGroup('${g}', this)">${g} (${count})</button>`;
            });
            filterArea.innerHTML = html;
        }

        // Xử lý lọc nhóm
        function filterByGroup(groupName, btn) {
            document.querySelectorAll('#filterButtonArea button').forEach(b => {
                b.classList.remove('btn-secondary', 'active');
                b.classList.add('btn-outline-primary');
            });
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-secondary', 'active');

            if (groupName === 'all') {
                renderStudentTable(currentStudentList);
                renderClassOptions(allLopCuaTruong);
            } else {
                const filtered = currentStudentList.filter(s => s.ten_to_hop === groupName);
                renderStudentTable(filtered);
                // Lọc dropdown lớp theo nhóm này luôn
                if (filtered.length > 0) {
                    const maToHop = filtered[0].ma_to_hop_mon;
                    // FIX SO SÁNH CHUỖI/SỐ
                    const filteredClasses = allLopCuaTruong.filter(c => String(c.ma_to_hop_mon) === String(maToHop));
                    renderClassOptions(filteredClasses);
                }
            }
        }

        function renderStudentTable(data) {
            const tbody = document.getElementById('studentAssignmentBody');
            tbody.innerHTML = '';
            document.getElementById('waitingCountBadge').textContent = `${data.length} học sinh`;

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">Không có dữ liệu</td></tr>';
                return;
            }

            data.forEach(hs => {
                let badgeColor = 'bg-info';
                if ((hs.ten_to_hop || '').includes('TN')) badgeColor = 'bg-success';
                if ((hs.ten_to_hop || '').includes('XH')) badgeColor = 'bg-warning text-dark';

                tbody.innerHTML += `
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="assign-cb" value="${hs.ma_nguoi_dung}" data-tohop="${hs.ma_to_hop_mon || ''}" onchange="updateAssignCount()">
                        </td>
                        <td>${escapeHtml(hs.so_bao_danh)}</td>
                        <td class="fw-bold">${escapeHtml(hs.ho_ten)}</td>
                        <td class="text-danger fw-bold">${hs.tong_diem}</td>
                        <td><span class="badge ${badgeColor}">${hs.ten_to_hop || 'Chưa rõ'}</span></td>
                        <td><span class="badge bg-secondary">Chưa xếp</span></td>
                    </tr>
                `;
            });
        }

        function renderClassOptions(classes) {
            const select = document.getElementById('selectLopDestination');
            select.innerHTML = '<option value="">-- Chọn lớp thủ công --</option>';
            if (classes.length === 0) {
                select.innerHTML += '<option disabled>Không tìm thấy lớp phù hợp</option>';
                return;
            }
            classes.forEach(c => {
                select.innerHTML += `<option value="${c.ma_lop}">${escapeHtml(c.ten_lop)}</option>`;
            });
        }

        function toggleCheckAllAssign() {
            const state = document.getElementById('checkAllAssign').checked;
            document.querySelectorAll('.assign-cb').forEach(cb => cb.checked = state);
            updateAssignCount();
        }

        function updateAssignCount() {
            const count = document.querySelectorAll('.assign-cb:checked').length;
            document.getElementById('selectedAssignCount').textContent = `${count} chọn`;
        }

        // XẾP LỚP THỦ CÔNG
        async function executeAssignment() {
            const ma_lop = document.getElementById('selectLopDestination').value;
            const checkboxes = document.querySelectorAll('.assign-cb:checked');
            const ids = Array.from(checkboxes).map(cb => cb.value);

            if (!ma_lop) { showNotification('Vui lòng chọn lớp đích!', 'warning'); return; }
            if (ids.length === 0) { showNotification('Chưa chọn học sinh nào!', 'warning'); return; }
            if (!confirm(`Xếp ${ids.length} học sinh vào lớp này?`)) return;

            try {
                const res = await apiCall(`${API_URL}/thucHienXepLopApi`, {
                    method: 'POST', headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ ma_lop: ma_lop, danh_sach_ma_hoc_sinh: ids })
                });
                showNotification(res.message, 'success');
                loadStudentsForAssignment();
                document.getElementById('checkAllAssign').checked = false;
                document.getElementById('selectedAssignCount').textContent = '0 chọn';
            } catch(e) {}
        }

        // XẾP LỚP TỰ ĐỘNG
        async function executeAutoAssignment() {
            const checkboxes = document.querySelectorAll('.assign-cb:checked');
            const ids = Array.from(checkboxes).map(cb => cb.value);

            if (ids.length === 0) { alert('Vui lòng chọn ít nhất 1 học sinh để phân lớp tự động!'); return; }
            if (!confirm(`Hệ thống sẽ tự động tìm lớp phù hợp cho ${ids.length} học sinh này dựa trên Tổ hợp môn.\n\nBạn có chắc chắn muốn chạy không?`)) return;

            try {
                const ma_truong = document.getElementById('selectTruongXepLop').value;
                const res = await apiCall(`${API_URL}/autoPhanLopApi`, {
                    method: 'POST', headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ ma_truong: ma_truong, danh_sach_hs: ids })
                });
                if (res.success) {
                    alert(res.message);
                    loadStudentsForAssignment();
                    showXemLopForm();
                } else {
                    alert('Lỗi: ' + res.message);
                }
            } catch(e) { alert('Lỗi hệ thống!'); }
        }


        // --- LOGIC XEM DANH SÁCH LỚP ---
    async function showXemLopForm() {
        document.getElementById('mainMenu').style.display = 'none';
        document.getElementById('xepLopForm').style.display = 'none'; // Ẩn các form khác
        document.getElementById('xemLopForm').style.display = 'block';
        
        // Load danh sách trường
        const select = document.getElementById('viewTruongSelect');
        select.innerHTML = '<option value="">-- Chọn trường --</option>';
        if (dsTruongData.length === 0) {
            try { const res = await apiCall(`${API_URL}/getDsTruongApi`); dsTruongData = res.data; } catch(e) {}
        }
        dsTruongData.forEach(tr => {
            select.innerHTML += `<option value="${tr.ma_truong}">${escapeHtml(tr.ten_truong)}</option>`;
        });
    }

    async function loadLopForView() {
        const ma_truong = document.getElementById('viewTruongSelect').value;
        const selectLop = document.getElementById('viewLopSelect');
        selectLop.innerHTML = '<option value="">-- Đang tải... --</option>';
        
        if (!ma_truong) { selectLop.innerHTML = '<option value="">-- Chọn trường trước --</option>'; return; }

        try {
            const resClasses = await apiCall(`${API_URL}/getDsLopApi`);
            const classes = resClasses.data.filter(c => c.ma_truong == ma_truong);
            
            selectLop.innerHTML = '<option value="">-- Chọn lớp --</option>';
            classes.forEach(c => {
                // Hiện tên lớp kèm sĩ số
                selectLop.innerHTML += `<option value="${c.ma_lop}">${c.ten_lop} (Sĩ số: ${c.si_so})</option>`;
            });
        } catch(e) {}
    }

    async function loadHocSinhInClass() {
        const ma_lop = document.getElementById('viewLopSelect').value;
        const ten_lop = document.getElementById('viewLopSelect').options[document.getElementById('viewLopSelect').selectedIndex].text;
        
        if (!ma_lop) return;

        try {
            const res = await apiCall(`${API_URL}/getDsHocSinhTrongLopApi`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ ma_lop: ma_lop })
            });

            document.getElementById('classDetailArea').style.display = 'block';
            document.getElementById('currentClassName').textContent = ten_lop;
            document.getElementById('currentClassCount').textContent = `${res.data.length} học sinh`;
            
            const tbody = document.getElementById('classStudentBody');
            tbody.innerHTML = '';

            if (res.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Lớp chưa có học sinh nào.</td></tr>';
            } else {
                res.data.forEach((hs, index) => {
                    let dob = hs.ngay_sinh ? new Date(hs.ngay_sinh).toLocaleDateString('vi-VN') : '';
                    let joinDate = hs.ngay_nhap_hoc ? new Date(hs.ngay_nhap_hoc).toLocaleDateString('vi-VN') : '';
                    
                    // HIGHLIGHT: Nếu là học sinh mới (is_new = 1) -> Hiện Badge "Mới tuyển"
                    let statusBadge = '';
                    let rowClass = '';
                    if (hs.is_new == 1) {
                        statusBadge = '<span class="badge bg-danger blink_me">🔥 Mới tuyển sinh</span>';
                        rowClass = 'table-warning'; // Tô màu vàng nhạt cho dòng này
                    } else {
                        statusBadge = '<span class="badge bg-success">Học sinh cũ</span>';
                    }

                    tbody.innerHTML += `
                        <tr class="${rowClass}">
                            <td>${index + 1}</td>
                            <td>${escapeHtml(hs.so_bao_danh)}</td>
                            <td class="fw-bold">${escapeHtml(hs.ho_ten)}</td>
                            <td>${dob}</td>
                            <td>${joinDate}</td>
                            <td>${statusBadge}</td>
                        </tr>
                    `;
                });
            }
        } catch(e) {}
    }
    </script>
</body>
</html>