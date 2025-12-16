<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class HocSinhController extends Controller {

    private $diemDanhHSModel;
    private $ma_hoc_sinh;
    private $ma_lop;
    

    public function __construct() {
        // 1. KIỂM TRA QUYỀN & ID
        $is_logged_in = isset($_SESSION['user_role']) && 
                          $_SESSION['user_role'] == 'HocSinh' && 
                          isset($_SESSION['user_id']) && 
                          isset($_SESSION['ma_lop']); // Quan trọng: check ma_lop

        if (!$is_logged_in) {
            if ($this->is_ajax()) { 
                http_response_code(401); 
                echo json_encode(['success' => false, 'message' => 'Phiên đăng nhập không hợp lệ.']);
                exit;
            } else { 
                header('Location: ' . BASE_URL . '/auth/index');
                exit;
            }
        }

        $this->ma_hoc_sinh = $_SESSION['user_id'];
        $this->ma_lop = $_SESSION['ma_lop'];
        $this->diemDanhHSModel = $this->loadModel('DiemDanhHSModel'); 
    }

    // Thêm vào trong class HocSinhController

    /**
     * TRANG DASHBOARD (Trang chủ của học sinh)
     * URL: /hocsinh/index
     */
    public function index() {
        // 1. Load Model
        $hsModel = $this->loadModel('HocSinhModel');

        // 2. Lấy thông tin từ Session
        $ma_hs  = $this->ma_hoc_sinh;
        $ma_lop = $this->ma_lop;

        // 3. Lấy dữ liệu từ Model
        // - Thông tin cá nhân
        $student_info = $hsModel->getThongTinHS($ma_hs);
        
        // - Thống kê bài tập (để hiện số bài chưa làm)
        $bai_tap_stats = $hsModel->getStatsBaiTap($ma_hs, $ma_lop);
        
        // - Lịch học tuần này
        $lich_hoc = $hsModel->getLichHocTuan($ma_lop);
        
        // - Bảng điểm chi tiết (Cái bạn vừa thêm ở Bước 1)
        $bang_diem = $hsModel->getBangDiem($ma_hs);

        // 4. Đóng gói dữ liệu để gửi sang View
        $data = [
            'user_name'     => $_SESSION['user_name'] ?? 'Học sinh',
            'student_info'  => $student_info,
            'bai_chua_nop'  => $bai_tap_stats['chua_nop'], // Dùng cho số đỏ đỏ trên dashboard
            'lich_tuan_count' => count($lich_hoc),         // Đếm số tiết học
            'lich_hoc_tuan' => $lich_hoc,
            'bang_diem'     => $bang_diem,                 // QUAN TRỌNG: Dữ liệu này sẽ đổ vào bảng
            'school_name'   => $_SESSION['school_name'] ?? 'THPT Manager'
        ];

        // 5. Gọi View
        // Lưu ý: Đảm bảo bạn đã có file views/HocSinh/dashboard.php
        $this->loadView('HocSinh/dashboard', $data);
    }

    // Trong class HocSinhController

    /**
     * TRANG BẢNG ĐIỂM CHI TIẾT
     * URL: /hocsinh/bangdiem
     */
    public function bangdiem() {
        // 1. Load Model
        $hsModel = $this->loadModel('HocSinhModel');

        // 2. Lấy thông tin
        $ma_hs = $this->ma_hoc_sinh;
        $student_info = $hsModel->getThongTinHS($ma_hs);
        $bang_diem = $hsModel->getBangDiem($ma_hs);

        // 3. Đóng gói dữ liệu
        $data = [
            'user_name'    => $_SESSION['user_name'] ?? 'Học sinh',
            'school_name'  => $_SESSION['school_name'] ?? 'THPT Manager',
            'student_info' => $student_info,
            'bang_diem'    => $bang_diem
        ];

        // 4. Gọi View (PHẢI CÓ ECHO)
        $content = $this->loadView('HocSinh/diem_so', $data);
        echo $content; // <--- Thêm dòng này mới hiện view ra được
    }

    /**
     * TRANG CHÍNH: Hiển thị danh sách phiên điểm danh
     * URL: /hocsinh/diemdanh
     */
    public function diemdanh() {
        if ($this->diemDanhHSModel === null) {
            die("Lỗi không tải được Model.");
        }
        
        $danhSach = $this->diemDanhHSModel->getDanhSachPhien($this->ma_hoc_sinh, $this->ma_lop);
        
        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Học sinh',
            'danh_sach_phien' => $danhSach
        ];

        $content = $this->loadView('HocSinh/diem_danh_hs', $data);
        echo $content;
    }

    /**
     * API: Nộp điểm danh (có hỗ trợ mật khẩu)
     * URL: /hocsinh/submitDiemDanhApi
     */
    public function submitDiemDanhApi() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            return;
        }

        $ma_phien = filter_input(INPUT_POST, 'ma_phien', FILTER_VALIDATE_INT);
        $mat_khau = trim($_POST['mat_khau'] ?? '');
        
        if (!$ma_phien) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu mã phiên.']);
            return;
        }

        // Sử dụng DiemDanhModel thay vì DiemDanhHSModel
        $diemDanhModel = $this->loadModel('DiemDanhModel');
        $result = $diemDanhModel->diemDanhHocSinh($ma_phien, $this->ma_hoc_sinh, $mat_khau);

        if ($result['success']) {
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
    }

    /**
     * API: Lấy chi tiết bài nộp (kèm ngay_nop_vietnam UTC+7)
     * URL: /hocsinh/getBaiNopChiTietApi?ma_bai_nop=XXX
     */
    public function getBaiNopChiTietApi() {
        header('Content-Type: application/json; charset=utf-8');

        $ma_bai_nop = filter_input(INPUT_GET, 'ma_bai_nop', FILTER_VALIDATE_INT);

        if (!$ma_bai_nop) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu mã bài nộp'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $baiTapModel = $this->loadModel('BaiTapModel');
        $submission = $baiTapModel->getChiTietBaiNopChoHocSinh($ma_bai_nop, $this->ma_hoc_sinh);

        if (!$submission) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy bài nộp'], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode([
            'success' => true,
            'data' => $submission
        ], JSON_UNESCAPED_UNICODE);
    }
}
?>