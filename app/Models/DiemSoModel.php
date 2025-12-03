<?php
/**
 * DiemSoModel: Xử lý logic CSDL cho nghiệp vụ Điểm số và Duyệt điểm.
 */
class DiemSoModel {
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
     * Lấy danh sách phiếu yêu cầu (ĐÃ NÂNG CẤP CÓ LỌC)
     * $trang_thai: 'ChoDuyet', 'DaDuyet', 'TuChoi', hoặc 'TatCa'
     * $limit: Giới hạn số lượng (dùng cho dashboard)
     */
    public function getDanhSachPhieu($trang_thai = 'ChoDuyet', $limit = null) {
        if ($this->db === null) return [];
        
        // Câu SQL gốc (đã xóa WHERE và ORDER BY)
        $sql = "SELECT 
                    p.ma_phieu,
                    p.tieu_de,
                    p.ngay_lap_phieu,
                    p.diem_cu,
                    p.diem_de_nghi,
                    p.ly_do_chinh_sua,
                    p.trang_thai_phieu,
                    gv_nd.ho_ten AS ten_giao_vien_yeu_cau,
                    hs_nd.ho_ten AS ten_hoc_sinh,
                    mh.ten_mon_hoc,
                    ds.loai_diem
                FROM phieu_yeu_cau_chinh_sua_diem AS p
                JOIN giao_vien AS gv ON p.ma_giao_vien = gv.ma_giao_vien
                JOIN nguoi_dung AS gv_nd ON gv.ma_giao_vien = gv_nd.ma_nguoi_dung
                JOIN diem_so AS ds ON p.ma_diem = ds.ma_diem
                JOIN mon_hoc AS mh ON ds.ma_mon_hoc = mh.ma_mon_hoc
                JOIN ket_qua_hoc_tap AS kqht ON ds.ma_ket_qua_hoc_tap = kqht.ma_ket_qua_hoc_tap
                JOIN hoc_sinh AS hs ON kqht.ma_nguoi_dung = hs.ma_hoc_sinh
                JOIN nguoi_dung AS hs_nd ON hs.ma_hoc_sinh = hs_nd.ma_nguoi_dung";
        
        $params = [];

        // Thêm WHERE động dựa trên bộ lọc
        if ($trang_thai != 'TatCa') {
            $sql .= " WHERE p.trang_thai_phieu = ?"; // Thêm điều kiện
            $params[] = $trang_thai;
        }

        $sql .= " ORDER BY p.ngay_lap_phieu DESC"; // Sắp xếp

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params); // Truyền mảng params vào
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachPhieu: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Xử lý duyệt phiếu (Transaction)
     */
    public function duyetPhieu($ma_phieu, $ma_nguoi_duyet) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi kết nối CSDL.'];

        $this->db->beginTransaction();
        try {
            // 1. Lấy thông tin điểm đề nghị và mã điểm từ phiếu
            $stmt_get = $this->db->prepare("SELECT ma_diem, diem_de_nghi FROM phieu_yeu_cau_chinh_sua_diem WHERE ma_phieu = ? AND trang_thai_phieu = 'ChoDuyet'");
            $stmt_get->execute([$ma_phieu]);
            $phieu = $stmt_get->fetch();

            if (!$phieu) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Phiếu không tồn tại hoặc đã được duyệt.'];
            }
            
            $ma_diem = $phieu['ma_diem'];
            $diem_moi = $phieu['diem_de_nghi'];

            // 2. Cập nhật điểm số trong bảng diem_so
            $sql_update_diem = "UPDATE diem_so SET diem_so = ? WHERE ma_diem = ?";
            $stmt_update_diem = $this->db->prepare($sql_update_diem);
            $stmt_update_diem->execute([$diem_moi, $ma_diem]);

            // 3. Cập nhật trạng thái phiếu
            $sql_update_phieu = "UPDATE phieu_yeu_cau_chinh_sua_diem SET
                                    trang_thai_phieu = 'DaDuyet',
                                    nguoi_duyet = ?,
                                    ngay_duyet = CURDATE()
                                WHERE ma_phieu = ?";
            $stmt_update_phieu = $this->db->prepare($sql_update_phieu);
            $stmt_update_phieu->execute([$ma_nguoi_duyet, $ma_phieu]);

            // 4. Commit
            $this->db->commit();
            
            return ['success' => true, 'message' => 'Đã duyệt và cập nhật điểm thành công.'];

        } catch (PDOException $e) {
            // --- SỬA LỖI 2: Viết liền lại ---
            $this->db->rollBack();
            error_log("Lỗi duyetPhieu: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi hệ thống khi cập nhật CSDL.'];
        }
    }

    /**
     * Xử lý từ chối phiếu
     */
    public function tuChoiPhieu($ma_phieu, $ma_nguoi_duyet, $ly_do) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi kết nối CSDL.'];
        
        if (empty($ly_do)) {
            return ['success' => false, 'message' => 'Lý do từ chối không được để trống.'];
        }

        $sql = "UPDATE phieu_yeu_cau_chinh_sua_diem SET
                    trang_thai_phieu = 'TuChoi',
                    nguoi_duyet = ?,
                    ngay_duyet = CURDATE(),
                    ghi_chu = ?
                WHERE ma_phieu = ? AND trang_thai_phieu = 'ChoDuyet'";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_nguoi_duyet, $ly_do, $ma_phieu]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Đã từ chối phiếu thành công.'];
            } else {
                return ['success' => false, 'message' => 'Phiếu không tồn tại hoặc đã được xử lý.'];
            }

        } catch (PDOException $e) {
            // --- SỬA LỖI 3: Viết liền lại ---
            error_log("Lỗi tuChoiPhieu: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi hệ thống khi cập nhật CSDL.'];
        }
    }


    // ... (Thêm vào cuối file DiemSoModel.php)

    /**
     * Lấy số phiếu đang chờ duyệt (cho Dashboard BGH)
     */
    public function getPhieuChoDuyetCount() {
        if ($this->db === null) return 0;
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM phieu_yeu_cau_chinh_sua_diem WHERE trang_thai_phieu = 'ChoDuyet'");
            $stmt->execute();
            return $stmt->fetchColumn() ?? 0;
        } catch (PDOException $e) {
            error_log("Lỗi getPhieuChoDuyetCount: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy điểm TB toàn trường (cho Dashboard BGH)
     */
    public function getDiemTBToanTruong() {
        if ($this->db === null) return 0.0;
        try {
            // (Đây là logic giả định, bạn có thể sửa lại)
            $stmt = $this->db->prepare("SELECT AVG(diem_so) FROM diem_so WHERE loai_diem = 'DiemHocKy'");
            $stmt->execute();
            $avg = $stmt->fetchColumn() ?? 0.0;
            return round($avg, 1); // Làm tròn 1 chữ số
        } catch (PDOException $e) {
            error_log("Lỗi getDiemTBToanTruong: " . $e->getMessage());
            return 0.0;
        }
    }
}
?>