<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Quản Lý Hồ Sơ Nhập Học | THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background: #f4f7fe; font-family: 'Inter', system-ui, -apple-system, sans-serif; color: #1e293b; }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
        .card-title { font-weight: 700; color: #475569; display: flex; align-items: center; gap: 10px; }
        .status-badge { padding: 8px 16px; border-radius: 50px; font-weight: 600; }
        .list-group-item { padding: 12px 20px; border-color: #f1f5f9; }
        .table thead th { background-color: #f8fafc; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
        .bg-gradient-primary { background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%); }
    </style>
</head>
<body>
<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0" style="color:#6D28D9">
            <i class="bi bi-person-vcard-fill me-2"></i>Hồ Sơ Nhập Học Trực Tuyến
        </h2>
        <a href="<?php echo BASE_URL; ?>/ThiSinh/dashboard" class="btn btn-outline-primary rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i>Quay lại Dashboard
        </a>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4"><i class="bi bi-info-circle text-primary"></i>Thông tin thí sinh</h5>
                    <div class="text-center mb-4">
                        <div class="bg-light d-inline-block p-3 rounded-circle mb-2">
                            <i class="bi bi-person-fill display-4 text-secondary"></i>
                        </div>
                        <h4 class="fw-bold mb-0"><?php echo htmlspecialchars($data['info']['ho_ten'] ?? 'Chưa cập nhật'); ?></h4>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3">SBD: <?php echo htmlspecialchars($data['info']['so_bao_danh'] ?? '---'); ?></span>
                    </div>
                    <ul class="list-group list-group-flush border-top">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Trường THCS:</span>
                            <span class="fw-medium"><?php echo htmlspecialchars($data['info']['truong_thcs'] ?? '--'); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Lớp cũ:</span>
                            <span class="fw-medium"><?php echo htmlspecialchars($data['info']['lop_hoc'] ?? '--'); ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bi bi-award text-warning"></i>Điểm thi tuyển sinh</h5>
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="p-2 bg-light rounded shadow-sm">
                                <small class="text-muted d-block">Toán</small>
                                <span class="fw-bold fs-5 text-primary"><?php echo $data['diem']['diem_toan'] ?? '0'; ?></span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-light rounded shadow-sm">
                                <small class="text-muted d-block">Văn</small>
                                <span class="fw-bold fs-5 text-primary"><?php echo $data['diem']['diem_van'] ?? '0'; ?></span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-light rounded shadow-sm">
                                <small class="text-muted d-block">Anh</small>
                                <span class="fw-bold fs-5 text-primary"><?php echo $data['diem']['diem_anh'] ?? '0'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            
            <div class="card border-start border-4 border-primary">
                <div class="card-body">
                    <h5 class="card-title mb-4"><i class="bi bi-shield-check text-success"></i>Trạng thái hồ sơ trúng tuyển</h5>
                    
                    <?php if (!empty($data['nhap_hoc_info'])): ?>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="text-muted small d-block">Trường trúng tuyển</label>
                                <h5 class="fw-bold text-primary"><?php echo htmlspecialchars($data['nhap_hoc_info']['ten_truong'] ?? '---'); ?></h5>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small d-block">Trạng thái hiện tại</label>
                                <?php
                                    $trang_thai = $data['nhap_hoc_info']['trang_thai_xac_nhan'] ?? '';
                                    if ($trang_thai === 'Xac_nhan_nhap_hoc') {
                                        echo '<span class="badge bg-success status-badge"><i class="bi bi-check-all"></i> Đã xác nhận nhập học</span>';
                                    } elseif ($trang_thai === 'Tu_choi' || $trang_thai === 'Tu_choi_nhap_hoc') {
                                        echo '<span class="badge bg-danger status-badge"><i class="bi bi-x-circle"></i> Đã từ chối nhập học</span>';
                                    } else {
                                        echo '<span class="badge bg-warning text-dark status-badge"><i class="bi bi-hourglass-split"></i> Đang chờ xác nhận</span>';
                                    }
                                ?>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded-3 mb-3">
                            <div class="row text-center">
                                <div class="col-md-6 border-end">
                                    <small class="text-muted d-block">Xếp lớp dự kiến</small>
                                    <span class="fw-bold fs-5"><?php echo !empty($data['nhap_hoc_info']['ten_lop']) ? $data['nhap_hoc_info']['ten_lop'] : 'Chưa xếp lớp'; ?></span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Tổ hợp đăng ký</small>
                                    <span class="fw-bold fs-5 text-truncate d-block px-2"><?php echo !empty($data['nhap_hoc_info']['ten_to_hop']) ? $data['nhap_hoc_info']['ten_to_hop'] : 'Chưa chọn'; ?></span>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($data['nhap_hoc_info']['ngay_nhap_hoc'])): ?>
                        <p class="mb-0 small text-muted text-end">
                            <i class="bi bi-info-circle"></i> Ghi nhận nhập học lúc: <?php echo date('H:i - d/m/Y', strtotime($data['nhap_hoc_info']['ngay_nhap_hoc'])); ?>
                        </p>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="alert alert-light border text-center py-4">
                            <i class="bi bi-emoji-frown fs-2 d-block mb-2 text-muted"></i>
                            Bác chưa có thông tin trúng tuyển hoặc dữ liệu đang được đồng bộ.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4"><i class="bi bi-list-check text-info"></i>Chi tiết kết quả xét tuyển</h5>
                    
                    <?php if (!empty($data['nguyen_vong']['dau']) || !empty($data['nguyen_vong']['truot'])): ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Thứ tự</th>
                                        <th>Tên Trường Đăng Ký</th>
                                        <th class="text-center">Kết Quả</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // 1. In nguyện vọng ĐẬU
                                    foreach (($data['nguyen_vong']['dau'] ?? []) as $nv) {
                                        echo '<tr class="table-success">';
                                        echo '<td class="fw-bold">NV '.($nv['thu_tu'] ?? '1').'</td>';
                                        echo '<td class="fw-bold text-success">'.htmlspecialchars($nv['ten_truong'] ?? '--').'</td>';
                                        echo '<td class="text-center"><span class="badge bg-success rounded-pill px-3">TRÚNG TUYỂN</span></td>';
                                        echo '</tr>';
                                    }
                                    // 2. In các nguyện vọng TRƯỢT
                                    foreach (($data['nguyen_vong']['truot'] ?? []) as $nv) {
                                        echo '<tr>';
                                        echo '<td>NV '.($nv['thu_tu'] ?? '--').'</td>';
                                        echo '<td class="text-secondary">'.htmlspecialchars($nv['ten_truong'] ?? '--').'</td>';
                                        echo '<td class="text-center"><span class="badge bg-light text-secondary border rounded-pill px-3">Không trúng tuyển</span></td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted py-3">Chưa có dữ liệu nguyện vọng đăng ký.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>