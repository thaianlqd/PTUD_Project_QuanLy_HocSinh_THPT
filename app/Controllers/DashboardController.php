<?php
if (session_status() === PHP_SESSION_NONE) session_start();

class DashboardController extends Controller {
    private $userModel;

    public function index() {
        // 1. Kiểm tra đăng nhập
        if (!isset($_SESSION['user_role'])) {
            header('Location: ' . BASE_URL . '/auth/index');
            exit;
        }

        $role = $_SESSION['user_role'];
        $user_id = $_SESSION['user_id'] ?? 0;
        $chuc_vu = $_SESSION['user_chuc_vu'] ?? null;
        
        // Load UserModel (Bắt buộc phải có)
        $this->userModel = $this->loadModel('UserModel');
        if (!$this->userModel) { die("Lỗi: Không tìm thấy file models/UserModel.php"); }

        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'User',
            'user_id' => $user_id
        ];
        
        // Khởi tạo cờ mặc định để tránh lỗi Undefined index ở View
        $data['is_bgh'] = false;
        $data['is_gvcn'] = false;

        // 2. Phân luồng xử lý
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
                // ============================================================
                // LOGIC TỔNG HỢP (3 TRONG 1) CHO GIÁO VIÊN
                // ============================================================

                // --- 1. DỮ LIỆU BAN GIÁM HIỆU ---
                if ($chuc_vu && (str_contains($chuc_vu, 'BanGiamHieu') || str_contains($chuc_vu, 'Hiệu') || str_contains($chuc_vu, 'Phó'))) {
                    $data['is_bgh'] = true;
                    $diemSoModel = $this->loadModel('DiemSoModel');
                    if ($diemSoModel) {
                        $data['bgh_yeu_cau_count'] = $diemSoModel->getPhieuChoDuyetCount();
                        $data['bgh_diem_tb']       = $diemSoModel->getDiemTBToanTruong();
                        $data['bgh_phieu_moi']     = $diemSoModel->getDanhSachPhieu('ChoDuyet', 5);
                        $data['bgh_chart_tron']    = $diemSoModel->getChartYeuCau();
                        $data['bgh_chart_cot']     = $diemSoModel->getChartDiemLop();
                    }
                }

                // --- 2. DỮ LIỆU CHỦ NHIỆM ---
                // Load thủ công để đảm bảo file tồn tại
                $pathModelCN = './app/Models/GiaoVienChuNhiemModel.php'; 
                $pathModelCN_Alt = '../app/Models/GiaoVienChuNhiemModel.php'; // Dự phòng

