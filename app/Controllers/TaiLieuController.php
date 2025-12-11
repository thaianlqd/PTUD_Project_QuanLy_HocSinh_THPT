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

        // Load GiaoVienBaiTapModel để lấy danh sách môn
        try {
            $gvBaiTapModel = $this->loadModel('GiaoVienBaiTapModel');
            $mon_hoc_list = $gvBaiTapModel->getMonGiangDay($ma_giao_vien);
            
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
     * Trang quản lý tài liệu (GV)
     */
    public function quanly() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'GiaoVien') {
            header('Location: ' . BASE_URL . '/auth/index');
            exit;
        }
        
        $ma_giao_vien = $_SESSION['user_id'];
        $model = $this->getTaiLieuModel();
        
        // ✅ THÊM: Load danh sách môn học của GV
        try {
            $gvBaiTapModel = $this->loadModel('GiaoVienBaiTapModel');
            $mon_hoc_list = $gvBaiTapModel->getMonGiangDay($ma_giao_vien);
        } catch (Exception $e) {
            $mon_hoc_list = [];
        }
        
        $data = [
            'user_name' => $_SESSION['user_name'] ?? 'Giáo Viên',
            'taiLieu_list' => $model->getDanhSachTaiLieuByGiaoVien($ma_giao_vien),
            'taiLieu_count' => $model->countTaiLieuByGiaoVien($ma_giao_vien),
            'mon_hoc_list' => $mon_hoc_list  // ✅ THÊM
        ];
        
        echo $this->loadView('BGH/quan_ly_tai_lieu', $data);
    }

    /**
     * Upload file (AJAX)
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
        $ten_tai_lieu = $_POST['ten_tai_lieu'] ?? 'Tài liệu không tên';
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
            'duong_dan_file' => $fullPath,
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

    public function download($ma_tai_lieu) {
        $model = $this->getTaiLieuModel();
        $tl = $model->getChiTietTaiLieu($ma_tai_lieu);
        
        if (!$tl) {
            die('Tài liệu không tồn tại');
        }

        $filePath = $tl['duong_dan_file'];
        
        if (!file_exists($filePath)) {
            die('File không tồn tại');
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public function hienThi($ma_mon_hoc) {
        $model = $this->getTaiLieuModel();
        
        $data = [
            'taiLieu_list' => $model->getDanhSachTaiLieuByMonHoc($ma_mon_hoc)
        ];
        
        echo $this->loadView('HocSinh/tai_lieu', $data);
    }
}
?>