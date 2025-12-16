<?php
class ThiSinhController extends Controller {
    private $model;

    public function __construct() {
        require_once dirname(__DIR__) . '/Models/ThiSinhModel.php';
        $this->model = new ThiSinhModel();
    }

    public function index() {
        // Chuyển hướng về dashboard hoặc trang chính của thí sinh
        header('Location: ' . BASE_URL . '/ThiSinh/dashboard');
        exit;
    }

    // public function quan_ly_ho_so() {
    //     if (session_status() === PHP_SESSION_NONE) session_start();
    //     if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'ThiSinh') {
    //         header('Location: ' . BASE_URL . '/auth/login');
    //         exit;
    //     }

    //     // Gọi model lấy thông tin nhập học
    //     require_once dirname(__DIR__) . '/Models/ThiSinhModel_NhapHoc.php';
    //     $nhapHocModel = new ThiSinhModel_NhapHoc();
    //     $user_id = $_SESSION['user_id'];
    //     $data['nhap_hoc_info'] = $nhapHocModel->getNhapHocInfo($user_id);

    //     echo $this->loadView('ThiSinh/quan_ly_ho_so', $data);
    // }
    public function quan_ly_ho_so() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'ThiSinh') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        require_once dirname(__DIR__) . '/Models/ThiSinhModel_NhapHoc.php';
        $nhapHocModel = new ThiSinhModel_NhapHoc();
        $user_id = $_SESSION['user_id'];

        $data['nhap_hoc_info'] = $nhapHocModel->getNhapHocInfo($user_id);
        $data['info'] = $nhapHocModel->getThongTinThiSinh($user_id);
        $data['nguyen_vong'] = $nhapHocModel->getDanhSachTruongNhapHoc($user_id);
        $data['diem'] = $nhapHocModel->getDiemThi($user_id);

        echo $this->loadView('ThiSinh/quan_ly_ho_so', $data);
    }


    // ====== TRANG DASHBOARD ======
    public function dashboard() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'ThiSinh') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
        
        $user_id = $_SESSION['user_id'];
        
        $data['info'] = $this->model->getThongTinCaNhan($user_id);
        $data['diem'] = $this->model->getDiemThi($user_id);
        $data['ket_qua'] = $this->model->getKetQuaTuyenSinh($user_id);
        $data['nguyen_vong'] = $this->model->getDanhSachNguyenVong($user_id);
        $data['nv_count'] = count($data['nguyen_vong']);
        
        $data['tong_diem'] = ($data['diem']['diem_toan'] ?? 0) * 2 + 
                            ($data['diem']['diem_van'] ?? 0) * 2 + 
                            ($data['diem']['diem_anh'] ?? 0);
        
        if (isset($data['ket_qua']['trang_thai'])) {
            $data['kq_class'] = ($data['ket_qua']['trang_thai'] == 'Dau') ? 'text-success' : 'text-danger';
            $data['kq_text'] = ($data['ket_qua']['trang_thai'] == 'Dau') ? 'Đậu' : 'Trượt';
        } else {
            $data['kq_class'] = 'text-secondary';
            $data['kq_text'] = 'Chưa có kết quả';
        }
        
        $data['xn_text'] = (isset($data['ket_qua']['trang_thai_xac_nhan']) && 
                            $data['ket_qua']['trang_thai_xac_nhan'] == 'Xac_nhan_nhap_hoc') 
                        ? 'Đã xác nhận' : 'Chưa xác nhận';
        
        echo $this->loadView('ThiSinh/dashboard', $data);
    }

    // ====== TRANG NGUYỆN VỌNG ======
    public function nguyenVong() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'ThiSinh') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $nguyen_vong = $this->model->getDanhSachNguyenVong($user_id);
        $truongs = $this->model->getDanhSachTruong();

        echo $this->loadView('ThiSinh/quan_ly_nguyen_vong', [
            'nguyen_vong' => $nguyen_vong,
            'truongs' => $truongs
        ]);
    }

    // ====== CÁC API CŨ (GIỮ NGUYÊN) ======
    
    public function dangKyNguyenVongApi() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Chưa login']); exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $result = $this->model->dangKyNguyenVong($_SESSION['user_id'], $input['ma_truong'], $input['thu_tu_nguyen_vong']);
        echo json_encode($result);
    }

    public function chinhSuaNguyenVongApi() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Chưa login']); exit;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $this->model->chinhSuaNguyenVong($_SESSION['user_id'], $input['ma_truong_cu'], $input['thu_tu_cu'], $input['ma_truong_moi'], $input['thu_tu_moi']);
        echo json_encode($result);
    }

    public function xoaNguyenVongApi() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Chưa login']); exit;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $this->model->xoaNguyenVong($_SESSION['user_id'], $input['ma_truong'], $input['thu_tu_nguyen_vong']);
        echo json_encode($result);
    }
    
    public function getDanhSachTruongApi() {
        header('Content-Type: application/json');
        try {
            $truongs = $this->model->getDanhSachTruong();
            echo json_encode(['success' => true, 'data' => $truongs]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ====== API MỚI: LƯU TOÀN BỘ (ĐÃ FIX LỖI SỐ 10) ======
    public function luuTatCaNguyenVongApi() {
        // [QUAN TRỌNG] Xóa bộ đệm output
        if (ob_get_length()) ob_clean(); 
        
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Phiên đăng nhập hết hạn']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $input = json_decode(file_get_contents('php://input'), true);
        
        $nv1 = $input['nv1'] ?? '';
        $nv2 = $input['nv2'] ?? '';
        $nv3 = $input['nv3'] ?? '';

        // Validate trùng
        $selectedSchools = array_filter([$nv1, $nv2, $nv3]);
        if (count($selectedSchools) !== count(array_unique($selectedSchools))) {
            echo json_encode(['success' => false, 'message' => 'Không được chọn trùng trường!']);
            exit;
        }

        try {
            // Bước 1: Xóa hết NV cũ của user
            $oldNV = $this->model->getDanhSachNguyenVong($user_id);
            foreach ($oldNV as $old) {
                $this->model->xoaNguyenVong($user_id, $old['ma_truong'], $old['thu_tu_nguyen_vong']);
            }

            // Bước 2: Insert lại
            $errors = [];
            
            if (!empty($nv1)) {
                $res = $this->model->dangKyNguyenVong($user_id, $nv1, 1);
                if (!$res['success']) $errors[] = "NV1: " . $res['message'];
            }
            if (!empty($nv2)) {
                $res = $this->model->dangKyNguyenVong($user_id, $nv2, 2);
                if (!$res['success']) $errors[] = "NV2: " . $res['message'];
            }
            if (!empty($nv3)) {
                $res = $this->model->dangKyNguyenVong($user_id, $nv3, 3);
                if (!$res['success']) $errors[] = "NV3: " . $res['message'];
            }

            if (empty($errors)) {
                echo json_encode(['success' => true, 'message' => 'Cập nhật thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi: ' . implode(", ", $errors)]);
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
        exit;
    }
}
?>