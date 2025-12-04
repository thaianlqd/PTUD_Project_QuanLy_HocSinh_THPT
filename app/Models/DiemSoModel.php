<?php
/**
 * DiemSoModel: Xử lý logic CSDL cho nghiệp vụ Điểm số và Duyệt điểm.
 */
class DiemSoModel {
    private $db;

    public function __construct() {
        // Kết nối CSDL (Hỗ trợ cả port 3307 và 3306)
        $ports = [3307, 3306]; 
        $connected = false;

        foreach ($ports as $port) {
            try {
                $dsn = "mysql:host=127.0.0.1;port=$port;dbname=thpt_manager;charset=utf8mb4";
                $this->db = new PDO($dsn, 'root', '');
                
                // Cấu hình PDO chuẩn
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
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
     */
    public function getDanhSachPhieu($trang_thai = 'ChoDuyet', $limit = null) {
        if ($this->db === null) return [];
        
        $sql = "SELECT 
                    p.ma_phieu,
                    p.tieu_de,
                    p.ngay_lap_phieu,
                    p.diem_cu,
                    p.diem_de_nghi,
                    p.ly_do_chinh_sua,
                    p.trang_thai_phieu,
                    gv_nd.ho_ten AS ten_giao_vien_yeu_cau, -- Alias đúng chuẩn
                    gv_nd.ho_ten AS ten_giao_vien,         -- Alias dự phòng cho Dashboard
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

        if ($trang_thai != 'TatCa') {
            $sql .= " WHERE p.trang_thai_phieu = ?"; 
            $params[] = $trang_thai;
        }

        $sql .= " ORDER BY p.ngay_lap_phieu DESC"; 

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params); 
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
            // 1. Lấy thông tin
            $stmt_get = $this->db->prepare("SELECT ma_diem, diem_de_nghi FROM phieu_yeu_cau_chinh_sua_diem WHERE ma_phieu = ? AND trang_thai_phieu = 'ChoDuyet'");
            $stmt_get->execute([$ma_phieu]);
            $phieu = $stmt_get->fetch();

            if (!$phieu) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Phiếu không tồn tại hoặc đã được duyệt.'];
            }
            
            // 2. Cập nhật điểm
            $sql_update_diem = "UPDATE diem_so SET diem_so = ? WHERE ma_diem = ?";
            $stmt_update_diem = $this->db->prepare($sql_update_diem);
            $stmt_update_diem->execute([$phieu['diem_de_nghi'], $phieu['ma_diem']]);

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
            error_log("Lỗi tuChoiPhieu: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi hệ thống khi cập nhật CSDL.'];
        }
    }

    // --- CÁC HÀM CHO DASHBOARD BGH (Đã bổ sung đầy đủ) ---

    public function getPhieuChoDuyetCount() {
        if ($this->db === null) return 0;
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM phieu_yeu_cau_chinh_sua_diem WHERE trang_thai_phieu = 'ChoDuyet'");
            return $stmt->fetchColumn() ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getDiemTBToanTruong() {
        if ($this->db === null) return 0.0;
        try {
            // Lấy trung bình cộng tất cả các đầu điểm (hoặc sửa thành DiemHocKy nếu muốn)
            $stmt = $this->db->query("SELECT AVG(diem_so) FROM diem_so"); 
            return round($stmt->fetchColumn() ?? 0, 2);
        } catch (PDOException $e) {
            return 0.0;
        }
    }

    /**
     * [MỚI] Dữ liệu cho Biểu đồ Tròn: Tỉ lệ trạng thái phiếu
     */
    public function getChartYeuCau() {
        if ($this->db === null) return ['ChoDuyet' => 0, 'DaDuyet' => 0, 'TuChoi' => 0];
        
        $sql = "SELECT trang_thai_phieu, COUNT(*) as so_luong 
                FROM phieu_yeu_cau_chinh_sua_diem 
                GROUP BY trang_thai_phieu";
        $stmt = $this->db->query($sql);
        
        $data = ['ChoDuyet' => 0, 'DaDuyet' => 0, 'TuChoi' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $data[$row['trang_thai_phieu']] = $row['so_luong'];
        }
        return $data;
    }

    /**
     * [MỚI] Dữ liệu cho Biểu đồ Cột: Top 5 lớp có điểm TB cao nhất
     */
    public function getChartDiemLop($school_id = null) {
        if ($this->db === null) return [];

        $sql = "SELECT l.ten_lop, AVG(ds.diem_so) as diem_tb
                FROM diem_so ds
                JOIN ket_qua_hoc_tap kq ON ds.ma_ket_qua_hoc_tap = kq.ma_ket_qua_hoc_tap
                JOIN hoc_sinh hs ON kq.ma_nguoi_dung = hs.ma_hoc_sinh
                JOIN lop_hoc l ON hs.ma_lop = l.ma_lop
                WHERE l.trang_thai_lop = 'HoatDong' ";

        if ($school_id) {
            $sql .= " AND l.ma_truong = " . (int)$school_id;
        }

        $sql .= " GROUP BY l.ten_lop 
                  ORDER BY diem_tb DESC 
                  LIMIT 5"; 

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
?>