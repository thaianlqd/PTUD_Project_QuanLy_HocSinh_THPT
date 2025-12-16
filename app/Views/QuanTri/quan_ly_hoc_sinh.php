<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Học Sinh | THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary fw-bold"><i class="bi bi-mortarboard-fill"></i> QUẢN LÝ HỌC SINH</h2>
        <div>
            <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-secondary me-2">Quay lại</a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-circle"></i> Thêm Học Sinh
            </button>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form action="" method="GET" class="d-flex gap-2">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm theo tên, mã HS, SĐT..." value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
            </form>
        </div>
    </div>

    <?php 
        $list_hs     = $data['hocsinhList'] ?? [];
        $list_lop    = $data['classes'] ?? [];
        $currentPage = $data['currentPage'] ?? 1;
        $totalPages  = $data['totalPages']  ?? 1;
        $totalCount  = $data['totalCount']  ?? count($list_hs);
    ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Họ Tên</th>
                        <th>Lớp</th>
                        <th>Ngày Sinh</th>
                        <th>SĐT / Email</th>
                        <th>Trạng Thái</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($list_hs) && is_array($list_hs)): ?>
                        <?php foreach ($list_hs as $hs): ?>
                            <tr data-hs='<?php echo htmlspecialchars(json_encode($hs), ENT_QUOTES, 'UTF-8'); ?>'>
                                <td>
                                    <strong><?php echo htmlspecialchars($hs['ho_ten']); ?></strong><br>
                                    <small class="text-muted">Mã: <?php echo $hs['ma_hoc_sinh']; ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark"><?php echo $hs['ten_lop'] ?? 'Chưa xếp'; ?></span>
                                </td>
                                <td><?php echo !empty($hs['ngay_sinh']) ? date('d/m/Y', strtotime($hs['ngay_sinh'])) : 'N/A'; ?></td>
                                <td>
                                    <i class="bi bi-telephone"></i> <?php echo $hs['so_dien_thoai'] ?? 'N/A'; ?><br>
                                    <small class="text-muted"><?php echo $hs['email'] ?? 'N/A'; ?></small>
                                </td>
                                <td>
                                    <?php if (($hs['trang_thai'] ?? 'DangHoc') == 'DangHoc'): ?>
                                        <span class="badge bg-success">Đang học</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><?php echo $hs['trang_thai'] ?? 'Unknown'; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary btn-edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center p-4">Không có dữ liệu</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Phân trang -->
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php if ($currentPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=1">« Đầu</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>">‹ Trước</a>
                </li>
            <?php endif; ?>
            
            <?php 
                $start = max(1, $currentPage - 2);
                $end = min($totalPages, $currentPage + 2);
                for ($i = $start; $i <= $end; $i++): 
            ?>
                <li class="page-item <?php echo $i === (int)$currentPage ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>">Tiếp ›</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $totalPages; ?>">Cuối »</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="text-center text-muted mt-2 mb-4">
        <small>Trang <?php echo $currentPage; ?> / <?php echo $totalPages; ?> (Tổng: <?php echo $totalCount; ?> học sinh)</small>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Thêm Học Sinh Mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addForm">
                    <input type="hidden" id="addSchoolId" value="<?php echo $data['school_id'] ?? $school_id ?? 0; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Họ tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="addName" required>
                        </div>
                        <div class="col-md-6">
                            <label>Email (Tên đăng nhập) <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="addEmail" required>
                        </div>
                        <div class="col-md-6">
                            <label>Mật khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="addPass" placeholder="Nhập mật khẩu" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="fillDefaultPass()">Tự phát sinh</button>
                            </div>
                            <small class="text-muted">Nhấn "Tự phát sinh" để tự điền mật khẩu mặc định 123456@A.</small>
                        </div>
                        <div class="col-md-6">
                            <label>Lớp học <span class="text-danger">*</span></label>
                            <select class="form-select" id="addClass" required>
                                <option value="">-- Chọn lớp --</option>
                                <?php foreach($list_lop as $lop): ?>
                                    <option value="<?php echo $lop['ma_lop']; ?>"><?php echo $lop['ten_lop']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Ngày sinh</label>
                            <input type="date" class="form-control" id="addDob">
                        </div>
                        <div class="col-md-6">
                            <label>Giới tính</label>
                            <select class="form-select" id="addGender">
                                <option value="Nam">Nam</option>
                                <option value="Nu">Nữ</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>SĐT</label>
                            <input type="text" class="form-control" id="addPhone">
                        </div>
                         <div class="col-md-6">
                            <label>Địa chỉ</label>
                            <input type="text" class="form-control" id="addAddress">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-success" onclick="submitAdd()">Lưu Học Sinh</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Cập Nhật Thông Tin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Họ tên</label>
                            <input type="text" class="form-control" id="editName">
                        </div>
                        <div class="col-md-6">
                            <label>Lớp</label>
                            <select class="form-select" id="editClass">
                                <?php foreach($list_lop as $lop): ?>
                                    <option value="<?php echo $lop['ma_lop']; ?>"><?php echo $lop['ten_lop']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Ngày sinh</label>
                            <input type="date" class="form-control" id="editDob">
                        </div>
                        <div class="col-md-6">
                            <label>Trạng thái</label>
                            <select class="form-select" id="editStatus">
                                <option value="DangHoc">Đang Học</option>
                                <option value="NghiHoc">Nghỉ Học</option>
                                <option value="ChuyenTruong">Chuyển Trường</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                             <label>SĐT</label>
                             <input type="text" class="form-control" id="editPhone">
                        </div>
                        <div class="col-md-6">
                             <label>Địa chỉ</label>
                             <input type="text" class="form-control" id="editAddress">
                        </div>
                         <div class="col-md-12">
                             <label>Đổi mật khẩu (để trống nếu không đổi)</label>
                             <input type="password" class="form-control" id="editPass" placeholder="...">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="submitEdit()">Cập Nhật</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const BASE_URL = "<?php echo BASE_URL; ?>";

    // Xử lý nút Sửa
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const tr = this.closest('tr');
            // Parse dữ liệu từ data-hs an toàn
            const data = JSON.parse(tr.dataset.hs);
            
            document.getElementById('editId').value = data.ma_hoc_sinh;
            document.getElementById('editName').value = data.ho_ten;
            // Dòng này hoạt động nhờ đã sửa Model thêm hs.ma_lop
            document.getElementById('editClass').value = data.ma_lop; 
            document.getElementById('editDob').value = data.ngay_sinh;
            document.getElementById('editPhone').value = data.so_dien_thoai;
            document.getElementById('editAddress').value = data.dia_chi;
            document.getElementById('editStatus').value = data.trang_thai;
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        });
    });

    // API Thêm
    async function submitAdd() {
        const payload = {
            ho_ten: document.getElementById('addName').value,
            email: document.getElementById('addEmail').value,
            password: document.getElementById('addPass').value,
            ma_lop: document.getElementById('addClass').value,
            ngay_sinh: document.getElementById('addDob').value,
            gioi_tinh: document.getElementById('addGender').value,
            so_dien_thoai: document.getElementById('addPhone').value,
            dia_chi: document.getElementById('addAddress').value,
            ma_truong: document.getElementById('addSchoolId').value
        };

        try {
            const res = await fetch(`${BASE_URL}/quantri/addHocSinhApi`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            
            const result = await res.json();
            
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert('Lỗi: ' + result.message);
            }
        } catch (err) {
            console.error(err);
            alert('Lỗi kết nối!');
        }
    }

    // Tự điền mật khẩu mặc định
    function fillDefaultPass() {
        document.getElementById('addPass').value = '123456@A';
    }

    // API Sửa
    async function submitEdit() {
        const payload = {
            ma_hoc_sinh: document.getElementById('editId').value,
            ho_ten: document.getElementById('editName').value,
            ma_lop: document.getElementById('editClass').value,
            ngay_sinh: document.getElementById('editDob').value,
            so_dien_thoai: document.getElementById('editPhone').value,
            dia_chi: document.getElementById('editAddress').value,
            trang_thai: document.getElementById('editStatus').value,
            password: document.getElementById('editPass').value
        };

        try {
            const res = await fetch(`${BASE_URL}/quantri/updateHocSinhApi`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if(data.success) { alert('Cập nhật thành công!'); location.reload(); }
            else { alert(data.message); }
        } catch(e) { console.error(e); }
    }

    // Xử lý nút Xóa
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', async function() {
            if(confirm('Bạn có chắc muốn xóa học sinh này không?')) {
                const tr = this.closest('tr');
                const data = JSON.parse(tr.dataset.hs);
                
                try {
                    const res = await fetch(`${BASE_URL}/quantri/deleteHocSinhApi`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ma_hoc_sinh: data.ma_hoc_sinh})
                    });
                    const result = await res.json();
                    if(result.success) { 
                        alert('Xóa thành công!');
                        tr.remove(); 
                    } else { 
                        alert(result.message); 
                    }
                } catch(e) { 
                    console.error(e);
                    alert('Lỗi kết nối!');
                }
            }
        });
    });
</script>
</body>
</html>