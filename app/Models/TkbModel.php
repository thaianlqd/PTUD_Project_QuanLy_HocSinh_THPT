<?php
/**
 * TkbModel: Xử lý logic nghiệp vụ cho chức năng Xếp Thời Khóa Biểu
 * (ĐÃ NÂNG CẤP ĐỂ HỖ TRỢ HỌC KỲ)
 */
class TkbModel {
    private $db;
    private $nam_hoc_hien_tai = 1; // Giả sử ID năm học hiện tại là 1

    public function __construct() {
        // Kết nối CSDL (Đã dùng port 3307 từ dump)
        try {
            $dsn = 'mysql:host=127.0.0.1;port=3307;dbname=thpt_manager;charset=utf8mb4';
            $this->db = new PDO($dsn, 'root', ''); // Sửa username/password nếu cần
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('DB Connection failed: ' . $e->getMessage());
            $this->db = null;
            die("Không thể kết nối CSDL: " . $e->getMessage());
        }
    }

    /**
     * Lấy danh sách lớp học để hiển thị ở bước 1 (Giữ nguyên)
     */
    public function getDanhSachLop() {
        if ($this->db === null) return [];

        // Query này đếm tổng số tiết đã xếp (cả 2 học kỳ) để
        // hiển thị tổng quan trên trang danh sách lớp
        $sql = "SELECT
                    l.ma_lop,
                    l.ten_lop,
                    l.si_so,
                    p.ten_phong AS ten_phong_chinh,
                    (SELECT COUNT(*) FROM tkb_chi_tiet t WHERE t.ma_lop = l.ma_lop) AS so_tiet_da_xep,
                    COALESCE((SELECT SUM(bpc_inner.so_tiet_tuan)
                              FROM bang_phan_cong bpc_inner
                              WHERE bpc_inner.ma_lop = l.ma_lop), 0) AS tong_tiet_ke_hoach
                FROM lop_hoc l
                LEFT JOIN phong_hoc p ON l.ma_phong_hoc_chinh = p.ma_phong
                WHERE l.ma_nam_hoc = ? AND l.trang_thai_lop = 'HoatDong'
                ORDER BY l.khoi, l.ten_lop";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->nam_hoc_hien_tai]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachLop: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy toàn bộ chi tiết TKB của 1 lớp (THEO HỌC KỲ)
     * <-- ĐÃ SỬA: Thêm $ma_hoc_ky
     */
    public function getChiTietTkbLop($ma_lop, $ma_hoc_ky) {
        if ($this->db === null) return [];

        $sql = "SELECT
                    t.thu,
                    t.tiet,
                    m.ten_mon_hoc,
                    nd.ho_ten AS ten_giao_vien,
                    COALESCE(ph_tkb.ten_phong, ph_mon.ten_phong, ph_lop.ten_phong) AS ten_phong_hoc,
                    COALESCE(t.ma_phong_hoc, ph_mon.ma_phong, l.ma_phong_hoc_chinh) AS ma_phong_hoc_thuc_te,
                    bpc.ma_phan_cong
                FROM tkb_chi_tiet t
                JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
                JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
                JOIN giao_vien gv ON bpc.ma_giao_vien = gv.ma_giao_vien
                JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
                JOIN lop_hoc l ON t.ma_lop = l.ma_lop
                LEFT JOIN phong_hoc ph_tkb ON t.ma_phong_hoc = ph_tkb.ma_phong
                LEFT JOIN phong_hoc ph_mon ON m.yeu_cau_phong_dac_biet <> 'None' AND ph_mon.loai_phong = m.yeu_cau_phong_dac_biet
                LEFT JOIN phong_hoc ph_lop ON l.ma_phong_hoc_chinh = ph_lop.ma_phong
                WHERE t.ma_lop = ? AND t.ma_hoc_ky = ?"; // <-- ĐÃ SỬA: Thêm ma_hoc_ky

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_lop, $ma_hoc_ky]); // <-- ĐÃ SỬA: Thêm ma_hoc_ky

