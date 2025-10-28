<?php
// Đảm bảo session đã được khởi động
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class DashboardController extends Controller {

    private $userModel; // Thêm property

    // Method mặc định (Khi truy cập URL: /dashboard)
    public function index() {
        // Kiểm tra xem đã đăng nhập chưa
        if (!isset($_SESSION['user_role'])) {
            // Nếu chưa, đá về trang đăng nhập
            header('Location: ' . BASE_URL . '/auth/index');
            exit;
        }

        $role = $_SESSION['user_role'];
        
        // Load model chung
        $this->userModel = $this->loadModel('UserModel');
        if ($this->userModel === null) {
            die("Lỗi nghiêm trọng: Không thể tải UserModel.");
        }

        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'User',
            'user_id' => $_SESSION['user_id'] ?? 0 
        ];
        
        $content = '';

        // Phân luồng dựa trên vai trò đã lưu trong Session (khớp với CSDL v3)
        switch ($role) {
            case 'HocSinh':
                // Load data từ model cho HS
                // (Bạn cần bổ sung các hàm này trong UserModel.php)
                // $data['student_info'] = $this->userModel->getStudentInfo($data['user_id']);
                // $data['bai_chua_nop'] = $this->userModel->getSoBaiChuaNop($data['user_id']);
                // $data['diem_tb_hk'] = $this->userModel->getDiemTbHocKy($data['user_id']);
                // $data['lich_tuan_count'] = $this->userModel->getSoBuoiHocTuan($data['user_id']);
                // $data['bai_tap_stats'] = $this->userModel->getBaiTapStats($data['user_id']);
                // $data['diem_tb_mon'] = $this->userModel->getDiemTbMon($data['user_id']);
                // $data['lich_hoc_tuan'] = $this->userModel->getLichHocTuan($data['user_id']);

                $content = $this->loadView('HocSinh/dashboard', $data);
                break;
            case 'PhuHuynh':
                // $data['ph_data'] = $this->userModel->getPhData($data['user_id']); 
                $content = $this->loadView('PhuHuynh/dashboard', $data);
                break;
            case 'ThiSinh':
                // $data['ts_data'] = $this->userModel->getTsData($data['user_id']);
                $content = $this->loadView('ThiSinh/dashboard', $data);
                break;
            case 'GiaoVien':
                 // $data['gv_data'] = $this->userModel->getGvData($data['user_id']);
                $content = $this->loadView('GiaoVien/dashboard', $data);
                break;
            
            case 'QuanTriVien':
                // Load data cho Quản Trị
                // Đây là các hàm gây lỗi, cần được sửa trong UserModel.php
                $data['tk_count'] = $this->userModel->getTotalUsers();
                $data['lop_count'] = $this->userModel->getTotalLop(); 
                $data['hs_count'] = $this->userModel->getTotalHs(); 
                $data['tk_role_data'] = $this->userModel->getTkByRole();
                $data['si_so_khoi'] = $this->userModel->getSiSoKhoi(); 
                $data['users_list'] = $this->userModel->getAllUsers(10); // Lấy 10 tài khoản mới nhất
                
                $content = $this->loadView('QuanTri/dashboard', $data);
                break;
            
            case 'NhanVienSoGD':
                // $data['sogd_data'] = $this->userModel->getSogdData($data['user_id']);
                $content = $this->loadView('SoGD/dashboard', $data);
                break;
                
            default:
                // Nếu vai trò lạ, hủy session và về trang login
                session_destroy();
                header('Location: ' . BASE_URL . '/auth/index?error=invalid_role');
                exit;
        }

        // Kiểm tra nếu loadView fail (debug)
        if (empty($content)) {
            echo "Error: View not found for role '$role'. Check folder app/Views/[Role]/dashboard.php";
            exit;
        }
        
        echo $content;
    }
}
?>

