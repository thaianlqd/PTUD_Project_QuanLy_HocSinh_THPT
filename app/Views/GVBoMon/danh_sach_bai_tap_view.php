<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DS Bài Tập - [<?php echo $data['lop_info']['ten_mon_hoc'] . ' - ' . $data['lop_info']['ten_lop']; ?>]</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style> 
        body { background-color: #f0f8ff; } 
        /* Ẩn các tùy chọn nâng cao ban đầu */
        #optionsTuLuan, #optionsTracNghiem, #optionsUploadFile { display: none; }
        #json_trac_nghiem_debug { max-height: 200px; overflow-y: scroll; background: #222; color: #0f0; font-size: 0.8rem; }
        /* Style cho phần hiển thị bài làm */
        #cham_noi_dung_bai img { max-width: 100%; height: auto; border-radius: 5px; }
        .pre-wrap { white-space: pre-wrap; word-wrap: break-word; }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        
        <header class="mb-4 p-4 bg-white rounded-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="fw-bold text-primary">
                        <i class="bi bi-collection-fill me-2"></i> 
                        Danh Sách Bài Tập 
                        <span class="text-success">[<?php echo htmlspecialchars($data['lop_info']['ten_mon_hoc']) . ' - ' . htmlspecialchars($data['lop_info']['ten_lop']); ?>]</span>
                    </h1>
                    <p class="text-muted mb-0">Quản lý các bài tập đã giao cho lớp.</p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL; ?>/giaovien/baitap" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Quay lại Chọn Lớp
                    </a>
                </div>
            </div>
        </header>

        <div id="formNotification" class="alert" style="display: none;"></div>

        <div class="mb-3">
            <button class="btn btn-lg btn-success fw-bold" 
                data-bs-toggle="modal" 
                data-bs-target="#modalGiaoBai"
                id="btnMoModalGiaoBai">
                <i class="bi bi-plus-circle-fill me-2"></i> Giao Bài Tập Mới
            </button>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tên Bài Tập</th>
                                <th>Loại Bài</th>
                                <th>Ngày Giao</th>
                                <th>Hạn Nộp</th>
                                <th>Thống Kê (Nộp / Sĩ số)</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['danh_sach_bai_tap'])): ?>
                                <tr><td colspan="6" class="text-center text-muted p-4">Chưa giao bài tập nào cho lớp này.</td></tr>
                            <?php endif; ?>

                            <?php foreach ($data['danh_sach_bai_tap'] as $bt): ?>
                                <tr>
                                    <td class="fw-bold">
                                        <a href="<?php echo BASE_URL; ?>/giaovien/chitietbaitap/<?php echo $bt['ma_bai_tap']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($bt['ten_bai_tap']); ?>
                                        </a>
                                    </td>
                                    <td><span class="badge bg-primary-subtle text-primary-emphasis"><?php echo $bt['loai_bai_tap']; ?></span></td>
                                    <td><?php echo date('d/m/Y', strtotime($bt['ngay_giao'])); ?></td>
                                    <td class="text-danger fw-medium"><?php echo date('H:i - d/m/Y', strtotime($bt['han_nop'])); ?></td>
                                    <td>
                                        <span class="fw-bold fs-5 <?php echo ($bt['so_luong_da_nop'] >= $bt['si_so']) ? 'text-success' : 'text-warning'; ?>">
                                            <?php echo $bt['so_luong_da_nop']; ?> / <?php echo $bt['si_so']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo BASE_URL; ?>/giaovien/chitietbaitap/<?php echo $bt['ma_bai_tap']; ?>" class="btn btn-outline-primary" title="Xem chi tiết">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            <button class="btn btn-outline-warning text-dark fw-bold" onclick="openModalSuaBaiTap(<?php echo $bt['ma_bai_tap']; ?>)" title="Sửa bài tập">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalGiaoBai" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold" id="modalTitle">
                        <i class="bi bi-pencil-fill me-2"></i> Giao Bài Tập Mới
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formGiaoBai" enctype="multipart/form-data">
                        <div id="modalFormNotification" class="alert" style="display: none;"></div>
                        <input type="hidden" id="modal_ma_lop" name="ma_lop" value="">
                        <input type="hidden" id="modal_ma_mon_hoc" name="ma_mon_hoc" value="">
                        <input type="hidden" id="json_trac_nghiem_hidden" name="json_trac_nghiem">

                        <div class="mb-3">
                            <label for="ten_bai_tap" class="form-label fw-bold">1. Tên bài tập (bắt buộc):</label>
                            <input type="text" class="form-control" id="ten_bai_tap" name="ten_bai_tap" required>
                        </div>
                        <div class="mb-3">
                            <label for="mo_ta_chung" class="form-label fw-bold">2. Mô tả chung/Yêu cầu:</label>
                            <textarea class="form-control" id="mo_ta_chung" name="mo_ta_chung" rows="2" placeholder="Ví dụ: Các em đọc kỹ đề và nộp bài đúng hạn..."></textarea>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="han_nop" class="form-label fw-bold">3. Hạn nộp (bắt buộc):</label>
                                <input type="datetime-local" class="form-control" id="han_nop" name="han_nop" required>
                            </div>
                            <div class="col-md-6">
                                <label for="file_dinh_kem" class="form-label fw-bold">4. Tệp đính kèm (Đề bài PDF, ...):</label>
                                <input type="file" class="form-control" id="file_dinh_kem" name="file_dinh_kem">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">5. Loại bài nộp của HS (bắt buộc):</label>
                            <select class="form-select" id="loai_bai_tap" name="loai_bai_tap" required>
                                <option value="" selected disabled>-- Chọn loại bài để học sinh nộp --</option>
                                <option value="TuLuan">Tự Luận (Gõ trực tiếp)</option>
                                <option value="UploadFile">Upload File (Nộp 1 file)</option>
                                <option value="TracNghiem">Trắc Nghiệm (Dán Text & Đáp Án)</option>
                            </select>
                        </div>

                        <div id="optionsTuLuan" class="p-3 bg-light rounded border mb-3">
                            <label for="noi_dung_tu_luan" class="form-label fw-bold text-primary">Nội dung đề bài chi tiết (Tự luận):</label>
                            <textarea class="form-control" id="noi_dung_tu_luan" name="noi_dung_tu_luan" rows="5" placeholder="Gõ đề bài chi tiết vào đây..."></textarea>
                        </div>

                        <div id="optionsTracNghiem" class="p-3 bg-light rounded border mb-3">
                            <div class="mb-3">
                                <label for="thoi_gian_lam_bai" class="form-label fw-bold text-danger">Thời gian làm bài (phút):</label>
                                <input type="number" class="form-control" id="thoi_gian_lam_bai" name="thoi_gian_lam_bai" value="45" min="10">
                            </div>
                            <div class="mb-3">
                                <label for="noi_dung_trac_nghiem" class="form-label fw-bold text-danger">Dán nội dung Đề (Câu hỏi và A,B,C,D):</label>
                                <textarea class="form-control" id="noi_dung_trac_nghiem" name="noi_dung_trac_nghiem" rows="8" placeholder="Dán toàn bộ câu hỏi và các đáp án A,B,C,D vào đây...&#10;Ví dụ:&#10;Câu 1: 1+1 bằng mấy?&#10;A. 1&#10;B. 2&#10;C. 3&#10;D. 4&#10;&#10;Câu 2: Thủ đô của Việt Nam là gì?&#10;A. Hà Nội&#10;B. TP.HCM&#10;C. Đà Nẵng&#10;D. Hải Phòng"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="chuoi_dap_an" class="form-label fw-bold text-danger">Chuỗi đáp án đúng (viết liền, không dấu cách):</label>
                                <input type="text" class="form-control" id="chuoi_dap_an" name="chuoi_dap_an" placeholder="Ví dụ: B,A (cho 2 câu trên) -> Gõ: BA">
                                <small class="text-muted">Số lượng ký tự phải khớp với số lượng câu hỏi.</small>
                            </div>
                             <details class="mt-2">
                                <summary class="btn btn-sm btn-outline-secondary">Xem JSON (Debug)</summary>
                                <pre id="json_trac_nghiem_debug"></pre>
                            </details>
                        </div>
                        
                        <div id="optionsUploadFile" class="p-3 bg-light rounded border mb-3" style="display: none;">
                            <label class="form-label fw-bold text-primary">Tùy chọn cho bài Upload:</label>
                            <div class="row g-2">
                                <div class="col-md-8">
                                    <label for="loai_file_cho_phep" class="form-label-sm">Loại file cho phép (cách nhau bằng dấu phẩy):</label>
                                    <input type="text" class="form-control form-control-sm" name="loai_file_cho_phep" value=".pdf,.docx,.zip">
                                </div>
                                <div class="col-md-4">
                                    <label for="dung_luong_toi_da" class="form-label-sm">Dung lượng tối đa (MB):</label>
                                    <input type="number" class="form-control form-control-sm" name="dung_luong_toi_da" value="5" min="1">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <button type="submit" class="btn btn-success btn-lg w-100 fw-bold" id="btnSubmit">
                            <i class="bi bi-check-circle-fill me-2"></i> Xác Nhận Giao Bài
                        </button>
                    </form>
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

                        <div id="sua_area_TuLuan" style="display:none;" class="p-3 bg-light border rounded mb-3">
                            <label class="form-label fw-bold text-primary">Nội dung đề bài chi tiết:</label>
                            <textarea class="form-control" id="sua_noi_dung_tu_luan" name="noi_dung_tu_luan" rows="5"></textarea>
                        </div>

                        <div id="sua_area_TracNghiem" style="display:none;" class="p-3 bg-light border rounded mb-3">
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

                        <div id="sua_area_UploadFile" style="display:none;" class="p-3 bg-light border rounded mb-3">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const BASE_URL = "<?php echo BASE_URL; ?>";
    // --- KHỞI TẠO MODAL ---
    const modalGiaoBaiEl = document.getElementById('modalGiaoBai');
    const modalGiaoBai = new bootstrap.Modal(modalGiaoBaiEl);
    
    // --- 1. JS KHI BẤM NÚT "GIAO BÀI MỚI" ---
    document.getElementById('btnMoModalGiaoBai').addEventListener('click', function() {
        // Lấy thông tin từ data của View (do PHP render)
        const ma_lop = <?php echo $data['lop_info']['ma_lop']; ?>;
        const ma_mon_hoc = <?php echo $data['lop_info']['ma_mon_hoc']; ?>;
        const ten_lop = "<?php echo htmlspecialchars($data['lop_info']['ten_lop']); ?>";
        const ten_mon_hoc = "<?php echo htmlspecialchars($data['lop_info']['ten_mon_hoc']); ?>";

        // Set giá trị ẩn cho form
        document.getElementById('modal_ma_lop').value = ma_lop;
        document.getElementById('modal_ma_mon_hoc').value = ma_mon_hoc;
        document.getElementById('modalTitle').innerHTML = 
            `<i class="bi bi-pencil-fill me-2"></i> Giao Bài [${ten_mon_hoc} - ${ten_lop}]`;

        // Reset form
        document.getElementById('formGiaoBai').reset();
        document.getElementById('optionsTuLuan').style.display = 'none';
        document.getElementById('optionsTracNghiem').style.display = 'none';
        document.getElementById('optionsUploadFile').style.display = 'none';
        document.getElementById('modalFormNotification').style.display = 'none';
        document.getElementById('json_trac_nghiem_debug').textContent = '';
        document.getElementById('btnSubmit').disabled = false;
        document.getElementById('btnSubmit').innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> Xác Nhận Giao Bài';
    });

    // --- 2. JS HIỂN THỊ TÙY CHỌN NÂNG CAO (GIAO BÀI) ---
    const loaiBaiTapSelect = document.getElementById('loai_bai_tap');
    const optionsTuLuan = document.getElementById('optionsTuLuan');
    const optionsTracNghiem = document.getElementById('optionsTracNghiem');
    const optionsUploadFile = document.getElementById('optionsUploadFile'); 
    
    loaiBaiTapSelect.addEventListener('change', function() {
        optionsTuLuan.style.display = 'none';
        optionsTracNghiem.style.display = 'none';
        optionsUploadFile.style.display = 'none'; 

        if (this.value === 'TuLuan') {
            optionsTuLuan.style.display = 'block';
        } else if (this.value === 'TracNghiem') {
            optionsTracNghiem.style.display = 'block';
        } else if (this.value === 'UploadFile') {
            optionsUploadFile.style.display = 'block';
        }
    });

    // --- 3. JS SUBMIT FORM GIAO BÀI ---
    const form = document.getElementById('formGiaoBai');
    const globalNotification = document.getElementById('formNotification');
    const modalNotification = document.getElementById('modalFormNotification');
    const submitButton = document.getElementById('btnSubmit');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Đang xử lý...';
        modalNotification.style.display = 'none';
        globalNotification.style.display = 'none';

        try {
            const loaiBaiTap = loaiBaiTapSelect.value;
            
            if (loaiBaiTap === 'TracNghiem') {
                const jsonOutput = parseTracNghiem();
                document.getElementById('json_trac_nghiem_hidden').value = jsonOutput;
                document.getElementById('json_trac_nghiem_debug').textContent = JSON.stringify(JSON.parse(jsonOutput), null, 2);
            }

            const formData = new FormData(this);
            
            const response = await fetch('<?php echo BASE_URL; ?>/giaovien/luuBaiTapApi', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            const result = await response.json();

            if (response.ok && result.success) {
                globalNotification.className = 'alert alert-success';
                globalNotification.textContent = result.message + " Đang tải lại danh sách...";
                globalNotification.style.display = 'block';
                modalGiaoBai.hide();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                throw new Error(result.message || 'Lỗi không xác định từ server.');
            }

        } catch (error) {
            modalNotification.className = 'alert alert-danger';
            modalNotification.textContent = 'Lỗi: ' + error.message;
            modalNotification.style.display = 'block';
            
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> Xác Nhận Giao Bài';
        }
    });

    // --- 4. JS CHO MODAL SỬA BÀI TẬP ---
    const modalSuaEl = document.getElementById('modalSuaBaiTap');
    const modalSua = new bootstrap.Modal(modalSuaEl);

    function openModalSuaBaiTap(id) {
        // Reset form
        document.getElementById('formSuaBaiTap').reset();
        document.getElementById('sua_area_TuLuan').style.display = 'none';
        document.getElementById('sua_area_TracNghiem').style.display = 'none';
        document.getElementById('sua_area_UploadFile').style.display = 'none';
        
        // Gọi API lấy thông tin
        const fd = new FormData();
        fd.append('ma_bai_tap', id);

        fetch('<?php echo BASE_URL; ?>/giaovien/layChiTietBaiTapDeSuaApi', {
            method: 'POST', body: fd
        })
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                const d = res.data;
                
                // Fill thông tin chung
                document.getElementById('sua_ma_bai_tap').value = d.ma_bai_tap;
                document.getElementById('sua_ten_bai_tap').value = d.ten_bai_tap;
                document.getElementById('sua_mo_ta').value = d.mo_ta;
                // Fix lỗi hiển thị datetime-local
                if(d.han_nop) {
                    document.getElementById('sua_han_nop').value = d.han_nop.replace(' ', 'T').substring(0, 16);
                }

                // Hiển thị link file cũ
                const linkDiv = document.getElementById('link_file_cu');
                if(d.file_dinh_kem) {
                    linkDiv.innerHTML = `<a href="<?php echo BASE_URL; ?>/${d.file_dinh_kem}" target="_blank" class="badge bg-info text-decoration-none">File hiện tại</a>`;
                } else {
                    linkDiv.innerHTML = '<span class="text-muted small">Không có file cũ</span>';
                }

                // Fill thông tin riêng theo loại
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
                else if (d.loai_bai_tap == 'UploadFile') {
                    document.getElementById('sua_area_UploadFile').style.display = 'block';
                    document.getElementById('sua_loai_file').value = d.loai_file_cho_phep;
                    document.getElementById('sua_max_size').value = d.dung_luong_toi_da;
                }

                modalSua.show();
            } else {
                alert('Lỗi: ' + res.message);
            }
        })
        .catch(err => alert('Lỗi kết nối: ' + err));
    }

    // --- 5. JS SUBMIT FORM SỬA BÀI ---
    // --- 5. JS SUBMIT FORM SỬA BÀI (VALIDATE CHẶT CHẼ) ---
    document.getElementById('formSuaBaiTap').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // 1. Lấy các vùng hiển thị để biết đang sửa loại bài tập nào
        const areaTN = document.getElementById('sua_area_TracNghiem');
        const areaTL = document.getElementById('sua_area_TuLuan');

        // 2. Validate Tự Luận
        if (areaTL.style.display !== 'none') {
            const contentTL = document.getElementById('sua_noi_dung_tu_luan').value.trim();
            if (!contentTL) {
                alert('Vui lòng nhập nội dung đề bài tự luận!');
                return; // Dừng lại
            }
        }

        // 3. Validate Trắc Nghiệm
        if (areaTN.style.display !== 'none') {
            const rawText = document.getElementById('sua_raw_trac_nghiem').value.trim();
            const ansKey = document.getElementById('sua_chuoi_dap_an').value.trim();
            
            // Bắt buộc phải có nội dung nếu đang mở tab trắc nghiệm
            if (!rawText || !ansKey) {
                alert('Vui lòng nhập đầy đủ câu hỏi và đáp án cho bài trắc nghiệm!');
                return;
            }

            try {
                // Thử parse, nếu sai cú pháp nó sẽ throw Error và nhảy xuống catch
                const jsonOutput = parseTracNghiemForEdit(rawText, ansKey); 
                
                // Nếu parse thành công thì gán vào input ẩn để gửi đi
                document.getElementById('sua_json_trac_nghiem').value = jsonOutput;
                
            } catch (err) {
                // Nếu lỗi cú pháp -> Báo lỗi và DỪNG SUBMIT
                alert('Lỗi cú pháp Trắc nghiệm:\n' + err.message + '\n\nVui lòng kiểm tra lại định dạng câu hỏi (Câu 1: ... A. B. C. D.)');
                return; // Dừng lại, không gửi form
            }
        }

        // 4. Nếu qua được hết validate thì mới gửi
        const btn = document.getElementById('btnLuuSua');
        const oldBtnText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang lưu...';

        const fd = new FormData(this);

        fetch(BASE_URL + '/giaovien/suaBaiTapApi', {
            method: 'POST', body: fd
        })
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                alert('✅ Cập nhật thành công!');
                location.reload();
            } else {
                alert('❌ Lỗi: ' + res.message);
            }
        })
        .catch(err => alert('❌ Lỗi kết nối: ' + err))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = oldBtnText;
        });
    });

    /**
     * --- 6. HÀM PARSER TRẮC NGHIỆM (CHUNG) ---
     */
    // function parseTracNghiem() {
    //     const questionText = document.getElementById('noi_dung_trac_nghiem').value;
    //     const answerKeyString = document.getElementById('chuoi_dap_an').value;
    //     if (!questionText || !answerKeyString) { throw new Error('Vui lòng nhập cả Nội dung đề và Chuỗi đáp án.'); }
        
    //     // Logic parse (rút gọn)
    //     const blocks = questionText.split(/(?:Câu|Câu hỏi|\d+)[ \t]*[\d\.:]+/).filter(b => b.trim() !== '');
    //     const keys = answerKeyString.toUpperCase().replace(/[^A-D]/g, '').split('');
        
    //     if (blocks.length === 0) throw new Error('Không tìm thấy câu hỏi.');
    //     if (blocks.length !== keys.length) throw new Error(`Số câu hỏi (${blocks.length}) không khớp đáp án (${keys.length}).`);

    //     const arr = [];
    //     for (let i = 0; i < blocks.length; i++) {
    //         const lines = blocks[i].trim().split('\n').filter(l => l.trim()!=='');
    //         const q = lines.shift().trim();
    //         // Lọc options A,B,C,D
    //         let opts = lines.map(l => l.trim()).filter(l => /^[A-D][\.\)]/i.test(l));
    //         // Nếu không có A,B,C,D rõ ràng -> Tự đánh số
    //         if(opts.length == 0) {
    //             opts = lines.map((opt, idx) => `${String.fromCharCode(65 + idx)}. ${opt}`);
    //         }
    //         arr.push({ id: i+1, text: q, options: [...new Set(opts)], correct: keys[i] });
    //     }
    //     return JSON.stringify({ questions: arr });
    // }
    function parseTracNghiem() {
        const questionText = document.getElementById('noi_dung_trac_nghiem').value;
        const answerKeyString = document.getElementById('chuoi_dap_an').value;
        if (!questionText || !answerKeyString) { throw new Error('Vui lòng nhập cả Nội dung đề và Chuỗi đáp án.'); }
        
        const blocks = questionText.split(/(?:Câu|Câu hỏi|\d+)[ \t]*[\d\.:]+/).filter(b => b.trim() !== '');
        const keys = answerKeyString.toUpperCase().replace(/[^A-D]/g, '').split('');
        
        if (blocks.length === 0) throw new Error('Không tìm thấy câu hỏi.');
        if (blocks.length !== keys.length) throw new Error(`Số câu hỏi (${blocks.length}) không khớp đáp án (${keys.length}).`);

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

    // Parser riêng cho form sửa (tái sử dụng logic trên)
    // function parseTracNghiemForEdit(text, key) {
    //     const blocks = text.split(/(?:Câu|Câu hỏi|\d+)[ \t]*[\d\.:]+/).filter(b => b.trim() !== '');
    //     const keys = key.toUpperCase().replace(/[^A-D]/g, '').split('');
    //     if (blocks.length !== keys.length) throw new Error('Số câu hỏi và đáp án không khớp');
        
    //     const arr = [];
    //     for (let i = 0; i < blocks.length; i++) {
    //         const lines = blocks[i].trim().split('\n').filter(l => l.trim()!=='');
    //         const q = lines.shift().trim();
    //         let opts = lines.map(l => l.trim()).filter(l => /^[A-D][\.\)]/i.test(l));
    //         if(opts.length == 0) opts = lines.map((opt, idx) => `${String.fromCharCode(65 + idx)}. ${opt}`);
    //         arr.push({ id: i+1, text: q, options: [...new Set(opts)], correct: keys[i] });
    //     }
    //     return JSON.stringify({ questions: arr });
    // }
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


    /**
     * --- 7. SESSION KEEP-ALIVE ---
     */
    function startSessionKeepAlive(interval) {
        setInterval(async () => {
            try { await fetch('<?php echo BASE_URL; ?>/giaovien/ping'); } catch (e) {}
        }, interval);
    }

    document.addEventListener('DOMContentLoaded', (event) => {
        startSessionKeepAlive(60000); // 1 phút
    });

</script>
</body>
</html>