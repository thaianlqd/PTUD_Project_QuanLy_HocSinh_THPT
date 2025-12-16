<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Quản Lý Trường THPT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        /* Variables */
        :root {
            --primary-color: #0A6ED1; /* Deep Blue */
            --secondary-color: #2196F3; /* Light Blue */
            --background-light: #F0F8FF; /* Alice Blue */
            --background-section: #EBF5FF; /* Very light blue for sections */
            --text-dark: #333;
            --text-light: #fff;
            --font-family: 'Roboto', sans-serif;
        }

        /* Global Styles */
        body {
            font-family: var(--font-family);
            background-color: var(--background-light);
            color: var(--text-dark);
            padding-top: 70px; /* Offset for fixed navbar */
        }

        /* Navbar */
        .navbar {
            background-color: var(--text-light) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        .navbar-brand {
            font-weight: 900; /* Extra bold */
            color: var(--primary-color) !important;
            font-size: 1.5rem;
        }
        .nav-link {
            font-weight: 500;
            color: var(--text-dark) !important;
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        .nav-link.btn-primary {
            background: linear-gradient(90deg, #0A6ED1 0%, #2196F3 100%);
            border: none;
            color: white !important;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .nav-link.btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(10,110,209,0.5);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: var(--text-light);
            padding: 120px 0;
            position: relative;
            overflow: hidden;
            clip-path: polygon(0 0, 100% 0, 100% 95%, 0 100%); /* Slight wave effect at bottom */
        }
        .hero h1 {
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            animation: fadeInUp 1s ease;
        }
        .hero p {
            font-size: 1.4rem;
            font-weight: 300;
            margin-bottom: 40px;
            animation: fadeInUp 1s ease 0.2s both;
        }
        .hero .btn-light {
            color: var(--primary-color);
            font-weight: 700;
            border-radius: 30px;
            transition: all 0.3s;
        }
        .hero .btn-light:hover {
            background-color: rgba(255, 255, 255, 0.9);
            transform: scale(1.05);
        }

        /* Giới Thiệu Section (Features) */
        #gioi-thieu {
            padding: 80px 0;
            background-color: var(--text-light); /* White background for contrast */
        }
        .text-primary {
            color: var(--primary-color) !important;
        }
        #gioi-thieu h2 {
            font-weight: 700;
        }

        /* Card Styling */
        .card {
            border: 1px solid rgba(10, 110, 209, 0.1);
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            overflow: hidden;
            height: 100%; /* Ensure all cards are same height */
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        .card i {
            color: var(--secondary-color);
            font-size: 3rem;
            margin-bottom: 1rem;
            transition: color 0.3s;
        }
        .card:hover i {
            color: var(--primary-color);
        }
        .card-title {
            font-weight: 700;
            color: var(--primary-color);
        }

        /* Tuyển Sinh Section */
        .tuyen-sinh {
            background-color: var(--background-section);
            padding: 80px 0;
        }
        .tuyen-sinh .card {
            background: var(--text-light);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: none; /* Disable hover effect for this card set if desired, or keep it */
        }
        .tuyen-sinh .card:hover {
             transform: none;
             box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        /* List Styling in Tuyển Sinh */
        .tuyen-sinh ul li {
            font-size: 1.05rem;
            margin-bottom: 12px !important;
            padding-left: 5px;
            border-left: 3px solid var(--secondary-color);
        }
        .tuyen-sinh ul li i {
            font-size: 1.2rem;
        }
        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 30px;
            padding: 12px 35px;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(10,110,209,0.5);
            background: linear-gradient(45deg, #0959B4 0%, #1a87e5 100%);
        }
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 30px;
            transition: all 0.3s;
        }
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }

        /* Tin Tức Nổi Bật */
        .d-flex i {
            min-width: 40px;
        }
        .text-muted {
            font-style: italic;
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: var(--text-light);
            padding: 40px 0;
            margin-top: -20px; /* Overlap with clip-path effect on hero */
            position: relative;
        }
        footer p {
            margin-bottom: 5px;
        }
        footer .small {
            opacity: 0.8;
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .hero h1 { font-size: 3rem; }
            .hero p { font-size: 1.1rem; }
            .hero { padding: 80px 0; }
        }
        @media (max-width: 768px) {
            .hero { clip-path: polygon(0 0, 100% 0, 100% 98%, 0 100%); }
            body { padding-top: 56px; }
            .navbar-brand { font-size: 1.2rem; }
            .hero h1 { font-size: 2.2rem; margin-bottom: 15px; }
            .hero p { font-size: 1rem; margin-bottom: 30px; }
            #gioi-thieu { padding: 40px 0; }
            .tuyen-sinh { padding: 40px 0; }
        }
    </style>
</head>
<body>
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
                    <li class="nav-item"><a class="nav-link btn btn-primary ms-lg-3 rounded-pill px-4" href="<?php echo BASE_URL; ?>/auth/index">Đăng Nhập</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container text-center">
            <h1>Chào Mừng Đến Với Hệ Thống Quản Lý Trường THPT</h1>
            <p class="lead">Nền tảng hiện đại quản lý tuyển sinh, học tập và hành chính – Dành cho mọi vai trò từ Sở GD đến học sinh.</p>
            <a href="<?php echo BASE_URL; ?>/auth/index" class="btn btn-light btn-lg px-5 py-3 shadow-lg">Bắt Đầu Ngay</a>
        </div>
    </section>

    <section id="gioi-thieu" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold text-primary">⚡ Giới Thiệu Về Hệ Thống ⚡</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card text-center p-4">
                        <i class="bi bi-building-education"></i>
                        <h5 class="card-title mt-3 fw-bold">Quản Lý Trường Học</h5>
                        <p class="card-text text-muted">Thông tin trường, lớp học, nhân sự và cơ sở vật chất được cập nhật thời gian thực.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center p-4">
                        <i class="bi bi-clipboard-check"></i>
                        <h5 class="card-title mt-3 fw-bold">Tuyển Sinh Thông Minh</h5>
                        <p class="card-text text-muted">Đăng ký nguyện vọng, xét tuyển tự động với chỉ tiêu và lọc ảo chính xác.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center p-4">
                        <i class="bi bi-graph-up"></i>
                        <h5 class="card-title mt-3 fw-bold">Thống Kê & Báo Cáo</h5>
                        <p class="card-text text-muted">Biểu đồ điểm số, điểm danh và học phí – Xuất PDF/Excel dễ dàng.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="tuyen-sinh" class="tuyen-sinh">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold text-primary">📅 Tuyển Sinh 2025 🚀</h2>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card p-4">
                        <h5 class="fw-bold text-primary mb-3"><i class="bi bi-calendar-event me-2"></i>Lịch Tuyển Sinh Chi Tiết</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="bi bi-calendar-check text-success me-2"></i>**Đăng ký nguyện vọng:** 02/05 - 10/05/2025</li>
                            <li class="mb-2"><i class="bi bi-pencil-square text-warning me-2"></i>**Điều chỉnh nguyện vọng:** 10/05 - 17/05/2025</li>
                            <li class="mb-2"><i class="bi bi-award text-danger me-2"></i>**Thi tuyển:** 06-07/06/2025</li>
                            <li><i class="bi bi-star-fill text-info me-2"></i>**Xét tuyển bổ sung:** Sau 10/06/2025</li>
                        </ul>
                        <a href="<?php echo BASE_URL; ?>/auth/register" class="btn btn-primary mt-3">Đăng Ký Nguyện Vọng Ngay</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card p-4" id="tin-tuc">
                        <h5 class="fw-bold text-primary mb-3"><i class="bi bi-megaphone me-2"></i>Tin Tức Nổi Bật</h5>
                        <div class="d-flex align-items-center mb-3 border-bottom pb-3">
                            <i class="bi bi-newspaper text-primary fs-3 me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-0">Chỉ Tiêu Tuyển Sinh 2025 Đã Công Bố</h6>
                                <small class="text-muted">Ngày 15/04/2025</small>
                            </div>
                        </div>
                        <p class="text-secondary">Hàng nghìn suất học bổng và chỉ tiêu mới cho lớp 10. Xem chi tiết và đăng ký ngay!</p>
                        <a href="#" class="btn btn-outline-primary mt-auto">Đọc Thêm</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container text-center">
            <p class="mb-2 fw-light">&copy; 2025 Hệ Thống Quản Lý Trường THPT. Tất cả quyền được bảo lưu.</p>
            <p class="text-light small">Liên hệ: info@thptmanager.edu.vn | Hotline: **1900-1234**</p>
            <div class="mt-3">
                <i class="bi bi-facebook mx-2" style="font-size: 1.5rem;"></i>
                <i class="bi bi-twitter-x mx-2" style="font-size: 1.5rem;"></i>
                <i class="bi bi-envelope-fill mx-2" style="font-size: 1.5rem;"></i>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scroll cho nav links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                // Kiểm tra xem href có phải là # hay không
                if (this.getAttribute('href') !== '#') {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
        
        // Active scroll effect cho navbar (optional: làm navbar nhỏ lại khi cuộn)
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('shadow-lg'); // Thêm box shadow mạnh hơn khi cuộn
            } else {
                navbar.classList.remove('shadow-lg');
            }
        });
    </script>
</body>
</html>