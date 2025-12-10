<?php
/**
 * ThiSinhModel_NhapHoc: Xử lý logic CSDL cho Đăng Ký Nhập Học
 */
class ThiSinhModel_NhapHoc {
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

                $connected = true;
                break;
            } catch (PDOException $e) {
                error_log("DB connection failed on port $port: " . $e->getMessage());
                continue;
            }
        }

        if (!$connected) {
            throw new Exception("Unable to connect to database on any port (3307, 3306)");
        }
    }

    public function getMaToHopByTruong($ma_truong) {
        try {
            $sql = "SELECT ma_to_hop_mon FROM truong_thpt WHERE ma_truong = :ma_truong";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_truong' => $ma_truong]);
            $result = $stmt->fetch();
            return $result ? $result['ma_to_hop_mon'] : null;
        } catch (Exception $e) {
            error_log("Error getMaToHopByTruong: " . $e->getMessage());
            return null;
        }
    }

    public function getToHopByTruong($ma_truong) {
        $sql = "SELECT DISTINCT th.ma_to_hop_mon, th.ten_to_hop 
                FROM lop_hoc lh
                JOIN to_hop_mon th ON lh.ma_to_hop_mon = th.ma_to_hop_mon
                WHERE lh.ma_truong = :ma_truong AND lh.khoi = 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ma_truong' => $ma_truong]);
        return $stmt->fetchAll();
    }

    /**
     * 1. Lấy danh sách trường trúng tuyển + không trúng tuyển của thí sinh
     * @param int $ma_nguoi_dung
     * @return array ['dau' => [], 'truot' => []]
     */
    public function getDanhSachTruongNhapHoc($ma_nguoi_dung) {
        try {
            // Lấy tất cả NV đã đăng ký
            $sqlNV = "SELECT nv.ma_truong, tt.ten_truong
                    FROM nguyen_vong nv
                    JOIN truong_thpt tt ON nv.ma_truong = tt.ma_truong
                    WHERE nv.ma_nguoi_dung = :ma_nguoi_dung
                    ORDER BY nv.thu_tu_nguyen_vong ASC";
            
            $stmtNV = $this->db->prepare($sqlNV);
            $stmtNV->execute([':ma_nguoi_dung' => $ma_nguoi_dung]);
            $danhSachNV = $stmtNV->fetchAll();
            
            // Lấy kết quả xét tuyển (chỉ trúng tuyển)
            // $sqlKQ = "SELECT kq.ma_ket_qua_tuyen_sinh, kq.trang_thai, kq.trang_thai_xac_nhan, 
            //                 kq.ma_truong_trung_tuyen AS ma_truong, tt.ten_truong,
            //                 tt.chi_tieu_hoc_sinh, tt.so_luong_hoc_sinh, kq.tong_diem
            //         FROM ket_qua_thi_tuyen_sinh kq
            //         JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
            //         LEFT JOIN truong_thpt tt ON kq.ma_truong_trung_tuyen = tt.ma_truong
            //         WHERE dts.ma_nguoi_dung = :ma_nguoi_dung";

            $sqlKQ = "SELECT 
                        kq.ma_ket_qua_tuyen_sinh, 
                        kq.trang_thai, 
                        kq.ma_truong_trung_tuyen AS ma_truong, 
                        tt.ten_truong,
                        kq.tong_diem
                    FROM ket_qua_thi_tuyen_sinh kq
                    JOIN diem_thi_tuyen_sinh dts ON kq.ma_diem_thi = dts.ma_diem_thi
                    JOIN truong_thpt tt ON kq.ma_truong_trung_tuyen = tt.ma_truong
                    WHERE dts.ma_nguoi_dung = :ma_nguoi_dung
                    AND kq.trang_thai LIKE '%dau%'
                    GROUP BY kq.ma_truong_trung_tuyen"; // <--- QUAN TRỌNG: Gộp nhóm theo trường
            
            $stmtKQ = $this->db->prepare($sqlKQ);
            $stmtKQ->execute([':ma_nguoi_dung' => $ma_nguoi_dung]);
            $ketQuaTuyen = $stmtKQ->fetchAll();
            
            // Tìm trường đậu
            $dau = [];
            $maTruongDau = null;
            foreach ($ketQuaTuyen as $kq) {
                if (strtolower($kq['trang_thai']) === 'dau' || strpos(strtolower($kq['trang_thai']), 'trung') !== false) {
                    $dau[] = $kq;
                    $maTruongDau = $kq['ma_truong'];
                }
            }
            
            // Tìm trường trượt (NV đăng ký nhưng không trúng tuyển)
            $truot = [];
            $maTruongDauArray = [$maTruongDau];
            foreach ($danhSachNV as $nv) {
                if (!in_array($nv['ma_truong'], $maTruongDauArray)) {
                    $truot[] = [
                        'ma_truong' => $nv['ma_truong'],
                        'ten_truong' => $nv['ten_truong'],
                        'trang_thai' => 'Truot'
                    ];
                }
            }
            
            error_log("NhapHoc user {$ma_nguoi_dung}: dau=" . count($dau) . " truot=" . count($truot));
            return ['dau' => $dau, 'truot' => $truot];
        } catch (Exception $e) {
            error_log("Error getDanhSachTruongNhapHoc: " . $e->getMessage());
            return ['dau' => [], 'truot' => []];
        }
    }

    public function getAllMonHoc() {
        try {
            $sql = "SELECT ma_mon_hoc, ten_mon_hoc, loai_mon 
                    FROM mon_hoc 
                    WHERE trang_thai_mon_hoc = 'HoatDong'
                    ORDER BY 
                        CASE WHEN loai_mon = 'Bắt buộc' THEN 1 ELSE 2 END,
                        ten_mon_hoc ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $monHoc = $stmt->fetchAll();

            // Phân loại
            $result = [
                'bat_buoc' => [],
                'tu_chon_khtn' => [],
                'tu_chon_khxh' => [],
                'tu_chon_cn_nt' => []
            ];

            foreach ($monHoc as $mon) {
                if ($mon['loai_mon'] === 'Bắt buộc') {
                    $result['bat_buoc'][] = $mon;
                } elseif (strpos($mon['loai_mon'], 'KHTN') !== false) {
                    $result['tu_chon_khtn'][] = $mon;
                } elseif (strpos($mon['loai_mon'], 'KHXH') !== false) {
                    $result['tu_chon_khxh'][] = $mon;
                } elseif (strpos($mon['loai_mon'], 'CN-NT') !== false) {
                    $result['tu_chon_cn_nt'][] = $mon;
                }
            }
            return $result;
        } catch (Exception $e) {
            error_log("Error getAllMonHoc: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 2. Lấy tổ hợp môn của trường (để hiển thị 8 môn bắt buộc + 3 nhóm tự chọn)
     * @param int $ma_to_hop_mon
     * @return array [
     *     'bat_buoc' => [...],
     *     'tu_chon_khtn' => [...],
     *     'tu_chon_khxh' => [...],
     *     'tu_chon_cn_nt' => [...]
     * ]
     */
    public function getMonHocByToHop($ma_to_hop_mon) {
        try {
            $sql = "SELECT 
                        m.ma_mon_hoc,
                        m.ten_mon_hoc,
                        m.loai_mon
                    FROM to_hop_mon_mon_hoc th
                    JOIN mon_hoc m ON th.ma_mon_hoc = m.ma_mon_hoc
                    WHERE th.ma_to_hop_mon = :ma_to_hop
                    AND th.trang_thai = 'HoatDong'
                    AND m.trang_thai_mon_hoc = 'HoatDong'
                    ORDER BY 
                        CASE WHEN m.loai_mon = 'Bắt buộc' THEN 1 ELSE 2 END,
                        m.ten_mon_hoc ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_to_hop' => $ma_to_hop_mon]);
            $monHoc = $stmt->fetchAll();

            // Phân loại
            $result = [
                'bat_buoc' => [],
                'tu_chon_khtn' => [],
                'tu_chon_khxh' => [],
                'tu_chon_cn_nt' => []
            ];

            foreach ($monHoc as $mon) {
                if ($mon['loai_mon'] === 'Bắt buộc') {
                    $result['bat_buoc'][] = $mon;
                } elseif (strpos($mon['loai_mon'], 'KHTN') !== false) {
                    $result['tu_chon_khtn'][] = $mon;
                } elseif (strpos($mon['loai_mon'], 'KHXH') !== false) {
                    $result['tu_chon_khxh'][] = $mon;
                } elseif (strpos($mon['loai_mon'], 'CN-NT') !== false) {
                    $result['tu_chon_cn_nt'][] = $mon;
                }
            }

            return $result;
        } catch (Exception $e) {
            error_log("Error getMonHocByToHop: " . $e->getMessage());
            return ['bat_buoc' => [], 'tu_chon_khtn' => [], 'tu_chon_khxh' => [], 'tu_chon_cn_nt' => []];
        }
    }

    /**
     * 3. Lấy danh sách lớp 10 sẵn sàng (sĩ số < 50) của 1 trường + tổ hợp môn
     * @param int $ma_truong
     * @param int $ma_to_hop_mon
     * @return array
     */
    public function getDanhSachLopKhoi10ByTruongVaToHop($ma_truong, $ma_to_hop_mon) {
        try {
            $sql = "SELECT 
                        lh.ma_lop,
                        lh.ten_lop,
                        lh.si_so,
                        lh.ma_to_hop_mon,
                        th.ten_to_hop,
                        nd.ho_ten as ten_gvcn
                    FROM lop_hoc lh
                    LEFT JOIN to_hop_mon th ON lh.ma_to_hop_mon = th.ma_to_hop_mon
                    LEFT JOIN nguoi_dung nd ON lh.ma_gvcn = nd.ma_nguoi_dung
                    WHERE lh.ma_truong = :ma_truong
                    AND lh.ma_to_hop_mon = :ma_to_hop_mon
                    AND lh.khoi = 10
                    AND lh.si_so < 50
                    AND lh.trang_thai_lop = 'HoatDong'
                    ORDER BY lh.ten_lop ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':ma_truong' => $ma_truong,
                ':ma_to_hop_mon' => $ma_to_hop_mon
            ]);

            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getDanhSachLopKhoi10ByTruongVaToHop: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 4. Lưu môn tự chọn của thí sinh (xóa cũ + insert mới)
     * @param int $ma_nguoi_dung
     * @param array $danh_sach_ma_mon [11, 12, 9, 15]
     * @return bool
     */
    public function saveChonMon($ma_nguoi_dung, $danh_sach_ma_mon) {
        try {
            $this->db->beginTransaction();

            // Xóa lựa chọn cũ
            $sqlDelete = "DELETE FROM chi_tiet_chon_mon WHERE ma_nguoi_dung = ?";
            $stmtDelete = $this->db->prepare($sqlDelete);
            $stmtDelete->execute([$ma_nguoi_dung]);

            // Insert lựa chọn mới
            $sqlInsert = "INSERT INTO chi_tiet_chon_mon (ma_nguoi_dung, ma_mon_hoc) VALUES (?, ?)";
            $stmtInsert = $this->db->prepare($sqlInsert);

            foreach ($danh_sach_ma_mon as $ma_mon) {
                $stmtInsert->execute([$ma_nguoi_dung, $ma_mon]);
            }

            $this->db->commit();
            error_log("✓ Saved chon_mon for user $ma_nguoi_dung with " . count($danh_sach_ma_mon) . " subjects");
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("✗ Error saveChonMon: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 5. Lấy môn tự chọn đã lưu của thí sinh
     * @param int $ma_nguoi_dung
     * @return array
     */
    public function getChonMonDaSave($ma_nguoi_dung) {
        try {
            $sql = "SELECT 
                        ct.ma_chi_tiet_chon_mon,
                        ct.ma_mon_hoc,
                        m.ten_mon_hoc,
                        m.loai_mon
                    FROM chi_tiet_chon_mon ct
                    JOIN mon_hoc m ON ct.ma_mon_hoc = m.ma_mon_hoc
                    WHERE ct.ma_nguoi_dung = :ma_nguoi_dung
                    ORDER BY ct.ngay_chon DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_nguoi_dung' => $ma_nguoi_dung]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getChonMonDaSave: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 6. Xác nhận nhập học: Insert vào phieu_dang_ky_nhap_hoc + Update sĩ số lớp
     * @param int $ma_nguoi_dung
     * @param int $ma_truong
     * @param int $ma_lop
     * @return array ['success' => bool, 'message' => string, 'ma_nhap_hoc' => int]
     */
    /**
     * 6. Xác nhận nhập học: Insert vào phieu_dang_ky_nhap_hoc + Update sĩ số lớp
     */
    // Thêm tham số $ma_to_hop_mon
    // public function xacNhanNhapHoc($ma_nguoi_dung, $ma_truong, $ma_lop = null, $ma_to_hop_mon = null) {
    //     try {
    //         $this->db->beginTransaction();

    //         // 1. Insert phieu_dang_ky_nhap_hoc (Đã thêm cột ma_to_hop_mon)
    //         $sqlInsert = "INSERT INTO phieu_dang_ky_nhap_hoc 
    //                       (ma_nguoi_dung, ma_truong, ma_lop, ma_to_hop_mon, ngay_nhap_hoc, tinh_trang_nhap_hoc) 
    //                       VALUES (?, ?, ?, ?, NOW(), 'Dang_nhap_hoc')";
            
    //         $stmtInsert = $this->db->prepare($sqlInsert);
    //         $stmtInsert->execute([$ma_nguoi_dung, $ma_truong, $ma_lop, $ma_to_hop_mon]); 
    //         $maNhapHoc = $this->db->lastInsertId();

    //         // 2. Update trạng thái xác nhận trong ket_qua_thi_tuyen_sinh
    //         // (Dùng subquery để lấy đúng ma_diem_thi)
    //         $sqlUpdateKQ = "UPDATE ket_qua_thi_tuyen_sinh 
    //                         SET trang_thai_xac_nhan = 'Xac_nhan_nhap_hoc', ngay_xac_nhan = NOW()
    //                         WHERE ma_diem_thi = (SELECT ma_diem_thi FROM diem_thi_tuyen_sinh WHERE ma_nguoi_dung = ? LIMIT 1) 
    //                         AND ma_truong_trung_tuyen = ?";
            
    //         $stmtUpdateKQ = $this->db->prepare($sqlUpdateKQ);
    //         $stmtUpdateKQ->execute([$ma_nguoi_dung, $ma_truong]);

    //         $this->db->commit();
    //         return [
    //             'success' => true,
    //             'message' => 'Xác nhận nhập học thành công! Nhà trường sẽ xếp lớp sau.',
    //             'ma_nhap_hoc' => $maNhapHoc
    //         ];
    //     } catch (Exception $e) {
    //         $this->db->rollBack();
    //         return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
    //     }
    // }
    public function xacNhanNhapHoc($ma_nguoi_dung, $ma_truong, $ma_lop = null, $ma_to_hop_mon = null) {
        try {
            $this->db->beginTransaction();

            // --- BƯỚC 1: XÓA PHIẾU CŨ (QUAN TRỌNG) ---
            // Để đảm bảo nếu học sinh bấm lại hoặc đăng ký lại thì phiếu cũ sẽ mất, chỉ giữ phiếu mới nhất
            $sqlDelete = "DELETE FROM phieu_dang_ky_nhap_hoc WHERE ma_nguoi_dung = ? AND ma_truong = ?";
            $stmtDelete = $this->db->prepare($sqlDelete);
            $stmtDelete->execute([$ma_nguoi_dung, $ma_truong]);

            // --- BƯỚC 2: TẠO PHIẾU MỚI ---
            $sqlInsert = "INSERT INTO phieu_dang_ky_nhap_hoc 
                          (ma_nguoi_dung, ma_truong, ma_lop, ma_to_hop_mon, ngay_nhap_hoc, tinh_trang_nhap_hoc) 
                          VALUES (?, ?, ?, ?, NOW(), 'Dang_nhap_hoc')";
            
            $stmtInsert = $this->db->prepare($sqlInsert);
            $stmtInsert->execute([$ma_nguoi_dung, $ma_truong, $ma_lop, $ma_to_hop_mon]); 
            $maNhapHoc = $this->db->lastInsertId();

            // --- BƯỚC 3: UPDATE TRẠNG THÁI KẾT QUẢ ---
            $sqlUpdateKQ = "UPDATE ket_qua_thi_tuyen_sinh 
                            SET trang_thai_xac_nhan = 'Xac_nhan_nhap_hoc', ngay_xac_nhan = NOW()
                            WHERE ma_diem_thi = (SELECT ma_diem_thi FROM diem_thi_tuyen_sinh WHERE ma_nguoi_dung = ? LIMIT 1) 
                            AND ma_truong_trung_tuyen = ?";
            $stmtUpdateKQ = $this->db->prepare($sqlUpdateKQ);
            $stmtUpdateKQ->execute([$ma_nguoi_dung, $ma_truong]);

            $this->db->commit();
            return [
                'success' => true,
                'message' => 'Xác nhận nhập học thành công! Nhà trường sẽ xếp lớp sau.',
                'ma_nhap_hoc' => $maNhapHoc
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    /**
     * 7. Từ chối nhập học: Update trạng thái từ chối
     * @param int $ma_nguoi_dung
     * @param int $ma_truong
     * @return bool
     */
    // public function tuChoiNhapHoc($ma_nguoi_dung, $ma_truong) {
    //     try {
    //         $sql = "UPDATE ket_qua_thi_tuyen_sinh 
    //                SET trang_thai_xac_nhan = 'Tu_choi_nhap_hoc', ngay_xac_nhan = NOW()
    //                WHERE ma_nguoi_dung = ? AND ma_truong_trung_tuyen = ?";
    //         $stmt = $this->db->prepare($sql);
    //         $result = $stmt->execute([$ma_nguoi_dung, $ma_truong]);

    //         error_log("✓ tuChoiNhapHoc: User $ma_nguoi_dung rejected school $ma_truong");
    //         return $result;
    //     } catch (Exception $e) {
    //         error_log("✗ Error tuChoiNhapHoc: " . $e->getMessage());
    //         return false;
    //     }
    // }
    /**
     * 7. Từ chối nhập học: Update trạng thái từ chối (ĐÃ SỬA LỖI SQL)
     * @param int $ma_nguoi_dung
     * @param int $ma_truong
     * @return bool
     */
    public function tuChoiNhapHoc($ma_nguoi_dung, $ma_truong) {
        try {
            $this->db->beginTransaction();

            // SỬA SQL: Dùng subquery để tìm ma_diem_thi từ ma_nguoi_dung
            $sql = "UPDATE ket_qua_thi_tuyen_sinh 
                    SET trang_thai_xac_nhan = 'Tu_choi_nhap_hoc', ngay_xac_nhan = NOW()
                    WHERE ma_diem_thi = (
                        SELECT ma_diem_thi 
                        FROM diem_thi_tuyen_sinh 
                        WHERE ma_nguoi_dung = ? 
                        LIMIT 1
                    ) 
                    AND ma_truong_trung_tuyen = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_nguoi_dung, $ma_truong]);
            
            // KIỂM TRA: Phải có ít nhất 1 dòng bị thay đổi thì mới return true
            $count = $stmt->rowCount();
            
            $this->db->commit();

            if ($count > 0) {
                return true; // Thành công thật
            } else {
                return false; // Không tìm thấy hồ sơ để update
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("✗ Error tuChoiNhapHoc: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 8. Lấy thông tin nhập học đã xác nhận của thí sinh
     * @param int $ma_nguoi_dung
     * @return array|null
     */
    public function getNhapHocInfo($ma_nguoi_dung) {
        try {
            $sql = "SELECT 
                        pnh.ma_nhap_hoc,
                        pnh.ma_nguoi_dung,
                        pnh.ma_truong,
                        pnh.ma_lop,
                        pnh.ngay_nhap_hoc,
                        pnh.tinh_trang_nhap_hoc,
                        tt.ten_truong,
                        lh.ten_lop,
                        thm.ten_to_hop,
                        nd.ho_ten as ten_gvcn
                    FROM phieu_dang_ky_nhap_hoc pnh
                    JOIN truong_thpt tt ON pnh.ma_truong = tt.ma_truong
                    JOIN lop_hoc lh ON pnh.ma_lop = lh.ma_lop
                    LEFT JOIN to_hop_mon thm ON lh.ma_to_hop_mon = thm.ma_to_hop_mon
                    LEFT JOIN nguoi_dung nd ON lh.ma_gvcn = nd.ma_nguoi_dung
                    WHERE pnh.ma_nguoi_dung = :ma_nguoi_dung
                    ORDER BY pnh.ngay_nhap_hoc DESC
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_nguoi_dung' => $ma_nguoi_dung]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getNhapHocInfo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 9. Validate chọn môn (phải 4 môn, mỗi nhóm ≥ 1)
     * @param array $danh_sach_ma_mon
     * (Đã xóa tham số $ma_to_hop_mon vì không dùng đến)
     * @return array ['valid' => bool, 'message' => string]
     */
    // public function validateChonMon($danh_sach_ma_mon) { // <-- SỬA DÒNG NÀY (Xóa tham số thứ 2)
    //     try {
    //         // Kiểm tra tổng 4 môn
    //         if (count($danh_sach_ma_mon) !== 4) {
    //             return ['valid' => false, 'message' => 'Phải chọn đúng 4 môn tự chọn!'];
    //         }

    //         // ... (Phần logic kiểm tra nhóm KHTN/KHXH/CNNT giữ nguyên không đổi) ...
    //         $sql = "SELECT ma_mon_hoc, loai_mon FROM mon_hoc WHERE ma_mon_hoc IN (" . implode(',', $danh_sach_ma_mon) . ")";
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->execute();
    //         $monChon = $stmt->fetchAll();

    //         $khtn = 0; $khxh = 0; $cnnt = 0;

    //         foreach ($monChon as $mon) {
    //             if (strpos($mon['loai_mon'], 'KHTN') !== false) $khtn++;
    //             elseif (strpos($mon['loai_mon'], 'KHXH') !== false) $khxh++;
    //             elseif (strpos($mon['loai_mon'], 'CN-NT') !== false) $cnnt++;
    //         }

    //         // Kiểm tra mỗi nhóm ≥ 1
    //         if ($khtn < 1 || $khxh < 1 || $cnnt < 1) {
    //             return ['valid' => false, 'message' => 'Mỗi nhóm phải chọn ít nhất 1 môn!'];
    //         }

    //         return ['valid' => true, 'message' => 'Lựa chọn môn hợp lệ!'];
    //     } catch (Exception $e) {
    //         error_log("Error validateChonMon: " . $e->getMessage());
    //         return ['valid' => false, 'message' => 'Lỗi kiểm tra: ' . $e->getMessage()];
    //     }
    // }
    /**
     * 9. Validate chọn môn (ĐÃ FIX: Bỏ tham số ma_to_hop_mon)
     */
    public function validateChonMon($danh_sach_ma_mon) {
        try {
            // Kiểm tra tổng 4 môn
            if (count($danh_sach_ma_mon) !== 4) {
                return ['valid' => false, 'message' => 'Phải chọn đúng 4 môn tự chọn!'];
            }

            // Lấy thông tin môn học để kiểm tra loại
            // Xử lý mảng an toàn cho SQL
            $ids = implode(',', array_map('intval', $danh_sach_ma_mon));
            
            $sql = "SELECT ma_mon_hoc, loai_mon FROM mon_hoc WHERE ma_mon_hoc IN ($ids)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $monChon = $stmt->fetchAll();

            $khtn = 0;
            $khxh = 0;
            $cnnt = 0;

            foreach ($monChon as $mon) {
                if (strpos($mon['loai_mon'], 'KHTN') !== false) {
                    $khtn++;
                } elseif (strpos($mon['loai_mon'], 'KHXH') !== false) {
                    $khxh++;
                } elseif (strpos($mon['loai_mon'], 'CN-NT') !== false) {
                    $cnnt++;
                }
            }

            // Kiểm tra mỗi nhóm ≥ 1
            if ($khtn < 1 || $khxh < 1 || $cnnt < 1) {
                return ['valid' => false, 'message' => 'Mỗi nhóm phải chọn ít nhất 1 môn!'];
            }

            return ['valid' => true, 'message' => 'Lựa chọn môn hợp lệ!'];
        } catch (Exception $e) {
            error_log("Error validateChonMon: " . $e->getMessage());
            return ['valid' => false, 'message' => 'Lỗi kiểm tra: ' . $e->getMessage()];
        }
    }


    /**
     * Tìm mã tổ hợp dựa trên danh sách môn học sinh chọn
     * @param int $ma_truong
     * @param array $mon_chon (Mảng 4 mã môn)
     * @return int|null
     */
    public function findToHopByMonChon($ma_truong, $mon_chon) {
        try {
            // Logic: Tìm tổ hợp nào trong trường đó chứa ĐỦ tất cả các môn đã chọn
            // (Giả sử bảng to_hop_mon_mon_hoc chứa định nghĩa các môn của tổ hợp)
            
            // Lấy tất cả tổ hợp của trường
            $sql = "SELECT DISTINCT th.ma_to_hop_mon 
                    FROM lop_hoc lh
                    JOIN to_hop_mon th ON lh.ma_to_hop_mon = th.ma_to_hop_mon
                    WHERE lh.ma_truong = :ma_truong AND lh.khoi = 10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_truong' => $ma_truong]);
            $to_hops = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($to_hops as $ma_to_hop) {
                // Lấy các môn của tổ hợp này
                $sqlMon = "SELECT ma_mon_hoc FROM to_hop_mon_mon_hoc WHERE ma_to_hop_mon = :ma_to_hop";
                $stmtMon = $this->db->prepare($sqlMon);
                $stmtMon->execute([':ma_to_hop' => $ma_to_hop]);
                $mon_to_hop = $stmtMon->fetchAll(PDO::FETCH_COLUMN);

                // Kiểm tra xem 4 môn chọn có nằm trong tổ hợp này không
                // (Giao của 2 mảng phải đủ số lượng môn chọn)
                $intersection = array_intersect($mon_chon, $mon_to_hop);
                
                // Nếu số lượng môn trùng khớp = số lượng môn chọn (4) -> ĐÚNG TỔ HỢP ĐÓ
                if (count($intersection) == count($mon_chon)) {
                    return $ma_to_hop;
                }
            }
            
            return null; // Không tìm thấy
        } catch (Exception $e) {
            error_log("Error findToHop: " . $e->getMessage());
            return null;
        }
    }

    
}
?>
