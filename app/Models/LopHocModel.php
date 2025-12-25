<?php
/**
 * LopHocModel: Xử lý logic CSDL cho CRUD Lớp học
 */
class LopHocModel {
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

    /**
     * 1. Tự động sinh tên lớp tăng dần theo khối (VD: 10A1, 10A2, ...)
     */
    // public function generateTenLop($khoi, $ma_nam_hoc) {
    //     try {
    //         $sql = "SELECT ten_lop FROM lop_hoc 
    //                 WHERE khoi = :khoi AND ma_nam_hoc = :nam_hoc 
    //                 ORDER BY ten_lop DESC LIMIT 1";
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->execute([':khoi' => $khoi, ':nam_hoc' => $ma_nam_hoc]);
    //         $lastLop = $stmt->fetch();

    //         if ($lastLop) {
    //             preg_match('/(\d+)$/', $lastLop['ten_lop'], $matches);
    //             $nextNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
    //         } else {
    //             $nextNumber = 1;
    //         }
            
    //         return $khoi . "A" . $nextNumber;
    //     } catch (Exception $e) {
    //         error_log("Error generateTenLop: " . $e->getMessage());
    //         return $khoi . "A1";
    //     }
    // }
    /**
     * 1. Tự động sinh tên lớp: 10A..., 11B..., 12C...
     */
    public function generateTenLop($khoi, $ma_nam_hoc) {
        try {
            // 1. Xác định chữ cái dựa trên Khối
            $letter = 'A'; // Mặc định
            if ($khoi == 11) $letter = 'B';
            if ($khoi == 12) $letter = 'C';

            $prefix = $khoi . $letter; // VD: 10A, 11B, 12C

            // 2. Tìm lớp có số thứ tự lớn nhất hiện tại (VD: tìm 11B5 để sinh ra 11B6)
            // ORDER BY LENGTH(ten_lop) DESC để xử lý trường hợp số 10 lớn hơn số 9 (VD: 11B10 > 11B9)
            $sql = "SELECT ten_lop FROM lop_hoc 
                    WHERE khoi = :khoi 
                    AND ma_nam_hoc = :nam_hoc 
                    AND ten_lop LIKE CONCAT(:prefix, '%')
                    ORDER BY LENGTH(ten_lop) DESC, ten_lop DESC 
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':khoi' => $khoi, 
                ':nam_hoc' => $ma_nam_hoc,
                ':prefix' => $prefix
            ]);
            $lastLop = $stmt->fetch();

            if ($lastLop) {
                // Lấy phần số bằng cách xóa phần Prefix (VD: "11B12" xóa "11B" còn "12")
                // Dùng str_ireplace để không phân biệt hoa thường cho chắc
                $lastNumber = (int)str_ireplace($prefix, '', $lastLop['ten_lop']);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }
            
