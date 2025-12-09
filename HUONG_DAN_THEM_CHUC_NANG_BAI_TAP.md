# 📚 HƯỚNG DẪN THÊM CHỨC NĂNG SỬA/XÓA BÀI TẬP + UPLOAD FILE ĐỀ BÀI

## 🎯 TÓM TẮT NHỮNG GÌ ĐÃ THÊM

### Backend (Hoàn tất):
✅ Model: `suaBaiTap()`, `xoaBaiTap()`, `getThongKeNopBai()` (fix timezone)  
✅ Controller: `suaBaiTapApi()`, `xoaBaiTapApi()`, `uploadDeBaiApi()`, `downloadDeBaiApi()`, `downloadBaiNopApi()`

### Frontend (Cần thêm vào view):
⏳ Modal sửa bài tập  
⏳ Nút hành động (Sửa/Xóa/Download) trong bảng  
⏳ JavaScript xử lý sửa/xóa bài tập  
⏳ Upload file đề bài  

---

## 📝 PHẦN 1: Thêm Modal Sửa Bài Tập

**Tìm trong file view `danh_sach_bai_tap_view.php`** (hoặc file quản lý bài tập của GV)

**Thêm sau bảng danh sách bài tập:**

```html
<!-- MODAL SỬA BÀI TẬP -->
<div class="modal fade" id="modalSuaBaiTap" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pencil-square"></i> Chỉnh Sửa Bài Tập
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="notificationSuaBaiTap" class="alert" style="display: none;"></div>
                <form id="formSuaBaiTap">
                    <input type="hidden" id="editMaBaiTap">
                    
                    <div class="mb-3">
                        <label for="editTenBaiTap" class="form-label">Tên bài tập:</label>
                        <input type="text" class="form-control" id="editTenBaiTap" required>
                    </div>

                    <div class="mb-3">
                        <label for="editMoTa" class="form-label">Mô tả:</label>
                        <textarea class="form-control" id="editMoTa" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="editHanNop" class="form-label">Hạn nộp:</label>
                        <input type="datetime-local" class="form-control" id="editHanNop" required>
                    </div>

                    <!-- Nội dung chi tiết (Tự luận) -->
                    <div id="editContainerTuLuan" style="display: none;" class="mb-3">
                        <label for="editNoiDungTuLuan" class="form-label">Nội dung đề bài:</label>
                        <textarea class="form-control" id="editNoiDungTuLuan" rows="4"></textarea>
                    </div>

                    <!-- File đề bài -->
                    <div id="editContainerFile" style="display: none;" class="mb-3">
                        <label for="editFileDeBai" class="form-label">File đề bài:</label>
                        <input type="file" class="form-control" id="editFileDeBai" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png">
                        <small class="text-muted">Định dạng: PDF, Word, PowerPoint, ảnh (tối đa 10MB)</small>
                        <div id="editFileStatus" class="mt-2"></div>
                    </div>

                    <!-- Trắc nghiệm -->
                    <div id="editContainerTracNghiem" style="display: none;" class="mb-3">
                        <label for="editThroiGianLamBai" class="form-label">Thời gian làm bài (phút):</label>
                        <input type="number" class="form-control" id="editThroiGianLamBai" min="1" value="60">
                        <label for="editCauHoiJSON" class="form-label mt-2">Danh sách câu hỏi (JSON):</label>
                        <textarea class="form-control" id="editCauHoiJSON" rows="4" placeholder='[{"cau_hoi": "...", "a": "...", "b": "...", "c": "...", "d": "...", "dap_an": "a"}]'></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-warning fw-bold" id="btnSuaBaiTap" onclick="submitSuaBaiTap()">
                    <i class="bi bi-check2-circle"></i> Lưu Thay Đổi
                </button>
            </div>
        </div>
    </div>
</div>
```

---

## 📝 PHẦN 2: Thêm Cột Hành Động vào Bảng

**Tìm bảng danh sách bài tập, thêm cột mới:**

