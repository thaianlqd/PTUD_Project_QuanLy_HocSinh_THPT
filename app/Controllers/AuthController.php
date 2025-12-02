<?php

class AuthController extends Controller {

    public function index() {
        $content = $this->loadView('Auth/login');
        echo $content;
    }

    public function login() {
        $message = '';
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            // CHANGED: Tải UserModel mới
            $userModel = $this->loadModel('UserModel');
            // CHANGED: checkLogin đã được viết lại trong Model
            $user = $userModel->checkLogin($username, $password);

            if ($user) {

                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_id'] = $user['id'];
                
                // THÊM DÒNG NÀY VÀO
                $_SESSION['user_chuc_vu'] = $user['chuc_vu']; // Sẽ lưu là 'BanGiamHieu'

                // === THÊM DÒNG NÀY ĐỂ SỬA LỖI ===
                $_SESSION['ma_lop'] = $user['ma_lop'] ?? null;
                // === HẾT PHẦN SỬA ===

                header('Location: ' . BASE_URL . '/dashboard');
                exit;
            } else {
                $message = 'Tên đăng nhập, mật khẩu không đúng hoặc tài khoản đã bị khóa!';
            }
        }

        $content = $this->loadView('Auth/login', ['error_message' => $message]);
        echo $content;
    }

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/auth/index');
        exit;
    }
}
?>
