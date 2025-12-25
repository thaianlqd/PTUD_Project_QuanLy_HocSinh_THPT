<?php
/**
 * DiemDanhModel: Xử lý CSDL cho nghiệp vụ Điểm danh
 * (PHIÊN BẢN NÂNG CẤP: Hỗ trợ 2 chế độ)
 */
class DiemDanhModel {
    private $db;

    public function __construct() {
        // SET TIMEZONE CHO PHP
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        
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
                // SET TIMEZONE CHO MySQL
                $this->db->exec("SET time_zone = '+07:00'");
                
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
     * Lấy các lớp GV được phân công (để chọn)
     */
    // public function getLopHocDaPhanCong($ma_giao_vien) {
    //     if ($this->db === null) return [];
        
    //     $sql = "SELECT 
    //                 l.ma_lop, l.ten_lop, l.si_so, mh.ten_mon_hoc, bpc.ma_mon_hoc
    //             FROM bang_phan_cong bpc
    //             JOIN lop_hoc l ON bpc.ma_lop = l.ma_lop
    //             JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
    //             WHERE bpc.ma_giao_vien = ?
    //             GROUP BY l.ma_lop, mh.ma_mon_hoc
    //             ORDER BY l.ten_lop, mh.ten_mon_hoc";
    //     try {
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->execute([$ma_giao_vien]);
    //         return $stmt->fetchAll();
    //     } catch (PDOException $e) {
    //         error_log("Lỗi getLopHocDaPhanCong (DiemDanh): " . $e->getMessage());
    //         return [];
    //     }
    // }
    /**
     * Lấy các lớp GV được phân công (đã lọc bỏ Chào cờ và Sinh hoạt)
     */
    public function getLopHocDaPhanCong($ma_giao_vien) {
        if ($this->db === null) return [];
        
        $sql = "SELECT 
                    l.ma_lop, l.ten_lop, l.si_so, mh.ten_mon_hoc, bpc.ma_mon_hoc
                FROM bang_phan_cong bpc
                JOIN lop_hoc l ON bpc.ma_lop = l.ma_lop
                JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
                WHERE bpc.ma_giao_vien = ?
                  AND bpc.ma_mon_hoc NOT IN (18, 19) -- [QUAN TRỌNG] Chặn Chào cờ (18), Sinh hoạt (19)
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
     * Lấy danh sách Học Sinh của 1 lớp
     */
    // public function getHocSinhTheoLop($ma_lop) {
    //     $sql = "SELECT nd.ma_nguoi_dung, nd.ho_ten, hs.trang_thai 
    //             FROM hoc_sinh hs
    //             JOIN nguoi_dung nd ON hs.ma_hoc_sinh = nd.ma_nguoi_dung
    //             WHERE hs.ma_lop = ? AND hs.trang_thai = 'DangHoc'
    //             ORDER BY nd.ho_ten";
    //     $stmt = $this->db->prepare($sql);
    //     $stmt->execute([$ma_lop]);
    //     return $stmt->fetchAll();
    // }
    public function getHocSinhTheoLop($ma_lop) {
        if ($this->db === null) return [];
        
        $sql = "SELECT 
                    hs.ma_hoc_sinh,
                    nd.ho_ten,
                    nd.email
                FROM hoc_sinh hs
                JOIN nguoi_dung nd ON hs.ma_hoc_sinh = nd.ma_nguoi_dung
                WHERE hs.ma_lop = ?
                ORDER BY nd.ho_ten";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_lop]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getHocSinhTheoLop: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * CẬP NHẬT: Lấy lịch sử các phiên điểm danh (chi tiết hơn)
     */
    // public function getLichSuPhien($ma_lop, $ma_mon_hoc, $ma_giao_vien) {
    //     // Cập nhật trạng thái các phiên 'HocSinh' đã quá hạn
    //     // === BẮT ĐẦU SỬA ===
    //     try {

    //         $this->db->exec("UPDATE phien_diem_danh
    //         SET trang_thai_phien = 'DangDiemDanh'
    //         WHERE ma_lop_hoc = $ma_lop
    //         AND loai_phien = 'HocSinh'
    //         AND trang_thai_phien = 'ChuaMo'
    //         AND NOW() BETWEEN thoi_gian_mo AND thoi_gian_dong");

    //         $this->db->exec("UPDATE phien_diem_danh 
    //         SET trang_thai_phien = 'HetThoiGian' 
    //         WHERE ma_lop_hoc = $ma_lop 
    //         AND loai_phien = 'HocSinh'
    //         AND trang_thai_phien = 'DangDiemDanh'
    //         AND thoi_gian_dong < NOW()");
    //     } catch (PDOException $e) {
    //         error_log("Lỗi cập nhật trạng thái phiên (GV): " . $e->getMessage());
    //     }
    //     // === KẾT THÚC SỬA ===

    //     $sql = "SELECT 
    //                 p.ma_phien, p.tieu_de, p.ngay_diem_danh, 
    //                 p.trang_thai_phien, p.loai_phien, 
    //                 p.thoi_gian_mo, p.thoi_gian_dong,
    //                 (SELECT COUNT(*) FROM chi_tiet_diem_danh ct 
    //                  WHERE ct.ma_phien = p.ma_phien) as da_diem_danh
    //             FROM phien_diem_danh p
    //             JOIN bang_phan_cong bpc ON p.ma_giao_vien = bpc.ma_giao_vien 
    //                                     AND p.ma_lop_hoc = bpc.ma_lop
    //             WHERE p.ma_lop_hoc = ? 
    //               AND p.ma_giao_vien = ?
    //               AND bpc.ma_mon_hoc = ?
    //             ORDER BY p.ngay_diem_danh DESC, p.ma_phien DESC 
    //             LIMIT 10";
    //     $stmt = $this->db->prepare($sql);
    //     $stmt->execute([$ma_lop, $ma_giao_vien, $ma_mon_hoc]);
    //     return $stmt->fetchAll();
    // }
    /**
     * CẬP NHẬT: Lấy lịch sử các phiên điểm danh (đã thêm p.thoi_gian)
     */
    // public function getLichSuPhien($ma_lop, $ma_mon_hoc, $ma_giao_vien) {
    //     // 1. Cập nhật trạng thái các phiên 'HocSinh' đã quá hạn
    //     try {
    //         // Tự động chuyển sang 'DangDiemDanh' nếu đến giờ mở
    //         $this->db->exec("UPDATE phien_diem_danh
    //         SET trang_thai_phien = 'DangDiemDanh'
    //         WHERE ma_lop_hoc = $ma_lop
    //         AND loai_phien = 'HocSinh'
    //         AND trang_thai_phien = 'ChuaMo'
    //         AND NOW() BETWEEN thoi_gian_mo AND thoi_gian_dong");

    //         // Tự động chuyển sang 'HetThoiGian' nếu quá giờ đóng
    //         $this->db->exec("UPDATE phien_diem_danh 
    //         SET trang_thai_phien = 'HetThoiGian' 
    //         WHERE ma_lop_hoc = $ma_lop 
    //         AND loai_phien = 'HocSinh'
    //         AND trang_thai_phien = 'DangDiemDanh'
    //         AND thoi_gian_dong < NOW()");
    //     } catch (PDOException $e) {
    //         error_log("Lỗi cập nhật trạng thái phiên (GV): " . $e->getMessage());
    //     }

    //     // 2. Lấy danh sách hiển thị
    //     $sql = "SELECT 
    //                 p.ma_phien, 
    //                 p.tieu_de, 
    //                 p.ngay_diem_danh, 
    //                 p.thoi_gian,  -- <=== QUAN TRỌNG: Đã thêm cột này để sửa lỗi N/A
    //                 p.trang_thai_phien, 
    //                 p.loai_phien, 
    //                 p.thoi_gian_mo, 
    //                 p.thoi_gian_dong,
    //                 (SELECT COUNT(*) FROM chi_tiet_diem_danh ct 
    //                  WHERE ct.ma_phien = p.ma_phien) as da_diem_danh
    //             FROM phien_diem_danh p
    //             JOIN bang_phan_cong bpc ON p.ma_giao_vien = bpc.ma_giao_vien 
    //                                     AND p.ma_lop_hoc = bpc.ma_lop
    //             WHERE p.ma_lop_hoc = ? 
    //               AND p.ma_giao_vien = ?
    //               AND bpc.ma_mon_hoc = ?
    //             ORDER BY p.ngay_diem_danh DESC, p.ma_phien DESC 
    //             LIMIT 10";
                
    //     try {
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->execute([$ma_lop, $ma_giao_vien, $ma_mon_hoc]);
    //         return $stmt->fetchAll();
    //     } catch (PDOException $e) {
    //         error_log("Lỗi getLichSuPhien: " . $e->getMessage());
    //         return [];
    //     }
    // }
    /**
     * Lấy lịch sử phiên và TỰ ĐỘNG cập nhật trạng thái phiên hết hạn
     */
    public function getLichSuPhien($ma_lop, $ma_mon_hoc, $ma_giao_vien) {
        try {
            // [LOGIC MỚI] Quét và đóng tất cả phiên đã quá giờ đóng mà vẫn để trạng thái Đang mở
            $sql_auto_close = "UPDATE phien_diem_danh 
                               SET trang_thai_phien = 'HetThoiGian' 
                               WHERE ma_lop_hoc = ? 
                               AND trang_thai_phien = 'DangDiemDanh' 
                               AND thoi_gian_dong < NOW()";
            $stmt_close = $this->db->prepare($sql_auto_close);
            $stmt_close->execute([$ma_lop]);

            // Cập nhật các phiên chuẩn bị đến giờ mở (cho phiên hẹn giờ)
            $sql_auto_open = "UPDATE phien_diem_danh 
                              SET trang_thai_phien = 'DangDiemDanh' 
                              WHERE ma_lop_hoc = ? 
                              AND trang_thai_phien = 'ChuaMo' 
                              AND NOW() >= thoi_gian_mo AND NOW() <= thoi_gian_dong";
            $stmt_open = $this->db->prepare($sql_auto_open);
            $stmt_open->execute([$ma_lop]);
        } catch (PDOException $e) { error_log("Lỗi tự động cập nhật phiên: " . $e->getMessage()); }

        // Lấy danh sách lịch sử như cũ
        $sql = "SELECT 
                    p.ma_phien, p.tieu_de, p.ngay_diem_danh, p.thoi_gian,
                    p.trang_thai_phien, p.loai_phien, p.thoi_gian_mo, p.thoi_gian_dong,
                    (SELECT COUNT(*) FROM chi_tiet_diem_danh ct WHERE ct.ma_phien = p.ma_phien) as da_diem_danh
                FROM phien_diem_danh p
                JOIN bang_phan_cong bpc ON p.ma_giao_vien = bpc.ma_giao_vien AND p.ma_lop_hoc = bpc.ma_lop
                WHERE p.ma_lop_hoc = ? AND p.ma_giao_vien = ? AND bpc.ma_mon_hoc = ?
                ORDER BY p.ngay_diem_danh DESC, p.ma_phien DESC LIMIT 15";
                
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_lop, $ma_giao_vien, $ma_mon_hoc]);
            return $stmt->fetchAll();
        } catch (PDOException $e) { return []; }
    }

