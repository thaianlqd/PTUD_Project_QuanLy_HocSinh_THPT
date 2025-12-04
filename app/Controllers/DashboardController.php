<?php
if (session_status() === PHP_SESSION_NONE) session_start();

class DashboardController extends Controller {
    private $userModel;

    public function index() {
        if (!isset($_SESSION['user_role'])) {
            header('Location: ' . BASE_URL . '/auth/index');
            exit;
        }

        $role = $_SESSION['user_role'];
        $user_id = $_SESSION['user_id'] ?? 0;
        $chuc_vu = $_SESSION['user_chuc_vu'] ?? null;
        $this->userModel = $this->loadModel('UserModel');

        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'User',
            'user_id' => $user_id
        ];
        
        // Cờ mặc định
        $data['is_bgh'] = false;
        $data['is_gvcn'] = false;

        switch ($role) {
            case 'QuanTriVien':
                $school_id = $this->userModel->getAdminSchoolId($user_id);
                $_SESSION['admin_school_id'] = $school_id;
                $data['tk_count'] = $this->userModel->getTotalUsers($school_id);
                $data['lop_count'] = $this->userModel->getTotalLop($school_id); 
                $data['hs_count'] = $this->userModel->getTotalHs($school_id); 
                $data['tk_role_data'] = $this->userModel->getTkByRole($school_id);
                $data['si_so_khoi'] = $this->userModel->getSiSoKhoi($school_id); 
                $data['users_list'] = $this->userModel->getAllUsers(10, $school_id);
                echo $this->loadView('QuanTri/dashboard', $data);
                break;

            case 'GiaoVien':
                // --- LOGIC TỔNG HỢP (3 TRONG 1) ---

                // 1. DỮ LIỆU BAN GIÁM HIỆU (Nếu có chức vụ)
                if ($chuc_vu && (str_contains($chuc_vu, 'BanGiamHieu') || str_contains($chuc_vu, 'Hiệu') || str_contains($chuc_vu, 'Phó'))) {
                    $data['is_bgh'] = true;
                    $diemSoModel = $this->loadModel('DiemSoModel');
                    $data['bgh_yeu_cau_count'] = $diemSoModel->getPhieuChoDuyetCount();
                    $data['bgh_diem_tb']       = $diemSoModel->getDiemTBToanTruong();
                    $data['bgh_phieu_moi']     = $diemSoModel->getDanhSachPhieu('ChoDuyet', 5);
                    $data['bgh_chart_tron']    = $diemSoModel->getChartYeuCau();
                    $data['bgh_chart_cot']     = $diemSoModel->getChartDiemLop();
                }

                // 2. DỮ LIỆU CHỦ NHIỆM (Load thủ công để tránh lỗi NULL Model)
                $pathModelCN = './app/Models/GiaoVienChuNhiemModel.php'; 
                if (file_exists($pathModelCN)) {
                    require_once $pathModelCN;
                    $gvcnModel = new GiaoVienChuNhiemModel();
                } else {
                    $gvcnModel = null;
                }

                if ($gvcnModel) {
                    $lopCN = $gvcnModel->getLopChuNhiem($user_id);
                    if ($lopCN) {
                        $data['is_gvcn'] = true;
                        $data['cn_info'] = $lopCN;
                        $data['cn_hs_list'] = $gvcnModel->getDanhSachHocSinh($lopCN['ma_lop']);
                        $data['cn_chart_hk'] = $gvcnModel->getChartHanhKiem($lopCN['ma_lop']);
                    }
                }

                // 3. DỮ LIỆU GIẢNG DẠY (Ai cũng có)
                $gvBaiTapModel = $this->loadModel('GiaoVienBaiTapModel');
                $data['gd_mon']        = $gvBaiTapModel->getMonGiangDay($user_id);
                $data['gd_lop_list']   = $gvBaiTapModel->getDanhSachLop($user_id);
                $data['gd_lop_count']  = $gvBaiTapModel->getLopDayCount($user_id);
                $data['gd_dd_count']   = $gvBaiTapModel->getPhienDiemDanhCount($user_id);
                $data['gd_nopbai_pct'] = $gvBaiTapModel->getTyLeNopBaiTB($user_id);
                $data['gd_chart_nop']  = $gvBaiTapModel->getChartNopBai($user_id);
                $data['gd_chart_dd']   = $gvBaiTapModel->getChartDiemDanh($user_id);

                // GỌI VIEW TRONG FOLDER BGH (Dùng chung cho mọi GV)
                echo $this->loadView('BGH/dashboard', $data);
                break;

            case 'HocSinh':
                echo $this->loadView('HocSinh/dashboard', $data);
                break;
            case 'PhuHuynh':
                $phuHuynhModel = $this->loadModel('PhuHuynhModel');
                $data['hoc_sinh_info'] = $phuHuynhModel->getHocSinhInfo($user_id);
                $data['hoa_don_count'] = $phuHuynhModel->getHoaDonCount($user_id);
                $data['phieu_vang_count'] = $phuHuynhModel->getPhieuVangCount($user_id);
                $data['bang_diem'] = $phuHuynhModel->getBangDiem($user_id);
                echo $this->loadView('PhuHuynh/dashboard', $data);
                break;
            case 'ThiSinh':
                echo $this->loadView('ThiSinh/dashboard', $data);
                break;
            default:
                session_destroy();
                header('Location: ' . BASE_URL . '/auth/index?error=invalid_role');
                exit;
        }
    }
}
?>