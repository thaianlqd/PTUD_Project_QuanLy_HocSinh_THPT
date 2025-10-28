<?php
/**
 * TuyenSinhModel: Xử lý logic CSDL cho chức năng Tuyển sinh
 */
class TuyenSinhModel {
    private $db;
    private $nam_hoc_hien_tai = 1; // Giả sử ID năm học là 1 (từ bảng nam_hoc)

    public function __construct() {
        // Kết nối CSDL
        try {
            $dsn = 'mysql:host=127.0.0.1;port=3307;dbname=thpt_manager;charset=utf8mb4';
            $this->db = new PDO($dsn, 'root', ''); // User: root, Pass: rỗng
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->db->exec("SET NAMES 'utf8mb4'");
        } catch (PDOException $e) {
            error_log('DB Connection failed (TuyenSinhModel): ' . $e->getMessage());
            $this->db = null;
            die("Không thể kết nối CSDL (TuyenSinhModel): " . $e->getMessage());
        }
    }

    /**
     * Lấy danh sách các trường THPT
     */
    public function getDanhSachTruong() {
        if ($this->db === null) return [];
        $sql = "SELECT ma_truong, ten_truong, chi_tieu_hoc_sinh, so_luong_hoc_sinh 
                FROM truong_thpt 
                ORDER BY ten_truong";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachTruong: " . $e->getMessage());
            return [];
        }
    }

    /**
     * LẤY DANH SÁCH LỚP HỌC (Cho dropdown)
     * *** HÀM MỚI ĐƯỢC CHUYỂN VÀO ĐÂY ***
     * @param int|null $ma_nam_hoc
     * @return array
     */
    public function getDanhSachLop($ma_nam_hoc = null) {
        if ($this->db === null) return [];
        
        $sql = "SELECT ma_lop, ten_lop, khoi, ma_truong, si_so 
                FROM lop_hoc 
                WHERE trang_thai_lop = 'HoatDong'";
        
        $params = [];
        if ($ma_nam_hoc !== null) {
            $sql .= " AND ma_nam_hoc = ?";
            $params[] = $ma_nam_hoc;
        }
        $sql .= " ORDER BY ten_lop";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachLop (TuyenSinhModel): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cập nhật chỉ tiêu cho 1 trường
     */
    public function updateChiTieu($ma_truong, $chi_tieu) {
        if ($this->db === null) return false;
        $sql = "UPDATE truong_thpt SET chi_tieu_hoc_sinh = ? WHERE ma_truong = ?";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$chi_tieu, $ma_truong]);
        } catch (PDOException $e) {
            error_log("Lỗi updateChiTieu: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy danh sách thí sinh và điểm thi (nếu có), filter theo ma_truong (NV1)
     */
    public function getDanhSachThiSinh($ma_truong = null) {
        if ($this->db === null) return [];
        
        $sql = "SELECT 
                    ts.ma_nguoi_dung, 
                    ts.so_bao_danh, 
                    nd.ho_ten, 
                    nd.ngay_sinh, 
                    ts.truong_thcs, 
                    ts.lop_hoc,
                    dts.diem_toan,
                    dts.diem_van,
                    dts.diem_anh
                FROM thi_sinh ts
                JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                LEFT JOIN diem_thi_tuyen_sinh dts ON ts.ma_nguoi_dung = dts.ma_nguoi_dung
                LEFT JOIN nguyen_vong nv ON ts.ma_nguoi_dung = nv.ma_nguoi_dung AND nv.thu_tu_nguyen_vong = 1";
        
        $params = [];
        if ($ma_truong) {
            $sql .= " WHERE nv.ma_truong = ?";
            $params[] = $ma_truong;
        }
        $sql .= " ORDER BY ts.so_bao_danh";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachThiSinh: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cập nhật điểm thi cho thí sinh
     */
    public function updateDiemThi($ma_nguoi_dung, $diem_toan, $diem_van, $diem_anh) {
        if ($this->db === null) return false;
        
        $sql = "INSERT INTO diem_thi_tuyen_sinh (ma_nguoi_dung, diem_toan, diem_van, diem_anh, nam_tuyen_sinh)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                diem_toan = VALUES(diem_toan),
                diem_van = VALUES(diem_van),
                diem_anh = VALUES(diem_anh)";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$ma_nguoi_dung, $diem_toan, $diem_van, $diem_anh, 2025]);
        } catch (PDOException $e) {
            error_log("Lỗi updateDiemThi: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Chạy logic xét tuyển (Giả lập)
     */
    public function runXetTuyen() {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi CSDL'];
        
        $this->db->beginTransaction();
        try {
            // Bước 1: Dọn dẹp kết quả cũ và sĩ số cũ của trường
            $this->db->exec("DELETE FROM ket_qua_thi_tuyen_sinh");
            $this->db->exec("UPDATE truong_thpt SET so_luong_hoc_sinh = 0"); 

            // Bước 2: Lấy tất cả thí sinh có điểm
            $sql_ts_diem = "SELECT ma_diem_thi, ma_nguoi_dung, (diem_toan + diem_van + diem_anh) AS tong_diem
                            FROM diem_thi_tuyen_sinh
                            WHERE diem_toan IS NOT NULL AND diem_van IS NOT NULL AND diem_anh IS NOT NULL
                            ORDER BY tong_diem DESC, diem_toan DESC"; 
            
            $stmt_ts_diem = $this->db->prepare($sql_ts_diem);
            $stmt_ts_diem->execute();
            $thi_sinh_list = $stmt_ts_diem->fetchAll();

            if (empty($thi_sinh_list)) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Không có thí sinh nào có đủ điểm để xét tuyển.'];
            }

            // Bước 3: Lấy chỉ tiêu các trường
            $sql_truong = "SELECT ma_truong, chi_tieu_hoc_sinh FROM truong_thpt ORDER BY ma_truong";
            $stmt_truong = $this->db->prepare($sql_truong);
            $stmt_truong->execute();
            $chi_tieu_truong = $stmt_truong->fetchAll(PDO::FETCH_KEY_PAIR);
            
            $so_luong_trung_tuyen_dem = array_fill_keys(array_keys($chi_tieu_truong), 0);
            $ket_qua_xet_tuyen = [];
            $thi_sinh_da_trung_tuyen = []; 

            // Bước 4: Xét tuyển (logic đơn giản: duyệt NV1, NV2)
            for ($nv_thu = 1; $nv_thu <= 2; $nv_thu++) {
                foreach ($thi_sinh_list as $ts) {
                    $ma_ts = $ts['ma_nguoi_dung'];
                    
                    if (isset($thi_sinh_da_trung_tuyen[$ma_ts])) {
                        continue;
                    }

                    $sql_nv = "SELECT ma_nguyen_vong, ma_truong FROM nguyen_vong WHERE ma_nguoi_dung = ? AND thu_tu_nguyen_vong = ?";
                    $stmt_nv = $this->db->prepare($sql_nv);
                    $stmt_nv->execute([$ma_ts, $nv_thu]);
                    $nguyen_vong = $stmt_nv->fetch();

                    if ($nguyen_vong) {
                        $ma_truong_nv = $nguyen_vong['ma_truong'];
                        
                        if (isset($chi_tieu_truong[$ma_truong_nv]) && $so_luong_trung_tuyen_dem[$ma_truong_nv] < $chi_tieu_truong[$ma_truong_nv]) {
                            $ket_qua_xet_tuyen[$ma_ts] = [
                                'ma_diem_thi' => $ts['ma_diem_thi'],
                                'tong_diem' => $ts['tong_diem'],
                                'trang_thai' => 'Dau',
                                'ma_nguyen_vong_trung_tuyen' => $nguyen_vong['ma_nguyen_vong'],
                                'ma_truong_trung_tuyen' => $ma_truong_nv
                            ];
                            $so_luong_trung_tuyen_dem[$ma_truong_nv]++; 
                            $thi_sinh_da_trung_tuyen[$ma_ts] = true; 
                        }
                    }
                } 
            } 

            // Bước 5: Thêm các thí sinh trượt
            foreach ($thi_sinh_list as $ts) {
                 if (!isset($thi_sinh_da_trung_tuyen[$ts['ma_nguoi_dung']])) {
                     $ket_qua_xet_tuyen[$ts['ma_nguoi_dung']] = [
                         'ma_diem_thi' => $ts['ma_diem_thi'],
                         'tong_diem' => $ts['tong_diem'],
                         'trang_thai' => 'Truot',
                         'ma_nguyen_vong_trung_tuyen' => null,
                         'ma_truong_trung_tuyen' => null
                     ];
                 }
            }
            
            // Bước 6: INSERT kết quả vào CSDL
            $sql_insert_kq = "INSERT INTO ket_qua_thi_tuyen_sinh (ma_diem_thi, tong_diem, trang_thai, ma_nguyen_vong_trung_tuyen, ma_truong_trung_tuyen, trang_thai_xac_nhan)
                              VALUES (?, ?, ?, ?, ?, 'Cho_xac_nhan')";
            $stmt_insert = $this->db->prepare($sql_insert_kq);
            
            foreach($ket_qua_xet_tuyen as $kq) {
                 $stmt_insert->execute([
                     $kq['ma_diem_thi'],
                     $kq['tong_diem'],
                     $kq['trang_thai'],
                     $kq['ma_nguyen_vong_trung_tuyen'],
                     $kq['ma_truong_trung_tuyen']
                 ]);
            }
            
            // Bước 7: Cập nhật sĩ số trúng tuyển (TẠM THỜI)
            foreach($so_luong_trung_tuyen_dem as $ma_truong => $so_luong) {
                $this->db->prepare("UPDATE truong_thpt SET so_luong_hoc_sinh = ? WHERE ma_truong = ?")
                         ->execute([$so_luong, $ma_truong]);
            }
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Xét tuyển thành công! Đã xét cho ' . count($thi_sinh_list) . ' thí sinh.'];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Lỗi runXetTuyen: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi CSDL khi xét tuyển: ' . $e->getMessage()];
        }
    }
    
    /**
     * Lấy kết quả thí sinh sau khi xét (HIỂN THỊ TOÀN CỤC)
     */
    public function getKetQuaThiSinh() {
        if ($this->db === null) return [];
        $sql = "SELECT 
                    ts.so_bao_danh, 
                    nd.ho_ten, 
                    kq.tong_diem, 
                    kq.trang_thai,
                    COALESCE(tr.ten_truong, 'N/A') AS truong_trung_tuyen,
                    kq.trang_thai_xac_nhan
                FROM ket_qua_thi_tuyen_sinh kq
                JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
                JOIN thi_sinh ts ON dts.ma_nguoi_dung = ts.ma_nguoi_dung
                JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                LEFT JOIN truong_thpt tr ON kq.ma_truong_trung_tuyen = tr.ma_truong
                ORDER BY kq.trang_thai, kq.tong_diem DESC";
        try {
             $stmt = $this->db->prepare($sql);
             $stmt->execute();
             return $stmt->fetchAll();
        } catch (PDOException $e) {
             error_log("Lỗi getKetQuaThiSinh: " . $e->getMessage());
             return [];
        }
    }
    
    /**
     * Lấy kết quả (sĩ số) của các trường sau khi xét
     */
     public function getKetQuaTruong() {
         if ($this->db === null) return [];
         return $this->getDanhSachTruong();
     }

    /**
     * Lấy danh sách thí sinh trúng tuyển theo trường (cho lọc ảo chi tiết)
     * *** ĐÂY LÀ HÀM ĐÃ SỬA LỖI ***
     */
    public function getDanhSachThiSinhTrungTuyenTheoTruong($ma_truong) {
        if ($this->db === null) return [];
        
        $sql = "SELECT 
                    kq.ma_ket_qua_tuyen_sinh, 
                    ts.so_bao_danh, 
                    nd.ho_ten, 
                    nd.ngay_sinh, 
                    kq.tong_diem, 
                    kq.trang_thai_xac_nhan,
                    kq.ngay_xac_nhan,
                    COALESCE(tr.ten_truong, 'N/A') AS ten_truong
                FROM ket_qua_thi_tuyen_sinh kq
                JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi -- <-- LỖI ĐÃ SỬA Ở ĐÂY
                JOIN thi_sinh ts ON dts.ma_nguoi_dung = ts.ma_nguoi_dung
                JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                JOIN truong_thpt tr ON kq.ma_truong_trung_tuyen = tr.ma_truong
                WHERE kq.trang_thai = 'Dau' AND kq.ma_truong_trung_tuyen = ?
                ORDER BY kq.tong_diem DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_truong]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachThiSinhTrungTuyenTheoTruong: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cập nhật trạng thái xác nhận batch cho thí sinh trúng tuyển
     */
    public function capNhatTrangThaiXacNhanBatch($danh_sach_xac_nhan, $ma_truong) {
        if ($this->db === null || empty($danh_sach_xac_nhan)) return false;
        
        $this->db->beginTransaction();
        try {
            $sql = "UPDATE ket_qua_thi_tuyen_sinh 
                    SET trang_thai_xac_nhan = ?, ngay_xac_nhan = NOW() 
                    WHERE ma_ket_qua_tuyen_sinh = ? AND ma_truong_trung_tuyen = ?";
            $stmt = $this->db->prepare($sql);
            
            foreach ($danh_sach_xac_nhan as $item) {
                $trang_thai = $item['trang_thai'] ?? 'Tu_choi_nhap_hoc';
                $ma_ket_qua = (int)$item['ma_ket_qua'];
                
                if (!in_array($trang_thai, ['Xac_nhan_nhap_hoc', 'Tu_choi_nhap_hoc'])) {
                    $trang_thai = 'Tu_choi_nhap_hoc';
                }
                
                $stmt->execute([$trang_thai, $ma_ket_qua, $ma_truong]);
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Lỗi capNhatTrangThaiXacNhanBatch: " . $e->getMessage());
            return false;
        }
    }

    /**
     * CHỐT DANH SÁCH NHẬP HỌC
     */
    public function chotDanhSachNhapHoc($ma_truong, $ma_lop_dich) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi CSDL'];
        
        $this->db->beginTransaction();
        try {
            // Bước 1: Lấy danh sách thí sinh ĐÃ XÁC NHẬN NHẬP HỌC
            $sql_get_ts = "SELECT 
                                dts.ma_nguoi_dung
                           FROM ket_qua_thi_tuyen_sinh kq
                           JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
                           WHERE kq.ma_truong_trung_tuyen = ?
                             AND kq.trang_thai_xac_nhan = 'Xac_nhan_nhap_hoc'
                             AND NOT EXISTS (
                                 SELECT 1 FROM hoc_sinh hs WHERE hs.ma_hoc_sinh = dts.ma_nguoi_dung
                             )";
            
            $stmt_get = $this->db->prepare($sql_get_ts);
            $stmt_get->execute([$ma_truong]);
            $danh_sach_thi_sinh = $stmt_get->fetchAll(PDO::FETCH_COLUMN);

            if (empty($danh_sach_thi_sinh)) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Không có thí sinh mới nào xác nhận nhập học để chốt.'];
            }

            // Bước 2: Chuyển từng thí sinh vào bảng hoc_sinh
            $sql_insert_hs = "INSERT INTO hoc_sinh (ma_hoc_sinh, ngay_nhap_hoc, trang_thai, ma_lop) 
                              VALUES (?, CURDATE(), 'DangHoc', ?)";
            $stmt_insert = $this->db->prepare($sql_insert_hs);
            
            $so_luong_nhap_hoc = 0;
            foreach ($danh_sach_thi_sinh as $ma_nguoi_dung) {
                $stmt_insert->execute([$ma_nguoi_dung, $ma_lop_dich]);
                $so_luong_nhap_hoc++;
            }

            // Bước 3: Cập nhật lại sĩ số THỰC TẾ
            $sql_update_truong = "UPDATE truong_thpt tr
                                  SET so_luong_hoc_sinh = (
                                      SELECT COUNT(hs.ma_hoc_sinh)
                                      FROM hoc_sinh hs
                                      JOIN lop_hoc lh ON hs.ma_lop = lh.ma_lop
                                      WHERE lh.ma_truong = tr.ma_truong AND hs.trang_thai = 'DangHoc'
                                  )
                                  WHERE tr.ma_truong = ?";
            $this->db->prepare($sql_update_truong)->execute([$ma_truong]);

            $this->db->commit();
            return ['success' => true, 'message' => "Đã chốt danh sách. Chuyển thành công $so_luong_nhap_hoc học sinh mới vào lớp."];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Lỗi chotDanhSachNhapHoc: " . $e->getMessage());
            if ($e->errorInfo[1] == 1062) {
                 return ['success' => false, 'message' => 'Lỗi: Một số thí sinh có thể đã tồn tại trong bảng học sinh.'];
            }
            return ['success' => false, 'message' => 'Lỗi CSDL khi chốt danh sách: ' . $e->getMessage()];
        }
    }
}
?>