    /**
     * CẬP NHẬT: Tạo một phiên điểm danh mới (hỗ trợ 2 loại + mật khẩu cho HS)
     */
    // public function taoPhienDiemDanhMoi($ma_lop, $ma_giao_vien, $tieu_de, $ghi_chu, $loai_phien, $thoi_gian_mo, $thoi_gian_dong, $yeu_cau_mat_khau = false, $mat_khau = null) {
        
    //     $trang_thai_phien = 'DangDiemDanh'; // Mặc định cho GV
        
    //     // Mật khẩu CHỈ áp dụng cho chế độ HocSinh
    //     $mat_khau_hash = null;
    //     if ($loai_phien == 'HocSinh') {
    //         // Nếu thời gian mở là tương lai
    //         if (strtotime($thoi_gian_mo) > time()) {
    //             $trang_thai_phien = 'ChuaMo';
    //         }
    //         // Nếu null, gán thời gian mặc định
    //         if (empty($thoi_gian_mo)) $thoi_gian_mo = date('Y-m-d H:i:s');
    //         if (empty($thoi_gian_dong)) $thoi_gian_dong = date('Y-m-d H:i:s', time() + 15 * 60); // Mặc định 15 phút
            
    //         // Mã hóa mật khẩu nếu có
    //         if ($yeu_cau_mat_khau && !empty($mat_khau)) {
    //             $mat_khau_hash = password_hash($mat_khau, PASSWORD_BCRYPT);
    //         }
    //     } else {
    //         // Nếu là GV tự điểm danh, set time_mo/dong là NULL và KHÔNG dùng mật khẩu
    //         $thoi_gian_mo = null;
    //         $thoi_gian_dong = null;
    //         $yeu_cau_mat_khau = false;
    //         $mat_khau_hash = null;
    //     }

