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
                $this->db->exec("SET time_zone = '+07:00'"); // Timezone Việt Nam
                
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
    // public function getLopHocDaPhanCong($ma_giao_vien) {
    //     if ($this->db === null) return [];
        
    //     $sql = "SELECT 
    //                 l.ma_lop, 
    //                 l.ten_lop, 
    //                 l.si_so,
    //                 mh.ma_mon_hoc,
    //                 mh.ten_mon_hoc
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
    //         error_log("Lỗi getLopHocDaPhanCong: " . $e->getMessage());
    //         return [];
    //     }
    // }
    public function getLopHocDaPhanCong($ma_giao_vien) {
        if ($this->db === null) return [];
        
        try {
            $sql = "SELECT DISTINCT 
                        bpc.ma_lop,
                        lh.ten_lop,
                        bpc.ma_mon_hoc,
                        mh.ten_mon_hoc,
                        lh.si_so
                    FROM bang_phan_cong bpc
                    INNER JOIN lop_hoc lh ON bpc.ma_lop = lh.ma_lop
                    INNER JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
                    WHERE bpc.ma_giao_vien = ?
                      AND mh.ten_mon_hoc NOT IN ('Chào cờ', 'Sinh hoạt lớp')
                    ORDER BY mh.ten_mon_hoc ASC, lh.ten_lop ASC";
            
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
        
        // ✅ VALIDATE dữ liệu đầu vào
        $required = ['ten_bai_tap', 'mo_ta_chung', 'han_nop', 'loai_bai_tap', 'ma_lop', 'ma_giao_vien', 'ma_mon_hoc'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                return ['success' => false, 'message' => "Thiếu trường bắt buộc: $field"];
            }
        }
        
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
                ':file_kem' => $data['file_dinh_kem'] ?? null,
                ':ma_lop' => $data['ma_lop'],
                ':ma_gv' => $data['ma_giao_vien'],
                ':ma_mon' => $data['ma_mon_hoc']
            ]);
            
            $ma_bai_tap_moi = $this->db->lastInsertId();

            // 2. Insert vào bảng con
            if ($data['loai_bai_tap'] == 'TuLuan') {
                $sql_child = "INSERT INTO bai_tap_tu_luan (ma_bai_tap, de_bai_chi_tiet) VALUES (?, ?)";
                $this->db->prepare($sql_child)->execute([$ma_bai_tap_moi, $data['noi_dung_tu_luan'] ?? '']);
            
            } elseif ($data['loai_bai_tap'] == 'UploadFile') {
                $sql_child = "INSERT INTO bai_tap_upload_file (ma_bai_tap, loai_file_cho_phep, dung_luong_toi_da) VALUES (?, ?, ?)";
                $this->db->prepare($sql_child)->execute([
                    $ma_bai_tap_moi, 
                    $data['loai_file_cho_phep'] ?? 'pdf,docx,jpg,png', 
                    $data['dung_luong_toi_da'] ?? 5242880 // 5MB default
                ]);
            
            } elseif ($data['loai_bai_tap'] == 'TracNghiem') {
                $sql_child = "INSERT INTO bai_tap_trac_nghiem (ma_bai_tap, danh_sach_cau_hoi, thoi_gian_lam_bai) VALUES (?, ?, ?)";
                $this->db->prepare($sql_child)->execute([
                    $ma_bai_tap_moi, 
                    $data['json_trac_nghiem'] ?? '[]', 
                    $data['thoi_gian_lam_bai'] ?? 60
                ]);
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
                    bt.ma_bai_tap DESC"; // <--- CHỖ NÀY: Sắp xếp theo ID là chuẩn nhất (Bài mới tạo ID to nhất)
        
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
        if ($this->db === null) return null;
        
        try {
            $sql = "SELECT 
                        l.ten_lop, mh.ten_mon_hoc, l.ma_lop, mh.ma_mon_hoc
                    FROM lop_hoc l
                    CROSS JOIN mon_hoc mh
                    WHERE l.ma_lop = ? AND mh.ma_mon_hoc = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_lop, $ma_mon_hoc]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Lỗi getThongTinLopMonHoc: " . $e->getMessage());
            return null;
        }
    }

    /**
     * HÀM MỚI (STEP 5): Lấy thông tin cơ bản của bài tập (để lấy ma_lop)
     */
    public function getThongTinCoBanBaiTap($ma_bai_tap) {
        if ($this->db === null) return null;
        
        try {
            // ✅ ĐÃ SỬA: Thêm han_nop, loai_bai_tap, file_dinh_kem
            $stmt = $this->db->prepare("SELECT ma_bai_tap, ma_lop, ma_mon_hoc, ten_bai_tap, mo_ta, han_nop, loai_bai_tap, file_dinh_kem FROM bai_tap WHERE ma_bai_tap = ?");
            $stmt->execute([$ma_bai_tap]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Lỗi getThongTinCoBanBaiTap: " . $e->getMessage());
            return null;
        }
    }
    /**
     * HÀM MỚI (STEP 5): Lấy DS Lớp và join với ai đã nộp
     * ✅ FIX DATETIME: Convert sang UTC+7 (Vietnam time)
     */
    public function getThongKeNopBai($ma_bai_tap, $ma_lop) {
        if ($this->db === null) return [];
        
        try {
            $sql = "SELECT 
                        hs.ma_hoc_sinh, 
                        nd.ho_ten, 
                        bn.ma_bai_nop, 
                        bn.ngay_nop,
                        CONVERT_TZ(bn.ngay_nop, '+00:00', '+07:00') as ngay_nop_vietnam,
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
        } catch (PDOException $e) {
            error_log("Lỗi getThongKeNopBai: " . $e->getMessage());
            return [];
        }
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

        try {
            // 1. Đếm tổng số bài ĐÃ NỘP (Actual)
            $sqlActual = "SELECT COUNT(*) FROM bai_nop bn 
                          JOIN bai_tap bt ON bn.ma_bai_tap = bt.ma_bai_tap 
                          WHERE bt.ma_giao_vien = ?";
            $stmt1 = $this->db->prepare($sqlActual);
            $stmt1->execute([$gv_id]);
            $actual = $stmt1->fetchColumn() ?? 0;

            // 2. Đếm số bài tập đã giao
            $sqlBaiTap = "SELECT COUNT(*) FROM bai_tap WHERE ma_giao_vien = ?";
            $stmt2 = $this->db->prepare($sqlBaiTap);
            $stmt2->execute([$gv_id]);
            $soBaiTap = $stmt2->fetchColumn() ?? 0;

            // 3. Đếm tổng sĩ số các lớp được giao bài
            $sqlSiSo = "SELECT SUM(DISTINCT lh.si_so) 
                        FROM bai_tap bt 
                        JOIN lop_hoc lh ON bt.ma_lop = lh.ma_lop 
                        WHERE bt.ma_giao_vien = ?";
            $stmt3 = $this->db->prepare($sqlSiSo);
            $stmt3->execute([$gv_id]);
            $tongSiSo = $stmt3->fetchColumn() ?? 0;

            // 4. Tính expected = số bài tập * tổng sĩ số
            $expected = $soBaiTap * $tongSiSo;

            // 5. Tính phần trăm
            if ($expected > 0) {
                return round(($actual / $expected) * 100, 1);
            }
            return 0;
        } catch (PDOException $e) {
            error_log("Lỗi getTyLeNopBaiTB: " . $e->getMessage());
            return 0;
        }
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

        try {
            $sql = "SELECT ct.trang_thai_diem_danh, COUNT(*) as so_luong
                    FROM phien_diem_danh p
                    JOIN chi_tiet_diem_danh ct ON p.ma_phien = ct.ma_phien
                    WHERE p.ma_giao_vien = ?
                    GROUP BY ct.trang_thai_diem_danh";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$gv_id]);
            
            $data = ['CoMat' => 0, 'Vang' => 0];
            
            foreach ($stmt->fetchAll() as $row) {
                // Chuẩn hóa: lowercase + trim
                $status = mb_strtolower(trim($row['trang_thai_diem_danh']), 'UTF-8');
                
                // Loại bỏ dấu tiếng Việt để so sánh dễ hơn
                $statusClean = str_replace(
                    ['ó', 'ă', 'ặ', 'có', 'co', '_', ' '],
                    ['o', 'a', 'a', 'co', 'co', '', ''],
                    $status
                );
                
                // Check nếu chứa "comat" hoặc "comat"
                if (strpos($statusClean, 'comat') !== false || 
                    $status == 'có mặt' || 
                    $status == 'co_mat' ||
                    $status == 'comat') {
                    $data['CoMat'] += $row['so_luong'];
                } else {
                    $data['Vang'] += $row['so_luong'];
                }
            }
            
            return $data;
        } catch (PDOException $e) {
            error_log("Lỗi getChartDiemDanh: " . $e->getMessage());
            return ['CoMat' => 0, 'Vang' => 0];
        }
    }

    // ========== CHỨC NĂNG SỬA/XÓA BÀI TẬP ==========

    /**
     * Sửa bài tập (chỉ cho GV tạo)
     */
    public function suaBaiTap($ma_bai_tap, $ma_giao_vien, $data) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi kết nối CSDL.'];

        try {
            // 1. Kiểm tra quyền sở hữu
            $stmt = $this->db->prepare("SELECT ma_bai_tap FROM bai_tap WHERE ma_bai_tap = ? AND ma_giao_vien = ?");
            $stmt->execute([$ma_bai_tap, $ma_giao_vien]);
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Không tìm thấy bài tập hoặc bạn không có quyền sửa.'];
            }

            // 2. Xây dựng câu SQL Update động
            // Luôn update các trường cơ bản
            $sql = "UPDATE bai_tap SET 
                        ten_bai_tap = :ten,
                        mo_ta = :mo_ta,
                        han_nop = :han_nop";
            
            // Chỉ update file nếu có file mới
            if (!empty($data['file_dinh_kem'])) {
                $sql .= ", file_dinh_kem = :file";
            }

            $sql .= " WHERE ma_bai_tap = :id";
            
            $stmt = $this->db->prepare($sql);
            
            // Bind tham số
            $stmt->bindValue(':ten', $data['ten_bai_tap']);
            $stmt->bindValue(':mo_ta', $data['mo_ta']);
            $stmt->bindValue(':han_nop', $data['han_nop']);
            $stmt->bindValue(':id', $ma_bai_tap);

            if (!empty($data['file_dinh_kem'])) {
                $stmt->bindValue(':file', $data['file_dinh_kem']);
            }

            $stmt->execute();

            return ['success' => true, 'message' => 'Cập nhật thành công!'];

        } catch (PDOException $e) {
            // Ghi log lỗi để debug
            error_log("Lỗi suaBaiTap: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
        }
    }

    /**
     * Xóa bài tập (chỉ được xóa nếu chưa có HS nào nộp)
     */
    public function xoaBaiTap($ma_bai_tap, $ma_giao_vien) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi kết nối CSDL.'];

        try {
            // 1. Kiểm tra bài tập có tồn tại và thuộc về GV này không
            $stmt = $this->db->prepare("SELECT ma_bai_tap FROM bai_tap WHERE ma_bai_tap = ? AND ma_giao_vien = ?");
            $stmt->execute([$ma_bai_tap, $ma_giao_vien]);
            
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Bài tập không tồn tại hoặc bạn không có quyền xóa!'];
            }

            // 2. Kiểm tra xem có HS nào đã nộp bài không
            $stmt = $this->db->prepare("SELECT COUNT(*) as so_nop FROM bai_nop WHERE ma_bai_tap = ?");
            $stmt->execute([$ma_bai_tap]);
            $result = $stmt->fetch();

            if ($result['so_nop'] > 0) {
                return ['success' => false, 'message' => 'Không thể xóa! Đã có ' . $result['so_nop'] . ' học sinh nộp bài.'];
            }

            // 3. Xóa bảng con trước
            $this->db->prepare("DELETE FROM bai_tap_tu_luan WHERE ma_bai_tap = ?")->execute([$ma_bai_tap]);
            $this->db->prepare("DELETE FROM bai_tap_upload_file WHERE ma_bai_tap = ?")->execute([$ma_bai_tap]);
            $this->db->prepare("DELETE FROM bai_tap_trac_nghiem WHERE ma_bai_tap = ?")->execute([$ma_bai_tap]);

            // 4. Xóa bảng cha
            $this->db->prepare("DELETE FROM bai_tap WHERE ma_bai_tap = ?")->execute([$ma_bai_tap]);

            return ['success' => true, 'message' => 'Xóa bài tập thành công!'];

        } catch (PDOException $e) {
            error_log("Lỗi xoaBaiTap: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()];
        }
    }

    // ========== FIX DATETIME TIMEZONE ==========

    /**
     * ✅ MỚI: Lấy chi tiết 1 bài nộp (kèm datetime chuyển UTC+7)
     */
    // public function getChiTietBaiNop($ma_bai_nop) {
    //     if ($this->db === null) return null;

    //     try {
    //         $sql = "SELECT 
    //                     bn.ma_bai_nop,
    //                     bn.ma_bai_tap,
    //                     bn.ma_nguoi_dung,
    //                     nd.ho_ten,
    //                     bn.ngay_nop,
    //                     CONVERT_TZ(bn.ngay_nop, '+00:00', '+07:00') as ngay_nop_vietnam,
    //                     bn.trang_thai,
    //                     bn.diem_so,
    //                     bn.file_nop,
    //                     bt.ten_bai_tap,
    //                     bt.han_nop
    //                 FROM 
    //                     bai_nop bn
    //                 JOIN 
    //                     nguoi_dung nd ON bn.ma_nguoi_dung = nd.ma_nguoi_dung
    //                 JOIN 
    //                     bai_tap bt ON bn.ma_bai_tap = bt.ma_bai_tap
    //                 WHERE 
    //                     bn.ma_bai_nop = ?";

    //         $stmt = $this->db->prepare($sql);
    //         $stmt->execute([$ma_bai_nop]);
    //         return $stmt->fetch();
    //     } catch (PDOException $e) {
    //         error_log("Lỗi getChiTietBaiNop: " . $e->getMessage());
    //         return null;
    //     }
    // }
    /**
     * Lấy chi tiết bài làm để hiển thị lên Modal chấm điểm
     */
    public function getChiTietBaiNop($ma_bai_nop) {
        if ($this->db === null) return null;

        try {
            $sql = "SELECT 
                        bn.ma_bai_nop,
                        bn.ma_bai_tap,
                        bn.diem_so,
                        bn.nhan_xet,      -- Cột mới thêm
                        bn.file_dinh_kem, -- Cột file của HS
                        bn.noi_dung_tra_loi, -- Cột bài làm text của HS
                        nd.ho_ten
                    FROM 
                        bai_nop bn
                    JOIN 
                        nguoi_dung nd ON bn.ma_nguoi_dung = nd.ma_nguoi_dung
                    WHERE 
                        bn.ma_bai_nop = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_bai_nop]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Lỗi getChiTietBaiNop: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lưu điểm số và nhận xét
     */
    public function capNhatDiemSo($ma_bai_nop, $diem_so, $nhan_xet) {
        if ($this->db === null) return false;
        try {
            // Cập nhật điểm, lời phê và đổi trạng thái thành Hoàn Thành
            $sql = "UPDATE bai_nop 
                    SET diem_so = ?, 
                        nhan_xet = ?, 
                        trang_thai = 'HoanThanh' 
                    WHERE ma_bai_nop = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$diem_so, $nhan_xet, $ma_bai_nop]);
        } catch (PDOException $e) {
            error_log("Lỗi capNhatDiemSo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ FIX: Lấy danh sách bài nộp với datetime đúng timezone
     */
    public function getDanhSachBaiNopCuaBaiTap($ma_bai_tap, $ma_lop = null) {
        if ($this->db === null) return [];

        try {
            $sql = "SELECT 
                        bn.ma_bai_nop,
                        bn.ma_bai_tap,
                        nd.ho_ten,
                        bn.ngay_nop,
                        CONVERT_TZ(bn.ngay_nop, '+00:00', '+07:00') as ngay_nop_vietnam,
                        bn.trang_thai,
                        bn.diem_so,
                        bn.file_nop
                    FROM 
                        bai_nop bn
                    JOIN 
                        nguoi_dung nd ON bn.ma_nguoi_dung = nd.ma_nguoi_dung
                    WHERE 
                        bn.ma_bai_tap = ?";
            
            if ($ma_lop) {
                $sql .= " AND bn.ma_nguoi_dung IN (SELECT ma_hoc_sinh FROM hoc_sinh WHERE ma_lop = ?)";
            }
            
            $sql .= " ORDER BY bn.ngay_nop DESC";

            $stmt = $this->db->prepare($sql);
            
            if ($ma_lop) {
                $stmt->execute([$ma_bai_tap, $ma_lop]);
            } else {
                $stmt->execute([$ma_bai_tap]);
            }
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachBaiNopCuaBaiTap: " . $e->getMessage());
            return [];
        }
    }

    

}
?>