<?php
/**
 * GiaoVienBaiTapModel: Xử lý logic giao bài và thống kê cho Giáo viên
 */
class GiaoVienBaiTapModel {
    private $db;
    
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
     * HÀM CŨ: Lấy danh sách Lớp + Môn học mà Giáo viên được phân công (cho trang chọn lớp)
     */
    public function getLopHocDaPhanCong($ma_giao_vien) {
        if ($this->db === null) return [];
        
        $sql = "SELECT 
                    l.ma_lop, 
                    l.ten_lop, 
                    l.si_so,
                    mh.ma_mon_hoc,
                    mh.ten_mon_hoc
                FROM bang_phan_cong bpc
                JOIN lop_hoc l ON bpc.ma_lop = l.ma_lop
                JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
                WHERE bpc.ma_giao_vien = ?
                GROUP BY l.ma_lop, mh.ma_mon_hoc
                ORDER BY l.ten_lop, mh.ten_mon_hoc";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_giao_vien]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getLopHocDaPhanCong: " . $e->getMessage());
            return [];
        }
    }

    /**
     * HÀM CŨ: Giao bài tập mới (Dùng cho Modal)
     */
    public function giaoBaiTap($data) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi kết nối CSDL.'];
        
        $this->db->beginTransaction();
        try {
            // 1. Insert vào bảng `bai_tap` (bảng cha)
            $sql_main = "INSERT INTO bai_tap 
                            (ten_bai_tap, mo_ta, ngay_giao, han_nop, loai_bai_tap, file_dinh_kem, ma_lop, ma_giao_vien, ma_mon_hoc)
                         VALUES 
                            (:ten, :mo_ta, CURDATE(), :han_nop, :loai, :file_kem, :ma_lop, :ma_gv, :ma_mon)";
            
            $stmt_main = $this->db->prepare($sql_main);
            $stmt_main->execute([
                ':ten' => $data['ten_bai_tap'],
                ':mo_ta' => $data['mo_ta_chung'],
                ':han_nop' => $data['han_nop'],
                ':loai' => $data['loai_bai_tap'],
                ':file_kem' => $data['file_dinh_kem'],
                ':ma_lop' => $data['ma_lop'],
                ':ma_gv' => $data['ma_giao_vien'],
                ':ma_mon' => $data['ma_mon_hoc']
            ]);
            
            $ma_bai_tap_moi = $this->db->lastInsertId();

            // 2. Insert vào bảng con
            if ($data['loai_bai_tap'] == 'TuLuan') {
                $sql_child = "INSERT INTO bai_tap_tu_luan (ma_bai_tap, de_bai_chi_tiet) VALUES (?, ?)";
                $this->db->prepare($sql_child)->execute([$ma_bai_tap_moi, $data['noi_dung_tu_luan']]);
            
            } elseif ($data['loai_bai_tap'] == 'UploadFile') {
                $sql_child = "INSERT INTO bai_tap_upload_file (ma_bai_tap, loai_file_cho_phep, dung_luong_toi_da) VALUES (?, ?, ?)";
                $this->db->prepare($sql_child)->execute([$ma_bai_tap_moi, $data['loai_file_cho_phep'], $data['dung_luong_toi_da']]);
            
            } elseif ($data['loai_bai_tap'] == 'TracNghiem') {
                $sql_child = "INSERT INTO bai_tap_trac_nghiem (ma_bai_tap, danh_sach_cau_hoi, thoi_gian_lam_bai) VALUES (?, ?, ?)";
                $this->db->prepare($sql_child)->execute([$ma_bai_tap_moi, $data['json_trac_nghiem'], $data['thoi_gian_lam_bai']]);
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Giao bài tập mới thành công!'];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Lỗi giaoBaiTap: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi CSDL khi giao bài: ' . $e->getMessage()];
        }
    }
    
    /**
     * HÀM MỚI (STEP 3): Lấy danh sách bài tập đã giao cho 1 lớp
     * (Kèm thống kê số lượng đã nộp)
     */
    public function getDanhSachBaiTapCuaLop($ma_lop, $ma_mon_hoc) {
        if ($this->db === null) return [];
        
        $sql = "SELECT 
                    bt.ma_bai_tap, 
                    bt.ten_bai_tap, 
                    bt.han_nop, 
                    bt.loai_bai_tap, 
                    bt.ngay_giao,
                    lh.si_so,
                    COUNT(DISTINCT bn.ma_nguoi_dung) AS so_luong_da_nop
                FROM 
                    bai_tap bt
                JOIN 
                    lop_hoc lh ON bt.ma_lop = lh.ma_lop
                LEFT JOIN 
                    bai_nop bn ON bt.ma_bai_tap = bn.ma_bai_tap
                WHERE 
                    bt.ma_lop = ? AND bt.ma_mon_hoc = ?
                GROUP BY 
                    bt.ma_bai_tap
                ORDER BY 
                    bt.han_nop DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_lop, $ma_mon_hoc]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachBaiTapCuaLop: " . $e->getMessage());
            return [];
        }
    }

    /**
     * HÀM MỚI: Lấy Tên Lớp, Tên Môn (cho tiêu đề trang)
     */
    public function getThongTinLopMonHoc($ma_lop, $ma_mon_hoc) {
        $sql = "SELECT 
                    l.ten_lop, mh.ten_mon_hoc, l.ma_lop, mh.ma_mon_hoc
                FROM lop_hoc l
                JOIN mon_hoc mh
                WHERE l.ma_lop = ? AND mh.ma_mon_hoc = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_lop, $ma_mon_hoc]);
        return $stmt->fetch();
    }

    /**
     * HÀM MỚI (STEP 5): Lấy thông tin cơ bản của bài tập (để lấy ma_lop)
     */
    public function getThongTinCoBanBaiTap($ma_bai_tap) {
        $stmt = $this->db->prepare("SELECT ma_lop, ma_mon_hoc, ten_bai_tap, mo_ta FROM bai_tap WHERE ma_bai_tap = ?");
        $stmt->execute([$ma_bai_tap]);
        return $stmt->fetch();
    }

    /**
     * HÀM MỚI (STEP 5): Lấy DS Lớp và join với ai đã nộp
     */
    public function getThongKeNopBai($ma_bai_tap, $ma_lop) {
        $sql = "SELECT 
                    hs.ma_hoc_sinh, 
                    nd.ho_ten, 
                    bn.ma_bai_nop, 
                    bn.ngay_nop, 
                    bn.trang_thai, 
                    bn.diem_so
                FROM 
                    hoc_sinh hs
                JOIN 
                    nguoi_dung nd ON hs.ma_hoc_sinh = nd.ma_nguoi_dung
                LEFT JOIN 
                    bai_nop bn ON hs.ma_hoc_sinh = bn.ma_nguoi_dung AND bn.ma_bai_tap = ?
                WHERE 
                    hs.ma_lop = ?
                ORDER BY 
                    nd.ho_ten";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_bai_tap, $ma_lop]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy số lớp GV đang dạy (cho Dashboard GV)
     */
    public function getLopDayCount($ma_giao_vien) {
        if ($this->db === null) return 0;
        try {
            $stmt = $this->db->prepare("SELECT COUNT(DISTINCT ma_lop) FROM bang_phan_cong WHERE ma_giao_vien = ?");
            $stmt->execute([$ma_giao_vien]);
            return $stmt->fetchColumn() ?? 0;
        } catch (PDOException $e) {
            error_log("Lỗi getLopDayCount: " . $e->getMessage());
            return 0;
        }
    }
}
?>