<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tài Khoản (CRUD) | THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --admin-primary: #198754; /* Green */
            --admin-light: #f0fff8; 
            --bg-color: #F0F8FF;
        }
        body { font-family: 'Roboto', sans-serif; background-color: var(--bg-color); }
        .table-success-custom { background-color: var(--admin-primary); color: white; }
        .table-striped > tbody > tr:nth-of-type(odd) > * { background-color: var(--admin-light); }
        .card { border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .btn-action { margin-right: 5px; }
        /* Style cho hàng bị highlight khi tìm kiếm */
        tr.highlight-search {
            background-color: #fff3cd !important; /* Màu vàng nhạt */
            transition: background-color 0.5s ease;
        }
    </style>
</head>

<body>
    <div class="container-fluid p-4">
        <header class="mb-5 p-4 bg-white rounded-3 shadow-sm">
            <h1 class="fw-bold text-center text-success">
                <i class="bi bi-person-gear me-2"></i> QUẢN LÝ TÀI KHOẢN HỆ THỐNG
            </h1>
            <p class="text-center text-muted">Quản lý (Sửa/Xóa) thông tin tài khoản người dùng</p>
             <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-outline-secondary btn-sm float-end" style="margin-top: -50px;">
                <i class="bi bi-arrow-left"></i> Quay lại Dashboard
             </a>
        </header>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card p-4">
                    <form class="d-flex" onsubmit="return handleSearch(event)">
                        <input class="form-control me-2 rounded-pill" type="search" placeholder="Nhập SĐT hoặc Họ Tên để tìm kiếm..." id="searchInput" aria-label="Search">
                        <button class="btn btn-success rounded-pill" type="submit">
                            <i class="bi bi-search"></i> Tìm Kiếm
                        </button>
                    </form>
                    <small id="searchHelp" class="mt-2 text-muted">Tìm kiếm sẽ lọc danh sách bên dưới.</small>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-success"><i class="bi bi-list-task me-2"></i> Danh Sách Tài Khoản</h3>
            <!-- Nút Cấp tài khoản đã bị vô hiệu hóa theo yêu cầu -->
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAccountModal">
                <i class="bi bi-person-plus me-2"></i> Cấp Tài Khoản Mới
            </button>
        </div>

        <!-- Thông báo Toàn cục (cho Sửa/Xóa) -->
        <div id="globalNotification" class="alert" style="display: none;" role="alert"></div>


        <div class="card p-0">
            <div class="card-body table-responsive p-0">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-success-custom">
                        <tr>
                            <th>Username</th>
                            <th>Họ Tên</th>
                            <th>SĐT</th>
                            <th>Vai Trò</th>
                            <th>Địa Chỉ</th>
                            <th>Trạng Thái</th>
                            <th class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody id="accountTableBody">
                        <?php if (empty($data['accounts'])): ?>
                            <tr>
                                <td colspan="7" class="text-center p-5 text-muted">Không tìm thấy tài khoản nào.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['accounts'] as $account): ?>
                                <!-- Lưu trữ toàn bộ dữ liệu vào data-* attributes -->
                                <tr class="account-row" 
                                    data-ma-tai-khoan="<?php echo $account['ma_tai_khoan']; ?>"
                                    data-username="<?php echo htmlspecialchars($account['username']); ?>"
                                    data-ho-ten="<?php echo htmlspecialchars($account['ho_ten']); ?>"
                                    data-so-dien-thoai="<?php echo htmlspecialchars($account['so_dien_thoai']); ?>"
                                    data-email="<?php echo htmlspecialchars($account['email']); ?>"
                                    data-vai-tro="<?php echo htmlspecialchars($account['vai_tro']); ?>"
                                    data-dia-chi="<?php echo htmlspecialchars($account['dia_chi'] ?? ''); ?>"
                                    data-trang-thai="<?php echo htmlspecialchars($account['trang_thai']); ?>"
                                    data-ngay-sinh="<?php echo htmlspecialchars($account['ngay_sinh'] ?? ''); ?>"
                                    data-gioi-tinh="<?php echo htmlspecialchars($account['gioi_tinh'] ?? ''); ?>">
                                    
                                    <td><?php echo htmlspecialchars($account['username']); ?></td>
                                    <td><?php echo htmlspecialchars($account['ho_ten']); ?></td>
                                    <td class="account-phone"><?php echo htmlspecialchars($account['so_dien_thoai']); ?></td>
                                    <td><?php echo htmlspecialchars($account['vai_tro']); ?></td>
                                    <td><?php echo htmlspecialchars($account['dia_chi'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($account['trang_thai'] == 'HoatDong'): ?>
                                            <span class="badge bg-success">Hoạt Động</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($account['trang_thai']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <!-- Truyền ma_tai_khoan vào hàm JS -->
                                        <button class="btn btn-sm btn-outline-primary btn-action" onclick="openEditModal(<?php echo $account['ma_tai_khoan']; ?>)">
                                            <i class="bi bi-pencil-fill"></i> Sửa
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-action" onclick="openDeleteModal(<?php echo $account['ma_tai_khoan']; ?>)">
                                            <i class="bi bi-trash-fill"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="card mt-3 p-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">
                        Đang hiển thị <strong><?php echo count($data['accounts']); ?></strong> tài khoản 
                        trong số <strong><?php echo $data['total_accounts']; ?></strong> 
                        | Trang <strong><?php echo $data['current_page']; ?></strong> / <strong><?php echo $data['total_pages']; ?></strong>
                    </p>
                </div>
                <div class="col-md-6">
                    <nav aria-label="Phân trang">
                        <ul class="pagination justify-content-end mb-0">
                            <!-- Nút "Trang trước" -->
                            <li class="page-item <?php echo $data['current_page'] <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL; ?>/quantri/quanlytaikhoan?page=<?php echo max(1, $data['current_page'] - 1); ?>">
                                    <i class="bi bi-chevron-left"></i> Trước
                                </a>
                            </li>

                            <!-- Các nút trang -->
                            <?php
                            $start_page = max(1, $data['current_page'] - 2);
                            $end_page = min($data['total_pages'], $data['current_page'] + 2);
                            
                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo BASE_URL; ?>/quantri/quanlytaikhoan?page=1">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif;
                            endif;

                            for ($p = $start_page; $p <= $end_page; $p++): ?>
                                <li class="page-item <?php echo $p == $data['current_page'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo BASE_URL; ?>/quantri/quanlytaikhoan?page=<?php echo $p; ?>">
                                        <?php echo $p; ?>
                                    </a>
                                </li>
                            <?php endfor;

                            if ($end_page < $data['total_pages']): 
                                if ($end_page < $data['total_pages'] - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo BASE_URL; ?>/quantri/quanlytaikhoan?page=<?php echo $data['total_pages']; ?>">
                                        <?php echo $data['total_pages']; ?>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <!-- Nút "Trang sau" -->
                            <li class="page-item <?php echo $data['current_page'] >= $data['total_pages'] ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL; ?>/quantri/quanlytaikhoan?page=<?php echo min($data['total_pages'], $data['current_page'] + 1); ?>">
                                    Sau <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editAccountModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i> Cập Nhật Thông Tin Tài Khoản</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                         <!-- Input ẩn chứa ma_tai_khoan -->
                        <input type="hidden" id="editMaTaiKhoan">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="editFullName" class="form-label">Họ Tên (<50 ký tự) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editFullName" required maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label for="editPhone" class="form-label">Số Điện Thoại <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="editPhone" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editEmail" class="form-label">Email (Tên Đăng Nhập) <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="editEmail" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editRole" class="form-label">Vai Trò <span class="text-danger">*</span></label>
                                <select class="form-select" id="editRole" required>
                                    <option value="">-- Chọn vai trò --</option>
                                    <option value="HocSinh">Học Sinh</option>
                                    <option value="PhuHuynh">Phụ Huynh</option>
                                    <option value="GiaoVien">Giáo Viên</option>
                                    <option value="BanGiamHieu">Ban Giám Hiệu</option>
                                </select>
                            </div>

                            <!-- ✅ PHẦN ĐỘNG CHO HỌC SINH: Khối + Lớp -->
                            <div id="editDivKhoi" class="col-md-6" style="display:none;">
                                <label for="editKhoi" class="form-label">Khối <span class="text-danger">*</span></label>
                                <select id="editKhoi" class="form-select">
                                    <option value="">-- Chọn khối --</option>
                                </select>
                            </div>
                            <div id="editDivLopHoc" class="col-md-6" style="display:none;">
                                <label for="editLopHoc" class="form-label">Lớp <span class="text-danger">*</span></label>
                                <select id="editLopHoc" class="form-select">
                                    <option value="">-- Chọn lớp --</option>
                                </select>
                            </div>

                            <!-- ✅ PHẦN ĐỘNG CHO GV/BGH: Lớp + Môn -->
                            <div id="editDivLopGV" class="col-md-6" style="display:none;">
                                <label for="editLopGV" class="form-label">Lớp <span class="text-danger">*</span></label>
                                <select id="editLopGV" class="form-select">
                                    <option value="">-- Chọn lớp --</option>
                                </select>
                            </div>
                            <div id="editDivMonHoc" class="col-md-6" style="display:none;">
                                <label for="editMonHoc" class="form-label">Bộ môn chuyên môn</label>
                                <select id="editMonHoc" class="form-select">
                                    <option value="">-- Vui lòng chọn lớp trước --</option>
                                </select>
                            </div>

                            <!-- ✅ PHẦN ĐỘNG CHO PHỤ HUYNH: Lớp + Học Sinh -->
                            <div id="editDivLopPH" class="col-md-6" style="display:none;">
                                <label for="editLopPH" class="form-label">Lớp của con <span class="text-danger">*</span></label>
                                <select id="editLopPH" class="form-select">
                                    <option value="">-- Chọn lớp --</option>
                                </select>
                            </div>
                            <div id="editDivHocSinh" class="col-md-6" style="display:none;">
                                <label for="editHocSinh" class="form-label">Chọn Học Sinh <span class="text-danger">*</span></label>
                                <select id="editHocSinh" class="form-select">
                                    <option value="">-- Vui lòng chọn lớp trước --</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="editBirthday" class="form-label">Ngày Sinh</label>
                                <input type="date" class="form-control" id="editBirthday">
                            </div>

                            <div class="col-md-6">
                                <label for="editGender" class="form-label">Giới Tính</label>
                                <select class="form-select" id="editGender">
                                    <option value="">-- Chọn --</option>
                                    <option value="Nam">Nam</option>
                                    <option value="Nu">Nữ</option>
                                    <option value="Khac">Khác</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="editPassword" class="form-label">Đặt lại Mật khẩu</label>
                                <input type="password" class="form-control" id="editPassword" placeholder="Để trống nếu không muốn thay đổi">
                            </div>
                            <div class="col-12">
                                <label for="editAddress" class="form-label">Địa chỉ</label>
                                <input type="text" class="form-control" id="editAddress" placeholder="Nhập địa chỉ...">
                            </div>
                        </div>
                        <div id="editNotification" class="mt-3 text-danger"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="handleUpdate()">Xác nhận Cập Nhật</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Xóa Tài Khoản -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i> Xác Nhận Xóa Tài Khoản</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Bạn có chắc chắn muốn **XÓA VĨNH VIỄN** tài khoản này không? Thao tác này không thể hoàn tác.</p>
                    <ul class="list-group mt-2">
                        <li class="list-group-item"><strong>Họ tên:</strong> <span id="deleteFullNameDisplay"></span></li>
                        <li class="list-group-item"><strong>SĐT:</strong> <span id="deletePhoneDisplay" class="fw-bold"></span></li>
                        <li class="list-group-item"><strong>Vai trò:</strong> <span id="deleteRoleDisplay"></span></li>
                    </ul>
                    <div id="deleteNotification" class="mt-3 text-danger"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <!-- Thêm input ẩn để lưu ma_tai_khoan -->
                    <input type="hidden" id="deleteMaTaiKhoan">
                    <button type="button" class="btn btn-danger" onclick="handleDelete()">Xác nhận Xóa</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cấp tài khoản (Đã vô hiệu hóa) -->
    <div class="modal fade" id="createAccountModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Cấp Tài Khoản Mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" id="createFullName" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label>Số điện thoại <span class="text-danger">*</span></label>
                        <input type="text" id="createPhone" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label>Email (làm tên đăng nhập) <span class="text-danger">*</span></label>
                        <input type="email" id="createEmail" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" id="createPassword" class="form-control" required placeholder="Nhập mật khẩu...">
                            <button class="btn btn-outline-secondary" type="button" id="generatePasswordBtn" title="Tự động tạo mật khẩu">
                                <i class="bi bi-magic"></i> Random
                            </button>
                        </div>
                        <small class="text-muted" style="font-size: 0.8rem;">Click Random để tạo pass: 123456@A</small>
                    </div>
                    <div class="col-md-6">
                        <label>Vai trò <span class="text-danger">*</span></label>
                        <select id="createRole" class="form-select" required>
                            <option value="">-- Chọn vai trò --</option>
                            <option value="HocSinh">Học Sinh</option>
                            <option value="PhuHuynh">Phụ Huynh</option>
                            <option value="GiaoVien">Giáo Viên</option>
                            <option value="BanGiamHieu">Ban Giám Hiệu</option>
                        </select>
                    </div>

                    <!-- Phần động theo vai trò -->
                    <div id="divLopHoc" class="col-12" style="display:none;">
                        <label>Khối <span class="text-danger">*</span></label>
                        <select id="createKhoi" class="form-select">
                            <option value="">-- Chọn khối --</option>
                        </select>
                    </div>
                    <div id="divLopHocChiTiet" class="col-12" style="display:none;">
                        <label>Lớp <span class="text-danger">*</span></label>
                        <select id="createLop" class="form-select"></select>
                    </div>
                    
                    <!-- Phụ Huynh: Chọn Lớp → Học sinh chưa có PH -->
                    <div id="divPhuHuynhLop" class="col-12" style="display:none;">
                        <label>Lớp của con <span class="text-danger">*</span></label>
                        <select id="createLopPH" class="form-select">
                            <option value="">-- Đang tải... --</option>
                        </select>
                    </div>
                    <div id="divPhuHuynhHS" class="col-12" style="display:none;">
                        <label>Chọn Học Sinh (con của phụ huynh) <span class="text-danger">*</span></label>
                        <select id="createHocSinhPH" class="form-select">
                            <option value="">-- Vui lòng chọn lớp trước --</option>
                        </select>
                    </div>
                    
                    <div id="divMonHoc" class="col-12" style="display:none;">
                        <label>Bộ môn chuyên môn (tùy chọn)</label>
                        <select id="createMonHoc" class="form-select">
                            <option value="">-- Không chọn --</option>
                            <!-- Sẽ load bằng JS -->
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Ngày sinh</label>
                        <input type="date" id="createBirthday" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Giới tính</label>
                        <select id="createGender" class="form-select">
                            <option value="">-- Chọn --</option>
                            <option value="Nam">Nam</option>
                            <option value="Nu">Nữ</option>
                            <option value="Khac">Khác</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label>Địa chỉ</label>
                        <input type="text" id="createAddress" class="form-control">
                    </div>
                </div>
                <div id="createNotification" class="mt-3 text-danger"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success" onclick="handleCreate()">Tạo tài khoản</button>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // --- KHAI BÁO BIẾN TOÀN CỤC ---
    let editModal, deleteModal, createModal;
    // Lấy BASE_URL từ PHP (đảm bảo code PHP đã define biến này)
    const BASE_URL = "<?php echo BASE_URL ?? ''; ?>";

    // --- KHỞI TẠO KHI DOM READY ---
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Khởi tạo các Modal Bootstrap
        const editModalEl = document.getElementById('editAccountModal');
        const deleteModalEl = document.getElementById('deleteAccountModal');
        const createModalEl = document.getElementById('createAccountModal');

        if (editModalEl) editModal = new bootstrap.Modal(editModalEl);
        if (deleteModalEl) deleteModal = new bootstrap.Modal(deleteModalEl);
        if (createModalEl) createModal = new bootstrap.Modal(createModalEl);

        // 2. Lắng nghe sự kiện UI - Sử dụng addEventListener với proper binding
        const createRoleSelect = document.getElementById('createRole');
        if (createRoleSelect) {
            createRoleSelect.addEventListener('change', function() {
                handleRoleChange.call(this);
            });
        }
        
        const createKhoiSelect = document.getElementById('createKhoi');
        if (createKhoiSelect) {
            createKhoiSelect.addEventListener('change', function() {
                handleKhoiChange.call(this);
            });
        }

        // 3. SỰ KIỆN QUAN TRỌNG: CHỌN LỚP -> LOAD MÔN (Dành cho GV/BGH)
        const createLopSelect = document.getElementById('createLop');
        if (createLopSelect) {
            createLopSelect.addEventListener('change', async function() {
                const maLop = this.value;
                const role = document.getElementById('createRole').value;
                const selectMon = document.getElementById('createMonHoc');

                // Chỉ chạy khi vai trò là Giáo Viên hoặc BGH
                if ((role === 'GiaoVien' || role === 'BanGiamHieu') && selectMon) {
                    if (!maLop) {
                        selectMon.innerHTML = '<option value="">-- Vui lòng chọn lớp trước --</option>';
                        return;
                    }

                    // Hiện thông báo đang tải
                    selectMon.innerHTML = '<option value="">Đang tải môn...</option>';

                    try {
                        // Gọi API lấy môn theo lớp (Cascading)
                        const response = await fetch(`${BASE_URL}/quantri/getDsMonTheoLopApi/${maLop}`);
                        const result = await response.json();

                        if (result.success && result.data.length > 0) {
                            let html = '<option value="">-- Chọn môn chuyên môn --</option>';
                            result.data.forEach(mon => {
                                html += `<option value="${mon.ma_mon_hoc}">${mon.ten_mon_hoc}</option>`;
                            });
                            selectMon.innerHTML = html;
                        } else {
                            selectMon.innerHTML = '<option value="">-- Lớp này không có môn phù hợp --</option>';
                        }
                    } catch (error) {
                        console.error("Lỗi tải môn:", error);
                        selectMon.innerHTML = '<option value="">Lỗi kết nối</option>';
                    }
                }
            });
        }

        // 4. Nút Random Password
        const genPassBtn = document.getElementById('generatePasswordBtn');
        if (genPassBtn) {
            genPassBtn.addEventListener('click', () => {
                const passInput = document.getElementById('createPassword');
                passInput.value = '123456@A'; // Pass mặc định
                passInput.type = 'text';
                setTimeout(() => passInput.type = 'password', 2000);
            });
        }

        // --- LẮNG NGHE SỰ KIỆN CHỌN VAI TRÒ TRONG MODAL EDIT ---
        const editRoleSelect = document.getElementById('editRole');
        if (editRoleSelect) {
            editRoleSelect.addEventListener('change', function() {
                handleEditRoleChange.call(this);
            });
        }

        // --- SỰ KIỆN CHỌN KHỐI TRONG EDIT ---
        const editKhoiSelect = document.getElementById('editKhoi');
        if (editKhoiSelect) {
            editKhoiSelect.addEventListener('change', function() {
                handleEditKhoiChange.call(this);
            });
        }

        // --- SỰ KIỆN CHỌN LỚP GV/BGH TRONG EDIT ---
        const editLopGVSelect = document.getElementById('editLopGV');
        if (editLopGVSelect) {
            editLopGVSelect.addEventListener('change', async function() {
                const maLop = this.value;
                const selectMon = document.getElementById('editMonHoc');
                
                if (!maLop) {
                    selectMon.innerHTML = '<option value="">-- Vui lòng chọn lớp trước --</option>';
                    return;
                }

                selectMon.innerHTML = '<option value="">Đang tải môn...</option>';

                try {
                    const response = await fetch(`${BASE_URL}/quantri/getDsMonTheoLopApi/${maLop}`);
                    const result = await response.json();

                    if (result.success && result.data.length > 0) {
                        let html = '<option value="">-- Chọn môn chuyên môn --</option>';
                        result.data.forEach(mon => {
                            html += `<option value="${mon.ma_mon_hoc}">${mon.ten_mon_hoc}</option>`;
                        });
                        selectMon.innerHTML = html;
                    } else {
                        selectMon.innerHTML = '<option value="">-- Không có môn phù hợp --</option>';
                    }
                } catch (error) {
                    console.error("Lỗi tải môn:", error);
                    selectMon.innerHTML = '<option value="">Lỗi kết nối</option>';
                }
            });
        }

        // --- SỰ KIỆN CHỌN LỚP PHỤ HUYNH TRONG EDIT ---
        const editLopPHSelect = document.getElementById('editLopPH');
        if (editLopPHSelect) {
            editLopPHSelect.addEventListener('change', async function() {
                const maLop = this.value;
                const selectHS = document.getElementById('editHocSinh');
                
                if (!maLop) {
                    selectHS.innerHTML = '<option value="">-- Vui lòng chọn lớp trước --</option>';
                    return;
                }

                selectHS.innerHTML = '<option value="">Đang tải danh sách học sinh...</option>';

                try {
                    const response = await fetch(`${BASE_URL}/quantri/getDsHocSinhChuaCoPhApi/${maLop}`);
                    const result = await response.json();

                    if (result.success && result.data.length > 0) {
                        let html = '<option value="">-- Chọn học sinh --</option>';
                        result.data.forEach(hs => {
                            html += `<option value="${hs.ma_hoc_sinh}">${hs.ho_ten} (${hs.ngay_sinh || 'N/A'})</option>`;
                        });
                        selectHS.innerHTML = html;
                    } else {
                        selectHS.innerHTML = '<option value="">Tất cả học sinh đã có phụ huynh</option>';
                    }
                } catch (error) {
                    console.error("Lỗi load học sinh:", error);
                    selectHS.innerHTML = `<option value="">Lỗi: ${error.message}</option>`;
                }
            });
        }
    });

    // --- HÀM XỬ LÝ VAI TRÒ TRONG MODAL EDIT ===
    function handleEditRoleChange() {
        const role = this.value;

        // 1. Ẩn tất cả trước
        document.getElementById('editDivKhoi').style.display = 'none';
        document.getElementById('editDivLopHoc').style.display = 'none';
        document.getElementById('editDivLopGV').style.display = 'none';
        document.getElementById('editDivMonHoc').style.display = 'none';
        document.getElementById('editDivLopPH').style.display = 'none';
        document.getElementById('editDivHocSinh').style.display = 'none';

        // Reset các select
        document.getElementById('editKhoi').value = "";
        document.getElementById('editLopHoc').value = "";
        document.getElementById('editLopGV').value = "";
        document.getElementById('editMonHoc').value = "";
        document.getElementById('editLopPH').value = "";
        document.getElementById('editHocSinh').value = "";

        // 2. Hiển thị theo vai trò
        if (role === 'HocSinh') {
            document.getElementById('editDivKhoi').style.display = 'block';
            document.getElementById('editDivLopHoc').style.display = 'block';
            loadEditDsKhoi();
        } 
        else if (role === 'GiaoVien' || role === 'BanGiamHieu') {
            document.getElementById('editDivLopGV').style.display = 'block';
            document.getElementById('editDivMonHoc').style.display = 'block';
            loadEditDsLopAll();
            document.getElementById('editMonHoc').innerHTML = '<option value="">-- Vui lòng chọn lớp trước --</option>';
        }
        else if (role === 'PhuHuynh') {
            document.getElementById('editDivLopPH').style.display = 'block';
            document.getElementById('editDivHocSinh').style.display = 'block';
            loadEditDsLopForPhuHuynh();
        }
    }

    async function loadEditDsKhoi() {
        const selectKhoi = document.getElementById('editKhoi');
        selectKhoi.innerHTML = '<option value="">-- Đang tải... --</option>';

        try {
            const response = await fetch(`${BASE_URL}/quantri/getDsKhoiApi`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                let html = '<option value="">-- Chọn khối --</option>';
                result.data.forEach(khoi => {
                    html += `<option value="${khoi}">Khối ${khoi}</option>`;
                });
                selectKhoi.innerHTML = html;
            } else {
                selectKhoi.innerHTML = '<option value="">Không có khối nào</option>';
            }
        } catch (error) { 
            console.error("Lỗi load khối:", error); 
            selectKhoi.innerHTML = '<option value="">Lỗi khi tải khối</option>';
        }
    }

    async function handleEditKhoiChange() {
        const khoi = this.value;
        const selectLop = document.getElementById('editLopHoc');
        
        if (!khoi) {
            selectLop.innerHTML = '<option value="">-- Chọn lớp --</option>';
            return;
        }

        selectLop.innerHTML = '<option value="">-- Đang tải... --</option>';

        try {
            const response = await fetch(`${BASE_URL}/quantri/getDsLopTheoKhoiApi/${khoi}`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                let html = '<option value="">-- Chọn lớp --</option>';
                result.data.forEach(lop => {
                    html += `<option value="${lop.ma_lop}">${lop.ten_lop}</option>`;
                });
                selectLop.innerHTML = html;
            } else {
                selectLop.innerHTML = '<option value="">Không có lớp nào</option>';
            }
        } catch (error) { 
            console.error("Lỗi load lớp:", error); 
            selectLop.innerHTML = '<option value="">Lỗi khi tải lớp</option>';
        }
    }

    async function loadEditDsLopAll() {
        const selectLop = document.getElementById('editLopGV');
        selectLop.innerHTML = '<option value="">-- Đang tải danh sách lớp... --</option>';

        try {
            const response = await fetch(`${BASE_URL}/quantri/getDsLopAllApi`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                let html = '<option value="">-- Chọn lớp --</option>';
                result.data.forEach(lop => {
                    html += `<option value="${lop.ma_lop}">Khối ${lop.khoi} - ${lop.ten_lop}</option>`;
                });
                selectLop.innerHTML = html;
            } else {
                selectLop.innerHTML = '<option value="">Không có lớp nào</option>';
            }
        } catch (error) { 
            console.error("Lỗi loadEditDsLopAll:", error); 
            selectLop.innerHTML = `<option value="">Lỗi: ${error.message}</option>`;
        }
    }

    async function loadEditDsLopForPhuHuynh() {
        const selectLop = document.getElementById('editLopPH');
        selectLop.innerHTML = '<option value="">-- Đang tải danh sách lớp... --</option>';

        try {
            const response = await fetch(`${BASE_URL}/quantri/getDsLopAllApi`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                let html = '<option value="">-- Chọn lớp --</option>';
                result.data.forEach(lop => {
                    html += `<option value="${lop.ma_lop}">Khối ${lop.khoi} - ${lop.ten_lop}</option>`;
                });
                selectLop.innerHTML = html;
            } else {
                selectLop.innerHTML = '<option value="">Không có lớp nào</option>';
            }
        } catch (error) { 
            console.error("Lỗi loadEditDsLopForPhuHuynh:", error); 
            selectLop.innerHTML = `<option value="">Lỗi: ${error.message}</option>`;
        }
    }

    // --- HÀM TIỆN ÍCH PREFILL DỮ LIỆU THEO VAI TRÒ (EDIT) ---
    async function loadEditHocSinhData(maTaiKhoan) {
        const notiEl = document.getElementById('editNotification');
        try {
            const res = await fetch(`${BASE_URL}/quantri/getHocSinhInfoApi/${maTaiKhoan}`);
            const result = await res.json();
            if (!result.success || !result.data) return;

            const info = result.data;
            await loadEditDsKhoi();
            const khoiSelect = document.getElementById('editKhoi');
            khoiSelect.value = info.khoi || '';
            await handleEditKhoiChange.call(khoiSelect);
            const lopSelect = document.getElementById('editLopHoc');
            lopSelect.value = info.ma_lop || '';
        } catch (error) {
            console.error('Lỗi loadEditHocSinhData:', error);
            if (notiEl) notiEl.textContent = 'Không thể tải dữ liệu lớp của học sinh.';
        }
    }

    async function loadEditMonHocByLop(maLop, selectedMon) {
        const selectMon = document.getElementById('editMonHoc');
        if (!maLop) {
            selectMon.innerHTML = '<option value="">-- Vui lòng chọn lớp trước --</option>';
            return;
        }
        
        // Đừng reset nếu đang loading (tránh nhấp nháy), nhưng ở đây reset để an toàn
        selectMon.innerHTML = '<option value="">Đang tải môn...</option>';

        try {
            const response = await fetch(`${BASE_URL}/quantri/getDsMonTheoLopApi/${maLop}`);
            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                let html = '<option value="">-- Chọn môn chuyên môn --</option>';
                result.data.forEach(mon => {
                    html += `<option value="${mon.ma_mon_hoc}">${mon.ten_mon_hoc}</option>`;
                });
                selectMon.innerHTML = html;
                
                // ✅ SET GIÁ TRỊ CŨ (QUAN TRỌNG)
                if (selectedMon) {
                    selectMon.value = selectedMon;
                }
            } else {
                selectMon.innerHTML = '<option value="">-- Không có môn phù hợp --</option>';
            }
        } catch (error) {
            console.error('Lỗi loadEditMonHocByLop:', error);
            selectMon.innerHTML = '<option value="">Lỗi kết nối</option>';
        }
    }

    async function loadEditGiaoVienData(maTaiKhoan) {
        const notiEl = document.getElementById('editNotification');
        try {
            const res = await fetch(`${BASE_URL}/quantri/getGiaoVienInfoApi/${maTaiKhoan}`);
            const result = await res.json();
            
            if (!result.success || !result.data) {
                console.log('Không có dữ liệu GV hoặc lỗi API');
                return;
            }

            const info = result.data;
            
            // ✅ BƯỚC QUAN TRỌNG: Gọi hàm tải danh sách lớp và AWAIT nó
            // Để đảm bảo <option> đã có trước khi gán value
            await loadEditDsLopAll();
            
            // 2. Sau khi list lớp đã tải xong, gán giá trị lớp cũ
            const lopSelect = document.getElementById('editLopGV');
            lopSelect.value = info.ma_lop || '';
            
            // 3. Tải danh sách môn theo lớp và gán môn cũ
            if (info.ma_lop) {
                await loadEditMonHocByLop(info.ma_lop, info.ma_mon_hoc);
            }
            
        } catch (error) {
            console.error('Lỗi loadEditGiaoVienData:', error);
            if (notiEl) notiEl.textContent = 'Không thể tải dữ liệu lớp/môn của giáo viên.';
        }
    }

    async function loadEditHocSinhByLop(maLop, currentHsId, currentHsName) {
        const selectHS = document.getElementById('editHocSinh');
        if (!maLop) {
            selectHS.innerHTML = '<option value="">-- Vui lòng chọn lớp trước --</option>';
            return;
        }

        selectHS.innerHTML = '<option value="">Đang tải danh sách học sinh...</option>';

        try {
            const response = await fetch(`${BASE_URL}/quantri/getDsHocSinhChuaCoPhApi/${maLop}`);
            const result = await response.json();

            let html = '<option value="">-- Chọn học sinh --</option>';
            let hasCurrent = false;

            if (result.success && Array.isArray(result.data)) {
                result.data.forEach(hs => {
                    html += `<option value="${hs.ma_hoc_sinh}">${hs.ho_ten} (${hs.ngay_sinh || 'N/A'})</option>`;
                    if (currentHsId && Number(hs.ma_hoc_sinh) === Number(currentHsId)) hasCurrent = true;
                });
            }

            if (currentHsId && !hasCurrent) {
                html += `<option value="${currentHsId}">${currentHsName || 'Học sinh hiện tại'}</option>`;
            }

            selectHS.innerHTML = html;
            if (currentHsId) selectHS.value = currentHsId;
        } catch (error) {
            console.error('Lỗi loadEditHocSinhByLop:', error);
            selectHS.innerHTML = `<option value="">Lỗi: ${error.message}</option>`;
        }
    }

    async function loadEditPhuHuynhData(maTaiKhoan) {
        const notiEl = document.getElementById('editNotification');
        try {
            const res = await fetch(`${BASE_URL}/quantri/getPhuHuynhInfoApi/${maTaiKhoan}`);
            const result = await res.json();
            if (!result.success || !result.data) return;

            const info = result.data;
            await loadEditDsLopForPhuHuynh();
            const lopSelect = document.getElementById('editLopPH');
            lopSelect.value = info.ma_lop || '';
            await loadEditHocSinhByLop(info.ma_lop, info.ma_hoc_sinh, info.ten_hs);
        } catch (error) {
            console.error('Lỗi loadEditPhuHuynhData:', error);
            if (notiEl) notiEl.textContent = 'Không thể tải dữ liệu phụ huynh.';
        }
    }

    // --- CÁC HÀM XỬ LÝ LOGIC UI ---

    function handleRoleChange() {
        const role = this.value;

        // 1. Ẩn và Reset tất cả trước
        document.getElementById('divLopHoc').style.display = 'none';
        document.getElementById('divLopHocChiTiet').style.display = 'none';
        document.getElementById('divMonHoc').style.display = 'none';
        document.getElementById('divPhuHuynhLop').style.display = 'none';
        document.getElementById('divPhuHuynhHS').style.display = 'none';

        const khoiEl = document.getElementById('createKhoi');
        const lopEl = document.getElementById('createLop');
        const monEl = document.getElementById('createMonHoc');
        
        if (khoiEl) khoiEl.value = "";
        if (lopEl) lopEl.value = "";
        if (monEl) monEl.value = "";

        // 2. Logic hiển thị
        if (role === 'HocSinh') {
            // Học sinh: Chọn Khối → Lớp
            document.getElementById('divLopHoc').style.display = 'block';
            loadDsKhoi();
        } 
        else if (role === 'GiaoVien' || role === 'BanGiamHieu') {
            // GV/BGH: Chọn Lớp trực tiếp (không cần Khối)
            document.getElementById('divLopHocChiTiet').style.display = 'block';
            document.getElementById('divMonHoc').style.display = 'block';
            
            // Load danh sách lớp
            loadDsLopAll();
            
            // Reset ô chọn môn, bắt buộc chọn lớp trước
            document.getElementById('createMonHoc').innerHTML = '<option value="">-- Vui lòng chọn lớp trước --</option>';
        }
        else if (role === 'PhuHuynh') {
            // Phụ Huynh: Chọn Lớp → Học sinh chưa có PH
            document.getElementById('divPhuHuynhLop').style.display = 'block';
            document.getElementById('divPhuHuynhHS').style.display = 'block';
            
            // Load danh sách lớp
            loadDsLopForPhuHuynh();
        }
    }

    async function loadDsKhoi() {
        const selectKhoi = document.getElementById('createKhoi');
        selectKhoi.innerHTML = '<option value="">-- Đang tải... --</option>'; // Hiển thị đang tải

        try {
            const response = await fetch(`${BASE_URL}/quantri/getDsKhoiApi`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                let html = '<option value="">-- Chọn khối --</option>';
                result.data.forEach(khoi => {
                    html += `<option value="${khoi}">Khối ${khoi}</option>`;
                });
                selectKhoi.innerHTML = html;
            } else {
                selectKhoi.innerHTML = '<option value="">Không có khối nào</option>';
            }
        } catch (error) { 
            console.error("Lỗi load khối:", error); 
            selectKhoi.innerHTML = '<option value="">Lỗi khi tải khối</option>';
        }
    }

    async function loadDsLopAll() {
        const selectLop = document.getElementById('createLop');
        if (!selectLop) return;
        
        selectLop.innerHTML = '<option value="">-- Đang tải danh sách lớp... --</option>';

        try {
            const response = await fetch(`${BASE_URL}/quantri/getDsLopAllApi`);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: Lỗi từ server`);
            }
            
            const result = await response.json();

            if (!result.success) {
                selectLop.innerHTML = `<option value="">Lỗi: ${result.message || 'Không thể tải danh sách lớp'}</option>`;
                return;
            }

            if (result.data && result.data.length > 0) {
                let html = '<option value="">-- Chọn lớp --</option>';
                result.data.forEach(lop => {
                    html += `<option value="${lop.ma_lop}">Khối ${lop.khoi} - ${lop.ten_lop}</option>`;
                });
                selectLop.innerHTML = html;
            } else {
                selectLop.innerHTML = '<option value="">Không có lớp nào</option>';
            }
        } catch (error) { 
            console.error("Lỗi loadDsLopAll:", error); 
            selectLop.innerHTML = `<option value="">Lỗi khi tải lớp: ${error.message}</option>`;
        }
    }

    async function handleKhoiChange() {
        const khoi = this.value;
        const selectLop = document.getElementById('createLop');
        const divLopChiTiet = document.getElementById('divLopHocChiTiet');
        const selectMon = document.getElementById('createMonHoc');
        
        // Reset ô Môn học khi đổi khối
        if (selectMon) selectMon.innerHTML = '<option value="">-- Vui lòng chọn lớp trước --</option>';

        if (!khoi) {
            if (divLopChiTiet) divLopChiTiet.style.display = 'none';
            if (selectLop) selectLop.innerHTML = '<option value="">-- Chọn lớp --</option>';
            return;
        }

        try {
            const response = await fetch(`${BASE_URL}/quantri/getDsLopTheoKhoiApi/${khoi}`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                let html = '<option value="">-- Chọn lớp --</option>';
                result.data.forEach(lop => {
                    html += `<option value="${lop.ma_lop}">${lop.ten_lop}</option>`;
                });
                if (selectLop) selectLop.innerHTML = html;
                if (divLopChiTiet) divLopChiTiet.style.display = 'block';
            } else {
                if (selectLop) selectLop.innerHTML = '<option value="">Không có lớp nào</option>';
            }
        } catch (error) { 
            console.error("Lỗi load lớp:", error); 
            if (selectLop) selectLop.innerHTML = '<option value="">Lỗi khi tải lớp</option>';
        }
    }

    // --- HÀM LOAD CHO PHỤ HUYNH ---
    async function loadDsLopForPhuHuynh() {
        const selectLop = document.getElementById('createLopPH');
        if (!selectLop) return;
        
        selectLop.innerHTML = '<option value="">-- Đang tải danh sách lớp... --</option>';

        try {
            const response = await fetch(`${BASE_URL}/quantri/getDsLopAllApi`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                let html = '<option value="">-- Chọn lớp --</option>';
                result.data.forEach(lop => {
                    html += `<option value="${lop.ma_lop}">Khối ${lop.khoi} - ${lop.ten_lop}</option>`;
                });
                selectLop.innerHTML = html;
                
                // Gắn event listener cho dropdown lớp PH
                selectLop.addEventListener('change', handleLopPHChange);
            } else {
                selectLop.innerHTML = '<option value="">Không có lớp nào</option>';
            }
        } catch (error) { 
            console.error("Lỗi loadDsLopForPhuHuynh:", error); 
            selectLop.innerHTML = `<option value="">Lỗi: ${error.message}</option>`;
        }
    }

    async function handleLopPHChange() {
        const maLop = this.value;
        const selectHS = document.getElementById('createHocSinhPH');
        if (!selectHS) return;

        if (!maLop) {
            selectHS.innerHTML = '<option value="">-- Vui lòng chọn lớp trước --</option>';
            return;
        }

        selectHS.innerHTML = '<option value="">-- Đang tải danh sách học sinh... --</option>';

        try {
            const response = await fetch(`${BASE_URL}/quantri/getDsHocSinhChuaCoPhApi/${maLop}`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                let html = '<option value="">-- Chọn học sinh --</option>';
                result.data.forEach(hs => {
                    html += `<option value="${hs.ma_hoc_sinh}">${hs.ho_ten} (${hs.ngay_sinh || 'N/A'})</option>`;
                });
                selectHS.innerHTML = html;
            } else {
                selectHS.innerHTML = '<option value="">Tất cả học sinh đã có phụ huynh</option>';
            }
        } catch (error) { 
            console.error("Lỗi load học sinh:", error); 
            selectHS.innerHTML = `<option value="">Lỗi: ${error.message}</option>`;
        }
    }

    // --- CÁC HÀM CRUD (API) ---

    // 1. TẠO TÀI KHOẢN
    async function handleCreate() {
        const notificationEl = document.getElementById('createNotification');
        notificationEl.textContent = '';

        const payload = {
            ho_ten: document.getElementById('createFullName').value.trim(),
            so_dien_thoai: document.getElementById('createPhone').value.trim(),
            email: document.getElementById('createEmail').value.trim(),
            password: document.getElementById('createPassword').value,
            vai_tro: document.getElementById('createRole').value,
            ngay_sinh: document.getElementById('createBirthday').value,
            gioi_tinh: document.getElementById('createGender').value,
            dia_chi: document.getElementById('createAddress').value.trim(),
            // Các trường phụ thuộc
            ma_lop: document.getElementById('createLop').value,
            mon_chuyen_mon: document.getElementById('createMonHoc').value,
            ma_hoc_sinh: document.getElementById('createHocSinhPH')?.value || null // Cho Phụ Huynh
        };

        // Validate cơ bản
        if (!payload.ho_ten || !payload.email || !payload.password || !payload.vai_tro) {
            notificationEl.textContent = 'Vui lòng điền các trường bắt buộc (*).';
            return;
        }
        if (!validateInput('email', payload.email, 'createNotification')) return;

        try {
            const res = await fetch(`${BASE_URL}/quantri/addAccountApi`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.success) {
                createModal.hide();
                showGlobalNotification(data.message, 'success');
                // Reload trang để cập nhật danh sách
                setTimeout(() => window.location.href = `${BASE_URL}/quantri/quanlytaikhoan?page=1`, 1000);
            } else {
                notificationEl.textContent = data.message || "Có lỗi xảy ra.";
            }
        } catch (error) {
            console.error(error);
            notificationEl.textContent = "Lỗi kết nối server.";
        }
    }

    // 2. MỞ MODAL SỬA
    async function openEditModal(ma_tai_khoan) {
        const tr = document.querySelector(`tr[data-ma-tai-khoan="${ma_tai_khoan}"]`);
        if (!tr) return;

        const d = tr.dataset;
        document.getElementById('editMaTaiKhoan').value = d.maTaiKhoan;
        document.getElementById('editFullName').value = d.hoTen;
        document.getElementById('editPhone').value = d.soDienThoai;
        document.getElementById('editEmail').value = d.email;
        document.getElementById('editRole').value = d.vaiTro;
        document.getElementById('editAddress').value = d.diaChi || '';
        document.getElementById('editBirthday').value = d.ngaySinh || '';
        document.getElementById('editGender').value = d.gioiTinh || '';
        document.getElementById('editPassword').value = ''; 
        document.getElementById('editNotification').textContent = '';

        try {
            // ✅ LOAD DỮ LIỆU TRƯỚC, RỒI MỚI TRIGGER CHANGE
            if (d.vaiTro === 'HocSinh') {
                // Trigger change để hiển thị form HS
                const roleSelect = document.getElementById('editRole');
                roleSelect.dispatchEvent(new Event('change'));
                // Chờ form load xong rồi prefill
                await new Promise(r => setTimeout(r, 200));
                await loadEditHocSinhData(ma_tai_khoan);
            } 
            else if (d.vaiTro === 'GiaoVien' || d.vaiTro === 'BanGiamHieu') {
                // 1. Hiển thị form GV (Chỉ để show/hide các DIV)
                const roleSelect = document.getElementById('editRole');
                // Chúng ta set value thủ công để tránh trigger change event gây load lặp lại (optional)
                // Nhưng để đơn giản, cứ dispatch event để nó ẩn/hiện div
                roleSelect.dispatchEvent(new Event('change')); 
                
                // 2. Gọi hàm tải dữ liệu chi tiết (Hàm này giờ đã có await load lớp nên không lo)
                await loadEditGiaoVienData(ma_tai_khoan);
            } 
            else if (d.vaiTro === 'PhuHuynh') {
                // Trigger change để hiển thị form PH
                const roleSelect = document.getElementById('editRole');
                roleSelect.dispatchEvent(new Event('change'));
                // Chờ form load xong rồi prefill
                await new Promise(r => setTimeout(r, 300));
                await loadEditPhuHuynhData(ma_tai_khoan);
            }
        } catch (error) {
            console.error('Lỗi prefill edit modal:', error);
            document.getElementById('editNotification').textContent = 'Không thể tải dữ liệu chi tiết của tài khoản.';
        }

        editModal.show();
    }

    // 3. CẬP NHẬT TÀI KHOẢN
    async function handleUpdate() {
        const notiEl = document.getElementById('editNotification');
        const role = document.getElementById('editRole').value;
        
        const payload = {
            ma_tai_khoan: document.getElementById('editMaTaiKhoan').value,
            ho_ten: document.getElementById('editFullName').value.trim(),
            email: document.getElementById('editEmail').value.trim(),
            so_dien_thoai: document.getElementById('editPhone').value.trim(),
            vai_tro: role,
            dia_chi: document.getElementById('editAddress').value.trim(),
            ngay_sinh: document.getElementById('editBirthday').value,
            gioi_tinh: document.getElementById('editGender').value,
            password: document.getElementById('editPassword').value,
            // ✅ THÊM: Gửi ma_lop theo vai trò
            ma_lop: role === 'HocSinh' 
                ? document.getElementById('editLopHoc').value 
                : (role === 'GiaoVien' || role === 'BanGiamHieu' 
                    ? document.getElementById('editLopGV').value 
                    : document.getElementById('editLopPH').value),
            // ✅ THÊM: Gửi môn cho GV/BGH
            mon_chuyen_mon: (role === 'GiaoVien' || role === 'BanGiamHieu')
                ? document.getElementById('editMonHoc').value 
                : null,
            // ✅ THÊM: Gửi học sinh cho Phụ Huynh
            ma_hoc_sinh: role === 'PhuHuynh'
                ? document.getElementById('editHocSinh').value
                : null
        };

        // Validate bắt buộc
        if (!payload.ho_ten || !payload.email || !payload.so_dien_thoai) {
            notiEl.textContent = 'Vui lòng điền các trường bắt buộc (*).';
            return;
        }

        if (!validateInput('email', payload.email, 'editNotification')) return;

        try {
            const res = await fetch(`${BASE_URL}/quantri/updateTaiKhoan`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.success) {
                editModal.hide();
                showGlobalNotification(data.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                notiEl.textContent = data.message;
            }
        } catch (err) {
            notiEl.textContent = "Lỗi kết nối server.";
        }
    }

    // 4. MỞ MODAL XÓA
    function openDeleteModal(ma_tai_khoan) {
        const tr = document.querySelector(`tr[data-ma-tai-khoan="${ma_tai_khoan}"]`);
        if (!tr) return;
        const d = tr.dataset;

        document.getElementById('deleteFullNameDisplay').textContent = d.hoTen;
        document.getElementById('deletePhoneDisplay').textContent = d.soDienThoai;
        document.getElementById('deleteRoleDisplay').textContent = d.vaiTro;
        document.getElementById('deleteMaTaiKhoan').value = d.maTaiKhoan;
        document.getElementById('deleteNotification').textContent = '';

        deleteModal.show();
    }

    // 5. XÓA TÀI KHOẢN
    async function handleDelete() {
        const ma_tai_khoan = document.getElementById('deleteMaTaiKhoan').value;
        const notiEl = document.getElementById('deleteNotification');

        try {
            const res = await fetch(`${BASE_URL}/quantri/deleteTaiKhoan`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ma_tai_khoan })
            });
            const data = await res.json();

            if (data.success) {
                deleteModal.hide();
                showGlobalNotification(data.message, 'warning');
                const tr = document.querySelector(`tr[data-ma-tai-khoan="${ma_tai_khoan}"]`);
                if (tr) tr.remove();
            } else {
                notiEl.textContent = data.message;
            }
        } catch (err) {
            notiEl.textContent = "Lỗi kết nối server.";
        }
    }

    // 6. TÌM KIẾM (Client-side)
    function handleSearch(event) {
        event.preventDefault();
        const term = document.getElementById('searchInput').value.toLowerCase().trim();
        const rows = document.querySelectorAll('#accountTableBody tr.account-row');
        let found = false;

        rows.forEach(row => {
            const name = row.dataset.hoTen.toLowerCase();
            const phone = row.dataset.soDienThoai.toLowerCase();
            const email = row.dataset.email.toLowerCase();

            if (name.includes(term) || phone.includes(term) || email.includes(term)) {
                row.style.display = '';
                found = true;
            } else {
                row.style.display = 'none';
            }
        });
        
        const helpText = document.getElementById('searchHelp');
        if (!found && term !== '') {
            helpText.textContent = 'Không tìm thấy kết quả nào.';
            helpText.className = 'mt-2 text-danger';
        } else {
            helpText.textContent = 'Tìm kiếm sẽ lọc danh sách bên dưới.';
            helpText.className = 'mt-2 text-muted';
        }
        return false;
    }

    // --- TIỆN ÍCH ---
    function validateInput(field, value, notiId) {
        const noti = document.getElementById(notiId);
        noti.textContent = '';
        if (field === 'email') {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!regex.test(value)) {
                noti.textContent = 'Email không đúng định dạng.';
                return false;
            }
        }
        return true;
    }

    function showGlobalNotification(msg, type = 'success') {
        const el = document.getElementById('globalNotification');
        if (!el) return;
        el.textContent = msg;
        el.className = `alert alert-${type} alert-dismissible fade show`;
        el.style.display = 'block';
        if (!el.querySelector('.btn-close')) {
            const btn = document.createElement('button');
            btn.className = 'btn-close';
            btn.dataset.bsDismiss = 'alert';
            el.appendChild(btn);
        }
        setTimeout(() => { el.style.display = 'none'; }, 4000);
    }
</script>
</body>
</html>
