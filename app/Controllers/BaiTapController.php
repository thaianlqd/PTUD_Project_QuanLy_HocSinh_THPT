<?php
// Đảm bảo session đã được khởi động
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class BaiTapController extends Controller {

    private $baiTapModel;
    private $ma_hoc_sinh; // Lưu mã người dùng (học sinh) đang đăng nhập

    public function __construct() {
        // (Giả sử bạn đã thêm hàm is_ajax() vào file Controller.php cha)
        $is_logged_in = isset($_SESSION['user_role']) && 
                        $_SESSION['user_role'] == 'HocSinh' && 
                        isset($_SESSION['user_id']);

        if (!$is_logged_in) {
            if ($this->is_ajax()) { 
                // Nếu là AJAX, trả về lỗi JSON
                http_response_code(401); // Unauthorized
                echo json_encode(['success' => false, 'message' => 'Phiên đăng nhập đã hết hạn. Vui lòng tải lại trang và đăng nhập lại.']);
                exit;
            } else { 
                // Nếu là truy cập web, chuyển hướng
                $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'] ?? BASE_URL . '/baitap';
                header('Location: ' . BASE_URL . '/auth/index');
                exit;
            }
        }

        // Lấy mã người dùng (học sinh) từ session
        $this->ma_hoc_sinh = $_SESSION['user_id'];

        // Load BaiTapModel
        $this->baiTapModel = $this->loadModel('BaiTapModel');
        if ($this->baiTapModel === null) {
            die("Lỗi: Không thể tải tài nguyên cần thiết (BaiTapModel).");
        }
    }

    /**
     * Hiển thị trang quản lý bài tập chính cho học sinh
     * URL: /baitap/index (hoặc /baitap)
     */
    public function index() {
        // Lấy danh sách bài tập từ Model
        $danhSachBaiTap = $this->baiTapModel->getDanhSachBaiTap($this->ma_hoc_sinh);

        // Chuẩn bị dữ liệu cho View
        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Học Sinh', // Lấy tên từ session nếu có
            'assignments' => $danhSachBaiTap // Truyền danh sách bài tập vào view
        ];

        // Load View và hiển thị
        $content = $this->loadView('HocSinh/quan_ly_bai_tap', $data);
        echo $content;
    }

     /**
     * API: Lấy chi tiết bài tập (dùng để load modal hoặc trang làm bài)
     * URL: /baitap/getChiTiet/{id} (GET)
     */
    public function getChiTiet($ma_bai_tap_str = '') {
         header('Content-Type: application/json'); // Luôn trả về JSON

         $ma_bai_tap = filter_var($ma_bai_tap_str, FILTER_VALIDATE_INT);

         if (!$ma_bai_tap) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'Mã bài tập không hợp lệ.']);
            return;
         }

        // Gọi hàm getChiTietBaiTap từ Model, truyền cả ma_hoc_sinh
        $assignmentDetails = $this->baiTapModel->getChiTietBaiTap($ma_bai_tap, $this->ma_hoc_sinh);

         if ($assignmentDetails) {
             echo json_encode(['success' => true, 'assignment' => $assignmentDetails]);
         } else {
             http_response_code(404); // Not Found
             echo json_encode(['success' => false, 'message' => 'Không tìm thấy bài tập hoặc bạn không có quyền truy cập.']);
         }
    }

    /**
     * API MỚI: Bắt đầu làm bài trắc nghiệm (Khởi động timer)
     * URL: /baitap/batDauLamBai/{id} (GET)
     */
    public function batDauLamBai($ma_bai_tap_str = '') {
        header('Content-Type: application/json');

        $ma_bai_tap = filter_var($ma_bai_tap_str, FILTER_VALIDATE_INT);

        if (!$ma_bai_tap) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Mã bài tập không hợp lệ.']);
            return;
        }

        $success = $this->baiTapModel->batDauLamBai($ma_bai_tap, $this->ma_hoc_sinh);

        if ($success) {
            // Lấy lại chi tiết để trả gio_bat_dau mới
            $details = $this->baiTapModel->getChiTietBaiTap($ma_bai_tap, $this->ma_hoc_sinh);
            echo json_encode(['success' => true, 'gio_bat_dau' => $details['gio_bat_dau_lam_bai'] ?? NOW()]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi khởi động bài làm.']);
        }
    }

    // --- API MỚI ĐỂ XEM LẠI/HỦY BÀI NỘP ---
    /**
     * API: Lấy chi tiết bài ĐÃ NỘP
     * URL: /baitap/getBaiNopChiTiet/{id} (GET)
     */
    public function getBaiNopChiTiet($ma_bai_tap_str = '') {
        header('Content-Type: application/json');
        $ma_bai_tap = filter_var($ma_bai_tap_str, FILTER_VALIDATE_INT);

        if (!$ma_bai_tap) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Mã bài tập không hợp lệ.']);
            return;
        }

        // 1. Lấy thông tin bài nộp (nội dung, file, điểm...)
        $submission_data = $this->baiTapModel->getBaiNopChiTiet($ma_bai_tap, $this->ma_hoc_sinh);
        
        if ($submission_data) {
             // 2. Lấy cả thông tin chung của bài tập (tên, loại, câu hỏi TN nếu có)
            $assignment_data = $this->baiTapModel->getChiTietBaiTap($ma_bai_tap, $this->ma_hoc_sinh);
            
            echo json_encode([
                'success' => true, 
                'submission' => $submission_data, // Thông tin bài nộp (file, text, điểm)
                'assignment' => $assignment_data   // Thông tin bài tập (tên, loại, câu hỏi)
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy bài đã nộp.']);
        }
    }

    /**
     * API: Hủy bài đã nộp
     * URL: /baitap/huyBaiNop (POST)
     * Dữ liệu POST: ma_bai_tap (JSON)
     */
    public function huyBaiNop() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            return;
        }

        $json_data = json_decode(file_get_contents('php://input'), true);
        $ma_bai_tap = filter_var($json_data['ma_bai_tap'] ?? null, FILTER_VALIDATE_INT);

        if (!$ma_bai_tap) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu mã bài tập.']);
            return;
        }
        
        // Kiểm tra xem bài đã bị chấm chưa
        $status = $this->baiTapModel->getTrangThaiBaiNopPublic($ma_bai_tap, $this->ma_hoc_sinh);
        if (str_contains($status, 'Hoàn Thành')) { // 'Hoàn Thành' nghĩa là đã chấm
            http_response_code(403); // Forbidden
            echo json_encode(['success' => false, 'message' => 'Không thể hủy bài đã được chấm điểm.']);
            return;
        }

        // Gọi Model để xóa
        $success = $this->baiTapModel->huyBaiNop($ma_bai_tap, $this->ma_hoc_sinh);

        if ($success) {
            // Lấy lại trạng thái mới (sẽ là 'Chưa Làm' hoặc 'Quá Hạn')
            $newStatus = $this->baiTapModel->getTrangThaiBaiNopPublic($ma_bai_tap, $this->ma_hoc_sinh);
            echo json_encode(['success' => true, 'message' => 'Đã hủy bài nộp. Bạn có thể nộp lại.', 'newStatus' => $newStatus]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi: Không thể hủy bài nộp.']);
        }
    }
    // --- HẾT API MỚI ---

    /**
     * API: Xử lý nộp bài trắc nghiệm (SỬA: Dùng getTrangThaiSauNop)
     */
    public function nopBaiTracNghiem() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            return;
        }

        $json_data = json_decode(file_get_contents('php://input'), true);
        $ma_bai_tap = filter_var($json_data['ma_bai_tap'] ?? null, FILTER_VALIDATE_INT);
        $answersJson = isset($json_data['answers']) ? json_encode($json_data['answers']) : null; 

        if (!$ma_bai_tap || !$answersJson || json_decode($answersJson) === null) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ (thiếu mã bài tập hoặc câu trả lời).']);
            return;
        }

        // SỬA: Gọi hàm chấm điểm mới, trả diem_so
        $result = $this->baiTapModel->luuVaChamDiemTracNghiem($ma_bai_tap, $this->ma_hoc_sinh, $answersJson);
        if ($result['success']) {
            $newStatus = $this->baiTapModel->getTrangThaiSauNop($ma_bai_tap, $this->ma_hoc_sinh);
            echo json_encode(['success' => true, 'message' => $result['message'], 'newStatus' => $newStatus, 'diem_so' => $result['diem_so']]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Có lỗi xảy ra khi lưu bài nộp. Vui lòng thử lại.']);
        }
    }

    /**
     * API: Xử lý nộp bài tự luận (Gõ trực tiếp) (SỬA: Dùng getTrangThaiSauNop)
     */
    public function nopBaiGoTrucTiep() {
         header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             http_response_code(405);
             echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            return;
        }

        $json_data = json_decode(file_get_contents('php://input'), true);
        $ma_bai_tap = filter_var($json_data['ma_bai_tap'] ?? null, FILTER_VALIDATE_INT);
        $noi_dung = trim($json_data['noi_dung'] ?? '');

        if (!$ma_bai_tap || empty($noi_dung)) {
             http_response_code(400);
             echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ (thiếu mã bài tập hoặc nội dung).']);
            return;
        }
         if (mb_strlen($noi_dung, 'UTF-8') < 20) { // Kiểm tra độ dài ký tự Unicode
             http_response_code(400);
             echo json_encode(['success' => false, 'message' => 'Nội dung bài làm quá ngắn (yêu cầu ít nhất 20 ký tự).']);
             return;
         }

        $success = $this->baiTapModel->luuBaiNopTuLuan($ma_bai_tap, $this->ma_hoc_sinh, $noi_dung, null);

         if ($success) {
            $newStatus = $this->baiTapModel->getTrangThaiSauNop($ma_bai_tap, $this->ma_hoc_sinh);
            echo json_encode(['success' => true, 'message' => 'Nộp bài thành công!', 'newStatus' => $newStatus]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi lưu bài nộp. Vui lòng thử lại.']);
        }
    }

    /**
     * API: Xử lý nộp bài tự luận (Upload file) (SỬA: Check quá hạn trước upload; Dùng getTrangThaiSauNop)
     */
    public function nopBaiUpload() {
         header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
             echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            return;
        }

        $ma_bai_tap = filter_input(INPUT_POST, 'ma_bai_tap', FILTER_VALIDATE_INT);

        if (!$ma_bai_tap || !isset($_FILES['file_bai_lam']) || $_FILES['file_bai_lam']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
             echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ hoặc lỗi tải file. Mã lỗi: ' . ($_FILES['file_bai_lam']['error'] ?? 'N/A')]);
            return;
        }

        // SỬA: Check quá hạn trước khi xử lý file
        $assignmentDetails = $this->baiTapModel->getChiTietBaiTap($ma_bai_tap, $this->ma_hoc_sinh);
        if (!$assignmentDetails) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy bài tập.']);
            return;
        }
        $han_nop = $assignmentDetails['han_nop'];
        if ($han_nop && strtotime($han_nop) < time()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Đã quá hạn nộp bài.']);
            return;
        }

        $file = $_FILES['file_bai_lam'];
        $allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Loại file không hợp lệ. Chỉ chấp nhận PDF hoặc DOCX.']);
            return;
        }
        if ($file['size'] > $maxSize) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dung lượng file vượt quá 5MB.']);
            return;
        }

        $uploadDir = '../public/uploads/bailam/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
             http_response_code(500);
             error_log("Không thể tạo thư mục upload: " . $uploadDir);
             echo json_encode(['success' => false, 'message' => 'Lỗi server: Không thể tạo thư mục lưu file.']);
             return;
        }
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safeFileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $newFileName = 'bailam_' . $ma_bai_tap . '_hs_' . $this->ma_hoc_sinh . '_' . time() . '_' . $safeFileName . '.' . $fileExtension;
        $destination = $uploadDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $relativePath = 'uploads/bailam/' . $newFileName;
            $success = $this->baiTapModel->luuBaiNopTuLuan($ma_bai_tap, $this->ma_hoc_sinh, null, $relativePath);

             if ($success) {
                 $newStatus = $this->baiTapModel->getTrangThaiSauNop($ma_bai_tap, $this->ma_hoc_sinh);
                echo json_encode(['success' => true, 'message' => 'Upload và nộp bài thành công!', 'newStatus' => $newStatus]);
            } else {
                 unlink($destination);
                 http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi lưu thông tin bài nộp.']);
            }
        } else {
            http_response_code(500);
            error_log("Không thể di chuyển file upload từ " . $file['tmp_name'] . " đến " . $destination);
            echo json_encode(['success' => false, 'message' => 'Lỗi server: Không thể di chuyển file đã upload.']);
        }
    }

    // Helper kiểm tra hạn nộp (nếu cần)
    private function isWithinDueDate($dueDate) {
         if (!$dueDate) return true; // Nếu không có hạn, luôn trong hạn
         return time() <= strtotime($dueDate); // dueDate đã có T23:59:59
    }

}
?>