```html
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Mã</th>
            <th>Tên Bài Tập</th>
            <th>Loại</th>
            <th>Hạn Nộp</th>
            <th>Đã Nộp</th>
            <th style="width: 150px;">Hành Động</th>  <!-- ← THÊM CỘT NÀY -->
        </tr>
    </thead>
    <tbody id="tableBody">
        <!-- Render từ JavaScript -->
    </tbody>
</table>
```

**JavaScript render bảng (sửa lại phần render):**

```javascript
function renderBangBaiTap(danhSachBaiTap) {
    const tbody = document.getElementById('tableBody');
    tbody.innerHTML = '';

    danhSachBaiTap.forEach(bai => {
        const hanNop = new Date(bai.han_nop).toLocaleString('vi-VN');
        const trangThaiText = bai.so_luong_da_nop >= bai.si_so ? 'Hoàn tất' : 'Chưa hết';
        const trangThaiClass = trangThaiText === 'Hoàn tất' ? 'text-success' : 'text-warning';

        let loaiText = '';
        if (bai.loai_bai_tap === 'TuLuan') loaiText = '<span class="badge bg-info">Tự Luận</span>';
        else if (bai.loai_bai_tap === 'UploadFile') loaiText = '<span class="badge bg-secondary">Upload File</span>';
        else loaiText = '<span class="badge bg-primary">Trắc Nghiệm</span>';

        tbody.innerHTML += `
            <tr>
                <td>${bai.ma_bai_tap}</td>
                <td>${bai.ten_bai_tap}</td>
                <td>${loaiText}</td>
                <td>${hanNop}</td>
                <td class="${trangThaiClass}">${bai.so_luong_da_nop}/${bai.si_so}</td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="openEditModal(${bai.ma_bai_tap})" title="Sửa">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteBaiTap(${bai.ma_bai_tap})" title="Xóa">
                        <i class="bi bi-trash"></i>
                    </button>
                    <button class="btn btn-sm btn-success" onclick="downloadDeBai(${bai.ma_bai_tap})" title="Tải đề bài">
                        <i class="bi bi-download"></i>
                    </button>
                </td>
            </tr>
        `;
    });
}
```

---

## 🔧 PHẦN 3: Thêm JavaScript Xử Lý

**Thêm vào cuối file JS (trước `</script>`):**

