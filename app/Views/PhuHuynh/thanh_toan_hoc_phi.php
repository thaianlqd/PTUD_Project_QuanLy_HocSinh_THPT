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
        .invoice-item { transition: all 0.3s ease; }
        
        /* Animation cho icon thành công */
        .success-checkmark { width: 80px; height: 80px; margin: 0 auto; }
        .check-icon { width: 80px; height: 80px; position: relative; border-radius: 50%; box-sizing: content-box; border: 4px solid #4CAF50; }
        .check-icon::before { top: 3px; left: -2px; width: 30px; transform-origin: 100% 50%; border-radius: 100px 0 0 100px; }
        .check-icon::after { top: 0; left: 30px; width: 60px; transform-origin: 0 50%; border-radius: 0 100px 100px 0; animation: rotate-circle 4.25s ease-in; }
        .check-icon::before, .check-icon::after { content: ''; height: 100px; position: absolute; background: #fff; transform: rotate(-45deg); }
        .icon-line { height: 5px; background-color: #4CAF50; display: block; border-radius: 2px; position: absolute; z-index: 10; }
        .line-tip { top: 46px; left: 14px; width: 25px; transform: rotate(45deg); animation: icon-line-tip 0.75s; }
        .line-long { top: 38px; right: 8px; width: 47px; transform: rotate(-45deg); animation: icon-line-long 0.75s; }
        .icon-circle { top: -4px; left: -4px; z-index: 10; width: 80px; height: 80px; border-radius: 50%; position: absolute; box-sizing: content-box; border: 4px solid rgba(76, 175, 80, .5); }
        .icon-fix { top: 8px; width: 5px; left: 26px; z-index: 1; height: 85px; position: absolute; transform: rotate(-45deg); background-color: #fff; }
        @keyframes icon-line-tip { 0% { width: 0; left: 1px; top: 19px; } 54% { width: 0; left: 1px; top: 19px; } 70% { width: 50px; left: -8px; top: 37px; } 84% { width: 17px; left: 21px; top: 48px; } 100% { width: 25px; left: 14px; top: 46px; } }
        @keyframes icon-line-long { 0% { width: 0; right: 46px; top: 54px; } 65% { width: 0; right: 46px; top: 54px; } 84% { width: 55px; right: 0px; top: 35px; } 100% { width: 47px; right: 8px; top: 38px; } }
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

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>
                Hóa đơn chưa thanh toán (<?php echo count($data['hoa_don_chua_tt']); ?>)
            </h5>
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="filter_status" id="filter_all" value="all" checked>
                <label class="btn btn-outline-dark btn-sm fw-bold bg-light" for="filter_all">Tất cả</label>

                <input type="radio" class="btn-check" name="filter_status" id="filter_valid" value="valid">
                <label class="btn btn-outline-dark btn-sm fw-bold bg-light" for="filter_valid">Còn hạn</label>

                <input type="radio" class="btn-check" name="filter_status" id="filter_expired" value="expired">
                <label class="btn btn-outline-dark btn-sm fw-bold bg-light" for="filter_expired">Đã quá hạn</label>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã HĐ</th><th>Ngày Lập</th><th>Nội Dung</th><th>Số Tiền</th><th>Thời Hạn Đóng</th><th class="text-center">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody id="invoice_list_body">
                        <?php if (empty($data['hoa_don_chua_tt'])): ?>
                            <tr><td colspan="6" class="text-center p-5 text-muted">Không có hóa đơn nào chưa thanh toán 🎉</td></tr>
                        <?php else: ?>
                            <?php foreach ($data['hoa_don_chua_tt'] as $hd): ?>
                                <tr class="invoice-item <?php echo !empty($hd['qua_han']) ? 'row-expired table-danger' : 'row-valid'; ?>">
                                    <td><strong>#<?php echo htmlspecialchars($hd['ma_hoa_don']); ?></strong></td>
                                    <td><?php echo date("d/m/Y", strtotime($hd['ngay_lap_hoa_don'])); ?></td>
                                    <td><?php echo htmlspecialchars($hd['ghi_chu'] ?? 'Học phí'); ?></td>
                                    <td class="fw-bold text-danger"><?php echo number_format($hd['thanh_tien'], 0, ',', '.'); ?> VNĐ</td>
                                    <td class="<?php echo !empty($hd['qua_han']) ? 'text-danger fw-bold' : 'text-success'; ?>">
                                        <?php echo date("d/m/Y", strtotime($hd['ngay_het_han'])); ?>
                                        <?php if (!empty($hd['qua_han'])): ?><div class="small"><i class="bi bi-exclamation-triangle-fill"></i> Quá hạn</div><?php else: ?><div class="small text-muted"><i class="bi bi-check-circle"></i> Còn hạn</div><?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($hd['qua_han'])): ?>
                                            <button class="btn btn-secondary btn-sm" disabled><i class="bi bi-lock-fill me-1"></i> Đã khóa</button>
                                        <?php else: ?>
                                            <button class="btn btn-success btn-sm fw-bold" onclick="openThanhToanModal(<?php echo (int)$hd['ma_hoa_don']; ?>, <?php echo (float)$hd['thanh_tien']; ?>, '<?php echo addslashes(htmlspecialchars($hd['ghi_chu'] ?? 'Học phí')); ?>')"><i class="bi bi-credit-card-fill me-1"></i> Thanh Toán</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if (!empty($data['hoa_don_cho_xac_nhan'])): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-dark">
            <h5 class="mb-0"><i class="bi bi-hourglass-split me-2"></i> Hóa đơn chờ xác nhận tại trường</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>Mã HĐ</th><th>Nội Dung</th><th>Số Tiền</th><th>Trạng Thái</th><th>In Phiếu</th></tr></thead>
                    <tbody>
                        <?php foreach ($data['hoa_don_cho_xac_nhan'] as $hd): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($hd['ma_hoa_don']); ?></strong></td>
                                <td><?php echo htmlspecialchars($hd['ghi_chu'] ?? 'Học phí'); ?></td>
                                <td class="fw-bold text-info"><?php echo number_format($hd['thanh_tien'], 0, ',', '.'); ?> VNĐ</td>
                                <td><span class="badge bg-warning text-dark">Chờ xác nhận tiền mặt</span></td>
                                <td><a href="<?php echo BASE_URL; ?>/thanhtoan/inPhieu?ma_hoa_don=<?php echo $hd['ma_hoa_don']; ?>" target="_blank" class="btn btn-outline-dark btn-sm"><i class="bi bi-printer"></i></a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i> Lịch sử đã thanh toán (<?php echo count($data['hoa_don_da_tt']); ?>)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>Mã HĐ</th><th>Nội Dung</th><th>Số Tiền</th><th>Ngày TT</th><th>Phương Thức</th><th>Phiếu</th></tr></thead>
                    <tbody>
                        <?php if (empty($data['hoa_don_da_tt'])): ?>
                            <tr><td colspan="6" class="text-center p-5 text-muted">Chưa có dữ liệu.</td></tr>
                        <?php else: ?>
                            <?php foreach ($data['hoa_don_da_tt'] as $hd): ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($hd['ma_hoa_don']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($hd['ghi_chu'] ?? 'Học phí'); ?></td>
                                    <td class="fw-bold text-success"><?php echo number_format($hd['thanh_tien'], 0, ',', '.'); ?> VNĐ</td>
                                    <td><?php echo date("d/m/Y H:i", strtotime($hd['ngay_thanh_toan'])); ?></td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($hd['hinh_thuc_thanh_toan'] ?? 'Không rõ'); ?></span></td>
                                    <td><a href="<?php echo BASE_URL; ?>/thanhtoan/inPhieu?ma_hoa_don=<?php echo $hd['ma_hoa_don']; ?>" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer-fill"></i></a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalThanhToan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">Xác Nhận Thanh Toán</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Thanh toán cho hóa đơn: <strong id="modal_noi_dung"></strong></p>
                <p>Số tiền: <strong class="text-danger fs-4" id="modal_so_tien"></strong></p>
                <form id="formThanhToan">
                    <input type="hidden" id="modal_ma_hoa_don" name="ma_hoa_don">
                    <div class="list-group">
                        <label class="list-group-item list-group-item-action"><input class="form-check-input me-2" type="radio" name="phuong_thuc" value="VNPAY" checked> <i class="bi bi-qr-code text-primary"></i> VNPAY</label>
                        <label class="list-group-item list-group-item-action"><input class="form-check-input me-2" type="radio" name="phuong_thuc" value="SepayQR"> <i class="bi bi-bank2 text-info"></i> Quét QR Ngân hàng</label>
                        <label class="list-group-item list-group-item-action"><input class="form-check-input me-2" type="radio" name="phuong_thuc" value="TienMat"> <i class="bi bi-cash-coin text-success"></i> Tiền mặt</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success fw-bold" id="btnXacNhanTT" onclick="submitThanhToan()">Tiếp Tục <i class="bi bi-arrow-right"></i></button>
            </div>
            <div id="loaderSepay" class="text-center p-3" style="display:none;"><span class="spinner-border text-success"></span> Đang xử lý...</div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSepayQR" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Quét Mã QR Để Thanh Toán</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="clearInterval(window.pollInterval); window.location.reload();"></button>
            </div>
            <div class="modal-body text-center">
                <div class="qr-box mx-auto mb-3"><img id="qr_image" src="" style="width:100%; max-width:250px;"></div>
                <h4 class="text-danger fw-bold" id="qr_amount"></h4>
                <p>Nội dung: <strong id="qr_ref_code" class="text-success"></strong></p>
                <span id="qr_status" class="badge bg-warning text-dark">Đang chờ thanh toán...</span>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSuccess" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <div class="modal-body">
                <div class="success-checkmark">
                    <div class="check-icon">
                        <span class="icon-line line-tip"></span>
                        <span class="icon-line line-long"></span>
                        <div class="icon-circle"></div>
                        <div class="icon-fix"></div>
                    </div>
                </div>
                <h3 class="fw-bold text-success mt-3">Thanh toán thành công!</h3>
                <p class="text-muted">Giao dịch đã được ghi nhận vào hệ thống.</p>
                
                <div class="d-grid gap-2 mt-4">
                    <button class="btn btn-primary btn-lg" id="btnViewInvoice">
                        <i class="bi bi-printer-fill me-2"></i> XEM & IN BIÊN LAI
                    </button>
                    <button class="btn btn-outline-secondary" onclick="window.location.reload()">
                        Đóng và về danh sách
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const BASE_URL = "<?php echo BASE_URL; ?>";
    const modalSuccess = new bootstrap.Modal(document.getElementById('modalSuccess'));
    const btnViewInvoice = document.getElementById('btnViewInvoice');
    let invoiceIdToPrint = null;

    // Hàm hiển thị Modal thành công
    window.showSuccessModal = (invoiceId) => {
        invoiceIdToPrint = invoiceId;
        // Ẩn các modal khác nếu đang mở
        bootstrap.Modal.getInstance(document.getElementById('modalThanhToan'))?.hide();
        bootstrap.Modal.getInstance(document.getElementById('modalSepayQR'))?.hide();
        
        // Hiện modal thành công
        modalSuccess.show();
    };

    // Sự kiện bấm nút "Xem & In"
    btnViewInvoice.addEventListener('click', () => {
        if (invoiceIdToPrint) {
            window.open(`${BASE_URL}/thanhtoan/inPhieu?ma_hoa_don=${invoiceIdToPrint}`, '_blank');
        }
    });

    // -----------------------------------------------------------
    // 1. XỬ LÝ VNPAY (TỪ SESSION)
    // -----------------------------------------------------------
    <?php if (isset($_SESSION['print_invoice_id'])): ?>
        const vnpayInvoiceId = <?php echo $_SESSION['print_invoice_id']; ?>;
        showSuccessModal(vnpayInvoiceId); // Hiện modal thay vì tự mở tab
        <?php unset($_SESSION['print_invoice_id']); ?>
    <?php endif; ?>

    // -----------------------------------------------------------
    // 2. XỬ LÝ SEPAY (POLLING)
    // -----------------------------------------------------------
    window.pollInterval = null;
    window.startPolling = (maHoaDon) => {
        const statusEl = document.getElementById('qr_status');
        if (window.pollInterval) clearInterval(window.pollInterval);
        
        window.pollInterval = setInterval(() => {
            $.ajax({
                url: `${BASE_URL}/thanhtoan/checkSepayStatus`,
                method: 'POST',
                data: { ma_hoa_don: maHoaDon },
                dataType: 'json',
                success: function(res) {
                    if (res.trang_thai_hoa_don === "DaThanhToan") {
                        clearInterval(window.pollInterval);
                        showSuccessModal(maHoaDon); // Hiện modal thành công
                    } else if (res.trang_thai_hoa_don === "order_not_found") {
                        clearInterval(window.pollInterval);
                        statusEl.className = 'badge bg-danger';
                        statusEl.textContent = 'Lỗi hóa đơn';
                    }
                }
            });
        }, 2000);
    }

    // -----------------------------------------------------------
    // 3. CÁC HÀM CŨ (MODAL THANH TOÁN, LỌC...)
    // -----------------------------------------------------------
    const modalThanhToan = new bootstrap.Modal('#modalThanhToan');
    const modalSepayQR = new bootstrap.Modal('#modalSepayQR');
    
    // Lọc
    document.querySelectorAll('input[name="filter_status"]').forEach(r => {
        r.addEventListener('change', e => {
            const v = e.target.value;
            document.querySelectorAll('.invoice-item').forEach(row => {
                if (v === 'all') row.style.display = '';
                else if (v === 'valid') row.style.display = row.classList.contains('row-valid') ? '' : 'none';
                else if (v === 'expired') row.style.display = row.classList.contains('row-expired') ? '' : 'none';
            });
        });
    });

    window.openThanhToanModal = (id, tien, nd) => {
        document.getElementById('modal_ma_hoa_don').value = id;
        document.getElementById('modal_so_tien').textContent = new Intl.NumberFormat('vi-VN').format(tien) + ' VNĐ';
        document.getElementById('modal_noi_dung').textContent = nd;
        if(window.pollInterval) clearInterval(window.pollInterval);
        modalThanhToan.show();
    };

    window.submitThanhToan = async () => {
        const form = document.getElementById('formThanhToan');
        const formData = new FormData(form);
        const type = document.querySelector('input[name="phuong_thuc"]:checked').value;
        const btn = document.getElementById('btnXacNhanTT');
        
        btn.disabled = true; btn.innerHTML = 'Đang xử lý...';
        document.getElementById('loaderSepay').style.display = (type === 'SepayQR' ? 'block' : 'none');

        let url = '';
        if (type === 'VNPAY') url = `${BASE_URL}/thanhtoan/taoYeuCau`;
        else if (type === 'SepayQR') url = `${BASE_URL}/thanhtoan/taoYeuCauSepay`;
        else url = `${BASE_URL}/thanhtoan/taoYeuCauTienMat`;

        try {
            const res = await fetch(url, { method: 'POST', body: formData });
            const d = await res.json();
            if (res.ok && d.success) {
                modalThanhToan.hide();
                if (type === 'TienMat') {
                    if (d.print_url) window.open(d.print_url, '_blank');
                    window.location.reload();
                } else if (type === 'VNPAY') {
                    window.location.href = d.redirect_url;
                } else if (type === 'SepayQR') {
                    const dt = d.payment_details;
                    document.getElementById('qr_image').src = dt.qr_img_url;
                    document.getElementById('qr_amount').textContent = new Intl.NumberFormat('vi-VN').format(dt.so_tien) + ' VNĐ';
                    document.getElementById('qr_ref_code').textContent = dt.ref_code;
                    modalSepayQR.show();
                    startPolling(dt.ma_hoa_don);
                }
            } else { alert(d.message); }
        } catch (e) { alert('Lỗi kết nối'); } 
        finally { btn.disabled = false; btn.innerHTML = 'Tiếp Tục <i class="bi bi-arrow-right"></i>'; document.getElementById('loaderSepay').style.display = 'none'; }
    };
});
</script>
</body>
</html>