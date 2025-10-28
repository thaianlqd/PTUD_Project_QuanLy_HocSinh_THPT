<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Giáo Viên (CRUD) | THPT Manager</title>
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
        tr.highlight-search {
            background-color: #fff3cd !important;
            transition: background-color 0.5s ease;
        }
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <div class="container-fluid p-4">
        <header class="mb-4 p-4 bg-white rounded-3 shadow-sm d-flex justify-content-between align-items-center">
            <div>
                 <h1 class="fw-bold text-success">
                    <i class="bi bi-people-fill me-2"></i> QUẢN LÝ GIÁO VIÊN
                </h1>
                <p class="mb-0 text-muted">Thêm, sửa, xóa thông tin giáo viên trong hệ thống.</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại Dashboard
             </a>
        </header>

        <!-- Thanh Tìm kiếm và Thêm mới -->
        <div class="card p-3 mb-4">
             <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="flex-grow-1 me-3 mb-2 mb-md-0">
                    <form class="d-flex" onsubmit="return handleSearch(event)">
                        <input class="form-control me-2" type="search" placeholder="Nhập SĐT hoặc Họ Tên giáo viên để lọc..." id="searchInput" aria-label="Search">
                        <button class="btn btn-outline-success" type="submit" style="white-space: nowrap;">
                            <i class="bi bi-search"></i> Tìm
                        </button>
                    </form>
                </div>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#userModal" onclick="prepareAddModal()">
                    <i class="bi bi-person-plus-fill me-2"></i> Thêm Giáo Viên Mới
                </button>
             </div>
        </div>
        
         <!-- Thông báo Toàn cục -->
        <div id="globalNotification" class="alert" style="display: none;" role="alert"></div>

        <!-- Bảng Danh Sách Giáo Viên -->
        <div class="card p-0">
            <div class="card-body table-responsive p-0">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-success-custom">
                        <tr>
                            <th>Họ Tên</th>
                            <th>SĐT</th>
                            <th>Email (Username)</th>
                            <th>Chức vụ</th>
                            <th>Trình độ</th>
                            <th>Ngày vào trường</th>
                            <th>Trạng Thái</th>
                            <th class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody id="accountTableBody">
                        <?php if (empty($data['giao_vien'])): ?>
                            <tr>
                                <td colspan="8" class="text-center p-5 text-muted">Không tìm thấy giáo viên nào.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['giao_vien'] as $gv): ?>
                                <tr class="account-row" 
                                    data-ma-nguoi-dung="<?php echo $gv['ma_nguoi_dung']; ?>"
                                    data-ma-tai-khoan="<?php echo $gv['ma_tai_khoan']; ?>">
                                    
                                    <td class="fw-bold ho-ten"><?php echo htmlspecialchars($gv['ho_ten']); ?></td>
                                    <td class="so-dien-thoai"><?php echo htmlspecialchars($gv['so_dien_thoai']); ?></td>
                                    <td><?php echo htmlspecialchars($gv['email']); ?></td>
                                    <td><?php echo htmlspecialchars($gv['chuc_vu']); ?></td>
                                    <td><?php echo htmlspecialchars($gv['trinh_do_chuyen_mon']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($gv['ngay_vao_truong']))); ?></td>
                                    <td>
                                        <?php if ($gv['trang_thai'] == 'HoatDong'): ?>
                                            <span class="badge bg-success">Hoạt Động</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($gv['trang_thai']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary btn-action" onclick="openEditModal(<?php echo $gv['ma_nguoi_dung']; ?>)">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-action" onclick="openDeleteModal(<?php echo $gv['ma_tai_khoan']; ?>, '<?php echo htmlspecialchars($gv['ho_ten'], ENT_QUOTES); ?>')">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Thêm/Sửa Giáo Viên -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="userForm">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalTitle"><i class="bi bi-person-plus-fill me-2"></i> Thêm Giáo Viên Mới</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Input ẩn chứa ID -->
                        <input type="hidden" id="ma_nguoi_dung" name="ma_nguoi_dung">
                        <input type="hidden" id="ma_tai_khoan" name="ma_tai_khoan">
                        
                        <div class="row g-3">
                            <h6 class="col-12 text-primary fw-bold border-bottom pb-2">Thông tin cá nhân</h6>
                            <div class="col-md-6">
                                <label for="ho_ten" class="form-label">Họ Tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ho_ten" name="ho_ten" required maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label for="so_dien_thoai" class="form-label">Số Điện Thoại <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="so_dien_thoai" name="so_dien_thoai" required pattern="[0-9]{10}">
                            </div>
                            <div class="col-md-6">
                                <label for="ngay_sinh" class="form-label">Ngày Sinh <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="ngay_sinh" name="ngay_sinh" required>
                            </div>
                             <div class="col-md-6">
                                <label for="gioi_tinh" class="form-label">Giới Tính <span class="text-danger">*</span></label>
                                <select class="form-select" id="gioi_tinh" name="gioi_tinh" required>
                                    <option value="Nam">Nam</option>
                                    <option value="Nu">Nữ</option>
                                    <option value="Khac">Khác</option>
                                </select>
                            </div>
                             <div class="col-12">
                                <label for="dia_chi" class="form-label">Địa chỉ</label>
                                <input type="text" class="form-control" id="dia_chi" name="dia_chi" placeholder="Nhập địa chỉ...">
                            </div>
                            
                            <h6 class="col-12 text-primary fw-bold border-bottom pb-2 mt-4">Thông tin công tác & tài khoản</h6>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email (Dùng làm Username) <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                             <div class="col-md-6">
                                <label for="password" class="form-label">Mật khẩu <span id="passwordRequired" class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Để trống nếu không muốn đổi (khi sửa)">
                            </div>
                             <div class="col-md-6">
                                <label for="chuc_vu" class="form-label">Chức vụ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="chuc_vu" name="chuc_vu" required value="Giáo viên bộ môn">
                            </div>
                             <div class="col-md-6">
                                <label for="trinh_do" class="form-label">Trình độ chuyên môn <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="trinh_do" name="trinh_do" required placeholder="Cử nhân Sư phạm...">
                            </div>
                             <div class="col-md-6">
                                <label for="ngay_vao_truong" class="form-label">Ngày vào trường <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="ngay_vao_truong" name="ngay_vao_truong" required>
                            </div>
                        </div>
                        <div id="formNotification" class="mt-3 text-danger"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success" id="saveButton">Lưu Thay Đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Xóa Tài Khoản -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1">
         <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i> Xác Nhận Xóa Giáo Viên</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn **XÓA VĨNH VIỄN** giáo viên này không?</p>
                    <h6 class="text-danger fw-bold"><span id="deleteNameDisplay"></span></h6>
                    <small class="text-muted">Lưu ý: Thao tác này sẽ xóa tài khoản, hồ sơ người dùng và hồ sơ giáo viên. Không thể hoàn tác.</small>
                    <div id="deleteNotification" class="mt-3 text-danger"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <input type="hidden" id="deleteMaTaiKhoan">
                    <button type="button" class="btn btn-danger" onclick="handleDelete()">Xác nhận Xóa</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let userModal, deleteModal;
        const BASE_URL = "<?php echo BASE_URL ?? ''; ?>";

        document.addEventListener('DOMContentLoaded', () => {
            userModal = new bootstrap.Modal(document.getElementById('userModal'));
            deleteModal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
        });

        // --- HÀM TÌM KIẾM ---
        function handleSearch(event) {
            event.preventDefault();
            const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
            const rows = document.querySelectorAll('#accountTableBody tr.account-row');
            let found = false;
            rows.forEach(row => {
                row.classList.remove('highlight-search');
                const hoTen = row.querySelector('.ho-ten').textContent.toLowerCase();
                const phone = row.querySelector('.so-dien-thoai').textContent.toLowerCase();
                if (searchTerm === '' || hoTen.includes(searchTerm) || phone.includes(searchTerm)) {
                    row.style.display = '';
                    if(searchTerm !== '') row.classList.add('highlight-search');
                    found = true;
                } else {
                    row.style.display = 'none';
                }
            });
             return false;
        }

        // --- HÀM HIỂN THỊ THÔNG BÁO ---
        function showGlobalNotification(message, type = 'success') {
            const el = document.getElementById('globalNotification');
            el.textContent = message;
            el.className = `alert alert-${type} alert-dismissible fade show`;
            el.style.display = 'block';
            if (!el.querySelector('.btn-close')) {
                 const closeButton = document.createElement('button');
                 closeButton.type = 'button'; closeButton.className = 'btn-close';
                 closeButton.setAttribute('data-bs-dismiss', 'alert');
                 el.appendChild(closeButton);
            }
            setTimeout(() => { bootstrap.Alert.getInstance(el)?.close(); }, 5000);
        }
        function setFormNotification(msg, type = 'danger') {
             const el = document.getElementById('formNotification');
             el.className = `text-${type}`;
             el.textContent = msg;
        }

        // --- LOGIC THÊM MỚI ---
        function prepareAddModal() {
            document.getElementById('userForm').reset(); // Xóa sạch form
            document.getElementById('modalTitle').textContent = 'Thêm Giáo Viên Mới';
            document.getElementById('ma_nguoi_dung').value = '';
            document.getElementById('ma_tai_khoan').value = '';
            document.getElementById('password').placeholder = 'Nhập mật khẩu (bắt buộc)';
            document.getElementById('passwordRequired').style.display = 'inline'; // Hiển thị dấu *
            document.getElementById('password').required = true; // Bắt buộc nhập pass
            document.getElementById('saveButton').textContent = 'Tạo Mới';
            setFormNotification('', 'danger');
        }
        
        // --- LOGIC SỬA ---
        async function openEditModal(ma_nguoi_dung) {
            prepareAddModal(); // Xóa form cũ
            document.getElementById('modalTitle').textContent = 'Cập Nhật Thông Tin Giáo Viên';
            document.getElementById('password').placeholder = 'Để trống nếu không đổi';
            document.getElementById('passwordRequired').style.display = 'none';
            document.getElementById('password').required = false; // Không bắt buộc
            document.getElementById('saveButton').textContent = 'Lưu Thay Đổi';
            setFormNotification('Đang tải dữ liệu...', 'info');

            try {
                const response = await fetch(`${BASE_URL}/quantri/getGiaoVienDetailsApi/${ma_nguoi_dung}`);
                if (!response.ok) throw new Error('Lỗi mạng khi tải dữ liệu.');
                const result = await response.json();
                if (!result.success) throw new Error(result.message);

                const gv = result.data;
                // Điền dữ liệu vào form
                document.getElementById('ma_nguoi_dung').value = gv.ma_nguoi_dung;
                document.getElementById('ma_tai_khoan').value = gv.ma_tai_khoan;
                document.getElementById('ho_ten').value = gv.ho_ten;
                document.getElementById('so_dien_thoai').value = gv.so_dien_thoai;
                document.getElementById('ngay_sinh').value = gv.ngay_sinh;
                document.getElementById('gioi_tinh').value = gv.gioi_tinh;
                document.getElementById('dia_chi').value = gv.dia_chi;
                document.getElementById('email').value = gv.email;
                document.getElementById('chuc_vu').value = gv.chuc_vu;
                document.getElementById('trinh_do').value = gv.trinh_do_chuyen_mon;
                document.getElementById('ngay_vao_truong').value = gv.ngay_vao_truong;
                
                setFormNotification('', 'danger'); // Xóa text "Đang tải"
                userModal.show();
                
            } catch (error) {
                console.error("Lỗi openEditModal:", error);
                showGlobalNotification(`Lỗi: ${error.message}`, 'danger');
            }
        }
        
        // --- XỬ LÝ SUBMIT FORM (THÊM VÀ SỬA) ---
        document.getElementById('userForm').addEventListener('submit', async function(e) {
            e.preventDefault(); // Ngăn form submit
            setFormNotification('Đang xử lý...', 'info');
            const saveButton = document.getElementById('saveButton');
            saveButton.disabled = true;

            const ma_nguoi_dung = document.getElementById('ma_nguoi_dung').value;
            const isEditing = !!ma_nguoi_dung; // Kiểm tra xem đây là Sửa hay Thêm

            // Lấy dữ liệu từ form
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // Lấy cả ma_tai_khoan (nếu đang sửa)
            if(isEditing) {
                data.ma_tai_khoan = document.getElementById('ma_tai_khoan').value;
            }

            const apiUrl = isEditing ? `${BASE_URL}/quantri/updateGiaoVienApi` : `${BASE_URL}/quantri/addGiaoVienApi`;

            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();

                if (result.success) {
                    userModal.hide();
                    showGlobalNotification(result.message, 'success');
                    // Tải lại trang để cập nhật danh sách
                    setTimeout(() => window.location.reload(), 1500); 
                } else {
                    setFormNotification(result.message || 'Lỗi không xác định.', 'danger');
                }
            } catch (error) {
                 console.error("Lỗi submit form:", error);
                 setFormNotification('Lỗi kết nối máy chủ.', 'danger');
            } finally {
                saveButton.disabled = false;
            }
        });

        // --- LOGIC XÓA ---
        function openDeleteModal(ma_tai_khoan, ho_ten) {
            document.getElementById('deleteNameDisplay').textContent = ho_ten;
            document.getElementById('deleteMaTaiKhoan').value = ma_tai_khoan;
            document.getElementById('deleteNotification').textContent = '';
            deleteModal.show();
        }

        async function handleDelete() {
            const ma_tai_khoan = document.getElementById('deleteMaTaiKhoan').value;
            const notificationEl = document.getElementById('deleteNotification');
            const deleteButton = document.querySelector('#deleteAccountModal .btn-danger');
            
            deleteButton.disabled = true;
            notificationEl.textContent = 'Đang xóa...';

             try {
                const response = await fetch(`${BASE_URL}/quantri/deleteGiaoVienApi`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ma_tai_khoan: ma_tai_khoan })
                });

                const result = await response.json();

                if (result.success) {
                    deleteModal.hide();
                    showGlobalNotification(result.message, 'success');
                    // Xóa hàng khỏi DOM
                    const tr = document.querySelector(`tr[data-ma-tai-khoan="${ma_tai_khoan}"]`);
                    if (tr) tr.remove();
                } else {
                    notificationEl.textContent = result.message || 'Lỗi không xác định.';
                }
            } catch (error) {
                console.error("Lỗi fetch delete:", error);
                notificationEl.textContent = 'Lỗi kết nối máy chủ.';
            } finally {
                 deleteButton.disabled = false;
            }
        }
    </script>
</body>
</html>
