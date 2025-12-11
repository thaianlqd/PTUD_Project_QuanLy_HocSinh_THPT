<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý trường học | THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background: #f5f7fb; }
        .page-wrap { max-width: 1200px; margin: 0 auto; padding: 24px 16px 48px; }
        .card { border: none; box-shadow: 0 6px 20px rgba(0,0,0,0.05); }
        .table thead th { background: #0d6efd; color: #fff; border: none; }
        .badge-pill { border-radius: 999px; }
        .stat-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; background: #eef3ff; color: #0d2d62; font-weight: 600; }
    </style>
</head>
<body>
<div class="page-wrap">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Quản lý trường học</h2>
            <p class="text-muted mb-0">Xem nhanh số lớp, sĩ số, giáo viên, phụ huynh theo từng trường.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="<?php echo BASE_URL; ?>/dashboard/index"><i class="bi bi-arrow-left"></i> Về dashboard</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="ma_truong" class="form-label mb-1">Chọn trường</label>
                    <select name="ma_truong" id="ma_truong" class="form-select">
                        <option value="">Tất cả trường</option>
                        <?php foreach (($school_options ?? []) as $opt): ?>
                            <option value="<?php echo htmlspecialchars($opt['ma_truong']); ?>" <?php echo (($selected_school ?? null) == $opt['ma_truong']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($opt['ten_truong']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Lọc</button>
                </div>
                <?php if (!empty($selected_school)): ?>
                    <div class="col-md-3 text-md-end">
                        <a class="btn btn-light" href="<?php echo BASE_URL; ?>/TruongHoc"><i class="bi bi-x-circle"></i> Bỏ lọc</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($schools)): ?>
                <p class="text-muted mb-0">Không có dữ liệu hiển thị.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Trường</th>
                                <th>Địa chỉ</th>
                                <th class="text-center">Lớp</th>
                                <th class="text-center">Sĩ số khai báo</th>
                                <th class="text-center">HS đang học</th>
                                <th class="text-center">Giáo viên</th>
                                <th class="text-center">Admin</th>
                                <th class="text-center">Phụ huynh</th>
                                <th class="text-center">Phân công</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schools as $row): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($row['ten_truong']); ?></div>
                                        <div class="text-muted small">Mã: <?php echo htmlspecialchars($row['ma_truong']); ?></div>
                                    </td>
                                    <td class="small"><?php echo htmlspecialchars($row['dia_chi'] ?? ''); ?><br><span class="text-muted">SĐT: <?php echo htmlspecialchars($row['so_dien_thoai'] ?? ''); ?></span></td>
                                    <td class="text-center fw-semibold"><?php echo (int)($row['so_lop'] ?? 0); ?></td>
                                    <td class="text-center">
                                        <span class="stat-chip"><i class="bi bi-people"></i><?php echo (int)($row['tong_si_so_khai_bao'] ?? 0); ?></span>
                                    </td>
                                    <td class="text-center fw-semibold text-primary"><?php echo (int)($row['so_hoc_sinh_dang_hoc'] ?? 0); ?></td>
                                    <td class="text-center fw-semibold"><?php echo (int)($row['so_giao_vien'] ?? 0); ?></td>
                                    <td class="text-center fw-semibold"><?php echo (int)($row['so_admin_truong'] ?? 0); ?></td>
                                    <td class="text-center fw-semibold"><?php echo (int)($row['so_phu_huynh'] ?? 0); ?></td>
                                    <td class="text-center fw-semibold"><?php echo (int)($row['so_phan_cong_day'] ?? 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
