<?php
/**
 * ThanhToanModel: Xử lý logic CSDL cho Hóa đơn và Biên nhận
 * (ĐÃ SỬA LỖI CÚ PHÁP VÀ TRÙNG LẶP HÀM)
 */
class ThanhToanModel {
    private $db;

    public function __construct() {
        try {
            $dsn = 'mysql:host=127.0.0.1;port=3307;dbname=thpt_manager;charset=utf8mb4';
            $this->db = new PDO($dsn, 'root', '');
            
            // --- SỬA LỖI 1 (Viết liền) ---
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->db->exec("SET NAMES 'utf8mb4'");

        } catch (PDOException $e) {
            $this->db = null;
            die("Không thể kết nối CSDL (ThanhToanModel): " . $e->getMessage());
        }
    }

    /**
     * Lấy danh sách hóa đơn CHƯA THANH TOÁN của Phụ huynh (cập nhật với ngay_het_han, trang_thai_tam và lọc trạng thái tạm)
     */
    public function getHoaDonChuaThanhToan($ma_phu_huynh) {
        $sql = "SELECT 
                    hd.ma_hoa_don,
                    hd.ngay_lap_hoa_don,
                    hd.thanh_tien,
                    hd.ghi_chu,
                    hd.trang_thai_hoa_don,
                    hd.ngay_het_han,
                    hd.trang_thai_tam
                FROM hoa_don hd
                WHERE hd.ma_nguoi_dung = ? 
                AND hd.trang_thai_hoa_don = 'ChuaThanhToan'
                AND (hd.trang_thai_tam IS NULL OR hd.trang_thai_tam = 'ChuaThanhToan')  -- Lọc chỉ hóa đơn chưa xử lý tạm
                ORDER BY hd.ngay_lap_hoa_don DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_phu_huynh]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy danh sách hóa đơn ĐÃ THANH TOÁN của Phụ huynh (giới hạn 10 mới nhất, cập nhật với ngay_het_han)
     */
    public function getHoaDonDaThanhToan($ma_phu_huynh, $limit = 10) {
        $sql = "SELECT 
                    hd.ma_hoa_don,
                    hd.ngay_lap_hoa_don,
                    hd.ngay_thanh_toan,
                    hd.thanh_tien,
                    hd.ghi_chu,
                    hd.hinh_thuc_thanh_toan,
                    hd.ngay_het_han
                FROM hoa_don hd
                WHERE hd.ma_nguoi_dung = ? 
                AND hd.trang_thai_hoa_don = 'DaThanhToan'
                ORDER BY hd.ngay_thanh_toan DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        
        // Bind tham số với kiểu dữ liệu phù hợp
        $stmt->bindParam(1, $ma_phu_huynh, PDO::PARAM_INT);  // Ép kiểu int cho ma_nguoi_dung (an toàn)
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);          // Ép kiểu int cho LIMIT (sửa lỗi chính)
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Lấy chi tiết 1 hóa đơn (cập nhật với thời hạn và trạng thái tạm)
     */
    public function getHoaDonChiTiet($ma_hoa_don, $ma_phu_huynh) {
        $sql = "SELECT *, ngay_het_han, trang_thai_tam FROM hoa_don 
                WHERE ma_hoa_don = ? AND ma_nguoi_dung = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_hoa_don, $ma_phu_huynh]);
        return $stmt->fetch();
    }
    
    /**
     * XÁC NHẬN THANH TOÁN (Quan trọng nhất, cập nhật với reset trang_thai_tam)
     */
    public function xacNhanThanhToan($ma_hoa_don, $ma_giao_dich, $hinh_thuc, $so_tien) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi CSDL'];
        
        $this->db->beginTransaction();
        try {
            // 1. Kiểm tra hóa đơn
            $stmt_check = $this->db->prepare("SELECT trang_thai_hoa_don, thanh_tien FROM hoa_don WHERE ma_hoa_don = ? FOR UPDATE");
            $stmt_check->execute([$ma_hoa_don]);
            $hoa_don = $stmt_check->fetch();

            if (!$hoa_don) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Hóa đơn không tồn tại.'];
            }
            if ($hoa_don['trang_thai_hoa_don'] == 'DaThanhToan') {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Hóa đơn này đã được thanh toán trước đó.'];
            }
            // (Sửa: Chuyển so_tien về float để so sánh)
            if ((float)$hoa_don['thanh_tien'] != (float)$so_tien) {
                $this->db->rollBack();
                return ['success' => false, 'message' => "Số tiền không khớp (Yêu cầu: {$hoa_don['thanh_tien']} | Nhận: {$so_tien})"];
            }

            // 2. Cập nhật Hóa đơn (Bảng 36), reset trang_thai_tam về NULL
            $sql_update_hd = "UPDATE hoa_don 
                              SET trang_thai_hoa_don = 'DaThanhToan',
                                  ma_giao_dich_ben_thu_3 = ?,
                                  ngay_thanh_toan = NOW(),
                                  hinh_thuc_thanh_toan = ?,
                                  trang_thai_tam = NULL
                              WHERE ma_hoa_don = ?";
            $this->db->prepare($sql_update_hd)->execute([$ma_giao_dich, $hinh_thuc, $ma_hoa_don]);
            
            // 3. Tạo Biên nhận (Bảng 37)
            $sql_insert_bn = "INSERT INTO bien_nhan_thanh_toan (ma_hoa_don, noi_dung_thanh_toan, trang_thai)
                              VALUES (?, ?, 'DaThanhToan')";
            $this->db->prepare($sql_insert_bn)->execute([
                $ma_hoa_don,
                "Thanh toán học phí qua $hinh_thuc. Mã GD: $ma_giao_dich"
            ]);

            $this->db->commit();
            return ['success' => true, 'message' => 'Xác nhận thanh toán thành công.'];

        } catch (PDOException $e) {
            // --- SỬA LỖI 3 (Viết liền) ---
            $this->db->rollBack();
            error_log("Lỗi xacNhanThanhToan: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
        }
    }

    /**
     * Kiểm tra hóa đơn có quá hạn không (trả về true nếu quá hạn)
     */
    public function kiemTraQuaHan($ma_hoa_don) {
        $sql = "SELECT ngay_het_han FROM hoa_don WHERE ma_hoa_don = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_hoa_don]);
        $row = $stmt->fetch();
        if (!$row || empty($row['ngay_het_han'])) return false;
        return strtotime($row['ngay_het_han']) < time();  // So sánh với ngày hiện tại
    }

    /**
     * Cập nhật trạng thái tạm cho thanh toán tiền mặt
     */
    public function capNhatTrangThaiTam($ma_hoa_don, $trang_thai_tam = 'ChoThanhToanTaiTruong') {
        $sql = "UPDATE hoa_don SET trang_thai_tam = ? WHERE ma_hoa_don = ? AND trang_thai_hoa_don = 'ChuaThanhToan'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$trang_thai_tam, $ma_hoa_don]);
    }


    /**
     * Lấy danh sách hóa đơn CHỜ XÁC NHẬN TẠI TRƯỜNG của Phụ huynh
     */
    public function getHoaDonChoXacNhanTaiTruong($ma_phu_huynh) {
        $sql = "SELECT 
                    hd.ma_hoa_don,
                    hd.ngay_lap_hoa_don,
                    hd.thanh_tien,
                    hd.ghi_chu,
                    hd.trang_thai_hoa_don,
                    hd.ngay_het_han,
                    hd.trang_thai_tam
                FROM hoa_don hd
                WHERE hd.ma_nguoi_dung = ? 
                AND hd.trang_thai_hoa_don = 'ChuaThanhToan'
                AND hd.trang_thai_tam = 'ChoThanhToanTaiTruong'
                ORDER BY hd.ngay_lap_hoa_don DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_phu_huynh]);
        return $stmt->fetchAll();
    }


    // ThanhToanModel.php

    /**
     * XÁC NHẬN THANH TOÁN SEPAY (Dùng cho Webhook)
     * Hàm này được gọi khi Sepay thông báo chuyển khoản thành công.
     * @param int $ma_hoa_don Mã hóa đơn
     * @param float $so_tien Số tiền Sepay gửi về (đơn vị VNĐ)
     * @param string $ma_giao_dich Mã tham chiếu Sepay (referenceCode)
     * @return array Kết quả xử lý
     */
    public function xacNhanThanhToanSepay($ma_hoa_don, $so_tien, $ma_giao_dich) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi CSDL'];
        
        $this->db->beginTransaction();
        try {
            // 1. Kiểm tra hóa đơn, số tiền và trạng thái (lock row)
            $stmt_check = $this->db->prepare("SELECT trang_thai_hoa_don, thanh_tien FROM hoa_don WHERE ma_hoa_don = ? FOR UPDATE");
            $stmt_check->execute([$ma_hoa_don]);
            $hoa_don = $stmt_check->fetch();

            if (!$hoa_don) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Hóa đơn không tồn tại.'];
            }
            if ($hoa_don['trang_thai_hoa_don'] == 'DaThanhToan') {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Hóa đơn này đã được thanh toán trước đó.'];
            }
            // So sánh số tiền (Sepay gửi VNĐ, hóa đơn cũng là VNĐ)
            if (abs((float)$hoa_don['thanh_tien'] - (float)$so_tien) > 0.01) {
                $this->db->rollBack();
                return ['success' => false, 'message' => "Số tiền không khớp (Yêu cầu: {$hoa_don['thanh_tien']} | Nhận: {$so_tien})"];
            }

            // 2. Cập nhật Hóa đơn (Phương thức là 'SepayQR')
            $sql_update_hd = "UPDATE hoa_don 
                            SET trang_thai_hoa_don = 'DaThanhToan',
                                ma_giao_dich_ben_thu_3 = ?,
                                ngay_thanh_toan = NOW(),
                                hinh_thuc_thanh_toan = 'SepayQR',
                                trang_thai_tam = NULL
                            WHERE ma_hoa_don = ?";
            $this->db->prepare($sql_update_hd)->execute([$ma_giao_dich, $ma_hoa_don]);
            
            // 3. Tạo Biên nhận
            $sql_insert_bn = "INSERT INTO bien_nhan_thanh_toan (ma_hoa_don, noi_dung_thanh_toan, trang_thai)
                            VALUES (?, ?, 'DaThanhToan')";
            $this->db->prepare($sql_insert_bn)->execute([
                $ma_hoa_don,
                "Thanh toán học phí qua Sepay QR. Mã GD: $ma_giao_dich"
            ]);

            $this->db->commit();
            return ['success' => true, 'message' => 'Xác nhận thanh toán thành công.'];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Lỗi xacNhanThanhToanSepay: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
        }
    }

    /**
     * Lấy trạng thái hóa đơn chỉ bằng ID (Dùng cho Polling)
     */
    public function getTrangThaiHoaDon($ma_hoa_don) {
        $sql = "SELECT trang_thai_hoa_don FROM hoa_don WHERE ma_hoa_don = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_hoa_don]);
        $row = $stmt->fetch();
        return $row ? $row['trang_thai_hoa_don'] : null;
    }

    
}
?>