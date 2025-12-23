<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo (isset($data['mode']) && $data['mode'] === 'edit') ? 'Sửa Lớp Học' : 'Thêm Lớp Học Mới'; ?> | THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background: #f5f7fa; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .card-header-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0; padding: 20px; font-weight: 600; }
        .form-label { font-weight: 600; color: #2c3e50; margin-top: 15px; }
        .form-control, .form-select { border: 1px solid #ddd; border-radius: 8px; }
        .form-control:focus, .form-select:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
        .table-custom thead { background: #f8f9fa; }
        .table-custom th { font-weight: 600; color: #2c3e50; border-bottom: 2px solid #ddd; }
        .btn-primary-custom { background: #667eea; border: none; border-radius: 8px; }
        .btn-primary-custom:hover { background: #5568d3; }
        .badge-primary { background: #667eea; }
        .badge-success { background: #2ecc71; }
        .loading-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 9999; }
        .spinner-border { color: white; }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <header class="mb-4 p-4 bg-white rounded-3 shadow-sm d-flex justify-content-between align-items-center">
            <div>
                <h2 class="m-0">
                    <i class="bi bi-bookmarks"></i> 
                    <?php echo (isset($data['mode']) && $data['mode'] === 'edit') ? 'Sửa Lớp: ' . htmlspecialchars($data['lop']['ten_lop'] ?? '') : 'Thêm Lớp Học Mới'; ?>
                </h2>
                <small class="text-muted">
                    <?php echo (isset($data['mode']) && $data['mode'] === 'edit') ? 'Cập nhật thông tin lớp và phân công giáo viên.' : 'Tạo lớp mới, xếp phòng và phân công giáo viên giảng dạy.'; ?>
                </small>
            </div>
            <a href="<?php echo BASE_URL; ?>/LopHoc" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left"></i> Quay lại Danh sách
            </a>
        </header>

        <div class="card card-custom">
            <div class="card-header-custom">
                <i class="bi bi-<?php echo (isset($data['mode']) && $data['mode'] === 'edit') ? 'pencil-square' : 'plus-circle'; ?>"></i> 
                <?php echo (isset($data['mode']) && $data['mode'] === 'edit') ? 'Sửa Lớp Học' : 'Thêm Lớp Học Mới'; ?>
            </div>
            <div class="card-body p-4">
                <form id="formTaoLop" method="POST" action="<?php echo (isset($data['mode']) && $data['mode'] === 'edit') ? BASE_URL . '/LopHoc/update' : BASE_URL . '/LopHoc/store'; ?>">
                    
                    <?php if (isset($data['mode']) && $data['mode'] === 'edit'): ?>
                        <input type="hidden" name="ma_lop" value="<?php echo $data['lop']['ma_lop']; ?>">
                    <?php endif; ?>
                    
                    <h5 class="mb-3 text-primary"><i class="bi bi-gear"></i> Cấu Hình Chung</h5>
                    
                    <input type="hidden" id="ma_nam_hoc" value="<?php echo $data['ma_nam_hoc']; ?>">
                    <input type="hidden" id="ma_truong" value="<?php echo $data['ma_truong']; ?>">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Năm học</label>
                            <input type="text" class="form-control" value="2024-2025" disabled>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Khối Lớp <span class="text-danger">*</span></label>
                            <select id="selectKhoi" name="khoi" class="form-select" required>
                                <option value="">-- Chọn khối --</option>
                                <option value="10" <?php echo (isset($data['lop']['khoi']) && $data['lop']['khoi'] == 10) ? 'selected' : ''; ?>>Khối 10</option>
                                <option value="11" <?php echo (isset($data['lop']['khoi']) && $data['lop']['khoi'] == 11) ? 'selected' : ''; ?>>Khối 11</option>
                                <option value="12" <?php echo (isset($data['lop']['khoi']) && $data['lop']['khoi'] == 12) ? 'selected' : ''; ?>>Khối 12</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên Lớp <?php echo (isset($data['mode']) && $data['mode'] === 'edit') ? '(Read-only)' : '(Tự động sinh)'; ?></label>
                            <input type="text" id="inputTenLop" name="ten_lop" class="form-control" 
                                   placeholder="Sẽ tự động sinh khi chọn khối" 
                                   value="<?php echo (isset($data['lop']['ten_lop'])) ? htmlspecialchars($data['lop']['ten_lop']) : ''; ?>"
                                   <?php echo (isset($data['mode']) && $data['mode'] === 'edit') ? 'readonly' : ''; ?>>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Phòng Học <span class="text-danger">*</span></label>
                            <select id="selectPhong" name="ma_phong" class="form-select" required>
                                <option value="">-- Vui lòng chọn Phòng --</option>
                                <?php if (isset($data['mode']) && $data['mode'] === 'edit' && isset($data['phong_trong'])): ?>
                                    <?php foreach ($data['phong_trong'] as $p): ?>
                                        <option value="<?php echo $p['ma_phong']; ?>" 
                                                <?php echo ($p['ma_phong'] == $data['lop']['ma_phong_hoc_chinh']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($p['ten_phong']); ?> (Sức chứa: <?php echo $p['suc_chua']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Tổ Hợp Môn <span class="text-danger">*</span></label>
                            <select id="selectToHop" name="ma_to_hop" class="form-select" required>
                                <option value="">-- Chọn tổ hợp --</option>
                                <?php if (!empty($data['to_hop_list'])): ?>
                                    <?php foreach ($data['to_hop_list'] as $th): ?>
                                        <option value="<?php echo $th['ma_to_hop_mon']; ?>" 
                                                <?php echo (isset($data['lop']['ma_to_hop_mon']) && $data['lop']['ma_to_hop_mon'] == $th['ma_to_hop_mon']) ? 'selected' : ''; ?>>
                                            <?php echo $th['ten_to_hop']; ?><?php echo !empty($th['mo_ta']) ? ' - ' . $th['mo_ta'] : ''; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <h5 class="mb-3 text-primary"><i class="bi bi-book"></i> Danh Sách Môn Học & Phân Công</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-custom table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Môn Học</th>
                                    <th>Số Tiết / Tuần</th>
                                    <th>Giáo Viên Giảng Dạy</th>
                                </tr>
                            </thead>
                            <tbody id="bodyPhanCong">
                                <?php if (isset($data['mode']) && $data['mode'] === 'edit' && !empty($data['phan_cong'])): ?>
                                    <?php 
                                    foreach ($data['phan_cong'] as $idx => $pc): 
                                        $ma_mon = $pc['ma_mon_hoc'];
                                        $ten_mon = $pc['ten_mon_hoc'];
                                        $loai_mon = $pc['loai_mon'] ?? 'Tự chọn';
                                        $so_tiet = $pc['so_tiet_tuan'];
                                        $ma_gv_hien_tai = $pc['ma_giao_vien'];

                                        $badge = ($loai_mon === 'Bắt buộc') 
                                            ? '<span class="badge bg-success ms-2">Bắt buộc</span>' 
                                            : '<span class="badge bg-info ms-2">Tự chọn</span>';
                                    ?>
                                    <tr>
                                        <td class="text-center fw-bold text-secondary"><?php echo $idx + 1; ?></td>
                                        <td>
                                            <span class="fw-bold text-primary"><?php echo htmlspecialchars($ten_mon); ?></span>
                                            <?php echo $badge; ?>
                                            <input type="hidden" name="mon_id[<?php echo $idx; ?>]" value="<?php echo $ma_mon; ?>">
                                            <input type="hidden" name="mon_ten[<?php echo $idx; ?>]" value="<?php echo htmlspecialchars($ten_mon); ?>">
                                            <input type="hidden" name="mon_loai[<?php echo $idx; ?>]" value="<?php echo htmlspecialchars($loai_mon); ?>">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm text-center" style="max-width: 80px;" 
                                                   name="mon_so_tiet[<?php echo $idx; ?>]" value="<?php echo $so_tiet; ?>" min="1">
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm select-gv" name="giao_vien_id[<?php echo $idx; ?>]" required>
                                                <option value="">-- Chọn GV --</option>
                                                <?php 
                                                if (!empty($data['giao_vien'])) {
                                                    $printed_gv = []; 
                                                    foreach ($data['giao_vien'] as $gv) {
                                                        // Logic gợi ý GV: Tên môn trong DB GV phải chứa tên môn đang xét (hoặc là người đang được chọn)
                                                        $is_teach = (stripos($gv['ten_mon_hoc'] ?? '', $ten_mon) !== false);
                                                        
                                                        if ($is_teach || $gv['ma_giao_vien'] == $ma_gv_hien_tai) {
                                                            if (!in_array($gv['ma_giao_vien'], $printed_gv)) {
                                                                $selected = ($gv['ma_giao_vien'] == $ma_gv_hien_tai) ? 'selected' : '';
                                                                echo "<option value='{$gv['ma_giao_vien']}' data-name='{$gv['ho_ten']}' $selected>{$gv['ho_ten']}</option>";
                                                                $printed_gv[] = $gv['ma_giao_vien'];
                                                            }
                                                        }
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <?php echo (isset($data['mode']) && $data['mode'] === 'edit') 
                                                ? 'Chưa có dữ liệu phân công.' 
                                                : 'Vui lòng chọn Tổ hợp môn ở trên để tải danh sách môn học.'; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mb-3 text-primary"><i class="bi bi-person-badge"></i> Chọn Giáo Viên Chủ Nhiệm (GVCN)</h5>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Chỉ có thể chọn GVCN từ danh sách các giáo viên bộ môn đã phân công ở trên.
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Giáo Viên Chủ Nhiệm <span class="text-danger">*</span></label>
                            
                            <select id="selectGVCN" name="ma_gvcn" class="form-select" required>
                                <option value="">-- Vui lòng chọn phân công môn học trước --</option>
                            </select>
                        </div>
                        <?php if (isset($data['mode']) && $data['mode'] === 'edit'): ?>
                            <div class="col-md-6">
                                <label class="form-label">Trạng Thái Lớp</label>
                                <select id="selectTrangThai" name="trang_thai_lop" class="form-select">
                                    <option value="HoatDong" <?php echo ($data['lop']['trang_thai_lop'] === 'HoatDong') ? 'selected' : ''; ?>>Hoạt Động</option>
                                    <option value="TamNghi" <?php echo ($data['lop']['trang_thai_lop'] === 'TamNghi') ? 'selected' : ''; ?>>Tạm Nghị</option>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-3 justify-content-center mt-5">
                        <button type="submit" class="btn btn-primary-custom btn-lg px-5">
                            <i class="bi bi-<?php echo (isset($data['mode']) && $data['mode'] === 'edit') ? 'check-circle' : 'check-circle'; ?>"></i> 
                            <?php echo (isset($data['mode']) && $data['mode'] === 'edit') ? 'Cập nhật' : 'Hoàn Tất'; ?>
                        </button>
                        <a href="<?php echo BASE_URL; ?>/LopHoc" class="btn btn-light btn-lg px-5">
                            <i class="bi bi-x-circle"></i> Hủy bỏ
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="mainLoading">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        const BASE_URL = "<?php echo BASE_URL ?? '/PTUD_Project_QLHS_THPT/PTUD_Project_QuanLy_HocSinh_THPT/public'; ?>";
        const namHoc = $("#ma_nam_hoc").val();

        function toggleLoading(show) {
            if (show) $("#mainLoading").css('display', 'flex');
            else $("#mainLoading").hide();
        }

        // =========================================================================
        // [QUAN TRỌNG] HÀM CẬP NHẬT LIST GVCN TỪ BẢNG PHÂN CÔNG
        // =========================================================================
        function updateGVCNList() {
            const gvcnSelect = $('#selectGVCN');
            // Lấy giá trị GVCN hiện tại (đang được chọn hoặc từ DB khi sửa)
            // Nếu PHP có đổ dữ liệu sẵn (khi edit), ta lấy từ biến PHP
            const currentGVCNFromPHP = "<?php echo $data['lop']['ma_gvcn'] ?? ''; ?>";
            const currentSelected = gvcnSelect.val();
            
            // Ưu tiên: Giá trị người dùng đang chọn -> Giá trị từ DB -> Rỗng
            const targetValue = currentSelected || currentGVCNFromPHP;

            const uniqueTeachers = new Map();

            // Quét tất cả select GV bộ môn
            $('select[name^="giao_vien_id"]').each(function() {
                const teacherId = $(this).val();
                const teacherName = $(this).find('option:selected').text();

                if (teacherId && teacherId !== "") {
                    // Chỉ thêm nếu có tên (tránh option --Chọn--)
                    if (!uniqueTeachers.has(teacherId) && !teacherName.includes('--')) {
                        uniqueTeachers.set(teacherId, teacherName);
                    }
                }
            });

            // Xóa cũ & Thêm option mới
            gvcnSelect.empty();
            
            if (uniqueTeachers.size === 0) {
                gvcnSelect.append('<option value="">-- Vui lòng chọn phân công môn học trước --</option>');
            } else {
                gvcnSelect.append('<option value="">-- Chọn GVCN --</option>');
                
                uniqueTeachers.forEach((name, id) => {
                    const isSelected = (String(id) === String(targetValue)) ? 'selected' : '';
                    gvcnSelect.append(`<option value="${id}" ${isSelected}>${name}</option>`);
                });
            }
        }

        // Kích hoạt khi thay đổi bất kỳ GV bộ môn nào
        $(document).on('change', 'select[name^="giao_vien_id"]', function() {
            updateGVCNList();
        });

        // Nếu đang ở trang Sửa, chạy 1 lần lúc load trang để fill list GVCN
        if ($('input[name="ma_lop"]').length > 0) {
            updateGVCNList();
        }

        // =========================================================================
        // CÁC LOGIC KHÁC (GIỮ NGUYÊN)
        // =========================================================================

        // 1. Chọn Khối -> Sinh tên lớp + Lấy phòng
        $("#selectKhoi").change(function() {
            const khoi = $(this).val();
            if (!khoi) {
                $("#inputTenLop").val("").attr('placeholder', 'Vui lòng chọn khối');
                $("#selectPhong").html('<option value="">-- Chọn khối trước --</option>');
                return;
            }
            toggleLoading(true);
            $.post(BASE_URL + '/LopHoc/ajaxGenerateTenLop', {khoi: khoi, nam_hoc: namHoc}, function(res) {
                if (res.success) $("#inputTenLop").val(res.ten_lop);
            });
            $.post(BASE_URL + '/LopHoc/ajaxGetPhongTrong', {nam_hoc: namHoc}, function(res) {
                toggleLoading(false);
                if (res.success) {
                    let html = '<option value="">-- Chọn phòng học --</option>';
                    if (res.phong_trong && res.phong_trong.length > 0) {
                        res.phong_trong.forEach(p => {
                            html += `<option value="${p.ma_phong}">${p.ten_phong} (Sức chứa: ${p.suc_chua})</option>`;
                        });
                    } else {
                        html += '<option disabled>Hết phòng trống!</option>';
                    }
                    $("#selectPhong").html(html);
                } else {
                    alert("Lỗi: " + (res.error || "Không thể tải phòng"));
                }
            }).fail(function() { toggleLoading(false); });
        });

        // 2. Chọn Tổ Hợp -> Load Môn & GV
        $("#selectToHop").change(function() {
            const toHop = $(this).val();
            if (!toHop) {
                $("#bodyPhanCong").html(`<tr><td colspan="4" class="text-center text-muted py-4">Vui lòng chọn Tổ hợp môn.</td></tr>`);
                return;
            }
            toggleLoading(true);
            $.post(BASE_URL + '/LopHoc/ajaxGetMonVaGiaoVien', {ma_to_hop: toHop}, function(res) {
                toggleLoading(false);
                if (!res.success) {
                    alert("Lỗi: " + (res.error || "Không thể tải dữ liệu"));
                    return;
                }
                const gvList = res.giao_vien || [];
                const monList = res.mon_hoc || [];
                let htmlMon = '';

                if (monList.length === 0) {
                    htmlMon = `<tr><td colspan="4" class="text-center text-warning">Tổ hợp này không có môn học!</td></tr>`;
                } else {
                    monList.forEach((mon, idx) => {
                        let optGV = '<option value="">-- Chọn GV --</option>';
                        // Lọc GV theo môn (tương đối)
                        const gvTheoMon = gvList.filter(gv => 
                             gv.ten_mon_hoc && gv.ten_mon_hoc.toLowerCase().includes(mon.ten_mon_hoc.toLowerCase())
                        );
                        // Unique
                        const uniqueGV = [...new Map(gvTheoMon.map(gv => [gv.ma_giao_vien, gv])).values()];

                        if (uniqueGV.length > 0) {
                            uniqueGV.forEach(gv => {
                                optGV += `<option value="${gv.ma_giao_vien}">${gv.ho_ten}</option>`;
                            });
                        } else {
                            optGV += '<option disabled>Chưa có GV bộ môn này</option>';
                        }

                        const soTiet = mon.so_tiet_hk1 || 3;
                        const badge = mon.loai === 'bat_buoc' 
                            ? '<span class="badge bg-success ms-2">Bắt buộc</span>' 
                            : '<span class="badge bg-info ms-2">Tự chọn</span>';

                        htmlMon += `
                            <tr>
                                <td class="text-center fw-bold text-secondary">${idx + 1}</td>
                                <td>
                                    <span class="fw-bold text-primary">${mon.ten_mon_hoc}</span>
                                    ${badge}
                                    <input type="hidden" name="mon_id[${idx}]" value="${mon.ma_mon_hoc}">
                                    <input type="hidden" name="mon_ten[${idx}]" value="${mon.ten_mon_hoc}">
                                    <input type="hidden" name="mon_loai[${idx}]" value="${mon.loai_mon}">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm text-center" style="max-width: 80px;" 
                                           name="mon_so_tiet[${idx}]" value="${soTiet}" min="1">
                                </td>
                                <td>
                                    <select class="form-select form-select-sm select-gv" name="giao_vien_id[${idx}]" required>
                                        ${optGV}
                                    </select>
                                </td>
                            </tr>
                        `;
                    });
                }
                $("#bodyPhanCong").html(htmlMon);
                
                // Sau khi render xong bảng môn học, cập nhật list GVCN
                updateGVCNList();

            }).fail(function() {
                toggleLoading(false);
                alert("Lỗi kết nối Server!");
            });
        });

        // 3. Submit Form
        $("#formTaoLop").submit(function(e) {
            e.preventDefault();
            const khoi = $("#selectKhoi").val();
            const toHop = $("#selectToHop").val();
            const phong = $("#selectPhong").val();
            const gvcn = $("#selectGVCN").val();

            if (!khoi || !toHop || !phong || !gvcn) {
                alert("Vui lòng điền đầy đủ thông tin (Khối, Tổ hợp, Phòng, GVCN)!");
                return;
            }
            toggleLoading(true);
            const formData = new FormData(this);
            formData.append('ma_nam_hoc', namHoc);
            
            const isEdit = $("input[name='ma_lop']").length > 0;
            const submitUrl = isEdit ? (BASE_URL + '/LopHoc/update') : (BASE_URL + '/LopHoc/store');

            $.ajax({
                url: submitUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    toggleLoading(false);
                    if (res.success) {
                        alert(res.message || (isEdit ? "Cập nhật thành công!" : "Tạo lớp thành công!"));
                        window.location.href = BASE_URL + '/LopHoc';
                    } else {
                        alert("Lỗi: " + (res.error || res.message || "Không thể xử lý yêu cầu"));
                    }
                },
                error: function(xhr, status, error) {
                    toggleLoading(false);
                    console.error(xhr.responseText);
                    alert("Lỗi hệ thống: " + error);
                }
            });
        });
    });
    </script>
</body>
</html>