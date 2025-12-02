<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán Học Phí</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f8ff; }
        .card:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: 0.3s; }
        .qr-box { border: 1px solid #ddd; padding: 15px; border-radius: 8px; background-color: #fff; }
    </style>
</head>
<body>

<?php
if (!defined('BASE_URL')) define('BASE_URL', '');
$data['hoa_don_chua_tt'] = $data['hoa_don_chua_tt'] ?? [];
$data['hoa_don_da_tt'] = $data['hoa_don_da_tt'] ?? [];
$data['hoa_don_cho_xac_nhan'] = $data['hoa_don_cho_xac_nhan'] ?? [];
?>

<div class="container-fluid p-4">
    <header class="mb-4 p-4 bg-white rounded-3 shadow-sm">
        <h1 class="fw-bold text-center text-primary"><i class="bi bi-wallet2 me-2"></i> THANH TOÁN HỌC PHÍ</h1>
        <p class="text-center text-muted">
            Chào mừng Phụ huynh, <?php echo htmlspecialchars($data['user_name'] ?? 'Khách'); ?>!
        </p>
        <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-outline-secondary btn-sm float-end" style="margin-top: -50px;">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </header>

    <?php if (isset($data['flash_message'])): ?>
        <div class="alert alert-<?php echo $data['flash_message']['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($data['flash_message']['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Hóa đơn chưa thanh toán -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>
                Hóa đơn chưa thanh toán (<?php echo count($data['hoa_don_chua_tt']); ?>)
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã HĐ</th>
                            <th>Ngày Lập</th>
                            <th>Nội Dung</th>
                            <th>Số Tiền</th>
                            <th>Thời Hạn Đóng</th>
                            <th class="text-center">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['hoa_don_chua_tt'])): ?>
                            <tr><td colspan="6" class="text-center p-5 text-muted">Không có hóa đơn nào chưa thanh toán 🎉</td></tr>
                        <?php else: ?>
                            <?php foreach ($data['hoa_don_chua_tt'] as $hd): ?>
                                <tr class="<?php echo !empty($hd['qua_han']) ? 'table-danger' : ''; ?>">
                                    <td><strong>#<?php echo htmlspecialchars($hd['ma_hoa_don']); ?></strong></td>
                                    <td><?php echo date("d/m/Y", strtotime($hd['ngay_lap_hoa_don'])); ?></td>
                                    <td><?php echo htmlspecialchars($hd['ghi_chu'] ?? 'Học phí'); ?></td>
                                    <td class="fw-bold text-danger"><?php echo number_format($hd['thanh_tien'], 0, ',', '.'); ?> VNĐ</td>
                                    <td class="<?php echo !empty($hd['qua_han']) ? 'text-danger fw-bold' : 'text-warning'; ?>">
                                        <?php echo date("d/m/Y", strtotime($hd['ngay_het_han'])); ?>
                                        <?php if (!empty($hd['qua_han'])): ?><br><small>(Quá hạn)</small><?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-success btn-sm"
                                            onclick="openThanhToanModal(
                                                <?php echo (int)$hd['ma_hoa_don']; ?>,
                                                <?php echo (float)$hd['thanh_tien']; ?>,
                                                '<?php echo addslashes(htmlspecialchars($hd['ghi_chu'] ?? 'Học phí')); ?>'
                                            )">
                                            <i class="bi bi-credit-card-fill me-2"></i> Thanh Toán Ngay
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

    <!-- Hóa đơn chờ xác nhận tiền mặt -->
    <?php if (!empty($data['hoa_don_cho_xac_nhan'])): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-dark">
            <h5 class="mb-0"><i class="bi bi-hourglass-split me-2"></i>
                Hóa đơn chờ xác nhận tại trường (<?php echo count($data['hoa_don_cho_xac_nhan']); ?>)
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã HĐ</th><th>Ngày Lập</th><th>Nội Dung</th>
                            <th>Số Tiền</th><th>Thời Hạn Đóng</th><th>Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['hoa_don_cho_xac_nhan'] as $hd): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($hd['ma_hoa_don']); ?></strong></td>
                                <td><?php echo date("d/m/Y", strtotime($hd['ngay_lap_hoa_don'])); ?></td>
                                <td><?php echo htmlspecialchars($hd['ghi_chu'] ?? 'Học phí'); ?></td>
                                <td class="fw-bold text-info"><?php echo number_format($hd['thanh_tien'], 0, ',', '.'); ?> VNĐ</td>
                                <td><?php echo date("d/m/Y", strtotime($hd['ngay_het_han'])); ?></td>
                                <td><span class="badge bg-warning text-dark">Chờ xác nhận tiền mặt</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Lịch sử đã thanh toán -->
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>
                Lịch sử hóa đơn đã thanh toán (<?php echo count($data['hoa_don_da_tt']); ?>)
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã HĐ</th><th>Ngày Lập</th><th>Nội Dung</th>
                            <th>Số Tiền</th><th>Thời Hạn</th><th>Ngày Thanh Toán</th><th>Phương Thức</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['hoa_don_da_tt'])): ?>
                            <tr><td colspan="7" class="text-center p-5 text-muted">Chưa có hóa đơn nào đã thanh toán.</td></tr>
                        <?php else: ?>
                            <?php foreach ($data['hoa_don_da_tt'] as $hd): ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($hd['ma_hoa_don']); ?></strong></td>
                                    <td><?php echo date("d/m/Y", strtotime($hd['ngay_lap_hoa_don'])); ?></td>
                                    <td><?php echo htmlspecialchars($hd['ghi_chu'] ?? 'Học phí'); ?></td>
                                    <td class="fw-bold text-success"><?php echo number_format($hd['thanh_tien'], 0, ',', '.'); ?> VNĐ</td>
                                    <td><?php echo date("d/m/Y", strtotime($hd['ngay_het_han'])); ?></td>
                                    <td><?php echo date("d/m/Y H:i", strtotime($hd['ngay_thanh_toan'])); ?></td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($hd['hinh_thuc_thanh_toan'] ?? 'Không rõ'); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal chọn phương thức thanh toán -->
