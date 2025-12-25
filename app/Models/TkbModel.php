<?php
/**
 * TkbModel: Xử lý logic nghiệp vụ cho chức năng Xếp Thời Khóa Biểu
 * (ĐÃ NÂNG CẤP ĐỂ HỖ TRỢ HỌC KỲ)
 */
class TkbModel {
    private $db;
    private $nam_hoc_hien_tai = 1; // Giả sử ID năm học hiện tại là 1

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
     * Lấy danh sách lớp học để hiển thị ở bước 1 (Giữ nguyên)
     */
    /**
     * Lấy danh sách lớp học (ĐÃ SỬA: Lọc theo trường)
     */
    public function getDanhSachLop($school_id = null) {
        if ($this->db === null) return [];

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
                WHERE l.ma_nam_hoc = ? AND l.trang_thai_lop = 'HoatDong'";

        // --- THÊM ĐIỀU KIỆN LỌC TRƯỜNG ---
        $params = [$this->nam_hoc_hien_tai];
        
        if ($school_id) {
            $sql .= " AND l.ma_truong = ?";
            $params[] = $school_id;
        }
        // ----------------------------------

        $sql .= " ORDER BY l.khoi, l.ten_lop";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
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
                    t.thu, t.tiet,
                    m.ten_mon_hoc,
                    nd.ho_ten AS ten_giao_vien,
                    COALESCE(ph_tkb.ten_phong, ph_mon.ten_phong, ph_lop.ten_phong) AS ten_phong_hoc,
                    COALESCE(t.ma_phong_hoc, ph_mon.ma_phong, l.ma_phong_hoc_chinh) AS ma_phong_hoc_thuc_te,
                    bpc.ma_phan_cong,
                    t.loai_tiet,
                    t.ghi_chu
                FROM tkb_chi_tiet t
                LEFT JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
                LEFT JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
                LEFT JOIN giao_vien gv ON bpc.ma_giao_vien = gv.ma_giao_vien
                LEFT JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
                JOIN lop_hoc l ON t.ma_lop = l.ma_lop
                LEFT JOIN phong_hoc ph_tkb ON t.ma_phong_hoc = ph_tkb.ma_phong
                LEFT JOIN phong_hoc ph_mon ON m.yeu_cau_phong_dac_biet <> 'None' AND ph_mon.loai_phong = m.yeu_cau_phong_dac_biet
                LEFT JOIN phong_hoc ph_lop ON l.ma_phong_hoc_chinh = ph_lop.ma_phong
                WHERE t.ma_lop = ? AND t.ma_hoc_ky = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_lop, $ma_hoc_ky]);
        $tkb_data = [];
        foreach ($stmt->fetchAll() as $row) {
            $thu = (int)$row['thu']; $tiet = (int)$row['tiet'];
            $tkb_data[$thu][$tiet] = [
                'mon' => $row['ten_mon_hoc'],
                'gv' => $row['ten_giao_vien'],
                'phong' => $row['ten_phong_hoc'],
                'ma_phong' => $row['ma_phong_hoc_thuc_te'],
                'ma_phan_cong' => $row['ma_phan_cong'],
                'loai_tiet' => $row['loai_tiet'] ?: 'hoc',
                'ghi_chu' => $row['ghi_chu'] ?: ''
            ];
        }
        return $tkb_data;
    }


   /**
     * Lấy dữ liệu cho sidebar ràng buộc (THEO HỌC KỲ)
     * <-- ĐÃ SỬA: Thêm $ma_hoc_ky
     */
    // public function getRangBuocLop($ma_lop, $ma_hoc_ky) {
    //     if ($this->db === null) return [
    //         'ten_lop' => 'N/A', 'phong_chinh' => 'N/A', 'gvcn' => 'N/A',
    //         'tong_tiet_da_xep' => 0, 'tong_tiet_ke_hoach' => 0,
    //         'mon_hoc' => []
    //     ];

    //     try {
    //         // 1. Thông tin chung
    //         $sql_info = "SELECT l.ten_lop, p.ten_phong AS ten_phong_chinh, nd.ho_ten AS ten_gvcn
    //                     FROM lop_hoc l
    //                     LEFT JOIN phong_hoc p ON l.ma_phong_hoc_chinh = p.ma_phong
    //                     LEFT JOIN bang_phan_cong bpc_cn ON bpc_cn.ma_lop = l.ma_lop AND bpc_cn.ma_mon_hoc IN (18, 19)
    //                     LEFT JOIN giao_vien gv ON bpc_cn.ma_giao_vien = gv.ma_giao_vien
    //                     LEFT JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
    //                     WHERE l.ma_lop = ? LIMIT 1";
    //         $stmt_info = $this->db->prepare($sql_info);
    //         $stmt_info->execute([$ma_lop]);
    //         $info = $stmt_info->fetch();

    //         // 2. Tổng số tiết ĐÃ XẾP của cả lớp (Bất kể môn gì, loại gì)
    //         // Đếm tất cả các ô đã được lấp đầy trong bảng tkb_chi_tiet
    //         $sql_count_all = "SELECT COUNT(*) FROM tkb_chi_tiet WHERE ma_lop = ? AND ma_hoc_ky = ?";
    //         $stmt_count = $this->db->prepare($sql_count_all);
    //         $stmt_count->execute([$ma_lop, $ma_hoc_ky]);
    //         $tong_da_xep_total = (int)$stmt_count->fetchColumn(); 

    //         // 3. Lấy KẾ HOẠCH phân công (Định mức)
    //         $sql_phan_cong = "SELECT bpc.ma_phan_cong, m.ten_mon_hoc, bpc.so_tiet_tuan
    //                         FROM bang_phan_cong bpc
    //                         JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
    //                         WHERE bpc.ma_lop = ?";
    //         $stmt_phan_cong = $this->db->prepare($sql_phan_cong);
    //         $stmt_phan_cong->execute([$ma_lop]);
    //         $all_phan_cong = $stmt_phan_cong->fetchAll(PDO::FETCH_ASSOC);

    //         // 4. [QUAN TRỌNG] Đếm số tiết theo từng Môn (Phân công)
    //         // Logic: Group by ID phân công. Bất kể là 'hoc', 'thi', 'tam_nghi', cứ trùng ID phân công là đếm.
    //         $sql_da_xep = "SELECT ma_phan_cong, COUNT(*) as count
    //                     FROM tkb_chi_tiet
    //                     WHERE ma_lop = ? AND ma_hoc_ky = ? AND ma_phan_cong IS NOT NULL
    //                     GROUP BY ma_phan_cong";
    //         $stmt_da_xep = $this->db->prepare($sql_da_xep);
    //         $stmt_da_xep->execute([$ma_lop, $ma_hoc_ky]);
    //         $da_xep_map = $stmt_da_xep->fetchAll(PDO::FETCH_KEY_PAIR);

    //         // 5. Ghép dữ liệu để trả về View
    //         $mon_hoc_aggregated = [];
    //         $tong_ke_hoach_total = 0;

    //         // Nhóm phân công theo tên môn (đề phòng 1 môn có 2 GV dạy)
    //         $phan_cong_grouped_by_mon = [];
    //         foreach ($all_phan_cong as $pc) {
    //             $phan_cong_grouped_by_mon[$pc['ten_mon_hoc']][] = $pc;
    //         }

    //         foreach ($phan_cong_grouped_by_mon as $ten_mon => $phan_congs_cua_mon) {
    //             $da_xep_mon = 0;
    //             $ke_hoach_mon = 0;
    //             $ma_phan_cong_list_mon = [];

    //             if (!empty($phan_congs_cua_mon)) {
    //                 // Lấy định mức của phân công đầu tiên (thường là chuẩn)
    //                 $ke_hoach_mon = (int)$phan_congs_cua_mon[0]['so_tiet_tuan'];
    //             }

    //             foreach ($phan_congs_cua_mon as $pc) {
    //                 $ma_phan_cong = $pc['ma_phan_cong'];
    //                 // Lấy số lượng đã đếm được ở bước 4
    //                 $da_xep_pc = $da_xep_map[$ma_phan_cong] ?? 0;
    //                 $da_xep_mon += $da_xep_pc;
    //                 $ma_phan_cong_list_mon[] = $ma_phan_cong;
    //             }

    //             $mon_hoc_aggregated[$ten_mon] = [
    //                 'da_xep' => $da_xep_mon,
    //                 'ke_hoach' => $ke_hoach_mon,
    //                 'ma_phan_cong_list' => $ma_phan_cong_list_mon
    //             ];

    //             $tong_ke_hoach_total += $ke_hoach_mon;
    //         }

    //         return [
    //             'ten_lop' => $info['ten_lop'] ?? 'Không tìm thấy',
    //             'phong_chinh' => $info['ten_phong_chinh'] ?? 'Chưa gán',
    //             'gvcn' => $info['ten_gvcn'] ?? 'Chưa gán',
    //             'tong_tiet_da_xep' => $tong_da_xep_total,
    //             'tong_tiet_ke_hoach' => $tong_ke_hoach_total,
    //             'mon_hoc' => $mon_hoc_aggregated
    //         ];
    //     } catch (PDOException $e) {
    //         error_log("Lỗi getRangBuocLop: " . $e->getMessage());
    //         return [];
    //     }
    // }

    // public function getRangBuocLop($ma_lop, $ma_hoc_ky, $ngay_bat_dau = null, $ngay_ket_thuc = null) {
    //     if ($this->db === null) return [];

    //     try {
    //         // 1. Lấy thông tin cơ bản (Giữ nguyên)
    //         $sql_info = "SELECT l.ten_lop, p.ten_phong AS ten_phong_chinh, nd.ho_ten AS ten_gvcn
    //                     FROM lop_hoc l
    //                     LEFT JOIN phong_hoc p ON l.ma_phong_hoc_chinh = p.ma_phong
    //                     LEFT JOIN bang_phan_cong bpc_cn ON bpc_cn.ma_lop = l.ma_lop AND bpc_cn.ma_mon_hoc IN (18, 19)
    //                     LEFT JOIN giao_vien gv ON bpc_cn.ma_giao_vien = gv.ma_giao_vien
    //                     LEFT JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
    //                     WHERE l.ma_lop = ? LIMIT 1";
    //         $stmt_info = $this->db->prepare($sql_info);
    //         $stmt_info->execute([$ma_lop]);
    //         $info = $stmt_info->fetch();

    //         // 2. Lấy KẾ HOẠCH (Giữ nguyên)
    //         $sql_phan_cong = "SELECT bpc.ma_phan_cong, m.ten_mon_hoc, bpc.so_tiet_tuan
    //                         FROM bang_phan_cong bpc
    //                         JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
    //                         WHERE bpc.ma_lop = ?";
    //         $stmt_phan_cong = $this->db->prepare($sql_phan_cong);
    //         $stmt_phan_cong->execute([$ma_lop]);
    //         $all_phan_cong = $stmt_phan_cong->fetchAll(PDO::FETCH_ASSOC);

    //         // 3. [FIX] ĐẾM SỐ TIẾT THỰC TẾ
    //         $thuc_te_map = []; 
    //         $tong_da_xep_total = 0;

    //         if (!$ngay_bat_dau) $ngay_bat_dau = date('Y-m-d', strtotime('monday this week'));
    //         if (!$ngay_ket_thuc) $ngay_ket_thuc = date('Y-m-d', strtotime('sunday this week'));

    //         $tkb_merged = $this->getChiTietTkbTuan($ma_lop, $ma_hoc_ky, $ngay_bat_dau, $ngay_ket_thuc);
            
    //         foreach ($tkb_merged as $thu => $cac_tiet) {
    //             foreach ($cac_tiet as $tiet => $data) {
    //                 // Logic Đếm:
    //                 // 1. Phải có Môn học (ma_phan_cong không null)
    //                 // 2. Loại tiết phải là 'hoc', 'thi' hoặc 'day_bu'. 'tam_nghi' KHÔNG ĐẾM.
    //                 if (!empty($data['ma_phan_cong']) && in_array($data['loai_tiet'], ['hoc', 'thi', 'day_bu'])) {
    //                     $mpc = $data['ma_phan_cong'];
    //                     if (!isset($thuc_te_map[$mpc])) $thuc_te_map[$mpc] = 0;
                        
    //                     $thuc_te_map[$mpc]++; 
    //                     $tong_da_xep_total++; 
    //                 }
    //             }
    //         }

    //         // 4. Tổng hợp dữ liệu (Giữ nguyên)
    //         $mon_hoc_aggregated = [];
    //         $tong_ke_hoach_total = 0;

    //         $phan_cong_grouped = [];
    //         foreach ($all_phan_cong as $pc) {
    //             $phan_cong_grouped[$pc['ten_mon_hoc']][] = $pc;
    //         }

    //         foreach ($phan_cong_grouped as $ten_mon => $ds_pc) {
    //             $da_xep_mon = 0;
    //             $ke_hoach_mon = 0;
    //             $ma_pc_list = [];

    //             if (!empty($ds_pc)) $ke_hoach_mon = (int)$ds_pc[0]['so_tiet_tuan'];

    //             foreach ($ds_pc as $pc) {
    //                 $mpc = $pc['ma_phan_cong'];
    //                 $sl = $thuc_te_map[$mpc] ?? 0;
    //                 $da_xep_mon += $sl;
    //                 $ma_pc_list[] = $mpc;
    //             }

    //             $mon_hoc_aggregated[$ten_mon] = [
    //                 'da_xep' => $da_xep_mon,
    //                 'ke_hoach' => $ke_hoach_mon,
    //                 'ma_phan_cong_list' => $ma_pc_list
    //             ];
    //             $tong_ke_hoach_total += $ke_hoach_mon;
    //         }

    //         return [
    //             'ten_lop' => $info['ten_lop'] ?? 'N/A',
    //             'phong_chinh' => $info['ten_phong_chinh'] ?? 'N/A',
    //             'gvcn' => $info['ten_gvcn'] ?? 'N/A',
    //             'tong_tiet_da_xep' => $tong_da_xep_total,
    //             'tong_tiet_ke_hoach' => $tong_ke_hoach_total,
    //             'mon_hoc' => $mon_hoc_aggregated
    //         ];

    //     } catch (PDOException $e) {
    //         error_log("Lỗi getRangBuocLop: " . $e->getMessage());
    //         return [];
    //     }
    // }
    public function getRangBuocLop($ma_lop, $ma_hoc_ky, $ngay_bat_dau = null, $ngay_ket_thuc = null) {
        if ($this->db === null) return [];
        try {
            // 1. Lấy thông tin cơ bản (Lớp, Phòng, GVCN)
            $sql_info = "SELECT l.ten_lop, p.ten_phong AS ten_phong_chinh, nd.ho_ten AS ten_gvcn
                        FROM lop_hoc l LEFT JOIN phong_hoc p ON l.ma_phong_hoc_chinh = p.ma_phong
                        LEFT JOIN bang_phan_cong bpc_cn ON bpc_cn.ma_lop = l.ma_lop AND bpc_cn.ma_mon_hoc IN (18, 19)
                        LEFT JOIN giao_vien gv ON bpc_cn.ma_giao_vien = gv.ma_giao_vien
                        LEFT JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
                        WHERE l.ma_lop = ? LIMIT 1";
            $stmt_info = $this->db->prepare($sql_info); $stmt_info->execute([$ma_lop]);
            $info = $stmt_info->fetch();

            // 2. Lấy KẾ HOẠCH phân công (Lọc bỏ 18, 19)
            $sql_phan_cong = "SELECT bpc.ma_phan_cong, m.ten_mon_hoc, bpc.so_tiet_tuan
                            FROM bang_phan_cong bpc JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
                            WHERE bpc.ma_lop = ? AND m.ma_mon_hoc NOT IN (18, 19)";
            $stmt_phan_cong = $this->db->prepare($sql_phan_cong); $stmt_phan_cong->execute([$ma_lop]);
            $all_phan_cong = $stmt_phan_cong->fetchAll(PDO::FETCH_ASSOC);

            // 3. ĐẾM SỐ TIẾT THỰC TẾ TRONG TUẦN
            $thuc_te_map = []; $tong_da_xep_total = 0;
            if (!$ngay_bat_dau) $ngay_bat_dau = date('Y-m-d', strtotime('monday this week'));
            if (!$ngay_ket_thuc) $ngay_ket_thuc = date('Y-m-d', strtotime('sunday this week'));

            $tkb_merged = $this->getChiTietTkbTuan($ma_lop, $ma_hoc_ky, $ngay_bat_dau, $ngay_ket_thuc);
            foreach ($tkb_merged as $thu => $cac_tiet) {
                foreach ($cac_tiet as $tiet => $data) {
                    if (!empty($data['ma_phan_cong']) && in_array($data['loai_tiet'], ['hoc', 'thi', 'day_bu'])) {
                        $mpc = $data['ma_phan_cong'];
                        // Chỉ đếm nếu mã phân công này thuộc môn văn hóa (không phải 18, 19)
                        foreach($all_phan_cong as $pc) {
                            if($pc['ma_phan_cong'] == $mpc) {
                                $thuc_te_map[$mpc] = ($thuc_te_map[$mpc] ?? 0) + 1;
                                $tong_da_xep_total++;
                                break;
                            }
                        }
                    }
                }
            }

            // 4. Tổng hợp trả về View
            $mon_hoc_aggregated = []; $tong_ke_hoach_total = 0;
            foreach ($all_phan_cong as $pc) {
                $ten_mon = $pc['ten_mon_hoc'];
                if (!isset($mon_hoc_aggregated[$ten_mon])) {
                    $mon_hoc_aggregated[$ten_mon] = ['da_xep' => 0, 'ke_hoach' => (int)$pc['so_tiet_tuan']];
                    $tong_ke_hoach_total += (int)$pc['so_tiet_tuan'];
                }
                $mon_hoc_aggregated[$ten_mon]['da_xep'] += ($thuc_te_map[$pc['ma_phan_cong']] ?? 0);
            }

            return [
                'ten_lop' => $info['ten_lop'] ?? 'N/A', 'phong_chinh' => $info['ten_phong_chinh'] ?? 'N/A',
                'gvcn' => $info['ten_gvcn'] ?? 'N/A', 'tong_tiet_da_xep' => $tong_da_xep_total,
                'tong_tiet_ke_hoach' => $tong_ke_hoach_total, 'mon_hoc' => $mon_hoc_aggregated
            ];
        } catch (PDOException $e) { return []; }
    }

    // public function kiemTraRangBuoc($ma_lop, $ma_hoc_ky, $thu, $tiet, $ma_phan_cong, $ma_tkb_chi_tiet_dang_sua = null, $ngay_check = null) {
    //     if ($this->db === null) return "Lỗi kết nối CSDL.";

    //     try {
    //         // 1. LẤY THÔNG TIN PHÂN CÔNG
    //         $sql_pc = "SELECT bpc.ma_giao_vien, bpc.ma_mon_hoc, bpc.so_tiet_tuan, m.ten_mon_hoc
    //                    FROM bang_phan_cong bpc
    //                    JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
    //                    WHERE bpc.ma_phan_cong = ?";
    //         $stmt_pc = $this->db->prepare($sql_pc);
    //         $stmt_pc->execute([$ma_phan_cong]);
    //         $phan_cong_info = $stmt_pc->fetch();

    //         if (!$phan_cong_info) return "Không tìm thấy thông tin phân công.";

    //         $ma_giao_vien = $phan_cong_info['ma_giao_vien'];
    //         $ke_hoach_mon = (int)$phan_cong_info['so_tiet_tuan'];
    //         $ten_mon_hoc  = $phan_cong_info['ten_mon_hoc'];

    //         // 2. CHECK TRÙNG GIÁO VIÊN & PHÒNG (Giữ nguyên)
    //         $sql_gv_ban = "SELECT COUNT(*) FROM tkb_chi_tiet t
    //                        JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
    //                        WHERE bpc.ma_giao_vien = ? 
    //                        AND t.thu = ? AND t.tiet = ? AND t.ma_hoc_ky = ?
    //                        AND t.ma_tkb_chi_tiet <> ?"; 
    //         $stmt_gv_ban = $this->db->prepare($sql_gv_ban);
    //         $stmt_gv_ban->execute([$ma_giao_vien, $thu, $tiet, $ma_hoc_ky, $ma_tkb_chi_tiet_dang_sua ?? -1]);
            
    //         if ($stmt_gv_ban->fetchColumn() > 0) {
    //             return "Giáo viên đã có lịch dạy LỊCH CỨNG ở lớp khác vào Thứ $thu, Tiết $tiet.";
    //         }

    //         // =================================================================
    //         // 3. [FIX UPDATE] KIỂM TRA ĐỊNH MỨC THÔNG MINH (BAO QUÁT CẢ 2 CHIỀU)
    //         // =================================================================
            
    //         if ($ngay_check) {
    //             // === CASE A: Đang lưu thay đổi cho NGÀY ===
    //             // Logic: Lấy lịch tuần đó -> Đè tiết mới -> Đếm
    //             $dt = new DateTime($ngay_check);
    //             $dw = (int)$dt->format('N');
    //             $start = (clone $dt)->modify('-' . ($dw - 1) . ' days')->format('Y-m-d');
    //             $end   = (clone $dt)->modify('+6 days')->format('Y-m-d');

    //             $tkb_merged = $this->getChiTietTkbTuan($ma_lop, $ma_hoc_ky, $start, $end);
                
    //             // Giả lập ghi đè
    //             $tkb_merged[$thu][$tiet] = ['ma_phan_cong' => $ma_phan_cong, 'loai_tiet' => 'hoc'];

    //             $count = 0;
    //             foreach ($tkb_merged as $d) foreach ($d as $t) {
    //                 if (isset($t['ma_phan_cong']) && $t['ma_phan_cong'] == $ma_phan_cong && in_array($t['loai_tiet']??'hoc', ['hoc','thi','day_bu'])) $count++;
    //             }

    //             if ($count > $ke_hoach_mon) return "Vượt quá định mức tuần này ($count/$ke_hoach_mon).";

    //         } else {
    //             // === CASE B: Đang lưu LỊCH CỨNG (Học kỳ) ===
                
    //             // B1. Kiểm tra Lịch Cứng thuần túy (Base)
    //             $sql_cung = "SELECT COUNT(*) FROM tkb_chi_tiet 
    //                          WHERE ma_lop = ? AND ma_phan_cong = ? AND ma_hoc_ky = ? 
    //                          AND loai_tiet IN ('hoc', 'thi', 'day_bu') 
    //                          AND ma_tkb_chi_tiet <> ?";
    //             $stmt_cung = $this->db->prepare($sql_cung);
    //             $stmt_cung->execute([$ma_lop, $ma_phan_cong, $ma_hoc_ky, $ma_tkb_chi_tiet_dang_sua ?? -1]);
    //             $count_cung = (int)$stmt_cung->fetchColumn() + 1; // +1 tiết đang thêm
                
    //             if ($count_cung > $ke_hoach_mon) return "Vượt quá định mức (Lịch cứng: $count_cung/$ke_hoach_mon).";

    //             // B2. [QUAN TRỌNG] QUÉT CÁC TUẦN CÓ THAY ĐỔI
    //             // Lấy danh sách các ngày có thay đổi của lớp này
    //             $sql_dates = "SELECT DISTINCT ngay_thay_doi FROM tkb_thay_doi WHERE ma_lop = ?";
    //             $stmt_dates = $this->db->prepare($sql_dates);
    //             $stmt_dates->execute([$ma_lop]);
    //             $dates = $stmt_dates->fetchAll(PDO::FETCH_COLUMN);

    //             // Gom theo tuần để check (tránh check 1 tuần nhiều lần)
    //             $weeks_checked = [];
    //             foreach ($dates as $d) {
    //                 $dt = new DateTime($d);
    //                 $week_key = $dt->format('oW'); // Năm+Tuần
    //                 if (isset($weeks_checked[$week_key])) continue;
    //                 $weeks_checked[$week_key] = true;

    //                 // Tính start/end tuần đó
    //                 $dw = (int)$dt->format('N');
    //                 $start = (clone $dt)->modify('-' . ($dw - 1) . ' days')->format('Y-m-d');
    //                 $end   = (clone $dt)->modify('+6 days')->format('Y-m-d');

    //                 // Lấy TKB tuần đó (đã bao gồm thay đổi)
    //                 // Lưu ý: TKB này được lấy từ DB nên CHƯA CÓ tiết cứng ta sắp thêm
    //                 $tkb_merged = $this->getChiTietTkbTuan($ma_lop, $ma_hoc_ky, $start, $end);

    //                 // Check xem ô [Thu][Tiet] ta định thêm có bị ĐÈ bởi lịch thay đổi không?
    //                 // Nếu ô đó trong tuần này đã có lịch Thay đổi (VD: Nghỉ), thì việc ta thêm lịch cứng ở dưới sẽ không làm tăng số tiết.
    //                 // Nếu ô đó chưa có lịch thay đổi, thì lịch cứng mới sẽ hiển thị -> Tăng số tiết -> Cần check.
                    
    //                 $is_overridden = !empty($tkb_merged[$thu][$tiet]['is_thay_doi']);

    //                 if (!$is_overridden) {
    //                     // Nếu không bị đè -> Tiết cứng mới sẽ có hiệu lực trong tuần này -> Đếm
    //                     $count_week = 0;
    //                     foreach ($tkb_merged as $d_data) foreach ($d_data as $t_data) {
    //                         if (isset($t_data['ma_phan_cong']) && $t_data['ma_phan_cong'] == $ma_phan_cong && in_array($t_data['loai_tiet']??'hoc', ['hoc','thi','day_bu'])) {
    //                             $count_week++;
    //                         }
    //                     }
                        
    //                     $total = $count_week + 1; // Cộng tiết mới
    //                     if ($total > $ke_hoach_mon) {
    //                         return "Không thể thêm lịch cứng: Tuần $start đã có lịch dạy bù, thêm nữa sẽ vượt định mức ($total/$ke_hoach_mon).";
    //                     }
    //                 }
    //             }
    //         }

    //         return true;

    //     } catch (PDOException $e) {
    //         return "Lỗi hệ thống: " . $e->getMessage();
    //     }
    // }

    public function kiemTraRangBuoc($ma_lop, $ma_hoc_ky, $thu, $tiet, $ma_phan_cong, $ma_tkb_chi_tiet_dang_sua = null, $ngay_check = null) {
        if ($this->db === null) return "Lỗi kết nối CSDL.";

        try {
            // 1. LẤY THÔNG TIN PHÂN CÔNG & DANH SÁCH GV CÙNG MÔN
            $stmt = $this->db->prepare("SELECT ma_mon_hoc, so_tiet_tuan, ma_giao_vien FROM bang_phan_cong WHERE ma_phan_cong = ?");
            $stmt->execute([$ma_phan_cong]);
            $pc_info = $stmt->fetch();
            if (!$pc_info) return "Không tìm thấy phân công.";

            $ma_mon = $pc_info['ma_mon_hoc'];
            $dinh_muc_mon = (int)$pc_info['so_tiet_tuan'];
            $ma_gv_hien_tai = $pc_info['ma_giao_vien'];

            // Lấy TẤT CẢ mã phân công của môn này trong lớp (Thầy An, Thầy Tuấn...)
            $stmt = $this->db->prepare("SELECT ma_phan_cong FROM bang_phan_cong WHERE ma_lop = ? AND ma_mon_hoc = ?");
            $stmt->execute([$ma_lop, $ma_mon]);
            $all_pc_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // 2. XÁC ĐỊNH NGÀY ĐỂ LẤY TKB TUẦN
            $date_str = $ngay_check ?: date('Y-m-d');
            $dt = new DateTime($date_str);
            $dw = (int)$dt->format('N'); 
            $start = (clone $dt)->modify('-' . ($dw - 1) . ' days')->format('Y-m-d');
            $end   = (clone $dt)->modify('+6 days')->format('Y-m-d');

            // 3. LẤY TKB MERGED (Lịch chuẩn + Lịch thay đổi)
            $tkb_merged = $this->getChiTietTkbTuan($ma_lop, $ma_hoc_ky, $start, $end);

            // 4. GIẢ LẬP ĐÈ TIẾT MỚI VÀO ĐỂ ĐẾM (Xóa tiết cũ đang sửa nếu có để không đếm trùng)
            // Ép kiểu để tránh lệch mảng
            $thu = (int)$thu; 
            $tiet = (int)$tiet;
            $tkb_merged[$thu][$tiet] = [
                'ma_phan_cong' => $ma_phan_cong, 
                'loai_tiet' => 'hoc' // Giả sử là tiết học chính
            ];

            $count_mon_tuan = 0;
            $count_tong_tuan = 0;

            // 5. VÒNG LẶP ĐẾM CHUẨN
            for ($d = 2; $d <= 8; $d++) { // Duyệt từ Thứ 2 đến CN
                for ($t = 1; $t <= 7; $t++) { // Duyệt từ Tiết 1 đến 7
                    $slot = $tkb_merged[$d][$t] ?? null;
                    
                    if ($slot && !empty($slot['ma_phan_cong'])) {
                        $loai = $slot['loai_tiet'] ?? 'hoc';
                        
                        // Chỉ đếm nếu là tiết thực học (không đếm tạm nghỉ)
                        if (in_array($loai, ['hoc', 'thi', 'day_bu'])) {
                            // Đếm tổng cả lớp
                            $count_tong_tuan++;

                            // Đếm riêng môn này (Check xem ID phân công có nằm trong list anh em không)
                            if (in_array($slot['ma_phan_cong'], $all_pc_ids)) {
                                $count_mon_tuan++;
                            }
                        }
                    }
                }
            }

            // 6. KIỂM TRA ĐỊNH MỨC
            if ($count_mon_tuan > $dinh_muc_mon) {
                return "Môn học đã đủ định mức ($dinh_muc_mon tiết). Hiện tại xếp thêm sẽ thành $count_mon_tuan tiết.";
            }

            if ($count_tong_tuan > 41) {
                return "Lớp đã đạt giới hạn 41 tiết/tuần. Hiện tại là $count_tong_tuan tiết.";
            }

            // 7. KIỂM TRA TRÙNG LỊCH GIÁO VIÊN Ở LỚP KHÁC
            // Check lịch cứng
            $sql_gv = "SELECT l.ten_lop FROM tkb_chi_tiet t 
                       JOIN lop_hoc l ON t.ma_lop = l.ma_lop
                       JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
                       WHERE bpc.ma_giao_vien = ? AND t.thu = ? AND t.tiet = ? 
                       AND t.ma_hoc_ky = ? AND t.ma_lop <> ?";
            $stmt = $this->db->prepare($sql_gv);
            $stmt->execute([$ma_gv_hien_tai, $thu, $tiet, $ma_hoc_ky, $ma_lop]);
            $conflict = $stmt->fetchColumn();
            if ($conflict) return "Giáo viên này đã có lịch dạy cố định tại lớp $conflict.";

            return true;

        } catch (Exception $e) {
            return "Lỗi hệ thống: " . $e->getMessage();
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
     * Trả về `ma_truong` của 1 lớp, hoặc null nếu không tìm thấy
     */
    public function getMaTruongByLop($ma_lop) {
        if ($this->db === null) return null;
        try {
            $stmt = $this->db->prepare("SELECT ma_truong FROM lop_hoc WHERE ma_lop = ? LIMIT 1");
            $stmt->execute([$ma_lop]);
            $res = $stmt->fetchColumn();
            if ($res === false || $res === null) return null;
            return $res;
        } catch (PDOException $e) {
            error_log("Lỗi getMaTruongByLop: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tự động xác định phòng học cho một tiết (Giữ nguyên)
     */
    // private function xacDinhPhongHoc($ma_phan_cong, $ma_lop) {
    //     // (Hàm này giữ nguyên, không cần thay đổi)
    //     if ($this->db === null) return $this->getPhongHocChinhID($ma_lop);

    //     try {
    //         $sql = "SELECT m.yeu_cau_phong_dac_biet
    //                 FROM bang_phan_cong bpc
    //                 JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
    //                 WHERE bpc.ma_phan_cong = ?";
    //         $stmt_mon = $this->db->prepare($sql);
    //         $stmt_mon->execute([$ma_phan_cong]);
    //         $yeu_cau = $stmt_mon->fetchColumn();

    //         if ($yeu_cau && $yeu_cau !== 'None') {
    //             $sql_phong_dt = "SELECT ma_phong
    //                              FROM phong_hoc
    //                              WHERE loai_phong = ? AND trang_thai_phong = 'HoatDong'
    //                              LIMIT 1";
    //             $stmt_phong_dt = $this->db->prepare($sql_phong_dt);
    //             $stmt_phong_dt->execute([$yeu_cau]);
    //             $ma_phong_dac_thu = $stmt_phong_dt->fetchColumn();

    //             if ($ma_phong_dac_thu) {
    //                 return (int)$ma_phong_dac_thu;
    //             }
    //         }
    //         return $this->getPhongHocChinhID($ma_lop);
    //     } catch (PDOException $e) {
    //         error_log("Lỗi xacDinhPhongHoc: " . $e->getMessage());
    //         return $this->getPhongHocChinhID($ma_lop);
    //     }
    // }
    private function xacDinhPhongHoc($ma_phan_cong, $ma_lop) {
        if ($this->db === null) return $this->getPhongHocChinhID($ma_lop);
        if (!$ma_phan_cong) return $this->getPhongHocChinhID($ma_lop);
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
                if ($ma_phong_dac_thu) return (int)$ma_phong_dac_thu;
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
    public function luuTietHoc($ma_lop, $ma_hoc_ky, $thu, $tiet, $ma_phan_cong = null, $loai_tiet = 'hoc', $ghi_chu = null) {
        if ($this->db === null) return false;
        $loai_tiet = in_array($loai_tiet, ['hoc','thi','tam_nghi']) ? $loai_tiet : 'hoc';
        $this->db->beginTransaction();
        try {
            $ma_phong = $ma_phan_cong ? $this->xacDinhPhongHoc($ma_phan_cong, $ma_lop) : $this->getPhongHocChinhID($ma_lop);
            $sql = "INSERT INTO tkb_chi_tiet (ma_lop, ma_hoc_ky, thu, tiet, ma_phan_cong, ma_phong_hoc, loai_tiet, ghi_chu)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE ma_phan_cong = VALUES(ma_phan_cong),
                                            ma_phong_hoc = VALUES(ma_phong_hoc),
                                            loai_tiet = VALUES(loai_tiet),
                                            ghi_chu = VALUES(ghi_chu)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_lop, $ma_hoc_ky, $thu, $tiet, $ma_phan_cong, $ma_phong, $loai_tiet, $ghi_chu]);
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Lỗi luuTietHoc: " . $e->getMessage());
            return false;
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



    public function getMaTkbChiTietBySlot($ma_lop, $ma_hoc_ky, $thu, $tiet) {
        if ($this->db === null) return null;
        try {
            $sql = "SELECT ma_tkb_chi_tiet FROM tkb_chi_tiet WHERE ma_lop = ? AND ma_hoc_ky = ? AND thu = ? AND tiet = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_lop, $ma_hoc_ky, $thu, $tiet]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Lỗi getMaTkbChiTietBySlot: " . $e->getMessage());
            return null;
        }
    }


    // ... Các hàm cũ giữ nguyên ...

    /**
     * HÀM MỚI (QUAN TRỌNG): Lấy TKB chuẩn TRỘN VỚI TKB thay đổi theo tuần
     */
    public function getChiTietTkbTuan($ma_lop, $ma_hoc_ky, $ngay_bat_dau, $ngay_ket_thuc) {
        // 1. Lấy TKB Cứng (Lịch lặp lại)
        $tkb_chuan = $this->getChiTietTkbLop($ma_lop, $ma_hoc_ky);

        // 2. Lấy TKB Thay đổi trong tuần này (Lịch thi, nghỉ, bù...)
        if ($this->db === null) return $tkb_chuan;

        $sql = "SELECT t.*, m.ten_mon_hoc, nd.ho_ten as ten_giao_vien, p.ten_phong
                FROM tkb_thay_doi t
                LEFT JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
                LEFT JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
                LEFT JOIN giao_vien gv ON bpc.ma_giao_vien = gv.ma_giao_vien
                LEFT JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
                LEFT JOIN phong_hoc p ON (
                    -- Logic tìm tên phòng: Nếu lớp có set phòng riêng thì lấy, không thì lấy phòng học chính
                     SELECT ma_phong FROM phong_hoc WHERE ma_phong = (
                        SELECT COALESCE(ph1.ma_phong, ph2.ma_phong_hoc_chinh)
                        FROM bang_phan_cong bpc_sub 
                        JOIN mon_hoc m_sub ON bpc_sub.ma_mon_hoc = m_sub.ma_mon_hoc
                        LEFT JOIN phong_hoc ph1 ON ph1.loai_phong = m_sub.yeu_cau_phong_dac_biet
                        JOIN lop_hoc ph2 ON ph2.ma_lop = t.ma_lop
                        WHERE bpc_sub.ma_phan_cong = t.ma_phan_cong
                        LIMIT 1
                     )
                )
                WHERE t.ma_lop = ? AND t.ngay_thay_doi BETWEEN ? AND ?";
        
        // *Lưu ý: Query trên hơi phức tạp đoạn join phòng, nếu bác muốn đơn giản 
        // thì chỉ cần select bảng tkb_thay_doi thôi, tên môn/gv xử lý sau cũng được.
        // Ở đây tôi viết gọn lại query đơn giản cho bác dễ debug nhé:
        
        $sql_simple = "SELECT t.*, m.ten_mon_hoc, nd.ho_ten as ten_giao_vien
                       FROM tkb_thay_doi t
                       LEFT JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
                       LEFT JOIN mon_hoc m ON bpc.ma_mon_hoc = m.ma_mon_hoc
                       LEFT JOIN giao_vien gv ON bpc.ma_giao_vien = gv.ma_giao_vien
                       LEFT JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
                       WHERE t.ma_lop = ? AND t.ngay_thay_doi BETWEEN ? AND ?";

        try {
            $stmt = $this->db->prepare($sql_simple);
            $stmt->execute([$ma_lop, $ngay_bat_dau, $ngay_ket_thuc]);
            $thay_doi_list = $stmt->fetchAll();

            // 3. Logic ĐÈ (Overlay)
            foreach ($thay_doi_list as $row) {
                // Tính thứ mấy trong tuần (2-8) từ ngày
                // date('N') trả về 1 (Thứ 2) -> 7 (CN). Hệ thống bác dùng 2-8 nên phải +1
                $timestamp = strtotime($row['ngay_thay_doi']);
                $thu = date('N', $timestamp) + 1; 

                $tiet = (int)$row['tiet'];

                // Tạo dữ liệu mới để đè vào
                $tkb_chuan[$thu][$tiet] = [
                    'mon' => $row['ten_mon_hoc'] ?? ($row['loai_tiet'] == 'tam_nghi' ? 'Tạm nghỉ' : 'N/A'),
                    'gv' => $row['ten_giao_vien'] ?? '',
                    'phong' => 'Check lịch ngày', // Hoặc bác query lấy tên phòng
                    'ma_phong' => null,
                    'ma_phan_cong' => $row['ma_phan_cong'],
                    'loai_tiet' => $row['loai_tiet'],
                    'ghi_chu' => $row['ghi_chu'],
                    'is_thay_doi' => true, // Cờ này để View hiện icon cảnh báo
                    'ngay_cu_the' => $row['ngay_thay_doi']
                ];
            }
        } catch (PDOException $e) {
             error_log("Lỗi getChiTietTkbTuan: " . $e->getMessage());
        }

        return $tkb_chuan;
    }

    /**
     * HÀM MỚI: Lưu thay đổi cho 1 ngày cụ thể
     */
    // public function luuThayDoiTietHoc($ma_lop, $ngay, $tiet, $ma_phan_cong, $loai_tiet, $ghi_chu) {
    //     if ($this->db === null) return false;
        
    //     // Nếu loại tiết là tam_nghi thì ma_phan_cong có thể null
    //     if ($loai_tiet == 'tam_nghi') $ma_phan_cong = null;

    //     // Kiểm tra xem đã có record thay đổi cho ngày này chưa
    //     // Nếu có thì UPDATE, chưa thì INSERT. 
    //     // Tuy nhiên bảng tkb_thay_doi của bác ID là tự tăng, nên ta check tồn tại trước
        
    //     $sql_check = "SELECT ma_thay_doi FROM tkb_thay_doi WHERE ma_lop = ? AND ngay_thay_doi = ? AND tiet = ?";
    //     $stmt = $this->db->prepare($sql_check);
    //     $stmt->execute([$ma_lop, $ngay, $tiet]);
    //     $existing_id = $stmt->fetchColumn();

    //     try {
    //         if ($existing_id) {
    //             // Update
    //             $sql = "UPDATE tkb_thay_doi SET ma_phan_cong = ?, loai_tiet = ?, ghi_chu = ? 
    //                     WHERE ma_thay_doi = ?";
    //             $stmt = $this->db->prepare($sql);
    //             $stmt->execute([$ma_phan_cong, $loai_tiet, $ghi_chu, $existing_id]);
    //         } else {
    //             // Insert
    //             $sql = "INSERT INTO tkb_thay_doi (ma_lop, ngay_thay_doi, tiet, ma_phan_cong, loai_tiet, ghi_chu) 
    //                     VALUES (?, ?, ?, ?, ?, ?)";
    //             $stmt = $this->db->prepare($sql);
    //             $stmt->execute([$ma_lop, $ngay, $tiet, $ma_phan_cong, $loai_tiet, $ghi_chu]);
    //         }
    //         return true;
    //     } catch (PDOException $e) {
    //         error_log("Lỗi luuThayDoiTietHoc: " . $e->getMessage());
    //         return false;
    //     }
    // }
    /**
     * HÀM MỚI: Lưu thay đổi cho 1 ngày cụ thể
     */
    public function luuThayDoiTietHoc($ma_lop, $ngay, $tiet, $ma_phan_cong, $loai_tiet, $ghi_chu) {
        if ($this->db === null) return false;
        
        // [QUAN TRỌNG]: Đã xóa dòng ép ma_phan_cong = null ở đây.
        // Giờ đây nếu bác gửi ID môn "An Ninh", nó sẽ lưu vào DB, từ đó đếm được tiết.

        // Kiểm tra xem đã có record thay đổi cho ngày này chưa
        $sql_check = "SELECT ma_thay_doi FROM tkb_thay_doi WHERE ma_lop = ? AND ngay_thay_doi = ? AND tiet = ?";
        $stmt = $this->db->prepare($sql_check);
        $stmt->execute([$ma_lop, $ngay, $tiet]);
        $existing_id = $stmt->fetchColumn();

        try {
            if ($existing_id) {
                // Update
                $sql = "UPDATE tkb_thay_doi SET ma_phan_cong = ?, loai_tiet = ?, ghi_chu = ? 
                        WHERE ma_thay_doi = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$ma_phan_cong, $loai_tiet, $ghi_chu, $existing_id]);
            } else {
                // Insert
                $sql = "INSERT INTO tkb_thay_doi (ma_lop, ngay_thay_doi, tiet, ma_phan_cong, loai_tiet, ghi_chu) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$ma_lop, $ngay, $tiet, $ma_phan_cong, $loai_tiet, $ghi_chu]);
            }
            return true;
        } catch (PDOException $e) {
            error_log("Lỗi luuThayDoiTietHoc: " . $e->getMessage());
            return false;
        }
    }

    /**
     * HÀM MỚI: Xóa thay đổi (trả về lịch chuẩn)
     */
    public function xoaThayDoiTietHoc($ma_lop, $ngay, $tiet) {
        if ($this->db === null) return false;
        try {
            $stmt = $this->db->prepare("DELETE FROM tkb_thay_doi WHERE ma_lop = ? AND ngay_thay_doi = ? AND tiet = ?");
            return $stmt->execute([$ma_lop, $ngay, $tiet]);
        } catch (PDOException $e) {
            return false;
        }
    }


    /**
     * HÀM MỚI: Lấy danh sách môn và Check bận (Kiểm tra cả Lịch Thay Đổi & Lịch Cứng)
     */
    public function getDanhSachMonHocGV_CheckBan($ma_lop, $thu, $tiet, $ngay_check, $ma_hoc_ky) {
        if ($this->db === null) return ['mon_hoc_gv' => []];

        // 1. Lấy danh sách môn được phân công cho lớp hiện tại (Để đổ list vào dropdown)
        // (Vẫn dùng hàm cũ để lấy danh sách gốc)
        $ds_mon = $this->getDanhSachMonHocGV($ma_lop); 
        
        $result = ['mon_hoc_gv' => []];

        foreach ($ds_mon as $item) {
            $ma_gv = $item['ma_giao_vien'];
            $ma_phan_cong = $item['ma_phan_cong'];
            
            $is_ban = false;
            $ly_do = '';

            // --- A. KIỂM TRA LỊCH THAY ĐỔI (Ưu tiên số 1) ---
            // Xem vào NGÀY CỤ THỂ này, GV có lịch (Học/Thi/Bù) ở BẤT KỲ lớp nào không?
            if ($ngay_check) {
                $sql_check_temp = "SELECT l.ten_lop, t.loai_tiet 
                                   FROM tkb_thay_doi t
                                   JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
                                   JOIN lop_hoc l ON t.ma_lop = l.ma_lop
                                   WHERE bpc.ma_giao_vien = ? 
                                   AND t.ngay_thay_doi = ? 
                                   AND t.tiet = ?
                                   AND t.loai_tiet IN ('hoc', 'thi', 'day_bu')";
                
                $stmt_temp = $this->db->prepare($sql_check_temp);
                $stmt_temp->execute([$ma_gv, $ngay_check, $tiet]);
                $temp_conflict = $stmt_temp->fetch();

                if ($temp_conflict) {
                    $is_ban = true;
                    $loai_txt = ($temp_conflict['loai_tiet'] == 'thi') ? 'coi thi' : 'dạy bù';
                    $ly_do = "(Bận $loai_txt lớp {$temp_conflict['ten_lop']})";
                }
            }
            
            // --- B. KIỂM TRA LỊCH CỨNG (Nếu chưa bị bận ở bước A) ---
            if (!$is_ban) {
                // Check xem GV có lịch cứng ở lớp khác không?
                $sql_check_fixed = "SELECT l.ten_lop, t.ma_phan_cong
                                    FROM tkb_chi_tiet t
                                    JOIN bang_phan_cong bpc ON t.ma_phan_cong = bpc.ma_phan_cong
                                    JOIN lop_hoc l ON t.ma_lop = l.ma_lop
                                    WHERE bpc.ma_giao_vien = ?
                                    AND t.ma_hoc_ky = ?
                                    AND t.thu = ?
                                    AND t.tiet = ?
                                    AND t.ma_tkb_chi_tiet <> -1"; // Tránh lỗi cú pháp
                
                $stmt_fixed = $this->db->prepare($sql_check_fixed);
                $stmt_fixed->execute([$ma_gv, $ma_hoc_ky, $thu, $tiet]);
                $fixed_conflict = $stmt_fixed->fetch();

                if ($fixed_conflict) {
                    // Có lịch cứng, NHƯNG phải check xem ngày này ông ý có xin nghỉ tiết đó không?
                    $is_cancelled = false;
                    if ($ngay_check) {
                        $mpc_conflict = $fixed_conflict['ma_phan_cong'];
                        $sql_is_cancelled = "SELECT COUNT(*) FROM tkb_thay_doi 
                                             WHERE ma_phan_cong = ? 
                                             AND ngay_thay_doi = ? 
                                             AND tiet = ? 
                                             AND loai_tiet = 'tam_nghi'";
                        $stmt_cancel = $this->db->prepare($sql_is_cancelled);
                        $stmt_cancel->execute([$mpc_conflict, $ngay_check, $tiet]);
                        if ($stmt_cancel->fetchColumn() > 0) {
                            $is_cancelled = true;
                        }
                    }

                    if (!$is_cancelled) {
                        $is_ban = true;
                        $ly_do = "(Bận lịch cứng lớp {$fixed_conflict['ten_lop']})";
                    }
                }
            }

            // --- C. ĐÓNG GÓI KẾT QUẢ ---
            $result['mon_hoc_gv'][] = [
                'ma_phan_cong' => $ma_phan_cong,
                'ten_hien_thi' => $item['ten_mon_hoc'] . ' - ' . $item['ten_giao_vien'],
                'is_ban'       => $is_ban,
                'ly_do'        => $ly_do
            ];
        }

        return $result;
    }
    

}
?>