    //     $sql = "INSERT INTO phien_diem_danh 
    //                 (ngay_diem_danh, thoi_gian, tieu_de, ghi_chu, trang_thai_phien, ma_lop_hoc, ma_giao_vien, loai_phien, thoi_gian_mo, thoi_gian_dong, yeu_cau_mat_khau, mat_khau_phien)
    //             VALUES (CURDATE(), CURTIME(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    //     try {
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->execute([$tieu_de, $ghi_chu, $trang_thai_phien, $ma_lop, $ma_giao_vien, $loai_phien, $thoi_gian_mo, $thoi_gian_dong, $yeu_cau_mat_khau ? 1 : 0, $mat_khau_hash]);
    //         return $this->db->lastInsertId();
    //     } catch (PDOException $e) {
    //         error_log("Lỗi taoPhienDiemDanhMoi: " . $e->getMessage());
    //         return false;
    //     }
    // }
    /**
     * CẬP NHẬT: Tạo phiên mới (Có check trùng phiên đang diễn ra)
     */
    // public function taoPhienDiemDanhMoi($ma_lop, $ma_giao_vien, $tieu_de, $ghi_chu, $loai_phien, $thoi_gian_mo, $thoi_gian_dong, $yeu_cau_mat_khau = false, $mat_khau = null) {
        
