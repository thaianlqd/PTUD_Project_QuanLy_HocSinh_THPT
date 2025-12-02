<?php
// Đảm bảo session đã được khởi động
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class DashboardController extends Controller {

    private $userModel; // Thêm property

    // Method mặc định (Khi truy cập URL: /dashboard)
    public function index() {
        // 1. KIỂM TRA LOGIN
        if (!isset($_SESSION['user_role'])) {
            // Nếu chưa, đá về trang đăng nhập
            header('Location: ' . BASE_URL . '/auth/index');
            exit;
        }

        // 2. CHUẨN BỊ DATA CHUNG
        $role = $_SESSION['user_role'];
        $chuc_vu = $_SESSION['user_chuc_vu'] ?? null; // Lấy chức vụ
        $user_id = $_SESSION['user_id'] ?? 0;
        
        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'User',
            'user_id' => $user_id
        ];
        
        $content = '';
        
        // Load model chung
        $this->userModel = $this->loadModel('UserModel');
        if ($this->userModel === null) {
            die("Lỗi nghiêm trọng: Không thể tải UserModel.");
        }


        // 3. PHÂN LUỒNG VÀ NẠP DATA RIÊNG
        switch ($role) {
            case 'HocSinh':
                // (Code của Học Sinh)
                $content = $this->loadView('HocSinh/dashboard', $data);
                break;
                
            case 'PhuHuynh':
                $phuHuynhModel = $this->loadModel('PhuHuynhModel');

                $data['hoc_sinh_info'] = $phuHuynhModel->getHocSinhInfo($data['user_id']);
                $data['hoa_don_count'] = $phuHuynhModel->getHoaDonCount($data['user_id']);
                $data['phieu_vang_count'] = $phuHuynhModel->getPhieuVangCount($data['user_id']);
                $data['bang_diem'] = $phuHuynhModel->getBangDiem($data['user_id']);

                $content = $this->loadView('PhuHuynh/dashboard', $data);
                break;
                
            case 'ThiSinh':
                $content = $this->loadView('ThiSinh/dashboard', $data);
                break;
            
            // --- BẮT ĐẦU PHẦN SỬA ---
            case 'GiaoVien':
                // Check chức vụ đã lưu trong Session
                
                // (str_contains an toàn hơn == vì chức vụ có thể là 'Hiệu Trưởng' hoặc 'BanGiamHieu')
                if ($chuc_vu && (str_contains($chuc_vu, 'BanGiamHieu') || str_contains($chuc_vu, 'Hiệu Trưởng'))) {
                    // --- NẠP DATA CHO BGH ---
                    $diemSoModel = $this->loadModel('DiemSoModel');
                    $data['yeu_cau_count'] = $diemSoModel->getPhieuChoDuyetCount();
                    $data['diem_tb_truong'] = $diemSoModel->getDiemTBToanTruong();
                    $data['phieu_moi_nhat'] = $diemSoModel->getDanhSachPhieu('ChoDuyet', 5);
                    
                    $content = $this->loadView('BGH/dashboard', $data); // <--- LOAD VIEW BGH

                } elseif ($chuc_vu && str_contains($chuc_vu, 'Chủ Nhiệm')) {
                    // --- NẠP DATA CHO GVCN ---
                    // (Bạn có thể nạp data riêng cho GVCN ở đây sau)
                    
                    $content = $this->loadView('GVCN/dashboard', $data); // <--- LOAD VIEW GVCN

                } else {
                    // --- NẠP DATA CHO GV BỘ MÔN (Mặc định) ---
                    $gvBaiTapModel = $this->loadModel('GiaoVienBaiTapModel');
                    $data['lop_day_count'] = $gvBaiTapModel->getLopDayCount($user_id);
                    // $data['bai_nop_percent'] = ... (tải sau)
                    
                    // SỬA ĐƯỜNG DẪN Ở ĐÂY:
                    $content = $this->loadView('GVBoMon/dashboard', $data); // <--- SỬA THÀNH 'GVBoMon'
                }
                break;
            
            // Case 'BanGiamHieu' (Dự phòng nếu vai trò CSDL của bạn là BGH)
            case 'BanGiamHieu':
                $diemSoModel = $this->loadModel('DiemSoModel');
                $data['yeu_cau_count'] = $diemSoModel->getPhieuChoDuyetCount();
                $data['diem_tb_truong'] = $diemSoModel->getDiemTBToanTruong();
                $data['phieu_moi_nhat'] = $diemSoModel->getDanhSachPhieu('ChoDuyet', 5);
                
                $content = $this->loadView('BGH/dashboard', $data); // <--- LOAD VIEW BGH
                break;
            // --- KẾT THÚC PHẦN SỬA ---
            
            case 'QuanTriVien':
                // (Code của Quản Trị Viên)
                $data['tk_count'] = $this->userModel->getTotalUsers();
                $data['lop_count'] = $this->userModel->getTotalLop(); 
                $data['hs_count'] = $this->userModel->getTotalHs(); 
                $data['tk_role_data'] = $this->userModel->getTkByRole();
                $data['si_so_khoi'] = $this->userModel->getSiSoKhoi(); 
                $data['users_list'] = $this->userModel->getAllUsers(10);
                $content = $this->loadView('QuanTri/dashboard', $data);
                break;
                
            case 'NhanVienSoGD':
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
            // Sửa thông báo lỗi
            echo "Error: View không tìm thấy cho role '$role' (Chuc vu: '$chuc_vu'). Kiểm tra thư mục: app/views/";
            exit;
        }
        
        echo $content;
    }
}
?>