```javascript
// ========== CHỨC NĂNG SỬA BÀI TẬP ==========
const modalSuaBaiTap = new bootstrap.Modal(document.getElementById('modalSuaBaiTap'));

async function openEditModal(maBaiTap) {
    document.getElementById('editMaBaiTap').value = maBaiTap;
    document.getElementById('notificationSuaBaiTap').style.display = 'none';

    // Tìm dữ liệu bài tập (từ danh sách render trước đó)
    const baiTap = currentDanhSachBaiTap.find(b => b.ma_bai_tap == maBaiTap);
    if (!baiTap) {
        alert('Không tìm thấy bài tập!');
        return;
    }

    // Điền dữ liệu vào form
    document.getElementById('editTenBaiTap').value = baiTap.ten_bai_tap || '';
    document.getElementById('editMoTa').value = baiTap.mo_ta || '';
    
    // Convert datetime sang format datetime-local
    const hanNop = new Date(baiTap.han_nop);
    document.getElementById('editHanNop').value = hanNop.toISOString().slice(0, 16);

    // Hiển thị phần nội dung chi tiết theo loại
    document.getElementById('editContainerTuLuan').style.display = 'none';
    document.getElementById('editContainerFile').style.display = 'none';
    document.getElementById('editContainerTracNghiem').style.display = 'none';

    if (baiTap.loai_bai_tap === 'TuLuan') {
        document.getElementById('editContainerTuLuan').style.display = 'block';
        // TODO: Lấy nội dung từ bảng bai_tap_tu_luan
    } else if (baiTap.loai_bai_tap === 'UploadFile') {
        document.getElementById('editContainerFile').style.display = 'block';
    } else if (baiTap.loai_bai_tap === 'TracNghiem') {
        document.getElementById('editContainerTracNghiem').style.display = 'block';
        // TODO: Lấy câu hỏi từ bảng bai_tap_trac_nghiem
    }

    modalSuaBaiTap.show();
}

async function submitSuaBaiTap() {
    const maBaiTap = document.getElementById('editMaBaiTap').value;
    const tenBaiTap = document.getElementById('editTenBaiTap').value.trim();
    const moTa = document.getElementById('editMoTa').value.trim();
    const hanNop = document.getElementById('editHanNop').value.trim();

    if (!tenBaiTap || !hanNop) {
        alert('Vui lòng nhập đầy đủ thông tin!');
        return;
    }

    const btn = document.getElementById('btnSuaBaiTap');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang lưu...';

    const formData = new FormData();
    formData.append('ma_bai_tap', maBaiTap);
    formData.append('ten_bai_tap', tenBaiTap);
    formData.append('mo_ta', moTa);
    formData.append('han_nop', hanNop);

    try {
        const res = await fetch(BASE_URL + '/giaovien/suaBaiTapApi', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            alert('✅ ' + data.message);
            modalSuaBaiTap.hide();
            location.reload();
        } else {
            alert('❌ ' + data.message);
        }
    } catch (err) {
        alert('❌ Lỗi: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle"></i> Lưu Thay Đổi';
    }
}

async function deleteBaiTap(maBaiTap) {
    if (!confirm('⚠️ Bạn có chắc muốn xóa bài tập này không?\n\nLưu ý: Chỉ xóa được nếu chưa có HS nào nộp bài!')) {
        return;
    }

    const formData = new FormData();
    formData.append('ma_bai_tap', maBaiTap);

    try {
        const res = await fetch(BASE_URL + '/giaovien/xoaBaiTapApi', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ ' + data.message);
        }
    } catch (err) {
        alert('❌ Lỗi: ' + err.message);
    }
}

// ========== CHỨC NĂNG UPLOAD + DOWNLOAD ==========

// Upload file đề bài
async function uploadFileDeBai(file, callback) {
    const formData = new FormData();
    formData.append('file', file);

    try {
        const res = await fetch(BASE_URL + '/giaovien/uploadDeBaiApi', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            callback(data.file_path); // Trả về đường dẫn file
        } else {
            alert('❌ ' + data.message);
        }
    } catch (err) {
        alert('❌ Lỗi upload: ' + err.message);
    }
}

// Download file đề bài
function downloadDeBai(maBaiTap) {
    window.location.href = BASE_URL + `/giaovien/downloadDeBaiApi?ma_bai_tap=${maBaiTap}`;
}

// Download file bài nộp
function downloadBaiNop(maBaiNop) {
    window.location.href = BASE_URL + `/giaovien/downloadBaiNopApi?ma_bai_nop=${maBaiNop}`;
}
```

---

## 🎯 PHẦN 4: Lưu ý khi thêm vào View

1. **Khởi tạo biến global (đầu file):**
   ```javascript
   const BASE_URL = '<?php echo BASE_URL; ?>';
   let currentDanhSachBaiTap = [];
   ```

2. **Lưu danh sách khi render:**
   ```javascript
   currentDanhSachBaiTap = danhSachBaiTap;
   renderBangBaiTap(danhSachBaiTap);
   ```

3. **Tạo thư mục upload nếu chưa có:**
   ```bash
   mkdir -p public/uploads/debai/
   mkdir -p public/uploads/bailam/
   chmod 755 public/uploads/
   ```

---

## ✅ CHECKLIST HOÀN TẤT

- [ ] Thêm Modal Sửa Bài Tập vào View
- [ ] Thêm cột "Hành Động" vào bảng
- [ ] Thêm JavaScript xử lý sửa/xóa/download
- [ ] Tạo thư mục `uploads/debai/` và `uploads/bailam/`
- [ ] Test upload file đề bài
- [ ] Test download file
- [ ] Test sửa bài tập
- [ ] Test xóa bài tập (chỉ khi chưa có HS nộp)

---

**Cần giúp gì thêm không? 😊**
