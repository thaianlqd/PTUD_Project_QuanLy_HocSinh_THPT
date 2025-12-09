<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class LopHocController extends Controller {
    private $model;

    public function __construct() {
        $this->model = $this->loadModel('LopHocModel');
    }

    /**
     * Kiểm tra đủ điều kiện môn tự chọn: tổng >= 4 và mỗi nhóm KHTN/KHXH/CN-NT >= 1
     */
    private function validateTuChon($listPhanCong) {
        $groupCount = ['KHTN' => 0, 'KHXH' => 0, 'CN-NT' => 0];
        $elective = 0;

        foreach ($listPhanCong as $pc) {
            if (empty($pc['ma_mon'])) continue;
            $mon = $this->model->getMonHocById($pc['ma_mon']);
            if (!$mon || $mon['loai_mon'] === 'Bắt buộc') continue;

            $elective++;
            foreach (['KHTN', 'KHXH', 'CN-NT'] as $grp) {
                if (stripos($mon['loai_mon'], $grp) !== false) {
                    $groupCount[$grp]++;
                    break;
                }
            }
        }

        return (
            $elective >= 4 &&
            $groupCount['KHTN'] > 0 &&
            $groupCount['KHXH'] > 0 &&
            $groupCount['CN-NT'] > 0
        );
    }

    /**
     * Hiển thị danh sách lớp học (Trang chính)
     */
    public function index() {
        $ma_truong = $_SESSION['admin_school_id'] ?? 1;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 15;

        $khoi = isset($_GET['khoi']) ? trim($_GET['khoi']) : '';

        $lop_hoc_list = $this->model->getDanhSachLopPaginated($ma_truong, $page, $limit, $khoi);
        $total_lop = $this->model->countLopHoc($ma_truong, $khoi);
        $total_pages = ceil($total_lop / $limit);

        $data = [
            'lop_hoc_list' => $lop_hoc_list,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_lop' => $total_lop,
            'filter_khoi' => $khoi
        ];

        echo $this->loadView('QuanTri/danh_sach_lop', $data);
    }

    /**
     * Hiển thị trang Thêm Lớp Học Mới
     */
    public function create() {
        if (!isset($_SESSION['admin_school_id'])) {
            $_SESSION['admin_school_id'] = 1;
        }
        
        $data['to_hop_list'] = $this->model->getAllToHop();
        $data['ma_nam_hoc'] = $_SESSION['ma_nam_hoc'] ?? 1;
        $data['ma_truong'] = $_SESSION['admin_school_id'];
        
        echo $this->loadView('QuanTri/quan_ly_lophoc', $data); 
    }

    /**
     * AJAX: Sinh tên lớp tự động khi chọn khối
     */
    public function ajaxGenerateTenLop() {
        header('Content-Type: application/json');
        
        $khoi = $_POST['khoi'] ?? null;
        $nam_hoc = $_POST['nam_hoc'] ?? 1;
        
        if (!$khoi) {
            echo json_encode(['error' => 'Thiếu khối']);
            return;
        }
        
        try {
            $ten_lop = $this->model->generateTenLop($khoi, $nam_hoc);
            echo json_encode([
                'success' => true,
                'ten_lop' => $ten_lop
            ]);
        } catch (Exception $e) {
            error_log("Error ajaxGenerateTenLop: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Lấy danh sách phòng học trống
     */
    public function ajaxGetPhongTrong() {
        header('Content-Type: application/json');
        
        $nam_hoc = $_POST['nam_hoc'] ?? 1;
        
        try {
            $phong_trong = $this->model->getPhongHocTrong($nam_hoc);
            echo json_encode([
                'success' => true,
                'phong_trong' => $phong_trong
            ]);
        } catch (Exception $e) {
            error_log("Error ajaxGetPhongTrong: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Lấy danh sách môn học theo tổ hợp + danh sách GV
     */
    public function ajaxGetMonVaGiaoVien() {
        header('Content-Type: application/json');
        
        $ma_to_hop = $_POST['ma_to_hop'] ?? null;
        
        if (!$ma_to_hop) {
            echo json_encode(['success' => false, 'error' => 'Thiếu mã tổ hợp']);
            return;
        }
        
        try {
            $ma_truong = $_SESSION['admin_school_id'] ?? 1;
            
            $mon_hoc = $this->model->getMonHocByToHop($ma_to_hop);
            $giao_vien = $this->model->getDanhSachGiaoVienVaMonDay($ma_truong); // ✅ THAY ĐỔI
            
            // DEBUG LOG
            error_log("Số môn học: " . count($mon_hoc));
            error_log("Số giáo viên: " . count($giao_vien));
            error_log("Danh sách GV: " . json_encode($giao_vien));
            
            echo json_encode([
                'success' => true,
                'mon_hoc' => $mon_hoc,
                'giao_vien' => $giao_vien,
                'debug' => [
                    'count_mon' => count($mon_hoc),
                    'count_gv' => count($giao_vien)
                ]
            ]);
        } catch (Exception $e) {
            error_log("Lỗi ajaxGetMonVaGiaoVien: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Lưu lớp học mới
     */
    public function store() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        try {
            // Validation
            $required = ['khoi', 'ma_to_hop', 'ma_phong'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
                    return;
                }
            }

            // Tự động sinh tên lớp nếu không có
            $ten_lop = $_POST['ten_lop'] ?? null;
            if (empty($ten_lop)) {
                $ten_lop = $this->model->generateTenLop(
                    $_POST['khoi'], 
                    $_POST['ma_nam_hoc'] ?? 1
                );
            }

            $dataLop = [
                'ten_lop' => $ten_lop,
                'khoi' => $_POST['khoi'],
                'ma_to_hop' => $_POST['ma_to_hop'],
                'ma_nam_hoc' => $_POST['ma_nam_hoc'] ?? 1,
                'ma_truong' => $_SESSION['admin_school_id'] ?? 1,
                'ma_phong_hoc_chinh' => $_POST['ma_phong'],
                'ma_gvcn' => $_POST['ma_gvcn'] ?? null
            ];

            // Build phân công list
            $listPhanCong = [];
            if (isset($_POST['mon_id']) && is_array($_POST['mon_id'])) {
                foreach ($_POST['mon_id'] as $idx => $ma_mon) {
                    $ma_gv = $_POST['giao_vien_id'][$idx] ?? null;
                    if (!empty($ma_mon) && !empty($ma_gv)) {
                        $listPhanCong[] = [
                            'ma_mon' => $ma_mon,
                            'ma_gv' => $ma_gv,
                            'ten_mon' => $_POST['mon_ten'][$idx] ?? 'Unknown',
                            'so_tiet' => $_POST['mon_so_tiet'][$idx] ?? 3
                        ];
                    }
                }
            }

            if (empty($listPhanCong)) {
                echo json_encode(['success' => false, 'error' => 'Phải phân công ít nhất 1 giáo viên!']);
                return;
            }

            if (!$this->validateTuChon($listPhanCong)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Cần ≥4 môn tự chọn và mỗi nhóm KHTN/KHXH/CN-NT ≥1 môn.'
                ]);
                return;
            }

            // Tạo lớp
            $result = $this->model->createLopFull($dataLop, $listPhanCong);
            
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            error_log("Error store: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'error' => 'Lỗi hệ thống: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Xóa lớp học (AJAX)
     */
    public function delete() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            $ma_lop = $data['ma_lop'] ?? null;
            if (empty($ma_lop)) {
                echo json_encode(['success' => false, 'message' => 'Mã lớp không được để trống']);
                return;
            }

            $result = $this->model->deleteLopHoc($ma_lop);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Xóa lớp học thành công!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Xóa lớp học thất bại!'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            error_log("Error delete: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Hiển thị trang Sửa Lớp Học
     */
    /**
     * AJAX: Lấy dữ liệu lớp để hiển thị trong Modal sửa
     */
    public function ajaxGetLopData() {
        header('Content-Type: application/json');
        
        $ma_lop = $_POST['ma_lop'] ?? null;
        
        if (!$ma_lop) {
            echo json_encode(['success' => false, 'error' => 'Thiếu mã lớp']);
            return;
        }
        
        try {
            $ma_truong = $_SESSION['admin_school_id'] ?? 1;
            
            // Lấy thông tin lớp
            $lop = $this->model->getLopHocById($ma_lop);
            
            if (!$lop) {
                echo json_encode(['success' => false, 'error' => 'Không tìm thấy lớp']);
                return;
            }
            
            // Lấy phân công hiện tại
            $phan_cong = $this->model->getPhanCongByLop($ma_lop);
            
            // Lấy dropdown data
            $to_hop_list = $this->model->getAllToHop();
            $mon_hoc = $this->model->getMonHocByToHop($lop['ma_to_hop_mon']);
            $phong_trong = $this->model->getPhongHocTrong($_SESSION['ma_nam_hoc'] ?? 1);
            $giao_vien = $this->model->getDanhSachGiaoVienVaMonDay($ma_truong);
            
            echo json_encode([
                'success' => true,
                'lop' => $lop,
                'phan_cong' => $phan_cong,
                'to_hop_list' => $to_hop_list,
                'mon_hoc' => $mon_hoc,
                'phong_trong' => $phong_trong,
                'giao_vien' => $giao_vien
            ]);
            
        } catch (Exception $e) {
            error_log("Error ajaxGetLopData: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    

    /**
     * Cập nhật lớp học (AJAX)
     */
    public function update() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            // Validation
            $ma_lop = $_POST['ma_lop'] ?? null;
            if (empty($ma_lop)) {
                echo json_encode(['success' => false, 'message' => 'Mã lớp không được để trống']);
                return;
            }

            $required = ['ten_lop', 'khoi', 'ma_to_hop', 'ma_phong'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => "Trường $field không được để trống"]);
                    return;
                }
            }

            // Dữ liệu lớp
            $dataLop = [
                'ten_lop' => trim($_POST['ten_lop']),
                'khoi' => (int)$_POST['khoi'],
                'ma_to_hop' => (int)$_POST['ma_to_hop'],
                'ma_phong_hoc_chinh' => (int)$_POST['ma_phong'],
                'ma_gvcn' => !empty($_POST['ma_gvcn']) ? (int)$_POST['ma_gvcn'] : null,
                'trang_thai_lop' => $_POST['trang_thai_lop'] ?? 'HoatDong'
            ];

            // Build phân công list
            $listPhanCong = [];
            if (isset($_POST['mon_id']) && is_array($_POST['mon_id'])) {
                foreach ($_POST['mon_id'] as $idx => $ma_mon) {
                    $ma_gv = $_POST['giao_vien_id'][$idx] ?? null;
                    
                    // Chỉ thêm nếu cả môn và GV đều được chọn
                    if (!empty($ma_mon) && !empty($ma_gv)) {
                        $listPhanCong[] = [
                            'ma_mon' => (int)$ma_mon,
                            'ma_gv' => (int)$ma_gv,
                            'ten_mon' => $_POST['mon_ten'][$idx] ?? 'Unknown',
                            'so_tiet' => !empty($_POST['mon_so_tiet'][$idx]) ? (int)$_POST['mon_so_tiet'][$idx] : 3
                        ];
                    }
                }
            }

            // Kiểm tra có phân công hay không
            if (empty($listPhanCong)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Phải phân công ít nhất 1 giáo viên!'
                ]);
                return;
            }

            if (!$this->validateTuChon($listPhanCong)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cần ≥4 môn tự chọn và mỗi nhóm KHTN/KHXH/CN-NT ≥1 môn.'
                ]);
                return;
            }

            // Gọi Model để cập nhật
            $result = $this->model->updateLopFull($ma_lop, $dataLop, $listPhanCong);
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            http_response_code(500);
            error_log("Error update: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX: Lấy môn học + phân công hiện tại của lớp (dùng khi sửa)
     */
    public function ajaxGetDataForEdit() {
        header('Content-Type: application/json');
        
        $ma_lop = $_POST['ma_lop'] ?? null;
        $ma_to_hop = $_POST['ma_to_hop'] ?? null;
        
        if (!$ma_lop || !$ma_to_hop) {
            echo json_encode(['success' => false, 'error' => 'Thiếu tham số']);
            return;
        }
        
        try {
            $ma_truong = $_SESSION['admin_school_id'] ?? 1;
            
            // Lấy danh sách môn theo tổ hợp MỚI (nếu user thay đổi tổ hợp)
            $mon_hoc = $this->model->getMonHocByToHop($ma_to_hop);
            
            // Lấy phân công HIỆN TẠI của lớp
            $phan_cong = $this->model->getPhanCongByLop($ma_lop);
            
            // Lấy tất cả GV
            $giao_vien = $this->model->getDanhSachGiaoVienVaMonDay($ma_truong);
            
            echo json_encode([
                'success' => true,
                'mon_hoc' => $mon_hoc,
                'phan_cong' => $phan_cong,
                'giao_vien' => $giao_vien
            ]);
            
        } catch (Exception $e) {
            error_log("Error ajaxGetDataForEdit: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function view() {
        $ma_lop = $_GET['id'] ?? null;
        
        if (!$ma_lop) {
            header('Location: ' . BASE_URL . '/LopHoc');
            exit;
        }
        
        try {
            $lop = $this->model->getLopHocById($ma_lop);
            $phan_cong = $this->model->getPhanCongByLop($ma_lop);
            
            if (!$lop) {
                $_SESSION['error'] = 'Không tìm thấy lớp!';
                header('Location: ' . BASE_URL . '/LopHoc');
                exit;
            }
            
            $data = [
                'lop' => $lop,
                'phan_cong' => $phan_cong
            ];
            
            echo $this->loadView('QuanTri/xem_chi_tiet_lop', $data);  // ✅ THÊM echo
            
        } catch (Exception $e) {
            error_log("Error view: " . $e->getMessage());
            $_SESSION['error'] = 'Lỗi hệ thống!';
            header('Location: ' . BASE_URL . '/LopHoc');
            exit;
        }
    }

    


    
}
?>