<?php
class QuanLyTuyenSinhController { // Chạy độc lập, không cần extends Controller
    private $model;

    public function __construct() {
        // -----------------------------------------------------------
        // 1. TỰ ĐỘNG LOAD MODEL (Fix lỗi đường dẫn tuyệt đối)
        // -----------------------------------------------------------
        
        // __DIR__ là thư mục chứa file này (app/controllers)
        // dirname(__DIR__) lùi ra 1 cấp là thư mục (app)
        $appDir = dirname(__DIR__); 
        $modelPath = $appDir . '/models/TuyenSinhModel.php';
        
        if (file_exists($modelPath)) {
            require_once $modelPath;
            $this->model = new TuyenSinhModel();
        } else {
            // In ra đường dẫn lỗi để dễ debug
            die("Lỗi nghiêm trọng: Không tìm thấy file Model!<br>Hệ thống đang tìm tại: <strong>$modelPath</strong><br>Vui lòng kiểm tra lại file TuyenSinhModel.php.");
        }
        
        // -----------------------------------------------------------
        // 2. CHECK QUYỀN SUPER ADMIN
        // -----------------------------------------------------------
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Logic: Phải là QuanTriVien VÀ (ma_truong là null hoặc rỗng)
        $isSuperAdmin = isset($_SESSION['user_role']) 
                        && $_SESSION['user_role'] === 'QuanTriVien' 
                        && (empty($_SESSION['admin_school_id']));
        
        if (!$isSuperAdmin) {
            // Check nếu URL chứa 'Api' (tất cả API endpoint đều có từ này)
            $isApiCall = strpos($_SERVER['REQUEST_URI'], 'Api') !== false;
            
            if ($isApiCall) {
                // Trả JSON 403 cho API call
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Truy cập bị từ chối: Chỉ Super Admin mới được phép.']); 
                exit;
            }
            
            // Nếu truy cập trực tiếp trình duyệt
            die("<h1>Truy cập bị từ chối</h1><p>Chức năng này chỉ dành cho Tài khoản Sở Giáo Dục (Super Admin).</p><a href='/'>Quay lại trang chủ</a>");
        }
    }

    // -----------------------------------------------------------
    // 3. HÀM LOAD VIEW (Thay thế $this->loadView cũ)
    // -----------------------------------------------------------
    private function renderView($viewPath, $data = []) {
        if (!empty($data)) {
            extract($data); // Biến mảng thành biến lẻ
        }

        // Tạo đường dẫn tuyệt đối tới View
        $appDir = dirname(__DIR__);
        $fullPath = $appDir . '/views/' . $viewPath . '.php';
        
        if (file_exists($fullPath)) {
            require_once $fullPath;
        } else {
            die("Lỗi nghiêm trọng: Không tìm thấy file View!<br>Hệ thống đang tìm tại: <strong>$fullPath</strong>");
        }
    }

    // ===========================================================
    // PHẦN 1: GIAO DIỆN CHÍNH (Single Page)
    // ===========================================================
    public function index() {
        // Load file view chính nằm ở app/views/SuperAdmin/quan_ly_tuyen_sinh.php
        $this->renderView('SuperAdmin/quan_ly_tuyen_sinh', []);
    }

    // ===========================================================
    // PHẦN 2: API XỬ LÝ DỮ LIỆU (Trả về JSON)
    // ===========================================================

    // Helper: Trả về JSON và kết thúc
    private function sendJson($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // API 1: Lấy danh sách trường & chỉ tiêu
    public function getDsTruongApi() {
        $data = $this->model->getDanhSachTruong();
        $this->sendJson(['success' => true, 'data' => $data]);
    }

    // API 2: Cập nhật chỉ tiêu tuyển sinh
    public function updateChiTieuApi() {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) {
            $this->model->updateChiTieuBatch($input);
            $this->sendJson(['success' => true, 'message' => 'Đã cập nhật chỉ tiêu thành công!']);
        }
        $this->sendJson(['success' => false, 'message' => 'Dữ liệu gửi lên không hợp lệ']);
    }

