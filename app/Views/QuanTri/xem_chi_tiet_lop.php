<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Lớp Học | THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background: #f5f7fa; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .card-header-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0; padding: 20px; font-weight: 600; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #667eea; }
        .info-label { font-weight: 600; color: #667eea; font-size: 0.85rem; text-transform: uppercase; }
        .info-value { font-size: 1.1rem; color: #2c3e50; margin-top: 5px; }
        .table-custom thead { background: #f8f9fa; }
        .table-custom th { font-weight: 600; color: #2c3e50; border-bottom: 2px solid #ddd; }
        .badge-custom { padding: 8px 12px; border-radius: 6px; font-weight: 500; }
        .btn-action { border-radius: 8px; font-weight: 500; }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <!-- Header -->
        <header class="mb-4 p-4 bg-white rounded-3 shadow-sm d-flex justify-content-between align-items-center">
            <div>
                <h2 class="m-0">
                    <i class="bi bi-info-circle"></i> Chi Tiết Lớp Học
                </h2>
                <small class="text-muted">Xem thông tin chi tiết lớp và danh sách giáo viên giảng dạy</small>
            </div>
            <div class="d-flex gap-2">
                <a href="<?php echo BASE_URL; ?>/LopHoc/edit?id=<?php echo $data['lop']['ma_lop']; ?>" class="btn btn-warning btn-action">
                    <i class="bi bi-pencil-square"></i> Sửa Lớp
                </a>
                <a href="<?php echo BASE_URL; ?>/LopHoc" class="btn btn-outline-secondary btn-action">
                    <i class="bi bi-arrow-left"></i> Quay Lại
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <div class="row g-4">
            <!-- THÔNG TIN CHUNG -->
            <div class="col-lg-8">
                <div class="card card-custom">
                    <div class="card-header-custom">
                        <i class="bi bi-house-door-fill"></i> Thông Tin Chung
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <div class="info-label">Tên Lớp</div>
                                    <div class="info-value">
                                        <span class="badge bg-primary badge-custom"><?php echo htmlspecialchars($data['lop']['ten_lop']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <div class="info-label">Khối Lớp</div>
                                    <div class="info-value">
                                        <span class="badge bg-secondary badge-custom">Khối <?php echo $data['lop']['khoi']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <div class="info-label">Tổ Hợp Môn</div>
                                    <div class="info-value">
                                        <?php echo htmlspecialchars($data['lop']['ten_to_hop'] ?? 'N/A'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <div class="info-label">Phòng Học Chính</div>
                                    <div class="info-value">
                                        <?php echo htmlspecialchars($data['lop']['ten_phong'] ?? 'Chưa xếp'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <div class="info-label">Giáo Viên Chủ Nhiệm</div>
                                    <div class="info-value">
                                        <?php echo htmlspecialchars($data['lop']['ten_gvcn'] ?? 'Chưa chỉ định'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <div class="info-label">Trạng Thái</div>
                                    <div class="info-value">
                                        <?php if ($data['lop']['trang_thai_lop'] === 'HoatDong'): ?>
                                            <span class="badge bg-success badge-custom">Hoạt Động</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary badge-custom">Tạm Nghi</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DANH SÁCH PHÂN CÔNG GIÁO VIÊN -->
                <div class="card card-custom mt-4">
                    <div class="card-header-custom">
                        <i class="bi bi-person-lines-fill"></i> Danh Sách Phân Công Giáo Viên
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($data['phan_cong'])): ?>
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">Chưa có phân công giáo viên</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-custom table-hover mb-0">
                                    <thead>
                                        <tr class="text-center">
                                            <th style="width: 8%;">#</th>
                                            <th style="width: 35%;">Môn Học</th>
                                            <th style="width: 30%;">Giáo Viên</th>
                                            <th style="width: 15%;">Số Tiết/Tuần</th>
                                            <th style="width: 12%;">Loại</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['phan_cong'] as $index => $pc): ?>
                                            <tr>
                                                <td class="text-center fw-bold text-secondary"><?php echo $index + 1; ?></td>
                                                <td>
                                                    <span class="fw-bold text-primary"><?php echo htmlspecialchars($pc['ten_mon_hoc']); ?></span>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($pc['ten_giao_vien']); ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info badge-custom"><?php echo $pc['so_tiet_tuan']; ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($pc['loai_mon'] === 'Bắt buộc'): ?>
                                                        <span class="badge bg-success badge-custom">Bắt buộc</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info badge-custom">Tự chọn</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- SIDEBAR: THỐNG KÊ NHANH -->
            <div class="col-lg-4">
                <!-- Card: Tổng Số Môn -->
                <div class="card card-custom mb-3">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-book-fill fs-1 text-primary mb-3" style="display: block;"></i>
                        <h5 class="text-muted mb-1">Tổng Số Môn Học</h5>
                        <h2 class="text-primary fw-bold"><?php echo count($data['phan_cong']); ?></h2>
                    </div>
                </div>

                <!-- Card: Tổng Số Tiết/Tuần -->
                <div class="card card-custom mb-3">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-clock-fill fs-1 text-success mb-3" style="display: block;"></i>
                        <h5 class="text-muted mb-1">Tổng Tiết/Tuần</h5>
                        <h2 class="text-success fw-bold">
                            <?php 
                            $tong_tiet = 0;
                            foreach ($data['phan_cong'] as $pc) {
                                $tong_tiet += $pc['so_tiet_tuan'] ?? 0;
                            }
                            echo $tong_tiet;
                            ?>
                        </h2>
                    </div>
                </div>

                <!-- Card: Số Giáo Viên -->
                <div class="card card-custom mb-3">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-people-fill fs-1 text-warning mb-3" style="display: block;"></i>
                        <h5 class="text-muted mb-1">Số Giáo Viên</h5>
                        <h2 class="text-warning fw-bold">
                            <?php 
                            $list_gv = [];
                            foreach ($data['phan_cong'] as $pc) {
                                if (!in_array($pc['ma_giao_vien'], $list_gv)) {
                                    $list_gv[] = $pc['ma_giao_vien'];
                                }
                            }
                            echo count($list_gv);
                            ?>
                        </h2>
                    </div>
                </div>

                <!-- Card: Môn Bắt Buộc -->
                <div class="card card-custom">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-check-circle-fill fs-1 text-danger mb-3" style="display: block;"></i>
                        <h5 class="text-muted mb-1">Môn Bắt Buộc</h5>
                        <h2 class="text-danger fw-bold">
                            <?php 
                            $mon_bat_buoc = 0;
                            foreach ($data['phan_cong'] as $pc) {
                                if ($pc['loai_mon'] === 'Bắt buộc') {
                                    $mon_bat_buoc++;
                                }
                            }
                            echo $mon_bat_buoc;
                            ?>
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex gap-2 justify-content-center">
                    <a href="<?php echo BASE_URL; ?>/LopHoc/edit?id=<?php echo $data['lop']['ma_lop']; ?>" class="btn btn-warning btn-lg btn-action">
                        <i class="bi bi-pencil-square"></i> Sửa Lớp
                    </a>
                    <a href="<?php echo BASE_URL; ?>/LopHoc" class="btn btn-secondary btn-lg btn-action">
                        <i class="bi bi-arrow-left"></i> Quay Lại Danh Sách
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
