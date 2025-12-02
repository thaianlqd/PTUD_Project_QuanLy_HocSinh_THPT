<?php
/**
 * DiemDanhModel: Xử lý CSDL cho nghiệp vụ Điểm danh
 * (PHIÊN BẢN NÂNG CẤP: Hỗ trợ 2 chế độ)
 */
class DiemDanhModel {
    private $db;

    public function __construct() {
        try {
            $dsn = 'mysql:host=127.0.0.1;port=3307;dbname=thpt_manager;charset=utf8mb4';
            $this->db = new PDO($dsn, 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->db->exec("SET NAMES 'utf8mb4'");
        } catch (PDOException $e) {
            $this->db = null;
            die("Không thể kết nối CSDL (DiemDanhModel): " . $e->getMessage());
        }
    }

    /**
     * Lấy các lớp GV được phân công (để chọn)
     */
    public function getLopHocDaPhanCong($ma_giao_vien) {
        if ($this->db === null) return [];
        
        $sql = "SELECT 
                    l.ma_lop, l.ten_lop, l.si_so, mh.ten_mon_hoc, bpc.ma_mon_hoc
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
            error_log("Lỗi getLopHocDaPhanCong (DiemDanh): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lấy danh sách Học Sinh của 1 lớp
     */
    public function getHocSinhTheoLop($ma_lop) {
        $sql = "SELECT nd.ma_nguoi_dung, nd.ho_ten, hs.trang_thai 
                FROM hoc_sinh hs
                JOIN nguoi_dung nd ON hs.ma_hoc_sinh = nd.ma_nguoi_dung
                WHERE hs.ma_lop = ? AND hs.trang_thai = 'DangHoc'
                ORDER BY nd.ho_ten";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_lop]);
        return $stmt->fetchAll();
    }
    
    /**
     * CẬP NHẬT: Lấy lịch sử các phiên điểm danh (chi tiết hơn)
     */
    public function getLichSuPhien($ma_lop, $ma_mon_hoc, $ma_giao_vien) {
        // Cập nhật trạng thái các phiên 'HocSinh' đã quá hạn
        // === BẮT ĐẦU SỬA ===
        try {

            $this->db->exec("UPDATE phien_diem_danh
            SET trang_thai_phien = 'DangDiemDanh'
            WHERE ma_lop_hoc = $ma_lop
            AND loai_phien = 'HocSinh'
            AND trang_thai_phien = 'ChuaMo'
            AND NOW() BETWEEN thoi_gian_mo AND thoi_gian_dong");

            $this->db->exec("UPDATE phien_diem_danh 
            SET trang_thai_phien = 'HetThoiGian' 
            WHERE ma_lop_hoc = $ma_lop 
            AND loai_phien = 'HocSinh'
            AND trang_thai_phien = 'DangDiemDanh'
            AND thoi_gian_dong < NOW()");
        } catch (PDOException $e) {
            error_log("Lỗi cập nhật trạng thái phiên (GV): " . $e->getMessage());
        }
        // === KẾT THÚC SỬA ===

        $sql = "SELECT 
                    p.ma_phien, p.tieu_de, p.ngay_diem_danh, 
                    p.trang_thai_phien, p.loai_phien, 
                    p.thoi_gian_mo, p.thoi_gian_dong,
                    (SELECT COUNT(*) FROM chi_tiet_diem_danh ct 
                     WHERE ct.ma_phien = p.ma_phien) as da_diem_danh
                FROM phien_diem_danh p
                JOIN bang_phan_cong bpc ON p.ma_giao_vien = bpc.ma_giao_vien 
                                        AND p.ma_lop_hoc = bpc.ma_lop
                WHERE p.ma_lop_hoc = ? 
                  AND p.ma_giao_vien = ?
                  AND bpc.ma_mon_hoc = ?
                ORDER BY p.ngay_diem_danh DESC, p.ma_phien DESC 
                LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_lop, $ma_giao_vien, $ma_mon_hoc]);
        return $stmt->fetchAll();
    }

    /**
     * CẬP NHẬT: Tạo một phiên điểm danh mới (hỗ trợ 2 loại)
     */
    public function taoPhienDiemDanhMoi($ma_lop, $ma_giao_vien, $tieu_de, $ghi_chu, $loai_phien, $thoi_gian_mo, $thoi_gian_dong) {
        
        $trang_thai_phien = 'DangDiemDanh'; // Mặc định cho GV
        
        if ($loai_phien == 'HocSinh') {
            // Nếu thời gian mở là tương lai
            if (strtotime($thoi_gian_mo) > time()) {
                $trang_thai_phien = 'ChuaMo';
            }
            // Nếu null, gán thời gian mặc định
            if (empty($thoi_gian_mo)) $thoi_gian_mo = date('Y-m-d H:i:s');
            if (empty($thoi_gian_dong)) $thoi_gian_dong = date('Y-m-d H:i:s', time() + 15 * 60); // Mặc định 15 phút
        } else {
            // Nếu là GV tự điểm danh, set time_mo/dong là NULL
            $thoi_gian_mo = null;
            $thoi_gian_dong = null;
        }

        $sql = "INSERT INTO phien_diem_danh 
                    (ngay_diem_danh, thoi_gian, tieu_de, ghi_chu, trang_thai_phien, ma_lop_hoc, ma_giao_vien, loai_phien, thoi_gian_mo, thoi_gian_dong)
                VALUES (CURDATE(), CURTIME(), ?, ?, ?, ?, ?, ?, ?, ?)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tieu_de, $ghi_chu, $trang_thai_phien, $ma_lop, $ma_giao_vien, $loai_phien, $thoi_gian_mo, $thoi_gian_dong]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Lỗi taoPhienDiemDanhMoi: " . $e->getMessage());
            return false;
        }
    }

    /**
     * CẬP NHẬT: Lấy thông tin 1 phiên (chi tiết hơn)
     */
    public function getPhienInfo($ma_phien, $ma_giao_vien) {
         $sql = "SELECT 
                    p.*, 
                    l.ten_lop, l.si_so, 
                    mh.ten_mon_hoc,
                    (SELECT COUNT(*) FROM chi_tiet_diem_danh ct 
                     WHERE ct.ma_phien = p.ma_phien) as da_diem_danh
                FROM phien_diem_danh p
                JOIN lop_hoc l ON p.ma_lop_hoc = l.ma_lop
                JOIN bang_phan_cong bpc ON l.ma_lop = bpc.ma_lop AND p.ma_giao_vien = bpc.ma_giao_vien
                JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
                WHERE p.ma_phien = ? AND p.ma_giao_vien = ?
                GROUP BY p.ma_phien";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_phien, $ma_giao_vien]);
        return $stmt->fetch();
    }
    
    /**
     * CẬP NHẬT: Lấy chi tiết điểm danh (thêm thông tin HS)
     */
    public function getChiTietDiemDanh($ma_phien, $ma_lop) {
        $sql = "SELECT 
                    nd.ma_nguoi_dung, 
                    nd.ho_ten,
                    ct.trang_thai_diem_danh,
                    ct.thoi_gian_nop -- Giả sử bạn thêm cột này để HSDĐ
                FROM hoc_sinh hs
                JOIN nguoi_dung nd ON hs.ma_hoc_sinh = nd.ma_nguoi_dung
                LEFT JOIN chi_tiet_diem_danh ct ON hs.ma_hoc_sinh = ct.ma_nguoi_dung AND ct.ma_phien = ?
                WHERE hs.ma_lop = ? AND hs.trang_thai = 'DangHoc'
                ORDER BY nd.ho_ten";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_phien, $ma_lop]);
        return $stmt->fetchAll();
    }
    
    /**
     * Lưu hàng loạt chi tiết điểm danh (Dùng cho GV điểm danh thủ công)
     */
    public function luuChiTietDiemDanh($ma_phien, $danh_sach_diem_danh) {
        if ($this->db === null || empty($danh_sach_diem_danh)) return false;

        $sql = "INSERT INTO chi_tiet_diem_danh (ma_phien, ma_nguoi_dung, trang_thai_diem_danh) 
                VALUES (:ma_phien, :ma_nguoi_dung, :trang_thai)
                ON DUPLICATE KEY UPDATE 
                trang_thai_diem_danh = VALUES(trang_thai_diem_danh)";
        
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($danh_sach_diem_danh as $ma_hs => $trang_thai) {
                $stmt->execute([
                    ':ma_phien' => $ma_phien,
                    ':ma_nguoi_dung' => $ma_hs,
                    ':trang_thai' => $trang_thai
                ]);
            }
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Lỗi luuChiTietDiemDanh: " . $e->getMessage());
            return false;
        }
    }

