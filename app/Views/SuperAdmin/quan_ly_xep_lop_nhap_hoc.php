<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Xếp Lớp & Nhập Học | Sở GD</title>
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
                <h2><i class="bi bi-people-fill"></i> Quản lý Xếp lớp & Nhập học</h2>
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
                <div class="col-md-6 mb-3">
                    <div class="card function-card" onclick="showXepLopForm()">
                        <div class="card-body text-center">
                            <i class="bi bi-people-fill"></i>
                            <h5 class="mt-3">Xếp Lớp & Chốt Hồ Sơ</h5>
                            <p class="text-muted">Phân lớp thủ công hoặc tự động cho học sinh</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
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

        <!-- Xếp Lớp Form -->
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
                                <div class="d-flex gap-2 flex-wrap" id="filterButtonArea"></div>
                            </div>
                            <div class="table-container mb-3" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th class="text-center"><input type="checkbox" id="checkAllAssign" onchange="toggleCheckAllAssign()"></th>
                                            <th>SBD</th>
                                            <th>Họ tên</th>
                                            <th>Điểm</th>
                                            <th>Tổ hợp</th>
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
                                            <select class="form-select me-2" id="selectLopDestination"></select>
                                            <button class="btn btn-success fw-bold" onclick="executeAssignment()"><i class="bi bi-check2-all"></i> Xếp lớp thủ công (<span id="selectedAssignCount">0 chọn</span>)</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="card border-primary bg-primary text-white h-100">
                                        <button class="btn btn-primary h-100 fw-bold w-100" onclick="executeAutoAssignment()">
                                            <i class="bi bi-lightning-charge"></i> Phân lớp tự động (theo tổ hợp)
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

        <!-- Xem Lớp Form -->
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
                                            <th>#</th>
                                            <th>SBD</th>
                                            <th>Họ tên</th>
                                            <th>Ngày sinh</th>
                                            <th>Ngày nhập học</th>
                                            <th>Trạng thái</th>
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
        let allLopCuaTruong = [];
        let currentStudentList = [];

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
        function backToMenu() {
            document.getElementById('mainMenu').style.display = 'block';
            document.getElementById('xepLopForm').style.display = 'none';
            document.getElementById('xemLopForm').style.display = 'none';
        }
        // --- Xếp Lớp ---
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
                const [resStudents, resClasses] = await Promise.all([
                    apiCall(`${API_URL}/getHocSinhChoXepLopApi`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ ma_truong }) }),
                    apiCall(`${API_URL}/getDsLopApi`)
                ]);
                allLopCuaTruong = (resClasses.data || []).filter(c => c.ma_truong == ma_truong);
                currentStudentList = resStudents.data || [];
                renderFilterButtons();
                renderStudentTable(currentStudentList);
                renderClassOptions(allLopCuaTruong);
                document.getElementById('assignmentArea').style.display = 'block';
                document.getElementById('assignmentEmptyState').style.display = 'none';
            } catch(e) { console.error(e); }
        }
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
                if (filtered.length > 0) {
                    const maToHop = filtered[0].ma_to_hop_mon;
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
        // --- Xem Lớp ---
        async function showXemLopForm() {
            document.getElementById('mainMenu').style.display = 'none';
            document.getElementById('xepLopForm').style.display = 'none';
            document.getElementById('xemLopForm').style.display = 'block';
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
                        let statusBadge = '';
                        let rowClass = '';
                        if (hs.is_new == 1) {
                            statusBadge = '<span class="badge bg-danger blink_me">🔥 Mới tuyển sinh</span>';
                            rowClass = 'table-warning';
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
