<?php
/**
 * PhuHuynhModel: Xử lý logic CSDL cho Phụ Huynh
 */
class PhuHuynhModel {
    private $db;
    private $ma_hoc_sinh_con; // Mã HS của phụ huynh này

    public function __construct() {
        // Danh sách các port cần thử (Ưu tiên 3307 trước, nếu lỗi thì thử 3306)
        $ports = [3307, 3306]; 
        $connected = false;

        foreach ($ports as $port) {
            try {
                $dsn = "mysql:host=127.0.0.1;port=$port;dbname=thpt_manager;charset=utf8mb4";
                $this->db = new PDO($dsn, 'root', '');
                
                // Cấu hình PDO chuẩn
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                // QUAN TRỌNG: Để true để dùng lại được tham số (ví dụ :username dùng 3 lần)
                $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true); 
                
                $this->db->exec("SET NAMES 'utf8mb4'");
                
                $connected = true;
                break; 
            } catch (PDOException $e) {
                continue;
            }
        }

        if (!$connected) {
            die("Lỗi: Không thể kết nối CSDL (Port 3306/3307). Kiểm tra XAMPP.");
        }
    }

    /**
     * Tìm xem phụ huynh này là cha mẹ của HS nào.
     * (Hiện chưa có bảng 'phu_huynh_hoc_sinh' nên tạm giả định lấy HS có ID=2)
     */
    private function getMaHocSinhCuaPhuHuynh($ma_phu_huynh) {
        $sql = "SELECT ma_hoc_sinh FROM hoc_sinh WHERE ma_hoc_sinh = 2 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $this->ma_hoc_sinh_con = $stmt->fetchColumn();
        return $this->ma_hoc_sinh_con;
    }

    /**
     * Lấy thông tin cơ bản (Tên con, tên lớp)
     */
    public function getHocSinhInfo($ma_phu_huynh) {
        $ma_hs = $this->getMaHocSinhCuaPhuHuynh($ma_phu_huynh);
        if (!$ma_hs) return ['ten_con' => 'Chưa liên kết', 'ten_lop' => 'N/A'];

        $sql = "SELECT nd.ho_ten AS ten_con, l.ten_lop 
                FROM hoc_sinh hs
                JOIN nguoi_dung nd ON hs.ma_hoc_sinh = nd.ma_nguoi_dung
                LEFT JOIN lop_hoc l ON hs.ma_lop = l.ma_lop
                WHERE hs.ma_hoc_sinh = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_hs]);
        return $stmt->fetch();
    }

    /**
     * Đếm số hóa đơn chưa thanh toán
     */
    public function getHoaDonCount($ma_phu_huynh) {
        $sql = "SELECT COUNT(*) FROM hoa_don 
                WHERE ma_nguoi_dung = ? AND trang_thai_hoa_don = 'ChuaThanhToan'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_phu_huynh]);
        return $stmt->fetchColumn() ?? 0;
    }

    /**
     * Đếm số phiếu vắng chờ duyệt (của con)
     */
    public function getPhieuVangCount($ma_phu_huynh) {
        $ma_hs = $this->getMaHocSinhCuaPhuHuynh($ma_phu_huynh);
        if (!$ma_hs) return 0;
        
        $sql = "SELECT COUNT(*) FROM phieu_xin_nghi_hoc 
                WHERE ma_nguoi_dung = ? AND trang_thai_don = 'ChoDuyet'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_hs]);
        return $stmt->fetchColumn() ?? 0;
    }

    /**
     * Lấy bảng điểm trung bình các môn (của con)
     */
    public function getBangDiem($ma_phu_huynh) {
        $ma_hs = $this->getMaHocSinhCuaPhuHuynh($ma_phu_huynh);
        if (!$ma_hs) return [];

        // Lấy mã 'ket_qua_hoc_tap' của HS đó
        $stmt_kqht = $this->db->prepare("SELECT ma_ket_qua_hoc_tap FROM ket_qua_hoc_tap WHERE ma_nguoi_dung = ? LIMIT 1");
        $stmt_kqht->execute([$ma_hs]);
        $ma_kqht = $stmt_kqht->fetchColumn();
        if (!$ma_kqht) return [];
        
        // Lấy tất cả điểm của mã KQHT đó
        $sql = "SELECT 
                    mh.ten_mon_hoc,
                    ds.diem_so,
                    ds.loai_diem
                FROM diem_so ds
                JOIN mon_hoc mh ON ds.ma_mon_hoc = mh.ma_mon_hoc
                WHERE ds.ma_ket_qua_hoc_tap = ?
                ORDER BY mh.ten_mon_hoc";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_kqht]);
        
        // Gộp điểm theo từng môn
        $bang_diem = [];
        foreach ($stmt->fetchAll() as $row) {
            $mon = $row['ten_mon_hoc'];
            if (!isset($bang_diem[$mon])) {
                $bang_diem[$mon] = [
                    'DiemMieng' => [], 
                    'Diem15Phut' => [], 
                    'Diem1Tiet' => [], 
                    'DiemHocKy' => [], 
                    'TB' => 0
                ];
            }
            $bang_diem[$mon][$row['loai_diem']][] = $row['diem_so'];
        }
        
        // Tính điểm trung bình
        foreach ($bang_diem as $mon => &$diem) {
            $tong = 0; 
            $so_luong = 0;
            if (!empty($diem['DiemMieng'])) { 
                $tong += array_sum($diem['DiemMieng']); 
                $so_luong += count($diem['DiemMieng']); 
            }
            if (!empty($diem['Diem15Phut'])) { 
                $tong += array_sum($diem['Diem15Phut']); 
                $so_luong += count($diem['Diem15Phut']); 
            }
            if (!empty($diem['Diem1Tiet'])) { 
                $tong += array_sum($diem['Diem1Tiet']) * 2; 
                $so_luong += count($diem['Diem1Tiet']) * 2; 
            }
            if (!empty($diem['DiemHocKy'])) { 
                $tong += array_sum($diem['DiemHocKy']) * 3; 
                $so_luong += count($diem['DiemHocKy']) * 3; 
            }
            $diem['TB'] = ($so_luong > 0) ? round($tong / $so_luong, 1) : 0;
        }

        return $bang_diem;
    }
}
?>
