<?php
/**
 * DiemSoModel: Xử lý logic CSDL cho nghiệp vụ Điểm số và Duyệt điểm.
 */
class DiemSoModel {
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
     * Gửi phiếu yêu cầu chỉnh sửa điểm
     */
    public function guiPhieuChinhSuaDiem($ma_gv, $ma_hoc_sinh, $ma_mon_hoc, $ma_hoc_ky, $data_moi, $ly_do) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi kết nối CSDL.'];
        
        try {
            // Lấy điểm cũ
            $stmt_cu = $this->db->prepare("SELECT diem_mieng, diem_15phut, diem_1tiet, diem_gua_ky, diem_cuoi_ky
                                          FROM diem_mon_hoc_hoc_ky
                                          WHERE ma_hoc_sinh = ? AND ma_mon_hoc = ? AND ma_hoc_ky = ?");
            $stmt_cu->execute([$ma_hoc_sinh, $ma_mon_hoc, $ma_hoc_ky]);
            $diem_cu = $stmt_cu->fetch(PDO::FETCH_ASSOC);
            
            if (!$diem_cu) {
                return ['success' => false, 'message' => 'Không tìm thấy bản ghi điểm cũ.'];
            }
            
            // INSERT phiếu yêu cầu
            $sql = "INSERT INTO phieu_yeu_cau_chinh_sua_diem 
                    (ma_giao_vien, ma_hoc_sinh, ma_mon_hoc, ma_hoc_ky,
                     diem_mieng_cu, diem_15phut_cu, diem_1tiet_cu, diem_gua_ky_cu, diem_cuoi_ky_cu,
                     diem_mieng_moi, diem_15phut_moi, diem_1tiet_moi, diem_gua_ky_moi, diem_cuoi_ky_moi,
                     ly_do_chinh_sua, trang_thai_phieu, ngay_lap_phieu)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ChoDuyet', NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $ma_gv, $ma_hoc_sinh, $ma_mon_hoc, $ma_hoc_ky,
                $diem_cu['diem_mieng'], $diem_cu['diem_15phut'], $diem_cu['diem_1tiet'], 
                $diem_cu['diem_gua_ky'], $diem_cu['diem_cuoi_ky'],
                $data_moi['diem_mieng'] ?? null, $data_moi['diem_15phut'] ?? null, $data_moi['diem_1tiet'] ?? null,
                $data_moi['diem_gua_ky'] ?? null, $data_moi['diem_cuoi_ky'] ?? null,
                $ly_do
            ]);
            
