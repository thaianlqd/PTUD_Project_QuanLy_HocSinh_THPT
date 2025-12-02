<?php
/**
 * DiemDanhHSModel: Xử lý CSDL cho Học sinh xem và nộp điểm danh
 */
class DiemDanhHSModel {
    private $db;

    public function __construct() {
        try {
            $dsn = 'mysql:host=127.0.0.1;port=3307;dbname=thpt_manager;charset=utf8mb4';
            $this->db = new PDO($dsn, 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->db = null;
        }
    }

    /**
     * Tự động cập nhật các phiên đã quá hạn
     */
    /**
     * Tự động cập nhật các phiên (ChuaMo -> DangDiemDanh) và (DangDiemDanh -> HetThoiGian)
     */
    public function capNhatTrangThaiPhien($ma_lop) {
        if ($this->db === null) return;

        try {
            // 1. MỞ PHIÊN: Chuyển 'ChuaMo' → 'DangDiemDanh'
            $sqlMo = "
                UPDATE phien_diem_danh
                SET trang_thai_phien = 'DangDiemDanh'
                WHERE ma_lop_hoc = ?
                AND loai_phien = 'HocSinh'
                AND trang_thai_phien = 'ChuaMo'
                AND NOW() BETWEEN thoi_gian_mo AND thoi_gian_dong
            ";
            $stmtMo = $this->db->prepare($sqlMo);
            $stmtMo->execute([$ma_lop]);

            // 2. ĐÓNG PHIÊN: Chuyển 'DangDiemDanh' → 'HetThoiGian'
            $sqlDong = "
                UPDATE phien_diem_danh
                SET trang_thai_phien = 'HetThoiGian'
                WHERE ma_lop_hoc = ?
                AND loai_phien = 'HocSinh'
                AND trang_thai_phien = 'DangDiemDanh'
                AND thoi_gian_dong < NOW()
            ";
            $stmtDong = $this->db->prepare($sqlDong);
            $stmtDong->execute([$ma_lop]);

        } catch (PDOException $e) {
            error_log('Lỗi capNhatTrangThaiPhien (HS): ' . $e->getMessage());
        }
    }


    /**
     * Lấy danh sách các phiên (đang mở và lịch sử) cho HS
     */
    public function getDanhSachPhien($ma_hoc_sinh, $ma_lop) {
        if ($this->db === null) return [];
        
        // Cập nhật trạng thái trước khi lấy
        $this->capNhatTrangThaiPhien($ma_lop); // Sửa tên hàm ở đây

        $sql = "SELECT 
                    p.ma_phien, p.tieu_de, p.ghi_chu,
                    p.loai_phien, p.trang_thai_phien,
                    p.thoi_gian_mo, p.thoi_gian_dong,
                    ct.trang_thai_diem_danh,
                    ct.thoi_gian_nop
                FROM phien_diem_danh p
                LEFT JOIN chi_tiet_diem_danh ct ON p.ma_phien = ct.ma_phien AND ct.ma_nguoi_dung = ?
                WHERE p.ma_lop_hoc = ? AND p.loai_phien = 'HocSinh'
                ORDER BY p.thoi_gian_mo DESC, p.ma_phien DESC
                LIMIT 10";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_hoc_sinh, $ma_lop]);
        return $stmt->fetchAll();
    }

    /**
     * Học sinh nộp điểm danh
     */
    public function submitDiemDanh($ma_phien, $ma_hoc_sinh, $ma_lop) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi kết nối CSDL.'];

        $this->db->beginTransaction();
        try {
            // 1. Kiểm tra xem phiên có hợp lệ không
            $sqlCheck = "SELECT ma_phien FROM phien_diem_danh 
                         WHERE ma_phien = ? 
                           AND ma_lop_hoc = ?
                           AND loai_phien = 'HocSinh'
                           AND trang_thai_phien = 'DangDiemDanh'
                           AND NOW() BETWEEN thoi_gian_mo AND thoi_gian_dong
                         FOR UPDATE"; // Khóa hàng để tránh 2 lần nộp
            
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$ma_phien, $ma_lop]);
            $phien = $stmtCheck->fetch();

            if (!$phien) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Phiên đã đóng hoặc không tồn tại.'];
            }

            // 2. Kiểm tra xem đã nộp chưa (dù câu check trên đã đủ)
            $sqlCheckNop = "SELECT ma_ctdd FROM chi_tiet_diem_danh WHERE ma_phien = ? AND ma_nguoi_dung = ?";
            $stmtCheckNop = $this->db->prepare($sqlCheckNop);
            $stmtCheckNop->execute([$ma_phien, $ma_hoc_sinh]);
            
            if ($stmtCheckNop->fetch()) {
                 $this->db->rollBack();
                 return ['success' => false, 'message' => 'Bạn đã điểm danh phiên này rồi.'];
            }
            
            // 3. Tiến hành nộp
            $sqlInsert = "INSERT INTO chi_tiet_diem_danh 
                            (ma_phien, ma_nguoi_dung, trang_thai_diem_danh, thoi_gian_nop) 
                          VALUES 
                            (?, ?, 'CoMat', NOW())";
            
            $stmtInsert = $this->db->prepare($sqlInsert);
            $stmtInsert->execute([$ma_phien, $ma_hoc_sinh]);

            $this->db->commit();
            return ['success' => true, 'message' => 'Điểm danh thành công!'];

        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
        }
    }
}
?>