<?php
class ThiSinhModel {
    private $db;

    public function __construct() {
        $ports = [3307, 3306];
        $connected = false;
        foreach ($ports as $port) {
            try {
                $dsn = "mysql:host=127.0.0.1;port=$port;dbname=thpt_manager;charset=utf8mb4";
                $this->db = new PDO($dsn, 'root', '');
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->db->exec("SET NAMES 'utf8mb4'");
                $connected = true;
                break;
            } catch (PDOException $e) { continue; }
        }
        if (!$connected) die("Lỗi kết nối CSDL.");
    }

    // 1. Lấy thông tin cá nhân & SBD
    public function getThongTinCaNhan($user_id) {
        $sql = "SELECT nd.ho_ten, nd.ngay_sinh, nd.email, ts.so_bao_danh, ts.truong_thcs, ts.lop_hoc 
                FROM nguoi_dung nd
                JOIN thi_sinh ts ON nd.ma_nguoi_dung = ts.ma_nguoi_dung
                WHERE nd.ma_nguoi_dung = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch() ?: [];
    }

    // 2. Lấy danh sách nguyện vọng đã đăng ký
    public function getDanhSachNguyenVong($user_id) {
        $sql = "SELECT nv.thu_tu_nguyen_vong, tr.ten_truong, nv.ma_truong
                FROM nguyen_vong nv
                JOIN truong_thpt tr ON nv.ma_truong = tr.ma_truong
                WHERE nv.ma_nguoi_dung = ?
                ORDER BY nv.thu_tu_nguyen_vong ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    // 3. Lấy điểm thi
    public function getDiemThi($user_id) {
        $sql = "SELECT diem_toan, diem_van, diem_anh 
                FROM diem_thi_tuyen_sinh 
                WHERE ma_nguoi_dung = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch() ?: ['diem_toan' => 0, 'diem_van' => 0, 'diem_anh' => 0];
    }

    // 4. Lấy kết quả xét tuyển
    public function getKetQuaTuyenSinh($user_id) {
        $sql = "SELECT kq.trang_thai, kq.trang_thai_xac_nhan, tr.ten_truong AS truong_trung_tuyen
                FROM diem_thi_tuyen_sinh dts
                JOIN ket_qua_thi_tuyen_sinh kq ON dts.ma_diem_thi = kq.ma_diem_thi
                LEFT JOIN truong_thpt tr ON kq.ma_truong_trung_tuyen = tr.ma_truong
                WHERE dts.ma_nguoi_dung = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch() ?: null;
    }

    // 5. Đăng ký nguyện vọng mới (ĐÃ FIX: Bỏ ngay_dang_ky)
    public function dangKyNguyenVong($user_id, $ma_truong, $thu_tu_nguyen_vong) {
        try {
            // Kiểm tra trùng lặp
            $checkSql = "SELECT COUNT(*) as count FROM nguyen_vong 
                        WHERE ma_nguoi_dung = ? AND ma_truong = ? AND thu_tu_nguyen_vong = ?";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$user_id, $ma_truong, $thu_tu_nguyen_vong]);
            $result = $checkStmt->fetch();
            
            if ($result['count'] > 0) {
                return ['success' => false, 'message' => 'Nguyện vọng này đã được đăng ký rồi!'];
            }
            
            // Kiểm tra vị trí
            $positionCheckSql = "SELECT COUNT(*) as count FROM nguyen_vong 
                                WHERE ma_nguoi_dung = ? AND thu_tu_nguyen_vong = ?";
            $positionCheckStmt = $this->db->prepare($positionCheckSql);
            $positionCheckStmt->execute([$user_id, $thu_tu_nguyen_vong]);
            $positionResult = $positionCheckStmt->fetch();
            
            if ($positionResult['count'] > 0) {
                return ['success' => false, 'message' => 'Vị trí này đã có nguyện vọng!'];
            }
            
            // INSERT (Đã bỏ cột ngay_dang_ky)
            $sql = "INSERT INTO nguyen_vong (ma_nguoi_dung, ma_truong, thu_tu_nguyen_vong) 
                    VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user_id, $ma_truong, $thu_tu_nguyen_vong]);
            
            return ['success' => true, 'message' => 'Đăng ký nguyện vọng thành công!'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    // 6. Chỉnh sửa nguyện vọng (ĐÃ FIX: Bỏ ngay_dang_ky)
    public function chinhSuaNguyenVong($user_id, $ma_truong_cu, $thu_tu_cu, $ma_truong_moi, $thu_tu_moi) {
        try {
            $checkSql = "SELECT COUNT(*) as count FROM nguyen_vong 
                        WHERE ma_nguoi_dung = ? AND ma_truong = ? AND thu_tu_nguyen_vong = ?";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$user_id, $ma_truong_cu, $thu_tu_cu]);
            if ($checkStmt->fetch()['count'] == 0) {
                return ['success' => false, 'message' => 'Nguyện vọng cũ không tồn tại!'];
            }
            
            if ($thu_tu_cu != $thu_tu_moi) {
                $posSql = "SELECT COUNT(*) as count FROM nguyen_vong 
                           WHERE ma_nguoi_dung = ? AND thu_tu_nguyen_vong = ? AND ma_truong != ?";
                $posStmt = $this->db->prepare($posSql);
                $posStmt->execute([$user_id, $thu_tu_moi, $ma_truong_cu]);
                if ($posStmt->fetch()['count'] > 0) return ['success' => false, 'message' => 'Vị trí NV mới đã có!'];
            }
            
            if ($ma_truong_cu != $ma_truong_moi) {
                $dupSql = "SELECT COUNT(*) as count FROM nguyen_vong 
                           WHERE ma_nguoi_dung = ? AND ma_truong = ? AND thu_tu_nguyen_vong != ?";
                $dupStmt = $this->db->prepare($dupSql);
                $dupStmt->execute([$user_id, $ma_truong_moi, $thu_tu_cu]);
                if ($dupStmt->fetch()['count'] > 0) return ['success' => false, 'message' => 'Trường này đã chọn ở NV khác!'];
            }
            
            // UPDATE (Đã bỏ cột ngay_dang_ky)
            $sql = "UPDATE nguyen_vong 
                    SET ma_truong = ?, thu_tu_nguyen_vong = ?
                    WHERE ma_nguoi_dung = ? AND ma_truong = ? AND thu_tu_nguyen_vong = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_truong_moi, $thu_tu_moi, $user_id, $ma_truong_cu, $thu_tu_cu]);
            
            return ['success' => true, 'message' => 'Chỉnh sửa thành công!'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    // 7. Xóa nguyện vọng
    public function xoaNguyenVong($user_id, $ma_truong, $thu_tu_nguyen_vong) {
        try {
            $sql = "DELETE FROM nguyen_vong 
                    WHERE ma_nguoi_dung = ? AND ma_truong = ? AND thu_tu_nguyen_vong = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user_id, $ma_truong, $thu_tu_nguyen_vong]);
            return ['success' => true, 'message' => 'Xóa thành công!'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    // 8. Lấy danh sách trường THPT
    public function getDanhSachTruong() {
        $sql = "SELECT ma_truong, ten_truong FROM truong_thpt ORDER BY ten_truong ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>