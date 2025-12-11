<?php
// Đảm bảo session đã được khởi động
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class GiaoVienController extends Controller {

    private $giaoVienBaiTapModel;
    private $diemDanhModel; // Model cho điểm danh
    private $ma_giao_vien;

    public function __construct() {
        // DEBUG: Log session để kiểm tra
        error_log("GiaoVienController __construct - Session ID: " . session_id());
        error_log("GiaoVienController __construct - Session data: " . print_r($_SESSION, true));
        
        // 1. KIỂM TRA QUYỀN & ID
        $is_logged_in = isset($_SESSION['user_role']) && 
                          in_array($_SESSION['user_role'], ['GiaoVien', 'BanGiamHieu']) && 
                          isset($_SESSION['user_id']) && 
                          $_SESSION['user_id'] > 0;

        if (!$is_logged_in) {
            error_log("GiaoVienController: Session không hợp lệ. Redirecting...");
            
            // Kiểm tra nếu là AJAX request
            $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                       strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            
            if ($is_ajax || (method_exists($this, 'is_ajax') && $this->is_ajax())) { 
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401); 
                echo json_encode([
                    'success' => false, 
                    'message' => 'Phiên đăng nhập không hợp lệ hoặc đã hết hạn. Vui lòng tải lại trang và đăng nhập lại.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            } else { 
                header('Location: ' . BASE_URL . '/auth/index');
                exit;
            }
        }

        $this->ma_giao_vien = $_SESSION['user_id'];
        
        $this->giaoVienBaiTapModel = $this->loadModel('GiaoVienBaiTapModel'); // (Giữ lại nếu cần)
        $this->diemDanhModel = $this->loadModel('DiemDanhModel'); 
        
        if ($this->diemDanhModel === null) {
            die("Lỗi nghiêm trọng: Không thể tải DiemDanhModel (GVController).");
        }
    }

    // ----- CÁC HÀM BÀI TẬP -----
    
    public function index() { $this->baitap(); }
    
    /**
     * HÀM CŨ: Hiển thị trang CHỌN LỚP
     * URL: /giaovien/baitap
     */
    public function baitap() {
        $danhSachLop = $this->giaoVienBaiTapModel->getLopHocDaPhanCong($this->ma_giao_vien);
        $data = ['user_name' => $_SESSION['user_name'] ?? 'Giáo viên', 'danh_sach_lop' => $danhSachLop];
        // Sửa tên file view cho đúng
        $content = $this->loadView('GVBoMon/chon_lop_baitap', $data); 
        echo $content;
    }

    /**
     * HÀM MỚI (STEP 2): Hiển thị DANH SÁCH BÀI TẬP của 1 lớp
     * URL: /giaovien/danhsachbaitap/{ma_lop}/{ma_mon_hoc}
     */
    public function danhSachBaiTap($ma_lop, $ma_mon_hoc) {
        // 1. Gọi Model để lấy DS Bài tập đã giao
        $danhSachBaiTap = $this->giaoVienBaiTapModel->getDanhSachBaiTapCuaLop($ma_lop, $ma_mon_hoc);

        // 2. Lấy thông tin chung của lớp/môn
        $lopInfo = $this->giaoVienBaiTapModel->getThongTinLopMonHoc($ma_lop, $ma_mon_hoc);
        
        if(!$lopInfo) {
            die("Lỗi: Không tìm thấy thông tin lớp hoặc môn học.");
        }
        
        // 3. Chuẩn bị data cho View
        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Giáo viên',
            'danh_sach_bai_tap' => $danhSachBaiTap,
            'lop_info' => $lopInfo // Gửi thông tin lớp qua
        ];

        // 4. Load View MỚI
        $content = $this->loadView('GVBoMon/danh_sach_bai_tap_view', $data);
        echo $content;
    }

    /**
     * HÀM MỚI (STEP 5): Hiển thị CHI TIẾT NỘP BÀI của 1 bài tập
     * URL: /giaovien/chitietbaitap/{ma_bai_tap}
     */
    public function chiTietBaiTap($ma_bai_tap) {
        // 1. Lấy thông tin cơ bản của bài tập (để lấy ma_lop)
        $dataLop = $this->giaoVienBaiTapModel->getThongTinCoBanBaiTap($ma_bai_tap);
        
        if(!$dataLop) { die("Không tìm thấy bài tập!"); }

        // 2. Lấy danh sách nộp bài (Ai nộp, ai chưa)
        $danhSachNopBai = $this->giaoVienBaiTapModel->getThongKeNopBai($ma_bai_tap, $dataLop['ma_lop']);
        
        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Giáo viên',
            'bai_tap_info' => $dataLop,
            'danh_sach_nop_bai' => $danhSachNopBai
        ];
        
        // 3. Load View MỚI
        $content = $this->loadView('GVBoMon/chi_tiet_bai_tap_view', $data);
        echo $content;
    }

    /**
     * API MỚI: Upload file đề bài
     */
    public function uploadDeBaiApi() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_FILES['file'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu file!'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $file = $_FILES['file'];
        $uploadDir = __DIR__ . '/../../public/uploads/debai/';
        
        // Tạo thư mục nếu chưa tồn tại
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Validate file
        $allowedExts = ['pdf', 'doc', 'docx', 'txt', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($fileExt, $allowedExts)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Định dạng file không hợp lệ!'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($file['size'] > $maxSize) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File quá lớn (tối đa 10MB)!'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Generate unique filename
        $timestamp = time();
        $randomStr = bin2hex(random_bytes(4));
        $fileName = "debai_{$timestamp}_{$randomStr}.{$fileExt}";
        $filePath = $uploadDir . $fileName;
        $relativeePath = "uploads/debai/{$fileName}";

        // Move file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi upload file!'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Trả về relative path để lưu vào DB
        echo json_encode([
            'success' => true,
            'message' => 'Upload thành công!',
            'file_path' => $relativeePath
        ], JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * API: Lưu bài tập (Dùng cho Modal)
     */
    public function luuBaiTapApi() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
             http_response_code(405);
             echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
             return;
        }

        $data = [
            'ten_bai_tap' => trim($_POST['ten_bai_tap'] ?? ''),
            'mo_ta_chung' => trim($_POST['mo_ta_chung'] ?? ''),
            'han_nop' => $_POST['han_nop'] ?? null,
            'loai_bai_tap' => $_POST['loai_bai_tap'] ?? '',
            'ma_lop' => filter_var($_POST['ma_lop'], FILTER_VALIDATE_INT),
            'ma_mon_hoc' => filter_var($_POST['ma_mon_hoc'], FILTER_VALIDATE_INT),
            'ma_giao_vien' => $this->ma_giao_vien,
            'noi_dung_tu_luan' => trim($_POST['noi_dung_tu_luan'] ?? ''),
            'json_trac_nghiem' => $_POST['json_trac_nghiem'] ?? null, 
            'thoi_gian_lam_bai' => filter_var($_POST['thoi_gian_lam_bai'] ?? 45, FILTER_VALIDATE_INT),
            'loai_file_cho_phep' => $_POST['loai_file_cho_phep'] ?? '.pdf,.docx',
            'dung_luong_toi_da' => filter_var($_POST['dung_luong_toi_da'] ?? 5, FILTER_VALIDATE_INT),
            'file_dinh_kem' => null
        ];
        
        // Xử lý upload file đính kèm (đề bài)
        if (isset($_FILES['file_dinh_kem']) && $_FILES['file_dinh_kem']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['file_dinh_kem'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['pdf', 'docx', 'doc', 'png', 'jpg', 'jpeg', 'zip', 'rar']; 

            if (!in_array($fileExtension, $allowedExtensions)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Lỗi: File đính kèm (đề bài) có định dạng không hợp lệ.']);
                return;
            }
            
            $uploadDir = '../public/uploads/debai/';
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
            $safeFileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
            $newFileName = 'debai_' . $data['ma_lop'] . '_' . time() . '_' . $safeFileName . '.' . $fileExtension;
            
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFileName)) {
                $data['file_dinh_kem'] = 'uploads/debai/' . $newFileName;
            } else {
                 http_response_code(500);
                 echo json_encode(['success' => false, 'message' => 'Lỗi khi tải file đính kèm (server error).']);
                 return;
            }
        }
        
        if (empty($data['ten_bai_tap']) || empty($data['han_nop']) || empty($data['loai_bai_tap'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập Tên, Hạn nộp và Loại bài tập.']);
            return;
        }
        if ($data['loai_bai_tap'] == 'TracNghiem' && empty($data['json_trac_nghiem'])) {
             http_response_code(400);
             echo json_encode(['success' => false, 'message' => 'Lỗi: Nội dung trắc nghiệm rỗng.']);
             return;
        }
        
        $result = $this->giaoVienBaiTapModel->giaoBaiTap($data);
        if ($result['success']) { echo json_encode($result); } 
        else { http_response_code(500); echo json_encode($result); }
    }

    /**
     * API MỚI: Sửa bài tập
     */
    public function suaBaiTapApi() {
        // Bắt đầu bộ đệm để chặn các Warning rác in ra màn hình
        ob_start(); 
        
        try {
            $ma_bai_tap = filter_input(INPUT_POST, 'ma_bai_tap', FILTER_VALIDATE_INT);
            
            if (!$ma_bai_tap) {
                throw new Exception('Thiếu mã bài tập');
            }

            // 1. Xử lý ngày giờ (Xóa chữ T)
            $han_nop = $_POST['han_nop'] ?? null;
            if ($han_nop) {
                $han_nop = str_replace('T', ' ', $han_nop); 
            }

            // 2. Chuẩn bị dữ liệu
            $data = [
                'ten_bai_tap' => trim($_POST['ten_bai_tap'] ?? ''),
                'mo_ta'       => trim($_POST['mo_ta'] ?? ''),
                'han_nop'     => $han_nop, 
                'file_dinh_kem' => null
                // Các field con (TuLuan/TracNghiem) nếu Modal chưa có thì cứ để null, Model sẽ bỏ qua
            ];

            // 3. Xử lý Upload File (Chỉ khi có file mới)
            if (isset($_FILES['file_dinh_kem_new']) && $_FILES['file_dinh_kem_new']['error'] == UPLOAD_ERR_OK) {
                $file = $_FILES['file_dinh_kem_new'];
                $uploadDir = __DIR__ . '/../../public/uploads/debai/';
                
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $fileName = 'update_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                    $data['file_dinh_kem'] = 'uploads/debai/' . $fileName;
                }
            }

            // 4. Gọi Model
            $result = $this->giaoVienBaiTapModel->suaBaiTap($ma_bai_tap, $this->ma_giao_vien, $data);
            
            // Xóa sạch bộ đệm trước khi in JSON (Fix lỗi "Not JSON")
            ob_clean(); 
            
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result);

        } catch (Exception $e) {
            ob_clean(); // Xóa lỗi rác nếu có
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * API MỚI: Xóa bài tập
     */
    public function xoaBaiTapApi() {
        header('Content-Type: application/json; charset=utf-8');

        $ma_bai_tap = filter_input(INPUT_POST, 'ma_bai_tap', FILTER_VALIDATE_INT);
        
        if (!$ma_bai_tap) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu mã bài tập'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $result = $this->giaoVienBaiTapModel->xoaBaiTap($ma_bai_tap, $this->ma_giao_vien);
        
        if ($result['success']) {
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(400);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * API: Download file đề bài
     */
    public function downloadDeBaiApi() {
        $ma_bai_tap = filter_input(INPUT_GET, 'ma_bai_tap', FILTER_VALIDATE_INT);
        
        if (!$ma_bai_tap) {
            header('HTTP/1.1 404 Not Found');
            echo 'Thiếu mã bài tập!';
            return;
        }

        try {
            // 1. Lấy thông tin file
            $stmt = $this->db->prepare("SELECT file_dinh_kem, ten_bai_tap FROM bai_tap WHERE ma_bai_tap = ?");
            $stmt->execute([$ma_bai_tap]);
            $baiTap = $stmt->fetch();

            if (!$baiTap || !$baiTap['file_dinh_kem']) {
                header('HTTP/1.1 404 Not Found');
                echo 'Không tìm thấy file!';
                return;
            }

            $filePath = $baiTap['file_dinh_kem'];
            
            // 2. Kiểm tra file tồn tại
            if (!file_exists($filePath)) {
                header('HTTP/1.1 404 Not Found');
                echo 'File không tồn tại: ' . $filePath;
                error_log("File đề bài không tìm thấy: $filePath");
                return;
            }

            // 3. Download file
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;

        } catch (Exception $e) {
            error_log("Lỗi downloadDeBaiApi: " . $e->getMessage());
            header('HTTP/1.1 500 Internal Server Error');
            echo 'Lỗi server!';
        }
    }

    /**
     * API: Download file bài nộp của HS
     */
    public function downloadBaiNopApi() {
        $ma_bai_nop = filter_input(INPUT_GET, 'ma_bai_nop', FILTER_VALIDATE_INT);
        
        if (!$ma_bai_nop) {
            header('HTTP/1.1 404 Not Found');
            echo 'Thiếu mã bài nộp!';
            return;
        }

        try {
            // 1. Lấy thông tin file bài nộp
            $stmt = $this->db->prepare("SELECT file_bai_nop FROM bai_nop WHERE ma_bai_nop = ?");
            $stmt->execute([$ma_bai_nop]);
            $baiNop = $stmt->fetch();

            if (!$baiNop || !$baiNop['file_bai_nop']) {
                header('HTTP/1.1 404 Not Found');
                echo 'Không tìm thấy file bài nộp!';
                return;
            }

            $filePath = $baiNop['file_bai_nop'];
            
            // 2. Kiểm tra file tồn tại
            if (!file_exists($filePath)) {
                header('HTTP/1.1 404 Not Found');
                echo 'File không tồn tại!';
                error_log("File bài nộp không tìm thấy: $filePath");
                return;
            }

            // 3. Download file
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;

        } catch (Exception $e) {
            error_log("Lỗi downloadBaiNopApi: " . $e->getMessage());
            header('HTTP/1.1 500 Internal Server Error');
            echo 'Lỗi server!';
        }
    }

    /**
     * API: Lấy bài làm học sinh (Load vào Modal)
     */
    public function layBaiLamHocSinhApi() {
        ob_start(); // Chặn lỗi rác
        try {
            $ma_bai_nop = filter_input(INPUT_POST, 'ma_bai_nop', FILTER_VALIDATE_INT);
            if (!$ma_bai_nop) throw new Exception('Thiếu mã bài nộp');

            $baiLam = $this->giaoVienBaiTapModel->getChiTietBaiNop($ma_bai_nop);
            
            if (!$baiLam) throw new Exception('Không tìm thấy dữ liệu bài nộp này.');

            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $baiLam]);

        } catch (Exception $e) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * API: Lưu điểm và nhận xét
     */
    public function luuDiemSoApi() {
        ob_start();
        try {
            $ma_bai_nop = filter_input(INPUT_POST, 'ma_bai_nop', FILTER_VALIDATE_INT);
            $diem_so = isset($_POST['diem_so']) ? floatval($_POST['diem_so']) : null;
            $nhan_xet = $_POST['nhan_xet'] ?? '';

            if (!$ma_bai_nop || $diem_so === null || $diem_so < 0 || $diem_so > 10) {
                throw new Exception('Điểm số không hợp lệ (phải từ 0-10).');
            }

            $result = $this->giaoVienBaiTapModel->capNhatDiemSo($ma_bai_nop, $diem_so, $nhan_xet);

            ob_clean();
            header('Content-Type: application/json');
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Đã lưu điểm thành công!']);
            } else {
                throw new Exception('Lỗi CSDL khi lưu điểm.');
            }

        } catch (Exception $e) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ----- CÁC HÀM ĐIỂM DANH (ĐÃ SỬA) -----

    /**
     * TRANG 1: CHỌN LỚP
     * URL: /giaovien/diemdanh
     */
    public function diemdanh() {
        $danhSachLop = $this->diemDanhModel->getLopHocDaPhanCong($this->ma_giao_vien);
        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Giáo viên',
            'danh_sach_lop' => $danhSachLop
        ];
        // Sửa tên view cho đúng
        $content = $this->loadView('GVBoMon/diem_danh_chon_lop', $data);
        echo $content;
    }

    /**
     * TRANG 2 (MỚI): CHI TIẾT LỚP & LỊCH SỬ PHIÊN
     * URL: /giaovien/chitietlop/{ma_lop}/{ma_mon_hoc}
     */
    public function chitietlop($ma_lop, $ma_mon_hoc) {
        if (empty($ma_lop) || empty($ma_mon_hoc)) {
            die("Thiếu mã lớp hoặc mã môn học.");
        }

        // 1. Lấy thông tin cơ bản
        $lopInfo = $this->diemDanhModel->getLopMonHocInfo($ma_lop, $ma_mon_hoc, $this->ma_giao_vien);
        if (!$lopInfo) {
            die("Không tìm thấy thông tin lớp hoặc bạn không được phân công.");
        }

        // 2. Lấy danh sách HS (Dùng cho Modal tạo phiên thủ công)
        $danh_sach_hs = $this->diemDanhModel->getHocSinhTheoLop($ma_lop);
        
        // 3. Lấy lịch sử phiên
        $lich_su = $this->diemDanhModel->getLichSuPhien($ma_lop, $ma_mon_hoc, $this->ma_giao_vien);

        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Giáo viên',
            'lop_info' => $lopInfo,
            'danh_sach_hs' => $danh_sach_hs,
            'lich_su' => $lich_su
        ];

        // Load View MỚI
        $content = $this->loadView('GVBoMon/diem_danh_chi_tiet_lop', $data);
        echo $content;
    }


    // ----- CÁC API HỖ TRỢ (Giữ nguyên) -----
    
    // HÀM getChiTietLopApi() ĐÃ BỊ XÓA (Không cần nữa)

    /**
     * API: Tạo phiên điểm danh (2 loại + hỗ trợ mật khẩu)
     */
    public function taoPhienApi() {
        header('Content-Type: application/json');
        $ma_lop = filter_input(INPUT_POST, 'ma_lop', FILTER_VALIDATE_INT);
        $tieu_de = trim($_POST['tieu_de'] ?? '');
        $ghi_chu = trim($_POST['ghi_chu'] ?? '');
        $loai_phien = $_POST['loai_phien'] ?? 'GiaoVien'; 
        
        // Validate thời gian an toàn - chỉ gán nếu không rỗng
        $thoi_gian_mo = !empty($_POST['thoi_gian_mo']) ? $_POST['thoi_gian_mo'] : null;
        $thoi_gian_dong = !empty($_POST['thoi_gian_dong']) ? $_POST['thoi_gian_dong'] : null;
        
        // Mật khẩu (CHỈ dùng cho chế độ HocSinh)
        $yeu_cau_mat_khau = isset($_POST['yeu_cau_mat_khau']) && $_POST['yeu_cau_mat_khau'] === 'true';
        $mat_khau = trim($_POST['mat_khau'] ?? '');

        if (!$ma_lop || empty($tieu_de)) {
             http_response_code(400);
             echo json_encode(['success' => false, 'message' => 'Thiếu mã lớp hoặc tiêu đề.']);
             return;
        }
        
        // Kiểm tra mật khẩu nếu yêu cầu (chỉ ở chế độ HocSinh)
        if ($loai_phien == 'HocSinh' && $yeu_cau_mat_khau && empty($mat_khau)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mật khẩu khi bật yêu cầu mật khẩu.']);
            return;
        }

        $ma_phien = $this->diemDanhModel->taoPhienDiemDanhMoi(
            $ma_lop, 
            $this->ma_giao_vien, 
            $tieu_de, 
            $ghi_chu, 
            $loai_phien, 
            $thoi_gian_mo, 
            $thoi_gian_dong,
            $yeu_cau_mat_khau,
            $mat_khau
        );
        
        if ($ma_phien) {
            if ($loai_phien == 'GiaoVien') {
                echo json_encode(['success'=>true, 'message'=>'Đã tạo phiên thủ công.', 'ma_phien_moi' => $ma_phien]);
            } else {
                $msg = 'Đã tạo phiên tự động cho HS';
                if ($yeu_cau_mat_khau) {
                    $msg .= ' (Có mật khẩu)';
                }
                echo json_encode(['success'=>true, 'message'=> $msg]);
            }
        } else {
            http_response_code(500);
            echo json_encode(['success'=>false, 'message'=>'Lỗi tạo phiên trong CSDL']);
        }
    }
    
    /**
     * API: Lấy chi tiết 1 phiên (để xem lại hoặc điểm danh thủ công)
     */
    public function getChiTietPhienApi() {
        header('Content-Type: application/json');
        $ma_phien = filter_input(INPUT_POST, 'ma_phien', FILTER_VALIDATE_INT);
        if (!$ma_phien) {
             http_response_code(400);
             echo json_encode(['success' => false, 'message' => 'Thiếu mã phiên.']);
             return;
        }

        $phien_info = $this->diemDanhModel->getPhienInfo($ma_phien, $this->ma_giao_vien);
        if (!$phien_info) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy phiên hoặc bạn không có quyền xem.']);
            return;
        }
        
        $ma_lop = $phien_info['ma_lop_hoc'];
        $danh_sach_chi_tiet = $this->diemDanhModel->getChiTietDiemDanh($ma_phien, $ma_lop);

        echo json_encode([
            'success' => true,
            'phien_info' => $phien_info,
            'danh_sach_chi_tiet' => $danh_sach_chi_tiet
        ]);
    }

    /**
     * API: Lưu điểm danh (cho loại 'GiaoVien')
     */
    public function luuDiemDanhApi() {
        header('Content-Type: application/json');
        $ma_phien = filter_input(INPUT_POST, 'ma_phien', FILTER_VALIDATE_INT);
        $data = $_POST['diemdanh'] ?? []; // mảng [ma_hs => trang_thai]
        
        if (!$ma_phien || empty($data)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu mã phiên hoặc data điểm danh.']);
            return;
        }
        
        $ok = $this->diemDanhModel->luuChiTietDiemDanh($ma_phien, $data);
        if ($ok) echo json_encode(['success'=>true, 'message'=>'Đã lưu thành công!']);
        else echo json_encode(['success'=>false, 'message'=>'Lỗi lưu DB']);
    }
    
    /**
     * API: Kết thúc phiên (bất kể loại nào)
     */
    public function ketThucPhienApi() {
        header('Content-Type: application/json');
        $ma_phien = filter_input(INPUT_POST, 'ma_phien', FILTER_VALIDATE_INT);

        if (!$ma_phien) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu mã phiên.']);
            return;
        }

        $ok = $this->diemDanhModel->ketThucPhien($ma_phien, $this->ma_giao_vien);
        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Đã kết thúc phiên.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi CSDL hoặc phiên đã kết thúc.']);
        }
    }

    /**
     * API MỚI: Cập nhật phiên điểm danh
     */
    public function capNhatPhienApi() {
        header('Content-Type: application/json; charset=utf-8');

        $ma_phien = filter_input(INPUT_POST, 'ma_phien', FILTER_VALIDATE_INT);
        $tieu_de = trim($_POST['tieu_de'] ?? '');
        $ghi_chu = trim($_POST['ghi_chu'] ?? '');
        $thoi_gian_mo = !empty($_POST['thoi_gian_mo']) ? $_POST['thoi_gian_mo'] : null;
        $thoi_gian_dong = !empty($_POST['thoi_gian_dong']) ? $_POST['thoi_gian_dong'] : null;
        $yeu_cau_mat_khau = isset($_POST['yeu_cau_mat_khau']) && $_POST['yeu_cau_mat_khau'] === 'true';
        $mat_khau = trim($_POST['mat_khau'] ?? '');

        if (!$ma_phien || empty($tieu_de)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $result = $this->diemDanhModel->capNhatPhien(
            $ma_phien,
            $this->ma_giao_vien,
            $tieu_de,
            $ghi_chu,
            $thoi_gian_mo,
            $thoi_gian_dong,
            $yeu_cau_mat_khau,
            $mat_khau
        );

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * API MỚI: Xóa phiên điểm danh
     */
    public function xoaPhienApi() {
        header('Content-Type: application/json; charset=utf-8');

        $ma_phien = filter_input(INPUT_POST, 'ma_phien', FILTER_VALIDATE_INT);

        if (!$ma_phien) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu mã phiên'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $result = $this->diemDanhModel->xoaPhien($ma_phien, $this->ma_giao_vien);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }


    //note: phần thời khóa biểu giáo viên ở đây 
    /**
     * ✅ HÀM 1: TRANG CHÍNH XEM LỊCH DẠY
     * URL: /giaovien/lichdayview
     * Hiển thị danh sách lớp để chọn xem TKB
     */
    public function xemTkbByLopApi() {
        header('Content-Type: application/json; charset=utf-8');
        
        $ma_lop = filter_input(INPUT_POST, 'ma_lop', FILTER_VALIDATE_INT);
        $ma_hoc_ky = $_POST['ma_hoc_ky'] ?? '1';  // ✅ ĐỔI: 'HK1' → '1'
        
        if (!$ma_lop) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Thiếu mã lớp'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            $giaoVienModel = $this->loadModel('GiaoVienModel');
            
            // Lấy TKB chi tiết
            $tkb_data = $giaoVienModel->getTkbGVByLop($this->ma_giao_vien, $ma_lop, $ma_hoc_ky);
            
            // Lấy danh sách tiết học
            $tiet_hoc_list = $giaoVienModel->getDanhSachTietHoc();
            
            // Lấy thông tin lớp
            $lop_info = $giaoVienModel->getLopDayByGiaoVien($this->ma_giao_vien);
            $current_lop = null;
            
            foreach ($lop_info as $lop) {
                if ($lop['ma_lop'] == $ma_lop) {
                    $current_lop = $lop;
                    break;
                }
            }
            
            if (!$current_lop) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Lớp không tồn tại hoặc bạn không được phân công'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $tkb_data,
                'tiet_hoc' => $tiet_hoc_list,
                'lop_info' => $current_lop,
                'ma_hoc_ky' => $ma_hoc_ky
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function xemTkbAllApi() {
        header('Content-Type: application/json; charset=utf-8');
        
        $ma_hoc_ky = $_POST['ma_hoc_ky'] ?? '1';  // ✅ ĐỔI: 'HK1' → '1'
        
        try {
            $giaoVienModel = $this->loadModel('GiaoVienModel');
            
            $tkb_all = $giaoVienModel->getTkbGVAll($this->ma_giao_vien, $ma_hoc_ky);
            $tiet_hoc_list = $giaoVienModel->getDanhSachTietHoc();
            $stats = $giaoVienModel->getThongKeTkbGV($this->ma_giao_vien, $ma_hoc_ky);
            
            echo json_encode([
                'success' => true,
                'data' => $tkb_all,
                'tiet_hoc' => $tiet_hoc_list,
                'stats' => $stats,
                'ma_hoc_ky' => $ma_hoc_ky
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function lichdayview() {
        $giaoVienModel = $this->loadModel('GiaoVienModel');
        
        $lop_list = $giaoVienModel->getLopDayByGiaoVien($this->ma_giao_vien);
        $stats = $giaoVienModel->getThongKeTkbGV($this->ma_giao_vien, '1');  // ✅ ĐỔI: 'HK1' → '1'
        
        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Giáo Viên',
            'lop_list' => $lop_list,
            'stats' => $stats,
            'ma_hoc_ky' => '1'  // ✅ ĐỔI
        ];
        
        echo $this->loadView('BGH/quan_ly_lich_day', $data);
    }

}
?>