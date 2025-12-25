<?php
if (session_status() === PHP_SESSION_NONE) session_start();

class DiemSoController extends Controller {
    
    // public function nhap() {
    //     // Kiểm tra quyền Giáo viên hoặc Ban Giám Hiệu
    //     if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['GiaoVien', 'BanGiamHieu'])) {
    //         header('Content-Type: application/json');
    //         echo json_encode(['success' => false, 'message' => 'Không có quyền.']);
    //         exit;
    //     }
        
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $ma_hoc_sinh = $_POST['ma_hoc_sinh'] ?? '';
    //         $ma_mon_hoc = $_POST['ma_mon_hoc'] ?? '';
    //         $ma_hoc_ky = $_POST['ma_hoc_ky'] ?? 'HK1';
            
    //         $data = [
    //             'diem_mieng' => !empty($_POST['diem_mieng']) ? floatval($_POST['diem_mieng']) : null,
    //             'diem_15phut' => !empty($_POST['diem_15phut']) ? floatval($_POST['diem_15phut']) : null,
    //             'diem_1tiet' => !empty($_POST['diem_1tiet']) ? floatval($_POST['diem_1tiet']) : null,
    //             'diem_gua_ky' => !empty($_POST['diem_gua_ky']) ? floatval($_POST['diem_gua_ky']) : null,
    //             'diem_cuoi_ky' => !empty($_POST['diem_cuoi_ky']) ? floatval($_POST['diem_cuoi_ky']) : null
    //         ];
            
    //         $model = $this->loadModel('DiemSoModel');
    //         $result = $model->nhapDiemMon($ma_hoc_sinh, $ma_mon_hoc, $ma_hoc_ky, $data);
            
    //         header('Content-Type: application/json');
    //         echo json_encode($result);
    //         exit;
    //     }
    // }