    //     // --- [1. CHECK TRÙNG PHIÊN] ---
    //     // Kiểm tra xem lớp này có phiên nào đang 'DangDiemDanh' không?
    //     try {
    //         $sql_check = "SELECT COUNT(*) FROM phien_diem_danh 
    //                       WHERE ma_lop_hoc = ? 
    //                       AND trang_thai_phien = 'DangDiemDanh'";
    //         $stmt_check = $this->db->prepare($sql_check);
    //         $stmt_check->execute([$ma_lop]);
            
    //         if ($stmt_check->fetchColumn() > 0) {
    //             return -1; // Trả về mã lỗi -1: Đang có phiên chạy
    //         }
    //     } catch (PDOException $e) {
    //         error_log("Lỗi check trùng phiên: " . $e->getMessage());
    //         // Nếu lỗi check thì cứ cho tạo tiếp (fail-safe) hoặc return false tùy bác
    //     }
    //     // ------------------------------

    //     $trang_thai_phien = 'DangDiemDanh'; // Mặc định cho GV
        
    //     // Mật khẩu CHỈ áp dụng cho chế độ HocSinh
    //     $mat_khau_hash = null;
    //     if ($loai_phien == 'HocSinh') {
    //         // Nếu thời gian mở là tương lai
    //         if (strtotime($thoi_gian_mo) > time()) {
    //             $trang_thai_phien = 'ChuaMo';
    //         }
    //         // Nếu null, gán thời gian mặc định
    //         if (empty($thoi_gian_mo)) $thoi_gian_mo = date('Y-m-d H:i:s');
    //         if (empty($thoi_gian_dong)) $thoi_gian_dong = date('Y-m-d H:i:s', time() + 15 * 60); // Mặc định 15 phút
            
    //         // Mã hóa mật khẩu nếu có
    //         if ($yeu_cau_mat_khau && !empty($mat_khau)) {
    //             $mat_khau_hash = password_hash($mat_khau, PASSWORD_BCRYPT);
    //         }
    //     } else {
    //         // Nếu là GV tự điểm danh, set time_mo/dong là NULL và KHÔNG dùng mật khẩu
    //         $thoi_gian_mo = null;
    //         $thoi_gian_dong = null;
    //         $yeu_cau_mat_khau = false;
    //         $mat_khau_hash = null;
    //     }

