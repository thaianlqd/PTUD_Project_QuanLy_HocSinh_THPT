<?php
class HocSinhControllerCN extends Controller {

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        $model = $this->loadModel('HocSinhCNModel');
        
        // Lấy ID trường từ Session
        $school_id = $_SESSION['admin_school_id'] ?? 0; 

        if ($school_id == 0) {
             header('Location: ' . BASE_URL . '/dashboard');
             exit;
        }

        $keyword = $_GET['keyword'] ?? '';

        $hocsinhList = $model->getHocSinhBySchool($school_id, $keyword);
        $data = [
            'hocsinhList' => $hocsinhList,
            'classes' => $model->getDanhSachLop($school_id),
            'school_id' => $school_id,
            // placeholder phân trang (chưa làm server-side)
            'currentPage' => 1,
            'totalPages' => 1,
            'totalCount' => count($hocsinhList)
        ];

        // Load View
        $this->loadView('Quantri/quan_ly_hoc_sinh', $data);
    }

    // --- API: THÊM HỌC SINH ---
    public function add() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['ho_ten']) || empty($data['email']) || empty($data['password']) || empty($data['ma_lop'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
            return;
        }
        
        // Thêm ma_truong từ session
        $data['ma_truong'] = $_SESSION['admin_school_id'] ?? 0;
        
        $hocSinhModel = $this->loadModel('HocSinhCNModel');
        $result = $hocSinhModel->addStudent($data); // ✅ Đổi thành addStudent
        
        if ($result === true) {
            echo json_encode(['success' => true, 'message' => 'Thêm học sinh thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => is_string($result) ? $result : 'Lỗi không xác định!']);
        }
    }

    // --- API: SỬA HỌC SINH ---
    public function update() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $model = $this->loadModel('HocSinhCNModel');

        if (empty($data['ma_hoc_sinh'])) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy ID học sinh']);
            return;
        }

        $result = $model->updateStudent($data);

        echo json_encode($result === true
            ? ['success' => true, 'message' => 'Cập nhật thành công']
            : ['success' => false, 'message' => $result]);
    }

    // --- API: XÓA HỌC SINH ---
    public function delete() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['ma_hoc_sinh'])) {
            echo json_encode(['success' => false, 'message' => 'Thiếu mã học sinh!']);
            return;
        }
        
        $hocSinhModel = $this->loadModel('HocSinhCNModel');
        $result = $hocSinhModel->deleteStudent($data['ma_hoc_sinh']); // ✅ Dùng deleteStudent
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Xóa học sinh thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Xóa thất bại!']);
        }
    }
}
?>