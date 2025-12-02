<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Bài Tập - Chọn Lớp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style> 
        body { background-color: #f0f8ff; } 
        .card-link { text-decoration: none; cursor: pointer; }
        .card { transition: all 0.2s ease; } 
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); } 
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <header class="mb-4 p-4 bg-white rounded-3 shadow-sm">
            <h1 class="fw-bold text-center text-primary"><i class="bi bi-book-half me-2"></i> QUẢN LÝ BÀI TẬP</h1>
            <p class="text-center text-muted">Chào mừng, <?php echo htmlspecialchars($data['user_name']); ?>!</p>
            <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-outline-secondary btn-sm float-end" style="margin-top: -50px;">
                <i class="bi bi-arrow-left"></i> Quay lại Dashboard
            </a>
        </header>

        <div id="formNotification" class="alert" style="display: none;"></div>

        <h3 class="fw-bold text-primary mb-3">Chọn Lớp/Môn Học để xem bài tập:</h3>
        
        <div class="row g-3">
            <?php if (empty($data['danh_sach_lop'])): ?>
                <div class="col-12"><div class="alert alert-warning">Bạn chưa được phân công giảng dạy lớp nào.</div></div>
            <?php else: ?>
                <?php foreach ($data['danh_sach_lop'] as $lop): ?>
                    <div class="col-md-4 col-lg-3">
                        <a class="card-link" 
                           href="<?php echo BASE_URL; ?>/giaovien/danhsachbaitap/<?php echo $lop['ma_lop']; ?>/<?php echo $lop['ma_mon_hoc']; ?>">
                           
                            <div class="card h-100">
                                <div class="card-body text-center p-4">
                                    <i class="bi bi-journal-text fs-1 text-primary"></i>
                                    <h5 class="card-title fw-bold mt-3"><?php echo htmlspecialchars($lop['ten_mon_hoc']); ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($lop['ten_lop']); ?></h6>
                                    <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill">Sĩ số: <?php echo $lop['si_so']; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>