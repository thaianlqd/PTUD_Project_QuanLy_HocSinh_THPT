<?php
// Đảm bảo session đã được khởi động
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class BGHController extends Controller { // <-- Tên class là BGHController

    private $diemSoModel;
    private $ma_nguoi_dung_bgh; // Mã của BGH đang đăng nhập (chính là ma_giao_vien)

    // public function __construct() {
    //     // --- KIỂM TRA QUYỀN TRUY CẬP CỦA BGH ---
        
    //     // ✅ SỬA: Chỉ cho BGH vào (BGH có user_role = 'BanGiamHieu')
    //     if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'BanGiamHieu') {
    //         header('Location: ' . BASE_URL . '/auth/index');
    //         exit;
    //     }
        
    //     // --- Hết kiểm tra quyền ---

    //     $this->diemSoModel = $this->loadModel('DiemSoModel');
    //     if ($this->diemSoModel === null) {
    //          die("Lỗi nghiêm trọng: Không thể tải DiemSoModel.");
    //     }
        
    //     // Lấy mã người dùng (GV/BGH) từ session
    //     // (Bạn PHẢI lưu ma_nguoi_dung vào session khi họ đăng nhập thành công)
    //     $this->ma_nguoi_dung_bgh = $_SESSION['user_id'] ?? 0; 
    //     if ($this->ma_nguoi_dung_bgh == 0) {
    //         die("Lỗi: Phiên đăng nhập không hợp lệ. Không tìm thấy user_id.");
    //     }
    // }
    public function __construct() {
        // 1. Khởi động session nếu chưa có
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Lấy thông tin từ session
        $role    = $_SESSION['user_role'] ?? '';
        // Sử dụng mb_strtolower để xử lý tiếng Việt có dấu chính xác
        $chuc_vu = mb_strtolower($_SESSION['user_chuc_vu'] ?? '', 'UTF-8');

        // 3. Logic kiểm tra quyền (Đồng bộ với Dashboard)
        // - Role là 'BanGiamHieu'
        // - HOẶC Role là 'GiaoVien' NHƯNG chức vụ có chứa từ khóa BGH
        $is_bgh = ($role === 'BanGiamHieu')
               || ($role === 'GiaoVien' && (
                    str_contains($chuc_vu, 'ban giám hiệu') || 
                    str_contains($chuc_vu, 'hiệu trưởng') || 
                    str_contains($chuc_vu, 'phó hiệu') ||
                    str_contains($chuc_vu, 'bgh')
                  ));
        
        // --- DEBUG (Mở ra nếu vẫn lỗi để xem nó đang nhận giá trị gì) ---
        // echo "Role: " . $role . "<br>";
        // echo "Chuc vu (goc): " . $_SESSION['user_chuc_vu'] . "<br>";
        // echo "Chuc vu (lower): " . $chuc_vu . "<br>";
        // echo "Is BGH: " . ($is_bgh ? 'Yes' : 'No');
        // die();
        // -------------------------------------------------------------

        // 4. Nếu không phải BGH -> Đá về trang login
        if (!$is_bgh) {
            // Có thể thêm báo lỗi để biết tại sao bị đá
            // header('Location: ' . BASE_URL . '/auth/index?error=access_denied'); 
            header('Location: ' . BASE_URL . '/auth/index');
            exit;
        }

        // 5. Load Model
        $this->diemSoModel = $this->loadModel('DiemSoModel');
        if ($this->diemSoModel === null) {
            die("Lỗi nghiêm trọng: Không thể tải DiemSoModel.");
        }

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
     * ✅ SỬA: Đọc JSON từ request body thay vì $_POST
     */
    // public function xuLyPhieuDiem() {
    //     header('Content-Type: application/json');
        
    //     // ✅ SỬA: Đọc JSON từ php://input
    //     $data = json_decode(file_get_contents('php://input'), true);
        
    //     $ma_phieu = filter_var($data['ma_phieu'] ?? null, FILTER_VALIDATE_INT);
    //     $action = $data['action'] ?? ''; // 'duyet' hoặc 'tuchoi'

    //     if (!$ma_phieu || !$action) {
    //         http_response_code(400);
    //         echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    //         return;
    //     }

    //     $result = [];
        
    //     if ($action == 'duyet') {
    //         // Thực hiện duyệt - Gọi đúng tên method trong Model
    //         $result = $this->diemSoModel->duyetPhieuChinhSuaMoi($ma_phieu, $this->ma_nguoi_dung_bgh);
        
    //     } elseif ($action == 'tuchoi') {
    //         // Thực hiện từ chối
    //         $ly_do = $data['ly_do'] ?? '';
    //         if (empty(trim($ly_do))) {
    //              http_response_code(400);
    //              echo json_encode(['success' => false, 'message' => 'Vui lòng nhập lý do từ chối.']);
    //              return;
    //         }
    //         $result = $this->diemSoModel->tuChoiPhieuChinhSuaMoi($ma_phieu, $this->ma_nguoi_dung_bgh, $ly_do);
        
    //     } else {
    //         http_response_code(400);
    //         echo json_encode(['success' => false, 'message' => 'Hành động không xác định.']);
    //         return;
    //     }

    //     // Trả kết quả về cho client
    //     if ($result['success']) {
    //         echo json_encode($result);
    //     } else {
    //         http_response_code(500);
    //         echo json_encode($result);
    //     }
    // }
    public function xuLyPhieuDiem() {
        header('Content-Type: application/json');
        
        // Đọc dữ liệu JSON từ body request
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        $ma_phieu = isset($data['ma_phieu']) ? intval($data['ma_phieu']) : 0;
        $action = $data['action'] ?? ''; // 'duyet' hoặc 'tuchoi'

        if ($ma_phieu <= 0 || empty($action)) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            return;
        }

        // Gọi model xử lý (ma_nguoi_dung_bgh lấy từ session trong __construct)
        if ($action == 'duyet') {
            $result = $this->diemSoModel->duyetPhieuChinhSuaMoi($ma_phieu, $this->ma_nguoi_dung_bgh);
        } elseif ($action == 'tuchoi') {
            $ly_do = $data['ly_do'] ?? 'Không có lý do cụ thể';
            $result = $this->diemSoModel->tuChoiPhieuChinhSuaMoi($ma_phieu, $this->ma_nguoi_dung_bgh, $ly_do);
        } else {
            $result = ['success' => false, 'message' => 'Hành động không hợp lệ.'];
        }

        echo json_encode($result);
        exit;
    }
    
    // ... (Thêm các hàm khác của BGHController nếu có) ...
}
?>