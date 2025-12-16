<?php
// File: app/Views/ThiSinh/quan_ly_ho_so.php
// Trang quản lý hồ sơ nhập học của thí sinh
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Quản Lý Hồ Sơ - Thí Sinh | THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background: #faf7ff; font-family: 'Roboto', sans-serif; }
        .card { border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
        .status-badge { font-size: 1rem; }
    </style>
</head>
<body>
<div class="container py-4">
    <h2 class="fw-bold mb-4" style="color:#8B5CF6"><i class="bi bi-person-vcard me-2"></i>Quản Lý Hồ Sơ Nhập Học</h2>
    <!-- Thông tin thí sinh -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Thông Tin Thí Sinh</h5>
            <?php if (!empty($data['info']) && is_array($data['info'])): ?>
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item"><strong>Họ tên:</strong> <?php echo isset($data['info']['ho_ten']) ? htmlspecialchars($data['info']['ho_ten']) : '<span class="text-danger">Không có dữ liệu</span>'; ?></li>
                    <li class="list-group-item"><strong>Số báo danh:</strong> <?php echo isset($data['info']['so_bao_danh']) ? htmlspecialchars($data['info']['so_bao_danh']) : '<span class="text-danger">Không có dữ liệu</span>'; ?></li>
                    <li class="list-group-item"><strong>Trường THCS:</strong> <?php echo isset($data['info']['truong_thcs']) ? htmlspecialchars($data['info']['truong_thcs']) : '<span class="text-danger">Không có dữ liệu</span>'; ?></li>
                </ul>
            <?php else: ?>
                <div class="alert alert-warning mb-0">Không tìm thấy thông tin thí sinh.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Trạng thái hồ sơ nhập học -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Trạng Thái Hồ Sơ</h5>
            <?php if (!empty($data['nhap_hoc_info'])): ?>
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Trạng thái xác nhận</span>
                        <?php
                            $trang_thai = $data['nhap_hoc_info']['trang_thai_xac_nhan'] ?? '';
                            if ($trang_thai === 'Xac_nhan_nhap_hoc') {
                                echo '<span class="badge bg-success status-badge">Đã xác nhận nhập học</span>';
                            } elseif ($trang_thai === 'Tu_choi' || $trang_thai === 'Tu_choi_nhap_hoc') {
                                echo '<span class="badge bg-danger status-badge">Đã từ chối nhập học</span>';
                            } else {
                                echo '<span class="badge bg-secondary status-badge">Chưa xác nhận</span>';
                            }
                        ?>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Trường đã đăng ký</span>
                        <span class="fw-bold text-primary"><?php echo $data['nhap_hoc_info']['ten_truong'] ?? '--'; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Lớp (nếu có)</span>
                        <span class="fw-bold">
                            <?php echo !empty($data['nhap_hoc_info']['ten_lop']) ? $data['nhap_hoc_info']['ten_lop'] : 'Chưa xếp lớp'; ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Tổ hợp môn</span>
                        <span>
                            <?php echo !empty($data['nhap_hoc_info']['ten_to_hop']) ? $data['nhap_hoc_info']['ten_to_hop'] : 'Chưa chọn tổ hợp'; ?>
                        </span>
                    </li>
                </ul>
                <div class="alert alert-info mb-0">
                    Ngày xác nhận: <strong>
                        <?php
                            echo !empty($data['nhap_hoc_info']['ngay_nhap_hoc'])
                                ? date('d/m/Y', strtotime($data['nhap_hoc_info']['ngay_nhap_hoc']))
                                : '--';
                        ?>
                    </strong>
                </div>
                <?php if ($trang_thai === 'Tu_choi' || $trang_thai === 'Tu_choi_nhap_hoc'): ?>
                <div class="alert alert-warning mt-2">
                    Bạn đã từ chối nhập học tại trường này. Nếu muốn nhập học lại, vui lòng liên hệ nhà trường.
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-warning mb-0">
                    Bạn chưa xác nhận nhập học hoặc chưa đăng ký trường nào.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Danh sách nguyện vọng đã đăng ký -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Danh Sách Nguyện Vọng Đã Đăng Ký</h5>
            <?php if (!empty($data['nguyen_vong']['dau']) || !empty($data['nguyen_vong']['truot'])): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Thứ tự NV</th>
                                <th>Trường</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Hiển thị nguyện vọng ĐẬU
                            foreach (($data['nguyen_vong']['dau'] ?? []) as $nv) {
                                echo '<tr>';
                                echo '<td>NV '.($nv['thu_tu'] ?? '--').'</td>';
                                echo '<td>'.htmlspecialchars($nv['ten_truong'] ?? '--').'</td>';
                                echo '<td><span class="badge bg-success">Đậu</span></td>';
                                echo '</tr>';
                            }
                            // Hiển thị nguyện vọng TRƯỢT
                            foreach (($data['nguyen_vong']['truot'] ?? []) as $nv) {
                                echo '<tr>';
                                echo '<td>NV '.($nv['thu_tu'] ?? '--').'</td>';
                                echo '<td>'.htmlspecialchars($nv['ten_truong'] ?? '--').'</td>';
                                echo '<td><span class="badge bg-danger">Trượt</span></td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-0">Chưa có dữ liệu nguyện vọng.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Điểm thi -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Điểm Thi</h5>
            <?php if (!empty($data['diem'])): ?>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Toán: <strong><?php echo $data['diem']['diem_toan'] ?? '--'; ?></strong></li>
                    <li class="list-group-item">Ngữ Văn: <strong><?php echo $data['diem']['diem_van'] ?? '--'; ?></strong></li>
                    <li class="list-group-item">Tiếng Anh: <strong><?php echo $data['diem']['diem_anh'] ?? '--'; ?></strong></li>
                </ul>
            <?php else: ?>
                <div class="alert alert-warning mb-0">Chưa có dữ liệu điểm thi.</div>
            <?php endif; ?>
        </div>
    </div>
    <a href="<?php echo BASE_URL; ?>/ThiSinh/dashboard" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Quay lại Dashboard</a>
</div>
</body>
</html>
