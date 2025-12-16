<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        /* Variables */
        :root {
            --primary-color: #0A6ED1; /* Deep Blue */
            --secondary-color: #2196F3; /* Light Blue */
            --background-start: #E3F2FD; /* Very Light Blue */
            --background-end: #BBDEFB; /* Medium Light Blue */
            --text-dark: #333;
            --font-family: 'Roboto', sans-serif;
        }

        /* Global & Body */
        body {
            font-family: var(--font-family);
            background: linear-gradient(135deg, var(--background-start) 0%, var(--background-end) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Login Card */
        .login-card {
            max-width: 420px; /* Tăng nhẹ độ rộng */
            width: 100%;
            border: none;
            border-radius: 20px; /* Bo tròn hơn */
            background-color: white;
            box-shadow: 0 15px 40px rgba(10,110,209,0.3); /* Shadow mạnh hơn, nổi bật hơn */
            overflow: hidden;
            animation: slideInUp 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55); /* Animation mượt hơn */
        }
        .login-card .card-body {
            padding: 3rem; /* Tăng padding */
        }

        /* Header */
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .login-header i {
            color: var(--primary-color);
            font-size: 4.5rem; /* Icon lớn hơn */
            margin-bottom: 0.5rem;
        }
        .login-header h3 {
            color: var(--primary-color);
            font-weight: 900; /* Extra bold */
            margin-bottom: 0.25rem;
        }
        .login-header p {
            color: #777;
            font-size: 1.05rem;
            font-weight: 300;
        }

        /* Form Controls */
        .form-control {
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            padding: 0.85rem 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.2); /* Sử dụng secondary color cho shadow */
        }
        .form-label {
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 0.4rem;
        }

        /* Button */
        .btn-primary {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 12px;
            padding: 0.85rem;
            font-weight: 700;
            width: 100%;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(10,110,209,0.5);
            background: linear-gradient(90deg, #0959B4 0%, #1a87e5 100%);
        }

        /* Links & Checkbox */
        .link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        .link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Animation Keyframes */
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive Adjustments */
        @media (max-width: 576px) { 
            .login-card { margin: 1rem; border-radius: 15px; } 
            .login-card .card-body { padding: 2rem; }
            .login-header i { font-size: 3.5rem; } 
            .login-header h3 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5">
                <div class="login-card">
                    <div class="card-body">
                        <div class="login-header">
                            <i class="bi bi-shield-lock"></i>
                            <h3>ĐĂNG NHẬP HỆ THỐNG</h3>
                            <p>Truy cập dashboard dành cho Giáo viên, Admin và Sở GD.</p>
                        </div>
                        
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo BASE_URL; ?>/auth/login">
                            <div class="mb-3">
                                <label for="username" class="form-label">Tên Đăng Nhập (Email/Số ĐT)</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Nhập email hoặc số điện thoại" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Mật Khẩu</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Nhập mật khẩu" required>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                                </div>
                                <a href="#" class="link small">Quên Mật Khẩu?</a>
                            </div>
                            <button type="submit" class="btn btn-primary mb-4">Đăng Nhập</button>
                            
                            </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>