<div class="modal fade" id="modalThanhToan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">Xác Nhận Thanh Toán</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="notificationModal" class="alert alert-danger" style="display:none;"></div>
                <p>Bạn sắp thanh toán cho hóa đơn:</p>
                <ul class="list-group mb-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Nội dung: <strong id="modal_noi_dung">Học phí</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Số tiền: <strong class="text-danger fs-5" id="modal_so_tien">0 VNĐ</strong>
                    </li>
                </ul>
                <form id="formThanhToan">
                    <input type="hidden" id="modal_ma_hoa_don" name="ma_hoa_don">
                    <label class="form-label fw-bold">Chọn phương thức:</label>
                    <div class="list-group">
                        <label class="list-group-item list-group-item-action">
                            <input class="form-check-input me-2" type="radio" name="phuong_thuc" value="VNPAY" checked>
                            <i class="bi bi-qr-code text-primary"></i> Thanh toán qua VNPAY
                        </label>
                        <label class="list-group-item list-group-item-action">
                            <input class="form-check-input me-2" type="radio" name="phuong_thuc" value="SepayQR">
                            <i class="bi bi-bank2 text-info"></i> Thanh toán bằng QR Ngân hàng (Sepay)
                        </label>
                        <label class="list-group-item list-group-item-action">
                            <input class="form-check-input me-2" type="radio" name="phuong_thuc" value="TienMat">
                            <i class="bi bi-cash-coin text-success"></i> Tiền mặt tại trường
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success fw-bold" id="btnXacNhanTT" onclick="submitThanhToan()">
                    Tiếp Tục Thanh Toán <i class="bi bi-arrow-right-short"></i>
                </button>
            </div>
            <div id="loaderSepay" class="text-center p-3" style="display:none;">
                <span class="spinner-border spinner-border-sm me-2"></span> Đang tạo mã QR...
            </div>
        </div>
    </div>
</div>

