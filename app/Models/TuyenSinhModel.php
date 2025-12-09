<?php
class TuyenSinhModel {
    private $db;

    public function __construct() {
        // Kết nối CSDL (Tự động thử port 3307 và 3306)
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

    // --- API 1: CHỈ TIÊU ---
    public function getDanhSachTruong() {
        return $this->db->query("SELECT ma_truong, ten_truong, chi_tieu_hoc_sinh, so_luong_hoc_sinh FROM truong_thpt ORDER BY ma_truong")->fetchAll();
    }

    public function updateChiTieuBatch($data) {
        $sql = "UPDATE truong_thpt SET chi_tieu_hoc_sinh = ? WHERE ma_truong = ?";
        $stmt = $this->db->prepare($sql);
        foreach ($data as $ma_truong => $chi_tieu) {
            $stmt->execute([$chi_tieu, $ma_truong]);
        }
        return true;
    }

    // --- API 2: NHẬP ĐIỂM ---
    public function getDanhSachThiSinhByTruong($ma_truong) {
        // Lấy thí sinh đăng ký NV1 vào trường này
        $sql = "SELECT ts.ma_nguoi_dung, ts.so_bao_danh, nd.ho_ten, nd.ngay_sinh, ts.truong_thcs, ts.lop_hoc,
                       dts.diem_toan, dts.diem_van, dts.diem_anh
                FROM thi_sinh ts
                JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                JOIN nguyen_vong nv ON ts.ma_nguoi_dung = nv.ma_nguoi_dung
                LEFT JOIN diem_thi_tuyen_sinh dts ON ts.ma_nguoi_dung = dts.ma_nguoi_dung
                WHERE nv.thu_tu_nguyen_vong = 1 AND nv.ma_truong = ?
                ORDER BY ts.so_bao_danh ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_truong]);
        return $stmt->fetchAll();
    }

    public function updateDiemBatch($payload) {
        $sql = "INSERT INTO diem_thi_tuyen_sinh (ma_nguoi_dung, diem_toan, diem_van, diem_anh, nam_tuyen_sinh)
                VALUES (?, ?, ?, ?, 2025)
                ON DUPLICATE KEY UPDATE 
                diem_toan = VALUES(diem_toan), diem_van = VALUES(diem_van), diem_anh = VALUES(diem_anh)";
        $stmt = $this->db->prepare($sql);
        foreach ($payload as $row) {
            $stmt->execute([$row['ma_nguoi_dung'], $row['diem_toan'], $row['diem_van'], $row['diem_anh']]);
        }
        return true;
    }

    // --- API 3: LỌC ẢO (CORE LOGIC) ---
    public function runLocAo() {
        $this->db->beginTransaction();
        try {
            // 1. Reset dữ liệu cũ
            $this->db->exec("DELETE FROM ket_qua_thi_tuyen_sinh");
            $this->db->exec("UPDATE truong_thpt SET so_luong_hoc_sinh = 0");

            // 2. Lấy thí sinh có đủ điểm (Toán * 2 + Văn * 2 + Anh)
            $sql = "SELECT dts.ma_diem_thi, dts.ma_nguoi_dung, 
                           (dts.diem_toan * 2 + dts.diem_van * 2 + dts.diem_anh) as tong_diem 
                    FROM diem_thi_tuyen_sinh dts
                    WHERE diem_toan IS NOT NULL AND diem_van IS NOT NULL AND diem_anh IS NOT NULL
                    ORDER BY tong_diem DESC";
            $thi_sinh_list = $this->db->query($sql)->fetchAll();

            // 3. Lấy chỉ tiêu
            $chi_tieu = $this->db->query("SELECT ma_truong, chi_tieu_hoc_sinh FROM truong_thpt")->fetchAll(PDO::FETCH_KEY_PAIR);
            $da_tuyen = array_fill_keys(array_keys($chi_tieu), 0);

            // 4. Xét tuyển
            $stmtInsert = $this->db->prepare("INSERT INTO ket_qua_thi_tuyen_sinh (ma_diem_thi, tong_diem, trang_thai, ma_nguyen_vong_trung_tuyen, ma_truong_trung_tuyen, trang_thai_xac_nhan) VALUES (?, ?, ?, ?, ?, 'Cho_xac_nhan')");
            
            foreach ($thi_sinh_list as $ts) {
                $passed = false;
                $nvs = $this->db->query("SELECT ma_nguyen_vong, ma_truong FROM nguyen_vong WHERE ma_nguoi_dung = {$ts['ma_nguoi_dung']} ORDER BY thu_tu_nguyen_vong ASC")->fetchAll();
                
                foreach ($nvs as $nv) {
                    $tid = $nv['ma_truong'];
                    if (isset($chi_tieu[$tid]) && $da_tuyen[$tid] < $chi_tieu[$tid]) {
                        $da_tuyen[$tid]++;
                        $stmtInsert->execute([$ts['ma_diem_thi'], $ts['tong_diem'], 'Dau', $nv['ma_nguyen_vong'], $tid]);
                        $passed = true;
                        break; 
                    }
                }
                if (!$passed) {
                    $this->db->prepare("INSERT INTO ket_qua_thi_tuyen_sinh (ma_diem_thi, tong_diem, trang_thai, trang_thai_xac_nhan) VALUES (?, ?, 'Truot', 'Cho_xac_nhan')")->execute([$ts['ma_diem_thi'], $ts['tong_diem']]);
                }
            }

            // 5. Cập nhật số lượng tạm tính
            foreach ($da_tuyen as $mt => $sl) {
                $this->db->prepare("UPDATE truong_thpt SET so_luong_hoc_sinh = ? WHERE ma_truong = ?")->execute([$sl, $mt]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getKetQuaLoc() {
        $sql = "SELECT ts.so_bao_danh, nd.ho_ten, kq.tong_diem, kq.trang_thai, kq.trang_thai_xac_nhan, COALESCE(t.ten_truong, '---') as truong_trung_tuyen
                FROM ket_qua_thi_tuyen_sinh kq
                JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
                JOIN thi_sinh ts ON dts.ma_nguoi_dung = ts.ma_nguoi_dung
                JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                LEFT JOIN truong_thpt t ON kq.ma_truong_trung_tuyen = t.ma_truong
                ORDER BY kq.tong_diem DESC";
        return $this->db->query($sql)->fetchAll();
    }

    // --- API 4: CHỐT DANH SÁCH ---
    public function getDsLopKhoi10() {
        return $this->db->query("SELECT ma_lop, ten_lop, ma_truong, khoi FROM lop_hoc WHERE khoi = 10 AND trang_thai_lop = 'HoatDong'")->fetchAll();
    }

    public function getThiSinhTrungTuyenTheoTruong($ma_truong) {
        $sql = "SELECT kq.ma_ket_qua_tuyen_sinh, ts.so_bao_danh, nd.ho_ten, nd.ngay_sinh, kq.tong_diem, kq.trang_thai_xac_nhan
                FROM ket_qua_thi_tuyen_sinh kq
                JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
                JOIN thi_sinh ts ON dts.ma_nguoi_dung = ts.ma_nguoi_dung
                JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
                WHERE kq.ma_truong_trung_tuyen = ? AND kq.trang_thai = 'Dau'
                ORDER BY kq.tong_diem DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_truong]);
        return $stmt->fetchAll();
    }

    public function updateTrangThaiXacNhanBatch($data, $ma_truong) {
        $sql = "UPDATE ket_qua_thi_tuyen_sinh SET trang_thai_xac_nhan = ? WHERE ma_ket_qua_tuyen_sinh = ? AND ma_truong_trung_tuyen = ?";
        $stmt = $this->db->prepare($sql);
        foreach ($data as $item) {
            $stmt->execute([$item['trang_thai'], $item['ma_ket_qua'], $ma_truong]);
        }
        return true;
    }

    public function chotNhapHoc($ma_truong, $ma_lop_dich) {
        $this->db->beginTransaction();
        try {
            // Lấy thí sinh đã XÁC NHẬN nhập học
            $sql = "SELECT dts.ma_nguoi_dung 
                    FROM ket_qua_thi_tuyen_sinh kq
                    JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
                    WHERE kq.ma_truong_trung_tuyen = ? AND kq.trang_thai_xac_nhan = 'Xac_nhan_nhap_hoc'
                    AND NOT EXISTS (SELECT 1 FROM hoc_sinh hs WHERE hs.ma_hoc_sinh = dts.ma_nguoi_dung)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_truong]);
            $users = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $stmtIns = $this->db->prepare("INSERT INTO hoc_sinh (ma_hoc_sinh, ngay_nhap_hoc, trang_thai, ma_lop) VALUES (?, CURDATE(), 'DangHoc', ?)");
            $count = 0;
            foreach ($users as $uid) {
                $stmtIns->execute([$uid, $ma_lop_dich]);
                $count++;
            }
            
            // Cập nhật lại sĩ số lớp
             $this->db->prepare("UPDATE lop_hoc SET si_so = (SELECT COUNT(*) FROM hoc_sinh WHERE ma_lop = ?) WHERE ma_lop = ?")->execute([$ma_lop_dich, $ma_lop_dich]);

            $this->db->commit();
            return $count;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getDsTruongThcs() {
        $sql = "SELECT DISTINCT truong_thcs FROM thi_sinh WHERE truong_thcs IS NOT NULL AND truong_thcs != '' ORDER BY truong_thcs";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDsThiSinhByTruongThcs($truong_thcs) {
        $sql = "
            SELECT 
                ts.ma_nguoi_dung,
                nd.ho_ten,
                nd.ngay_sinh,
                ts.so_bao_danh,
                ts.lop_hoc,
                ts.truong_thcs,
                
                nv1.ma_truong AS ma_truong_nv1,
                t1.ten_truong AS ten_truong_nv1,
                
                nv2.ma_truong AS ma_truong_nv2,
                t2.ten_truong AS ten_truong_nv2,
                
                nv3.ma_truong AS ma_truong_nv3,
                t3.ten_truong AS ten_truong_nv3,
                
                dt.diem_toan,
                dt.diem_van,
                dt.diem_anh
            FROM thi_sinh ts
            JOIN nguoi_dung nd ON ts.ma_nguoi_dung = nd.ma_nguoi_dung
            
            LEFT JOIN nguyen_vong nv1 ON ts.ma_nguoi_dung = nv1.ma_nguoi_dung AND nv1.thu_tu_nguyen_vong = 1
            LEFT JOIN truong_thpt t1 ON nv1.ma_truong = t1.ma_truong
            
            LEFT JOIN nguyen_vong nv2 ON ts.ma_nguoi_dung = nv2.ma_nguoi_dung AND nv2.thu_tu_nguyen_vong = 2
            LEFT JOIN truong_thpt t2 ON nv2.ma_truong = t2.ma_truong
            
            LEFT JOIN nguyen_vong nv3 ON ts.ma_nguoi_dung = nv3.ma_nguoi_dung AND nv3.thu_tu_nguyen_vong = 3
            LEFT JOIN truong_thpt t3 ON nv3.ma_truong = t3.ma_truong
            
            LEFT JOIN diem_thi_tuyen_sinh dt ON ts.ma_nguoi_dung = dt.ma_nguoi_dung
            
            WHERE ts.truong_thcs = :truong_thcs
            ORDER BY ts.so_bao_danh
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['truong_thcs' => $truong_thcs]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>