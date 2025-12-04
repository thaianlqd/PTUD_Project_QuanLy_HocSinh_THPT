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

    // --- THÊM VÀO CUỐI FILE GiaoVienBaiTapModel.php ---

    /**
     * Lấy dữ liệu biểu đồ Cột: Số lượng bài nộp theo từng lớp
     */
    public function getChartNopBai($gv_id) {
        if ($this->db === null) return [];
        
        // Logic: Lấy các lớp GV dạy -> Đếm tổng bài đã nộp của HS lớp đó
        $sql = "SELECT l.ten_lop, COUNT(bn.ma_bai_nop) as so_luong_nop
                FROM bang_phan_cong bpc
                JOIN lop_hoc l ON bpc.ma_lop = l.ma_lop
                -- Join để lấy bài tập do chính GV này giao
                LEFT JOIN bai_tap bt ON bpc.ma_lop = bt.ma_lop AND bt.ma_giao_vien = bpc.ma_giao_vien
                LEFT JOIN bai_nop bn ON bt.ma_bai_tap = bn.ma_bai_tap
                WHERE bpc.ma_giao_vien = ?
                GROUP BY l.ten_lop";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$gv_id]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy dữ liệu biểu đồ Tròn: Tỉ lệ điểm danh
     */
    
    
    // Đếm tổng số phiên đã tạo
    public function getPhienDiemDanhCount($gv_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM phien_diem_danh WHERE ma_giao_vien = ?");
        $stmt->execute([$gv_id]);
        return $stmt->fetchColumn() ?? 0;
    }

    /**
     * Tính tỷ lệ nộp bài trung bình của tất cả các lớp
     * Công thức: (Tổng số bài đã nộp / Tổng số bài phải nộp) * 100
     */
    public function getTyLeNopBaiTB($gv_id) {
        if ($this->db === null) return 0;

        // 1. Đếm tổng số bài ĐÃ NỘP (Actual)
        $sqlActual = "SELECT COUNT(*) FROM bai_nop bn 
                      JOIN bai_tap bt ON bn.ma_bai_tap = bt.ma_bai_tap 
                      WHERE bt.ma_giao_vien = ?";
        $stmt1 = $this->db->prepare($sqlActual);
        $stmt1->execute([$gv_id]);
        $actual = $stmt1->fetchColumn() ?? 0;

        // 2. Đếm tổng số bài PHẢI NỘP (Expected)
        // (Tổng sĩ số của các lớp được giao bài)
        $sqlExpected = "SELECT SUM(lh.si_so) 
                        FROM bai_tap bt 
                        JOIN lop_hoc lh ON bt.ma_lop = lh.ma_lop 
                        WHERE bt.ma_giao_vien = ?";
        $stmt2 = $this->db->prepare($sqlExpected);
        $stmt2->execute([$gv_id]);
        $expected = $stmt2->fetchColumn() ?? 0;

        // 3. Tính phần trăm
        if ($expected > 0) {
            return round(($actual / $expected) * 100, 1); // Làm tròn 1 chữ số thập phân
        }
        return 0; // Chưa giao bài nào thì là 0%
    }

    // --- BỔ SUNG CÁC HÀM HIỂN THỊ DASHBOARD ---

    /**
     * 1. Lấy tên môn học mà giáo viên đang dạy (để hiện ở Profile)
     */
    public function getMonGiangDay($user_id) {
        if ($this->db === null) return "Chưa phân công";
        
        // Lấy tên môn từ bảng phân công. LIMIT 1 để lấy 1 môn đại diện nếu dạy nhiều môn
        $sql = "SELECT mh.ten_mon_hoc 
                FROM bang_phan_cong bpc 
                JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc 
                WHERE bpc.ma_giao_vien = ? 
                LIMIT 1";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        
        return $result['ten_mon_hoc'] ?? 'Giáo Viên Bộ Môn';
    }

    /**
     * 2. Lấy danh sách tên các lớp đang phụ trách (để hiện badge 10A1, 10A2...)
     */
    public function getDanhSachLop($user_id) {
        if ($this->db === null) return [];

        $sql = "SELECT DISTINCT lh.ten_lop
                FROM bang_phan_cong bpc
                JOIN lop_hoc lh ON bpc.ma_lop = lh.ma_lop
                WHERE bpc.ma_giao_vien = ?
                ORDER BY lh.ten_lop ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id]);
        
        // PDO::FETCH_COLUMN giúp trả về mảng 1 chiều: ['10A1', '10A2', ...] thay vì mảng kết hợp
        return $stmt->fetchAll(PDO::FETCH_COLUMN); 
    }

    /**
     * 3. SỬA LẠI HÀM getChartDiemDanh (Lưu ý tên cột)
     * Trong CSDL thường cột trạng thái là `trang_thai` chứ không phải `trang_thai_diem_danh`
     * Bạn kiểm tra lại CSDL nhé, dưới đây mình để là `trang_thai` cho chuẩn.
     */
    /**
     * 3. SỬA LẠI HÀM getChartDiemDanh (Fix lỗi Column not found)
     */
    public function getChartDiemDanh($gv_id) {
        if ($this->db === null) return ['CoMat' => 0, 'Vang' => 0];

        // SỬA: Đổi 'trang_thai' thành 'trang_thai_diem_danh'
        $sql = "SELECT ct.trang_thai_diem_danh, COUNT(*) as so_luong
                FROM phien_diem_danh p
                JOIN chi_tiet_diem_danh ct ON p.ma_phien = ct.ma_phien
                WHERE p.ma_giao_vien = ?
                GROUP BY ct.trang_thai_diem_danh";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$gv_id]);
        
        $data = ['CoMat' => 0, 'Vang' => 0];
        foreach ($stmt->fetchAll() as $row) {
            // SỬA: Lấy đúng key từ kết quả trả về
            $status = strtolower($row['trang_thai_diem_danh']);
            
            // Logic kiểm tra (Lưu ý: check kỹ xem trong DB lưu là 'co_mat' hay 'CoMat')
            if ($status == 'co_mat' || $status == 'comat' || $status == 'có mặt') {
                $data['CoMat'] += $row['so_luong'];
            } else {
                // Gom tất cả các trạng thái khác (vắng có phép, không phép...) vào nhóm Vắng
                $data['Vang'] += $row['so_luong'];
            }
        }
        return $data;
    }


    


}
?>