    public function nhap() {
        // Chỉ cho phép POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ma_hoc_sinh = $_POST['ma_hoc_sinh'] ?? '';
            $ma_mon_hoc = $_POST['ma_mon_hoc'] ?? '';
            $ma_hoc_ky = $_POST['ma_hoc_ky'] ?? 'HK1';
            
            // Nhận đúng tên Key từ FormData
            $data = [
                'diem_mieng'   => isset($_POST['diem_mieng']) ? floatval($_POST['diem_mieng']) : null,
                'diem_15phut'  => isset($_POST['diem_15phut']) ? floatval($_POST['diem_15phut']) : null,
                'diem_1tiet'   => isset($_POST['diem_1tiet']) ? floatval($_POST['diem_1tiet']) : null,
                'diem_gua_ky'  => isset($_POST['diem_gua_ky']) ? floatval($_POST['diem_gua_ky']) : null,
                'diem_cuoi_ky' => isset($_POST['diem_cuoi_ky']) ? floatval($_POST['diem_cuoi_ky']) : null
            ];
            
            $model = $this->loadModel('DiemSoModel');
            $result = $model->nhapDiemMon($ma_hoc_sinh, $ma_mon_hoc, $ma_hoc_ky, $data);
            
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }
    }
    
    /**
     * Gửi phiếu yêu cầu chỉnh sửa điểm (Giáo viên hoặc Ban Giám Hiệu)
     */
    // public function guiPhieuChinhSua() {
    //     if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['GiaoVien', 'BanGiamHieu'])) {
    //         header('Content-Type: application/json');
    //         echo json_encode(['success' => false, 'message' => 'Không có quyền.']);
    //         exit;
    //     }
        
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $ma_gv = $_SESSION['user_id'] ?? 0;
    //         $ma_hoc_sinh = $_POST['ma_hoc_sinh'] ?? '';
    //         $ma_mon_hoc = $_POST['ma_mon_hoc'] ?? '';
    //         $ma_hoc_ky = $_POST['ma_hoc_ky'] ?? 'HK1';
    //         $ly_do = $_POST['ly_do'] ?? '';
            
    //         $data_moi = [
    //             'diem_mieng' => !empty($_POST['diem_mieng']) ? floatval($_POST['diem_mieng']) : null,
    //             'diem_15phut' => !empty($_POST['diem_15phut']) ? floatval($_POST['diem_15phut']) : null,
    //             'diem_1tiet' => !empty($_POST['diem_1tiet']) ? floatval($_POST['diem_1tiet']) : null,
    //             'diem_gua_ky' => !empty($_POST['diem_gua_ky']) ? floatval($_POST['diem_gua_ky']) : null,
    //             'diem_cuoi_ky' => !empty($_POST['diem_cuoi_ky']) ? floatval($_POST['diem_cuoi_ky']) : null
    //         ];
            
    //         $model = $this->loadModel('DiemSoModel');
    //         $result = $model->guiPhieuChinhSuaDiem($ma_gv, $ma_hoc_sinh, $ma_mon_hoc, $ma_hoc_ky, $data_moi, $ly_do);
            
    //         header('Content-Type: application/json');
    //         echo json_encode($result);
    //         exit;
    //     }
    // }
    public function guiPhieuChinhSua() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ma_gv = $_SESSION['user_id'] ?? 0;
            $ma_hoc_sinh = $_POST['ma_hoc_sinh'] ?? '';
            $ma_mon_hoc = $_POST['ma_mon_hoc'] ?? '';
            $ma_hoc_ky = $_POST['ma_hoc_ky'] ?? 'HK1';
            $ly_do = $_POST['ly_do'] ?? '';
            
            // Dùng toán tử ba ngôi kiểm tra isset và rỗng để không mất điểm 0
            $data_moi = [
                'diem_mieng'   => (isset($_POST['diem_mieng']) && $_POST['diem_mieng'] !== '') ? floatval($_POST['diem_mieng']) : null,
                'diem_15phut'  => (isset($_POST['diem_15phut']) && $_POST['diem_15phut'] !== '') ? floatval($_POST['diem_15phut']) : null,
                'diem_1tiet'   => (isset($_POST['diem_1tiet']) && $_POST['diem_1tiet'] !== '') ? floatval($_POST['diem_1tiet']) : null,
                'diem_gua_ky'  => (isset($_POST['diem_gua_ky']) && $_POST['diem_gua_ky'] !== '') ? floatval($_POST['diem_gua_ky']) : null,
                'diem_cuoi_ky' => (isset($_POST['diem_cuoi_ky']) && $_POST['diem_cuoi_ky'] !== '') ? floatval($_POST['diem_cuoi_ky']) : null
            ];
            
            $model = $this->loadModel('DiemSoModel');
            $result = $model->guiPhieuChinhSuaDiem($ma_gv, $ma_hoc_sinh, $ma_mon_hoc, $ma_hoc_ky, $data_moi, $ly_do);
            
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }
    }
    
    /**
     * Duyệt phiếu chỉnh sửa điểm (Ban Giám Hiệu)
     */
    public function duyetPhieuChinhSua() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'BanGiamHieu') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không có quyền.']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ma_phieu = $_POST['ma_phieu'] ?? '';
            $ma_nguoi_duyet = $_SESSION['user_id'] ?? 0;
            
            $model = $this->loadModel('DiemSoModel');
            $result = $model->duyetPhieuChinhSuaMoi($ma_phieu, $ma_nguoi_duyet);
            
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }
    }
    
    /**
     * Từ chối phiếu chỉnh sửa điểm (Ban Giám Hiệu)
     */
    public function tuChoiPhieuChinhSua() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'BanGiamHieu') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không có quyền.']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ma_phieu = $_POST['ma_phieu'] ?? '';
            $ma_nguoi_duyet = $_SESSION['user_id'] ?? 0;
            $ly_do = $_POST['ly_do'] ?? '';
            
            $model = $this->loadModel('DiemSoModel');
            $result = $model->tuChoiPhieuChinhSuaMoi($ma_phieu, $ma_nguoi_duyet, $ly_do);
            
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }
    }

    /**
     * Xem danh sách phiếu của GV (API JSON)
     */
    public function danhSachPhieuCuaToi() {
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['GiaoVien', 'BanGiamHieu'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không có quyền.']);
            exit;
        }
        
        $ma_gv = $_SESSION['user_id'] ?? 0;
        $trang_thai = $_GET['trang_thai'] ?? 'TatCa'; // ChoDuyet, DaDuyet, TuChoi, TatCa
        
        $model = $this->loadModel('DiemSoModel');
        // ✅ SỬA: Gọi hàm mới getDanhSachPhieuCuaGV (có logic BGH xem tất cả)
        $data = $model->getDanhSachPhieuByGV($ma_gv, $trang_thai);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

}
?>