            $tkb_data = [];
            foreach ($stmt->fetchAll() as $row) {
                $tkb_data[$row['thu']][$row['tiet']] = [
                    'mon' => $row['ten_mon_hoc'],
                    'gv' => $row['ten_giao_vien'],
                    'phong' => $row['ten_phong_hoc'],
                    'ma_phong' => $row['ma_phong_hoc_thuc_te'],
                    'ma_phan_cong' => $row['ma_phan_cong']
                ];
            }
            return $tkb_data;
        } catch (PDOException $e) {
            error_log("Lỗi getChiTietTkbLop: " . $e->getMessage());
            return [];
        }
    }

   /**
     * Lấy dữ liệu cho sidebar ràng buộc (THEO HỌC KỲ)
     * <-- ĐÃ SỬA: Thêm $ma_hoc_ky
     */
    public function getRangBuocLop($ma_lop, $ma_hoc_ky) {
        if ($this->db === null) return [
            'ten_lop' => 'N/A', 'phong_chinh' => 'N/A', 'gvcn' => 'N/A',
            'tong_tiet_da_xep' => 0, 'tong_tiet_ke_hoach' => 0,
            'mon_hoc' => []
        ];

        try {
            // 1. Thông tin chung (Lớp, Phòng, GVCN) - Giữ nguyên
            $sql_info = "SELECT
                            l.ten_lop,
                            p.ten_phong AS ten_phong_chinh,
                            nd.ho_ten AS ten_gvcn
                         FROM lop_hoc l
                         LEFT JOIN phong_hoc p ON l.ma_phong_hoc_chinh = p.ma_phong
                         LEFT JOIN bang_phan_cong bpc_cn ON bpc_cn.ma_lop = l.ma_lop AND bpc_cn.ma_mon_hoc IN (18, 19)
                         LEFT JOIN giao_vien gv ON bpc_cn.ma_giao_vien = gv.ma_giao_vien
                         LEFT JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
                         WHERE l.ma_lop = ?
                         LIMIT 1";
            $stmt_info = $this->db->prepare($sql_info);
            $stmt_info->execute([$ma_lop]);
            $info = $stmt_info->fetch();

            // 2. Lấy TOÀN BỘ phân công cho lớp (Giữ nguyên)
            // (Giả định rằng phân công dùng chung cho cả 2 học kỳ)
            $sql_phan_cong = "SELECT
                                bpc.ma_phan_cong,
                                m.ten_mon_hoc,
                                bpc.so_tiet_tuan
                            FROM bang_phan_cong bpc
                            JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
                            WHERE bpc.ma_lop = ?";
            $stmt_phan_cong = $this->db->prepare($sql_phan_cong);
            $stmt_phan_cong->execute([$ma_lop]);
            $all_phan_cong = $stmt_phan_cong->fetchAll(PDO::FETCH_ASSOC);

            // 3. Lấy số tiết đã xếp cho lớp (THEO HỌC KỲ)
            // <-- ĐÃ SỬA: Thêm ma_hoc_ky
            $sql_da_xep = "SELECT ma_phan_cong, COUNT(*) as count
                           FROM tkb_chi_tiet
                           WHERE ma_lop = ? AND ma_hoc_ky = ?
                           GROUP BY ma_phan_cong";
            $stmt_da_xep = $this->db->prepare($sql_da_xep);
            $stmt_da_xep->execute([$ma_lop, $ma_hoc_ky]); // <-- ĐÃ SỬA
            $da_xep_map = $stmt_da_xep->fetchAll(PDO::FETCH_KEY_PAIR); // [ma_phan_cong => count]

            // 4. Dùng PHP để nhóm và tổng hợp lại (Giữ nguyên)
            $mon_hoc_aggregated = [];
            $tong_da_xep_total = 0;
            $tong_ke_hoach_total = 0;

            $phan_cong_grouped_by_mon = [];
            foreach ($all_phan_cong as $pc) {
                // ... (code nhóm thủ công giữ nguyên) ...
                $ten_mon = $pc['ten_mon_hoc'];
                if (!isset($phan_cong_grouped_by_mon[$ten_mon])) {
                    $phan_cong_grouped_by_mon[$ten_mon] = [];
                }
                $phan_cong_grouped_by_mon[$ten_mon][] = $pc;
            }

            foreach ($phan_cong_grouped_by_mon as $ten_mon => $phan_congs_cua_mon) {
                // ... (code duyệt và tính toán giữ nguyên) ...
                $da_xep_mon = 0;
                $ke_hoach_mon = 0; 
                $ma_phan_cong_list_mon = [];

                if (!empty($phan_congs_cua_mon)) {
                    $ke_hoach_mon = (int)$phan_congs_cua_mon[0]['so_tiet_tuan'];
                }

                foreach($phan_congs_cua_mon as $pc) {
                    if (!isset($pc['ma_phan_cong'])) {
                        error_log("Thiếu key 'ma_phan_cong' trong dữ liệu phân công của môn: $ten_mon");
                        continue; 
                    }
                    $ma_phan_cong = $pc['ma_phan_cong'];
                    $da_xep_pc = $da_xep_map[$ma_phan_cong] ?? 0;

                    $da_xep_mon += $da_xep_pc;
                    $ma_phan_cong_list_mon[] = $ma_phan_cong;
                }

                 $mon_hoc_aggregated[$ten_mon] = [
                    'da_xep' => $da_xep_mon,
                    'ke_hoach' => $ke_hoach_mon,
                    'ma_phan_cong_list' => $ma_phan_cong_list_mon
                 ];

                 $tong_da_xep_total += $da_xep_mon;
                 $tong_ke_hoach_total += $ke_hoach_mon;
            }

            return [
                'ten_lop' => $info['ten_lop'] ?? 'Không tìm thấy',
                'phong_chinh' => $info['ten_phong_chinh'] ?? 'Chưa gán',
                'gvcn' => $info['ten_gvcn'] ?? 'Chưa gán',
                'tong_tiet_da_xep' => $tong_da_xep_total,
                'tong_tiet_ke_hoach' => $tong_ke_hoach_total,
                'mon_hoc' => $mon_hoc_aggregated
            ];
        } catch (PDOException $e) {
            error_log("Lỗi getRangBuocLop: " . $e->getMessage());
            return [
                'ten_lop' => 'Lỗi CSDL', 'phong_chinh' => 'Lỗi', 'gvcn' => 'Lỗi',
                'tong_tiet_da_xep' => 0, 'tong_tiet_ke_hoach' => 0,
                'mon_hoc' => []
            ];
        }
    }


    /**
     * Lấy danh sách môn học + giáo viên (Giữ nguyên)
     */
    public function getDanhSachMonHocGV($ma_lop) {
        // (Hàm này giữ nguyên, vì nó lấy từ bang_phan_cong)
        if ($this->db === null) return [];

        $sql = "SELECT
                    bpc.ma_phan_cong,
                    m.ten_mon_hoc,
                    nd.ho_ten AS ten_giao_vien,
                    bpc.ma_giao_vien,
                    m.yeu_cau_phong_dac_biet,
                    (SELECT ph.ma_phong
                     FROM phong_hoc ph
                     WHERE ph.loai_phong = m.yeu_cau_phong_dac_biet
                     LIMIT 1) AS ma_phong_dac_biet
                FROM bang_phan_cong bpc
                JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
                JOIN giao_vien gv ON bpc.ma_giao_vien = gv.ma_giao_vien
                JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
                WHERE bpc.ma_lop = ?
                AND m.ma_mon_hoc NOT IN (18, 19)
                ORDER BY m.ten_mon_hoc, nd.ho_ten";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_lop]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachMonHocGV: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Kiểm tra xem 1 GIÁO VIÊN bị bận vào (Thứ, Tiết) nào (Giữ nguyên)
     * (Hàm này dùng cho modal, nó check TẤT CẢ học kỳ để cảnh báo)
     */
    public function getGVBan($ma_giao_vien) {
        if ($this->db === null) return [];
        $sql = "SELECT t.thu, t.tiet
                FROM tkb_chi_tiet t
                JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
                WHERE bpc.ma_giao_vien = ?";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_giao_vien]);
            $ban = [];
            foreach ($stmt->fetchAll() as $row) {
                $ban[$row['thu']][$row['tiet']] = true;
            }
            return $ban;
        } catch (PDOException $e) {
            error_log("Lỗi getGVBan: " . $e->getMessage()); return [];
        }
    }

    /**
     * Kiểm tra xem 1 PHÒNG HỌC bị bận vào (Thứ, Tiết) nào (Giữ nguyên)
     * (Hàm này dùng cho modal, nó check TẤT CẢ học kỳ để cảnh báo)
     */
    public function getPhongBan($ma_phong_hoc) {
        if ($this->db === null || $ma_phong_hoc === null || $ma_phong_hoc == '') return [];
        $sql = "SELECT thu, tiet FROM tkb_chi_tiet WHERE ma_phong_hoc = ?";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_phong_hoc]);
            $ban = [];
            foreach ($stmt->fetchAll() as $row) {
                $ban[$row['thu']][$row['tiet']] = true;
            }
            return $ban;
        } catch (PDOException $e) {
            error_log("Lỗi getPhongBan: " . $e->getMessage()); return [];
        }
    }

    /**
     * Lấy ID phòng học chính của lớp (Giữ nguyên)
     */
    public function getPhongHocChinhID($ma_lop) {
        if ($this->db === null) return null;
        try {
            $stmt = $this->db->prepare("SELECT ma_phong_hoc_chinh FROM lop_hoc WHERE ma_lop = ?");
            $stmt->execute([$ma_lop]);
            $result = $stmt->fetchColumn();
             return ($result === false || $result == 0) ? null : (int)$result;
        } catch (PDOException $e) {
            error_log("Lỗi getPhongHocChinhID: " . $e->getMessage()); return null;
        }
    }

    /**
     * Tự động xác định phòng học cho một tiết (Giữ nguyên)
     */
    private function xacDinhPhongHoc($ma_phan_cong, $ma_lop) {
        // (Hàm này giữ nguyên, không cần thay đổi)
        if ($this->db === null) return $this->getPhongHocChinhID($ma_lop);

        try {
            $sql = "SELECT m.yeu_cau_phong_dac_biet
                    FROM bang_phan_cong bpc
                    JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
                    WHERE bpc.ma_phan_cong = ?";
            $stmt_mon = $this->db->prepare($sql);
            $stmt_mon->execute([$ma_phan_cong]);
            $yeu_cau = $stmt_mon->fetchColumn();

            if ($yeu_cau && $yeu_cau !== 'None') {
                $sql_phong_dt = "SELECT ma_phong
                                 FROM phong_hoc
                                 WHERE loai_phong = ? AND trang_thai_phong = 'HoatDong'
                                 LIMIT 1";
                $stmt_phong_dt = $this->db->prepare($sql_phong_dt);
                $stmt_phong_dt->execute([$yeu_cau]);
                $ma_phong_dac_thu = $stmt_phong_dt->fetchColumn();

                if ($ma_phong_dac_thu) {
                    return (int)$ma_phong_dac_thu;
                }
            }
            return $this->getPhongHocChinhID($ma_lop);
        } catch (PDOException $e) {
            error_log("Lỗi xacDinhPhongHoc: " . $e->getMessage());
            return $this->getPhongHocChinhID($ma_lop);
        }
    }


    /**
     * Lưu 1 tiết học (THEO HỌC KỲ)
     * <-- ĐÃ SỬA: Thêm $ma_hoc_ky
     */
    public function luuTietHoc($ma_lop, $ma_hoc_ky, $thu, $tiet, $ma_phan_cong) {
        if ($this->db === null) return false;

        $ma_phong_hoc = $this->xacDinhPhongHoc($ma_phan_cong, $ma_lop);

        // <-- ĐÃ SỬA: Thêm ma_hoc_ky vào INSERT
        $sql = "INSERT INTO tkb_chi_tiet (ma_lop, ma_hoc_ky, thu, tiet, ma_phan_cong, ma_phong_hoc)
                VALUES (:ma_lop, :ma_hoc_ky, :thu, :tiet, :ma_phan_cong, :ma_phong_hoc)
                ON DUPLICATE KEY UPDATE
                ma_phan_cong = VALUES(ma_phan_cong),
                ma_phong_hoc = VALUES(ma_phong_hoc)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ma_lop', $ma_lop, PDO::PARAM_INT);
            $stmt->bindParam(':ma_hoc_ky', $ma_hoc_ky, PDO::PARAM_INT); // <-- ĐÃ SỬA
            $stmt->bindParam(':thu', $thu, PDO::PARAM_INT);
            $stmt->bindParam(':tiet', $tiet, PDO::PARAM_INT);
            $stmt->bindParam(':ma_phan_cong', $ma_phan_cong, PDO::PARAM_INT);
            
            if ($ma_phong_hoc === null) {
                $stmt->bindValue(':ma_phong_hoc', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':ma_phong_hoc', $ma_phong_hoc, PDO::PARAM_INT);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Lỗi luuTietHoc: " . $e->getMessage());
            return false;
        }
    }

     /**
     * Kiểm tra các ràng buộc (THEO HỌC KỲ)
     * <-- ĐÃ SỬA: Thêm $ma_hoc_ky và cập nhật các câu SQL
     */
    public function kiemTraRangBuoc($ma_lop, $ma_hoc_ky, $thu, $tiet, $ma_phan_cong, $ma_tkb_chi_tiet_dang_sua = null) {
        if ($this->db === null) return "Lỗi kết nối CSDL.";

        try {
            // 1. Lấy thông tin phân công (Giữ nguyên)
            $sql_pc = "SELECT bpc.ma_giao_vien, bpc.ma_mon_hoc, bpc.so_tiet_tuan, m.ten_mon_hoc
                       FROM bang_phan_cong bpc
                       JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
                       WHERE bpc.ma_phan_cong = ?";
            $stmt_pc = $this->db->prepare($sql_pc);
            $stmt_pc->execute([$ma_phan_cong]);
            $phan_cong_info = $stmt_pc->fetch();

            if (!$phan_cong_info) return "Không tìm thấy thông tin phân công.";

            $ma_giao_vien = $phan_cong_info['ma_giao_vien'];
            $ma_mon_hoc = $phan_cong_info['ma_mon_hoc'];
            $ke_hoach_mon = (int)$phan_cong_info['so_tiet_tuan'];
            $ten_mon_hoc = $phan_cong_info['ten_mon_hoc'];

            // 2. Xác định phòng học (Giữ nguyên)
            $ma_phong_hoc = $this->xacDinhPhongHoc($ma_phan_cong, $ma_lop);

            // 3. Kiểm tra trùng lịch GIÁO VIÊN (THEO HỌC KỲ)
            // <-- ĐÃ SỬA: Thêm ma_hoc_ky
            $sql_gv_ban = "SELECT COUNT(*) FROM tkb_chi_tiet t
                           JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
                           WHERE bpc.ma_giao_vien = ? AND t.thu = ? AND t.tiet = ? AND t.ma_hoc_ky = ?
                           AND t.ma_tkb_chi_tiet <> ?";
            $stmt_gv_ban = $this->db->prepare($sql_gv_ban);
            $stmt_gv_ban->execute([$ma_giao_vien, $thu, $tiet, $ma_hoc_ky, $ma_tkb_chi_tiet_dang_sua ?? -1]);
            if ($stmt_gv_ban->fetchColumn() > 0) {
                return "Giáo viên đã có lịch dạy vào Thứ $thu, Tiết $tiet (trong học kỳ này).";
            }

            // 4. Kiểm tra trùng lịch PHÒNG HỌC (THEO HỌC KỲ)
            if ($ma_phong_hoc !== null) {
                // <-- ĐÃ SỬA: Thêm ma_hoc_ky
                $sql_phong_ban = "SELECT COUNT(*) FROM tkb_chi_tiet
                                  WHERE ma_phong_hoc = ? AND thu = ? AND tiet = ? AND ma_hoc_ky = ?
                                  AND ma_tkb_chi_tiet <> ?";
                $stmt_phong_ban = $this->db->prepare($sql_phong_ban);
                $stmt_phong_ban->execute([$ma_phong_hoc, $thu, $tiet, $ma_hoc_ky, $ma_tkb_chi_tiet_dang_sua ?? -1]);
                if ($stmt_phong_ban->fetchColumn() > 0) {
                    $stmt_ten_phong = $this->db->prepare("SELECT ten_phong FROM phong_hoc WHERE ma_phong = ?");
                    $stmt_ten_phong->execute([$ma_phong_hoc]);
                    $ten_phong = $stmt_ten_phong->fetchColumn();
                    return "Phòng học '$ten_phong' đã có lớp khác sử dụng vào Thứ $thu, Tiết $tiet (trong học kỳ này).";
                }
            }

            // 5. Kiểm tra vượt quá số tiết/môn/tuần (THEO HỌC KỲ)
            // <-- ĐÃ SỬA: Thêm ma_hoc_ky
            $sql_da_xep_mon = "SELECT COUNT(*) FROM tkb_chi_tiet
                               WHERE ma_lop = ? AND ma_phan_cong = ? AND ma_hoc_ky = ?
                               AND ma_tkb_chi_tiet <> ?";
            $stmt_da_xep_mon = $this->db->prepare($sql_da_xep_mon);
            $stmt_da_xep_mon->execute([$ma_lop, $ma_phan_cong, $ma_hoc_ky, $ma_tkb_chi_tiet_dang_sua ?? -1]);
            $so_tiet_da_xep_mon = (int)$stmt_da_xep_mon->fetchColumn();

            if (($so_tiet_da_xep_mon + 1) > $ke_hoach_mon) {
                 return "Vượt quá số tiết kế hoạch cho môn '$ten_mon_hoc' (Đã xếp $so_tiet_da_xep_mon / $ke_hoach_mon).";
            }

            // 6. Kiểm tra vượt quá tổng số tiết/tuần (THEO HỌC KỲ)
            $gioi_han_tuan = 45; 
             // <-- ĐÃ SỬA: Thêm ma_hoc_ky
             $sql_tong_tiet_tuan = "SELECT COUNT(*) FROM tkb_chi_tiet WHERE ma_lop = ? AND ma_hoc_ky = ? AND ma_tkb_chi_tiet <> ?";
             $stmt_tong_tiet_tuan = $this->db->prepare($sql_tong_tiet_tuan);
             $stmt_tong_tiet_tuan->execute([$ma_lop, $ma_hoc_ky, $ma_tkb_chi_tiet_dang_sua ?? -1]);
             $tong_tiet_da_xep_tuan = (int)$stmt_tong_tiet_tuan->fetchColumn();

             if (($tong_tiet_da_xep_tuan + 1) > $gioi_han_tuan) {
                  return "Vượt quá tổng số tiết tối đa trong tuần (Giới hạn: $gioi_han_tuan tiết).";
             }


            return true;

        } catch (PDOException $e) {
            error_log("Lỗi kiemTraRangBuoc: " . $e->getMessage());
            return "Lỗi hệ thống khi kiểm tra ràng buộc: " . $e->getMessage();
        }
    }


    /**
     * Xóa 1 tiết học (THEO HỌC KỲ)
     * <-- ĐÃ SỬA: Thêm $ma_hoc_ky
     */
    public function xoaTietHoc($ma_lop, $ma_hoc_ky, $thu, $tiet) {
        if ($this->db === null) return false;

        $sql = "DELETE FROM tkb_chi_tiet WHERE ma_lop = ? AND ma_hoc_ky = ? AND thu = ? AND tiet = ?";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$ma_lop, $ma_hoc_ky, $thu, $tiet]);
        } catch (PDOException $e) {
            error_log("Lỗi xoaTietHoc: " . $e->getMessage());
            return false;
        }
    }

    /**
     * HÀM MỚI: Tìm học kỳ dựa trên một ngày cụ thể
     */
    public function getHocKyTuNgay($ngay_sql) {
        if ($this->db === null) return null;
        
        // Dùng bảng hoc_ky của bạn
        $sql = "SELECT ma_hoc_ky, ten_hoc_ky 
                FROM hoc_ky
                WHERE :ngay BETWEEN ngay_bat_dau AND ngay_ket_thuc
                LIMIT 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ngay', $ngay_sql);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result ? $result : null; // Trả về null nếu không tìm thấy (VD: Hè)
        } catch (PDOException $e) {
            error_log("Lỗi getHocKyTuNgay: " . $e->getMessage());
            return null;
        }
    }

    /**
     * HÀM MỚI: Lấy tên lớp (dùng khi nghỉ hè)
     */
    public function getTenLop($ma_lop) {
        if ($this->db === null) return 'N/A';
        try {
            $stmt = $this->db->prepare("SELECT ten_lop FROM lop_hoc WHERE ma_lop = ?");
            $stmt->execute([$ma_lop]);
            $result = $stmt->fetchColumn();
            return $result ? $result : 'N/A'; // Trả về N/A nếu không tìm thấy
        } catch (PDOException $e) {
            return 'Lỗi';
        }
    }
}
?>