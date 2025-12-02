<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Điểm Danh - <?php echo $data['lop_info']['ten_lop']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f8ff; }
        .custom-radio { width: 1.2em; height: 1.2em; cursor: pointer; }
        .history-row { cursor: pointer; }
        .history-row:hover { background-color: #f0f0f0; }
        #henGioFields { display: none; }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <header class="mb-4 p-4 bg-white rounded-3 shadow-sm d-flex justify-content-between align-items-center">
            <div>
                <h1 class="fw-bold text-primary">
                    <i class="bi bi-check-circle-fill me-2"></i> 
                    Lớp: <?php echo htmlspecialchars($data['lop_info']['ten_lop']); ?>
                </h1>
                <p class="text-muted fs-5 mb-0">Môn học: <?php echo htmlspecialchars($data['lop_info']['ten_mon_hoc']); ?></p>
            </div>
            <a href="<?php echo BASE_URL; ?>/giaovien/diemdanh" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Quay Lại Chọn Lớp</a>
        </header>
        
        <div id="notification" class="alert" style="display: none;"></div>

        <div class="card shadow-sm">
            <div class="card-body">
                <button class="btn btn-warning w-100 mb-3 fw-bold p-3" onclick="openCreateSessionModal()">
                    <i class="bi bi-plus-circle"></i> Tạo Phiên Điểm Danh Mới
                </button>
                <h6 class="fw-bold text-secondary border-bottom pb-2">Lịch Sử Điểm Danh (Bấm vào hàng để xem chi tiết)</h6>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Tiêu Đề / Loại Phiên</th>
                                <th>Thời Gian</th>
                                <th>Kết Quả</th>
                                <th>Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody id="tableHistoryBody">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTaoPhien" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold" id="titleTaoPhien">Thiết Lập Phiên Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="notificationModalTaoPhien" class="alert" style="display: none;"></div>
                    <form id="formTaoPhien">
                        <div class="mb-3">
                            <label for="inputTieuDe" class="form-label">Tiêu đề:</label>
                            <input type="text" class="form-control" id="inputTieuDe" required>
                        </div>
                        <div class="mb-3">
                            <label for="selectLoaiPhien" class="form-label">Loại điểm danh:</label>
                            <select class="form-select" id="selectLoaiPhien">
                                <option value="GiaoVien">1. Giáo viên điểm danh thủ công (mặc định)</option>
                                <option value="HocSinh">2. Học sinh tự điểm danh (hẹn giờ)</option>
                            </select>
                        </div>
                        <div id="henGioFields" class="p-3 bg-light rounded border mb-3">
                            <h6 class="text-primary">Thiết lập thời gian (cho Học sinh)</h6>
                            <div class="mb-3">
                                <label for="inputThoiGianMo" class="form-label">Thời gian Mở:</label>
                                <input type="datetime-local" class="form-control" id="inputThoiGianMo">
                                <div class="form-text">Bỏ trống để bắt đầu ngay.</div>
                            </div>
                            <div class="mb-3">
                                <label for="inputThoiGianDong" class="form-label">Thời gian Đóng:</label>
                                <input type="datetime-local" class="form-control" id="inputThoiGianDong">
                                <div class="form-text">Bỏ trống để đặt 15 phút.</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="inputGhiChu" class="form-label">Ghi chú (nếu có):</label>
                            <textarea class="form-control" id="inputGhiChu" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary w-100 fw-bold" id="btnSubmitTaoPhien" onclick="submitTaoPhien()">
                        Bắt Đầu Điểm Danh Thủ Công >>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalChiTietPhien" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white" id="modalDiemDanhHeader">
                    <h5 class="modal-title fw-bold" id="titleDiemDanh"><i class="bi bi-list-check"></i> Bảng Điểm Danh</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="phienInfoBox" class="alert alert-secondary d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Trạng Thái:</strong> <span id="phienInfoTrangThai" class="fw-bold"></span><br>
                            <strong>Thời gian:</strong> <span id="phienInfoThoiGian"></span>
                        </div>
                        <button class="btn btn-danger" id="btnKetThucPhien" onclick="submitKetThucPhien()">
                            <i class="bi bi-stop-circle-fill"></i> Kết Thúc Phiên Ngay
                        </button>
                    </div>
                    <div id="notificationModal" class="alert" style="display: none;"></div>
                    <div class="d-flex justify-content-end mb-2 gap-2" id="diemDanhToolbar" style="display: none;">
                        <button class="btn btn-sm btn-outline-success" onclick="checkAll('CoMat')">Tất cả Có Mặt</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="checkAll('VangKhongPhep')">Tất cả Vắng KP</button>
                    </div>
                    <form id="formDiemDanh">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light text-center" id="theadDiemDanh"></thead>
                                <tbody id="tbodyDiemDanh"></tbody>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btnCloseDiemDanh">Đóng</button>
                    <button type="button" class="btn btn-success fw-bold px-4" id="btnSubmitLuuDiemDanh" onclick="submitLuuDiemDanh()">LƯU KẾT QUẢ</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const BASE_URL = "<?php echo BASE_URL ?? ''; ?>";
        
        // --- DỮ LIỆU ĐƯỢC NẠP SẴN TỪ CONTROLLER ---
        const currentMaLop = <?php echo $data['lop_info']['ma_lop']; ?>;
        const currentMaMonHoc = <?php echo $data['lop_info']['ma_mon_hoc']; ?>;
        const currentTenLop = '<?php echo htmlspecialchars($data['lop_info']['ten_lop']); ?>';
        const currentTenMon = '<?php echo htmlspecialchars($data['lop_info']['ten_mon_hoc']); ?>';
        const currentSiSo = <?php echo $data['lop_info']['si_so']; ?>;
        
        // Dùng cho modal tạo phiên thủ công
        const currentStudentList = <?php echo json_encode($data['danh_sach_hs']); ?>; 
        
        // Dùng để render bảng lịch sử lần đầu
        let preloadedHistory = <?php echo json_encode($data['lich_su']); ?>;

        // ---------------------------------------------

        let currentMaPhien = 0;
        let modalTao, modalChiTietPhien;
        
        const notification = document.getElementById('notification');
        const modalNotification = document.getElementById('notificationModal');

        document.addEventListener('DOMContentLoaded', function() {
            // Khởi tạo 2 modal (Modal 1 không còn)
            modalTao = new bootstrap.Modal(document.getElementById('modalTaoPhien'));
            modalChiTietPhien = new bootstrap.Modal(document.getElementById('modalChiTietPhien'));

            // Gắn sự kiện cho dropdown chọn loại phiên
            document.getElementById('selectLoaiPhien').addEventListener('change', function() {
                const henGioFields = document.getElementById('henGioFields');
                const btnSubmit = document.getElementById('btnSubmitTaoPhien');
                if (this.value === 'HocSinh') {
                    henGioFields.style.display = 'block';
                    btnSubmit.textContent = 'Tạo Phiên Tự Động';
                    btnSubmit.classList.remove('btn-primary');
                    btnSubmit.classList.add('btn-info');
                } else {
                    henGioFields.style.display = 'none';
                    btnSubmit.textContent = 'Bắt Đầu Điểm Danh Thủ Công >>';
                    btnSubmit.classList.add('btn-primary');
                    btnSubmit.classList.remove('btn-info');
                }
            });

            // Tải bảng lịch sử ngay lập tức
            renderHistoryTable(preloadedHistory);
        });

        // 1. (Helper) Vẽ bảng lịch sử
        function renderHistoryTable(lich_su) {
            const tbodyHistory = document.getElementById('tableHistoryBody');
            tbodyHistory.innerHTML = '';
            if(!lich_su || lich_su.length === 0) {
                tbodyHistory.innerHTML = '<tr><td colspan="5" class="text-center">Chưa có phiên điểm danh nào.</td></tr>';
                return;
            }

            lich_su.forEach(phien => {
                let loaiPhienText = phien.loai_phien == 'GiaoVien' 
                    ? '<span class="badge bg-secondary">GV Điểm Danh</span>' 
                    : '<span class="badge bg-info">HS Tự Động</span>';
                
                let thoiGianText = 'N/A';
                if (phien.loai_phien == 'HocSinh' && phien.thoi_gian_mo) {
                    let mo = new Date(phien.thoi_gian_mo).toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'});
                    let dong = new Date(phien.thoi_gian_dong).toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'});
                    thoiGianText = `${mo} - ${dong}`;
                }

                let trangThaiClass = '';
                let trangThaiText = phien.trang_thai_phien;
                switch(phien.trang_thai_phien) {
                    case 'DangDiemDanh': trangThaiClass = 'text-success fw-bold'; trangThaiText = 'Đang diễn ra'; break;
                    case 'HetThoiGian': trangThaiClass = 'text-muted'; trangThaiText = 'Đã kết thúc'; break;
                    case 'ChuaMo': trangThaiClass = 'text-primary'; trangThaiText = 'Sắp diễn ra'; break;
                }

                tbodyHistory.innerHTML += `
                    <tr class="history-row" onclick="openSessionDetailModal(${phien.ma_phien})">
                        <td>${new Date(phien.ngay_diem_danh).toLocaleDateString('vi-VN')}</td>
                        <td>
                            <div class="fw-bold">${phien.tieu_de}</div>
                            <div>${loaiPhienText}</div>
                        </td>
                        <td>${thoiGianText}</td>
                        <td class="text-center fw-bold">${phien.da_diem_danh} / ${currentSiSo}</td>
                        <td class="${trangThaiClass}">${trangThaiText}</td>
                    </tr>
                `;
            });
        }
        
        // (Hàm này MỚI, dùng để tải lại lịch sử sau khi tạo/lưu)
        async function refreshHistoryTable() {
             try {
                // Chúng ta không thể gọi lại API 'getChiTietLopApi'
                // Thay vào đó, chúng ta sẽ fetch lại chính trang này (cách 1)
                // Hoặc chúng ta tạo 1 API riêng chỉ để lấy lịch sử (cách 2)
                
                // Cách 2: Tạo API riêng (Giả sử bạn sẽ tạo API 'getLichSuApi')
                // const formData = new FormData();
                // formData.append('ma_lop', currentMaLop);
                // formData.append('ma_mon_hoc', currentMaMonHoc);
                // const res = await fetch(BASE_URL + '/giaovien/getLichSuApi', { method: 'POST', body: formData });
                // const data = await res.json();
                // if (data.success) renderHistoryTable(data.lich_su);
                
                // Cách 3: Tải lại trang (Đơn giản nhất)
                location.reload();

            } catch (err) {
                console.error("Lỗi tải lại lịch sử:", err);
            }
        }


        // 2. Mở Modal 2 (Tạo Phiên)
        function openCreateSessionModal() {
            const today = new Date().toLocaleDateString('vi-VN');
            document.getElementById('inputTieuDe').value = `Điểm danh ${currentTenMon} - ${currentTenLop} - ${today}`;
            document.getElementById('inputGhiChu').value = '';
            document.getElementById('selectLoaiPhien').value = 'GiaoVien';
            document.getElementById('selectLoaiPhien').dispatchEvent(new Event('change'));
            document.getElementById('notificationModalTaoPhien').style.display = 'none';
            modalTao.show();     
        }

        // 3. Submit Tạo Phiên (Xử lý 2 luồng)
        async function submitTaoPhien() {
            const btn = document.getElementById('btnSubmitTaoPhien');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
            
            const formData = new FormData(document.getElementById('formTaoPhien'));
            formData.append('ma_lop', currentMaLop);
            formData.append('tieu_de', document.getElementById('inputTieuDe').value);
            formData.append('loai_phien', document.getElementById('selectLoaiPhien').value);
            formData.append('thoi_gian_mo', document.getElementById('inputThoiGianMo').value);
            formData.append('thoi_gian_dong', document.getElementById('inputThoiGianDong').value);
            formData.append('ghi_chu', document.getElementById('inputGhiChu').value);
            
            try {
                const res = await fetch(BASE_URL + '/giaovien/taoPhienApi', { method: 'POST', body: formData });
                const data = await res.json();

                if(data.success) {
                    modalTao.hide();
                    
                    if (data.ma_phien_moi) {
                        // LUỒNG 1: GV TỰ ĐIỂM DANH
                        // Mở modal 3 ở chế độ nhập liệu
                        await openSessionDetailModal(data.ma_phien_moi, true); 
                    } else {
                        // LUỒNG 2: HS TỰ ĐIỂM DANH
                        notification.className = 'alert alert-success';
                        notification.textContent = 'Đã tạo phiên điểm danh tự động cho học sinh!';
                        notification.style.display = 'block';
                        setTimeout(() => notification.style.display = 'none', 3000);
                        await refreshHistoryTable(); // Tải lại lịch sử
                    }
                } else { throw new Error(data.message || 'Lỗi không rõ'); }
            } catch (err) {
                const noti = document.getElementById('notificationModalTaoPhien');
                noti.className = 'alert alert-danger';
                noti.textContent = 'Lỗi: ' + err.message;
                noti.style.display = 'block';
            } finally {
                btn.disabled = false;
                // Text của nút sẽ được reset khi mở lại modal
            }
        }
        
        // 4. Mở Modal 3 (Chi Tiết Phiên)
        async function openSessionDetailModal(maPhien, isNewManualEntry = false) {
            // (Hàm này giữ nguyên 100% logic như file cũ)
            // ... (Copy toàn bộ nội dung hàm 'openSessionDetailModal' từ file cũ vào đây) ...
            
            currentMaPhien = maPhien;
            const tbody = document.getElementById('tbodyDiemDanh');
            tbody.innerHTML = '<tr><td colspan="6" class="text-center p-5">Đang tải chi tiết phiên...</td></tr>';
            
            modalChiTietPhien.show(); 
            modalNotification.style.display = 'none';

            try {
                const formData = new FormData();
                formData.append('ma_phien', maPhien);
                
                const res = await fetch(BASE_URL + '/giaovien/getChiTietPhienApi', 
                { method: 'POST', body: formData,
                    headers: { // <-- THÊM CÁI NÀY
                        'X-Requested-With': 'XMLHttpRequest'
                    }

                });
                const data = await res.json();
                
                if (data.success) {
                    const phien_info = data.phien_info;
                    const chi_tiet = data.danh_sach_chi_tiet;
                    
                    document.getElementById('titleDiemDanh').textContent = `Chi Tiết Phiên: ${phien_info.tieu_de}`;
                    
                    let trangThaiText = phien_info.trang_thai_phien;
                    let trangThaiClass = 'alert-secondary';
                    if (trangThaiText == 'DangDiemDanh') { trangThaiText = 'ĐANG DIỄN RA'; trangThaiClass = 'alert-success'; }
                    if (trangThaiText == 'HetThoiGian') { trangThaiText = 'ĐÃ KẾT THÚC'; trangThaiClass = 'alert-danger'; }
                    if (trangThaiText == 'ChuaMo') { trangThaiText = 'SẮP DIỄN RA'; trangThaiClass = 'alert-primary'; }
                    document.getElementById('phienInfoBox').className = `alert ${trangThaiClass} d-flex justify-content-between align-items-center`;
                    document.getElementById('phienInfoTrangThai').textContent = trangThaiText;

                    let thoiGianText = `Tạo lúc ${new Date(phien_info.ngay_diem_danh + ' ' + phien_info.thoi_gian).toLocaleString('vi-VN')}`;
                    if(phien_info.loai_phien == 'HocSinh') {
                         thoiGianText = `Mở: ${new Date(phien_info.thoi_gian_mo).toLocaleString('vi-VN')} - Đóng: ${new Date(phien_info.thoi_gian_dong).toLocaleString('vi-VN')}`;
                    }
                    document.getElementById('phienInfoThoiGian').textContent = thoiGianText;
                    
                    const btnKetThuc = document.getElementById('btnKetThucPhien');
                    btnKetThuc.style.display = (phien_info.trang_thai_phien != 'HetThoiGian') ? 'block' : 'none';

                    if (phien_info.loai_phien == 'GiaoVien') {
                        // Nếu là phiên mới (isNewManualEntry=true) hoặc đang diễn ra, cho phép sửa
                        const isReadOnly = (phien_info.trang_thai_phien == 'HetThoiGian'); 
                        
                        // QUAN TRỌNG: Khi tạo mới (isNewManualEntry), danh sách `chi_tiet` từ API trả về
                        // sẽ chưa có HS (vì chưa ai được điểm danh).
                        // Chúng ta phải dùng `currentStudentList` đã tải sẵn.
                        const listToRender = isNewManualEntry ? currentStudentList : chi_tiet;
                        
                        renderManualEntryTable(listToRender, isReadOnly, isNewManualEntry);
                        
                        document.getElementById('diemDanhToolbar').style.display = isReadOnly ? 'none' : 'flex';
                        document.getElementById('btnSubmitLuuDiemDanh').style.display = isReadOnly ? 'none' : 'block';
                    } 
                    else {
                        renderReviewTable(chi_tiet, phien_info.si_so);
                        document.getElementById('diemDanhToolbar').style.display = 'none';
                        document.getElementById('btnSubmitLuuDiemDanh').style.display = 'none';
                    }
                } else {
                    throw new Error(data.message || 'Lỗi không rõ');
                }
            } catch(err) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger p-5">Lỗi: ${err.message}</td></tr>`;
            }
        }

        // 5a. (Helper) Vẽ bảng ĐIỂM DANH THỦ CÔNG (cho GV)
        function renderManualEntryTable(studentList, isReadOnly = false, isNew = false) {
            // (Hàm này giữ nguyên 100% logic như file cũ)
            // ... (Copy toàn bộ nội dung hàm 'renderManualEntryTable' từ file cũ vào đây) ...
            
            const thead = document.getElementById('theadDiemDanh');
            const tbody = document.getElementById('tbodyDiemDanh');
            tbody.innerHTML = '';
            
            thead.innerHTML = `
                <tr>
                    <th>STT</th>
                    <th>Họ Tên</th>
                    <th class="text-success">Có Mặt</th>
                    <th class="text-danger">Vắng KP</th>
                    <th class="text-warning">Vắng CP</th>
                    <th class="text-info">Đi Trễ</th>
                </tr>
            `;

            const disabledAttr = isReadOnly ? 'disabled' : ''; 
            
            studentList.forEach((hs, index) => {
                const maHs = hs.ma_nguoi_dung;
                
                // NẾU LÀ PHIÊN MỚI: mặc định 'CoMat'
                // NẾU LÀ XEM LẠI: lấy 'trang_thai_diem_danh' (có thể null nếu GV chưa điểm danh em đó)
                // => Mặc định là 'CoMat' nếu 'trang_thai_diem_danh' là null.
                const trang_thai_cu = hs.trang_thai_diem_danh ?? 'CoMat'; 
                
                let rowHtml = `<tr id="row-hs-${maHs}">
                    <td class="text-center">${index + 1}</td>
                    <td class="text-start fw-medium">${hs.ho_ten}</td>`;

                const statuses = ['CoMat', 'VangKhongPhep', 'VangCoPhep', 'DiTre'];
                statuses.forEach((status) => {
                    const checked = (trang_thai_cu == status) ? 'checked' : '';
                    rowHtml += `
                    <td class="text-center">
                        <div class="form-check d-flex justify-content-center">
                            <input class="form-check-input custom-radio" type="radio" 
                                   name="diemdanh[${maHs}]" value="${status}" 
                                   ${checked} ${disabledAttr}>
                        </div>
                    </td>`;
                });
                rowHtml += '</tr>';
                tbody.innerHTML += rowHtml;
            });
        }

        // 5b. (Helper) Vẽ bảng XEM KẾT QUẢ (cho HS tự điểm danh)
        function renderReviewTable(studentList, siSo) {
            // (Hàm này giữ nguyên 100% logic như file cũ)
            // ... (Copy toàn bộ nội dung hàm 'renderReviewTable' từ file cũ vào đây) ...
            
             const thead = document.getElementById('theadDiemDanh');
            const tbody = document.getElementById('tbodyDiemDanh');
            tbody.innerHTML = '';

            thead.innerHTML = `
                <tr>
                    <th>STT</th>
                    <th>Họ Tên</th>
                    <th>Trạng Thái</th>
                    <th>Thời gian điểm danh</th>
                </tr>
            `;

            let daDiemDanh = 0;
            studentList.forEach((hs, index) => {
                let trangThaiText = '<span class="badge bg-danger">CHƯA ĐIỂM DANH</span>';
                let thoiGianText = '---';
                
                if (hs.trang_thai_diem_danh) {
                    daDiemDanh++;
                    trangThaiText = '<span class="badge bg-success">ĐÃ CÓ MẶT</span>';
                    thoiGianText = hs.thoi_gian_nop 
                        ? new Date(hs.thoi_gian_nop).toLocaleString('vi-VN')
                        : 'N/A';
                }

                tbody.innerHTML += `
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td class="fw-medium">${hs.ho_ten}</td>
                        <td class="text-center">${trangThaiText}</td>
                        <td class="text-center">${thoiGianText}</td>
                    </tr>
                `;
            });
        }

        // 6. Helper: Check all
        function checkAll(val) {
            // (Hàm này giữ nguyên)
            document.querySelectorAll(`#formDiemDanh input[value="${val}"]`).forEach(r => {
                if (!r.disabled) { 
                    r.checked = true;
                }
            });
        }

        // 7. Lưu kết quả
        async function submitLuuDiemDanh() {
            // (Hàm này gần như giữ nguyên)
            const btn = document.getElementById('btnSubmitLuuDiemDanh');
            const form = document.getElementById('formDiemDanh');
            const formData = new FormData(form);
            formData.append('ma_phien', currentMaPhien);

            if(!confirm('Xác nhận lưu kết quả điểm danh?')) return;
            
            btn.disabled = true;
            modalNotification.style.display = 'none';

            try {
                const res = await fetch(BASE_URL + '/giaovien/luuDiemDanhApi', { method: 'POST', body: formData });
                const data = await res.json();

                if(data.success) {
                    modalNotification.className = 'alert alert-success';
                    modalNotification.textContent = 'Đã lưu thành công!';
                    modalNotification.style.display = 'block';
                    
                    document.getElementById('phienInfoTrangThai').textContent = 'ĐÃ KẾT THÚC (Đã lưu)';
                    document.getElementById('phienInfoBox').className = `alert alert-secondary d-flex justify-content-between align-items-center`;
                    document.getElementById('btnKetThucPhien').style.display = 'none';
                    document.getElementById('btnSubmitLuuDiemDanh').style.display = 'none';
                    document.getElementById('diemDanhToolbar').style.display = 'none';
                    document.querySelectorAll('#formDiemDanh input').forEach(ip => ip.disabled = true);

                    // Tải lại lịch sử ở trang chính
                    await refreshHistoryTable(); 

                } else { throw new Error(data.message || 'Lỗi không rõ'); }
            } catch (err) {
                 modalNotification.className = 'alert alert-danger';
                 modalNotification.textContent = 'Lỗi: ' + err.message;
                 modalNotification.style.display = 'block';
                 btn.disabled = false; // Cho phép thử lại nếu lỗi
            }
        }

        // 8. KẾT THÚC PHIÊN
        async function submitKetThucPhien() {
            // (Hàm này gần như giữ nguyên)
            if(!confirm('Bạn có chắc muốn KẾT THÚC phiên điểm danh này ngay lập tức không?')) return;
            
            const btn = document.getElementById('btnKetThucPhien');
            btn.disabled = true;
            
            const formData = new FormData();
            formData.append('ma_phien', currentMaPhien);

            try {
                const res = await fetch(BASE_URL + '/giaovien/ketThucPhienApi', { method: 'POST', body: formData });
                const data = await res.json();
                
                if (data.success) {
                    modalNotification.className = 'alert alert-warning';
                    modalNotification.textContent = 'Đã kết thúc phiên!';
                    modalNotification.style.display = 'block';
                    
                    document.getElementById('phienInfoTrangThai').textContent = 'ĐÃ KẾT THÚC';
                    document.getElementById('phienInfoBox').className = `alert alert-danger d-flex justify-content-between align-items-center`;
                    btn.style.display = 'none';
                    document.getElementById('btnSubmitLuuDiemDanh').style.display = 'none';

                    // Tải lại lịch sử ở trang chính
                    await refreshHistoryTable(); 
                } else { throw new Error(data.message); }
            } catch (err) {
                modalNotification.className = 'alert alert-danger';
                modalNotification.textContent = 'Lỗi: ' + err.message;
                modalNotification.style.display = 'block';
                btn.disabled = false;
            }
        }

    </script>
</body>
</html>