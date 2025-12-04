<?php
// Đảm bảo session đã được khởi động (nên đặt ở public/index.php)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class QuanTriController extends Controller {

    private $userModel;
    private $tkbModel;
    private $accountModel; 
    private $giaoVienModel;
    private $tuyenSinhModel; // <-- ĐÃ THÊM

    public function __construct() {
        // Kiểm tra quyền Quản trị
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'QuanTriVien') {
            header('Location: ' . BASE_URL . '/auth/index');
            exit;
        }
        
        // Load tất cả model cần thiết
        $this->userModel = $this->loadModel('UserModel');
        $this->tkbModel = $this->loadModel('TkbModel');
        $this->accountModel = $this->loadModel('AccountModel');
        $this->giaoVienModel = $this->loadModel('GiaoVienModel');
        $this->tuyenSinhModel = $this->loadModel('TuyenSinhModel'); // <-- ĐÃ THÊM

        // Kiểm tra xem Model có load thành công không
        if ($this->tkbModel === null || $this->accountModel === null || $this->giaoVienModel === null || $this->tuyenSinhModel === null) {
             die("Lỗi nghiêm trọng: Không thể tải một hoặc nhiều Model (Tkb/Account/GiaoVien/TuyenSinh).");
        }
    }

    /**
     * Sửa: Hàm index mặc định trỏ về Dashboard chung
     */
    public function index() {
         // Trỏ về /dashboard (nơi có các card chức năng) sẽ hợp lý hơn
        header('Location: ' . BASE_URL . '/dashboard');
        exit;
    }

    // --- CÁC HÀM XẾP THỜI KHÓA BIỂU (ĐÃ CẬP NHẬT) ---

    /**
     * URL: /quantri/xeptkb
     */
    // public function xeptkb() {
    //     $danhSachLop = $this->tkbModel->getDanhSachLop();
    //     $data = [
    //         'user_name' => $_SESSION['user_name'] ?? 'Admin',
    //         'lop_hoc' => $danhSachLop
    //     ];
    //     if (isset($_SESSION['flash_message'])) {
    //         $data['flash_message'] = $_SESSION['flash_message'];
    //         unset($_SESSION['flash_message']);
    //     }
    //     $content = $this->loadView('QuanTri/danh_sach_lop_tkb', $data);
    //     echo $content;
    // }
    /**
     * URL: /quantri/xeptkb
     * Hiển thị danh sách lớp để chọn xếp lịch
     */
    public function xeptkb() {
        // 1. Lấy ID trường từ Session hoặc CSDL
        // (Biến này giúp phân biệt Admin Trường vs Super Admin)
        $school_id = $_SESSION['admin_school_id'] ?? null;

        if (!$school_id && isset($_SESSION['user_id'])) {
            // Nếu Session chưa có, gọi Model để lấy lại cho chắc
            if (!$this->userModel) { 
                $this->userModel = $this->loadModel('UserModel'); 
            }
            $school_id = $this->userModel->getAdminSchoolId($_SESSION['user_id']);
            
            // Lưu lại vào session để dùng cho lần sau đỡ phải query lại
            $_SESSION['admin_school_id'] = $school_id;
        }

        // 2. Gọi Model lấy danh sách lớp (Có truyền school_id để lọc)
        // Nếu school_id là NULL -> Lấy hết (Super Admin)
        // Nếu school_id là 1 -> Chỉ lấy lớp trường Minh Khai
        $danhSachLop = $this->tkbModel->getDanhSachLop($school_id);

        // 3. Chuẩn bị dữ liệu gửi sang View
        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Admin',
            'lop_hoc' => $danhSachLop
        ];

        // Xử lý thông báo (Flash Message) nếu có
        if (isset($_SESSION['flash_message'])) {
            $data['flash_message'] = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
        }

        // 4. Load View
        $content = $this->loadView('QuanTri/danh_sach_lop_tkb', $data);
        echo $content;
    }

    /**
     * URL: /quantri/chiTietTkb/1
     * HOẶC: /quantri/chiTietTkb/1?date=2025-11-20
     * <-- ĐÃ CẬP NHẬT HOÀN TOÀN VỚI LOGIC HỌC KỲ -->
     */
    public function chiTietTkb($ma_lop = 0) {
        $ma_lop = (int)$ma_lop;
        if ($ma_lop <= 0) {
            header('Location: ' . BASE_URL . '/quantri/xeptkb');
            exit;
        }

        // --- Logic Xử lý Ngày (Đã có) ---
        $selected_date_str = $_GET['date'] ?? date('Y-m-d');
        try {
            $selected_date = new DateTime($selected_date_str);
        } catch (Exception $e) {
            $selected_date = new DateTime();
        }

        $day_of_week = (int)$selected_date->format('N'); // 1 = T2, ..., 7 = CN
        $start_of_week = clone $selected_date;
        $start_of_week->modify('-' . ($day_of_week - 1) . ' days'); // Lùi về Thứ 2
        
        $week_dates = [];
        $week_dates_sql = []; // Mảng ngày để query SQL
        $current_day_iterator = clone $start_of_week;
        
        for ($i = 0; $i < 6; $i++) {
            $week_dates[] = $current_day_iterator->format('d/m/Y');
            $week_dates_sql[] = $current_day_iterator->format('Y-m-d'); 
            $current_day_iterator->modify('+1 day');
        }

        $prev_week_date = (clone $start_of_week)->modify('-7 days')->format('Y-m-d');
        $next_week_date = (clone $start_of_week)->modify('+7 days')->format('Y-m-d');
        $base_url_tkb = BASE_URL . '/quantri/chiTietTkb/' . $ma_lop;
        $current_date_param = '?date=' . $selected_date->format('Y-m-d');
        
        // Lưu lại date param để dùng cho form POST (lưu/xóa)
        $_SESSION['last_date_param'] = $current_date_param;

        
        // --- LOGIC MỚI: TÌM HỌC KỲ ---
        $start_date_sql = $week_dates_sql[0]; // Ngày Thứ 2 của tuần
        $hoc_ky = $this->tkbModel->getHocKyTuNgay($start_date_sql);
        
        $ma_hoc_ky = null;
        $ten_hoc_ky = "Nghỉ (Ngoài thời gian học kỳ)"; // Mặc định là nghỉ

        if ($hoc_ky) {
            $ma_hoc_ky = $hoc_ky['ma_hoc_ky'];
            $ten_hoc_ky = $hoc_ky['ten_hoc_ky'];
        }
        // --- KẾT THÚC LOGIC MỚI ---

        // --- Cập nhật các lệnh gọi Model ---
        $tkbData = [];
        $rangBuoc = [];
        // Luôn lấy tên lớp, kể cả khi nghỉ hè
        $tenLop = $this->tkbModel->getTenLop($ma_lop); 

        if ($tenLop === 'N/A') { // Kiểm tra nếu lớp không tồn tại
             $_SESSION['flash_message'] = ['type' => 'danger', 'message' => "Không tìm thấy thông tin lớp học (ID: $ma_lop)."];
             header('Location: ' . BASE_URL . '/quantri/xeptkb');
             exit;
        }
        
        // Chỉ tải TKB nếu chúng ta đang trong 1 học kỳ
        if ($ma_hoc_ky !== null) {
            $rangBuoc = $this->tkbModel->getRangBuocLop($ma_lop, $ma_hoc_ky);
            $tkbData = $this->tkbModel->getChiTietTkbLop($ma_lop, $ma_hoc_ky);
        } else {
            // Nếu là nghỉ hè, tự tạo dữ liệu rỗng để View không bị lỗi
            $rangBuoc = [
                'ten_lop' => $tenLop,
                'phong_chinh' => 'N/A', 'gvcn' => 'N/A',
                'tong_tiet_da_xep' => 0, 'tong_tiet_ke_hoach' => 0,
                'mon_hoc' => []
            ];
        }

        $phongHocChinhID = $this->tkbModel->getPhongHocChinhID($ma_lop);
        $danhSachTatCaLop = $this->tkbModel->getDanhSachLop(); 

        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Admin',
            'ma_lop' => $ma_lop,
            'nam_hoc' => '2025-2026', // Nên lấy động
            'rang_buoc' => $rangBuoc,
            'tkb_data' => $tkbData,
            'phong_hoc_chinh_id' => $phongHocChinhID,
            'danh_sach_lop' => $danhSachTatCaLop,
            
            // Dữ liệu ngày tháng
            'selected_date' => $selected_date->format('Y-m-d'),
            'week_dates' => $week_dates,
            'prev_week_link' => $base_url_tkb . '?date=' . $prev_week_date,
            'next_week_link' => $base_url_tkb . '?date=' . $next_week_date,
            'current_date_param' => $current_date_param,

            // --- DỮ LIỆU MỚI ---
            'ma_hoc_ky' => $ma_hoc_ky, // (sẽ là null nếu nghỉ hè)
            'ten_hoc_ky' => $ten_hoc_ky
        ];
        
        if (isset($_SESSION['flash_message'])) {
            $data['flash_message'] = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
        }

        $content = $this->loadView('QuanTri/chi_tiet_tkb', $data);
        echo $content;
    }


    /**
     * URL: /quantri/luuTietHoc (POST)
     * <-- ĐÃ CẬP NHẬT HOÀN TOÀN VỚI LOGIC HỌC KỲ -->
     */
    public function luuTietHoc() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $ma_lop = filter_input(INPUT_POST, 'ma_lop', FILTER_VALIDATE_INT);
            $thu = filter_input(INPUT_POST, 'thu', FILTER_VALIDATE_INT);
            $tiet = filter_input(INPUT_POST, 'tiet', FILTER_VALIDATE_INT);
            // --- DÒNG MỚI ---
            $ma_hoc_ky = filter_input(INPUT_POST, 'ma_hoc_ky', FILTER_VALIDATE_INT);

            // Tạo link redirect có chứa ngày (lấy từ session đã lưu)
            $date_param = $_SESSION['last_date_param'] ?? ''; 
            $redirect_url = BASE_URL . '/quantri/chiTietTkb/' . ($ma_lop ?? '') . $date_param;


            if (!$ma_lop || !$thu || !$tiet || !$ma_hoc_ky) { // <-- Thêm check !$ma_hoc_ky
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Dữ liệu không hợp lệ (lớp, học kỳ, thứ, tiết).'];
                header('Location: ' . $redirect_url);
                exit;
            }

            if (isset($_POST['delete']) && $_POST['delete'] == '1') {
                // --- CẬP NHẬT HÀM ---
                $success = $this->tkbModel->xoaTietHoc($ma_lop, $ma_hoc_ky, $thu, $tiet);
                $_SESSION['flash_message'] = $success ? ['type' => 'success', 'message' => 'Đã xóa tiết học.'] : ['type' => 'danger', 'message' => 'Xóa thất bại.'];
                header('Location: ' . $redirect_url);
                exit;
            }

            if (isset($_POST['save']) && $_POST['save'] == '1') {
                $ma_phan_cong = filter_input(INPUT_POST, 'ma_phan_cong', FILTER_VALIDATE_INT);
                if (!$ma_phan_cong) {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Vui lòng chọn Môn học.'];
                    header('Location: ' . $redirect_url);
                    exit;
                }
                
                // --- CẬP NHẬT HÀM ---
                $kiemTra = $this->tkbModel->kiemTraRangBuoc($ma_lop, $ma_hoc_ky, $thu, $tiet, $ma_phan_cong);
                
                if ($kiemTra !== true) {
                    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => "Không thể lưu: " . $kiemTra];
                    header('Location: ' . $redirect_url);
                    exit;
                }

                // --- CẬP NHẬT HÀM ---
                $success = $this->tkbModel->luuTietHoc($ma_lop, $ma_hoc_ky, $thu, $tiet, $ma_phan_cong);
                $_SESSION['flash_message'] = $success ? ['type' => 'success', 'message' => 'Đã lưu tiết học.'] : ['type' => 'danger', 'message' => 'Lưu thất bại.'];
                header('Location: ' . $redirect_url);
                exit;
            }
        }
        // Redirect về trang danh sách lớp nếu có lỗi gì đó
        header('Location: ' . BASE_URL . '/quantri/xeptkb');
        exit;
    }


    /**
     * API: /quantri/getDanhSachMonHocGV/1/2/3 (lop/thu/tiet)
     * (Hàm này giữ nguyên, không cần thay đổi)
     */
    public function getDanhSachMonHocGV($ma_lop = 0, $thu = 0, $tiet = 0) {
        header('Content-Type: application/json');
        $ma_lop = (int)$ma_lop; $thu = (int)$thu; $tiet = (int)$tiet;
        if ($ma_lop <= 0 || $thu < 2 || $thu > 7 || $tiet < 1 || $tiet > 7) {
            echo json_encode(['error' => 'Thiếu thông tin (lớp, thứ, tiết).']);
            return;
        }

        $ds_mon_gv_phan_cong = $this->tkbModel->getDanhSachMonHocGV($ma_lop);
        $phong_hoc_chinh_id = $this->tkbModel->getPhongHocChinhID($ma_lop);
        $result = ['mon_hoc_gv' => []];

        foreach ($ds_mon_gv_phan_cong as $item) {
            $ma_giao_vien = $item['ma_giao_vien'];
            $ma_phan_cong = $item['ma_phan_cong'];
            $ma_phong_du_kien = $item['ma_phong_dac_biet'] ?? $phong_hoc_chinh_id;
            
            // Kiểm tra GV bận (check TẤT CẢ các học kỳ để cảnh báo)
            $gv_ban_lich = $this->tkbModel->getGVBan($ma_giao_vien);
            $is_gv_ban = isset($gv_ban_lich[$thu][$tiet]);
            
            $is_phong_ban = false;
            if ($ma_phong_du_kien !== null) {
                // Kiểm tra Phòng bận (check TẤT CẢ các học kỳ để cảnh báo)
                 $phong_ban_lich = $this->tkbModel->getPhongBan($ma_phong_du_kien);
                 $is_phong_ban = isset($phong_ban_lich[$thu][$tiet]);
            }

            $is_option_ban = $is_gv_ban || $is_phong_ban;
            $ly_do_ban = $is_gv_ban ? '(GV bận)' : ($is_phong_ban ? '(Phòng bận)' : '');
            
            $result['mon_hoc_gv'][] = [
                'ma_phan_cong' => $ma_phan_cong,
                'ten_hien_thi' => $item['ten_mon_hoc'] . ' - (GV: ' . $item['ten_giao_vien'] . ')',
                'is_ban' => $is_option_ban,
                'ly_do' => $ly_do_ban
            ];
        }
        echo json_encode($result);
    }
    
    // --- CÁC HÀM QUẢN LÝ TÀI KHOẢN (GIỮ NGUYÊN) ---

    /**
     * URL: /quantri/quanlytaikhoan
     */
    public function quanlytaikhoan() {
        if (!$this->accountModel) { die("Lỗi: AccountModel chưa được load."); }
        $accounts = $this->accountModel->getAllAccounts();
        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Admin',
            'accounts' => $accounts
        ];
        $content = $this->loadView('QuanTri/quan_ly_taikhoan', $data);
        echo $content;
    }

    /**
     * API: /quantri/updateTaiKhoan (POST)
     */
    public function updateTaiKhoan() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['ma_tai_khoan']) || empty($data['ho_ten']) || empty($data['email']) || empty($data['vai_tro'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            return;
        }
        $success = $this->accountModel->updateAccount($data);
        if ($success === true) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật tài khoản thành công!']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => is_string($success) ? $success : 'Lỗi: Không thể cập nhật tài khoản.']);
        }
    }

    /**
     * API: /quantri/deleteTaiKhoan (POST)
     */
    public function deleteTaiKhoan() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $ma_tai_khoan = filter_var($data['ma_tai_khoan'] ?? null, FILTER_VALIDATE_INT);
        if (!$ma_tai_khoan) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Mã tài khoản không hợp lệ.']);
            return;
        }
        $success = $this->accountModel->deleteAccount($ma_tai_khoan);
        if ($success === true) {
            echo json_encode(['success' => true, 'message' => 'Xóa tài khoản thành công!']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => is_string($success) ? $success : 'Lỗi: Không thể xóa tài khoản.']);
        }
    }
    
    
    // --- CÁC HÀM QUẢN LÝ GIÁO VIÊN (GIỮ NGUYÊN) ---
    
    /**
     * URL: /quantri/quanlygiaovien
     */
    public function quanlygiaovien() {
        $danhSachGiaoVien = $this->giaoVienModel->getDanhSachGiaoVien();
        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Admin',
            'giao_vien' => $danhSachGiaoVien
        ];
        $content = $this->loadView('QuanTri/quan_ly_giaovien', $data);
        echo $content;
    }

    /**
     * API: /quantri/getGiaoVienDetailsApi/{id} (GET)
     */
    public function getGiaoVienDetailsApi($ma_nguoi_dung = 0) {
        header('Content-Type: application/json');
        $ma_nguoi_dung = (int)$ma_nguoi_dung;
        if ($ma_nguoi_dung <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID Giáo viên không hợp lệ.']);
            return;
        }
        $details = $this->giaoVienModel->getGiaoVienById($ma_nguoi_dung);
        if ($details) {
            echo json_encode(['success' => true, 'data' => $details]);
        } else {
             http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy giáo viên.']);
        }
    }

    /**
     * API: /quantri/addGiaoVienApi (POST)
     */
    public function addGiaoVienApi() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['email']) || empty($data['password']) || empty($data['ho_ten']) || empty($data['so_dien_thoai']) || empty($data['ngay_sinh'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ các trường bắt buộc (*).']);
            return;
        }
        $result = $this->giaoVienModel->addGiaoVien($data);
        if ($result === true) {
            echo json_encode(['success' => true, 'message' => 'Thêm giáo viên mới thành công!']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => is_string($result) ? $result : 'Lỗi không xác định khi thêm.']);
        }
    }

    /**
     * API: /quantri/updateGiaoVienApi (POST)
     */
    public function updateGiaoVienApi() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['ma_nguoi_dung']) || empty($data['ma_tai_khoan']) || empty($data['ho_ten']) || empty($data['email'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            return;
        }
        $result = $this->giaoVienModel->updateGiaoVien($data);
        if ($result === true) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin giáo viên thành công!']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => is_string($result) ? $result : 'Lỗi không xác định khi cập nhật.']);
        }
    }
    
    /**
     * API: /quantri/deleteGiaoVienApi (POST)
     */
    public function deleteGiaoVienApi() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $ma_tai_khoan = filter_var($data['ma_tai_khoan'] ?? null, FILTER_VALIDATE_INT);
        if (!$ma_tai_khoan) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Mã tài khoản không hợp lệ.']);
            return;
        }
        $result = $this->giaoVienModel->deleteGiaoVien($ma_tai_khoan);
        if ($result === true) {
            echo json_encode(['success' => true, 'message' => 'Xóa giáo viên thành công!']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => is_string($result) ? $result : 'Lỗi không xác định khi xóa.']);
        }
    }

    /**
     * API MỚI: /quantri/addAccountApi (POST)
     */
    public function addAccountApi() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['email']) || empty($data['password']) || empty($data['ho_ten']) || empty($data['vai_tro']) || empty($data['so_dien_thoai'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ các trường bắt buộc (*).']);
            return;
        }
        
        $result = $this->accountModel->createAccount($data);
        
        if ($result === true) {
            echo json_encode(['success' => true, 'message' => 'Tạo tài khoản mới thành công!']);
        } else {
            http_response_code(400); 
            echo json_encode(['success' => false, 'message' => $result]);
        }
    }

    
    
    
    // ===================================================================
    // --- HÀM CHO QUẢN LÝ TUYỂN SINH (GIỮ NGUYÊN) ---
    // ===================================================================
    
    /**
     * URL: /quantri/quanlytuyensinh
     */
    public function quanlytuyensinh() {
        if (!$this->tuyenSinhModel) { die("Lỗi: TuyenSinhModel chưa được load."); }
        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Admin',
        ];
        $content = $this->loadView('QuanTri/quan_ly_tuyen_sinh', $data); 
        echo $content;
    }

    /**
     * API: /quantri/getDsTruongApi (GET)
     */
    public function getDsTruongApi() {
        header('Content-Type: application/json');
        if (!$this->tuyenSinhModel) { 
             http_response_code(500);
             echo json_encode(['success' => false, 'message' => 'Lỗi server: TuyenSinhModel không khả dụng.']);
             return;
        }
        $ds_truong = $this->tuyenSinhModel->getDanhSachTruong();
        echo json_encode(['success' => true, 'data' => $ds_truong]);
    }

    /**
     * API MỚI: /quantri/getDsLopApi (GET)
     */
    public function getDsLopApi() {
        header('Content-Type: application/json');
        if (!$this->tuyenSinhModel) { // Sửa check
             http_response_code(500);
             echo json_encode(['success' => false, 'message' => 'Lỗi server: TuyenSinhModel không khả dụng.']);
             return;
        }
        $ds_lop = $this->tuyenSinhModel->getDanhSachLop(1); 
        echo json_encode(['success' => true, 'data' => $ds_lop]);
    }
    
    /**
     * API: /quantri/updateChiTieuApi (POST)
     */
    public function updateChiTieuApi() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!is_array($data) || empty($data)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ (phải là object {ma_truong: chi_tieu}).']);
            return;
        }

        $successCount = 0;
        foreach ($data as $ma_truong_str => $chi_tieu) {
            $ma_truong = filter_var($ma_truong_str, FILTER_VALIDATE_INT);
            $chi_tieu = filter_var($chi_tieu, FILTER_VALIDATE_INT);
            if ($ma_truong && $chi_tieu !== false && $chi_tieu >= 0) {
                if ($this->tuyenSinhModel->updateChiTieu($ma_truong, $chi_tieu)) {
                    $successCount++;
                }
            }
        }

        if ($successCount > 0) {
            echo json_encode(['success' => true, 'message' => "Cập nhật chỉ tiêu thành công cho $successCount trường."]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Không có trường nào được cập nhật thành công.']);
        }
    }

    /**
     * API: /quantri/getDsThiSinhApi/{ma_truong?} (GET)
     */
    public function getDsThiSinhApi($ma_truong = null) {
        header('Content-Type: application/json');
        $ds_thi_sinh = $this->tuyenSinhModel->getDanhSachThiSinh($ma_truong ? (int)$ma_truong : null);
        echo json_encode(['success' => true, 'data' => $ds_thi_sinh]);
    }

    /**
     * API: /quantri/updateDiemApi (POST)
     */
    public function updateDiemApi() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu phải là array các object thí sinh.']);
            return;
        }

        $successCount = 0;
        foreach ($data as $item) {
            $ma_nguoi_dung = filter_var($item['ma_nguoi_dung'] ?? null, FILTER_VALIDATE_INT);
            $diem_toan = isset($item['diem_toan']) ? filter_var($item['diem_toan'], FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) : null;
            $diem_van = isset($item['diem_van']) ? filter_var($item['diem_van'], FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) : null;
            $diem_anh = isset($item['diem_anh']) ? filter_var($item['diem_anh'], FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) : null;

            if (!$ma_nguoi_dung) {
                error_log("Lỗi updateDiemApi: Mã thí sinh không hợp lệ cho item: " . json_encode($item));
                continue;
            }
            
            $validate = function($diem) {
                return $diem === null || ($diem >= 0 && $diem <= 10);
            };
            if (!$validate($diem_toan) || !$validate($diem_van) || !$validate($diem_anh)) {
                error_log("Lỗi updateDiemApi: Điểm không hợp lệ cho ma_nguoi_dung $ma_nguoi_dung");
                continue;
            }

            if ($this->tuyenSinhModel->updateDiemThi($ma_nguoi_dung, $diem_toan, $diem_van, $diem_anh)) {
                $successCount++;
            }
        }

        if ($successCount > 0) {
            echo json_encode(['success' => true, 'message' => "Cập nhật điểm thành công cho $successCount thí sinh."]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Không có điểm nào được cập nhật thành công.']);
        }
    }
    
    /**
     * API: /quantri/runLocAoApi (POST)
     */
    public function runLocAoApi() {
        header('Content-Type: application/json');
        $result = $this->tuyenSinhModel->runXetTuyen();
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => $result['message']]);
        } else {
             http_response_code(500);
             echo json_encode(['success' => false, 'message' => $result['message']]);
        }
    }
    
     /**
     * API: /quantri/getKetQuaLocApi (GET)
     */
    public function getKetQuaLocApi() {
        header('Content-Type: application/json');
        $ket_qua_thi_sinh = $this->tuyenSinhModel->getKetQuaThiSinh();
        $ket_qua_truong = $this->tuyenSinhModel->getKetQuaTruong();
        
        echo json_encode([
            'success' => true, 
            'thi_sinh' => $ket_qua_thi_sinh,
            'truong' => $ket_qua_truong
        ]);
    }

    /**
     * API: /quantri/getThiSinhTrungTuyenTheoTruongApi/{ma_truong} (GET)
     */
    public function getThiSinhTrungTuyenTheoTruongApi($ma_truong = 0) {
        header('Content-Type: application/json');
        $ma_truong = (int)$ma_truong;
        if ($ma_truong <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Mã trường không hợp lệ.']);
            return;
        }
        $ds_thi_sinh = $this->tuyenSinhModel->getDanhSachThiSinhTrungTuyenTheoTruong($ma_truong);
        echo json_encode(['success' => true, 'data' => $ds_thi_sinh]);
    }

    /**
     * API: /quantri/capNhatXacNhanApi (POST)
     */
    public function capNhatXacNhanApi() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!is_array($data) || empty($data['danh_sach_xac_nhan']) || !isset($data['ma_truong'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ (cần danh_sach_xac_nhan và ma_truong).']);
            return;
        }
        
        $ma_truong = (int)$data['ma_truong'];
        $success = $this->tuyenSinhModel->capNhatTrangThaiXacNhanBatch($data['danh_sach_xac_nhan'], $ma_truong);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái xác nhận thành công.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái.']);
        }
    }
    
    /**
     * API MỚI: /quantri/chotNhapHocApi (POST)
     */
    public function chotNhapHocApi() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        
        $ma_truong = filter_var($data['ma_truong'] ?? null, FILTER_VALIDATE_INT);
        $ma_lop_dich = filter_var($data['ma_lop_dich'] ?? null, FILTER_VALIDATE_INT);

        if (!$ma_truong || !$ma_lop_dich) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn trường và lớp đích hợp lệ.']);
            return;
        }

        if (!$this->tuyenSinhModel) { die("Lỗi: TuyenSinhModel chưa được load."); }
        
        $result = $this->tuyenSinhModel->chotDanhSachNhapHoc($ma_truong, $ma_lop_dich);
        
        if ($result['success']) {
            echo json_encode($result);
        } else {
            http_response_code(500);
            echo json_encode($result);
        }
    }
    
    // --- HẾT PHẦN TUYỂN SINH ---
}
?>