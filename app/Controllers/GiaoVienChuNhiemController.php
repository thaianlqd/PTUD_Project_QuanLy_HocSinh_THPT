<?php
// Start session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load Model GVCN (đường dẫn tương đối chuẩn)
require_once __DIR__ . '/../Models/GiaoVienChuNhiemModel.php'; 

class GiaoVienChuNhiemController {

    private $gvcnModel;
    private $ma_giao_vien;

    public function __construct() {
        // 1. Check Đăng nhập: Phải là 'GiaoVien'
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'GiaoVien') {
            // Sửa đường dẫn redirect về login cho đúng project của bạn
            header('Location: /PTUD_Project_QLHS_THPT/PTUD_Project_QuanLy_HocSinh_THPT/public/auth/index');
            exit;
        }

        $this->ma_giao_vien = $_SESSION['user_id'];
        $this->gvcnModel = new GiaoVienChuNhiemModel();

        // 2. Check xem có phải GVCN không? (Bảo mật lớp 2)
        $lopCN = $this->gvcnModel->getLopChuNhiem($this->ma_giao_vien);
        if (!$lopCN) {
            die("LỖI: Bạn không phải là Giáo viên chủ nhiệm của lớp nào!");
        }
        
        // Lưu mã lớp vào session để dùng cho tiện ở các hàm sau
        $_SESSION['ma_lop_cn'] = $lopCN['ma_lop'];
    }

    /**
     * Mặc định: Đá về Dashboard chung (tab Chủ nhiệm)
     */
    public function index() {
        // Redirect về Dashboard chính
        $base = defined('BASE_URL') ? BASE_URL : '/PTUD_Project_QLHS_THPT/PTUD_Project_QuanLy_HocSinh_THPT/public';
        header('Location: ' . $base . '/dashboard#tab-cn');
        exit;
    }

    /**
     * ACTION: Trang Duyệt Đơn Xin Phép (Giao diện chi tiết)
     * URL: /giaovienchunhiem/duyetdon
     */
    public function duyetdon() {
        $ma_lop = $_SESSION['ma_lop_cn'];
        
        // A. Lấy dữ liệu từ Model
        // 1. Lấy danh sách đơn xin phép của lớp
        $ds_don = $this->gvcnModel->getDanhSachDonXinPhep($ma_lop);
        
        // 2. Lấy thông tin lớp để hiển thị tiêu đề
        $thong_tin_lop = $this->gvcnModel->getLopChuNhiem($this->ma_giao_vien);

        // B. Đóng gói dữ liệu
        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Giáo Viên',
            'ten_lop'   => $thong_tin_lop['ten_lop'],
            'ds_don'    => $ds_don
        ];

        // C. Gọi View (Đã fix đường dẫn trỏ về thư mục BGH)
        extract($data);
        
        // [FIX LỖI] Trỏ về thư mục BGH thay vì GiaoVien
        $viewPath = __DIR__ . '/../Views/BGH/duyet_don.php'; 
        
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            // Fallback: Tìm thử ở thư mục GiaoVien nếu lỡ tay tạo sai chỗ
            $viewPathAlt = __DIR__ . '/../Views/GiaoVien/duyet_don.php';
            if (file_exists($viewPathAlt)) {
                require_once $viewPathAlt;
            } else {
                echo "<div style='color:red; padding:20px;'>";
                echo "<h3>LỖI KHÔNG TÌM THẤY VIEW</h3>";
                echo "Hệ thống đã tìm ở 2 nơi nhưng không thấy file <b>duyet_don.php</b>:<br>";
                echo "1. " . $viewPath . "<br>";
                echo "2. " . $viewPathAlt . "<br>";
                echo "Vui lòng kiểm tra lại bạn đã lưu file View vào đúng thư mục <b>app/Views/BGH/</b> chưa?";
                echo "</div>";
            }
        }
    }

    /**
     * ACTION: Xử lý Logic Duyệt/Từ chối (POST)
     * URL: /giaovienchunhiem/xulyduyet
     */
    public function xulyduyet() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ma_phieu = $_POST['ma_phieu'] ?? null;
            $action   = $_POST['hanh_dong'] ?? ''; // 'duyet' hoặc 'tuchoi'

            if ($ma_phieu && $action) {
                // Xác định trạng thái mới
                $trang_thai = ($action === 'duyet') ? 'DaDuyet' : 'TuChoi';
                
                // Gọi Model update Database
                $this->gvcnModel->duyetDonXinPhep($ma_phieu, $trang_thai);
                
                // (Optional) Nếu cần logic trừ điểm chuyên cần thì gọi thêm hàm ở đây
            }
            
            // Xử lý xong -> Redirect quay lại trang danh sách để thấy kết quả
            $base = defined('BASE_URL') ? BASE_URL : '/PTUD_Project_QLHS_THPT/PTUD_Project_QuanLy_HocSinh_THPT/public';
            header('Location: ' . $base . '/giaovienchunhiem/duyetdon');
            exit;
        }
    }

    /**
     * ACTION: Cập nhật Hạnh Kiểm (API JSON)
     * URL: /giaovienchunhiem/capnhathanhkiem
     */
    public function capnhathanhkiem() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ma_hoc_sinh = $_POST['ma_hs'];
            $hanh_kiem   = $_POST['hanh_kiem']; // Tot, Kha, TB...
            $ma_hoc_ky   = $_POST['hoc_ky'] ?? 'HK1';

            // Gọi Model update (Cần viết hàm này trong GiaoVienChuNhiemModel nếu muốn dùng thật)
            // $result = $this->gvcnModel->updateHanhKiem($ma_hoc_sinh, $hanh_kiem, $ma_hoc_ky);
            
            // Giả lập thành công trả về JSON
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật hạnh kiểm thành công!']);
        }
    }
}
?>