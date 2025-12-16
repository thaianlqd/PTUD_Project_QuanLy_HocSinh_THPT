<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xử Lý Yêu Cầu Chỉnh Sửa Điểm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #f4f7f6; }
        .card { border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .table-hover tbody tr { transition: all 0.3s ease; }
        .badge.bg-warning { color: #000 !important; }
        .btn-action { margin-right: 5px; }
        .diem-cu { text-decoration: line-through; color: #dc3545; }
        .diem-moi { color: #198754; font-weight: bold; font-size: 1.1em; }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <header class="mb-4 p-4 bg-white rounded-3 shadow-sm">
            <h1 class="fw-bold text-center text-primary">
                <i class="bi bi-pencil-square me-2"></i> DUYỆT YÊU CẦU CHỈNH SỬA ĐIỂM
            </h1>
            <p class="text-center text-muted">Chào mừng, <?php echo htmlspecialchars($data['user_name']); ?> (Ban Giám Hiệu)</p>
            <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-outline-secondary btn-sm float-end" style="margin-top: -50px;">
                <i class="bi bi-arrow-left"></i> Quay lại Dashboard
            </a>
        </header>

        <div id="globalNotification" class="alert" style="display: none;" role="alert"></div>

        <div class="card mb-3">
            <div class="card-body d-flex justify-content-center py-2">
                <div class="btn-group" role="group">
                    <?php
                        $current_filter = $data['filter_trang_thai'] ?? 'ChoDuyet';
                        $filters = [
                            'ChoDuyet' => 'Chờ duyệt',
                            'DaDuyet' => 'Đã duyệt',
                            'TuChoi' => 'Từ chối',
                            'TatCa' => 'Tất cả'
                        ];
                        
                        foreach ($filters as $key => $value) {
                            $active_class = ($current_filter == $key) ? 'active' : '';
                            $url = BASE_URL . '/bgh/duyetdiem?trang_thai=' . $key;
                            
                            $btn_color = 'btn-outline-primary';
                            if ($key == 'DaDuyet') $btn_color = 'btn-outline-success';
                            if ($key == 'TuChoi') $btn_color = 'btn-outline-danger';
                            if ($key == 'ChoDuyet') $btn_color = 'btn-outline-warning text-dark'; // Thêm text-dark cho dễ đọc

                            echo "<a href=\"{$url}\" class=\"btn {$btn_color} {$active_class}\">{$value}</a>";
                        }
                    ?>
                </div>
            </div>
        </div>
        <div class="card p-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-list-task me-2"></i> Danh sách phiếu (Lọc theo: <?php echo htmlspecialchars($filters[$current_filter]); ?>)</h5>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>Ngày Lập</th>
                            <th>Giáo Viên YC</th>
                            <th>Học Sinh</th>
                            <th>Môn Học</th>
                            <th>Học Kỳ</th>
                            <th width="250">Chi Tiết Điểm</th>
                            <th>Lý Do</th>
                            <th width="100">Trạng Thái</th>
                            <th width="180" class="text-center">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody id="phieuTableBody">
                        <?php if (empty($data['danh_sach_phieu'])): ?>
                            <tr>
                                <td colspan="10" class="text-center p-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <strong>Không có phiếu nào</strong>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['danh_sach_phieu'] as $index => $phieu): ?>
                                <tr id="row-phieu-<?php echo $phieu['ma_phieu']; ?>">
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($phieu['ngay_lap_phieu'])); ?></td>
                                    <td><?php echo htmlspecialchars($phieu['ten_giao_vien_yeu_cau']); ?></td>
                                    <td><?php echo htmlspecialchars($phieu['ten_hoc_sinh']); ?></td>
                                    <td><?php echo htmlspecialchars($phieu['ten_mon_hoc']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $phieu['ma_hoc_ky'] ?? 'N/A'; ?></span></td>
                                    <td>
                                        <?php 
                                        // Hiển thị chi tiết điểm thay đổi
                                        $fields = [
                                            'Miệng' => 'diem_mieng',
                                            '15p' => 'diem_15phut',
                                            '1T' => 'diem_1tiet',
                                            'GK' => 'diem_gua_ky',
                                            'CK' => 'diem_cuoi_ky'
                                        ];
                                        
                                        echo '<small>';
                                        foreach ($fields as $label => $field) {
                                            $cu = $phieu[$field . '_cu'] ?? '--';
                                            $moi = $phieu[$field . '_moi'] ?? '--';
                                            
                                            // Chỉ hiển thị nếu có thay đổi
                                            if ($cu != $moi && $moi !== '--') {
                                                echo "<strong>{$label}:</strong> ";
                                                echo "<span class='diem-cu'>{$cu}</span> ";
                                                echo "<i class='bi bi-arrow-right-short'></i> ";
                                                echo "<span class='diem-moi'>{$moi}</span><br>";
                                            }
                                        }
                                        echo '</small>';
                                        ?>
                                    </td>
                                    <td>
                                        <small class="text-muted fst-italic">
                                            <?php echo htmlspecialchars(mb_substr($phieu['ly_do_chinh_sua'] ?? 'N/A', 0, 50)); ?>
                                            <?php echo mb_strlen($phieu['ly_do_chinh_sua'] ?? '') > 50 ? '...' : ''; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                            $status = $phieu['trang_thai_phieu'];
                                            if ($status == 'ChoDuyet') {
                                                echo '<span class="badge bg-warning text-dark badge-status-' . $phieu['ma_phieu'] . '">Chờ Duyệt</span>';
                                            } elseif ($status == 'DaDuyet') {
                                                echo '<span class="badge bg-success badge-status-' . $phieu['ma_phieu'] . '">Đã Duyệt</span>';
                                            } elseif ($status == 'TuChoi') {
                                                echo '<span class="badge bg-danger badge-status-' . $phieu['ma_phieu'] . '">Từ Chối</span>';
                                            }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group-<?php echo $phieu['ma_phieu']; ?>">
                                            <?php if ($phieu['trang_thai_phieu'] == 'ChoDuyet'): ?>
                                                <button class="btn btn-sm btn-success btn-action" 
                                                        onclick="openConfirmModal('duyet', <?php echo $phieu['ma_phieu']; ?>, '<?php echo htmlspecialchars($phieu['ten_hoc_sinh']); ?>')">
                                                    <i class="bi bi-check-circle"></i> Duyệt
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-action" 
                                                        onclick="openConfirmModal('tuchoi', <?php echo $phieu['ma_phieu']; ?>, '<?php echo htmlspecialchars($phieu['ten_hoc_sinh']); ?>')">
                                                    <i class="bi bi-x-circle"></i> Từ chối
                                                </button>
                                            <?php else: ?>
                                                <small class="text-muted">
                                                    <i class="bi bi-check-all"></i> Đã xử lý
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" id="confirmModalHeader">
                    <h5 class="modal-title" id="confirmModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmModalBody"></p>
                    
                    <div id="lyDoTuChoiWrapper" class="mt-3" style="display: none;">
                        <label for="lyDoInput" class="form-label">Nhập lý do từ chối (bắt buộc):</label>
                        <textarea class="form-control" id="lyDoInput" rows="3"></textarea>
                        <div id="confirmNotification" class="mt-2 text-danger"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="confirmMaPhieu">
                    <input type="hidden" id="confirmAction">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn" id="confirmButton" onclick="handleSubmit()">Xác nhận</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const BASE_URL = "<?php echo BASE_URL ?? ''; ?>";
        let confirmModal;

        document.addEventListener('DOMContentLoaded', () => {
            // Kiểm tra xem bootstrap.Modal có tồn tại không
            if (typeof bootstrap === 'undefined' || typeof bootstrap.Modal === 'undefined') {
                console.error("LỖI: Không tải được file bootstrap.bundle.min.js. Hãy kiểm tra kết nối mạng.");
            } else {
                // Chỉ khởi tạo Modal nếu Bootstrap đã tải thành công
                confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            }
        });

        function openConfirmModal(action, maPhieu, tenHS) {
            // Kiểm tra nếu modal chưa được khởi tạo thì báo lỗi
            if (!confirmModal) {
                alert("Lỗi: Không thể khởi tạo Pop-up (Modal). Kiểm tra F12/Console.");
                return;
            }

            const modalTitle = document.getElementById('confirmModalTitle');
            const modalBody = document.getElementById('confirmModalBody');
            const modalHeader = document.getElementById('confirmModalHeader');
            const confirmBtn = document.getElementById('confirmButton');
            const lyDoWrapper = document.getElementById('lyDoTuChoiWrapper');
            const lyDoInput = document.getElementById('lyDoInput');
            const confirmNotif = document.getElementById('confirmNotification');

            // Reset
            lyDoWrapper.style.display = 'none'; 
            lyDoInput.value = '';
            confirmNotif.textContent = '';
            
            document.getElementById('confirmMaPhieu').value = maPhieu;
            document.getElementById('confirmAction').value = action;

            if (action === 'duyet') {
                modalTitle.textContent = 'Xác nhận DUYỆT chỉnh sửa điểm';
                modalBody.textContent = `Bạn có chắc chắn muốn duyệt yêu cầu sửa điểm cho học sinh "${tenHS}"?`;
                modalHeader.className = 'modal-header bg-success text-white';
                confirmBtn.className = 'btn btn-success';
                confirmBtn.textContent = 'Xác nhận Duyệt';
            } else {
                modalTitle.textContent = 'Xác nhận TỪ CHỐI chỉnh sửa điểm';
                modalBody.textContent = `Bạn có chắc chắn muốn TỪ CHỐI yêu cầu sửa điểm cho học sinh "${tenHS}"?`;
                modalHeader.className = 'modal-header bg-danger text-white';
                confirmBtn.className = 'btn btn-danger';
                confirmBtn.textContent = 'Xác nhận Từ chối';
                lyDoWrapper.style.display = 'block';
            }
            
            confirmModal.show();
        }

        async function handleSubmit() {
            const maPhieu = document.getElementById('confirmMaPhieu').value;
            const action = document.getElementById('confirmAction').value;
            const lyDo = document.getElementById('lyDoInput').value.trim();
            const confirmNotif = document.getElementById('confirmNotification');
            confirmNotif.textContent = '';

            if (action === 'tuchoi' && lyDo === '') {
                confirmNotif.textContent = '⚠️ Lý do từ chối không được để trống.';
                return;
            }

            // ✅ SỬA: Tạo object JSON thay vì FormData
            const payload = {
                ma_phieu: maPhieu,
                action: action
            };
            
            if (action === 'tuchoi') {
                payload.ly_do = lyDo;
            }

            try {
                const response = await fetch(`${BASE_URL}/bgh/xuLyPhieuDiem`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (result.success) {
                    // Đóng modal
                    confirmModal.hide();
                    
                    // Hiển thị thông báo thành công
                    showGlobalNotification(result.message, 'success');
                    
                    // Cập nhật UI hàng
                    const row = document.getElementById(`row-phieu-${maPhieu}`);
                    if (row) {
                        row.style.opacity = '0.5';
                        
                        // Đổi badge trạng thái
                        const badge = row.querySelector('.badge-status-' + maPhieu);
                        if (badge) {
                            if (action === 'duyet') {
                                badge.className = 'badge bg-success badge-status-' + maPhieu;
                                badge.textContent = 'Đã Duyệt';
                            } else {
                                badge.className = 'badge bg-danger badge-status-' + maPhieu;
                                badge.textContent = 'Từ Chối';
                            }
                        }
                        
                        // Vô hiệu hóa nút
                        const btnGroup = row.querySelector('.btn-group-' + maPhieu);
                        if (btnGroup) {
                            btnGroup.innerHTML = '<small class="text-muted"><i class="bi bi-check-all"></i> Đã xử lý</small>';
                        }
                    }
                } else {
                    confirmNotif.textContent = '❌ ' + (result.message || 'Lỗi không xác định.');
                }
            } catch (error) {
                console.error("Lỗi fetch xuLyPhieuDiem:", error);
                confirmNotif.textContent = '❌ Lỗi kết nối máy chủ. Vui lòng thử lại.';
            }
        }
        
        function showGlobalNotification(message, type = 'success') {
            const el = document.getElementById('globalNotification');
            el.textContent = message;
            el.className = `alert alert-${type} alert-dismissible fade show`;
            el.style.display = 'block';
            
            if (!el.querySelector('.btn-close')) {
                const closeButton = document.createElement('button');
                closeButton.type = 'button';
                closeButton.className = 'btn-close';
                closeButton.setAttribute('data-bs-dismiss', 'alert');
                closeButton.setAttribute('aria-label', 'Close');
                el.appendChild(closeButton);
            }
            setTimeout(() => {
                if (el) el.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>