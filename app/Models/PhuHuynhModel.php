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
    public function getMaHocSinhCuaPhuHuynh($ma_phu_huynh) {
        // Câu lệnh SQL mới: Tìm trong bảng liên kết dựa theo ID phụ huynh đăng nhập
        $sql = "SELECT ma_hoc_sinh 
                FROM phu_huynh_hoc_sinh 
                WHERE ma_phu_huynh = ? 
                LIMIT 1"; // Tạm thời lấy 1 học sinh đầu tiên tìm thấy
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_phu_huynh]); // Truyền ID thật (ví dụ 20) vào dấu ?
        
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
     * ✅ Lấy bảng điểm trung bình các môn (của con)
     */
    public function getBangDiem($ma_phu_huynh) {
        $ma_hs = $this->getMaHocSinhCuaPhuHuynh($ma_phu_huynh);
        
        error_log("getBangDiem - ma_phu_huynh: $ma_phu_huynh, ma_hs: " . ($ma_hs ?? 'NULL'));
        
        if (!$ma_hs) {
            error_log("getBangDiem - Không tìm thấy ma_hoc_sinh!");
            return [];
        }

        // ✅ Query từ bảng diem_mon_hoc_hoc_ky
        $sql = "SELECT 
                    mh.ten_mon_hoc,
                    d.diem_mieng,
                    d.diem_15phut,
                    d.diem_1tiet,
                    d.diem_gua_ky,
                    d.diem_cuoi_ky,
                    d.diem_tb_mon_hk,
                    d.xep_loai_mon,
                    d.ma_hoc_ky
                FROM diem_mon_hoc_hoc_ky d
                JOIN mon_hoc mh ON d.ma_mon_hoc = mh.ma_mon_hoc
                WHERE d.ma_hoc_sinh = ?
                ORDER BY d.ma_hoc_ky, mh.ten_mon_hoc";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_hs]);
            $results = $stmt->fetchAll();
            
            error_log("getBangDiem - Số dòng điểm: " . count($results));
            if (count($results) > 0) {
                error_log("getBangDiem - Dòng đầu: " . json_encode($results[0]));
            }
            
            // Gộp điểm theo từng môn
            $bang_diem = [];
            foreach ($results as $row) {
                $mon = $row['ten_mon_hoc'];
                $hoc_ky = $row['ma_hoc_ky']; // ✅ HK1 hoặc HK2
                
                if (!isset($bang_diem[$mon])) {
                    $bang_diem[$mon] = [];
                }
                
                $bang_diem[$mon][$hoc_ky] = [
                    'DiemMieng' => $row['diem_mieng'] ?? 0,
                    'Diem15Phut' => $row['diem_15phut'] ?? 0,
                    'Diem1Tiet' => $row['diem_1tiet'] ?? 0,
                    'DiemGiuaKy' => $row['diem_gua_ky'] ?? 0,
                    'DiemCuoiKy' => $row['diem_cuoi_ky'] ?? 0,
                    'TB' => $row['diem_tb_mon_hk'] ?? 0,           // ✅ SỬA
                    'XepLoai' => $row['xep_loai_mon'] ?? 'ChuaDat' // ✅ SỬA
                ];
            }
            
            error_log("getBangDiem - Kết quả cuối: " . json_encode($bang_diem));
            
            return $bang_diem;
            
        } catch (PDOException $e) {
            error_log("Lỗi getBangDiem: " . $e->getMessage());
            return [];
        }
    }

    public function getTenTruongCuaCon($ma_phu_huynh) {
        $ma_hs = $this->getMaHocSinhCuaPhuHuynh($ma_phu_huynh);
        if (!$ma_hs) {
            return 'THPT Manager';
        }

        $sql = "
            SELECT tt.ten_truong 
            FROM hoc_sinh hs
            JOIN lop_hoc l ON hs.ma_lop = l.ma_lop
            JOIN truong_thpt tt ON l.ma_truong = tt.ma_truong
            WHERE hs.ma_hoc_sinh = ?
            LIMIT 1
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_hs]);
            $ten_truong = $stmt->fetchColumn();
            
            return $ten_truong ?: 'THPT Manager';
        } catch (Exception $e) {
            return 'THPT Manager'; // nếu có lỗi gì thì vẫn không crash
        }
    }

    
}
?>
