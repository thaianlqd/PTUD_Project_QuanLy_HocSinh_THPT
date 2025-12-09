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
     * API: Lấy chi tiết bài NỘP (kèm ngay_nop_vietnam UTC+7)
     * URL: /baitap/getBaiNopChiTietApi?ma_bai_nop=XXX (GET)
     * Trả về: data flattened (không cần submission + assignment riêng)
     */
    public function getBaiNopChiTietApi() {
        header('Content-Type: application/json; charset=utf-8');

        $ma_bai_nop = filter_input(INPUT_GET, 'ma_bai_nop', FILTER_VALIDATE_INT);
        $ma_hoc_sinh = $_SESSION['user_id'] ?? null;

        if (!$ma_bai_nop || !$ma_hoc_sinh) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $baiNop = $this->baiTapModel->getChiTietBaiNopChoHocSinh($ma_bai_nop, $ma_hoc_sinh);

            if (!$baiNop) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy bài nộp'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // ✅ Trả về JSON (Đã bổ sung nhan_xet)
            echo json_encode([
                'success' => true,
                'data' => [
                    'ma_bai_nop' => $baiNop['ma_bai_nop'],
                    'ma_bai_tap' => $baiNop['ma_bai_tap'],
                    'ten_bai_tap' => $baiNop['ten_bai_tap'],
                    'loai_bai_tap' => $baiNop['loai_bai_tap'],
                    'mo_ta' => $baiNop['mo_ta'],
                    'ngay_nop_vietnam' => $baiNop['ngay_nop_vietnam'],
                    'han_nop_vietnam' => $baiNop['han_nop_vietnam'],
                    'ngay_nop' => $baiNop['ngay_nop'],
                    'han_nop' => $baiNop['han_nop'],
                    'trang_thai' => $baiNop['trang_thai'],
                    'diem_so' => $baiNop['diem_so'],
                    
                    // 👇👇 THÊM DÒNG NÀY VÀO ĐÂY 👇👇
                    'nhan_xet' => $baiNop['nhan_xet'], 
                    
                    'file_nop' => $baiNop['file_nop'],
                    'noi_dung_tra_loi' => $baiNop['noi_dung_tra_loi'],
                    'gio_bat_dau_lam_bai' => $baiNop['gio_bat_dau_lam_bai'],
                    
                    // Các thông tin khác giữ nguyên
                    'de_bai_tu_luan' => $baiNop['de_bai_tu_luan'],
                    'danh_sach_cau_hoi' => $baiNop['danh_sach_cau_hoi'],
                    'thoi_gian_lam_bai' => $baiNop['thoi_gian_lam_bai'],
                    'loai_file_cho_phep' => $baiNop['loai_file_cho_phep'],
                    'dung_luong_toi_da' => $baiNop['dung_luong_toi_da']
                ]
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("Lỗi getBaiNopChiTietApi: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống'], JSON_UNESCAPED_UNICODE);
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
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            return;
        }

        $result = $this->baiTapModel->luuVaChamDiemTracNghiem($ma_bai_tap, $this->ma_hoc_sinh, $answersJson);
        
        if ($result['success']) {
            $newStatus = $this->baiTapModel->getTrangThaiSauNop($ma_bai_tap, $this->ma_hoc_sinh);
            
            // ✅ THÊM MỚI: Lấy ma_bai_nop để trả về cho JS
            $ma_bai_nop = $this->baiTapModel->getMaBaiNop($ma_bai_tap, $this->ma_hoc_sinh);

            echo json_encode([
                'success' => true, 
                'message' => $result['message'], 
                'newStatus' => $newStatus, 
                'diem_so' => $result['diem_so'],
                'ma_bai_nop' => $ma_bai_nop // <--- QUAN TRỌNG
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $result['message']]);
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
             echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            return;
        }
         if (mb_strlen($noi_dung, 'UTF-8') < 20) {
             http_response_code(400);
             echo json_encode(['success' => false, 'message' => 'Nội dung quá ngắn (tối thiểu 20 ký tự).']);
             return;
         }

        $success = $this->baiTapModel->luuBaiNopTuLuan($ma_bai_tap, $this->ma_hoc_sinh, $noi_dung, null);

         if ($success) {
            $newStatus = $this->baiTapModel->getTrangThaiSauNop($ma_bai_tap, $this->ma_hoc_sinh);
            
            // ✅ THÊM MỚI: Lấy ma_bai_nop
            $ma_bai_nop = $this->baiTapModel->getMaBaiNop($ma_bai_tap, $this->ma_hoc_sinh);

            echo json_encode([
                'success' => true, 
                'message' => 'Nộp bài thành công!', 
                'newStatus' => $newStatus,
                'ma_bai_nop' => $ma_bai_nop // <--- QUAN TRỌNG
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi lưu bài nộp.']);
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
             echo json_encode(['success' => false, 'message' => 'Lỗi tải file.']);
            return;
        }

        // Check quá hạn
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
            echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file PDF hoặc DOCX.']);
            return;
        }
        if ($file['size'] > $maxSize) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File quá lớn (> 5MB).']);
            return;
        }

        $uploadDir = '../public/uploads/bailam/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
             http_response_code(500);
             echo json_encode(['success' => false, 'message' => 'Lỗi server (mkdir).']);
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
                 
                 // ✅ THÊM MỚI: Lấy ma_bai_nop
                 $ma_bai_nop = $this->baiTapModel->getMaBaiNop($ma_bai_tap, $this->ma_hoc_sinh);

                echo json_encode([
                    'success' => true, 
                    'message' => 'Upload thành công!', 
                    'newStatus' => $newStatus,
                    'ma_bai_nop' => $ma_bai_nop // <--- QUAN TRỌNG
                ]);
            } else {
                 unlink($destination);
                 http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Lỗi lưu thông tin bài nộp.']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi di chuyển file.']);
        }
    }

    /**
     * ✅ API: Download file bài nộp
     * URL: /baitap/downloadBaiNopApi?ma_bai_nop=XXX (GET)
     */
    public function downloadBaiNopApi() {
        $ma_bai_nop = filter_input(INPUT_GET, 'ma_bai_nop', FILTER_VALIDATE_INT);
        $ma_hoc_sinh = $_SESSION['user_id'] ?? null;

        if (!$ma_bai_nop || !$ma_hoc_sinh) {
            http_response_code(400);
            die('Thiếu thông tin xác thực.');
        }

        try {
            // GỌI MODEL (Thay vì $this->db->prepare...)
            $fileRelPath = $this->baiTapModel->getFilePath($ma_bai_nop, $ma_hoc_sinh);

            if (!$fileRelPath) {
                http_response_code(404);
                die('Không tìm thấy file hoặc bạn không có quyền.');
            }

            // Đường dẫn thực tế trên server
            $filePath = __DIR__ . '/../../public/' . $fileRelPath; 
            // Lưu ý: $fileRelPath trong DB là "uploads/bailam/..." nên nối thêm public/

            if (!file_exists($filePath)) {
                error_log("File not found on disk: $filePath");
                http_response_code(404);
                die('File vật lý không tồn tại.');
            }

            // Download file
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));

            readfile($filePath);
            exit;

        } catch (Exception $e) {
            error_log("Lỗi downloadBaiNopApi: " . $e->getMessage());
            http_response_code(500);
            die('Lỗi server.');
        }
    }

    // Helper kiểm tra hạn nộp (nếu cần)
    private function isWithinDueDate($dueDate) {
         if (!$dueDate) return true; // Nếu không có hạn, luôn trong hạn
         return time() <= strtotime($dueDate); // dueDate đã có T23:59:59
    }

}
?>