    // API 3: Lấy danh sách thí sinh theo trường (Để nhập điểm)
    public function getDsThiSinhApi($ma_truong) {
        // Lấy ma_truong từ URL (nếu routing hỗ trợ) hoặc param
        // Ở đây giả sử $ma_truong được truyền vào method
        // Nếu router của bác không truyền tham số, bác phải lấy từ $_GET hoặc URI
        if (empty($ma_truong)) {
             // Fallback: Cố gắng lấy từ URL nếu tham số rỗng (tùy router)
             $segments = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
             $ma_truong = end($segments);
        }

        $data = $this->model->getDanhSachThiSinhByTruong($ma_truong);
        $this->sendJson(['success' => true, 'data' => $data]);
    }

    // API 4: Lưu điểm thi
    public function updateDiemApi() {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) {
            $this->model->updateDiemBatch($input);
            $this->sendJson(['success' => true, 'message' => 'Đã lưu điểm thi thành công!']);
        }
        $this->sendJson(['success' => false, 'message' => 'Lỗi dữ liệu đầu vào']);
    }

    // API 5: Chạy thuật toán Lọc Ảo (Reset & Tính lại)
    // public function runLocAoApi() {
    //     try {
    //         $this->model->runLocAo();
    //         $this->sendJson(['success' => true, 'message' => 'Đã chạy lọc ảo thành công! Kết quả mới đã được tạo.']);
    //     } catch (Exception $e) {
    //         $this->sendJson(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    //     }
    // }
    // API 5: Chạy thuật toán Lọc Ảo
    // public function runLocAoApi() {
    //     try {
    //         // Lấy tham số mode từ body (json)
    //         $input = json_decode(file_get_contents('php://input'), true);
    //         $mode = $input['mode'] ?? 'reset'; // Mặc định là reset nếu không truyền

    //         $resetAll = ($mode === 'reset'); // True nếu reset, False nếu update

    //         $this->model->runLocAo($resetAll);
            
    //         $msg = $resetAll 
    //             ? 'Đã chạy Lọc ảo mới (Reset toàn bộ) thành công!' 
    //             : 'Đã cập nhật Lọc ảo (Giữ nguyên các hồ sơ đã xác nhận) thành công!';

    //         $this->sendJson(['success' => true, 'message' => $msg]);
    //     } catch (Exception $e) {
    //         $this->sendJson(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    //     }
    // }
    public function runLocAoApi() {
        try {
            // 1. Lấy dữ liệu từ Client
            $input = json_decode(file_get_contents('php://input'), true);
            
            // 2. Lấy chế độ: 'reset' hoặc 'normal' (hoặc 'update')
            // Nếu không gửi gì lên thì mặc định là 'normal' cho an toàn (tránh lỡ tay reset nhầm)
            $mode = isset($input['mode']) ? $input['mode'] : 'normal'; 

            // 3. Truyền thẳng chuỗi 'reset' hoặc 'normal' xuống Model
            // Model sẽ tự so sánh if ($mode == 'reset')
            $result = $this->model->runLocAo($mode);
            
            // 4. Trả về kết quả từ Model (Model đã return sẵn mảng ['success' => ..., 'message' => ...])
            $this->sendJson($result);

        } catch (Exception $e) {
            $this->sendJson(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }


    // API: Lấy dữ liệu cho tab "Danh sách Trượt & Điểm Chuẩn"
    public function getDuLieuTruotVaDiemChuanApi() {
        try {
            $dsTruot = $this->model->getDanhSachTruot();
            $diemChuan = $this->model->getBangDiemChuan();
            
            $this->sendJson([
                'success' => true, 
                'ds_truot' => $dsTruot,
                'bang_diem_chuan' => $diemChuan
            ]);
        } catch (Exception $e) {
            $this->sendJson(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    // API 6: Lấy kết quả Lọc Ảo (Toàn cục)
    public function getKetQuaLocApi() {
        $ts = $this->model->getKetQuaLoc();
        $tr = $this->model->getDanhSachTruong();
        $this->sendJson(['success' => true, 'thi_sinh' => $ts, 'truong' => $tr]);
    }

    // API 7: Lấy danh sách Lớp 10 (Để chốt hồ sơ)
    public function getDsLopApi() {
        $data = $this->model->getDsLopKhoi10();
        $this->sendJson(['success' => true, 'data' => $data]);
    }

    // API 8: Lấy thí sinh trúng tuyển theo trường (Để duyệt)
    public function getThiSinhTrungTuyenTheoTruongApi($ma_truong) {
        if (empty($ma_truong)) {
             $segments = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
             $ma_truong = end($segments);
        }
        
        $data = $this->model->getThiSinhTrungTuyenTheoTruong($ma_truong);
        $this->sendJson(['success' => true, 'data' => $data]);
    }

    // API 9: Lưu trạng thái Xác nhận/Từ chối nhập học
    public function capNhatXacNhanApi() {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['danh_sach_xac_nhan']) && isset($input['ma_truong'])) {
            $this->model->updateTrangThaiXacNhanBatch($input['danh_sach_xac_nhan'], $input['ma_truong']);
            $this->sendJson(['success' => true, 'message' => 'Đã lưu trạng thái xác nhận hồ sơ.']);
        }
        $this->sendJson(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    }

    // API 10: Chốt danh sách (Chuyển thí sinh thành học sinh chính thức)
    public function chotNhapHocApi() {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['ma_truong']) && isset($input['ma_lop_dich'])) {
            try {
                $count = $this->model->chotNhapHoc($input['ma_truong'], $input['ma_lop_dich']);
                $this->sendJson(['success' => true, 'message' => "Thành công! Đã tạo hồ sơ học sinh cho $count em vào lớp."]);
            } catch (Exception $e) {
                $this->sendJson(['success' => false, 'message' => 'Lỗi khi chốt: ' . $e->getMessage()]);
            }
        }
        $this->sendJson(['success' => false, 'message' => 'Thiếu thông tin trường hoặc lớp đích']);
    }

    public function getDsTruongThcsApi() {
        $model = new TuyenSinhModel();
        $truongs = $model->getDsTruongThcs();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $truongs]);
    }

    public function getDsThiSinhByTruongThcsApi() {
        header('Content-Type: application/json');
        try {
            // ✅ Đọc từ POST body
            $input = json_decode(file_get_contents('php://input'), true);
            $truong_thcs = $input['truong_thcs'] ?? null;
            
            if (!$truong_thcs) {
                throw new Exception('Thiếu tham số truong_thcs');
            }
            
            $model = new TuyenSinhModel();
            $dsThiSinh = $model->getDsThiSinhByTruongThcs($truong_thcs);
            echo json_encode(['success' => true, 'data' => $dsThiSinh]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }


    // API 11: Lấy danh sách thí sinh chờ xếp lớp theo Tổ hợp môn
    public function getThiSinhChoXepLopApi() {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['ma_truong']) && isset($input['ma_to_hop'])) {
            $data = $this->model->getThiSinhChoXepLop($input['ma_truong'], $input['ma_to_hop']);
            $this->sendJson(['success' => true, 'data' => $data]);
        }
        $this->sendJson(['success' => false, 'message' => 'Thiếu thông tin']);
    }

    // API 11: Lấy danh sách học sinh chờ xếp lớp
    public function getHocSinhChoXepLopApi() {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['ma_truong'])) {
            $data = $this->model->getHocSinhChoXepLopByTruong($input['ma_truong']);
            $this->sendJson(['success' => true, 'data' => $data]);
        }
        $this->sendJson(['success' => false, 'message' => 'Thiếu mã trường']);
    }

    // API 12: Thực hiện xếp lớp
    public function thucHienXepLopApi() {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['ma_lop']) && isset($input['danh_sach_ma_hoc_sinh'])) {
            try {
                $count = $this->model->thucHienXepLop($input['ma_lop'], $input['danh_sach_ma_hoc_sinh']);
                $this->sendJson(['success' => true, 'message' => "Đã xếp thành công $count học sinh vào lớp!"]);
            } catch (Exception $e) {
                $this->sendJson(['success' => false, 'message' => $e->getMessage()]);
            }
        }
        $this->sendJson(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    }

    // API: Phân lớp tự động
    public function autoPhanLopApi() {
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['ma_truong']) && isset($input['danh_sach_hs'])) {
            $result = $this->model->autoPhanLop($input['ma_truong'], $input['danh_sach_hs']);
            $this->sendJson($result);
        }
        $this->sendJson(['success' => false, 'message' => 'Thiếu dữ liệu']);
    }

    // API 13: Lấy danh sách học sinh trong lớp
    public function getDsHocSinhTrongLopApi() {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['ma_lop'])) {
            $data = $this->model->getDsHocSinhTrongLop($input['ma_lop']);
            $this->sendJson(['success' => true, 'data' => $data]);
        }
        $this->sendJson(['success' => false, 'message' => 'Thiếu mã lớp']);
    }


    
}
?>