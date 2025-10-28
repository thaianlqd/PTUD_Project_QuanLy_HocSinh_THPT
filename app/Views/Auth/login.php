<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - THPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { max-width: 400px; width: 100%; border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(10,110,209,0.2); overflow: hidden; animation: slideInUp 0.8s ease; }
        .login-card .card-body { padding: 2.5rem; }
        .login-header { text-align: center; margin-bottom: 2rem; }
        .login-header i { color: #0A6ED1; font-size: 4rem; margin-bottom: 1rem; }
        .login-header h3 { color: #0A6ED1; font-weight: 700; margin-bottom: 0.5rem; }
        .login-header p { color: #666; font-size: 1rem; }
        .form-control { border-radius: 10px; border: 1px solid #ddd; padding: 0.75rem 1rem; transition: border-color 0.3s; }
        .form-control:focus { border-color: #0A6ED1; box-shadow: 0 0 0 0.2rem rgba(10,110,209,0.25); }
        .btn-primary { background: linear-gradient(135deg, #0A6ED1 0%, #2196F3 100%); border: none; border-radius: 10px; padding: 0.75rem; font-weight: 500; width: 100%; transition: all 0.3s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(10,110,209,0.4); }
        .link { color: #0A6ED1; text-decoration: none; transition: color 0.3s; }
        .link:hover { color: #2196F3; text-decoration: underline; }
        .form-check-input:checked { background-color: #0A6ED1; border-color: #0A6ED1; }
        @keyframes slideInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 576px) { .login-card { margin: 1rem; } .login-header i { font-size: 3rem; } }
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
                            <h3>Đăng Nhập Hệ Thống</h3>
                            <p>Vui lòng nhập thông tin để truy cập dashboard theo vai trò của bạn.</p>
                        </div>
                        
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo BASE_URL; ?>/auth/login">
                            <div class="mb-3">
                                <label for="username" class="form-label fw-medium">Tên Đăng Nhập (Email/Số ĐT)</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Nhập email hoặc số điện thoại" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label fw-medium">Mật Khẩu</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Nhập mật khẩu" required>
                            </div>
                            <div class="mb-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mb-3">Đăng Nhập</button>
                            <div class="text-center">
                                <a href="<?php echo BASE_URL; ?>/auth/register" class="link me-3">Chưa có tài khoản? Đăng Ký Thí Sinh</a>
                                <a href="#" class="link">Quên Mật Khẩu?</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>