            return $prefix . $nextNumber;

        } catch (Exception $e) {
            error_log("Error generateTenLop: " . $e->getMessage());
            // Fallback nếu lỗi
            $letter = ($khoi == 11) ? 'B' : (($khoi == 12) ? 'C' : 'A');
            return $khoi . $letter . "1";
        }
    }

    /**
     * 2. Lấy danh sách phòng học trống (phòng chưa có lớp hoặc lớp đã bị xóa)
     */

    /**
     * 2. Lấy danh sách phòng học trống (Của trường hiện tại)
     * [ĐÃ SỬA] Thêm điều kiện lọc theo ma_truong
     */
    public function getPhongHocTrong($ma_nam_hoc, $ma_lop_tru_ra = null) {
        // Lấy ID trường từ Session
        $ma_truong = $_SESSION['admin_school_id'] ?? 1;

        try {
            $sql = "SELECT ph.ma_phong, ph.ten_phong, ph.suc_chua
                    FROM phong_hoc ph
                    WHERE ph.trang_thai_phong = 'HoatDong'
                    AND ph.ma_truong = :ma_truong  -- [QUAN TRỌNG] Chỉ lấy phòng của trường đang đăng nhập
                    AND ph.ma_phong NOT IN (
                        SELECT DISTINCT ma_phong_hoc_chinh FROM lop_hoc 
                        WHERE ma_nam_hoc = :nam_hoc 
                        AND ma_phong_hoc_chinh IS NOT NULL 
                        AND trang_thai_lop = 'HoatDong'";
            
            // Nếu đang sửa, trừ lớp hiện tại ra (để hiện lại phòng cũ của lớp đó)
            if ($ma_lop_tru_ra) {
                $sql .= " AND ma_lop != :ma_lop_tru_ra";
            }

            $sql .= ")"; // Đóng ngoặc NOT IN
            $sql .= " ORDER BY ph.ten_phong ASC";
            
            $stmt = $this->db->prepare($sql);
            
            $params = [
                ':nam_hoc' => $ma_nam_hoc,
                ':ma_truong' => $ma_truong // Bind tham số trường vào
            ];
            
            if ($ma_lop_tru_ra) {
                $params[':ma_lop_tru_ra'] = $ma_lop_tru_ra;
            }

            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getPhongHocTrong: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 3. Lấy danh sách tổ hợp môn
     */
    public function getAllToHop() {
        try {
            $sql = "SELECT ma_to_hop_mon, ten_to_hop, mo_ta 
                    FROM to_hop_mon 
                    ORDER BY ten_to_hop ASC";
            return $this->db->query($sql)->fetchAll();
        } catch (Exception $e) {
            error_log("Error getAllToHop: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 4. Lấy môn học theo tổ hợp (gồm môn tổ hợp + môn bắt buộc)
     */
    public function getMonHocByToHop($ma_to_hop) {
        $sql = "
            SELECT m.ma_mon_hoc, m.ten_mon_hoc, m.loai_mon,
                3 AS so_tiet_hk1,
                'bat_buoc' AS loai
            FROM mon_hoc m
            WHERE m.loai_mon = 'Bắt buộc' AND m.trang_thai_mon_hoc = 'HoatDong'
            UNION ALL
            SELECT m.ma_mon_hoc, m.ten_mon_hoc, m.loai_mon,
                3 AS so_tiet_hk1,
                'tu_chon' AS loai
            FROM to_hop_mon_mon_hoc th
            JOIN mon_hoc m ON m.ma_mon_hoc = th.ma_mon_hoc
            WHERE th.ma_to_hop_mon = :th AND m.trang_thai_mon_hoc = 'HoatDong'
            AND m.loai_mon <> 'Bắt buộc'
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['th' => $ma_to_hop]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMonHocById($ma_mon_hoc) {
        $sql = "SELECT ma_mon_hoc, ten_mon_hoc, loai_mon FROM mon_hoc WHERE ma_mon_hoc = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $ma_mon_hoc]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 5. Lấy danh sách giáo viên hoạt động
     */
    public function getDanhSachGiaoVien($ma_truong = null) {
        if ($this->db === null) return [];
        
        $sql = "SELECT DISTINCT 
                    gv.ma_giao_vien,
                    nd.ho_ten,
                    gv.chuc_vu
                FROM giao_vien gv
                JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
                JOIN tai_khoan tk ON nd.ma_tai_khoan = tk.ma_tai_khoan
                WHERE tk.trang_thai = 'HoatDong'";
        
        if ($ma_truong) {
            // Chỉ lấy GV có gv.ma_truong = ? HOẶC dạy lớp của trường ?
            $sql .= " AND (
                        gv.ma_truong = :ma_truong
                        OR gv.ma_giao_vien IN (
                            SELECT DISTINCT bpc.ma_giao_vien
                            FROM bang_phan_cong bpc
                            JOIN lop_hoc lh ON bpc.ma_lop = lh.ma_lop
                            WHERE lh.ma_truong = :ma_truong2
                        )
                    )";
        }
        
        $sql .= " ORDER BY nd.ho_ten";
        
        try {
            $stmt = $this->db->prepare($sql);
            if ($ma_truong) {
                $stmt->execute([
                    ':ma_truong' => $ma_truong,
                    ':ma_truong2' => $ma_truong
                ]);
            } else {
                $stmt->execute();
            }
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getDanhSachGiaoVien: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy danh sách giáo viên dạy môn học cụ thể (mỗi GV chỉ 1 lần)
     */
    public function getDanhSachGiaoVienTheoMon($ma_mon_hoc, $ma_truong) {
        if ($this->db === null) return [];
        
        $sql = "SELECT DISTINCT 
                    gv.ma_giao_vien,
                    nd.ho_ten,
                    mh.ten_mon_hoc
                FROM giao_vien gv
                JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
                JOIN tai_khoan tk ON nd.ma_tai_khoan = tk.ma_tai_khoan
                JOIN bang_phan_cong bpc ON gv.ma_giao_vien = bpc.ma_giao_vien
                JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
                JOIN lop_hoc lh ON bpc.ma_lop = lh.ma_lop
                WHERE tk.trang_thai = 'HoatDong'
                AND mh.ma_mon_hoc = :ma_mon
                AND lh.ma_truong = :ma_truong
                ORDER BY nd.ho_ten";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':ma_mon' => $ma_mon_hoc,
                ':ma_truong' => $ma_truong
            ]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getDanhSachGiaoVienTheoMon: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy tất cả GV kèm môn họ dạy
     */
    public function getDanhSachGiaoVienVaMonDay($ma_truong) {
        if ($this->db === null) return [];
        
        $sql = "SELECT DISTINCT 
                    gv.ma_giao_vien,
                    nd.ho_ten,
                    mh.ma_mon_hoc,
                    mh.ten_mon_hoc
                FROM giao_vien gv
                JOIN nguoi_dung nd ON gv.ma_giao_vien = nd.ma_nguoi_dung
                JOIN tai_khoan tk ON nd.ma_tai_khoan = tk.ma_tai_khoan
                JOIN bang_phan_cong bpc ON gv.ma_giao_vien = bpc.ma_giao_vien
                JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
                JOIN lop_hoc lh ON bpc.ma_lop = lh.ma_lop
                WHERE tk.trang_thai = 'HoatDong'
                AND lh.ma_truong = :ma_truong
                AND mh.ten_mon_hoc NOT IN ('Chào cờ', 'Sinh hoạt lớp')
                ORDER BY nd.ho_ten";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_truong' => $ma_truong]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getDanhSachGiaoVienVaMonDay: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 6. Tạo lớp học mới + phân công giáo viên (TRANSACTION)
         */
        public function getDanhSachLopPaginated($ma_truong, $page = 1, $limit = 15, $khoi = null) {
            if ($this->db === null) return [];

            $offset = ($page - 1) * $limit;

            $sql = "SELECT 
                        lh.ma_lop,
                        lh.ten_lop,
                        lh.khoi,
                        lh.si_so,
                        lh.trang_thai_lop,
                        thm.ten_to_hop,
                        ph.ten_phong,
                        nd.ho_ten as ten_gvcn
                    FROM lop_hoc lh
                    LEFT JOIN to_hop_mon thm ON lh.ma_to_hop_mon = thm.ma_to_hop_mon
                    LEFT JOIN phong_hoc ph ON lh.ma_phong_hoc_chinh = ph.ma_phong
                    LEFT JOIN nguoi_dung nd ON lh.ma_gvcn = nd.ma_nguoi_dung
                    WHERE lh.ma_truong = :ma_truong";

            if (!empty($khoi)) {
                $sql .= " AND lh.khoi = :khoi";
            }

            $sql .= " ORDER BY lh.khoi, lh.ten_lop
                       LIMIT :limit OFFSET :offset";

            try {
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':ma_truong', $ma_truong, PDO::PARAM_INT);
                if (!empty($khoi)) {
                    $stmt->bindParam(':khoi', $khoi, PDO::PARAM_INT);
                }
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log("Error getDanhSachLopPaginated: " . $e->getMessage());
                return [];
            }
        }

        /**
         * Đếm tổng số lớp học của trường
         */
        public function countLopHoc($ma_truong, $khoi = null) {
            if ($this->db === null) return 0;

            $sql = "SELECT COUNT(*) FROM lop_hoc WHERE ma_truong = ?";
            $params = [$ma_truong];

            if (!empty($khoi)) {
                $sql .= " AND khoi = ?";
                $params[] = $khoi;
            }

            try {
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                return (int)$stmt->fetchColumn();
            } catch (PDOException $e) {
                error_log("Error countLopHoc: " . $e->getMessage());
                return 0;
            }
        }
    public function createLopFull($dataLop, $listPhanCong) {
        try {
            $this->db->beginTransaction();

            // A. Insert Lớp Học
            $sqlLop = "INSERT INTO lop_hoc 
                       (ten_lop, khoi, ma_to_hop_mon, ma_nam_hoc, ma_truong, ma_phong_hoc_chinh, ma_gvcn, si_so, trang_thai_lop) 
                       VALUES 
                       (:ten, :khoi, :tohop, :nam, :truong, :phong, :gvcn, 0, 'HoatDong')";
            
            $stmt = $this->db->prepare($sqlLop);
            $stmt->execute([
                ':ten' => $dataLop['ten_lop'],
                ':khoi' => $dataLop['khoi'],
                ':tohop' => $dataLop['ma_to_hop'],
                ':nam' => $dataLop['ma_nam_hoc'],
                ':truong' => $dataLop['ma_truong'],
                ':phong' => $dataLop['ma_phong_hoc_chinh'],
                ':gvcn' => $dataLop['ma_gvcn']
            ]);
            
            $maLopMoi = $this->db->lastInsertId();
            error_log("✓ Created lop_hoc ID: $maLopMoi, Name: {$dataLop['ten_lop']}");

            // B. Insert Phân công giáo viên
            $sqlPC = "INSERT INTO bang_phan_cong 
                      (ten_bang_phan_cong, trang_thai, ma_mon_hoc, ma_giao_vien, ma_lop, so_tiet_tuan) 
                      VALUES 
                      (:ten_pc, 'HoatDong', :ma_mon, :ma_gv, :ma_lop, :so_tiet)";
            
            $stmtPC = $this->db->prepare($sqlPC);
            $insertedPC = 0;

            foreach ($listPhanCong as $pc) {
                if (!empty($pc['ma_gv']) && !empty($pc['ma_mon'])) {
                    $tenPhanCong = "PC " . $pc['ten_mon'] . " - " . $dataLop['ten_lop'];

                    $stmtPC->execute([
                        ':ten_pc' => $tenPhanCong,
                        ':ma_mon' => $pc['ma_mon'],
                        ':ma_gv' => $pc['ma_gv'],
                        ':ma_lop' => $maLopMoi,
                        ':so_tiet' => (int)($pc['so_tiet'] ?? 3)
                    ]);
                    $insertedPC++;
                }
            }
            
            error_log("✓ Created $insertedPC bang_phan_cong records");

            $this->db->commit();
            return [
                'success' => true,
                'ma_lop' => $maLopMoi,
                'message' => "Tạo lớp {$dataLop['ten_lop']} thành công!"
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("✗ Error createLopFull: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Xóa lớp học (cùng phân công)
     */
    public function deleteLopHoc($ma_lop) {
        if ($this->db === null) return false;
        
        $this->db->beginTransaction();
        try {
            // 1. Xóa bang_phan_cong trước
            $sql_phan_cong = "DELETE FROM bang_phan_cong WHERE ma_lop = ?";
            $stmt_phan_cong = $this->db->prepare($sql_phan_cong);
            $stmt_phan_cong->execute([$ma_lop]);
            
            // 2. Xóa lop_hoc
            $sql_lop = "DELETE FROM lop_hoc WHERE ma_lop = ?";
            $stmt_lop = $this->db->prepare($sql_lop);
            $result = $stmt_lop->execute([$ma_lop]);
            
            $this->db->commit();
            error_log("✓ Deleted lop_hoc ID: $ma_lop");
            return $result;
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("✗ Lỗi deleteLopHoc: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy chi tiết 1 lớp học
     */
    public function getLopHocById($ma_lop) {
        if ($this->db === null) return null;
        
        $sql = "SELECT 
                    lh.*,
                    thm.ten_to_hop,
                    ph.ten_phong,
                    nd.ho_ten as ten_gvcn
                FROM lop_hoc lh
                LEFT JOIN to_hop_mon thm ON lh.ma_to_hop_mon = thm.ma_to_hop_mon
                LEFT JOIN phong_hoc ph ON lh.ma_phong_hoc_chinh = ph.ma_phong
                LEFT JOIN nguoi_dung nd ON lh.ma_gvcn = nd.ma_nguoi_dung
                WHERE lh.ma_lop = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_lop]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getLopHocById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy danh sách lớp học theo trường
     */
    public function getDanhSachLopByTruong($ma_truong) {
        if ($this->db === null) return [];
        
        $sql = "SELECT ma_lop, ten_lop 
                FROM lop_hoc 
                WHERE ma_truong = ? AND trang_thai_lop = 'HoatDong'
                ORDER BY ten_lop";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_truong]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getDanhSachLopByTruong: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 7. Sửa lớp học + cập nhật phân công giáo viên (TRANSACTION)
     * 
     * @param int $ma_lop - Mã lớp cần sửa
     * @param array $dataLop - Dữ liệu lớp mới
     * @param array $listPhanCong - Danh sách phân công mới
     * @return array ['success' => bool, 'message' => string]
     */
    // public function updateLopFull($ma_lop, $dataLop, $listPhanCong) {
    //     try {
    //         $this->db->beginTransaction();

    //         // A. Cập nhật thông tin lớp học
    //         $sqlUpdate = "UPDATE lop_hoc SET 
    //                         ten_lop = :ten,
    //                         khoi = :khoi,
    //                         ma_to_hop_mon = :tohop,
    //                         ma_phong_hoc_chinh = :phong,
    //                         ma_gvcn = :gvcn,
    //                         trang_thai_lop = :trang_thai
    //                     WHERE ma_lop = :ma_lop";
            
    //         $stmt = $this->db->prepare($sqlUpdate);
    //         $stmt->execute([
    //             ':ten' => $dataLop['ten_lop'],
    //             ':khoi' => $dataLop['khoi'],
    //             ':tohop' => $dataLop['ma_to_hop'],
    //             ':phong' => $dataLop['ma_phong_hoc_chinh'],
    //             ':gvcn' => $dataLop['ma_gvcn'],
    //             ':trang_thai' => $dataLop['trang_thai_lop'] ?? 'HoatDong',
    //             ':ma_lop' => $ma_lop
    //         ]);
            
    //         error_log("✓ Updated lop_hoc ID: $ma_lop, Name: {$dataLop['ten_lop']}");

    //         // B. Xóa toàn bộ phân công cũ
    //         $sqlDeletePC = "DELETE FROM bang_phan_cong WHERE ma_lop = ?";
    //         $stmtDelete = $this->db->prepare($sqlDeletePC);
    //         $stmtDelete->execute([$ma_lop]);
            
    //         $deletedCount = $stmtDelete->rowCount();
    //         error_log("✓ Deleted $deletedCount old bang_phan_cong records");

    //         // C. Insert lại phân công mới
    //         $sqlInsertPC = "INSERT INTO bang_phan_cong 
    //                         (ten_bang_phan_cong, trang_thai, ma_mon_hoc, ma_giao_vien, ma_lop, so_tiet_tuan) 
    //                         VALUES 
    //                         (:ten_pc, 'HoatDong', :ma_mon, :ma_gv, :ma_lop, :so_tiet)";
            
    //         $stmtPC = $this->db->prepare($sqlInsertPC);
    //         $insertedPC = 0;

    //         foreach ($listPhanCong as $pc) {
    //             if (!empty($pc['ma_gv']) && !empty($pc['ma_mon'])) {
    //                 $tenPhanCong = "PC " . $pc['ten_mon'] . " - " . $dataLop['ten_lop'];

    //                 $stmtPC->execute([
    //                     ':ten_pc' => $tenPhanCong,
    //                     ':ma_mon' => $pc['ma_mon'],
    //                     ':ma_gv' => $pc['ma_gv'],
    //                     ':ma_lop' => $ma_lop,
    //                     ':so_tiet' => (int)($pc['so_tiet'] ?? 3)
    //                 ]);
    //                 $insertedPC++;
    //             }
    //         }
            
    //         error_log("✓ Created $insertedPC new bang_phan_cong records");

    //         $this->db->commit();
    //         return [
    //             'success' => true,
    //             'message' => "Cập nhật lớp {$dataLop['ten_lop']} thành công!"
    //         ];
            
    //     } catch (Exception $e) {
    //         $this->db->rollBack();
    //         error_log("✗ Error updateLopFull: " . $e->getMessage());
    //         return [
    //             'success' => false,
    //             'message' => 'Lỗi: ' . $e->getMessage()
    //         ];
    //     }
    // }

    /**
     * [MỚI] Kiểm tra xem GV này đã chủ nhiệm lớp nào khác trong năm học này chưa
     * @param int $ma_gv Mã giáo viên
     * @param int $ma_nam_hoc Mã năm học
     * @param int|null $ma_lop_tru_ra (Dùng khi sửa) Mã lớp hiện tại để trừ ra
     * @return bool True nếu ĐÃ bận, False nếu chưa chủ nhiệm lớp nào
     */
    public function isGVCNDaCoLop($ma_gv, $ma_nam_hoc, $ma_lop_tru_ra = null) {
        $sql = "SELECT COUNT(*) FROM lop_hoc 
                WHERE ma_gvcn = :ma_gv 
                AND ma_nam_hoc = :nam_hoc 
                AND trang_thai_lop = 'HoatDong'"; // Chỉ tính lớp đang hoạt động
        
        $params = [':ma_gv' => $ma_gv, ':nam_hoc' => $ma_nam_hoc];

        if ($ma_lop_tru_ra) {
            $sql .= " AND ma_lop != :ma_lop"; // Trừ chính lớp đang sửa
            $params[':ma_lop'] = $ma_lop_tru_ra;
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error check GVCN: " . $e->getMessage());
            return false; // Mặc định cho qua nếu lỗi DB (hoặc true tùy policy)
        }
    }

    /**
     * [ĐÃ SỬA] Cập nhật lớp học + Phân công (Logic thông minh: Diff thay vì Delete All)
     * Giúp giữ lại ma_phan_cong cũ để không mất điểm số.
     */
    public function updateLopFull($ma_lop, $dataLop, $listPhanCong) {
        try {
            $this->db->beginTransaction();

            // A. Cập nhật thông tin lớp học
            $sqlUpdate = "UPDATE lop_hoc SET 
                            ten_lop = :ten,
                            khoi = :khoi,
                            ma_to_hop_mon = :tohop,
                            ma_phong_hoc_chinh = :phong,
                            ma_gvcn = :gvcn,
                            trang_thai_lop = :trang_thai
                          WHERE ma_lop = :ma_lop";
            
            $stmt = $this->db->prepare($sqlUpdate);
            $stmt->execute([
                ':ten' => $dataLop['ten_lop'],
                ':khoi' => $dataLop['khoi'],
                ':tohop' => $dataLop['ma_to_hop'],
                ':phong' => $dataLop['ma_phong_hoc_chinh'],
                ':gvcn' => $dataLop['ma_gvcn'],
                ':trang_thai' => $dataLop['trang_thai_lop'] ?? 'HoatDong',
                ':ma_lop' => $ma_lop
            ]);

            // B. XỬ LÝ PHÂN CÔNG (Logic Diff)
            
            // 1. Lấy danh sách phân công CŨ từ DB
            $sqlGetOld = "SELECT ma_phan_cong, ma_mon_hoc FROM bang_phan_cong WHERE ma_lop = ?";
            $stmtOld = $this->db->prepare($sqlGetOld);
            $stmtOld->execute([$ma_lop]);
            $oldAssignments = $stmtOld->fetchAll(PDO::FETCH_ASSOC);

            // Map lại mảng cũ: [ma_mon_hoc => ma_phan_cong] để dễ tìm kiếm
            $mapOld = [];
            foreach ($oldAssignments as $item) {
                $mapOld[$item['ma_mon_hoc']] = $item['ma_phan_cong'];
            }

            // 2. Chuẩn bị câu lệnh SQL
            $sqlInsert = "INSERT INTO bang_phan_cong 
                          (ten_bang_phan_cong, trang_thai, ma_mon_hoc, ma_giao_vien, ma_lop, so_tiet_tuan) 
                          VALUES (:ten, 'HoatDong', :mon, :gv, :lop, :tiet)";
            
            $sqlUpdatePC = "UPDATE bang_phan_cong SET 
                            ma_giao_vien = :gv, 
                            so_tiet_tuan = :tiet, 
                            ten_bang_phan_cong = :ten 
                            WHERE ma_phan_cong = :id";
            
            $stmtInsert = $this->db->prepare($sqlInsert);
            $stmtUpdatePC = $this->db->prepare($sqlUpdatePC);

            // Mảng lưu các môn ĐÃ XỬ LÝ trong danh sách mới
            $processedMonIds = [];

            // 3. Duyệt danh sách MỚI gửi lên
            foreach ($listPhanCong as $pc) {
                if (empty($pc['ma_gv']) || empty($pc['ma_mon'])) continue;

                $maMon = $pc['ma_mon'];
                $tenPC = "PC " . $pc['ten_mon'] . " - " . $dataLop['ten_lop'];
                $soTiet = (int)($pc['so_tiet'] ?? 3);
                
                $processedMonIds[] = $maMon; // Đánh dấu môn này vẫn còn

                if (isset($mapOld[$maMon])) {
                    // --- TRƯỜNG HỢP 1: Môn này ĐÃ CÓ -> UPDATE ---
                    // Ta giữ nguyên ma_phan_cong cũ (là $mapOld[$maMon])
                    $stmtUpdatePC->execute([
                        ':gv' => $pc['ma_gv'],
                        ':tiet' => $soTiet,
                        ':ten' => $tenPC,
                        ':id' => $mapOld[$maMon]
                    ]);
                } else {
                    // --- TRƯỜNG HỢP 2: Môn này MỚI THÊM -> INSERT ---
                    $stmtInsert->execute([
                        ':ten' => $tenPC,
                        ':mon' => $maMon,
                        ':gv' => $pc['ma_gv'],
                        ':lop' => $ma_lop,
                        ':tiet' => $soTiet
                    ]);
                }
            }

            // 4. Xóa các môn KHÔNG CÒN trong danh sách mới (DELETE)
            // Những môn có trong mapOld nhưng không nằm trong $processedMonIds nghĩa là user đã bỏ môn đó
            $monCanXoa = array_diff(array_keys($mapOld), $processedMonIds);
            
            if (!empty($monCanXoa)) {
                $idsToDelete = [];
                foreach ($monCanXoa as $mId) {
                    $idsToDelete[] = $mapOld[$mId];
                }
                
                // Chuyển mảng ID thành chuỗi ?,?,?
                $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
                $sqlDelete = "DELETE FROM bang_phan_cong WHERE ma_phan_cong IN ($placeholders)";
                $stmtDelete = $this->db->prepare($sqlDelete);
                $stmtDelete->execute($idsToDelete);
            }

            $this->db->commit();
            return [
                'success' => true,
                'message' => "Cập nhật lớp {$dataLop['ten_lop']} thành công!"
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("✗ Error updateLopFull: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi cập nhật: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 8. Lấy danh sách phân công của 1 lớp (để hiển thị khi sửa)
     * 
     * @param int $ma_lop
     * @return array Danh sách phân công kèm thông tin GV, môn
     */
    public function getPhanCongByLop($ma_lop) {
        if ($this->db === null) return [];
        
        $sql = "SELECT 
                    bpc.ma_phan_cong AS ma_phan_cong,   -- đổi tên cột đúng
                    bpc.ma_mon_hoc,
                    bpc.ma_giao_vien,
                    bpc.so_tiet_tuan,
                    mh.ten_mon_hoc,
                    mh.loai_mon,
                    nd.ho_ten as ten_giao_vien
                FROM bang_phan_cong bpc
                LEFT JOIN mon_hoc mh ON bpc.ma_mon_hoc = mh.ma_mon_hoc
                LEFT JOIN nguoi_dung nd ON bpc.ma_giao_vien = nd.ma_nguoi_dung
                WHERE bpc.ma_lop = ?
                  AND (mh.ten_mon_hoc IS NULL OR mh.ten_mon_hoc NOT IN ('Chào cờ', 'Sinh hoạt lớp'))
                ORDER BY 
                    CASE 
                        WHEN mh.loai_mon = 'Bắt buộc' THEN 1
                        ELSE 2
                    END,
                    mh.ten_mon_hoc";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_lop]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getPhanCongByLop: " . $e->getMessage());
            return [];
        }
    }


    /**
     * [MỚI] Đếm số lượng học sinh đang học trong lớp
     */
    public function countHocSinhByLop($ma_lop) {
        // Giả sử bảng học sinh tên là 'hoc_sinh' và có cột 'ma_lop'
        // Thêm điều kiện trang_thai nếu cần (ví dụ chỉ đếm học sinh đang học)
        $sql = "SELECT COUNT(*) FROM hoc_sinh WHERE ma_lop = :ma_lop"; 
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_lop' => $ma_lop]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * [MỚI] Lấy thông tin sức chứa của phòng học
     */
    public function getSucChuaByPhong($ma_phong) {
        $sql = "SELECT suc_chua FROM phong_hoc WHERE ma_phong = :ma_phong";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':ma_phong' => $ma_phong]);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 999; // Nếu lỗi thì trả về số lớn để không chặn sai
        }
    }


    
}
?>