                if (file_exists($pathModelCN)) {
                    require_once $pathModelCN;
                    $gvcnModel = new GiaoVienChuNhiemModel();
                } elseif (file_exists($pathModelCN_Alt)) {
                    require_once $pathModelCN_Alt;
                    $gvcnModel = new GiaoVienChuNhiemModel();
                } else {
                    $gvcnModel = null;
                    // echo "Cảnh báo: Không tìm thấy file GiaoVienChuNhiemModel.php";
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

                // --- 3. DỮ LIỆU GIẢNG DẠY (Mặc định có) ---
                $gvBaiTapModel = $this->loadModel('GiaoVienBaiTapModel');
                if (!$gvBaiTapModel) { die("Lỗi: Không tìm thấy file models/GiaoVienBaiTapModel.php"); }
                
                $data['gd_mon']        = $gvBaiTapModel->getMonGiangDay($user_id);
                $data['gd_lop_list']   = $gvBaiTapModel->getDanhSachLop($user_id);
                $data['gd_lop_count']  = $gvBaiTapModel->getLopDayCount($user_id);
                $data['gd_dd_count']   = $gvBaiTapModel->getPhienDiemDanhCount($user_id);
                $data['gd_nopbai_pct'] = $gvBaiTapModel->getTyLeNopBaiTB($user_id);
                $data['gd_chart_nop']  = $gvBaiTapModel->getChartNopBai($user_id);
                $data['gd_chart_dd']   = $gvBaiTapModel->getChartDiemDanh($user_id);

                // DEBUG: Bỏ comment dòng dưới để xem dữ liệu truyền sang View
                // echo "<pre>"; print_r($data); echo "</pre>"; die();

                // Gọi View chung trong folder BGH
                echo $this->loadView('BGH/dashboard', $data);
                break;

            case 'HocSinh':
                    // ==================================================================
                    // DASHBOARD HỌC SINH – PHIÊN BẢN SẠCH & ỔN ĐỊNH NHẤT (đã sửa lỗi hiển thị "Hồ sơ chưa hoàn chỉnh")
                    // ==================================================================

                    // Load model chuẩn như các role khác (QuanTriVien, GiaoVien, PhuHuynh)
                    $hsModel = $this->loadModel('HocSinhModel');

                    if (!$hsModel) {
                        die("Lỗi nghiêm trọng: Không thể load file models/HocSinhModel.php");
                    }

                    // Lấy thông tin học sinh từ CSDL (hàm chính)
                    $hsInfo = $hsModel->getThongTinHS($user_id);

                    // Fallback bằng session (giữ lại logic cũ của bạn nếu cần, nhưng thường không còn cần thiết nữa vì query đã chạy đúng)
                    $session_info = [];
                    $ma_lop_session = $_SESSION['ma_lop'] ?? null;

                    if (!$hsInfo && $ma_lop_session) {
                        $lop_info = $hsModel->getTenLopByMaLop($ma_lop_session);
                        if ($lop_info) {
                            $session_info = [
                                'ma_hoc_sinh'   => $user_id,
                                'ho_ten'      => $_SESSION['user_name'] ?? 'Học Sinh',
                                'ngay_sinh'   => null,
                                'ten_lop'     => $lop_info['ten_lop'],
                                'ma_lop'       => $ma_lop_session,
                                'nien_khoa'  => $hsModel->getNienKhoaByMaNamHoc($lop_info['ma_nam_hoc'] ?? null) ?? 'N/A'
                            ];
                        }
                    }

                    // Ưu tiên dữ liệu từ CSDL → fallback → rỗng
                    $data['student_info'] = $hsInfo ?: $session_info ?: [];

                    // Gán các giá trị mặc định để View không lỗi
                    $data['bai_chua_nop']       = 0;
                    $data['lich_tuan_count']      = 0;
                    $data['diem_tb_hk']         = '--';
                    $data['diem_tb_mon']     = [];
                    $data['bai_tap_stats']     = ['da_nop' => 0, 'chua_nop' => 0];
                    $data['lich_hoc_tuan']     = [];

                    // Nếu có thông tin học sinh → lấy đầy đủ thống kê
                    if (!empty($data['student_info'])) {
                        $ma_hs  = $data['student_info']['ma_hoc_sinh'];
                        $ma_lop = $data['student_info']['ma_lop'] ?? null;

                        if ($ma_lop && $ma_lop !== 'N/A') {
                            $data['bai_tap_stats'] = $hsModel->getStatsBaiTap($ma_hs, $ma_lop);
                            $data['bai_chua_nop']       = $data['bai_tap_stats']['chua_nop'] ?? 0;

                            $data['diem_tb_mon']   = $hsModel->getDiemTrungBinhMon($ma_hs);
                            $data['diem_tb_hk']         = $hsModel->getDiemTongKetHK($ma_hs);

                            $data['lich_hoc_tuan']   = $hsModel->getLichHocTuan($ma_lop);
                            $data['lich_tuan_count']      = count($data['lich_hoc_tuan']);
                        }
                    }

                    echo $this->loadView('HocSinh/dashboard', $data);
                    break;
                            
            case 'PhuHuynh':
                $phuHuynhModel = $this->loadModel('PhuHuynhModel');
                $data['hoc_sinh_info'] = $phuHuynhModel->getHocSinhInfo($user_id);
                $data['hoa_don_count'] = $phuHuynhModel->getHoaDonCount($user_id);
                $data['phieu_vang_count'] = $phuHuynhModel->getPhieuVangCount($user_id);
                $data['bang_diem'] = $phuHuynhModel->getBangDiem($user_id);

                $data['school_name'] = $phuHuynhModel->getTenTruongCuaCon($user_id);
                $_SESSION['school_name'] = $data['school_name']; // nếu muốn dùng ở trang khác
                
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