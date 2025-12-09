<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Bài Tập & Chấm Điểm</title>
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
                    </div>
                    <div class="p-2 bg-light rounded text-secondary border">
                        <small class="fw-bold text-uppercase">Mô tả / Đề bài:</small><br>
                        <?php echo nl2br(htmlspecialchars($data['bai_tap_info']['mo_ta'] ?? 'Không có mô tả')); ?>
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
                    data-bs-toggle="modal" 
                    data-bs-target="#modalSuaBaiTap">
                <i class="bi bi-pencil-square"></i> Chỉnh sửa đề bài
            </button>
            
            <button class="btn btn-danger fw-bold shadow-sm ms-2" 
                    onclick="deleteBaiTap(<?php echo $data['bai_tap_info']['ma_bai_tap'] ?? 0; ?>)">
                <i class="bi bi-trash"></i> Xóa bài tập này
            </button>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card shadow-sm">
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
                    <div class="card-body p-0">
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
                                                    <?php 
                                                        if (!empty($hs['ngay_nop'])) {
                                                            echo date('H:i d/m', strtotime($hs['ngay_nop'])); 
                                                        } else {
                                                            echo '<span class="text-primary fst-italic">Đang làm...</span>';
                                                        }
                                                    ?>
                                                </small>

                                                <?php if (!empty($hs['gio_bat_dau_lam_bai']) && !empty($hs['ngay_nop'])): ?>
                                                    <small class="badge bg-light text-secondary border mt-1 fw-normal">
                                                        <i class="bi bi-hourglass-split"></i> 
                                                        <?php echo isset($hs['thoi_gian_lam_bai_phut']) ? $hs['thoi_gian_lam_bai_phut'] . ' phút' : '?'; ?>
                                                    </small>
                                                <?php endif; ?>
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
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white fw-bold py-3">
                        <i class="bi bi-x-circle-fill me-2"></i> DANH SÁCH CHƯA NỘP
                    </div>
                    <div class="card-body p-0">
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
                        <input type="hidden" name="ma_bai_tap" value="<?php echo $data['bai_tap_info']['ma_bai_tap'] ?? ''; ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên bài tập:</label>
                            <input type="text" class="form-control" name="ten_bai_tap" 
                                   value="<?php echo htmlspecialchars($data['bai_tap_info']['ten_bai_tap'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Hạn nộp:</label>
                            <?php 
                                $hanNopVal = isset($data['bai_tap_info']['han_nop']) ? date('Y-m-d\TH:i', strtotime($data['bai_tap_info']['han_nop'])) : '';
                            ?>
                            <input type="datetime-local" class="form-control" name="han_nop" 
                                   value="<?php echo $hanNopVal; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô tả / Ghi chú:</label>
                            <textarea class="form-control" name="mo_ta" rows="3"><?php echo htmlspecialchars($data['bai_tap_info']['mo_ta'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">File đính kèm (Đề bài):</label>
                            <?php if(!empty($data['bai_tap_info']['file_dinh_kem'])): ?>
                                <div class="alert alert-success py-2 small mb-2"><i class="bi bi-check-circle"></i> Đang có file cũ. Tải lên file mới sẽ ghi đè.</div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="file_dinh_kem_new">
                        </div>

                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary fw-bold px-4">Lưu Thay Đổi</button>
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
        // --- CONFIG & GLOBAL VARS ---
        // Lấy thông tin lớp/môn an toàn (tránh lỗi Undefined variable trong PHP)
        const maLop = "<?php echo $data['bai_tap_info']['ma_lop'] ?? 0; ?>";
        const maMonHoc = "<?php echo $data['bai_tap_info']['ma_mon_hoc'] ?? 0; ?>";
        
        // Khởi tạo Modal Object
        const modalChamDiemEl = document.getElementById('modalChamDiem');
        const modalChamDiem = new bootstrap.Modal(modalChamDiemEl);

        // --- HÀM 1: XÓA BÀI TẬP ---
        function deleteBaiTap(maBaiTap) {
            if (!confirm('CẢNH BÁO QUAN TRỌNG:\n\nBạn có chắc chắn muốn xóa bài tập này?\nHành động này sẽ xóa toàn bộ dữ liệu bài làm của học sinh (nếu có) và không thể hoàn tác.')) {
                return;
            }

            const formData = new FormData();
            formData.append('ma_bai_tap', maBaiTap);

            fetch("<?php echo BASE_URL; ?>/giaovien/xoaBaiTapApi", {
                method: 'POST',
                body: formData
            })
            .then(response => response.text()) 
            .then(text => {
                try {
                    const result = JSON.parse(text); 
                    if (result.success) {
                        alert('✅ ' + result.message);
                        window.location.href = "<?php echo BASE_URL; ?>/giaovien/danhsachbaitap/" + maLop + "/" + maMonHoc;
                    } else {
                        alert('⚠️ KHÔNG THỂ XÓA:\n' + result.message);
                    }
                } catch (e) {
                    console.error("Server Error:", text);
                    alert('❌ LỖI HỆ THỐNG (Xem Console).');
                }
            })
            .catch(err => alert('❌ Lỗi kết nối mạng: ' + err));
        }

        // --- HÀM 2: SỬA BÀI TẬP ---
        document.getElementById('formSuaBaiTap').addEventListener('submit', async function(e) {
            e.preventDefault();
            if(!confirm('Bạn có chắc chắn muốn cập nhật thông tin bài tập này?')) return;

            const formData = new FormData(this);

            try {
                const response = await fetch('<?php echo BASE_URL; ?>/giaovien/suaBaiTapApi', {
                    method: 'POST',
                    body: formData
                });
                const text = await response.text();
                const result = JSON.parse(text);
                
                if (result.success) {
                    alert('✅ Cập nhật thành công!');
                    location.reload(); 
                } else {
                    alert('❌ Lỗi: ' + result.message);
                }
            } catch (err) {
                console.error(err);
                alert('❌ Lỗi Server hoặc Kết nối.');
            }
        });

        // --- HÀM 3: MỞ MODAL CHẤM ĐIỂM (Load dữ liệu) ---
        function moModalChamDiem(maBaiNop) {
            // Reset UI
            document.getElementById('cham_ten_hs').textContent = 'Đang tải...';
            document.getElementById('cham_noi_dung_bai').innerHTML = '<div class="text-center mt-5"><div class="spinner-border text-primary"></div><br>Đang lấy dữ liệu bài làm...</div>';
            document.getElementById('cham_ma_bai_nop').value = maBaiNop;
            document.getElementById('cham_diem_so').value = '';
            document.getElementById('cham_nhan_xet').value = '';
            
            modalChamDiem.show();

            // Gọi API
            const formData = new FormData();
            formData.append('ma_bai_nop', maBaiNop);

            fetch("<?php echo BASE_URL; ?>/giaovien/layBaiLamHocSinhApi", {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    const data = res.data;
                    
                    // Điền thông tin
                    document.getElementById('cham_ten_hs').textContent = data.ho_ten;
                    document.getElementById('cham_diem_so').value = data.diem_so; 
                    document.getElementById('cham_nhan_xet').value = data.nhan_xet || '';

                    // Xử lý hiển thị bài làm
                    let htmlContent = '';
                    let hasContent = false;

                    // A. Nội dung Tự Luận / Trắc Nghiệm (Cột noi_dung_tra_loi)
                    if (data.noi_dung_tra_loi && data.noi_dung_tra_loi.trim() !== '') {
                        hasContent = true;
                        // Check xem có phải JSON trắc nghiệm không
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
                            // Tự luận thường
                            htmlContent += `
                                <div class="mb-4">
                                    <h6 class="fw-bold text-primary border-bottom pb-2">Bài làm (Văn bản):</h6>
                                    <div class="p-4 bg-white border rounded shadow-sm pre-wrap" style="font-size: 1.1rem; line-height: 1.6;">${data.noi_dung_tra_loi}</div>
                                </div>
                            `;
                        }
                    }

                    // B. File Đính Kèm (Cột file_dinh_kem)
                    if (data.file_dinh_kem && data.file_dinh_kem.trim() !== '') {
                        hasContent = true;
                        const fileUrl = "<?php echo BASE_URL; ?>/" + data.file_dinh_kem;
                        const ext = fileUrl.split('.').pop().toLowerCase();

                        htmlContent += `
                            <div class="mb-3">
                                <h6 class="fw-bold text-info border-bottom pb-2 mt-4">Tệp đính kèm:</h6>
                        `;

                        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                            // Ảnh -> Hiện luôn
                            htmlContent += `
                                <div class="text-center bg-dark p-2 rounded">
                                    <img src="${fileUrl}" alt="Bài làm">
                                </div>
                            `;
                        } else {
                            // File khác -> Nút tải
                            htmlContent += `
                                <div class="d-flex align-items-center p-4 border rounded bg-light shadow-sm">
                                    <i class="bi bi-file-earmark-arrow-down fs-1 text-primary me-3"></i>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold text-break">${data.file_dinh_kem.split('/').pop()}</h6>
                                        <small class="text-muted">Nhấn vào nút bên cạnh để tải về chấm.</small>
                                    </div>
                                    <a href="${fileUrl}" target="_blank" class="btn btn-primary fw-bold">
                                        <i class="bi bi-download"></i> Tải Về
                                    </a>
                                </div>
                            `;
                        }
                        htmlContent += `</div>`;
                    }

                    if (!hasContent) {
                        htmlContent = `
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-x display-1 opacity-25"></i>
                                <p class="mt-3 fs-5">Học sinh nộp bài trống.</p>
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

        // --- HÀM 4: LƯU ĐIỂM SỐ ---
        document.getElementById('formLuuDiem').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = this.querySelector('button[type="submit"]');
            const oldText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang lưu...';

            const formData = new FormData(this);

            fetch("<?php echo BASE_URL; ?>/giaovien/luuDiemSoApi", {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    alert('✅ ' + res.message);
                    location.reload(); // F5 để cập nhật danh sách bên ngoài
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
    </script>
</body>
</html>