<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Điểm Danh Trực Tuyến</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background-color: #f0f2f5; 
            font-family: 'Inter', sans-serif;
        }
        .main-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header-card {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            color: white;
            border: none;
        }
        .session-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            border: 0;
            border-radius: 12px;
            overflow: hidden;
            background: white;
        }
        .session-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
        }
        .status-badge {
            font-size: 0.85rem;
            padding: 0.4em 0.8em;
            border-radius: 20px;
        }
        /* Border left indicators */
        .card-waiting { border-left: 5px solid #dc3545; }
        .card-done { border-left: 5px solid #198754; }
        .card-history { border-left: 5px solid #6c757d; }
    </style>
</head>
<body>
    <div class="container p-4 main-container">
        
        <div class="card header-card shadow-sm mb-4 rounded-4">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-1"><i class="bi bi-qr-code-scan"></i> Điểm Danh</h2>
                    <p class="mb-0 opacity-75">Xin chào, <strong><?php echo htmlspecialchars($data['user_name']); ?></strong>!</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-light text-success fw-bold shadow-sm">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>

        <div id="main-notification" class="alert" style="display: none;"></div>
        
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-hourglass-split text-primary me-2 fs-5"></i>
            <h5 class="fw-bold text-primary mb-0">Phiên đang diễn ra</h5>
        </div>
        
        <div class="row g-3 mb-5" id="session-list">
            <?php 
                $hasActiveSession = false;
                $activeHtml = '';
                $historyHtml = '';
                $now = time();

                foreach ($data['danh_sach_phien'] as $phien) {
                    $trang_thai_phien = $phien['trang_thai_phien'];
                    $da_nop = $phien['trang_thai_diem_danh'] == 'CoMat';
                    $thoi_gian_dong = strtotime($phien['thoi_gian_dong']);
                    
                    // Logic phân loại (Giữ nguyên logic của bạn)
                    $cardStyleClass = 'card-history'; 
                    $buttonHtml = '';
                    $statusBadge = '';
                    $timeInfo = '';

                    $dongLuc = (new DateTime($phien['thoi_gian_dong']))->format('H:i d/m');

                    if ($da_nop) {
                        // Đã điểm danh
                        $cardStyleClass = 'card-done';
                        $nopLuc = $phien['thoi_gian_nop'] ? (new DateTime($phien['thoi_gian_nop']))->format('H:i:s d/m/Y') : 'N/A';
                        $statusBadge = "<span class='badge bg-success bg-opacity-10 text-success border border-success status-badge'><i class='bi bi-check-circle-fill'></i> Đã có mặt lúc $nopLuc</span>";
                        $timeInfo = "<small class='text-muted'>Đóng phiên: $dongLuc</small>";
                        
                        $historyHtml .= buildNiceCard($phien, $cardStyleClass, $statusBadge, $buttonHtml, $timeInfo);

                    } elseif ($trang_thai_phien == 'DangDiemDanh' && $now <= $thoi_gian_dong) {
                        // Đang chờ điểm danh (ACTIVE)
                        $hasActiveSession = true;
                        $cardStyleClass = 'card-waiting';
                        $statusBadge = "<span class='badge bg-danger bg-opacity-10 text-danger border border-danger status-badge'><i class='bi bi-exclamation-circle-fill'></i> Chưa điểm danh</span>";
                        $timeInfo = "<small class='text-danger fw-bold'>Hết hạn: $dongLuc</small>";
                        
                        // Nút mở Modal thay vì gọi hàm cũ
                        $tieuDeEscape = htmlspecialchars($phien['tieu_de'], ENT_QUOTES);
                        $buttonHtml = "
                        <button class='btn btn-success fw-bold w-100 py-2 shadow-sm' 
                                onclick='openDiemDanhModal({$phien['ma_phien']}, \"$tieuDeEscape\")'>
                            <i class='bi bi-fingerprint'></i> ĐIỂM DANH NGAY
                        </button>";
                        
                        $activeHtml .= buildNiceCard($phien, $cardStyleClass, $statusBadge, $buttonHtml, $timeInfo);

                    } else {
                        // Hết hạn / Đã đóng
                        $cardStyleClass = 'card-history border-secondary';
                        $statusBadge = "<span class='badge bg-secondary status-badge'>Đã kết thúc</span>";
                        $timeInfo = "<small class='text-muted'>Đã đóng: $dongLuc</small>";
                        
                        $historyHtml .= buildNiceCard($phien, $cardStyleClass, $statusBadge, $buttonHtml, $timeInfo);
                    }
                }

                // Hàm build card giao diện mới
                function buildNiceCard($phien, $cardClass, $statusBadge, $buttonHtml, $timeInfo) {
                    return "
                    <div class='col-12'>
                        <div class='card shadow-sm session-card $cardClass'>
                            <div class='card-body p-4'>
                                <div class='row align-items-center'>
                                    <div class='col-md-8 mb-3 mb-md-0'>
                                        <div class='d-flex justify-content-between align-items-start mb-2'>
                                            <h5 class='card-title fw-bold mb-0 text-dark'>{$phien['tieu_de']}</h5>
                                        </div>
                                        <div class='mb-2'>$statusBadge</div>
                                        <p class='text-muted small mb-2'><i class='bi bi-info-circle'></i> {$phien['ghi_chu']}</p>
                                        $timeInfo
                                    </div>
                                    <div class='col-md-4'>
                                        $buttonHtml
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>";
                }

                if (!$hasActiveSession) {
                    echo "
                    <div class='col-12'>
                        <div class='text-center py-5 bg-white rounded-3 shadow-sm border border-light'>
                            <img src='https://cdn-icons-png.flaticon.com/512/7486/7486744.png' width='80' class='mb-3 opacity-50' alt='Relax'>
                            <h6 class='text-muted'>Hiện không có phiên điểm danh nào cần thực hiện.</h6>
                        </div>
                    </div>";
                } else {
                    echo $activeHtml;
                }
            ?>
        </div>

        <div class="d-flex align-items-center mb-3 pt-3 border-top">
            <i class="bi bi-clock-history text-secondary me-2 fs-5"></i>
            <h5 class="fw-bold text-secondary mb-0">Lịch sử điểm danh</h5>
        </div>
        <div class="row g-3">
            <?php 
                echo $historyHtml;
                if (empty($data['danh_sach_phien'])) {
                     echo "<div class='col-12 text-center text-muted fst-italic py-4'>Chưa có dữ liệu lịch sử.</div>";
                }
            ?>
        </div>
    </div>

    <div class="modal fade" id="modalDiemDanh" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock"></i> Xác thực điểm danh</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-3">Bạn đang điểm danh cho phiên: <br><strong class="text-success fs-5" id="modalSessionTitle">...</strong></p>
                    
                    <div id="modal-alert" class="alert alert-danger d-none p-2 small"></div>

                    <form id="formDiemDanh" onsubmit="event.preventDefault(); submitFromModal();">
                        <input type="hidden" id="inputMaPhien">
                        <div class="mb-3">
                            <label for="inputMatKhau" class="form-label text-muted fw-bold small">MẬT KHẨU PHIÊN (NẾU CÓ)</label>
                            <input type="text" class="form-control form-control-lg text-center fw-bold letter-spacing" 
                                   id="inputMatKhau" placeholder="Nhập mã/mật khẩu...">
                            <div class="form-text">Bỏ trống nếu giáo viên không yêu cầu mật khẩu.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-success fw-bold px-4" id="btnConfirmDiemDanh" onclick="submitFromModal()">
                        <i class="bi bi-send"></i> Gửi Điểm Danh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const BASE_URL = "<?php echo BASE_URL ?? ''; ?>";
        // Khởi tạo Modal Bootstrap
        const myModal = new bootstrap.Modal(document.getElementById('modalDiemDanh'));
        
        // Element tham chiếu
        const inputMaPhien = document.getElementById('inputMaPhien');
        const inputMatKhau = document.getElementById('inputMatKhau');
        const modalTitle = document.getElementById('modalSessionTitle');
        const modalAlert = document.getElementById('modal-alert');
        const btnConfirm = document.getElementById('btnConfirmDiemDanh');

        // Hàm mở Modal (thay vì Prompt)
        function openDiemDanhModal(maPhien, tieuDe) {
            // Reset form
            inputMaPhien.value = maPhien;
            modalTitle.textContent = tieuDe;
            inputMatKhau.value = ''; 
            modalAlert.classList.add('d-none');
            btnConfirm.disabled = false;
            btnConfirm.innerHTML = '<i class="bi bi-send"></i> Gửi Điểm Danh';
            
            // Hiện modal
            myModal.show();
            
            // Focus vào ô input sau khi modal hiện (tăng trải nghiệm)
            setTimeout(() => inputMatKhau.focus(), 500);
        }

        // Hàm xử lý gửi data (Logic Fetch API giữ nguyên, chỉ đổi UI)
        async function submitFromModal() {
            const maPhien = inputMaPhien.value;
            const matKhau = inputMatKhau.value.trim();

            // UI Loading
            btnConfirm.disabled = true;
            btnConfirm.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
            modalAlert.classList.add('d-none');

            const formData = new FormData();
            formData.append('ma_phien', maPhien);
            if (matKhau) {
                formData.append('mat_khau', matKhau);
            }

            try {
                // Gọi API như cũ
                const res = await fetch(BASE_URL + '/hocsinh/submitDiemDanhApi', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await res.json();

                if (res.ok && data.success) {
                    // Thành công: Ẩn modal, hiện thông báo xanh ở màn hình chính
                    myModal.hide();
                    
                    const mainNoti = document.getElementById('main-notification');
                    mainNoti.className = 'alert alert-success shadow-sm fw-bold';
                    mainNoti.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${data.message}`;
                    mainNoti.style.display = 'block';
                    
                    // Scroll lên top
                    window.scrollTo({ top: 0, behavior: 'smooth' });

                    // Reload sau 1.5s
                    setTimeout(() => location.reload(), 1500);
                } else {
                    // Thất bại: Hiện lỗi ngay trong Modal để nhập lại
                    throw new Error(data.message || 'Lỗi không xác định');
                }
            } catch (err) {
                // Hiển thị lỗi trong Modal
                modalAlert.textContent = err.message;
                modalAlert.className = 'alert alert-danger p-2 small';
                modalAlert.classList.remove('d-none');
                
                // Reset nút bấm
                btnConfirm.disabled = false;
                btnConfirm.innerHTML = '<i class="bi bi-arrow-repeat"></i> Thử lại';
            }
        }
    </script>
</body>
</html>