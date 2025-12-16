<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán Học Phí</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --card-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
            --border-radius: 16px;
        }

        body {
            background-color: #f3f4f6; /* Màu nền xám nhẹ dịu mắt */
            font-family: 'Inter', sans-serif;
            color: #4b5563;
        }

        /* Header Style */
        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 3rem 2rem;
            border-radius: 0 0 30px 30px;
            margin-bottom: -3rem; /* Để card trồi lên */
            box-shadow: 0 4px 20px rgba(118, 75, 162, 0.3);
        }

        /* Card Style - Hiện đại hóa */
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            background: var(--glass-bg);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
        }
        
        /* Filter Tabs Custom */
        .filter-group {
            background: #f1f5f9;
            padding: 5px;
            border-radius: 12px;
            display: inline-flex;
        }
        .btn-check:checked + .btn-custom-filter {
            background-color: #fff;
            color: #764ba2;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .btn-custom-filter {
            border: none;
            border-radius: 8px;
            padding: 6px 16px;
            font-weight: 600;
            color: #64748b;
            transition: all 0.2s;
        }
        .btn-custom-filter:hover {
            color: #764ba2;
        }

        /* Table Styling */
        .table thead th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
            padding: 1rem;
        }
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .invoice-item:hover {
            background-color: #f8fafc;
        }
        
        /* Status Badge */
        .status-badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .status-valid { background-color: #dcfce7; color: #166534; }
        .status-expired { background-color: #fee2e2; color: #991b1b; }
        .status-pending { background-color: #fef9c3; color: #854d0e; }
        .status-paid { background-color: #e0f2fe; color: #075985; }

        /* Button Gradients */
        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: white;
            font-weight: 600;
            padding: 8px 20px;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .btn-gradient:hover {
            opacity: 0.9;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(118, 75, 162, 0.3);
        }

        /* QR Box */
        .qr-box {
            border: 2px dashed #cbd5e1;
            padding: 20px;
            border-radius: 16px;
            background-color: #f8fafc;
        }

        /* Success Animation (Giữ nguyên của bác vì nó đẹp rồi) */
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

<div class="page-header text-center">
    <h1 class="fw-bold mb-2"><i class="bi bi-mortarboard-fill me-2"></i> Cổng Thanh Toán Học Phí</h1>
    <p class="opacity-75 mb-4">Xin chào, <strong><?php echo htmlspecialchars($data['user_name'] ?? 'Quý Phụ Huynh'); ?></strong></p>
    <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-light btn-sm rounded-pill px-4 shadow-sm text-primary fw-bold">
        <i class="bi bi-arrow-left me-1"></i> Quay lại Dashboard
    </a>
</div>

<div class="container pb-5" style="margin-top: 2rem;">
    
    <?php if (isset($data['flash_message'])): ?>
        <div class="alert alert-<?php echo $data['flash_message']['type']; ?> alert-dismissible fade show shadow-sm border-0 rounded-4 mb-4" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i> <?php echo htmlspecialchars($data['flash_message']['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-5">
        <div class="card-header bg-white border-0 pt-4 px-4 d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-1"><i class="bi bi-receipt-cutoff text-primary me-2"></i>Hóa đơn cần thanh toán</h5>
                <small class="text-muted">Vui lòng thanh toán trước thời hạn để tránh bị khóa.</small>
            </div>
            
            <div class="filter-group mt-2 mt-md-0">
                <input type="radio" class="btn-check" name="filter_status" id="filter_all" value="all" checked>
                <label class="btn btn-custom-filter" for="filter_all">Tất cả</label>

                <input type="radio" class="btn-check" name="filter_status" id="filter_valid" value="valid">
                <label class="btn btn-custom-filter" for="filter_valid">Còn hạn</label>

                <input type="radio" class="btn-check" name="filter_status" id="filter_expired" value="expired">
                <label class="btn btn-custom-filter" for="filter_expired">Quá hạn</label>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Mã HĐ</th>
                            <th>Ngày Lập</th>
                            <th>Nội Dung Thu</th>
                            <th>Số Tiền</th>
                            <th>Hạn Đóng</th>
                            <th class="text-center pe-4">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody id="invoice_list_body">
                        <?php if (empty($data['hoa_don_chua_tt'])): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-check-circle display-4 text-success opacity-50"></i>
                                        <p class="mt-3 fw-medium">Tuyệt vời! Không có hóa đơn nào cần thanh toán.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['hoa_don_chua_tt'] as $hd): ?>
                                <tr class="invoice-item <?php echo !empty($hd['qua_han']) ? 'row-expired' : 'row-valid'; ?>">
                                    <td class="ps-4 fw-bold text-primary">#<?php echo htmlspecialchars($hd['ma_hoa_don']); ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($hd['ngay_lap_hoa_don'])); ?></td>
                                    <td><?php echo htmlspecialchars($hd['ghi_chu'] ?? 'Học phí'); ?></td>
                                    <td class="fw-bold text-danger fs-6"><?php echo number_format($hd['thanh_tien'], 0, ',', '.'); ?> ₫</td>
                                    <td>
                                        <?php if (!empty($hd['qua_han'])): ?>
                                            <span class="status-badge status-expired"><i class="bi bi-exclamation-triangle-fill"></i> Quá hạn</span>
                                            <div class="small text-muted mt-1"><?php echo date("d/m/Y", strtotime($hd['ngay_het_han'])); ?></div>
                                        <?php else: ?>
                                            <span class="status-badge status-valid"><i class="bi bi-clock"></i> Còn hạn</span>
                                            <div class="small text-muted mt-1"><?php echo date("d/m/Y", strtotime($hd['ngay_het_han'])); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center pe-4">
                                        <?php if (!empty($hd['qua_han'])): ?>
                                            <button class="btn btn-secondary btn-sm rounded-pill px-3" disabled>
                                                <i class="bi bi-lock-fill"></i> Đã khóa
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-gradient btn-sm" onclick="openThanhToanModal(<?php echo (int)$hd['ma_hoa_don']; ?>, <?php echo (float)$hd['thanh_tien']; ?>, '<?php echo addslashes(htmlspecialchars($hd['ghi_chu'] ?? 'Học phí')); ?>')">
                                                Thanh Toán <i class="bi bi-chevron-right ms-1"></i>
                                            </button>
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
    <div class="card mb-5 border-warning border-start border-4">
        <div class="card-header bg-white border-0 pt-3">
            <h6 class="fw-bold text-warning"><i class="bi bi-hourglass-split me-2"></i>Đang chờ xác nhận tại trường</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">Mã HĐ</th><th>Nội Dung</th><th>Số Tiền</th><th>Trạng Thái</th><th class="text-center pe-4">Phiếu</th></tr></thead>
                    <tbody>
                        <?php foreach ($data['hoa_don_cho_xac_nhan'] as $hd): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?php echo htmlspecialchars($hd['ma_hoa_don']); ?></td>
                                <td><?php echo htmlspecialchars($hd['ghi_chu'] ?? 'Học phí'); ?></td>
                                <td class="fw-bold"><?php echo number_format($hd['thanh_tien'], 0, ',', '.'); ?> ₫</td>
                                <td><span class="status-badge status-pending">Chờ nộp tiền mặt</span></td>
                                <td class="text-center pe-4"><a href="<?php echo BASE_URL; ?>/thanhtoan/inPhieu?ma_hoa_don=<?php echo $hd['ma_hoa_don']; ?>" target="_blank" class="btn btn-outline-dark btn-sm rounded-circle"><i class="bi bi-printer"></i></a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history text-success me-2"></i>Lịch sử giao dịch</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th class="ps-4">Mã HĐ</th><th>Nội Dung</th><th>Số Tiền</th><th>Thời Gian</th><th>Kênh TT</th><th class="text-center pe-4">Biên Lai</th></tr></thead>
                    <tbody>
                        <?php if (empty($data['hoa_don_da_tt'])): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">Chưa có lịch sử giao dịch nào.</td></tr>
                        <?php else: ?>
                            <?php foreach ($data['hoa_don_da_tt'] as $hd): ?>
                                <tr>
                                    <td class="ps-4 text-muted">#<?php echo htmlspecialchars($hd['ma_hoa_don']); ?></td>
                                    <td><?php echo htmlspecialchars($hd['ghi_chu'] ?? 'Học phí'); ?></td>
                                    <td class="fw-bold text-success"><?php echo number_format($hd['thanh_tien'], 0, ',', '.'); ?> ₫</td>
                                    <td><?php echo date("H:i - d/m/Y", strtotime($hd['ngay_thanh_toan'])); ?></td>
                                    <td><span class="status-badge status-paid"><?php echo htmlspecialchars($hd['hinh_thuc_thanh_toan'] ?? 'Không rõ'); ?></span></td>
                                    <td class="text-center pe-4"><a href="<?php echo BASE_URL; ?>/thanhtoan/inPhieu?ma_hoa_don=<?php echo $hd['ma_hoa_don']; ?>" target="_blank" class="btn btn-light btn-sm text-secondary rounded-circle shadow-sm"><i class="bi bi-printer-fill"></i></a></td>
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
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-white border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Chọn hình thức thanh toán</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <p class="text-muted mb-1">Thanh toán cho: <strong id="modal_noi_dung" class="text-dark"></strong></p>
                    <h3 class="fw-bold text-primary" id="modal_so_tien"></h3>
                </div>
                
                <form id="formThanhToan">
                    <input type="hidden" id="modal_ma_hoa_don" name="ma_hoa_don">
                    <div class="d-grid gap-3">
                        <label class="btn btn-outline-light text-start p-3 border rounded-3 d-flex align-items-center shadow-sm position-relative">
                            <input class="form-check-input position-absolute top-50 end-0 me-3 translate-middle-y" type="radio" name="phuong_thuc" value="VNPAY" checked>
                            <div class="bg-white p-2 rounded shadow-sm me-3"><img src="https://vinadesign.vn/uploads/images/2023/05/vnpay-logo-vinadesign-25-12-57-55.png" height="30" alt="VNPAY"></div>
                            <div>
                                <div class="fw-bold text-dark">Ví VNPAY / Ngân hàng</div>
                                <div class="small text-muted">Thẻ ATM, Visa, Mobile Banking</div>
                            </div>
                        </label>

                        <label class="btn btn-outline-light text-start p-3 border rounded-3 d-flex align-items-center shadow-sm position-relative">
                            <input class="form-check-input position-absolute top-50 end-0 me-3 translate-middle-y" type="radio" name="phuong_thuc" value="SepayQR">
                            <div class="bg-white p-2 rounded shadow-sm me-3"><i class="bi bi-qr-code-scan fs-4 text-primary"></i></div>
                            <div>
                                <div class="fw-bold text-dark">Chuyển khoản QR</div>
                                <div class="small text-muted">Tự động xác nhận 24/7</div>
                            </div>
                        </label>

                        <label class="btn btn-outline-light text-start p-3 border rounded-3 d-flex align-items-center shadow-sm position-relative">
                            <input class="form-check-input position-absolute top-50 end-0 me-3 translate-middle-y" type="radio" name="phuong_thuc" value="TienMat">
                            <div class="bg-white p-2 rounded shadow-sm me-3"><i class="bi bi-cash-stack fs-4 text-success"></i></div>
                            <div>
                                <div class="fw-bold text-dark">Tiền mặt</div>
                                <div class="small text-muted">Đóng trực tiếp tại phòng tài vụ</div>
                            </div>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-gradient rounded-pill px-5 shadow" id="btnXacNhanTT" onclick="submitThanhToan()">Tiếp Tục <i class="bi bi-arrow-right"></i></button>
            </div>
            <div id="loaderSepay" class="text-center p-3" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2 text-muted small">Đang khởi tạo giao dịch...</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSepayQR" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-scan me-2"></i>Quét mã QR để thanh toán</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="clearInterval(window.pollInterval); window.location.reload();"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="qr-box mx-auto mb-3 bg-white p-3 rounded-3 shadow-sm" style="max-width: 280px;">
                    <img id="qr_image" src="" style="width:100%;">
                </div>
                <h3 class="text-danger fw-bold mb-1" id="qr_amount"></h3>
                <p class="text-muted">Nội dung CK: <strong id="qr_ref_code" class="text-primary bg-light px-2 py-1 rounded"></strong></p>
                
                <div class="mt-4">
                    <span id="qr_status" class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                        <span class="spinner-grow spinner-grow-sm me-1" role="status" aria-hidden="true"></span>
                        Đang chờ thanh toán...
                    </span>
                    <p class="small text-muted mt-2 mb-0">Hệ thống sẽ tự động xác nhận khi tiền về.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSuccess" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 text-center p-4">
            <div class="modal-body">
                <div class="success-checkmark">
                    <div class="check-icon">
                        <span class="icon-line line-tip"></span>
                        <span class="icon-line line-long"></span>
                        <div class="icon-circle"></div>
                        <div class="icon-fix"></div>
                    </div>
                </div>
                <h3 class="fw-bold text-success mt-4">Thanh toán thành công!</h3>
                <p class="text-muted mb-4">Giao dịch đã được ghi nhận vào hệ thống nhà trường.</p>
                
                <div class="d-grid gap-2 col-10 mx-auto">
                    <button class="btn btn-primary btn-lg rounded-pill shadow" id="btnViewInvoice">
                        <i class="bi bi-file-earmark-pdf me-2"></i> XEM BIÊN LAI
                    </button>
                    <button class="btn btn-outline-secondary rounded-pill" onclick="window.location.reload()">
                        Về danh sách
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