    /**
     * MỚI: Giáo viên bấm kết thúc phiên
     */
    public function ketThucPhien($ma_phien, $ma_giao_vien) {
        $sql = "UPDATE phien_diem_danh 
                SET trang_thai_phien = 'HetThoiGian', thoi_gian_dong = NOW()
                WHERE ma_phien = ? AND ma_giao_vien = ? AND trang_thai_phien != 'HetThoiGian'";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_phien, $ma_giao_vien]);
            return $stmt->rowCount() > 0; // Trả về true nếu cập nhật thành công
        } catch (PDOException $e) {
            error_log("Lỗi ketThucPhien: " . $e->getMessage());
            return false;
        }
    }

    /**
     * MỚI: Lấy thông tin cơ bản của Lớp + Môn học (để hiển thị tiêu đề)
     */
    public function getLopMonHocInfo($ma_lop, $ma_mon_hoc, $ma_giao_vien) {
        $sql = "SELECT 
                    l.ma_lop, l.ten_lop, l.si_so, 
                    mh.ma_mon_hoc, mh.ten_mon_hoc
                FROM bang_phan_cong bpc
                JOIN lop_hoc l ON bpc.ma_lop = l.ma_lop
                JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
                WHERE bpc.ma_giao_vien = ?
                  AND bpc.ma_lop = ?
                  AND bpc.ma_mon_hoc = ?
                LIMIT 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_giao_vien, $ma_lop, $ma_mon_hoc]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Lỗi getLopMonHocInfo (DiemDanh): " . $e->getMessage());
            return false;
        }
    }
}
?>