<!-- Modal hiển thị QR Sepay -->
<div class="modal fade" id="modalSepayQR" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-qr-code-scan me-2"></i> THANH TOÁN BẰNG VIETQR</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" 
                    onclick="clearInterval(window.pollInterval); window.location.reload();"></button>
            </div>
            <div class="modal-body text-center">
                <div class="alert alert-danger" id="qr_error_message" style="display:none;"></div>
                <div class="qr-box mx-auto mb-3">
                    <img id="qr_image" src="" alt="Mã QR Thanh toán" style="width: 100%; max-width: 250px;">
                </div>
                <h4 class="text-danger fw-bold" id="qr_amount">0 VNĐ</h4>
                <p class="text-muted">Quét mã QR bằng bất kỳ ứng dụng Ngân hàng nào.</p>
                <ul class="list-group mb-3 text-start">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Mã HĐ: <span id="qr_order_id" class="fw-bold text-primary">#0</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Nội dung chuyển khoản (BẮT BUỘC): <span id="qr_ref_code" class="fw-bold text-success">HOCPHI_0</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Trạng thái: <span id="qr_status" class="badge bg-warning text-dark">Đang chờ thanh toán...</span>
                    </li>
                </ul>
                <p class="text-secondary small">Lưu ý: Hệ thống đang tự động kiểm tra giao dịch, không cần F5.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const modalThanhToan = new bootstrap.Modal('#modalThanhToan');
    const modalSepayQR = new bootstrap.Modal('#modalSepayQR');
    const notif = document.getElementById('notificationModal');
    const loader = document.getElementById('loaderSepay');
    const btnXacNhan = document.getElementById('btnXacNhanTT');
    const BASE_URL = "<?php echo BASE_URL; ?>";

    window.pollInterval = null;

    window.openThanhToanModal = (maHD, soTien, noiDung) => {
        document.getElementById('modal_ma_hoa_don').value = maHD;
        document.getElementById('modal_so_tien').textContent = new Intl.NumberFormat('vi-VN').format(soTien) + ' VNĐ';
        document.getElementById('modal_noi_dung').textContent = noiDung || 'Học phí';
        notif.style.display = 'none';
        if (window.pollInterval) clearInterval(window.pollInterval);
        modalThanhToan.show();
    };

    function startPolling(maHoaDon) {
        const statusEl = document.getElementById('qr_status');
        if (window.pollInterval) clearInterval(window.pollInterval);

        statusEl.className = 'badge bg-warning text-dark';
        statusEl.textContent = 'Đang chờ thanh toán...';

        window.pollInterval = setInterval(() => {
            $.ajax({
                url: `${BASE_URL}/thanhtoan/checkSepayStatus`,
                method: 'POST',
                data: { ma_hoa_don: maHoaDon },
                dataType: 'json',
                success: function(res) {
                    if (res.trang_thai_hoa_don === "DaThanhToan") {
                        clearInterval(window.pollInterval);
                        modalSepayQR.hide();
                        alert("✅ Thanh toán thành công! Trang sẽ tải lại...");
                        window.location.reload();
                    } else if (res.trang_thai_hoa_don === "order_not_found") {
                        clearInterval(window.pollInterval);
                        statusEl.className = 'badge bg-danger';
                        statusEl.textContent = 'Hóa đơn không tồn tại';
                    }
                },
                error: () => console.error('Lỗi kiểm tra trạng thái QR')
            });
        }, 2000);
    }

    window.submitThanhToan = async () => {
        const form = document.getElementById('formThanhToan');
        const formData = new FormData(form);
        const phuong_thuc = document.querySelector('input[name="phuong_thuc"]:checked').value;

        notif.style.display = 'none';
        loader.style.display = phuong_thuc === 'SepayQR' ? 'block' : 'none';
        btnXacNhan.disabled = true;
        btnXacNhan.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';

        let endpoint = '';
        if (phuong_thuc === 'VNPAY') endpoint = `${BASE_URL}/thanhtoan/taoYeuCau`;
        else if (phuong_thuc === 'SepayQR') endpoint = `${BASE_URL}/thanhtoan/taoYeuCauSepay`;
        else if (phuong_thuc === 'TienMat') endpoint = `${BASE_URL}/thanhtoan/taoYeuCauTienMat`;

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (res.ok && data.success) {
                modalThanhToan.hide();

                if (phuong_thuc === 'TienMat') {
                    if (data.print_url) window.open(data.print_url, '_blank');
                    window.location.reload();
                } else if (phuong_thuc === 'VNPAY') {
                    setTimeout(() => window.location.href = data.redirect_url, 500);
                } else if (phuong_thuc === 'SepayQR') {
                    const d = data.payment_details;
                    document.getElementById('qr_order_id').textContent = '#' + d.ma_hoa_don;
                    document.getElementById('qr_amount').textContent = new Intl.NumberFormat('vi-VN').format(d.so_tien) + ' VNĐ';
                    document.getElementById('qr_ref_code').textContent = d.ref_code;
                    document.getElementById('qr_image').src = d.qr_img_url;
                    modalSepayQR.show();
                    startPolling(d.ma_hoa_don);
                }
            } else {
                throw new Error(data.message || 'Lỗi không xác định');
            }
        } catch (err) {
            notif.textContent = 'Lỗi: ' + (err.message || 'Kết nối thất bại');
            notif.style.display = 'block';
        } finally {
            loader.style.display = 'none';
            btnXacNhan.disabled = false;
            btnXacNhan.innerHTML = 'Tiếp Tục Thanh Toán <i class="bi bi-arrow-right-short"></i>';
        }
    };
});
</script>
</body>
</html>