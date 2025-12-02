<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Điểm Danh Trực Tuyến</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; }
        .session-card {
            transition: all 0.2s ease;
            border-left-width: 5px;
        }
        .session-card.border-success { border-left-color: #198754 !important; }
        .session-card.border-secondary { border-left-color: #6c757d !important; }
        .session-card.border-danger { border-left-color: #dc3545 !important; }
    </style>
</head>
<body>
    <div class="container p-4">
        <header class="mb-4 p-4 bg-white rounded-3 shadow-sm">
            <h1 class="fw-bold text-center text-success"><i class="bi bi-person-check-fill me-2"></i> ĐIỂM DANH CỦA TÔI</h1>
            <p class="text-center text-muted">Chào mừng, <?php echo htmlspecialchars($data['user_name']); ?>!</p>
            <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-outline-secondary btn-sm float-end" style="margin-top: -50px;"><i class="bi bi-arrow-left"></i> Dashboard</a>
        </header>

        <div id="notification" class="alert" style="display: none;"></div>
        
        <h4 class="text-primary mb-3">Phiên điểm danh đang chờ:</h4>
        
        <div class="row g-3" id="session-list">
            <?php 
                $hasActiveSession = false;
                $activeHtml = '';
                $historyHtml = '';
                $now = time();

                foreach ($data['danh_sach_phien'] as $phien) {
                    $trang_thai_phien = $phien['trang_thai_phien'];
                    $da_nop = $phien['trang_thai_diem_danh'] == 'CoMat';
                    $thoi_gian_dong = strtotime($phien['thoi_gian_dong']);
                    
                    // Xử lý logic trạng thái
                    $cardClass = 'border-secondary'; // Lịch sử (mặc định)
                    $buttonHtml = '';
                    $statusText = '';

                    if ($da_nop) {
                        $cardClass = 'border-success';
                        $nopLuc = $phien['thoi_gian_nop'] ? (new DateTime($phien['thoi_gian_nop']))->format('H:i:s d/m/Y') : 'N/A';
                        $statusText = "<strong class='text-success'>Đã điểm danh (lúc $nopLuc)</strong>";
                        $historyHtml .= buildCard($phien, $cardClass, $statusText, $buttonHtml);
                    } elseif ($trang_thai_phien == 'DangDiemDanh' && $now <= $thoi_gian_dong) {
                        $hasActiveSession = true;
                        $cardClass = 'border-danger'; // Đang chờ
                        $statusText = "<strong class='text-danger'>ĐANG CHỜ BẠN ĐIỂM DANH</strong>";
                        $buttonHtml = "<button class='btn btn-success fw-bold w-100' onclick='submitDiemDanh(this, {$phien['ma_phien']})'><i class='bi bi-check-circle-fill'></i> ĐIỂM DANH NGAY</button>";
                        $activeHtml .= buildCard($phien, $cardClass, $statusText, $buttonHtml);
                    } else {
                        // Hết hạn hoặc bị đóng
                        $statusText = "<span class='text-muted'>Đã hết hạn</span>";
                        $historyHtml .= buildCard($phien, $cardClass, $statusText, $buttonHtml);
                    }
                }

                // Hàm trợ giúp để build card (chỉ dùng trong file PHP này)
                function buildCard($phien, $cardClass, $statusText, $buttonHtml) {
                    $dongLuc = (new DateTime($phien['thoi_gian_dong']))->format('H:i d/m');
                    return "
                    <div class='col-12'>
                        <div class='card shadow-sm session-card $cardClass'>
                            <div class='card-body d-md-flex justify-content-between align-items-center'>
                                <div>
                                    <h5 class='card-title fw-bold'>{$phien['tieu_de']}</h5>
                                    <p class='card-text mb-2'>{$phien['ghi_chu']}</p>
                                    <small class='text-muted'>Hạn chót: <span class='fw-bold'>$dongLuc</span> | $statusText</small>
                                </div>
                                <div class='mt-3 mt-md-0' style='min-width: 200px;'>
                                    $buttonHtml
                                </div>
                            </div>
                        </div>
                    </div>";
                }

                // In ra danh sách
                if (!$hasActiveSession) {
                    echo "<div class='col-12'><div class='alert alert-info'>Không có phiên điểm danh nào đang chờ.</div></div>";
                } else {
                    echo $activeHtml;
                }
            ?>
        </div>

        <hr class="my-4">

        <h4 class="text-secondary mb-3">Lịch sử điểm danh:</h4>
        <div class="row g-3">
            <?php 
                echo $historyHtml;
                if (empty($data['danh_sach_phien'])) {
                     echo "<div class='col-12'><div class='alert alert-light'>Chưa có lịch sử điểm danh.</div></div>";
                }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const BASE_URL = "<?php echo BASE_URL ?? ''; ?>";
        const notification = document.getElementById('notification');

        async function submitDiemDanh(button, maPhien) {
            if (!confirm('Bạn có chắc muốn điểm danh phiên này?')) return;

            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
            notification.style.display = 'none';

            const formData = new FormData();
            formData.append('ma_phien', maPhien);

            try {
                const res = await fetch(BASE_URL + '/hocsinh/submitDiemDanhApi', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await res.json();

                if (res.ok && data.success) {
                    notification.className = 'alert alert-success';
                    notification.textContent = data.message;
                    notification.style.display = 'block';
                    // Tải lại trang để cập nhật lịch sử
                    setTimeout(() => location.reload(), 1000);
                } else {
                    throw new Error(data.message || 'Lỗi không rõ');
                }
            } catch (err) {
                notification.className = 'alert alert-danger';
                notification.textContent = 'Lỗi: ' + err.message;
                notification.style.display = 'block';
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-check-circle-fill"></i> ĐIỂM DANH NGAY';
            }
        }
    </script>
</body>
</html>