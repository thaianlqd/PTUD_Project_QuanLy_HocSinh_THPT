<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Tin Tức | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-newspaper text-primary"></i> Quản Lý Tin Tức & Thông Báo</h3>
            <div>
                <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-light border me-2">Quay lại Dashboard</a>
                <button class="btn btn-primary" onclick="openModalAdd()">
                    <i class="bi bi-plus-lg"></i> Viết bài mới
                </button>
            </div>
        </div>

        <div class="table-container">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Tác giả</th>
                        <th>Loại tin</th>
                        <th>Ngày đăng</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="newsTableBody">
                    </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="newsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thêm Bài Viết Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newsForm">
                        <input type="hidden" id="ma_bai_viet">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tiêu đề bài viết:</label>
                            <input type="text" class="form-control" id="tieu_de" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tác giả (Hiển thị):</label>
                                <input type="text" class="form-control" id="tac_gia" value="Ban Tuyển Sinh">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Loại tin:</label>
                                <select class="form-select" id="loai_bai_viet">
                                    <option value="ThongBao">Thông Báo Chung</option>
                                    <option value="TuyenSinh">Tuyển Sinh</option>
                                    <option value="HoatDong">Hoạt Động</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nội dung (Tóm tắt):</label>
                            <textarea class="form-control" id="noi_dung" rows="5" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Trạng thái:</label>
                            <select class="form-select w-auto" id="trang_thai">
                                <option value="DaDang">Đã Đăng (Hiện lên web)</option>
                                <option value="Nhap">Bản Nháp (Ẩn đi)</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" onclick="saveNews()">Lưu Bài Viết</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_URL = "<?php echo BASE_URL; ?>/quanlytintuc";
        const modal = new bootstrap.Modal(document.getElementById('newsModal'));

        // Load danh sách
        async function loadNews() {
            try {
                const res = await fetch(`${API_URL}/getListApi`);
                const json = await res.json();
                const tbody = document.getElementById('newsTableBody');
                tbody.innerHTML = '';
                
                if(json.data && json.data.length > 0) {
                    json.data.forEach(item => {
                        // ĐÃ SỬA: Logic hiển thị Badge theo trạng thái mới
                        let badge = '';
                        if (item.trang_thai === 'DaDang') {
                            badge = '<span class="badge bg-success">Đã Đăng</span>';
                        } else if (item.trang_thai === 'Nhap') {
                            badge = '<span class="badge bg-warning text-dark">Bản Nháp</span>';
                        } else {
                            badge = '<span class="badge bg-secondary">Đã Xóa</span>';
                        }
                        
                        let date = new Date(item.ngay_dang).toLocaleDateString('vi-VN');

                        tbody.innerHTML += `
                            <tr>
                                <td class="fw-bold text-primary">${item.tieu_de}</td>
                                <td>${item.tac_gia}</td>
                                <td><span class="badge border text-dark">${item.loai_bai_viet}</span></td>
                                <td>${date}</td>
                                <td>${badge}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick='editNews(${JSON.stringify(item)})'><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteNews(${item.ma_bai_viet})"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Chưa có bài viết nào</td></tr>';
                }
            } catch (e) { console.error(e); }
        }

        // Mở Modal Thêm
        function openModalAdd() {
            document.getElementById('newsForm').reset();
            document.getElementById('ma_bai_viet').value = '';
            document.getElementById('modalTitle').innerText = 'Thêm Bài Viết Mới';
            // Mặc định chọn Đã Đăng cho tiện
            document.getElementById('trang_thai').value = 'DaDang';
            modal.show();
        }

        // Mở Modal Sửa
        function editNews(item) {
            document.getElementById('ma_bai_viet').value = item.ma_bai_viet;
            document.getElementById('tieu_de').value = item.tieu_de;
            document.getElementById('tac_gia').value = item.tac_gia;
            document.getElementById('loai_bai_viet').value = item.loai_bai_viet;
            document.getElementById('noi_dung').value = item.noi_dung;
            document.getElementById('trang_thai').value = item.trang_thai; // Value này phải khớp với option (DaDang/Nhap)
            
            document.getElementById('modalTitle').innerText = 'Cập Nhật Bài Viết';
            modal.show();
        }

        // Lưu (Thêm hoặc Sửa)
        async function saveNews() {
            const id = document.getElementById('ma_bai_viet').value;
            const payload = {
                tieu_de: document.getElementById('tieu_de').value,
                tac_gia: document.getElementById('tac_gia').value,
                loai_bai_viet: document.getElementById('loai_bai_viet').value,
                noi_dung: document.getElementById('noi_dung').value,
                trang_thai: document.getElementById('trang_thai').value
            };

            let url = `${API_URL}/addApi`;
            if (id) {
                url = `${API_URL}/updateApi`;
                payload.ma_bai_viet = id;
            }

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                const json = await res.json();
                if (json.success) {
                    alert(json.message);
                    modal.hide();
                    loadNews();
                } else {
                    alert('Lỗi: ' + json.message);
                }
            } catch (e) { 
                console.error(e);
                alert('Lỗi hệ thống: ' + e.message);
            }
        }

        // Xóa
        async function deleteNews(id) {
            if (!confirm('Bạn có chắc muốn xóa bài này?')) return;
            try {
                const res = await fetch(`${API_URL}/deleteApi`, {
                    method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({id: id})
                });
                const json = await res.json();
                if (json.success) {
                    loadNews();
                } else {
                    alert(json.message);
                }
            } catch(e) {}
        }

        // Chạy lần đầu
        loadNews();
    </script>
</body>
</html>