    //     $sql = "INSERT INTO phien_diem_danh 
    //                 (ngay_diem_danh, thoi_gian, tieu_de, ghi_chu, trang_thai_phien, ma_lop_hoc, ma_giao_vien, loai_phien, thoi_gian_mo, thoi_gian_dong, yeu_cau_mat_khau, mat_khau_phien)
    //             VALUES (CURDATE(), CURTIME(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    //     try {
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->execute([$tieu_de, $ghi_chu, $trang_thai_phien, $ma_lop, $ma_giao_vien, $loai_phien, $thoi_gian_mo, $thoi_gian_dong, $yeu_cau_mat_khau ? 1 : 0, $mat_khau_hash]);
    //         return $this->db->lastInsertId();
    //     } catch (PDOException $e) {
    //         error_log("Lỗi taoPhienDiemDanhMoi: " . $e->getMessage());
    //         return false;
    //     }
    // }
    /**
     * Tạo phiên mới (Chỉ chặn trùng phiên diễn ra TRONG NGÀY)
     */
    public function taoPhienDiemDanhMoi($ma_lop, $ma_giao_vien, $tieu_de, $ghi_chu, $loai_phien, $thoi_gian_mo, $thoi_gian_dong, $yeu_cau_mat_khau = false, $mat_khau = null) {
        
        // [SỬA LẠI SQL CHECK] Chỉ kiểm tra phiên đang mở của NGÀY HÔM NAY
        try {
            $sql_check = "SELECT COUNT(*) FROM phien_diem_danh 
                          WHERE ma_lop_hoc = ? 
                          AND trang_thai_phien = 'DangDiemDanh'
                          AND ngay_diem_danh = CURDATE()"; // <--- Thêm điều kiện này
            $stmt_check = $this->db->prepare($sql_check);
            $stmt_check->execute([$ma_lop]);
            if ($stmt_check->fetchColumn() > 0) { return -1; }
        } catch (PDOException $e) { error_log("Lỗi check trùng phiên: " . $e->getMessage()); }

        $trang_thai_phien = ($loai_phien == 'HocSinh' && strtotime($thoi_gian_mo) > time()) ? 'ChuaMo' : 'DangDiemDanh';
        $mat_khau_hash = ($loai_phien == 'HocSinh' && $yeu_cau_mat_khau && !empty($mat_khau)) ? password_hash($mat_khau, PASSWORD_BCRYPT) : null;
        
        if ($loai_phien == 'HocSinh') {
            if (empty($thoi_gian_mo)) $thoi_gian_mo = date('Y-m-d H:i:s');
            if (empty($thoi_gian_dong)) $thoi_gian_dong = date('Y-m-d H:i:s', time() + 15 * 60);
        } else {
            $thoi_gian_mo = null; $thoi_gian_dong = null; $yeu_cau_mat_khau = false; $mat_khau_hash = null;
        }

        $sql = "INSERT INTO phien_diem_danh 
                (ngay_diem_danh, thoi_gian, tieu_de, ghi_chu, trang_thai_phien, ma_lop_hoc, ma_giao_vien, loai_phien, thoi_gian_mo, thoi_gian_dong, yeu_cau_mat_khau, mat_khau_phien)
                VALUES (CURDATE(), CURTIME(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$tieu_de, $ghi_chu, $trang_thai_phien, $ma_lop, $ma_giao_vien, $loai_phien, $thoi_gian_mo, $thoi_gian_dong, $yeu_cau_mat_khau ? 1 : 0, $mat_khau_hash]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) { return false; }
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
    // public function getChiTietDiemDanh($ma_phien, $ma_lop) {
    //     $sql = "SELECT 
    //                 nd.ma_nguoi_dung, 
    //                 nd.ho_ten,
    //                 ct.trang_thai_diem_danh,
    //                 ct.thoi_gian_nop
    //             FROM hoc_sinh hs
    //             JOIN nguoi_dung nd ON hs.ma_nguoi_dung = nd.ma_nguoi_dung
    //             LEFT JOIN chi_tiet_diem_danh ct ON hs.ma_nguoi_dung = ct.ma_nguoi_dung AND ct.ma_phien = ?
    //             WHERE hs.ma_lop = ? AND hs.trang_thai = 'DangHoc'
    //             ORDER BY nd.ho_ten";
        
    //     $stmt = $this->db->prepare($sql);
    //     $stmt->execute([$ma_phien, $ma_lop]);
    //     return $stmt->fetchAll();
    // }
    public function getChiTietDiemDanh($ma_phien, $ma_lop) {
        if ($this->db === null) return [];
        
        $sql = "SELECT 
                    hs.ma_hoc_sinh,
                    nd.ho_ten,
                    COALESCE(ct.trang_thai_diem_danh, 'ChuaDiemDanh') AS trang_thai,
                    ct.thoi_gian_nop
                FROM hoc_sinh hs
                JOIN nguoi_dung nd ON hs.ma_hoc_sinh = nd.ma_nguoi_dung
                LEFT JOIN chi_tiet_diem_danh ct 
                    ON ct.ma_phien = ? AND ct.ma_nguoi_dung = hs.ma_hoc_sinh
                WHERE hs.ma_lop = ?
                ORDER BY nd.ho_ten";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_phien, $ma_lop]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getChiTietDiemDanh: " . $e->getMessage());
            return [];
        }
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

