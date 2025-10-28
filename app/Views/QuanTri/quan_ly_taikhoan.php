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
            <button class="btn btn-primary" disabled title="Chức năng đang phát triển">
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
    </div>

    <!-- Modal Sửa Tài Khoản -->
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
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i> Cấp Tài Khoản Mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Chức năng này cần được xây dựng ở một flow riêng (Tạo `tai_khoan` -> Lấy `ma_tai_khoan` -> Tạo `nguoi_dung` -> Tạo vai trò chi tiết `hoc_sinh`/`giao_vien`...).</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Khởi tạo các đối tượng Modal của Bootstrap
        let editModal, deleteModal;
        const BASE_URL = "<?php echo BASE_URL ?? ''; ?>"; // Lấy BASE_URL từ PHP

        document.addEventListener('DOMContentLoaded', () => {
            editModal = new bootstrap.Modal(document.getElementById('editAccountModal'));
            deleteModal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
        });

        // --- HÀM VALIDATION (Client-side) ---
        function validateInput(field, value, notificationElId) {
             const notification = document.getElementById(notificationElId);
            notification.textContent = ''; // Xóa lỗi cũ
            if (field === 'fullName') {
                if (value.length > 100) { notification.textContent = "Họ tên không được vượt quá 100 ký tự."; return false; }
            }
            if (field === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) { notification.textContent = "Email không đúng định dạng."; return false; }
            }
             if (field === 'password') {
                if (value.length > 0 && value.length < 6) { // Chỉ kiểm tra nếu người dùng nhập
                    notification.textContent = "Mật khẩu mới phải có ít nhất 6 ký tự."; return false; 
                }
            }
            return true; // Hợp lệ
        }

        // --- CÁC HÀM XỬ LÝ CHÍNH ---

        // Xử lý Tìm kiếm (Client-side)
        function handleSearch(event) {
            event.preventDefault();
            const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
            const rows = document.querySelectorAll('#accountTableBody tr.account-row');
            let found = false;

            rows.forEach(row => {
                row.classList.remove('highlight-search'); // Xóa highlight cũ
                const hoTen = row.dataset.hoTen.toLowerCase();
                const phone = row.dataset.soDienThoai.toLowerCase();

                if (searchTerm === '') {
                    row.style.display = ''; // Hiển thị lại tất cả
                    found = true;
                } else if (hoTen.includes(searchTerm) || phone.includes(searchTerm)) {
                    row.style.display = ''; // Hiển thị hàng khớp
                    row.classList.add('highlight-search'); // Highlight hàng
                    found = true;
                } else {
                    row.style.display = 'none'; // Ẩn hàng không khớp
                }
            });
            
             const helpText = document.getElementById('searchHelp');
             if (!found && searchTerm !== '') {
                 helpText.textContent = `Không tìm thấy tài khoản nào khớp với "${searchTerm}".`;
                 helpText.classList.remove('text-muted');
                 helpText.classList.add('text-danger');
             } else {
                 helpText.textContent = 'Tìm kiếm sẽ lọc danh sách bên dưới.';
                 helpText.classList.remove('text-danger');
                 helpText.classList.add('text-muted');
             }
             
            return false; // Ngăn form submit
        }

        // Mở Modal Sửa
        function openEditModal(ma_tai_khoan) {
            // Tìm <tr> tương ứng bằng data-ma-tai-khoan
            const tr = document.querySelector(`tr[data-ma-tai-khoan="${ma_tai_khoan}"]`);
            if (!tr) {
                alert("Lỗi: Không tìm thấy dữ liệu tài khoản.");
                return;
            }
            const account = tr.dataset; // Lấy tất cả data-* attributes

            // Điền dữ liệu vào form
            document.getElementById('editMaTaiKhoan').value = account.maTaiKhoan;
            document.getElementById('editFullName').value = account.hoTen;
            document.getElementById('editPhone').value = account.soDienThoai;
            document.getElementById('editEmail').value = account.email;
            document.getElementById('editRole').value = account.vaiTro;
            document.getElementById('editAddress').value = account.diaChi || '';
            document.getElementById('editPassword').value = ''; // Luôn xóa trống
            document.getElementById('editNotification').textContent = '';

            editModal.show();
        }

        // Xử lý Cập nhật (Gọi API)
        async function handleUpdate() {
            const notificationEl = document.getElementById('editNotification');
            
            // Lấy dữ liệu từ form
            const ma_tai_khoan = document.getElementById('editMaTaiKhoan').value;
            const ho_ten = document.getElementById('editFullName').value;
            const email = document.getElementById('editEmail').value;
            const vai_tro = document.getElementById('editRole').value;
            const password = document.getElementById('editPassword').value; // Để trống nếu không đổi
            const dia_chi = document.getElementById('editAddress').value;

            // Validate
            if (!validateInput('fullName', ho_ten, 'editNotification') || 
                !validateInput('email', email, 'editNotification') ||
                !validateInput('password', password, 'editNotification')) {
                return; // Dừng nếu lỗi
            }

            const payload = {
                ma_tai_khoan: ma_tai_khoan,
                ho_ten: ho_ten,
                email: email, // Email mới cũng sẽ là username mới
                vai_tro: vai_tro,
                dia_chi: dia_chi,
                password: password // Gửi mật khẩu (trống hoặc mới)
            };

            try {
                const response = await fetch(`${BASE_URL}/quantri/updateTaiKhoan`, {
                    method: 'POST', // Hoặc PUT
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (result.success) {
                    editModal.hide();
                    showGlobalNotification(result.message, 'success');
                    // Cập nhật lại bảng (Cách đơn giản: reload trang)
                    // Cách tốt hơn: Cập nhật DOM (phức tạp hơn)
                    window.location.reload(); // Tải lại trang để thấy thay đổi
                } else {
                    notificationEl.textContent = result.message || 'Lỗi không xác định.';
                }
            } catch (error) {
                console.error("Lỗi fetch update:", error);
                notificationEl.textContent = 'Lỗi kết nối máy chủ. Vui lòng thử lại.';
            }
        }
        
        // Mở Modal Xóa
        function openDeleteModal(ma_tai_khoan) {
            const tr = document.querySelector(`tr[data-ma-tai-khoan="${ma_tai_khoan}"]`);
             if (!tr) {
                alert("Lỗi: Không tìm thấy dữ liệu tài khoản.");
                return;
            }
            const account = tr.dataset;

            document.getElementById('deleteFullNameDisplay').textContent = account.hoTen;
            document.getElementById('deletePhoneDisplay').textContent = account.soDienThoai;
            document.getElementById('deleteRoleDisplay').textContent = account.vaiTro;
            document.getElementById('deleteMaTaiKhoan').value = account.maTaiKhoan; // Lưu ID vào input ẩn
            document.getElementById('deleteNotification').textContent = '';
            
            deleteModal.show();
        }

        // Xử lý Xóa (Gọi API)
        async function handleDelete() {
            const ma_tai_khoan = document.getElementById('deleteMaTaiKhoan').value;
            const notificationEl = document.getElementById('deleteNotification');

            if (!ma_tai_khoan) {
                notificationEl.textContent = 'Lỗi: Không tìm thấy mã tài khoản.';
                return;
            }

             try {
                const response = await fetch(`${BASE_URL}/quantri/deleteTaiKhoan`, {
                    method: 'POST', // Hoặc DELETE
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
                notificationEl.textContent = 'Lỗi kết nối máy chủ. Vui lòng thử lại.';
            }
        }

        // Hiển thị thông báo chung
        function showGlobalNotification(message, type = 'success') {
            const el = document.getElementById('globalNotification');
            el.textContent = message;
            el.className = `alert alert-${type} alert-dismissible fade show`;
            el.style.display = 'block';
            
            // Thêm nút đóng (nếu chưa có)
            if (!el.querySelector('.btn-close')) {
                 const closeButton = document.createElement('button');
                 closeButton.type = 'button';
                 closeButton.className = 'btn-close';
                 closeButton.setAttribute('data-bs-dismiss', 'alert');
                 closeButton.setAttribute('aria-label', 'Close');
                 el.appendChild(closeButton);
            }

            // Tự động ẩn sau 5s
            setTimeout(() => {
                 if (el) el.style.display = 'none';
            }, 5000);
        }

    </script>
</body>
</html>
