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
                                    data-trang-thai="<?php echo htmlspecialchars($account['trang_thai']); ?>">
                                    
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
                                <label for="editPhone" class="form-label">Số Điện Thoại</label>
                                <input type="tel" class="form-control" id="editPhone" readonly disabled>
                            </div>
                            <div class="col-md-6">
                                <label for="editEmail" class="form-label">Email (Tên Đăng Nhập) <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="editEmail" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editRole" class="form-label">Vai Trò <span class="text-danger">*</span></label>
                                <select class="form-select" id="editRole" required>
                                    <!-- Lấy vai trò từ CSDL -->
                                    <option value="HocSinh">Học Sinh</option>
                                    <option value="PhuHuynh">Phụ Huynh</option>
                                    <option value="GiaoVien">Giáo Viên</option>
                                    <option value="NhanVienSoGD">Nhân Viên Sở GD</option>
                                    <option value="ThiSinh">Thí Sinh</option>
                                    <!-- Không cho phép set QuanTriVien ở đây -->
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
                        <label>Lớp học <span class="text-danger">*</span></label>
                        <select id="createLop" class="form-select"></select>
                    </div>
                    <div id="divMonHoc" class="col-12" style="display:none;">
                        <label>Bộ môn chuyên môn (tùy chọn)</label>
                        <select id="createMonHoc" class="form-select">
                            <option value="">-- Không chọn --</option>
                            <!-- Sẽ load bằng JS -->
                        </select>
                    </div>
                    <div id="divHocSinhCon" class="col-12" style="display:none;">
                        <label>Chọn con (có thể chọn nhiều)</label>
                        <select id="createHocSinhCon" class="form-select" multiple size="5"></select>
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

        // 2. Lắng nghe sự kiện thay đổi VAI TRÒ (trong Modal Tạo mới)
        const createRoleSelect = document.getElementById('createRole');
        if (createRoleSelect) {
            createRoleSelect.addEventListener('change', handleRoleChange);
        }

        // 3. Sự kiện nút tạo mật khẩu ngẫu nhiên (nếu có trong HTML)
        const genPassBtn = document.getElementById('generatePasswordBtn');
        if (genPassBtn) {
            genPassBtn.addEventListener('click', () => {
                const passInput = document.getElementById('createPassword');
                passInput.value = '123456@A'; // Mật khẩu mặc định
                passInput.type = 'text';
                setTimeout(() => passInput.type = 'password', 2000);
            });
        }
    });

    // --- CÁC HÀM XỬ LÝ LOGIC GIAO DIỆN (UI) ---

    // Xử lý khi đổi vai trò -> Ẩn/Hiện các ô nhập liệu tương ứng
    function handleRoleChange() {
        const role = this.value; // this = #createRole select box

        // Ẩn tất cả các div mở rộng trước
        document.getElementById('divLopHoc').style.display = 'none';
        document.getElementById('divMonHoc').style.display = 'none';
        document.getElementById('divHocSinhCon').style.display = 'none';

        // Reset giá trị để tránh gửi dữ liệu thừa
        document.getElementById('createLop').value = "";
        document.getElementById('createMonHoc').value = "";

        // Logic hiển thị theo vai trò
        if (role === 'HocSinh') {
            document.getElementById('divLopHoc').style.display = 'block';
            loadDsLop(); // Gọi API lấy danh sách lớp
        } else if (role === 'GiaoVien') {
            document.getElementById('divMonHoc').style.display = 'block';
            // loadDsMonHoc(); // Nếu cần load môn học thì gọi hàm ở đây
        } else if (role === 'PhuHuynh') {
            document.getElementById('divHocSinhCon').style.display = 'block';
            // loadDsHocSinh(); // Nếu cần load ds học sinh thì gọi hàm ở đây
        }
    }

    // Hàm gọi API lấy danh sách lớp (Chỉ load 1 lần để tối ưu)
    async function loadDsLop() {
        const selectLop = document.getElementById('createLop');
        // Nếu đã có dữ liệu (lớn hơn 1 option mặc định) thì không load lại
        if (selectLop.options.length > 1) return; 

        try {
            // Gọi API từ Controller: QuanTriController -> getDsLopApi
            const response = await fetch(`${BASE_URL}/quantri/getDsLopApi`);
            const result = await response.json();

            if (result.success && result.data) {
                let html = '<option value="">-- Chọn lớp học --</option>';
                result.data.forEach(lop => {
                    // Giả sử API trả về {ma_lop: 1, ten_lop: '10A1'}
                    html += `<option value="${lop.ma_lop}">${lop.ten_lop}</option>`;
                });
                selectLop.innerHTML = html;
            } else {
                console.warn("Không tải được danh sách lớp:", result.message);
            }
        } catch (error) {
            console.error("Lỗi kết nối khi tải lớp:", error);
        }
    }

    // --- CÁC HÀM CRUD (TẠO, SỬA, XÓA, TÌM KIẾM) ---

    // 1. TẠO MỚI TÀI KHOẢN (CREATE)
    async function handleCreate() {
        const notificationEl = document.getElementById('createNotification');
        notificationEl.textContent = '';

        // Thu thập dữ liệu
        const payload = {
            ho_ten: document.getElementById('createFullName').value.trim(),
            so_dien_thoai: document.getElementById('createPhone').value.trim(),
            email: document.getElementById('createEmail').value.trim(),
            password: document.getElementById('createPassword').value,
            vai_tro: document.getElementById('createRole').value,
            ngay_sinh: document.getElementById('createBirthday').value,
            gioi_tinh: document.getElementById('createGender').value,
            dia_chi: document.getElementById('createAddress').value.trim(),
            // Dữ liệu mở rộng
            ma_lop: document.getElementById('createLop').value,
            mon_chuyen_mon: document.getElementById('createMonHoc').value,
            hoc_sinh_con: [] // Xử lý multiselect phụ huynh sau nếu cần
        };

        // Validate cơ bản phía Client
        if (!payload.ho_ten || !payload.email || !payload.password || !payload.vai_tro) {
            notificationEl.textContent = 'Vui lòng điền các trường bắt buộc (*).';
            return;
        }
        if (!validateInput('email', payload.email, 'createNotification')) return;
        
        // Validate logic nghiệp vụ
        if (payload.vai_tro === 'HocSinh' && !payload.ma_lop) {
            notificationEl.textContent = 'Với Học sinh, bắt buộc phải chọn Lớp học.';
            return;
        }

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

    // 2. MỞ MODAL SỬA (EDIT)
    function openEditModal(ma_tai_khoan) {
        // Tìm dòng tr chứa dữ liệu
        const tr = document.querySelector(`tr[data-ma-tai-khoan="${ma_tai_khoan}"]`);
        if (!tr) return;

        const d = tr.dataset; // Lấy dataset
        
        // Đổ dữ liệu vào form sửa
        document.getElementById('editMaTaiKhoan').value = d.maTaiKhoan;
        document.getElementById('editFullName').value = d.hoTen;
        document.getElementById('editPhone').value = d.soDienThoai; // Readonly
        document.getElementById('editEmail').value = d.email;
        document.getElementById('editRole').value = d.vaiTro;
        document.getElementById('editAddress').value = d.diaChi || '';
        document.getElementById('editPassword').value = ''; // Reset pass
        document.getElementById('editNotification').textContent = '';

        editModal.show();
    }

    // 3. CẬP NHẬT TÀI KHOẢN (UPDATE)
    async function handleUpdate() {
        const notiEl = document.getElementById('editNotification');
        
        const payload = {
            ma_tai_khoan: document.getElementById('editMaTaiKhoan').value,
            ho_ten: document.getElementById('editFullName').value.trim(),
            email: document.getElementById('editEmail').value.trim(),
            vai_tro: document.getElementById('editRole').value,
            dia_chi: document.getElementById('editAddress').value.trim(),
            password: document.getElementById('editPassword').value
        };

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

    // 4. MỞ MODAL XÓA (DELETE)
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

    // 5. XÁC NHẬN XÓA
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
                showGlobalNotification(data.message, 'warning'); // Màu vàng cho action xóa
                // Xóa dòng khỏi bảng ngay lập tức (UI trick)
                const tr = document.querySelector(`tr[data-ma-tai-khoan="${ma_tai_khoan}"]`);
                if (tr) tr.remove();
            } else {
                notiEl.textContent = data.message;
            }
        } catch (err) {
            notiEl.textContent = "Lỗi kết nối server.";
        }
    }

    // 6. TÌM KIẾM (CLIENT SIDE - FILTER)
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
        
        // Hiển thị thông báo tìm kiếm
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

    // --- CÁC HÀM TIỆN ÍCH (HELPER) ---
    
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
        
        // Nút đóng
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
