<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Điểm Danh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f8ff; }
        /* Hiệu ứng card chuyên nghiệp */
        .card { transition: all 0.2s ease; cursor: pointer; border-left: 4px solid #ffc107; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); border-left-color: #ffca2c; }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <header class="mb-4 p-4 bg-white rounded-3 shadow-sm">
            <h1 class="fw-bold text-center text-warning"><i class="bi bi-check-circle-fill me-2"></i> QUẢN LÝ ĐIỂM DANH</h1>
            <p class="text-center text-muted">Chào mừng, <?php echo htmlspecialchars($data['user_name']); ?>!</p>
            <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-outline-secondary btn-sm float-end" style="margin-top: -50px;">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </header>
        
        <h4 class="text-warning mb-3 fw-bold" style="color: #d39e00 !important;">Chọn Lớp/Môn học để bắt đầu:</h4>
        
        <div class="row g-3">
            <?php if (!empty($data['danh_sach_lop'])): ?>
                <?php foreach ($data['danh_sach_lop'] as $lop): ?>
                    <div class="col-md-4 col-lg-3">
                        <a href="<?php echo BASE_URL; ?>/giaovien/chitietlop/<?php echo $lop['ma_lop']; ?>/<?php echo $lop['ma_mon_hoc']; ?>" 
                           class="card h-100 text-decoration-none">
                           
                            <div class="card-body text-center p-4">
                                <i class="bi bi-people-fill fs-1 text-warning"></i>
                                
                                <h5 class="card-title fw-bold mt-3 text-dark"><?php echo htmlspecialchars($lop['ten_mon_hoc']); ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($lop['ten_lop']); ?></h6>
                                
                                <span class="badge bg-warning-subtle text-dark rounded-pill border border-warning-subtle">
                                    Sĩ số: <?php echo $lop['si_so']; ?>
                                </span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Bác chưa được phân công dạy lớp nào trong học kỳ này.
                    </div>
                </div>
            <?php endif; ?>
            </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>