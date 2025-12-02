<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Nộp Bài</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f8ff; }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <header class="mb-4 p-4 bg-white rounded-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="fw-bold text-primary">
                        <i class="bi bi-file-earmark-text-fill me-2"></i>
                        <?php echo htmlspecialchars($data['bai_tap_info']['ten_bai_tap']); ?>
                    </h1>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($data['bai_tap_info']['mo_ta']); ?></p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL . '/giaovien/danhsachbaitap/' . $data['bai_tap_info']['ma_lop'] . '/' . $data['bai_tap_info']['ma_mon_hoc']; ?>"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Quay lại Danh sách
                    </a>
                </div>
            </div>
        </header>
        
        <div class="mb-3">
            <button class="btn btn-warning"><i class="bi bi-pencil"></i> Chỉnh sửa bài tập này</button>
            <button class="btn btn-danger"><i class="bi bi-trash"></i> Xóa bài tập</button>
        </div>
        <hr>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="bi bi-check-circle-fill me-2"></i> ĐÃ NỘP BÀI
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php
                        $daNop = false;
                        foreach ($data['danh_sach_nop_bai'] as $hs):
                            if ($hs['ma_bai_nop'] !== null):
                                $daNop = true;
                        ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php echo htmlspecialchars($hs['ho_ten']); ?>
                                        <small class="d-block text-muted">Nộp lúc: <?php echo date('H:i - d/m/Y', strtotime($hs['ngay_nop'])); ?></small>
                                    </div>
                                    <div>
                                        <span class="badge <?php echo $hs['trang_thai'] == 'HoanThanh' ? 'bg-success' : 'bg-info'; ?> me-2">
                                            <?php echo $hs['trang_thai'] == 'HoanThanh' ? ('Điểm: ' . $hs['diem_so']) : 'Chờ chấm'; ?>
                                        </span>
                                        <button class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i> Chấm</button>
                                    </div>
                                </li>
                        <?php
                            endif;
                        endforeach;
                        if (!$daNop): ?>
                            <li class="list-group-item text-muted text-center">Chưa có học sinh nào nộp.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white fw-bold">
                        <i class="bi bi-x-circle-fill me-2"></i> CHƯA NỘP BÀI
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php
                        $chuaNop = false;
                        foreach ($data['danh_sach_nop_bai'] as $hs):
                            if ($hs['ma_bai_nop'] === null):
                                $chuaNop = true;
                        ?>
                                <li class="list-group-item">
                                    <?php echo htmlspecialchars($hs['ho_ten']); ?>
                                </li>
                        <?php
                            endif;
                        endforeach;
                        if (!$chuaNop): ?>
                            <li class="list-group-item text-muted text-center">Tất cả học sinh đã nộp bài.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>