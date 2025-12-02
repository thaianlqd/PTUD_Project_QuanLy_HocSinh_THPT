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
        // 1. KIỂM TRA QUYỀN & ID
        $is_logged_in = isset($_SESSION['user_role']) && 
                          $_SESSION['user_role'] == 'GiaoVien' && 
                          isset($_SESSION['user_id']) && 
                          $_SESSION['user_id'] > 0;

        if (!$is_logged_in) {
            // (Giả sử bạn đã thêm hàm is_ajax() vào file Controller.php cha)
            if ($this->is_ajax()) { 
                http_response_code(401); 
                echo json_encode(['success' => false, 'message' => 'Phiên đăng nhập không hợp lệ hoặc đã hết hạn. Vui lòng tải lại trang và đăng nhập lại.']);
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
     * API: Tạo phiên điểm danh (2 loại)
     */
    public function taoPhienApi() {
        header('Content-Type: application/json');
        $ma_lop = filter_input(INPUT_POST, 'ma_lop', FILTER_VALIDATE_INT);
        $tieu_de = trim($_POST['tieu_de'] ?? '');
        $ghi_chu = trim($_POST['ghi_chu'] ?? '');
        $loai_phien = $_POST['loai_phien'] ?? 'GiaoVien'; 
        $thoi_gian_mo = $_POST['thoi_gian_mo'] ?? null;
        $thoi_gian_dong = $_POST['thoi_gian_dong'] ?? null;

        if (!$ma_lop || empty($tieu_de)) {
             http_response_code(400);
             echo json_encode(['success' => false, 'message' => 'Thiếu mã lớp hoặc tiêu đề.']);
             return;
        }

        $ma_phien = $this->diemDanhModel->taoPhienDiemDanhMoi($ma_lop, $this->ma_giao_vien, $tieu_de, $ghi_chu, $loai_phien, $thoi_gian_mo, $thoi_gian_dong);
        
        if ($ma_phien) {
            if ($loai_phien == 'GiaoVien') {
                echo json_encode(['success'=>true, 'message'=>'Đã tạo phiên thủ công.', 'ma_phien_moi' => $ma_phien]);
            } else {
                echo json_encode(['success'=>true, 'message'=>'Đã tạo phiên tự động cho HS.']);
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
}
?>