            return ['success' => true, 'message' => 'Gửi phiếu yêu cầu chỉnh sửa thành công!'];
            
        } catch (PDOException $e) {
            error_log("Lỗi guiPhieuChinhSuaDiem: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
        }
    }
    
    /**
     * Duyệt phiếu chỉnh sửa điểm (Ban Giám Hiệu)
     */
    public function duyetPhieuChinhSuaMoi($ma_phieu, $ma_nguoi_duyet) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi kết nối CSDL.'];
        
        $this->db->beginTransaction();
        try {
            // Lấy thông tin phiếu
            $stmt_phieu = $this->db->prepare("SELECT * FROM phieu_yeu_cau_chinh_sua_diem WHERE ma_phieu = ? AND trang_thai_phieu = 'ChoDuyet'");
            $stmt_phieu->execute([$ma_phieu]);
            $phieu = $stmt_phieu->fetch(PDO::FETCH_ASSOC);
            
            if (!$phieu) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Không tìm thấy phiếu hoặc đã được xử lý.'];
            }
            
            // Tính điểm TX mới - KỂM TRA NULL
            $diem_tx_moi = null;
            if ($phieu['diem_mieng_moi'] !== null && $phieu['diem_15phut_moi'] !== null && $phieu['diem_1tiet_moi'] !== null) {
                $diem_tx_moi = round(($phieu['diem_mieng_moi'] + $phieu['diem_15phut_moi'] + $phieu['diem_1tiet_moi']) / 3, 2);
            }
            
            // Tính ĐTB Môn mới - KIỂM TRA NULL
            $diem_tb_mon_moi = null;
            if ($diem_tx_moi !== null && $phieu['diem_gua_ky_moi'] !== null && $phieu['diem_cuoi_ky_moi'] !== null) {
                $diem_tb_mon_moi = round(($diem_tx_moi + $phieu['diem_gua_ky_moi']*2 + $phieu['diem_cuoi_ky_moi']*3) / 6, 2);
            }
            
            // Xếp loại mới
            $xep_loai_moi = 'ChuaDat';
            if ($diem_tb_mon_moi !== null) {
                if ($diem_tb_mon_moi >= 8.0) $xep_loai_moi = 'Gioi';
                elseif ($diem_tb_mon_moi >= 6.5) $xep_loai_moi = 'Kha';
                elseif ($diem_tb_mon_moi >= 5.0) $xep_loai_moi = 'Dat';
            }
            
            // Cập nhật điểm
            $sql_update = "UPDATE diem_mon_hoc_hoc_ky SET
                            diem_mieng = ?, diem_15phut = ?, diem_1tiet = ?,
                            diem_tx = ?, diem_gua_ky = ?, diem_cuoi_ky = ?,
                            diem_tb_mon_hk = ?, xep_loai_mon = ?, ngay_cap_nhat = NOW()
                          WHERE ma_hoc_sinh = ? AND ma_mon_hoc = ? AND ma_hoc_ky = ?";
            $stmt_update = $this->db->prepare($sql_update);
            $stmt_update->execute([
                $phieu['diem_mieng_moi'], $phieu['diem_15phut_moi'], $phieu['diem_1tiet_moi'],
                $diem_tx_moi, $phieu['diem_gua_ky_moi'], $phieu['diem_cuoi_ky_moi'],
                $diem_tb_mon_moi, $xep_loai_moi,
                $phieu['ma_hoc_sinh'], $phieu['ma_mon_hoc'], $phieu['ma_hoc_ky']
            ]);
            
            // Cập nhật trạng thái phiếu
            $sql_phieu = "UPDATE phieu_yeu_cau_chinh_sua_diem SET 
                          trang_thai_phieu = 'DaDuyet', 
                          ma_nguoi_duyet = ?, 
                          ngay_duyet = NOW() 
                          WHERE ma_phieu = ?";
            $stmt_phieu = $this->db->prepare($sql_phieu);
            $stmt_phieu->execute([$ma_nguoi_duyet, $ma_phieu]);
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Duyệt phiếu và cập nhật điểm thành công!'];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Lỗi duyetPhieuChinhSuaMoi: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
        }
    }
    
    /**
     * Từ chối phiếu chỉnh sửa điểm
     */
    public function tuChoiPhieuChinhSuaMoi($ma_phieu, $ma_nguoi_duyet, $ly_do) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi kết nối CSDL.'];
        
        try {
            $sql = "UPDATE phieu_yeu_cau_chinh_sua_diem SET 
                    trang_thai_phieu = 'TuChoi', 
                    ma_nguoi_duyet = ?, 
                    ly_do_tu_choi = ?, 
                    ngay_duyet = NOW()
                    WHERE ma_phieu = ? AND trang_thai_phieu = 'ChoDuyet'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_nguoi_duyet, $ly_do, $ma_phieu]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Từ chối phiếu thành công!'];
            } else {
                return ['success' => false, 'message' => 'Phiếu không tồn tại hoặc đã được xử lý.'];
            }

        } catch (PDOException $e) {
            error_log("Lỗi tuChoiPhieuChinhSuaMoi: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
        }
    }

    /**
     * Lấy danh sách phiếu yêu cầu (ĐÃ NÂNG CẤP CÓ LỌC)
     */
    // public function getDanhSachPhieu($trang_thai = 'ChoDuyet', $limit = null) {
    //     if ($this->db === null) return [];
        
    //     $sql = "SELECT 
    //                 p.ma_phieu,
    //                 p.tieu_de,
    //                 p.ngay_lap_phieu,
    //                 p.diem_cu,
    //                 p.diem_de_nghi,
    //                 p.ly_do_chinh_sua,
    //                 p.trang_thai_phieu,
    //                 gv_nd.ho_ten AS ten_giao_vien_yeu_cau,
    //                 gv_nd.ho_ten AS ten_giao_vien,
    //                 hs_nd.ho_ten AS ten_hoc_sinh,
    //                 mh.ten_mon_hoc,
    //                 dmhk.loai_diem
    //             FROM phieu_yeu_cau_chinh_sua_diem AS p
    //             JOIN giao_vien AS gv ON p.ma_giao_vien = gv.ma_giao_vien
    //             JOIN nguoi_dung AS gv_nd ON gv.ma_giao_vien = gv_nd.ma_nguoi_dung
    //             JOIN diem_mon_hoc_hoc_ky AS dmhk ON p.ma_diem = dmhk.ma_diem_mon_hk
    //             JOIN mon_hoc AS mh ON dmhk.ma_mon_hoc = mh.ma_mon_hoc
    //             JOIN ket_qua_hoc_tap AS kqht ON dmhk.ma_hoc_sinh = kqht.ma_hoc_sinh
    //             JOIN hoc_sinh AS hs ON kqht.ma_hoc_sinh = hs.ma_hoc_sinh
    //             JOIN nguoi_dung AS hs_nd ON hs.ma_hoc_sinh = hs_nd.ma_nguoi_dung";
        
    //     $params = [];

    //     if ($trang_thai != 'TatCa') {
    //         $sql .= " WHERE p.trang_thai_phieu = ?"; 
    //         $params[] = $trang_thai;
    //     }

    //     $sql .= " ORDER BY p.ngay_lap_phieu DESC"; 

    //     if ($limit !== null) {
    //         $sql .= " LIMIT " . (int)$limit;
    //     }
        
    //     try {
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->execute($params); 
    //         return $stmt->fetchAll();
    //     } catch (PDOException $e) {
    //         error_log("Lỗi getDanhSachPhieu: " . $e->getMessage());
    //         return [];
    //     }
    // }
    public function getDanhSachPhieu($trang_thai = 'ChoDuyet', $limit = null) {
        if ($this->db === null) return [];
        
        $sql = "SELECT 
                    p.ma_phieu,
                    p.ngay_lap_phieu,
                    p.ly_do_chinh_sua,
                    p.trang_thai_phieu,
                    p.ma_hoc_ky,
                    -- Điểm cũ
                    p.diem_mieng_cu, p.diem_15phut_cu, p.diem_1tiet_cu,
                    p.diem_gua_ky_cu, p.diem_cuoi_ky_cu,
                    -- Điểm mới
                    p.diem_mieng_moi, p.diem_15phut_moi, p.diem_1tiet_moi,
                    p.diem_gua_ky_moi, p.diem_cuoi_ky_moi,
                    -- Thông tin GV
                    gv_nd.ho_ten AS ten_giao_vien_yeu_cau,
                    -- Thông tin HS
                    hs_nd.ho_ten AS ten_hoc_sinh,
                    -- Môn học
                    mh.ten_mon_hoc
                FROM phieu_yeu_cau_chinh_sua_diem p
                JOIN nguoi_dung gv_nd ON p.ma_giao_vien = gv_nd.ma_nguoi_dung
                JOIN nguoi_dung hs_nd ON p.ma_hoc_sinh = hs_nd.ma_nguoi_dung
                JOIN mon_hoc mh ON p.ma_mon_hoc = mh.ma_mon_hoc";
        
        $params = [];

        if ($trang_thai != 'TatCa') {
            $sql .= " WHERE p.trang_thai_phieu = ?"; 
            $params[] = $trang_thai;
        }

        $sql .= " ORDER BY p.ngay_lap_phieu DESC"; 

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params); 
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachPhieu: " . $e->getMessage());
            return [];
        }
    }


    /**
     * Xử lý duyệt phiếu (Transaction)
     */
    public function duyetPhieu($ma_phieu, $ma_nguoi_duyet) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi kết nối CSDL.'];

        $this->db->beginTransaction();
        try {
            // 1. Lấy thông tin
            $stmt_get = $this->db->prepare("SELECT ma_diem, diem_de_nghi FROM phieu_yeu_cau_chinh_sua_diem WHERE ma_phieu = ? AND trang_thai_phieu = 'ChoDuyet'");
            $stmt_get->execute([$ma_phieu]);
            $phieu = $stmt_get->fetch();

            if (!$phieu) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Phiếu không tồn tại hoặc đã được duyệt.'];
            }
            
            // 2. Cập nhật điểm
            $sql_update_diem = "UPDATE diem_mon_hoc_hoc_ky SET diem_tb_mon_hk = ? WHERE ma_diem_mon_hk = ?";
            $stmt_update_diem = $this->db->prepare($sql_update_diem);
            $stmt_update_diem->execute([$phieu['diem_de_nghi'], $phieu['ma_diem']]);

            // 3. Cập nhật trạng thái phiếu
            $sql_update_phieu = "UPDATE phieu_yeu_cau_chinh_sua_diem SET
                                    trang_thai_phieu = 'DaDuyet',
                                    nguoi_duyet = ?,
                                    ngay_duyet = CURDATE()
                                WHERE ma_phieu = ?";
            $stmt_update_phieu = $this->db->prepare($sql_update_phieu);
            $stmt_update_phieu->execute([$ma_nguoi_duyet, $ma_phieu]);

            // 4. Commit
            $this->db->commit();
            return ['success' => true, 'message' => 'Đã duyệt và cập nhật điểm thành công.'];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Lỗi duyetPhieu: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi hệ thống khi cập nhật CSDL.'];
        }
    }

    /**
     * Xử lý từ chối phiếu
     */
    public function tuChoiPhieu($ma_phieu, $ma_nguoi_duyet, $ly_do) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi kết nối CSDL.'];
        
        if (empty($ly_do)) {
            return ['success' => false, 'message' => 'Lý do từ chối không được để trống.'];
        }

        $sql = "UPDATE phieu_yeu_cau_chinh_sua_diem SET
                    trang_thai_phieu = 'TuChoi',
                    nguoi_duyet = ?,
                    ngay_duyet = CURDATE(),
                    ghi_chu = ?
                WHERE ma_phieu = ? AND trang_thai_phieu = 'ChoDuyet'";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_nguoi_duyet, $ly_do, $ma_phieu]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Đã từ chối phiếu thành công.'];
            } else {
                return ['success' => false, 'message' => 'Phiếu không tồn tại hoặc đã được xử lý.'];
            }

        } catch (PDOException $e) {
            error_log("Lỗi tuChoiPhieu: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi hệ thống khi cập nhật CSDL.'];
        }
    }

    // --- CÁC HÀM CHO DASHBOARD BGH ---

    public function getPhieuChoDuyetCount() {
        if ($this->db === null) return 0;
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM phieu_yeu_cau_chinh_sua_diem WHERE trang_thai_phieu = 'ChoDuyet'");
            return $stmt->fetchColumn() ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getDiemTBToanTruong() {
        if ($this->db === null) return 0.0;
        try {
            $stmt = $this->db->query("SELECT AVG(diem_tb_mon_hk) FROM diem_mon_hoc_hoc_ky"); 
            return round($stmt->fetchColumn() ?? 0, 2);
        } catch (PDOException $e) {
            return 0.0;
        }
    }

    /**
     * [MỚI] Dữ liệu cho Biểu đồ Tròn: Tỉ lệ trạng thái phiếu
     */
    public function getChartYeuCau() {
        if ($this->db === null) return ['ChoDuyet' => 0, 'DaDuyet' => 0, 'TuChoi' => 0];
        
        $sql = "SELECT trang_thai_phieu, COUNT(*) as so_luong 
                FROM phieu_yeu_cau_chinh_sua_diem 
                GROUP BY trang_thai_phieu";
        $stmt = $this->db->query($sql);
        
        $data = ['ChoDuyet' => 0, 'DaDuyet' => 0, 'TuChoi' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $data[$row['trang_thai_phieu']] = $row['so_luong'];
        }
        return $data;
    }

    /**
     * [MỚI] Dữ liệu cho Biểu đồ Cột: Top 5 lớp có điểm TB cao nhất
     */
    public function getChartDiemLop($school_id = null) {
        if ($this->db === null) return [];

        $sql = "SELECT l.ten_lop, AVG(dmhk.diem_tb_mon_hk) as diem_tb
                FROM diem_mon_hoc_hoc_ky dmhk
                JOIN ket_qua_hoc_tap kq ON dmhk.ma_hoc_sinh = kq.ma_hoc_sinh
                JOIN hoc_sinh hs ON kq.ma_hoc_sinh = hs.ma_hoc_sinh
                JOIN lop_hoc l ON hs.ma_lop = l.ma_lop
                WHERE l.trang_thai_lop = 'HoatDong' ";

        if ($school_id) {
            $sql .= " AND l.ma_truong = " . (int)$school_id;
        }

        $sql .= " GROUP BY l.ten_lop 
                  ORDER BY diem_tb DESC 
                  LIMIT 5"; 

        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getChartDiemLop: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Danh sách HS và điểm môn do giáo viên này dạy (theo học kỳ)
     */
    public function getDanhSachDiemMonByGV($ma_gv, $ma_hoc_ky = 'HK1') {
        if ($this->db === null) return [];
        $sql = "SELECT 
                    l.ma_lop, l.ten_lop,
                    mh.ma_mon_hoc, mh.ten_mon_hoc,
                    hs.ma_hoc_sinh, nd.ho_ten,
                    dmhk.diem_mieng,
                    dmhk.diem_15phut,
                    dmhk.diem_1tiet,
                    dmhk.diem_tx,
                    dmhk.diem_gua_ky, 
                    dmhk.diem_cuoi_ky,
                    dmhk.diem_tb_mon_hk, 
                    dmhk.xep_loai_mon
                FROM bang_phan_cong bpc
                JOIN lop_hoc l      ON bpc.ma_lop = l.ma_lop
                JOIN mon_hoc mh     ON bpc.ma_mon_hoc = mh.ma_mon_hoc
                JOIN hoc_sinh hs    ON hs.ma_lop = l.ma_lop
                JOIN nguoi_dung nd  ON hs.ma_hoc_sinh = nd.ma_nguoi_dung
                LEFT JOIN diem_mon_hoc_hoc_ky dmhk 
                    ON dmhk.ma_hoc_sinh = hs.ma_hoc_sinh
                   AND dmhk.ma_mon_hoc  = bpc.ma_mon_hoc
                   AND dmhk.ma_hoc_ky   = ?
                WHERE bpc.ma_giao_vien = ?
                ORDER BY l.ten_lop, mh.ten_mon_hoc, nd.ho_ten";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ma_hoc_ky, $ma_gv]);
        return $stmt->fetchAll();
    }

    /**
     * Nhập điểm lần đầu (chỉ INSERT nếu chưa có)
     */
    public function nhapDiemMon($ma_hoc_sinh, $ma_mon_hoc, $ma_hoc_ky, $data) {
        if ($this->db === null) return ['success' => false, 'message' => 'Lỗi kết nối CSDL.'];
        
        try {
            // Kiểm tra đã có điểm chưa
            $check = $this->db->prepare("SELECT ma_diem_mon_hk FROM diem_mon_hoc_hoc_ky 
                                         WHERE ma_hoc_sinh = ? AND ma_mon_hoc = ? AND ma_hoc_ky = ?");
            $check->execute([$ma_hoc_sinh, $ma_mon_hoc, $ma_hoc_ky]);
            $exists = $check->fetch();
            
            if ($exists) {
                return ['success' => false, 'message' => 'Điểm đã tồn tại! Nếu muốn sửa, vui lòng gửi phiếu yêu cầu.'];
            }
            
            // Tính điểm TX từ 3 cột
            $diem_mieng = $data['diem_mieng'] ?? null;
            $diem_15phut = $data['diem_15phut'] ?? null;
            $diem_1tiet = $data['diem_1tiet'] ?? null;
            
            $diem_tx = null;
            if ($diem_mieng !== null && $diem_15phut !== null && $diem_1tiet !== null) {
                $diem_tx = round(($diem_mieng + $diem_15phut + $diem_1tiet) / 3, 2);
            }
            
            $diem_gk = $data['diem_gua_ky'] ?? null;
            $diem_ck = $data['diem_cuoi_ky'] ?? null;
            
            // Tính ĐTB Môn HK: TX*1 + GK*2 + CK*3 / 6
            $diem_tb_mon_hk = null;
            if ($diem_tx !== null && $diem_gk !== null && $diem_ck !== null) {
                $diem_tb_mon_hk = round(($diem_tx + $diem_gk*2 + $diem_ck*3) / 6, 2);
            }
            
            // Xếp loại môn
            $xep_loai_mon = 'ChuaDat';
            if ($diem_tb_mon_hk !== null) {
                if ($diem_tb_mon_hk >= 8.0) $xep_loai_mon = 'Gioi';
                elseif ($diem_tb_mon_hk >= 6.5) $xep_loai_mon = 'Kha';
                elseif ($diem_tb_mon_hk >= 5.0) $xep_loai_mon = 'Dat';
            }
            
            // INSERT điểm mới
            $sql = "INSERT INTO diem_mon_hoc_hoc_ky 
                    (ma_hoc_sinh, ma_mon_hoc, ma_hoc_ky, diem_mieng, diem_15phut, diem_1tiet,
                     diem_tx, diem_gua_ky, diem_cuoi_ky, diem_tb_mon_hk, xep_loai_mon, ngay_cap_nhat)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $ma_hoc_sinh, $ma_mon_hoc, $ma_hoc_ky,
                $diem_mieng, $diem_15phut, $diem_1tiet,
                $diem_tx, $diem_gk, $diem_ck,
                $diem_tb_mon_hk, $xep_loai_mon
            ]);
            
            return ['success' => true, 'message' => 'Nhập điểm thành công!'];
            
        } catch (PDOException $e) {
            error_log("Lỗi nhapDiemMon: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
        }
    }
    
    /**
     * Kiểm tra xem học sinh đã có điểm môn này chưa
     */
    public function kiemTraDaTonTaiDiem($ma_hoc_sinh, $ma_mon_hoc, $ma_hoc_ky) {
        if ($this->db === null) return false;
        try {
            $stmt = $this->db->prepare("SELECT ma_diem_mon_hk FROM diem_mon_hoc_hoc_ky 
                                       WHERE ma_hoc_sinh = ? AND ma_mon_hoc = ? AND ma_hoc_ky = ?");
            $stmt->execute([$ma_hoc_sinh, $ma_mon_hoc, $ma_hoc_ky]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Lấy danh sách phiếu của giáo viên (theo mã GV)
     */
    public function getDanhSachPhieuByGV($ma_gv, $trang_thai = 'TatCa') {
        if ($this->db === null) return [];
        
        $sql = "SELECT 
                    p.ma_phieu,
                    p.ma_hoc_sinh,
                    p.ma_mon_hoc,
                    p.ma_hoc_ky,
                    p.ly_do_chinh_sua,
                    p.trang_thai_phieu,
                    p.ngay_lap_phieu,
                    p.ngay_duyet,
                    p.ly_do_tu_choi,
                    -- Điểm cũ
                    p.diem_mieng_cu, p.diem_15phut_cu, p.diem_1tiet_cu,
                    p.diem_gua_ky_cu, p.diem_cuoi_ky_cu,
                    -- Điểm mới đề xuất
                    p.diem_mieng_moi, p.diem_15phut_moi, p.diem_1tiet_moi,
                    p.diem_gua_ky_moi, p.diem_cuoi_ky_moi,
                    -- Thông tin học sinh, môn
                    nd.ho_ten AS ten_hoc_sinh,
                    mh.ten_mon_hoc,
                    -- Người duyệt (nếu có)
                    nd_duyet.ho_ten AS ten_nguoi_duyet
                FROM phieu_yeu_cau_chinh_sua_diem p
                JOIN hoc_sinh hs ON p.ma_hoc_sinh = hs.ma_hoc_sinh
                JOIN nguoi_dung nd ON hs.ma_hoc_sinh = nd.ma_nguoi_dung
                JOIN mon_hoc mh ON p.ma_mon_hoc = mh.ma_mon_hoc
                LEFT JOIN nguoi_dung nd_duyet ON p.ma_nguoi_duyet = nd_duyet.ma_nguoi_dung
                WHERE p.ma_giao_vien = ?";
        
        $params = [$ma_gv];
        
        if ($trang_thai !== 'TatCa') {
            $sql .= " AND p.trang_thai_phieu = ?";
            $params[] = $trang_thai;
        }
        
        $sql .= " ORDER BY p.ngay_lap_phieu DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Lỗi getDanhSachPhieuByGV: " . $e->getMessage());
            return [];
        }
    }

}
?>