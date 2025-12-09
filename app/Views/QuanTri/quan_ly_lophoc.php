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
        <!-- Header -->
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

        <!-- Main Form Card -->
            <div class="card card-custom">
                <div class="card-header-custom">
                    <i class="bi bi-<?php echo (isset($data['mode']) && $data['mode'] === 'edit') ? 'pencil-square' : 'plus-circle'; ?>"></i> 
                    <?php echo (isset($data['mode']) && $data['mode'] === 'edit') ? 'Sửa Lớp Học' : 'Thêm Lớp Học Mới'; ?>
                </div>
            <div class="card-body p-4">
                <form id="formTaoLop" method="POST" action="<?php echo (isset($data['mode']) && $data['mode'] === 'edit') ? BASE_URL . '/LopHoc/update' : BASE_URL . '/LopHoc/store'; ?>">
                    
                    <!-- Hidden field: Mã lớp (khi sửa) -->
                    <?php if (isset($data['mode']) && $data['mode'] === 'edit'): ?>
                        <input type="hidden" name="ma_lop" value="<?php echo $data['lop']['ma_lop']; ?>">
                    <?php endif; ?>
                    
                    <!-- 1. CẤU HÌNH CHUNG -->
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

                    <!-- 2. DANH SÁCH MÔN HỌC & PHÂN CÔNG -->
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
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        Vui lòng chọn Tổ hợp môn ở trên để tải danh sách môn học.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 3. GIÁO VIÊN CHỦ NHIỆM -->
                    <h5 class="mb-3 text-primary"><i class="bi bi-person-badge"></i> Chọn Giáo Viên Chủ NhiỆm (GVCN)</h5>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Chỉ có thể chọn GVCN từ danh sách các giáo viên bộ môn đã phân công ở trên.
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Giáo Viên Chủ NhiỆm <span class="text-danger">*</span></label>
                            <select id="selectGVCN" name="ma_gvcn" class="form-select" required>
                                <option value="">-- Chọn GVCN --</option>
                                <?php if (isset($data['mode']) && $data['mode'] === 'edit' && isset($data['giao_vien'])): ?>
                                    <?php foreach ($data['giao_vien'] as $gv): ?>
                                        <option value="<?php echo $gv['ma_giao_vien']; ?>" 
                                                <?php echo ($gv['ma_giao_vien'] == ($data['lop']['ma_gvcn'] ?? '')) ? 'selected' : ''; ?>
                                                data-name="<?php echo htmlspecialchars($gv['ho_ten']); ?>">
                                            <?php echo htmlspecialchars($gv['ho_ten']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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

                    <!-- Buttons -->
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

    <!-- Loading Overlay -->
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

        // ===== 1. XỬ LÝ CHỌN KHỐI =====
        $("#selectKhoi").change(function() {
            const khoi = $(this).val();
            
            if (!khoi) {
                $("#inputTenLop").val("").attr('placeholder', 'Vui lòng chọn khối');
                $("#selectPhong").html('<option value="">-- Chọn khối trước --</option>');
                return;
            }

            toggleLoading(true);

            // 1a. Sinh tên lớp
            $.post(BASE_URL + '/LopHoc/ajaxGenerateTenLop', {khoi: khoi, nam_hoc: namHoc}, function(res) {
                if (res.success) {
                    $("#inputTenLop").val(res.ten_lop);
                } else {
                    alert("Lỗi: " + (res.error || "Không thể sinh tên lớp"));
                }
            });

            // 1b. Lấy danh sách phòng trống
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
            }).fail(function(xhr, status, error) {
                toggleLoading(false);
                console.error("AJAX Error:", status, error);
                alert("Lỗi kết nối Server!");
            });
        });

        // ===== 2. XỬ LÝ CHỌN TỔ HỢP MÔN =====
        $("#selectToHop").change(function() {
            const toHop = $(this).val();
            
            if (!toHop) {
                $("#bodyPhanCong").html(`
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Vui lòng chọn Tổ hợp môn để tải danh sách.
                        </td>
                    </tr>
                `);
                return;
            }

            toggleLoading(true);

            $.post(BASE_URL + '/LopHoc/ajaxGetMonVaGiaoVien', {ma_to_hop: toHop}, function(res) {
                toggleLoading(false);
                
                if (!res.success) {
                    alert("Lỗi: " + (res.error || "Không thể tải môn học"));
                    return;
                }

                console.log('Response data:', res); // Debug log
                
                let html = '';
                const gvList = res.giao_vien || [];
                const monList = res.mon_hoc || [];

                console.log('Danh sách GV:', gvList); // Debug log
                console.log('Danh sách môn:', monList); // Debug log

                if (monList.length === 0) {
                    html = `<tr><td colspan="4" class="text-center text-warning">Tổ hợp này không có môn học!</td></tr>`;
                } else {
                    monList.forEach((mon, idx) => {
                        let optGV = '<option value="">-- Chọn GV --</option>';
                        
                        // Lọc GV theo môn học cụ thể (mỗi GV chỉ hiện 1 lần)
                        const gvTheoMon = gvList.filter(gv => 
                            gv.ten_mon_hoc && gv.ten_mon_hoc.toLowerCase().includes(mon.ten_mon_hoc.toLowerCase())
                        );
                        
                        // Populate dropdown giáo viên (loại bỏ duplicate)
                        const uniqueGV = [...new Map(gvTheoMon.map(gv => [gv.ma_giao_vien, gv])).values()];
                        
                        if (uniqueGV.length > 0) {
                            uniqueGV.forEach(gv => {
                                optGV += `<option value="${gv.ma_giao_vien}" data-name="${gv.ho_ten}">${gv.ho_ten}</option>`;
                            });
                        } else {
                            optGV += '<option disabled>Không có GV dạy môn này</option>';
                        }

                        const soTiet = mon.so_tiet_hk1 || 3;
                        const badge = mon.loai === 'bat_buoc' 
                            ? '<span class="badge bg-success ms-2">Bắt buộc</span>' 
                            : '<span class="badge bg-info ms-2">Tự chọn</span>';

                        html += `
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

                $("#bodyPhanCong").html(html);
                updateGVCNList();
            }).fail(function(xhr, status, error) {
                toggleLoading(false);
                console.error("AJAX Error:", status, error);
                alert("Lỗi kết nối Server!");
            });
        });

        // ===== 3. CẬP NHẬT DANH SÁCH GVCN KHI CHỌN GV =====
        $(document).on('change', '.select-gv', function() {
            updateGVCNList();
        });

        function updateGVCNList() {
            const listGV = [];
            
            $(".select-gv").each(function() {
                const gvId = $(this).val();
                const gvName = $(this).find(':selected').data('name');
                
                if (gvId && gvName) {
                    // Check trùng lặp
                    if (!listGV.find(g => g.id === parseInt(gvId))) {
                        listGV.push({id: parseInt(gvId), name: gvName});
                    }
                }
            });

            const currentSelected = $("#selectGVCN").val();
            let html = '<option value="">-- Chọn GVCN --</option>';
            
            if (listGV.length > 0) {
                listGV.forEach(gv => {
                    const selected = gv.id == currentSelected ? 'selected' : '';
                    html += `<option value="${gv.id}" ${selected}>${gv.name}</option>`;
                });
            } else {
                html += '<option disabled>(Vui lòng chọn GV bộ môn trước)</option>';
            }
            
            $("#selectGVCN").html(html);
        }

        // ===== 4. SUBMIT FORM =====
        $("#formTaoLop").submit(function(e) {
            e.preventDefault();
            
            // Validation
            const khoi = $("#selectKhoi").val();
            const toHop = $("#selectToHop").val();
            const phong = $("#selectPhong").val();
            const gvcn = $("#selectGVCN").val();

            if (!khoi || !toHop || !phong || !gvcn) {
                alert("Vui lòng điền đầy đủ thông tin!");
                return;
            }

            // Kiểm tra điều kiện môn tự chọn: >=4 và mỗi nhóm KHTN/KHXH/CN-NT >=1 (chỉ tính môn đã gán GV)
            const monLoai = $("input[name^='mon_loai']").map(function(){ return $(this).val(); }).get();
            const gvIds = $("select[name^='giao_vien_id']").map(function(){ return $(this).val(); }).get();
            const groupCount = { KHTN: 0, KHXH: 0, 'CN-NT': 0 };
            let elective = 0;

            monLoai.forEach((loai, idx) => {
                const gv = gvIds[idx] || '';
                if (!gv) return; // chỉ tính môn đã gán GV
                const lower = (loai || '').toLowerCase();
                if (lower.includes('bắt buộc')) return;
                elective++;
                ['KHTN','KHXH','CN-NT'].forEach(grp => {
                    if ((loai || '').indexOf(grp) !== -1) groupCount[grp]++;
                });
            });

            if (elective < 4 || groupCount.KHTN === 0 || groupCount.KHXH === 0 || groupCount['CN-NT'] === 0) {
                alert('Cần ≥4 môn tự chọn và mỗi nhóm KHTN/KHXH/CN-NT phải có ≥1 môn.');
                return;
            }

            toggleLoading(true);

            const formData = new FormData(this);
            formData.append('ma_nam_hoc', namHoc);
            
            // Xác định URL submit dựa trên mode
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
                        alert(res.message || (isEdit ? "Cập nhật lớp thành công!" : "Tạo lớp thành công!"));
                        window.location.href = BASE_URL + '/LopHoc';
                    } else {
                        alert("Lỗi: " + (res.error || "Không thể xử lý"));
                    }
                },
                error: function(xhr, status, error) {
                    toggleLoading(false);
                    console.error("AJAX Error:", status, error);
                    alert("Lỗi hệ thống: " + error);
                }
            });
        });
    });
    </script>
</body>
</html>
