<?php
// 1. Start Session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. SỬA LỖI ĐƯỜNG DẪN MODEL (QUAN TRỌNG)
// __DIR__ là thư mục hiện tại (app/Controllers)
// /../ nghĩa là lùi ra ngoài 1 cấp (về thư mục app)
// Sau đó đi vào thư mục Models
require_once __DIR__ . '/../Models/PhuHuynhModel.php'; 

class PhuHuynhController {

    private $phModel;
    private $ma_phu_huynh;

    public function __construct() {
        // KIỂM TRA ĐĂNG NHẬP
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'PhuHuynh' || !isset($_SESSION['user_id'])) {
            // Sửa đường dẫn chuyển hướng về Login (tùy chỉnh theo project của bạn)
            $base_url = '/PTUD_Project_QLHS_THPT/PTUD_Project_QuanLy_HocSinh_THPT/public';
            header('Location: ' . $base_url . '/auth/index');
            exit;
        }

        $this->ma_phu_huynh = $_SESSION['user_id'];
        $this->phModel = new PhuHuynhModel();
    }

    public function index() {
        $this->dashboard();
    }

    public function dashboard() {
        // A. LẤY DỮ LIỆU TỪ MODEL

        

        $hoc_sinh_info = $this->phModel->getHocSinhInfo($this->ma_phu_huynh);
        $school_name   = $this->phModel->getTenTruongCuaCon($this->ma_phu_huynh);
        $hoa_don_count = $this->phModel->getHoaDonCount($this->ma_phu_huynh);
        $phieu_vang_count = $this->phModel->getPhieuVangCount($this->ma_phu_huynh);
        $bang_diem     = $this->phModel->getBangDiem($this->ma_phu_huynh);

        // B. ĐÓNG GÓI DATA
        $data = [
            'user_name'        => $_SESSION['user_name'] ?? 'Phụ Huynh',
            'school_name'      => $school_name,
            'hoc_sinh_info'    => $hoc_sinh_info,
            'hoa_don_count'    => $hoa_don_count,
            'phieu_vang_count' => $phieu_vang_count,
            'bang_diem'        => $bang_diem
        ];

        // C. GỌI VIEW (SỬA LẠI ĐƯỜNG DẪN VIEW)
        extract($data); 
        
        // Thử tìm file view theo cấu trúc app/Views
        $viewPath = __DIR__ . '/../Views/PhuHuynh/dashboard.php';
        
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            // Fallback: Thử tìm ở thư mục gốc views (nếu cấu trúc khác)
            $viewPath2 = __DIR__ . '/../../views/PhuHuynh/dashboard.php';
            if (file_exists($viewPath2)) {
                require_once $viewPath2;
            } else {
                echo "LỖI: Không tìm thấy file View tại: <br> - $viewPath <br> - $viewPath2";
            }
        }
    }

    public function hoso() {
        // A. LẤY DỮ LIỆU
        $hoc_sinh_info  = $this->phModel->getHocSinhInfo($this->ma_phu_huynh);
        $school_name    = $this->phModel->getTenTruongCuaCon($this->ma_phu_huynh);
        $student_detail = $this->phModel->getThongTinChiTietCon($this->ma_phu_huynh);

        // B. ĐÓNG GÓI DATA
        $data = [
            'user_name'      => $_SESSION['user_name'] ?? 'Phụ Huynh',
            'school_name'    => $school_name,
            'hoc_sinh_info'  => $hoc_sinh_info,
            'student_detail' => $student_detail
        ];

        // C. GỌI VIEW
        extract($data);

        $viewPath = __DIR__ . '/../Views/PhuHuynh/ho_so.php';
        
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            $viewPath2 = __DIR__ . '/../../views/PhuHuynh/ho_so.php';
            if (file_exists($viewPath2)) {
                require_once $viewPath2;
            } else {
                echo "LỖI: Không tìm thấy file View tại: <br> - $viewPath <br> - $viewPath2";
            }
        }
    }

    /**
     * TRANG XIN PHÉP VẮNG
     * URL: /phuhuynh/xinphep
     */
    public function xinphep() {
        // 1. Xử lý khi người dùng bấm nút GỬI ĐƠN (POST)
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ngay_bd = $_POST['ngay_bat_dau'] ?? '';
            $ngay_kt = $_POST['ngay_ket_thuc'] ?? '';
            $ly_do   = trim($_POST['ly_do'] ?? '');

            if (empty($ngay_bd) || empty($ngay_kt) || empty($ly_do)) {
                $message = '<div class="alert alert-danger">Vui lòng điền đầy đủ thông tin!</div>';
            } elseif (strtotime($ngay_bd) > strtotime($ngay_kt)) {
                $message = '<div class="alert alert-danger">Ngày kết thúc phải sau ngày bắt đầu!</div>';
            } else {
                // Gọi Model để lưu
                $result = $this->phModel->taoDonXinPhep($this->ma_phu_huynh, $ngay_bd, $ngay_kt, $ly_do);
                if ($result) {
                    $message = '<div class="alert alert-success">Gửi đơn thành công! Đang chờ GVCN duyệt.</div>';
                } else {
                    $message = '<div class="alert alert-danger">Lỗi hệ thống, vui lòng thử lại sau.</div>';
                }
            }
        }

        // 2. Lấy dữ liệu hiển thị (Sidebar + Lịch sử đơn)
        $hoc_sinh_info = $this->phModel->getHocSinhInfo($this->ma_phu_huynh);
        $lich_su_don   = $this->phModel->getLichSuXinPhep($this->ma_phu_huynh);

        // 3. Đóng gói data
        $data = [
            'user_name'     => $_SESSION['user_name'] ?? 'Phụ Huynh',
            'hoc_sinh_info' => $hoc_sinh_info,
            'lich_su_don'   => $lich_su_don,
            'message'       => $message // Thông báo lỗi/thành công
        ];

        // 4. Gọi View
        extract($data);
        // Nhớ tạo file view này ở bước 3 nhé
        require_once __DIR__ . '/../Views/PhuHuynh/xin_phep.php';
    }
    
}
?>