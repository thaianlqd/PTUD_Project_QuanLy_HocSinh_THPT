<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn Lớp - Xếp Thời Khóa Biểu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        /* Modern color system with professional palette */
        :root {
            --primary-color: #2563eb;
            --primary-light: #dbeafe;
            --success-color: #16a34a;
            --warning-color: #ea580c;
            --danger-color: #dc2626;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', sans-serif;
            color: #1f2937;
        }

        /* Navbar with modern gradient and smooth styling */
        .navbar {
            background: linear-gradient(90deg, #1e293b 0%, #0f172a 100%) !important;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            padding: 0.75rem 0;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            background: linear-gradient(90deg, #60a5fa 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: var(--transition);
        }

        .navbar-brand:hover {
            transform: translateY(-2px);
        }

        .navbar-brand i {
            -webkit-text-fill-color: #60a5fa;
            margin-right: 0.75rem;
        }

        .nav-link {
            color: #e5e7eb !important;
            font-weight: 500;
            font-size: 0.95rem;
            margin: 0 0.5rem;
            transition: var(--transition);
            position: relative;
        }

        .nav-link:hover {
            color: #60a5fa !important;
            transform: translateY(-2px);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #60a5fa, #3b82f6);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .dropdown-menu {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 0.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .dropdown-item {
            color: #e5e7eb;
            transition: var(--transition);
        }

        .dropdown-item:hover {
            background: #0f172a;
            color: #60a5fa;
        }

        /* Main container and alert styling */
        main {
            padding: 2rem 1rem;
        }

        .alert {
            border-radius: 0.75rem;
            border: none;
            font-weight: 500;
            box-shadow: var(--card-shadow);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #dcfce7;
            color: #15803d;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .alert-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .alert-info {
            background: #cffafe;
            color: #0c4a6e;
        }

        /* Card with improved styling and shadows */
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: var(--transition);
            background: white;
        }

        .card:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transform: translateY(-4px);
        }

        .card-header {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 2rem;
            border: none;
            text-align: center;
        }

        .card-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .card-header p {
            font-size: 0.95rem;
            opacity: 0.9;
            margin: 0;
            font-weight: 500;
        }

        /* Table styling with better spacing and hover effects */
        .table-responsive {
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
            font-size: 0.95rem;
        }

        .table thead th {
            background: #f8fafc;
            color: #1e293b;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1.25rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .table tbody tr {
            transition: var(--transition);
            border-bottom: 1px solid #f1f5f9;
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
            box-shadow: inset 0 0 10px rgba(37, 99, 235, 0.05);
        }

        .table tbody td {
            padding: 1.25rem;
            vertical-align: middle;
            color: #475569;
        }

        .table tbody td:first-child {
            font-weight: 600;
            color: #1e293b;
        }

        /* Badge and status indicators */
        .text-danger {
            color: #dc2626 !important;
        }

        .text-muted {
            color: #94a3b8 !important;
        }

        .text-center {
            color: #64748b;
            font-weight: 500;
        }

        /* Progress bar styling with custom appearance */
        .progress-container {
            position: relative;
            height: 28px;
            background-color: #e2e8f0;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
        }

        .progress-bar {
            height: 100%;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(100%);
            }
        }

        .bg-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%) !important;
        }

        .bg-success {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
        }

        .bg-warning {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%) !important;
        }

        .bg-danger {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
        }

        .bg-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;
        }

        .progress-text {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            left: 0;
            font-weight: 600;
            font-size: 0.875rem;
            color: #1e293b;
            z-index: 2;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.5);
            mix-blend-mode: multiply;
        }

        /* Button styling with hover effects */
        .btn-outline-primary {
            color: #2563eb;
            border-color: #2563eb;
            font-weight: 600;
            transition: var(--transition);
            border-radius: 0.5rem;
        }

        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            border-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-outline-primary i {
            margin-right: 0.5rem;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        /* Footer styling */
        .footer {
            margin-top: auto;
            background: linear-gradient(90deg, #1e293b 0%, #0f172a 100%);
            color: white;
            padding: 2rem 0;
            border-top: 1px solid #334155;
        }

        .footer span {
            color: #94a3b8;
            font-size: 0.95rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            main {
                padding: 1rem;
            }

            .card-header {
                padding: 1.5rem;
            }

            .card-header h1 {
                font-size: 1.35rem;
            }

            .table {
                font-size: 0.875rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.75rem;
            }

            .btn-sm {
                padding: 0.4rem 0.75rem;
                font-size: 0.8rem;
            }

            .progress-container {
                height: 24px;
            }

            .navbar-brand {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 576px) {
            .card-header h1 {
                font-size: 1.1rem;
            }

            .card-header p {
                font-size: 0.85rem;
            }

            main {
                padding: 0.75rem;
            }

            .table-responsive {
                font-size: 0.8rem;
            }
        }

        /* Icon styling */
        i {
            font-size: 1rem;
        }

        .bi-exclamation-triangle-fill {
            color: #dc2626;
        }

        .bi-geo-alt-fill {
            color: #2563eb;
        }

        .bi-pencil-square {
            font-size: 0.95rem;
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
