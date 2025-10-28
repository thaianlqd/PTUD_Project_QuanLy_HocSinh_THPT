<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Quản Lý Trường THPT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #F0F8FF; } /* Light blue background */
        .navbar { background-color: #fff !important; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .navbar-brand { font-weight: 700; color: #0A6ED1 !important; }
        .hero { background: linear-gradient(135deg, #0A6ED1 0%, #2196F3 100%); color: white; padding: 100px 0; }
        .hero h1 { font-size: 3.5rem; font-weight: 700; animation: fadeInUp 1s ease; }
        .hero p { font-size: 1.2rem; animation: fadeInUp 1s ease 0.2s both; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.3s ease; overflow: hidden; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .card i { color: #0A6ED1; font-size: 2.5rem; margin-bottom: 1rem; transition: color 0.3s; }
        .card:hover i { color: #2196F3; }
        .tuyen-sinh { background-color: #E3F2FD; padding: 80px 0; }
        .tuyen-sinh .card { background: white; }
        .btn-primary { background: linear-gradient(135deg, #0A6ED1 0%, #2196F3 100%); border: none; border-radius: 25px; padding: 10px 30px; font-weight: 500; transition: all 0.3s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(10,110,209,0.4); }
        footer { background: linear-gradient(135deg, #0A6ED1 0%, #2196F3 100%); color: white; padding: 40px 0; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 768px) { .hero h1 { font-size: 2.5rem; } .hero { padding: 60px 0; } }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">THPT Manager</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#gioi-thieu">Giới Thiệu</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tuyen-sinh">Tuyển Sinh</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tin-tuc">Tin Tức</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary ms-2 rounded-pill px-3" href="<?php echo BASE_URL; ?>/auth/index">Đăng Nhập</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container text-center">
            <h1>Chào Mừng Đến Với Hệ Thống Quản Lý Trường THPT</h1>
            <p class="lead">Nền tảng hiện đại quản lý tuyển sinh, học tập và hành chính – Dành cho mọi vai trò từ Sở GD đến học sinh.</p>
            <a href="<?php echo BASE_URL; ?>/auth/index" class="btn btn-light btn-lg px-5 py-3">Bắt Đầu Ngay</a>
        </div>
    </section>

    <!-- Giới Thiệu Section -->
    <section id="gioi-thieu" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold text-primary">Giới Thiệu Về Hệ Thống</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card text-center p-4">
                        <i class="bi bi-building-education"></i>
                        <h5 class="card-title mt-3 fw-bold">Quản Lý Trường Học</h5>
                        <p class="card-text">Thông tin trường, lớp học, nhân sự và cơ sở vật chất được cập nhật thời gian thực.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center p-4">
                        <i class="bi bi-clipboard-check"></i>
                        <h5 class="card-title mt-3 fw-bold">Tuyển Sinh Thông Minh</h5>
                        <p class="card-text">Đăng ký nguyện vọng, xét tuyển tự động với chỉ tiêu và lọc ảo chính xác.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center p-4">
                        <i class="bi bi-graph-up"></i>
                        <h5 class="card-title mt-3 fw-bold">Thống Kê & Báo Cáo</h5>
                        <p class="card-text">Biểu đồ điểm số, điểm danh và học phí – Xuất PDF/Excel dễ dàng.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tuyển Sinh Section -->
    <section id="tuyen-sinh" class="tuyen-sinh">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold text-primary">Tuyển Sinh 2025</h2>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card p-4">
                        <h5 class="fw-bold text-primary mb-3">Lịch Tuyển Sinh Chi Tiết</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="bi bi-calendar-check text-primary me-2"></i>Đăng ký nguyện vọng: 02/05 - 10/05/2025</li>
                            <li class="mb-2"><i class="bi bi-pencil-square text-primary me-2"></i>Điều chỉnh nguyện vọng: 10/05 - 17/05/2025</li>
                            <li class="mb-2"><i class="bi bi-award text-primary me-2"></i>Thi tuyển: 06-07/06/2025</li>
                            <li><i class="bi bi-star-fill text-primary me-2"></i>Xét tuyển bổ sung: Sau 10/06/2025</li>
                        </ul>
                        <a href="<?php echo BASE_URL; ?>/auth/register" class="btn btn-primary mt-3">Đăng Ký Ngay</a> <!-- Sửa nếu có register route -->
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card p-4">
                        <h5 class="fw-bold text-primary mb-3">Tin Tức Nổi Bật</h5>
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-newspaper text-primary fs-4 me-3"></i>
                            <div>
                                <h6 class="fw-bold">Chỉ Tiêu Tuyển Sinh 2025 Đã Công Bố</h6>
                                <small class="text-muted">Ngày 15/04/2025</small>
                            </div>
                        </div>
                        <p class="text-muted">Hàng nghìn suất học bổng và chỉ tiêu mới cho lớp 10. Xem chi tiết và đăng ký ngay!</p>
                        <a href="#" class="btn btn-outline-primary">Đọc Thêm</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p class="mb-2">&copy; 2025 Hệ Thống Quản Lý Trường THPT. Tất cả quyền được bảo lưu.</p>
            <p class="text-muted small">Liên hệ: info@thptmanager.edu.vn | Hotline: 1900-1234</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scroll cho nav links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
            });
        });
    </script>
</body>
</html>





