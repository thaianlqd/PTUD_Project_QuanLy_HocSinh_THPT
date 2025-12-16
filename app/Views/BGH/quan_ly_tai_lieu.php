<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tài Liệu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Segoe UI', Roboto, sans-serif; background-color: #f3f4f6; }
        .sidebar { width: 280px; position: fixed; height: 100vh; background: #fff; z-index: 1000; box-shadow: 4px 0 15px rgba(0,0,0,0.05); }
        .main-content { margin-left: 280px; padding: 30px; }
        .card-tai-lieu { border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: 0.3s; }
        .card-tai-lieu:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.12); }
        .badge-loai { font-size: 0.75rem; padding: 5px 10px; }
        @media (max-width: 991px) { .sidebar { transform: translateX(-100%); } .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div style="padding: 30px 20px; text-align: center; color: white; background: linear-gradient(135deg, #fd7e14 0%, #b35200 100%);">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="rounded-circle border border-3 border-white" width="80">
            <h6 class="fw-bold mt-2 mb-0"><?php echo htmlspecialchars($data['user_name']); ?></h6>
            <small>Giáo Viên Bộ Môn</small>
        </div>
        <nav class="nav flex-column mt-3 px-2">
            <a class="nav-link" href="<?php echo BASE_URL; ?>/dashboard"><i class="bi bi-grid-fill me-2"></i>Dashboard</a>
            <a class="nav-link active" href="<?php echo BASE_URL; ?>/tailieu/quanly"><i class="bi bi-file-earmark-pdf me-2"></i>Tài Liệu</a>
            <a class="nav-link" href="<?php echo BASE_URL; ?>/giaovien/diemdanh"><i class="bi bi-upc-scan me-2"></i>Điểm Danh</a>
            <a class="nav-link" href="<?php echo BASE_URL; ?>/auth/logout" style="margin-top: auto;"><i class="bi bi-box-arrow-right text-danger me-2"></i>Đăng Xuất</a>
        </nav>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- DEBUG: Kiểm tra dữ liệu từ Controller -->
        <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
            <strong>🔍 DEBUG INFO:</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <pre style="margin: 10px 0 0 0; font-size: 0.85rem; background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 200px; overflow-y: auto;"><code><?php 
                echo "SESSION user_id: " . ($_SESSION['user_id'] ?? 'KHÔNG CÓ') . "\n";
                echo "SESSION user_role: " . ($_SESSION['user_role'] ?? 'KHÔNG CÓ') . "\n";
                echo "SESSION user_name: " . ($_SESSION['user_name'] ?? 'KHÔNG CÓ') . "\n\n";
                echo "mon_hoc_list count: " . count($data['mon_hoc_list'] ?? []) . "\n";
                echo "mon_hoc_list data:\n";
                var_dump($data['mon_hoc_list']);
            ?></code></pre>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">Quản Lý Tài Liệu</h4>
                <small class="text-muted"><i class="bi bi-files"></i> <?php echo $data['taiLieu_count']; ?> tài liệu</small>
            </div>
            <button class="btn btn-primary btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUploadTaiLieu">
                <i class="bi bi-plus-circle me-2"></i>Upload Tài Liệu Mới
            </button>
        </div>

        <?php if(!empty($data['taiLieu_list'])): ?>
            <div class="row g-3">
                <?php foreach($data['taiLieu_list'] as $tl): 
                    $icon = 'bi-file';
                    if (strpos($tl['file_dinh_kem'], '.pdf') !== false) $icon = 'bi-file-pdf';
                    elseif (in_array(pathinfo($tl['file_dinh_kem'], PATHINFO_EXTENSION), ['doc', 'docx'])) $icon = 'bi-file-word';
                    elseif (in_array(pathinfo($tl['file_dinh_kem'], PATHINFO_EXTENSION), ['xls', 'xlsx'])) $icon = 'bi-file-spreadsheet';
                    elseif (in_array(pathinfo($tl['file_dinh_kem'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif'])) $icon = 'bi-file-image';
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-tai-lieu p-4">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <i class="bi <?php echo $icon; ?> fs-1 text-warning"></i>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-secondary" onclick="editTaiLieu(<?php echo $tl['ma_tai_lieu']; ?>)" title="Chỉnh sửa">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteTaiLieu(<?php echo $tl['ma_tai_lieu']; ?>)" title="Xóa">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($tl['ten_tai_lieu']); ?></h6>
                        
                        <div class="mb-2">
                            <span class="badge bg-light text-dark"><?php echo $tl['ten_mon_hoc']; ?></span>
                            <span class="badge badge-loai bg-info text-white"><?php echo $tl['loai_tai_lieu']; ?></span>
                        </div>

                        <p class="text-muted small mb-2"><?php echo htmlspecialchars(substr($tl['mo_ta'], 0, 100)); ?></p>

                        <small class="text-muted d-block mb-3">
                            <i class="bi bi-calendar-event"></i> <?php echo date('d/m/Y', strtotime($tl['ngay_tao'])); ?>
                        </small>

                        <a href="<?php echo BASE_URL; ?>/tailieu/download/<?php echo $tl['ma_tai_lieu']; ?>" class="btn btn-sm btn-warning text-dark w-100">
                            <i class="bi bi-download me-1"></i>Tải Xuống
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center py-5">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                <p class="mb-0">Chưa có tài liệu nào. <strong>Upload tài liệu mới</strong> để bắt đầu!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- MODAL UPLOAD TÀI LIỆU -->
    <div class="modal fade" id="modalUploadTaiLieu" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Upload Tài Liệu Mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formUploadTaiLieu" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên Tài Liệu <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="inp_ten_tai_lieu" placeholder="VD: Bài giảng chương 1 - Hàm số" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Môn Học <span class="text-danger">*</span></label>
                                <select class="form-select" id="inp_ma_mon_hoc" required>
                                    <option value="">-- Chọn môn học --</option>
                                    <?php if (!empty($data['mon_hoc_list'])): ?>
                                        <?php foreach ($data['mon_hoc_list'] as $mon): ?>
                                            <option value="<?php echo $mon['ma_mon_hoc']; ?>">
                                                <?php echo htmlspecialchars($mon['ten_mon_hoc']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>Không có môn học nào</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Loại Tài Liệu <span class="text-danger">*</span></label>
                                <select class="form-select" id="inp_loai_tai_lieu" required>
                                    <option value="">-- Chọn loại --</option>
                                    <option value="Bài giảng">Bài giảng</option>
                                    <option value="Slide">Slide</option>
                                    <option value="Bài tập">Bài tập</option>
                                    <option value="Đáp án">Đáp án</option>
                                    <option value="Tài liệu tham khảo">Tài liệu tham khảo</option>
                                    <option value="Video">Video hướng dẫn</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô Tả</label>
                            <textarea class="form-control" id="inp_mo_ta" rows="3" placeholder="Mô tả chi tiết về tài liệu..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Upload File <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="file" class="form-control" id="inp_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.jpg,.jpeg,.png,.gif,.zip,.rar" required>
                                <span class="input-group-text">Max 50MB</span>
                            </div>
                            <small class="text-muted d-block mt-2">
                                Các loại file được phép: PDF, Word, Excel, TXT, Ảnh (JPG, PNG), ZIP, RAR
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Ghi Chú (Tùy chọn)</label>
                            <textarea class="form-control" id="inp_ghi_chu" rows="2" placeholder="VD: Dành cho lớp 10A1, HK1..."></textarea>
                        </div>

                        <div id="uploadProgress" style="display:none;">
                            <div class="progress mb-2">
                                <div class="progress-bar progress-bar-animated" id="progressBar" style="width: 0%"></div>
                            </div>
                            <p class="small text-muted" id="uploadStatus">Đang upload...</p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="btnSubmitUpload">
                        <i class="bi bi-cloud-upload me-1"></i>Upload
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CHỈNH SỬA TÀI LIỆU -->
    <div class="modal fade" id="modalEditTaiLieu" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pencil-fill me-2"></i>Chỉnh Sửa Tài Liệu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditTaiLieu">
                        <input type="hidden" id="edit_ma_tai_lieu">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên Tài Liệu</label>
                            <input type="text" class="form-control" id="edit_ten_tai_lieu" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Loại Tài Liệu</label>
                            <select class="form-select" id="edit_loai_tai_lieu" required>
                                <option value="Bài giảng">Bài giảng</option>
                                <option value="Slide">Slide</option>
                                <option value="Bài tập">Bài tập</option>
                                <option value="Đáp án">Đáp án</option>
                                <option value="Tài liệu tham khảo">Tài liệu tham khảo</option>
                                <option value="Video">Video hướng dẫn</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô Tả</label>
                            <textarea class="form-control" id="edit_mo_ta" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Ghi Chú</label>
                            <textarea class="form-control" id="edit_ghi_chu" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-warning text-dark" onclick="submitEditTaiLieu()">
                        <i class="bi bi-check-circle me-1"></i>Lưu Thay Đổi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // === UPLOAD TÀI LIỆU ===
        document.getElementById('btnSubmitUpload').addEventListener('click', function() {
            const form = document.getElementById('formUploadTaiLieu');
            if (!form.checkValidity()) {
                alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
                return;
            }

            const formData = new FormData();
            formData.append('ten_tai_lieu', document.getElementById('inp_ten_tai_lieu').value);
            formData.append('mo_ta', document.getElementById('inp_mo_ta').value);
            formData.append('loai_tai_lieu', document.getElementById('inp_loai_tai_lieu').value);
            formData.append('ma_mon_hoc', document.getElementById('inp_ma_mon_hoc').value);
            formData.append('ghi_chu', document.getElementById('inp_ghi_chu').value);
            formData.append('file', document.getElementById('inp_file').files[0]);

            // Hiển thị progress
            document.getElementById('uploadProgress').style.display = 'block';

            const xhr = new XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    document.getElementById('progressBar').style.width = percentComplete + '%';
                    document.getElementById('uploadStatus').textContent = Math.round(percentComplete) + '% hoàn thành';
                }
            });

            xhr.addEventListener('load', function() {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Lỗi: ' + response.message);
                    document.getElementById('uploadProgress').style.display = 'none';
                }
            });

            xhr.addEventListener('error', function() {
                alert('Lỗi kết nối khi upload');
                document.getElementById('uploadProgress').style.display = 'none';
            });

            xhr.open('POST', '<?php echo BASE_URL; ?>/tailieu/upload');
            xhr.send(formData);
        });

        // === CHỈNH SỬA TÀI LIỆU ===
        function editTaiLieu(maTaiLieu) {
            // Fetch dữ liệu từ server
            fetch('<?php echo BASE_URL; ?>/tailieu/getChiTiet/' + maTaiLieu)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Fill modal
                        document.getElementById('edit_ma_tai_lieu').value = data.data.ma_tai_lieu;
                        document.getElementById('edit_ten_tai_lieu').value = data.data.ten_tai_lieu;
                        document.getElementById('edit_loai_tai_lieu').value = data.data.loai_tai_lieu;
                        document.getElementById('edit_mo_ta').value = data.data.mo_ta;
                        document.getElementById('edit_ghi_chu').value = data.data.ghi_chu;
                        
                        // Mở modal
                        const modal = new bootstrap.Modal(document.getElementById('modalEditTaiLieu'));
                        modal.show();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                });
        }

        function submitEditTaiLieu() {
            const formData = new FormData();
            formData.append('ma_tai_lieu', document.getElementById('edit_ma_tai_lieu').value);
            formData.append('ten_tai_lieu', document.getElementById('edit_ten_tai_lieu').value);
            formData.append('loai_tai_lieu', document.getElementById('edit_loai_tai_lieu').value);
            formData.append('mo_ta', document.getElementById('edit_mo_ta').value);
            formData.append('ghi_chu', document.getElementById('edit_ghi_chu').value);

            fetch('<?php echo BASE_URL; ?>/tailieu/update', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            });
        }

        // === XÓA TÀI LIỆU ===
        function deleteTaiLieu(maTaiLieu) {
            if (confirm('Bạn chắc chắn muốn xóa tài liệu này?')) {
                const formData = new FormData();
                formData.append('ma_tai_lieu', maTaiLieu);

                fetch('<?php echo BASE_URL; ?>/tailieu/delete', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                });
            }
        }
    </script>
</body>
</html>