<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Bài Tập - <?php echo htmlspecialchars($data['bai_tap_info']['ten_bai_tap'] ?? ''); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f8ff; font-family: 'Segoe UI', sans-serif; }
        .card { transition: 0.3s; border: none; }
        .card:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .list-group-item { transition: 0.2s; border-left: 3px solid transparent; }
        .list-group-item:hover { background-color: #f8f9fa; border-left-color: #0d6efd; }
        .stt-badge { min-width: 25px; display: inline-block; font-weight: bold; color: #6c757d; }
        /* Style cho phần hiển thị bài làm */
        #cham_noi_dung_bai img { max-width: 100%; height: auto; border-radius: 5px; }
        .pre-wrap { white-space: pre-wrap; word-wrap: break-word; }
        /* Ẩn các tùy chọn nâng cao trong modal sửa */
        #sua_area_TuLuan, #sua_area_TracNghiem, #sua_area_UploadFile { display: none; }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        
        <header class="mb-4 p-4 bg-white rounded-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="fw-bold text-primary mb-2">
                        <i class="bi bi-file-earmark-text-fill me-2"></i>
                        <?php echo htmlspecialchars($data['bai_tap_info']['ten_bai_tap'] ?? 'Bài tập không tên'); ?>
                    </h2>
                    <div class="text-muted mb-2">
                        <i class="bi bi-calendar-event"></i> Hạn nộp: 
                        <span class="fw-bold text-danger">
                            <?php echo date('H:i - d/m/Y', strtotime($data['bai_tap_info']['han_nop'])); ?>
                        </span>
                        <span class="badge bg-secondary ms-2"><?php echo $data['bai_tap_info']['loai_bai_tap']; ?></span>
                    </div>
                    <div class="p-3 bg-light rounded text-secondary border mt-2">
                        <small class="fw-bold text-uppercase text-primary">Mô tả / Đề bài:</small><br>
                        <div class="pre-wrap mt-1"><?php echo htmlspecialchars($data['bai_tap_info']['mo_ta'] ?? 'Không có mô tả'); ?></div>
                        
                        <?php if(!empty($data['bai_tap_info']['file_dinh_kem'])): ?>
                            <div class="mt-2 pt-2 border-top">
                                <a href="<?php echo BASE_URL . '/' . $data['bai_tap_info']['file_dinh_kem']; ?>" target="_blank" class="btn btn-sm btn-outline-primary fw-bold">
                                    <i class="bi bi-paperclip"></i> Tải file đề bài
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <?php 
                        $backLop = $data['bai_tap_info']['ma_lop'] ?? 0;
                        $backMon = $data['bai_tap_info']['ma_mon_hoc'] ?? 0;
                    ?>
                    <a href="<?php echo BASE_URL . '/giaovien/danhsachbaitap/' . $backLop . '/' . $backMon; ?>"
                       class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        </header>
        
        <div class="mb-4">
            <button class="btn btn-warning fw-bold text-dark shadow-sm" 
                    onclick="openModalSuaBaiTap(<?php echo $data['bai_tap_info']['ma_bai_tap']; ?>)">
                <i class="bi bi-pencil-square"></i> Chỉnh sửa đề bài
            </button>
            
            <button class="btn btn-danger fw-bold shadow-sm ms-2" 
                    onclick="deleteBaiTap(<?php echo $data['bai_tap_info']['ma_bai_tap'] ?? 0; ?>)">
                <i class="bi bi-trash"></i> Xóa bài tập này
            </button>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white fw-bold d-flex justify-content-between align-items-center py-3">
                        <span><i class="bi bi-check-circle-fill me-2"></i> DANH SÁCH ĐÃ NỘP</span>
                        <span class="badge bg-white text-success rounded-pill fs-6 px-3">
                            <?php 
                                $countDaNop = 0;
                                foreach ($data['danh_sach_nop_bai'] as $hs) if ($hs['ma_bai_nop'] !== null) $countDaNop++;
                                echo $countDaNop;
                            ?>
                        </span>
                    </div>
                    <div class="card-body p-0 overflow-auto" style="max-height: 600px;">
                        <ul class="list-group list-group-flush">
                            <?php
                            $daNop = false;
                            $stt = 1; 
                            foreach ($data['danh_sach_nop_bai'] as $hs):
                                if ($hs['ma_bai_nop'] !== null):
                                    $daNop = true;
                            ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                        <div class="d-flex align-items-start">
                                            <span class="stt-badge pt-1 text-success fs-5"><?php echo $stt++; ?></span>
                                            <div class="ms-2">
                                                <div class="fw-bold text-dark mb-1 fs-6">
                                                    <?php echo htmlspecialchars($hs['ho_ten']); ?>
                                                </div>
                                                <small class="d-block text-muted">
                                                    <i class="bi bi-clock-history"></i> Nộp: 
                                                    <?php echo !empty($hs['ngay_nop']) ? date('H:i d/m', strtotime($hs['ngay_nop'])) : 'Đang làm...'; ?>
                                                </small>
                                            </div>
                                        </div>

                                        <div class="text-end">
                                            <div class="mb-2">
                                                <span class="badge <?php echo $hs['trang_thai'] == 'HoanThanh' ? 'bg-success' : 'bg-warning text-dark'; ?> px-3 py-2 rounded-pill">
                                                    <?php echo $hs['trang_thai'] == 'HoanThanh' ? ('Điểm: <b>' . $hs['diem_so'] . '</b>') : 'Chưa chấm'; ?>
                                                </span>
                                            </div>
                                            <button class="btn btn-sm btn-outline-primary fw-bold" 
                                                    onclick="moModalChamDiem(<?php echo $hs['ma_bai_nop']; ?>)">
                                                <i class="bi bi-pencil-fill"></i> Chấm bài
                                            </button>
                                        </div>
                                    </li>
                            <?php
                                endif;
                            endforeach;
                            
                            if (!$daNop): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                    Chưa có học sinh nào nộp bài.
                                </div>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-danger text-white fw-bold py-3">
                        <i class="bi bi-x-circle-fill me-2"></i> DANH SÁCH CHƯA NỘP
                    </div>
                    <div class="card-body p-0 overflow-auto" style="max-height: 600px;">
                        <ul class="list-group list-group-flush">
                            <?php
                            $chuaNop = false;
                            $stt2 = 1; 
                            foreach ($data['danh_sach_nop_bai'] as $hs):
                                if ($hs['ma_bai_nop'] === null):
                                    $chuaNop = true;
                            ?>
                                    <li class="list-group-item py-3 d-flex align-items-center">
                                        <span class="stt-badge text-danger"><?php echo $stt2++; ?></span>
                                        <span class="ms-2 fw-medium text-secondary">
                                            <i class="bi bi-person-fill me-2"></i>
                                            <?php echo htmlspecialchars($hs['ho_ten']); ?>
                                        </span>
                                    </li>
                            <?php
                                endif;
                            endforeach;

                            if (!$chuaNop): ?>
                                <div class="text-center py-5 text-success">
                                    <i class="bi bi-emoji-sunglasses fs-1 d-block mb-3"></i>
                                    Tuyệt vời! Tất cả học sinh đã nộp bài.
                                </div>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSuaBaiTap" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square"></i> Cập Nhật Bài Tập</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formSuaBaiTap" enctype="multipart/form-data">
                        <input type="hidden" id="sua_ma_bai_tap" name="ma_bai_tap">
                        <input type="hidden" id="sua_json_trac_nghiem" name="json_trac_nghiem">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên bài tập:</label>
                            <input type="text" class="form-control" id="sua_ten_bai_tap" name="ten_bai_tap" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Hạn nộp:</label>
                            <input type="datetime-local" class="form-control" id="sua_han_nop" name="han_nop" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô tả / Ghi chú:</label>
                            <textarea class="form-control" id="sua_mo_ta" name="mo_ta" rows="3"></textarea>
                        </div>

                        <div id="sua_area_TuLuan" class="p-3 bg-light border rounded mb-3">
                            <label class="form-label fw-bold text-primary">Nội dung đề bài chi tiết:</label>
                            <textarea class="form-control" id="sua_noi_dung_tu_luan" name="noi_dung_tu_luan" rows="5"></textarea>
                        </div>

                        <div id="sua_area_TracNghiem" class="p-3 bg-light border rounded mb-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-danger">Thời gian làm bài (phút):</label>
                                <input type="number" class="form-control" id="sua_thoi_gian_lam_bai" name="thoi_gian_lam_bai">
                            </div>
                            <div class="alert alert-warning small">
                                <i class="bi bi-exclamation-triangle"></i> <strong>Lưu ý:</strong> Để sửa câu hỏi trắc nghiệm, vui lòng nhập lại đề bài và đáp án đúng theo cú pháp chuẩn vào ô bên dưới. Hệ thống sẽ tạo lại bộ câu hỏi mới.
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Dán lại nội dung Đề (Câu hỏi + A,B,C,D):</label>
                                <textarea class="form-control" id="sua_raw_trac_nghiem" rows="6" placeholder="Câu 1: ..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Chuỗi đáp án đúng mới:</label>
                                <input type="text" class="form-control" id="sua_chuoi_dap_an" placeholder="VD: ABCD...">
                            </div>
                        </div>

                        <div id="sua_area_UploadFile" class="p-3 bg-light border rounded mb-3">
                            <div class="row g-2">
                                <div class="col-md-8">
                                    <label class="form-label-sm">Loại file cho phép:</label>
                                    <input type="text" class="form-control form-control-sm" id="sua_loai_file" name="loai_file_cho_phep">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-sm">Max size (MB):</label>
                                    <input type="number" class="form-control form-control-sm" id="sua_max_size" name="dung_luong_toi_da">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">File đính kèm (Đề bài):</label>
                            <div id="link_file_cu" class="mb-2"></div>
                            <input type="file" class="form-control" name="file_dinh_kem_new">
                            <small class="text-muted">Chỉ chọn file nếu muốn thay đổi file cũ.</small>
                        </div>

                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary fw-bold px-4" id="btnLuuSua">Lưu Thay Đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalChamDiem" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" style="height: 90vh;">
            <div class="modal-content h-100">
                <div class="modal-header bg-primary text-white py-2">
                    <h5 class="modal-title fs-6">
                        <i class="bi bi-person-circle me-2"></i> Chấm bài: 
                        <span id="cham_ten_hs" class="fw-bold text-warning text-uppercase ms-1">---</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0 h-100">
                        <div class="col-md-8 bg-light border-end d-flex flex-column h-100">
                            <div class="p-2 border-bottom bg-white d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-secondary"><i class="bi bi-eye"></i> Xem bài làm</span>
                            </div>
                            <div id="cham_noi_dung_bai" class="flex-grow-1 p-3 overflow-auto">
                                <div class="text-center mt-5 text-muted">Đang tải dữ liệu...</div>
                            </div>
                        </div>

                        <div class="col-md-4 bg-white p-4 overflow-auto">
                            <h5 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="bi bi-pencil-fill"></i> Kết Quả</h5>
                            
                            <form id="formLuuDiem">
                                <input type="hidden" id="cham_ma_bai_nop" name="ma_bai_nop">
                                
                                <div class="mb-4 text-center">
                                    <label class="form-label fw-bold text-secondary">ĐIỂM SỐ</label>
                                    <input type="number" class="form-control form-control-lg text-center fw-bold text-danger border-danger" 
                                           id="cham_diem_so" name="diem_so" step="0.1" min="0" max="10" placeholder="0.0" 
                                           style="font-size: 3rem; height: 80px;" required>
                                    <div class="form-text">Nhập điểm từ 0 đến 10</div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary">Nhận xét / Lời phê:</label>
                                    <textarea class="form-control" id="cham_nhan_xet" name="nhan_xet" rows="6" 
                                              placeholder="Nhập nhận xét chi tiết vào đây..."></textarea>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success btn-lg fw-bold shadow">
                                        <i class="bi bi-save me-2"></i> LƯU KẾT QUẢ
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // --- CONSTANTS ---
        // QUAN TRỌNG: Định nghĩa BASE_URL ngay đầu script để tránh lỗi ReferenceError
        const BASE_URL = "<?php echo BASE_URL; ?>";
        const maLop = "<?php echo $data['bai_tap_info']['ma_lop'] ?? 0; ?>";
        const maMonHoc = "<?php echo $data['bai_tap_info']['ma_mon_hoc'] ?? 0; ?>";
        
        // --- MODAL INSTANCES ---
        const modalChamDiemEl = document.getElementById('modalChamDiem');
        const modalChamDiem = new bootstrap.Modal(modalChamDiemEl);
        
        const modalSuaEl = document.getElementById('modalSuaBaiTap');
        const modalSua = new bootstrap.Modal(modalSuaEl);

        // --- 1. XÓA BÀI TẬP ---
        function deleteBaiTap(maBaiTap) {
            if (!confirm('CẢNH BÁO QUAN TRỌNG:\n\nBạn có chắc chắn muốn xóa bài tập này?\nHành động này sẽ xóa toàn bộ dữ liệu bài làm của học sinh (nếu có) và không thể hoàn tác.')) {
                return;
            }

            const formData = new FormData();
            formData.append('ma_bai_tap', maBaiTap);

            fetch(BASE_URL + "/giaovien/xoaBaiTapApi", {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) 
            .then(result => {
                if (result.success) {
                    alert('✅ ' + result.message);
                    window.location.href = BASE_URL + "/giaovien/danhsachbaitap/" + maLop + "/" + maMonHoc;
                } else {
                    alert('⚠️ KHÔNG THỂ XÓA:\n' + result.message);
                }
            })
            .catch(err => alert('❌ Lỗi kết nối mạng: ' + err));
        }

        // --- 2. SỬA BÀI TẬP: LOAD DỮ LIỆU ---
        function openModalSuaBaiTap(id) {
            // Reset form
            document.getElementById('formSuaBaiTap').reset();
            document.getElementById('sua_area_TuLuan').style.display = 'none';
            document.getElementById('sua_area_TracNghiem').style.display = 'none';
            document.getElementById('sua_area_UploadFile').style.display = 'none';
            
            // Gọi API lấy thông tin
            const fd = new FormData();
            fd.append('ma_bai_tap', id);

            fetch(BASE_URL + '/giaovien/layChiTietBaiTapDeSuaApi', {
                method: 'POST', body: fd
            })
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    const d = res.data;
                    
                    document.getElementById('sua_ma_bai_tap').value = d.ma_bai_tap;
                    document.getElementById('sua_ten_bai_tap').value = d.ten_bai_tap;
                    document.getElementById('sua_mo_ta').value = d.mo_ta;
                    
                    if(d.han_nop) {
                        document.getElementById('sua_han_nop').value = d.han_nop.replace(' ', 'T').substring(0, 16);
                    }

                    const linkDiv = document.getElementById('link_file_cu');
                    if(d.file_dinh_kem) {
                        linkDiv.innerHTML = `<a href="${BASE_URL}/${d.file_dinh_kem}" target="_blank" class="badge bg-info text-decoration-none">File hiện tại</a>`;
                    } else {
                        linkDiv.innerHTML = '<span class="text-muted small">Không có file cũ</span>';
                    }

                    if (d.loai_bai_tap == 'TuLuan') {
                        document.getElementById('sua_area_TuLuan').style.display = 'block';
                        document.getElementById('sua_noi_dung_tu_luan').value = d.noi_dung_tu_luan;
                    }
                    else if (d.loai_bai_tap == 'TracNghiem') {
                        document.getElementById('sua_area_TracNghiem').style.display = 'block';
                        document.getElementById('sua_thoi_gian_lam_bai').value = d.thoi_gian_lam_bai;

                        // --- BỔ SUNG ĐOẠN NÀY ---
                        if (d.json_trac_nghiem) {
                            try {
                                const obj = JSON.parse(d.json_trac_nghiem);
                                let raw = '';
                                let ans = '';
                                obj.questions.forEach((q, idx) => {
                                    raw += `Câu ${idx+1}: ${q.text}\n`;
                                    q.options.forEach(opt => raw += opt + '\n');
                                    ans += q.correct;
                                    raw += '\n';
                                });
                                document.getElementById('sua_raw_trac_nghiem').value = raw.trim();
                                document.getElementById('sua_chuoi_dap_an').value = ans;
                            } catch(e) {
                                document.getElementById('sua_raw_trac_nghiem').value = '';
                                document.getElementById('sua_chuoi_dap_an').value = '';
                            }
                        } else {
                            document.getElementById('sua_raw_trac_nghiem').value = '';
                            document.getElementById('sua_chuoi_dap_an').value = '';
                        }
                    }

                    modalSua.show();
                } else {
                    alert('Lỗi: ' + res.message);
                }
            })
            .catch(err => alert('Lỗi kết nối: ' + err));
        }

        // --- 3. SỬA BÀI TẬP: SUBMIT ---
        document.getElementById('formSuaBaiTap').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Xử lý trắc nghiệm nếu có nhập
            const areaTN = document.getElementById('sua_area_TracNghiem');
            if (areaTN.style.display !== 'none') {
                const rawText = document.getElementById('sua_raw_trac_nghiem').value.trim();
                const ansKey = document.getElementById('sua_chuoi_dap_an').value.trim();
                
                if (rawText && ansKey) {
                    try {
                        const jsonOutput = parseTracNghiemForEdit(rawText, ansKey); 
                        document.getElementById('sua_json_trac_nghiem').value = jsonOutput;
                    } catch (err) {
                        alert('Lỗi Trắc nghiệm: ' + err.message);
                        return;
                    }
                }
            }

            const btn = document.getElementById('btnLuuSua');
            btn.disabled = true;
            btn.innerHTML = 'Đang lưu...';

            const fd = new FormData(this);

            fetch(BASE_URL + '/giaovien/suaBaiTapApi', {
                method: 'POST', body: fd
            })
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    alert('Cập nhật thành công!');
                    location.reload();
                } else {
                    alert('Lỗi: ' + res.message);
                }
            })
            .catch(err => alert('Lỗi: ' + err))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'Lưu Thay Đổi';
            });
        });

        // --- 4. CHẤM ĐIỂM: LOAD & HIỂN THỊ (UI ĐẸP) ---
        function moModalChamDiem(maBaiNop) {
            document.getElementById('cham_ten_hs').textContent = 'Đang tải...';
            document.getElementById('cham_noi_dung_bai').innerHTML = '<div class="text-center mt-5"><div class="spinner-border text-primary"></div><br>Đang lấy dữ liệu bài làm...</div>';
            document.getElementById('cham_ma_bai_nop').value = maBaiNop;
            document.getElementById('cham_diem_so').value = '';
            document.getElementById('cham_nhan_xet').value = '';
            
            modalChamDiem.show();

            const formData = new FormData();
            formData.append('ma_bai_nop', maBaiNop);

            fetch(BASE_URL + "/giaovien/layBaiLamHocSinhApi", {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    const data = res.data;
                    
                    document.getElementById('cham_ten_hs').textContent = data.ho_ten;
                    document.getElementById('cham_diem_so').value = data.diem_so; 
                    document.getElementById('cham_nhan_xet').value = data.nhan_xet || '';

                    let htmlContent = '';
                    let hasContent = false;

                    // A. Nội dung Tự Luận / Trắc Nghiệm (Text)
                    if (data.noi_dung_tra_loi && data.noi_dung_tra_loi.trim() !== '') {
                        hasContent = true;
                        if (data.noi_dung_tra_loi.trim().startsWith('{') && data.noi_dung_tra_loi.includes('"q1"')) {
                             htmlContent += `
                                <div class="alert alert-warning mb-3 shadow-sm">
                                    <i class="bi bi-list-check"></i> <strong>Bài Trắc Nghiệm:</strong> Đây là dữ liệu đáp án hệ thống.
                                </div>
                                <div class="p-3 bg-light border rounded font-monospace small pre-wrap shadow-sm">
                                    ${data.noi_dung_tra_loi}
                                </div>
                            `;
                        } else {
                            htmlContent += `
                                <div class="mb-4">
                                    <h6 class="fw-bold text-primary border-bottom pb-2">Bài làm (Văn bản):</h6>
                                    <div class="p-4 bg-white border rounded shadow-sm pre-wrap" style="font-size: 1.1rem; line-height: 1.6;">${data.noi_dung_tra_loi}</div>
                                </div>
                            `;
                        }
                    }

                    // B. File Đính Kèm (Card UI)
                    if (data.file_dinh_kem && data.file_dinh_kem.trim() !== '') {
                        hasContent = true;
                        const fileUrl = BASE_URL + "/" + data.file_dinh_kem;
                        const ext = fileUrl.split('.').pop().toLowerCase();
                        let rawName = data.file_dinh_kem.split('/').pop(); 

                        htmlContent += `
                            <div class="mb-3">
                                <h6 class="fw-bold text-info border-bottom pb-2 mt-4">
                                    <i class="bi bi-paperclip"></i> Tệp đính kèm:
                                </h6>
                        `;

                        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                            htmlContent += `
                                <div class="text-center bg-dark p-2 rounded shadow-sm">
                                    <img src="${fileUrl}" alt="Bài làm" class="img-fluid" style="max-height: 400px;">
                                </div>
                            `;
                        } else {
                            htmlContent += `
                                <div class="card border-0 shadow-sm bg-light">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="me-3 text-primary">
                                            <i class="bi bi-file-earmark-text-fill" style="font-size: 3rem;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="card-title fw-bold text-dark mb-1">Bài làm của học sinh</h5>
                                            <p class="card-text text-muted small mb-0">
                                                Định dạng: .${ext.toUpperCase()} <br>
                                                <span class="fst-italic text-secondary" style="font-size: 0.75rem;">
                                                    (File hệ thống: ${rawName.substring(0, 30)}...)
                                                </span>
                                            </p>
                                        </div>
                                        <a href="${fileUrl}" target="_blank" class="btn btn-primary fw-bold px-4 py-2">
                                            <i class="bi bi-download"></i> Tải Về
                                        </a>
                                    </div>
                                </div>
                            `;
                        }
                        htmlContent += `</div>`;
                    }

                    if (!hasContent) {
                        htmlContent = `
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-x display-1 opacity-25"></i>
                                <p class="mt-3 fs-5">Học sinh chưa nộp bài hoặc nộp bài trống.</p>
                            </div>
                        `;
                    }

                    document.getElementById('cham_noi_dung_bai').innerHTML = htmlContent;

                } else {
                    alert('Lỗi: ' + res.message);
                    modalChamDiem.hide();
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('cham_noi_dung_bai').innerHTML = '<div class="alert alert-danger">Lỗi kết nối server (Xem Console).</div>';
            });
        }

        // --- 5. LƯU ĐIỂM ---
        document.getElementById('formLuuDiem').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = this.querySelector('button[type="submit"]');
            const oldText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang lưu...';

            const formData = new FormData(this);

            fetch(BASE_URL + "/giaovien/luuDiemSoApi", {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    alert('✅ ' + res.message);
                    location.reload(); 
                } else {
                    alert('❌ Lỗi: ' + res.message);
                }
            })
            .catch(err => alert('Lỗi kết nối: ' + err))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = oldText;
            });
        });

        // Helper: Parser trắc nghiệm cho form sửa
        function parseTracNghiemForEdit(text, key) {
            const blocks = text.split(/(?:Câu|Câu hỏi|\d+)[ \t]*[\d\.:]+/).filter(b => b.trim() !== '');
            const keys = key.toUpperCase().replace(/[^A-D]/g, '').split('');
            if (blocks.length !== keys.length) throw new Error('Số câu hỏi và đáp án không khớp');

            const arr = [];
            for (let i = 0; i < blocks.length; i++) {
                const lines = blocks[i].trim().split('\n').filter(l => l.trim()!=='');
                const q = lines.shift().trim();
                let opts = lines.map(l => l.trim()).filter(l => /^[A-D][\.\)]/i.test(l));
                if(opts.length == 0) opts = lines.map((opt, idx) => `${String.fromCharCode(65 + idx)}. ${opt}`);

                // --- KIỂM TRA ĐỦ 4 ĐÁP ÁN ---
                if (opts.length !== 4) throw new Error(`Câu ${i+1} phải có đủ 4 đáp án (A, B, C, D)!`);

                // --- KIỂM TRA ĐÁP ÁN ĐÚNG HỢP LỆ ---
                if (!['A','B','C','D'].includes(keys[i])) throw new Error(`Đáp án đúng của câu ${i+1} phải là A, B, C hoặc D!`);

                arr.push({ id: i+1, text: q, options: [...new Set(opts)], correct: keys[i] });
            }
            return JSON.stringify({ questions: arr });
        }
        
        // --- 7. SESSION KEEP-ALIVE ---
        // Giúp giáo viên không bị đăng xuất khi đang chấm điểm lâu
        function startSessionKeepAlive(interval) {
            setInterval(async () => {
                try { await fetch(BASE_URL + '/giaovien/ping'); } catch (e) {}
            }, interval);
        }

        document.addEventListener('DOMContentLoaded', (event) => {
            startSessionKeepAlive(60000); // 1 phút
        });
    </script>
</body>
</html>