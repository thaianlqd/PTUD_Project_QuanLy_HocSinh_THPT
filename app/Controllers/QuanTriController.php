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

    // --- CÁC HÀM QUẢN LÝ TÀI KHOẢN (GIỮ NGUYÊN) ---

    /**
     * URL: /quantri/quanlytaikhoan
     */

    public function quanlytaikhoan() {
        if (!$this->accountModel) { die("Lỗi: AccountModel chưa được load."); }
        
        // Lấy trang hiện tại từ query string (mặc định trang 1)
        $current_page = (int)($_GET['page'] ?? 1);
        if ($current_page < 1) $current_page = 1;
        $limit_per_page = 10;
        
        // Lọc theo trường nếu admin đang ở cấp trường
        $school_id = $_SESSION['admin_school_id'] ?? null;
        if (!$school_id && isset($_SESSION['user_id'])) {
            // Lấy lại từ UserModel nếu chưa có trong session
            if (!$this->userModel) { $this->userModel = $this->loadModel('UserModel'); }
            $school_id = $this->userModel->getAdminSchoolId($_SESSION['user_id']);
            if ($school_id) $_SESSION['admin_school_id'] = $school_id;
        }

        // Lấy dữ liệu phân trang
        if ($school_id) {
            $accounts = $this->accountModel->getAccountsBySchoolPaginated($school_id, $current_page, $limit_per_page);
            $total_accounts = $this->accountModel->countAccountsBySchool($school_id);
        } else {
            $accounts = $this->accountModel->getAllAccountsPaginated($current_page, $limit_per_page);
            $total_accounts = $this->accountModel->countAllAccounts();
        }
        
        // Tính toán số trang
        $total_pages = ceil($total_accounts / $limit_per_page);
        if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;
        
        // Lấy danh sách vai trò khả dụng (nếu admin trường thì lọc, nếu super admin thì tất cả)
        $available_roles = $school_id ? $this->accountModel->getAvailableRolesForSchoolAdmin() : [
            'HocSinh' => 'Học Sinh',
            'PhuHuynh' => 'Phụ Huynh',
            'GiaoVien' => 'Giáo Viên',
            'BanGiamHieu' => 'Ban Giám Hiệu',
            'NhanVienSoGD' => 'Nhân Viên Sở GD',
            'ThiSinh' => 'Thí Sinh'
        ];
        
        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Admin',
            'accounts' => $accounts,
            'available_roles' => $available_roles,
            'current_page' => $current_page,
            'total_pages' => $total_pages,
            'total_accounts' => $total_accounts,
            'limit_per_page' => $limit_per_page
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
        // Lọc giáo viên theo trường nếu admin ở cấp trường
        $school_id = $_SESSION['admin_school_id'] ?? null;
        if (!$school_id && isset($_SESSION['user_id'])) {
            if (!$this->userModel) { $this->userModel = $this->loadModel('UserModel'); }
            $school_id = $this->userModel->getAdminSchoolId($_SESSION['user_id']);
            if ($school_id) $_SESSION['admin_school_id'] = $school_id;
        }
        
        $danhSachGiaoVien = $this->giaoVienModel->getDanhSachGiaoVien($school_id);
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
    // public function addAccountApi() {
    //     header('Content-Type: application/json');
    //     $data = json_decode(file_get_contents('php://input'), true);
        
    //     if (empty($data['email']) || empty($data['password']) || empty($data['ho_ten']) || empty($data['vai_tro']) || empty($data['so_dien_thoai'])) {
    //         http_response_code(400);
    //         echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ các trường bắt buộc (*).']);
    //         return;
    //     }
        
    //     // Lấy school_id từ session (nếu có)
    //     $school_id = $this->userModel->getAdminSchoolId($_SESSION['user_id'] ?? null);
        
    //     $result = $this->accountModel->createAccount($data, $school_id);
        
    //     if ($result === true) {
    //         echo json_encode(['success' => true, 'message' => 'Tạo tài khoản mới thành công!']);
    //     } else {
    //         http_response_code(400); 
    //         echo json_encode(['success' => false, 'message' => $result]);
    //     }
    // }
    public function addAccountApi() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        $result = $this->accountModel->createAccount($data);

        if ($result === true) {
            echo json_encode(['success' => true, 'message' => 'Tạo tài khoản thành công!']);
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
    // public function getDsLopApi() {
    //     header('Content-Type: application/json');
    //     if (!$this->tuyenSinhModel) { // Sửa check
    //          http_response_code(500);
    //          echo json_encode(['success' => false, 'message' => 'Lỗi server: TuyenSinhModel không khả dụng.']);
    //          return;
    //     }
    //     $ds_lop = $this->tuyenSinhModel->getDanhSachLop(1); 
    //     echo json_encode(['success' => true, 'data' => $ds_lop]);
    // }
    public function getDsLopApi() {
        header('Content-Type: application/json');
        
        $school_id = $_SESSION['admin_school_id'] ?? null;

        if (!$school_id) {
            echo json_encode([
                'success' => false, 
                'message' => 'Không xác định được trường của Admin.', 
                'data' => [] 
            ]);
            return;
        }
        
        // SỬA Ở ĐÂY: Gọi từ accountModel
        if (!$this->accountModel) {
             http_response_code(500);
             echo json_encode(['success' => false, 'message' => 'Lỗi: AccountModel chưa load.']);
             return;
        }

        // Gọi hàm vừa thêm bên AccountModel
        $ds_lop = $this->accountModel->getDanhSachLop($school_id); 
        
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