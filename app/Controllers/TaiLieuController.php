<?php
if (session_status() === PHP_SESSION_NONE) session_start();

class TaiLieuController extends Controller {
    private $taiLieuModel;
    private $uploadDir = './uploads/tai_lieu/';

    public function __construct() {
        if (!is_dir($this->uploadDir)) {
            @mkdir($this->uploadDir, 0755, true);
        }
    }

    private function getTaiLieuModel() {
        if (!$this->taiLieuModel) {
            try {
                $this->taiLieuModel = $this->loadModel('TaiLieuModel');
            } catch (Exception $e) {
                die("Lỗi: Không tìm thấy TaiLieuModel.php<br>" . $e->getMessage());
            }
        }
        return $this->taiLieuModel;
    }

    /**
     * Lấy danh sách môn học của GV (AJAX)
     */
    public function getMonHocOfGV() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(['success' => false, 'message' => 'Method không hợp lệ']);
            exit;
        }

        $ma_giao_vien = $_SESSION['user_id'] ?? null;
        
        if (!$ma_giao_vien) {
            echo json_encode(['success' => false, 'message' => 'Không xác định được giáo viên']);
            exit;
        }

        try {
            // ✅ Dùng TaiLieuModel
            $model = $this->getTaiLieuModel();
            $mon_hoc_list = $model->getMonGiangDay($ma_giao_vien);
            
            echo json_encode([
                'success' => true,
                'data' => $mon_hoc_list
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Trang quản lý tài liệu (GV/BGH)
     * FIX #1: Cho phép cả GiaoVien và BanGiamHieu
     */
    public function quanly() {
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['GiaoVien', 'BanGiamHieu'])) {
            header('Location: ' . BASE_URL . '/auth/index');
            exit;
        }
        
        $ma_giao_vien = $_SESSION['user_id'];
        $model = $this->getTaiLieuModel();
        
        // ✅ Khởi tạo mặc định là array
        $mon_hoc_list = [];
        
        try {
            // ✅ Dùng TaiLieuModel
            $result = $model->getMonGiangDay($ma_giao_vien);
            
            // ✅ Kiểm tra type
            if (is_array($result)) {
                $mon_hoc_list = $result;
            } else {
                error_log("WARNING: getMonGiangDay không trả array, type=" . gettype($result));
                $mon_hoc_list = [];
            }
        } catch (Exception $e) {
            error_log("Lỗi load môn học: " . $e->getMessage());
            $mon_hoc_list = [];
        }
        
        $taiLieu_list = $model->getDanhSachTaiLieuByGiaoVien($ma_giao_vien) ?? [];
        
        $data = [
            'user_name'     => $_SESSION['user_name'] ?? 'Giáo Viên',
            'mon_hoc_list'  => $mon_hoc_list,
            'taiLieu_list'  => $taiLieu_list,
            'taiLieu_count' => count($taiLieu_list)
        ];
        
        echo $this->loadView('BGH/quan_ly_tai_lieu', $data);
    }


    /**
     * Lấy chi tiết tài liệu (AJAX)
     */
    public function getChiTiet($ma_tai_lieu) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(['success' => false, 'message' => 'Method không hợp lệ']);
            exit;
        }

        $ma_giao_vien = $_SESSION['user_id'] ?? null;
        
        if (!$ma_giao_vien) {
            echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
            exit;
        }

        try {
            $model = $this->getTaiLieuModel();
            $tl = $model->getChiTietTaiLieu($ma_tai_lieu);  // ← Gọi hàm Model
            
            if (!$tl) {
                echo json_encode(['success' => false, 'message' => 'Tài liệu không tồn tại']);
                exit;
            }
            
            // Kiểm tra quyền (GV chỉ xem tài liệu của mình)
            if ($tl['ma_giao_vien'] != $ma_giao_vien) {
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xem tài liệu này']);
                exit;
            }
            
            echo json_encode(['success' => true, 'data' => $tl]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
        }
    }

    /**
     * Upload file (AJAX)
     * FIX #2: Bắt buộc nhập tên tài liệu
     */
    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method không hợp lệ']);
            exit;
        }

        $ma_giao_vien = $_SESSION['user_id'] ?? null;
        
        if (!$ma_giao_vien) {
            echo json_encode(['success' => false, 'message' => 'Không xác định được giáo viên']);
            exit;
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Lỗi upload file: ' . ($_FILES['file']['error'] ?? 'Không rõ')]);
            exit;
        }

        $file = $_FILES['file'];
        
        // ✅ FIX: Bắt buộc nhập tên tài liệu
        $ten_tai_lieu = trim($_POST['ten_tai_lieu'] ?? '');
        if ($ten_tai_lieu === '') {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên tài liệu']);
            exit;
        }
        
        $mo_ta = $_POST['mo_ta'] ?? '';
        $loai_tai_lieu = $_POST['loai_tai_lieu'] ?? 'Bài giảng';
        $ma_mon_hoc = $_POST['ma_mon_hoc'] ?? null;
        $ghi_chu = $_POST['ghi_chu'] ?? '';

        if (!$ma_mon_hoc) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn môn học']);
            exit;
        }

        $maxSize = 50 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'File quá lớn (Max 50MB)']);
            exit;
        }

        $allowedMimes = ['application/pdf', 'application/msword', 
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'text/plain', 'image/jpeg', 'image/png', 'image/gif',
                        'application/zip', 'application/x-rar-compressed'];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedMimes)) {
            echo json_encode(['success' => false, 'message' => 'Loại file không được phép']);
            exit;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $fullPath = $this->uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu file']);
            exit;
        }

        $model = $this->getTaiLieuModel();
        $result = $model->addTaiLieu([
            'ten_tai_lieu' => $ten_tai_lieu,
            'mo_ta' => $mo_ta,
            'loai_tai_lieu' => $loai_tai_lieu,
            'file_dinh_kem' => $fileName,
            'ghi_chu' => $ghi_chu,
            'ma_giao_vien' => $ma_giao_vien,
            'ma_mon_hoc' => $ma_mon_hoc
        ]);

        echo json_encode($result);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method không hợp lệ']);
            exit;
        }

        $ma_giao_vien = $_SESSION['user_id'] ?? null;
        
        $data = [
            'ma_tai_lieu' => $_POST['ma_tai_lieu'],
            'ten_tai_lieu' => $_POST['ten_tai_lieu'],
            'mo_ta' => $_POST['mo_ta'] ?? '',
            'loai_tai_lieu' => $_POST['loai_tai_lieu'],
            'ghi_chu' => $_POST['ghi_chu'] ?? '',
            'ma_giao_vien' => $ma_giao_vien
        ];

        $model = $this->getTaiLieuModel();
        $result = $model->updateTaiLieu($data);
        echo json_encode($result);
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method không hợp lệ']);
            exit;
        }

        $ma_giao_vien = $_SESSION['user_id'] ?? null;
        $ma_tai_lieu = $_POST['ma_tai_lieu'] ?? null;

        if (!$ma_tai_lieu) {
            echo json_encode(['success' => false, 'message' => 'Mã tài liệu không hợp lệ']);
            exit;
        }

        $model = $this->getTaiLieuModel();
        $result = $model->deleteTaiLieu($ma_tai_lieu, $ma_giao_vien);
        echo json_encode($result);
    }

    /**
     * Tải tài liệu
     * FIX #3: Thêm kiểm tra đăng nhập và quyền truy cập
     */
    public function download($ma_tai_lieu) {
        // ✅ FIX: Kiểm tra đăng nhập
        if (!isset($_SESSION['user_role'])) {
            die('Vui lòng đăng nhập để tải tài liệu');
        }

        $model = $this->getTaiLieuModel();
        $tl = $model->getChiTietTaiLieu($ma_tai_lieu);
        
        if (!$tl) {
            die('Tài liệu không tồn tại');
        }

        // ✅ FIX: Kiểm tra quyền truy cập
        $role = $_SESSION['user_role'];
        $user_id = $_SESSION['user_id'] ?? null;

        if (in_array($role, ['GiaoVien', 'BanGiamHieu'])) {
            // GV chỉ tải tài liệu của mình, BGH tải tất cả
            if ($role === 'GiaoVien' && $tl['ma_giao_vien'] != $user_id) {
                die('Bạn không có quyền tải tài liệu này');
            }
        } elseif (in_array($role, ['HocSinh', 'PhuHuynh'])) {
            // HS/PH chỉ tải tài liệu đang hiển thị
            // (không có trang_thai nên bỏ check này)
        }

        // ✅ FIX: Dùng uploadDir + file_dinh_kem
        $filePath = $this->uploadDir . $tl['file_dinh_kem'];
        
        if (!file_exists($filePath)) {
            error_log("File not found: " . $filePath);
            die('File không tồn tại: ' . $filePath);
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    // public function hienThi($ma_mon_hoc) {
    //     $model = $this->getTaiLieuModel();
        
    //     $data = [
    //         'taiLieu_list' => $model->getDanhSachTaiLieuByMonHoc($ma_mon_hoc)
    //     ];
        
    //     echo $this->loadView('HocSinh/tai_lieu', $data);
    // }


    /**
     * Hiển thị tài liệu cho Học sinh
     */
    public function hienThi() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'HocSinh') {
            header('Location: ' . BASE_URL . '/auth/index');
            exit;
        }

        $ma_hoc_sinh = $_SESSION['user_id'] ?? null;
        if (!$ma_hoc_sinh) {
            echo 'Không xác định được học sinh';
            return;
        }

        $model = $this->getTaiLieuModel();

        // Lấy thông tin HS và lớp (fallback từ session)
        $hs_info = $model->getHocSinhInfo($ma_hoc_sinh);
        $ma_lop  = $hs_info['ma_lop'] ?? ($_SESSION['ma_lop'] ?? $_SESSION['user_lop'] ?? null);
        $ten_lop = $hs_info['ten_lop'] ?? ($_SESSION['user_class_name'] ?? '---');

        $tai_lieu_list = [];
        $mon_hoc_list  = [];

        if ($ma_lop) {
            $mon_hoc_list = $model->getMonHocCuaLop($ma_lop) ?? [];
            foreach ($mon_hoc_list as $mon) {
                $docs = $model->getDanhSachTaiLieuByMonHoc($mon['ma_mon_hoc']);
                if ($docs) $tai_lieu_list = array_merge($tai_lieu_list, $docs);
            }
        }

        $data = [
            'tai_lieu_list' => $tai_lieu_list,
            'mon_hoc_list'  => $mon_hoc_list,
            'ten_lop'       => $ten_lop,
        ];

        echo $this->loadView('HocSinh/tai_lieu_hoc_tap', $data);
    }


    
}
?>