    /**
     * MỚI: Kiểm tra mật khẩu phiên (CHỈ cho chế độ HocSinh)
     */
    public function kiemTraMatKhauPhien($ma_phien, $mat_khau_nhap = null) {
        $sql = "SELECT yeu_cau_mat_khau, mat_khau_phien, loai_phien, trang_thai_phien 
                FROM phien_diem_danh WHERE ma_phien = ?";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_phien]);
            $phien = $stmt->fetch();

            if (!$phien) {
                return ['success' => false, 'message' => 'Phiên không tồn tại'];
            }

            // Chỉ cho phép HS điểm danh ở chế độ HocSinh
            if ($phien['loai_phien'] != 'HocSinh') {
                return ['success' => false, 'message' => 'Phiên này không dành cho học sinh điểm danh'];
            }

            // Kiểm tra trạng thái phiên
            if ($phien['trang_thai_phien'] != 'DangDiemDanh') {
                return ['success' => false, 'message' => 'Phiên điểm danh chưa mở hoặc đã đóng'];
            }

            // Nếu không yêu cầu mật khẩu → Pass
            if (!$phien['yeu_cau_mat_khau']) {
                return ['success' => true, 'message' => 'Không cần mật khẩu'];
            }

            // Nếu yêu cầu mật khẩu → Kiểm tra
            if (empty($mat_khau_nhap)) {
                return ['success' => false, 'message' => 'Vui lòng nhập mật khẩu'];
            }

            if (password_verify($mat_khau_nhap, $phien['mat_khau_phien'])) {
                return ['success' => true, 'message' => 'Mật khẩu đúng'];
            } else {
                return ['success' => false, 'message' => 'Mật khẩu sai'];
            }
        } catch (PDOException $e) {
            error_log("Lỗi kiemTraMatKhauPhien: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi hệ thống'];
        }
    }

