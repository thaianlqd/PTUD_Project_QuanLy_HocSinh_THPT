<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn Lớp - Xếp Thời Khóa Biểu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .card-header { background-color: #0d6efd; color: white; } /* Blue header */
        .list-group-item-action:hover { background-color: #e9ecef; }
        .progress-bar { transition: width 0.6s ease; }
        .table th { background-color: #f8f9fa; } /* Light gray table header */
        .progress-container { /* Thêm container để text không bị tràn */
            position: relative;
            height: 22px;
            background-color: #e9ecef; /* Màu nền cho thanh progress */
            border-radius: 0.375rem; /* Bo góc giống progress bar */
            overflow: hidden; /* Ẩn phần tràn của progress-bar */
        }
        .progress-text {
             position: absolute;
             display: block;
             width: 100%;
             text-align: center;
             line-height: 22px;
             left: 0;
             font-weight: bold;
             color: #212529; /* Màu chữ mặc định (đen) */
             mix-blend-mode: difference; /* Hiệu ứng để chữ nổi bật trên nền */
             filter: invert(1) grayscale(1); /* Tăng độ tương phản */
             z-index: 2; /* Đè lên trên progress-bar */
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <header class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?php echo BASE_URL ?? ''; ?>/dashboard">
                <i class="bi bi-calendar-check-fill me-2"></i> Hệ Thống Quản Lý THPT
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL ?? ''; ?>/dashboard">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                           <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($data['user_name'] ?? 'Admin'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL ?? ''; ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <main class="container p-4">

        <!-- Hiển thị thông báo nếu có -->
        <?php if (isset($data['flash_message'])): ?>
            <div class="alert alert-<?php echo htmlspecialchars($data['flash_message']['type']); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($data['flash_message']['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-header text-center">
                <h1 class="h3 mb-0 fw-bold">Xếp Thời Khóa Biểu</h1>
                <p class="mb-0 text-white-50">Năm học 2025-2026 (Chọn lớp để bắt đầu)</p>
            </div>
            <div class="card-body p-0">
                 <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">Tên Lớp</th>
                                <th scope="col" class="text-center">Sĩ Số</th>
                                <th scope="col">Phòng Học Chính</th>
                                <th scope="col" style="min-width: 200px;">Tiến Độ Xếp Lịch ( / 45 tiết)</th>
                                <th scope="col" class="text-end pe-3">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Vòng lặp danh sách lớp -->
                            <?php if (empty($data['lop_hoc'])): ?>
                                <tr>
                                    <td colspan="5" class="text-center p-5">
                                        <h5 class="text-danger mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Không tìm thấy dữ liệu lớp học.</h5>
                                        <p class="text-muted mb-0">Vui lòng kiểm tra lại CSDL hoặc thêm lớp học mới.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data['lop_hoc'] as $lop): ?>
                                    <?php
                                        // Tính toán tiến độ dựa trên giới hạn 45
                                        $gioi_han_tuan = 45;
                                        $da_xep = (int)($lop['so_tiet_da_xep'] ?? 0);
                                        $ke_hoach = (int)($lop['tong_tiet_ke_hoach'] ?? 0); // Vẫn lấy kế hoạch để tham khảo

                                        // Phần trăm tính theo giới hạn 45
                                        $phan_tram = ($gioi_han_tuan == 0) ? 0 : round(($da_xep / $gioi_han_tuan) * 100);

                                        // Xác định màu sắc progress bar
                                        $bg_progress = 'bg-primary'; // Mặc định: Đang xếp
                                        if ($da_xep > $gioi_han_tuan) {
                                             $bg_progress = 'bg-danger'; // Vượt quá giới hạn
                                             $phan_tram = 100; // Hiển thị đầy thanh màu đỏ
                                        } elseif ($da_xep == $gioi_han_tuan) {
                                            $bg_progress = 'bg-success'; // Đạt giới hạn
                                        } elseif ($da_xep > 0 && $da_xep >= $ke_hoach && $ke_hoach > 0) {
                                             $bg_progress = 'bg-success'; // Đã xếp đủ theo kế hoạch (dù chưa đủ 45)
                                        } elseif ($da_xep > 0) {
                                            $bg_progress = 'bg-warning text-dark'; // Đang xếp, chưa đủ kế hoạch
                                        } elseif ($ke_hoach == 0) {
                                             $bg_progress = 'bg-secondary'; // Chưa có kế hoạch
                                             $phan_tram = 0; // Hiển thị thanh rỗng màu xám
                                        }

                                        // Chuẩn bị text hiển thị
                                        $progress_text = "$da_xep / $gioi_han_tuan";
                                        if ($ke_hoach == 0) {
                                            $progress_text = 'N/A'; // Chưa có kế hoạch
                                        } elseif($da_xep > $gioi_han_tuan) {
                                            $progress_text .= " (Vượt!)";
                                        }

                                        // Xác định màu text
                                        $text_color = (strpos($bg_progress, 'text-dark') !== false || $bg_progress == 'bg-secondary') ? 'text-dark' : 'text-white';

                                    ?>
                                    <tr>
                                        <td class="ps-3 fw-bold"><?php echo htmlspecialchars($lop['ten_lop']); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($lop['si_so']); ?></td>
                                        <td>
                                            <?php if (!empty($lop['ten_phong_chinh'])): ?>
                                                <i class="bi bi-geo-alt-fill text-muted me-1"></i> <?php echo htmlspecialchars($lop['ten_phong_chinh']); ?>
                                            <?php else: ?>
                                                <span class="text-danger fst-italic">Chưa gán</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="progress-container" title="<?php echo "$da_xep / $ke_hoach (Kế hoạch) | Giới hạn: $gioi_han_tuan"; ?>">
                                                <!-- Thanh bar -->
                                                <div class="progress-bar <?php echo $bg_progress; ?> progress-bar-striped"
                                                     role="progressbar" style="width: <?php echo $phan_tram; ?>%; height: 22px;"
                                                     aria-valuenow="<?php echo $da_xep; ?>" aria-valuemin="0" aria-valuemax="<?php echo $gioi_han_tuan; ?>">
                                                </div>
                                                <!-- Text đè lên -->
                                                <span class="progress-text <?php //echo $text_color; // Dùng mix-blend-mode thay vì đổi màu text ?>">
                                                    <?php echo $progress_text; ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="<?php echo BASE_URL . '/quantri/chiTietTkb/' . htmlspecialchars($lop['ma_lop']); ?>"
                                               class="btn btn-sm btn-outline-primary">
                                               <i class="bi bi-pencil-square me-1"></i> Xếp lịch
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div> <!-- End card-body -->
        </div> <!-- End card -->
    </main>

    <footer class="footer mt-auto py-3 bg-dark text-white mt-5">
        <div class="container text-center">
            <span class="text-white-50">&copy; <?php echo date("Y"); ?> Hệ thống Quản lý Trường THPT.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

