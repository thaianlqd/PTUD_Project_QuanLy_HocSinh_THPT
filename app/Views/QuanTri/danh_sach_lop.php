<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Lớp Học</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script>const BASE_URL = "<?php echo BASE_URL; ?>";</script>
</head>

<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-house-door-fill text-primary"></i> Quản Lý Lớp Học</h2>
                <p class="text-muted mb-0">Tổng số lớp: <strong class="text-primary"><?php echo $data['total_lop']; ?></strong></p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="<?php echo BASE_URL; ?>/LopHoc/create" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle-fill"></i> Thêm Lớp Mới
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <label class="mb-0">Lọc theo khối:</label>
                    <select id="filterKhoi" class="form-select w-auto" onchange="applyFilterKhoi()">
                        <option value="" <?php echo empty($data['filter_khoi']) ? 'selected' : ''; ?>>Tất cả</option>
                        <option value="10" <?php echo ($data['filter_khoi'] === '10') ? 'selected' : ''; ?>>Khối 10</option>
                        <option value="11" <?php echo ($data['filter_khoi'] === '11') ? 'selected' : ''; ?>>Khối 11</option>
                        <option value="12" <?php echo ($data['filter_khoi'] === '12') ? 'selected' : ''; ?>>Khối 12</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th style="width: 5%;">#</th>
                                <th style="width: 12%;">Tên Lớp</th>
                                <th style="width: 8%;">Khối</th>
                                <th style="width: 8%;">Sĩ Số</th>
                                <th style="width: 15%;">Tổ Hợp</th>
                                <th style="width: 12%;">Phòng Học</th>
                                <th style="width: 15%;">GVCN</th>
                                <th style="width: 10%;">Trạng Thái</th>
                                <th style="width: 15%;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['lop_hoc_list'])): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1"></i>
                                        <p class="mt-2">Chưa có lớp học nào</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $offset = ($data['current_page'] - 1) * 15;
                                foreach ($data['lop_hoc_list'] as $index => $lop): 
                                ?>
                                    <tr>
                                        <td class="text-center fw-bold"><?php echo $offset + $index + 1; ?></td>
                                        <td class="fw-bold text-primary"><?php echo htmlspecialchars($lop['ten_lop']); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($lop['khoi']); ?></span>
                                        </td>
                                        <td class="text-center"><?php echo htmlspecialchars($lop['si_so'] ?? 0); ?></td>
                                        <td><?php echo htmlspecialchars($lop['ten_to_hop'] ?? 'Chưa có'); ?></td>
                                        <td><?php echo htmlspecialchars($lop['ten_phong'] ?? 'Chưa có'); ?></td>
                                        <td><?php echo htmlspecialchars($lop['ten_gvcn'] ?? 'Chưa có'); ?></td>
                                        <td class="text-center">
                                            <?php if ($lop['trang_thai_lop'] == 'HoatDong'): ?>
                                                <span class="badge bg-success">Hoạt Động</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Ngừng</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="openEditModal(<?php echo $lop['ma_lop']; ?>)" title="Sửa">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <button onclick="deleteLop(<?php echo $lop['ma_lop']; ?>, '<?php echo htmlspecialchars($lop['ten_lop'], ENT_QUOTES); ?>')" 
                                                    class="btn btn-sm btn-outline-danger" title="Xóa">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                            <a href="<?php echo BASE_URL; ?>/LopHoc/view?id=<?php echo $lop['ma_lop']; ?>" 
                                               class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($data['total_pages'] > 1): ?>
                    <nav aria-label="Phân trang">
                        <ul class="pagination justify-content-center mt-4">
                            <!-- Previous -->
                            <li class="page-item <?php echo ($data['current_page'] <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $data['current_page'] - 1; ?><?php echo !empty($data['filter_khoi']) ? '&khoi=' . $data['filter_khoi'] : ''; ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            <!-- Page numbers -->
                            <?php for ($i = 1; $i <= $data['total_pages']; $i++): ?>
                                <li class="page-item <?php echo ($i == $data['current_page']) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($data['filter_khoi']) ? '&khoi=' . $data['filter_khoi'] : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next -->
                            <li class="page-item <?php echo ($data['current_page'] >= $data['total_pages']) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $data['current_page'] + 1; ?><?php echo !empty($data['filter_khoi']) ? '&khoi=' . $data['filter_khoi'] : ''; ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
                </div>
                <!-- MODAL SỬA LỚP HỌC -->
                <div class="modal fade" id="editLopModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Sửa Lớp Học</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div id="modalError" class="alert alert-danger d-none"></div>
                                <form id="editLopForm">
                                    <input type="hidden" id="maLopHidden" name="ma_lop">
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Tên Lớp</label>
                                            <input type="text" class="form-control" id="tenLopEdit" name="ten_lop" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Khối</label>
                                            <input type="text" class="form-control" id="khoiEdit" name="khoi" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Tổ Hợp Môn</label>
                                            <select class="form-select" id="toHopEdit" name="ma_to_hop" onchange="loadMonHocModal()">
                                                <option value="">-- Chọn tổ hợp --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Phòng Học</label>
                                            <select class="form-select" id="phongHocEdit" name="ma_phong">
                                                <option value="">-- Chọn phòng --</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Giáo Viên Chủ Nhiệm</label>
                                        <select class="form-select" id="gvChuNhiemEdit" name="ma_gvcn">
                                            <option value="">-- Chọn giáo viên --</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Trạng Thái</label>
                                        <select class="form-select" id="trangThaiEdit" name="trang_thai_lop">
                                            <option value="HoatDong">Hoạt động</option>
                                            <option value="TamNghi">Tạm ngưng</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Danh Sách Phân Công Giáo Viên</strong></label>
                                        <div id="phanCongTableEdit" style="max-height: 250px; overflow-y: auto;">
                                            <!-- Table sẽ được render bằng JS -->
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                <button type="button" class="btn btn-primary" onclick="submitSuaLop()">Cập Nhật</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cache danh sách GV và GVCN hiện tại (phục vụ dropdown GVCN)
        let gvListCache = [];
        let currentGvcnCache = null;
        function applyFilterKhoi() {
            const val = document.getElementById('filterKhoi').value;
            const params = new URLSearchParams(window.location.search);
            if (val) params.set('khoi', val); else params.delete('khoi');
            params.delete('page');
            window.location.search = params.toString();
        }

        function deleteLop(ma_lop, ten_lop) {
            if (!confirm(`Bạn có chắc muốn xóa lớp "${ten_lop}"?\n\nLưu ý: Sẽ xóa toàn bộ phân công giáo viên của lớp này!`)) {
                return;
            }

            fetch(BASE_URL + '/LopHoc/delete', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ma_lop: ma_lop})
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Lỗi kết nối!');
            });
        }

        function openEditModal(maLop) {
            showModalError('');
            // Đổi sang form-urlencoded để $_POST nhận được
            fetch(BASE_URL + '/LopHoc/ajaxGetLopData', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'ma_lop=' + encodeURIComponent(maLop)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const lop = data.lop || (data.data && data.data.lop) || {};
                    const phanCong = data.phan_cong || (data.data && data.data.phan_cong) || [];
                    const gvList = data.giao_vien || (data.data && data.data.giao_vien) || [];
                    const toHopList = data.to_hop_list || [];
                    const monHocList = data.mon_hoc || [];
                    const phongTrong = data.phong_trong || [];

                    // Cache để dựng dropdown GVCN động
                    gvListCache = gvList;
                    currentGvcnCache = lop.ma_gvcn || lop.ma_giao_vien_chu_nhiem || '';

                    document.getElementById('maLopHidden').value = lop.ma_lop || '';
                    document.getElementById('tenLopEdit').value = lop.ten_lop || '';
                    document.getElementById('khoiEdit').value = lop.khoi || '';
                    document.getElementById('trangThaiEdit').value = (lop.trang_thai_lop === 'HoatDong' || lop.hoat_dong == 1) ? 'HoatDong' : 'TamNghi';

                    // Populate Tổ hợp
                    const toHopSelect = document.getElementById('toHopEdit');
                    toHopSelect.innerHTML = '<option value="">-- Chọn tổ hợp --</option>';
                    toHopList.forEach(th => {
                        const opt = document.createElement('option');
                        opt.value = th.ma_to_hop_mon;
                        opt.textContent = th.ten_to_hop;
                        if (th.ma_to_hop_mon == lop.ma_to_hop_mon) opt.selected = true;
                        toHopSelect.appendChild(opt);
                    });

                    // Populate phòng học (dùng đúng khóa phong_hoc.ma_phong)
                    const phongSelect = document.getElementById('phongHocEdit');
                    phongSelect.innerHTML = '<option value="">-- Chọn phòng --</option>';
                    let hasCurrentRoom = false;
                    const currentRoom = lop.ma_phong || lop.ma_phong_hoc_chinh;

                    phongTrong.forEach(ph => {
                        const maPhong = ph.ma_phong ?? ph.ma_phong_hoc ?? ph.ma_phong_hoc_chinh;
                        if (!maPhong) return;
                        const opt = document.createElement('option');
                        opt.value = maPhong;
                        opt.textContent = ph.ten_phong;
                        if (String(maPhong) === String(currentRoom)) {
                            opt.selected = true;
                            hasCurrentRoom = true;
                        }
                        phongSelect.appendChild(opt);
                    });

                    // Nếu phòng hiện tại không nằm trong danh sách trống, vẫn hiển thị để giữ giá trị
                    if (!hasCurrentRoom && currentRoom) {
                        const opt = document.createElement('option');
                        opt.value = currentRoom;
                        opt.textContent = lop.ten_phong || ('Phòng ' + currentRoom);
                        opt.selected = true;
                        phongSelect.appendChild(opt);
                    }

                    renderPhanCongModal(monHocList, phanCong, gvList);
                    // Sau khi render phân công, cập nhật dropdown GVCN dựa trên GV đang chọn
                    rebuildGvcnOptions();

                    const modal = new bootstrap.Modal(document.getElementById('editLopModal'));
                    modal.show();
                } else {
                    showModalError(data.error || 'Không thể tải dữ liệu lớp!');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                showModalError('Lỗi khi tải dữ liệu!');
            });
        }

        function renderPhanCongModal(monHocList, phanCongList, gvList) {
            let html = `
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:35%">Môn Học</th>
                            <th style="width:45%">Giáo Viên</th>
                            <th style="width:20%">Tiết/Tuần</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            monHocList.forEach(mon => {
                const pc = phanCongList.find(p => p.ma_mon_hoc == mon.ma_mon_hoc) || {};
                const soTiet = pc.so_tiet_tuan || mon.so_tiet_tuan || 3;
                html += `
                    <tr>
                        <td>
                            <input type="hidden" name="mon_id[]" value="${mon.ma_mon_hoc}">
                            <input type="hidden" name="mon_ten[]" value="${mon.ten_mon_hoc || ''}">
                            <input type="hidden" name="mon_loai[]" value="${mon.loai_mon || ''}">
                            <input type="hidden" name="mon_so_tiet[]" value="${soTiet}">
                            ${mon.ten_mon_hoc || ''}
                        </td>
                        <td>
                            <select class="form-select form-select-sm" name="giao_vien_id[]">
                                <option value="">-- Chọn giáo viên --</option>
                `;
                gvList
                    .filter(gv => !mon.ma_mon_hoc || gv.ma_mon_hoc == mon.ma_mon_hoc)
                    .forEach(gv => {
                        const selected = gv.ma_giao_vien == pc.ma_giao_vien ? 'selected' : '';
                        html += `<option value="${gv.ma_giao_vien}" ${selected}>${gv.ten_giao_vien || gv.ho_ten || ''}</option>`;
                    });
                html += `
                            </select>
                        </td>
                        <td>${soTiet}</td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
            `;

            document.getElementById('phanCongTableEdit').innerHTML = html;

            // Khi đổi giáo viên ở từng môn, cập nhật lại dropdown GVCN
            document.querySelectorAll('select[name="giao_vien_id[]"]').forEach(sel => {
                sel.addEventListener('change', rebuildGvcnOptions);
            });

            // Lần đầu render, xây dropdown GVCN
            rebuildGvcnOptions();
        }

        // Xây lại dropdown GVCN dựa trên các giáo viên đang được chọn trong phân công
        function rebuildGvcnOptions() {
            const gvSelect = document.getElementById('gvChuNhiemEdit');
            if (!gvSelect) return;

            const selectedGvIds = new Set();
            document.querySelectorAll('select[name="giao_vien_id[]"]').forEach(sel => {
                if (sel.value) selectedGvIds.add(String(sel.value));
            });
            if (currentGvcnCache) selectedGvIds.add(String(currentGvcnCache));

            const oldVal = gvSelect.value;
            const selectedVal = oldVal || (currentGvcnCache ? String(currentGvcnCache) : '');

            gvSelect.innerHTML = '<option value="">-- Chọn giáo viên --</option>';
            selectedGvIds.forEach(id => {
                const gv = gvListCache.find(g => String(g.ma_giao_vien) === String(id)) || {};
                const opt = document.createElement('option');
                opt.value = id;
                opt.textContent = gv.ten_giao_vien || gv.ho_ten || ('GV #' + id);
                if (String(id) === selectedVal) opt.selected = true;
                gvSelect.appendChild(opt);
            });
        }

        function loadMonHocModal() {
            const maLop = document.getElementById('maLopHidden').value;
            const maToHop = document.getElementById('toHopEdit').value;
            if (!maLop || !maToHop) return;

            fetch(BASE_URL + '/LopHoc/ajaxGetDataForEdit', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'ma_lop=' + encodeURIComponent(maLop) + '&ma_to_hop=' + encodeURIComponent(maToHop)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const monHocList = data.mon_hoc || [];
                    const phanCongList = data.phan_cong || [];
                    const gvList = data.giao_vien || [];
                    renderPhanCongModal(monHocList, phanCongList, gvList);
                } else {
                    showModalError(data.error || 'Không tải được môn học');
                }
            })
            .catch(err => {
                console.error('Error loadMonHocModal:', err);
                showModalError('Lỗi khi tải môn học');
            });
        }

        function submitSuaLop() {
            const form = document.getElementById('editLopForm');
            const formData = new FormData(form);

            // Kiểm tra bắt buộc
            const required = ['ten_lop','khoi','ma_to_hop','ma_phong'];
            for (const field of required) {
                if (!formData.get(field)) {
                    showModalError('Vui lòng nhập đầy đủ thông tin bắt buộc');
                    return;
                }
            }

            // Kiểm tra điều kiện môn tự chọn: >=4 và mỗi nhóm KHTN/KHXH/CN-NT >=1
            const monIds = formData.getAll('mon_id[]');
            const monLoai = formData.getAll('mon_loai[]');
            const gvIds = formData.getAll('giao_vien_id[]');
            const groupCount = { KHTN: 0, KHXH: 0, 'CN-NT': 0 };
            let elective = 0;

            monIds.forEach((mid, idx) => {
                const loai = (monLoai[idx] || '').toString();
                const gv = gvIds[idx] || '';
                if (!mid || !gv) return; // chỉ tính môn đã gán GV
                if (loai.toLowerCase().includes('bắt buộc')) return;
                elective++;
                ['KHTN','KHXH','CN-NT'].forEach(grp => {
                    if (loai.indexOf(grp) !== -1) groupCount[grp]++;
                });
            });

            if (elective < 4 || groupCount.KHTN === 0 || groupCount.KHXH === 0 || groupCount['CN-NT'] === 0) {
                showModalError('Cần ≥4 môn tự chọn và mỗi nhóm KHTN/KHXH/CN-NT phải có ≥1 môn.');
                return;
            }

            fetch(BASE_URL + '/LopHoc/update', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification('Cập nhật lớp thành công!', 'success');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editLopModal'));
                    modal.hide();
                    setTimeout(() => location.reload(), 800);
                } else {
                    showModalError(data.message || data.error || 'Cập nhật thất bại!');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                showModalError('Lỗi cập nhật lớp!');
            });
        }

        function showModalError(message) {
            const errorDiv = document.getElementById('modalError');
            if (message) {
                errorDiv.textContent = message;
                errorDiv.classList.remove('d-none');
            } else {
                errorDiv.classList.add('d-none');
            }
        }

        function showNotification(message, type = 'info') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000);
        }
    </script>
</body>
</html>