    /**
     * MỚI: Học sinh tự điểm danh (CHỈ dùng cho loai_phien = HocSinh)
     */
    public function diemDanhHocSinh($ma_phien, $ma_hoc_sinh, $mat_khau_nhap = null) {
        // Bước 1: Kiểm tra mật khẩu (nếu có)
        $check = $this->kiemTraMatKhauPhien($ma_phien, $mat_khau_nhap);
        if (!$check['success']) {
            return $check; // Trả về lỗi
        }

        // Bước 2: Kiểm tra HS đã điểm danh chưa
        $sql_check = "SELECT ma_ctdd FROM chi_tiet_diem_danh 
                      WHERE ma_phien = ? AND ma_nguoi_dung = ?";
        try {
            $stmt = $this->db->prepare($sql_check);
            $stmt->execute([$ma_phien, $ma_hoc_sinh]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Bạn đã điểm danh rồi'];
            }
        } catch (PDOException $e) {
            error_log("Lỗi kiểm tra điểm danh: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi hệ thống'];
        }

        // Bước 3: Lưu điểm danh
        $sql = "INSERT INTO chi_tiet_diem_danh (ma_phien, ma_nguoi_dung, trang_thai_diem_danh, thoi_gian_nop) 
                VALUES (?, ?, 'CoMat', NOW())";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_phien, $ma_hoc_sinh]);
            return ['success' => true, 'message' => 'Điểm danh thành công'];
        } catch (PDOException $e) {
            error_log("Lỗi diemDanhHocSinh: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi khi lưu điểm danh'];
        }
    }

    /**
     * MỚI: Cập nhật phiên điểm danh
     */
    public function capNhatPhien($ma_phien, $ma_giao_vien, $tieu_de, $ghi_chu, $thoi_gian_mo = null, $thoi_gian_dong = null, $yeu_cau_mat_khau = false, $mat_khau = null) {
        // Kiểm tra phiên có thuộc GV này không
        $sql_check = "SELECT loai_phien, yeu_cau_mat_khau FROM phien_diem_danh 
                      WHERE ma_phien = ? AND ma_giao_vien = ?";
        try {
            $stmt = $this->db->prepare($sql_check);
            $stmt->execute([$ma_phien, $ma_giao_vien]);
            $phien = $stmt->fetch();

            if (!$phien) {
                return ['success' => false, 'message' => 'Phiên không tồn tại hoặc bạn không có quyền'];
            }

            $loai_phien = $phien['loai_phien'];

            // Build SQL động
            $fields = ["tieu_de = ?", "ghi_chu = ?"];
            $params = [$tieu_de, $ghi_chu];

            // Nếu là phiên HS → Cho phép cập nhật thời gian + mật khẩu
            if ($loai_phien == 'HocSinh') {
                if (!empty($thoi_gian_mo)) {
                    $fields[] = "thoi_gian_mo = ?";
                    $params[] = $thoi_gian_mo;
                }
                if (!empty($thoi_gian_dong)) {
                    $fields[] = "thoi_gian_dong = ?";
                    $params[] = $thoi_gian_dong;
                }

                // Cập nhật mật khẩu
                $fields[] = "yeu_cau_mat_khau = ?";
                $params[] = $yeu_cau_mat_khau ? 1 : 0;

                if ($yeu_cau_mat_khau && !empty($mat_khau)) {
                    $fields[] = "mat_khau_phien = ?";
                    $params[] = password_hash($mat_khau, PASSWORD_BCRYPT);
                } elseif (!$yeu_cau_mat_khau) {
                    $fields[] = "mat_khau_phien = NULL";
                }
            }

            $params[] = $ma_phien;
            $params[] = $ma_giao_vien;

            $sql = "UPDATE phien_diem_danh SET " . implode(", ", $fields) . " 
                    WHERE ma_phien = ? AND ma_giao_vien = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return ['success' => true, 'message' => 'Cập nhật phiên thành công'];
        } catch (PDOException $e) {
            error_log("Lỗi capNhatPhien: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
        }
    }

    /**
     * MỚI: Xóa phiên điểm danh (chỉ được xóa nếu chưa có ai điểm danh)
     */
    public function xoaPhien($ma_phien, $ma_giao_vien) {
        try {
            // Kiểm tra quyền sở hữu
            $sql_check = "SELECT ma_phien FROM phien_diem_danh 
                          WHERE ma_phien = ? AND ma_giao_vien = ?";
            $stmt = $this->db->prepare($sql_check);
            $stmt->execute([$ma_phien, $ma_giao_vien]);

            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Phiên không tồn tại hoặc bạn không có quyền'];
            }

            // Kiểm tra có ai đã điểm danh chưa
            $sql_count = "SELECT COUNT(*) as total FROM chi_tiet_diem_danh WHERE ma_phien = ?";
            $stmt_count = $this->db->prepare($sql_count);
            $stmt_count->execute([$ma_phien]);
            $result = $stmt_count->fetch();

            if ($result['total'] > 0) {
                return ['success' => false, 'message' => 'Không thể xóa phiên đã có học sinh điểm danh'];
            }

            // Xóa phiên
            $sql_delete = "DELETE FROM phien_diem_danh WHERE ma_phien = ? AND ma_giao_vien = ?";
            $stmt_delete = $this->db->prepare($sql_delete);
            $stmt_delete->execute([$ma_phien, $ma_giao_vien]);

            return ['success' => true, 'message' => 'Xóa phiên thành công'];
        } catch (PDOException $e) {
            error_log("Lỗi xoaPhien: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
        }
    }
}
?>