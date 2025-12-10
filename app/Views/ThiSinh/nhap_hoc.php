<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Nhập Học</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .container-main {
            max-width: 1000px;
            margin: 0 auto;
        }

        .page-title {
            color: white;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
            font-size: 2.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .card {
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            font-weight: 600;
            padding: 15px 20px;
        }

        .card-body {
            padding: 25px;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e9ecef;
            z-index: 0;
        }

        .step-item {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-number {
            width: 40px;
            height: 40px;
            margin: 0 auto 10px;
            background: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 18px;
            color: #666;
        }

        .step-item.active .step-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .step-item.completed .step-number {
            background: var(--success-color);
            color: white;
        }

        .step-title {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }

        .step-item.active .step-title {
            color: var(--primary-color);
            font-weight: 600;
        }

        .school-item {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .school-item:hover {
            border-color: var(--primary-color);
            background: #f8f9ff;
        }

        .school-item.selected {
            border-color: var(--success-color);
            background: #f0fdf4;
        }

        .school-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }

        .status-dau {
            background: #d1fae5;
            color: #065f46;
        }

        .status-truot {
            background: #fee2e2;
            color: #991b1b;
        }

        .subject-group {
            margin-bottom: 25px;
        }

        .group-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--primary-color);
        }

        .subject-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            margin-bottom: 10px;
            transition: all 0.2s ease;
        }

        .subject-item:hover {
            background: #f8f9fa;
            border-color: var(--primary-color);
        }

        .subject-item input[type="radio"] {
            cursor: pointer;
        }

        .subject-item label {
            margin: 0 0 0 10px;
            cursor: pointer;
            flex: 1;
        }

        .subject-item input[type="radio"]:checked + label {
            color: var(--success-color);
            font-weight: 600;
        }

        .class-item {
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .class-item:hover {
            border-color: var(--primary-color);
            background: #f8f9ff;
        }

        .class-item.selected {
            border-color: var(--success-color);
            background: #f0fdf4;
            border-width: 2px;
        }

        .class-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .class-name {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }

        .class-capacity {
            background: #e9ecef;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            color: #666;
        }

        .class-capacity.available {
            background: #d1fae5;
            color: #065f46;
            font-weight: 600;
        }

        .confirmation-box {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }

        .confirmation-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .confirmation-item:last-child {
            border-bottom: none;
        }

        .confirmation-label {
            color: #666;
            font-weight: 500;
        }

        .confirmation-value {
            color: #333;
            font-weight: 600;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-outline-custom {
            border: 2px solid var(--danger-color);
            color: var(--danger-color);
            background: transparent;
            font-weight: 600;
            padding: 10px 25px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-outline-custom:hover {
            background: var(--danger-color);
            color: white;
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            gap: 10px;
        }

        .alert-custom {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .alert-info-custom {
            background: #cffafe;
            color: #164e63;
            border-left: 4px solid #0891b2;
        }

        .alert-warning-custom {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }

        .alert-success-custom {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .subject-counter {
            background: #667eea;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .subject-counter.warning {
            background: var(--warning-color);
            color: #333;
        }

        .subject-counter.success {
            background: var(--success-color);
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner-border {
            width: 40px;
            height: 40px;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.8rem;
            }

            .step-indicator {
                flex-direction: column;
            }

            .step-indicator::before {
                display: none;
            }

            .step-item {
                margin-bottom: 20px;
            }

            .class-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .class-capacity {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container-main">
        <h1 class="page-title">📋 Đăng Ký Nhập Học</h1>

        <!-- Step Indicator -->
        <div class="card">
            <div class="card-body">
                <div class="step-indicator">
                    <div class="step-item active" id="step1-indicator">
                        <div class="step-number">1</div>
                        <div class="step-title">Chọn Trường</div>
                    </div>
                    <div class="step-item" id="step2-indicator">
                        <div class="step-number">2</div>
                        <div class="step-title">Chọn Môn</div>
                    </div>
                    <div class="step-item" id="step3-indicator">
                        <div class="step-number">3</div>
                        <div class="step-title">Chọn Lớp</div>
                    </div>
                    <div class="step-item" id="step4-indicator">
                        <div class="step-number">4</div>
                        <div class="step-title">Xác Nhận</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 1: Chọn Trường -->
        <div class="card" id="step1">
            <div class="card-header">
                <i class="bi bi-building"></i> Bước 1: Chọn Trường
            </div>
            <div class="card-body">
                <div id="loading-step1" class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>

                <div id="alert-chua-dau" class="alert alert-warning-custom" style="display: none;">
                    <strong>⚠️ Thông báo:</strong> Bạn chưa trúng tuyển vào trường nào. Vui lòng chờ thông báo từ nhà trường.
                </div>

                <div id="alert-huong-dan" class="alert alert-info-custom" style="display: none;">
                    <strong>ℹ️ Hướng dẫn:</strong> Chọn một trường mà bạn trúng tuyển để tiếp tục đăng ký nhập học.
                </div>

                <div id="danh-sach-truong"></div>
            </div>
        </div>

        <!-- STEP 2: Chọn Môn -->
        <div class="card" id="step2" style="display: none;">
            <div class="card-header">
                <i class="bi bi-book"></i> Bước 2: Chọn Môn Học
            </div>
            <div class="card-body">
                <div class="alert alert-info-custom">
                    <strong>ℹ️ Hướng dẫn:</strong> Chọn tổng cộng <strong>4 môn</strong> (bao gồm tất cả 8 môn bắt buộc + 4 môn tự chọn, mỗi nhóm ít nhất 1 môn).
                </div>

                <div class="subject-counter">
                    Đã chọn: <span id="subject-count">0</span>/4 môn
                </div>

                <div id="danh-sach-mon"></div>
            </div>
        </div>

        <!-- STEP 3: Chọn Lớp -->
        <div class="card" id="step3" style="display: none;">
            <div class="card-header">
                <i class="bi bi-door-open"></i> Bước 3: Chọn Lớp 10
            </div>
            <div class="card-body">
                <div class="alert alert-info-custom">
                    <strong>ℹ️ Hướng dẫn:</strong> Chọn lớp 10 mà bạn muốn nhập học. Chỉ hiển thị những lớp còn chỗ trống.
                </div>

                <div id="loading-step3" class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>

                <div id="danh-sach-lop"></div>
            </div>
        </div>

        <!-- STEP 4: Xác Nhận -->
        <div class="card" id="step4" style="display: none;">
            <div class="card-header">
                <i class="bi bi-check-circle"></i> Bước 4: Xác Nhận Đăng Ký
            </div>
            <div class="card-body">
                <div class="alert alert-success-custom">
                    <strong>✓ Thông tin của bạn:</strong> Vui lòng kiểm tra lại trước khi xác nhận.
                </div>

                <div class="confirmation-box">
                    <div class="confirmation-item">
                        <span class="confirmation-label">🏫 Trường Đăng Ký:</span>
                        <span class="confirmation-value" id="confirm-truong">-</span>
                    </div>
                    <div class="confirmation-item">
                        <span class="confirmation-label">📚 Tổ Hợp Môn:</span>
                        <span class="confirmation-value" id="confirm-tohop">-</span>
                    </div>
                    <div class="confirmation-item">
                        <span class="confirmation-label">📝 Môn Học Chọn:</span>
                        <span class="confirmation-value" id="confirm-mon">-</span>
                    </div>
                    <div class="confirmation-item">
                        <span class="confirmation-label">🚪 Lớp Đăng Ký:</span>
                        <span class="confirmation-value" id="confirm-lop">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="navigation-buttons">
            <button class="btn btn-outline-secondary" id="btn-prev" style="display: none;">
                ← Quay Lại
            </button>
            <div style="flex: 1;"></div>
            <button class="btn btn-danger" id="btn-reject" style="display: none;">
                ❌ Từ Chối Nhập Học
            </button>
            <button class="btn btn-primary-custom" id="btn-next">
                Tiếp Tục →
            </button>
            <button class="btn btn-success" id="btn-confirm" style="display: none;">
                ✓ Xác Nhận Đăng Ký
            </button>
        </div>
    </div>

    <!-- Modal: Từ Chối Nhập Học -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Từ Chối Nhập Học</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn <strong>từ chối</strong> nhập học tại <strong><span id="reject-truong-name"></span></strong>?</p>
                    <p class="text-muted">Thao tác này không thể hoàn tác.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger" id="btn-reject-confirm">Đồng ý Từ Chối</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Xác Nhận Đăng Ký -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Xác Nhận Đăng Ký Nhập Học</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn <strong>xác nhận</strong> đăng ký nhập học?</p>
                    <p class="text-muted">Sau khi xác nhận, bạn sẽ là học sinh chính thức của trường.</p>
                    <div id="final-confirmation"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-success" id="btn-confirm-final">Xác Nhận Đăng Ký</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        let currentStep = 1;
        let selectedSchool = null;
        let selectedToHop = null;
        let selectedSubjects = [];
        let selectedClass = null;

        // ===== LOAD DANH SÁCH TRƯỜNG =====
        async function loadDanhSachTruong() {
            try {
                const response = await fetch(`${BASE_URL}/ThisinhNhaphoc/getDanhSachTruongApi`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });

                const data = await response.json();

                if (!data.success) {
                    alert('Lỗi: ' + data.message);
                    return;
                }

                renderDanhSachTruong(data.data);
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
            }
        }


        // 1. Sửa hàm render để bỏ phần hiển thị tổ hợp thừa và data-ma-to-hop
        function renderDanhSachTruong(danh_sach) {
            const container = document.getElementById('danh-sach-truong');
            const alertChuaDau = document.getElementById('alert-chua-dau');
            const alertHuongDan = document.getElementById('alert-huong-dan');
            
            if (!danh_sach.dau || danh_sach.dau.length === 0) {
                alertChuaDau.style.display = 'block';
                alertHuongDan.style.display = 'none';
                container.innerHTML = '';
                return;
            }

            alertChuaDau.style.display = 'none';
            alertHuongDan.style.display = 'block';

            let html = '<h5 class="mb-3">🎯 Các Trường Trúng Tuyển:</h5>';
            danh_sach.dau.forEach(school => {
                html += `
                    <div class="school-item" data-ma-truong="${school.ma_truong}">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h6 class="mb-1 fw-bold text-primary">${school.ten_truong}</h6>
                                <small class="text-muted">Điểm chuẩn: ${school.tong_diem}</small>
                            </div>
                            <span class="school-status status-dau">✓ Đậu</span>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;

            // Add event listeners
            document.querySelectorAll('.school-item').forEach(item => {
                item.addEventListener('click', function() {
                    selectSchool(this);
                });
            });
        }

        // 2. Sửa hàm selectSchool (chỉ cần lấy mã trường)
        function selectSchool(element) {
            document.querySelectorAll('.school-item').forEach(item => item.classList.remove('selected'));
            element.classList.add('selected');

            selectedSchool = {
                ma_truong: element.dataset.maTruong,
                ten_truong: element.querySelector('h6').textContent
            };
            // Không cần selectedToHop nữa
            document.getElementById('btn-next').disabled = false;
        }

        // 3. Sửa hàm loadMonHoc (Gọi API không cần tham số)
        async function loadMonHoc() {
            try {
                // Gọi API lấy tất cả môn (không gửi body gì cả)
                const response = await fetch(`${BASE_URL}/ThisinhNhaphoc/getMonHocApi`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });
                
                const result = await response.json();
                
                if (!result.success || !result.data) {
                    alert('Lỗi: Không thể tải danh sách môn học.');
                    return;
                }

                renderMonHoc(result.data);
                
                // Chuyển bước sau khi load xong
                // Lưu ý: Đừng gọi goToStep(2) ở đây nếu hàm này được gọi BỞI goToStep(2)
                // Nếu hàm này chạy độc lập thì ok.
            } catch (e) {
                console.error("Error loadMonHoc:", e);
                alert('Lỗi tải môn học: ' + e.message);
            }
        }

        function displayTuChonGroup(containerId, subjects, groupName) {
            const container = document.getElementById(containerId);
            if (!container) {
                console.error(`Container ${containerId} not found!`);
                return;
            }

            container.innerHTML = (subjects || []).map(mon => `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="${mon.ma_mon_hoc}" 
                        id="mon_${mon.ma_mon_hoc}" onchange="updateCounter()">
                    <label class="form-check-label" for="mon_${mon.ma_mon_hoc}">
                        ${mon.ten_mon_hoc}
                    </label>
                </div>
            `).join('');
        }

        // function renderMonHoc(mon_hoc) {
        //     const container = document.getElementById('danh-sach-mon');
        //     let html = '';

        //     // --- 1. Môn Bắt Buộc (Giữ nguyên hiển thị Badge) ---
        //     if (mon_hoc.bat_buoc && mon_hoc.bat_buoc.length > 0) {
        //         html += `
        //             <div class="subject-group">
        //                 <div class="group-title">📌 Môn Bắt Buộc (${mon_hoc.bat_buoc.length} môn)</div>
        //                 <div class="d-flex flex-wrap gap-2 mb-3">
        //         `;
        //         mon_hoc.bat_buoc.forEach(subject => {
        //             html += `<span class="badge bg-secondary p-2" style="font-size: 14px;">${subject.ten_mon_hoc}</span>`;
        //         });
        //         html += `</div></div>`;
        //     }

        //     // --- 2. Các nhóm Tự Chọn (SỬA: Radio -> Checkbox) ---
        //     const electiveGroups = {
        //         'tu_chon_khtn': { title: '🔬 Tự Chọn - KHTN', subjects: mon_hoc.tu_chon_khtn || [] },
        //         'tu_chon_khxh': { title: '📖 Tự Chọn - KHXH', subjects: mon_hoc.tu_chon_khxh || [] },
        //         'tu_chon_cn_nt': { title: '🎨 Tự Chọn - CN-NT', subjects: mon_hoc.tu_chon_cn_nt || [] }
        //     };

        //     Object.entries(electiveGroups).forEach(([key, group]) => {
        //         if (group.subjects.length > 0) {
        //             html += `<div class="subject-group">
        //                 <div class="group-title">${group.title} (Chọn ít nhất 1)</div>`;

        //             group.subjects.forEach(subject => {
        //                 html += `
        //                     <div class="subject-item">
        //                         <input class="form-check-input" type="checkbox" name="mon_tu_chon" 
        //                                id="mon_${subject.ma_mon_hoc}" 
        //                                value="${subject.ma_mon_hoc}" 
        //                                data-group="${key}">
        //                         <label class="form-check-label ms-2" for="mon_${subject.ma_mon_hoc}">${subject.ten_mon_hoc}</label>
        //                     </div>
        //                 `;
        //             });

        //             html += '</div>';
        //         }
        //     });

        //     container.innerHTML = html;

        //     // Add event listeners cho Checkbox
        //     document.querySelectorAll('input[name="mon_tu_chon"]').forEach(checkbox => {
        //         checkbox.addEventListener('change', function() {
        //             updateSubjectSelection();
        //         });
        //     });

        //     updateSubjectSelection(); 
        // }
        function renderMonHoc(mon_hoc) {
            const container = document.getElementById('danh-sach-mon');
            let html = '';

            // --- 1. THÊM PHẦN GỢI Ý COMBO TẠI ĐÂY ---
            html += `
                <div class="alert alert-warning mb-4">
                    <h5 class="alert-heading fw-bold mb-2">📌 DANH SÁCH TỔ HỢP MÔN NHÀ TRƯỜNG ĐANG ĐÀO TẠO</h5>
                    <p class="mb-2">Học sinh vui lòng chọn đúng <strong>4 môn</strong> theo một trong các công thức dưới đây để đảm bảo có lớp học:</p>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card h-100 border-primary bg-light">
                                <div class="card-header bg-primary text-white fw-bold">Combo 1 (Tự nhiên 1)</div>
                                <ul class="list-group list-group-flush small">
                                    <li class="list-group-item">🔹 Vật lí</li>
                                    <li class="list-group-item">🔹 Hóa học</li>
                                    <li class="list-group-item">🔸 GD Kinh tế & PL</li>
                                    <li class="list-group-item">🔻 Tin học</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card h-100 border-success bg-light">
                                <div class="card-header bg-success text-white fw-bold">Combo 2 (Tự nhiên 2)</div>
                                <ul class="list-group list-group-flush small">
                                    <li class="list-group-item">🔹 Hóa học</li>
                                    <li class="list-group-item">🔹 Sinh học</li>
                                    <li class="list-group-item">🔸 Địa lí</li>
                                    <li class="list-group-item">🔻 Tin học</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card h-100 border-warning bg-light">
                                <div class="card-header bg-warning text-dark fw-bold">Combo 3 (Xã hội - CN)</div>
                                <ul class="list-group list-group-flush small">
                                    <li class="list-group-item">🔹 Vật lí</li>
                                    <li class="list-group-item">🔸 Địa lí</li>
                                    <li class="list-group-item">🔸 GD Kinh tế & PL</li>
                                    <li class="list-group-item">🔻 Công nghệ</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // --- 2. Tiếp tục render Môn Bắt Buộc & Tự Chọn như cũ ---
            if (mon_hoc.bat_buoc && mon_hoc.bat_buoc.length > 0) {
                html += `
                    <div class="subject-group mt-4">
                        <div class="group-title">📌 Môn Bắt Buộc (${mon_hoc.bat_buoc.length} môn)</div>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                `;
                mon_hoc.bat_buoc.forEach(subject => {
                    html += `<span class="badge bg-secondary p-2" style="font-size: 14px;">${subject.ten_mon_hoc}</span>`;
                });
                html += `</div></div>`;
            }

            // Render các nhóm Tự Chọn (Checkbox)
            const electiveGroups = {
                'tu_chon_khtn': { title: '🔬 Tự Chọn - KHTN', subjects: mon_hoc.tu_chon_khtn || [] },
                'tu_chon_khxh': { title: '📖 Tự Chọn - KHXH', subjects: mon_hoc.tu_chon_khxh || [] },
                'tu_chon_cn_nt': { title: '🎨 Tự Chọn - CN-NT', subjects: mon_hoc.tu_chon_cn_nt || [] }
            };

            Object.entries(electiveGroups).forEach(([key, group]) => {
                if (group.subjects.length > 0) {
                    html += `<div class="subject-group">
                        <div class="group-title">${group.title}</div>`;

                    group.subjects.forEach(subject => {
                        html += `
                            <div class="subject-item">
                                <input class="form-check-input" type="checkbox" name="mon_tu_chon" 
                                       id="mon_${subject.ma_mon_hoc}" 
                                       value="${subject.ma_mon_hoc}" 
                                       data-group="${key}">
                                <label class="form-check-label ms-2" for="mon_${subject.ma_mon_hoc}">${subject.ten_mon_hoc}</label>
                            </div>
                        `;
                    });
                    html += '</div>';
                }
            });

            container.innerHTML = html;

            // Add Listeners
            document.querySelectorAll('input[name="mon_tu_chon"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSubjectSelection();
                });
            });

            updateSubjectSelection();
        }

        
        // function updateSubjectSelection() {
        //     const checkboxes = document.querySelectorAll('input[name="mon_tu_chon"]:checked');
        //     let selectedSubjects = [];
            
        //     // 1. Reset bộ đếm
        //     let countKHTN = 0;
        //     let countKHXH = 0;
        //     let countCNNT = 0;

        //     // 2. Duyệt qua các checkbox đang chọn
        //     checkboxes.forEach(cb => {
        //         const val = parseInt(cb.value);
        //         const group = cb.dataset.group;
                
        //         selectedSubjects.push(val);

        //         // Phân loại đếm
        //         if (group === 'tu_chon_khtn') countKHTN++;
        //         else if (group === 'tu_chon_khxh') countKHXH++;
        //         else if (group === 'tu_chon_cn_nt') countCNNT++;
        //     });

        //     // 3. Cập nhật hiển thị số lượng
        //     const totalSelected = selectedSubjects.length;
        //     document.getElementById('subject-count').textContent = totalSelected;
        //     const counterBox = document.querySelector('.subject-counter');

        //     // --- 4. KIỂM TRA ĐIỀU KIỆN (VALIDATION) ---
        //     let isValid = false;
        //     let msg = "";
        //     let isError = false; // Cờ báo lỗi

        //     // Rule 1: Kiểm tra Max 2 trước (để báo lỗi cụ thể cho user)
        //     if (countKHTN > 2) {
        //         msg = "⚠️ Nhóm KHTN chỉ được chọn tối đa 2 môn!";
        //         isError = true;
        //     } else if (countKHXH > 2) {
        //         msg = "⚠️ Nhóm KHXH chỉ được chọn tối đa 2 môn!";
        //         isError = true;
        //     } else if (countCNNT > 2) {
        //         msg = "⚠️ Nhóm CN-NT chỉ được chọn tối đa 2 môn!";
        //         isError = true;
        //     }
        //     // Rule 2: Kiểm tra tổng số lượng
        //     else if (totalSelected !== 4) {
        //         if (totalSelected > 4) {
        //             msg = `⚠️ Quá số lượng! Chỉ được chọn 4 môn. (Đang chọn: ${totalSelected})`;
        //             isError = true;
        //         } else {
        //             // Chưa đủ thì chỉ nhắc nhẹ, chưa gọi là lỗi
        //             msg = `(Đang chọn: ${totalSelected}/4 môn)`; 
        //         }
        //     } 
        //     // Rule 3: Kiểm tra Min 1 (Mỗi nhóm phải có ít nhất 1)
        //     else if (countKHTN < 1) {
        //         msg = "⚠️ Thiếu môn nhóm KHTN (Cần ít nhất 1)";
        //         isError = true;
        //     } else if (countKHXH < 1) {
        //         msg = "⚠️ Thiếu môn nhóm KHXH (Cần ít nhất 1)";
        //         isError = true;
        //     } else if (countCNNT < 1) {
        //         msg = "⚠️ Thiếu môn nhóm CN-NT (Cần ít nhất 1)";
        //         isError = true;
        //     } 
        //     // Tất cả OK
        //     else {
        //         isValid = true;
        //         msg = "Hợp lệ! ✅ Nhấn Tiếp tục";
        //     }

        //     // --- 5. CẬP NHẬT GIAO DIỆN ---
            
        //     // Tìm hoặc tạo thẻ hiển thị lỗi (nếu chưa có)
        //     let msgBox = document.getElementById('msg-validate');
        //     if (!msgBox) {
        //         msgBox = document.createElement('span');
        //         msgBox.id = 'msg-validate';
        //         msgBox.className = 'ms-2 fw-bold';
        //         counterBox.parentNode.insertBefore(msgBox, counterBox.nextSibling);
        //     }
            
        //     msgBox.textContent = msg;

        //     // Xử lý màu sắc và nút bấm
        //     const btnNext = document.getElementById('btn-next');
        //     counterBox.classList.remove('warning', 'bg-danger', 'bg-success');
        //     msgBox.className = 'ms-2 fw-bold'; // Reset class text

        //     if (isValid) {
        //         counterBox.classList.add('bg-success');
        //         msgBox.classList.add('text-success');
        //         btnNext.disabled = false;
        //     } else {
        //         btnNext.disabled = true;
        //         if (isError) {
        //             counterBox.classList.add('bg-danger');
        //             msgBox.classList.add('text-danger');
        //         } else {
        //             counterBox.classList.add('warning');
        //             msgBox.classList.add('text-muted');
        //         }
        //     }
        // }
        function updateSubjectSelection() {
            const checkboxes = document.querySelectorAll('input[name="mon_tu_chon"]:checked');
            
            // --- SỬA Ở ĐÂY: Bỏ chữ 'let' đi để cập nhật vào biến toàn cục ---
            selectedSubjects = []; 
            
            // 1. Reset bộ đếm
            let countKHTN = 0;
            let countKHXH = 0;
            let countCNNT = 0;

            // 2. Duyệt qua các checkbox đang chọn
            checkboxes.forEach(cb => {
                const val = parseInt(cb.value);
                const group = cb.dataset.group;
                
                selectedSubjects.push(val);

                // Phân loại đếm
                if (group === 'tu_chon_khtn') countKHTN++;
                else if (group === 'tu_chon_khxh') countKHXH++;
                else if (group === 'tu_chon_cn_nt') countCNNT++;
            });

            // 3. Cập nhật hiển thị số lượng
            const totalSelected = selectedSubjects.length;
            document.getElementById('subject-count').textContent = totalSelected;
            const counterBox = document.querySelector('.subject-counter');

            // --- 4. KIỂM TRA ĐIỀU KIỆN (VALIDATION) ---
            let isValid = false;
            let msg = "";
            let isError = false;

            // Rule 1: Kiểm tra Max 2 trước
            if (countKHTN > 2) {
                msg = "⚠️ Nhóm KHTN chỉ được chọn tối đa 2 môn!";
                isError = true;
            } else if (countKHXH > 2) {
                msg = "⚠️ Nhóm KHXH chỉ được chọn tối đa 2 môn!";
                isError = true;
            } else if (countCNNT > 2) {
                msg = "⚠️ Nhóm CN-NT chỉ được chọn tối đa 2 môn!";
                isError = true;
            }
            // Rule 2: Kiểm tra tổng số lượng
            else if (totalSelected !== 4) {
                if (totalSelected > 4) {
                    msg = `⚠️ Quá số lượng! (Đang chọn: ${totalSelected})`;
                    isError = true;
                } else {
                    msg = `(Đang chọn: ${totalSelected}/4 môn)`; 
                }
            } 
            // Rule 3: Kiểm tra Min 1
            else if (countKHTN < 1) {
                msg = "⚠️ Thiếu môn nhóm KHTN";
                isError = true;
            } else if (countKHXH < 1) {
                msg = "⚠️ Thiếu môn nhóm KHXH";
                isError = true;
            } else if (countCNNT < 1) {
                msg = "⚠️ Thiếu môn nhóm CN-NT";
                isError = true;
            } 
            // Tất cả OK
            else {
                isValid = true;
                msg = "Hợp lệ! ✅ Nhấn Tiếp tục";
            }

            // --- 5. CẬP NHẬT GIAO DIỆN ---
            let msgBox = document.getElementById('msg-validate');
            if (!msgBox) {
                msgBox = document.createElement('span');
                msgBox.id = 'msg-validate';
                msgBox.className = 'ms-2 fw-bold';
                counterBox.parentNode.insertBefore(msgBox, counterBox.nextSibling);
            }
            msgBox.textContent = msg;

            const btnNext = document.getElementById('btn-next');
            counterBox.classList.remove('warning', 'bg-danger', 'bg-success');
            msgBox.className = 'ms-2 fw-bold';

            if (isValid) {
                counterBox.classList.add('bg-success');
                msgBox.classList.add('text-success');
                btnNext.disabled = false;
            } else {
                btnNext.disabled = true;
                if (isError) {
                    counterBox.classList.add('bg-danger');
                    msgBox.classList.add('text-danger');
                } else {
                    counterBox.classList.add('warning');
                    msgBox.classList.add('text-muted');
                }
            }
        }

        async function loadChonMonDaSave() {
            try {
                const response = await fetch(`${BASE_URL}/ThisinhNhaphoc/getChonMonDaSaveApi`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });

                const data = await response.json();

                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(item => {
                        const radio = document.getElementById(`mon_${item.ma_mon_hoc}`);
                        if (radio) radio.checked = true;
                    });
                    updateSubjectSelection();
                }
            } catch (error) {
                console.error('Error loading saved subjects:', error);
            }
        }

        // ===== LOAD DANH SÁCH LỚP =====
        async function loadDanhSachLop() {
            try {
                document.getElementById('loading-step3').style.display = 'block';

                const response = await fetch(`${BASE_URL}/ThisinhNhaphoc/getDanhSachLopApi`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        ma_truong: selectedSchool.ma_truong,
                        // ma_to_hop_mon: selectedToHop, <--- BỎ DÒNG NÀY
                        danh_sach_ma_mon: selectedSubjects // <--- THÊM DÒNG NÀY
                    })
                });

                const data = await response.json();

                if (!data.success) {
                    alert('Lỗi: ' + data.message);
                    // Nếu lỗi (không tìm thấy lớp), quay lại bước chọn môn
                    goToStep(2); 
                    return;
                }

                // Cập nhật lại selectedToHop từ server trả về (để dùng cho bước Xác nhận cuối cùng)
                if (data.ma_to_hop_mon) {
                    selectedToHop = data.ma_to_hop_mon;
                }

                renderDanhSachLop(data.data);
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
            } finally {
                document.getElementById('loading-step3').style.display = 'none';
            }
        }

        function renderDanhSachLop(danh_sach) {
            const container = document.getElementById('danh-sach-lop');

            if (!danh_sach || danh_sach.length === 0) {
                container.innerHTML = '<div class="alert alert-warning">Hiện chưa có lớp nào phù hợp với tổ hợp này.</div>';
                document.getElementById('btn-next').disabled = true;
                return;
            }

            // Tạo danh sách tên lớp
            const tenLops = danh_sach.map(lop => `<strong>${lop.ten_lop}</strong>`).join(', ');

            let html = `
                <div class="alert alert-success">
                    <h5 class="alert-heading"><i class="bi bi-check-circle-fill"></i> Có lớp phù hợp!</h5>
                    <p>Dựa trên tổ hợp môn bạn chọn, bạn sẽ được xếp vào một trong các lớp sau:</p>
                    <div class="p-3 bg-white rounded shadow-sm border">
                        <span class="fs-5 text-primary">${tenLops}</span>
                    </div>
                    <hr>
                    <p class="mb-0 small text-muted">
                        * Lưu ý: Việc xếp lớp cụ thể sẽ do <strong>Ban Giám Hiệu</strong> nhà trường quyết định sau khi bạn xác nhận nhập học.
                    </p>
                </div>
            `;

            container.innerHTML = html;

            // Mở nút tiếp tục luôn (không cần chờ user chọn lớp nữa)
            document.getElementById('btn-next').disabled = false;
        }

        function selectClass(element) {
            document.querySelectorAll('.class-item').forEach(item => {
                item.classList.remove('selected');
            });
            element.classList.add('selected');

            selectedClass = {
                ma_lop: element.dataset.maLop,
                ten_lop: element.querySelector('.class-name').textContent
            };

            document.getElementById('btn-next').disabled = false;
        }

        // ===== NAVIGATION =====
        function goToStep(step) {
            document.querySelectorAll('.card[id^="step"]').forEach(card => {
                card.style.display = 'none';
            });
            document.getElementById(`step${step}`).style.display = 'block';

            // Update step indicators
            for (let i = 1; i <= 4; i++) {
                const indicator = document.getElementById(`step${i}-indicator`);
                indicator.classList.remove('active', 'completed');
                if (i === step) {
                    indicator.classList.add('active');
                } else if (i < step) {
                    indicator.classList.add('completed');
                }
            }

            // Update buttons
            document.getElementById('btn-prev').style.display = step > 1 ? 'block' : 'none';
            document.getElementById('btn-next').style.display = step < 4 ? 'block' : 'none';
            document.getElementById('btn-confirm').style.display = step === 4 ? 'block' : 'none';
            document.getElementById('btn-reject').style.display = step > 1 && step < 4 ? 'block' : 'none';

            if (step === 2) {
                loadMonHoc();
            } else if (step === 3) {
                loadDanhSachLop();
            } else if (step === 4) {
                prepareConfirmation();
            }

            currentStep = step;
        }

        function prepareConfirmation() {
            document.getElementById('confirm-truong').textContent = selectedSchool.ten_truong;
            
            // Hiện tên tổ hợp (nếu có lưu) hoặc hiện số môn
            const soMon = document.getElementById('subject-count').textContent;
            document.getElementById('confirm-mon').textContent = `${soMon} môn đã chọn`;

            // Chỗ này sửa thành thông báo chờ xếp lớp
            document.getElementById('confirm-lop').innerHTML = '<span class="badge bg-warning text-dark">Đang chờ nhà trường xếp lớp</span>';
            
            // Ẩn dòng Tổ hợp môn nếu không cần thiết hoặc để mặc định
            document.getElementById('confirm-tohop').textContent = "Theo nguyện vọng đã chọn";
        }

        // ===== SAVE CHỌN MÔN =====
        // async function saveChonMon() {
        //     try {
        //         const response = await fetch(`${BASE_URL}/ThisinhNhaphoc/saveChonMonApi`, {
        //             method: 'POST',
        //             headers: { 'Content-Type': 'application/json' },
        //             body: JSON.stringify({
        //                 // ma_to_hop_mon: selectedToHop,  <-- XÓA DÒNG NÀY ĐI
        //                 danh_sach_ma_mon: selectedSubjects
        //             })
        //         });

        //         const data = await response.json();

        //         if (!data.success) {
        //             alert('Lỗi: ' + data.message);
        //             return false;
        //         }

        //         return true;
        //     } catch (error) {
        //         console.error('Error:', error);
        //         alert('Có lỗi xảy ra');
        //         return false;
        //     }
        // }
        async function saveChonMon() {
            try {
                const response = await fetch(`${BASE_URL}/ThisinhNhaphoc/saveChonMonApi`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        // FIX: Không gửi ma_to_hop_mon nữa, chỉ gửi danh sách môn
                        danh_sach_ma_mon: selectedSubjects
                    })
                });

                const data = await response.json();

                if (!data.success) {
                    alert('Lỗi: ' + data.message);
                    return false;
                }

                return true;
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
                return false;
            }
        }

        // ===== XÁC NHẬN NHẬP HỌC =====
        async function xacNhanNhapHoc() {
            try {
                const response = await fetch(`${BASE_URL}/ThisinhNhaphoc/xacNhanNhapHocApi`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        ma_truong: selectedSchool.ma_truong,
                        // ma_lop: selectedClass.ma_lop
                        ma_to_hop_mon: selectedToHop
                    })
                });

                const data = await response.json();

                if (!data.success) {
                    alert('Lỗi: ' + data.message);
                    return false;
                }

                return true;
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
                return false;
            }
        }

        // ===== TỪ CHỐI NHẬP HỌC =====
        async function tuChoiNhapHoc() {
            try {
                const response = await fetch(`${BASE_URL}/ThisinhNhaphoc/tuChoiNhapHocApi`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        ma_truong: selectedSchool.ma_truong
                    })
                });

                const data = await response.json();

                if (!data.success) {
                    alert('Lỗi: ' + data.message);
                    return false;
                }

                return true;
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
                return false;
            }
        }

        // ===== EVENT LISTENERS =====
        document.getElementById('btn-next').addEventListener('click', async function() {
            if (currentStep === 1) {
                if (!selectedSchool) {
                    alert('Vui lòng chọn trường');
                    return;
                }
                goToStep(2);
            } else if (currentStep === 2) {
                // Kiểm tra lại lần cuối
                if (selectedSubjects.length !== 4) {
                    alert('Vui lòng chọn đủ 4 môn học (Mỗi nhóm ít nhất 1 môn, tối đa 2 môn)');
                    return;
                }
                // Nếu logic đúng thì lưu và đi tiếp
                if (await saveChonMon()) {
                    goToStep(3);
                }
            } else if (currentStep === 3) {
                // if (!selectedClass) {
                //     alert('Vui lòng chọn lớp');
                //     return;
                // }
                goToStep(4);
            }
        });

        document.getElementById('btn-prev').addEventListener('click', function() {
            if (currentStep > 1) {
                goToStep(currentStep - 1);
            }
        });

        document.getElementById('btn-reject').addEventListener('click', function() {
            document.getElementById('reject-truong-name').textContent = selectedSchool.ten_truong;
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        });

        document.getElementById('btn-reject-confirm').addEventListener('click', async function() {
            if (await tuChoiNhapHoc()) {
                alert('Từ chối nhập học thành công');
                bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                location.reload();
            }
        });

        document.getElementById('btn-confirm').addEventListener('click', function() {
            new bootstrap.Modal(document.getElementById('confirmModal')).show();
        });

        document.getElementById('btn-confirm-final').addEventListener('click', async function() {
            if (await xacNhanNhapHoc()) {
                alert('Xác nhận nhập học thành công!');
                bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
                location.reload();
            }
        });

        // ===== INITIALIZATION =====
        document.addEventListener('DOMContentLoaded', function() {
            loadDanhSachTruong();
            document.getElementById('btn-next').disabled = true;
        });
    </script>
</body>
</html>
