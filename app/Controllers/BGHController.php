<?php
// Đảm bảo session đã được khởi động
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class BGHController extends Controller { // <-- Tên class là BGHController

    private $diemSoModel;
    private $ma_nguoi_dung_bgh; // Mã của BGH đang đăng nhập (chính là ma_giao_vien)

    public function __construct() {
        // --- KIỂM TRA QUYỀN TRUY CẬP CỦA BGH ---
        
        // 1. Phải đăng nhập với vai trò GiaoVien
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'GiaoVien') {
            header('Location: ' . BASE_URL . '/auth/index');
            exit;
        }
        
        // 2. Phải là BGH
        // TODO: Bạn cần lấy 'user_chuc_vu' từ CSDL lúc đăng nhập và lưu vào Session
        // Ví dụ: $_SESSION['user_chuc_vu'] = 'BanGiamHieu'
        // if (!isset($_SESSION['user_chuc_vu']) || $_SESSION['user_chuc_vu'] != 'BanGiamHieu') {
        //     echo "Tài khoản của bạn không có quyền truy cập chức năng này.";
        //     exit;
        // }
        
        // --- Hết kiểm tra quyền ---

        $this->diemSoModel = $this->loadModel('DiemSoModel');
        if ($this->diemSoModel === null) {
             die("Lỗi nghiêm trọng: Không thể tải DiemSoModel.");
        }
        
        // Lấy mã người dùng (GV/BGH) từ session
        // (Bạn PHẢI lưu ma_nguoi_dung vào session khi họ đăng nhập thành công)
        $this->ma_nguoi_dung_bgh = $_SESSION['user_id'] ?? 0; 
        if ($this->ma_nguoi_dung_bgh == 0) {
            die("Lỗi: Phiên đăng nhập không hợp lệ. Không tìm thấy user_id.");
        }
    }
    
    /**
     * Trang chính: Hiển thị danh sách phiếu (ĐÃ NÂNG CẤP CÓ LỌC)
     * URL: /bgh/duyetdiem
     * URL (Filter): /bgh/duyetdiem?trang_thai=DaDuyet
     */
    public function duyetdiem() {
        // 1. Lấy trạng thái lọc từ URL, mặc định là 'ChoDuyet'
        $trang_thai = $_GET['trang_thai'] ?? 'ChoDuyet';

        // 2. (Bảo mật) Chỉ cho phép các giá trị đã định
        $allowed_filters = ['ChoDuyet', 'DaDuyet', 'TuChoi', 'TatCa'];
        if (!in_array($trang_thai, $allowed_filters)) {
            $trang_thai = 'ChoDuyet'; // Quay về mặc định nếu nhập bậy
        }

        // 3. Gọi Model với bộ lọc
        $danhSachPhieu = $this->diemSoModel->getDanhSachPhieu($trang_thai);
        
        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Ban Giám Hiệu',
            'danh_sach_phieu' => $danhSachPhieu,
            'filter_trang_thai' => $trang_thai // <-- Truyền trạng thái lọc về View
        ];

        // Tải view
        $content = $this->loadView('BGH/duyet_diem', $data);
        echo $content;
    }
    
    /**
     * API: Xử lý hành động Duyệt / Từ chối
     * URL: /bgh/xuLyPhieuDiem (POST)
     */
    public function xuLyPhieuDiem() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        $ma_phieu = filter_var($data['ma_phieu'] ?? null, FILTER_VALIDATE_INT);
        $action = $data['action'] ?? ''; // 'duyet' hoặc 'tuchoi'

        if (!$ma_phieu || !$action) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            return;
        }

        $result = [];
        
        if ($action == 'duyet') {
            // Thực hiện duyệt
            $result = $this->diemSoModel->duyetPhieu($ma_phieu, $this->ma_nguoi_dung_bgh);
        
        } elseif ($action == 'tuchoi') {
            // Thực hiện từ chối
            $ly_do = $data['ly_do'] ?? '';
            if (empty($ly_do)) {
                 http_response_code(400);
                 echo json_encode(['success' => false, 'message' => 'Vui lòng nhập lý do từ chối.']);
                 return;
            }
            $result = $this->diemSoModel->tuChoiPhieu($ma_phieu, $this->ma_nguoi_dung_bgh, $ly_do);
        
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Hành động không xác định.']);
            return;
        }

        // Trả kết quả về cho client
        if ($result['success']) {
            echo json_encode($result);
        } else {
            http_response_code(500);
            echo json_encode($result);
        }
    }
    
    // ... (Thêm các hàm khác của BGHController nếu có) ...
}
?>