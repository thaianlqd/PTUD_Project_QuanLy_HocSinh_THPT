<?php
/**
 * ThisinhNhaphocController: Xử lý Đăng Ký Nhập Học cho thí sinh
 */
// THÊM DÒNG NÀY NGAY ĐẦU FILE
error_reporting(E_ALL);
ini_set('display_errors', 1);
class ThisinhNhaphocController {
    private $model;

    public function __construct() {
        // --- XÓA ĐOẠN GÂY LỖI ---
        // require_once dirname(__DIR__) . '/Core/Database.php';
        // $this->db = Database::getInstance()->getConnection();
        // -------------------------

        // Chỉ cần gọi Model (Model đã tự kết nối DB rồi)
        require_once dirname(__DIR__) . '/Models/ThiSinhModel_NhapHoc.php';
        $this->model = new ThiSinhModel_NhapHoc();
    }

    // ====== HELPER: LOAD VIEW ======
    protected function loadView($view, $data = []) {
        extract($data);
        require_once dirname(__DIR__) . '/Views/' . $view . '.php';
    }

    // ====== TRANG CHÍNH: ĐĂNG KÝ NHẬP HỌC ======
    public function nhapHoc() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'ThiSinh') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        // Chỉ load view, KHÔNG lấy dữ liệu ở đây
        // Dữ liệu sẽ được load qua AJAX từ getDanhSachTruongApi()
        $data = [];
        $this->loadView('ThiSinh/nhap_hoc', $data);
    }

    public function getToHopByTruongApi() {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $ma_truong = $input['ma_truong'];
            
            // Query lấy các tổ hợp DISTINCT từ bảng lop_hoc của trường đó
            $sql = "SELECT DISTINCT th.ma_to_hop_mon, th.ten_to_hop 
                    FROM lop_hoc lh
                    JOIN to_hop_mon th ON lh.ma_to_hop_mon = th.ma_to_hop_mon
                    WHERE lh.ma_truong = :ma_truong AND lh.khoi = 10";
            
            // Bạn cần gọi qua Model để chạy câu SQL này (hoặc viết vào Model rồi gọi)
            // Giả sử bạn thêm hàm getToHopMoi($ma_truong) trong Model
            $result = $this->model->getToHopByTruong($ma_truong); 
            
            echo json_encode(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ====== API: LẤY DANH SÁCH TRƯỜNG ======
    public function getDanhSachTruongApi() {
        // THÊM DEBUG NGAY ĐẦU HÀM
        error_log("=== getDanhSachTruongApi CALLED ===");
        
        header('Content-Type: application/json; charset=utf-8');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        error_log("Session ID: " . session_id());
        error_log("Session data: " . print_r($_SESSION, true));
        
        if (!isset($_SESSION['user_id'])) {
            error_log("User not logged in");
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập', 'session' => $_SESSION]);
            exit;
        }

        try {
            $user_id = $_SESSION['user_id'];
            error_log("Calling model with user_id: " . $user_id);
            
            $result = $this->model->getDanhSachTruongNhapHoc($user_id);
            
            error_log("Model result: " . print_r($result, true));
            
            echo json_encode(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            error_log("EXCEPTION: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    // ====== API: LẤY MÔN HỌC THEO TỔ HỢP ======
    // public function getMonHocApi() {
    //     header('Content-Type: application/json; charset=utf-8');
        
    //     if (session_status() === PHP_SESSION_NONE) session_start();
        
    //     if (!isset($_SESSION['user_id'])) {
    //         echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    //         exit;
    //     }

    //     try {
    //         $user_id = $_SESSION['user_id'];
            
    //         // Lấy ma_to_hop_mon từ query string HOẶC từ ma_truong
    //         $ma_to_hop_mon = $_GET['ma_to_hop_mon'] ?? null;
    //         $ma_truong = $_GET['ma_truong'] ?? null;
            
    //         // Nếu chỉ có ma_truong, lấy ma_to_hop_mon từ trường đó
    //         if (!$ma_to_hop_mon && $ma_truong) {
    //             $sqlTruong = "SELECT ma_to_hop_mon FROM truong_thpt WHERE ma_truong = ?";
    //             $stmtTruong = $this->db->prepare($sqlTruong);
    //             $stmtTruong->execute([$ma_truong]);
    //             $truong = $stmtTruong->fetch();
    //             if ($truong) {
    //                 $ma_to_hop_mon = $truong['ma_to_hop_mon'];
    //             }
    //         }
            
    //         if (!$ma_to_hop_mon) {
    //             echo json_encode(['success' => false, 'message' => 'Thiếu tổ hợp môn']);
    //             exit;
    //         }

    //         error_log("getMonHocApi: user=$user_id, ma_to_hop_mon=$ma_to_hop_mon");
            
    //         $result = $this->model->getMonHocByToHop($ma_to_hop_mon);
    //         echo json_encode(['success' => true, 'data' => $result]);
    //     } catch (Exception $e) {
    //         error_log("Error getMonHocApi: " . $e->getMessage());
    //         echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    //     }
    //     exit;
    // }
    public function getMonHocApi() {
        header('Content-Type: application/json; charset=utf-8');
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            exit;
        }
    
        try {
            // Không cần lấy ma_to_hop_mon nữa, lấy thẳng toàn bộ môn
            $result = $this->model->getAllMonHoc();
            
            echo json_encode(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            error_log("Error getMonHocApi: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    // ====== API: LẤY DANH SÁCH LỚP 10 ======
    public function getDanhSachLopApi() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'ThiSinh') {
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
            exit;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // SỬA: Nhận danh_sach_ma_mon thay vì ma_to_hop_mon
            if (!isset($input['ma_truong']) || !isset($input['danh_sach_ma_mon'])) {
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin trường hoặc môn chọn']);
                exit;
            }

            $ma_truong = $input['ma_truong'];
            $mon_chon = $input['danh_sach_ma_mon'];

            // 1. Tìm tổ hợp môn phù hợp dựa trên 4 môn đã chọn
            $ma_to_hop_tim_duoc = $this->model->findToHopByMonChon($ma_truong, $mon_chon);

            if (!$ma_to_hop_tim_duoc) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy lớp học nào phù hợp với các môn bạn chọn tại trường này.']);
                exit;
            }

            // 2. Lấy danh sách lớp theo tổ hợp tìm được
            $danh_sach_lop = $this->model->getDanhSachLopKhoi10ByTruongVaToHop($ma_truong, $ma_to_hop_tim_duoc);

            echo json_encode([
                'success' => true, 
                'data' => $danh_sach_lop,
                'ma_to_hop_mon' => $ma_to_hop_tim_duoc // Trả về để JS lưu lại dùng cho bước sau
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    // ====== API: LƯU CHỌN MÔN TẠM THỜI ======
    // public function saveChonMonApi() {
    //     header('Content-Type: application/json');
    //     if (session_status() === PHP_SESSION_NONE) session_start();
        
    //     if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'ThiSinh') {
    //         echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    //         exit;
    //     }

    //     try {
    //         $input = json_decode(file_get_contents('php://input'), true);
            
    //         // --- SỬA 1: Chỉ kiểm tra danh_sach_ma_mon, BỎ kiểm tra ma_to_hop_mon ---
    //         if (!isset($input['danh_sach_ma_mon'])) {
    //             echo json_encode([
    //                 'success' => false,
    //                 'message' => 'Thiếu tham số danh_sach_ma_mon'
    //             ]);
    //             exit;
    //         }

    //         $user_id = $_SESSION['user_id'];
    //         $danh_sach_ma_mon = $input['danh_sach_ma_mon']; 

    //         // --- SỬA 2: Gọi hàm validate mà KHÔNG CẦN tham số thứ 2 ---
    //         $validate = $this->model->validateChonMon($danh_sach_ma_mon);
            
    //         if (!$validate['valid']) {
    //             echo json_encode(['success' => false, 'message' => $validate['message']]);
    //             exit;
    //         }

    //         // Lưu chọn môn
    //         $result = $this->model->saveChonMon($user_id, $danh_sach_ma_mon);

    //         if ($result) {
    //             echo json_encode(['success' => true, 'message' => 'Lưu môn học thành công']);
    //         } else {
    //             echo json_encode(['success' => false, 'message' => 'Không thể lưu môn học']);
    //         }
    //     } catch (Exception $e) {
    //         echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    //     }
    // }
    // ====== API: LƯU CHỌN MÔN TẠM THỜI (ĐÃ FIX) ======
    public function saveChonMonApi() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'ThiSinh') {
            echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
            exit;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // --- FIX: Chỉ kiểm tra danh_sach_ma_mon, KHÔNG kiểm tra ma_to_hop_mon ---
            if (!isset($input['danh_sach_ma_mon'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Thiếu tham số danh_sach_ma_mon'
                ]);
                exit;
            }

            $user_id = $_SESSION['user_id'];
            $danh_sach_ma_mon = $input['danh_sach_ma_mon']; 

            // Gọi hàm validate (Model cũng cần sửa tương ứng, xem bên dưới)
            $validate = $this->model->validateChonMon($danh_sach_ma_mon);
            
            if (!$validate['valid']) {
                echo json_encode([
                    'success' => false,
                    'message' => $validate['message']
                ]);
                exit;
            }

            // Lưu chọn môn
            $result = $this->model->saveChonMon($user_id, $danh_sach_ma_mon);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Lưu môn học thành công'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không thể lưu môn học'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
    }



    // ====== API: LẤY MÔN HỌC ĐÃ CHỌN ======
    public function getChonMonDaSaveApi() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'ThiSinh') {
            echo json_encode([
                'success' => false,
                'message' => 'Không có quyền truy cập'
            ]);
            exit;
        }

        try {
            $user_id = $_SESSION['user_id'];
            $chon_mon = $this->model->getChonMonDaSave($user_id);

            echo json_encode([
                'success' => true,
                'data' => $chon_mon
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
    }

    // ====== API: XÁC NHẬN NHẬP HỌC ======
    // public function xacNhanNhapHocApi() {
    //     header('Content-Type: application/json');
    //     if (session_status() === PHP_SESSION_NONE) session_start();
    //     if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false]); exit; }

    //     try {
    //         $input = json_decode(file_get_contents('php://input'), true);
    //         $user_id = $_SESSION['user_id'];
            
    //         // Chỉ cần mã trường (và mã tổ hợp nếu muốn lưu lại để admin tham khảo)
    //         if (!isset($input['ma_truong'])) {
    //             echo json_encode(['success' => false, 'message' => 'Thiếu mã trường']); exit;
    //         }

    //         // Gọi Model (Truyền null cho ma_lop)
    //         $result = $this->model->xacNhanNhapHoc($user_id, $input['ma_truong'], null);
            
    //         echo json_encode($result);
    //     } catch (Exception $e) {
    //         echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    //     }
    // }
    public function xacNhanNhapHocApi() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false, 'message' => 'Hết phiên đăng nhập']); exit; }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $user_id = $_SESSION['user_id'];
            
            // --- SỬA Ở ĐÂY: CHỈ BẮT BUỘC MA_TRUONG ---
            if (!isset($input['ma_truong'])) {
                echo json_encode(['success' => false, 'message' => 'Thiếu mã trường']); 
                exit;
            }

            // Ma to hop mon: Có thì lấy, không có thì để NULL (Không bắt buộc nữa)
            $ma_to_hop = isset($input['ma_to_hop_mon']) ? $input['ma_to_hop_mon'] : null;

            // Gọi Model: Truyền null cho ma_lop (vì chưa xếp lớp)
            $result = $this->model->xacNhanNhapHoc($user_id, $input['ma_truong'], null, $ma_to_hop);
            
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    // ====== API: TỪ CHỐI NHẬP HỌC ======
    public function tuChoiNhapHocApi() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'ThiSinh') {
            echo json_encode([
                'success' => false,
                'message' => 'Không có quyền truy cập'
            ]);
            exit;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['ma_truong'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Thiếu tham số ma_truong'
                ]);
                exit;
            }

            $user_id = $_SESSION['user_id'];
            $ma_truong = $input['ma_truong'];

            // Từ chối nhập học
            $result = $this->model->tuChoiNhapHoc($user_id, $ma_truong);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Từ chối nhập học thành công'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không thể từ chối nhập học'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
    }

    // ====== API: LẤY THÔNG TIN NHẬP HỌC ======
    public function getNhapHocInfoApi() {
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'ThiSinh') {
            echo json_encode([
                'success' => false,
                'message' => 'Không có quyền truy cập'
            ]);
            exit;
        }

        try {
            $user_id = $_SESSION['user_id'];
            $nhap_hoc_info = $this->model->getNhapHocInfo($user_id);

            echo json_encode([
                'success' => true,
                'data' => $nhap_hoc_info
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
    }
}
?>
