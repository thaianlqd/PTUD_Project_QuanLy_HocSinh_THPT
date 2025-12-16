<?php
class QuanLyTinTucController {
    private $model;

    public function __construct() {
        // Load Model
        $modelPath = dirname(__DIR__) . '/models/TinTucModel.php';
        if (!file_exists($modelPath)) {
            die(json_encode(['success' => false, 'message' => 'Lỗi: Không tìm thấy file TinTucModel.php']));
        }
        require_once $modelPath;
        
        try {
            $this->model = new TinTucModel();
        } catch (Exception $e) {
            die(json_encode(['success' => false, 'message' => 'Lỗi kết nối CSDL: ' . $e->getMessage()]));
        }
        
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function index() {
        require_once dirname(__DIR__) . '/views/SuperAdmin/quan_ly_tin_tuc.php';
    }

    public function getListApi() {
        try {
            $data = $this->model->getAllBaiViet();
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function addApi() {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) throw new Exception("Không nhận được dữ liệu");

            // --- LOGIC MỚI: LẤY ID SUPER ADMIN ---
            
            // Cách 1: Lấy từ Session (Nếu bác đã làm chức năng đăng nhập hoàn chỉnh)
            $userId = $_SESSION['user_id'] ?? null;

            // Cách 2: Nếu đang test hoặc session chưa chuẩn, lấy cứng ID = 1 (Vì trong ảnh bác gửi, Super Admin có ma_qtv = 1)
            if (!$userId) {
                $userId = 1; 
            }

            // Kiểm tra xem ID này có tồn tại trong bảng 'nguoi_dung' không để tránh lỗi
            // (Giả sử ID 1 luôn tồn tại vì đó là Super Admin mặc định)
            
            $input['ma_nguoi_dung'] = $userId; 
            
            $result = $this->model->addBaiViet($input);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Thêm bài viết thành công!']);
            } else {
                throw new Exception("Lỗi SQL khi lưu bài viết.");
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    public function updateApi() {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if ($input && isset($input['ma_bai_viet'])) {
                $id = $input['ma_bai_viet'];
                $result = $this->model->updateBaiViet($id, $input);
                echo json_encode(['success' => true, 'message' => 'Cập nhật thành công!']);
            } else {
                throw new Exception("Thiếu mã bài viết");
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function deleteApi() {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if ($input && isset($input['id'])) {
                $this->model->deleteBaiViet($input['id']);
                echo json_encode(['success' => true, 'message' => 'Đã xóa thành công!']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
?>